<?php
header('Content-Type: application/json');
require_once '../includes/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["error" => "Method Not Allowed. Only POST requests are accepted."]);
    exit;
}

$rawInput = file_get_contents('php://input');
$data = json_decode($rawInput, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    echo json_encode(["error" => "Invalid JSON payload."]);
    exit;
}

$requiredFields = ['user_id', 'topic', 'seeds', 'location', 'language_code'];
foreach ($requiredFields as $field) {
    if (!isset($data[$field])) {
        http_response_code(400);
        echo json_encode(["error" => "Missing required field: {$field}"]);
        exit;
    }
}

// Sanitization
$userId = intval($data['user_id']);
$topic = trim(htmlspecialchars($data['topic']));
$seeds = trim(htmlspecialchars($data['seeds']));
$countryCode = trim(htmlspecialchars($data['location']));
$languageCode = trim(htmlspecialchars($data['language_code']));
$runTime = isset($data['run_time']) ? trim(htmlspecialchars($data['run_time'])) : null;

if ($userId <= 0 || empty($topic) || empty($seeds)) {
    http_response_code(400);
    echo json_encode(["error" => "Invalid data provided. Check user_id, topic, and seeds."]);
    exit;
}

try {
    // --- USER VERIFICATION BLOCK ---
    // Check if the user_id exists in the 'users' table
    $userCheckStmt = $pdo->prepare("SELECT id FROM users WHERE id = ?");
    $userCheckStmt->execute([$userId]);
    
    if (!$userCheckStmt->fetch()) {
        http_response_code(404);
        echo json_encode(["error" => "User verification failed: User ID does not exist."]);
        exit;
    }
    // -------------------------------

    // --- CONFIGURATION UPSERT BLOCK ---
    // Check if a configuration already exists for this user_id in 'keyword_inputs'
    $configCheckStmt = $pdo->prepare("SELECT input_id FROM keyword_inputs WHERE user_id = ?");
    $configCheckStmt->execute([$userId]);
    $existingConfig = $configCheckStmt->fetch(PDO::FETCH_ASSOC);

    if ($existingConfig) {
        // UPDATE existing configuration using user_id
        $inputId = $existingConfig['input_id']; // Retain the existing input_id for the response
        $stmt = $pdo->prepare("UPDATE keyword_inputs SET topic = ?, seeds = ?, location = ?, language_code = ?, run_time = ? WHERE user_id = ?");
        $stmt->execute([$topic, $seeds, $countryCode, $languageCode, $runTime, $userId]);
        $message = "Configuration updated successfully.";
    } else {
        // INSERT new configuration
        $stmt = $pdo->prepare("INSERT INTO keyword_inputs (user_id, topic, seeds, location, language_code, run_time) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$userId, $topic, $seeds, $countryCode, $languageCode, $runTime]);
        $inputId = $pdo->lastInsertId();
        $message = "Configuration saved successfully.";
    }

    http_response_code(200);
    echo json_encode([
        "success" => true,
        "message" => $message,
        "input_id" => $inputId
    ]);

} catch (PDOException $e) {
    error_log("Database error in save_input_config.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(["error" => "Internal Server Error while saving configuration."]);
}
?>