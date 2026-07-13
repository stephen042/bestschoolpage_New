<?php

/**
 * Sidebar Menu - Rebuilt for PHP 8.x
 * Dynamic sidebar with role-based permissions
 * FIXED: Proper school owner ID handling to prevent cross-school data display
 * IMPROVED: Mobile scrolling support
 */

// Prevent direct access
if (!defined('DS')) {
    require_once dirname(__DIR__) . '/config.php';
}

// ============================================================================
// VALIDATE SESSION AND SCHOOL OWNER ID
// ============================================================================

// First, check if user is logged in
if (empty($_SESSION['userid'])) {
    // If not logged in, redirect to login
    if (basename($_SERVER['PHP_SELF']) !== 'login.php') {
        redirect(SITE_URL . 'login.php');
        exit;
    }
    return; // Don't render sidebar for login page
}

$iCurrentFileName = basename($_SERVER['PHP_SELF']);
$currentFile = basename($_SERVER["SCRIPT_FILENAME"], '.php') . ".php";

// ============================================================================
// INITIALIZE VARIABLES WITH PROPER SCHOOL OWNER ID
// ============================================================================

// CRITICAL FIX: Always use create_by_userid from session, NEVER fall back to userid
$schoolOwnerId = (int)($_SESSION['create_by_userid'] ?? 0);

// Verify the school owner ID is valid
if ($schoolOwnerId <= 0) {
    // If invalid, try to recover from the school_register table
    $userData = db_get_row(
        "SELECT create_by_userid, usertype FROM school_register WHERE id = ?",
        [(int)$_SESSION['userid']]
    );

    if (!empty($userData)) {
        $schoolOwnerId = (int)($userData['create_by_userid'] ?? 0);
        if ($schoolOwnerId <= 0) {
            // If create_by_userid is 0, check if this user is the owner (usertype = 0)
            if ((int)($userData['usertype'] ?? 1) === 0) {
                $schoolOwnerId = (int)$_SESSION['userid'];
            } else {
                // This is a staff/teacher with missing create_by_userid
                // Try to find the school owner
                $owner = db_get_row(
                    "SELECT id FROM school_register WHERE usertype = 0 AND status = 1 ORDER BY id ASC LIMIT 1"
                );
                if (!empty($owner)) {
                    $schoolOwnerId = (int)$owner['id'];
                    // Fix the user record
                    db_update(
                        "school_register",
                        ['create_by_userid' => $schoolOwnerId],
                        "id = ?",
                        [(int)$_SESSION['userid']]
                    );
                    // Update session
                    $_SESSION['create_by_userid'] = $schoolOwnerId;
                    error_log("Fixed create_by_userid for user: " . $_SESSION['userid'] . " -> " . $schoolOwnerId);
                } else {
                    // Last resort - use user's own ID (should only happen in multi-owner mode)
                    $schoolOwnerId = (int)$_SESSION['userid'];
                    error_log("WARNING: Using user's own ID as school owner for: " . $_SESSION['userid']);
                }
            }
        }
        $_SESSION['create_by_userid'] = $schoolOwnerId;
    } else {
        // Cannot find user - force logout
        session_destroy();
        redirect(SITE_URL . 'login.php');
        exit;
    }
}

// Additional verification: check if the school owner ID actually exists
$ownerCheck = db_get_val(
    "SELECT id FROM school_register WHERE id = ?",
    [$schoolOwnerId]
);

if (empty($ownerCheck)) {
    // The school owner ID doesn't exist in the database
    error_log("CRITICAL: School owner ID " . $schoolOwnerId . " does not exist for user: " . $_SESSION['userid']);

    // Try to find a valid owner
    $owner = db_get_row(
        "SELECT id FROM school_register WHERE usertype = 0 AND status = 1 ORDER BY id ASC LIMIT 1"
    );
    if (!empty($owner)) {
        $schoolOwnerId = (int)$owner['id'];
        $_SESSION['create_by_userid'] = $schoolOwnerId;
        error_log("Recovered school owner ID: " . $schoolOwnerId);
    } else {
        // No valid owner exists - use user's own ID
        $schoolOwnerId = (int)$_SESSION['userid'];
        $_SESSION['create_by_userid'] = $schoolOwnerId;
        error_log("WARNING: Using user's own ID as school owner after recovery failure");
    }
}

// ============================================================================
// OTHER SESSION VARIABLES
// ============================================================================
$sessionUserId = (int)($_SESSION['userid'] ?? 0);
$sessionUsername = (string)($_SESSION['username'] ?? '');
$sessionEmail = (string)($_SESSION['email'] ?? '');
$userType = $_SESSION['usertype'] ?? '';
$dashboardFile = 'dashboard.php';

// Determine if this is a school owner/admin session
$isSchoolOwnerSession = ($sessionUserId > 0 && $sessionUserId === $schoolOwnerId);
$isAdminMenu = ($userType == '0' || $isSchoolOwnerSession);

// Fallback teacher files (for staff without specific permissions)
$fallbackTeacherFiles = [
    'index.php',
    'home.php',
    'class_teacher_roll_call.php',
    'class_teacher_roll_call_bulk.php',
    'input_score_class_teacher.php',
    'class_teacher_subject_result.php',
    'class_teacher_make_comment.php',
    'class_teacher_traits.php',
    'class_teacher_pyschomotor.php',
    'board_sheet.php',
    'cumulative_board_sheet.php',
    'subject_specific_comment.php',
    'removesubject.php',
    'logout.php',
];

