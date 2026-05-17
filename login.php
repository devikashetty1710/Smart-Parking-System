<?php
session_start();

// Redirect if already logged in
if(isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
} else if(isset($_SESSION['admin_id'])) {
    header("Location: admin/dashboard.php");
    exit();
}

// Process login form
if($_SERVER["REQUEST_METHOD"] == "POST") {
    include_once "config/database.php";
    $conn = getConnection();
    
    // Sanitize and get inputs
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $passwordInput = isset($_POST['password']) ? $_POST['password'] : '';
    $isAdminLogin = isset($_POST['is_admin']) && $_POST['is_admin'] == '1';

    if (empty($email) || empty($passwordInput)) {
        $error = "Email or Password missing.";
    } else {
        // Query users table with role filter for admin login
        $sql = "SELECT * FROM users WHERE email = ?" . ($isAdminLogin ? " AND role = 'admin'" : "");
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            $error = "Database error. Please try again.";
        } else {
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result && $result->num_rows === 1) {
                $user = $result->fetch_assoc();

                if ($passwordInput === $user['password']) {
                    // Set session variables
                    if ($isAdminLogin) {
                        $_SESSION['admin_id'] = $user['user_id'];
                        $_SESSION['admin_email'] = $user['email'];
                        $_SESSION['admin_name'] = $user['full_name'];
                    } else {
                        $_SESSION['user_id'] = $user['user_id'];
                        $_SESSION['email'] = $user['email'];
                        $_SESSION['full_name'] = $user['full_name'];
                    }
                    
                    // Redirect based on login type and stored redirect URL
                    if ($isAdminLogin) {
                        if(isset($_SESSION['redirect_url'])) {
                            $redirect_url = $_SESSION['redirect_url'];
                            unset($_SESSION['redirect_url']);
                            header("Location: " . $redirect_url);
                        } else {
                            header("Location: admin/dashboard.php");
                        }
                    } else {
                        header("Location: user/dashboard.php");
                    }
                    exit();
                } else {
                    $error = "Incorrect password.";
                }
            } else {
                $error = $isAdminLogin ? "Admin not found." : "User not found. Please register.";
            }
            $stmt->close();
        }
    }
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Smart Parking System</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-form login-form">
            <h2>Login to SmartPark</h2>
            <?php if(isset($error)): ?>
                <div class="error-message"><?php echo $error; ?></div>
            <?php endif; ?>
            <form action="login.php" method="POST">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="is_admin" value="1"> Login as Admin
                    </label>
                </div>
                <button type="submit" class="submit-btn">Login</button>
            </form>
            <div class="auth-links">
                <p>Don't have an account? <a href="register.php">Register</a></p>
                <p><a href="forgot_password.php">Forgot Password?</a></p>
            </div>
            <div class="back-to-home">
                <a href="index.php"><i class="fas fa-arrow-left"></i> Back to Home</a>
            </div>
        </div>
    </div>
</body>
</html>
