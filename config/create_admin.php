<?php
include_once "database.php";

function createAdminUser($email, $password, $fullName = 'Admin User') {
    try {
        $conn = getConnection();
        
        // Check if admin already exists
        $stmt = $conn->prepare("SELECT 1 FROM admins WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            echo "Admin user already exists.";
            return false;
        }
        
        // Hash the password
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        
        // Insert admin user
        $stmt = $conn->prepare("INSERT INTO admins (full_name, email, password) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $fullName, $email, $hashedPassword);
        
        if ($stmt->execute()) {
            echo "Admin user created successfully!";
            return true;
        } else {
            throw new Exception("Error creating admin user: " . $stmt->error);
        }
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage();
        return false;
    } finally {
        if (isset($stmt)) {
            $stmt->close();
        }
        if (isset($conn)) {
            $conn->close();
        }
    }
}

// If this file is run directly, create an admin user with command line arguments
if (basename($_SERVER['SCRIPT_FILENAME']) == basename(__FILE__)) {
    if ($argc < 3) {
        echo "Usage: php create_admin.php <email> <password> [full_name]\n";
        exit(1);
    }
    
    $email = $argv[1];
    $password = $argv[2];
    $fullName = isset($argv[3]) ? $argv[3] : 'Admin User';
    
    createAdminUser($email, $password, $fullName);
}
?> 