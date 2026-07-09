<?php  
/**
 * Logout Script - Enhanced Security Version
 */

require_once '../config.php'; 
require_once 'inc.session-create.php'; 

// ============================================================================
// ENHANCED SECURE LOGOUT
// ============================================================================

// Get user info before destroying session
$userId = $_SESSION['userid'] ?? 0;
$sessionId = session_id();

// Clear all session variables
$_SESSION = [];

// Delete session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $params["path"], $params["domain"], $params["secure"] ?? false, $params["httponly"] ?? true);
}

// Destroy session
session_destroy();

// Regenerate session ID to prevent reuse
session_start();
session_regenerate_id(true);
session_destroy();

// Clear remember me tokens
if (isset($_COOKIE['remember_token'])) {
    setcookie('remember_token', '', time() - 3600, '/');
    
    // Also clear from database if you store tokens
    // db_delete("user_tokens", "session_id = ?", [$sessionId]);
}

// Clear CSRF tokens if stored
if (isset($_COOKIE['csrf_token'])) {
    setcookie('csrf_token', '', time() - 3600, '/');
}

// Invalidate any existing session in database (if using database sessions)
// db_update("sessions", ['is_active' => 0], "session_id = ?", [$sessionId]);

// Redirect with cache control headers to prevent back button access
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: Thu, 19 Nov 1981 08:52:00 GMT");

redirect(SITE_URL . 'index.php');
exit;
?>