<?php
include_once "database.php";
include_once "create_admin.php";

function setupDatabase($createAdmin = false, $adminEmail = null, $adminPassword = null, $adminName = null) {
    try {
        $conn = getConnection();
        
        // Read and execute SQL queries from admin_queries.sql
        $sql = file_get_contents(__DIR__ . '/admin_queries.sql');
        
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
        
        echo "Database setup completed successfully!\n";
        
        // Create admin user if requested
        if ($createAdmin && $adminEmail && $adminPassword) {
            createAdminUser($adminEmail, $adminPassword, $adminName);
        }
        
        return true;
    } catch (Exception $e) {
        echo "Error setting up database: " . $e->getMessage() . "\n";
        return false;
    } finally {
        if (isset($conn)) {
            $conn->close();
        }
    }
}

// Execute setup if this file is run directly
if (basename($_SERVER['SCRIPT_FILENAME']) == basename(__FILE__)) {
    $createAdmin = false;
    $adminEmail = null;
    $adminPassword = null;
    $adminName = null;
    
    // Check for command line arguments
    if ($argc > 1) {
        $createAdmin = true;
        $adminEmail = $argv[1];
        $adminPassword = $argv[2];
        $adminName = isset($argv[3]) ? $argv[3] : null;
    }
    
    setupDatabase($createAdmin, $adminEmail, $adminPassword, $adminName);
}
?> 