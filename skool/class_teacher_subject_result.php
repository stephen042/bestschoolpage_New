<?php
// ============================================================================
// CLASS TEACHER SUBJECT RESULT
// ============================================================================
// Handles subject result management for class teachers
// Version: 4.0 (Fully Mobile Responsive)
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
$sessionUsername = (string)($_SESSION['username'] ?? '');
$sessionEmail = (string)($_SESSION['email'] ?? '');
$isSchoolOwnerSession = ($sessionUserId > 0 && $sessionUserId === $create_by_userid);

$PageTitle = "Class Teacher Subject Result";
$FileName = 'class_teacher_subject_result.php';

// ============================================================================
// GET CLASS AND SUBJECT DETAILS
// ============================================================================
$aryList = [];
$randomid = isset($_GET['randomid']) ? trim($_GET['randomid']) : '';

if (!empty($randomid)) {
    $aryList = db_get_row(
        "SELECT * FROM school_class WHERE randomid = ? AND create_by_userid = ?",
        [$randomid, $create_by_userid]
    );
}

$subjectid = [];
$subjectParam = isset($_GET['subject']) ? trim($_GET['subject']) : '';

if (!empty($subjectParam)) {
    $subjectid = db_get_row(
        "SELECT * FROM school_subject WHERE randomid = ? AND create_by_userid = ?",
        [$subjectParam, $create_by_userid]
    );
}

// ============================================================================
// VALIDATION
// ============================================================================
$validate = new Validation();

// ============================================================================
// SESSION MESSAGES
// ============================================================================
if (!empty($_SESSION['success'])) {
    $stat['success'] = $_SESSION['success'];
    unset($_SESSION['success']);
}

// ============================================================================
// GET TEACHER'S ASSIGNED CLASSES
// ============================================================================
$classList = [];
$assignedClassIds = [];

if ($_SESSION['usertype'] == '1') {
    $staffDetails = db_get_row(
        "SELECT id FROM staff_manage WHERE staff_id = ? OR id = ?",
        [$sessionUsername, $sessionUserId]
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

        if (!empty($assignedClassIds)) {
            $classIds = implode(',', array_map('intval', $assignedClassIds));
            $classList = db_get_rows(
                "SELECT * FROM school_class WHERE create_by_userid = ? AND id IN ($classIds) ORDER BY name ASC",
                [$create_by_userid]
            );
        }
    }
} else {
    $classList = db_get_rows(
        "SELECT * FROM school_class WHERE create_by_userid = ? ORDER BY name ASC",
        [$create_by_userid]
    );
}

// ============================================================================
// GET SESSIONS, TERMS, AND SUBJECTS FOR FORMS
// ============================================================================
$sessionList = db_get_rows(
    "SELECT * FROM school_session WHERE create_by_userid = ? ORDER BY id DESC",
    [$create_by_userid]
);

$termList = db_get_rows(
    "SELECT * FROM school_term WHERE create_by_userid = ? ORDER BY id ASC",
    [$create_by_userid]
);

// Get subjects for the selected class
$subjectList = [];
if (!empty($aryList) && isset($aryList['id'])) {
    $subjectList = db_get_rows(
        "SELECT * FROM school_subject WHERE class_id = ? AND create_by_userid = ? ORDER BY subject ASC",
        [(int)$aryList['id'], $create_by_userid]
    );
}

// Get assessments for the selected class
$assessmentList = [];
if (!empty($aryList) && isset($aryList['id'])) {
    $assessmentList = db_get_rows(
        "SELECT * FROM school_assessment WHERE create_by_userid = ? AND (class_id = ? OR class_id IS NULL OR class_id = 0) ORDER BY id ASC",
        [$create_by_userid, (int)$aryList['id']]
    );
}

// ============================================================================
// GET STUDENTS AND SCORES FOR DISPLAY
// ============================================================================
$students = [];
$scoreData = [];
$grandTotal = 0;
$classTotal = 0;
$highLow = [];
$studentScores = [];

