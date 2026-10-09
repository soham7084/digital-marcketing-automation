<?php
// ==========================================
// 1. Setup and Headers (Frontend Ready)
// ==========================================
header("Access-Control-Allow-Origin: *"); // Update with your actual frontend domain in production
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

require_once '../includes/config.php';

$method = $_SERVER['REQUEST_METHOD'];

// ==========================================
// 2. Handle GET Request (Fetch Previous Data)
// ==========================================
if ($method === 'GET') {
    $user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;

    if ($user_id <= 0) {
        http_response_code(400);
        echo json_encode(["error" => "Invalid or missing user_id."]);
        exit;
    }

    try {
        // Fetch the user's saved inputs (Will only be one row now)
        $stmt = $pdo->prepare("SELECT ba_id, priority_min, user_location, business_name, run_time, created_at FROM business_account_ranking_inputs WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $history = $stmt->fetchAll(PDO::FETCH_ASSOC);

        http_response_code(200);
        echo json_encode([
            "success" => true,
            "data" => $history
        ]);
        exit;

    } catch (PDOException $e) {
        error_log("DB Error fetching ranking inputs: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(["error" => "Internal Server Error while fetching history."]);
        exit;
    }
}

// ==========================================
// 3. Handle POST Request (Save or Update Data)
// ==========================================
elseif ($method === 'POST') {

    // Parse JSON body sent by the frontend
    $input_data = json_decode(file_get_contents("php://input"), true);

    // Sanitization & Validation
    $user_id = isset($input_data['user_id']) ? intval($input_data['user_id']) : 0;
    $priority_min = isset($input_data['priority_min']) ? intval($input_data['priority_min']) : 3;
    $user_location = isset($input_data['user_location']) ? trim(htmlspecialchars($input_data['user_location'])) : '';
    $business_name = isset($input_data['business_name']) ? trim(htmlspecialchars($input_data['business_name'])) : '';

    // Configured for your need: allow run_time to be strictly null if the toggle is off
    $run_time = (isset($input_data['run_time']) && $input_data['run_time'] !== null) ? trim(htmlspecialchars($input_data['run_time'])) : null;

    // Require mandatory fields (run_time is no longer required to be filled)
    if ($user_id <= 0 || empty($user_location) || empty($business_name)) {
        http_response_code(400);
        echo json_encode(["error" => "Missing required fields. Please ensure user_id, user_location, and business_name are provided."]);
        exit;
    }

    try {
        // Step A: Check if a record already exists for this user_id
        $check_sql = "SELECT ba_id FROM business_account_ranking_inputs WHERE user_id = ?";
        $check_stmt = $pdo->prepare($check_sql);
        $check_stmt->execute([$user_id]);
        $existing_record = $check_stmt->fetch(PDO::FETCH_ASSOC);

        $ba_id = null;
        $response_message = "";
        $http_status = 200;

        if ($existing_record) {
            // Step B1: Record exists -> UPDATE
            $update_sql = "UPDATE business_account_ranking_inputs 
                           SET priority_min = ?, user_location = ?, business_name = ?, run_time = ?, created_at = CURRENT_TIMESTAMP 
                           WHERE user_id = ?";
            $update_stmt = $pdo->prepare($update_sql);
            $update_stmt->execute([$priority_min, $user_location, $business_name, $run_time, $user_id]);

            $ba_id = $existing_record['ba_id'];
            $response_message = "Configuration updated successfully.";
            $http_status = 200; // 200 OK
        } else {
            // Step B2: Record does not exist -> INSERT
            $insert_sql = "INSERT INTO business_account_ranking_inputs (user_id, priority_min, user_location, business_name, run_time) 
                           VALUES (?, ?, ?, ?, ?)";
            $insert_stmt = $pdo->prepare($insert_sql);
            $insert_stmt->execute([$user_id, $priority_min, $user_location, $business_name, $run_time]);

            $ba_id = $pdo->lastInsertId();
            $response_message = "Configuration saved successfully.";
            $http_status = 201; // 201 Created
        }

        // Step B3: Create trigger entry & fire n8n webhook
        $trigger_stmt = $pdo->prepare("INSERT INTO daily_schedule_trigger_ba (ba_id, status, hit_time) VALUES (?, 'pending', NOW())");
        $trigger_stmt->execute([$ba_id]);
        $trigger_id = $pdo->lastInsertId();

        $n8n_payload = json_encode([
            "user_id"       => (int)$user_id,
            "priority_min"  => (int)$priority_min,
            "user_location" => $user_location,
            "business_name" => $business_name,
            "trigger_id"    => (int)$trigger_id
        ]);

        $n8n_webhook_urls = [
            "http://localhost:5678/webhook/business-ranking"
        ];

        foreach ($n8n_webhook_urls as $n8n_webhook_url) {
            $ch = curl_init($n8n_webhook_url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $n8n_payload);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Content-Length: ' . strlen($n8n_payload)
            ]);
            curl_setopt($ch, CURLOPT_TIMEOUT, 3); 
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            if ($httpCode >= 200 && $httpCode < 300) {
                break;
            }
        }

        // Step C: Fetch the user's updated data to send back to the frontend UI immediately
        $fetch_sql = "SELECT ba_id, priority_min, user_location, business_name, run_time, created_at FROM business_account_ranking_inputs WHERE user_id = ?";
        $fetch_stmt = $pdo->prepare($fetch_sql);
        $fetch_stmt->execute([$user_id]);
        $updated_history = $fetch_stmt->fetchAll(PDO::FETCH_ASSOC);

        // Step D: Send Success Response
        http_response_code($http_status);
        echo json_encode([
            "success" => true,
            "message" => $response_message,
            "latest_id" => $ba_id,
            "history" => $updated_history
        ]);

    } catch (PDOException $e) {
        // We are temporarily sending the EXACT SQL error back to the frontend so you can read it
        http_response_code(500);
        echo json_encode(["error" => "DATABASE CRASH: " . $e->getMessage()]);
    }
} else {
    // Reject anything that isn't a GET or POST
    http_response_code(405);
    echo json_encode(["error" => "Method Not Allowed."]);
}
?>