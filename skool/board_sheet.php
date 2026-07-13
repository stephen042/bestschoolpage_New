<?php

/**
 * Broad Sheet / Board Sheet - FIXED VERSION
 * Displays students, subjects, assessments, scores, totals, averages, positions
 * Version: 4.0 (Fully Mobile Responsive)
 */

require_once('../config.php');
require_once('inc.session-create.php');

$PageTitle = "Broad Sheet";
$FileName = 'board_sheet.php';

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
$randomid = $_GET['randomid'] ?? '';
$selectedSession = $_GET['session'] ?? '';
$selectedTerm = $_GET['term_id'] ?? '';
$exportMode = ($_GET['export'] ?? '') === 'excel';

// ============================================================================
// GET ALL CLASSES FOR SIDEBAR
// ============================================================================
$allClasses = [];
if ($isSchoolOwnerSession) {
    $allClasses = db_get_rows("SELECT * FROM school_class WHERE create_by_userid = ? ORDER BY name ASC", [$create_by_userid]);
} else {
    $teacherStaffId = db_get_val(
        "SELECT id FROM staff_manage
         WHERE create_by_userid = ?
           AND (staff_id = ? OR email = ? OR id = ?)
         ORDER BY id DESC
         LIMIT 1",
        [$create_by_userid, $sessionUsername, $sessionEmail, $sessionUserId]
    );

    if (!empty($teacherStaffId)) {
        $assignedClassIds = db_get_val(
            "SELECT GROUP_CONCAT(school_class) FROM class_teacher WHERE staff_id = ?",
            [$teacherStaffId]
        );

        if (!empty($assignedClassIds)) {
            $allClasses = db_get_rows(
                "SELECT * FROM school_class WHERE create_by_userid = ? AND id IN ($assignedClassIds) ORDER BY name ASC",
                [$create_by_userid]
            );
        }
    }
}

$sessions = db_get_rows("SELECT * FROM school_session WHERE create_by_userid = ? ORDER BY id DESC", [$create_by_userid]);
$terms = db_get_rows("SELECT * FROM school_term WHERE create_by_userid = ? ORDER BY id DESC", [$create_by_userid]);

// ============================================================================
// GET CLASS DETAILS
// ============================================================================
$classDetail = [];
if (!empty($randomid)) {
    $classDetail = db_get_row("SELECT * FROM school_class WHERE randomid = ? AND create_by_userid = ?", [$randomid, $create_by_userid]);
}

// ============================================================================
// GET ASSESSMENTS FOR THIS CLASS
// ============================================================================
$assessments = [];
if (!empty($classDetail['id'])) {
    $assessments = db_get_rows(
        "SELECT * FROM school_assessment WHERE (class_id = ? OR class_id IS NULL OR class_id = 0) AND create_by_userid = ? ORDER BY id ASC",
        [$classDetail['id'], $create_by_userid]
    );
}
$totalAssessments = count($assessments);

// ============================================================================
// GET SCHOOL DETAILS
// ============================================================================
$schoolDetails = db_get_row("SELECT * FROM school_register WHERE id = ?", [$create_by_userid]);
$state = db_get_row("SELECT * FROM state WHERE id = ?", [$schoolDetails['state'] ?? 0]);
$stateName = $state['title'] ?? '';

// ============================================================================
// VARIABLES
// ============================================================================
$subjects = [];
$students = [];
$studentScores = [];
$studentTotals = [];
$studentAverages = [];
$studentPositions = [];
$totalSubjects = 0;
$sessionName = '';
$termName = '';
$classTotalSum = 0;
$studentCount = 0;

