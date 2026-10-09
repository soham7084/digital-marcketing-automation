<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../includes/config.php';
require_once '../includes/session.php';

// Controller -> Handle Request Method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Method Not Allowed'
    ]);
    exit();
}

// Controller -> Receive Request Data
$input = json_decode(
    file_get_contents('php://input'),
    true
);

// Input Validation
if (
    empty($input['email']) ||
    empty($input['password'])
) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Email and Password are required'
    ]);
    exit();
}

// Sanitize Input
$email = trim($input['email']);
$password = trim($input['password']);

// Rules Validation
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid email format'
    ]);
    exit();
}

try {

    // Check User
    $stmt = $pdo->prepare(
        "SELECT * FROM users WHERE email = ?"
    );

    $stmt->execute([$email]);

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Invalid email or password'
        ]);
        exit();
    }

    // Verify Password
    if (!password_verify($password, $user['password'])) {

        http_response_code(401);
        echo json_encode([
            'success' => false,
            'message' => 'Invalid email or password'
        ]);
        exit();
    }

    // Create Session
    session_start();

    $_SESSION['user_id'] = $user['id'];
    $_SESSION['name'] = $user['name'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['phone_no'] = $user['phone_no'];
    $_SESSION['role'] = $user['role'];

    // Success Response
    echo json_encode([
        'success' => true,
        'message' => 'Signin Successful',
        'user' => [
            'id' => $user['id'],
            'name' => $user['name'] ?? '',
            'email' => $user['email'] ?? '',
            'phone_no' => $user['phone_no'] ?? '',
            'role' => $user['role'] ?? ''
        ]
    ]);

} catch (PDOException $e) {

    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}