<?php
// cron_scheduler.php
// Triggered via external web cron service (e.g., cron-job.org)

// --- SECURITY CHECK ---
// Ensure this matches the password you use in your cron-job.org URL
$secretPassword = "my_secure_password_123";

if (!isset($_GET['secret']) || $_GET['secret'] !== $secretPassword) {
    http_response_code(403);
    echo json_encode(["error" => "Forbidden. Invalid or missing secret key."]);
    exit;
}
// ----------------------

header('Content-Type: application/json');
require_once '../includes/config.php';

try {
    // Force the timezone to Indian Standard Time
    date_default_timezone_set('Asia/Kolkata');
    
    // 1. Get the current time variables
    $currentTime = date('H:i'); // E.g., "14:30" (used to match the user's scheduled time)
    $currentDateTime = date('Y-m-d H:i'); // E.g., "2026-07-08 14:30" (used to prevent double-firing in the same minute)

    // 2. Query Table 1a with a LEFT JOIN to Table 1b to prevent exact-minute duplicate runs
    $sql = "
        SELECT ki.* FROM keyword_inputs ki
        LEFT JOIN daily_schedule_trigger dst 
            ON ki.input_id = dst.input_id 
            AND DATE_FORMAT(dst.hit_time, '%Y-%m-%d %H:%i') = ?
        WHERE TIME_FORMAT(ki.run_time, '%H:%i') = ?
        AND dst.trigger_id IS NULL
    ";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$currentDateTime, $currentTime]);
    $scheduledTasks = $stmt->fetchAll();

    // If nothing is scheduled right now (or if it already ran this minute), politely exit
    if (empty($scheduledTasks)) {
        echo json_encode(["message" => "Cron run successful: No tasks scheduled or already executed for " . $currentTime]);
        exit; 
    }

    $triggeredCount = 0;

    // 3. Loop through matching tasks and start the execution process
    foreach ($scheduledTasks as $task) {
        $inputId = $task['input_id'];

        // --- CREATE THE DAILY LOG ---
        // Insert a new row into Table 1b (daily_schedule_trigger)
        $triggerStmt = $pdo->prepare("INSERT INTO daily_schedule_trigger (input_id, status, hit_time) VALUES (?, 'pending', CURRENT_TIMESTAMP)");
        $triggerStmt->execute([$inputId]);
        
        // Grab the unique ID for this specific run
        $triggerId = $pdo->lastInsertId();

        // --- PREPARE N8N PAYLOAD ---
        // Ultra-lean payload: Just the return ticket (trigger_id) and the scraping instructions
        $n8nPayload = [
            "trigger_id"    => $triggerId,
            "topic"         => $task['topic'],
            "seeds"         => $task['seeds'],
            "location"      => $task['location'],
            "language_code" => $task['language_code']
        ];

        // --- TRIGGER THE N8N WEBHOOK ---
        $n8nWebhookUrls = [
            'http://localhost:5678/webhook-test/keyword-research-free'
        ];

        $success = false;
        foreach ($n8nWebhookUrls as $n8nWebhookUrl) {
            $ch = curl_init($n8nWebhookUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($n8nPayload));
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            curl_setopt($ch, CURLOPT_TIMEOUT, 5); 
            $res = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            if ($httpCode >= 200 && $httpCode < 300) {
                $success = true;
                break;
            }
        }
        
        if (!$success) {
            // If n8n is down or unreachable, log it, and update the trigger status to 'failed'
            error_log("Cron automation failed to trigger n8n for trigger_id {$triggerId}");
            
            $failStmt = $pdo->prepare("UPDATE daily_schedule_trigger SET status = 'failed' WHERE trigger_id = ?");
            $failStmt->execute([$triggerId]);
        } else {
            $triggeredCount++;
        }
    }

    // Return a success message for the cron service to log
    echo json_encode([
        "message" => "Cron run successful.",
        "tasks_triggered" => $triggeredCount,
        "time" => $currentTime
    ]);

} catch (PDOException $e) {
    error_log("Database error in cron_scheduler.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(["error" => "Internal Server Error during cron execution."]);
}
?>