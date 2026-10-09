<?php
header('Content-Type: application/json; charset=UTF-8');
require_once '../includes/config.php';

// Force timezone so the database time matches your local operating time
date_default_timezone_set('Asia/Kolkata');

// ==========================================
// 1. Security Check
// ==========================================
$secret_key = "business_123"; // Change this to a secure string
if (!isset($_GET['cron_secret']) || $_GET['cron_secret'] !== $secret_key) {
    http_response_code(401);
    echo json_encode(["error" => "Unauthorized. Invalid cron secret."]);
    exit;
}

$n8n_webhook_urls = [
    "http://localhost:5678/webhook/business-ranking"
];

try {
    // ==========================================
    // 2. Fetch Tasks Scheduled for THIS Exact Minute
    // ==========================================
    // Changed LEFT JOIN: Now it only blocks if a trigger already fired in this EXACT minute today.
    // This allows the user to run the flow multiple times a day if their run_time changes.
    $sql = "SELECT b.ba_id, b.user_id, b.priority_min, b.user_location, b.business_name, b.run_time 
            FROM business_account_ranking_inputs b
            LEFT JOIN daily_schedule_trigger_ba d 
                ON b.ba_id = d.ba_id 
                AND DATE_FORMAT(d.hit_time, '%Y-%m-%d %H:%i') = DATE_FORMAT(NOW(), '%Y-%m-%d %H:%i')
            WHERE DATE_FORMAT(STR_TO_DATE(b.run_time, '%H:%i'), '%H:%i') = DATE_FORMAT(NOW(), '%H:%i')
            AND d.trigger_id IS NULL";

    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $due_tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!$due_tasks) {
        echo json_encode(["message" => "No workflows scheduled for this exact minute."]);
        exit;
    }

    $triggered_count = 0;

    // ==========================================
    // 3. Process & Trigger
    // ==========================================
    foreach ($due_tasks as $task) {
        
        // A. Insert into the trigger table as 'pending'
        $insert_sql = "INSERT INTO daily_schedule_trigger_ba (ba_id, status, hit_time) VALUES (?, 'pending', NOW())";
        $insert_stmt = $pdo->prepare($insert_sql);
        $insert_stmt->execute([$task['ba_id']]);
        
        $trigger_id = $pdo->lastInsertId();

        // B. Prepare the payload for n8n
        $payload = json_encode([
            "user_id"       => (int)$task['user_id'],
            "priority_min"  => (int)$task['priority_min'],
            "user_location" => $task['user_location'],
            "business_name" => $task['business_name'],
            "trigger_id"    => (int)$trigger_id
        ]);

        // C. Fire the n8n Webhook via cURL (Fire and Forget)
        foreach ($n8n_webhook_urls as $n8n_webhook_url) {
            $ch = curl_init($n8n_webhook_url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Content-Length: ' . strlen($payload)
            ]);

            curl_setopt($ch, CURLOPT_TIMEOUT, 3); 
            $res = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            if ($httpCode >= 200 && $httpCode < 300) {
                break;
            }
        }

        $triggered_count++;
    }

    echo json_encode(["success" => true, "workflows_triggered" => $triggered_count]);

} catch (PDOException $e) {
    error_log("Cron DB Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(["error" => "Internal Server Error."]);
}
?>