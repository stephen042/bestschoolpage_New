<?php
// ============================================================================
// CLASS TEACHER ROLL CALL
// ============================================================================
// Handles attendance tracking for class teachers
// ============================================================================

include('../config.php');
include('inc.session-create.php');

// ============================================================================
// FIX: USE CORRECT USER IDENTIFICATION (Same as dashboard.php)
// ============================================================================
$create_by_userid = (int)($_SESSION['userid'] ?? 0);

// If create_by_userid is not set in session, try to get it from the user record
if ($create_by_userid == 0 && !empty($_SESSION['userid'])) {
    $userData = db_get_row("SELECT create_by_userid FROM users WHERE id = ?", [$_SESSION['userid']]);
    if ($userData && !empty($userData['create_by_userid'])) {
        $create_by_userid = (int)$userData['create_by_userid'];
    }
}

// Fallback: if still 0, use the user's own ID
if ($create_by_userid == 0) {
    $create_by_userid = (int)($_SESSION['userid'] ?? 0);
}

$create_by_usertype = (string)($_SESSION['usertype'] ?? '');
$sessionUserId = (int)($_SESSION['userid'] ?? 0);

// ============================================================================
// GET CLASS DETAILS
// ============================================================================
$studentDetail = [];
$randomid = isset($_GET['randomid']) ? trim($_GET['randomid']) : '';

if (!empty($randomid)) {
    $studentDetail = db_get_row(
        "SELECT * FROM school_class WHERE randomid = ? AND create_by_userid = ?",
        [$randomid, $create_by_userid]
    );
}

// ============================================================================
// GET TEACHER'S ASSIGNED CLASSES
// ============================================================================
$assignedClassIds = [];

if ($_SESSION['usertype'] == '1') {
    $staffDetails = db_get_row(
        "SELECT id FROM staff_manage WHERE staff_id = ? OR id = ?",
        [$_SESSION['username'], $_SESSION['userid']]
    );
    
    if (!empty($staffDetails)) {
        $staffId = (int)$staffDetails['id'];
        $assignedClasses = db_get_rows(
            "SELECT school_class FROM class_teacher WHERE staff_id = ? AND create_by_userid = ?",
            [$staffId, $create_by_userid]
        );
        
        foreach ($assignedClasses as $class) {
            $assignedClassIds[] = (int)$class['school_class'];
        }
    }
}

// ============================================================================
// VALIDATION
// ============================================================================
$validate = new Validation();
$PageTitle = 'Class Teacher Roll Call';
$FileName = 'class_teacher_roll_call.php';

// ============================================================================
// SESSION MESSAGES
// ============================================================================
if (!empty($_SESSION['success'])) {
    $stat['success'] = $_SESSION['success'];
    unset($_SESSION['success']);
}

// ============================================================================
// PROCESS ROLL CALL SUBMISSION
// ============================================================================
if (isset($_POST['roll_call'])) {
    $session_id = isset($_GET['session']) ? (int)$_GET['session'] : 0;
    $term_id = isset($_GET['term_id']) ? (int)$_GET['term_id'] : 0;
    $date = isset($_GET['date']) ? trim($_GET['date']) : '';
    
    $validate->addRule($session_id, 'num', 'session', true);
    $validate->addRule($term_id, 'num', 'Term', true);
    $validate->addRule($date, '', 'ROLL CALL DATE', true);
    
    if ($validate->validate()) {
        try {
            $todayPresent = isset($_POST['present']) ? implode(",", $_POST['present']) : '';
            $todayLate = isset($_POST['late']) ? implode(",", $_POST['late']) : '';
            
            $randomid = randomFix(15);
            
            $aryData = array(
                'roll_call_date'      => $date,
                'session_id'          => $session_id,
                'term_id'             => $term_id,
                'class_id'            => (int)($studentDetail['id'] ?? 0),
                'present'             => $todayPresent,
                'late'                => $todayLate,
                'userid'              => $sessionUserId,
                'usertype'            => (int)$_SESSION['usertype'],
                'create_by_userid'    => $create_by_userid,
                'create_by_usertype'  => $create_by_usertype,
                'randomid'            => $randomid,
            );
            
            $flgIn = db_insert("class_teacher_roll_call", $aryData);
            
            if ($flgIn) {
                $_SESSION['success'] = "Class Roll Submitted Successfully";
                redirect($FileName . '?action=table&randomid=' . urlencode($randomid) . '&date=' . urlencode($date) . '&session=' . $session_id . '&term_id=' . $term_id);
            } else {
                $stat['error'] = "Failed to submit roll call. Please try again.";
            }
        } catch (Exception $e) {
            $stat['error'] = "An error occurred while submitting roll call.";
        }
    } else {
        $stat['error'] = $validate->errors();
    }
}

