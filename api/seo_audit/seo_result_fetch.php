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

$user_id     = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;
$filter_date = !empty($_GET['date']) && $_GET['date'] !== 'all' ? trim($_GET['date']) : (!empty($_GET['filter_date']) ? trim($_GET['filter_date']) : null);

if ($user_id <= 0) {
    http_response_code(400);
    echo json_encode(["success" => false, "error" => "Invalid or missing user_id."]);
    exit;
}

try {
    $sql = "SELECT 
                r.result_id,
                r.trigger_id,
                r.page_url,
                r.performance_score,
                r.seo_score,
                r.accessibility_score,
                r.best_practices_score,
                r.total_issues,
                r.critical_issues,
                r.audit_summary,
                r.detailed_results,
                r.get_time
            FROM seo_audit_results r
            INNER JOIN daily_schedule_trigger_seo d ON r.trigger_id = d.trigger_id
            INNER JOIN seo_audit_inputs i ON d.audit_input_id = i.audit_input_id
            WHERE i.user_id = :user_id";

    $params = [':user_id' => $user_id];

    if ($filter_date) {
        $sql .= " AND (DATE(r.get_time) = :filter_date OR LEFT(r.get_time, 10) = :filter_date)";
        $params[':filter_date'] = $filter_date;
    }

    $sql .= " ORDER BY r.result_id DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Decode JSON detailed_results string for each row
    foreach ($results as &$row) {
        if (!empty($row['detailed_results'])) {
            $decoded = json_decode($row['detailed_results'], true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $row['detailed_results'] = $decoded;
            }
        }
    }

    http_response_code(200);
    echo json_encode([
        "success" => true,
        "message" => empty($results) ? "No SEO audit results found." : "SEO audit results fetched successfully.",
        "total_results" => count($results),
        "data" => $results
    ]);

} catch (PDOException $e) {
    error_log("DB Error in seo_result_fetch.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(["success" => false, "error" => "Internal Server Error: " . $e->getMessage()]);
}
?>