if (!empty($classDetail['id']) && !empty($selectedSession) && !empty($selectedTerm)) {
    $sessionName = db_get_val("SELECT session FROM school_session WHERE id = ?", [$selectedSession]);
    $termName = db_get_val("SELECT term FROM school_term WHERE id = ?", [$selectedTerm]);

    $subjects = db_get_rows("SELECT * FROM school_subject WHERE class_id = ? AND create_by_userid = ? ORDER BY subject ASC", [$classDetail['id'], $create_by_userid]);
    $totalSubjects = count($subjects);

    $students = db_get_rows(
        "SELECT * FROM manage_student WHERE class = ? AND session = ? AND term_id = ? AND create_by_userid = ? ORDER BY first_name ASC",
        [$classDetail['id'], $selectedSession, $selectedTerm, $create_by_userid]
    );
    $studentCount = count($students);

    $allScores = db_get_rows(
        "SELECT student_id, subject_id, assesment_id, score 
         FROM input_score_class_teacher 
         WHERE session_id = ? 
         AND term_id = ? 
         AND class_id = ? 
         AND create_by_userid = ?",
        [$selectedSession, $selectedTerm, $classDetail['id'], $create_by_userid]
    );

    $scoresLookup = [];
    foreach ($allScores as $scoreRow) {
        $scoresLookup[$scoreRow['student_id']][$scoreRow['subject_id']][$scoreRow['assesment_id']] = $scoreRow['score'];
    }

    $subjectsOfferedCount = max(1, (int)$totalSubjects);

    foreach ($students as $student) {
        $studentTotal = 0;

        foreach ($subjects as $subject) {
            foreach ($assessments as $assessment) {
                $scoreValue = $scoresLookup[$student['id']][$subject['id']][$assessment['id']] ?? 0;
                $studentScores[$student['id']][$subject['id']][$assessment['id']] = $scoreValue;
                $studentTotal += $scoreValue;
            }
        }

        $studentTotals[$student['id']] = $studentTotal;
        $studentAverage = round($studentTotal / $subjectsOfferedCount, 2);
        $studentAverages[$student['id']] = $studentAverage;
        $classTotalSum += $studentAverage;
    }

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

$classAverage = ($studentCount > 0) ? round($classTotalSum / $studentCount, 2) : 0;
$allAverages = array_values($studentAverages);
$highestAverage = !empty($allAverages) ? round(max($allAverages), 2) : 0;
$lowestAverage = !empty($allAverages) ? round(min($allAverages), 2) : 0;

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

// Fast server-side export
if ($exportMode) {
    if (empty($classDetail['id']) || empty($selectedSession) || empty($selectedTerm)) {
        $_SESSION['error'] = 'Select class, session and term before exporting.';
        redirect('board_sheet.php?randomid=' . urlencode((string)$randomid) . '&session=' . urlencode((string)$selectedSession) . '&term_id=' . urlencode((string)$selectedTerm));
        exit;
    }

    while (ob_get_level() > 0) {
        ob_end_clean();
    }

    $safeClass = preg_replace('/[^a-zA-Z0-9_-]/', '_', (string)($classDetail['name'] ?? 'class'));
    $fileName = 'broad_sheet_' . $safeClass . '_' . date('Ymd_His') . '.csv';

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $fileName . '"');
    header('Pragma: no-cache');
    header('Expires: 0');

    $out = fopen('php://output', 'w');
    fwrite($out, "\xEF\xBB\xBF");

    $header = ['#', 'Student ID', 'First Name', 'Last Name'];
    foreach ($subjects as $subject) {
        foreach ($assessments as $assessment) {
            $header[] = (string)$subject['subject'] . ' - ' . (string)$assessment['assesment'];
        }
    }
    $header[] = 'Total';
    $header[] = 'Avg';
    $header[] = 'Grade';
    $header[] = 'Pos';
    fputcsv($out, $header);

    $counter = 0;
    foreach ($students as $student) {
        $counter++;
        $row = [
            $counter,
            (string)($student['student_id'] ?? ''),
            (string)($student['first_name'] ?? ''),
            (string)($student['last_name'] ?? '')
        ];

        $studentTotal = 0;
        foreach ($subjects as $subject) {
            foreach ($assessments as $assessment) {
                $score = (float)($studentScores[$student['id']][$subject['id']][$assessment['id']] ?? 0);
                $studentTotal += $score;
                $row[] = number_format($score, 2, '.', '');
            }
        }

        $avg = (float)($studentAverages[$student['id']] ?? 0);
        $pos = (int)($studentPositions[$student['id']] ?? 0);
        $suffix = $pos === 1 ? 'st' : ($pos === 2 ? 'nd' : ($pos === 3 ? 'rd' : 'th'));

        $row[] = number_format($studentTotal, 2, '.', '');
        $row[] = number_format($avg, 2, '.', '');
        $row[] = getGrade($avg);
        $row[] = $pos > 0 ? ($pos . $suffix) : '-';

        fputcsv($out, $row);
    }

    fclose($out);
    exit;
}
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

        .broad-container {
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
            margin-top: 16px;
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
        TABLE WRAPPER - MOBILE FIRST
        ============================================================ */
        .table-wrapper {
            overflow-x: auto;
            background: white;
            border-radius: 12px;
            border: 1px solid #e0e0e0;
            -webkit-overflow-scrolling: touch;
            margin: 0 -4px;
            padding: 0 4px;
        }

        /* ============================================================
        BROAD TABLE - MOBILE FIRST
        ============================================================ */
        .broad-table {
            border-collapse: collapse;
            font-size: 11px;
            min-width: 700px;
            width: 100%;
        }

        .broad-table th,
        .broad-table td {
            border: 1px solid #ddd;
            padding: 6px 4px;
            text-align: center;
            vertical-align: middle;
            white-space: nowrap;
        }

        .broad-table th {
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

        .broad-table th small {
            font-weight: 400;
            opacity: 0.8;
            font-size: 8px;
            display: block;
        }

        .broad-table tr:nth-child(even) {
            background: #f9f9f9;
        }

        .broad-table tr:active {
            background: #f0f4ff;
        }

        .broad-table .student-name-cell {
            text-align: left !important;
            font-weight: 600;
            font-size: 12px;
        }

        .broad-table .student-id-cell {
            color: #999;
            font-size: 10px;
        }

        .broad-table .total-cell {
            font-weight: 700;
            background: #e8f5e9;
            color: #2e7d32;
        }

        .broad-table .grade-cell {
            font-weight: 700;
            background: #e3f2fd;
            color: #0d47a1;
        }

        .broad-table .pos-cell {
            font-weight: 700;
        }

        .broad-table .pos-1 {
            background: #ffd700;
            color: #333;
            font-weight: 700;
        }

        .broad-table .pos-2 {
            background: #c0c0c0;
            color: #333;
            font-weight: 700;
        }

        .broad-table .pos-3 {
            background: #cd7f32;
            color: white;
            font-weight: 700;
        }

        .broad-table .position-badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 10px;
            font-weight: 700;
            background: #1B3058;
            color: white;
        }

        .broad-table .position-badge.gold {
            background: #ffd700;
            color: #333;
        }

        .broad-table .position-badge.silver {
            background: #c0c0c0;
            color: #333;
        }

        .broad-table .position-badge.bronze {
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
            .broad-container {
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

            .broad-table {
                font-size: 12px;
                min-width: auto;
            }

            .broad-table th,
            .broad-table td {
                padding: 8px 10px;
            }

            .broad-table th {
                font-size: 10px;
            }

            .broad-table .student-name-cell {
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
            .broad-container {
                padding: 30px;
            }

            .class-panel {
                width: 320px;
            }

            .broad-table th,
            .broad-table td {
                padding: 10px 14px;
            }

            .broad-table th {
                font-size: 11px;
            }
        }

        /* ============================================================
        RESPONSIVE - SMALL MOBILE (480px-)
        ============================================================ */
        @media (max-width: 480px) {
            .broad-container {
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

            .broad-table {
                font-size: 9px;
                min-width: 580px;
            }

            .broad-table th,
            .broad-table td {
                padding: 4px 2px;
            }

            .broad-table th {
                font-size: 7px;
            }

            .broad-table td {
                font-size: 9px;
            }

            .broad-table .student-name-cell {
                font-size: 10px;
            }

            .broad-table .student-id-cell {
                font-size: 8px;
            }

            .broad-table .position-badge {
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

            .broad-table {
                font-size: 9px;
                min-width: auto;
            }

            .broad-table th {
                background: #1B3058 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
                color: white !important;
            }

            .broad-table .total-cell,
            .broad-table .grade-cell,
            .broad-table .pos-1,
            .broad-table .pos-2,
            .broad-table .pos-3 {
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

            .broad-container {
                padding: 0;
            }

            .table-wrapper {
                border: 1px solid #ddd;
                overflow: visible;
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
                <div class="broad-container">

                    <!-- Page Header -->
                    <div class="page-header">
                        <h2><i class="fa fa-table"></i> <?= htmlspecialchars($PageTitle) ?></h2>
                        <p>View comprehensive student performance across all subjects and assessments</p>
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
                                        <a href="?randomid=<?= urlencode($class['randomid']) ?><?= $selectedSession ? '&session=' . $selectedSession : '' ?><?= $selectedTerm ? '&term_id=' . $selectedTerm : '' ?>"
                                            class="class-item <?= ($randomid == $class['randomid']) ? 'active' : '' ?>">
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
                                    <form method="GET" action="" id="filterForm">
                                        <input type="hidden" name="randomid" value="<?= htmlspecialchars($randomid) ?>">
                                        <div class="filter-grid">
                                            <div class="filter-group">
                                                <label><i class="fa fa-calendar"></i> Session</label>
                                                <select name="session" class="filter-select" required>
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
                                                    <i class="fa fa-filter"></i> Load Report
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                </div>

                                <?php if (!empty($selectedSession) && !empty($selectedTerm)): ?>

                                    <!-- HEADER -->
                                    <div class="header-section">
                                        <div class="school-name"><?= htmlspecialchars($schoolDetails['name'] ?? 'School Name') ?></div>
                                        <div class="school-address">
                                            <?= htmlspecialchars($schoolDetails['location'] ?? '') ?><?= !empty($stateName) ? ', ' . htmlspecialchars($stateName) : '' ?>
                                        </div>
                                        <div class="report-title">BROAD SHEET REPORT</div>
                                        <div class="info-bar">
                                            <span><strong>Class:</strong> <?= htmlspecialchars($classDetail['name']) ?></span>
                                            <span><strong>Session:</strong> <?= htmlspecialchars($sessionName) ?></span>
                                            <span><strong>Term:</strong> <?= htmlspecialchars($termName) ?></span>
                                            <span><strong>Students:</strong> <?= $studentCount ?></span>
                                        </div>
                                    </div>

                                    <!-- TABLE -->
                                    <div class="table-wrapper">
                                        <table class="broad-table" id="broadSheetTable">
                                            <thead>
                                                <tr>
                                                    <th rowspan="2">#</th>
                                                    <th rowspan="2">ID</th>
                                                    <th rowspan="2" style="min-width:80px;">Student</th>
                                                    <?php foreach ($subjects as $subject): ?>
                                                        <th colspan="<?= $totalAssessments ?>"><?= htmlspecialchars($subject['subject']) ?></th>
                                                    <?php endforeach; ?>
                                                    <th rowspan="2">Total</th>
                                                    <th rowspan="2">Avg</th>
                                                    <th rowspan="2">Grade</th>
                                                    <th rowspan="2">Pos</th>
                                                </tr>
                                                <tr>
                                                    <?php foreach ($subjects as $subject): ?>
                                                        <?php foreach ($assessments as $assessment): ?>
                                                            <th><small><?= htmlspecialchars($assessment['assesment']) ?></small></th>
                                                        <?php endforeach; ?>
                                                    <?php endforeach; ?>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php $counter = 0;
                                                foreach ($students as $student): $counter++; ?>
                                                    <tr>
                                                        <td><?= $counter ?></td>
                                                        <td class="student-id-cell"><?= htmlspecialchars($student['student_id']) ?></td>
                                                        <td class="student-name-cell">
                                                            <?= htmlspecialchars(($student['first_name'] ?? '') . ' ' . ($student['last_name'] ?? '')) ?>
                                                        </td>

                                                        <?php
                                                        $studentTotal = 0;
                                                        foreach ($subjects as $subject):
                                                            foreach ($assessments as $assessment):
                                                                $score = $studentScores[$student['id']][$subject['id']][$assessment['id']] ?? 0;
                                                                $studentTotal += $score;
                                                        ?>
                                                                <td><?= number_format($score, 1) ?></td>
                                                        <?php
                                                            endforeach;
                                                        endforeach;
                                                        ?>

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
                                                <?php if (empty($students)): ?>
                                                    <tr>
                                                        <td colspan="<?= 4 + ($totalSubjects * max(1, $totalAssessments)) + 4 ?>" style="text-align:center; padding:30px; color:#999;">
                                                            <i class="fa fa-users" style="font-size:24px; display:block; margin-bottom:8px;"></i>
                                                            No students found for this class
                                                        </td>
                                                    </tr>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>

                                    <!-- SUMMARY -->
                                    <div class="summary-card">
                                        <div class="summary-item">
                                            <div class="summary-label">Students</div>
                                            <div class="summary-value"><?= $studentCount ?></div>
                                        </div>
                                        <div class="summary-item">
                                            <div class="summary-label">Class Average</div>
                                            <div class="summary-value"><?= number_format($classAverage, 2) ?></div>
                                        </div>
                                        <div class="summary-item">
                                            <div class="summary-label">Highest</div>
                                            <div class="summary-value"><?= number_format($highestAverage, 2) ?></div>
                                        </div>
                                        <div class="summary-item">
                                            <div class="summary-label">Lowest</div>
                                            <div class="summary-value"><?= number_format($lowestAverage, 2) ?></div>
                                        </div>
                                    </div>

                                    <!-- BUTTONS -->
                                    <div class="btn-group">
                                        <button class="btn btn-success" onclick="exportToExcel()">
                                            <i class="fa fa-file-excel-o"></i> Download Excel
                                        </button>
                                        <button class="btn btn-danger" onclick="window.print()">
                                            <i class="fa fa-file-pdf-o"></i> Save as PDF
                                        </button>
                                    </div>

                                <?php else: ?>
                                    <div class="empty-state">
                                        <i class="fa fa-filter"></i>
                                        <h3>Select Session and Term</h3>
                                        <p>Please select a session and term to view the Broad Sheet.</p>
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
        function exportToExcel() {
            const params = new URLSearchParams(window.location.search);
            params.set('export', 'excel');
            window.location.href = 'board_sheet.php?' + params.toString();
        }

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