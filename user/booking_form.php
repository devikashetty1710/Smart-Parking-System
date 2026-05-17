<?php
session_start();
include_once "../config/database.php";

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Check if booking details exist in session
if (!isset($_SESSION['booking'])) {
    header("Location: ../all_spaces.php");
    exit();
}

$booking = $_SESSION['booking'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Parking Space - SmartPark</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #4CAF50;
            --secondary-color: #2196F3;
            --text-color: #333;
            --light-gray: #f5f5f5;
            --border-color: #ddd;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: var(--light-gray);
            color: var(--text-color);
            line-height: 1.6;
        }

        .container {
            max-width: 800px;
            margin: 2rem auto;
            padding: 0 1rem;
        }

        .booking-form {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .form-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .form-header h1 {
            color: var(--primary-color);
            margin-bottom: 0.5rem;
        }

        .space-details {
            background: var(--light-gray);
            padding: 1rem;
            border-radius: 5px;
            margin-bottom: 2rem;
        }

        .space-details h3 {
            color: var(--secondary-color);
            margin-bottom: 0.5rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
        }

        input[type="datetime-local"],
        input[type="number"] {
            width: 100%;
            padding: 0.8rem;
            border: 1px solid var(--border-color);
            border-radius: 5px;
            font-size: 1rem;
        }

        .price-display {
            font-size: 1.2rem;
            font-weight: bold;
            color: var(--primary-color);
            margin-top: 0.5rem;
        }

        .btn {
            display: inline-block;
            padding: 0.8rem 1.5rem;
            border: none;
            border-radius: 5px;
            font-size: 1rem;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .btn-primary {
            background-color: var(--primary-color);
            color: white;
        }

        .btn-secondary {
            background-color: var(--secondary-color);
            color: white;
        }

        .btn:hover {
            opacity: 0.9;
        }

        .error {
            color: #dc3545;
            margin-top: 0.5rem;
            font-size: 0.9rem;
        }

        @media (max-width: 768px) {
            .container {
                margin: 1rem auto;
            }

            .booking-form {
                padding: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="booking-form">
            <div class="form-header">
                <h1>Book Parking Space</h1>
                <p>Complete your booking details below</p>
            </div>

            <div class="space-details">
                <h3><?php echo htmlspecialchars($booking['space_name']); ?></h3>
                <p>Location: <?php echo htmlspecialchars($booking['location']); ?></p>
                <p>Price per hour: $<?php echo number_format($booking['price'], 2); ?></p>
            </div>

            <form action="process_booking.php" method="POST" id="bookingForm">
                <input type="hidden" name="space_id" value="<?php echo htmlspecialchars($booking['space_id']); ?>">
                
                <div class="form-group">
                    <label for="start_time">Start Time</label>
                    <input type="datetime-local" id="start_time" name="start_time" required 
                           min="<?php echo date('Y-m-d\TH:i'); ?>">
                </div>

                <div class="form-group">
                    <label for="duration">Duration (hours)</label>
                    <input type="number" id="duration" name="duration" min="1" max="24" value="1" required>
                </div>

                <div class="price-display">
                    Total Price: $<span id="totalPrice"><?php echo number_format($booking['price'], 2); ?></span>
                </div>

                <div class="form-group" style="margin-top: 2rem;">
                    <button type="submit" class="btn btn-primary">Confirm Booking</button>
                    <a href="../all_spaces.php" class="btn btn-secondary" style="margin-left: 1rem;">Cancel</a>
                </div>
            </form>
        </div>
    </div>

    <script>
        const durationInput = document.getElementById('duration');
        const totalPriceSpan = document.getElementById('totalPrice');
        const pricePerHour = <?php echo $booking['price']; ?>;

        durationInput.addEventListener('input', function() {
            const duration = parseInt(this.value) || 0;
            const totalPrice = duration * pricePerHour;
            totalPriceSpan.textContent = totalPrice.toFixed(2);
        });
    </script>
</body>
</html> 