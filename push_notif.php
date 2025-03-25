<?php
require './get_fcm_token.php';

$deviceToken = "cpL43TGnQe-0DaWmDEXVhd:APA91bEqLfOFSIdeas1HEJankFXm1xQNbcUCYl3O0qRUt8EhsdK2ntKmeHmHg5mwf_IgrZDhTIWPOJzBHZsq2881uplBbdJgetADeGdsh53ii-PJ5hukcx8";
$fcmUrl = "https://fcm.googleapis.com/v1/projects/ezmart-f178a/messages:send";

$accessToken = getOAuthToken();

$notificationData = [
    "message" => [
        "token" => $deviceToken,
        "notification" => [
            "title" => "Test Notification",
            "body" => "Hello from PHP using FCM V1!"
        ]
    ]
];
$token = getOAuthToken();
if (strpos($token, 'Error:') === 0) {
    echo "Failed to get token: " . $token;
} else {
    echo "Access Token: " . $token;
}

$headers = [
    "Authorization: Bearer $accessToken",
    "Content-Type: application/json"
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $fcmUrl);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($notificationData));

$response = curl_exec($ch);
curl_close($ch);

echo "<h3>Response from FCM:</h3>";
echo "<pre>$response</pre>";
?>