<?php
/**
 * ============================================================================
 * DASHBOARD MENU - ROLE-BASED ACCESS CONTROL
 * ============================================================================
 * - Admin sees ALL menu items
 * - Staff/Teacher sees ONLY items from their role permissions
 * - Shows user name and assigned role in header
 * ============================================================================
 */

require_once('../config.php');
require_once('inc.session-create.php');



// If the user is already on index.php and session exists, show the page
// If no session, go to login
if (empty($_SESSION['userid'])) {
    redirect(SKOOL_URL . 'login.php');
    exit;
}

// Route landing page based on role to avoid dashboard redirect loops.
$userType = $_SESSION['usertype'] ?? '';
// Only school admin should be forced to analytics dashboard.
// Staff/teachers (usertype=1) should stay on role-based menu index page.
if (in_array((string)$userType, ['0', 'admin'], true)) {
    redirect(SKOOL_URL . 'dashboard.php');
    exit;
}

// Prevent redirect loop - if we came from a redirect, stay
if (isset($_GET['loop']) && $_GET['loop'] == '1') {
    // Remove the parameter to prevent infinite loop
    header('Location: ' . str_replace('?loop=1', '', $_SERVER['REQUEST_URI']));
    exit;
}


// ============================================================================
// INITIALIZATION
// ============================================================================
$stat = [];
$userId = (int)($_SESSION['userid'] ?? 0);
$userType = $_SESSION['usertype'] ?? '';
$username = $_SESSION['username'] ?? '';
$sessionEmail = $_SESSION['email'] ?? '';
$schoolOwnerId = (int)($_SESSION['create_by_userid'] ?? 0);
if ($schoolOwnerId <= 0) {
    $schoolOwnerId = $userId;
}
$isSchoolOwnerSession = ($userId > 0 && $userId === $schoolOwnerId);

// ============================================================================
// GET USER DETAILS
// ============================================================================
$userDetails = db_get_row("SELECT * FROM school_register WHERE id = ?", [$userId]);

// ============================================================================
// GET STAFF DETAILS (if user is staff/teacher)
// ============================================================================
$staffDetails = [];
$assignedRole = '';
$permittedFiles = [];
$isAdmin = ($userType == '0' || $isSchoolOwnerSession);
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

if (!$isAdmin) {
    // Resolve staff identity within current school scope.
    $staffDetails = db_get_row(
        "SELECT * FROM staff_manage
         WHERE create_by_userid = ?
           AND (staff_id = ? OR email = ? OR id = ?)
         ORDER BY id DESC
         LIMIT 1",
        [$schoolOwnerId, $username, $sessionEmail, $userId]
    );
    
    if (!empty($staffDetails)) {
        $staffId = $staffDetails['id'];
        $candidateStaffIds = [$userId];
        if ($staffId > 0 && !in_array($staffId, $candidateStaffIds, true)) {
            $candidateStaffIds[] = $staffId;
        }
        
        // Get assigned role
        $placeholders = implode(',', array_fill(0, count($candidateStaffIds), '?'));
        $roleParams = $candidateStaffIds;
        $roleParams[] = $schoolOwnerId;
        $roleData = db_get_row(
            "SELECT r.id, r.role, ar.principal 
             FROM assign_role ar
             LEFT JOIN roles r ON ar.role_id = r.id
             WHERE ar.staff_id IN ($placeholders) AND ar.create_by_userid = ?
             ORDER BY ar.id DESC
             LIMIT 1",
            $roleParams
        );
        
        if (!empty($roleData)) {
            $assignedRole = $roleData['role'] ?? 'No Role Assigned';
            $roleId = $roleData['id'] ?? 0;
            
            // Get permitted files for this role
            if (!empty($roleId)) {
                $permittedFiles = db_get_rows(
                    "SELECT file_name FROM role_permission WHERE role_id = ?",
                    [$roleId]
                );
                // Convert to simple array of file names
                $permittedFiles = array_column($permittedFiles, 'file_name');
            }
        }

        if (empty($permittedFiles)) {
            $permittedFiles = $fallbackTeacherFiles;
            $assignedRole = $assignedRole !== '' ? $assignedRole : 'Teacher';
        }
    }
}

if (!$isAdmin && empty($permittedFiles)) {
    $permittedFiles = $fallbackTeacherFiles;
    if ($assignedRole === '') {
        $assignedRole = 'Teacher';
    }
}

// ============================================================================
// GET MENU ITEMS (All available pages)
// ============================================================================
$allMenuItems = db_get_rows(
    "SELECT * FROM school_filename 
     WHERE file_name IS NOT NULL AND file_name != ''
     ORDER BY title ASC",
    []
);

// ============================================================================
// FILTER MENU ITEMS BASED ON USER TYPE
// ============================================================================
$displayMenuItems = [];

