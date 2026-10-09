<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

require_once '../includes/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(["success" => false, "error" => "Method Not Allowed. Use GET."]);
    exit;
}

$triggerId = isset($_GET['trigger_id']) ? intval($_GET['trigger_id']) : 0;

if ($triggerId <= 0) {
    http_response_code(400);
    echo json_encode(["success" => false, "error" => "Invalid or missing trigger_id."]);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT trigger_id, status, hit_time, completion_time FROM daily_schedule_trigger_seo WHERE trigger_id = ?");
    $stmt->execute([$triggerId]);
    $trigger = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$trigger) {
        http_response_code(404);
        echo json_encode(["success" => false, "error" => "Trigger ID not found."]);
        exit;
    }

    $status = $trigger['status'];
    $isDone = ($status === 'done');

    http_response_code(200);
    echo json_encode([
        "success" => true,
        "trigger_id" => $triggerId,
        "status" => $status,
        "is_done" => $isDone,
        "hit_time" => $trigger['hit_time'],
        "completion_time" => $trigger['completion_time']
    ]);

} catch (PDOException $e) {
    error_log("DB Error in check_status.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(["success" => false, "error" => "Internal Server Error: " . $e->getMessage()]);
}
?>
