<?php

/**
 * Cumulative Broad Sheet - Modern Version
 * Displays cumulative student performance across all subjects
 * Version: 4.0 (Fully Mobile Responsive)
 */

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
$sessionUsername = (string)($_SESSION['username'] ?? '');
$sessionEmail = (string)($_SESSION['email'] ?? '');
$isSchoolOwnerSession = ($sessionUserId > 0 && $sessionUserId === $create_by_userid);

// ============================================================================
// INITIALIZATION
// ============================================================================
$PageTitle = 'Cumulative Broad Sheet';
$FileName = 'cumulative_board_sheet.php';
$validate = new Validation();

// Safe GET/POST parameter defaults
$get_randomid = $_GET['randomid'] ?? '';
$get_action   = $_GET['action'] ?? '';
$post_session = $_POST['session'] ?? '';
$post_term_id = $_POST['term_id'] ?? '';

// ============================================================================
// GET CLASS DETAILS
// ============================================================================
$classDetail = [];
if (!empty($get_randomid)) {
    $classDetail = db_get_row(
        "SELECT * FROM school_class WHERE randomid = ? AND create_by_userid = ?",
        [$get_randomid, $create_by_userid]
    );
}

// ============================================================================
// GET ALL CLASSES FOR SIDEBAR
// ============================================================================
$allClasses = [];
if ($_SESSION['usertype'] == '1') {
    $schoolRegisterStaffid = db_get_val(
        "SELECT username FROM school_register WHERE id = ?",
        [$sessionUserId]
    );
    $staffManageId = db_get_val(
        "SELECT id FROM staff_manage WHERE staff_id = ?",
        [$schoolRegisterStaffid]
    );
    $assignedClassIds = db_get_val(
        "SELECT GROUP_CONCAT(school_class) FROM class_teacher WHERE staff_id = ?",
        [$staffManageId]
    );
    $assignedClassIds = $assignedClassIds ? $assignedClassIds : '0';
    $allClasses = db_get_rows(
        "SELECT * FROM school_class WHERE create_by_userid = ? AND id IN ($assignedClassIds) ORDER BY name ASC",
        [$create_by_userid]
    );
} else {
    $allClasses = db_get_rows(
        "SELECT * FROM school_class WHERE create_by_userid = ? ORDER BY name ASC",
        [$create_by_userid]
    );
}

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
// GET SUBJECTS, STUDENTS, AND SCORES
// ============================================================================
$subjects = [];
$students = [];
$studentScores = [];
$studentTotals = [];
$studentAverages = [];
$studentPositions = [];
$totalSubjects = 0;
$studentCount = 0;
$classTotal = 0;
$highLow = [];

