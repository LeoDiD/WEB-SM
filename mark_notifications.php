<?php
require_once 'config/db.php';

header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception("Invalid request method.");
    }

    $data = json_decode(file_get_contents('php://input'), true);

    if (isset($data['mark_all']) && $data['mark_all'] === true) {
        $query = "UPDATE notifications SET status = 'read' WHERE status = 'unread'";
        $stmt = $conn->prepare($query);
        $stmt->execute();
        echo json_encode(["success" => true]);
    } else {
        $notificationId = $data['id'] ?? null;
        if (!$notificationId) {
            throw new Exception("Notification ID is required.");
        }

        $query = "UPDATE notifications SET status = 'read' WHERE id = :id";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':id', $notificationId, PDO::PARAM_INT);
        $stmt->execute();

        echo json_encode(["success" => true]);
    }
} catch (Exception $e) {
    echo json_encode(["success" => false, "error" => $e->getMessage()]);
}
?>
