<?php
/**
 * Header Component - Fixed for PHP 8.x
 * Displays school name and user profile with proper error handling
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
    .topbar {
        background: #fff;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        z-index: 999;
    }
    .topbar-left {
        float: left;
        width: 240px;
        background: #1B3058;
        height: 60px;
        position: relative;
    }
    .topbar-left .text-center {
        height: 100%;
    }
    .navbar {
        margin-left: 240px;
        background: #fff;
        border: none;
        border-radius: 0;
        margin-bottom: 0;
        min-height: 60px;
    }
    .button-menu-mobile {
        background: transparent;
        border: none;
        color: #1B3058;
        font-size: 20px;
        margin-top: 15px;
        margin-left: 15px;
        cursor: pointer;
    }
    .button-menu-mobile:hover {
        color: #f21151;
    }
    .ven {
        color: #1B3058;
        font-size: 18px;
        margin-top: 18px;
        margin-left: 10px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .navbar-nav {
        margin: 0;
    }
    .navbar-nav > li {
        float: left;
    }
    .navbar-nav > li > .profile {
        padding: 4px 8px;
        display: flex;
        align-items: center;
        gap: 6px;
        cursor: pointer;
        border: 2px solid #f21151;
        border-radius: 999px;
        background: #1B3058;
        margin-top: 10px;
        min-height: 36px;
    }
    .navbar-nav > li > .profile:hover {
        background: #142445;
    }
    .navbar-nav > li > .profile .settings-icon {
        color: #f21151;
        font-size: 13px;
    }
    .navbar-nav > li > .profile .settings-text {
        color: #ffffff;
        font-weight: 600;
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: 0.25px;
    }
    .navbar-nav > li > .profile img {
        width: 24px;
        height: 24px;
        border-radius: 50%;
        object-fit: cover;
        border: 1px solid #f21151;
    }
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
        box-shadow: 0 6px 12px rgba(0,0,0,.175);
    }
    .dropdown-menu > li > a {
        display: block;
        padding: 8px 20px;
        clear: both;
        font-weight: 400;
        line-height: 1.42857143;
        color: #333;
        white-space: nowrap;
        text-decoration: none;
    }
    .dropdown-menu > li > a:hover {
        background-color: #f5f5f5;
        color: #1B3058;
    }
    .dropdown-menu > li > a i {
        margin-right: 8px;
        width: 16px;
    }
    .taugl {
        float: left;
        width: calc(100% - 150px);
    }
    .pull-right {
        float: right !important;
    }
    @media (max-width: 768px) {
        .topbar-left {
            width: 60px;
        }
        .navbar {
            margin-left: 60px;
        }
        .ven {
            font-size: 14px;
            width: auto !important;
            max-width: 200px;
        }
        .taugl .col-md-9 {
            padding-left: 0;
        }
        .navbar-nav > li > .profile .settings-text {
            display: none;
        }
    }
    @media (max-width: 480px) {
        .ven {
            font-size: 12px;
            max-width: 150px;
        }
    }
</style>

<div class="topbar">
    <div class="topbar-left">
        <div class="text-center"></div>
    </div>
    <div class="navbar navbar-default" role="navigation">
        <div class="container">
            <div class="row">
                <div class="pull-left taugl">
                    <div class="row">
                        <div class="col-md-3">
                            <button class="button-menu-mobile open-left">
                                <i class="fa fa-bars"></i>
                            </button>
                            <span class="clearfix"></span> 
                        </div>
                        <div class="col-md-9">
                            <h2 class="ven shclnmdcls" style="font-size: 20px;" title="<?= htmlspecialchars($headerDisplayName) ?>">
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
    // Dropdown toggle
    $('.dropdown-toggle').dropdown();
    
    // Mobile menu toggle
    $('.button-menu-mobile').on('click', function() {
        $('body').toggleClass('sidebar-collapsed');
        $(window).trigger('resize');
    });
});
</script>