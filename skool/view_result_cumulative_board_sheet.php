<?php

/**
 * ============================================================================
 * VIEW RESULT CUMULATIVE BROAD SHEET - MODERN REDESIGN
 * ============================================================================
 * Description: View cumulative broad sheet with all subjects and assessments
 * Version: 4.0 (Fully Mobile Responsive)
 * ============================================================================
 */

include('../config.php');
include('inc.session-create.php');

$PageTitle = "View Cumulative Broad Sheet";
$FileName = 'view_result_cumulative_board_sheet.php';

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
$classDetail = db_get_row(
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
// GET FILTER DATA
// ============================================================================
$sessions = db_get_rows(
    "SELECT * FROM school_session WHERE create_by_userid = ? ORDER BY id DESC",
    [$create_by_userid]
);

$terms = db_get_rows(
    "SELECT * FROM school_term WHERE create_by_userid = ? ORDER BY id DESC",
    [$create_by_userid]
);

// Get subjects for the selected class
$subjects = [];
if (!empty($classDetail['id'])) {
    $subjects = db_get_rows(
        "SELECT * FROM school_subject WHERE class_id = ? AND create_by_userid = ? ORDER BY subject ASC",
        [$classDetail['id'], $create_by_userid]
    );
}
$totalSubjects = count($subjects);

// ============================================================================
// GET POST DATA
// ============================================================================
$postSession = isset($_POST['session']) ? (int)$_POST['session'] : 0;
$postTerm = isset($_POST['term_id']) ? (int)$_POST['term_id'] : 0;

// ============================================================================
// GET STUDENTS AND SCORES
// ============================================================================
$students = [];
$studentScores = [];
$studentTotals = [];
$studentAverages = [];
$studentGrades = [];
$studentPositions = [];
$classTotal = 0;
$highLow = [];
$tStudent = 0;

if (!empty($classDetail['id']) && $postSession > 0 && $postTerm > 0 && !empty($subjects)) {
    $students = db_get_rows(
        "SELECT * FROM manage_student 
         WHERE class = ? AND session = ? AND term_id = ? AND create_by_userid = ? 
         ORDER BY first_name ASC",
        [$classDetail['id'], $postSession, $postTerm, $create_by_userid]
    );

    foreach ($students as $student) {
        $studentId = (int)$student['id'];
        $totalScore = 0;
        $scores = [];

        foreach ($subjects as $subject) {
            // Get total score for this subject
            $score = db_get_val(
                "SELECT SUM(score) FROM input_score_class_teacher 
                 WHERE student_id = ? 
                 AND subject_id = ? 
                 AND session_id = ? 
                 AND term_id = ? 
                 AND class_id = ? 
                 AND create_by_userid = ?",
                [$studentId, $subject['id'], $postSession, $postTerm, $classDetail['id'], $create_by_userid]
            );
            $scoreValue = $score !== false ? (float)$score : 0;
            $scores[$subject['id']] = $scoreValue;
            $totalScore += $scoreValue;
        }

        $studentScores[$studentId] = $scores;
        $studentTotals[$studentId] = $totalScore;
        $avg = $totalSubjects > 0 ? round($totalScore / $totalSubjects, 2) : 0;
        $studentAverages[$studentId] = $avg;
        $classTotal += $avg;
        $highLow[] = $avg;
        $tStudent++;

        // Get grade
        $grade = db_get_val(
            "SELECT grade FROM school_grade 
             WHERE create_by_userid = ? 
             AND minimum_number <= ? 
             AND maximum_number >= ?",
            [$create_by_userid, $avg, $avg]
        );
        $studentGrades[$studentId] = $grade ?: 'N/A';
    }

    // Calculate positions
    arsort($studentAverages);
    $rank = 1;
    $prevScore = -1;
    $tieRank = 1;
    foreach ($studentAverages as $studentId => $avgScore) {
        if ($avgScore != $prevScore) {
            $studentPositions[$studentId] = $rank;
            $tieRank = $rank;
        } else {
            $studentPositions[$studentId] = $tieRank;
        }
        $rank++;
        $prevScore = $avgScore;
    }
}

$classAverage = $tStudent > 0 ? round($classTotal / $tStudent, 2) : 0;
$highestAverage = !empty($highLow) ? round(max($highLow), 2) : 0;
$lowestAverage = !empty($highLow) ? round(min($highLow), 2) : 0;

// Get session and term names
$sessionName = db_get_val("SELECT session FROM school_session WHERE id = ?", [$postSession]);
$termName = db_get_val("SELECT term FROM school_term WHERE id = ?", [$postTerm]);
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

        .cumulative-container {
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

        .btn-danger {
            background: #dc3545;
            color: white;
        }

        .btn-danger:hover {
            background: #c82333;
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

        .btn-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
            margin-top: 12px;
        }

        .btn-group .btn {
            width: 100%;
        }

        /* ============================================================
        RESULT CARD - MOBILE FIRST
        ============================================================ */
        .result-card {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
            overflow: hidden;
            margin-top: 16px;
        }

        .result-card .card-header {
            padding: 14px 18px;
            background: linear-gradient(135deg, #1B3058, #2a4780);
            color: white;
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .result-card .card-header .title {
            font-weight: 600;
            font-size: 15px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .result-card .card-header .subtitle {
            font-size: 12px;
            opacity: 0.8;
            font-weight: 400;
        }

        .result-card .card-body {
            padding: 12px 8px;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        /* ============================================================
        RESULT TABLE - MOBILE FIRST
        ============================================================ */
        .result-table {
            width: 100%;
            min-width: 700px;
            border-collapse: collapse;
            font-size: 11px;
        }

        .result-table th,
        .result-table td {
            padding: 6px 4px;
            text-align: center;
            border: 1px solid #e0e0e0;
            vertical-align: middle;
        }

        .result-table th {
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

        .result-table th small {
            font-weight: 400;
            opacity: 0.7;
            font-size: 8px;
        }

        .result-table td {
            font-size: 11px;
        }

        .result-table .student-name-cell {
            text-align: left;
            font-weight: 600;
            font-size: 12px;
        }

        .result-table .student-id-cell {
            color: #999;
            font-size: 10px;
        }

        .result-table .total-cell {
            font-weight: 700;
            background: #e8f5e9;
            color: #2e7d32;
        }

        .result-table .average-cell {
            font-weight: 700;
            background: #fff3e0;
            color: #e65100;
        }

        .result-table .grade-cell {
            font-weight: 700;
            background: #e3f2fd;
            color: #0d47a1;
        }

        .result-table .position-cell {
            font-weight: 700;
            background: #fce4ec;
            color: #c62828;
        }

        .result-table .position-badge {
            display: inline-block;
            padding: 2px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
            background: #1B3058;
            color: white;
        }

        .result-table .position-badge.gold {
            background: #ffc107;
            color: #333;
        }

        .result-table .position-badge.silver {
            background: #bdbdbd;
            color: #333;
        }

        .result-table .position-badge.bronze {
            background: #cd7f32;
            color: white;
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
        RESPONSIVE - TABLET (768px+)
        ============================================================ */
        @media (min-width: 768px) {
            .cumulative-container {
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

            .btn-group {
                flex-direction: row;
                flex-wrap: wrap;
            }

            .btn-group .btn {
                width: auto;
            }

            .result-table {
                font-size: 12px;
                min-width: auto;
            }

            .result-table th,
            .result-table td {
                padding: 8px 10px;
            }

            .result-table th {
                font-size: 10px;
            }

            .result-table td {
                font-size: 12px;
            }

            .result-table .student-name-cell {
                font-size: 13px;
            }

            .result-card .card-header {
                flex-direction: row;
                justify-content: space-between;
                align-items: center;
                padding: 16px 24px;
            }

            .result-card .card-body {
                padding: 16px 20px;
            }

            .summary-stats {
                grid-template-columns: repeat(4, 1fr);
                gap: 12px;
                padding: 16px 20px;
            }

            .summary-stat .number {
                font-size: 22px;
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

            .page-header h2 {
                font-size: 28px;
            }
        }

        /* ============================================================
        RESPONSIVE - DESKTOP (1024px+)
        ============================================================ */
        @media (min-width: 1024px) {
            .cumulative-container {
                padding: 30px;
            }

            .class-panel {
                width: 320px;
            }

            .result-table th,
            .result-table td {
                padding: 10px 14px;
            }
        }

        /* ============================================================
        RESPONSIVE - SMALL MOBILE (480px-)
        ============================================================ */
        @media (max-width: 480px) {
            .cumulative-container {
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

            .result-table {
                font-size: 10px;
                min-width: 550px;
            }

            .result-table th,
            .result-table td {
                padding: 4px 2px;
            }

            .result-table th {
                font-size: 8px;
            }

            .result-table td {
                font-size: 10px;
            }

            .result-table .student-name-cell {
                font-size: 11px;
            }

            .result-table .position-badge {
                font-size: 9px;
                padding: 1px 6px;
            }

            .result-card .card-header .title {
                font-size: 13px;
            }

            .result-card .card-header .subtitle {
                font-size: 10px;
            }

            .btn {
                font-size: 12px;
                padding: 8px 14px;
                min-height: 38px;
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

            .info-bar {
                font-size: 11px;
                padding: 8px 12px;
            }
        }

        /* ============================================================
        PRINT STYLES
        ============================================================ */
        @media print {

            .class-panel,
            .filter-card,
            .btn-group,
            .btn,
            .no-print {
                display: none !important;
            }

            .result-card {
                box-shadow: none !important;
                border: 1px solid #ddd;
            }

            .result-card .card-header {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            body {
                background: white;
            }

            .cumulative-container {
                padding: 0;
            }

            .result-table th {
                background: #f8f9fa !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .result-table .total-cell,
            .result-table .average-cell,
            .result-table .grade-cell,
            .result-table .position-cell {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .result-table .position-badge {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .summary-stats {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .info-bar {
                border: 1px solid #ddd;
                background: #f8f9fa !important;
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
                <div class="cumulative-container">

                    <!-- Page Header -->
                    <div class="page-header">
                        <h2><i class="fa fa-bar-chart"></i> <?= htmlspecialchars($PageTitle) ?></h2>
                        <p>View cumulative broad sheet with all subjects and assessments</p>
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
                                        <a href="?action=board_sheet&randomid=<?= urlencode($class['randomid']) ?>"
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

                            <?php if (!empty($classDetail)): ?>

                                <!-- Filter Form -->
                                <div class="filter-card">
                                    <form method="POST" action="" id="filterForm">
                                        <input type="hidden" name="randomid" value="<?= htmlspecialchars($_GET['randomid'] ?? '') ?>">
                                        <input type="hidden" name="action" value="board_sheet">

                                        <div class="filter-grid">
                                            <div class="filter-group">
                                                <label><i class="fa fa-calendar"></i> Session</label>
                                                <select name="session" class="filter-select" required>
                                                    <option value="">-- Select --</option>
                                                    <?php foreach ($sessions as $s): ?>
                                                        <option value="<?= $s['id'] ?>" <?= ($postSession == $s['id']) ? 'selected' : '' ?>>
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
                                                        <option value="<?= $t['id'] ?>" <?= ($postTerm == $t['id']) ? 'selected' : '' ?>>
                                                            <?= htmlspecialchars($t['term']) ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="filter-actions">
                                                <button type="submit" class="btn btn-primary btn-block">
                                                    <i class="fa fa-search"></i> Load Report
                                                </button>
                                            </div>
                                        </div>
                                    </form>

                                    <!-- Print Button -->
                                    <div class="btn-group">
                                        <?php if ($postSession == 0 || $postTerm == 0): ?>
                                            <button type="button" class="btn btn-danger" onclick="alert('Please select session and term first')">
                                                <i class="fa fa-file-pdf-o"></i> Print Cumulative Broad Sheet
                                            </button>
                                        <?php else: ?>
                                            <a href="<?= SKOOL_URL ?>cummulative_broad_sheet_pfd.php?randomid=<?= urlencode($_GET['randomid'] ?? '') ?>&session=<?= $postSession ?>&term_id=<?= $postTerm ?>"
                                                class="btn btn-danger" target="_blank">
                                                <i class="fa fa-file-pdf-o"></i> Print Cumulative Broad Sheet
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <!-- Results Display -->
                                <?php if (!empty($postSession) && !empty($postTerm)): ?>

                                    <?php if (!empty($students)): ?>
                                        <div class="result-card">
                                            <div class="card-header">
                                                <div class="title">
                                                    <i class="fa fa-table"></i>
                                                    <?= htmlspecialchars($classDetail['name'] ?? 'Class') ?> - Cumulative Broad Sheet
                                                </div>
                                                <div class="subtitle">
                                                    <i class="fa fa-info-circle"></i>
                                                    <?= count($students) ?> student(s) · <?= $totalSubjects ?> subject(s)
                                                </div>
                                            </div>

                                            <!-- Info Bar -->
                                            <div class="info-bar">
                                                <div class="info-item">
                                                    <strong>Session:</strong>
                                                    <span><?= htmlspecialchars($sessionName ?: 'N/A') ?></span>
                                                </div>
                                                <div class="info-item">
                                                    <strong>Term:</strong>
                                                    <span><?= htmlspecialchars($termName ?: 'N/A') ?></span>
                                                </div>
                                                <div class="info-item">
                                                    <strong>Class:</strong>
                                                    <span><?= htmlspecialchars($classDetail['name']) ?></span>
                                                </div>
                                            </div>

                                            <div class="card-body">
                                                <table class="result-table" id="resultTable">
                                                    <thead>
                                                        <tr>
                                                            <th style="min-width:80px;">Student</th>
                                                            <th style="min-width:60px;">ID</th>
                                                            <th style="min-width:80px;">Other Name</th>
                                                            <?php foreach ($subjects as $subject): ?>
                                                                <th><?= htmlspecialchars($subject['subject']) ?></th>
                                                            <?php endforeach; ?>
                                                            <th>Subj</th>
                                                            <th>Total</th>
                                                            <th>Avg</th>
                                                            <th>Grade</th>
                                                            <th>Position</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php foreach ($students as $student):
                                                            $studentId = (int)$student['id'];
                                                            $totalScore = $studentTotals[$studentId] ?? 0;
                                                            $avg = $studentAverages[$studentId] ?? 0;
                                                            $grade = $studentGrades[$studentId] ?? 'N/A';
                                                            $position = $studentPositions[$studentId] ?? 0;
                                                            $suffix = 'th';
                                                            $rankClass = '';
                                                            if ($position == 1) {
                                                                $suffix = 'st';
                                                                $rankClass = 'gold';
                                                            } elseif ($position == 2) {
                                                                $suffix = 'nd';
                                                                $rankClass = 'silver';
                                                            } elseif ($position == 3) {
                                                                $suffix = 'rd';
                                                                $rankClass = 'bronze';
                                                            }
                                                        ?>
                                                            <tr>
                                                                <td class="student-name-cell"><?= htmlspecialchars($student['first_name']) ?></td>
                                                                <td class="student-id-cell"><?= htmlspecialchars($student['student_id']) ?></td>
                                                                <td class="student-name-cell"><?= htmlspecialchars($student['other_name'] ?? '') ?></td>
                                                                <?php foreach ($subjects as $subject): ?>
                                                                    <td><?= isset($studentScores[$studentId][$subject['id']]) ? number_format($studentScores[$studentId][$subject['id']], 1) : '0.0' ?></td>
                                                                <?php endforeach; ?>
                                                                <td><?= $totalSubjects ?></td>
                                                                <td class="total-cell"><strong><?= number_format($totalScore, 1) ?></strong></td>
                                                                <td class="average-cell"><?= number_format($avg, 2) ?></td>
                                                                <td class="grade-cell"><?= htmlspecialchars($grade) ?></td>
                                                                <td class="position-cell">
                                                                    <?php if ($position > 0): ?>
                                                                        <span class="position-badge <?= $rankClass ?>">
                                                                            <?= $position . $suffix ?>
                                                                        </span>
                                                                    <?php else: ?>
                                                                        -
                                                                    <?php endif; ?>
                                                                </td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    </tbody>
                                                </table>
                                            </div>

                                            <!-- Summary Stats -->
                                            <div class="summary-stats">
                                                <div class="summary-stat">
                                                    <div class="number"><?= $tStudent ?></div>
                                                    <div class="label">Students</div>
                                                </div>
                                                <div class="summary-stat">
                                                    <div class="number"><?= number_format($classAverage, 2) ?></div>
                                                    <div class="label">Class Average</div>
                                                </div>
                                                <div class="summary-stat">
                                                    <div class="number"><?= number_format($highestAverage, 2 ?? 0) ?></div>
                                                    <div class="label">Highest</div>
                                                </div>
                                                <div class="summary-stat">
                                                    <div class="number"><?= number_format($lowestAverage, 2 ?? 0) ?></div>
                                                    <div class="label">Lowest</div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <div class="alert alert-warning">
                                            <i class="fa fa-exclamation-triangle"></i>
                                            <div>
                                                No students found for the selected criteria.
                                                <br><small>Please ensure students are enrolled in this class for the selected session and term.</small>
                                            </div>
                                        </div>
                                    <?php endif; ?>

                                <?php elseif (!empty($postSession) && empty($postTerm)): ?>
                                    <div class="alert alert-info">
                                        <i class="fa fa-info-circle"></i>
                                        <div>Please select a term to continue.</div>
                                    </div>
                                <?php elseif (empty($postSession)): ?>
                                    <div class="alert alert-info">
                                        <i class="fa fa-info-circle"></i>
                                        <div>Please select a session and term to view the cumulative broad sheet.</div>
                                    </div>
                                <?php endif; ?>

                            <?php else: ?>
                                <div class="alert alert-info">
                                    <i class="fa fa-info-circle"></i>
                                    <div>Please select a class from the left panel to view the cumulative broad sheet.</div>
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
                    var session = form.querySelector('select[name="session"]');
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