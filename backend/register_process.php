<?php
session_start();
require_once('../connection/connection.php');

// Check if it's an AJAX request
$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $firstname = trim($_POST['firstname']);
    $lastname = trim($_POST['lastname']);
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $confirm = $_POST['confirm'];
    $address = $_POST['address'];

    // Get individual address components if available
    $region = isset($_POST['region']) ? trim($_POST['region']) : '';
    $province_code = isset($_POST['province_code']) ? trim($_POST['province_code']) : '';
    $municipality_code = isset($_POST['municipality_code']) ? trim($_POST['municipality_code']) : '';
    $barangay_code = isset($_POST['barangay_code']) ? trim($_POST['barangay_code']) : '';
    $street = isset($_POST['street']) ? trim($_POST['street']) : '';

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
    } elseif (!preg_match('/^[A-Za-z0-9_]{3,}$/', $username)) {
        $errors[] = "Username must be at least 3 characters and can only contain letters, numbers, and underscores";
    }

    if (empty($password)) {
        $errors[] = "Password is required";
    } elseif (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters";
    }

    if ($password !== $confirm) {
        $errors[] = "Passwords do not match";
    }

    if (empty($address)) {
        $errors[] = "Address is required";
    }

    // If there are validation errors
    if (!empty($errors)) {
        $errorMessage = implode(". ", $errors);
        if ($isAjax) {
            echo json_encode(['status' => 'error', 'message' => $errorMessage]);
            exit();
        } else {
            $_SESSION['register_error'] = $errorMessage;
            header("Location: ../register.php");
            exit();
        }
    }

    // Connect to database
    $conn = con();

    // Check for database connection errors
    if (!$conn) {
        if ($isAjax) {
            echo json_encode(['status' => 'error', 'message' => 'Database connection failed']);
            exit();
        } else {
            $_SESSION['register_error'] = "Database connection failed";
            header("Location: ../register.php");
            exit();
        }
    }

    // Check if username already exists
    $check_query = "SELECT * FROM user_tbl WHERE username = ?";
    $stmt = mysqli_prepare($conn, $check_query);

    if (!$stmt) {
        if ($isAjax) {
            echo json_encode(['status' => 'error', 'message' => 'Database error: ' . mysqli_error($conn)]);
            exit();
        } else {
            $_SESSION['register_error'] = "Database error: " . mysqli_error($conn);
            header("Location: ../register.php");
            exit();
        }
    }

    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) > 0) {
        if ($isAjax) {
            echo json_encode(['status' => 'error', 'message' => 'Username already exists']);
            exit();
        } else {
            $_SESSION['register_error'] = "Username already exists";
            header("Location: ../register.php");
            exit();
        }
    }

    mysqli_stmt_close($stmt);

    // Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $date_created = time();
    $user_type = "user";

    // Insert new user
    $insert_query = "INSERT INTO user_tbl (firstname, lastname, address, password, username, date_created, usert_type, 
                    region_code, province_code, municipality_code, barangay_code, street) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = mysqli_prepare($conn, $insert_query);

    if (!$stmt) {
        if ($isAjax) {
            echo json_encode(['status' => 'error', 'message' => 'Database error: ' . mysqli_error($conn)]);
            exit();
        } else {
            $_SESSION['register_error'] = "Database error: " . mysqli_error($conn);
            header("Location: ../register.php");
            exit();
        }
    }

    mysqli_stmt_bind_param(
        $stmt,
        "ssssssssssss",
        $firstname,
        $lastname,
        $address,
        $hashed_password,
        $username,
        $date_created,
        $user_type,
        $region,
        $province_code,
        $municipality_code,
        $barangay_code,
        $street
    );

    if (mysqli_stmt_execute($stmt)) {
        if ($isAjax) {
            echo json_encode(['status' => 'success', 'message' => 'Registration successful!']);
            exit();
        } else {
            $_SESSION['register_success'] = "Registration successful! You can now login.";
            header("Location: ../login.php");
            exit();
        }
    } else {
        $error = mysqli_stmt_error($stmt);
        if ($isAjax) {
            echo json_encode(['status' => 'error', 'message' => 'Registration failed: ' . $error]);
            exit();
        } else {
            $_SESSION['register_error'] = "Registration failed: " . $error;
            header("Location: ../register.php");
            exit();
        }
    }

    mysqli_stmt_close($stmt);
    mysqli_close($conn);
} else {
    // If not a POST request
    if ($isAjax) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
        exit();
    } else {
        header("Location: ../register.php");
        exit();
    }
}
?>