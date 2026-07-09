<?php
/**
 * Sidebar Menu - Rebuilt for PHP 8.x
 * Dynamic sidebar with active state detection and user-specific menus
 */

// Prevent direct access
if (!defined('DS')) {
    require_once dirname(__DIR__) . '/config.php';
}

$currentFileName = basename($_SERVER['PHP_SELF']);

// Get user profile image with fallback
$profileImage = '../image/user.png';
if (!empty($iLoginUserDetail['profileimage']) && file_exists("../uploads/" . $iLoginUserDetail['profileimage'])) {
    $profileImage = "../uploads/" . $iLoginUserDetail['profileimage'];
}

// Get user fullname with fallback
$userFullname = $iLoginUserDetail['fullname'] ?? ($iLoginUserDetail['name'] ?? 'User');

// Determine user type for menu permissions
$userType = $_SESSION['usertype'] ?? '';
$isAdmin = ($userType == '1' || $userType == 'admin');
$isParent = ($userType == '2' || $userType == 'parent');
?>

<style>
    /* Sidebar Styles */
    .left.side-menu {
        position: fixed;
        top: 60px;
        left: 0;
        bottom: 0;
        width: 240px;
        background: #1B3058;
        z-index: 999;
        transition: all 0.3s ease;
    }
    
    .sidebar-inner {
        height: 100%;
        overflow-y: auto;
        overflow-x: hidden;
    }
    
    /* Custom Scrollbar */
    .sidebar-inner::-webkit-scrollbar {
        width: 5px;
    }
    .sidebar-inner::-webkit-scrollbar-track {
        background: #0f1e3a;
    }
    .sidebar-inner::-webkit-scrollbar-thumb {
        background: #f21151;
        border-radius: 5px;
    }
    
    /* User Details Section */
    .user-details {
        padding: 20px 15px;
        border-bottom: 1px solid rgba(255,255,255,0.1);
        margin-bottom: 15px;
    }
    .user-details .raju {
        margin-right: 10px;
    }
    .user-details .raju img {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid #f21151;
    }
    .user-info .dropdown-toggle {
        color: white;
        text-decoration: none;
        font-size: 14px;
        font-weight: 500;
    }
    .user-info .dropdown-toggle:hover {
        color: #f21151;
    }
    
    /* Menu Items */
    #sidebar-menu ul {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    #sidebar-menu ul li {
        position: relative;
    }
    #sidebar-menu ul li a {
        display: block;
        padding: 12px 20px;
        color: #a0a9c0;
        text-decoration: none;
        font-size: 14px;
        transition: all 0.3s ease;
    }
    #sidebar-menu ul li a:hover {
        color: white;
        background: rgba(255,255,255,0.1);
    }
    #sidebar-menu ul li a.active {
        color: white;
        background: #f21151;
    }
    #sidebar-menu ul li a i {
        margin-right: 10px;
        width: 20px;
        text-align: center;
    }
    
    /* Submenu Items */
    #sidebar-menu ul ul {
        padding-left: 40px;
        background: rgba(0,0,0,0.2);
    }
    #sidebar-menu ul ul li a {
        padding: 10px 20px;
        font-size: 13px;
    }
    #sidebar-menu ul ul li a i {
        font-size: 12px;
    }
    
    /* Menu Title */
    .menu-title {
        padding: 15px 20px 5px;
        color: #7a8cb4;
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 1px;
    }
    
    /* Menu Arrow for Submenus */
    .menu-arrow {
        float: right;
        transition: transform 0.3s ease;
    }
    .has_sub.open > a .menu-arrow {
        transform: rotate(90deg);
    }
    
    /* Responsive Sidebar */
    @media (max-width: 768px) {
        .left.side-menu {
            left: -240px;
        }
        .left.side-menu.open {
            left: 0;
        }
        body.sidebar-collapsed .left.side-menu {
            left: -240px;
        }
        body.sidebar-collapsed .left.side-menu.open {
            left: 0;
        }
    }
</style>

