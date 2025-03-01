<?php
require '../order/db.php'; // Ensure the correct path to your db.php file

// Set content type to JSON
header('Content-Type: application/json');

try {
    // Execute query to count total orders
    $stmt = $conn->query("SELECT     COUNT(*) AS total FROM orders");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    // Ensure a valid response
    if ($result) {
        echo json_encode(["total_order" => (int)$result['total']]); // Fixed key name
    } else {
        echo json_encode(["error" => "Failed to fetch order count"]);
    }
} catch (PDOException $e) {
    echo json_encode(["error" => "Database error: " . $e->getMessage()]);
}
?>