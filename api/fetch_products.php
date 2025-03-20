<?php
require_once __DIR__ . '/../config/db.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

$server_url = "http://192.168.100.15/WEB-SM/";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(["success" => false, "error" => "Database connection failed"]);
    exit;
}

// Handle OPTIONS preflight request for CORS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// FETCH products with optional category filter
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $action = $_GET['action'] ?? '';

    if ($action === 'getTotalProducts') {
        getTotalProducts($conn);
        exit;
    }

    $category = isset($_GET['category']) ? $conn->real_escape_string($_GET['category']) : null;


    if ($category) {
        // If category is provided, filter products by category
        $stmt = $conn->prepare("SELECT id, name, price, stock, image, category FROM products WHERE category = ?");
        $stmt->bind_param("s", $category);
    } else {
        // If no category is provided, return all products
        $stmt = $conn->prepare("SELECT id, name, price, stock, image, category FROM products");
    }

    $stmt->execute();
    $result = $stmt->get_result();
    $products = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    echo json_encode(["success" => true, "products" => $products]);
    exit;
}

// HANDLE POST (Add/Update/Delete product)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
    $action = $data['action'] ?? '';

    // Add product
    if ($action === 'add') {
        if (isset($data['name'], $data['price'], $data['stock'], $data['image'], $data['category'])) {
            $stmt = $conn->prepare("INSERT INTO products (name, price, stock, image, category) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sdiss", $data['name'], $data['price'], $data['stock'], $data['image'], $data['category']);
            if ($stmt->execute()) {
                echo json_encode(["success" => true]);
            } else {
                echo json_encode(["success" => false, "error" => $stmt->error]);
            }
            $stmt->close();
        } else {
            echo json_encode(["success" => false, "error" => "Missing required fields"]);
        }
        exit;
    }

    // Update product
    if ($action === 'update') {
        if (isset($data['id'], $data['price'], $data['stock'], $data['category'])) {
            $stmt = $conn->prepare("UPDATE products SET price = ?, stock = ?, category = ? WHERE id = ?");
            $stmt->bind_param("dssi", $data['price'], $data['stock'], $data['category'], $data['id']);
            if ($stmt->execute()) {
                echo json_encode(["success" => true]);
            } else {
                echo json_encode(["success" => false, "error" => $stmt->error]);
            }
            $stmt->close();
        } else {
            echo json_encode(["success" => false, "error" => "Missing required fields for update"]);
        }
        exit;
    }

    // Delete product
    if ($action === 'delete') {
        if (isset($data['id'])) {
            $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
            $stmt->bind_param("i", $data['id']);
            if ($stmt->execute()) {
                echo json_encode(["success" => true]);
            } else {
                echo json_encode(["success" => false, "error" => $stmt->error]);
            }
            $stmt->close();
        } else {
            echo json_encode(["success" => false, "error" => "Missing product ID"]);
        }
        exit;
    }

    echo json_encode(["success" => false, "error" => "Invalid action"]);
    exit;
}

    // Get total number of products
    function getTotalProducts($conn) {
        $query = "SELECT COUNT(*) as totalProducts FROM products";
        $result = $conn->query($query);
        
        if ($result) {
            $row = $result->fetch_assoc();
            echo json_encode(["totalProducts" => (int)$row["totalProducts"]]);
        } else {
            echo json_encode(["success" => false, "error" => "Failed to fetch total products"]);
        }
    }

http_response_code(400);
echo json_encode(["success" => false, "error" => "Invalid request"]);
?>
