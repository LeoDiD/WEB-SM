<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/db.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);
if (!isset($data['email'])) {
    echo json_encode(["success" => false, "error" => "Email is required"]);
    exit;
}

$email = $data['email'];
$conn = new mysqli($servername, $username, $password, $dbname);

$query = $conn->prepare("SELECT id FROM users_mobile WHERE email = ?");
$query->bind_param("s", $email);
$query->execute();
$result = $query->get_result();
if ($result->num_rows === 0) {
    echo json_encode(["success" => false, "error" => "Email not found"]);
    exit;
}

$user = $result->fetch_assoc();
// Generate reset token
$token = bin2hex(random_bytes(32)); // Generate a 64-character token
$expiry = date('Y-m-d H:i:s', strtotime('+2 hours'));

// Save token and expiry in the database
$stmt = $conn->prepare("UPDATE users_mobile SET reset_token=?, reset_expiry=? WHERE email=?");
$stmt->bind_param("sss", $token, $expiry, $email);
if (!$stmt->execute()) {
    echo json_encode(["success" => false, "error" => "Database error"]);
    exit;
}

$reset_link = "http://192.168.100.15/WEB-SM/auth/reset_password.php?token=$token";

$mail = new PHPMailer(true);
try {
    
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'shunaml1604@gmail.com';
    $mail->Password = 'hqdj fdkv ksqu mypj'; 
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;

    $mail->setFrom('shunaml1604@gmail.com', 'EZ Mart');
    $mail->addAddress($email);
    $mail->isHTML(true);
    $mail->Subject = "Password Reset Request";
    $mail->Body = "Click the link below to reset your password: <br> <a href='$reset_link'>$reset_link</a>";

    $mail->send();
    echo json_encode(["success" => true, "message" => "Password reset link sent"]);
} catch (Exception $e) {
    
    error_log("Email sending failed: " . $e->getMessage());
    echo json_encode(["success" => false, "error" => "Email could not be sent. Error: " . $e->getMessage()]);
}
?>