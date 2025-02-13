<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="login.css">
</head>
<body>
    <div class="login-container">
        <fieldset>
            <legend>Log Into Dashboard</legend>
            <form action="login_process.php"  method="post" onsubmit="return validateForm()">
                <div class="input-group">
                    <label for="username">Username</label>
                    <input type="text" name="username" id="username" required>
                    <span id="username-error" class="error"></span>
                </div>
                <div class="input-group">
                    <label for="password">Password</label>
                    <input type="password" name="password" id="password" required>
                    <span id="password-error" class="error"></span>
                </div>
                <button type="submit">Login</button>
            </form>
    </div>
    <script src="login.js"></script>
</body>
</html>
