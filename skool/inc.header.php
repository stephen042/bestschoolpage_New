<?php

/**
 * Header Component - Fixed for PHP 8.x
 * Displays school name and user profile with proper error handling
 * FIXED: Sidebar toggle with proper spacing and Font Awesome bars icon
 * IMPROVED: Mobile responsive with better school name display
 * FIXED: Desktop topbar alignment issue
 * FIXED: School name visible on all devices
 */

// Prevent direct access
if (!defined('DS')) {
    require_once dirname(__DIR__) . '/config.php';
}

// Get school name with proper fallback
$skoolname = [];
$schoolName = 'School Management System';
$schoolIdForHeader = isset($create_by_userid) ? (int)$create_by_userid : (int)($_SESSION['userid'] ?? 0);
$headerDisplayName = $schoolName;

if (!empty($schoolIdForHeader)) {
    $skoolname = db_get_row("SELECT * FROM school_register WHERE id = ?", [$schoolIdForHeader]);
    if (!empty($skoolname)) {
        $schoolName = $skoolname['name'] ?? 'School Management System';
        $headerDisplayName = $schoolName;
    }
}

// For teacher/staff logins, display full staff name in header title.
$sessionUserId = (int)($_SESSION['userid'] ?? 0);
$sessionUserType = (string)($_SESSION['usertype'] ?? '');
$sessionUsername = (string)($_SESSION['username'] ?? '');
$sessionEmail = (string)($_SESSION['email'] ?? '');

if ($sessionUserType === '1' && $sessionUserId > 0 && $schoolIdForHeader > 0 && $sessionUserId !== $schoolIdForHeader) {
    $staffRow = db_get_row(
        "SELECT first_name, last_name
         FROM staff_manage
         WHERE create_by_userid = ?
           AND (staff_id = ? OR email = ?)
         ORDER BY id DESC
         LIMIT 1",
        [$schoolIdForHeader, $sessionUsername, $sessionEmail]
    );

    if (!empty($staffRow)) {
        $fullName = trim((string)($staffRow['first_name'] ?? '') . ' ' . (string)($staffRow['last_name'] ?? ''));
        if ($fullName !== '') {
            $headerDisplayName = $fullName;
        }
    }
}

