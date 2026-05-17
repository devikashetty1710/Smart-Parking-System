<?php
// Include admin authentication check
require_once 'admin_auth.php';

include_once "../config/database.php";
$conn = getConnection();

// Get all payments with reservation and user details
$payments = [];
$result = $conn->query("SELECT p.*, r.reservation_id, r.start_time, r.end_time, 
                               u.full_name, u.email, ps.space_name, ps.location
                        FROM payments p
                        JOIN reservations r ON p.reservation_id = r.reservation_id
                        JOIN users u ON r.user_id = u.user_id
                        JOIN parking_spaces ps ON r.space_id = ps.space_id
                        ORDER BY p.payment_date DESC");
if($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $payments[] = $row;
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payments Management - Smart Parking System</title>
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
                <li><a href="reservations.php"><i class="fas fa-calendar-check"></i> Reservations</a></li>
                <li><a href="users.php"><i class="fas fa-users"></i> Users</a></li>
                <li class="active"><a href="payments.php"><i class="fas fa-money-bill-wave"></i> Payments</a></li>
                <li><a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </div>
        
        <div class="admin-content">
            <div class="admin-header">
                <h1>Payments Management</h1>
                <div class="admin-user">
                    <span>Welcome, <?php echo $_SESSION['admin_name']; ?></span>
                    <a href="../logout.php" class="logout-link"><i class="fas fa-sign-out-alt"></i> Logout</a>
                </div>
            </div>
            
            <div class="admin-section">
                <div class="section-header">
                    <h2>All Payments</h2>
                </div>
                
                <div class="table-container">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>User</th>
                                <th>Space</th>
                                <th>Reservation Period</th>
                                <th>Amount</th>
                                <th>Method</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($payments as $payment): ?>
                            <tr>
                                <td><?php echo $payment['payment_id']; ?></td>
                                <td>
                                    <?php echo $payment['full_name']; ?><br>
                                    <small><?php echo $payment['email']; ?></small>
                                </td>
                                <td>
                                    <?php echo $payment['space_name']; ?><br>
                                    <small><?php echo $payment['location']; ?></small>
                                </td>
                                <td>
                                    <?php echo date('M d, Y H:i', strtotime($payment['start_time'])); ?><br>
                                    to<br>
                                    <?php echo date('M d, Y H:i', strtotime($payment['end_time'])); ?>
                                </td>
                                <td>$<?php echo number_format($payment['amount'], 2); ?></td>
                                <td><?php echo ucfirst($payment['payment_method']); ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo strtolower($payment['status']); ?>">
                                        <?php echo ucfirst($payment['status']); ?>
                                    </span>
                                </td>
                                <td><?php echo date('M d, Y H:i', strtotime($payment['payment_date'])); ?></td>
                                <td>
                                    <a href="view_payment.php?id=<?php echo $payment['payment_id']; ?>" class="action-btn view">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if(empty($payments)): ?>
                            <tr>
                                <td colspan="9">No payments found.</td>
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