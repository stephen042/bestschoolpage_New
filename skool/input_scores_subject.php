<?php

/**
 * ============================================================================
 * INPUT SCORE SUBJECT TEACHER - MODERN REDESIGN (FIXED)
 * ============================================================================
 * Description: Subject teachers can enter scores for all assessments at once
 * Features: Auto-load students, real-time calculation, bulk save, edit existing scores
 * Version: 4.0 (Fully Mobile Responsive)
 * ============================================================================
 */

require_once('../config.php');
require_once('inc.session-create.php');

$PageTitle = "Input Score (Subject Teacher)";
$FileName = 'input_scores_subject.php';

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
// INITIALIZATION
// ============================================================================
$stat = [];
$randomid = $_GET['randomid'] ?? '';
$action = $_GET['action'] ?? '';
$selectedSession = $_GET['session'] ?? '';
$selectedTerm = $_GET['term_id'] ?? '';
$selectedAssessment = $_GET['assesment'] ?? '';

if (isset($_SESSION['success']) && $_SESSION['success'] != "") {
    $stat['success'] = $_SESSION['success'];
    unset($_SESSION['success']);
}

if (isset($_SESSION['error']) && $_SESSION['error'] != "") {
    $stat['error'] = $_SESSION['error'];
    unset($_SESSION['error']);
}

// ============================================================================
// GET SUBJECT DETAILS (the selected subject from left panel)
// ============================================================================
$subjectDetail = [];
if (!empty($randomid)) {
    $subjectDetail = db_get_row(
        "SELECT * FROM school_subject WHERE randomid = ? AND create_by_userid = ?",
        [$randomid, $create_by_userid]
    );
}

// ============================================================================
// GET CLASS DETAILS from the selected subject
// ============================================================================
$classDetail = [];
if (!empty($subjectDetail['class_id'])) {
    $classDetail = db_get_row(
        "SELECT * FROM school_class WHERE id = ? AND create_by_userid = ?",
        [$subjectDetail['class_id'], $create_by_userid]
    );
}

// ============================================================================
// GET ASSESSMENTS FOR THIS CLASS (global + class-specific)
// ============================================================================
$assessments = [];
$assessmentPercentages = [];
if (!empty($classDetail['id'])) {
    $assessments = db_get_rows(
        "SELECT * FROM school_assessment WHERE (class_id = ? OR class_id IS NULL OR class_id = 0) AND create_by_userid = ? ORDER BY id ASC",
        [$classDetail['id'], $create_by_userid]
    );
    foreach ($assessments as $ass) {
        $assessmentPercentages[$ass['id']] = floatval($ass['percentage'] ?? 0);
    }
}

// ============================================================================
// GET STUDENTS FOR THE CLASS (FIXED: multiple attempts)
// ============================================================================
$students = [];
if (!empty($classDetail['id']) && !empty($selectedSession) && !empty($selectedTerm)) {
    // First try: match exactly
    $students = db_get_rows(
        "SELECT * FROM manage_student WHERE class = ? AND session = ? AND term_id = ? AND create_by_userid = ? ORDER BY first_name ASC",
        [$classDetail['id'], $selectedSession, $selectedTerm, $create_by_userid]
    );

    // If no students found, try without term_id
    if (empty($students)) {
        $students = db_get_rows(
            "SELECT * FROM manage_student WHERE class = ? AND session = ? AND create_by_userid = ? ORDER BY first_name ASC",
            [$classDetail['id'], $selectedSession, $create_by_userid]
        );
    }

    // If still no students, try without session
    if (empty($students)) {
        $students = db_get_rows(
            "SELECT * FROM manage_student WHERE class = ? AND create_by_userid = ? ORDER BY first_name ASC",
            [$classDetail['id'], $create_by_userid]
        );
    }
}

