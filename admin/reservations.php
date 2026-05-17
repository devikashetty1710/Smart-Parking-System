<?php
// Include admin authentication check
require_once 'admin_auth.php';

include_once "../config/database.php";
$conn = getConnection();

// Get all reservations with user and space details
$reservations = [];
$result = $conn->query("SELECT r.*, u.full_name, u.email, p.space_name, p.location 
                        FROM reservations r 
                        JOIN users u ON r.user_id = u.user_id 
                        JOIN parking_spaces p ON r.space_id = p.space_id 
                        ORDER BY r.created_at DESC");
if($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $reservations[] = $row;
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservations Management - Smart Parking System</title>
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="../css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="admin-container">
        <div class="admin-sidebar">
            <div class="logo">SmartPark</div>
            <ul class="admin-menu">
                <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="spaces.php"><i class="fas fa-parking"></i> Parking Spaces</a></li>
                <li class="active"><a href="reservations.php"><i class="fas fa-calendar-check"></i> Reservations</a></li>
                <li><a href="users.php"><i class="fas fa-users"></i> Users</a></li>
                <li><a href="payments.php"><i class="fas fa-money-bill-wave"></i> Payments</a></li>
                <li><a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </div>
        
        <div class="admin-content">
            <div class="admin-header">
                <h1>Reservations Management</h1>
                <div class="admin-user">
                    <span>Welcome, <?php echo $_SESSION['admin_name']; ?></span>
                    <a href="../logout.php" class="logout-link"><i class="fas fa-sign-out-alt"></i> Logout</a>
                </div>
            </div>
            
            <div class="admin-section">
                <div class="section-header">
                    <h2>All Reservations</h2>
                </div>
                
                <div class="table-container">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>User</th>
                                <th>Space</th>
                                <th>Location</th>
                                <th>Start Time</th>
                                <th>End Time</th>
                                <th>Total Price</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($reservations as $reservation): ?>
                            <tr>
                                <td><?php echo $reservation['reservation_id']; ?></td>
                                <td>
                                    <?php echo $reservation['full_name']; ?><br>
                                    <small><?php echo $reservation['email']; ?></small>
                                </td>
                                <td><?php echo $reservation['space_name']; ?></td>
                                <td><?php echo $reservation['location']; ?></td>
                                <td><?php echo date('M d, Y H:i', strtotime($reservation['start_time'])); ?></td>
                                <td><?php echo date('M d, Y H:i', strtotime($reservation['end_time'])); ?></td>
                                <td>$<?php echo number_format($reservation['total_price'], 2); ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo strtolower($reservation['status']); ?>">
                                        <?php echo ucfirst($reservation['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="view_reservation.php?id=<?php echo $reservation['reservation_id']; ?>" class="action-btn view">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if(empty($reservations)): ?>
                            <tr>
                                <td colspan="9">No reservations found.</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html> 