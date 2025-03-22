<?php
// fetch_notifications.php
require_once __DIR__ . '/../config/db.php';

header('Content-Type: application/json');

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    echo json_encode(["success" => false, "error" => "Database connection failed"]);
    exit;
}

$query = "SELECT * FROM notifications WHERE status = 'unread' ORDER BY id DESC";
$result = $conn->query($query);

$notifications = [];
while ($row = $result->fetch_assoc()) {
    $notifications[] = $row;
}

echo json_encode(["success" => true, "notifications" => $notifications]);

$conn->close();
?>
