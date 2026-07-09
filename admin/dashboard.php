<?php
/**
 * ============================================================================
 * SUPER ADMIN DASHBOARD - SYSTEM-WIDE STATISTICS
 * ============================================================================
 * - Shows all schools and their data
 * - Only accessible to super admin (usertype = 1)
 * - Real stats from database
 * ============================================================================
 */

require_once('../config.php');
require_once('admin-session-check.php');

// ============================================================================
// VERIFY SUPER ADMIN ACCESS
// ============================================================================
if ($_SESSION['usertype'] != '1') {
    redirect(SKOOL_URL . 'index.php');
    exit;
}

// ============================================================================
// GET SYSTEM-WIDE STATISTICS
// ============================================================================

// Total Schools
$totalSchools = db_get_val("SELECT COUNT(*) FROM school_register");

// Total Students (All Schools)
$totalStudents = db_get_val("SELECT COUNT(*) FROM manage_student");

// Total Teachers (All Schools)
$totalTeachers = db_get_val("SELECT COUNT(*) FROM staff_manage");

// Total Parents (All Schools)
$totalParents = db_get_val("SELECT COUNT(*) FROM student_guardian");

// Total Sessions
$totalSessions = db_get_val("SELECT COUNT(*) FROM school_session");

// Total Classes (All Schools)
$totalClasses = db_get_val("SELECT COUNT(*) FROM school_class");

// Total Subjects (All Schools)
$totalSubjects = db_get_val("SELECT COUNT(*) FROM school_subject");

// Total Users (school_register)
$totalUsers = db_get_val("SELECT COUNT(*) FROM school_register");

// Recent Schools (last 5)
$recentSchools = db_get_rows(
    "SELECT id, name, location, email, contact_no, create_at 
     FROM school_register 
     ORDER BY id DESC 
     LIMIT 5"
);

// Schools by Type (if you have school_type table)
$schoolTypes = db_get_rows(
    "SELECT st.school_type, COUNT(sr.id) as count 
     FROM school_register sr
     LEFT JOIN school_type st ON sr.school_type = st.id
     GROUP BY sr.school_type
     ORDER BY count DESC"
);

// Students by School (Top 5 schools with most students)
$topSchools = db_get_rows(
    "SELECT sr.name, COUNT(ms.id) as student_count
     FROM school_register sr
     LEFT JOIN manage_student ms ON sr.id = ms.create_by_userid
     GROUP BY sr.id
     ORDER BY student_count DESC
     LIMIT 5"
);

// Total Revenue (if you have payments table)
// $totalRevenue = db_get_val("SELECT SUM(amount) FROM payments");

// Today's registrations (schools registered today)
$todayRegistrations = db_get_val(
    "SELECT COUNT(*) FROM school_register WHERE DATE(create_at) = CURDATE()"
);

