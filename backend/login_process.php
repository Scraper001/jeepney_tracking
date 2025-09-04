<?php
session_start();
require_once('../connection/connection.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['identity'];
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        $_SESSION['login_error'] = "Username and password are required";
        header("Location: ../login.php");
        exit();
    }

    $conn = con();

    $query = "SELECT * FROM user_tbl WHERE username = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) == 1) {
        $user = mysqli_fetch_assoc($result);

        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['user_type'] = $user['usert_type'];

            header("Location: ../dashboard.php");
            exit();
        } else {
            $_SESSION['login_error'] = "Invalid password";
            header("Location: ../login.php");
            exit();
        }
    } else {
        $_SESSION['login_error'] = "User not found";
        header("Location: ../login.php");
        exit();
    }

    mysqli_stmt_close($stmt);
    mysqli_close($conn);
}
?>