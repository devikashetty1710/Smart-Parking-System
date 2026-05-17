<?php
include_once "config/database.php";

try {
    // Get database connection
    $conn = getConnection();

    // Read and execute the SQL file
    $sql = file_get_contents('setup_database.sql');
    
    // Split the SQL file into individual queries
    $queries = array_filter(array_map('trim', explode(';', $sql)));
    
    $successCount = 0;
    $errors = [];

    // Execute each query
    foreach ($queries as $query) {
        if (!empty($query)) {
            try {
                if ($conn->query($query)) {
                    $successCount++;
                } else {
                    $errors[] = "Error executing query: " . $conn->error;
                }
            } catch (Exception $e) {
                $errors[] = "Exception: " . $e->getMessage();
            }
        }
    }

    // Output results
    echo "<h2>Database Setup Results</h2>";
    echo "<p>Successfully executed $successCount queries.</p>";

    if (!empty($errors)) {
        echo "<h3>Errors:</h3>";
        echo "<ul>";
        foreach ($errors as $error) {
            echo "<li>" . htmlspecialchars($error) . "</li>";
        }
        echo "</ul>";
    }

    // Close the connection
    $conn->close();

} catch (Exception $e) {
    echo "<p>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?> 