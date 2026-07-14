<?php

/**
 * ============================================================================
 * VIEW RESULT STUDENT CUMULATIVE RESULT - MODERN REDESIGN
 * ============================================================================
 * Description: View cumulative results for individual students across terms
 * Version: 4.0 (Fully Mobile Responsive)
 * ============================================================================
 */

include('../config.php');
include('inc.session-create.php');

$pageTitle = 'Student Cumulative Result';
$FileName = 'view_result_student_cumulative_result.php';

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
// GET STUDENT DETAILS
// ============================================================================
$student = db_get_row(
    "SELECT * FROM manage_student WHERE randomid = ? AND create_by_userid = ?",
    [$_GET['randomid'] ?? '', $create_by_userid]
);

// Get class details
$classDetail = [];
if (!empty($student['class'])) {
    $classDetail = db_get_row(
        "SELECT * FROM school_class WHERE id = ? AND create_by_userid = ?",
        [$student['class'], $create_by_userid]
    );
}

// Get all assessments for this class
$iAssesment = db_get_val(
    "SELECT GROUP_CONCAT(id) FROM school_assessment WHERE (class_id = ? OR class_id IS NULL OR class_id = 0) AND create_by_userid = ?",
    [$student['class'] ?? 0, $create_by_userid]
);

// ============================================================================
// GET SESSIONS AND TERMS
// ============================================================================
$sessions = db_get_rows(
    "SELECT * FROM school_session WHERE create_by_userid = ? ORDER BY id DESC",
    [$create_by_userid]
);

$terms = db_get_rows(
    "SELECT * FROM school_term WHERE create_by_userid = ? ORDER BY id ASC",
    [$create_by_userid]
);

// ============================================================================
// GET STUDENTS FOR SIDEBAR
// ============================================================================
$selectedSession = isset($_GET['session']) ? (int)$_GET['session'] : 0;

$allStudents = [];
$totalStudent = 0;

if ($selectedSession > 0) {
    $allStudents = db_get_rows(
        "SELECT DISTINCT student_id, first_name, last_name, picture, randomid, class 
         FROM manage_student 
         WHERE session = ? AND create_by_userid = ? 
         ORDER BY first_name ASC",
        [$selectedSession, $create_by_userid]
    );
    $totalStudent = count($allStudents);
}

// ============================================================================
// GET SUBJECTS AND SCORES FOR THE STUDENT
// ============================================================================
$subjects = [];
$studentScores = [];
$subjectTotals = [];
$subjectAverages = [];
$subjectGrades = [];
$classAverages = [];
$totalSubjects = 0;
$highLow = [];

