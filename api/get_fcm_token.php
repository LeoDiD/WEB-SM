<?php
require 'vendor/autoload.php'; // Load Google Client Library

use Google\Client as Google_Client;

header('Content-Type: application/json'); // Ensure JSON response

function getAccessToken() {
    $keyFilePath = __DIR__ . "/config/ezmart-firebase-adminsdk.json"; // Ensure correct path

    if (!file_exists($keyFilePath)) {
        error_log("Service account JSON file not found!");
        return ["status" => "error", "message" => "Service account JSON file not found!"];
    }

    $client = new Google_Client();
    $client->setAuthConfig($keyFilePath);
    $client->addScope("https://www.googleapis.com/auth/firebase.messaging");

    try {
        $client->refreshTokenWithAssertion();
        $token = $client->getAccessToken();
        return ["status" => "success", "access_token" => $token["access_token"] ?? null];
    } catch (Exception $e) {
        error_log("Error getting access token: " . $e->getMessage());
        return ["status" => "error", "message" => "Failed to retrieve access token."];
    }
}

// Output the JSON response
echo json_encode(getAccessToken(), JSON_PRETTY_PRINT);
?>
