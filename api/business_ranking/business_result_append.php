<?php
// ==========================================
// 1. Setup and Request Check
// ==========================================
header('Content-Type: application/json; charset=UTF-8');
require_once '../includes/config.php';

// Force timezone so the database time matches your local operating time
date_default_timezone_set('Asia/Kolkata');

// Method Restriction
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["error" => "Method Not Allowed. Only POST requests are accepted."]);
    exit;
}

// ==========================================
// 2. Receiving and Validating Data
// ==========================================
$input_data = json_decode(file_get_contents("php://input"), true);

// Extract and strictly sanitize to prevent NULLs
$trigger_id          = isset($input_data['trigger_id']) ? intval($input_data['trigger_id']) : 0;
$timestamp           = !empty($input_data['timestamp']) ? trim($input_data['timestamp']) : date('Y-m-d\TH:i:sP');
$keyword             = !empty($input_data['keyword']) ? trim($input_data['keyword']) : 'Unknown Keyword';
$cluster             = !empty($input_data['cluster']) ? trim($input_data['cluster']) : 'N/A';
$search_intent       = !empty($input_data['search_intent']) ? trim($input_data['search_intent']) : 'N/A';
$priority            = isset($input_data['priority']) ? intval($input_data['priority']) : 3;
$number_one_location = !empty($input_data['number_one_location']) ? trim($input_data['number_one_location']) : 'Not Found';
$number_one_ranking  = !empty($input_data['number_one_ranking']) ? trim($input_data['number_one_ranking']) : 'Not Found';
$number_one_rating   = !empty($input_data['number_one_rating']) ? trim($input_data['number_one_rating']) : 'No Rating';
$top_3_competitors   = !empty($input_data['top_3_competitors']) ? trim($input_data['top_3_competitors']) : 'No Competitors Found';
$status              = !empty($input_data['status']) ? trim($input_data['status']) : 'Not in Top Results';

if ($trigger_id <= 0) {
    http_response_code(400);
    echo json_encode(["error" => "Invalid or missing trigger_id."]);
    exit;
}

try {
    // ==========================================
    // 3. Database Insertion (Result Table)
    // ==========================================
    $sql = "INSERT INTO business_account_ranking_result 
            (trigger_id, timestamp, keyword, cluster, search_intent, priority, number_one_location, number_one_ranking, number_one_rating, top_3_competitors, status) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
    $stmt = $pdo->prepare($sql);
    
    $stmt->execute([
        $trigger_id, 
        $timestamp, 
        $keyword, 
        $cluster, 
        $search_intent, 
        $priority, 
        $number_one_location, 
        $number_one_ranking, 
        $number_one_rating, 
        $top_3_competitors, 
        $status
    ]);

    $new_result_id = $pdo->lastInsertId();

    // ==========================================
    // 4. Update Trigger Status (Schedule Table)
    // ==========================================
    // This marks the job as done and updates the completion time to the exact moment this row was saved.
    $update_sql = "UPDATE daily_schedule_trigger_ba SET status = 'done', completion_time = NOW() WHERE trigger_id = ?";
    $update_stmt = $pdo->prepare($update_sql);
    $update_stmt->execute([$trigger_id]);


    // ==========================================
    // 5. Final Response
    // ==========================================
    http_response_code(201);
    echo json_encode([
        "success" => true,
        "message" => "Result saved and trigger status updated successfully.",
        "result_id" => $new_result_id
    ]);

} catch (PDOException $e) {
    error_log("DB Error saving ranking result: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(["error" => "Internal Server Error while saving result."]);
}
?>