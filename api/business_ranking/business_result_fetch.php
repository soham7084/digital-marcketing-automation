<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

require_once '../includes/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    exit;
}

$user_id    = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;
$filter_date = !empty($_GET['filter_date']) ? trim($_GET['filter_date']) : null; // Expecting YYYY-MM-DD

if ($user_id <= 0) {
    http_response_code(400);
    echo json_encode(["error" => "Invalid or missing user_id."]);
    exit;
}

try {
    // 1. Explicitly select ONLY the requested columns
    $sql = "SELECT 
                r.timestamp, 
                r.keyword, 
                r.cluster, 
                r.search_intent, 
                r.priority, 
                r.number_one_location, 
                r.number_one_ranking, 
                r.number_one_rating, 
                r.top_3_competitors, 
                r.status
            FROM business_account_ranking_result r
            INNER JOIN daily_schedule_trigger_ba d ON r.trigger_id = d.trigger_id
            INNER JOIN business_account_ranking_inputs b ON d.ba_id = b.ba_id
            WHERE b.user_id = :user_id";

    $params = [':user_id' => $user_id];

    // 2. Apply Date Filter
    if ($filter_date) {
        $sql .= " AND (DATE(r.timestamp) = :filter_date OR LEFT(r.timestamp, 10) = :filter_date)";
        $params[':filter_date'] = $filter_date;
    }

    $sql .= " ORDER BY r.result_id DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    http_response_code(200);
    echo json_encode([
        "success" => true,
        "data" => $results
    ]);

} catch (PDOException $e) {
    error_log("DB Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(["error" => "Internal Server Error."]);
}
?>