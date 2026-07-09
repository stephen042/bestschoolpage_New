<?php  
/**
 * Logout Script with Activity Logging
 */

require_once '../config.php'; 
require_once 'inc.session-create.php'; 

// Get user info before destroying session
$userId = $_SESSION['userid'] ?? 0;
$username = $_SESSION['username'] ?? 'Unknown';

// Clear all session variables
$_SESSION = [];

// Delete session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
}

// Destroy session
session_destroy();

// Optional: Log logout activity (uncomment if you have a logs table)
/*
try {
    db_insert("user_logs", [
        'user_id' => $userId,
        'username' => $username,
        'action' => 'logout',
        'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '',
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
        'logout_time' => date('Y-m-d H:i:s')
    ]);
} catch (Exception $e) {
    // Silent fail - don't break logout
}
*/

// Clear any remember me cookies
if (isset($_COOKIE['remember_token'])) {
    setcookie('remember_token', '', time() - 3600, '/');
}

// Redirect to home page
redirect(SITE_URL . 'index.php');
exit;
?>