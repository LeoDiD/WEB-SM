<?php
require_once __DIR__ . '/../config/db.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    echo json_encode(["success" => false, "error" => "Database connection failed"]);
    exit;
}

// Log the request method and parameters
error_log("Request Method: " . $_SERVER['REQUEST_METHOD']);
error_log("POST Data: " . print_r($_POST, true));
error_log("GET Data: " . print_r($_GET, true));
error_log("Input Data: " . file_get_contents("php://input"));

// Handle OPTIONS preflight request for CORS
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $action = $_GET['action'] ?? '';
    error_log("Action: " . $action);

    switch ($action) {
        case 'fetch':
            fetchOrders($conn);
            break;
        default:
            echo json_encode(["success" => false, "error" => "Invalid action"]);
            break;
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
    if (!$data || !isset($data['action'])) {
        echo json_encode(["success" => false, "error" => "Missing action parameter"]);
        exit;
    }

    $action = $data['action'];
    error_log("Action: " . $action);

    switch ($action) {
        case 'confirm':
            confirmOrder($conn, $data);
            break;
        case 'delete':
            deleteOrder($conn, $data);
            break;
        case 'place':
            placeOrder($conn, $data);
            break;
        default:
            echo json_encode(["success" => false, "error" => "Invalid action"]);
            break;
    }
} else {
    echo json_encode(["success" => false, "error" => "Invalid request method"]);
}

// Fetch all orders
function fetchOrders($conn) {
    $query = "SELECT * FROM orders";
    $result = $conn->query($query);

    if (!$result) {
        echo json_encode(["success" => false, "error" => "Failed to fetch orders"]);
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
    $id = $data['id'] ?? 0;

    if (!$id) {
        echo json_encode(["success" => false, "error" => "Invalid order ID"]);
        exit;
    }

    $stmt = $conn->prepare("UPDATE orders SET status = 'Confirmed' WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["success" => false, "error" => "Failed to confirm order"]);
    }
}

// Delete an order
function deleteOrder($conn, $data) {
    $id = $data['id'] ?? 0;

    if (!$id) {
        echo json_encode(["success" => false, "error" => "Invalid order ID"]);
        exit;
    }

    $stmt = $conn->prepare("DELETE FROM orders WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["success" => false, "error" => "Failed to delete order"]);
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
            throw new Exception("Failed to place order");
        }

        $order_id = $stmt->insert_id;

        // Reduce stock for each product
        foreach ($items as $item) {
            $product_id = $item['id'];
            $quantity = $item['quantity'];

            $stmt = $conn->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
            $stmt->bind_param("ii", $quantity, $product_id);

            if (!$stmt->execute()) {
                throw new Exception("Failed to update product stock");
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