// ============================================================================
// PROCESS ROLL CALL UPDATE
// ============================================================================
if (isset($_POST['update_roll_call'])) {
    $session_id = isset($_GET['session']) ? (int)$_GET['session'] : 0;
    $term_id = isset($_GET['term_id']) ? (int)$_GET['term_id'] : 0;
    $date = isset($_GET['date']) ? trim($_GET['date']) : '';
    
    $validate->addRule($session_id, 'num', 'session', true);
    $validate->addRule($term_id, 'num', 'Term', true);
    $validate->addRule($date, '', 'ROLL CALL DATE', true);
    
    if ($validate->validate()) {
        try {
            $todayPresent = isset($_POST['present']) ? implode(",", $_POST['present']) : '';
            $todayLate = isset($_POST['late']) ? implode(",", $_POST['late']) : '';
            
            $aryData = array(
                'roll_call_date'      => $date,
                'session_id'          => $session_id,
                'term_id'             => $term_id,
                'class_id'            => (int)($studentDetail['id'] ?? 0),
                'present'             => $todayPresent,
                'late'                => $todayLate,
                'userid'              => $sessionUserId,
                'usertype'            => (int)$_SESSION['usertype'],
                'create_by_userid'    => $create_by_userid,
                'create_by_usertype'  => $create_by_usertype,
            );
            
            $flgIn = db_update(
                "class_teacher_roll_call",
                $aryData,
                "roll_call_date = ? AND session_id = ? AND term_id = ? AND class_id = ? AND create_by_userid = ?",
                [$date, $session_id, $term_id, (int)($studentDetail['id'] ?? 0), $create_by_userid]
            );
            
            if ($flgIn !== false) {
                $_SESSION['success'] = "Class Roll Updated Successfully";
                redirect($FileName . '?action=table&randomid=' . urlencode($randomid) . '&date=' . urlencode($date) . '&session=' . $session_id . '&term_id=' . $term_id);
            } else {
                $stat['error'] = "Failed to update roll call. Please try again.";
            }
        } catch (Exception $e) {
            $stat['error'] = "An error occurred while updating roll call.";
        }
    } else {
        $stat['error'] = $validate->errors();
    }
}

// ============================================================================
// GET DATA FOR DISPLAY
// ============================================================================
$classList = [];
try {
    if ($_SESSION['usertype'] == '1') {
        if (!empty($assignedClassIds)) {
            $classIds = implode(',', array_map('intval', $assignedClassIds));
            $classList = db_get_rows(
                "SELECT * FROM school_class WHERE create_by_userid = ? AND id IN ($classIds) ORDER BY name ASC",
                [$create_by_userid]
            );
        }
    } else {
        $classList = db_get_rows(
            "SELECT * FROM school_class WHERE create_by_userid = ? ORDER BY name ASC",
            [$create_by_userid]
        );
    }
} catch (Exception $e) {
    $classList = [];
}

$rollDetail = [];
if (isset($_GET['action']) && $_GET['action'] == 'table' && !empty($studentDetail)) {
    $session_id = isset($_GET['session']) ? (int)$_GET['session'] : 0;
    $term_id = isset($_GET['term_id']) ? (int)$_GET['term_id'] : 0;
    $date = isset($_GET['date']) ? trim($_GET['date']) : '';
    
    if ($session_id > 0 && $term_id > 0 && !empty($date)) {
        $rollDetail = db_get_row(
            "SELECT * FROM class_teacher_roll_call 
             WHERE roll_call_date = ? AND session_id = ? AND term_id = ? 
             AND class_id = ? AND create_by_userid = ?",
            [$date, $session_id, $term_id, (int)($studentDetail['id'] ?? 0), $create_by_userid]
        );
    }
}

