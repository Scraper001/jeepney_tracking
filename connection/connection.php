<?php
function con()
{
    $host = "localhost";
    $user = "root";
    $password = "";
    $database = "jeepney_db";

    $conn = mysqli_connect($host, $user, $password, $database);

    if (!$conn) {
        die("Connection failed: " . mysqli_connect_error());
    }

    return $conn;
}
?>