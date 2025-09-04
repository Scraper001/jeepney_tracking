<?php
session_start();
require_once('connection/connection.php');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_type = $_SESSION['user_type'] ?? 'user';

// Route user to appropriate dashboard based on user type
switch ($user_type) {
    case 'driver':
        header("Location: driver_dashboard.php");
        exit();
    case 'admin':
        header("Location: admin_dashboard.php");
        exit();
    case 'user':
    default:
        header("Location: commuter_dashboard.php");
        exit();
}
?>