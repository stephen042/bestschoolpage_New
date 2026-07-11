<?php

/**
 * Header Component - Fixed for PHP 8.x
 * Displays school name and user profile with proper error handling
 * FIXED: Sidebar toggle with proper spacing and Font Awesome bars icon
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
$userImage = '../image/user.png';
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
    }

    .topbar-left {
        float: left;
        width: 240px;
        background: #1B3058;
        height: 60px;
        position: relative;
        transition: width 0.3s ease;
        overflow: hidden;
    }

    .topbar-left .text-center {
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .navbar {
        margin-left: 240px;
        background: #fff;
        border: none;
        border-radius: 0;
        margin-bottom: 0;
        min-height: 60px;
        height: 60px;
        transition: margin-left 0.3s ease;
    }

    .navbar .container {
        width: 100%;
        padding: 0 15px;
        height: 100%;
    }

    .navbar .container .row {
        height: 100%;
        display: flex;
        align-items: center;
        margin: 0;
    }

    /* ============================================================
       MENU TOGGLE BUTTON
       ============================================================ */
    .button-menu-mobile {
        background: transparent;
        border: none;
        color: #1B3058;
        font-size: 22px;
        padding: 8px 12px;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s ease;
        margin: 0;
        line-height: 1;
        width: 44px;
        height: 44px;
        border-radius: 4px;
    }

    .button-menu-mobile:hover {
        color: #f21151;
        background: rgba(27, 48, 88, 0.05);
    }

    .button-menu-mobile:focus {
        outline: none;
    }

    .button-menu-mobile i {
        font-size: 22px;
    }

    /* ============================================================
       SCHOOL NAME STYLES
       ============================================================ */
    .ven {
        color: #1B3058;
        font-size: 20px;
        font-weight: 600;
        margin: 0;
        padding: 0;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        line-height: 60px;
        height: 60px;
    }

    /* ============================================================
       HEADER LAYOUT
       ============================================================ */
    .taugl {
        float: left;
        display: flex;
        align-items: center;
        height: 60px;
        flex: 1;
    }

    .taugl .row {
        display: flex;
        align-items: center;
        width: 100%;
        margin: 0;
    }

    .taugl .col-md-3 {
        flex: 0 0 auto;
        width: auto;
        padding: 0;
        display: flex;
        align-items: center;
    }

    .taugl .col-md-9 {
        flex: 1;
        padding: 0 10px;
        min-width: 0;
    }

    /* ============================================================
       PROFILE / SETTINGS DROPDOWN
       ============================================================ */
    .navbar-nav {
        margin: 0;
        display: flex;
        align-items: center;
        height: 60px;
        padding-right: 5px;
    }

    .navbar-nav>li {
        float: left;
        list-style: none;
    }

    .navbar-nav>li>.profile {
        padding: 4px 12px 4px 8px;
        display: flex;
        align-items: center;
        gap: 8px;
        cursor: pointer;
        border: 2px solid #f21151;
        border-radius: 999px;
        background: #1B3058;
        min-height: 38px;
        text-decoration: none;
        transition: all 0.3s ease;
    }

    .navbar-nav>li>.profile:hover {
        background: #142445;
        text-decoration: none;
    }

    .navbar-nav>li>.profile .settings-icon {
        color: #f21151;
        font-size: 14px;
    }

    .navbar-nav>li>.profile .settings-text {
        color: #ffffff;
        font-weight: 600;
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: 0.25px;
    }

    .navbar-nav>li>.profile img {
        width: 28px;
        height: 28px;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid #f21151;
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
        margin-left: 60px;
    }

    /* ============================================================
       RESPONSIVE - TABLETS
       ============================================================ */
    @media (max-width: 1024px) {
        .navbar-nav>li>.profile .settings-text {
            display: none;
        }

        .navbar-nav>li>.profile {
            padding: 4px 8px;
        }
    }

    @media (max-width: 768px) {
        .topbar-left {
            width: 60px;
        }

        .navbar {
            margin-left: 60px;
        }

        .ven {
            font-size: 16px;
        }

        .taugl .col-md-9 {
            padding: 0 5px;
        }

        .button-menu-mobile {
            font-size: 20px;
            padding: 6px 10px;
            width: 38px;
            height: 38px;
        }

        .button-menu-mobile i {
            font-size: 20px;
        }

        .navbar-nav>li>.profile .settings-text {
            display: none;
        }

        .navbar-nav>li>.profile {
            padding: 3px 6px;
            min-height: 34px;
        }

        .navbar-nav>li>.profile img {
            width: 24px;
            height: 24px;
        }
    }

    @media (max-width: 480px) {
        .ven {
            font-size: 13px;
            max-width: 120px;
        }

        .button-menu-mobile {
            font-size: 18px;
            padding: 4px 8px;
            width: 34px;
            height: 34px;
        }

        .button-menu-mobile i {
            font-size: 18px;
        }

        .navbar-nav>li>.profile .settings-icon {
            font-size: 12px;
        }

        .navbar-nav>li>.profile img {
            width: 20px;
            height: 20px;
        }

        .taugl .col-md-3 {
            flex: 0 0 auto;
        }
    }

    /* ============================================================
       ANIMATION FOR SIDEBAR TOGGLE
       ============================================================ */
    .topbar-left,
    .navbar {
        transition: width 0.3s ease, margin-left 0.3s ease;
    }
