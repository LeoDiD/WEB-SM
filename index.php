<?php
// Strict session handling
declare(strict_types=1);
session_start();

// Security headers
header("Cache-Control: no-cache, must-revalidate");
header("X-Content-Type-Options: nosniff");
header("X-Frame-Options: DENY");

// Validate admin session
if (empty($_SESSION['admin_id']) || empty($_SESSION['admin_username'])) {
    header("Location: /WEB-SM/admin/admin_login.php");
    exit();
}

// Secure session variables
$user_id = (int)$_SESSION['admin_id'];
$username = htmlspecialchars(trim($_SESSION['admin_username']), ENT_QUOTES, 'UTF-8');
$user_role = !empty($_SESSION['admin_role']) 
    ? htmlspecialchars(ucfirst(strtolower(trim($_SESSION['admin_role']))), ENT_QUOTES, 'UTF-8')
    : 'Administrator';
    
// Get first name for display
$display_name = current(explode(' ', $username));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EZ Mart</title>
    
    <link rel="stylesheet" href="./assets/css/index.css">
    <link rel="stylesheet" href="./assets/css/sidebar.css">
    
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="logo-container">
            <img src="./assets/images/ez-mart.svg" alt="EZ Mart Logo" class="ez_logo" id="ezLogo" style="width: 50px; height: auto;">
            <span class="logo-text">Mart</span>
        </div>

<!-- User Profile and Notification Section -->
<div class="user-profile-notification">
    <!-- Notification Bell -->
    <div class="notification-container">
        <img src="./assets/icons/bell.svg" alt="Notifications" class="bell" id="bell">
        
        <!-- Notification Center -->
        <div id="notification-center">
            <h2>Notifications</h2>
            <div id="notification-container">
                <ul id="notification-list">
                    <li>No new notifications</li>
                </ul>
                <button id="mark-all-read">Mark All as Read</button>
            </div>
        </div>
    </div>

    <!-- User Profile Section -->
    <div class="user-container" id="userContainer">
        <div class="user-avatar-container">
            <img src="./assets/images/user_profile.png" alt="User Profile" class="user-profile">
            <div class="user-status-indicator"></div>
        </div>
        
        <div class="user-profile-info">
            <div class="user-greeting">Hello, <span class="user-display-name"><?php echo $display_name; ?></span></div>
            <div class="user-role-badge"><?php echo $user_role; ?></div>
        </div>
        
        <i class="fa-solid fa-chevron-down dropdown-icon" id="dropdownIcon"></i>

        <!-- Enhanced Dropdown Menu -->
        <div class="user-dropdown" id="userDropdown">
            <div class="dropdown-header">
                <div class="dropdown-avatar">
                    <img src="./assets/images/user_profile.png" alt="Profile">
                </div>
                <div class="dropdown-user-info">
                    <div class="dropdown-username"><?php echo $username; ?></div>
                    <div class="dropdown-useremail"><?php echo $user_role; ?> Account</div>
                </div>
            </div>
            
            <ul class="dropdown-menu">
                <li>
                    <a href="./user-profile/user_info.php">
                        <i class="fa-solid fa-user-pen"></i>
                        <span>My Profile</span>
                    </a>
                </li>
                <li>
                    <a href="#">
                        <i class="fa-solid fa-sliders"></i>
                        <span>Settings</span>
                    </a>
                </li>
                <li>
                    <a href="/WEB-SM/auth/logout.php" class="logout-btn">
                        <i class="fa-solid fa-arrow-right-from-bracket"></i>
                        <span>Sign Out</span>
                    </a>
                </li>
            </ul>
        </div>
    </div>
</div>

    </header>

    <!-- Main Content -->
    <main class="container">
        <h1 id="dashBoard">Dashboard</h1>

        <!-- Counter Container -->
        <div class="counter-container">
            <!-- Total Products Count -->
            <div class="counter-box">
                <label>Total Products:</label>
                <div class="counter-icon">
                    <img src="./assets/icons/product-icon-counter.png" alt="Product Icon">
                    <span id="totalProductCount">0</span>
                </div>
            </div>

            <!-- Order Counter -->
            <div class="counter-box">
                <label>Total Orders:</label>
                <div class="counter-icon">
                    <img src="./assets/icons/order-icon-counter.png" alt="Order Icon">
                    <span id="totalOrderCount">0</span>
                </div>
            </div>

            <!-- Total Customer Counter -->
            <div class="counter-box">
                <label>Total Customers:</label>
                <div class="counter-icon">
                    <img src="./assets/icons/customer-icon-counter.png" alt="Customer Icon">
                    <span id="totalCustomerCount">0</span>
                </div>
            </div>
        </div>

        <!-- Product List -->
        <div id="productList" class="product-list">
            <!-- Product items will be dynamically added here -->
        </div>
    </main>

    <!-- Sidebar -->
    <div id="sidebar" class="sidebar">
        <ul>
            <li>
                <a href="./index.php" title="Home">
                    <img src="./assets/icons/home-icon.png" alt="Home" id="sidebar-icon" style="width: 27px; height: 27px;">
                </a>
            </li>
            <li>
                <a href="./modules/products/product.html" title="Products">
                    <img src="./assets/icons/product.png" alt="Products" id="sidebar-icon" style="width: 24px; height: 24px;">
                </a>
            </li>
            <li>
                <a href="./modules/orders/order.html" title="Orders">
                    <img src="./assets/icons/order.png" alt="Orders" id="sidebar-icon" style="width: 27px; height: 27px;">
                </a>
            </li>
            <li>
                <a href="./modules/customers/customer.html" title="Customers">
                    <img src="./assets/icons/customer.png" alt="Customer" id="sidebar-icon" style="width: 29px; height: 29px;">
                </a>
            </li>
            <li>
                <a href="./modules/users/user-setting.html" title="User Setting">
                    <img src="./assets/icons/user-settings.png" alt="User-Settings" id="sidebar-icon" style="width: 30px; height: 30px;">
                </a>
            </li>
            <li>
                <a href="./auth/logout.php" title="Log out">
                    <img src="./assets/icons/logout.png" alt="Log out" id="sidebar-icon" style="width: 26px; height: 26px;">
                </a>
            </li>
        </ul>
    </div>

    <!-- Chart Container -->
    <div class="chart-container" style="height: 400px; width: 400px;">
        <canvas id="myChart" class="chart"></canvas>
        <canvas id="customerChart" class="chart"></canvas>
    </div>

    <!-- Include external JavaScript files -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="./js/chart.js"></script>
    <script src="./js/index.js"></script>
    <script src="./js/user.js"></script>
    <script src="./js/bell.js"></script>
</body>
</html>