<?php

require __DIR__ . '../vendor/autoload.php';
use Firebase\JWT\JWT;

function getOAuthToken() {
    $serviceAccountPath = "./service-account.json";

    if (!file_exists($serviceAccountPath)) {
        error_log("Error: service-account.json file not found.");
        return "Error: service-account.json file not found.";
    }

    $jsonKey = json_decode(file_get_contents($serviceAccountPath), true);

    if ($jsonKey === null) {
        error_log("Error: Failed to decode JSON from service-account.json");
        return "Error: Failed to decode JSON from service-account.json";
    }

    if (!isset($jsonKey["client_email"], $jsonKey["private_key"])) {
        error_log("Error: Required keys (client_email, private_key) missing in the JSON file.");
        return "Error: Required keys (client_email, private_key) missing in the JSON file.";
    }

    $now = time();

    $jwt = [
        "iss" => $jsonKey["client_email"],
        "scope" => "https://www.googleapis.com/auth/firebase.messaging",
        "aud" => "https://oauth2.googleapis.com/token",
        "iat" => $now,
        "exp" => $now + 3600
    ];

    try {
        $jwtEncoded = JWT::encode($jwt, $jsonKey["private_key"], 'RS256');
    } catch (Exception $e) {
        error_log("Error encoding JWT: " . $e->getMessage());
        return "Error encoding JWT: " . $e->getMessage();
    }

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://oauth2.googleapis.com/token");
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/x-www-form-urlencoded"]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
        "grant_type" => "urn:ietf:params:oauth:grant-type:jwt-bearer",
        "assertion" => $jwtEncoded
    ]));

    $response = curl_exec($ch);

    if ($response === false) {
        $errorMessage = curl_error($ch);
        curl_close($ch);
        error_log("Error: cURL request failed - " . $errorMessage);
        return "Error: cURL request failed - " . $errorMessage;
    }

    curl_close($ch);

    $data = json_decode($response, true);

    if (isset($data["access_token"])) {
        error_log("Access Token: " . $data["access_token"]); // Log the access token
        return $data["access_token"];
    } else {
        error_log("Error: Failed to retrieve access token. Response: " . json_encode($data));
        return "Error: Failed to retrieve access token. Response: " . json_encode($data);
    }
}

$token = getOAuthToken();
?>