<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products - EZ Mart</title>
    <link rel="stylesheet" href="../index.css">
    <link rel="stylesheet" href="../products/product.css">
    <link rel="stylesheet" href="../buttons/button.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
</head>
<body>
    <header class="header">
        <div class="logo-container">
            <img src="../images/ez-mart.svg" alt="EZ Mart Logo" class="ez_logo" id="ezLogo" style="width: 50px; height: auto;">
            <span class="logo-text">Mart</span>
        </div>
    </header>
    
    <main class="container">
        <h1>Products</h1>
        <!-- Filter Container -->
        <div class="filter-container">
            <label for="categoryFilter" class="filter-label">Category:</label>
            <select id="categoryFilter" class="filter-select">
                <option value="All">All</option>
                <option value="Snacks">Snacks</option>
                <option value="Pantry">Pantry</option>
                <option value="Beverage">Beverage</option>
                <option value="Bakery">Bakery</option>
            </select>
        </div>
        <!-- Product List -->
        <div id="productList" class="product-list">
            <!-- Product items will be dynamically added here -->
        </div>
        <!-- Add Product Button -->
        <div class="add-product-container">
            <button id="addProductBtn">Add Product</button>
        </div>
    </main>

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

    <!-- Modals -->
    <div id="addProductModal" class="modal">
        <div class="modal-content">
            <span id="closeAddProductModal" class="close">&times;</span>
            <h2>Add Product</h2>
            <form id="productForm">
                <div class="form-group">
                    <label for="name">Product Name</label>
                    <input type="text" id="name" placeholder="Enter product name" required>
                </div>
                <div class="form-group">
                    <label for="price">Price</label>
                    <input type="number" id="price" placeholder="Enter price" required>
                </div>
                <div class="form-group">
                    <label for="stock">Stock</label>
                    <input type="number" id="stock" placeholder="Enter stock" required>
                </div>
                <div class="form-group">
                    <label for="image">Image URL</label>
                    <input type="text" id="image" placeholder="Enter image URL" required>
                </div>
                <div class="form-group">
                    <label for="category">Category</label>
                    <select id="category" required>
                        <option value="Snacks">Snacks</option>
                        <option value="Pantry">Pantry</option>
                        <option value="Beverage">Beverage</option>
                        <option value="Bakery">Bakery</option>
                    </select>
                </div>
                <button type="submit" class="submit-btn">Add Product</button>
            </form>
        </div>
    </div>

    <div id="editModal" class="modal">
        <div class="modal-content">
            <span id="closeModal" class="close">&times;</span>
            <h2>Edit Product</h2>
            <form id="editForm">
                <input type="hidden" id="editProductId">
                <div class="form-group">
                    <label for="editName">Name</label>
                    <input type="text" id="editName" placeholder="Edit Name" required>
                </div>
                <div class="form-group">
                    <label for="editPrice">Price</label>
                    <input type="number" id="editPrice" placeholder="Enter price" required>
                </div>
                <div class="form-group">
                    <label for="editStock">Stock</label>
                    <input type="number" id="editStock" placeholder="Enter stock" required>
                </div>
                <div class="form-group">
                    <label for="editCategory">Category</label>
                    <select id="editCategory" required>
                        <option value="Snacks">Snacks</option>
                        <option value="Pantry">Pantry</option>
                        <option value="Beverage">Beverage</option>
                        <option value="Bakery">Bakery</option>
                    </select>
                </div>
                <button type="submit" class="submit-btn">Update Product</button>
            </form>
        </div>
    </div>

    <!-- Include product-specific JS -->
    <script src="product.js"></script>
    <script src="../buttons/button.js"></script>
</body>
</html>