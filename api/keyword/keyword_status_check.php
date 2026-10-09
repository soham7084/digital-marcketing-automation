<?php
header('Content-Type: application/json');
require_once '../includes/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(["error" => "Method Not Allowed. Use GET."]);
    exit;
}

if (!isset($_GET['trigger_id'])) {
    http_response_code(400);
    echo json_encode(["error" => "Missing required parameter: trigger_id"]);
    exit;
}

$triggerId = intval($_GET['trigger_id']);

if ($triggerId <= 0) {
    http_response_code(400);
    echo json_encode(["error" => "Invalid trigger_id."]);
    exit;
}

try {
    // 1. Check if results exist in the keyword_result table for this trigger
    $checkResultsStmt = $pdo->prepare("SELECT COUNT(*) FROM keyword_result WHERE trigger_id = ?");
    $checkResultsStmt->execute([$triggerId]);
    $resultCount = $checkResultsStmt->fetchColumn();

    $message = "No results found; status unchanged.";

    // 2. If results exist, automatically update the status to 'done' (if not already done)
    if ($resultCount > 0) {
        $updateStmt = $pdo->prepare("UPDATE daily_schedule_trigger SET status = 'done', completion_time = COALESCE(completion_time, CURRENT_TIMESTAMP) WHERE trigger_id = ? AND status != 'done'");
        $updateStmt->execute([$triggerId]);
        
        // Check if a row was actually changed
        if ($updateStmt->rowCount() > 0) {
            $message = "Status successfully updated to 'done'.";
        } else {
            $message = "Status was already marked as 'done'.";
        }
    }

    // 3. Return a lightweight confirmation (fetch logic removed)
    http_response_code(200);
    echo json_encode([
        "success" => true,
        "message" => $message,
        "records_found" => $resultCount
    ]);

} catch (PDOException $e) {
    error_log("Database error in check_status.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(["error" => "Internal Server Error."]);
}
?>