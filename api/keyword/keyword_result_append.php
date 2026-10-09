<?php
header('Content-Type: application/json');
require_once '../includes/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["error" => "Method Not Allowed. Use POST."]);
    exit;
}

$rawInput = file_get_contents('php://input');
$data = json_decode($rawInput, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    echo json_encode(["error" => "Invalid JSON payload."]);
    exit;
}

// Validate that n8n sent both the trigger_id and the results array
if (!isset($data['trigger_id']) || !isset($data['results']) || !is_array($data['results'])) {
    http_response_code(400);
    echo json_encode(["error" => "Missing trigger_id or invalid results array."]);
    exit;
}

$triggerId = intval($data['trigger_id']);
$results = $data['results'];

if ($triggerId <= 0) {
    http_response_code(400);
    echo json_encode(["error" => "Invalid trigger_id."]);
    exit;
}

try {
    $pdo->beginTransaction();

    // 1. ONLY insert the scraped keywords into the result table, now including get_time
    $insertStmt = $pdo->prepare("INSERT INTO keyword_result (trigger_id, keyword, cluster, search_intent, priority, get_time) VALUES (?, ?, ?, ?, ?, ?)");
    
    foreach ($results as $row) {
        
        // Convert the n8n date format ("05/07/2026, 20:42:25") to MySQL format ("2026-07-05 20:42:25")
        $mysqlTime = date('Y-m-d H:i:s'); // Fallback to current time just in case
        if (!empty($row['get_time'])) {
            $parsedDate = DateTime::createFromFormat('d/m/Y, H:i:s', $row['get_time']);
            if ($parsedDate !== false) {
                $mysqlTime = $parsedDate->format('Y-m-d H:i:s');
            }
        }

        $insertStmt->execute([
            $triggerId,
            trim(htmlspecialchars($row['keyword'] ?? '')),
            trim(htmlspecialchars($row['cluster'] ?? '')),
            trim(htmlspecialchars($row['search_intent'] ?? '')),
            intval($row['priority'] ?? 0),
            $mysqlTime
        ]);
    }

    $pdo->commit();
    
    // 2. Call the Status API to let it handle the status update
    // IMPORTANT: Change this URL to your actual server path!
    $statusApiUrl = "http://localhost/fluanc/api/keyword/keyword_status_check.php?trigger_id=" . $triggerId;
    
    $ch = curl_init($statusApiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5); 
    $statusResponse = curl_exec($ch);
    curl_close($ch);

    http_response_code(201);
    echo json_encode([
        "message" => "Results successfully appended.",
        "status_check_triggered" => true,
        "status_api_response" => json_decode($statusResponse) 
    ]);

} catch (PDOException $e) {
    $pdo->rollBack();
    error_log("Database transaction failed in n8n_callback.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(["error" => "DB Error: " . $e->getMessage()]);
}
?>