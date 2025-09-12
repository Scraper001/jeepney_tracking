<?php
/**
 * Database Setup Script
 * Creates the required database and users table for the Jeepney Tracking System
 */

require_once('connection/connection.php');

try {
    // Connect to MySQL without specifying database initially
    $host = "localhost";
    $user = "root";
    $password = "";
    
    $conn = mysqli_connect($host, $user, $password);
    
    if (!$conn) {
        throw new Exception("Connection failed: " . mysqli_connect_error());
    }
    
    echo "Connected to MySQL successfully.\n";
    
    // Create database if it doesn't exist
    $create_db_query = "CREATE DATABASE IF NOT EXISTS jeepney_db";
    if (mysqli_query($conn, $create_db_query)) {
        echo "Database 'jeepney_db' created or already exists.\n";
    } else {
        throw new Exception("Error creating database: " . mysqli_error($conn));
    }
    
    // Select the database
    if (mysqli_select_db($conn, "jeepney_db")) {
        echo "Database 'jeepney_db' selected.\n";
    } else {
        throw new Exception("Error selecting database: " . mysqli_error($conn));
    }
    
    // Create users table
    $create_table_query = "
    CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        fname VARCHAR(50) NOT NULL,
        mname VARCHAR(50) NULL,
        lname VARCHAR(50) NOT NULL,
        region VARCHAR(50) NOT NULL,
        province VARCHAR(50) NOT NULL,
        city VARCHAR(50) NOT NULL,
        barangay VARCHAR(50) NOT NULL,
        phone VARCHAR(15) NOT NULL,
        email VARCHAR(100) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    if (mysqli_query($conn, $create_table_query)) {
        echo "Table 'users' created successfully.\n";
    } else {
        throw new Exception("Error creating table: " . mysqli_error($conn));
    }
    
    // Create indexes for better performance
    $create_index_email = "CREATE INDEX IF NOT EXISTS idx_email ON users(email)";
    $create_index_phone = "CREATE INDEX IF NOT EXISTS idx_phone ON users(phone)";
    
    if (mysqli_query($conn, $create_index_email)) {
        echo "Email index created successfully.\n";
    }
    
    if (mysqli_query($conn, $create_index_phone)) {
        echo "Phone index created successfully.\n";
    }
    
    // Show table structure
    $describe_query = "DESCRIBE users";
    $result = mysqli_query($conn, $describe_query);
    
    echo "\nTable structure:\n";
    echo "+--------------+---------------+------+-----+---------------------+----------------+\n";
    echo "| Field        | Type          | Null | Key | Default             | Extra          |\n";
    echo "+--------------+---------------+------+-----+---------------------+----------------+\n";
    
    while ($row = mysqli_fetch_assoc($result)) {
        printf("| %-12s | %-13s | %-4s | %-3s | %-19s | %-14s |\n",
            $row['Field'],
            $row['Type'],
            $row['Null'],
            $row['Key'],
            $row['Default'] ?? 'NULL',
            $row['Extra']
        );
    }
    echo "+--------------+---------------+------+-----+---------------------+----------------+\n";
    
    mysqli_close($conn);
    echo "\nDatabase setup completed successfully!\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
?>