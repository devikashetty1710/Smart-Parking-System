<?php
// Set error reporting to display all errors
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database configuration
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

echo "<h1>SmartPark Database Initialization</h1>";

// Create database if not exists
$sql = "CREATE DATABASE IF NOT EXISTS $dbname";
if ($conn->query($sql) === TRUE) {
    echo "<p style='color:green;'>Database '$dbname' created successfully or already exists.</p>";
} else {
    echo "<p style='color:red;'>Error creating database: " . $conn->error . "</p>";
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

echo "<h2>Creating Tables:</h2>";
echo "<ul>";

foreach ($tables as $table_sql) {
    if ($conn->query($table_sql) === TRUE) {
        // Extract table name from SQL
        preg_match('/CREATE TABLE IF NOT EXISTS (\w+)/', $table_sql, $matches);
        $table_name = isset($matches[1]) ? $matches[1] : 'Unknown';
        echo "<li style='color:green;'>Table '$table_name' created successfully or already exists.</li>";
    } else {
        echo "<li style='color:red;'>Error creating table: " . $conn->error . "</li>";
    }
}

echo "</ul>";

// Insert admin user if not exists
$admin_email = 'admin@smartpark.com';
$admin_password = 'admin123'; // Plain password
$admin_name = 'System Administrator';

$check_admin = "SELECT * FROM users WHERE email = '$admin_email' AND role = 'admin'";
$result = $conn->query($check_admin);

echo "<h2>Creating Admin User:</h2>";

if ($result->num_rows == 0) {
    $insert_admin = "INSERT INTO users (full_name, email, phone, password, role) 
                     VALUES ('$admin_name', '$admin_email', '1234567890', '$admin_password', 'admin')";
    if ($conn->query($insert_admin) === TRUE) {
        echo "<p style='color:green;'>Admin user created successfully.</p>";
        echo "<p>Admin Email: $admin_email</p>";
        echo "<p>Admin Password: $admin_password</p>";
    } else {
        echo "<p style='color:red;'>Error creating admin user: " . $conn->error . "</p>";
    }
} else {
    echo "<p style='color:blue;'>Admin user already exists.</p>";
}

// Insert sample parking spaces
echo "<h2>Creating Sample Parking Spaces:</h2>";

$check_spaces = "SELECT * FROM parking_spaces";
$result = $conn->query($check_spaces);

if ($result->num_rows == 0) {
    $sample_spaces = [
        "INSERT INTO parking_spaces (space_name, location, price_per_hour) VALUES ('A1', 'Main Street', 2.00)",
        "INSERT INTO parking_spaces (space_name, location, price_per_hour) VALUES ('A2', 'Main Street', 2.00)",
        "INSERT INTO parking_spaces (space_name, location, price_per_hour) VALUES ('B1', 'Park Avenue', 2.50)",
        "INSERT INTO parking_spaces (space_name, location, price_per_hour) VALUES ('B2', 'Park Avenue', 2.50)",
        "INSERT INTO parking_spaces (space_name, location, price_per_hour) VALUES ('C1', 'Downtown', 3.00)",
        "INSERT INTO parking_spaces (space_name, location, price_per_hour) VALUES ('C2', 'Downtown', 3.00)"
    ];
    
    $success_count = 0;
    foreach ($sample_spaces as $sql) {
        if ($conn->query($sql) === TRUE) {
            $success_count++;
        }
    }
    
    echo "<p style='color:green;'>Created $success_count sample parking spaces.</p>";
} else {
    echo "<p style='color:blue;'>Sample parking spaces already exist.</p>";
}

// Close connection
$conn->close();

echo "<h2>Database Initialization Complete!</h2>";
echo "<p>You can now <a href='login.php'>login</a> to the system.</p>";
echo "<p>Admin credentials:</p>";
echo "<ul>";
echo "<li>Email: $admin_email</li>";
echo "<li>Password: $admin_password</li>";
echo "</ul>";
?> 