<div class="left side-menu">
    <div class="sidebar-inner slimscrollleft">
        <!-- User Details Section -->
        <div class="user-details">
            <div class="pull-left raju">
                <img src="<?= e($profileImage) ?>" alt="Profile Image">
            </div>
            <div class="user-info">
                <div class="dropdown">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                        <?= e($userFullname) ?> <i class="fa fa-chevron-down"></i>
                    </a>
                    <ul class="dropdown-menu">
                        <li><a href="login_profile.php"><i class="fa fa-user"></i> My Profile</a></li>
                        <li><a href="login_pass.php"><i class="fa fa-key"></i> Change Password</a></li>
                        <li class="divider"></li>
                        <li><a href="logout.php"><i class="fa fa-sign-out"></i> Logout</a></li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Sidebar Menu -->
        <div id="sidebar-menu">
            <ul>
                <li class="text-muted menu-title">Main Navigation</li>
                
                <!-- User Menu Item (Parent/Student specific) -->
                <?php if ($isParent || empty($isAdmin)): ?>
                <li>
                    <a href="login_profile.php" class="waves-effect <?= activeClass('login_profile.php', $currentFileName) ?>">
                        <i class="ti-user"></i> <span>My Account</span>
                    </a>
                </li>
                <ul class="list-unstyled">
                    <li>
                        <a href="login_profile.php" class="waves-effect <?= activeClass('login_profile.php', $currentFileName) ?>">
                            <i class="ti-arrow-right"></i> <span>Profile</span>
                        </a>
                    </li>
                    <li>
                        <a href="login_pass.php" class="waves-effect <?= activeClass('login_pass.php', $currentFileName) ?>">
                            <i class="ti-arrow-right"></i> <span>Change Password</span>
                        </a>
                    </li>
                </ul>
                <?php endif; ?>
                
                <!-- Result Section -->
                <li class="has_sub <?= hasSubmenuActive($currentFileName, ['term_result.php', 'cumulative_result.php']) ?>">
                    <a href="javascript:void(0);" class="waves-effect">
                        <i class="fa fa-graduation-cap"></i> <span> Result </span>
                        <span class="menu-arrow fa fa-chevron-right"></span>
                    </a>
                    <ul class="list-unstyled">
                        <li>
                            <a href="term_result.php" class="waves-effect <?= activeClass('term_result.php', $currentFileName) ?>">
                                <i class="ti-arrow-right"></i> <span>Term Result</span>
                            </a>
                        </li>
                        <!-- Cumulative Result (commented out in original, but available if needed) -->
                        <!--
                        <li>
                            <a href="cumulative_result.php" class="waves-effect <?= activeClass('cumulative_result.php', $currentFileName) ?>">
                                <i class="ti-arrow-right"></i> <span>Cumulative Result</span>
                            </a>
                        </li>
                        -->
                    </ul>
                </li>
                
                <!-- Divider -->
                <li class="text-muted menu-title">Actions</li>
                
                <!-- Logout -->
                <li>
                    <a href="logout.php" class="waves-effect <?= activeClass('logout.php', $currentFileName) ?>">
                        <i class="fa fa-sign-out"></i> <span>Logout</span>
                    </a>
                </li>
            </ul>
            <div class="clearfix"></div>
        </div>
        <div class="clearfix"></div>
    </div>
</div>

<script>
/**
 * Toggle submenu on click
 */
$(document).ready(function() {
    // Handle submenu toggle
    $('.has_sub > a').on('click', function(e) {
        e.preventDefault();
        var parentLi = $(this).parent('.has_sub');
        parentLi.toggleClass('open');
        parentLi.find('> ul').slideToggle(200);
    });
    
    // Open current active submenu
    $('.has_sub .active').parents('.has_sub').addClass('open');
    $('.has_sub.open > ul').show();
    
    // Mobile sidebar toggle
    $('.button-menu-mobile').on('click', function() {
        $('.left.side-menu').toggleClass('open');
    });
    
    // Close sidebar when clicking outside on mobile
    $(document).on('click', function(e) {
        if ($(window).width() <= 768) {
            if (!$(e.target).closest('.left.side-menu').length && 
                !$(e.target).closest('.button-menu-mobile').length) {
                $('.left.side-menu').removeClass('open');
            }
        }
    });
});
</script>

<?php
/**
 * Helper function to add 'active' class to current menu item
 */
function activeClass($filename, $currentFile) {
    return ($filename == $currentFile) ? 'active' : '';
}

/**
 * Helper function to check if submenu has active item
 */
function hasSubmenuActive($currentFile, $menuItems) {
    return in_array($currentFile, $menuItems) ? 'open' : '';
}
<li class="has_sub">
    <a href="javascript:void(0);">
        <i class="fa fa-cog"></i> <span>Settings</span>
        <span class="menu-arrow fa fa-chevron-right"></span>
    </a>
    <ul class="list-unstyled">
        <li><a href="general_settings.php">General Settings</a></li>
        <li><a href="email_settings.php">Email Settings</a></li>
    </ul>
</li>
?>