<?php
/**
 * ============================================================================
 * SUPER ADMIN DASHBOARD - MAIN ADMIN PORTAL
 * ============================================================================
 * Central hub for site administrators to manage the entire platform
 * Only accessible to super admin (usertype = 1)
 * ============================================================================
 */

require_once('../config.php');
require_once('admin-session-check.php');

// ============================================================================
// VERIFY SUPER ADMIN ACCESS
// ============================================================================
if ((int)($_SESSION['usertype'] ?? 0) !== 1) {
    redirect(SKOOL_URL . 'index.php');
    exit;
}

// ============================================================================
// INITIALIZATION
// ============================================================================
$adminId = (int)($_SESSION['userid'] ?? 0);
$adminName = $_SESSION['username'] ?? 'Admin';
$adminSchoolName = $_SESSION['school_name'] ?? 'Site Administrator';

// Get admin user full details
$adminUser = db_get_row("SELECT * FROM school_register WHERE id = ? AND usertype = ?", [$adminId, 1]);

if (empty($adminUser)) {
    session_destroy();
    redirect(ADMIN_URL . 'login.php?error=unauthorized');
    exit;
}

// ============================================================================
// GET SYSTEM STATISTICS
// ============================================================================
$stats = [
    'total_schools' => db_get_val("SELECT COUNT(*) FROM school_register WHERE usertype = '0' AND status = '1'") ?? 0,
    'total_users' => db_get_val("SELECT COUNT(*) FROM school_register WHERE status = '1'") ?? 0,
    'total_students' => db_get_val("SELECT COUNT(*) FROM manage_student") ?? 0,
    'total_teachers' => db_get_val("SELECT COUNT(*) FROM staff_manage") ?? 0,
];

// ============================================================================
// ADMIN MENU ITEMS
// ============================================================================
$adminMenuItems = [
    [
        'title' => 'Dashboard',
        'file' => 'dashboard.php',
        'icon' => '📊',
        'description' => 'System statistics and overview'
    ],
    [
        'title' => 'Schools',
        'file' => 'school_register.php',
        'icon' => '🏫',
        'description' => 'Manage all schools'
    ],
    [
        'title' => 'Users',
        'file' => 'manageuser.php',
        'icon' => '👥',
        'description' => 'Manage all users'
    ],
    [
        'title' => 'Settings',
        'file' => 'app_settings.php',
        'icon' => '⚙️',
        'description' => 'System settings'
    ],
];

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - BestSchoolPage</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            background: #f5f7fa; 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            color: #333;
        }
        .container { max-width: 1200px; margin: 0 auto; padding: 20px; }
        
        header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px 20px;
            border-radius: 10px;
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 20px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        header h1 { font-size: 28px; margin-bottom: 5px; }
        header p { opacity: 0.95; font-size: 14px; }
        .header-right { 
            display: flex; 
            gap: 15px; 
            align-items: center;
            flex-wrap: wrap;
        }
        .admin-badge {
            background: rgba(255,255,255,0.2);
            padding: 8px 16px;
            border-radius: 25px;
            font-size: 13px;
            font-weight: 600;
        }
        .logout-btn {
            background: rgba(255,255,255,0.2);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 25px;
            cursor: pointer;
            font-size: 13px;
            transition: all 0.2s;
            text-decoration: none;
            display: inline-block;
        }
        .logout-btn:hover { background: rgba(255,255,255,0.3); }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 30px;
        }
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            text-align: center;
            transition: transform 0.2s;
        }
        .stat-card:hover { transform: translateY(-3px); }
        .stat-card .number {
            font-size: 32px;
            font-weight: 700;
            color: #667eea;
            margin-bottom: 5px;
        }
        .stat-card .label {
            font-size: 13px;
            color: #888;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .section-title {
            font-size: 18px;
            font-weight: 600;
            color: #333;
            margin-bottom: 15px;
            margin-top: 30px;
        }
        
        .menu-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 15px;
            margin-bottom: 30px;
        }
        .menu-card {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            transition: all 0.3s;
            text-decoration: none;
            color: #333;
            display: flex;
            flex-direction: column;
        }
        .menu-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.15);
        }
        .menu-card-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            text-align: center;
            font-size: 40px;
        }
        .menu-card-body {
            padding: 15px;
            flex: 1;
        }
        .menu-card-title {
            font-size: 16px;
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
        }
        .menu-card-desc {
            font-size: 12px;
            color: #888;
            line-height: 1.4;
        }
        .menu-card-footer {
            padding: 10px 15px 15px;
            border-top: 1px solid #f0f0f0;
            font-size: 12px;
            color: #667eea;
            font-weight: 600;
        }
        
        footer {
            text-align: center;
            padding: 20px;
            color: #888;
            font-size: 12px;
            border-top: 1px solid #e0e0e0;
            margin-top: 40px;
        }
        
        @media (max-width: 768px) {
            header { flex-direction: column; text-align: center; }
            .header-right { justify-content: center; }
            .menu-grid { grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); }
        }
    </style>
</head>
<body>

<div class="container">
    <!-- Header -->
    <header>
        <div>
            <h1>🎓 Admin Portal</h1>
            <p>Welcome, <?php echo htmlspecialchars($adminUser['name'] ?? 'Administrator'); ?></p>
        </div>
        <div class="header-right">
            <div class="admin-badge">👑 SUPER ADMIN</div>
            <a href="<?php echo ADMIN_URL; ?>logout.php" class="logout-btn" onclick="return confirm('Logout?')">Logout</a>
        </div>
    </header>

    <!-- Statistics -->
    <div>
        <h2 class="section-title">📊 System Overview</h2>
        <div class="stats-grid">
            <div class="stat-card">
                <div class="number"><?php echo (int)$stats['total_schools']; ?></div>
                <div class="label">Active Schools</div>
            </div>
            <div class="stat-card">
                <div class="number"><?php echo (int)$stats['total_users']; ?></div>
                <div class="label">Total Users</div>
            </div>
            <div class="stat-card">
                <div class="number"><?php echo (int)$stats['total_students']; ?></div>
                <div class="label">Total Students</div>
            </div>
            <div class="stat-card">
                <div class="number"><?php echo (int)$stats['total_teachers']; ?></div>
                <div class="label">Total Teachers</div>
            </div>
        </div>
    </div>

    <!-- Admin Menu -->
    <div>
        <h2 class="section-title">🔧 Admin Tools</h2>
        <div class="menu-grid">
            <?php foreach ($adminMenuItems as $item): ?>
                <a href="<?php echo htmlspecialchars($item['file']); ?>" class="menu-card">
                    <div class="menu-card-header"><?php echo $item['icon']; ?></div>
                    <div class="menu-card-body">
                        <div class="menu-card-title"><?php echo htmlspecialchars($item['title']); ?></div>
                        <div class="menu-card-desc"><?php echo htmlspecialchars($item['description']); ?></div>
                    </div>
                    <div class="menu-card-footer">Open →</div>
                </a>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Footer -->
    <footer>
        <p>BestSchoolPage Admin Portal | Super Admin Dashboard</p>
        <p>System time: <?php echo date('Y-m-d H:i:s'); ?></p>
    </footer>
</div>

</body>
</html>
