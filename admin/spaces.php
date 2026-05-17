<?php
// Include admin authentication check
require_once 'admin_auth.php';

include_once "../config/database.php";
$conn = getConnection();

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

// Get all parking spaces
$spaces = [];
$result = $conn->query("SELECT * FROM parking_spaces ORDER BY space_id DESC");
if($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $spaces[] = $row;
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Parking Spaces Management - Smart Parking System</title>
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
                <li class="active"><a href="spaces.php"><i class="fas fa-parking"></i> Parking Spaces</a></li>
                <li><a href="reservations.php"><i class="fas fa-calendar-check"></i> Reservations</a></li>
                <li><a href="users.php"><i class="fas fa-users"></i> Users</a></li>
                <li><a href="payments.php"><i class="fas fa-money-bill-wave"></i> Payments</a></li>
                <li><a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </div>
        
        <div class="admin-content">
            <div class="admin-header">
                <h1>Parking Spaces Management</h1>
                <div class="admin-user">
                    <span>Welcome, <?php echo $_SESSION['admin_name']; ?></span>
                    <a href="../logout.php" class="logout-link"><i class="fas fa-sign-out-alt"></i> Logout</a>
                </div>
            </div>
            
            <?php if(isset($message)): ?>
                <div class="alert alert-success"><?php echo $message; ?></div>
            <?php endif; ?>
            
            <?php if(isset($error)): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <div class="admin-section">
                <div class="section-header">
                    <h2>All Parking Spaces</h2>
                    <a href="add_space.php" class="btn btn-primary"><i class="fas fa-plus"></i> Add New Space</a>
                </div>
                
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
                                    <a href="spaces.php?delete=<?php echo $space['space_id']; ?>" 
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
            </div>
        </div>
    </div>
</body>
</html> 