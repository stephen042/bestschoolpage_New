<?php

/**
 * ============================================================================
 * CLASS LEVEL SUBJECT GRADE ANALYSIS - MODERN REDESIGN
 * ============================================================================
 * Description: Analyze subject grades at class level with grade distribution
 * Version: 4.0 (Fully Mobile Responsive)
 * ============================================================================
 */

include('../config.php');
include('inc.session-create.php');

$PageTitle = 'Class Level Subject Grade Analysis';
$FileName = 'view_result_class_level_subject_grade_analysis.php';

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

$validate = new Validation();
$stat = [];

if (isset($_SESSION['success']) && $_SESSION['success'] != "") {
    $stat['success'] = $_SESSION['success'];
    unset($_SESSION['success']);
}

// ============================================================================
// GET CLASS DETAILS
// ============================================================================
$currentClass = db_get_row(
    "SELECT * FROM school_class WHERE randomid = ? AND create_by_userid = ?",
    [$_GET['randomid'] ?? '', $create_by_userid]
);

// ============================================================================
// GET ALL CLASSES FOR SIDEBAR
// ============================================================================
$allClasses = db_get_rows(
    "SELECT * FROM school_class WHERE create_by_userid = ? ORDER BY name ASC",
    [$create_by_userid]
);

// ============================================================================
// GET SESSIONS AND TERMS
// ============================================================================
$sessions = db_get_rows(
    "SELECT * FROM school_session WHERE create_by_userid = ? ORDER BY id DESC",
    [$create_by_userid]
);

$terms = db_get_rows(
    "SELECT * FROM school_term WHERE create_by_userid = ? ORDER BY id DESC",
    [$create_by_userid]
);

// ============================================================================
// GET SELECTED SESSION AND TERM
// ============================================================================
$selectedSession = isset($_GET['grade_session']) ? (int)$_GET['grade_session'] : 0;
$selectedTerm = isset($_GET['term_id']) ? (int)$_GET['term_id'] : 0;

// ============================================================================
// GET GRADE RULES
// ============================================================================
$gradeRules = db_get_rows(
    "SELECT * FROM school_grade WHERE create_by_userid = ? ORDER BY minimum_number ASC",
    [$create_by_userid]
);

// ============================================================================
// GET SUBJECTS AND GRADE ANALYSIS
// ============================================================================
$subjects = [];
$gradeAnalysis = [];
$totalStudents = 0;

if (!empty($currentClass['id']) && $selectedSession > 0 && $selectedTerm > 0) {
    // Get all assessments for this class
    $totalAssesment = db_get_val(
        "SELECT GROUP_CONCAT(assesment_id) FROM score_entry_time_frame 
         WHERE create_by_userid = ? AND session = ? AND term_id = ?",
        [$create_by_userid, $selectedSession, $selectedTerm]
    );

    if (empty($totalAssesment)) {
        // Fallback: get assessments from school_assessment
        $totalAssesment = db_get_val(
            "SELECT GROUP_CONCAT(id) FROM school_assessment 
             WHERE (class_id = ? OR class_id IS NULL OR class_id = 0) AND create_by_userid = ?",
            [$currentClass['id'], $create_by_userid]
        );
    }

    // Get subjects for this class
    $subjects = db_get_rows(
        "SELECT * FROM school_subject WHERE class_id = ? AND create_by_userid = ? ORDER BY subject ASC",
        [$currentClass['id'], $create_by_userid]
    );

    // Get students for this class
    $students = db_get_rows(
        "SELECT * FROM manage_student 
         WHERE class = ? AND session = ? AND term_id = ? AND create_by_userid = ? 
         ORDER BY first_name ASC",
        [$currentClass['id'], $selectedSession, $selectedTerm, $create_by_userid]
    );
    $totalStudents = count($students);

    // For each subject, calculate grade distribution
    foreach ($subjects as $subject) {
        $subjectId = $subject['id'];
        $gradeCounts = [];

        // Initialize grade counts
        foreach ($gradeRules as $grade) {
            $gradeCounts[$grade['grade']] = 0;
        }

        foreach ($students as $student) {
            // Get total score for this student in this subject
            $score = db_get_val(
                "SELECT SUM(score) FROM input_score_class_teacher 
                 WHERE assesment_id IN ($totalAssesment) 
                 AND student_id = ? AND subject_id = ? AND create_by_userid = ?",
                [$student['id'], $subjectId, $create_by_userid]
            );
            $score = $score !== false ? (float)$score : 0;

            // Determine grade
            $grade = 'N/A';
            foreach ($gradeRules as $gradeRule) {
                $min = (float)($gradeRule['minimum_number'] ?? 0);
                $max = (float)($gradeRule['maximum_number'] ?? 0);
                if ($score >= $min && $score <= $max) {
                    $grade = $gradeRule['grade'];
                    break;
                }
            }

            if (isset($gradeCounts[$grade])) {
                $gradeCounts[$grade]++;
            }
        }

        $gradeAnalysis[$subjectId] = $gradeCounts;
    }
}