// ============================================================================
// GET EXISTING SCORES FOR ALL ASSESSMENTS
// ============================================================================
$existingScores = [];
if (!empty($students) && !empty($subjectDetail['id']) && !empty($assessments)) {
    foreach ($students as $student) {
        foreach ($assessments as $ass) {
            $score = db_get_val(
                "SELECT score FROM input_score_subject_teacher WHERE student_id = ? AND subject_id = ? AND assesment_id = ? AND session_id = ? AND term_id = ? AND class_id = ? AND create_by_userid = ?",
                [$student['id'], $subjectDetail['id'], $ass['id'], $selectedSession, $selectedTerm, $classDetail['id'], $create_by_userid]
            );
            $existingScores[$student['id']][$ass['id']] = $score !== false ? floatval($score) : '';
        }
    }
}

// ============================================================================
// SAVE ALL SCORES (BULK) WITH VALIDATION
// ============================================================================
if (isset($_POST['save_all_scores'])) {
    $studentIds = explode(',', $_POST['student_ids'] ?? '');
    $assessmentIds = explode(',', $_POST['assessment_ids'] ?? '');
    $validationErrors = [];
    $successCount = 0;

    foreach ($studentIds as $studentId) {
        if (empty($studentId)) continue;

        foreach ($assessmentIds as $assessmentId) {
            if (empty($assessmentId)) continue;

            $scoreKey = "score_{$studentId}_{$assessmentId}";
            $score = floatval($_POST[$scoreKey] ?? 0);
            $maxScore = $assessmentPercentages[$assessmentId] ?? 100;

            if ($score > $maxScore) {
                $studentName = db_get_val("SELECT first_name FROM manage_student WHERE id = ?", [$studentId]);
                $assessmentName = db_get_val("SELECT assesment FROM school_assessment WHERE id = ?", [$assessmentId]);
                $validationErrors[] = "$studentName - $assessmentName: Score ($score) exceeds maximum ($maxScore)";
                continue;
            }

            $existing = db_get_val(
                "SELECT id FROM input_score_subject_teacher WHERE student_id = ? AND subject_id = ? AND assesment_id = ? AND session_id = ? AND term_id = ? AND class_id = ? AND create_by_userid = ?",
                [$studentId, $subjectDetail['id'], $assessmentId, $selectedSession, $selectedTerm, $classDetail['id'], $create_by_userid]
            );

            $data = [
                'session_id' => $selectedSession,
                'term_id' => $selectedTerm,
                'class_id' => $classDetail['id'],
                'subject_id' => $subjectDetail['id'],
                'assesment_id' => $assessmentId,
                'student_id' => $studentId,
                'score' => $score,
                'usertype' => $_SESSION['usertype'] ?? '',
                'userid' => $_SESSION['userid'] ?? 0,
                'create_by_usertype' => $create_by_usertype,
                'create_by_userid' => $create_by_userid,
                'randomid' => randomFix(15),
            ];

            if ($existing) {
                db_update("input_score_subject_teacher", ['score' => $score], "id = ?", [$existing]);
            } else {
                db_insert("input_score_subject_teacher", $data);
            }
            $successCount++;
        }

        // Update total score
        $totalScore = 0;
        foreach ($assessmentIds as $assessmentId) {
            if (empty($assessmentId)) continue;
            $scoreKey = "score_{$studentId}_{$assessmentId}";
            $score = floatval($_POST[$scoreKey] ?? 0);
            $totalScore += $score;
        }
        $totalScore = round($totalScore, 2);

        $existingTotal = db_get_val(
            "SELECT id FROM result_total WHERE student_id = ? AND class_id = ? AND session_id = ? AND term_id = ? AND create_by_userid = ?",
            [$studentId, $classDetail['id'], $selectedSession, $selectedTerm, $create_by_userid]
        );

        if ($existingTotal) {
            db_update("result_total", ['total' => $totalScore], "id = ?", [$existingTotal]);
        } else {
            db_insert("result_total", [
                'session_id' => $selectedSession,
                'term_id' => $selectedTerm,
                'class_id' => $classDetail['id'],
                'student_id' => $studentId,
                'total' => $totalScore,
                'usertype' => $_SESSION['usertype'] ?? '',
                'userid' => $_SESSION['userid'] ?? 0,
                'create_by_usertype' => $create_by_usertype,
                'create_by_userid' => $create_by_userid,
            ]);
        }
    }

    if (!empty($validationErrors)) {
        $_SESSION['error'] = "Validation Errors:<br>" . implode("<br>", $validationErrors);
    } else {
        $_SESSION['success'] = "$successCount scores saved successfully!";
    }

    $redirectUrl = $FileName . "?action=input_score&randomid=" . $randomid . "&session=" . $selectedSession . "&term_id=" . $selectedTerm . "&assesment=" . $selectedAssessment;
    redirect($redirectUrl);
    exit;
}

