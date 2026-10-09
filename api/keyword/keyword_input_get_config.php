<?php
header('Content-Type: application/json');
require_once '../includes/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(["error" => "Method Not Allowed. Only GET requests are accepted."]);
    exit;
}

// Get the user_id from the URL string
$userId = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;

if ($userId <= 0) {
    http_response_code(400);
    echo json_encode(["error" => "Invalid or missing user_id."]);
    exit;
}

try {
    // Check if a configuration exists for this user_id in 'keyword_inputs'
    $stmt = $pdo->prepare("SELECT topic, seeds, location, language_code, run_time FROM keyword_inputs WHERE user_id = ? ORDER BY input_id DESC LIMIT 1");
    $stmt->execute([$userId]);
    $config = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($config) {
        http_response_code(200);
        echo json_encode([
            "success" => true,
            "data" => $config
        ]);
    } else {
        // Return 404 cleanly if they just haven't saved anything yet
        http_response_code(404);
        echo json_encode([
            "success" => false, 
            "message" => "No previous configuration found."
        ]);
    }

} catch (PDOException $e) {
    error_log("Database error in get_config.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(["error" => "Internal Server Error while fetching configuration."]);
}
?>