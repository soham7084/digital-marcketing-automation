<?php

function startSession(){
    if(session_status() == PHP_SESSION_NONE){
        session_start();
        $_SESSION['last_activity'] = time();
    }
}

//check if user is logged in
function isLoggedIn(){
    startSession();
    return (isset($_SESSION['user_id']) || isset($_SESSION['id'])) && isset($_SESSION['email']);
}

//Get current user data
function getCurrentUser(){
    if(!isLoggedIn()){
        return null;
    }
    return [
        'id' => $_SESSION['user_id'] ?? $_SESSION['id'] ?? null,
        'email' => $_SESSION['email'] ?? '',
        'name' => $_SESSION['name'] ?? '',
        'role' => $_SESSION['role'] ?? 'user',
        'phone' => $_SESSION['phone_no'] ?? $_SESSION['phone'] ?? ''
    ];
}

//Require authetication for protected routes
function requireAuth($redirectUrl = 'login_front.php')
{
    if (!isLoggedIn()) {
        header('Location: ' . $redirectUrl);
        exit();
    }
}

//Set session data after successful login
function setSession($userData){
    startSession();

    //Regenerate session ID to prevent session fixation attacks
    session_regenerate_id(true);
    
    $_SESSION['id'] = $userData['id'];
    $_SESSION['email'] = $userData['email'];
    $_SESSION['name'] = $userData['name'];
    $_SESSION['role'] = $userData['role'];
    $_SESSION['bio'] = $userData['bio'];
    $_SESSION['phone'] = $userData['phone'];
}

//Logout user and destroy session
function destroySession(){
    startSession();
    // Unset all session variables
    $_SESSION = [];

    // Destroy the session
    if(isset($_COOKIE[session_name()])){
        setcookie(session_name(), '', time() - 3600, '/');
    }

    session_destroy();
}

//Generate a random token for CSRF protection or API authentication
function generateCSRFToken(){
    startSession();
    if(!isset($_SESSION['csrf_token'])){
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); //bin2hex converts binary data into hexadecimal representation
    }
    return $_SESSION['csrf_token'];
}

//Validate CSRF token
function validateCSRFToken($token){
    startSession();
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}