if (!empty($classDetail['id']) && !empty($post_session) && !empty($post_term_id)) {
    // Get subjects for this class
    $subjects = db_get_rows(
        "SELECT * FROM school_subject WHERE class_id = ? AND create_by_userid = ? ORDER BY subject ASC",
        [$classDetail['id'], $create_by_userid]
    );
    $totalSubjects = count($subjects);

    // Get students for this class
    $students = db_get_rows(
        "SELECT * FROM manage_student 
         WHERE class = ? AND session = ? AND term_id = ? AND create_by_userid = ? 
         ORDER BY first_name ASC",
        [$classDetail['id'], $post_session, $post_term_id, $create_by_userid]
    );
    $studentCount = count($students);

    // Get all scores
    $allScores = db_get_rows(
        "SELECT student_id, subject_id, COALESCE(SUM(score), 0) AS total_score
         FROM input_score_class_teacher 
         WHERE session_id = ? 
         AND term_id = ? 
         AND class_id = ? 
         AND create_by_userid = ?
         GROUP BY student_id, subject_id",
        [$post_session, $post_term_id, $classDetail['id'], $create_by_userid]
    );

    // Build scores lookup
    $scoresLookup = [];
    foreach ($allScores as $scoreRow) {
        $scoresLookup[$scoreRow['student_id']][$scoreRow['subject_id']] = (float)$scoreRow['total_score'];
    }

    // Calculate totals and averages
    foreach ($students as $student) {
        $studentTotal = 0;
        foreach ($subjects as $subject) {
            $score = $scoresLookup[$student['id']][$subject['id']] ?? 0;
            $studentTotal += $score;
        }
        $studentTotals[$student['id']] = $studentTotal;
        $avg = $totalSubjects > 0 ? round($studentTotal / $totalSubjects, 2) : 0;
        $studentAverages[$student['id']] = $avg;
        $classTotal += $avg;
        $highLow[] = $avg;
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

// Get grade scale
$gradeRules = db_get_rows(
    "SELECT minimum_number, maximum_number, grade
     FROM school_grade
     WHERE create_by_userid = ?
     ORDER BY minimum_number DESC",
    [$create_by_userid]
);

function getGrade($score)
{
    global $gradeRules;
    foreach ($gradeRules as $rule) {
        $min = (float)($rule['minimum_number'] ?? 0);
        $max = (float)($rule['maximum_number'] ?? 0);
        if ($score >= $min && $score <= $max) {
            return (string)($rule['grade'] ?? '');
        }
    }
    return ($score >= 70 ? 'A' : ($score >= 60 ? 'B' : ($score >= 50 ? 'C' : ($score >= 45 ? 'D' : ($score >= 40 ? 'E' : 'F')))));
}

// Get session and term names
$sessionName = db_get_val("SELECT session FROM school_session WHERE id = ?", [$post_session]);
$termName = db_get_val("SELECT term FROM school_term WHERE id = ?", [$post_term_id]);
$className = $classDetail['name'] ?? '';

// Get school details
$schoolDetails = db_get_row("SELECT * FROM school_register WHERE id = ?", [$create_by_userid]);
$state = db_get_row("SELECT * FROM state WHERE id = ?", [$schoolDetails['state'] ?? 0]);
$stateName = $state['title'] ?? '';
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
            max-width: 1600px;
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

        .btn-danger {
            background: #dc3545;
            color: white;
        }

        .btn-danger:hover {
            background: #c82333;
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
        HEADER SECTION - MOBILE FIRST
        ============================================================ */
        .header-section {
            text-align: center;
            margin-bottom: 16px;
            padding: 16px;
            background: white;
            border-radius: 16px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
        }

        .header-section .school-name {
            font-size: 20px;
            font-weight: 700;
            color: #1B3058;
        }

        .header-section .school-address {
            font-size: 13px;
            color: #666;
            margin-top: 2px;
        }

        .header-section .report-title {
            font-size: 16px;
            font-weight: 700;
            text-decoration: underline;
            margin-top: 6px;
            color: #1B3058;
        }

        .header-section .info-bar {
            background: #f0f4f8;
            padding: 10px;
            border-radius: 10px;
            margin-top: 10px;
            font-size: 13px;
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 6px 16px;
        }

        .header-section .info-bar strong {
            color: #1B3058;
        }

        /* ============================================================
        RESULT CARD - MOBILE FIRST
        ============================================================ */
        .result-card {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
            overflow: hidden;
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
            white-space: nowrap;
        }

        .result-table th {
            background: #1B3058;
            color: white;
            font-weight: 700;
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            position: sticky;
            top: 0;
            z-index: 2;
        }

        .result-table th small {
            font-weight: 400;
            opacity: 0.8;
            font-size: 8px;
            display: block;
        }

        .result-table tr:nth-child(even) {
            background: #f9f9f9;
        }

        .result-table tr:active {
            background: #f0f4ff;
        }

        .result-table .student-name-cell {
            text-align: left !important;
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

        .result-table .grade-cell {
            font-weight: 700;
            background: #e3f2fd;
            color: #0d47a1;
        }

        .result-table .pos-cell {
            font-weight: 700;
        }

        .result-table .pos-1 {
            background: #ffd700;
            color: #333;
            font-weight: 700;
        }

        .result-table .pos-2 {
            background: #c0c0c0;
            color: #333;
            font-weight: 700;
        }

        .result-table .pos-3 {
            background: #cd7f32;
            color: white;
            font-weight: 700;
        }

        .result-table .position-badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 10px;
            font-weight: 700;
            background: #1B3058;
            color: white;
        }

        .result-table .position-badge.gold {
            background: #ffd700;
            color: #333;
        }

        .result-table .position-badge.silver {
            background: #c0c0c0;
            color: #333;
        }

        .result-table .position-badge.bronze {
            background: #cd7f32;
            color: white;
        }

        /* ============================================================
        SUMMARY CARD - MOBILE FIRST
        ============================================================ */
        .summary-card {
            background: white;
            border-radius: 16px;
            padding: 14px 12px;
            margin-top: 16px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
        }

        .summary-item {
            text-align: center;
            padding: 8px 4px;
            background: #f8f9fa;
            border-radius: 10px;
        }

        .summary-item .summary-label {
            font-size: 9px;
            color: #888;
            text-transform: uppercase;
            font-weight: 600;
        }

        .summary-item .summary-value {
            font-size: 18px;
            font-weight: 700;
            color: #1B3058;
        }

        /* ============================================================
        EMPTY STATE - MOBILE FIRST
        ============================================================ */
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            background: white;
            border-radius: 16px;
            color: #999;
        }

        .empty-state i {
            font-size: 48px;
            color: #ddd;
            display: block;
            margin-bottom: 12px;
        }

        .empty-state h3 {
            color: #666;
            font-size: 16px;
            margin-bottom: 4px;
        }

        .empty-state p {
            font-size: 13px;
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

        .alert-info {
            background: #d1ecf1;
            color: #0c5460;
            border-left: 4px solid #17a2b8;
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

            .main-content {
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

            .btn-group {
                flex-direction: row;
                flex-wrap: wrap;
                justify-content: flex-end;
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

            .result-table .student-name-cell {
                font-size: 13px;
            }

            .header-section .school-name {
                font-size: 24px;
            }

            .header-section .report-title {
                font-size: 18px;
            }

            .summary-card {
                grid-template-columns: repeat(4, 1fr);
                gap: 12px;
                padding: 16px 20px;
            }

            .summary-item .summary-value {
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

            .result-table th {
                font-size: 11px;
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
                font-size: 9px;
                min-width: 580px;
            }

            .result-table th,
            .result-table td {
                padding: 4px 2px;
            }

            .result-table th {
                font-size: 7px;
            }

            .result-table td {
                font-size: 9px;
            }

            .result-table .student-name-cell {
                font-size: 10px;
            }

            .result-table .student-id-cell {
                font-size: 8px;
            }

            .result-table .position-badge {
                font-size: 8px;
                padding: 1px 5px;
            }

            .header-section {
                padding: 12px;
            }

            .header-section .school-name {
                font-size: 16px;
            }

            .header-section .report-title {
                font-size: 14px;
            }

            .header-section .info-bar {
                font-size: 11px;
                flex-direction: column;
                gap: 4px;
            }

            .summary-card {
                grid-template-columns: 1fr 1fr;
                gap: 6px;
                padding: 10px 8px;
            }

            .summary-item .summary-value {
                font-size: 15px;
            }

            .summary-item .summary-label {
                font-size: 8px;
            }

            .btn {
                font-size: 12px;
                padding: 8px 14px;
                min-height: 38px;
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
            .no-print,
            .page-header {
                display: none !important;
            }

            .layout {
                display: block;
            }

            .main-content {
                width: 100%;
            }

            .result-table {
                font-size: 9px;
                min-width: auto;
            }

            .result-table th {
                background: #1B3058 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
                color: white !important;
            }

            .result-table .total-cell,
            .result-table .grade-cell,
            .result-table .pos-1,
            .result-table .pos-2,
            .result-table .pos-3 {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .header-section {
                box-shadow: none !important;
                border: 1px solid #ddd;
            }

            .summary-card {
                box-shadow: none !important;
                border: 1px solid #ddd;
            }

            body {
                background: white;
            }

            .cumulative-container {
                padding: 0;
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
                        <p>View cumulative student performance across all subjects</p>
                    </div>

                    <div class="layout">

                        <!-- LEFT SIDEBAR -->
                        <div class="class-panel">
                            <div class="panel-header">
                                <i class="fa fa-graduation-cap"></i> Select Class
                                <span class="count-badge"><?= count($allClasses) ?></span>
                            </div>
                            <div class="class-list">
                                <?php if (!empty($allClasses)): ?>
                                    <?php foreach ($allClasses as $class): ?>
                                        <a href="?action=board_sheet&randomid=<?= urlencode($class['randomid']) ?>"
                                            class="class-item <?= ($get_randomid == $class['randomid']) ? 'active' : '' ?>">
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

                        <!-- RIGHT MAIN CONTENT -->
                        <div class="main-content">
                            <?php if (!empty($classDetail)): ?>

                                <!-- FILTERS -->
                                <div class="filter-card">
                                    <form method="POST" action="" id="filterForm">
                                        <input type="hidden" name="randomid" value="<?= htmlspecialchars($get_randomid) ?>">
                                        <input type="hidden" name="action" value="<?= htmlspecialchars($get_action) ?>">
                                        <div class="filter-grid">
                                            <div class="filter-group">
                                                <label><i class="fa fa-calendar"></i> Session</label>
                                                <select name="session" class="filter-select" required>
                                                    <option value="">-- Select --</option>
                                                    <?php foreach ($sessions as $s): ?>
                                                        <option value="<?= $s['id'] ?>" <?= ($post_session == $s['id']) ? 'selected' : '' ?>>
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
                                                        <option value="<?= $t['id'] ?>" <?= ($post_term_id == $t['id']) ? 'selected' : '' ?>>
                                                            <?= htmlspecialchars($t['term']) ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="filter-actions">
                                                <button type="submit" class="btn btn-primary btn-block">
                                                    <i class="fa fa-filter"></i> Load Report
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                </div>

                                <?php if (!empty($post_session) && !empty($post_term_id)): ?>

                                    <!-- HEADER -->
                                    <div class="header-section">
                                        <div class="school-name"><?= htmlspecialchars($schoolDetails['name'] ?? 'School Name') ?></div>
                                        <div class="school-address">
                                            <?= htmlspecialchars($schoolDetails['location'] ?? '') ?><?= !empty($stateName) ? ', ' . htmlspecialchars($stateName) : '' ?>
                                        </div>
                                        <div class="report-title">CUMULATIVE BROAD SHEET REPORT</div>
                                        <div class="info-bar">
                                            <span><strong>Class:</strong> <?= htmlspecialchars($className) ?></span>
                                            <span><strong>Session:</strong> <?= htmlspecialchars($sessionName) ?></span>
                                            <span><strong>Term:</strong> <?= htmlspecialchars($termName) ?></span>
                                            <span><strong>Students:</strong> <?= $studentCount ?></span>
                                            <span><strong>Subjects:</strong> <?= $totalSubjects ?></span>
                                        </div>
                                    </div>

                                    <!-- TABLE -->
                                    <?php if (!empty($students)): ?>
                                        <div class="result-card">
                                            <div class="card-body">
                                                <table class="result-table" id="resultTable">
                                                    <thead>
                                                        <tr>
                                                            <th style="min-width:40px;">#</th>
                                                            <th style="min-width:60px;">ID</th>
                                                            <th style="min-width:80px;">First Name</th>
                                                            <th style="min-width:80px;">Last Name</th>
                                                            <th style="min-width:80px;">Other Name</th>
                                                            <?php foreach ($subjects as $subject): ?>
                                                                <th><small><?= htmlspecialchars($subject['subject']) ?></small></th>
                                                            <?php endforeach; ?>
                                                            <th>Subj</th>
                                                            <th>Total</th>
                                                            <th>Avg</th>
                                                            <th>Grade</th>
                                                            <th>Pos</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php $counter = 0;
                                                        foreach ($students as $student): $counter++; ?>
                                                            <tr>
                                                                <td><?= $counter ?></td>
                                                                <td class="student-id-cell"><?= htmlspecialchars($student['student_id']) ?></td>
                                                                <td class="student-name-cell"><?= htmlspecialchars($student['first_name']) ?></td>
                                                                <td class="student-name-cell"><?= htmlspecialchars($student['last_name']) ?></td>
                                                                <td class="student-name-cell"><?= htmlspecialchars($student['other_name'] ?? '') ?></td>

                                                                <?php
                                                                $studentTotal = 0;
                                                                foreach ($subjects as $subject):
                                                                    $score = $scoresLookup[$student['id']][$subject['id']] ?? 0;
                                                                    $studentTotal += $score;
                                                                ?>
                                                                    <td><?= number_format($score, 1) ?></td>
                                                                <?php endforeach; ?>

                                                                <td><?= $totalSubjects ?></td>
                                                                <td class="total-cell"><strong><?= number_format($studentTotal, 1) ?></strong></td>
                                                                <td><?= number_format($studentAverages[$student['id']] ?? 0, 2) ?></td>
                                                                <td class="grade-cell"><?= getGrade($studentAverages[$student['id']] ?? 0) ?></td>
                                                                <td class="pos-cell">
                                                                    <?php
                                                                    $pos = $studentPositions[$student['id']] ?? 0;
                                                                    $posClass = '';
                                                                    $suffix = 'th';
                                                                    if ($pos == 1) {
                                                                        $suffix = 'st';
                                                                        $posClass = 'gold';
                                                                    } elseif ($pos == 2) {
                                                                        $suffix = 'nd';
                                                                        $posClass = 'silver';
                                                                    } elseif ($pos == 3) {
                                                                        $suffix = 'rd';
                                                                        $posClass = 'bronze';
                                                                    }
                                                                    ?>
                                                                    <?php if ($pos > 0): ?>
                                                                        <span class="position-badge <?= $posClass ?>">
                                                                            <?= $pos . $suffix ?>
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
                                        </div>

                                        <!-- SUMMARY -->
                                        <div class="summary-card">
                                            <div class="summary-item">
                                                <div class="summary-label">Students</div>
                                                <div class="summary-value"><?= $studentCount ?></div>
                                            </div>
                                            <div class="summary-item">
                                                <div class="summary-label">Class Average</div>
                                                <div class="summary-value"><?= $studentCount > 0 ? number_format($classTotal / $studentCount, 2) : '0.00' ?></div>
                                            </div>
                                            <div class="summary-item">
                                                <div class="summary-label">Highest</div>
                                                <div class="summary-value"><?= !empty($highLow) ? number_format(max($highLow), 2) : '0.00' ?></div>
                                            </div>
                                            <div class="summary-item">
                                                <div class="summary-label">Lowest</div>
                                                <div class="summary-value"><?= !empty($highLow) ? number_format(min($highLow), 2) : '0.00' ?></div>
                                            </div>
                                        </div>

                                        <!-- BUTTONS -->
                                        <div class="btn-group">
                                            <a href="<?= SKOOL_URL ?>cummulative_broad_sheet_pfd.php?randomid=<?= urlencode($get_randomid) ?>&session=<?= urlencode($post_session) ?>&term_id=<?= urlencode($post_term_id) ?>"
                                                class="btn btn-danger" target="_blank">
                                                <i class="fa fa-file-pdf-o"></i> Print Cumulative Broad Sheet
                                            </a>
                                            <button class="btn btn-success" onclick="window.print()">
                                                <i class="fa fa-print"></i> Print Report
                                            </button>
                                        </div>

                                    <?php else: ?>
                                        <div class="empty-state">
                                            <i class="fa fa-users"></i>
                                            <h3>No Students Found</h3>
                                            <p>No students enrolled in this class for the selected session and term.</p>
                                        </div>
                                    <?php endif; ?>

                                <?php else: ?>
                                    <div class="empty-state">
                                        <i class="fa fa-filter"></i>
                                        <h3>Select Session and Term</h3>
                                        <p>Please select a session and term to view the Cumulative Broad Sheet.</p>
                                    </div>
                                <?php endif; ?>

                            <?php else: ?>
                                <div class="empty-state">
                                    <i class="fa fa-graduation-cap"></i>
                                    <h3>Select a Class</h3>
                                    <p>Please select a class from the left sidebar.</p>
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
        // Auto-submit filters on change
        document.addEventListener('DOMContentLoaded', function() {
            var filterForm = document.getElementById('filterForm');
            if (!filterForm) return;

            var selects = filterForm.querySelectorAll('select');
            selects.forEach(function(select) {
                select.addEventListener('change', function() {
                    var session = filterForm.querySelector('select[name="session"]');
                    var term = filterForm.querySelector('select[name="term_id"]');
                    if (session && term && session.value && term.value) {
                        filterForm.submit();
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