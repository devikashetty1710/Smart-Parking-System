<?php
session_start();
include_once "config/database.php";
include_once "includes/Mailer.php";

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
    header("Location: login.php");
    exit();
}

// Check if reservation ID is provided
if (!isset($_GET['reservation_id']) || !is_numeric($_GET['reservation_id'])) {
    header("Location: index.php");
    exit();
}

$reservation_id = $_GET['reservation_id'];
$user_id = $_SESSION['user_id'];
$conn = getConnection();

// Get reservation and space details joined with user details
$sql = "SELECT r.*, p.space_name, p.location, p.price_per_hour, u.email, u.full_name 
        FROM reservations r 
        JOIN parking_spaces p ON r.space_id = p.space_id 
        JOIN users u ON r.user_id = u.user_id
        WHERE r.reservation_id = ? AND r.user_id = ?";
        
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $reservation_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['error'] = "Reservation not found.";
    header("Location: index.php");
    exit();
}

$booking = $result->fetch_assoc();
$stmt->close();

$payment_completed = false;
$error = null;
$transaction_id = null;

// Handle checkout submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pay_now'])) {
    $cardholder = filter_input(INPUT_POST, 'cardholder', FILTER_SANITIZE_STRING);
    $card_number = filter_input(INPUT_POST, 'card_number', FILTER_SANITIZE_STRING);
    $expiry = filter_input(INPUT_POST, 'expiry', FILTER_SANITIZE_STRING);
    $cvv = filter_input(INPUT_POST, 'cvv', FILTER_SANITIZE_STRING);
    $method = filter_input(INPUT_POST, 'payment_method', FILTER_SANITIZE_STRING) ?: 'Credit Card';

    // Basic validation
    if (empty($cardholder) || empty($card_number) || empty($expiry) || empty($cvv)) {
        $error = "Please fill in all credit card payment details.";
    } elseif (strlen(str_replace(' ', '', $card_number)) < 16) {
        $error = "Invalid card number. Must be 16 digits.";
    } else {
        // Start database transaction
        $conn->begin_transaction();
        
        try {
            // 1. Update reservation status to 'active'
            $stmt = $conn->prepare("UPDATE reservations SET status = 'active' WHERE reservation_id = ?");
            $stmt->bind_param("i", $reservation_id);
            $stmt->execute();
            $stmt->close();

            // 2. Update parking space status to 'reserved'
            $stmt = $conn->prepare("UPDATE parking_spaces SET status = 'reserved' WHERE space_id = ?");
            $stmt->bind_param("i", $booking['space_id']);
            $stmt->execute();
            $stmt->close();

            // 3. Insert record into payments table
            $transaction_id = 'TXN-' . strtoupper(bin2hex(random_bytes(6)));
            $amount = $booking['total_price'];
            $status = 'completed';
            
            $stmt = $conn->prepare("INSERT INTO payments (reservation_id, amount, payment_method, transaction_id, status) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("idsss", $reservation_id, $amount, $method, $transaction_id, $status);
            $stmt->execute();
            $stmt->close();

            // Commit the transaction
            $conn->commit();
            $payment_completed = true;
            
            // 4. Send Confirmation Email!
            $booking_details = [
                'reservation_id' => $reservation_id,
                'space_name' => $booking['space_name'],
                'location' => $booking['location'],
                'start_time' => $booking['start_time'],
                'end_time' => $booking['end_time'],
                'total_price' => $booking['total_price']
            ];
            
            Mailer::sendBookingConfirmation($booking['email'], $booking['full_name'], $booking_details);

        } catch (Exception $e) {
            $conn->rollback();
            $error = "Transaction failed: " . $e->getMessage();
        }
    }
}

