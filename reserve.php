<?php
session_start();

// Check if user is logged in
if(!isset($_SESSION['user_id'])) {
    // Save intended destination for redirect after login
    $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
    header("Location: login.php");
    exit();
}

// Check if space ID is provided
if(!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$space_id = $_GET['id'];
$user_id = $_SESSION['user_id'];

include_once "config/database.php";
$conn = getConnection();

// Get parking space details
$space = null;
$stmt = $conn->prepare("SELECT * FROM parking_spaces WHERE space_id = ? AND status = 'available'");
$stmt->bind_param("i", $space_id);
$stmt->execute();
$result = $stmt->get_result();
if($result->num_rows > 0) {
    $space = $result->fetch_assoc();
} else {
    $error = "Parking space not available or does not exist.";
}
$stmt->close();

// Process reservation form
if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['reserve']) && $space) {
    $start_date = $_POST['start_date'];
    $start_time = $_POST['start_time'];
    $duration = $_POST['duration'];
    
    // Validate inputs
    $errors = [];
    
    if(empty($start_date)) {
        $errors[] = "Start date is required";
    }
    
    if(empty($start_time)) {
        $errors[] = "Start time is required";
    }
    
    if(empty($duration) || !is_numeric($duration) || $duration < 1) {
        $errors[] = "Valid duration is required";
    }
    
    if(empty($errors)) {
        // Calculate end time and price
        $start_datetime = $start_date . ' ' . $start_time;
        $end_datetime = date('Y-m-d H:i:s', strtotime($start_datetime . " + {$duration} hours"));
        $price_per_hour = $space['price_per_hour'];
        $total_price = $duration * $price_per_hour;
        
        // Create reservation
        $stmt = $conn->prepare("INSERT INTO reservations (user_id, space_id, start_time, end_time, total_price, status) 
                                VALUES (?, ?, ?, ?, ?, 'active')");
        $stmt->bind_param("iissdd", $user_id, $space_id, $start_datetime, $end_datetime, $total_price);
        
        if($stmt->execute()) {
            $reservation_id = $stmt->insert_id;
            
            // Update space status
            $update_space = $conn->prepare("UPDATE parking_spaces SET status = 'reserved' WHERE space_id = ?");
            $update_space->bind_param("i", $space_id);
            $update_space->execute();
            $update_space->close();
            
            // Redirect to payment page
            header("Location: payment.php?reservation_id={$reservation_id}");
            exit();
        } else {
            $errors[] = "Failed to create reservation: " . $stmt->error;
        }
        $stmt->close();
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reserve Parking - Smart Parking System</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <nav class="navbar">
        <div class="logo">SmartPark</div>
        <ul class="nav-links">
            <li><a href="index.php">Home</a></li>
            <li><a href="index.php#parking">Parking</a></li>
            <li><a href="user/dashboard.php">Dashboard</a></li>
            <li><a href="index.php#contact">Contact</a></li>
        </ul>
        <div class="auth-buttons">
            <span class="welcome-user">Welcome, <?php echo $_SESSION['full_name']; ?></span>
            <button class="logout-btn" onclick="window.location.href='logout.php'">Logout</button>
        </div>
    </nav>

    <div class="container">
        <div class="reservation-container">
            <h1>Reserve Parking Space</h1>
            
            <?php if(isset($error)): ?>
                <div class="error-message">
                    <p><?php echo $error; ?></p>
                    <a href="index.php#parking" class="btn">Find Another Space</a>
                </div>
            <?php elseif(isset($errors) && !empty($errors)): ?>
                <div class="error-message">
                    <ul>
                        <?php foreach($errors as $err): ?>
                            <li><?php echo $err; ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php else: ?>
                <div class="space-details">
                    <div class="space-card">
                        <div class="space-header">
                            <h2><?php echo $space['space_name']; ?></h2>
                            <span class="status-badge status-available">Available</span>
                        </div>
                        <div class="space-info">
                            <p><i class="fas fa-map-marker-alt"></i> Location: <?php echo $space['location']; ?></p>
                            <p><i class="fas fa-dollar-sign"></i> Price: $<?php echo number_format($space['price_per_hour'], 2); ?> per hour</p>
                        </div>
                    </div>
                    
                    <form action="reserve.php?id=<?php echo $space_id; ?>" method="POST" class="reservation-form">
                        <div class="form-group">
                            <label for="start_date">Start Date</label>
                            <input type="date" id="start_date" name="start_date" min="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="start_time">Start Time</label>
                            <input type="time" id="start_time" name="start_time" required>
                        </div>
                        <div class="form-group">
                            <label for="duration">Duration (hours)</label>
                            <input type="number" id="duration" name="duration" min="1" max="24" value="1" required>
                        </div>
                        <div class="price-calculation">
                            <div class="price-item">
                                <span>Price per hour:</span>
                                <span>$<?php echo number_format($space['price_per_hour'], 2); ?></span>
                            </div>
                            <div class="price-item">
                                <span>Duration:</span>
                                <span id="duration-display">1 hour</span>
                            </div>
                            <div class="price-item total">
                                <span>Estimated Total:</span>
                                <span id="total-price">$<?php echo number_format($space['price_per_hour'], 2); ?></span>
                            </div>
                        </div>
                        <button type="submit" name="reserve" class="submit-btn">Reserve Now</button>
                    </form>
                </div>
                
                <div class="reservation-policy">
                    <h3>Reservation Policy</h3>
                    <ul>
                        <li>Reservations can be made up to 30 days in advance.</li>
                        <li>Payment is required at the time of reservation.</li>
                        <li>Cancellations made more than 24 hours in advance receive a full refund.</li>
                        <li>Cancellations made within 24 hours incur a 50% fee.</li>
                        <li>No-shows are charged the full amount.</li>
                    </ul>
                </div>
            <?php endif; ?>
            
            <div class="back-to-parking">
                <a href="index.php#parking"><i class="fas fa-arrow-left"></i> Back to Parking Spaces</a>
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
                    <li><a href="index.php">Home</a></li>
                    <li><a href="index.php#parking">Parking</a></li>
                    <li><a href="index.php#contact">Contact</a></li>
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

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const durationInput = document.getElementById('duration');
            const durationDisplay = document.getElementById('duration-display');
            const totalPrice = document.getElementById('total-price');
            const pricePerHour = <?php echo $space ? $space['price_per_hour'] : 0; ?>;
            
            // Update price calculation when duration changes
            durationInput.addEventListener('input', function() {
                const hours = parseInt(this.value);
                if (hours === 1) {
                    durationDisplay.textContent = '1 hour';
                } else {
                    durationDisplay.textContent = hours + ' hours';
                }
                
                const total = (hours * pricePerHour).toFixed(2);
                totalPrice.textContent = '$' + total;
            });
            
            // Set min date to today
            const startDateInput = document.getElementById('start_date');
            const today = new Date().toISOString().split('T')[0];
            startDateInput.min = today;
        });
    </script>
</body>
</html> 