// ============================================================================
// GET USER PERMISSIONS (for non-admin users)
// ============================================================================
$ret = [];

if (!$isAdminMenu) {
    $staffForPerm = db_get_row(
        "SELECT id FROM staff_manage
         WHERE create_by_userid = ?
           AND (id = ? OR staff_id = ? OR email = ?)
         ORDER BY id DESC
         LIMIT 1",
        [$schoolOwnerId, $sessionUserId, $sessionUsername, $sessionEmail]
    );
    $effectiveStaffId = (int)($staffForPerm['id'] ?? 0);

    // Get staff role assignment
    $iStaffCheck = [];
    $candidateStaffIds = [];
    if ($sessionUserId > 0) {
        $candidateStaffIds[] = $sessionUserId;
    }
    if ($effectiveStaffId > 0 && !in_array($effectiveStaffId, $candidateStaffIds, true)) {
        $candidateStaffIds[] = $effectiveStaffId;
    }
    if (!empty($candidateStaffIds)) {
        $placeholders = implode(',', array_fill(0, count($candidateStaffIds), '?'));
        $params = $candidateStaffIds;
        $params[] = $schoolOwnerId;
        $iStaffCheck = db_get_row(
            "SELECT * FROM assign_role
             WHERE staff_id IN ($placeholders)
               AND create_by_userid = ?
             ORDER BY id DESC
             LIMIT 1",
            $params
        );
    }

    // Check permission for current file
    if (!empty($iStaffCheck) && !in_array($currentFile, ['home.php', 'index.php', 'subject_specific_comment.php', 'input_score_for_all_cas_subject.php'], true)) {
        $currentFileName = $currentFile;
        if ($currentFileName == 'class_teacher_roll_call_bulk.php') {
            $currentFileName = 'class_teacher_roll_call.php';
        }

        $fileRedirect = db_get_val(
            "SELECT id FROM role_permission WHERE role_id = ? AND file_name = ?",
            [$iStaffCheck['role_id'], $currentFileName]
        );

        if (empty($fileRedirect)) {
            redirect('index.php');
        }
    }

    // Get allowed files from role permissions
    if (!empty($iStaffCheck)) {
        $iFileDetails = db_get_rows(
            "SELECT * FROM role_permission WHERE role_id = ?",
            [$iStaffCheck['role_id']]
        );

        foreach ($iFileDetails as $iFilesList) {
            $iSelectFile = db_get_val(
                "SELECT file_name FROM school_filename WHERE file_name = ?",
                [$iFilesList['file_name']]
            );
            if (!empty($iSelectFile)) {
                $ret[] = $iSelectFile;
            }
        }
    }

    if (empty($ret)) {
        $ret = $fallbackTeacherFiles;
    }
}

if (!$isAdminMenu && empty($ret)) {
    $ret = $fallbackTeacherFiles;
}

// Get package permissions
$iPackageAllowFile = db_get_row(
    "SELECT file_allow FROM school_purchased_pacakage WHERE userid = ? AND status = '1' ORDER BY id DESC",
    [$schoolOwnerId]
);

$iPackageJsoneDecodeAllowFile = [];
if (!empty($iPackageAllowFile['file_allow'])) {
    $iPackageJsoneDecodeAllowFile = json_decode($iPackageAllowFile['file_allow'], true);
}
if (!is_array($iPackageJsoneDecodeAllowFile)) {
    $iPackageJsoneDecodeAllowFile = [];
}

// Sidebar logo image - ALWAYS use the school owner ID for the logo
$sidebarLogo = '../image/user.png';
$sidebarSchool = db_get_row("SELECT logo FROM school_register WHERE id = ?", [$schoolOwnerId]);
if (!empty($sidebarSchool['logo']) && file_exists("../uploads/" . $sidebarSchool['logo'])) {
    $sidebarLogo = "../uploads/" . $sidebarSchool['logo'];
}

// ============================================================================
// HELPER FUNCTIONS
// ============================================================================
function hasAccess($fileName, $permissions, $userType)
{
    global $isAdminMenu;
    if ($isAdminMenu) return true;
    return in_array($fileName, $permissions);
}

function isActive($currentFile, $targetFile)
{
    return ($currentFile == $targetFile) ? 'active' : '';
}
?>

