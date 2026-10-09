<?php
// seo_cron_trigger.php
// Endpoint triggered via external cron service (e.g. cron-job.org) to execute daily SEO Audit n8n flow

header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once '../includes/config.php';

// Force timezone to match user operating local time
date_default_timezone_set('Asia/Kolkata');

// ==========================================
// 1. Security Check
// ==========================================
$secretKey = "seo_123";
$providedSecret = isset($_GET['cron_secret']) ? $_GET['cron_secret'] : (isset($_GET['secret']) ? $_GET['secret'] : null);

if ($providedSecret !== null && $providedSecret !== $secretKey) {
    http_response_code(401);
    echo json_encode(["success" => false, "error" => "Unauthorized. Invalid cron secret key."]);
    exit;
}

try {
    // Current time strings
    $currentTime = date('H:i');
    $currentDateTime = date('Y-m-d H:i');

    // ==========================================
    // 2. Query Scheduled Tasks For Current Minute
    // ==========================================
    $sql = "
        SELECT i.audit_input_id, i.user_id, i.page_url, i.email, i.run_time 
        FROM seo_audit_inputs i
        LEFT JOIN daily_schedule_trigger_seo d 
            ON i.audit_input_id = d.audit_input_id 
            AND DATE_FORMAT(d.hit_time, '%Y-%m-%d %H:%i') = ?
        WHERE i.run_time IS NOT NULL 
          AND i.run_time != ''
          AND TIME_FORMAT(STR_TO_DATE(i.run_time, '%H:%i'), '%H:%i') = ?
          AND d.trigger_id IS NULL
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$currentDateTime, $currentTime]);
    $dueTasks = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($dueTasks)) {
        http_response_code(200);
        echo json_encode([
            "success" => true,
            "message" => "Cron run finished: No SEO audit workflows scheduled for " . $currentTime . " or already executed.",
            "time" => $currentTime
        ]);
        exit;
    }

    $n8nWebhookUrls = [
        'http://localhost:5678/webhook/seo-audit-1',
        'http://localhost:5678/webhook/seo-audit'
    ];

    $triggeredCount = 0;

    // ==========================================
    // 3. Process & Fire Webhook for Each Due Task
    // ==========================================
    foreach ($dueTasks as $task) {
        $auditInputId = $task['audit_input_id'];
        $userId       = $task['user_id'];
        $pageUrl      = $task['page_url'];
        $email        = $task['email'];
        $runTime      = $task['run_time'];

        // A. Insert into daily_schedule_trigger_seo table
        $triggerStmt = $pdo->prepare("INSERT INTO daily_schedule_trigger_seo (audit_input_id, status, hit_time) VALUES (?, 'pending', CURRENT_TIMESTAMP)");
        $triggerStmt->execute([$auditInputId]);
        $triggerId = $pdo->lastInsertId();

        // B. Prepare n8n payload
        $n8nPayload = [
            "trigger_id"     => (int)$triggerId,
            "audit_input_id" => (int)$auditInputId,
            "user_id"        => (int)$userId,
            "page_url"       => $pageUrl,
            "email"          => $email,
            "run_time"       => $runTime,
            "flow_name"      => "seo audit_1"
        ];

        // C. Fire n8n Webhook
        $webhookTriggered = false;
        foreach ($n8nWebhookUrls as $webhookUrl) {
            $ch = curl_init($webhookUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($n8nPayload));
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            curl_setopt($ch, CURLOPT_TIMEOUT, 4);
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode >= 200 && $httpCode < 300) {
                $webhookTriggered = true;
                break;
            }
        }

        if (!$webhookTriggered) {
            error_log("SEO Audit Cron Warning: Webhook call did not succeed for trigger_id {$triggerId}");
        }

        $triggeredCount++;
    }

    http_response_code(200);
    echo json_encode([
        "success" => true,
        "message" => "SEO Audit Cron executed successfully.",
        "workflows_triggered" => $triggeredCount,
        "time" => $currentTime
    ]);

} catch (PDOException $e) {
    error_log("Database error in seo_cron_trigger.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(["success" => false, "error" => "Internal Server Error during cron execution: " . $e->getMessage()]);
}
?>
