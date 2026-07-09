<?php
/**
 * ============================================================================
 * ADMIN SESSION CHECK - FOR ADMIN PAGES ONLY
 * ============================================================================
 * Ensures only super admin (usertype=1) can access admin pages.
 * Redirects unauthorized users to appropriate login page.
 * ============================================================================
 */

// Check if user is logged in
if (empty($_SESSION['userid'])) {
    // Not logged in - redirect to admin login
    redirect(ADMIN_URL . 'login.php');
    exit;
}

// Check if user is super admin (usertype must be exactly 1)
$usertype = (int)($_SESSION['usertype'] ?? 0);
if ($usertype !== 1) {
    // Not a super admin - redirect back to school dashboard
    redirect(SKOOL_URL . 'index.php');
    exit;
}

// Get admin user details
$adminUserId = (int)($_SESSION['userid'] ?? 0);
$adminUserName = $_SESSION['username'] ?? 'Admin';

// Verify user still exists and is still active
$adminUser = db_get_row("SELECT * FROM school_register WHERE id = ? AND usertype = ? AND status = '1'", [$adminUserId, 1]);

if (empty($adminUser)) {
    // User no longer exists or is inactive - log them out
    session_destroy();
    redirect(ADMIN_URL . 'login.php?error=account_inactive');
    exit;
}

// Update session with latest data if needed
$_SESSION['email'] = $adminUser['email'] ?? '';
$_SESSION['school_name'] = $adminUser['name'] ?? 'Site Admin';
$_SESSION['is_super_admin'] = true;

?>