// ============================================================================
// GET SUBJECTS FOR THE TEACHER (only subjects they are assigned to teach)
// ============================================================================
$teacherSubjects = [];
$teacherId = $sessionUserId;

// Get subjects where this teacher is assigned as subject teacher
$teacherSubjects = db_get_rows(
    "SELECT DISTINCT ss.* 
     FROM school_subject ss
     INNER JOIN subject_teacher st ON ss.id = st.school_subject
     WHERE st.staff_id = ? AND ss.create_by_userid = ?
     ORDER BY ss.subject ASC",
    [$teacherId, $create_by_userid]
);

// If no assigned subjects, get all subjects (fallback for admin)
if (empty($teacherSubjects)) {
    $teacherSubjects = db_get_rows(
        "SELECT * FROM school_subject WHERE create_by_userid = ? ORDER BY subject ASC",
        [$create_by_userid]
    );
}

// ============================================================================
// GET SESSIONS AND TERMS
// ============================================================================
$sessions = db_get_rows("SELECT * FROM school_session WHERE create_by_userid = ? ORDER BY id DESC", [$create_by_userid]);
$terms = db_get_rows("SELECT * FROM school_term WHERE create_by_userid = ? ORDER BY id DESC", [$create_by_userid]);

$allClasses = db_get_rows("SELECT * FROM school_class WHERE create_by_userid = ? ORDER BY name ASC", [$create_by_userid]);

// ============================================================================
// GRADE SCALE
// ============================================================================
$gradeScale = db_get_rows("SELECT * FROM school_grade WHERE create_by_userid = ? ORDER BY minimum_number DESC", [$create_by_userid]);

function calculateGrade($score, $gradeScale)
{
    if (empty($gradeScale)) {
        if ($score >= 70) return 'A';
        if ($score >= 60) return 'B';
        if ($score >= 50) return 'C';
        if ($score >= 45) return 'D';
        if ($score >= 40) return 'E';
        return 'F';
    }
    foreach ($gradeScale as $grade) {
        $min = floatval($grade['minimum_number'] ?? 0);
        $max = floatval($grade['maximum_number'] ?? 0);
        if ($score >= $min && $score <= $max) {
            return $grade['grade'];
        }
    }
    return 'N/A';
}

// ============================================================================
// GET AVAILABLE ASSESSMENTS FOR FILTER DROPDOWN
// ============================================================================
$availableAssessments = [];
if (!empty($classDetail['id'])) {
    $availableAssessments = db_get_rows(
        "SELECT * FROM school_assessment WHERE (class_id = ? OR class_id IS NULL OR class_id = 0) AND create_by_userid = ? ORDER BY id ASC",
        [$classDetail['id'], $create_by_userid]
    );
}

