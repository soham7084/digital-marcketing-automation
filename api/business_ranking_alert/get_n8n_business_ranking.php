<?php
// ==========================================
// 1. Setup and Request Check
// ==========================================
header('Content-Type: application/json; charset=UTF-8');
require_once '../includes/config.php'; // Ensure this points to your DB connection

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["error" => "Method Not Allowed. Only POST requests are accepted."]);
    exit;
}

$input_data = json_decode(file_get_contents("php://input"), true);
$user_id = isset($input_data['user_id']) ? intval($input_data['user_id']) : 0;

if ($user_id <= 0) {
    http_response_code(400);
    echo json_encode(["error" => "Invalid or missing user_id."]);
    exit;
}

try {
    // ==========================================
    // 2. Database Operations (Three-Table Join)
    // ==========================================
    // Joins Inputs -> Triggers -> Results to filter by user_id
    $sql = "SELECT 
                res.timestamp, 
                res.keyword, 
                res.cluster, 
                res.search_intent, 
                res.priority, 
                res.number_one_location, 
                res.number_one_ranking, 
                res.number_one_rating, 
                res.top_3_competitors, 
                res.status
            FROM business_account_ranking_result res
            INNER JOIN daily_schedule_trigger_ba dst ON res.trigger_id = dst.trigger_id
            INNER JOIN business_account_ranking_inputs bai ON dst.ba_id = bai.ba_id
            WHERE bai.user_id = ?
            ORDER BY res.result_id ASC";
            
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$user_id]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!$results) {
        http_response_code(404);
        echo json_encode(["error" => "No ranking results found for this user."]);
        exit;
    }

    // ==========================================
    // 3. Formatting Output for n8n
    // ==========================================
    $formatted_data = [];
    $row_num = 2; 

    foreach ($results as $row) {
        $formatted_data[] = [
            "row_number"          => $row_num++,
            "timestamp"           => $row['timestamp'],
            "keyword"             => $row['keyword'],
            "cluster"             => $row['cluster'],
            "search_intent"       => $row['search_intent'],
            "priority"            => intval($row['priority']),
            "number_one_location" => $row['number_one_location'],
            "number_one_ranking"  => $row['number_one_ranking'],
            "number_one_rating"   => $row['number_one_rating'],
            "top_3_competitors"   => $row['top_3_competitors'],
            "status"              => $row['status']
        ];
    }

    // ==========================================
    // 4. Final Response
    // ==========================================
    http_response_code(200);
    echo json_encode($formatted_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
    error_log("Database error in get_n8n_business_ranking.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(["error" => "Internal Server Error."]);
}
?>