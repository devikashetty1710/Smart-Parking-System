<?php
$host = 'localhost';
$dbname = 'smart_parking_db';
$username = 'root';
$password = '';

// Create connection
$conn = new mysqli($host, $username, $password);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create database if not exists
$sql = "CREATE DATABASE IF NOT EXISTS $dbname";
if ($conn->query($sql) !== TRUE) {
    die("Error creating database: " . $conn->error);
}

// Select the database
$conn->select_db($dbname);

// Create tables if not exist
$tables = [
    "CREATE TABLE IF NOT EXISTS users (
        user_id INT AUTO_INCREMENT PRIMARY KEY,
        full_name VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL UNIQUE,
        phone VARCHAR(15),
        password VARCHAR(255) NOT NULL,
        role ENUM('user', 'admin') DEFAULT 'user',
        registered_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )",
    
    "CREATE TABLE IF NOT EXISTS parking_spaces (
        space_id INT AUTO_INCREMENT PRIMARY KEY,
        space_name VARCHAR(50) NOT NULL,
        location VARCHAR(100) NOT NULL,
        status ENUM('available', 'occupied', 'reserved', 'maintenance') DEFAULT 'available',
        price_per_hour DECIMAL(10,2) NOT NULL DEFAULT 2.00,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )",
    
    "CREATE TABLE IF NOT EXISTS reservations (
        reservation_id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        space_id INT NOT NULL,
        start_time DATETIME NOT NULL,
        end_time DATETIME NOT NULL,
        total_price DECIMAL(10,2) NOT NULL,
        status ENUM('active', 'completed', 'cancelled') DEFAULT 'active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(user_id),
        FOREIGN KEY (space_id) REFERENCES parking_spaces(space_id)
    )",
    
    "CREATE TABLE IF NOT EXISTS payments (
        payment_id INT AUTO_INCREMENT PRIMARY KEY,
        reservation_id INT NOT NULL,
        amount DECIMAL(10,2) NOT NULL,
        payment_method VARCHAR(50) NOT NULL,
        transaction_id VARCHAR(100),
        status ENUM('pending', 'completed', 'failed') DEFAULT 'pending',
        payment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (reservation_id) REFERENCES reservations(reservation_id)
    )"
];

foreach ($tables as $table_sql) {
    if ($conn->query($table_sql) !== TRUE) {
        echo "Error creating table: " . $conn->error;
    }
}

// Insert admin user if not exists
$admin_email = 'admin@smartpark.com';
$admin_password = 'admin123'; // Plain password
$admin_name = 'System Administrator';

$check_admin = "SELECT * FROM users WHERE email = '$admin_email' AND role = 'admin'";
$result = $conn->query($check_admin);

if ($result->num_rows == 0) {
    $insert_admin = "INSERT INTO users (full_name, email, phone, password, role) 
                     VALUES ('$admin_name', '$admin_email', '1234567890', '$admin_password', 'admin')";
    $conn->query($insert_admin);
}

// Function to get database connection
function getConnection() {
    global $host, $dbname, $username, $password;
    $conn = new mysqli($host, $username, $password, $dbname);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    return $conn;
}
?> 