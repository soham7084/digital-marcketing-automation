<?php

ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting(E_ALL);

session_start();

require_once __DIR__ . "/../api/includes/config.php";

/* =====================================
   CHECK LOGIN
====================================== */

if (!isset($_SESSION['user_id'])) {

    header("Location: login_front.php");
    exit();

}

/* =====================================
   USER DATA
====================================== */

$user_id = $_SESSION['user_id'];

$name  = $_SESSION['name']  ?? "User";

$email = $_SESSION['email'] ?? "";

$role  = $_SESSION['role']  ?? "user";

/* =====================================
   USER INITIALS
====================================== */

$initials = "";

$parts = explode(" ",trim($name));

$initials .= strtoupper(substr($parts[0],0,1));

if(count($parts) > 1){

    $initials .= strtoupper(substr(end($parts),0,1));

}