<?php
session_start();
include_once "../config/database.php";

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$conn = getConnection();

// Get user details
$sql = "SELECT * FROM users WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validate current password
    if ($current_password !== $user['password']) {
        $error = "Current password is incorrect.";
    } elseif ($new_password !== $confirm_password) {
        $error = "New passwords do not match.";
    } elseif (strlen($new_password) < 6) {
        $error = "New password must be at least 6 characters long.";
    } else {
        // Update password
        $update_sql = "UPDATE users SET password = ? WHERE user_id = ?";
        $stmt = $conn->prepare($update_sql);
        $stmt->bind_param("si", $new_password, $user_id);
        
        if ($stmt->execute()) {
            $success = "Password updated successfully.";
        } else {
            $error = "Error updating password. Please try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - Smart Parking System</title>
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #4CAF50;
            --primary-dark: #388E3C;
            --primary-light: rgba(76, 175, 80, 0.1);
            --text-dark: #333;
            --text-light: #666;
            --white: #fff;
            --gray-light: #f5f5f5;
            --shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            --success-color: #4CAF50;
            --error-color: #f44336;
        }

        body {
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--gray-light);
            color: var(--text-dark);
            line-height: 1.6;
        }

        .profile-wrapper {
            padding-top: 80px;
            min-height: 100vh;
        }

        .profile-container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 2rem 1rem;
        }

        .profile-header {
            background: var(--white);
            padding: 2rem;
            border-radius: 10px;
            box-shadow: var(--shadow);
            margin-bottom: 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .header-content h1 {
            margin: 0;
            font-size: 2rem;
            color: var(--text-dark);
        }

        .header-content p {
            margin: 0.5rem 0 0;
            color: var(--text-light);
        }

        .action-buttons {
            display: flex;
            gap: 1rem;
        }

        .btn {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            border-radius: 5px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn i {
            font-size: 1.1rem;
        }

        .btn-primary {
            background: var(--primary-color);
            color: var(--white);
        }

        .btn-primary:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
        }

        .btn-secondary {
            background: var(--gray-light);
            color: var(--text-dark);
        }

        .btn-secondary:hover {
            background: #e0e0e0;
            transform: translateY(-2px);
        }

        .profile-content {
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 2rem;
        }

        .profile-sidebar {
            background: var(--white);
            border-radius: 10px;
            box-shadow: var(--shadow);
            overflow: hidden;
        }

        .profile-avatar {
            background: var(--primary-light);
            padding: 2rem;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
        }

        .avatar-icon {
            width: 100px;
            height: 100px;
            background: var(--white);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1rem;
            box-shadow: var(--shadow);
        }

        .avatar-icon i {
            font-size: 3rem;
            color: var(--primary-color);
        }

        .user-name {
            font-size: 1.5rem;
            font-weight: 500;
            color: var(--text-dark);
            margin: 0 0 0.5rem 0;
        }

        .user-email {
            color: var(--text-light);
            margin: 0 0 1rem 0;
        }

        .profile-stats {
            padding: 1.5rem;
            border-top: 1px solid var(--gray-light);
        }

        .stat-item {
            display: flex;
            justify-content: space-between;
            padding: 0.75rem 0;
            border-bottom: 1px solid var(--gray-light);
        }

        .stat-item:last-child {
            border-bottom: none;
        }

        .stat-label {
            color: var(--text-light);
        }

        .stat-value {
            font-weight: 500;
            color: var(--text-dark);
        }

        .profile-main {
            background: var(--white);
            border-radius: 10px;
            box-shadow: var(--shadow);
            padding: 2rem;
        }

        .section-title {
            font-size: 1.5rem;
            color: var(--text-dark);
            margin: 0 0 1.5rem 0;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid var(--gray-light);
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: var(--text-dark);
            font-weight: 500;
        }

        .form-control {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1rem;
            transition: border-color 0.3s;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            outline: none;
        }

        .form-text {
            display: block;
            margin-top: 0.5rem;
            color: var(--text-light);
            font-size: 0.9rem;
        }

        .alert {
            padding: 1rem;
            border-radius: 5px;
            margin-bottom: 1.5rem;
        }

        .alert-success {
            background: #e8f5e9;
            color: #2e7d32;
            border: 1px solid #c8e6c9;
        }

        .alert-error {
            background: #ffebee;
            color: #c62828;
            border: 1px solid #ffcdd2;
        }

        .btn-submit {
            background: var(--primary-color);
            color: var(--white);
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 5px;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-submit:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
        }

        .password-section {
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 1px solid var(--gray-light);
        }

        @media (max-width: 768px) {
            .profile-content {
                grid-template-columns: 1fr;
            }

            .profile-header {
                flex-direction: column;
                text-align: center;
            }

            .action-buttons {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <?php include '../includes/navbar.php'; ?>

    <div class="profile-wrapper">
        <div class="profile-container">
            <div class="profile-header">
                <div class="header-content">
                    <h1>My Profile</h1>
                    <p>Manage your account information and preferences</p>
                </div>
                <div class="action-buttons">
                    <a href="dashboard.php" class="btn btn-secondary">
                        <i class="fas fa-tachometer-alt"></i>
                        Dashboard
                    </a>
                </div>
            </div>

            <?php if (!empty($error)): ?>
                <div class="alert alert-error">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>
            <?php if (!empty($success)): ?>
                <div class="alert alert-success">
                    <?php echo $success; ?>
                </div>
            <?php endif; ?>

            <div class="profile-content">
                <div class="profile-sidebar">
                    <div class="profile-avatar">
                        <div class="avatar-icon">
                            <i class="fas fa-user"></i>
                        </div>
                        <h2 class="user-name"><?php echo htmlspecialchars($user['full_name']); ?></h2>
                        <p class="user-email"><?php echo htmlspecialchars($user['email']); ?></p>
                    </div>
                    <div class="profile-stats">
                        <div class="stat-item">
                            <span class="stat-label">Member Since</span>
                            <span class="stat-value"><?php echo date('M Y', strtotime($user['created_at'])); ?></span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">Account Type</span>
                            <span class="stat-value"><?php echo ucfirst($user['role']); ?></span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">Last Login</span>
                            <span class="stat-value"><?php echo isset($_SESSION['last_login']) ? date('M d, Y', strtotime($_SESSION['last_login'])) : 'N/A'; ?></span>
                        </div>
                    </div>
                </div>

                <div class="profile-main">
                    <h2 class="section-title">Personal Information</h2>
                    <form method="POST" action="">
                        <div class="form-group">
                            <label for="full_name">Full Name</label>
                            <input type="text" id="full_name" name="full_name" class="form-control" value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="email">Email Address</label>
                            <input type="email" id="email" name="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                            <span class="form-text">This email will be used for notifications and account recovery.</span>
                        </div>
                        
                        <div class="form-group">
                            <label for="phone">Phone Number</label>
                            <input type="tel" id="phone" name="phone" class="form-control" value="<?php echo htmlspecialchars($user['phone']); ?>">
                            <span class="form-text">Optional. Used for important notifications.</span>
                        </div>
                        
                        <button type="submit" class="btn-submit">Update Profile</button>
                    </form>
                    
                    <div class="password-section">
                        <h2 class="section-title">Change Password</h2>
                        <form method="POST" action="">
                            <div class="form-group">
                                <label for="current_password">Current Password</label>
                                <input type="password" id="current_password" name="current_password" class="form-control" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="new_password">New Password</label>
                                <input type="password" id="new_password" name="new_password" class="form-control" required>
                                <span class="form-text">Password must be at least 6 characters long.</span>
                            </div>
                            
                            <div class="form-group">
                                <label for="confirm_password">Confirm New Password</label>
                                <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
                            </div>
                            
                            <button type="submit" class="btn-submit">Change Password</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html> 