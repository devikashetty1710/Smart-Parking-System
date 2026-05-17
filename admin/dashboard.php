<?php
// Include admin authentication check
require_once 'admin_auth.php';

include_once "../config/database.php";
$conn = getConnection();

// Get statistics
$stats = [
    'total_spaces' => 0,
    'available_spaces' => 0,
    'occupied_spaces' => 0,
    'users_count' => 0,
    'reservations_count' => 0,
    'revenue' => 0
];

// Total spaces
$result = $conn->query("SELECT COUNT(*) as count FROM parking_spaces");
if($result) {
    $stats['total_spaces'] = $result->fetch_assoc()['count'];
}

// Available spaces (all spaces are considered available for now)
$stats['available_spaces'] = $stats['total_spaces'];

// Occupied spaces (no spaces are considered occupied for now)
$stats['occupied_spaces'] = 0;

// Users count
$result = $conn->query("SELECT COUNT(*) as count FROM users");
if($result) {
    $stats['users_count'] = $result->fetch_assoc()['count'];
}

// Reservations count
$result = $conn->query("SELECT COUNT(*) as count FROM reservations");
if($result) {
    $stats['reservations_count'] = $result->fetch_assoc()['count'];
}

// Total revenue
$result = $conn->query("SELECT SUM(amount) as total FROM payments WHERE status = 'completed'");
if($result) {
    $row = $result->fetch_assoc();
    $stats['revenue'] = $row['total'] ? $row['total'] : 0;
}

// Handle add parking space form
$addSpaceMessage = '';
if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_space'])) {
    $space_name = filter_input(INPUT_POST, 'space_name', FILTER_SANITIZE_STRING);
    $location = filter_input(INPUT_POST, 'location', FILTER_SANITIZE_STRING);
    $price = filter_input(INPUT_POST, 'price', FILTER_VALIDATE_FLOAT);
    
    $stmt = $conn->prepare("INSERT INTO parking_spaces (space_name, location, price_per_hour) VALUES (?, ?, ?)");
    $stmt->bind_param("ssd", $space_name, $location, $price);
    
    if($stmt->execute()) {
        $addSpaceMessage = "Parking space added successfully.";
    } else {
        $addSpaceMessage = "Error: " . $stmt->error;
    }
    $stmt->close();
}

// Handle space deletion
if(isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $space_id = $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM parking_spaces WHERE space_id = ?");
    $stmt->bind_param("i", $space_id);
    if($stmt->execute()) {
        $message = "Parking space deleted successfully.";
    } else {
        $error = "Error deleting parking space: " . $stmt->error;
    }
    $stmt->close();
}

// Get all parking spaces for management
$spaces = [];
$result = $conn->query("SELECT * FROM parking_spaces ORDER BY space_id DESC");
if($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $spaces[] = $row;
    }
}

