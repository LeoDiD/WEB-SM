<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order - EZ Mart</title>
    
    <link rel="stylesheet" href="../index.css">
    <link rel="stylesheet" href="order.css">
    <link rel="stylesheet" href="../buttons/button.css">

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
</head>
<body>
    <header class="header">
        <img src="../images/ez-mart.svg" alt="EZ Mart Logo" class="ez_logo" id="ezLogo" style="width: 60px;">
        <span class="logo-text"> Mart</span>
    </header>
    
    <!-- Sidebar -->
    <div id="sidebar" class="sidebar">
        <ul>
            <li><a href="../index.php">Home</a></li>
            <li><a href="../products/product.php">Products</a></li>
            <li><a href="../order/order.php">Orders</a></li>
            <li><a href="../login/login.php">Log out</a></li>
        </ul>
    </div>
    
    <main class="container">
        <h1>Order Section</h1>
        <table class="order-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Order</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="orderTableBody">
                <!-- Orders will be dynamically inserted here -->
            </tbody>
        </table>
    </main>

    <!-- Order View Modal -->
    <div id="orderModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h2>Order Details</h2>
            <p id="orderDetails"></p>
        </div>
    </div>

    <!-- JavaScript Files -->
    <script src="../buttons/button.js"></script>
    <script src="order.js"></script>
</html>
