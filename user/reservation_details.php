<?php
session_start();
include_once "../config/database.php";

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

// Check if reservation ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: reservations.php");
    exit();
}

$reservation_id = $_GET['id'];
$user_id = $_SESSION['user_id'];

// Get database connection
$conn = getConnection();

// Get reservation details with space information
$sql = "SELECT r.*, p.space_name, p.location, p.price_per_hour 
        FROM reservations r 
        JOIN parking_spaces p ON r.space_id = p.space_id 
        WHERE r.reservation_id = ? AND r.user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $reservation_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

// Check if reservation exists and belongs to the user
if ($result->num_rows === 0) {
    $_SESSION['error'] = "Reservation not found or you don't have permission to view it.";
    header("Location: reservations.php");
    exit();
}

$reservation = $result->fetch_assoc();

// Calculate duration in hours
$start_time = new DateTime($reservation['start_time']);
$end_time = new DateTime($reservation['end_time']);
$duration = $start_time->diff($end_time)->h + ($start_time->diff($end_time)->days * 24);

// Close database connection
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservation Details - SmartPark</title>
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

        .details-wrapper {
            padding-top: 80px;
            min-height: 100vh;
        }

        .details-container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 2rem 1rem;
        }

        .details-header {
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
            padding: 0.75rem 1.5rem;
            border-radius: 5px;
            text-decoration: none;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s;
        }

        .btn-primary {
            background-color: var(--primary-color);
            color: var(--white);
        }

        .btn-primary:hover {
            background-color: var(--primary-dark);
        }

        .btn-secondary {
            background-color: var(--gray-light);
            color: var(--text-dark);
        }

        .btn-secondary:hover {
            background-color: #e0e0e0;
        }

        .details-card {
            background: var(--white);
            border-radius: 10px;
            box-shadow: var(--shadow);
            overflow: hidden;
            margin-bottom: 2rem;
        }

        .details-section {
            padding: 2rem;
            border-bottom: 1px solid #eee;
        }

        .details-section:last-child {
            border-bottom: none;
        }

        .details-section h2 {
            margin-top: 0;
            color: var(--text-dark);
            font-size: 1.5rem;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .details-section h2 i {
            color: var(--primary-color);
        }

        .details-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
        }

        .detail-item {
            display: flex;
            flex-direction: column;
        }

        .detail-label {
            font-size: 0.9rem;
            color: var(--text-light);
            margin-bottom: 0.25rem;
        }

        .detail-value {
            font-size: 1.1rem;
            font-weight: 500;
            color: var(--text-dark);
        }

        .status-badge {
            display: inline-block;
            padding: 0.5rem 1rem;
            border-radius: 50px;
            font-size: 0.9rem;
            font-weight: 500;
        }

        .status-pending {
            background-color: #fff3cd;
            color: #856404;
        }

        .status-active {
            background-color: #d4edda;
            color: #155724;
        }

        .status-completed {
            background-color: #cce5ff;
            color: #004085;
        }

        .status-cancelled {
            background-color: #f8d7da;
            color: #721c24;
        }

        .price-breakdown {
            width: 100%;
            border-collapse: collapse;
        }

        .price-breakdown th,
        .price-breakdown td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        .price-breakdown th {
            font-weight: 500;
            color: var(--text-light);
        }

        .price-breakdown tr:last-child td {
            border-bottom: none;
            font-weight: 600;
        }

        .price-breakdown .total-row td {
            color: var(--primary-color);
            font-size: 1.1rem;
        }

        @media (max-width: 768px) {
            .details-header {
                flex-direction: column;
                align-items: flex-start;
            }

            .action-buttons {
                width: 100%;
                justify-content: space-between;
            }

            .details-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <?php include '../includes/navbar.php'; ?>

    <div class="details-wrapper">
        <div class="details-container">
            <div class="details-header">
                <div class="header-content">
                    <h1>Reservation Details</h1>
                    <p>View detailed information about your parking reservation</p>
                </div>
                <div class="action-buttons">
                    <a href="reservations.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i>
                        Back to Reservations
                    </a>
                    <?php if ($reservation['status'] == 'active' || $reservation['status'] == 'pending'): ?>
                        <a href="extend_reservation.php?id=<?php echo $reservation_id; ?>" class="btn btn-primary">
                            <i class="fas fa-clock"></i>
                            Extend Time
                        </a>
                    <?php endif; ?>
                </div>
            </div>

            <div class="details-card">
                <div class="details-section">
                    <h2><i class="fas fa-info-circle"></i> Reservation Information</h2>
                    <div class="details-grid">
                        <div class="detail-item">
                            <span class="detail-label">Reservation ID</span>
                            <span class="detail-value">#<?php echo $reservation['reservation_id']; ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Status</span>
                            <span class="status-badge status-<?php echo strtolower($reservation['status']); ?>">
                                <?php echo ucfirst($reservation['status']); ?>
                            </span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Created On</span>
                            <span class="detail-value"><?php echo date('M d, Y h:i A', strtotime($reservation['created_at'])); ?></span>
                        </div>
                    </div>
                </div>

                <div class="details-section">
                    <h2><i class="fas fa-parking"></i> Parking Space</h2>
                    <div class="details-grid">
                        <div class="detail-item">
                            <span class="detail-label">Space Name</span>
                            <span class="detail-value"><?php echo htmlspecialchars($reservation['space_name']); ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Location</span>
                            <span class="detail-value"><?php echo htmlspecialchars($reservation['location']); ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Price Per Hour</span>
                            <span class="detail-value">$<?php echo number_format($reservation['price_per_hour'], 2); ?></span>
                        </div>
                    </div>
                </div>

                <div class="details-section">
                    <h2><i class="fas fa-calendar-alt"></i> Time Details</h2>
                    <div class="details-grid">
                        <div class="detail-item">
                            <span class="detail-label">Start Time</span>
                            <span class="detail-value"><?php echo date('M d, Y h:i A', strtotime($reservation['start_time'])); ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">End Time</span>
                            <span class="detail-value"><?php echo date('M d, Y h:i A', strtotime($reservation['end_time'])); ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Duration</span>
                            <span class="detail-value"><?php echo $duration; ?> hours</span>
                        </div>
                    </div>
                </div>

                <div class="details-section">
                    <h2><i class="fas fa-dollar-sign"></i> Payment Details</h2>
                    <table class="price-breakdown">
                        <tr>
                            <th>Item</th>
                            <th>Amount</th>
                        </tr>
                        <tr>
                            <td>Parking Fee (<?php echo $duration; ?> hours × $<?php echo number_format($reservation['price_per_hour'], 2); ?>)</td>
                            <td>$<?php echo number_format($reservation['total_price'], 2); ?></td>
                        </tr>
                        <tr class="total-row">
                            <td>Total Amount</td>
                            <td>$<?php echo number_format($reservation['total_price'], 2); ?></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>
</body>
</html> 