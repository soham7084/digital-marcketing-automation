<?php
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once '../includes/config.php';

// Helper function: Ensure 'run_time' column exists in seo_audit_inputs
try {
    $colCheck = $pdo->query("SHOW COLUMNS FROM seo_audit_inputs LIKE 'run_time'");
    if ($colCheck && $colCheck->rowCount() === 0) {
        $pdo->exec("ALTER TABLE seo_audit_inputs ADD COLUMN run_time VARCHAR(10) NULL AFTER email");
    }
} catch (Exception $e) {
    // Ignore if column already exists or table alter fails silently
}

$method = $_SERVER['REQUEST_METHOD'];

// ==========================================
// 1. GET REQUEST (Fetch Saved Configuration)
// ==========================================
if ($method === 'GET') {
    $userId = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;

    if ($userId <= 0) {
        http_response_code(400);
        echo json_encode(["success" => false, "error" => "Invalid or missing user_id."]);
        exit;
    }

    try {
        $stmt = $pdo->prepare("SELECT audit_input_id, user_id, page_url, email, run_time, created_at FROM seo_audit_inputs WHERE user_id = ? ORDER BY audit_input_id DESC LIMIT 1");
        $stmt->execute([$userId]);
        $config = $stmt->fetch(PDO::FETCH_ASSOC);

        http_response_code(200);
        echo json_encode([
            "success" => true,
            "data" => $config ?: null
        ]);
        exit;

    } catch (PDOException $e) {
        error_log("Database error in create_audit.php GET: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(["success" => false, "error" => "Database Error: " . $e->getMessage()]);
        exit;
    }
}

// ==========================================
// 2. POST REQUEST (Save Input & Trigger n8n Flow: seo audit_1)
// ==========================================
elseif ($method === 'POST') {
    $rawInput = file_get_contents('php://input');
    $data = json_decode($rawInput, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        http_response_code(400);
        echo json_encode(["success" => false, "error" => "Invalid JSON payload."]);
        exit;
    }

    // Extract inputs
    $userId  = isset($data['user_id']) ? intval($data['user_id']) : 0;
    $pageUrl = isset($data['page_url']) ? trim(htmlspecialchars($data['page_url'])) : '';
    $email   = isset($data['email']) ? trim(htmlspecialchars($data['email'])) : '';
    $runTime = (isset($data['run_time']) && $data['run_time'] !== null && $data['run_time'] !== '') ? trim(htmlspecialchars($data['run_time'])) : null;

    if ($userId <= 0 || empty($pageUrl) || empty($email)) {
        http_response_code(400);
        echo json_encode(["success" => false, "error" => "Missing required fields: user_id, page_url, or email."]);
        exit;
    }

    try {
        // Step A: Verify user exists in users table
        $userCheckStmt = $pdo->prepare("SELECT id FROM users WHERE id = ?");
        $userCheckStmt->execute([$userId]);
        if (!$userCheckStmt->fetch()) {
            http_response_code(404);
            echo json_encode(["success" => false, "error" => "User verification failed: User ID does not exist."]);
            exit;
        }

        // Step B: Upsert configuration in seo_audit_inputs table
        $checkStmt = $pdo->prepare("SELECT audit_input_id FROM seo_audit_inputs WHERE user_id = ?");
        $checkStmt->execute([$userId]);
        $existing = $checkStmt->fetch(PDO::FETCH_ASSOC);

        if ($existing) {
            $auditInputId = $existing['audit_input_id'];
            $updateStmt = $pdo->prepare("UPDATE seo_audit_inputs SET page_url = ?, email = ?, run_time = ?, created_at = CURRENT_TIMESTAMP WHERE user_id = ?");
            $updateStmt->execute([$pageUrl, $email, $runTime, $userId]);
            $message = "SEO audit configuration updated successfully.";
        } else {
            $insertStmt = $pdo->prepare("INSERT INTO seo_audit_inputs (user_id, page_url, email, run_time) VALUES (?, ?, ?, ?)");
            $insertStmt->execute([$userId, $pageUrl, $email, $runTime]);
            $auditInputId = $pdo->lastInsertId();
            $message = "SEO audit configuration saved successfully.";
        }

        // Step C: Log trigger in daily_schedule_trigger_seo table
        $triggerStmt = $pdo->prepare("INSERT INTO daily_schedule_trigger_seo (audit_input_id, status, hit_time) VALUES (?, 'pending', CURRENT_TIMESTAMP)");
        $triggerStmt->execute([$auditInputId]);
        $triggerId = $pdo->lastInsertId();

        // Step D: Trigger the n8n flow "seo audit_1"
        $n8nPayload = [
            "trigger_id"     => (int)$triggerId,
            "audit_input_id" => (int)$auditInputId,
            "user_id"        => (int)$userId,
            "page_url"       => $pageUrl,
            "email"          => $email,
            "run_time"       => $runTime,
            "flow_name"      => "seo audit_1"
        ];

        // Primary and Fallback n8n Webhook URLs for flow "seo audit_1"
        $n8nWebhookUrls = [
            'http://localhost:5678/webhook/seo-audit-1'
        ];

        $triggered = false;
        foreach ($n8nWebhookUrls as $n8nWebhookUrl) {
            $ch = curl_init($n8nWebhookUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($n8nPayload));
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            curl_setopt($ch, CURLOPT_TIMEOUT, 3);
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode >= 200 && $httpCode < 300) {
                $triggered = true;
                break;
            }
        }

        http_response_code(200);
        echo json_encode([
            "success" => true,
            "message" => $message,
            "audit_input_id" => $auditInputId,
            "trigger_id" => $triggerId,
            "n8n_triggered" => $triggered,
            "flow_name" => "seo audit_1"
        ]);

    } catch (PDOException $e) {
        error_log("Database error in create_audit.php POST: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(["success" => false, "error" => "Database Error: " . $e->getMessage()]);
    }
} else {
    http_response_code(405);
    echo json_encode(["success" => false, "error" => "Method Not Allowed."]);
}
?>
