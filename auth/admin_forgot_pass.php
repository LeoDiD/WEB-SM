<?php
session_start();
require '../config/db.php';

$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $new_password = trim($_POST['new_password']);
    $confirm_password = trim($_POST['confirm_password']);

    // Validate inputs
    if (empty($username) || empty($new_password) || empty($confirm_password)) {
        $error = "Please fill in all fields.";
    } elseif ($new_password !== $confirm_password) {
        $error = "Passwords do not match.";
    } elseif (strlen($new_password) < 8) {
        $error = "Password must be at least 8 characters long.";
    } else {
        // Check if admin exists
        $check_sql = "SELECT id FROM admins WHERE username = :username";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bindValue(':username', $username, PDO::PARAM_STR);
        $check_stmt->execute();
        
        if ($check_stmt->rowCount() == 0) {
            $error = "Admin account not found.";
        } else {
            // Update password
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $update_sql = "UPDATE admins SET password = :password WHERE username = :username";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bindValue(':password', $hashed_password, PDO::PARAM_STR);
            $update_stmt->bindValue(':username', $username, PDO::PARAM_STR);
            
            if ($update_stmt->execute()) {
                $_SESSION['success'] = "Password updated successfully. You can now login with your new password.";
                header("Location: admin_login.php");
                exit();
            } else {
                $error = "Password update failed. Please try again.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Forgot Password</title>
    <link rel="stylesheet" href="../assets/css/login.css">
    <style>
        .error { color: red; margin-bottom: 15px; }
        .success { color: green; margin-bottom: 15px; }
    </style>
</head>
<body>
    <div class="login-container">
        <fieldset>
            <legend>Admin Password Reset</legend>
            <?php if (!empty($error)) echo "<p class='error'>$error</p>"; ?>
            <?php if (!empty($success)) echo "<p class='success'>$success</p>"; ?>
            <form action="admin_forgot_pass.php" method="post" id="forgotForm">
                <div class="input-group">
                    <label for="username">Username</label>
                    <input type="text" placeholder="Enter your username" name="username" id="username" required>
                </div>
                <div class="input-group">
                    <label for="new_password">New Password</label>
                    <input type="password" placeholder="Enter new password" name="new_password" id="new_password" required>
                    <small>Minimum 8 characters</small>
                </div>
                <div class="input-group">
                    <label for="confirm_password">Confirm New Password</label>
                    <input type="password" placeholder="Confirm new password" name="confirm_password" id="confirm_password" required>
                </div>
                <button type="submit">Reset Password</button>
                <div class="login-link">
                    <a href="admin_login.php">Back to Login</a>
                </div>
            </form>
        </fieldset>
    </div>
    <script>
        document.getElementById('forgotForm').addEventListener('submit', function(e) {
            const newPassword = document.getElementById('new_password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            if (newPassword.length < 8) {
                e.preventDefault();
                alert('Password must be at least 8 characters long');
                return false;
            }
            
            if (newPassword !== confirmPassword) {
                e.preventDefault();
                alert('Passwords do not match');
                return false;
            }
            
            return true;
        });
    </script>
</body>
</html>