<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smart Parking System</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <main>
        <section id="home" class="hero">
            <div class="hero-content">
                <h1>Smart Parking Solution</h1>
                <p>Find and reserve parking spaces in real-time with our smart parking system</p>
                <button class="cta-button" onclick="window.location.href='all_spaces.php'">View All Spaces</button>
            </div>
        </section>

        <section id="features" class="features-section">
            <h2>Smart Parking Features</h2>
            <div class="features-grid">
                <div class="feature-card">
                    <i class="fas fa-mobile-alt"></i>
                    <h3>Mobile Booking</h3>
                    <p>Reserve parking spaces on-the-go with our mobile-friendly interface</p>
                </div>
                <div class="feature-card">
                    <i class="fas fa-clock"></i>
                    <h3>Real-time Availability</h3>
                    <p>Get up-to-date information on parking space availability</p>
                </div>
                <div class="feature-card">
                    <i class="fas fa-credit-card"></i>
                    <h3>Secure Payments</h3>
                    <p>Pay securely online through various payment methods</p>
                </div>
                <div class="feature-card">
                    <i class="fas fa-history"></i>
                    <h3>Booking History</h3>
                    <p>Track all your past and upcoming reservations in one place</p>
                </div>
            </div>
        </section>
    </main>

    <?php include 'includes/footer.php'; ?>
</body>
</html> 