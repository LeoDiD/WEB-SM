<?php
session_start();
require '../config/db.php';
header("Location: http://localhost/WEB-SM/index.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // Validate inputs
    if (empty($username) || empty($password)) {
        $_SESSION['error'] = "Please fill in all fields.";
        header("Location: admin_login.php");
        exit();
    }

    // Fetch admin data
    $sql = "SELECT * FROM admins WHERE username = :username";
    $stmt = $conn->prepare($sql);
    $stmt->bindValue(':username', $username, PDO::PARAM_STR);
    $stmt->execute();
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($admin && password_verify($password, $admin['password'])) {
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_username'] = $admin['username'];
        header("Location: http://localhost/WEB-SM/index.php");
        exit();
    } else {
        $_SESSION['error'] = "Invalid admin credentials.";
        header("Location: admin_login.php");
        exit();
    }
}
?>