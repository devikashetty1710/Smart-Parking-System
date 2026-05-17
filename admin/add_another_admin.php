<?php
include_once "../config/database.php";

// Get database connection
$conn = getConnection();

// Admin details
$admin_name = 'John Smith';
$admin_email = 'john.smith@smartpark.com';
$admin_phone = '9876543210';
$admin_password = password_hash('password123', PASSWORD_DEFAULT); // Password: password123
$admin_role = 'admin';

// Check if admin already exists
$check_sql = "SELECT * FROM users WHERE email = ? AND role = 'admin'";
$stmt = $conn->prepare($check_sql);
$stmt->bind_param("s", $admin_email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    // Insert new admin
    $insert_sql = "INSERT INTO users (full_name, email, phone, password, role) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($insert_sql);
    $stmt->bind_param("sssss", $admin_name, $admin_email, $admin_phone, $admin_password, $admin_role);
    
    if ($stmt->execute()) {
        echo "New admin account created successfully!<br>";
        echo "Email: " . $admin_email . "<br>";
        echo "Password: password123<br>";
    } else {
        echo "Error creating admin account: " . $conn->error . "<br>";
    }
} else {
    echo "Admin account with this email already exists.<br>";
}

$conn->close();
?> 