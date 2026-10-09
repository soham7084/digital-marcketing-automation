<?php
header('Content-Type: application/json; charset=UTF-8');
require_once '../includes/config.php';

// Force timezone for accurate schedule matching
date_default_timezone_set('Asia/Kolkata');

// ==========================================
// 1. Security Check
// ==========================================
$secret_key = "alert_123"; // Ensure this matches your cron setup
if (!isset($_GET['cron_secret']) || $_GET['cron_secret'] !== $secret_key) {
    http_response_code(401);
    echo json_encode(["error" => "Unauthorized."]);
    exit;
}

$n8n_webhook_urls = [
    "http://localhost:5678/webhook/rank_alert"
];

try {
    // ==========================================
    // 2. Fetch Tasks Scheduled for THIS Exact Minute
    // ==========================================
    $sql = "SELECT r.input_id, r.user_id 
            FROM rank_alert_input r
            LEFT JOIN alert_engine_daily_trigger a 
                ON r.input_id = a.input_id 
                AND DATE_FORMAT(a.hit_time, '%Y-%m-%d %H:%i') = DATE_FORMAT(NOW(), '%Y-%m-%d %H:%i')
            WHERE DATE_FORMAT(r.run_time, '%H:%i') = DATE_FORMAT(NOW(), '%H:%i')
            AND a.trigger_id IS NULL";

    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $due_tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!$due_tasks) {
        echo json_encode(["message" => "No alert workflows scheduled for this minute."]);
        exit;
    }

    $triggered_count = 0;

    // ==========================================
    // 3. Process & Trigger
    // ==========================================
    foreach ($due_tasks as $task) {
        // A. Log the trigger start in the new alert table
        $insert_sql = "INSERT INTO alert_engine_daily_trigger (input_id, hit_time) VALUES (?, NOW())";
        $insert_stmt = $pdo->prepare($insert_sql);
        $insert_stmt->execute([$task['input_id']]);
        
        $trigger_id = $pdo->lastInsertId();

        // B. Prepare the payload for n8n
        $payload = json_encode([
            "user_id"    => (int)$task['user_id'],
            "input_id"   => (int)$task['input_id'],
            "trigger_id" => (int)$trigger_id
        ]);

        // C. Fire the n8n Webhook
        foreach ($n8n_webhook_urls as $n8n_webhook_url) {
            $ch = curl_init($n8n_webhook_url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
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

    echo json_encode(["success" => true, "alerts_triggered" => $triggered_count]);

} catch (PDOException $e) {
    error_log("Alert Cron DB Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(["error" => "Internal Server Error."]);
}
?>