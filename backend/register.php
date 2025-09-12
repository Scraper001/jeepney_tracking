<?php
/**
 * Registration Backend Script
 * Handles user registration with complete validation and secure password hashing
 * 
 * This script processes user registration requests with the following features:
 * - Complete input validation for all required fields
 * - Email format validation
 * - Philippine phone number format validation  
 * - Secure password hashing using PHP's password_hash()
 * - Duplicate email checking
 * - Prepared statements for SQL injection protection
 * - JSON response format for AJAX requests
 * 
 * @author Jeepney Tracking System
 * @version 1.0
 */

// Start session for error handling
session_start();

// Include database connection
require_once('../connection/connection.php');

// Set content type for JSON responses
header('Content-Type: application/json');

// Function to validate email format
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

// Function to validate Philippine phone number format
function validatePhoneNumber($phone) {
    // Remove all non-digits for validation
    $digits = preg_replace('/\D/', '', $phone);
    // Check for Philippine mobile formats: 09xxxxxxxxx or +639xxxxxxxxx
    return preg_match('/^(09\d{9}|639\d{9})$/', $digits);
}

// Function to send JSON response
function sendResponse($status, $message) {
    echo json_encode(['status' => $status, 'message' => $message]);
    exit();
}

// Check if request method is POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    sendResponse('error', 'Invalid request method');
}

try {
    // Get and sanitize form data
    $fname = trim($_POST['fname'] ?? '');
    $mname = trim($_POST['mname'] ?? ''); // Optional field
    $lname = trim($_POST['lname'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $region = trim($_POST['region'] ?? '');
    $province = trim($_POST['province'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $barangay = trim($_POST['barangay'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm'] ?? '';
    
    // Validation array to collect all errors
    $errors = [];
    
    // Validate required fields - ensure no fields are empty
    if (empty($fname)) {
        $errors[] = "First name is required";
    } elseif (strlen($fname) < 2) {
        $errors[] = "First name must be at least 2 characters";
    }
    
    if (empty($lname)) {
        $errors[] = "Last name is required";
    } elseif (strlen($lname) < 2) {
        $errors[] = "Last name must be at least 2 characters";
    }
    
    // Validate email format
    if (empty($email)) {
        $errors[] = "Email address is required";
    } elseif (!validateEmail($email)) {
        $errors[] = "Please enter a valid email address";
    }
    
    // Validate phone format
    if (empty($phone)) {
        $errors[] = "Phone number is required";
    } elseif (!validatePhoneNumber($phone)) {
        $errors[] = "Please enter a valid Philippine phone number (e.g., 09123456789)";
    }
    
    // Validate address fields
    if (empty($region)) {
        $errors[] = "Region is required";
    }
    
    if (empty($province)) {
        $errors[] = "Province is required";
    }
    
    if (empty($city)) {
        $errors[] = "City/Municipality is required";
    }
    
    if (empty($barangay)) {
        $errors[] = "Barangay is required";
    }
    
    // Validate password
    if (empty($password)) {
        $errors[] = "Password is required";
    } elseif (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters";
    }
    
    // Validate password confirmation
    if ($password !== $confirm) {
        $errors[] = "Password confirmation does not match";
    }
    
    // If validation errors exist, return error response
    if (!empty($errors)) {
        sendResponse('error', implode(". ", $errors));
    }
    
    // Connect to database
    $conn = con();
    
    if (!$conn) {
        sendResponse('error', 'Database connection failed');
    }
    
    // Check if email already exists (email should be unique)
    $check_query = "SELECT id FROM users WHERE email = ?";
    $stmt = mysqli_prepare($conn, $check_query);
    
    if (!$stmt) {
        sendResponse('error', 'Database preparation error: ' . mysqli_error($conn));
    }
    
    // Bind parameter and execute query
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    // If email already exists, return error
    if (mysqli_num_rows($result) > 0) {
        mysqli_stmt_close($stmt);
        mysqli_close($conn);
        sendResponse('error', 'Email address is already registered');
    }
    
    mysqli_stmt_close($stmt);
    
    // Hash password securely before storing in database
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    // Prepare INSERT query for users table
    $insert_query = "INSERT INTO users (fname, mname, lname, region, province, city, barangay, phone, email, password, created_at) 
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
    
    $stmt = mysqli_prepare($conn, $insert_query);
    
    if (!$stmt) {
        sendResponse('error', 'Database preparation error: ' . mysqli_error($conn));
    }
    
    // Bind parameters for the INSERT query
    mysqli_stmt_bind_param(
        $stmt,
        "ssssssssss",
        $fname,
        $mname,
        $lname,
        $region,
        $province,
        $city,
        $barangay,
        $phone,
        $email,
        $hashed_password
    );
    
    // Execute the INSERT query
    if (mysqli_stmt_execute($stmt)) {
        // Registration successful
        mysqli_stmt_close($stmt);
        mysqli_close($conn);
        sendResponse('success', 'Registration successful! Your account has been created.');
    } else {
        // Registration failed
        $error = mysqli_stmt_error($stmt);
        mysqli_stmt_close($stmt);
        mysqli_close($conn);
        sendResponse('error', 'Registration failed: ' . $error);
    }
    
} catch (Exception $e) {
    // Handle any unexpected errors
    sendResponse('error', 'An unexpected error occurred. Please try again later.');
}
?>