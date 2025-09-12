<?php
/**
 * Registration Backend Processing
 * Handles user registration with complete validation and secure password hashing
 * 
 * @author Jeepney Tracking System
 * @version 1.0
 */

session_start();
require_once('../connection/connection.php');

// Check if it's an AJAX request
$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

/**
 * Send JSON response for AJAX requests or redirect for regular form submissions
 */
function sendResponse($status, $message, $isAjax) {
    if ($isAjax) {
        echo json_encode(['status' => $status, 'message' => $message]);
        exit();
    } else {
        $_SESSION['register_' . $status] = $message;
        header("Location: ../register.php");
        exit();
    }
}

/**
 * Validate email format
 */
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Validate Philippine phone number format
 */
function validatePhoneNumber($phone) {
    // Remove spaces and allow +63 or 0 prefix followed by 10 digits
    $cleanPhone = preg_replace('/\s/', '', $phone);
    return preg_match('/^(\+63|0)\d{10}$/', $cleanPhone);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get and sanitize form data
    $firstname = trim($_POST['firstname'] ?? '');
    $lastname = trim($_POST['lastname'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm'] ?? '';
    $terms = isset($_POST['terms']);
    
    // Get location data
    $region = $_POST['region'] ?? '';
    $province_code = $_POST['province_code'] ?? '';
    $municipality_code = $_POST['municipality_code'] ?? '';
    $barangay_code = $_POST['barangay_code'] ?? '';
    $street = trim($_POST['street'] ?? '');
    
    // Create full address from components
    $address = $_POST['address'] ?? '';

    // Initialize validation errors array
    $errors = [];

    // Validate required fields
    if (empty($firstname)) {
        $errors[] = "First name is required";
    } elseif (strlen($firstname) < 2) {
        $errors[] = "First name must be at least 2 characters";
    }

    if (empty($lastname)) {
        $errors[] = "Last name is required";
    } elseif (strlen($lastname) < 2) {
        $errors[] = "Last name must be at least 2 characters";
    }

    if (empty($username)) {
        $errors[] = "Username is required";
    } elseif (!preg_match('/^[A-Za-z0-9_]{3,}$/', $username)) {
        $errors[] = "Username must be at least 3 characters and can only contain letters, numbers, and underscores";
    }

    // Validate email
    if (empty($email)) {
        $errors[] = "Email address is required";
    } elseif (!validateEmail($email)) {
        $errors[] = "Please enter a valid email address";
    }

    // Validate phone number
    if (empty($phone)) {
        $errors[] = "Phone number is required";
    } elseif (!validatePhoneNumber($phone)) {
        $errors[] = "Please enter a valid Philippine phone number (e.g., 09123456789)";
    }

    // Validate location fields
    if (empty($region)) {
        $errors[] = "Region is required";
    }
    if (empty($province_code)) {
        $errors[] = "Province is required";
    }
    if (empty($municipality_code)) {
        $errors[] = "Municipality is required";
    }
    if (empty($street)) {
        $errors[] = "Street address is required";
    }

    // Validate password
    if (empty($password)) {
        $errors[] = "Password is required";
    } elseif (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters";
    }

    // Validate password confirmation
    if ($password !== $confirm) {
        $errors[] = "Passwords do not match";
    }

    // Validate terms acceptance
    if (!$terms) {
        $errors[] = "You must accept the terms and conditions";
    }

    // If there are validation errors, return them
    if (!empty($errors)) {
        $errorMessage = implode(". ", $errors);
        sendResponse('error', $errorMessage, $isAjax);
    }

    // Connect to database
    try {
        $conn = con();
        if (!$conn) {
            throw new Exception("Database connection failed");
        }
    } catch (Exception $e) {
        sendResponse('error', 'Database connection failed. Please try again later.', $isAjax);
    }

    // Check if email already exists
    $check_email_query = "SELECT id FROM users WHERE email = ?";
    $stmt = mysqli_prepare($conn, $check_email_query);
    
    if (!$stmt) {
        sendResponse('error', 'Database error. Please try again later.', $isAjax);
    }

    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) > 0) {
        mysqli_stmt_close($stmt);
        sendResponse('error', 'Email address is already registered. Please use a different email.', $isAjax);
    }

    mysqli_stmt_close($stmt);

    // Check if phone number already exists
    $check_phone_query = "SELECT id FROM users WHERE phone = ?";
    $stmt = mysqli_prepare($conn, $check_phone_query);
    
    if (!$stmt) {
        sendResponse('error', 'Database error. Please try again later.', $isAjax);
    }

    mysqli_stmt_bind_param($stmt, "s", $phone);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) > 0) {
        mysqli_stmt_close($stmt);
        sendResponse('error', 'Phone number is already registered. Please use a different phone number.', $isAjax);
    }

    mysqli_stmt_close($stmt);

    // Hash password securely
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Get region, province, city, and barangay names from API or form data
    $region_name = '';
    $province_name = '';
    $city_name = '';
    $barangay_name = '';
    
    // Extract names from the full address or use the form data
    if (!empty($address)) {
        $address_parts = explode(', ', $address);
        if (count($address_parts) >= 4) {
            $barangay_name = $address_parts[count($address_parts) - 4] ?? '';
            $city_name = $address_parts[count($address_parts) - 3] ?? '';
            $province_name = $address_parts[count($address_parts) - 2] ?? '';
            $region_name = $address_parts[count($address_parts) - 1] ?? '';
        }
    }

    // Insert new user into database
    $insert_query = "INSERT INTO users (fname, lname, region, province, city, barangay, phone, email, password) 
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = mysqli_prepare($conn, $insert_query);

    if (!$stmt) {
        sendResponse('error', 'Database error. Please try again later.', $isAjax);
    }

    mysqli_stmt_bind_param(
        $stmt,
        "sssssssss",
        $firstname,
        $lastname,
        $region_name,
        $province_name,
        $city_name,
        $barangay_name,
        $phone,
        $email,
        $hashed_password
    );

    if (mysqli_stmt_execute($stmt)) {
        mysqli_stmt_close($stmt);
        mysqli_close($conn);
        
        // Registration successful
        if ($isAjax) {
            echo json_encode([
                'status' => 'success', 
                'message' => 'Registration successful! Your account has been created.'
            ]);
            exit();
        } else {
            $_SESSION['register_success'] = "Registration successful! You can now login with your credentials.";
            header("Location: ../login.php");
            exit();
        }
    } else {
        $error = mysqli_stmt_error($stmt);
        mysqli_stmt_close($stmt);
        mysqli_close($conn);
        
        // Check if it's a duplicate entry error
        if (strpos($error, 'Duplicate entry') !== false) {
            if (strpos($error, 'email') !== false) {
                sendResponse('error', 'Email address is already registered.', $isAjax);
            } else {
                sendResponse('error', 'An account with this information already exists.', $isAjax);
            }
        } else {
            sendResponse('error', 'Registration failed. Please try again later.', $isAjax);
        }
    }
} else {
    // If not a POST request
    sendResponse('error', 'Invalid request method', $isAjax);
}
?>