// Get class name for display
$className = $classDetail['name'] ?? 'Select a Subject';
$subjectName = $subjectDetail['subject'] ?? '';
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

        .score-container {
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
        .two-column-layout {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        /* ============================================================
        SUBJECT LIST PANEL - MOBILE FIRST
        ============================================================ */
        .subject-list-panel {
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

        .subject-list {
            max-height: 300px;
            overflow-y: auto;
            -webkit-overflow-scrolling: touch;
        }

        .subject-item {
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

        .subject-item:active {
            background: #e8eef5;
        }

        .subject-item:hover {
            background: #f8f9ff;
        }

        .subject-item.active {
            background: #e8eef5;
            border-left: 4px solid #1B3058;
        }

        .subject-item .subject-icon {
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

        .subject-item.active .subject-icon {
            background: #1B3058;
            color: white;
        }

        .subject-item .subject-name {
            font-weight: 600;
            font-size: 14px;
        }

        .subject-item .subject-meta {
            font-size: 11px;
            color: #999;
            margin-top: 2px;
        }

        .subject-item .subject-arrow {
            margin-left: auto;
            color: #ccc;
            font-size: 14px;
        }

        /* ============================================================
        SCORE PANEL - MOBILE FIRST
        ============================================================ */
        .score-panel {
            width: 100%;
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

        .filter-select:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .filter-input {
            width: 100%;
            padding: 10px 14px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 14px;
            background: #f8f9fa;
            color: #333;
        }

        .auto-load-status {
            font-size: 12px;
            color: #666;
            margin-top: 10px;
            padding: 8px 12px;
            background: #f8f9fa;
            border-radius: 8px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .auto-load-status i {
            font-size: 14px;
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

        .btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        /* ============================================================
        SCORE CARD - MOBILE FIRST
        ============================================================ */
        .score-card {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
            overflow: hidden;
        }

        .score-card .card-header {
            padding: 14px 18px;
            background: linear-gradient(135deg, #1B3058, #2a4780);
            color: white;
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .score-card .card-header .title {
            font-weight: 600;
            font-size: 15px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .score-card .card-header .subtitle {
            font-size: 12px;
            opacity: 0.8;
            font-weight: 400;
        }

        .score-card .card-body {
            padding: 12px 8px;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        .score-card .card-footer {
            padding: 12px 16px;
            background: #f8f9fa;
            border-top: 1px solid #eee;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        /* ============================================================
        SCORE TABLE - MOBILE FIRST
        ============================================================ */
        .score-table {
            width: 100%;
            min-width: 600px;
            border-collapse: collapse;
            font-size: 11px;
        }

        .score-table th,
        .score-table td {
            padding: 6px 4px;
            text-align: center;
            border: 1px solid #e0e0e0;
            vertical-align: middle;
        }

        .score-table th {
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

        .score-table th small {
            font-weight: 400;
            opacity: 0.7;
            font-size: 8px;
        }

        .score-table td {
            font-size: 11px;
        }

        .score-table .student-name-cell {
            text-align: left;
            font-weight: 600;
            font-size: 12px;
        }

        .score-table .student-id-cell {
            color: #999;
            font-size: 10px;
        }

        .score-table input[type="number"] {
            width: 50px;
            padding: 4px 2px;
            text-align: center;
            border: 2px solid #e0e0e0;
            border-radius: 6px;
            font-size: 12px;
            transition: all 0.2s;
            -webkit-appearance: none;
            appearance: none;
        }

        .score-table input[type="number"]:focus {
            outline: none;
            border-color: #1B3058;
            box-shadow: 0 0 0 3px rgba(27, 48, 88, 0.1);
        }

        .score-table input[type="number"].invalid {
            border-color: #dc3545;
            background-color: #fff0f0;
        }

        .score-table input[type="number"]:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .score-table .total-cell {
            font-weight: 700;
            background: #e8f5e9;
            color: #2e7d32;
        }

        .score-table .grade-cell {
            font-weight: 700;
            background: #e3f2fd;
            color: #0d47a1;
        }

        .score-table .max-hint {
            display: block;
            font-size: 7px;
            color: #999;
            margin-top: 2px;
            min-height: 12px;
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
        LOADING SPINNER
        ============================================================ */
        .spinner {
            display: inline-block;
            width: 16px;
            height: 16px;
            border: 2px solid #fff;
            border-top-color: transparent;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        /* ============================================================
        RESPONSIVE - TABLET (768px+)
        ============================================================ */
        @media (min-width: 768px) {
            .score-container {
                padding: 25px;
            }

            .two-column-layout {
                flex-direction: row;
                gap: 25px;
            }

            .subject-list-panel {
                width: 280px;
                flex-shrink: 0;
            }

            .subject-list {
                max-height: 70vh;
            }

            .score-panel {
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

            .score-table {
                font-size: 12px;
                min-width: auto;
            }

            .score-table th,
            .score-table td {
                padding: 8px 10px;
            }

            .score-table th {
                font-size: 10px;
            }

            .score-table td {
                font-size: 12px;
            }

            .score-table .student-name-cell {
                font-size: 13px;
            }

            .score-table input[type="number"] {
                width: 70px;
                padding: 6px 8px;
                font-size: 13px;
            }

            .score-card .card-header {
                flex-direction: row;
                justify-content: space-between;
                align-items: center;
                padding: 16px 24px;
            }

            .score-card .card-header .subtitle {
                font-size: 12px;
            }

            .score-card .card-body {
                padding: 16px 20px;
            }

            .score-card .card-footer {
                flex-direction: row;
                justify-content: flex-end;
                padding: 15px 24px;
            }

            .score-card .card-footer .btn {
                width: auto;
            }

            .page-header h2 {
                font-size: 28px;
            }
        }

        /* ============================================================
        RESPONSIVE - DESKTOP (1024px+)
        ============================================================ */
        @media (min-width: 1024px) {
            .score-container {
                padding: 30px;
            }

            .subject-list-panel {
                width: 320px;
            }

            .score-table th,
            .score-table td {
                padding: 10px 14px;
            }

            .score-table input[type="number"] {
                width: 80px;
                padding: 8px;
                font-size: 14px;
            }
        }

        /* ============================================================
        RESPONSIVE - SMALL MOBILE (480px-)
        ============================================================ */
        @media (max-width: 480px) {
            .score-container {
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

            .score-table {
                font-size: 10px;
                min-width: 500px;
            }

            .score-table th,
            .score-table td {
                padding: 4px 2px;
            }

            .score-table th {
                font-size: 8px;
            }

            .score-table td {
                font-size: 10px;
            }

            .score-table .student-name-cell {
                font-size: 11px;
            }

            .score-table input[type="number"] {
                width: 40px;
                padding: 3px 2px;
                font-size: 10px;
            }

            .score-card .card-header .title {
                font-size: 13px;
            }

            .score-card .card-header .subtitle {
                font-size: 10px;
            }

            .btn {
                font-size: 12px;
                padding: 8px 14px;
                min-height: 38px;
            }

            .auto-load-status {
                font-size: 11px;
                padding: 6px 10px;
            }
        }

        /* ============================================================
        PRINT STYLES
        ============================================================ */
        @media print {

            .subject-list-panel,
            .filter-card,
            .card-footer,
            .btn,
            .no-print,
            .auto-load-status {
                display: none !important;
            }

            .score-card {
                box-shadow: none !important;
                border: 1px solid #ddd;
            }

            .score-card .card-header {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            body {
                background: white;
            }

            .score-container {
                padding: 0;
            }

            .score-table th {
                background: #f8f9fa !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .score-table .total-cell,
            .score-table .grade-cell {
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
                <div class="score-container">
                    <div class="page-header">
                        <h2><i class="fa fa-pencil-square-o"></i> <?= htmlspecialchars($PageTitle) ?></h2>
                        <p>Select subject, session, term, and assessment to load students automatically</p>
                    </div>

                    <?= showMessage($stat) ?>

                    <div class="two-column-layout">
                        <!-- LEFT: Subject List -->
                        <div class="subject-list-panel">
                            <div class="panel-header">
                                <i class="fa fa-book"></i> My Subjects
                                <span class="count-badge"><?= count($teacherSubjects) ?></span>
                            </div>
                            <div class="subject-list">
                                <?php if (!empty($teacherSubjects)): ?>
                                    <?php foreach ($teacherSubjects as $subject):
                                        $className = db_get_val("SELECT name FROM school_class WHERE id = ?", [$subject['class_id']]);
                                    ?>
                                        <a href="?action=input_score&randomid=<?= urlencode($subject['randomid']) ?><?= $selectedSession ? '&session=' . urlencode($selectedSession) : '' ?><?= $selectedTerm ? '&term_id=' . urlencode($selectedTerm) : '' ?><?= $selectedAssessment ? '&assesment=' . urlencode($selectedAssessment) : '' ?>"
                                            class="subject-item <?= ($randomid == $subject['randomid']) ? 'active' : '' ?>">
                                            <div class="subject-icon">
                                                <i class="fa fa-chalkboard-teacher"></i>
                                            </div>
                                            <div>
                                                <div class="subject-name"><?= htmlspecialchars($subject['subject']) ?></div>
                                                <div class="subject-meta"><i class="fa fa-graduation-cap"></i> <?= htmlspecialchars($className ?: 'N/A') ?></div>
                                            </div>
                                            <div class="subject-arrow"><i class="fa fa-chevron-right"></i></div>
                                        </a>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="empty-state" style="padding: 30px 20px;">
                                        <i class="fa fa-book"></i>
                                        <p>No subjects assigned to you</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- RIGHT: Score Entry -->
                        <div class="score-panel">
                            <?php if (!empty($subjectDetail) && !empty($classDetail)): ?>
                                <!-- Filters - Auto Load Students -->
                                <div class="filter-card">
                                    <form method="GET" action="" id="filterForm">
                                        <input type="hidden" name="action" value="input_score">
                                        <input type="hidden" name="randomid" value="<?= htmlspecialchars($randomid) ?>">
                                        <div class="filter-grid">
                                            <div class="filter-group">
                                                <label><i class="fa fa-calendar"></i> Session</label>
                                                <select name="session" class="filter-select" onchange="this.form.submit()">
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
                                                <select name="term_id" class="filter-select" onchange="this.form.submit()">
                                                    <option value="">-- Select --</option>
                                                    <?php foreach ($terms as $t): ?>
                                                        <option value="<?= $t['id'] ?>" <?= ($selectedTerm == $t['id']) ? 'selected' : '' ?>>
                                                            <?= htmlspecialchars($t['term']) ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="filter-group" style="flex:0.5;">
                                                <label><i class="fa fa-book"></i> Subject</label>
                                                <input type="text" class="filter-input" value="<?= htmlspecialchars($subjectDetail['subject'] ?? '') ?>" disabled>
                                            </div>
                                            <div class="filter-group" style="flex:0.5;">
                                                <label><i class="fa fa-users"></i> Class</label>
                                                <input type="text" class="filter-input" value="<?= htmlspecialchars($classDetail['name'] ?? '') ?>" disabled>
                                            </div>
                                        </div>
                                        <div class="auto-load-status">
                                            <?php if (!empty($selectedSession) && !empty($selectedTerm)): ?>
                                                <i class="fa fa-check-circle" style="color:#28a745;"></i>
                                                <span><?= count($students) ?> student(s) loaded for this selection.</span>
                                            <?php else: ?>
                                                <i class="fa fa-info-circle" style="color:#17a2b8;"></i>
                                                <span>Select session and term to load students automatically.</span>
                                            <?php endif; ?>
                                        </div>
                                    </form>
                                </div>

                                <!-- Score Entry Table -->
                                <?php if (!empty($selectedSession) && !empty($selectedTerm) && !empty($students) && !empty($assessments)): ?>
                                    <div class="score-card">
                                        <form method="POST" id="scoreForm">
                                            <input type="hidden" name="save_all_scores" value="1">

                                            <div class="card-header">
                                                <div class="title">
                                                    <i class="fa fa-table"></i>
                                                    <?= htmlspecialchars($subjectDetail['subject'] ?? 'Subject') ?>
                                                </div>
                                                <div class="subtitle">
                                                    <i class="fa fa-info-circle"></i> Enter all scores below
                                                </div>
                                            </div>

                                            <div class="card-body">
                                                <table class="score-table" id="scoreTable">
                                                    <thead>
                                                        <tr>
                                                            <th style="min-width:80px;">Student</th>
                                                            <th style="min-width:50px;">ID</th>
                                                            <?php foreach ($assessments as $ass): ?>
                                                                <th><?= htmlspecialchars($ass['assesment']) ?><br><small>(Max: <?= floatval($ass['percentage'] ?? 0) ?>)</small></th>
                                                            <?php endforeach; ?>
                                                            <th>Total</th>
                                                            <th>Grade</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php foreach ($students as $student):
                                                            $totalScore = 0;
                                                            foreach ($assessments as $ass) {
                                                                $score = floatval($existingScores[$student['id']][$ass['id']] ?? 0);
                                                                $totalScore += $score;
                                                            }
                                                            $totalScore = round($totalScore, 2);
                                                            $grade = calculateGrade($totalScore, $gradeScale);
                                                        ?>
                                                            <tr>
                                                                <td class="student-name-cell"><?= htmlspecialchars($student['first_name'] . ' ' . $student['last_name']) ?></td>
                                                                <td class="student-id-cell"><?= htmlspecialchars($student['student_id']) ?></td>
                                                                <?php foreach ($assessments as $ass):
                                                                    $scoreValue = $existingScores[$student['id']][$ass['id']] ?? '';
                                                                    $maxScore = floatval($ass['percentage'] ?? 0);
                                                                ?>
                                                                    <td>
                                                                        <input type="number"
                                                                            class="score-input"
                                                                            data-student="<?= $student['id'] ?>"
                                                                            data-assessment="<?= $ass['id'] ?>"
                                                                            data-max="<?= $maxScore ?>"
                                                                            value="<?= htmlspecialchars($scoreValue) ?>"
                                                                            step="any"
                                                                            min="0"
                                                                            max="<?= $maxScore ?>"
                                                                            placeholder="0"
                                                                            inputmode="decimal">
                                                                        <span class="max-hint"></span>
                                                                    </td>
                                                                <?php endforeach; ?>
                                                                <td class="total-cell" id="total_<?= $student['id'] ?>"><?= $totalScore ?></td>
                                                                <td class="grade-cell" id="grade_<?= $student['id'] ?>"><?= htmlspecialchars($grade) ?></td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    </tbody>
                                                </table>
                                            </div>

                                            <div class="card-footer">
                                                <button type="button" class="btn btn-success btn-block" onclick="validateAndSubmit()" <?= empty($students) ? 'disabled' : '' ?>>
                                                    <i class="fa fa-save"></i> Save All Scores
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                <?php elseif (!empty($selectedSession) && !empty($selectedTerm) && empty($students)): ?>
                                    <div class="alert alert-warning">
                                        <i class="fa fa-exclamation-triangle"></i>
                                        <div>No students found for this class. Please add students first.</div>
                                    </div>
                                <?php elseif (!empty($selectedSession) && empty($selectedTerm)): ?>
                                    <div class="alert alert-info">
                                        <i class="fa fa-info-circle"></i>
                                        <div>Please select a term to continue.</div>
                                    </div>
                                <?php elseif (empty($selectedSession)): ?>
                                    <div class="alert alert-info">
                                        <i class="fa fa-info-circle"></i>
                                        <div>Please select a session and term to continue.</div>
                                    </div>
                                <?php endif; ?>
                            <?php else: ?>
                                <div class="alert alert-info">
                                    <i class="fa fa-info-circle"></i>
                                    <div>Please select a subject from the left panel.</div>
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
        // ============================================================================
        // JAVASCRIPT FUNCTIONS - REAL-TIME CALCULATION & VALIDATION
        // ============================================================================

        // Real-time calculation of total (RAW SUM)
        function calculateStudentTotal(studentId) {
            var total = 0;
            var inputs = document.querySelectorAll('.score-input[data-student="' + studentId + '"]');
            inputs.forEach(function(input) {
                var score = parseFloat(input.value) || 0;
                total += score;
            });
            total = Math.round(total * 100) / 100;
            var totalCell = document.getElementById('total_' + studentId);
            if (totalCell) totalCell.innerText = total;

            var grade = getGrade(total);
            var gradeCell = document.getElementById('grade_' + studentId);
            if (gradeCell) gradeCell.innerText = grade;
        }

        function getGrade(totalScore) {
            if (totalScore >= 70) return 'A';
            if (totalScore >= 60) return 'B';
            if (totalScore >= 50) return 'C';
            if (totalScore >= 45) return 'D';
            if (totalScore >= 40) return 'E';
            return 'F';
        }

        // Validate a single input
        function validateInput(input) {
            var value = parseFloat(input.value);
            var max = parseFloat(input.getAttribute('data-max')) || 0;
            var isValid = true;
            var errorMsg = '';

            if (isNaN(value)) {
                isValid = true; // Empty is allowed
                errorMsg = '';
            } else if (value < 0) {
                isValid = false;
                errorMsg = 'Min 0';
            } else if (value > max) {
                isValid = false;
                errorMsg = 'Max ' + max;
            }

            if (!isValid) {
                input.classList.add('invalid');
                var hint = input.nextElementSibling;
                if (hint) {
                    hint.innerHTML = errorMsg;
                    hint.style.color = '#dc3545';
                    hint.style.display = 'block';
                }
            } else {
                input.classList.remove('invalid');
                var hint = input.nextElementSibling;
                if (hint) {
                    hint.innerHTML = '';
                }
            }

            return isValid;
        }

        // Validate all inputs before submission
        function validateAllInputs() {
            var allInputs = document.querySelectorAll('.score-input');
            var isValid = true;
            var errorMessages = [];

            allInputs.forEach(function(input) {
                var value = parseFloat(input.value);
                var max = parseFloat(input.getAttribute('data-max')) || 0;
                var studentName = input.closest('tr').querySelector('.student-name-cell');
                var studentNameText = studentName ? studentName.innerText : 'Unknown';

                if (!isNaN(value) && value > max) {
                    isValid = false;
                    input.classList.add('invalid');
                    errorMessages.push(studentNameText + ': Score ' + value + ' exceeds max ' + max);
                } else if (!isNaN(value) && value < 0) {
                    isValid = false;
                    input.classList.add('invalid');
                    errorMessages.push(studentNameText + ': Score cannot be negative');
                } else {
                    input.classList.remove('invalid');
                }
            });

            if (!isValid) {
                alert('⚠️ Validation Errors:\n' + errorMessages.join('\n'));
                return false;
            }
            return true;
        }

        // Attach event listeners to all score inputs
        document.querySelectorAll('.score-input').forEach(function(input) {
            input.addEventListener('input', function() {
                var studentId = this.getAttribute('data-student');
                validateInput(this);
                calculateStudentTotal(studentId);
            });
            input.addEventListener('blur', function() {
                validateInput(this);
            });
        });

        function validateAndSubmit() {
            if (!validateAllInputs()) {
                return;
            }

            var form = document.getElementById('scoreForm');
            if (!form) return;

            // Remove any previously generated hidden inputs
            form.querySelectorAll('input[data-generated="1"]').forEach(function(el) {
                el.remove();
            });

            var studentIds = [];
            var assessmentIds = [];
            var inputs = document.querySelectorAll('.score-input');

            inputs.forEach(function(input) {
                var studentId = input.getAttribute('data-student');
                var assessmentId = input.getAttribute('data-assessment');
                if (studentId && !studentIds.includes(studentId)) studentIds.push(studentId);
                if (assessmentId && !assessmentIds.includes(assessmentId)) assessmentIds.push(assessmentId);
            });

            var studentIdsInput = document.createElement('input');
            studentIdsInput.type = 'hidden';
            studentIdsInput.name = 'student_ids';
            studentIdsInput.value = studentIds.join(',');
            studentIdsInput.setAttribute('data-generated', '1');
            form.appendChild(studentIdsInput);

            var assessmentIdsInput = document.createElement('input');
            assessmentIdsInput.type = 'hidden';
            assessmentIdsInput.name = 'assessment_ids';
            assessmentIdsInput.value = assessmentIds.join(',');
            assessmentIdsInput.setAttribute('data-generated', '1');
            form.appendChild(assessmentIdsInput);

            inputs.forEach(function(input) {
                var studentId = input.getAttribute('data-student');
                var assessmentId = input.getAttribute('data-assessment');
                var value = input.value;
                var hiddenInput = document.createElement('input');
                hiddenInput.type = 'hidden';
                hiddenInput.name = 'score_' + studentId + '_' + assessmentId;
                hiddenInput.value = value;
                hiddenInput.setAttribute('data-generated', '1');
                form.appendChild(hiddenInput);
            });

            // Show loading state on button
            var btn = form.querySelector('button[type="button"]');
            if (btn) {
                btn.innerHTML = '<span class="spinner" style="width:16px;height:16px;border-color:#fff;border-top-color:transparent;"></span> Saving...';
                btn.disabled = true;
            }

            form.submit();
        }
    </script>
</body>

</html>