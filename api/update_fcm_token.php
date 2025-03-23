<?php
ini_set('display_errors', 0);
error_reporting(0);

require_once __DIR__ . '/../config/db.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Connect to the database
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    echo json_encode(["success" => false, "error" => "Database connection failed: " . $conn->connect_error]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $data = json_decode(file_get_contents("php://input"), true);

    if (!isset($data['token'])) {
        echo json_encode(["success" => false, "error" => "Missing FCM token"]);
        exit;
    }

    $fcm_token = $data['token'];

    if (strlen($fcm_token) < 10) {
        echo json_encode(["success" => false, "error" => "Invalid FCM token"]);
        exit;
    }

    // Insert or update the FCM token in the database
    $stmt = $conn->prepare("INSERT INTO fcm_tokens (token) VALUES (?)");
    $stmt->bind_param("s", $fcm_token);

    if ($stmt->execute()) {
        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["success" => false, "error" => "Failed to update FCM token: " . $stmt->error]);
    }

    $stmt->close();
} else {
    echo json_encode(["success" => false, "error" => "Invalid request method"]);
}
$conn->close();
?>