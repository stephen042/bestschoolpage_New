<?php
/**
 * Session Initialization & User Validation - Rebuilt for PHP 8.x
 * Handles user authentication, session validation, and user data retrieval
 */

// Prevent direct access
if (!defined('DS')) {
    require_once dirname(__DIR__) . '/config.php';
}

// ============================================================================
// SESSION VALIDATION
// ============================================================================

// Check if user is logged in
if (empty($_SESSION['userid'])) {
    redirect(PARENT_URL);
    exit;
}

// ============================================================================
// USER DATA RETRIEVAL
// ============================================================================

$userId = (int)$_SESSION['userid'];
$userType = $_SESSION['usertype'] ?? '';

// Get logged-in user details from student_guardian table
$loginUserDetail = db_get_row(
    "SELECT * FROM student_guardian WHERE id = ?",
    [$userId]
);

// Check if user exists
if (empty($loginUserDetail)) {
    session_destroy();
    redirect(PARENT_URL);
    exit;
}

// ============================================================================
// DETERMINE CREATOR IDs
// ============================================================================

$create_by_usertype = $loginUserDetail['create_by_usertype'] ?? '';

// Determine create_by_userid
if (empty($loginUserDetail['create_by_userid']) || $loginUserDetail['create_by_userid'] == '0') {
    $create_by_userid = $userId;
} else {
    $create_by_userid = (int)$loginUserDetail['create_by_userid'];
}

// ============================================================================
// GET SCHOOL/REGISTER DETAILS
// ============================================================================

$currentSkoolnameDetails = db_get_row(
    "SELECT * FROM school_register WHERE id = ?",
    [$create_by_userid]
);

// If no school found, create empty array to prevent errors
if (empty($currentSkoolnameDetails)) {
    $currentSkoolnameDetails = [];
}

// ============================================================================
// MAKE VARIABLES AVAILABLE GLOBALLY
// ============================================================================

// For backward compatibility with old code
$iLoginUserDetail = $loginUserDetail;
$iCurrentskoolnameDetails = $currentSkoolnameDetails;

// Also set session variables for consistency
if (!empty($loginUserDetail['name'])) {
    $_SESSION['name'] = $loginUserDetail['name'];
}
if (!empty($loginUserDetail['email'])) {
    $_SESSION['email'] = $loginUserDetail['email'];
}

// ============================================================================
// HELPER FUNCTIONS FOR THIS SESSION
// ============================================================================

/**
 * Check if current user has admin privileges
 */
function isAdmin() {
    global $userType;
    return $userType == '1' || $userType == 'admin';
}

/**
 * Check if current user is a parent
 */
function isParent() {
    global $userType;
    return $userType == '2' || $userType == 'parent';
}

/**
 * Get current user's ID
 */
function getCurrentUserId() {
    return $_SESSION['userid'] ?? 0;
}

/**
 * Get current user's type
 */
function getCurrentUserType() {
    return $_SESSION['usertype'] ?? '';
}

/**
 * Get school/register ID for current user
 */
function getSchoolId() {
    global $create_by_userid;
    return $create_by_userid ?? 0;
}

/**
 * Get school name for current user
 */
function getSchoolName() {
    global $currentSkoolnameDetails;
    return $currentSkoolnameDetails['name'] ?? 'School Management System';
}

// ============================================================================
// SESSION SECURITY - REGENERATE SESSION ID PERIODICALLY
// ============================================================================

if (!isset($_SESSION['last_regeneration'])) {
    $_SESSION['last_regeneration'] = time();
} elseif (time() - $_SESSION['last_regeneration'] > 3600) {
    // Regenerate session ID every hour for security
    session_regenerate_id(true);
    $_SESSION['last_regeneration'] = time();
}

// ============================================================================
// LOGGING (Optional - uncomment if you have a logs table)
// ============================================================================


// Log user activity
function logActivity($action, $details = '') {
    global $userId, $userType;
    
    try {
        db_insert("user_activity_logs", [
            'user_id' => $userId,
            'user_type' => $userType,
            'action' => $action,
            'details' => $details,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
            'created_at' => date('Y-m-d H:i:s')
        ]);
    } catch (Exception $e) {
        // Silent fail - don't break the application
    }
}

?>