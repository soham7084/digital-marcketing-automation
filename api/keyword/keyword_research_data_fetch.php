<?php
header('Content-Type: application/json');
require_once '../includes/config.php';

// 1. Ensure it is a GET request
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(["error" => "Method Not Allowed. Use GET."]);
    exit;
}

// 2. Check if 'user_id' is provided
if (!isset($_GET['user_id'])) {
    http_response_code(400);
    echo json_encode(["error" => "Missing required parameter: 'user_id'."]);
    exit;
}

$userId = intval($_GET['user_id']);
$requestedDate = isset($_GET['date']) ? trim($_GET['date']) : 'all'; // Default to 'all' if not set

// 3. Validate the User ID
if ($userId <= 0) {
    http_response_code(400);
    echo json_encode(["error" => "Invalid user_id."]);
    exit;
}

// 4. Validate the Date format (Only if a specific date is requested)
if ($requestedDate !== 'all') {
    $dateObject = DateTime::createFromFormat('Y-m-d', $requestedDate);
    if (!$dateObject || $dateObject->format('Y-m-d') !== $requestedDate) {
        http_response_code(400);
        echo json_encode(["error" => "Invalid date format. Please use YYYY-MM-DD (e.g., 2026-07-05)."]);
        exit;
    }
}

try {
    // 5. Build dynamic query based on if we want "All" or a "Specific Date"
    if ($requestedDate === 'all') {
        $sql = "
            SELECT kr.keyword, kr.cluster, kr.search_intent, kr.priority, kr.get_time 
            FROM keyword_result kr
            INNER JOIN daily_schedule_trigger dst ON kr.trigger_id = dst.trigger_id
            INNER JOIN keyword_inputs ki ON dst.input_id = ki.input_id
            WHERE ki.user_id = ?
            ORDER BY kr.get_time DESC
        ";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$userId]);
    } else {
        $sql = "
            SELECT kr.keyword, kr.cluster, kr.search_intent, kr.priority, kr.get_time 
            FROM keyword_result kr
            INNER JOIN daily_schedule_trigger dst ON kr.trigger_id = dst.trigger_id
            INNER JOIN keyword_inputs ki ON dst.input_id = ki.input_id
            WHERE ki.user_id = ? AND DATE(kr.get_time) = ?
            ORDER BY kr.get_time DESC
        ";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$userId, $requestedDate]);
    }
    
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 6. Return the data (always return 200 with success state)
    http_response_code(200);
    echo json_encode([
        "success" => true,
        "message" => empty($results) ? ($requestedDate === 'all' ? "No results found for this user." : "No results found for date: {$requestedDate}") : "Results fetched successfully.",
        "total_results" => count($results),
        "data" => $results
    ]);

} catch (PDOException $e) {
    error_log("Database error in keyword_research_data_fetch.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(["error" => "DB Error: " . $e->getMessage()]);
}
?>