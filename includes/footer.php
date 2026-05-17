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
                <li><a href="all_spaces.php">Parking</a></li>
                <li><a href="#features">Features</a></li>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <li><a href="user/dashboard.php">Dashboard</a></li>
                <?php else: ?>
                    <li><a href="login.php">Login</a></li>
                    <li><a href="register.php">Register</a></li>
                <?php endif; ?>
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
        <p>&copy; <?php echo date('Y'); ?> SmartPark. All rights reserved.</p>
    </div>
</footer>

<style>
    footer {
        background-color: #333;
        color: #fff;
        padding: 3rem 0 1rem;
        margin-top: 3rem;
    }

    .footer-content {
        max-width: 1200px;
        margin: 0 auto;
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 2rem;
        padding: 0 1rem;
    }

    .footer-section h3 {
        color: #4CAF50;
        margin-bottom: 1rem;
        font-size: 1.2rem;
    }

    .footer-section p {
        line-height: 1.6;
        color: #ccc;
    }

    .footer-section ul {
        list-style: none;
        padding: 0;
    }

    .footer-section ul li {
        margin-bottom: 0.5rem;
    }

    .footer-section ul li a {
        color: #ccc;
        text-decoration: none;
        transition: color 0.3s;
    }

    .footer-section ul li a:hover {
        color: #4CAF50;
    }

    .social-icons {
        display: flex;
        gap: 1rem;
    }

    .social-icons a {
        color: #fff;
        font-size: 1.5rem;
        transition: color 0.3s;
    }

    .social-icons a:hover {
        color: #4CAF50;
    }

    .footer-bottom {
        text-align: center;
        margin-top: 2rem;
        padding-top: 1rem;
        border-top: 1px solid #555;
    }

    .footer-bottom p {
        color: #ccc;
        font-size: 0.9rem;
    }

    @media (max-width: 768px) {
        .footer-content {
            grid-template-columns: 1fr;
            text-align: center;
        }

        .social-icons {
            justify-content: center;
        }
    }
</style> 