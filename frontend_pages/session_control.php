<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login_front.php");
    exit();
}

$id = $_SESSION['user_id'];
$name = $_SESSION['name'] ?? 'User';
$email = $_SESSION['email'] ?? '';
$role = $_SESSION['role'] ?? '';
$phone_no = $_SESSION['phone_no'] ?? '';

$meta_connected = true;

$initials = "";
if (!empty($name)) {
    $parts = explode(" ", $name);
    $initials .= strtoupper(substr($parts[0], 0, 1));
    if (count($parts) > 1) {
        $initials .= strtoupper(substr($parts[count($parts) - 1], 0, 1));
    }
} else {
    $initials = "U";
}

?>