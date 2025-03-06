<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile Settings</title>
    <link rel="stylesheet" href="user.css">
</head>
<body>
    <div class="user-form-container">
        <div class="user-form">
            <div class="user-profile-section">
                <div class="user-profile">
                    <img src="../images/user_profile.png" alt="User Profile" id="userProfile">
                </div>
                <div class="user-upload">
                    <button class="upload-btn-upload">Upload New</button>
                    <button class="upload-btn-delete">Delete Avatar</button>
                </div>
            </div>

            <h2>Profile Settings</h2>

            <form action="user_settings.php" method="post" class="user-details">
                <div class="form-row">
                    <div class="form-group">
                        <label for="Fname">First Name:</label>
                        <input type="text" id="Fname" name="first_name" placeholder="First Name" required>
                    </div>
                    <div class="form-group">
                        <label for="Lname">Last Name:</label>
                        <input type="text" id="Lname" name="last_name" placeholder="Last Name" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="email">Email:</label>
                        <input type="email" id="email" name="email" placeholder="example@gmail.com" required>
                    </div>
                    <div class="form-group">
                        <label for="number">Mobile Number:</label>
                        <input type="number" id="number" name="mobile_number" placeholder="+63XXXXXXXXX" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group gender-section">
                        <label>Gender:</label>
                        <div class="gender-options">
                            <label for="male">
                                <input type="radio" id="male" name="gender" value="male" required> Male
                            </label>
                            <label for="female">
                                <input type="radio" id="female" name="gender" value="female" required> Female
                            </label>
                        </div>
                    </div>

                    <div class="save-button-container">
                        <button type="submit">Save Changes</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
