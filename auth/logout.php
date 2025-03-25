<?php
session_start();
$_SESSION = array();
session_destroy();

// Redirect to login page
header("Location: admin_login.php");
exit();
?>