if (isset($_GET['action']) && $_GET['action'] == 'input_score' && !empty($aryList)) {
    $postSession = isset($_POST['session']) ? (int)$_POST['session'] : 0;
    $postTerm = isset($_POST['term_id']) ? (int)$_POST['term_id'] : 0;
    $postSubject = isset($_POST['subject']) ? (int)$_POST['subject'] : 0;
    $postAssessments = isset($_POST['assesment']) ? array_map('intval', $_POST['assesment']) : [];

    if ($postSession > 0 && $postTerm > 0 && $postSubject > 0 && !empty($postAssessments)) {
        $students = db_get_rows(
            "SELECT * FROM manage_student 
             WHERE class = ? AND session = ? AND term_id = ? AND create_by_userid = ? 
             ORDER BY first_name ASC",
            [(int)$aryList['id'], $postSession, $postTerm, $create_by_userid]
        );

        foreach ($students as $student) {
            $studentId = (int)$student['id'];
            $totalScore = 0;
            $scores = [];

            foreach ($postAssessments as $assessmentId) {
                $score = db_get_row(
                    "SELECT score FROM input_score_class_teacher 
                     WHERE assesment_id = ? AND student_id = ? AND subject_id = ? AND create_by_userid = ?",
                    [$assessmentId, $studentId, $postSubject, $create_by_userid]
                );

                $scoreValue = isset($score['score']) ? (float)$score['score'] : 0;
                $scores[$assessmentId] = $scoreValue;
                $totalScore += $scoreValue;
            }

            $scoreData[$studentId] = [
                'student' => $student,
                'scores' => $scores,
                'total' => $totalScore
            ];

            $studentScores[$studentId] = $totalScore;
            $classTotal += $totalScore;
            $highLow[] = $totalScore;
        }
    }
}

// ============================================================================
// HELPER FUNCTION FOR RANKING
// ============================================================================
if (!function_exists('setPosition')) {
    function setPosition($standings)
    {
        $rankings = array();
        arsort($standings);
        $rank = 1;
        $tie_rank = 0;
        $prev_score = -1;
        $count = 0;

        foreach ($standings as $name => $score) {
            if ($score != $prev_score) {
                $count = 0;
                $prev_score = $score;
                $rankings[$name] = array('score' => $score, 'rank' => $rank);
            } else {
                $prev_score = $score;
                if ($count++ == 0) {
                    $tie_rank = $rank - 1;
                }
                $rankings[$name] = array('score' => $score, 'rank' => $tie_rank);
            }
            $rank++;
        }
        return $rankings;
    }
}

$rankedScores = [];
if (!empty($studentScores)) {
    $rankedScores = setPosition($studentScores);
}

// Get selected values for form
$selectedSession = isset($_POST['session']) ? (int)$_POST['session'] : 0;
$selectedTerm = isset($_POST['term_id']) ? (int)$_POST['term_id'] : 0;
$selectedSubject = isset($_POST['subject']) ? (int)$_POST['subject'] : 0;
$selectedAssessments = isset($_POST['assesment']) ? array_map('intval', $_POST['assesment']) : [];

