<?php
require __DIR__ . '/config/db.php'; // Ensure it uses PDO

header('Content-Type: application/json');

// Get JSON input from request
$input = json_decode(file_get_contents("php://input"), true);
$token = $input['token'] ?? '';

if (empty($token)) {
    echo json_encode(["status" => "error", "message" => "FCM token is required"]);
    exit;
}

try {
    // Insert or update token in `users` table (ensure the table has `fcm_token` column)
    $sql = "INSERT INTO users (fcm_token) VALUES (:token) 
            ON DUPLICATE KEY UPDATE fcm_token = :token";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':token', $token);

    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Token saved successfully"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Failed to save token"]);
    }
} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => "Database error: " . $e->getMessage()]);
}
?>