$students = [];
if (!empty($studentDetail) && isset($_GET['session']) && isset($_GET['term_id'])) {
    $session_id = (int)$_GET['session'];
    $term_id = (int)$_GET['term_id'];
    $class_id = (int)($studentDetail['id'] ?? 0);
    
    if ($session_id > 0 && $term_id > 0 && $class_id > 0) {
        $students = db_get_rows(
            "SELECT * FROM manage_student 
             WHERE class = ? AND session = ? AND term_id = ? AND create_by_userid = ?",
            [$class_id, $session_id, $term_id, $create_by_userid]
        );
    }
}

// Get sessions and terms for dropdowns
$sessionList = db_get_rows("SELECT * FROM school_session WHERE create_by_userid = ? ORDER BY id DESC", [$create_by_userid]);
$termList = db_get_rows("SELECT * FROM school_term WHERE create_by_userid = ? ORDER BY id ASC", [$create_by_userid]);

// Get class name for display
$className = $studentDetail['name'] ?? 'Select a Class';
?>
<!DOCTYPE html>
<html>
<head>
    <?php include('inc.meta.php'); ?>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <style>
        /* ============================================================
        RESET & BASE
        ============================================================ */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { background: #f0f2f5; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        
        .roll-container { 
            max-width: 1400px; 
            margin: 0 auto; 
            padding: 20px; 
        }
        
        /* ============================================================
        PAGE HEADER
        ============================================================ */
        .page-header {
            margin-bottom: 25px;
        }
        .page-header h2 {
            color: #1B3058;
            margin: 0;
            font-size: 24px;
            font-weight: 700;
        }
        .page-header p {
            color: #666;
            margin-top: 5px;
            font-size: 14px;
        }
        
        /* ============================================================
        TWO COLUMN LAYOUT - MOBILE FIRST
        ============================================================ */
        .two-column-layout {
            display: flex;
            gap: 25px;
            flex-wrap: wrap;
        }
        .sidebar-panel {
            flex: 1;
            min-width: 280px;
            max-width: 350px;
        }
        .main-panel {
            flex: 3;
            min-width: 300px;
        }
        
        /* ============================================================
        CLASS LIST SIDEBAR
        ============================================================ */
        .class-sidebar {
            background: white;
            border-radius: 20px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            overflow: hidden;
        }
        .sidebar-header {
            background: linear-gradient(135deg, #1B3058 0%, #2a4780 100%);
            color: white;
            padding: 16px 20px;
            font-weight: 600;
            font-size: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .sidebar-header i {
            font-size: 18px;
        }
        .class-list-items {
            max-height: 500px;
            overflow-y: auto;
        }
        .class-item {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 14px 20px;
            border-bottom: 1px solid #f0f0f0;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
            color: #333;
        }
        .class-item:hover {
            background: #f8f9ff;
        }
        .class-item.active {
            background: #e8eef5;
            border-left: 4px solid #1B3058;
        }
        .class-item .class-icon {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            background: #e8eef5;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            color: #1B3058;
            flex-shrink: 0;
        }
        .class-item.active .class-icon {
            background: #1B3058;
            color: white;
        }
        .class-item .class-name {
            font-weight: 600;
            font-size: 15px;
        }
        .class-item .class-meta {
            font-size: 12px;
            color: #999;
            margin-top: 2px;
        }
        .no-classes {
            padding: 30px 20px;
            text-align: center;
            color: #999;
        }
        .no-classes i {
            font-size: 40px;
            color: #ddd;
            display: block;
            margin-bottom: 10px;
        }
        
        /* ============================================================
        FILTER BAR
        ============================================================ */
        .filter-card {
            background: white;
            border-radius: 20px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        }
        .filter-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }
        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }
        .filter-group.full-width {
            grid-column: 1 / -1;
        }
        .filter-group label {
            font-size: 11px;
            font-weight: 700;
            color: #888;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .filter-group select,
        .filter-group input {
            padding: 10px 14px;
            border: 2px solid #e0e0e0;
            border-radius: 12px;
            font-size: 14px;
            background: white;
            transition: all 0.2s;
            width: 100%;
        }
        .filter-group select:focus,
        .filter-group input:focus {
            outline: none;
            border-color: #1B3058;
        }
        .filter-group input[type="text"] {
            -webkit-appearance: none;
        }
        .filter-actions {
            grid-column: 1 / -1;
            display: flex;
            gap: 10px;
            margin-top: 4px;
        }
        .btn-filter {
            background: #1B3058;
            color: white;
            border: none;
            padding: 10px 24px;
            border-radius: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            flex: 1;
        }
        .btn-filter:hover {
            background: #f21151;
            transform: translateY(-2px);
        }
        .btn-filter-outline {
            background: transparent;
            color: #1B3058;
            border: 2px solid #1B3058;
            padding: 8px 20px;
            border-radius: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            flex: 1;
        }
        .btn-filter-outline:hover {
            background: #1B3058;
            color: white;
        }
        
        /* ============================================================
        ATTENDANCE TABLE
        ============================================================ */
        .attendance-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            overflow: hidden;
        }
        .attendance-header {
            background: linear-gradient(135deg, #1B3058 0%, #2a4780 100%);
            color: white;
            padding: 16px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
        }
        .attendance-header h3 {
            margin: 0;
            font-size: 16px;
            font-weight: 600;
        }
        .attendance-header .badge {
            background: rgba(255,255,255,0.2);
            padding: 4px 14px;
            border-radius: 20px;
            font-size: 12px;
        }
        .attendance-body {
            padding: 16px;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
        
        /* Table Styles */
        .attendance-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
            min-width: 500px;
        }
        .attendance-table thead th {
            background: #f8f9fa;
            padding: 10px 12px;
            text-align: left;
            font-weight: 700;
            color: #1B3058;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            border-bottom: 2px solid #e0e0e0;
            position: sticky;
            top: 0;
        }
        .attendance-table tbody td {
            padding: 10px 12px;
            border-bottom: 1px solid #f0f0f0;
            vertical-align: middle;
        }
        .attendance-table tbody tr:hover {
            background: #f8f9ff;
        }
        .attendance-table tbody tr:nth-child(even) {
            background: #fafbfc;
        }
        .attendance-table tbody tr:nth-child(even):hover {
            background: #f8f9ff;
        }
        
        /* Checkbox styling */
        .attendance-table input[type="checkbox"] {
            width: 20px;
            height: 20px;
            cursor: pointer;
            accent-color: #1B3058;
            border-radius: 4px;
        }
        .attendance-table .checkbox-cell {
            text-align: center;
        }
        
        .student-name-cell {
            font-weight: 500;
        }
        .student-id-cell {
            color: #888;
            font-size: 12px;
        }
        
        /* Submit Button */
        .btn-submit {
            background: #28a745;
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 40px;
            font-weight: 600;
            font-size: 15px;
            cursor: pointer;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-top: 16px;
        }
        .btn-submit:hover {
            background: #218838;
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(40, 167, 69, 0.3);
        }
        .btn-submit:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }
        
        .btn-update {
            background: #ffc107;
            color: #333;
            border: none;
            padding: 12px 30px;
            border-radius: 40px;
            font-weight: 600;
            font-size: 15px;
            cursor: pointer;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-top: 16px;
        }
        .btn-update:hover {
            background: #e0a800;
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(255, 193, 7, 0.3);
        }
        
        .btn-group {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            margin-top: 10px;
        }
        
        /* ============================================================
        EMPTY STATE
        ============================================================ */
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #999;
        }
        .empty-state i {
            font-size: 48px;
            color: #ddd;
            display: block;
            margin-bottom: 12px;
        }
        .empty-state h4 {
            color: #666;
            margin-bottom: 6px;
        }
        .empty-state p {
            font-size: 13px;
        }
        
        .alert {
            padding: 12px 16px;
            border-radius: 14px;
            margin-bottom: 16px;
            font-size: 13px;
        }
        .alert-info {
            background: #d1ecf1;
            color: #0c5460;
            border-left: 4px solid #17a2b8;
        }
        .alert-warning {
            background: #fff3cd;
            color: #856404;
            border-left: 4px solid #ffc107;
        }
        .alert-success {
            background: #d4edda;
            color: #155724;
            border-left: 4px solid #28a745;
        }
        .alert-danger {
            background: #f8d7da;
            color: #721c24;
            border-left: 4px solid #dc3545;
        }
        
        /* ============================================================
        RESPONSIVE
        ============================================================ */
        @media (max-width: 768px) {
            .roll-container {
                padding: 12px;
            }
            .two-column-layout {
                flex-direction: column;
            }
            .sidebar-panel {
                max-width: 100%;
                min-width: auto;
            }
            .filter-grid {
                grid-template-columns: 1fr;
            }
            .filter-group.full-width {
                grid-column: 1;
            }
            .filter-actions {
                flex-direction: column;
            }
            .btn-filter,
            .btn-filter-outline {
                width: 100%;
                text-align: center;
            }
            .attendance-header {
                flex-direction: column;
                text-align: center;
            }
            .attendance-table {
                font-size: 12px;
                min-width: 400px;
            }
            .attendance-table thead th,
            .attendance-table tbody td {
                padding: 8px 8px;
            }
            .attendance-table input[type="checkbox"] {
                width: 18px;
                height: 18px;
            }
            .btn-group {
                flex-direction: column;
            }
            .btn-submit,
            .btn-update {
                width: 100%;
                justify-content: center;
            }
            .page-header h2 {
                font-size: 20px;
            }
        }
        
        @media (max-width: 480px) {
            .roll-container {
                padding: 8px;
            }
            .filter-card {
                padding: 14px;
                border-radius: 16px;
            }
            .attendance-body {
                padding: 10px 6px;
            }
            .attendance-table {
                font-size: 11px;
                min-width: 320px;
            }
            .attendance-table thead th,
            .attendance-table tbody td {
                padding: 6px 4px;
            }
            .attendance-table .student-name-cell {
                font-size: 11px;
            }
            .class-item {
                padding: 12px 14px;
            }
            .class-item .class-icon {
                width: 36px;
                height: 36px;
                font-size: 16px;
            }
            .class-item .class-name {
                font-size: 13px;
            }
        }
        
        @media (min-width: 769px) and (max-width: 1024px) {
            .filter-grid {
                grid-template-columns: 1fr 1fr;
            }
            .sidebar-panel {
                min-width: 240px;
                max-width: 280px;
            }
        }
        
        /* ============================================================
        PRINT STYLES
        ============================================================ */
        @media print {
            .filter-card,
            .sidebar-panel,
            .btn-group,
            .btn-submit,
            .btn-update {
                display: none !important;
            }
            .attendance-card {
                box-shadow: none !important;
                border: 1px solid #ddd;
            }
            .attendance-header {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            body {
                background: white;
            }
        }
    </style>
</head>
<body>
<div id="wrapper">
    <?php include('inc.header.php'); ?>
    <?php include('inc.sideleft.php'); ?>
    <div class="content-page">
        <div class="content">
            <div class="roll-container">
                
                <!-- Page Header -->
                <div class="page-header">
                    <h2><i class="fa fa-clipboard"></i> <?= e($PageTitle) ?></h2>
                    <p>Select a class, session, term, and date to take attendance</p>
                </div>
                
                <?= showMessage($stat) ?>
                
                <div class="two-column-layout">
                    
                    <!-- LEFT: Class List -->
                    <div class="sidebar-panel">
                        <div class="class-sidebar">
                            <div class="sidebar-header">
                                <i class="fa fa-graduation-cap"></i> My Classes
                                <span style="margin-left: auto; background: rgba(255,255,255,0.2); padding: 2px 12px; border-radius: 12px; font-size: 12px;">
                                    <?= count($classList) ?>
                                </span>
                            </div>
                            <div class="class-list-items">
                                <?php if (!empty($classList)): ?>
                                    <?php foreach ($classList as $iList): ?>
                                        <a href="<?= $FileName ?>?action=table&randomid=<?= e($iList['randomid']) ?>" 
                                           class="class-item <?= (isset($_GET['randomid']) && $_GET['randomid'] == $iList['randomid']) ? 'active' : '' ?>">
                                            <div class="class-icon">
                                                <i class="fa fa-book"></i>
                                            </div>
                                            <div>
                                                <div class="class-name"><?= e($iList['name']) ?></div>
                                                <div class="class-meta">Click to take roll call</div>
                                            </div>
                                        </a>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="no-classes">
                                        <i class="fa fa-book"></i>
                                        <p>No classes assigned</p>
                                        <small>Contact your administrator</small>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <!-- RIGHT: Main Content -->
                    <div class="main-panel">
                        
                        <?php if (isset($_GET['action']) && $_GET['action'] == 'table' && !empty($studentDetail)): ?>
                            
                            <!-- Filter Bar -->
                            <div class="filter-card">
                                <form method="GET" action="" id="filterForm">
                                    <input type="hidden" name="action" value="table">
                                    <input type="hidden" name="randomid" value="<?= e($_GET['randomid'] ?? '') ?>">
                                    
                                    <div class="filter-grid">
                                        <div class="filter-group">
                                            <label><i class="fa fa-calendar"></i> Session</label>
                                            <select name="session" id="session" required>
                                                <option value="">Select Session</option>
                                                <?php foreach ($sessionList as $sList): ?>
                                                    <option value="<?= (int)$sList['id'] ?>" <?= (isset($_GET['session']) && $_GET['session'] == $sList['id']) ? 'selected' : '' ?>>
                                                        <?= e($sList['session']) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        
                                        <div class="filter-group">
                                            <label><i class="fa fa-tag"></i> Term</label>
                                            <select name="term_id" id="term_id" required>
                                                <option value="">Select Term</option>
                                                <?php foreach ($termList as $tList): ?>
                                                    <option value="<?= (int)$tList['id'] ?>" <?= (isset($_GET['term_id']) && $_GET['term_id'] == $tList['id']) ? 'selected' : '' ?>>
                                                        <?= e($tList['term']) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        
                                        <div class="filter-group full-width">
                                            <label><i class="fa fa-calendar-check-o"></i> Roll Call Date</label>
                                            <input type="text" class="datepicker" placeholder="YYYY-MM-DD" name="date" 
                                                   value="<?= isset($_GET['date']) ? e($_GET['date']) : date('Y-m-d') ?>" 
                                                   autocomplete="off" required />
                                        </div>
                                        
                                        <div class="filter-actions">
                                            <button type="submit" class="btn-filter">
                                                <i class="fa fa-search"></i> Load Students
                                            </button>
                                            <button type="button" class="btn-filter-outline" onclick="resetFilters()">
                                                <i class="fa fa-refresh"></i> Reset
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                            
                            <!-- Attendance Table -->
                            <?php if (!empty($students)): ?>
                                <div class="attendance-card">
                                    <div class="attendance-header">
                                        <h3>
                                            <i class="fa fa-users"></i> 
                                            <?= e($className) ?> - Roll Call
                                        </h3>
                                        <span class="badge">
                                            <?= count($students) ?> Students
                                        </span>
                                    </div>
                                    <div class="attendance-body">
                                        <form method="post" action="">
                                            <div style="overflow-x: auto;">
                                                <table class="attendance-table">
                                                    <thead>
                                                        <tr>
                                                            <th style="width:50px;">#</th>
                                                            <th>Student ID</th>
                                                            <th>Student Name</th>
                                                            <th style="text-align:center; width:100px;">Present</th>
                                                            <th style="text-align:center; width:100px;">Late</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php 
                                                        $i = 0;
                                                        $presentIds = [];
                                                        $lateIds = [];
                                                        
                                                        if (!empty($rollDetail)) {
                                                            $presentIds = !empty($rollDetail['present']) ? explode(",", $rollDetail['present']) : [];
                                                            $lateIds = !empty($rollDetail['late']) ? explode(",", $rollDetail['late']) : [];
                                                        }
                                                        
                                                        foreach ($students as $iList): 
                                                            $i++;
                                                            $studentData = db_get_row(
                                                                "SELECT * FROM manage_student WHERE id = ? AND create_by_userid = ?",
                                                                [$iList['id'], $create_by_userid]
                                                            );
                                                            if (empty($studentData)) continue;
                                                        ?>
                                                            <tr>
                                                                <td><?= $i ?></td>
                                                                <td class="student-id-cell"><?= e($studentData['student_id'] ?? '') ?></td>
                                                                <td class="student-name-cell">
                                                                    <?= e(($studentData['first_name'] ?? '') . ' ' . ($studentData['last_name'] ?? '')) ?>
                                                                </td>
                                                                <td class="checkbox-cell">
                                                                    <input type="checkbox" name="present[]" value="<?= (int)$iList['id'] ?>" 
                                                                           <?= in_array((string)$iList['id'], $presentIds) ? 'checked' : '' ?>>
                                                                </td>
                                                                <td class="checkbox-cell">
                                                                    <input type="checkbox" name="late[]" value="<?= (int)$iList['id'] ?>" 
                                                                           <?= in_array((string)$iList['id'], $lateIds) ? 'checked' : '' ?>>
                                                                </td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                            
                                            <div class="btn-group">
                                                <?php if (empty($rollDetail)): ?>
                                                    <button type="submit" name="roll_call" class="btn-submit">
                                                        <i class="fa fa-check"></i> Lock Attendance
                                                    </button>
                                                <?php else: ?>
                                                    <button type="submit" name="update_roll_call" class="btn-update">
                                                        <i class="fa fa-pencil"></i> Update Attendance
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            <?php else: ?>
                                <div class="attendance-card">
                                    <div class="attendance-header">
                                        <h3><i class="fa fa-users"></i> No Students Found</h3>
                                    </div>
                                    <div class="empty-state">
                                        <i class="fa fa-user-slash"></i>
                                        <h4>No Students Enrolled</h4>
                                        <p>No students are enrolled in this class for the selected session and term.</p>
                                    </div>
                                </div>
                            <?php endif; ?>
                            
                        <?php else: ?>
                            <!-- No Class Selected -->
                            <div class="attendance-card">
                                <div class="attendance-header">
                                    <h3><i class="fa fa-hand-pointer-o"></i> Select a Class</h3>
                                </div>
                                <div class="empty-state">
                                    <i class="fa fa-book"></i>
                                    <h4>Choose a Class</h4>
                                    <p>Select a class from the left sidebar to take attendance.</p>
                                    <small style="color: #ccc;">Click on any class name to get started</small>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                    </div>
                </div>
                
            </div>
        </div>
        <?php include('inc.footer.php'); ?>
    </div>
</div>

<?php include('inc.js.php'); ?>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
    // Initialize datepicker
    // document.addEventListener('DOMContentLoaded', function() {
    //     var dateInputs = document.querySelectorAll('.datepicker');
    //     dateInputs.forEach(function(input) {
    //         flatpickr(input, {
    //             dateFormat: 'Y-m-d',
    //             maxDate: new Date(),
    //             allowInput: true
    //         });
    //     });
    // });
    
    function resetFilters() {
        var form = document.getElementById('filterForm');
        var inputs = form.querySelectorAll('select, input');
        inputs.forEach(function(input) {
            if (input.name !== 'action' && input.name !== 'randomid') {
                input.value = '';
            }
        });
        // Set date to today
        var dateInput = form.querySelector('input[name="date"]');
        if (dateInput) {
            dateInput.value = '<?= date('Y-m-d') ?>';
        }
        form.submit();
    }
</script>
</body>
</html>