<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customers - EZ Mart</title>
    <link rel="stylesheet" href="../customers/customer.css">
    <link rel="stylesheet" href="../buttons/button.css">

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="customer.js"></script>
</head>
<body>
    <header class="header">
        <img src="../images/ez-mart.svg" alt="EZ Mart Logo" class="ez_logo" id="ezLogo" style="width: 50px;">
        <span class="logo-text">Mart</span>
    </header>

    <!-- Sidebar -->
    <div id="sidebar" class="sidebar">
        <ul>
            <li>
                <a href="../index.php" title="Home">
                    <img src="../icons/home-icon.png" alt="Home" id="sidebar-icon" style="width: 27px; height: 27px;">
                </a>
            </li>
            <li>
                <a href="../products/product.php" title="Products">
                    <img src="../icons/product.png" alt="Products" id="sidebar-icon" style="width: 24px; height: 24px;">
                </a>
            </li>
            <li>
                <a href="../order/order.php" title="Orders">
                    <img src="../icons/order.png" alt="Orders" id="sidebar-icon" style="width: 27px; height: 27px;">
                </a>
            </li>
            <li>
                <a href="../customers/customer.php" title="Customers">
                    <img src="../icons/customer.png" alt="Customer" id="sidebar-icon" style="width: 29px; height: 29px;">
                </a>
            </li>
            <li>
                <a href="../user_accounts/user-accounts.php" title="User Accounts">
                    <img src="../icons/user-settings.png" alt="User-Settings" id="sidebar-icon" style="width: 30px; height: 30px;">
                </a>
            </li>
            <li>
                <a href="../login/login.php" title="Log out">
                    <img src="../icons/logout.png" alt="Log out" id="sidebar-icon" style="width: 26px; height: 26px;">
                </a>
            </li>
        </ul>
    </div>

    <div class="main-content">
    <div class="header-action">
        <h1>Customers</h1>
        <div class="action-container">
            <input type="text" id="searchCustomer" placeholder="Search by customer name..." class="search-input">
            <button id="addCustomerBtn" class="button-primary">Add Customer</button>
        </div>
    </div>
    <div id="customerList" class="customer-list"></div>
</div>


    <!-- Modal for Adding Customer -->
    <div id="addCustomerModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
                <h2>Add New Customer</h2>
            <form id="addCustomerForm">
                <!-- Customer Name Field -->
                <div class="form-group">
                    <label for="name">Customer Name:</label>
                    <input type="text" id="name" placeholder="Enter customer name" required>
                </div>

                <!-- Product Field -->
                <div class="form-group">
                    <label for="product">Product:</label>
                    <input type="text" id="product" placeholder="Enter product name" required>
                </div>

                <!-- Total Price Field -->
                <div class="form-group">
                    <label for="total_price">Total Price:</label>
                    <input type="number" id="total_price" placeholder="Enter total price" required>
                </div>
                <button type="submit" class="button-primary">Add Customer</button>
            </form>
        </div>
    </div>
</body>
</html>