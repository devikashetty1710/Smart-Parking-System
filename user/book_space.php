<?php
session_start();
include_once "../config/database.php";

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $space_id = $_POST['space_id'] ?? null;
    $space_name = $_POST['space_name'] ?? '';
    $location = $_POST['location'] ?? '';
    $price = $_POST['price'] ?? 0;

    if (!$space_id) {
        $_SESSION['error'] = "Invalid parking space selected.";
        header("Location: ../all_spaces.php");
        exit();
    }

    try {
        $conn = getConnection();
        
        // Check if space is still available
        $stmt = $conn->prepare("SELECT * FROM parking_spaces WHERE space_id = ?");
        $stmt->bind_param("i", $space_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $space = $result->fetch_assoc();

        if (!$space) {
            $_SESSION['error'] = "Parking space not found.";
            header("Location: ../all_spaces.php");
            exit();
        }

        // Store booking details in session for the booking form
        $_SESSION['booking'] = [
            'space_id' => $space_id,
            'space_name' => $space_name,
            'location' => $location,
            'price' => $price
        ];

        // Redirect to booking form
        header("Location: booking_form.php");
        exit();

    } catch (Exception $e) {
        $_SESSION['error'] = "An error occurred. Please try again.";
        header("Location: ../all_spaces.php");
        exit();
    }
} else {
    // If not POST request, redirect back to spaces page
    header("Location: ../all_spaces.php");
    exit();
}
?> 