// Get user profile image
$userImage = 'assets-new/images/icon.jpg';
if (!empty($_SESSION['userid'])) {
    // Try to get from admin_login
    $user = db_get_row("SELECT profileimage FROM admin_login WHERE id = ?", [$_SESSION['userid']]);
    if (!empty($user['profileimage']) && file_exists("../uploads/" . $user['profileimage'])) {
        $userImage = "../uploads/" . $user['profileimage'];
    } else {
        // Try from student_guardian
        $parent = db_get_row("SELECT logo FROM student_guardian WHERE id = ?", [$_SESSION['userid']]);
        if (!empty($parent['logo']) && file_exists("../uploads/" . $parent['logo'])) {
            $userImage = "../uploads/" . $parent['logo'];
        }
    }
}
?>
<style>
    /* ============================================================
       TOPBAR - HEADER STYLES
       ============================================================ */
    .topbar {
        background: #fff;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        z-index: 1000;
        height: 60px;
        min-height: 60px;
        display: flex;
        align-items: stretch;
    }

    .topbar-left {
        width: 240px;
        background: #1B3058;
        height: 60px;
        min-height: 60px;
        position: relative;
        transition: width 0.3s ease;
        overflow: hidden;
        flex-shrink: 0;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .topbar-left .text-center {
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    /* School logo/icon inside topbar-left */
    .topbar-left .logo-icon {
        color: #fff;
        font-size: 22px;
        font-weight: 700;
        letter-spacing: 1px;
        text-decoration: none;
        padding: 0 10px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 100%;
    }

    .topbar-left .logo-icon .logo-text {
        color: #fff;
    }

    .topbar-left .logo-icon .logo-highlight {
        color: #f21151;
    }

    .navbar {
        margin-left: 0;
        background: #fff;
        border: none;
        border-radius: 0;
        margin-bottom: 0;
        min-height: 60px;
        height: 60px;
        transition: margin-left 0.3s ease;
        padding: 0;
        flex: 1;
        display: flex;
        align-items: center;
    }

    .navbar .container-fluid {
        width: 100%;
        padding: 0 15px;
        height: 100%;
        display: flex;
        align-items: center;
    }

    .navbar .container-fluid .row {
        height: 100%;
        display: flex;
        align-items: center;
        margin: 0;
        width: 100%;
        flex-wrap: nowrap;
    }

    /* ============================================================
       MENU TOGGLE BUTTON
       ============================================================ */
    .button-menu-mobile {
        background: transparent;
        border: none;
        color: #1B3058;
        font-size: 20px;
        padding: 8px 10px;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s ease;
        margin: 0;
        line-height: 1;
        width: 40px;
        height: 40px;
        border-radius: 4px;
        flex-shrink: 0;
    }

    .button-menu-mobile:hover {
        color: #f21151;
        background: rgba(27, 48, 88, 0.05);
    }

    .button-menu-mobile:focus {
        outline: none;
    }

    .button-menu-mobile i {
        font-size: 20px;
    }

    /* ============================================================
       SCHOOL NAME STYLES - VISIBLE ON ALL DEVICES
       ============================================================ */
    .ven {
        color: #1B3058;
        font-size: 18px;
        font-weight: 600;
        margin: 0;
        padding: 0 5px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        line-height: 60px;
        height: 60px;
        max-width: 100%;
        display: block;
    }

    /* ============================================================
       HEADER LAYOUT
       ============================================================ */
    .taugl {
        display: flex;
        align-items: center;
        height: 60px;
        flex: 1;
        min-width: 0;
        max-width: calc(100% - 160px);
    }

    .taugl .row {
        display: flex;
        align-items: center;
        width: 100%;
        margin: 0;
        flex-wrap: nowrap;
    }

    .taugl .col-md-3,
    .taugl .col-sm-3,
    .taugl .col-xs-3 {
        flex: 0 0 auto;
        width: auto;
        padding: 0;
        display: flex;
        align-items: center;
        flex-shrink: 0;
    }

    .taugl .col-md-9,
    .taugl .col-sm-9,
    .taugl .col-xs-9 {
        flex: 1;
        padding: 0 5px;
        min-width: 0;
        overflow: hidden;
    }

    /* ============================================================
       PROFILE / SETTINGS DROPDOWN
       ============================================================ */
    .navbar-nav {
        margin: 0;
        display: flex;
        align-items: center;
        height: 60px;
        padding-right: 0;
        flex-shrink: 0;
    }

    .navbar-nav>li {
        float: left;
        list-style: none;
    }

    .navbar-nav>li>.profile {
        padding: 4px 10px 4px 8px;
        display: flex;
        align-items: center;
        gap: 6px;
        cursor: pointer;
        border: 2px solid #f21151;
        border-radius: 999px;
        background: #1B3058;
        min-height: 36px;
        text-decoration: none;
        transition: all 0.3s ease;
    }

    .navbar-nav>li>.profile:hover {
        background: #142445;
        text-decoration: none;
    }

    .navbar-nav>li>.profile .settings-icon {
        color: #f21151;
        font-size: 13px;
    }

    .navbar-nav>li>.profile .settings-text {
        color: #ffffff;
        font-weight: 600;
        font-size: 10px;
        text-transform: uppercase;
        letter-spacing: 0.25px;
        white-space: nowrap;
    }

    .navbar-nav>li>.profile img {
        width: 26px;
        height: 26px;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid #f21151;
        flex-shrink: 0;
    }

    /* ============================================================
       DROPDOWN MENU
       ============================================================ */
    .dropdown-menu {
        position: absolute;
        top: 100%;
        right: 0;
        left: auto;
        z-index: 1000;
        min-width: 180px;
        padding: 5px 0;
        margin: 2px 0 0;
        font-size: 14px;
        text-align: left;
        list-style: none;
        background-color: #fff;
        background-clip: padding-box;
        border: 1px solid #e3e3e3;
        border-radius: 4px;
        box-shadow: 0 6px 12px rgba(0, 0, 0, .175);
    }

    .dropdown-menu>li>a {
        display: block;
        padding: 8px 20px;
        clear: both;
        font-weight: 400;
        line-height: 1.42857143;
        color: #333;
        white-space: nowrap;
        text-decoration: none;
        transition: all 0.2s ease;
    }

    .dropdown-menu>li>a:hover {
        background-color: #f5f5f5;
        color: #1B3058;
    }

    .dropdown-menu>li>a i {
        margin-right: 8px;
        width: 16px;
        text-align: center;
    }

    .dropdown-menu .divider {
        height: 1px;
        margin: 4px 0;
        overflow: hidden;
        background-color: #e5e5e5;
    }

    /* ============================================================
       PULL RIGHT
       ============================================================ */
    .pull-right {
        float: right !important;
    }

    .clearfix {
        clear: both;
    }

    /* ============================================================
       RESPONSIVE - SIDEBAR COLLAPSED STATE
       ============================================================ */
    body.sidebar-collapsed .topbar-left {
        width: 60px;
    }

    body.sidebar-collapsed .navbar {
        margin-left: 0;
    }

    /* ============================================================
       RESPONSIVE - TABLETS & SMALL DESKTOP
       ============================================================ */
    @media (max-width: 1024px) {
        .navbar-nav>li>.profile .settings-text {
            display: none;
        }

        .navbar-nav>li>.profile {
            padding: 4px 8px;
            min-height: 34px;
        }

        .navbar-nav>li>.profile img {
            width: 24px;
            height: 24px;
        }

        .ven {
            font-size: 16px;
        }

        .topbar-left .logo-icon {
            font-size: 18px;
        }
    }

    @media (max-width: 768px) {
        .topbar-left {
            width: 60px;
        }

        .navbar {
            margin-left: 0;
        }

        .taugl {
            max-width: calc(100% - 110px);
            flex: 1;
        }

        .ven {
            font-size: 14px;
            line-height: 60px;
            height: 60px;
            display: block !important;
        }

        .taugl .col-md-9,
        .taugl .col-sm-9,
        .taugl .col-xs-9 {
            padding: 0 3px;
            flex: 1;
            min-width: 0;
            overflow: hidden;
        }

        .button-menu-mobile {
            font-size: 18px;
            padding: 6px 8px;
            width: 36px;
            height: 36px;
        }

        .button-menu-mobile i {
            font-size: 18px;
        }

        .navbar-nav>li>.profile .settings-text {
            display: none;
        }

        .navbar-nav>li>.profile {
            padding: 3px 6px;
            min-height: 30px;
            gap: 4px;
        }

        .navbar-nav>li>.profile img {
            width: 22px;
            height: 22px;
        }

        .navbar-nav>li>.profile .settings-icon {
            font-size: 12px;
        }

        .navbar .container-fluid {
            padding: 0 5px;
        }

        .topbar-left .logo-icon {
            font-size: 14px;
        }

        /* Make sure school name container has enough space */
        .taugl .row {
            flex-wrap: nowrap;
        }
    }

    @media (max-width: 480px) {
        .topbar {
            height: 55px;
            min-height: 55px;
        }

        .topbar-left {
            height: 55px;
            min-height: 55px;
            width: 50px;
        }

        .navbar {
            min-height: 55px;
            height: 55px;
        }

        .taugl {
            height: 55px;
            max-width: calc(100% - 90px);
            flex: 1;
        }

        .ven {
            font-size: 12px;
            line-height: 55px;
            height: 55px;
            padding: 0 3px;
            display: block !important;
        }

        .button-menu-mobile {
            font-size: 16px;
            padding: 4px 6px;
            width: 32px;
            height: 32px;
        }

        .button-menu-mobile i {
            font-size: 16px;
        }

        .navbar-nav {
            height: 55px;
        }

        .navbar-nav>li>.profile {
            padding: 2px 5px;
            min-height: 28px;
            gap: 3px;
            border-width: 1.5px;
        }

        .navbar-nav>li>.profile img {
            width: 20px;
            height: 20px;
            border-width: 1.5px;
        }

        .navbar-nav>li>.profile .settings-icon {
            font-size: 11px;
        }

        .navbar .container-fluid {
            padding: 0 3px;
        }

        .taugl .col-md-3,
        .taugl .col-sm-3,
        .taugl .col-xs-3 {
            padding: 0;
            flex-shrink: 0;
        }

        .taugl .col-md-9,
        .taugl .col-sm-9,
        .taugl .col-xs-9 {
            padding: 0 2px;
            flex: 1;
            min-width: 0;
            overflow: hidden;
        }

        .topbar-left .logo-icon {
            font-size: 12px;
            padding: 0 5px;
        }
    }

    /* ============================================================
       EXTRA SMALL SCREENS (<= 360px)
       ============================================================ */
    @media (max-width: 360px) {
        .topbar {
            height: 50px;
            min-height: 50px;
        }

        .topbar-left {
            height: 50px;
            min-height: 50px;
            width: 44px;
        }

        .navbar {
            min-height: 50px;
            height: 50px;
        }

        .taugl {
            height: 50px;
            max-width: calc(100% - 80px);
            flex: 1;
        }

        .ven {
            font-size: 10px;
            line-height: 50px;
            height: 50px;
            display: block !important;
        }

        .button-menu-mobile {
            font-size: 14px;
            padding: 3px 5px;
            width: 28px;
            height: 28px;
        }

        .button-menu-mobile i {
            font-size: 14px;
        }

        .navbar-nav {
            height: 50px;
        }

        .navbar-nav>li>.profile {
            padding: 2px 4px;
            min-height: 24px;
            gap: 2px;
        }

        .navbar-nav>li>.profile img {
            width: 18px;
            height: 18px;
        }

        .navbar-nav>li>.profile .settings-icon {
            font-size: 10px;
        }

        .topbar-left .logo-icon {
            font-size: 10px;
            padding: 0 3px;
        }

        .taugl .col-md-9,
        .taugl .col-sm-9,
        .taugl .col-xs-9 {
            padding: 0 2px;
        }
    }

    /* ============================================================
       ANIMATION FOR SIDEBAR TOGGLE
       ============================================================ */
    .topbar-left,
    .navbar {
        transition: width 0.3s ease, margin-left 0.3s ease;
    }

    /* ============================================================
       SIDEBAR & CONTENT TRANSITION BASE
       ============================================================ */
    #sidebar-wrapper {
        position: fixed;
        top: 60px;
        left: 0;
        width: 240px;
        height: calc(100vh - 60px);
        transition: transform 0.3s ease-in-out;
        z-index: 1000;
    }

    .content-page,
    .topbar .navbar,
    .footer {
        transition: margin-left 0.3s ease-in-out;
    }

    /* ============================================================
       DESKTOP TOGGLE (DESKTOP PUSH EFFECT)
       ============================================================ */
    @media (min-width: 769px) {
        .content-page {
            margin-left: 240px;
        }

        body.sidebar-collapsed #sidebar-wrapper {
            transform: translateX(-100%);
        }

        body.sidebar-collapsed .content-page,
        body.sidebar-collapsed .topbar .navbar,
        body.sidebar-collapsed .footer {
            margin-left: 0 !important;
        }

        #sidebar-wrapper {
            top: 60px;
        }
    }

    /* ============================================================
       MOBILE SIDEBAR (OVERLAY EFFECT)
       ============================================================ */
    @media (max-width: 768px) {
        #sidebar-wrapper {
            width: 280px !important;
            transform: translateX(-100%);
            top: 60px;
            height: calc(100vh - 60px);
            overflow-y: auto;
            -webkit-overflow-scrolling: touch;
        }

        #sidebar-wrapper .nav-label,
        #sidebar-wrapper span,
        #sidebar-wrapper .menu-title {
            display: inline-block !important;
        }

        body.mobile-sidebar-open #sidebar-wrapper {
            transform: translateX(0);
        }

        .content-page {
            margin-left: 0 !important;
        }

        #sidebar-wrapper::-webkit-scrollbar {
            width: 4px;
        }

        #sidebar-wrapper::-webkit-scrollbar-track {
            background: rgba(0, 0, 0, 0.1);
        }

        #sidebar-wrapper::-webkit-scrollbar-thumb {
            background: #f21151;
            border-radius: 4px;
        }
    }

    /* Hide the mobile title by default on desktop/large screens */
    .mobile-school-name {
        display: none;
    }

    /* On mobile screens (768px and below) */
    @media (max-width: 768px) {

        .taugl {
            position: relative;
            flex: 1;
            max-width: none;
        }

        .mobile-school-name {
            position: absolute;
            left: 50%;
            top: 80%;
            transform: translate(-100%, -100%);
            width: calc(100% - -100px); /* leaves space for menu button */
            display: flex;
            justify-content: center;
            align-items: center;
            pointer-events: none;
            z-index: 1;
        }

        .mobile-school-name h2 {
            margin: 0;
            width: 100%;
            text-align: center;
            font-size: 13px;
            font-weight: 600;
            color: #1B3058;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .desktop-school-name {
            display: none !important;
        }
    }

    /* Further reduce font size on extra small screens (e.g., phones below 480px) */
    @media (max-width: 480px) {
        .mobile-school-name h2.text-sm {
            font-size: 11px;
        }
    }
