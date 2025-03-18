<?php
require_once __DIR__ . '/../config/db.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    echo json_encode(["success" => false, "error" => "Database connection failed: " . $conn->connect_error]);
    exit;
}

// Log the request method and parameters
error_log("Request Method: " . $_SERVER['REQUEST_METHOD']);
error_log("POST Data: " . print_r($_POST, true));
error_log("GET Data: " . print_r($_GET, true));
error_log("Input Data: " . file_get_contents("php://input"));

// Handle OPTIONS preflight request for CORS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Handle GET requests (e.g., fetch orders)
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $action = $_GET['action'] ?? '';
    error_log("Action: " . $action);

    if ($action === 'fetch') {
        fetchOrders($conn);
    } else {
        echo json_encode(["success" => false, "error" => "Invalid action for GET request"]);
    }
}

// Handle POST requests (e.g., confirm, delete, place, update_status)
elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
    if (!$data || !isset($data['action'])) {
        echo json_encode(["success" => false, "error" => "Missing action parameter"]);
        exit;
    }
    
    $action = $data['action'];
    error_log("Action: " . $action);
    
    switch ($action) {
        case 'fetch':
            fetchOrders($conn);
            break;
        case 'confirm':
            confirmOrder($conn, $data);
            break;
        case 'delete':
            deleteOrder($conn, $data);
            break;
        case 'place':
            placeOrder($conn, $data);
            break;
        case 'update_status': // Handle update_status action
            updateOrderStatus($conn, $data);
            break;
        default:
            echo json_encode(["success" => false, "error" => "Invalid action"]);
            break;
    }
} else {
    // Invalid request method
    echo json_encode(["success" => false, "error" => "Invalid request method"]);
}

// Fetch all orders
function fetchOrders($conn) {
    $query = "SELECT * FROM orders";
    $result = $conn->query($query);

    if (!$result) {
        echo json_encode(["success" => false, "error" => "Failed to fetch orders: " . $conn->error]);
        exit;
    }

    $orders = [];
    while ($row = $result->fetch_assoc()) {
        $orders[] = $row;
    }

    echo json_encode(["success" => true, "data" => $orders]);
}

// Confirm an order
function confirmOrder($conn, $data) {
    if (!isset($data['id'])) {
        echo json_encode(["success" => false, "error" => "Missing order ID"]);
        exit;
    }

    $id = $data['id'];
    $stmt = $conn->prepare("UPDATE orders SET status = 'Confirmed' WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["success" => false, "error" => "Failed to confirm order: " . $stmt->error]);
    }
}

// Delete an order
function deleteOrder($conn, $data) {
    if (!isset($data['id'])) {
        echo json_encode(["success" => false, "error" => "Missing order ID"]);
        exit;
    }

    $id = $data['id'];
    $stmt = $conn->prepare("DELETE FROM orders WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["success" => false, "error" => "Failed to delete order: " . $stmt->error]);
    }
}

// Update order status
function updateOrderStatus($conn, $data) {
    if (!isset($data['id'], $data['status'])) {
        echo json_encode(["success" => false, "error" => "Missing required fields"]);
        exit;
    }

    $id = $data['id'];
    $status = $data['status'];

    // Validate status
    $allowedStatuses = ['Pending', 'Processing', 'Completed', 'Canceled'];
    if (!in_array($status, $allowedStatuses)) {
        echo json_encode(["success" => false, "error" => "Invalid status"]);
        exit;
    }

    // Update the order status
    $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $id);

    if ($stmt->execute()) {
        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["success" => false, "error" => "Failed to update order status"]);
    }
}

// Place a new order
function placeOrder($conn, $data) {
    if (!isset($data['customer_name'], $data['total_price'], $data['items'])) {
        echo json_encode(["success" => false, "error" => "Missing required fields"]);
        exit;
    }

    $customer_name = $conn->real_escape_string($data['customer_name']);
    $total_price = $data['total_price'];
    $items = $data['items'];
    $status = 'Pending'; // Default status

    // Start transaction
    $conn->begin_transaction();

    try {
        // Insert the order
        $stmt = $conn->prepare("INSERT INTO orders (customer_name, order_details, total_price, status) VALUES (?, ?, ?, ?)");
        $order_details = json_encode($items);
        $stmt->bind_param("ssds", $customer_name, $order_details, $total_price, $status);

        if (!$stmt->execute()) {
            throw new Exception("Failed to place order: " . $stmt->error);
        }

        $order_id = $stmt->insert_id;

        // Reduce stock for each product
        foreach ($items as $item) {
            $product_id = $item['id'];
            $quantity = $item['quantity'];

            $stmt = $conn->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
            $stmt->bind_param("ii", $quantity, $product_id);

            if (!$stmt->execute()) {
                throw new Exception("Failed to update product stock: " . $stmt->error);
            }
        }

        // Commit transaction
        $conn->commit();

        echo json_encode(["success" => true, "order_id" => $order_id]);
    } catch (Exception $e) {
        // Rollback transaction
        $conn->rollback();
        echo json_encode(["success" => false, "error" => $e->getMessage()]);
    }
}

$conn->close();
?>