if ($isAdmin) {
    // ADMIN: Show ALL menu items
    $displayMenuItems = $allMenuItems;
} else {
    // STAFF/TEACHER: Show ONLY permitted items
    foreach ($allMenuItems as $item) {
        $fileName = $item['file_name'] ?? '';
        // Check if file is in permitted list (strip .php extension for comparison)
        $baseName = str_replace('.php', '', $fileName);
        foreach ($permittedFiles as $permitted) {
            $permittedBase = str_replace('.php', '', $permitted);
            if ($baseName == $permittedBase || $fileName == $permitted) {
                $displayMenuItems[] = $item;
                break;
            }
        }
    }
}

// ============================================================================
// GET ASSIGNED CLASSES FOR TEACHER
// ============================================================================
$assignedClasses = [];
if (!$isAdmin && !empty($staffDetails)) {
    $assignedClasses = db_get_rows(
        "SELECT sc.id, sc.name, sc.short_name 
         FROM class_teacher ct
         LEFT JOIN school_class sc ON ct.school_class = sc.id
         WHERE ct.staff_id = ? AND ct.create_by_userid = ?",
        [$staffDetails['id'], $schoolOwnerId]
    );
}

// ============================================================================
// GET ASSIGNED SUBJECTS FOR TEACHER
// ============================================================================
$assignedSubjects = [];
if (!$isAdmin && !empty($staffDetails)) {
    $assignedSubjects = db_get_rows(
        "SELECT ss.id, ss.subject, sc.name as class_name 
         FROM subject_teacher st
         LEFT JOIN school_subject ss ON st.school_subject = ss.id
         LEFT JOIN school_class sc ON ss.class_id = sc.id
         WHERE st.staff_id = ? AND st.create_by_userid = ?",
        [$staffDetails['id'], $schoolOwnerId]
    );
}
?>
<!DOCTYPE html>
<html>
<head>
    <?php include('inc.meta.php'); ?>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { background: #f0f2f5; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        
        .dashboard-container { max-width: 1400px; margin: 0 auto; padding: 20px; }
        
        /* Welcome Header */
        .welcome-card {
            background: linear-gradient(135deg, #1B3058 0%, #2a4780 100%);
            color: white;
            padding: 25px 30px;
            border-radius: 20px;
            margin-bottom: 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }
        .welcome-text h2 { font-size: 24px; margin-bottom: 5px; }
        .welcome-text p { opacity: 0.9; font-size: 14px; }
        .role-badge {
            background: rgba(255,255,255,0.2);
            padding: 8px 20px;
            border-radius: 30px;
            font-size: 14px;
            font-weight: 600;
        }
        
        /* Assigned Info Cards */
        .info-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 15px;
            margin-bottom: 25px;
        }
        .info-card {
            background: white;
            padding: 15px 20px;
            border-radius: 16px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        }
        .info-card .label { font-size: 11px; color: #888; text-transform: uppercase; letter-spacing: 0.5px; }
        .info-card .value { font-size: 16px; font-weight: 600; color: #1B3058; margin-top: 5px; }
        .info-card .tags { display: flex; flex-wrap: wrap; gap: 5px; margin-top: 5px; }
        .info-card .tag {
            background: #e8eef5;
            padding: 2px 10px;
            border-radius: 20px;
            font-size: 11px;
            color: #1B3058;
        }
        
        /* Menu Grid */
        .menu-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 15px;
        }
        .menu-item {
            background: white;
            padding: 20px;
            border-radius: 16px;
            text-align: center;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            transition: all 0.3s;
            text-decoration: none;
            display: block;
            color: #333;
        }
        .menu-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 30px rgba(0,0,0,0.12);
        }
        .menu-item i {
            font-size: 32px;
            color: #1B3058;
            margin-bottom: 10px;
            display: block;
        }
        .menu-item h4 { font-size: 14px; font-weight: 600; margin-bottom: 5px; }
        .menu-item p { font-size: 11px; color: #888; margin: 0; }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            background: white;
            border-radius: 20px;
            color: #999;
        }
        .empty-state i { font-size: 60px; color: #ddd; margin-bottom: 15px; display: block; }
        
        .logout-btn {
            background: rgba(255,255,255,0.2);
            border: none;
            color: white;
            padding: 10px 20px;
            border-radius: 30px;
            cursor: pointer;
            font-size: 14px;
            transition: all 0.2s;
            text-decoration: none;
        }
        .logout-btn:hover { background: rgba(255,255,255,0.3); }
        
        @media (max-width: 600px) {
            .dashboard-container { padding: 15px; }
            .welcome-card { flex-direction: column; text-align: center; }
            .menu-grid { grid-template-columns: repeat(auto-fill, minmax(160px, 1fr)); }
        }
    </style>
</head>
<body class="fixed-left">
<div id="wrapper">
    <?php include('inc.header.php'); ?>
    <?php include('inc.sideleft.php'); ?>
    <div class="content-page">
        <div class="content">
            <div class="dashboard-container">
                
                <!-- Welcome Header -->
                <div class="welcome-card">
                    <div class="welcome-text">
                        <h2>👋 Welcome, <?= htmlspecialchars($userDetails['name'] ?? 'User') ?>!</h2>
                        <p>
                            <?php if ($isAdmin): ?>
                                <?= htmlspecialchars($userDetails['name'] ?? 'Admin') ?> (Administrator)
                            <?php else: ?>
                                <?= htmlspecialchars($staffDetails['first_name'] ?? '') . ' ' . htmlspecialchars($staffDetails['last_name'] ?? '') ?>
                                <?= !empty($assignedRole) ? ' | Role: ' . htmlspecialchars($assignedRole) : '' ?>
                            <?php endif; ?>
                        </p>
                    </div>
                    <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
                        <span class="role-badge">
                            <?= $isAdmin ? '👑 Admin' : '👨‍🏫 ' . htmlspecialchars($assignedRole ?: 'Staff') ?>
                        </span>
                        <a href="<?= SKOOL_URL ?>home.php?logout=1" class="logout-btn" onclick="return confirm('Are you sure you want to logout?')">
                            <i class="fa fa-sign-out"></i> Logout
                        </a>
                    </div>
                </div>
                
                <!-- Assigned Info Cards (for Teachers) -->
                <?php if (!$isAdmin): ?>
                    <div class="info-cards">
                        <?php if (!empty($assignedClasses)): ?>
                            <div class="info-card">
                                <div class="label"><i class="fa fa-graduation-cap"></i> Assigned Classes</div>
                                <div class="tags">
                                    <?php foreach ($assignedClasses as $class): ?>
                                        <span class="tag"><?= htmlspecialchars($class['name'] ?? 'N/A') ?></span>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($assignedSubjects)): ?>
                            <div class="info-card">
                                <div class="label"><i class="fa fa-book"></i> Assigned Subjects</div>
                                <div class="tags">
                                    <?php foreach ($assignedSubjects as $subject): ?>
                                        <span class="tag"><?= htmlspecialchars($subject['subject'] ?? 'N/A') ?> (<?= htmlspecialchars($subject['class_name'] ?? 'N/A') ?>)</span>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (empty($assignedClasses) && empty($assignedSubjects)): ?>
                            <div class="info-card">
                                <div class="label"><i class="fa fa-info-circle"></i> No assignments yet</div>
                                <div style="font-size: 13px; color: #888; margin-top: 5px;">
                                    Contact administrator to assign classes and subjects.
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
                
                <!-- Menu Items -->
                <h3 style="margin-bottom: 15px; color: #1B3058;">
                    <i class="fa fa-th-large"></i> Quick Access Menu
                    <span style="font-size: 12px; color: #888; font-weight: normal; margin-left: 10px;">
                        (<?= count($displayMenuItems) ?> items available)
                    </span>
                </h3>
                
                <?php if (!empty($displayMenuItems)): ?>
                    <div class="menu-grid">
                        <?php foreach ($displayMenuItems as $item): ?>
                            <?php 
                            $fileName = $item['file_name'] ?? '';
                            $title = $item['title'] ?? '';
                            // Skip if file doesn't exist
                            if (empty($fileName) || !file_exists($fileName)) continue;
                            ?>
                            <a href="<?= SKOOL_URL . $fileName ?>" class="menu-item">
                                <?php 
                                // Generate icon based on title
                                $icon = 'fa-file-text-o';
                                if (strpos($title, 'Configuration') !== false) $icon = 'fa-cog';
                                elseif (strpos($title, 'Role') !== false) $icon = 'fa-shield';
 elseif (strpos($title, 'User') !== false) $icon = 'fa-users';
 elseif (strpos($title, 'Class') !== false) $icon = 'fa-graduation-cap';
                                elseif (strpos($title, 'Subject') !== false) $icon = 'fa-book';
                                elseif (strpos($title, 'Score') !== false || strpos($title, 'Input') !== false) $icon = 'fa-pencil-square-o';
                                elseif (strpos($title, 'Result') !== false || strpos($title, 'Board') !== false) $icon = 'fa-bar-chart';
                                elseif (strpos($title, 'Staff') !== false) $icon = 'fa-user-tie';
                                elseif (strpos($title, 'Parent') !== false) $icon = 'fa-users';
                                elseif (strpos($title, 'Student') !== false) $icon = 'fa-child';
                                elseif (strpos($title, 'Comment') !== false) $icon = 'fa-comment';
                                elseif (strpos($title, 'Roll Call') !== false) $icon = 'fa-check-square-o';
                                elseif (strpos($title, 'Sms') !== false) $icon = 'fa-envelope';
                                elseif (strpos($title, 'Psychomotor') !== false) $icon = 'fa-hand-paper-o';
                                ?>
                                <i class="fa <?= $icon ?>"></i>
                                <h4><?= htmlspecialchars($title) ?></h4>
                                <p><?= htmlspecialchars(str_replace('.php', '', $fileName)) ?></p>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fa fa-ban"></i>
                        <?php if ($isAdmin): ?>
                            <p>No menu items found. Please add items to <strong>school_filename</strong> table.</p>
                        <?php else: ?>
                            <p>You don't have permission to access any pages yet.</p>
                            <p style="font-size: 13px;">Please contact the administrator to assign permissions to your role.</p>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
                
            </div>
        </div>
        <?php include('inc.footer.php'); ?>
    </div>
</div>

<?php include('inc.js.php'); ?>
</body>
</html>