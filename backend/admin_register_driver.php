<?php
// Backend process for admin driver registration
session_start();
require_once('../connection/connection.php');

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    $_SESSION['register_error'] = "Unauthorized access";
    header("Location: ../admin_dashboard.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $firstname = trim($_POST['firstname']);
    $lastname = trim($_POST['lastname']);
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $jeepney_id = isset($_POST['jeepney_id']) ? trim($_POST['jeepney_id']) : '';
    $route = trim($_POST['route']);

    // Validation
    $errors = [];

    if (empty($firstname)) {
        $errors[] = "First name is required";
    }

    if (empty($lastname)) {
        $errors[] = "Last name is required";
    }

    if (empty($username)) {
        $errors[] = "Username is required";
    } elseif (strlen($username) < 3) {
        $errors[] = "Username must be at least 3 characters";
    }

    if (empty($password)) {
        $errors[] = "Password is required";
    } elseif (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters";
    }

    if (empty($route)) {
        $errors[] = "Route is required";
    }

    if (!empty($errors)) {
        $_SESSION['register_error'] = implode(". ", $errors);
        header("Location: ../admin_dashboard.php");
        exit();
    }

    $conn = con();

    // Check if username already exists
    $check_query = "SELECT * FROM user_tbl WHERE username = ?";
    $stmt = mysqli_prepare($conn, $check_query);
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) > 0) {
        $_SESSION['register_error'] = "Username already exists";
        mysqli_stmt_close($stmt);
        mysqli_close($conn);
        header("Location: ../admin_dashboard.php");
        exit();
    }

    mysqli_stmt_close($stmt);

    // Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $date_created = time();

    // Insert new driver
    $insert_query = "INSERT INTO user_tbl (firstname, lastname, username, password, date_created, usert_type, status) 
                    VALUES (?, ?, ?, ?, ?, 'driver', 'active')";

    $stmt = mysqli_prepare($conn, $insert_query);
    mysqli_stmt_bind_param($stmt, "ssssi", $firstname, $lastname, $username, $hashed_password, $date_created);

    if (mysqli_stmt_execute($stmt)) {
        $driver_id = mysqli_insert_id($conn);
        mysqli_stmt_close($stmt);

        // Generate jeepney ID if not provided
        if (empty($jeepney_id)) {
            $jeepney_id = 'JP-' . str_pad($driver_id, 3, '0', STR_PAD_LEFT);
        }

        // Create jeepney entry
        $jeepney_query = "INSERT INTO jeepney_tbl (jeepney_id, driver_id, route, capacity, status) 
                         VALUES (?, ?, ?, 14, 'offline')";
        $stmt2 = mysqli_prepare($conn, $jeepney_query);
        mysqli_stmt_bind_param($stmt2, "sis", $jeepney_id, $driver_id, $route);

        if (mysqli_stmt_execute($stmt2)) {
            // Log the activity
            $admin_id = $_SESSION['user_id'];
            $log_query = "INSERT INTO system_log_tbl (user_id, action, details, ip_address) VALUES (?, ?, ?, ?)";
            $log_stmt = mysqli_prepare($conn, $log_query);
            $ip_address = $_SERVER['REMOTE_ADDR'] ?? '';
            $log_details = "Registered driver: $username with jeepney: $jeepney_id";
            mysqli_stmt_bind_param($log_stmt, "isss", $admin_id, "Driver Registration", $log_details, $ip_address);
            mysqli_stmt_execute($log_stmt);
            mysqli_stmt_close($log_stmt);

            $_SESSION['register_success'] = "Driver registered successfully! Jeepney ID: $jeepney_id";
        } else {
            // Rollback user creation
            mysqli_query($conn, "DELETE FROM user_tbl WHERE id = $driver_id");
            $_SESSION['register_error'] = "Failed to create jeepney entry";
        }

        mysqli_stmt_close($stmt2);
    } else {
        $_SESSION['register_error'] = "Failed to register driver: " . mysqli_error($conn);
        mysqli_stmt_close($stmt);
    }

    mysqli_close($conn);
    header("Location: ../admin_dashboard.php");
    exit();
} else {
    header("Location: ../admin_dashboard.php");
    exit();
}
?>