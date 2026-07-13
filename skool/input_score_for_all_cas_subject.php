<?php

/**
 * ============================================================================
 * INPUT SCORE FOR ALL CAS SUBJECT - MODERN REDESIGN (FIXED)
 * ============================================================================
 * Description: Subject teachers can enter scores for all CA assessments at once
 * Features: Auto-load students, bulk save, edit existing scores
 * Version: 4.0 (Fully Mobile Responsive)
 * ============================================================================
 */

require_once('../config.php');
require_once('inc.session-create.php');

$PageTitle = "Input Score For All Cas Subject";
$FileName = 'input_score_for_all_cas_subject.php';

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

function randomFix($length = 10)
{
    $characters = '123456789abcdefghijklmnpqrstuvwxyzABCDEFGHIJKLMNPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[random_int(0, $charactersLength - 1)];
    }
    return $randomString;
}

// ============================================================================
// GET SUBJECT DETAILS
// ============================================================================
$subjectDetail = [];
if (!empty($randomid)) {
    $subjectDetail = db_get_row(
        "SELECT * FROM school_subject WHERE randomid = ? AND create_by_userid = ?",
        [$randomid, $create_by_userid]
    );
}

// ============================================================================
// GET CLASS DETAILS
// ============================================================================
$classDetail = [];
if (!empty($subjectDetail['class_id'])) {
    $classDetail = db_get_row(
        "SELECT * FROM school_class WHERE id = ? AND create_by_userid = ?",
        [$subjectDetail['class_id'], $create_by_userid]
    );
}

// ============================================================================
// GET SESSION AND TERM DETAILS
// ============================================================================
$sessionDetail = [];
if (!empty($selectedSession)) {
    $sessionDetail = db_get_row(
        "SELECT * FROM school_session WHERE id = ? AND create_by_userid = ?",
        [$selectedSession, $create_by_userid]
    );
}

$termDetail = [];
if (!empty($selectedTerm)) {
    $termDetail = db_get_row(
        "SELECT * FROM school_term WHERE id = ? AND create_by_userid = ?",
        [$selectedTerm, $create_by_userid]
    );
}

// ============================================================================
// GET ALL ASSESSMENTS FOR THIS CLASS
// ============================================================================
$assessments = [];
if (!empty($classDetail['id'])) {
    $assessments = db_get_rows(
        "SELECT * FROM school_assessment WHERE (class_id = ? OR class_id IS NULL OR class_id = 0) AND create_by_userid = ? ORDER BY id ASC",
        [$classDetail['id'], $create_by_userid]
    );
}

// ============================================================================
// GET STUDENTS FOR THE CLASS
// ============================================================================
$students = [];
if (!empty($classDetail['id']) && !empty($selectedSession) && !empty($selectedTerm)) {
    $students = db_get_rows(
        "SELECT * FROM manage_student 
         WHERE class = ? AND session = ? AND term_id = ? AND create_by_userid = ? 
         ORDER BY first_name ASC",
        [$classDetail['id'], $selectedSession, $selectedTerm, $create_by_userid]
    );
}

// ============================================================================
// GET EXISTING SCORES
// ============================================================================
$existingScores = [];
if (!empty($students) && !empty($subjectDetail['id']) && !empty($assessments)) {
    foreach ($students as $student) {
        foreach ($assessments as $ass) {
            $score = db_get_val(
                "SELECT score FROM input_score_class_teacher 
                 WHERE student_id = ? AND subject_id = ? AND assesment_id = ? 
                 AND session_id = ? AND term_id = ? AND class_id = ? 
                 AND create_by_userid = ?",
                [
                    $student['id'],
                    $subjectDetail['id'],
                    $ass['id'],
                    $selectedSession,
                    $selectedTerm,
                    $classDetail['id'],
                    $create_by_userid
                ]
            );
            $existingScores[$student['id']][$ass['id']] = $score !== false ? floatval($score) : '';
        }
    }
}