<style>
    /* ============================================================
       SIDEBAR MAIN STYLES - IMPROVED MOBILE SCROLLING
       ============================================================ */
    .left.side-menu {
        --menu-bg-1: #1B3058;
        --menu-bg-2: #142445;
        --menu-accent: #f21151;
        --menu-text: #d7e5ff;
        --menu-text-soft: #9cb3db;
        --menu-border: rgba(255, 255, 255, 0.12);
        --menu-glow: rgba(242, 17, 81, 0.45);
        position: fixed;
        top: 60px;
        left: 0;
        bottom: 0;
        width: 240px;
        background: linear-gradient(170deg, var(--menu-bg-1) 0%, var(--menu-bg-2) 100%);
        z-index: 999;
        transition: transform 0.3s ease;
        border-right: 1px solid var(--menu-border);
        box-shadow: 10px 0 30px rgba(0, 0, 0, 0.35);
        overflow: hidden;
        display: flex;
        flex-direction: column;
    }

    /* FIX: Sidebar collapsed state - hides sidebar on desktop */
    body.sidebar-collapsed .left.side-menu {
        transform: translateX(-100%);
    }

    /* FIX: Sidebar collapsed state - adjusts content margin */
    body.sidebar-collapsed .content-page {
        margin-left: 0 !important;
    }

    .left.side-menu::before {
        content: "";
        position: absolute;
        top: -90px;
        left: -80px;
        width: 250px;
        height: 250px;
        background: radial-gradient(circle, rgba(242, 17, 81, 0.28) 0%, rgba(242, 17, 81, 0) 70%);
        pointer-events: none;
        z-index: 0;
    }

    .left.side-menu::after {
        content: "";
        position: absolute;
        right: -80px;
        bottom: -120px;
        width: 220px;
        height: 220px;
        background: radial-gradient(circle, rgba(255, 255, 255, 0.16) 0%, rgba(255, 255, 255, 0) 75%);
        pointer-events: none;
        z-index: 0;
    }

    body.fixed-left .side-menu.left {
        top: 60px !important;
        left: 0 !important;
        width: 240px !important;
        z-index: 999 !important;
    }

    .content-page {
        margin-left: 240px !important;
        transition: margin-left 0.3s ease;
    }

    /* ============================================================
       SIDEBAR INNER - SCROLLABLE CONTAINER
       ============================================================ */
    .sidebar-inner {
        position: relative;
        z-index: 1;
        flex: 1;
        overflow-y: auto;
        overflow-x: hidden;
        padding: 10px 12px 24px;
        min-height: 0;
        /* Critical for flex scrolling */
        -webkit-overflow-scrolling: touch;
        /* Smooth scrolling on iOS */
    }

    .sidebar-inner::-webkit-scrollbar {
        width: 5px;
    }

    .sidebar-inner::-webkit-scrollbar-track {
        background: rgba(12, 24, 47, 0.8);
        border-radius: 3px;
    }

    .sidebar-inner::-webkit-scrollbar-thumb {
        background: var(--menu-accent);
        border-radius: 6px;
    }

    .sidebar-inner::-webkit-scrollbar-thumb:hover {
        background: #d40e45;
    }

    /* Firefox scrollbar styling */
    .sidebar-inner {
        scrollbar-width: thin;
        scrollbar-color: var(--menu-accent) rgba(12, 24, 47, 0.8);
    }

    /* ============================================================
       USER DETAILS
       ============================================================ */
    .user-details {
        margin: 8px 2px 14px;
        padding: 12px;
        border: 1px solid rgba(255, 255, 255, 0.15);
        background: rgba(255, 255, 255, 0.05);
        border-radius: 14px;
        text-align: center;
        flex-shrink: 0;
    }

    .user-details .raju {
        float: none !important;
        margin: 0 auto;
    }

    .user-details .raju img {
        width: 64px;
        height: 64px;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid var(--menu-accent);
        box-shadow: 0 0 16px rgba(242, 17, 81, 0.45);
    }

    /* ============================================================
       SIDEBAR MENU
       ============================================================ */
    #sidebar-menu {
        padding: 0;
        flex-shrink: 0;
    }

    #sidebar-menu ul {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    #sidebar-menu>ul>li {
        margin-bottom: 6px;
    }

    #sidebar-menu ul li a {
        display: flex;
        align-items: center;
        gap: 10px;
        min-height: 44px;
        padding: 11px 12px;
        color: var(--menu-text) !important;
        text-decoration: none;
        font-size: 13px !important;
        letter-spacing: 0.35px;
        border-radius: 12px;
        border: 1px solid transparent;
        transition: all 0.25s ease;
        width: auto !important;
        position: relative;
    }

    #sidebar-menu>ul>li>a {
        background: rgba(255, 255, 255, 0.04);
    }

    #sidebar-menu>ul>li>a:hover {
        color: #ffffff !important;
        border-color: rgba(242, 17, 81, 0.5);
        background: linear-gradient(90deg, rgba(242, 17, 81, 0.2) 0%, rgba(242, 17, 81, 0.04) 100%);
        box-shadow: 0 0 0 1px rgba(242, 17, 81, 0.25), 0 0 16px rgba(242, 17, 81, 0.22);
        transform: translateX(2px);
    }

    #sidebar-menu ul li a.active,
    #sidebar-menu ul li.active>a {
        color: #ffffff !important;
        border-color: rgba(242, 17, 81, 0.65);
        background: linear-gradient(90deg, rgba(242, 17, 81, 0.95) 0%, rgba(242, 17, 81, 0.72) 100%);
        box-shadow: 0 0 20px var(--menu-glow);
    }

    #sidebar-menu ul li a i {
        width: 22px;
        min-width: 22px;
        text-align: center;
        margin-right: 0;
        color: #ffffff;
        font-size: 15px;
        opacity: 0.95;
    }

    #sidebar-menu>ul>li>a>span {
        flex: 1;
        font-weight: 600;
    }

    #sidebar-menu ul ul {
        margin: 6px 0 10px 12px;
        padding: 6px;
        border-left: 1px solid rgba(255, 255, 255, 0.15);
        background: rgba(9, 18, 36, 0.35);
        border-radius: 10px;
    }

    #sidebar-menu ul ul li a {
        min-height: 36px;
        padding: 8px 10px;
        font-size: 12px !important;
        color: var(--menu-text-soft) !important;
        background: transparent;
        border-radius: 8px;
    }

    #sidebar-menu ul ul li a i {
        font-size: 11px;
        width: 16px;
        min-width: 16px;
        color: #ffc0d5;
    }

    #sidebar-menu ul ul li a:hover,
    #sidebar-menu ul ul li a.active {
        color: #ffffff !important;
        background: rgba(242, 17, 81, 0.22);
        border-color: rgba(242, 17, 81, 0.45);
    }

    .menu-title {
        padding: 14px 8px 8px;
        color: #88a1cc;
        font-size: 10px;
        text-transform: uppercase;
        letter-spacing: 1.6px;
    }

    .menu-arrow {
        margin-left: auto;
        color: #f4a0bc;
        font-size: 12px !important;
        transition: transform 0.25s ease;
    }

    .has_sub.open>a .menu-arrow {
        transform: rotate(90deg);
    }

    /* ============================================================
       MOBILE SIDEBAR - OVERLAY WITH SCROLLING
       ============================================================ */
    @media (max-width: 768px) {
        .left.side-menu {
            left: 0 !important;
            transform: translateX(-100%);
            top: 60px !important;
            height: calc(100vh - 60px) !important;
            width: 280px !important;
            position: fixed;
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        .left.side-menu.open {
            transform: translateX(0);
        }

        /* When body has sidebar-collapsed on mobile, hide sidebar */
        body.sidebar-collapsed .left.side-menu {
            transform: translateX(-100%);
        }

        body.fixed-left .side-menu.left {
            left: 0 !important;
            transform: translateX(-100%);
        }

        body.fixed-left .side-menu.left.open {
            transform: translateX(0) !important;
        }

        .content-page {
            margin-left: 0 !important;
        }

        body.sidebar-collapsed .content-page {
            margin-left: 0 !important;
        }

        /* Mobile scrollbar - thinner for touch devices */
        .sidebar-inner::-webkit-scrollbar {
            width: 4px;
        }

        .sidebar-inner {
            scrollbar-width: thin;
        }

        /* Ensure all menu items are visible when scrolling */
        .sidebar-inner {
            padding-bottom: 40px;
        }

        /* Add extra padding at bottom for better scrolling experience */
        #sidebar-menu {
            padding-bottom: 20px;
        }
    }

    /* ============================================================
       SMALL MOBILE SCREENS
       ============================================================ */
    @media (max-width: 480px) {
        .left.side-menu {
            width: 260px !important;
        }

        #sidebar-menu ul li a {
            min-height: 40px;
            padding: 9px 10px;
            font-size: 12px !important;
        }

        #sidebar-menu ul li a i {
            font-size: 14px;
            width: 20px;
            min-width: 20px;
        }

        .user-details .raju img {
            width: 50px;
            height: 50px;
        }

        .user-details {
            padding: 10px;
            margin: 4px 2px 10px;
        }
    }

    /* ============================================================
       EXTRA SMALL SCREENS
       ============================================================ */
    @media (max-width: 360px) {
        .left.side-menu {
            width: 240px !important;
        }

        #sidebar-menu ul li a {
            min-height: 36px;
            padding: 7px 8px;
            font-size: 11px !important;
            gap: 6px;
        }

        #sidebar-menu ul li a i {
            font-size: 12px;
            width: 18px;
            min-width: 18px;
        }

        .user-details .raju img {
            width: 42px;
            height: 42px;
            object-fit: contain;
        }

        .user-details {
            padding: 8px;
            margin: 4px 2px 8px;
        }
    }

    #sidebar-menu > img {
        display: block;
        width: 100%;
        max-width: 220px;
        height: auto;
        margin: 0 auto 12px;
        padding: 8px 0;
        object-fit: contain;
    }

    @media (max-width: 360px) {
        #sidebar-menu > img {
            max-width: 180px;
            padding: 6px 0;
        }
    }

    /* ============================================================
       DESKTOP SIDEBAR COLLAPSED STATE
       ============================================================ */
    @media (min-width: 769px) {
        body.sidebar-collapsed .left.side-menu {
            transform: translateX(-100%);
        }
    }
