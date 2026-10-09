<?php
// ==========================================
// 1. Setup and Request Check
// ==========================================
header('Content-Type: application/json; charset=UTF-8');
require_once '../includes/config.php';

// Method Restriction - Expecting POST from n8n HTTP Request node
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["error" => "Method Not Allowed. Only POST requests are accepted."]);
    exit;
}

// ==========================================
// 2. Receiving and Validating Data
// ==========================================
// Parse JSON body sent by the n8n HTTP Request node
$input_data = json_decode(file_get_contents("php://input"), true);

// Sanitization & Default Values
$user_id       = isset($input_data['user_id']) ? intval($input_data['user_id']) : 0;
$priority_min  = isset($input_data['priority_min']) ? intval($input_data['priority_min']) : 3;
$user_location = isset($input_data['user_location']) ? htmlspecialchars($input_data['user_location']) : '';
$business_name = isset($input_data['business_name']) ? htmlspecialchars($input_data['business_name']) : '';

if ($user_id <= 0) {
    http_response_code(400);
    echo json_encode(["error" => "Invalid or missing user_id."]);
    exit;
}

try {
    // ==========================================
    // 3. Database Operations
    // ==========================================
    // Linking tables and filtering by BOTH user_id and priority_min
    $sql = "SELECT 
                kr.keyword, 
                kr.cluster, 
                kr.search_intent, 
                kr.priority, 
                kr.get_time 
            FROM keyword_result kr
            INNER JOIN daily_schedule_trigger dst ON kr.trigger_id = dst.trigger_id
            INNER JOIN keyword_inputs ki ON dst.input_id = ki.input_id
            WHERE ki.user_id = ? AND kr.priority >= ?
            ORDER BY kr.result_id ASC";
            
    $stmt = $pdo->prepare($sql);
    
    // Pass both variables into the execution array
    $stmt->execute([$user_id, $priority_min]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!$results) {
        http_response_code(404);
        echo json_encode(["error" => "No keyword results found matching this user and priority level."]);
        exit;
    }

    // ==========================================
    // 4. Formatting Output for n8n Loop
    // ==========================================
    $formatted_data = [];
    $row_num = 2; // Starting at 2 

    foreach ($results as $row) {
        $formatted_data[] = [
            "row_number"    => $row_num++,
            "keyword"       => $row['keyword'],
            "cluster"       => $row['cluster'],
            "search_intent" => $row['search_intent'],
            "priority"      => intval($row['priority']),
            "date"          => date('d/m/Y', strtotime($row['get_time'])),
            
            // Injecting webhook data into every row so n8n retains it during the loop
            "user_location" => $user_location,
            "business_name" => $business_name
        ];
    }

    // ==========================================
    // 5. Final Response
    // ==========================================
    // Send the raw flat array directly back to the n8n HTTP Request node
    http_response_code(200);
    echo json_encode($formatted_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
    // Error Logging
    error_log("Database error in get_n8n_keywords.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(["error" => "Internal Server Error while processing keyword data."]);
}
?>