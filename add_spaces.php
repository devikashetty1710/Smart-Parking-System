<?php
// Include database configuration
include_once "config/database.php";

// Function to add parking spaces
function addParkingSpaces($conn) {
    // Array of parking spaces to insert
    $spaces = [
        // Downtown Area (10 spaces)
        ['DT-A1', 'Downtown', 5.00, 1, 'Downtown parking space near shopping center'],
        ['DT-A2', 'Downtown', 5.00, 1, 'Downtown parking space near shopping center'],
        ['DT-A3', 'Downtown', 5.00, 1, 'Downtown parking space near shopping center'],
        ['DT-A4', 'Downtown', 5.00, 1, 'Downtown parking space near shopping center'],
        ['DT-A5', 'Downtown', 5.00, 1, 'Downtown parking space near shopping center'],
        ['DT-B1', 'Downtown', 4.50, 1, 'Downtown parking space near office buildings'],
        ['DT-B2', 'Downtown', 4.50, 1, 'Downtown parking space near office buildings'],
        ['DT-B3', 'Downtown', 4.50, 1, 'Downtown parking space near office buildings'],
        ['DT-B4', 'Downtown', 4.50, 1, 'Downtown parking space near office buildings'],
        ['DT-B5', 'Downtown', 4.50, 1, 'Downtown parking space near office buildings'],

        // Business District (10 spaces)
        ['BD-A1', 'Business District', 6.00, 1, 'Business district premium parking'],
        ['BD-A2', 'Business District', 6.00, 1, 'Business district premium parking'],
        ['BD-A3', 'Business District', 6.00, 1, 'Business district premium parking'],
        ['BD-A4', 'Business District', 6.00, 1, 'Business district premium parking'],
        ['BD-A5', 'Business District', 6.00, 1, 'Business district premium parking'],
        ['BD-B1', 'Business District', 5.50, 1, 'Business district standard parking'],
        ['BD-B2', 'Business District', 5.50, 1, 'Business district standard parking'],
        ['BD-B3', 'Business District', 5.50, 1, 'Business district standard parking'],
        ['BD-B4', 'Business District', 5.50, 1, 'Business district standard parking'],
        ['BD-B5', 'Business District', 5.50, 1, 'Business district standard parking'],

        // Shopping Mall (10 spaces)
        ['SM-A1', 'Shopping Mall', 4.00, 1, 'Shopping mall parking near main entrance'],
        ['SM-A2', 'Shopping Mall', 4.00, 1, 'Shopping mall parking near main entrance'],
        ['SM-A3', 'Shopping Mall', 4.00, 1, 'Shopping mall parking near main entrance'],
        ['SM-A4', 'Shopping Mall', 4.00, 1, 'Shopping mall parking near main entrance'],
        ['SM-A5', 'Shopping Mall', 4.00, 1, 'Shopping mall parking near main entrance'],
        ['SM-B1', 'Shopping Mall', 3.50, 1, 'Shopping mall parking near food court'],
        ['SM-B2', 'Shopping Mall', 3.50, 1, 'Shopping mall parking near food court'],
        ['SM-B3', 'Shopping Mall', 3.50, 1, 'Shopping mall parking near food court'],
        ['SM-B4', 'Shopping Mall', 3.50, 1, 'Shopping mall parking near food court'],
        ['SM-B5', 'Shopping Mall', 3.50, 1, 'Shopping mall parking near food court']
    ];

    // Prepare the SQL statement
    $sql = "INSERT INTO parking_spaces (space_name, location, price_per_hour, capacity, description) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);

    // Counter for successful insertions
    $successCount = 0;
    $errors = [];

    // Insert each space
    foreach ($spaces as $space) {
        try {
            $stmt->bind_param("ssdis", $space[0], $space[1], $space[2], $space[3], $space[4]);
            if ($stmt->execute()) {
                $successCount++;
            } else {
                $errors[] = "Error adding space {$space[0]}: " . $stmt->error;
            }
        } catch (Exception $e) {
            $errors[] = "Exception for space {$space[0]}: " . $e->getMessage();
        }
    }

    return [
        'success_count' => $successCount,
        'total_spaces' => count($spaces),
        'errors' => $errors
    ];
}

// Main execution
try {
    // Get database connection
    $conn = getConnection();

    // Check if connection is successful
    if ($conn) {
        // Add parking spaces
        $result = addParkingSpaces($conn);

        // Output results
        echo "<h2>Parking Spaces Addition Results</h2>";
        echo "<p>Successfully added {$result['success_count']} out of {$result['total_spaces']} parking spaces.</p>";

        if (!empty($result['errors'])) {
            echo "<h3>Errors:</h3>";
            echo "<ul>";
            foreach ($result['errors'] as $error) {
                echo "<li>" . htmlspecialchars($error) . "</li>";
            }
            echo "</ul>";
        }

        // Close the connection
        $conn->close();
    } else {
        echo "<p>Error: Could not connect to the database.</p>";
    }
} catch (Exception $e) {
    echo "<p>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?> 