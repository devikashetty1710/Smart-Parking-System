<?php
// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

// Check if user session is valid
$conn = getConnection();
$stmt = $conn->prepare("SELECT user_id FROM users WHERE user_id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    // Invalid session, destroy it and redirect to login
    session_destroy();
    header("Location: ../login.php");
    exit();
}

$stmt->close();
$conn->close();
?> 