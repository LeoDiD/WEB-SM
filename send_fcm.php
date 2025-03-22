<?php
require 'vendor/autoload.php'; // Ensure Composer dependencies are loaded

use Google\Client as Google_Client; // Import Google Client

// Function to get Firebase Access Token
function getAccessToken() {
    $keyFilePath = __DIR__ . "/config/ezmart-firebase-adminsdk.json"; // Ensure correct path
    
    if (!file_exists($keyFilePath)) {
        error_log("Service account JSON file not found!");
        return null;
    }

    $client = new Google_Client();
    $client->setAuthConfig($keyFilePath);
    $client->addScope("https://www.googleapis.com/auth/firebase.messaging");

    try {
        $client->refreshTokenWithAssertion();
        $token = $client->getAccessToken();
        return $token["access_token"] ?? null;
    } catch (Exception $e) {
        error_log("Error getting access token: " . $e->getMessage());
        return null;
    }
}

// Function to send FCM notification
function sendFCMNotification($title, $body, $token = null) {
    $firebaseProjectId = "ezmart-f178a"; // Ensure correct Firebase project ID

    $accessToken = getAccessToken();
    if (!$accessToken) {
        error_log("Failed to obtain access token.");
        return false;
    }

    $url = "https://fcm.googleapis.com/v1/projects/$firebaseProjectId/messages:send";

    // Construct the FCM message payload
    $data = [
        "message" => [
            "notification" => [
                "title" => $title,
                "body" => $body,
            ],
            "webpush" => [
                "notification" => [
                    "icon" => "https://yourwebsite.com/icon.png",
                ]
            ]
        ]
    ];

    // If a token is provided, send to the specific device, otherwise to the topic "admin"
    if ($token) {
        $data["message"]["token"] = $token;
    } else {
        $data["message"]["topic"] = "admin";
    }

    $headers = [
        "Authorization: Bearer " . $accessToken,
        "Content-Type: application/json"
    ];

    // Initialize and execute cURL
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    // Log the response
    error_log("FCM Response Code: " . $httpCode);
    error_log("FCM Response: " . $response);

    // Check for success (HTTP 200 or 201)
    if ($httpCode === 200 || $httpCode === 201) {
        return true;
    } else {
        error_log("Failed to send FCM notification.");
        return false;
    }
}

// Example Usage (for Testing)
if (isset($_POST['title']) && isset($_POST['body'])) {
    $title = $_POST['title'];
    $body = $_POST['body'];
    $token = $_POST['token'] ?? null;

    $success = sendFCMNotification($title, $body, $token);
    echo json_encode(["success" => $success]);
}
?>