// Get class name for display
$className = $aryList['name'] ?? 'Select a Class';
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

        .result-container {
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
        ASSESSMENT CHECKBOXES - MOBILE FIRST
        ============================================================ */
        .assessment-group {
            margin-top: 8px;
            padding-top: 12px;
            border-top: 1px solid #f0f0f0;
        }

        .assessment-group .assessment-label {
            font-size: 11px;
            font-weight: 700;
            color: #888;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: block;
            margin-bottom: 8px;
        }

        .assessment-group .checkbox-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .assessment-group .checkbox-item {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 13px;
            color: #333;
            cursor: pointer;
            padding: 4px 10px;
            background: #f8f9fa;
            border-radius: 20px;
            border: 1px solid #e0e0e0;
            transition: all 0.2s;
        }

        .assessment-group .checkbox-item:active {
            transform: scale(0.97);
        }

        .assessment-group .checkbox-item input[type="checkbox"] {
            width: 16px;
            height: 16px;
            cursor: pointer;
            accent-color: #1B3058;
        }

        .assessment-group .checkbox-item .ass-percent {
            font-size: 10px;
            color: #999;
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

        .btn-primary:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
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

        .btn-info {
            background: #17a2b8;
            color: white;
        }

        .btn-info:hover {
            background: #138496;
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

        .btn-sm {
            padding: 6px 12px;
            font-size: 12px;
            min-height: 32px;
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
            min-width: 600px;
            border-collapse: collapse;
            font-size: 12px;
        }

        .result-table th,
        .result-table td {
            padding: 8px 6px;
            text-align: center;
            border: 1px solid #e0e0e0;
            vertical-align: middle;
        }

        .result-table th {
            background: #f8f9fa;
            font-weight: 700;
            color: #1B3058;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            position: sticky;
            top: 0;
            z-index: 2;
        }

        .result-table th small {
            font-weight: 400;
            opacity: 0.7;
            font-size: 9px;
        }

        .result-table td {
            font-size: 12px;
        }

        .result-table .student-name-cell {
            text-align: left;
            font-weight: 600;
            font-size: 13px;
        }

        .result-table .student-id-cell {
            color: #999;
            font-size: 11px;
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

        .result-table .position-cell {
            font-weight: 700;
            background: #fff3e0;
            color: #e65100;
        }

        .result-table .rank-1 {
            background: #fff8e1;
        }

        .result-table .rank-2 {
            background: #f5f5f5;
        }

        .result-table .rank-3 {
            background: #fafafa;
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
            .result-container {
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

            .result-table {
                font-size: 13px;
                min-width: auto;
            }

            .result-table th,
            .result-table td {
                padding: 10px 12px;
            }

            .result-table th {
                font-size: 11px;
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

            .btn-group {
                flex-direction: row;
                flex-wrap: wrap;
            }

            .btn-group .btn {
                width: auto;
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
            .result-container {
                padding: 30px;
            }

            .class-panel {
                width: 320px;
            }

            .result-table th,
            .result-table td {
                padding: 12px 16px;
            }
        }

        /* ============================================================
        RESPONSIVE - SMALL MOBILE (480px-)
        ============================================================ */
        @media (max-width: 480px) {
            .result-container {
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
                min-width: 480px;
            }

            .result-table th,
            .result-table td {
                padding: 5px 3px;
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

            .assessment-group .checkbox-item {
                font-size: 11px;
                padding: 3px 8px;
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

            .result-container {
                padding: 0;
            }

            .result-table th {
                background: #f8f9fa !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .result-table .total-cell,
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
        }
    </style>
</head>

<body>
    <div id="wrapper">
        <?php include('inc.header.php'); ?>
        <?php include('inc.sideleft.php'); ?>
        <div class="content-page">
            <div class="content">
                <div class="result-container">

                    <!-- Page Header -->
                    <div class="page-header">
                        <h2><i class="fa fa-bar-chart"></i> <?= htmlspecialchars($PageTitle) ?></h2>
                        <p>Select a class, session, term, subject, and assessments to view results</p>
                    </div>

                    <?= showMessage($stat) ?>

                    <div class="layout">

                        <!-- LEFT: Class List -->
                        <div class="class-panel">
                            <div class="panel-header">
                                <i class="fa fa-graduation-cap"></i> Select Class
                                <span class="count-badge"><?= count($classList) ?></span>
                            </div>
                            <div class="class-list">
                                <?php if (!empty($classList)): ?>
                                    <?php foreach ($classList as $iList): ?>
                                        <a href="<?= $FileName ?>?action=input_score&randomid=<?= e($iList['randomid']) ?>"
                                            class="class-item <?= (isset($_GET['randomid']) && $_GET['randomid'] == $iList['randomid']) ? 'active' : '' ?>">
                                            <div class="class-icon">
                                                <i class="fa fa-book"></i>
                                            </div>
                                            <div class="class-name"><?= e($iList['name']) ?></div>
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

                            <?php if (!empty($aryList)): ?>

                                <!-- Filter Form -->
                                <div class="filter-card">
                                    <form method="POST" action="" id="filterForm">
                                        <input type="hidden" name="action" value="input_score">
                                        <input type="hidden" name="randomid" value="<?= e($_GET['randomid'] ?? '') ?>">
                                        <input type="hidden" name="new_randomid" value="<?= isset($iList['randomid']) ? e($iList['randomid']) : '' ?>">

                                        <div class="filter-grid">
                                            <div class="filter-group">
                                                <label><i class="fa fa-calendar"></i> Session</label>
                                                <select name="session" class="filter-select" required>
                                                    <option value="">-- Select --</option>
                                                    <?php foreach ($sessionList as $sList): ?>
                                                        <option value="<?= (int)$sList['id'] ?>" <?= ($selectedSession == $sList['id']) ? 'selected' : '' ?>>
                                                            <?= e($sList['session']) ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="filter-group">
                                                <label><i class="fa fa-tag"></i> Term</label>
                                                <select name="term_id" class="filter-select" required>
                                                    <option value="">-- Select --</option>
                                                    <?php foreach ($termList as $tList): ?>
                                                        <option value="<?= (int)$tList['id'] ?>" <?= ($selectedTerm == $tList['id']) ? 'selected' : '' ?>>
                                                            <?= e($tList['term']) ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="filter-group">
                                                <label><i class="fa fa-book"></i> Subject</label>
                                                <select name="subject" class="filter-select" required>
                                                    <option value="">-- Select --</option>
                                                    <?php foreach ($subjectList as $subList): ?>
                                                        <option value="<?= (int)$subList['id'] ?>" <?= ($selectedSubject == $subList['id']) ? 'selected' : '' ?>>
                                                            <?= e($subList['subject']) ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        </div>

                                        <!-- Assessments -->
                                        <?php if (!empty($assessmentList)): ?>
                                            <div class="assessment-group">
                                                <span class="assessment-label"><i class="fa fa-check-square-o"></i> Select Assessments</span>
                                                <div class="checkbox-grid">
                                                    <?php foreach ($assessmentList as $assList): ?>
                                                        <label class="checkbox-item">
                                                            <input type="checkbox" name="assesment[]" value="<?= (int)$assList['id'] ?>"
                                                                <?= in_array($assList['id'], $selectedAssessments) ? 'checked' : '' ?>>
                                                            <?= e($assList['assesment'] ?? '') ?>
                                                            <span class="ass-percent">(<?= floatval($assList['percentage'] ?? 0) ?>%)</span>
                                                        </label>
                                                    <?php endforeach; ?>
                                                </div>
                                            </div>
                                        <?php endif; ?>

                                        <div class="filter-actions" style="margin-top: 12px;">
                                            <button type="submit" class="btn btn-primary btn-block">
                                                <i class="fa fa-search"></i> View Results
                                            </button>
                                            <button type="reset" class="btn btn-outline btn-block" onclick="resetFilters()">
                                                <i class="fa fa-refresh"></i> Reset
                                            </button>
                                        </div>
                                    </form>

                                    <!-- Print Buttons -->
                                    <div class="btn-group">
                                        <?php if ($selectedSubject == 0): ?>
                                            <button type="button" class="btn btn-info" onclick="alert('Please select a subject first')">
                                                <i class="fa fa-file-pdf-o"></i> Print Subject Result
                                            </button>
                                            <button type="button" class="btn btn-info" onclick="alert('Please select a subject first')">
                                                <i class="fa fa-file-pdf-o"></i> Print Empty Score Sheet
                                            </button>
                                        <?php else: ?>
                                            <a href="<?= SKOOL_URL ?>class_teacher_subject_result_pdf.php?randomid=<?= isset($_GET['randomid']) ? e($_GET['randomid']) : '' ?>&subject=<?= $selectedSubject ?>&session=<?= $selectedSession ?>&term_id=<?= $selectedTerm ?>&assesments=<?= implode('-', $selectedAssessments) ?>"
                                                class="btn btn-danger" target="_blank">
                                                <i class="fa fa-file-pdf-o"></i> Print Subject Result
                                            </a>
                                            <a href="<?= SKOOL_URL ?>class_teacher_subject_empty_result_pdf.php?randomid=<?= isset($_GET['randomid']) ? e($_GET['randomid']) : '' ?>&subject=<?= $selectedSubject ?>&session=<?= $selectedSession ?>&term_id=<?= $selectedTerm ?>&assesments=<?= implode('-', $selectedAssessments) ?>"
                                                class="btn btn-info" target="_blank">
                                                <i class="fa fa-file-pdf-o"></i> Print Empty Score Sheet
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <!-- Results Display -->
                                <?php if (!empty($scoreData)): ?>
                                    <div class="result-card">
                                        <div class="card-header">
                                            <div class="title">
                                                <i class="fa fa-table"></i>
                                                <?= e($className) ?> - Subject Results
                                            </div>
                                            <div class="subtitle">
                                                <i class="fa fa-info-circle"></i>
                                                <?= count($students) ?> student(s)
                                            </div>
                                        </div>
                                        <div class="card-body">
                                            <table class="result-table" id="resultTable">
                                                <thead>
                                                    <tr>
                                                        <th style="min-width:120px;">Student</th>
                                                        <th style="min-width:60px;">ID</th>
                                                        <?php foreach ($selectedAssessments as $assId):
                                                            $assDetail = db_get_row(
                                                                "SELECT assesment, percentage FROM school_assessment WHERE id = ?",
                                                                [$assId]
                                                            );
                                                        ?>
                                                            <th><?= e($assDetail['assesment'] ?? 'Ass') ?><br><small>(<?= e($assDetail['percentage'] ?? 0) ?>%)</small></th>
                                                        <?php endforeach; ?>
                                                        <th>Total</th>
                                                        <th>Grade</th>
                                                        <th>Position</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php
                                                    $tStudent = 0;
                                                    $classTotal = 0;
                                                    $highLow = [];
                                                    $studentTotals = [];

                                                    foreach ($scoreData as $studentId => $data):
                                                        $tStudent++;
                                                        $student = $data['student'];
                                                        $totalScore = $data['total'];
                                                        $studentTotals[$studentId] = $totalScore;
                                                        $classTotal += $totalScore;
                                                        $highLow[] = $totalScore;
                                                    ?>
                                                        <tr id="row-<?= $studentId ?>">
                                                            <td class="student-name-cell"><?= e($student['first_name'] ?? '') . ' ' . e($student['last_name'] ?? '') ?></td>
                                                            <td class="student-id-cell"><?= e($student['student_id'] ?? '') ?></td>
                                                            <?php foreach ($selectedAssessments as $assId): ?>
                                                                <td><?= isset($data['scores'][$assId]) ? number_format($data['scores'][$assId], 2) : '0.00' ?></td>
                                                            <?php endforeach; ?>
                                                            <td class="total-cell"><?= number_format($totalScore, 2) ?></td>
                                                            <td class="grade-cell">
                                                                <?php
                                                                $grade = db_get_val(
                                                                    "SELECT grade FROM school_grade 
                                                             WHERE create_by_userid = ? 
                                                             AND minimum_number <= ? 
                                                             AND maximum_number >= ?",
                                                                    [$create_by_userid, $totalScore, $totalScore]
                                                                );
                                                                echo e($grade ?: 'N/A');
                                                                ?>
                                                            </td>
                                                            <td class="position-cell" id="rank_<?= $studentId ?>">
                                                                <span class="position-badge">--</span>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>

                                            <!-- Rankings Script -->
                                            <script>
                                                <?php if (!empty($studentTotals)): ?>
                                                    <?php
                                                    $rankedScores = setPosition($studentTotals);
                                                    foreach ($rankedScores as $studentId => $data):
                                                        $rank = $data['rank'];
                                                        $suffix = 'th';
                                                        $rankClass = '';
                                                        if ($rank == 1) {
                                                            $suffix = 'st';
                                                            $rankClass = 'gold';
                                                        } elseif ($rank == 2) {
                                                            $suffix = 'nd';
                                                            $rankClass = 'silver';
                                                        } elseif ($rank == 3) {
                                                            $suffix = 'rd';
                                                            $rankClass = 'bronze';
                                                        }
                                                    ?>
                                                        document.getElementById('rank_<?= $studentId ?>').innerHTML =
                                                            '<span class="position-badge <?= $rankClass ?>"><?= $rank . $suffix ?></span>';
                                                    <?php endforeach; ?>
                                                <?php endif; ?>
                                            </script>
                                        </div>

                                        <!-- Summary Stats -->
                                        <div class="summary-stats">
                                            <div class="summary-stat">
                                                <div class="number"><?= $tStudent ?></div>
                                                <div class="label">Students</div>
                                            </div>
                                            <div class="summary-stat">
                                                <div class="number"><?= $tStudent > 0 ? round($classTotal / $tStudent, 2) : '0.00' ?></div>
                                                <div class="label">Class Average</div>
                                            </div>
                                            <div class="summary-stat">
                                                <div class="number"><?= !empty($highLow) ? round(max($highLow), 2) : '0.00' ?></div>
                                                <div class="label">Highest Score</div>
                                            </div>
                                            <div class="summary-stat">
                                                <div class="number"><?= !empty($highLow) ? round(min($highLow), 2) : '0.00' ?></div>
                                                <div class="label">Lowest Score</div>
                                            </div>
                                        </div>
                                    </div>
                                <?php elseif (isset($_POST['session']) && isset($_POST['term_id']) && isset($_POST['subject']) && !empty($_POST['assesment'])): ?>
                                    <div class="alert alert-warning">
                                        <i class="fa fa-exclamation-triangle"></i>
                                        <div>
                                            No results found for the selected criteria.
                                            <br><small>Please ensure that scores have been entered for this subject.</small>
                                        </div>
                                    </div>
                                <?php endif; ?>

                            <?php else: ?>
                                <div class="alert alert-info">
                                    <i class="fa fa-info-circle"></i>
                                    <div>Please select a class from the left panel to view results.</div>
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
        function resetFilters() {
            var form = document.getElementById('filterForm');
            if (!form) return;
            var selects = form.querySelectorAll('select');
            selects.forEach(function(select) {
                select.selectedIndex = 0;
            });
            var checkboxes = form.querySelectorAll('input[type="checkbox"]');
            checkboxes.forEach(function(checkbox) {
                checkbox.checked = false;
            });
            form.submit();
        }

        // Auto-submit when filters change (for better UX)
        document.addEventListener('DOMContentLoaded', function() {
            var form = document.getElementById('filterForm');
            if (!form) return;

            var selects = form.querySelectorAll('select');
            selects.forEach(function(select) {
                select.addEventListener('change', function() {
                    // Check if all required fields are filled
                    var session = form.querySelector('select[name="session"]');
                    var term = form.querySelector('select[name="term_id"]');
                    var subject = form.querySelector('select[name="subject"]');
                    var assessments = form.querySelectorAll('input[type="checkbox"]:checked');

                    if (session && session.value && term && term.value && subject && subject.value && assessments.length > 0) {
                        form.submit();
                    }
                });
            });

            var checkboxes = form.querySelectorAll('input[type="checkbox"]');
            checkboxes.forEach(function(checkbox) {
                checkbox.addEventListener('change', function() {
                    var session = form.querySelector('select[name="session"]');
                    var term = form.querySelector('select[name="term_id"]');
                    var subject = form.querySelector('select[name="subject"]');
                    var assessments = form.querySelectorAll('input[type="checkbox"]:checked');

                    if (session && session.value && term && term.value && subject && subject.value && assessments.length > 0) {
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
                    this.classList.add('active');
                });
            });
        });
    </script>
</body>

</html>