// Get recent reservations
$reservations = [];
$result = $conn->query("SELECT r.*, u.full_name, u.email, p.space_name 
                        FROM reservations r 
                        JOIN users u ON r.user_id = u.user_id 
                        JOIN parking_spaces p ON r.space_id = p.space_id 
                        ORDER BY r.created_at DESC LIMIT 10");
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
    <title>Admin Dashboard - Smart Parking System</title>
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="../css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="admin-container">
        <div class="admin-sidebar">
            <div class="logo">SmartPark</div>
            <ul class="admin-menu">
                <li class="active"><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="spaces.php"><i class="fas fa-parking"></i> Parking Spaces</a></li>
                <li><a href="reservations.php"><i class="fas fa-calendar-check"></i> Reservations</a></li>
                <li><a href="users.php"><i class="fas fa-users"></i> Users</a></li>
                <li><a href="payments.php"><i class="fas fa-money-bill-wave"></i> Payments</a></li>
                <li><a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </div>
        
        <div class="admin-content">
            <div class="admin-header">
                <h1>Admin Dashboard</h1>
                <div class="admin-user">
                    <span>Welcome, <?php echo $_SESSION['admin_name']; ?></span>
                    <a href="../logout.php" class="logout-link"><i class="fas fa-sign-out-alt"></i> Logout</a>
                </div>
            </div>
            
            <div class="admin-stats">
                <div class="stat-card">
                    <i class="fas fa-parking"></i>
                    <div class="stat-info">
                        <h3>Total Spaces</h3>
                        <span><?php echo $stats['total_spaces']; ?></span>
                    </div>
                </div>
                <div class="stat-card">
                    <i class="fas fa-check-circle"></i>
                    <div class="stat-info">
                        <h3>Available</h3>
                        <span><?php echo $stats['available_spaces']; ?></span>
                    </div>
                </div>
                <div class="stat-card">
                    <i class="fas fa-times-circle"></i>
                    <div class="stat-info">
                        <h3>Occupied</h3>
                        <span><?php echo $stats['occupied_spaces']; ?></span>
                    </div>
                </div>
                <div class="stat-card">
                    <i class="fas fa-users"></i>
                    <div class="stat-info">
                        <h3>Users</h3>
                        <span><?php echo $stats['users_count']; ?></span>
                    </div>
                </div>
                <div class="stat-card">
                    <i class="fas fa-calendar-check"></i>
                    <div class="stat-info">
                        <h3>Reservations</h3>
                        <span><?php echo $stats['reservations_count']; ?></span>
                    </div>
                </div>
                <div class="stat-card">
                    <i class="fas fa-dollar-sign"></i>
                    <div class="stat-info">
                        <h3>Revenue</h3>
                        <span>$<?php echo number_format($stats['revenue'], 2); ?></span>
                    </div>
                </div>
            </div>
            
            <div class="admin-sections">
                <div class="admin-section">
                    <h2>Add Parking Space</h2>
                    <?php if(!empty($addSpaceMessage)): ?>
                        <div class="message"><?php echo $addSpaceMessage; ?></div>
                    <?php endif; ?>
                    <form method="POST" action="dashboard.php" class="admin-form">
                        <div class="form-group">
                            <label for="space_name">Space Name/Number</label>
                            <input type="text" id="space_name" name="space_name" required>
                        </div>
                        <div class="form-group">
                            <label for="location">Location</label>
                            <input type="text" id="location" name="location" required>
                        </div>
                        <div class="form-group">
                            <label for="price">Price per Hour ($)</label>
                            <input type="number" id="price" name="price" step="0.01" min="0" required>
                        </div>
                        <button type="submit" name="add_space" class="btn">Add Space</button>
                    </form>
                </div>
                
                <div class="admin-section">
                    <h2>Recent Parking Spaces</h2>
                    <div class="table-container">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Location</th>
                                    <th>Price</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($spaces as $space): ?>
                                <tr>
                                    <td><?php echo $space['space_id']; ?></td>
                                    <td><?php echo $space['space_name']; ?></td>
                                    <td><?php echo $space['location']; ?></td>
                                    <td>$<?php echo number_format($space['price_per_hour'], 2); ?></td>
                                    <td>
                                        <a href="edit_space.php?id=<?php echo $space['space_id']; ?>" class="action-btn edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="dashboard.php?delete=<?php echo $space['space_id']; ?>" 
                                           class="action-btn delete" 
                                           onclick="return confirm('Are you sure you want to delete this space?')">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if(empty($spaces)): ?>
                                <tr>
                                    <td colspan="5">No parking spaces found.</td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <a href="spaces.php" class="view-all">View All Spaces</a>
                </div>
            </div>
            
            <div class="admin-section">
                <h2>Recent Reservations</h2>
                <div class="table-container">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>User</th>
                                <th>Space</th>
                                <th>Start Time</th>
                                <th>End Time</th>
                                <th>Status</th>
                                <th>Price</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($reservations as $reservation): ?>
                            <tr>
                                <td><?php echo $reservation['reservation_id']; ?></td>
                                <td><?php echo $reservation['full_name']; ?><br><small><?php echo $reservation['email']; ?></small></td>
                                <td><?php echo $reservation['space_name']; ?></td>
                                <td><?php echo date('M d, Y H:i', strtotime($reservation['start_time'])); ?></td>
                                <td><?php echo date('M d, Y H:i', strtotime($reservation['end_time'])); ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo strtolower($reservation['status']); ?>">
                                        <?php echo ucfirst($reservation['status']); ?>
                                    </span>
                                </td>
                                <td>$<?php echo number_format($reservation['total_price'], 2); ?></td>
                                <td>
                                    <a href="view_reservation.php?id=<?php echo $reservation['reservation_id']; ?>" class="action-btn view">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if(empty($reservations)): ?>
                            <tr>
                                <td colspan="8">No reservations found.</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <a href="reservations.php" class="view-all">View All Reservations</a>
            </div>
        </div>
    </div>
</body>
</html> 