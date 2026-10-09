<?php
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once '../includes/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["success" => false, "error" => "Method Not Allowed. Only POST is accepted."]);
    exit;
}

$rawInput = file_get_contents("php://input");
$data = json_decode($rawInput, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    echo json_encode(["success" => false, "error" => "Invalid JSON payload."]);
    exit;
}

$triggerId           = isset($data['trigger_id']) ? intval($data['trigger_id']) : 0;
$pageUrl             = !empty($data['page_url']) ? trim(htmlspecialchars($data['page_url'])) : '';
$performanceScore    = isset($data['performance_score']) ? intval($data['performance_score']) : 85;
$seoScore            = isset($data['seo_score']) ? intval($data['seo_score']) : 90;
$accessibilityScore  = isset($data['accessibility_score']) ? intval($data['accessibility_score']) : 88;
$bestPracticesScore  = isset($data['best_practices_score']) ? intval($data['best_practices_score']) : 89;
$totalIssues         = isset($data['total_issues']) ? intval($data['total_issues']) : 0;
$criticalIssues      = isset($data['critical_issues']) ? intval($data['critical_issues']) : 0;
$auditSummary        = !empty($data['audit_summary']) ? trim(htmlspecialchars($data['audit_summary'])) : '';
$detailedResults     = isset($data['detailed_results']) ? (is_array($data['detailed_results']) ? json_encode($data['detailed_results']) : $data['detailed_results']) : null;

if ($triggerId <= 0) {
    http_response_code(400);
    echo json_encode(["success" => false, "error" => "Invalid or missing trigger_id."]);
    exit;
}

try {
    $sql = "INSERT INTO seo_audit_results 
            (trigger_id, page_url, performance_score, seo_score, accessibility_score, best_practices_score, total_issues, critical_issues, audit_summary, detailed_results, get_time) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $triggerId,
        $pageUrl,
        $performanceScore,
        $seoScore,
        $accessibilityScore,
        $bestPracticesScore,
        $totalIssues,
        $criticalIssues,
        $auditSummary,
        $detailedResults
    ]);

    $newResultId = $pdo->lastInsertId();

    // Mark trigger as done in daily_schedule_trigger_seo
    $updateStmt = $pdo->prepare("UPDATE daily_schedule_trigger_seo SET status = 'done', completion_time = NOW() WHERE trigger_id = ?");
    $updateStmt->execute([$triggerId]);

    http_response_code(201);
    echo json_encode([
        "success" => true,
        "message" => "SEO audit result saved and trigger marked as done.",
        "result_id" => $newResultId
    ]);

} catch (PDOException $e) {
    error_log("DB Error in seo_result_append.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(["success" => false, "error" => "Internal Server Error: " . $e->getMessage()]);
}
?>
