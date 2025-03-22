<?php
require __DIR__ . '/vendor/autoload.php'; // Firebase SDK

use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;

function sendFCMNotification($token, $title, $body) {
    $factory = (new Factory)->withServiceAccount(__DIR__ . '/config/ezmart-firebase-adminsdk.json');
    $messaging = $factory->createMessaging();

    $notification = Notification::create($title, $body);
    $message = CloudMessage::withTarget('token', $token)->withNotification($notification);

    try {
        $messaging->send($message);
        return true;
    } catch (Exception $e) {
        error_log("FCM Error: " . $e->getMessage());
        return false;
    }
}
?>
