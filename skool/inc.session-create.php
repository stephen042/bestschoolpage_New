<?php
/**
 * ============================================================================
 * SESSION INITIALIZATION & USER VALIDATION - PHP 8.x FIXED
 * ============================================================================
 * Handles user authentication, session validation, and user data retrieval
 * FIXED: Prevents duplicate function declaration errors
 * FIXED: Redirects to SKOOL_URL for admin/staff, PARENT_URL for parents
 * ============================================================================
 */

// Prevent direct access and multiple inclusions
if (defined('SESSION_INITIALIZED')) {
    return;
}

if (!defined('DS')) {
    require_once dirname(__DIR__) . '/config.php';
}

define('SESSION_INITIALIZED', true);

// ============================================================================
// SESSION VALIDATION
// ============================================================================

// Check if user is logged in
if (empty($_SESSION['userid'])) {
    // If not logged in, redirect to school login page
    redirect(SKOOL_URL);
    exit;
}

// ============================================================================
// USER DATA RETRIEVAL
// ============================================================================

$userId = (int)$_SESSION['userid'];
$userType = $_SESSION['usertype'] ?? '';

// Resolve user source using session user type to avoid id-collision between tables.
$loginUserDetail = [];
$loginSource = '';
$isParentSession = in_array((string)$userType, ['2', 'parent'], true);

if ($isParentSession) {
    $loginUserDetail = db_get_row(
        "SELECT * FROM student_guardian WHERE id = ?",
        [$userId]
    );
    $loginSource = 'student_guardian';

    if (empty($loginUserDetail)) {
        $loginUserDetail = db_get_row(
            "SELECT * FROM school_register WHERE id = ?",
            [$userId]
        );
        $loginSource = 'school_register';
    }
} else {
    $loginUserDetail = db_get_row(
        "SELECT * FROM school_register WHERE id = ?",
        [$userId]
    );
    $loginSource = 'school_register';

    if (empty($loginUserDetail)) {
        $loginUserDetail = db_get_row(
            "SELECT * FROM student_guardian WHERE id = ?",
            [$userId]
        );
        $loginSource = 'student_guardian';
    }
}

// Check if user exists with proper error handling
if (empty($loginUserDetail)) {
    session_destroy();
    redirect(SKOOL_URL . 'login.php');
    exit;
}

// ============================================================================
// DETERMINE CREATOR IDs
// ============================================================================

$create_by_usertype = $loginUserDetail['create_by_usertype'] ?? '';

// Determine create_by_userid
if ($loginSource === 'school_register') {
    $sourceCreateBy = (int)($loginUserDetail['create_by_userid'] ?? 0);
    if ($sourceCreateBy > 0) {
        $create_by_userid = $sourceCreateBy;
    } else {
        $resolvedOwnerId = 0;
        $loginUsername = (string)($loginUserDetail['username'] ?? '');
        $loginEmail = (string)($loginUserDetail['email'] ?? '');

        if ($loginUsername !== '' || $loginEmail !== '') {
            $resolvedOwnerId = (int) db_get_val(
                "SELECT create_by_userid
                 FROM staff_manage
                 WHERE (staff_id = ? OR email = ? OR id = ?)
                   AND create_by_userid > 0
                 ORDER BY id DESC
                 LIMIT 1",
                [$loginUsername, $loginEmail, $userId]
            );

            if ($resolvedOwnerId <= 0) {
                $resolvedOwnerId = (int) db_get_val(
                    "SELECT ar.create_by_userid
                     FROM assign_role ar
                     LEFT JOIN staff_manage sm ON ar.staff_id = sm.id
                     WHERE (sm.staff_id = ? OR sm.email = ? OR sm.id = ? OR ar.staff_id = ?)
                       AND ar.create_by_userid > 0
                     ORDER BY ar.id DESC
                     LIMIT 1",
                    [$loginUsername, $loginEmail, $userId, $userId]
                );
            }
        }

        $create_by_userid = ($resolvedOwnerId > 0) ? $resolvedOwnerId : $userId;
    }
} elseif (empty($loginUserDetail['create_by_userid']) || $loginUserDetail['create_by_userid'] == '0') {
    $create_by_userid = $userId;
} else {
    $create_by_userid = (int)$loginUserDetail['create_by_userid'];
}

// Keep session scope canonical so all pages resolve the same school owner.
$_SESSION['create_by_userid'] = $create_by_userid;

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
if (!empty($loginUserDetail['first_name'])) {
    $_SESSION['fullname'] = $loginUserDetail['first_name'] . ' ' . ($loginUserDetail['last_name'] ?? '');
} elseif (!empty($loginUserDetail['fullname'])) {
    $_SESSION['fullname'] = $loginUserDetail['fullname'];
} elseif (!empty($loginUserDetail['name'])) {
    $_SESSION['fullname'] = $loginUserDetail['name'];
}

if (!empty($loginUserDetail['email'])) {
    $_SESSION['email'] = $loginUserDetail['email'];
}

if ($loginSource === 'school_register') {
    $_SESSION['school_name'] = $loginUserDetail['name'] ?? ($_SESSION['school_name'] ?? '');
}

// ============================================================================
// HELPER FUNCTIONS FOR THIS SESSION (with protection against redeclaration)
// ============================================================================

if (!function_exists('isAdmin')) {
    /**
     * Check if current user has admin privileges
     */
    function isAdmin() {
        global $userType;
        return $userType == '1' || $userType == 'admin';
    }
}

if (!function_exists('isParent')) {
    /**
     * Check if current user is a parent
     */
    function isParent() {
        global $userType;
        return $userType == '2' || $userType == 'parent';
    }
}

if (!function_exists('getCurrentUserId')) {
    /**
     * Get current user's ID
     */
    function getCurrentUserId() {
        return $_SESSION['userid'] ?? 0;
    }
}

if (!function_exists('getCurrentUserType')) {
    /**
     * Get current user's type
     */
    function getCurrentUserType() {
        return $_SESSION['usertype'] ?? '';
    }
}

if (!function_exists('getSchoolId')) {
    /**
     * Get school/register ID for current user
     */
    function getSchoolId() {
        global $create_by_userid;
        return $create_by_userid ?? 0;
    }
}

if (!function_exists('getSchoolName')) {
    /**
     * Get school name for current user
     */
    function getSchoolName() {
        global $currentSkoolnameDetails;
        return $currentSkoolnameDetails['name'] ?? 'School Management System';
    }
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

/*
// Log user activity
if (!function_exists('logActivity')) {
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
}
*/
?>