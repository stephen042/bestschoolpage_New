<?php  
/**
 * Logout Script - Complete Rebuilt for PHP 8.x
 * Securely destroys session and logs out user
 */

require_once('../config.php'); 
require_once('inc.session-create.php'); 

// ============================================================================
// SECURE LOGOUT PROCESS
// ============================================================================

// Store user info before destroying session (for logging purposes)
$userId = $_SESSION['userid'] ?? 0;
$userType = $_SESSION['usertype'] ?? '';

// Clear all session variables
$_SESSION = [];

// If using session cookies, delete the session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(), 
        '', 
        time() - 42000,
        $params["path"], 
        $params["domain"],
        $params["secure"] ?? false, 
        $params["httponly"] ?? true
    );
}

// Destroy the session
session_destroy();

// Clear any remember me cookies if they exist
if (isset($_COOKIE['remember_token'])) {
    setcookie('remember_token', '', time() - 3600, '/');
    setcookie('remember_token', '', time() - 3600, '/', '', false, true);
}

// Clear any other application-specific cookies
$cookieNames = ['user_preferences', 'theme', 'language', 'admin_email', 'admin_password'];
foreach ($cookieNames as $cookieName) {
    if (isset($_COOKIE[$cookieName])) {
        setcookie($cookieName, '', time() - 3600, '/');
    }
}

// Optional: Log the logout activity (uncomment if you have a logs table)
/*
if ($userId > 0) {
    try {
        db_insert("user_logs", [
            'user_id' => $userId,
            'user_type' => $userType,
            'action' => 'logout',
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
            'logout_time' => date('Y-m-d H:i:s')
        ]);
    } catch (Exception $e) {
        // Silent fail - don't break logout
    }
}
*/

// Regenerate session ID to prevent session fixation (though session is destroyed)
// This is a good practice even after logout

// Clear any output buffers
while (ob_get_level() > 0) {
    ob_end_clean();
}

// Set no-cache headers to prevent back button access after logout
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: Thu, 19 Nov 1981 08:52:00 GMT");

// Redirect to login page
redirect(SITE_URL . 'login.php');
exit;
?>