if (!empty($student['class']) && !empty($selectedSession) && !empty($terms)) {
    // Get subjects for this class
    $subjects = db_get_rows(
        "SELECT * FROM school_subject WHERE class_id = ? AND create_by_userid = ? ORDER BY subject ASC",
        [$student['class'], $create_by_userid]
    );
    $totalSubjects = count($subjects);

    // Get term student IDs
    $termStudents = [];
    foreach ($terms as $term) {
        $termStudent = db_get_row(
            "SELECT id FROM manage_student 
             WHERE student_id = ? AND session = ? AND term_id = ? AND class = ? AND create_by_userid = ?",
            [$student['student_id'], $selectedSession, $term['id'], $student['class'], $create_by_userid]
        );
        if ($termStudent) {
            $termStudents[$term['id']] = $termStudent['id'];
        }
    }

    foreach ($subjects as $subject) {
        $subjectId = $subject['id'];
        $subjectTotal = 0;
        $termCount = 0;
        $subjectScores = [];

        foreach ($terms as $term) {
            $termId = $term['id'];
            if (!isset($termStudents[$termId])) {
                $subjectScores[$termId] = 0;
                continue;
            }
            $studentId = $termStudents[$termId];

            $score = db_get_val(
                "SELECT SUM(score) FROM input_score_class_teacher 
                 WHERE student_id = ? AND subject_id = ? AND session_id = ? AND term_id = ? AND create_by_userid = ?",
                [$studentId, $subjectId, $selectedSession, $termId, $create_by_userid]
            );
            $scoreValue = $score !== false ? (float)$score : 0;
            $subjectScores[$termId] = $scoreValue;
            $subjectTotal += $scoreValue;
            $termCount++;
        }

        $studentScores[$subjectId] = $subjectScores;
        $subjectTotals[$subjectId] = $subjectTotal;
        $avg = $termCount > 0 ? round($subjectTotal / $termCount, 2) : 0;
        $subjectAverages[$subjectId] = $avg;
        $highLow[] = $avg;

        // Get grade
        $grade = db_get_val(
            "SELECT grade FROM school_grade 
             WHERE create_by_userid = ? 
             AND minimum_number <= ? 
             AND maximum_number >= ?",
            [$create_by_userid, $avg, $avg]
        );
        $subjectGrades[$subjectId] = $grade ?: 'N/A';

        // Get class average for this subject
        $classAvg = db_get_val(
            "SELECT AVG(score) FROM input_score_class_teacher 
             WHERE assesment_id IN ($iAssesment) 
             AND session_id = ? AND class_id = ? AND subject_id = ? AND create_by_userid = ?",
            [$selectedSession, $student['class'], $subjectId, $create_by_userid]
        );
        $classAverages[$subjectId] = $classAvg !== false ? round((float)$classAvg, 2) : 0;
    }
}

// Calculate overall averages
$overallAverage = $totalSubjects > 0 ? round(array_sum($subjectAverages) / $totalSubjects, 2) : 0;
$overallGrade = db_get_val(
    "SELECT grade FROM school_grade 
     WHERE create_by_userid = ? 
     AND minimum_number <= ? 
     AND maximum_number >= ?",
    [$create_by_userid, $overallAverage, $overallAverage]
);
$overallGrade = $overallGrade ?: 'N/A';

$assessmentIds = [1, 2, 3]; // example

$placeholders = implode(',', array_fill(0, count($assessmentIds), '?'));

$sql = "
SELECT AVG(score)
FROM input_score_class_teacher
WHERE assesment_id IN ($placeholders)
AND session_id = ?
AND class_id = ?
AND create_by_userid = ?
";

$params = array_merge(
    $assessmentIds,
    [
        $selectedSession,
        $student['class'] ?? 0,
        $create_by_userid
    ]
);

$classOverallAvg = db_get_val($sql, $params);

