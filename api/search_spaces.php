<?php
// Set response headers
header('Content-Type: application/json');

// Include database connection
include_once "../config/database.php";
$conn = getConnection();

// Default response
$response = [
    'success' => false,
    'spaces' => [],
    'message' => 'No spaces found'
];

// Check if query parameter is provided
if(isset($_GET['query']) && !empty($_GET['query'])) {
    $query = trim($_GET['query']);
    
    // Prepare SQL with LIKE for searching location or space name
    $sql = "SELECT * FROM parking_spaces WHERE 
            (location LIKE ? OR space_name LIKE ?) 
            AND status = 'available' 
            ORDER BY space_id DESC";
    
    $stmt = $conn->prepare($sql);
    $searchParam = '%' . $query . '%';
    $stmt->bind_param("ss", $searchParam, $searchParam);
    
    if($stmt->execute()) {
        $result = $stmt->get_result();
        $spaces = [];
        
        if($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                // Format data for output
                $spaces[] = [
                    'space_id' => $row['space_id'],
                    'space_name' => $row['space_name'],
                    'location' => $row['location'],
                    'status' => $row['status'],
                    'price_per_hour' => $row['price_per_hour']
                ];
            }
            
            $response = [
                'success' => true,
                'spaces' => $spaces,
                'count' => count($spaces),
                'message' => count($spaces) . ' spaces found'
            ];
        }
    } else {
        $response['message'] = 'Error executing search: ' . $stmt->error;
    }
    
    $stmt->close();
} else {
    // If no query provided, return all available spaces
    $sql = "SELECT * FROM parking_spaces WHERE status = 'available' ORDER BY space_id DESC LIMIT 20";
    $result = $conn->query($sql);
    
    if($result && $result->num_rows > 0) {
        $spaces = [];
        while($row = $result->fetch_assoc()) {
            // Format data for output
            $spaces[] = [
                'space_id' => $row['space_id'],
                'space_name' => $row['space_name'],
                'location' => $row['location'],
                'status' => $row['status'],
                'price_per_hour' => $row['price_per_hour']
            ];
        }
        
        $response = [
            'success' => true,
            'spaces' => $spaces,
            'count' => count($spaces),
            'message' => count($spaces) . ' spaces found'
        ];
    }
}

$conn->close();

// Return response in JSON format
echo json_encode($response);
?> 