</style>

<div class="topbar">
    <div class="topbar-left">
        <div class="text-center">
            <!-- Logo or brand can go here -->
        </div>
    </div>
    <div class="navbar navbar-default" role="navigation">
        <div class="container-fluid">
            <div class="row">
                <div class="pull-left taugl">
                    <div class="row">
                        <div class="col-md-3 col-sm-3 col-xs-3">
                            <button class="button-menu-mobile open-left" aria-label="Toggle Sidebar">
                                <i class="fa fa-bars"></i>
                            </button>
                            <span class="clearfix"></span>
                        </div>
                        <div class="col-md-9 col-sm-9 col-xs-9">
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
        // ============================================================
        // DROPDOWN TOGGLE
        // ============================================================
        $('.dropdown-toggle').dropdown();

        // ============================================================
        // SIDEBAR TOGGLE - FIXED FOR PROPER SPACING
        // ============================================================
        $('.button-menu-mobile').on('click', function(e) {
            e.preventDefault();
            e.stopPropagation();

            // Toggle the sidebar collapsed class on body
            $('body').toggleClass('sidebar-collapsed');

            // If sidebar is open, close it; if closed, open it
            var sidebar = $('.left.side-menu');
            if (sidebar.hasClass('open')) {
                sidebar.removeClass('open');
            } else if ($(window).width() <= 768) {
                // On mobile, toggle the open class
                sidebar.toggleClass('open');
            }

            // Trigger resize event for any responsive elements
            $(window).trigger('resize');
        });

        // ============================================================
        // CLOSE SIDEBAR ON MOBILE WHEN CLICKING OUTSIDE
        // ============================================================
        $(document).on('click', function(e) {
            if ($(window).width() <= 768) {
                var sidebar = $('.left.side-menu');
                var toggle = $('.button-menu-mobile');

                // Check if click was outside the sidebar and toggle button
                if (!sidebar.is(e.target) &&
                    sidebar.has(e.target).length === 0 &&
                    !toggle.is(e.target) &&
                    toggle.has(e.target).length === 0) {
                    sidebar.removeClass('open');
                }
            }
        });

        // ============================================================
        // RESPONSIVE - RESET SIDEBAR STATE ON WINDOW RESIZE
        // ============================================================
        $(window).on('resize', function() {
            if ($(window).width() > 768) {
                $('.left.side-menu').removeClass('open');
            }
        });

        // ============================================================
        // PROFILE DROPDOWN - PREVENT SIDEBAR TOGGLE INTERFERENCE
        // ============================================================
        $('.profile').on('click', function(e) {
            e.stopPropagation();
        });
    });
</script>