// Get session and term names
$sessionName = db_get_val("SELECT session FROM school_session WHERE id = ?", [$selectedSession]);
$termName = db_get_val("SELECT term FROM school_term WHERE id = ?", [$selectedTerm]);
?>
<!DOCTYPE html>
<html>

<head>
    <?php include('inc.meta.php'); ?>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
    <style>
        /* ============================================================
        RESET & BASE - MOBILE FIRST
        ============================================================ */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: #f0f2f5;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }

        .analysis-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 15px;
        }

        /* ============================================================
        PAGE HEADER - MOBILE FIRST
        ============================================================ */
        .page-header {
            margin-bottom: 20px;
        }

        .page-header h2 {
            color: #1B3058;
            margin: 0;
            font-size: 22px;
            font-weight: 700;
        }

        .page-header h2 i {
            margin-right: 8px;
        }

        .page-header p {
            color: #666;
            margin-top: 4px;
            font-size: 14px;
        }

        /* ============================================================
        LAYOUT - MOBILE FIRST
        ============================================================ */
        .layout {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        /* ============================================================
        CLASS LIST PANEL - MOBILE FIRST
        ============================================================ */
        .class-panel {
            width: 100%;
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
            overflow: hidden;
        }

        .panel-header {
            padding: 14px 18px;
            background: linear-gradient(135deg, #1B3058, #2a4780);
            color: white;
            font-weight: 600;
            font-size: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .panel-header i {
            font-size: 18px;
        }

        .panel-header .count-badge {
            margin-left: auto;
            background: rgba(255, 255, 255, 0.2);
            padding: 2px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 400;
        }

        .class-list {
            max-height: 300px;
            overflow-y: auto;
            -webkit-overflow-scrolling: touch;
        }

        .class-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 18px;
            border-bottom: 1px solid #f0f0f0;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
            color: #333;
        }

        .class-item:active {
            background: #e8eef5;
        }

        .class-item:hover {
            background: #f8f9ff;
        }

        .class-item.active {
            background: #e8eef5;
            border-left: 4px solid #1B3058;
        }

        .class-item .class-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            background: #e8eef5;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #1B3058;
            flex-shrink: 0;
            font-size: 18px;
        }

        .class-item.active .class-icon {
            background: #1B3058;
            color: white;
        }

        .class-item .class-name {
            font-weight: 600;
            font-size: 14px;
        }

        .class-item .class-arrow {
            margin-left: auto;
            color: #ccc;
            font-size: 14px;
        }

        /* ============================================================
        FILTER CARD - MOBILE FIRST
        ============================================================ */
        .filter-card {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
            padding: 16px;
            margin-bottom: 20px;
        }

        .filter-grid {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .filter-group {
            width: 100%;
        }

        .filter-group label {
            display: block;
            font-size: 11px;
            font-weight: 700;
            color: #888;
            text-transform: uppercase;
            margin-bottom: 4px;
            letter-spacing: 0.5px;
        }

        .filter-select {
            width: 100%;
            padding: 10px 14px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 14px;
            background: #fff;
            transition: all 0.2s;
            -webkit-appearance: none;
            appearance: none;
            background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 12px center;
            background-size: 16px;
        }

        .filter-select:focus {
            border-color: #1B3058;
            outline: none;
            box-shadow: 0 0 0 3px rgba(27, 48, 88, 0.1);
        }

        .filter-actions {
            display: flex;
            gap: 10px;
            margin-top: 4px;
        }

        .filter-actions .btn {
            flex: 1;
            justify-content: center;
        }

        /* ============================================================
        BUTTONS - MOBILE FIRST
        ============================================================ */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 10px 16px;
            border: none;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            min-height: 44px;
            touch-action: manipulation;
        }

        .btn:active {
            transform: scale(0.97);
        }

        .btn-block {
            width: 100%;
        }

        .btn-primary {
            background: #1B3058;
            color: white;
        }

        .btn-primary:hover {
            background: #f21151;
        }

        .btn-success {
            background: #28a745;
            color: white;
        }

        .btn-success:hover {
            background: #218838;
        }

        .btn-outline {
            background: transparent;
            color: #1B3058;
            border: 2px solid #1B3058;
        }

        .btn-outline:hover {
            background: #1B3058;
            color: white;
        }

        /* ============================================================
        ANALYSIS CARD - MOBILE FIRST
        ============================================================ */
        .analysis-card {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
            overflow: hidden;
            margin-top: 16px;
        }

        .analysis-card .card-header {
            padding: 14px 18px;
            background: linear-gradient(135deg, #1B3058, #2a4780);
            color: white;
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .analysis-card .card-header .title {
            font-weight: 600;
            font-size: 15px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .analysis-card .card-header .subtitle {
            font-size: 12px;
            opacity: 0.8;
            font-weight: 400;
        }

        .analysis-card .card-body {
            padding: 12px 8px;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        /* ============================================================
        INFO BAR - MOBILE FIRST
        ============================================================ */
        .info-bar {
            display: flex;
            flex-direction: column;
            gap: 6px;
            padding: 10px 14px;
            background: #f8f9fa;
            border-radius: 10px;
            margin-bottom: 12px;
            font-size: 13px;
        }

        .info-bar .info-item {
            display: flex;
            gap: 8px;
        }

        .info-bar .info-item strong {
            color: #1B3058;
            min-width: 70px;
        }

        .info-bar .info-item span {
            color: #555;
        }

        /* ============================================================
        ANALYSIS TABLE - MOBILE FIRST
        ============================================================ */
        .analysis-table {
            width: 100%;
            min-width: 500px;
            border-collapse: collapse;
            font-size: 11px;
        }

        .analysis-table th,
        .analysis-table td {
            padding: 6px 4px;
            text-align: center;
            border: 1px solid #e0e0e0;
            vertical-align: middle;
        }

        .analysis-table th {
            background: #f8f9fa;
            font-weight: 700;
            color: #1B3058;
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            position: sticky;
            top: 0;
            z-index: 2;
        }

        .analysis-table td {
            font-size: 11px;
        }

        .analysis-table .subject-cell {
            text-align: left;
            font-weight: 600;
            font-size: 12px;
        }

        .analysis-table .total-cell {
            font-weight: 700;
            background: #e8f5e9;
            color: #2e7d32;
        }

        .analysis-table .grade-cell {
            font-weight: 600;
        }

        .analysis-table .grade-badge {
            display: inline-block;
            padding: 2px 10px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 700;
        }

        .analysis-table .grade-badge.percent {
            background: #e3f2fd;
            color: #0d47a1;
        }

        /* ============================================================
        SUMMARY STATS - MOBILE FIRST
        ============================================================ */
        .summary-stats {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
            padding: 12px 8px;
            background: #f8f9fa;
            border-top: 1px solid #eee;
        }

        .summary-stat {
            text-align: center;
            padding: 8px 4px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 1px 4px rgba(0, 0, 0, 0.05);
        }

        .summary-stat .number {
            font-size: 18px;
            font-weight: 700;
            color: #1B3058;
        }

        .summary-stat .label {
            font-size: 9px;
            color: #888;
            text-transform: uppercase;
            font-weight: 600;
            margin-top: 2px;
        }

        /* ============================================================
        ALERTS - MOBILE FIRST
        ============================================================ */
        .alert {
            padding: 12px 16px;
            border-radius: 12px;
            margin-bottom: 16px;
            font-size: 13px;
            display: flex;
            align-items: flex-start;
            gap: 10px;
        }

        .alert i {
            font-size: 18px;
            margin-top: 2px;
            flex-shrink: 0;
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

        /* ============================================================
        EMPTY STATE - MOBILE FIRST
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
            font-size: 16px;
            margin-bottom: 4px;
        }

        .empty-state p {
            font-size: 13px;
        }

        /* ============================================================
        RESPONSIVE - TABLET (768px+)
        ============================================================ */
        @media (min-width: 768px) {
            .analysis-container {
                padding: 25px;
            }

            .layout {
                flex-direction: row;
                gap: 25px;
            }

            .class-panel {
                width: 280px;
                flex-shrink: 0;
            }

            .class-list {
                max-height: 70vh;
            }

            .main-panel {
                flex: 1;
                min-width: 0;
            }

            .filter-grid {
                flex-direction: row;
                flex-wrap: wrap;
                gap: 15px;
            }

            .filter-group {
                flex: 1;
                min-width: 150px;
            }

            .filter-actions {
                flex: 0 0 auto;
            }

            .filter-actions .btn {
                flex: 0 0 auto;
                padding: 10px 24px;
            }

            .analysis-table {
                font-size: 12px;
                min-width: auto;
            }

            .analysis-table th,
            .analysis-table td {
                padding: 8px 10px;
            }

            .analysis-table th {
                font-size: 10px;
            }

            .analysis-table td {
                font-size: 12px;
            }

            .analysis-table .subject-cell {
                font-size: 13px;
            }

            .analysis-card .card-header {
                flex-direction: row;
                justify-content: space-between;
                align-items: center;
                padding: 16px 24px;
            }

            .analysis-card .card-body {
                padding: 16px 20px;
            }

            .info-bar {
                flex-direction: row;
                flex-wrap: wrap;
                gap: 12px 24px;
                padding: 12px 20px;
            }

            .info-bar .info-item strong {
                min-width: auto;
            }

            .summary-stats {
                grid-template-columns: repeat(4, 1fr);
                gap: 12px;
                padding: 16px 20px;
            }

            .summary-stat .number {
                font-size: 22px;
            }

            .page-header h2 {
                font-size: 28px;
            }
        }

        /* ============================================================
        RESPONSIVE - DESKTOP (1024px+)
        ============================================================ */
        @media (min-width: 1024px) {
            .analysis-container {
                padding: 30px;
            }

            .class-panel {
                width: 320px;
            }

            .analysis-table th,
            .analysis-table td {
                padding: 10px 14px;
            }
        }

        /* ============================================================
        RESPONSIVE - SMALL MOBILE (480px-)
        ============================================================ */
        @media (max-width: 480px) {
            .analysis-container {
                padding: 10px;
            }

            .page-header h2 {
                font-size: 18px;
            }

            .page-header p {
                font-size: 12px;
            }

            .filter-card {
                padding: 12px;
            }

            .filter-select {
                font-size: 13px;
                padding: 8px 12px;
            }

            .analysis-table {
                font-size: 10px;
                min-width: 420px;
            }

            .analysis-table th,
            .analysis-table td {
                padding: 4px 2px;
            }

            .analysis-table th {
                font-size: 8px;
            }

            .analysis-table td {
                font-size: 10px;
            }

            .analysis-table .subject-cell {
                font-size: 11px;
            }

            .analysis-table .grade-badge {
                font-size: 9px;
                padding: 1px 6px;
            }

            .analysis-card .card-header .title {
                font-size: 13px;
            }

            .analysis-card .card-header .subtitle {
                font-size: 10px;
            }

            .btn {
                font-size: 12px;
                padding: 8px 14px;
                min-height: 38px;
            }

            .info-bar {
                font-size: 11px;
                padding: 8px 12px;
            }

            .summary-stats {
                grid-template-columns: 1fr 1fr;
                gap: 6px;
                padding: 10px 6px;
            }

            .summary-stat .number {
                font-size: 15px;
            }

            .summary-stat .label {
                font-size: 8px;
            }
        }

        /* ============================================================
        PRINT STYLES
        ============================================================ */
        @media print {

            .class-panel,
            .filter-card,
            .btn,
            .no-print {
                display: none !important;
            }

            .analysis-card {
                box-shadow: none !important;
                border: 1px solid #ddd;
            }

            .analysis-card .card-header {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            body {
                background: white;
            }

            .analysis-container {
                padding: 0;
            }

            .analysis-table th {
                background: #f8f9fa !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .analysis-table .total-cell {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .info-bar {
                border: 1px solid #ddd;
                background: #f8f9fa !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .summary-stats {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
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
                <div class="analysis-container">

                    <!-- Page Header -->
                    <div class="page-header">
                        <h2><i class="fa fa-bar-chart"></i> <?= htmlspecialchars($PageTitle) ?></h2>
                        <p>Analyze subject grade distribution at class level</p>
                    </div>

                    <?= showMessage($stat) ?>

                    <div class="layout">

                        <!-- LEFT: Class List -->
                        <div class="class-panel">
                            <div class="panel-header">
                                <i class="fa fa-graduation-cap"></i> Select Class
                                <span class="count-badge"><?= count($allClasses) ?></span>
                            </div>
                            <div class="class-list">
                                <?php if (!empty($allClasses)): ?>
                                    <?php foreach ($allClasses as $class): ?>
                                        <a href="?action=table&randomid=<?= urlencode($class['randomid']) ?><?= $selectedSession ? '&grade_session=' . $selectedSession : '' ?><?= $selectedTerm ? '&term_id=' . $selectedTerm : '' ?>"
                                            class="class-item <?= (isset($_GET['randomid']) && $_GET['randomid'] == $class['randomid']) ? 'active' : '' ?>">
                                            <div class="class-icon">
                                                <i class="fa fa-book"></i>
                                            </div>
                                            <div class="class-name"><?= htmlspecialchars($class['name']) ?></div>
                                            <div class="class-arrow"><i class="fa fa-chevron-right"></i></div>
                                        </a>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="empty-state" style="padding: 30px 20px;">
                                        <i class="fa fa-book"></i>
                                        <p>No classes found</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- RIGHT: Main Content -->
                        <div class="main-panel">

                            <?php if (!empty($currentClass)): ?>

                                <!-- Filter Form -->
                                <div class="filter-card">
                                    <form method="GET" action="" id="filterForm">
                                        <input type="hidden" name="action" value="table">
                                        <input type="hidden" name="randomid" value="<?= htmlspecialchars($_GET['randomid'] ?? '') ?>">

                                        <div class="filter-grid">
                                            <div class="filter-group">
                                                <label><i class="fa fa-calendar"></i> Session</label>
                                                <select name="grade_session" class="filter-select" required>
                                                    <option value="">-- Select --</option>
                                                    <?php foreach ($sessions as $s): ?>
                                                        <option value="<?= $s['id'] ?>" <?= ($selectedSession == $s['id']) ? 'selected' : '' ?>>
                                                            <?= htmlspecialchars($s['session']) ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="filter-group">
                                                <label><i class="fa fa-tag"></i> Term</label>
                                                <select name="term_id" class="filter-select" required>
                                                    <option value="">-- Select --</option>
                                                    <?php foreach ($terms as $t): ?>
                                                        <option value="<?= $t['id'] ?>" <?= ($selectedTerm == $t['id']) ? 'selected' : '' ?>>
                                                            <?= htmlspecialchars($t['term']) ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="filter-actions">
                                                <button type="submit" class="btn btn-primary btn-block">
                                                    <i class="fa fa-search"></i> Load Analysis
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                </div>

                                <!-- Results Display -->
                                <?php if (!empty($selectedSession) && !empty($selectedTerm)): ?>

                                    <?php if (!empty($subjects) && $totalStudents > 0): ?>
                                        <div class="analysis-card">
                                            <div class="card-header">
                                                <div class="title">
                                                    <i class="fa fa-table"></i> Grade Analysis
                                                </div>
                                                <div class="subtitle">
                                                    <i class="fa fa-info-circle"></i>
                                                    <?= count($subjects) ?> subject(s) · <?= $totalStudents ?> student(s)
                                                </div>
                                            </div>

                                            <!-- Info Bar -->
                                            <div class="info-bar">
                                                <div class="info-item">
                                                    <strong>Class:</strong>
                                                    <span><?= htmlspecialchars($currentClass['name']) ?></span>
                                                </div>
                                                <div class="info-item">
                                                    <strong>Session:</strong>
                                                    <span><?= htmlspecialchars($sessionName ?: 'N/A') ?></span>
                                                </div>
                                                <div class="info-item">
                                                    <strong>Term:</strong>
                                                    <span><?= htmlspecialchars($termName ?: 'N/A') ?></span>
                                                </div>
                                            </div>

                                            <div class="card-body">
                                                <table class="analysis-table" id="analysisTable">
                                                    <thead>
                                                        <tr>
                                                            <th style="min-width:100px;">Subject</th>
                                                            <?php foreach ($gradeRules as $grade): ?>
                                                                <th><?= htmlspecialchars($grade['grade']) ?></th>
                                                            <?php endforeach; ?>
                                                            <th>Total</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php foreach ($subjects as $subject):
                                                            $subjectId = $subject['id'];
                                                            $gradeCounts = $gradeAnalysis[$subjectId] ?? [];
                                                            $subjectTotal = array_sum($gradeCounts);
                                                        ?>
                                                            <tr>
                                                                <td class="subject-cell"><?= htmlspecialchars($subject['subject']) ?></td>
                                                                <?php foreach ($gradeRules as $grade):
                                                                    $count = $gradeCounts[$grade['grade']] ?? 0;
                                                                    $percent = $subjectTotal > 0 ? round(($count / $subjectTotal) * 100, 1) : 0;
                                                                ?>
                                                                    <td class="grade-cell">
                                                                        <?= $count ?>
                                                                        <?php if ($count > 0): ?>
                                                                            <br>
                                                                            <span class="grade-badge percent"><?= $percent ?>%</span>
                                                                        <?php endif; ?>
                                                                    </td>
                                                                <?php endforeach; ?>
                                                                <td class="total-cell"><strong><?= $subjectTotal ?></strong></td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>

                                        <!-- Summary Stats -->
                                        <div class="summary-stats">
                                            <div class="summary-stat">
                                                <div class="number"><?= count($subjects) ?></div>
                                                <div class="label">Total Subjects</div>
                                            </div>
                                            <div class="summary-stat">
                                                <div class="number"><?= $totalStudents ?></div>
                                                <div class="label">Total Students</div>
                                            </div>
                                            <div class="summary-stat">
                                                <div class="number"><?= count($gradeRules) ?></div>
                                                <div class="label">Grade Levels</div>
                                            </div>
                                            <div class="summary-stat">
                                                <div class="number">
                                                    <?php
                                                    $totalGrades = 0;
                                                    foreach ($gradeAnalysis as $counts) {
                                                        $totalGrades += array_sum($counts);
                                                    }
                                                    echo $totalGrades;
                                                    ?>
                                                </div>
                                                <div class="label">Total Entries</div>
                                            </div>
                                        </div>

                                    <?php elseif (!empty($subjects) && $totalStudents == 0): ?>
                                        <div class="alert alert-warning">
                                            <i class="fa fa-exclamation-triangle"></i>
                                            <div>
                                                No students found for this class.
                                                <br><small>Please ensure students are enrolled in this class for the selected session and term.</small>
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <div class="alert alert-warning">
                                            <i class="fa fa-exclamation-triangle"></i>
                                            <div>
                                                No subjects found for this class.
                                                <br><small>Please add subjects to this class first.</small>
                                            </div>
                                        </div>
                                    <?php endif; ?>

                                <?php elseif (!empty($selectedSession) && empty($selectedTerm)): ?>
                                    <div class="alert alert-info">
                                        <i class="fa fa-info-circle"></i>
                                        <div>Please select a term to continue.</div>
                                    </div>
                                <?php elseif (empty($selectedSession)): ?>
                                    <div class="alert alert-info">
                                        <i class="fa fa-info-circle"></i>
                                        <div>Please select a session and term to view the grade analysis.</div>
                                    </div>
                                <?php endif; ?>

                            <?php else: ?>
                                <div class="alert alert-info">
                                    <i class="fa fa-info-circle"></i>
                                    <div>Please select a class from the left panel to view grade analysis.</div>
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
    <script>
        // Auto-submit when filters change
        document.addEventListener('DOMContentLoaded', function() {
            var form = document.getElementById('filterForm');
            if (!form) return;

            var selects = form.querySelectorAll('select');
            selects.forEach(function(select) {
                select.addEventListener('change', function() {
                    var session = form.querySelector('select[name="grade_session"]');
                    var term = form.querySelector('select[name="term_id"]');
                    if (session && term && session.value && term.value) {
                        form.submit();
                    }
                });
            });
        });

        // Add active class to class items
        document.addEventListener('DOMContentLoaded', function() {
            var classItems = document.querySelectorAll('.class-item');
            classItems.forEach(function(item) {
                item.addEventListener('click', function() {
                    var current = document.querySelector('.class-item.active');
                    if (current) {
                        current.classList.remove('active');
                    }
                });
            });
        });
    </script>
</body>

</html>