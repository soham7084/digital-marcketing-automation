<?php
// Configuration

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../includes/config.php';

// Controller -> Handle Request
if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Method Not Allowed'
    ]);
    exit();
}

// Controller -> Receive Request Data
$input = json_decode(file_get_contents('php://input'), true);

// Controller -> Input Validation
$required_fields = ['name', 'email', 'password', 'confirm_password', 'phone_no'];

foreach ($required_fields as $field) {
    if (empty($input[$field])) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => "Field '$field' is required"
        ]);
        exit();
    }
}

// Controller -> Sanitize Data
$name = trim($input['name']);
$email = trim($input['email']);
$password = trim($input['password']);
$confirm_password = trim($input['confirm_password']);
$phone_no = trim($input['phone_no']);

// Validation
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid email format'
    ]);
    exit();
}

if (strlen($password) < 6) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Password must be at least 6 characters long'
    ]);
    exit();
}

if ($password != $confirm_password) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Password and Confirm Password should be the same'
    ]);
    exit();
}

try {

    // Model -> Check Existing Email
    $stmt = $pdo->prepare(
        "SELECT id FROM users WHERE email = ?"
    );

    $stmt->execute([$email]);

    if ($stmt->fetch()) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Email already exists'
        ]);
        exit();
    }

    // Controller -> Hash Password
    $hashed_password = password_hash(
        $password,
        PASSWORD_BCRYPT
    );

    // Insert Into Database
    $stmt = $pdo->prepare(
        "INSERT INTO users
        (
            name,
            email,
            password,
            phone_no
        )
        VALUES
        (
            ?, ?, ?, ?
        )"
    );

    $stmt->execute([
        $name,
        $email,
        $hashed_password,
        $phone_no
    ]);

    // Fetch Generated ID
    $id = $pdo->lastInsertId();

    // View (JSON Response)
    http_response_code(201);
    echo json_encode([
        'success' => true,
        'message' => 'User registered successfully',
        'data' => [
            'id' => $id,
            'name' => $name,
            'email' => $email,
            'phone_no' => $phone_no
        ]
    ]);

} catch (PDOException $e) {

    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database Error: ' . $e->getMessage()
    ]);
}
?>