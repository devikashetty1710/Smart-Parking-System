<?php
include_once "database.php";

function setupAdminTable() {
    try {
        $conn = getConnection();
        
        // Read and execute SQL queries from admin_table.sql
        $sql = file_get_contents(__DIR__ . '/admin_table.sql');
        
        // Split the SQL file into individual queries
        $queries = array_filter(array_map('trim', explode(';', $sql)));
        
        // Execute each query
        foreach ($queries as $query) {
            if (!empty($query)) {
                if (!$conn->query($query)) {
                    throw new Exception("Error executing query: " . $conn->error);
                }
            }
        }
        
        echo "Admin table setup completed successfully!\n";
        return true;
    } catch (Exception $e) {
        echo "Error setting up admin table: " . $e->getMessage() . "\n";
        return false;
    } finally {
        if (isset($conn)) {
            $conn->close();
        }
    }
}

function createDefaultAdmin($email, $password, $fullName = 'Admin User') {
    try {
        $conn = getConnection();
        
        // Check if admin already exists
        $stmt = $conn->prepare("SELECT 1 FROM admins WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            echo "Admin user already exists.\n";
            return false;
        }
        
        // Hash the password
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        
        // Insert admin user
        $stmt = $conn->prepare("INSERT INTO admins (full_name, email, password) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $fullName, $email, $hashedPassword);
        
        if ($stmt->execute()) {
            echo "Default admin user created successfully!\n";
            return true;
        } else {
            throw new Exception("Error creating admin user: " . $stmt->error);
        }
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage() . "\n";
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

// Execute setup if this file is run directly
if (basename($_SERVER['SCRIPT_FILENAME']) == basename(__FILE__)) {
    setupAdminTable();
    
    // Create default admin if arguments are provided
    if ($argc > 2) {
        $email = $argv[1];
        $password = $argv[2];
        $fullName = isset($argv[3]) ? $argv[3] : 'Admin User';
        
        createDefaultAdmin($email, $password, $fullName);
    } else {
        echo "To create a default admin user, run: php setup_admin.php <email> <password> [full_name]\n";
    }
}
?> 