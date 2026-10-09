<?php

// ============================
// CONFIGURATION
// ============================
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../includes/config.php';
require_once '../includes/session.php';


// ============================
// CONTROLLER
// Handle Request Method
// ============================
if($_SERVER['REQUEST_METHOD'] !== 'POST'){
    http_response_code(405);

    echo json_encode([
        'success' => false,
        'message' => 'Method Not Allowed'
    ]);

    exit();
}


// ============================
// CONTROLLER
// Receive Request Data
// ============================
$input = json_decode(
    file_get_contents('php://input'),
    true
);


// ============================
// CONTROLLER
// Input Validation
// ============================
if(
    empty($input['email']) ||
    empty($input['password'])
){
    http_response_code(400);

    echo json_encode([
        'success' => false,
        'message' => 'Email and password are required'
    ]);

    exit();
}


// ============================
// CONTROLLER
// Sanitize Input
// ============================
$email = trim($input['email']);
$password = trim($input['password']);


// ============================
// CONTROLLER
// Business Rules Validation
// ============================
if(
    !filter_var(
        $email,
        FILTER_VALIDATE_EMAIL
    )
){
    http_response_code(400);

    echo json_encode([
        'success' => false,
        'message' => 'Invalid email format'
    ]);

    exit();
}

try {

    // ============================
    // MODEL
    // Fetch User By Email
    // ============================
    $stmt = $pdo->prepare(
        "SELECT * FROM users WHERE Email = ?"
    );

    $stmt->execute([$email]);

    $user = $stmt->fetch(
        PDO::FETCH_ASSOC
    ); // Fetch the user record based on email


    // ============================
    // CONTROLLER
    // Business Logic
    // Check User Exists
    // ============================
    if (!$user) {

        // ============================
        // VIEW (JSON RESPONSE)
        // ============================
        http_response_code(401);

        echo json_encode([
            'success' => false,
            'message' => 'Invalid email or password'
        ]);

        exit();
    }


    // ============================
    // CONTROLLER
    // Business Logic
    // Verify Password
    // ============================
    if (
        !password_verify(
            $password,
            $user['Password']
        )
    ) {

        // ============================
        // VIEW (JSON RESPONSE)
        // ============================
        http_response_code(401);

        echo json_encode([
            'success' => false,
            'message' => 'Invalid email or password'
        ]);

        exit();
    }


    // ============================
    // VIEW (SUCCESS RESPONSE)
    // ============================
    http_response_code(200);

    echo json_encode([
        'success' => true,
        'message' => 'Signin successful',
        'user' => [
            'id' => $user['User_id'],
            'name' => $user['Name'] ?? '',
            'email' => $user['Email'] ?? '',
            'phone' => $user['Phone_No'] ?? '',
            //'role' => $user['Role'] ?? ''
        ]
    ]);

} catch (PDOException $e) {

    // ============================
    // VIEW (ERROR RESPONSE)
    // ============================
    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}