</style>

<div class="topbar">
    <div class="navbar navbar-default" role="navigation">
        <div class="container-fluid">
            <div class="row">
                <div class="pull-left taugl">
                    <div class="row">
                        <div class="col-md-3 col-sm-3 col-xs-3">
                            <button class="button-menu-mobile open-left"
                                type="button"
                                aria-label="Toggle Sidebar">
                                <i class="fa fa-bars"></i>
                            </button>
                        </div>

                        <!-- Mobile view school name -->
                        <div class="mobile-school-name">
                            <h2 class="text-sm" title="<?= htmlspecialchars($headerDisplayName) ?>">
                                <?= htmlspecialchars($headerDisplayName) ?>
                            </h2>
                        </div>

                        <!-- Desktop view school name -->
                        <div class="col-md-9 col-sm-9 col-xs-9 desktop-school-name">
                            <h2 class="ven shclnmdcls" title="<?= htmlspecialchars($headerDisplayName) ?>">
                                <?= htmlspecialchars($headerDisplayName) ?>
                            </h2>
                        </div>
                    </div>
                </div>

                <ul class="nav navbar-nav navbar-right pull-right">
                    <li class="dropdown">
                        <a href="javascript:void(0);" class="dropdown-toggle profile" data-toggle="dropdown" aria-expanded="false">
                            <i class="fa fa-cog settings-icon"></i>
                            <span class="settings-text">Settings</span>
                            <img src="<?= htmlspecialchars($userImage) ?>" alt="User Profile">
                        </a>
                        <ul class="dropdown-menu">
                            <li><a href="<?= SKOOL_URL ?>login_pass.php"><i class="fa fa-key"></i> Change Password</a></li>
                            <li class="divider"></li>
                            <li><a href="<?= SKOOL_URL ?>logout.php"><i class="fa fa-sign-out"></i> Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        // Dropdown initialization
        if (typeof $.fn.dropdown !== 'undefined') {
            $('.dropdown-toggle').dropdown();
        }

        // UNIFIED SIDEBAR TOGGLE HANDLER
        $(document).off('click', '.button-menu-mobile').on('click', '.button-menu-mobile', function(e) {
            e.preventDefault();

            if ($(window).width() > 768) {
                // Desktop: Slide sidebar off-screen left / restore
                $('body').toggleClass('sidebar-collapsed');
            } else {
                // Mobile: Slide FULL sidebar in/out from left
                $('body').toggleClass('mobile-sidebar-open');
            }

            // Trigger resize event for responsive charts or DataTables
            $(window).trigger('resize');
        });

        // Close mobile menu if user clicks outside the menu
        $(document).on('click', function(e) {
            if ($(window).width() <= 768) {
                if (!$(e.target).closest('#sidebar-wrapper, .button-menu-mobile').length) {
                    $('body').removeClass('mobile-sidebar-open');
                }
            }
        });
    });
</script>