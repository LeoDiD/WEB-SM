<?php
// Include Composer autoloader
require __DIR__ . '/../get_fcm_token.php';
require __DIR__ . '../../vendor/autoload.php';
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
    } elseif ($action === 'getTotalOrders') {
        getTotalOrders($conn);
    } elseif ($action === 'customerOrderStats') {
        getCustomerOrderStats($conn);
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
        case 'update_status':
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

    // Fetch the FCM token for the order
    $stmt = $conn->prepare("SELECT fcm_token FROM orders WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $order = $result->fetch_assoc();

    if (!$order || empty($order['fcm_token'])) {
        echo json_encode(["success" => false, "error" => "FCM token not found for this order"]);
        exit;
    }

    $fcm_token = $order['fcm_token'];

    // Update the order status
    $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $id);

    if ($stmt->execute()) {
        // Send notification to the customer
        $notification_title = "Order Status Updated";
        $notification_body = "Your order status has been updated to: " . $status;
        $notification_sent = sendNotification($fcm_token, $notification_title, $notification_body);

        if ($notification_sent) {
            echo json_encode(["success" => true]);
        } else {
            echo json_encode(["success" => false, "error" => "Failed to send notification"]);
        }
    } else {
        echo json_encode(["success" => false, "error" => "Failed to update order status"]);
    }
}

// Place a new order
function placeOrder($conn, $data) {
    ob_start(); 

    error_log("Received data: " . print_r($data, true));

    if (!isset($data['customer_name'], $data['total_price'], $data['items'], $data['fcm_token'])) {
        error_log("Missing required fields");
        ob_end_clean();
        echo json_encode(["success" => false, "error" => "Missing required fields"]);
        exit;
    }

    $customer_name = $conn->real_escape_string($data['customer_name']);
    $total_price = $data['total_price'];
    $items = $data['items'];
    $fcm_token = $data['fcm_token'];
    $status = 'Pending';

    // Start transaction
    $conn->begin_transaction();

    try {
        // Insert the order
        $stmt = $conn->prepare("INSERT INTO orders (customer_name, order_details, total_price, status, fcm_token) VALUES (?, ?, ?, ?, ?)");
        $order_details = json_encode($items);
        $stmt->bind_param("ssdss", $customer_name, $order_details, $total_price, $status, $fcm_token);

        if (!$stmt->execute()) {
            throw new Exception("Failed to place order: " . $stmt->error);
        }

        $order_id = $stmt->insert_id;
        error_log("Order placed successfully. Order ID: " . $order_id);

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

        // Insert notification
        $notification_message = "New order placed by $customer_name. Order ID: $order_id.";
        $stmt = $conn->prepare("INSERT INTO notifications (message, status) VALUES (?, 'unread')");
        $stmt->bind_param("s", $notification_message);

        if (!$stmt->execute()) {
            throw new Exception("Failed to insert notification: " . $stmt->error);
        }

        // Commit transaction
        $conn->commit();

        // Send notification to the customer
        $notification_title = "Order Placed";
        $notification_body = "Your order has been placed successfully. Order ID: $order_id.";
        error_log("Sending notification to FCM token: " . $fcm_token); // Log the FCM token
        $notification_sent = sendNotification($fcm_token, $notification_title, $notification_body);

        if ($notification_sent) {
            ob_end_clean(); // Clear buffer
            echo json_encode(["success" => true, "order_id" => $order_id]);
        } else {
            ob_end_clean(); // Clear buffer
            echo json_encode(["success" => false, "error" => "Failed to send notification"]);
        }
    } catch (Exception $e) {
        // Rollback transaction
        $conn->rollback();
        error_log("Error in placeOrder: " . $e->getMessage());
        http_response_code(500); // Set HTTP status code to 500
        ob_end_clean(); // Clear buffer
        echo json_encode(["success" => false, "error" => $e->getMessage()]);
    }
}

function sendNotification($deviceToken, $title, $body) {
    $fcmUrl = "https://fcm.googleapis.com/v1/projects/ezmart-f178a/messages:send";
    $accessToken = getOAuthToken();

    // Log the access token for debugging
    error_log("Access Token: " . $accessToken);

    if (strpos($accessToken, 'Error:') === 0) {
        error_log("Error fetching OAuth token: " . $accessToken);
        return false;
    }

    $notificationData = [
        "message" => [
            "token" => $deviceToken,
            "notification" => [
                "title" => $title,
                "body" => $body
            ],
            "data" => [
                "orderStatus" => "Pending",
                "timestamp" => date("Y-m-d H:i:s")
            ]
        ]
    ];

    $headers = [
        "Authorization: Bearer $accessToken",
        "Content-Type: application/json"
    ];

    // Log the FCM request for debugging
    error_log("FCM Request: " . json_encode([
        "url" => $fcmUrl,
        "headers" => $headers,
        "body" => $notificationData
    ]));

    $client = new GuzzleHttp\Client();
    try {
        $response = $client->post($fcmUrl, [
            'headers' => $headers,
            'json' => $notificationData
        ]);

        $responseBody = json_decode($response->getBody(), true);
        error_log("FCM Response: " . json_encode($responseBody)); // Log the FCM response

        if (isset($responseBody['name'])) {
            error_log("Notification sent successfully: " . $responseBody['name']);
            return true;
        } else {
            error_log("FCM API returned an error: " . json_encode($responseBody));
            return false;
        }
    } catch (Exception $e) {
        error_log("Error sending notification: " . $e->getMessage());
        return false;
    }
}

// Get total number of orders
function getTotalOrders($conn) {
    $query = "SELECT COUNT(*) as total_orders FROM orders";
    $result = $conn->query($query);
    
    if ($result) {
        $row = $result->fetch_assoc();
        echo json_encode(["total_orders" => (int)$row["total_orders"]]);
    } else {
        echo json_encode(["success" => false, "error" => "Failed to fetch total orders"]);
    }
}

// Get customer order statistics
function getCustomerOrderStats($conn) {
    $query = "SELECT customer_name, COUNT(*) as order_count FROM orders GROUP BY customer_name";
    $result = $conn->query($query);

    if ($result) {
        $labels = [];
        $values = [];

        while ($row = $result->fetch_assoc()) {
            $labels[] = $row["customer_name"];
            $values[] = (int)$row["order_count"];
        }

        echo json_encode(["labels" => $labels, "values" => $values]);
    } else {
        echo json_encode(["success" => false, "error" => "Failed to fetch customer order stats"]);
    }
}

$conn->close();
?>