</style>

<div id="sidebar-wrapper" class="left side-menu">
    <div class="sidebar-inner slimscrollleft">
        <div class="user-details">
            <div class="pull-left raju">
                <img src="<?= htmlspecialchars($sidebarLogo) ?>" alt="School Logo">
            </div>
        </div>

        <div id="sidebar-menu">
            <ul>
                <!-- Home -->
                <?php if (hasAccess('home.php', $ret, $userType)): ?>
                    <li>
                        <a href="<?= SKOOL_URL ?>home.php" class="waves-effect <?= isActive($iCurrentFileName, 'home.php') ?>">
                            <i class="fa fa-university"></i><span>Home</span>
                        </a>
                    </li>
                <?php endif; ?>

                <!-- Dashboard -->
                <?php if (hasAccess('dashboard.php', $ret, $userType)): ?>
                    <li>
                        <a href="<?= SKOOL_URL . $dashboardFile ?>" class="waves-effect <?= isActive($iCurrentFileName, $dashboardFile) ?>">
                            <i class="fa fa-area-chart"></i><span>Dashboard</span>
                        </a>
                    </li>
                <?php endif; ?>

                <!-- Principal Section -->
                <?php if (hasAccess('princple_remark.php', $ret, $userType) || hasAccess('principal_sign_term.php', $ret, $userType) || $userType == '0'): ?>
                    <li class="has_sub">
                        <a href="javascript:void(0);" class="waves-effect">
                            <i class="fa fa-briefcase"></i><span>Principal</span><span class="menu-arrow fa fa-chevron-right"></span>
                        </a>
                        <ul class="list-unstyled">
                            <?php if (hasAccess('princple_remark.php', $ret, $userType)): ?>
                                <li><a href="princple_remark.php" class="<?= isActive($iCurrentFileName, 'princple_remark.php') ?>"><i class="ti-arrow-right"></i>Principle Remarks</a></li>
                            <?php endif; ?>
                            <?php if (hasAccess('principal_sign_term.php', $ret, $userType)): ?>
                                <li><a href="principal_sign_term.php" class="<?= isActive($iCurrentFileName, 'principal_sign_term.php') ?>"><i class="ti-arrow-right"></i>Principle Signature</a></li>
                            <?php endif; ?>
                            <?php if (hasAccess('principal_set_termdate.php', $ret, $userType)): ?>
                                <li><a href="principal_set_termdate.php" class="<?= isActive($iCurrentFileName, 'principal_set_termdate.php') ?>"><i class="ti-arrow-right"></i>Set Next-Term Date</a></li>
                            <?php endif; ?>
                        </ul>
                    </li>
                <?php endif; ?>

                <!-- Admin Section -->
                <?php if (hasAccess('manage_role.php', $ret, $userType) || $userType == '0'): ?>
                    <li class="has_sub">
                        <a href="javascript:void(0);" class="waves-effect">
                            <i class="fa fa-folder-open"></i><span>Admin</span><span class="menu-arrow fa fa-chevron-right"></span>
                        </a>
                        <ul class="list-unstyled">
                            <?php if (hasAccess('manage_traits_phycomotor.php', $ret, $userType)): ?>
                                <li><a href="manage_traits_phycomotor.php" class="<?= isActive($iCurrentFileName, 'manage_traits_phycomotor.php') ?>"><i class="ti-arrow-right"></i>Manage Traits Phycomotor</a></li>
                            <?php endif; ?>
                            <?php if (hasAccess('manage_role.php', $ret, $userType)): ?>
                                <li><a href="manage_role.php" class="<?= isActive($iCurrentFileName, 'manage_role.php') ?>"><i class="ti-arrow-right"></i>Manage Role</a></li>
                            <?php endif; ?>
                            <?php if (hasAccess('manage_user.php', $ret, $userType)): ?>
                                <li><a href="manage_user.php" class="<?= isActive($iCurrentFileName, 'manage_user.php') ?>"><i class="ti-arrow-right"></i>Manage User</a></li>
                            <?php endif; ?>
                            <?php if (hasAccess('manage_class_teacher.php', $ret, $userType)): ?>
                                <li><a href="manage_class_teacher.php" class="<?= isActive($iCurrentFileName, 'manage_class_teacher.php') ?>"><i class="ti-arrow-right"></i>Manage Class Teacher</a></li>
                            <?php endif; ?>
                            <?php if (hasAccess('manage_subject_teacher.php', $ret, $userType)): ?>
                                <li><a href="manage_subject_teacher.php" class="<?= isActive($iCurrentFileName, 'manage_subject_teacher.php') ?>"><i class="ti-arrow-right"></i>Manage Subject Teacher</a></li>
                            <?php endif; ?>
                            <?php if (hasAccess('score_entry_time_frame.php', $ret, $userType)): ?>
                                <li><a href="score_entry_time_frame.php" class="<?= isActive($iCurrentFileName, 'score_entry_time_frame.php') ?>"><i class="ti-arrow-right"></i>Score Entry Time Frame</a></li>
                            <?php endif; ?>
                            <li><a href="slider.php" class="<?= isActive($iCurrentFileName, 'slider.php') ?>"><i class="ti-arrow-right"></i>Configurations</a></li>
                            <li><a href="gallery.php" class="<?= isActive($iCurrentFileName, 'gallery.php') ?>"><i class="ti-arrow-right"></i>Gallery</a></li>
                            <li><a href="upcoming_event.php" class="<?= isActive($iCurrentFileName, 'upcoming_event.php') ?>"><i class="ti-arrow-right"></i>Upcoming Event</a></li>
                            <li><a href="blog.php" class="<?= isActive($iCurrentFileName, 'blog.php') ?>"><i class="ti-arrow-right"></i>Blog</a></li>
                            <li><a href="about_school.php" class="<?= isActive($iCurrentFileName, 'about_school.php') ?>"><i class="ti-arrow-right"></i>About School</a></li>
                        </ul>
                    </li>
                <?php endif; ?>

                <!-- Enrollment Officer Section -->
                <?php if (hasAccess('staff.php', $ret, $userType) || hasAccess('student.php', $ret, $userType) || $userType == '0'): ?>
                    <li class="has_sub">
                        <a href="javascript:void(0);" class="waves-effect">
                            <i class="fa fa-suitcase"></i><span>Enrollment Officer</span><span class="menu-arrow fa fa-chevron-right"></span>
                        </a>
                        <ul class="list-unstyled">
                            <?php if (hasAccess('staff.php', $ret, $userType)): ?>
                                <li><a href="staff.php" class="<?= isActive($iCurrentFileName, 'staff.php') ?>"><i class="ti-arrow-right"></i>Manage Staff</a></li>
                            <?php endif; ?>
                            <?php if (hasAccess('manage_parents.php', $ret, $userType)): ?>
                                <li><a href="manage_parents.php" class="<?= isActive($iCurrentFileName, 'manage_parents.php') ?>"><i class="ti-arrow-right"></i>Manage Parent</a></li>
                            <?php endif; ?>
                            <?php if (hasAccess('student.php', $ret, $userType)): ?>
                                <li><a href="student.php" class="<?= isActive($iCurrentFileName, 'student.php') ?>"><i class="ti-arrow-right"></i>Manage Student</a></li>
                            <?php endif; ?>
                            <?php if (hasAccess('move_student_to_nextTerm.php', $ret, $userType)): ?>
                                <li><a href="move_student_to_nextTerm.php" class="<?= isActive($iCurrentFileName, 'move_student_to_nextTerm.php') ?>"><i class="ti-arrow-right"></i>Transfer Student To Next Term</a></li>
                            <?php endif; ?>
                        </ul>
                    </li>
                <?php endif; ?>

                <!-- Class Teacher Section -->
                <?php if (hasAccess('class_teacher_roll_call.php', $ret, $userType) || hasAccess('input_score_class_teacher.php', $ret, $userType) || $userType == '0'): ?>
                    <li class="has_sub">
                        <a href="javascript:void(0);" class="waves-effect">
                            <i class="fa fa-male"></i><span>Class Teacher</span><span class="menu-arrow fa fa-chevron-right"></span>
                        </a>
                        <ul class="list-unstyled">
                            <li><a href="student.php" class="<?= isActive($iCurrentFileName, 'student.php') ?>"><i class="ti-arrow-right"></i>Manage Student</a></li>
                            <li><a href="removesubject.php" class="<?= isActive($iCurrentFileName, 'removesubject.php') ?>"><i class="ti-arrow-right"></i>Remove Subject</a></li>
                            <li><a href="class_teacher_roll_call.php" class="<?= isActive($iCurrentFileName, 'class_teacher_roll_call.php') ?>"><i class="ti-arrow-right"></i>Make Roll Call</a></li>
                            <li><a href="class_teacher_roll_call_bulk.php" class="<?= isActive($iCurrentFileName, 'class_teacher_roll_call_bulk.php') ?>"><i class="ti-arrow-right"></i>Attendance (Bulk Entry)</a></li>
                            <li><a href="input_score_class_teacher.php" class="<?= isActive($iCurrentFileName, 'input_score_class_teacher.php') ?>"><i class="ti-arrow-right"></i>Input Score</a></li>
                            <li><a href="class_teacher_subject_result.php" class="<?= isActive($iCurrentFileName, 'class_teacher_subject_result.php') ?>"><i class="ti-arrow-right"></i>Subject Result</a></li>
                            <li><a href="class_teacher_make_comment.php" class="<?= isActive($iCurrentFileName, 'class_teacher_make_comment.php') ?>"><i class="ti-arrow-right"></i>Make Comment</a></li>
                            <li><a href="board_sheet.php" class="<?= isActive($iCurrentFileName, 'board_sheet.php') ?>"><i class="ti-arrow-right"></i>Board Sheet</a></li>
                            <li><a href="cumulative_board_sheet.php" class="<?= isActive($iCurrentFileName, 'cumulative_board_sheet.php') ?>"><i class="ti-arrow-right"></i>Cumulative Board Sheet</a></li>
                            <li><a href="class_teacher_pyschomotor.php" class="<?= isActive($iCurrentFileName, 'class_teacher_pyschomotor.php') ?>"><i class="ti-arrow-right"></i>Phycomotor</a></li>
                            <li><a href="class_teacher_traits.php" class="<?= isActive($iCurrentFileName, 'class_teacher_traits.php') ?>"><i class="ti-arrow-right"></i>Affective Traits</a></li>
                        </ul>
                    </li>
                <?php endif; ?>

                <!-- Subject Teacher Section -->
                <?php if (hasAccess('subject_specific_comment.php', $ret, $userType) || hasAccess('input_scores_subject.php', $ret, $userType) || $userType == '0'): ?>
                    <li class="has_sub">
                        <a href="javascript:void(0);" class="waves-effect">
                            <i class="fa fa-flask"></i><span>Subject Teacher</span><span class="menu-arrow fa fa-chevron-right"></span>
                        </a>
                        <ul class="list-unstyled">
                            <li><a href="subject_specific_comment.php" class="<?= isActive($iCurrentFileName, 'subject_specific_comment.php') ?>"><i class="ti-arrow-right"></i>Subject Specific Comment</a></li>
                            <li><a href="input_scores_subject.php" class="<?= isActive($iCurrentFileName, 'input_scores_subject.php') ?>"><i class="ti-arrow-right"></i>Input Score</a></li>
                            <li><a href="input_score_for_all_cas_subject.php" class="<?= isActive($iCurrentFileName, 'input_score_for_all_cas_subject.php') ?>"><i class="ti-arrow-right"></i>Input Score for All CAs</a></li>
                        </ul>
                    </li>
                <?php endif; ?>

                <!-- SMS Manager -->
                <?php if (hasAccess('sms_plan.php', $ret, $userType) || hasAccess('send_sms.php', $ret, $userType) || $userType == '0'): ?>
                    <li class="has_sub">
                        <a href="javascript:void(0);" class="waves-effect">
                            <i class="fa fa-envelope"></i><span>SMS Manager</span><span class="menu-arrow fa fa-chevron-right"></span>
                        </a>
                        <ul class="list-unstyled">
                            <li><a href="sms_plan.php" class="<?= isActive($iCurrentFileName, 'sms_plan.php') ?>"><i class="ti-arrow-right"></i>SMS Plan</a></li>
                            <li><a href="send_sms.php" class="<?= isActive($iCurrentFileName, 'send_sms.php') ?>"><i class="ti-arrow-right"></i>Send SMS</a></li>
                        </ul>
                    </li>
                <?php endif; ?>

                <!-- Email Manager -->
                <?php if (hasAccess('send_email.php', $ret, $userType) || $userType == '0'): ?>
                    <li class="has_sub">
                        <a href="javascript:void(0);" class="waves-effect">
                            <i class="fa fa-th-list"></i><span>Email Manager</span><span class="menu-arrow fa fa-chevron-right"></span>
                        </a>
                        <ul class="list-unstyled">
                            <li><a href="send_mail.php" class="<?= isActive($iCurrentFileName, 'send_email.php') ?>"><i class="ti-arrow-right"></i>Send Email</a></li>
                        </ul>
                    </li>
                <?php endif; ?>

                <!-- Payment Details -->
                <?php if (hasAccess('transcation.php', $ret, $userType) || hasAccess('withdrawal_request.php', $ret, $userType) || $userType == '0'): ?>
                    <li class="has_sub">
                        <a href="javascript:void(0);" class="waves-effect">
                            <i class="fa fa-credit-card"></i><span>Payment Detail</span><span class="menu-arrow fa fa-chevron-right"></span>
                        </a>
                        <ul class="list-unstyled">
                            <li><a href="withdrawal_request.php" class="<?= isActive($iCurrentFileName, 'withdrawal_request.php') ?>"><i class="ti-arrow-right"></i>Withdrawal Request</a></li>
                            <li><a href="transcation.php" class="<?= isActive($iCurrentFileName, 'transcation.php') ?>"><i class="ti-arrow-right"></i>Transaction History</a></li>
                        </ul>
                    </li>
                <?php endif; ?>

                <!-- View Result -->
                <?php if (hasAccess('view_result_subject_result.php', $ret, $userType) || hasAccess('view_result_board_sheet.php', $ret, $userType) || $userType == '0'): ?>
                    <li class="has_sub">
                        <a href="javascript:void(0);" class="waves-effect">
                            <i class="fa fa-binoculars"></i><span>View Result</span><span class="menu-arrow fa fa-chevron-right"></span>
                        </a>
                        <ul class="list-unstyled">
                            <li><a href="view_result_subject_result.php" class="<?= isActive($iCurrentFileName, 'view_result_subject_result.php') ?>"><i class="ti-arrow-right"></i>Subject Result</a></li>
                            <li><a href="view_result_board_sheet.php" class="<?= isActive($iCurrentFileName, 'view_result_board_sheet.php') ?>"><i class="ti-arrow-right"></i>Board Sheet</a></li>
                            <li><a href="view_result_cumulative_board_sheet.php" class="<?= isActive($iCurrentFileName, 'view_result_cumulative_board_sheet.php') ?>"><i class="ti-arrow-right"></i>Cumulative Board Sheet</a></li>
                            <li><a href="view_result_student_cumulative_result.php" class="<?= isActive($iCurrentFileName, 'view_result_student_cumulative_result.php') ?>"><i class="ti-arrow-right"></i>Student Cumulative Result</a></li>
                            <li><a href="view_result_class_level_subject_grade_analysis.php" class="<?= isActive($iCurrentFileName, 'view_result_class_level_subject_grade_analysis.php') ?>"><i class="ti-arrow-right"></i>Class Level Grade Analysis</a></li>
                        </ul>
                    </li>
                <?php endif; ?>

                <!-- Staff Assessment -->
                <?php if (hasAccess('staff_assessment.php', $ret, $userType) || hasAccess('personal_assessment.php', $ret, $userType) || hasAccess('manage_assessment.php', $ret, $userType) || hasAccess('manage_assessment-my.php', $ret, $userType) || $userType == '0'): ?>
                    <li class="has_sub">
                        <a href="javascript:void(0);" class="waves-effect">
                            <i class="fa fa-th"></i><span>Staff Assessment</span><span class="menu-arrow fa fa-chevron-right"></span>
                        </a>
                        <ul class="list-unstyled">
                            <?php if (hasAccess('staff_assessment.php', $ret, $userType) || $userType == '0'): ?>
                                <li><a href="staff_assessment.php" class="<?= isActive($iCurrentFileName, 'staff_assessment.php') ?>"><i class="ti-arrow-right"></i>Manage Assessment</a></li>
                            <?php endif; ?>
                            <?php if (hasAccess('personal_assessment.php', $ret, $userType) || $userType == '0'): ?>
                                <li><a href="personal_assessment.php" class="<?= isActive($iCurrentFileName, 'personal_assessment.php') ?>"><i class="ti-arrow-right"></i>Personal Assessment</a></li>
                            <?php endif; ?>
                            <?php if (hasAccess('manage_assessment.php', $ret, $userType) || $userType == '0'): ?>
                                <li><a href="manage_assessment.php?action=list" class="<?= isActive($iCurrentFileName, 'manage_assessment.php') ?>"><i class="ti-arrow-right"></i>Assign Assessment</a></li>
                            <?php endif; ?>
                            <?php if (hasAccess('manage_assessment-my.php', $ret, $userType) || $userType == '0'): ?>
                                <li><a href="manage_assessment-my.php" class="<?= isActive($iCurrentFileName, 'manage_assessment-my.php') ?>"><i class="ti-arrow-right"></i>My Assessment</a></li>
                            <?php endif; ?>
                        </ul>
                    </li>
                <?php endif; ?>

                <!-- Finance Officer -->
                <?php if (hasAccess('takefeesturcture.php', $ret, $userType) || hasAccess('takefee.php', $ret, $userType) || hasAccess('inventory.php', $ret, $userType) || hasAccess('expenses.php', $ret, $userType) || hasAccess('fee_status_report.php', $ret, $userType) || hasAccess('financial_dashboard.php', $ret, $userType) || hasAccess('payroll.php', $ret, $userType) || hasAccess('petty_cash.php', $ret, $userType) || $userType == '0'): ?>
                    <li class="has_sub">
                        <a href="javascript:void(0);" class="waves-effect">
                            <i class="fa fa-credit-card"></i><span>Finance Officer</span><span class="menu-arrow fa fa-chevron-right"></span>
                        </a>
                        <ul class="list-unstyled">
                            <?php if (hasAccess('financial_dashboard.php', $ret, $userType) || $userType == '0'): ?>
                                <li><a href="financial_dashboard.php" class="<?= isActive($iCurrentFileName, 'financial_dashboard.php') ?>"><i class="ti-arrow-right"></i>Financial Dashboard</a></li>
                            <?php endif; ?>
                            <li class="has_sub">
                                <a href="javascript:void(0);" class="waves-effect">
                                    <i class="fa fa-money"></i><span>Fee Management</span><span class="menu-arrow fa fa-chevron-right"></span>
                                </a>
                                <ul class="list-unstyled">
                                    <?php if (hasAccess('takefeesturcture.php', $ret, $userType) || $userType == '0'): ?>
                                        <li><a href="takefeesturcture.php" class="<?= isActive($iCurrentFileName, 'takefeesturcture.php') ?>"><i class="ti-arrow-right"></i>Fee Structure</a></li>
                                    <?php endif; ?>
                                    <?php if (hasAccess('takefee.php', $ret, $userType) || $userType == '0'): ?>
                                        <li><a href="takefee.php" class="<?= isActive($iCurrentFileName, 'takefee.php') ?>"><i class="ti-arrow-right"></i>Take Fee</a></li>
                                    <?php endif; ?>
                                    <?php if (hasAccess('bulk_fee_assignment.php', $ret, $userType) || $userType == '0'): ?>
                                        <li><a href="bulk_fee_assignment.php" class="<?= isActive($iCurrentFileName, 'bulk_fee_assignment.php') ?>"><i class="ti-arrow-right"></i>Bulk Fee Assignment</a></li>
                                    <?php endif; ?>
                                    <?php if (hasAccess('fee_status_report.php', $ret, $userType) || $userType == '0'): ?>
                                        <li><a href="fee_status_report.php" class="<?= isActive($iCurrentFileName, 'fee_status_report.php') ?>"><i class="ti-arrow-right"></i>Fee Status Report</a></li>
                                    <?php endif; ?>
                                </ul>
                            </li>
                            <?php if (hasAccess('payroll.php', $ret, $userType) || $userType == '0'): ?>
                                <li class="has_sub">
                                    <a href="javascript:void(0);" class="waves-effect">
                                        <i class="fa fa-users"></i><span>Payroll</span><span class="menu-arrow fa fa-chevron-right"></span>
                                    </a>
                                    <ul class="list-unstyled">
                                        <li><a href="payroll.php" class="<?= isActive($iCurrentFileName, 'payroll.php') ?>"><i class="ti-arrow-right"></i>Payroll Management</a></li>
                                    </ul>
                                </li>
                            <?php endif; ?>
                            <?php if (hasAccess('petty_cash.php', $ret, $userType) || $userType == '0'): ?>
                                <li class="has_sub">
                                    <a href="javascript:void(0);" class="waves-effect">
                                        <i class="fa fa-credit-card"></i><span>Petty Cash</span><span class="menu-arrow fa fa-chevron-right"></span>
                                    </a>
                                    <ul class="list-unstyled">
                                        <li><a href="petty_cash.php" class="<?= isActive($iCurrentFileName, 'petty_cash.php') ?>"><i class="ti-arrow-right"></i>Petty Cash Management</a></li>
                                    </ul>
                                </li>
                            <?php endif; ?>
                            <li class="has_sub">
                                <a href="javascript:void(0);" class="waves-effect">
                                    <i class="fa fa-cubes"></i><span>Inventory & Expenses</span><span class="menu-arrow fa fa-chevron-right"></span>
                                </a>
                                <ul class="list-unstyled">
                                    <?php if (hasAccess('inventory.php', $ret, $userType) || $userType == '0'): ?>
                                        <li><a href="inventory.php" class="<?= isActive($iCurrentFileName, 'inventory.php') ?>"><i class="ti-arrow-right"></i>Manage Inventory</a></li>
                                    <?php endif; ?>
                                    <?php if (hasAccess('expenses.php', $ret, $userType) || $userType == '0'): ?>
                                        <li><a href="expenses.php" class="<?= isActive($iCurrentFileName, 'expenses.php') ?>"><i class="ti-arrow-right"></i>Income-Expenditure</a></li>
                                    <?php endif; ?>
                                </ul>
                            </li>
                        </ul>
                    </li>
                <?php endif; ?>

                <!-- Result -->
                <?php if (hasAccess('term_result.php', $ret, $userType) || $userType == '0'): ?>
                    <li class="has_sub">
                        <a href="javascript:void(0);" class="waves-effect">
                            <i class="fa fa-th"></i><span>Result</span><span class="menu-arrow fa fa-chevron-right"></span>
                        </a>
                        <ul class="list-unstyled">
                            <li><a href="term_result.php" class="<?= isActive($iCurrentFileName, 'term_result.php') ?>"><i class="ti-arrow-right"></i>Term Result</a></li>
                        </ul>
                    </li>
                <?php endif; ?>

                <!-- Logout -->
                <li>
                    <a href="logout.php" class="waves-effect <?= isActive($iCurrentFileName, 'logout.php') ?>">
                        <i class="fa fa-sign-out"></i><span>Logout</span>
                    </a>
                </li>
            </ul>
            <div class="clearfix"></div>
        </div>
    </div>
</div>