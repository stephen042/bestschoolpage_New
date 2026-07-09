<?php
/**
 * Header Component - Rebuilt for PHP 8.x
 * Dynamically displays school name and user profile
 */

// Prevent direct access
if (!defined('DS')) {
    require_once dirname(__DIR__) . '/config.php';
}

// Get school/company name from settings
$schoolName = '';
$companyLogo = '';

// Try to get from settings table
$settings = db_get_row("SELECT company_name, company_logo FROM settings WHERE id = 1");
if (!empty($settings)) {
    $schoolName = $settings['company_name'] ?? '';
    $companyLogo = $settings['company_logo'] ?? '';
}

// Fallback: get from school_register if settings doesn't have company_name
if (empty($schoolName) && !empty($_SESSION['userid'])) {
    $school = db_get_row("SELECT name FROM school_register WHERE id = ?", [$_SESSION['userid']]);
    $schoolName = $school['name'] ?? 'School Management System';
}

// Default if still empty
if (empty($schoolName)) {
    $schoolName = 'School Management System';
}

// Get user profile image
$userImage = '../image/user.png';
if (!empty($_SESSION['userid'])) {
    $user = db_get_row("SELECT profileimage FROM admin_login WHERE id = ?", [$_SESSION['userid']]);
    if (!empty($user['profileimage']) && file_exists("../uploads/" . $user['profileimage'])) {
        $userImage = "../uploads/" . $user['profileimage'];
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
        text-align: center;
        line-height: 60px;
    }
    .topbar-left .img-circle {
        max-height: 40px;
        width: auto;
        vertical-align: middle;
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
        padding: 10px 15px;
        display: block;
        cursor: pointer;
    }
    .navbar-nav > li > .profile img {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        object-fit: cover;
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
        <div class="text-center">
            <?php if (!empty($companyLogo) && file_exists("../uploads/" . $companyLogo)): ?>
                <img src="../uploads/<?= e($companyLogo) ?>" alt="Logo" class="img-circle" style="max-height: 40px;">
            <?php else: ?>
                <img src="../image/default-logo.png" alt="Logo" class="img-circle" style="max-height: 40px; visibility: hidden;">
            <?php endif; ?>
        </div>
    </div>
    <div class="navbar navbar-default" role="navigation">
        <div class="container-fluid">
            <div class="row">
                <div class="pull-left taugl">
                    <div class="row">
                        <div class="col-xs-3 col-md-2">
                            <button class="button-menu-mobile open-left">
                                <i class="fa fa-bars"></i>
                            </button>
                        </div>
                        <div class="col-xs-9 col-md-10">
                            <h2 class="ven" title="<?= e($schoolName) ?>">
                                <?= e($schoolName) ?>
                            </h2>
                        </div>
                    </div>
                </div>
                
                <ul class="nav navbar-nav navbar-right pull-right">
                    <li class="dropdown">
                        <a class="dropdown-toggle profile" data-toggle="dropdown" aria-expanded="false">
                            <img src="<?= e($userImage) ?>" alt="Profile Image">
                        </a>
                        <ul class="dropdown-menu dropdown-menu-right">
                            <li>
                                <a href="login_profile.php">
                                    <i class="fa fa-user-circle-o"></i> Update Profile
                                </a>
                            </li>
                            <li>
                                <a href="login_pass.php">
                                    <i class="fa fa-key"></i> Change Password
                                </a>
                            </li>
                            <li class="divider"></li>
                            <li>
                                <a href="logout.php">
                                    <i class="fa fa-sign-out"></i> Logout
                                </a>
                            </li>
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