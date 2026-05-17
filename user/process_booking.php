<?php
session_start();
include_once "../config/database.php";

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Check if booking details exist in session
if (!isset($_SESSION['booking'])) {
    header("Location: ../all_spaces.php");
    exit();
}

// Check if form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $space_id = $_POST['space_id'];
    $start_time = $_POST['start_time'];
    $duration = $_POST['duration'];
    $user_id = $_SESSION['user_id'];
    
    // Validate inputs
    $errors = [];
    
    if (empty($space_id)) {
        $errors[] = "Space ID is required";
    }
    
    if (empty($start_time)) {
        $errors[] = "Start time is required";
    }
    
    if (empty($duration) || $duration < 1 || $duration > 24) {
        $errors[] = "Duration must be between 1 and 24 hours";
    }
    
    // Calculate end time
    $end_time = date('Y-m-d H:i:s', strtotime($start_time . ' + ' . $duration . ' hours'));
    
    // Check if space is still available
    $check_sql = "SELECT COUNT(*) as count FROM reservations 
                  WHERE space_id = ? 
                  AND status IN ('active', 'pending')
                  AND (
                      (start_time <= ? AND end_time >= ?) OR
                      (start_time <= ? AND end_time >= ?) OR
                      (start_time >= ? AND end_time <= ?)
                  )";
    
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("issssss", $space_id, $start_time, $start_time, $end_time, $end_time, $start_time, $end_time);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    $row = $result->fetch_assoc();
    
    if ($row['count'] > 0) {
        $errors[] = "This space is no longer available for the selected time period";
    }
    
    // If no errors, proceed with booking
    if (empty($errors)) {
        // Calculate total price
        $booking = $_SESSION['booking'];
        $total_price = $booking['price'] * $duration;
        
        // Insert reservation
        $sql = "INSERT INTO reservations (user_id, space_id, start_time, end_time, total_price, status) 
                VALUES (?, ?, ?, ?, ?, 'pending')";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iissd", $user_id, $space_id, $start_time, $end_time, $total_price);
        
        if ($stmt->execute()) {
            $reservation_id = $stmt->insert_id;
            
            // Clear booking session data
            unset($_SESSION['booking']);
            
            // Redirect to secure payment checkout page!
            header("Location: ../payment.php?reservation_id={$reservation_id}");
            exit();
        } else {
            $errors[] = "An error occurred while processing your booking. Please try again.";
        }
    }
    
    // If there are errors, store them in session and redirect back
    if (!empty($errors)) {
        $_SESSION['errors'] = $errors;
        header("Location: booking_form.php");
        exit();
    }
} else {
    // If not POST request, redirect back to spaces page
    header("Location: ../all_spaces.php");
    exit();
}
?> 