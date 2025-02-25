<?php
require_once "db.php"; // Ensure database connection is included

header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

$action = $_POST['action'] ?? $_GET['action'] ?? '';

if ($action === "fetch") {
    try {
        $stmt = $conn->prepare("SELECT id, customer_name, order_details, status FROM orders ORDER BY id DESC");
        $stmt->execute();
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    } catch (PDOException $e) {
        echo json_encode(["success" => false, "error" => $e->getMessage()]);
    }
} elseif ($action === "confirm") {
    if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
        echo json_encode(["success" => false, "error" => "Invalid or missing order ID"]);
        exit();
    }

    $id = intval($_POST['id']);

    try {
        $stmt = $conn->prepare("UPDATE orders SET status = 'Confirmed' WHERE id = ?");
        if ($stmt->execute([$id])) {
            echo json_encode(["success" => true]);
        } else {
            echo json_encode(["success" => false, "error" => "Failed to confirm order"]);
        }
    } catch (PDOException $e) {
        echo json_encode(["success" => false, "error" => $e->getMessage()]);
    }
} elseif ($action === "delete") {
    if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
        echo json_encode(["success" => false, "error" => "Invalid or missing order ID"]);
        exit();
    }

    $id = intval($_POST['id']);

    try {
        $stmt = $conn->prepare("DELETE FROM orders WHERE id = ?");
        if ($stmt->execute([$id])) {
            echo json_encode(["success" => true]);
        } else {
            echo json_encode(["success" => false, "error" => "Failed to delete order"]);
        }
    } catch (PDOException $e) {
        echo json_encode(["success" => false, "error" => $e->getMessage()]);
    }
} elseif ($action === "getOrderStats") {
    // 📌 Get order count per status (Pending, Confirmed, etc.)
    try {
        $stmt = $conn->prepare("SELECT status, COUNT(*) AS total_orders FROM orders GROUP BY status");
        $stmt->execute();
        
        $data = ["labels" => [], "values" => []];

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $data['labels'][] = $row['status']; // Status: Pending, Confirmed
            $data['values'][] = $row['total_orders']; // Order count
        }

        echo json_encode($data);
    } catch (PDOException $e) {
        echo json_encode(["success" => false, "error" => $e->getMessage()]);
    }
} elseif ($action === "customerOrderStats") {
    // 📌 Get total orders per customer
    try {
        $stmt = $conn->prepare("SELECT customer_name, COUNT(*) AS total_orders FROM orders GROUP BY customer_name ORDER BY total_orders DESC");
        $stmt->execute();

        $data = ["labels" => [], "values" => []];

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $data['labels'][] = $row['customer_name'];
            $data['values'][] = $row['total_orders'];
        }

        echo json_encode($data);
    } catch (PDOException $e) {
        echo json_encode(["success" => false, "error" => $e->getMessage()]);
    }
} else {
    echo json_encode(["success" => false, "error" => "Invalid action"]);
}
?>
