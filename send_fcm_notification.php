<?php
require_once __DIR__ . '/get_fcm_token.php';

$token = getOAuthToken();
if (strpos($token, 'Error:') === 0) {
    echo "Failed to get token: " . $token;
} else {
    echo "Access Token: " . $token;
}

function sendFCMNotification($deviceToken, $title, $body) {
    $oauthToken = getOAuthToken(); 
    
    if (!$oauthToken) {

        return false; 
    }

    $url = "https://fcm.googleapis.com/v1/projects/ezmart-f178a/messages:send"; 

    $headers = [
        "Authorization: Bearer $oauthToken",
        "Content-Type: application/json"
    ];

    $notificationData = [
        "message" => [
            "token" => $deviceToken,
            "notification" => [
                "title" => $title,
                "body" => $body,
                "sound" => "default"
            ]
        ]
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($notificationData));

    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        error_log('cURL error: ' . curl_error($ch));
        curl_close($ch);
        return false;
    }

    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode !== 200) {
        error_log('FCM Response: ' . $response);
        return false;
    }

    return true;
}
?>
