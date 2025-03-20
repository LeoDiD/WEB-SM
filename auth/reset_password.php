<?php
require_once __DIR__ . '/../config/db.php'; // Adjust the path to your database configuration file

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Set PHP time zone
date_default_timezone_set('UTC');

// Check if token is provided in the query parameters
if (!isset($_GET['token'])) {
    echo json_encode(["success" => false, "error" => "Token is required"]);
    exit;
}

$token = $_GET['token'];

// Verify token
$conn = new mysqli($servername, $username, $password, $dbname);

// Set MySQL time zone
$conn->query("SET time_zone = '+00:00'");

// Debugging: Log the token being checked
error_log("Token being checked: " . $token);

// Fetch the token and expiry time from the database
$query = $conn->prepare("SELECT id, reset_expiry FROM users_mobile WHERE reset_token=? AND reset_expiry > NOW()");
$query->bind_param("s", $token);
$query->execute();
$result = $query->get_result();

if ($result->num_rows === 0) {
    // Debugging: Log the error details
    error_log("Token not found or expired");
    error_log("Token: " . $token);
    error_log("Current time: " . date('Y-m-d H:i:s'));
    echo json_encode(["success" => false, "error" => "Invalid or expired token"]);
    exit;
}

$user = $result->fetch_assoc();
$user_id = $user['id'];

// If this is a POST request, update the password and clear the token
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
    if (!isset($data['new_password'])) {
        echo json_encode(["success" => false, "error" => "New password is required"]);
        exit;
    }
    $new_password = password_hash($data['new_password'], PASSWORD_DEFAULT);

    $stmt = $conn->prepare("UPDATE users_mobile SET password=?, reset_token=NULL, reset_expiry=NULL WHERE id=?");
    $stmt->bind_param("si", $new_password, $user_id);

    if ($stmt->execute()) {
        echo json_encode(["success" => true, "message" => "Password updated successfully"]);
    } else {
        echo json_encode(["success" => false, "error" => "Failed to update password"]);
    }
} else {
    // For GET requests, just confirm that the token is valid
    echo json_encode(["success" => true, "message" => "Token is valid"]);
}
?>