// This month registrations
$monthRegistrations = db_get_val(
    "SELECT COUNT(*) FROM school_register WHERE MONTH(create_at) = MONTH(CURDATE()) AND YEAR(create_at) = YEAR(CURDATE())"
);
?>
<!DOCTYPE html>
<html>
<head>
    <?php include('inc.meta.php'); ?>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { background: #f0f2f5; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        
        .dashboard-container { max-width: 1400px; margin: 0 auto; padding: 20px; }
        
        /* Page Header */
        .page-header { margin-bottom: 25px; }
        .page-header h1 { color: #1B3058; font-size: 28px; }
        .page-header p { color: #666; margin-top: 5px; font-size: 14px; }
        
        /* Welcome Card */
        .welcome-card {
            background: linear-gradient(135deg, #1B3058 0%, #2a4780 100%);
            color: white;
            padding: 25px 30px;
            border-radius: 20px;
            margin-bottom: 25px;
        }
        .welcome-card h2 { font-size: 22px; margin-bottom: 5px; }
        .welcome-card p { opacity: 0.9; font-size: 14px; }
        
        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 16px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            text-align: center;
            transition: all 0.3s;
        }
        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        }
        .stat-card i {
            font-size: 36px;
            color: #1B3058;
            margin-bottom: 10px;
        }
        .stat-card .number {
            font-size: 28px;
            font-weight: 700;
            color: #1B3058;
        }
        .stat-card .label {
            font-size: 12px;
            color: #888;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-top: 5px;
        }
        .stat-card .sub {
            font-size: 11px;
            color: #999;
            margin-top: 8px;
        }
        
        /* Two Column Layout */
        .two-column {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 30px;
        }
        @media (max-width: 768px) {
            .two-column { grid-template-columns: 1fr; }
        }
        
        .card-modern {
            background: white;
            border-radius: 16px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            overflow: hidden;
        }
        .card-header {
            padding: 15px 20px;
            background: #f8f9fa;
            border-bottom: 1px solid #eee;
            font-weight: 600;
            color: #1B3058;
        }
        .card-body { padding: 20px; }
        .card-body ul { list-style: none; }
        .card-body li {
            padding: 10px 0;
            border-bottom: 1px solid #f0f0f0;
            display: flex;
            justify-content: space-between;
        }
        .card-body li:last-child { border-bottom: none; }
        .card-body li .name { font-weight: 500; }
        .card-body li .count { color: #1B3058; font-weight: 600; }
        
        .school-item { padding: 12px 0; border-bottom: 1px solid #f0f0f0; }
        .school-item:last-child { border-bottom: none; }
        .school-item .name { font-weight: 500; color: #1B3058; }
        .school-item .detail { font-size: 12px; color: #888; margin-top: 3px; }
        
        .highlight { color: #1B3058; font-weight: 600; }
        
        .subtitle { font-size: 12px; color: #888; margin-top: 5px; }
        
        .no-data { text-align: center; padding: 30px; color: #999; font-size: 14px; }
        
        @media (max-width: 600px) {
            .stats-grid { grid-template-columns: repeat(2, 1fr); }
            .two-column { grid-template-columns: 1fr; }
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
                
                <!-- Page Header -->
                <div class="page-header">
                    <h1><i class="fa fa-dashboard"></i> Super Admin Dashboard</h1>
                    <p>Overview of all schools and system-wide statistics</p>
                </div>
                
                <!-- Welcome Card -->
                <div class="welcome-card">
                    <h2>👋 Welcome, <?= htmlspecialchars($_SESSION['fullname'] ?? 'Super Admin') ?>!</h2>
                    <p>Here's what's happening across all schools in the system</p>
                </div>
                
                <!-- Stats Grid -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <i class="fa fa-university"></i>
                        <div class="number"><?= number_format($totalSchools) ?></div>
                        <div class="label">Total Schools</div>
                        <div class="sub">+<?= $monthRegistrations ?> this month</div>
                    </div>
                    <div class="stat-card">
                        <i class="fa fa-child"></i>
                        <div class="number"><?= number_format($totalStudents) ?></div>
                        <div class="label">Total Students</div>
                    </div>
                    <div class="stat-card">
                        <i class="fa fa-user-tie"></i>
                        <div class="number"><?= number_format($totalTeachers) ?></div>
                        <div class="label">Total Teachers</div>
                    </div>
                    <div class="stat-card">
                        <i class="fa fa-users"></i>
                        <div class="number"><?= number_format($totalParents) ?></div>
                        <div class="label">Total Parents</div>
                    </div>
                    <div class="stat-card">
                        <i class="fa fa-book"></i>
                        <div class="number"><?= number_format($totalClasses) ?></div>
                        <div class="label">Total Classes</div>
                    </div>
                    <div class="stat-card">
                        <i class="fa fa-puzzle-piece"></i>
                        <div class="number"><?= number_format($totalSubjects) ?></div>
                        <div class="label">Total Subjects</div>
                    </div>
                    <div class="stat-card">
                        <i class="fa fa-calendar"></i>
                        <div class="number"><?= number_format($totalSessions) ?></div>
                        <div class="label">Total Sessions</div>
                    </div>
                    <div class="stat-card">
                        <i class="fa fa-user"></i>
                        <div class="number"><?= number_format($totalUsers) ?></div>
                        <div class="label">Total Users</div>
                    </div>
                </div>
                
                <!-- Two Column Layout -->
                <div class="two-column">
                    
                    <!-- Left Column: Recent Schools -->
                    <div class="card-modern">
                        <div class="card-header">
                            <i class="fa fa-clock-o"></i> Recent School Registrations
                        </div>
                        <div class="card-body">
                            <?php if (!empty($recentSchools)): ?>
                                <?php foreach ($recentSchools as $school): ?>
                                    <div class="school-item">
                                        <div class="name"><?= htmlspecialchars($school['name'] ?? 'N/A') ?></div>
                                        <div class="detail">
                                            <?= htmlspecialchars($school['location'] ?? 'N/A') ?> | 
                                            <?= htmlspecialchars($school['email'] ?? 'N/A') ?> |
                                            Registered: <?= date('M d, Y', strtotime($school['create_at'] ?? 'now')) ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="no-data">No schools registered yet.</div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Right Column: Top Schools by Students -->
                    <div class="card-modern">
                        <div class="card-header">
                            <i class="fa fa-trophy"></i> Top Schools by Students
                        </div>
                        <div class="card-body">
                            <?php if (!empty($topSchools)): ?>
                                <?php foreach ($topSchools as $school): ?>
                                    <div class="school-item">
                                        <div class="name"><?= htmlspecialchars($school['name'] ?? 'N/A') ?></div>
                                        <div class="detail">
                                            👨‍🎓 <?= number_format($school['student_count'] ?? 0) ?> students
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="no-data">No student data available.</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <!-- School Types Distribution -->
                <?php if (!empty($schoolTypes)): ?>
                <div class="card-modern" style="margin-bottom: 30px;">
                    <div class="card-header">
                        <i class="fa fa-pie-chart"></i> Schools by Type
                    </div>
                    <div class="card-body">
                        <div style="display: flex; flex-wrap: wrap; gap: 20px;">
                            <?php foreach ($schoolTypes as $type): ?>
                                <div style="background: #f8f9fa; padding: 10px 20px; border-radius: 12px;">
                                    <span style="font-weight: 600;"><?= htmlspecialchars($type['school_type'] ?? 'Uncategorized') ?></span>
                                    <span style="background: #1B3058; color: white; padding: 2px 12px; border-radius: 20px; margin-left: 10px;">
                                        <?= $type['count'] ?>
                                    </span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- System Info -->
                <div class="card-modern">
                    <div class="card-header">
                        <i class="fa fa-info-circle"></i> System Information
                    </div>
                    <div class="card-body">
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; font-size: 13px;">
                            <div><span style="color: #888;">Total Schools:</span> <span class="highlight"><?= number_format($totalSchools) ?></span></div>
                            <div><span style="color: #888;">Total Students:</span> <span class="highlight"><?= number_format($totalStudents) ?></span></div>
                            <div><span style="color: #888;">Total Teachers:</span> <span class="highlight"><?= number_format($totalTeachers) ?></span></div>
                            <div><span style="color: #888;">Total Parents:</span> <span class="highlight"><?= number_format($totalParents) ?></span></div>
                            <div><span style="color: #888;">Total Classes:</span> <span class="highlight"><?= number_format($totalClasses) ?></span></div>
                            <div><span style="color: #888;">Total Subjects:</span> <span class="highlight"><?= number_format($totalSubjects) ?></span></div>
                            <div><span style="color: #888;">Total Sessions:</span> <span class="highlight"><?= number_format($totalSessions) ?></span></div>
                            <div><span style="color: #888;">Total Users:</span> <span class="highlight"><?= number_format($totalUsers) ?></span></div>
                        </div>
                    </div>
                </div>
                
            </div>
        </div>
        <?php include('inc.footer.php'); ?>
    </div>
</div>
<?php include('inc.js.php'); ?>
</body>
</html>inc.session-create.php