// ============================================================================
// SAVE SCORES (BULK)
// ============================================================================
if (isset($_POST['add_score']) || isset($_POST['edit_score'])) {
    $isEdit = isset($_POST['edit_score']);
    $studentIds = $_POST['student_id'] ?? [];
    $assessmentIds = $_POST['allassesment_id'] ?? [];
    $scores = $_POST['score'] ?? [];
    $offerings = $_POST['offering'] ?? [];

    $successCount = 0;

    // If editing, delete existing scores first
    if ($isEdit) {
        db_delete(
            "input_score_class_teacher",
            "class_id = ? AND subject_id = ? AND session_id = ? AND term_id = ? AND create_by_userid = ?",
            [
                $classDetail['id'],
                $subjectDetail['id'],
                $selectedSession,
                $selectedTerm,
                $create_by_userid
            ]
        );
    }

    foreach ($studentIds as $index => $studentId) {
        $assessmentId = $assessmentIds[$index] ?? 0;
        $score = floatval($scores[$index] ?? 0);
        $offering = $offerings[$index] ?? '';

        if (empty($studentId) || empty($assessmentId)) continue;

        // Check if score already exists (only for add, not edit)
        if (!$isEdit) {
            $existing = db_get_val(
                "SELECT id FROM input_score_class_teacher 
                 WHERE student_id = ? AND subject_id = ? AND assesment_id = ? 
                 AND session_id = ? AND term_id = ? AND class_id = ? 
                 AND create_by_userid = ?",
                [
                    $studentId,
                    $subjectDetail['id'],
                    $assessmentId,
                    $selectedSession,
                    $selectedTerm,
                    $classDetail['id'],
                    $create_by_userid
                ]
            );

            if ($existing) {
                continue; // Skip if already exists
            }
        }

        $data = [
            'session_id' => $selectedSession,
            'term_id' => $selectedTerm,
            'class_id' => $classDetail['id'],
            'subject_id' => $subjectDetail['id'],
            'offering' => $offering,
            'student_id' => $studentId,
            'assesment_id' => $assessmentId,
            'score' => $score,
            'usertype' => $_SESSION['usertype'] ?? '',
            'userid' => $_SESSION['userid'] ?? 0,
            'create_by_usertype' => $create_by_usertype,
            'create_by_userid' => $create_by_userid,
            'randomid' => randomFix(15),
        ];

        $result = db_insert("input_score_class_teacher", $data);
        if ($result) $successCount++;

        // Update total score
        $totalScore = db_get_val(
            "SELECT SUM(score) FROM input_score_class_teacher 
             WHERE session_id = ? AND term_id = ? AND class_id = ? 
             AND student_id = ? AND create_by_userid = ?",
            [
                $selectedSession,
                $selectedTerm,
                $classDetail['id'],
                $studentId,
                $create_by_userid
            ]
        );

        if ($totalScore === false) $totalScore = 0;
        $totalScore = round(floatval($totalScore), 2);

        $existingTotal = db_get_val(
            "SELECT id FROM result_total 
             WHERE session_id = ? AND term_id = ? AND class_id = ? 
             AND student_id = ? AND create_by_userid = ?",
            [
                $selectedSession,
                $selectedTerm,
                $classDetail['id'],
                $studentId,
                $create_by_userid
            ]
        );

        if ($existingTotal) {
            db_update(
                "result_total",
                ['total' => $totalScore],
                "id = ?",
                [$existingTotal]
            );
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
                'randomid' => randomFix(15),
            ]);
        }
    }

    if ($successCount > 0) {
        $_SESSION['success'] = ($isEdit ? "Updated" : "Saved") . " $successCount score(s) successfully!";
    } else {
        $_SESSION['error'] = "No scores were saved. Please check your input.";
    }

    redirect($FileName . "?action=input_score&randomid=" . $randomid . "&session=" . $selectedSession . "&term_id=" . $selectedTerm);
    exit;
}

// ============================================================================
// GET SUBJECTS FOR THE TEACHER
// ============================================================================
$teacherSubjects = [];
$teacherId = $sessionUserId;

// Get subjects where this teacher is assigned as subject teacher
$teacherStaffId = db_get_val(
    "SELECT id FROM staff_manage 
     WHERE create_by_userid = ? 
     AND (staff_id = ? OR email = ? OR id = ?)
     ORDER BY id DESC LIMIT 1",
    [$create_by_userid, $sessionUsername, $sessionEmail, $teacherId]
);

if (!empty($teacherStaffId)) {
    $teacherSubjects = db_get_rows(
        "SELECT DISTINCT ss.* 
         FROM school_subject ss
         INNER JOIN subject_teacher st ON ss.id = st.school_subject
         WHERE st.staff_id = ? AND ss.create_by_userid = ?
         ORDER BY ss.subject ASC",
        [$teacherStaffId, $create_by_userid]
    );
}

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

