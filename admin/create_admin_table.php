<?php
include_once "../config/database.php";

// Get database connection
$conn = getConnection();

// Create admins table
$sql = "CREATE TABLE IF NOT EXISTS admins (
    admin_id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL
)";

if ($conn->query($sql) === TRUE) {
    echo "Admins table created successfully<br>";
    
    // Check if default admin exists
    $check_sql = "SELECT * FROM admins WHERE username = 'admin'";
    $result = $conn->query($check_sql);
    
    if ($result->num_rows === 0) {
        // Create default admin account
        $username = "admin";
        $password = password_hash("admin123", PASSWORD_DEFAULT); // Default password: admin123
        $email = "admin@smartpark.com";
        $full_name = "System Administrator";
        
        $insert_sql = "INSERT INTO admins (username, password, email, full_name) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($insert_sql);
        $stmt->bind_param("ssss", $username, $password, $email, $full_name);
        
        if ($stmt->execute()) {
            echo "Default admin account created successfully<br>";
            echo "Username: admin<br>";
            echo "Password: admin123<br>";
        } else {
            echo "Error creating default admin account: " . $conn->error . "<br>";
        }
    } else {
        echo "Default admin account already exists<br>";
    }
} else {
    echo "Error creating admins table: " . $conn->error . "<br>";
}

$conn->close();
?> 