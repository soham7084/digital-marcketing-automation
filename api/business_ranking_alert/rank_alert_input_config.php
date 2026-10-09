<?php
// ==========================================
// 1. Setup and Headers (Frontend Ready)
// ==========================================
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

require_once '../includes/config.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;
    
    if ($user_id <= 0) {
        echo json_encode(["error" => "Invalid ID"]);
        exit;
    }

    $stmt = $pdo->prepare("SELECT run_time FROM rank_alert_input WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode($result ?: ["run_time" => null]);
    exit;
}

elseif ($method === 'POST') {
    // Parse JSON body sent by the frontend
    $input_data = json_decode(file_get_contents("php://input"), true);
    
    // Sanitization & Validation
    $user_id = isset($input_data['user_id']) ? intval($input_data['user_id']) : 0;
    $run_time = (isset($input_data['run_time']) && !empty($input_data['run_time'])) ? trim(htmlspecialchars($input_data['run_time'])) : null;

    if ($user_id <= 0) {
        http_response_code(400);
        echo json_encode(["error" => "Invalid user_id."]);
        exit;
    }

    try {
        // Check if config exists
        $stmt = $pdo->prepare("SELECT input_id FROM rank_alert_input WHERE user_id = ?");
        $stmt->execute([$user_id]);
        
        if ($stmt->fetch()) {
            // Update existing record
            $stmt = $pdo->prepare("UPDATE rank_alert_input SET run_time = ?, lust_input_time = NOW() WHERE user_id = ?");
            $stmt->execute([$run_time, $user_id]);
            $message = "Alert settings updated successfully.";
        } else {
            // Insert new record
            $stmt = $pdo->prepare("INSERT INTO rank_alert_input (user_id, run_time) VALUES (?, ?)");
            $stmt->execute([$user_id, $run_time]);
            $message = "Alert settings saved successfully.";
        }
        
        http_response_code(200);
        echo json_encode(["success" => true, "message" => $message]);

    } catch (PDOException $e) {
        error_log("DB Error in rank_alert_input_config.php: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(["error" => "Internal Server Error: " . $e->getMessage()]);
    }
} else {
    http_response_code(405);
    echo json_encode(["error" => "Method Not Allowed."]);
}
?>