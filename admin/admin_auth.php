<?php
session_start();

// Check if user is logged in and is admin
if(!isset($_SESSION['admin_id']) || !isset($_SESSION['admin_email'])) {
    // Store the current URL in session to redirect back after login
    $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
    
    // Redirect to login page
    header("Location: ../login.php");
    exit();
}
?> 