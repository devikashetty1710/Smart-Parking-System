<?php
session_start();
include_once "../config/database.php";

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

// Get user information
$conn = getConnection();
$user_id = $_SESSION['user_id'];

// Verify user exists
$stmt = $conn->prepare("SELECT user_id FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    session_destroy();
    header("Location: ../login.php");
    exit();
}

// Get user's active reservations
$sql = "SELECT COUNT(*) as active_count FROM reservations 
        WHERE user_id = ? AND status IN ('active', 'pending')";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$active_result = $stmt->get_result();
$active_count = $active_result->fetch_assoc()['active_count'];

// Get total reservations
$sql = "SELECT COUNT(*) as total_count FROM reservations WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$total_result = $stmt->get_result();
$total_count = $total_result->fetch_assoc()['total_count'];

// Get total spent
$sql = "SELECT COALESCE(SUM(total_price), 0) as total_spent FROM reservations 
        WHERE user_id = ? AND status = 'completed'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$spent_result = $stmt->get_result();
$total_spent = $spent_result->fetch_assoc()['total_spent'];

// Get recent reservations
$sql = "SELECT r.*, p.space_name, p.location, p.price_per_hour 
        FROM reservations r 
        JOIN parking_spaces p ON r.space_id = p.space_id 
        WHERE r.user_id = ? 
        ORDER BY r.created_at DESC 
        LIMIT 5";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$recent_reservations = $stmt->get_result();

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard - SmartPark</title>
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
        }

        body {
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--gray-light);
            color: var(--text-dark);
            line-height: 1.6;
        }

        .dashboard-wrapper {
            padding-top: 80px; /* Space for fixed navbar */
            min-height: 100vh;
        }

        .dashboard-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem 1rem;
        }

        .dashboard-header {
            background: var(--white);
            padding: 2rem;
            border-radius: 10px;
            box-shadow: var(--shadow);
            margin-bottom: 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .welcome-section {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .user-avatar {
            width: 60px;
            height: 60px;
            background: var(--primary-light);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .user-avatar i {
            font-size: 2rem;
            color: var(--primary-color);
        }

        .welcome-text h1 {
            margin: 0;
            font-size: 1.8rem;
            color: var(--text-dark);
        }

        .welcome-text p {
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

        .dashboard-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: var(--white);
            padding: 1.5rem;
            border-radius: 10px;
            box-shadow: var(--shadow);
            display: flex;
            align-items: center;
            gap: 1rem;
            transition: all 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-icon {
            width: 50px;
            height: 50px;
            background: var(--primary-light);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .stat-icon i {
            font-size: 1.5rem;
            color: var(--primary-color);
        }

        .stat-info {
            flex: 1;
        }

        .stat-info h3 {
            margin: 0;
            font-size: 1rem;
            color: var(--text-light);
        }

        .stat-info p {
            margin: 0.25rem 0 0;
            font-size: 1.5rem;
            font-weight: bold;
            color: var(--text-dark);
        }

        .dashboard-content {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 1.5rem;
        }

        .recent-reservations {
            background: var(--white);
            padding: 1.5rem;
            border-radius: 10px;
            box-shadow: var(--shadow);
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .section-header h2 {
            margin: 0;
            font-size: 1.5rem;
            color: var(--text-dark);
        }

        .view-all {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .view-all:hover {
            text-decoration: underline;
        }

        .reservation-list {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .reservation-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem;
            border-radius: 8px;
            background: var(--gray-light);
            transition: all 0.3s ease;
        }

        .reservation-item:hover {
            background: #e8e8e8;
        }

        .reservation-details {
            flex: 1;
        }

        .reservation-details h3 {
            margin: 0 0 0.5rem 0;
            font-size: 1.1rem;
            color: var(--text-dark);
        }

        .reservation-details p {
            margin: 0.25rem 0;
            color: var(--text-light);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .reservation-details i {
            width: 16px;
            color: var(--primary-color);
        }

        .reservation-status {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: 500;
            font-size: 0.9rem;
        }

        .status-active {
            background: #e8f5e9;
            color: #2e7d32;
        }

        .status-pending {
            background: #fff3e0;
            color: #ef6c00;
        }

        .status-completed {
            background: #e3f2fd;
            color: #1976d2;
        }

        .status-cancelled {
            background: #ffebee;
            color: #c62828;
        }

        .quick-actions {
            background: var(--white);
            padding: 1.5rem;
            border-radius: 10px;
            box-shadow: var(--shadow);
        }

        .quick-actions h2 {
            margin: 0 0 1.5rem 0;
            font-size: 1.5rem;
            color: var(--text-dark);
        }

        .action-list {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .action-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem;
            border-radius: 8px;
            background: var(--gray-light);
            text-decoration: none;
            color: var(--text-dark);
            transition: all 0.3s ease;
        }

        .action-item:hover {
            background: #e8e8e8;
            transform: translateX(5px);
        }

        .action-icon {
            width: 40px;
            height: 40px;
            background: var(--primary-light);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .action-icon i {
            font-size: 1.2rem;
            color: var(--primary-color);
        }

        .action-text {
            flex: 1;
        }

        .action-text h3 {
            margin: 0;
            font-size: 1rem;
            color: var(--text-dark);
        }

        .action-text p {
            margin: 0.25rem 0 0;
            font-size: 0.9rem;
            color: var(--text-light);
        }

        .no-reservations {
            text-align: center;
            padding: 2rem;
            color: var(--text-light);
        }

        @media (max-width: 992px) {
            .dashboard-content {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .dashboard-header {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
            }

            .welcome-section {
                flex-direction: column;
            }

            .action-buttons {
                width: 100%;
                justify-content: center;
            }

            .stat-card {
                flex-direction: column;
                text-align: center;
            }

            .stat-icon {
                margin-bottom: 0.5rem;
            }
        }
    </style>
</head>
<body>
    <?php include '../includes/navbar.php'; ?>

    <div class="dashboard-wrapper">
        <div class="dashboard-container">
            <div class="dashboard-header">
                <div class="welcome-section">
                    <div class="user-avatar">
                        <i class="fas fa-user"></i>
                    </div>
                    <div class="welcome-text">
                        <h1>Welcome, <?php echo htmlspecialchars($_SESSION['full_name']); ?>!</h1>
                        <p>Manage your parking reservations and account</p>
                    </div>
                </div>
                <div class="action-buttons">
                    <a href="../all_spaces.php" class="btn btn-primary">
                        <i class="fas fa-parking"></i>
                        Find Parking
                    </a>
                    <a href="profile.php" class="btn btn-secondary">
                        <i class="fas fa-user"></i>
                        Profile
                    </a>
                </div>
            </div>
            
            <div class="dashboard-stats">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Active Reservations</h3>
                        <p><?php echo $active_count; ?></p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-history"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Total Reservations</h3>
                        <p><?php echo $total_count; ?></p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Total Spent</h3>
                        <p>$<?php echo number_format($total_spent, 2); ?></p>
                    </div>
                </div>
            </div>
            
            <div class="dashboard-content">
                <div class="recent-reservations">
                    <div class="section-header">
                        <h2>Recent Reservations</h2>
                        <a href="reservations.php" class="view-all">
                            View All
                            <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                    <div class="reservation-list">
                        <?php if ($recent_reservations->num_rows > 0): ?>
                            <?php while($row = $recent_reservations->fetch_assoc()): ?>
                                <div class="reservation-item">
                                    <div class="reservation-details">
                                        <h3><?php echo htmlspecialchars($row['space_name']); ?></h3>
                                        <p><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($row['location']); ?></p>
                                        <p><i class="fas fa-calendar"></i> <?php echo date('M d, Y', strtotime($row['start_time'])); ?></p>
                                        <p><i class="fas fa-dollar-sign"></i> $<?php echo number_format($row['total_price'], 2); ?></p>
                                    </div>
                                    <div class="reservation-status status-<?php echo strtolower($row['status']); ?>">
                                        <?php echo ucfirst($row['status']); ?>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <div class="no-reservations">
                                <i class="fas fa-calendar-times" style="font-size: 3rem; color: var(--text-light); margin-bottom: 1rem;"></i>
                                <p>No reservations found.</p>
                                <a href="../all_spaces.php" class="btn btn-primary" style="display: inline-flex; margin-top: 1rem;">
                                    <i class="fas fa-parking"></i>
                                    Find Parking
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="quick-actions">
                    <h2>Quick Actions</h2>
                    <div class="action-list">
                        <a href="../all_spaces.php" class="action-item">
                            <div class="action-icon">
                                <i class="fas fa-search"></i>
                            </div>
                            <div class="action-text">
                                <h3>Find Parking</h3>
                                <p>Search and book available parking spaces</p>
                            </div>
                        </a>
                        <a href="reservations.php" class="action-item">
                            <div class="action-icon">
                                <i class="fas fa-calendar-check"></i>
                            </div>
                            <div class="action-text">
                                <h3>My Reservations</h3>
                                <p>View and manage your parking reservations</p>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <footer>
        <div class="footer-content">
            <div class="footer-section">
                <h3>SmartPark</h3>
                <p>Find the perfect parking spot with ease. Our smart parking system helps you save time and reduce stress.</p>
            </div>
            <div class="footer-section">
                <h3>Quick Links</h3>
                <ul>
                    <li><a href="../index.php">Home</a></li>
                    <li><a href="../index.php#parking">Parking</a></li>
                    <li><a href="../index.php#features">Features</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h3>Connect With Us</h3>
                <div class="social-icons">
                    <a href="#"><i class="fab fa-facebook"></i></a>
                    <a href="#"><i class="fab fa-twitter"></i></a>
                    <a href="#"><i class="fab fa-instagram"></i></a>
                    <a href="#"><i class="fab fa-linkedin"></i></a>
                </div>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2024 SmartPark. All rights reserved.</p>
        </div>
    </footer>
</body>
</html> 