$highestAvg = !empty($highLow) ? round(max($highLow), 2 ?? 0) : 0;
$lowestAvg = !empty($highLow) ? round(min($highLow), 2 ?? 0) : 0;
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
        STUDENT LIST PANEL - MOBILE FIRST
        ============================================================ */
        .student-panel {
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

        .student-list {
            max-height: 300px;
            overflow-y: auto;
            -webkit-overflow-scrolling: touch;
        }

        .student-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 14px;
            border-bottom: 1px solid #f0f0f0;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
            color: #333;
        }

        .student-item:active {
            background: #e8eef5;
        }

        .student-item:hover {
            background: #f8f9ff;
        }

        .student-item.active {
            background: #e8eef5;
            border-left: 4px solid #1B3058;
        }

        .student-item .student-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            overflow: hidden;
            background: #e8eef5;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #1B3058;
            flex-shrink: 0;
            font-size: 16px;
        }

        .student-item .student-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .student-item .student-name {
            font-weight: 600;
            font-size: 13px;
        }

        .student-item .student-meta {
            font-size: 10px;
            color: #999;
            margin-top: 1px;
        }

        .student-item .student-arrow {
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
        STUDENT PROFILE - MOBILE FIRST
        ============================================================ */
        .student-profile {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 12px 16px;
            background: #f8f9fa;
            border-bottom: 1px solid #eee;
        }

        .student-profile .avatar-lg {
            width: 56px;
            height: 56px;
            border-radius: 50%;
            overflow: hidden;
            background: #e8eef5;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            color: #1B3058;
            flex-shrink: 0;
            border: 2px solid #1B3058;
        }

        .student-profile .avatar-lg img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .student-profile .student-info h4 {
            font-size: 16px;
            margin: 0;
            color: #1B3058;
        }

        .student-profile .student-info p {
            font-size: 12px;
            margin: 2px 0 0;
            color: #666;
        }

        /* ============================================================
        RESULT TABLE - MOBILE FIRST
        ============================================================ */
        .result-table {
            width: 100%;
            min-width: 600px;
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

        .result-table .subject-cell {
            text-align: left;
            font-weight: 600;
            font-size: 12px;
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

        .result-table .class-avg-cell {
            background: #f3e5f5;
            color: #6a1b9a;
            font-weight: 600;
        }

        /* ============================================================
        OVERALL SUMMARY - MOBILE FIRST
        ============================================================ */
        .overall-summary {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
            padding: 12px 8px;
            background: #f8f9fa;
            border-top: 1px solid #eee;
        }

        .overall-summary .summary-item {
            text-align: center;
            padding: 8px 4px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 1px 4px rgba(0, 0, 0, 0.05);
        }

        .overall-summary .summary-item .number {
            font-size: 18px;
            font-weight: 700;
            color: #1B3058;
        }

        .overall-summary .summary-item .label {
            font-size: 9px;
            color: #888;
            text-transform: uppercase;
            font-weight: 600;
            margin-top: 2px;
        }

        .overall-summary .summary-item .grade-badge {
            display: inline-block;
            padding: 2px 14px;
            border-radius: 20px;
            font-size: 16px;
            font-weight: 700;
            background: #e3f2fd;
            color: #0d47a1;
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
            .cumulative-container {
                padding: 25px;
            }

            .layout {
                flex-direction: row;
                gap: 25px;
            }

            .student-panel {
                width: 300px;
                flex-shrink: 0;
            }

            .student-list {
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

            .result-table .subject-cell {
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

            .student-profile {
                padding: 16px 24px;
            }

            .student-profile .avatar-lg {
                width: 64px;
                height: 64px;
            }

            .student-profile .student-info h4 {
                font-size: 18px;
            }

            .overall-summary {
                grid-template-columns: repeat(4, 1fr);
                gap: 12px;
                padding: 16px 20px;
            }

            .overall-summary .summary-item .number {
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

            .student-panel {
                width: 340px;
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
                min-width: 500px;
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

            .result-table .subject-cell {
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

            .student-profile {
                padding: 10px 12px;
                gap: 10px;
            }

            .student-profile .avatar-lg {
                width: 44px;
                height: 44px;
                font-size: 18px;
            }

            .student-profile .student-info h4 {
                font-size: 14px;
            }

            .student-profile .student-info p {
                font-size: 11px;
            }

            .overall-summary {
                grid-template-columns: 1fr 1fr;
                gap: 6px;
                padding: 10px 6px;
            }

            .overall-summary .summary-item .number {
                font-size: 15px;
            }

            .overall-summary .summary-item .label {
                font-size: 8px;
            }

            .overall-summary .summary-item .grade-badge {
                font-size: 13px;
                padding: 1px 10px;
            }
        }

        /* ============================================================
        PRINT STYLES
        ============================================================ */
        @media print {

            .student-panel,
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

            .student-profile {
                border: 1px solid #ddd;
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
            .result-table .class-avg-cell {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .overall-summary {
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
                        <h2><i class="fa fa-bar-chart"></i> <?= htmlspecialchars($pageTitle) ?></h2>
                        <p>View cumulative results for individual students across all terms</p>
                    </div>

                    <?= showMessage($stat) ?>

                    <div class="layout">

                        <!-- LEFT: Student List -->
                        <div class="student-panel">
                            <div class="panel-header">
                                <i class="fa fa-users"></i> Students
                                <span class="count-badge"><?= $totalStudent ?></span>
                            </div>

                            <!-- Filter -->
                            <div style="padding: 12px 14px; border-bottom: 1px solid #f0f0f0;">
                                <form method="GET" action="" id="filterForm">
                                    <div class="filter-group" style="width:100%;">
                                        <label><i class="fa fa-calendar"></i> Session</label>
                                        <select name="session" class="filter-select" required onchange="this.form.submit()">
                                            <option value="">-- Select --</option>
                                            <?php foreach ($sessions as $s): ?>
                                                <option value="<?= $s['id'] ?>" <?= ($selectedSession == $s['id']) ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($s['session']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <input type="hidden" name="randomid" value="<?= htmlspecialchars($_GET['randomid'] ?? '') ?>">
                                    <input type="hidden" name="action" value="input_score">
                                </form>
                            </div>

                            <div class="student-list">
                                <?php if (!empty($allStudents)): ?>
                                    <?php foreach ($allStudents as $studentItem): ?>
                                        <a href="?action=input_score&randomid=<?= urlencode($studentItem['randomid']) ?>&session=<?= $selectedSession ?>"
                                            class="student-item <?= (isset($_GET['randomid']) && $_GET['randomid'] == $studentItem['randomid']) ? 'active' : '' ?>">
                                            <div class="student-avatar">
                                                <?php if (!empty($studentItem['picture']) && file_exists('../uploads/' . $studentItem['picture'])): ?>
                                                    <img src="../uploads/<?= htmlspecialchars($studentItem['picture']) ?>">
                                                <?php else: ?>
                                                    <i class="fa fa-user"></i>
                                                <?php endif; ?>
                                            </div>
                                            <div>
                                                <div class="student-name"><?= htmlspecialchars($studentItem['first_name'] . ' ' . $studentItem['last_name']) ?></div>
                                                <div class="student-meta">ID: <?= htmlspecialchars($studentItem['student_id']) ?></div>
                                            </div>
                                            <div class="student-arrow"><i class="fa fa-chevron-right"></i></div>
                                        </a>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="empty-state" style="padding: 30px 20px;">
                                        <i class="fa fa-user-slash"></i>
                                        <?php if ($selectedSession > 0): ?>
                                            <p>No students found for this session</p>
                                        <?php else: ?>
                                            <p>Please select a session to view students</p>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- RIGHT: Main Content -->
                        <div class="main-panel">

                            <?php if (!empty($student) && !empty($classDetail) && !empty($selectedSession) && !empty($terms)): ?>

                                <!-- Print Button -->
                                <div class="btn-group">
                                    <a href="<?= SKOOL_URL ?>view_result_student_cum_result_pdf.php?randomid=<?= urlencode($_GET['randomid'] ?? '') ?>&session=<?= $selectedSession ?>&outof=<?= $totalStudent ?>"
                                        class="btn btn-danger" target="_blank">
                                        <i class="fa fa-file-pdf-o"></i> Print Result Sheet
                                    </a>
                                </div>

                                <!-- Result Card -->
                                <div class="result-card">
                                    <div class="card-header">
                                        <div class="title">
                                            <i class="fa fa-table"></i> Cumulative Result
                                        </div>
                                        <div class="subtitle">
                                            <i class="fa fa-info-circle"></i>
                                            <?= $totalSubjects ?> subject(s)
                                        </div>
                                    </div>

                                    <!-- Student Profile -->
                                    <div class="student-profile">
                                        <div class="avatar-lg">
                                            <?php if (!empty($student['picture']) && file_exists('../uploads/' . $student['picture'])): ?>
                                                <img src="../uploads/<?= htmlspecialchars($student['picture']) ?>">
                                            <?php else: ?>
                                                <i class="fa fa-user"></i>
                                            <?php endif; ?>
                                        </div>
                                        <div class="student-info">
                                            <h4><?= htmlspecialchars($student['first_name'] . ' ' . $student['last_name']) ?></h4>
                                            <p>
                                                <strong>ID:</strong> <?= htmlspecialchars($student['student_id']) ?> &bull;
                                                <strong>Class:</strong> <?= htmlspecialchars($classDetail['name'] ?? 'N/A') ?>
                                            </p>
                                        </div>
                                    </div>

                                    <div class="card-body">
                                        <table class="result-table" id="resultTable">
                                            <thead>
                                                <tr>
                                                    <th style="min-width:80px;">Subject</th>
                                                    <?php foreach ($terms as $term): ?>
                                                        <th><?= htmlspecialchars($term['term']) ?></th>
                                                    <?php endforeach; ?>
                                                    <th>Total</th>
                                                    <th>Avg</th>
                                                    <th>Grade</th>
                                                    <th>Out of</th>
                                                    <th>Class Avg.</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($subjects as $subject):
                                                    $subjectId = $subject['id'];
                                                    $totalScore = $subjectTotals[$subjectId] ?? 0;
                                                    $avg = $subjectAverages[$subjectId] ?? 0;
                                                    $grade = $subjectGrades[$subjectId] ?? 'N/A';
                                                    $classAvg = $classAverages[$subjectId] ?? 0;
                                                ?>
                                                    <tr>
                                                        <td class="subject-cell"><?= htmlspecialchars($subject['subject']) ?></td>
                                                        <?php foreach ($terms as $term): ?>
                                                            <td><?= isset($studentScores[$subjectId][$term['id']]) ? number_format($studentScores[$subjectId][$term['id']], 1 ?? 0) : '0.0' ?></td>
                                                        <?php endforeach; ?>
                                                        <td class="total-cell"><strong><?= number_format($totalScore, 1 ?? 0) ?></strong></td>
                                                        <td class="average-cell"><?= number_format($avg, 2 ?? 0) ?></td>
                                                        <td class="grade-cell"><?= htmlspecialchars($grade) ?></td>
                                                        <td><?= $totalStudent ?></td>
                                                        <td class="class-avg-cell"><?= number_format($classAvg, 2 ?? 0) ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>

                                    <!-- Overall Summary -->
                                    <div class="overall-summary">
                                        <div class="summary-item">
                                            <div class="number"><?= $totalSubjects ?></div>
                                            <div class="label">Subjects</div>
                                        </div>
                                        <div class="summary-item">
                                            <div class="number"><?= number_format($overallAverage, 2 ?? 0) ?></div>
                                            <div class="label">Overall Avg</div>
                                        </div>
                                        <div class="summary-item">
                                            <div class="number"><span class="grade-badge"><?= htmlspecialchars($overallGrade) ?></span></div>
                                            <div class="label">Overall Grade</div>
                                        </div>
                                        <div class="summary-item">
                                            <div class="number"><?= $classOverallAvg ?? 0 ?></div>
                                            <div class="label">Class Avg</div>
                                        </div>
                                    </div>
                                </div>

                            <?php elseif (!empty($selectedSession) && empty($student)): ?>
                                <div class="alert alert-info">
                                    <i class="fa fa-info-circle"></i>
                                    <div>Please select a student from the left panel.</div>
                                </div>
                            <?php elseif (empty($selectedSession)): ?>
                                <div class="alert alert-info">
                                    <i class="fa fa-info-circle"></i>
                                    <div>Please select a session to view student results.</div>
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
        // Auto-submit when session changes
        document.addEventListener('DOMContentLoaded', function() {
            var form = document.getElementById('filterForm');
            if (!form) return;

            var selects = form.querySelectorAll('select');
            selects.forEach(function(select) {
                select.addEventListener('change', function() {
                    form.submit();
                });
            });
        });

        // Add active class to student items
        document.addEventListener('DOMContentLoaded', function() {
            var studentItems = document.querySelectorAll('.student-item');
            studentItems.forEach(function(item) {
                item.addEventListener('click', function() {
                    var current = document.querySelector('.student-item.active');
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