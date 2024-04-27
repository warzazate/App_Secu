<?php
session_start();
header("X-Frame-Options: DENY");
header('X-Content-Type-Options: nosniff');
header('X-XSS-Protection: 1; mode=block');
// Unset all session values
$_SESSION = array();

// Get session parameters
$params = session_get_cookie_params();

// Delete the actual cookie.
setcookie(session_name(), '', time() - 42000,
    $params["path"], 
    $params["domain"],
    $params["secure"], 
    $params["httponly"]
);

// Destroy session
session_destroy();

header("Location: index.php");
exit;
?>