// Calculate duration in hours
$start_time = new DateTime($booking['start_time']);
$end_time = new DateTime($booking['end_time']);
$duration = $start_time->diff($end_time)->h + ($start_time->diff($end_time)->days * 24);
if ($duration <= 0) $duration = 1;

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Secure Checkout - Smart Parking System</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary: #10b981;
            --primary-dark: #059669;
            --primary-light: rgba(16, 185, 129, 0.1);
            --dark: #0f172a;
            --slate: #475569;
            --light: #f8fafc;
            --border: #e2e8f0;
            --shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            --card-grad: linear-gradient(135deg, #1e293b, #0f172a);
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--light);
            color: var(--dark);
            margin: 0;
            padding: 0;
        }

        .checkout-wrapper {
            padding-top: 100px;
            padding-bottom: 60px;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .checkout-container {
            max-width: 900px;
            width: 100%;
            margin: 0 20px;
            background: white;
            border-radius: 16px;
            box-shadow: var(--shadow);
            overflow: hidden;
            display: grid;
            grid-template-columns: 1.1fr 0.9fr;
            border: 1px solid var(--border);
        }

        .checkout-form-section {
            padding: 40px;
        }

        .checkout-summary-section {
            background-color: #f8fafc;
            border-left: 1px solid var(--border);
            padding: 40px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .section-title {
            font-size: 22px;
            font-weight: 700;
            margin-top: 0;
            margin-bottom: 25px;
            color: var(--dark);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .form-group {
            margin-bottom: 20px;
            position: relative;
        }

        .form-group label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: var(--slate);
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .form-group input {
            width: 100%;
            padding: 12px 16px;
            border: 1.5px solid var(--border);
            border-radius: 8px;
            font-size: 15px;
            transition: all 0.3s ease;
            background-color: white;
            color: var(--dark);
        }

        .form-group input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px var(--primary-light);
        }

        .input-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .card-type-logos {
            display: flex;
            gap: 8px;
            position: absolute;
            right: 15px;
            top: 38px;
            font-size: 20px;
            color: #94a3b8;
        }

        .pay-btn {
            width: 100%;
            padding: 14px;
            background-color: var(--primary);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            box-shadow: 0 4px 10px rgba(16, 185, 129, 0.2);
            margin-top: 10px;
        }

        .pay-btn:hover {
            background-color: var(--primary-dark);
            transform: translateY(-1px);
        }

        .summary-card {
            background: var(--card-grad);
            color: white;
            padding: 24px;
            border-radius: 12px;
            margin-bottom: 25px;
            position: relative;
            overflow: hidden;
            box-shadow: 0 8px 25px rgba(15, 23, 42, 0.15);
        }

        .summary-card::before {
            content: '';
            position: absolute;
            width: 150px;
            height: 150px;
            background: rgba(16, 185, 129, 0.1);
            border-radius: 50%;
            right: -30px;
            top: -30px;
        }

        .summary-card h3 {
            margin: 0;
            font-size: 14px;
            font-weight: 500;
            color: #10b981;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .summary-card .slot-name {
            font-size: 32px;
            font-weight: 800;
            margin: 10px 0;
            letter-spacing: 0.5px;
        }

        .summary-card .zone-name {
            font-size: 14px;
            color: #94a3b8;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .summary-details {
            display: flex;
            flex-direction: column;
            gap: 12px;
            margin-bottom: 20px;
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            font-size: 14px;
            color: var(--slate);
        }

        .detail-row span.label {
            font-weight: 500;
        }

        .detail-row span.val {
            font-weight: 600;
            color: var(--dark);
        }

        .total-bill-row {
            border-top: 1.5px dashed var(--border);
            padding-top: 15px;
            margin-top: 15px;
            display: flex;
            justify-content: space-between;
            font-size: 18px;
            font-weight: 700;
            color: var(--dark);
        }

        .total-bill-row .amount {
            color: var(--primary);
            font-size: 22px;
        }

        .secure-badge {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            font-size: 13px;
            color: #64748b;
            margin-top: 15px;
        }

        /* Success screen styles */
        .success-card {
            max-width: 500px;
            width: 100%;
            background: white;
            border-radius: 16px;
            box-shadow: var(--shadow);
            padding: 40px;
            text-align: center;
            border: 1px solid var(--border);
            animation: slideUp 0.5s ease-out;
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .success-icon {
            width: 80px;
            height: 80px;
            background-color: var(--primary-light);
            color: var(--primary);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 40px;
            margin: 0 auto 25px;
        }

        .success-card h2 {
            font-size: 26px;
            font-weight: 800;
            margin: 0 0 10px;
            color: var(--dark);
        }

        .success-card p {
            color: var(--slate);
            font-size: 15px;
            margin-bottom: 30px;
            line-height: 1.6;
        }

        .ticket-receipt {
            background-color: var(--light);
            border: 1.5px dashed var(--border);
            border-radius: 10px;
            padding: 20px;
            text-align: left;
            margin-bottom: 30px;
        }

        .ticket-receipt-row {
            display: flex;
            justify-content: space-between;
            font-size: 14px;
            margin-bottom: 10px;
        }

        .ticket-receipt-row:last-child {
            margin-bottom: 0;
            padding-top: 10px;
            border-top: 1px dashed var(--border);
            font-weight: 700;
        }

        .ticket-receipt-row span.label {
            color: var(--slate);
        }

        .ticket-receipt-row span.value {
            color: var(--dark);
        }

        .btn-dash {
            display: block;
            width: 100%;
            padding: 14px;
            background-color: var(--dark);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            transition: background-color 0.3s;
        }

        .btn-dash:hover {
            background-color: #1e293b;
        }

        @media (max-width: 768px) {
            .checkout-container {
                grid-template-columns: 1fr;
            }
            .checkout-summary-section {
                border-left: none;
                border-top: 1px solid var(--border);
            }
        }
    </style>
</head>
<body>

    <?php include 'includes/navbar.php'; ?>

    <div class="checkout-wrapper">
        <?php if ($payment_completed): ?>
            <!-- Stunning Success Receipt Card -->
            <div class="success-card">
                <div class="success-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <h2>Payment Successful!</h2>
                <p>Your reservation is officially confirmed. A professional PDF invoice and digital parking permit have been dispatched to your email address: <strong><?php echo htmlspecialchars($booking['email']); ?></strong>.</p>
                
                <div class="ticket-receipt">
                    <div class="ticket-receipt-row">
                        <span class="label">Permit Code</span>
                        <span class="value" style="font-weight:700;color:var(--primary);"><?php echo $booking['space_name']; ?></span>
                    </div>
                    <div class="ticket-receipt-row">
                        <span class="label">Transaction ID</span>
                        <span class="value" style="font-family:monospace;"><?php echo $transaction_id; ?></span>
                    </div>
                    <div class="ticket-receipt-row">
                        <span class="label">Location</span>
                        <span class="value"><?php echo htmlspecialchars($booking['location']); ?></span>
                    </div>
                    <div class="ticket-receipt-row">
                        <span class="label">Duration</span>
                        <span class="value"><?php echo $duration; ?> hours</span>
                    </div>
                    <div class="ticket-receipt-row">
                        <span class="label">Total Paid</span>
                        <span class="value" style="color:var(--primary); font-size:16px;">$<?php echo number_format($booking['total_price'], 2); ?></span>
                    </div>
                </div>

                <a href="user/dashboard.php" class="btn-dash"><i class="fas fa-desktop" style="margin-right:8px;"></i> Go to Dashboard</a>
            </div>
        <?php else: ?>
            <!-- Checkout Form & Details -->
            <div class="checkout-container">
                <div class="checkout-form-section">
                    <h2 class="section-title"><i class="fas fa-credit-card" style="color:var(--primary);"></i> Payment Details</h2>
                    
                    <?php if ($error): ?>
                        <div class="error-message" style="margin-bottom: 20px;"><?php echo $error; ?></div>
                    <?php endif; ?>
                    
                    <form action="payment.php?reservation_id=<?php echo $reservation_id; ?>" method="POST" id="paymentForm">
                        <input type="hidden" name="payment_method" value="Credit Card">
                        
                        <div class="form-group">
                            <label for="cardholder">Cardholder Name</label>
                            <input type="text" id="cardholder" name="cardholder" placeholder="John Doe" required>
                        </div>

                        <div class="form-group">
                            <label for="card_number">Card Number</label>
                            <input type="text" id="card_number" name="card_number" placeholder="xxxx xxxx xxxx xxxx" maxlength="19" required>
                            <div class="card-type-logos">
                                <i class="fab fa-cc-visa"></i>
                                <i class="fab fa-cc-mastercard"></i>
                                <i class="fab fa-cc-amex"></i>
                            </div>
                        </div>

                        <div class="input-row">
                            <div class="form-group">
                                <label for="expiry">Expiry Date</label>
                                <input type="text" id="expiry" name="expiry" placeholder="MM/YY" maxlength="5" required>
                            </div>
                            <div class="form-group">
                                <label for="cvv">CVV</label>
                                <input type="password" id="cvv" name="cvv" placeholder="•••" maxlength="3" required>
                            </div>
                        </div>

                        <button type="submit" name="pay_now" class="pay-btn">
                            <i class="fas fa-shield-alt"></i> Pay $<?php echo number_format($booking['total_price'], 2); ?> Now
                        </button>
                    </form>

                    <div class="secure-badge">
                        <i class="fas fa-lock" style="color:#64748b;"></i> Secure 256-bit SSL Encrypted Transaction
                    </div>
                </div>

                <div class="checkout-summary-section">
                    <div>
                        <h2 class="section-title"><i class="fas fa-shopping-bag" style="color:var(--primary);"></i> Reservation</h2>
                        
                        <div class="summary-card">
                            <h3>Assigned Slot</h3>
                            <div class="slot-name"><?php echo $booking['space_name']; ?></div>
                            <div class="zone-name"><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($booking['location']); ?></div>
                        </div>

                        <div class="summary-details">
                            <div class="detail-row">
                                <span class="label">Price per Hour</span>
                                <span class="val">$<?php echo number_format($booking['price_per_hour'], 2); ?></span>
                            </div>
                            <div class="detail-row">
                                <span class="label">Duration</span>
                                <span class="val"><?php echo $duration; ?> Hours</span>
                            </div>
                            <div class="detail-row">
                                <span class="label">Start Time</span>
                                <span class="val" style="font-size:12px;"><?php echo date('M d, Y h:i A', strtotime($booking['start_time'])); ?></span>
                            </div>
                            <div class="detail-row">
                                <span class="label">End Time</span>
                                <span class="val" style="font-size:12px;"><?php echo date('M d, Y h:i A', strtotime($booking['end_time'])); ?></span>
                            </div>
                        </div>
                    </div>

                    <div class="total-bill-row">
                        <span>Total Due:</span>
                        <span class="amount">$<?php echo number_format($booking['total_price'], 2); ?></span>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Real-time Credit Card formatting script -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const cardNumber = document.getElementById('card_number');
            const expiry = document.getElementById('expiry');
            const cvv = document.getElementById('cvv');

            // Format card number with spaces (xxxx xxxx xxxx xxxx)
            cardNumber.addEventListener('input', function(e) {
                let target = e.target;
                let position = target.selectionEnd;
                let length = target.value.length;
                
                let value = target.value.replace(/\D/g, '');
                let formatted = '';
                
                for (let i = 0; i < value.length; i++) {
                    if (i > 0 && i % 4 === 0) {
                        formatted += ' ';
                    }
                    formatted += value[i];
                }
                
                target.value = formatted;
                
                // Keep cursor positioned correctly
                if (position < length) {
                    target.setSelectionRange(position, position);
                }
            });

            // Format expiry date (MM/YY)
            expiry.addEventListener('input', function(e) {
                let target = e.target;
                let value = target.value.replace(/\D/g, '');
                let formatted = '';
                
                if (value.length > 0) {
                    formatted = value.substring(0, 2);
                    if (value.length > 2) {
                        formatted += '/' + value.substring(2, 4);
                    }
                }
                target.value = formatted;
            });

            // Filter CVV only digits
            cvv.addEventListener('input', function(e) {
                let target = e.target;
                target.value = target.value.replace(/\D/g, '');
            });
        });
    </script>
</body>
</html>