// ============================================================================
// CALCULATE TOTAL SCORE FOR EACH STUDENT
// ============================================================================
function calculateStudentTotal($studentId, $subjectId, $sessionId, $termId, $classId, $createByUserId)
{
    $total = db_get_val(
        "SELECT SUM(score) FROM input_score_class_teacher 
         WHERE student_id = ? AND subject_id = ? 
         AND session_id = ? AND term_id = ? AND class_id = ? 
         AND create_by_userid = ?",
        [$studentId, $subjectId, $sessionId, $termId, $classId, $createByUserId]
    );
    return $total !== false ? round(floatval($total), 2) : 0;
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

        .filter-input {
            width: 100%;
            padding: 10px 14px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 14px;
            background: #f8f9fa;
            color: #333;
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
        }

        /* ============================================================
        PRINT STYLES
        ============================================================ */
        @media print {

            .subject-list-panel,
            .filter-card,
            .card-footer,
            .btn,
            .no-print {
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

            .score-table .total-cell {
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
                        <p>Enter scores for all Continuous Assessments (CAs) for each student</p>
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
                                        <a href="?action=input_score&randomid=<?= urlencode($subject['randomid']) ?><?= $selectedSession ? '&session=' . urlencode($selectedSession) : '' ?><?= $selectedTerm ? '&term_id=' . urlencode($selectedTerm) : '' ?>"
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
                                <!-- Filters -->
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
                                    </form>
                                </div>

                                <!-- Score Entry Table -->
                                <?php if (!empty($selectedSession) && !empty($selectedTerm) && !empty($students) && !empty($assessments)): ?>
                                    <div class="score-card">
                                        <form method="POST" action="" id="scoreForm">
                                            <input type="hidden" name="<?= (isset($_POST['edit_score'])) ? 'edit_score' : 'add_score' ?>" value="1">

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
                                                            <?php foreach ($assessments as $ass):
                                                                $percentage = db_get_val("SELECT percentage FROM score_entry_time_frame WHERE assesment_id = ?", [$ass['id']]);
                                                                $maxScore = floatval($percentage ?: $ass['percentage'] ?? 0);
                                                            ?>
                                                                <th><?= htmlspecialchars($ass['assesment']) ?><br><small>(Max: <?= $maxScore ?>)</small></th>
                                                            <?php endforeach; ?>
                                                            <th>Total</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php foreach ($students as $student):
                                                            $totalScore = calculateStudentTotal($student['id'], $subjectDetail['id'], $selectedSession, $selectedTerm, $classDetail['id'], $create_by_userid);
                                                        ?>
                                                            <tr>
                                                                <td class="student-name-cell"><?= htmlspecialchars($student['first_name'] . ' ' . $student['last_name']) ?></td>
                                                                <td class="student-id-cell"><?= htmlspecialchars($student['student_id']) ?></td>
                                                                <?php foreach ($assessments as $ass):
                                                                    $scoreValue = $existingScores[$student['id']][$ass['id']] ?? '';
                                                                    $maxScore = floatval($ass['percentage'] ?? 0);
                                                                ?>
                                                                    <td>
                                                                        <input type="hidden" name="student_id[]" value="<?= $student['id'] ?>">
                                                                        <input type="hidden" name="allassesment_id[]" value="<?= $ass['id'] ?>">
                                                                        <input type="number" class="score-input" name="score[]" value="<?= htmlspecialchars($scoreValue) ?>" step="any" min="0" max="<?= $maxScore ?>" placeholder="0" inputmode="decimal">
                                                                    </td>
                                                                <?php endforeach; ?>
                                                                <td class="total-cell" id="total_<?= $student['id'] ?>"><?= $totalScore ?></td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    </tbody>
                                                </table>
                                            </div>

                                            <div class="card-footer">
                                                <button type="submit" name="<?= (isset($_POST['edit_score'])) ? 'edit_score' : 'add_score' ?>" class="btn btn-success btn-block" <?= empty($students) ? 'disabled' : '' ?>>
                                                    <i class="fa fa-save"></i> <?= (isset($_POST['edit_score'])) ? 'Update' : 'Save' ?> All Scores
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
        // JAVASCRIPT FUNCTIONS - REAL-TIME CALCULATION
        // ============================================================================

        // Auto-calculate totals as user types
        document.querySelectorAll('.score-input').forEach(function(input) {
            input.addEventListener('input', function() {
                var row = this.closest('tr');
                var inputs = row.querySelectorAll('.score-input');
                var total = 0;
                inputs.forEach(function(inp) {
                    var val = parseFloat(inp.value) || 0;
                    total += val;
                });
                var totalCell = row.querySelector('.total-cell');
                if (totalCell) {
                    totalCell.textContent = total.toFixed(2);
                }
            });
        });

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

        // Add active class to subject items
        document.addEventListener('DOMContentLoaded', function() {
            var subjectItems = document.querySelectorAll('.subject-item');
            subjectItems.forEach(function(item) {
                item.addEventListener('click', function() {
                    var current = document.querySelector('.subject-item.active');
                    if (current) {
                        current.classList.remove('active');
                    }
                });
            });
        });
    </script>
</body>

</html>