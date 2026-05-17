<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Get the current script path to determine the correct relative path
$current_path = $_SERVER['SCRIPT_NAME'];
$is_user_section = strpos($current_path, '/user/') !== false;
$base_path = $is_user_section ? '../' : '';
?>
<nav class="navbar">
    <div class="nav-container">
        <div class="nav-left">
            <a href="<?php echo $base_path; ?>index.php" class="logo">
                <i class="fas fa-parking"></i>
                <span>SmartPark</span>
            </a>
        </div>
        
        <div class="nav-center">
            <ul class="nav-links">
                <li><a href="<?php echo $base_path; ?>index.php" class="nav-link">
                    <i class="fas fa-home"></i>
                    <span>Home</span>
                </a></li>
                <li><a href="<?php echo $base_path; ?>index.php#parking" class="nav-link">
                    <i class="fas fa-car"></i>
                    <span>Parking</span>
                </a></li>
                <li><a href="<?php echo $base_path; ?>index.php#features" class="nav-link">
                    <i class="fas fa-star"></i>
                    <span>Features</span>
                </a></li>
                <?php if(isset($_SESSION['user_id'])): ?>
                    <li><a href="<?php echo $base_path; ?>user/dashboard.php" class="nav-link">
                        <i class="fas fa-tachometer-alt"></i>
                        <span>Dashboard</span>
                    </a></li>
                <?php endif; ?>
            </ul>
        </div>
        
        <div class="nav-right">
            <?php if(isset($_SESSION['user_id'])): ?>
                <div class="user-menu">
                    <div class="user-info">
                        <i class="fas fa-user-circle"></i>
                        <span class="user-name"><?php echo htmlspecialchars($_SESSION['full_name']); ?></span>
                    </div>
                    <div class="dropdown-menu">
                        <a href="<?php echo $base_path; ?>user/profile.php" class="dropdown-item">
                            <i class="fas fa-user"></i>
                            <span>Profile</span>
                        </a>
                        <a href="<?php echo $base_path; ?>user/reservations.php" class="dropdown-item">
                            <i class="fas fa-calendar-check"></i>
                            <span>My Reservations</span>
                        </a>
                        <a href="<?php echo $base_path; ?>logout.php" class="dropdown-item">
                            <i class="fas fa-sign-out-alt"></i>
                            <span>Logout</span>
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <div class="auth-links">
                    <a href="<?php echo $base_path; ?>login.php" class="auth-btn login-btn">
                        <i class="fas fa-sign-in-alt"></i>
                        <span>Login</span>
                    </a>
                    <a href="<?php echo $base_path; ?>register.php" class="auth-btn register-btn">
                        <i class="fas fa-user-plus"></i>
                        <span>Register</span>
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</nav>

<style>
.navbar {
    background: #ffffff;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    z-index: 1000;
}

.nav-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0.8rem 1.5rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.nav-left .logo {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    text-decoration: none;
    color: #4CAF50;
    font-size: 1.5rem;
    font-weight: bold;
}

.nav-left .logo i {
    font-size: 1.8rem;
}

.nav-center .nav-links {
    display: flex;
    gap: 2rem;
    list-style: none;
    margin: 0;
    padding: 0;
}

.nav-link {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    text-decoration: none;
    color: #333;
    font-weight: 500;
    padding: 0.5rem;
    border-radius: 5px;
    transition: all 0.3s ease;
}

.nav-link:hover {
    color: #4CAF50;
    background: rgba(76, 175, 80, 0.1);
}

.nav-link i {
    font-size: 1.1rem;
}

.auth-links {
    display: flex;
    gap: 1rem;
}

.auth-btn {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    border-radius: 5px;
    text-decoration: none;
    font-weight: 500;
    transition: all 0.3s ease;
}

.login-btn {
    color: #4CAF50;
    background: rgba(76, 175, 80, 0.1);
}

.register-btn {
    color: white;
    background: #4CAF50;
}

.login-btn:hover {
    background: rgba(76, 175, 80, 0.2);
}

.register-btn:hover {
    background: #388E3C;
}

.user-menu {
    position: relative;
}

.user-info {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem;
    cursor: pointer;
    border-radius: 5px;
    transition: all 0.3s ease;
}

.user-info:hover {
    background: rgba(0, 0, 0, 0.05);
}

.user-info i {
    font-size: 1.5rem;
    color: #4CAF50;
}

.user-name {
    font-weight: 500;
    color: #333;
}

.dropdown-menu {
    position: absolute;
    top: 100%;
    right: 0;
    background: white;
    border-radius: 5px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    padding: 0.5rem;
    min-width: 200px;
    display: none;
}

.user-menu:hover .dropdown-menu {
    display: block;
}

.dropdown-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1rem;
    text-decoration: none;
    color: #333;
    border-radius: 5px;
    transition: all 0.3s ease;
}

.dropdown-item:hover {
    background: rgba(76, 175, 80, 0.1);
    color: #4CAF50;
}

.dropdown-item i {
    width: 20px;
    text-align: center;
}

@media (max-width: 768px) {
    .nav-container {
        padding: 0.8rem 1rem;
    }
    
    .nav-center .nav-links {
        gap: 1rem;
    }
    
    .nav-link span,
    .auth-btn span,
    .user-name {
        display: none;
    }
    
    .nav-link i,
    .auth-btn i,
    .user-info i {
        font-size: 1.2rem;
    }
}
</style> 