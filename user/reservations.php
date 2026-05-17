<?php
session_start();
include_once "../config/database.php";

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

// Get user's reservations
$conn = getConnection();
$user_id = $_SESSION['user_id'];

// Get all reservations with space details
$sql = "SELECT r.*, p.space_name, p.location, p.price_per_hour 
        FROM reservations r 
        JOIN parking_spaces p ON r.space_id = p.space_id 
        WHERE r.user_id = ? 
        ORDER BY r.created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$reservations = $result->fetch_all(MYSQLI_ASSOC);

// Get statistics
$sql = "SELECT 
            COUNT(*) as total_count,
            SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active_count,
            SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_count,
            SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled_count,
            COALESCE(SUM(CASE WHEN status = 'completed' THEN total_price ELSE 0 END), 0) as total_spent
        FROM reservations 
        WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stats_result = $stmt->get_result();
$stats = $stats_result->fetch_assoc();

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Reservations - SmartPark</title>
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

        .reservations-wrapper {
            padding-top: 80px;
            min-height: 100vh;
        }

        .reservations-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem 1rem;
        }

        .reservations-header {
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

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
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

        .reservations-list {
            background: var(--white);
            border-radius: 10px;
            box-shadow: var(--shadow);
            overflow: hidden;
        }

        .reservation-item {
            display: grid;
            grid-template-columns: 1fr auto;
            gap: 1.5rem;
            padding: 1.5rem;
            border-bottom: 1px solid var(--gray-light);
            transition: all 0.3s ease;
        }

        .reservation-item:last-child {
            border-bottom: none;
        }

        .reservation-item:hover {
            background: var(--gray-light);
        }

        .reservation-details {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .reservation-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 0.5rem;
        }

        .space-name {
            font-size: 1.2rem;
            font-weight: 500;
            color: var(--text-dark);
            margin: 0;
        }

        .reservation-status {
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
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

        .reservation-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-top: 0.5rem;
        }

        .info-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--text-light);
            font-size: 0.9rem;
        }

        .info-item i {
            color: var(--primary-color);
            width: 16px;
        }

        .reservation-actions {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
            justify-content: center;
        }

        .action-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            border-radius: 5px;
            text-decoration: none;
            font-weight: 500;
            font-size: 0.9rem;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
        }

        .action-btn i {
            font-size: 1rem;
        }

        .btn-view {
            background: var(--primary-light);
            color: var(--primary-color);
        }

        .btn-view:hover {
            background: var(--primary-color);
            color: var(--white);
        }

        .btn-cancel {
            background: #ffebee;
            color: #c62828;
        }

        .btn-cancel:hover {
            background: #c62828;
            color: var(--white);
        }

        .btn-extend {
            background: #e3f2fd;
            color: #1976d2;
        }

        .btn-extend:hover {
            background: #1976d2;
            color: var(--white);
        }

        .no-reservations {
            text-align: center;
            padding: 3rem;
        }

        .no-reservations i {
            font-size: 4rem;
            color: var(--text-light);
            margin-bottom: 1rem;
        }

        .no-reservations h2 {
            margin: 0 0 0.5rem 0;
            color: var(--text-dark);
        }

        .no-reservations p {
            color: var(--text-light);
            margin-bottom: 1.5rem;
        }

        @media (max-width: 768px) {
            .reservations-header {
                flex-direction: column;
                text-align: center;
            }

            .action-buttons {
                width: 100%;
                justify-content: center;
            }

            .reservation-item {
                grid-template-columns: 1fr;
                gap: 1rem;
            }

            .reservation-actions {
                flex-direction: row;
                justify-content: flex-start;
            }

            .action-btn {
                flex: 1;
            }
        }
    </style>
</head>
<body>
    <?php include '../includes/navbar.php'; ?>

    <div class="reservations-wrapper">
        <div class="reservations-container">
            <div class="reservations-header">
                <div class="header-content">
                    <h1>My Reservations</h1>
                    <p>View and manage your parking reservations</p>
                </div>
                <div class="action-buttons">
                    <a href="../all_spaces.php" class="btn btn-primary">
                        <i class="fas fa-parking"></i>
                        Find Parking
                    </a>
                    <a href="dashboard.php" class="btn btn-secondary">
                        <i class="fas fa-tachometer-alt"></i>
                        Dashboard
                    </a>
                </div>
            </div>

            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Total Reservations</h3>
                        <p><?php echo $stats['total_count']; ?></p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Active Reservations</h3>
                        <p><?php echo $stats['active_count']; ?></p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Completed</h3>
                        <p><?php echo $stats['completed_count']; ?></p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Total Spent</h3>
                        <p>$<?php echo number_format($stats['total_spent'], 2); ?></p>
                    </div>
                </div>
            </div>

            <div class="reservations-list">
                <?php if (count($reservations) > 0): ?>
                    <?php foreach ($reservations as $reservation): ?>
                        <div class="reservation-item">
                            <div class="reservation-details">
                                <div class="reservation-header">
                                    <h3 class="space-name"><?php echo htmlspecialchars($reservation['space_name']); ?></h3>
                                    <div class="reservation-status status-<?php echo strtolower($reservation['status']); ?>">
                                        <?php echo ucfirst($reservation['status']); ?>
                                    </div>
                                </div>
                                <div class="reservation-info">
                                    <div class="info-item">
                                        <i class="fas fa-map-marker-alt"></i>
                                        <?php echo htmlspecialchars($reservation['location']); ?>
                                    </div>
                                    <div class="info-item">
                                        <i class="fas fa-calendar"></i>
                                        <?php echo date('M d, Y', strtotime($reservation['start_time'])); ?>
                                    </div>
                                    <div class="info-item">
                                        <i class="fas fa-clock"></i>
                                        <?php echo date('h:i A', strtotime($reservation['start_time'])); ?> - 
                                        <?php echo date('h:i A', strtotime($reservation['end_time'])); ?>
                                    </div>
                                    <div class="info-item">
                                        <i class="fas fa-dollar-sign"></i>
                                        $<?php echo number_format($reservation['total_price'], 2); ?>
                                    </div>
                                </div>
                            </div>
                            <div class="reservation-actions">
                                <a href="reservation_details.php?id=<?php echo $reservation['reservation_id']; ?>" class="action-btn btn-view">
                                    <i class="fas fa-eye"></i>
                                    View Details
                                </a>
                                <?php if ($reservation['status'] == 'active' || $reservation['status'] == 'pending'): ?>
                                    <a href="extend_reservation.php?id=<?php echo $reservation['reservation_id']; ?>" class="action-btn btn-extend">
                                        <i class="fas fa-clock"></i>
                                        Extend Time
                                    </a>
                                    <a href="cancel_reservation.php?id=<?php echo $reservation['reservation_id']; ?>" class="action-btn btn-cancel" onclick="return confirm('Are you sure you want to cancel this reservation?');">
                                        <i class="fas fa-times"></i>
                                        Cancel
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="no-reservations">
                        <i class="fas fa-calendar-times"></i>
                        <h2>No Reservations Found</h2>
                        <p>You haven't made any parking reservations yet.</p>
                        <a href="../all_spaces.php" class="btn btn-primary">
                            <i class="fas fa-parking"></i>
                            Find Parking
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html> 