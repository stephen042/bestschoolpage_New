<?php

/**
 * ============================================================================
 * SUBJECT SPECIFIC COMMENT - MODERN REDESIGN (FIXED)
 * ============================================================================
 * Description: Subject teachers can enter qualitative comments for all students
 * Features: All students visible at once, inline editing, bulk save
 * Version: 4.0 (Fully Mobile Responsive)
 * ============================================================================
 */

require_once('../config.php');
require_once('inc.session-create.php');

$PageTitle = "Subject Specific Comment";
$FileName = 'subject_specific_comment.php';

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
$stat = [];
$randomid = $_GET['randomid'] ?? '';
$action = $_GET['action'] ?? '';
$selectedSession = $_GET['session'] ?? '';
$selectedTerm = $_GET['term_id'] ?? '';

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
// GET SUBJECTS FOR THE TEACHER (FIXED - using proper staff ID)
// ============================================================================
$teacherSubjects = [];

// First, get the teacher's staff ID from staff_manage
$teacherStaffId = db_get_val(
    "SELECT id FROM staff_manage 
     WHERE create_by_userid = ? 
     AND (staff_id = ? OR email = ? OR id = ?)
     ORDER BY id DESC LIMIT 1",
    [$create_by_userid, $sessionUsername, $sessionEmail, $sessionUserId]
);

// If we have a valid staff ID, get subjects assigned to this teacher
if (!empty($teacherStaffId)) {
    $teacherSubjects = db_get_rows(
        "SELECT DISTINCT ss.*, sc.name as class_name
         FROM school_subject ss
         INNER JOIN subject_teacher st ON ss.id = st.school_subject
         LEFT JOIN school_class sc ON ss.class_id = sc.id
         WHERE st.staff_id = ? AND ss.create_by_userid = ?
         ORDER BY ss.subject ASC",
        [$teacherStaffId, $create_by_userid]
    );
}

// If no assigned subjects, get all subjects (fallback for admin)
if (empty($teacherSubjects)) {
    $teacherSubjects = db_get_rows(
        "SELECT ss.*, sc.name as class_name
         FROM school_subject ss
         LEFT JOIN school_class sc ON ss.class_id = sc.id
         WHERE ss.create_by_userid = ?
         ORDER BY ss.subject ASC",
        [$create_by_userid]
    );
}

// Build class list for the sidebar using assigned classes for teachers.
$allClasses = [];
if ($isSchoolOwnerSession) {
    $allClasses = db_get_rows("SELECT * FROM school_class WHERE create_by_userid = ? ORDER BY name ASC", [$create_by_userid]);
} else {
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

// ============================================================================
// GET STUDENTS FOR THE CLASS, SESSION, TERM
// ============================================================================
$students = [];
if (!empty($classDetail['id']) && !empty($selectedSession) && !empty($selectedTerm)) {
    $students = db_get_rows(
        "SELECT
            id,
            student_id,
            first_name,
            last_name,
         FROM manage_student
         WHERE
            class = ?
            AND session = ?
            AND term_id = ?
            AND create_by_userid = ?
         ORDER BY
            first_name,
            last_name",
        [
            $classDetail['id'],
            $selectedSession,
            $selectedTerm,
            $create_by_userid
        ]
    );
}

// ============================================================================
// LOAD ALL EXISTING COMMENTS (ONE QUERY)
// ============================================================================
$existingComments = [];
if (
    !empty($classDetail['id']) &&
    !empty($selectedSession) &&
    !empty($selectedTerm) &&
    !empty($subjectDetail['id'])
) {
    $rows = db_get_rows(
        "SELECT *
         FROM subject_specific_comments
         WHERE
            class_id = ?
            AND session_id = ?
            AND term_id = ?
            AND subject_id = ?
            AND create_by_userid = ?",
        [
            $classDetail['id'],
            $selectedSession,
            $selectedTerm,
            $subjectDetail['id'],
            $create_by_userid
        ]
    );

    foreach ($rows as $row) {
        $existingComments[$row['student_id']] = $row;
    }
}

// ============================================================================
// SAVE ALL COMMENTS (BULK)
// ============================================================================
if (isset($_POST['save_all_comments'])) {
    $studentIds = array_filter(explode(',', $_POST['student_ids'] ?? ''));
    $success = 0;
    $failed = 0;

    foreach ($studentIds as $studentId) {
        $studentId = (int)$studentId;

        $save = [
            'session_id' => $selectedSession,
            'term_id' => $selectedTerm,
            'class_id' => $classDetail['id'],
            'subject_id' => $subjectDetail['id'],
            'student_id' => $studentId,
            'learning_strengths' => trim($_POST["learning_strengths_$studentId"] ?? ''),
            'learning_targets' => trim($_POST["learning_targets_$studentId"] ?? ''),
            'subject_specific_strengths' => trim($_POST["subject_specific_strengths_$studentId"] ?? ''),
            'subject_specific_target' => trim($_POST["subject_specific_target_$studentId"] ?? ''),
            'remarks' => trim($_POST["remarks_$studentId"] ?? ''),
            'attendance' => $_POST["attendance_$studentId"] ?? '',
            'punctuality' => $_POST["punctuality_$studentId"] ?? '',
            'behaviour' => $_POST["behaviour_$studentId"] ?? '',
            'effort' => $_POST["effort_$studentId"] ?? '',
            'academic_progress' => $_POST["academic_progress_$studentId"] ?? '',
            'curriculum_achievement' => $_POST["curriculum_achievement_$studentId"] ?? '',
            'userid' => $_SESSION['userid'],
            'usertype' => $_SESSION['usertype'],
            'create_by_userid' => $create_by_userid,
            'create_by_usertype' => $create_by_usertype
        ];

        $existing = db_get_val(
            "SELECT id
            FROM subject_specific_comments
            WHERE
            class_id = ?
            AND session_id = ?
            AND term_id = ?
            AND student_id = ?
            AND subject_id = ?
            AND create_by_userid = ?",
            [
                $classDetail['id'],
                $selectedSession,
                $selectedTerm,
                $studentId,
                $subjectDetail['id'],
                $create_by_userid
            ]
        );

        if ($existing) {
            $result = db_update(
                "subject_specific_comments",
                $save,
                "id = ?",
                [$existing]
            );
        } else {
            $save['randomid'] = randomFix(15);
            $result = db_insert(
                "subject_specific_comments",
                $save
            );
        }

        if ($result) {
            $success++;
        } else {
            $failed++;
        }
    }

    if ($success) {
        $stat['success'] = "$success student comment(s) saved successfully.";
    }
    if ($failed) {
        $stat['error'] = "$failed record(s) failed to save.";
    }

    // Reload comments
    $existingComments = [];
    $rows = db_get_rows(
        "SELECT *
        FROM subject_specific_comments
        WHERE
        class_id = ?
        AND session_id = ?
        AND term_id = ?
        AND subject_id = ?
        AND create_by_userid = ?",
        [
            $classDetail['id'],
            $selectedSession,
            $selectedTerm,
            $subjectDetail['id'],
            $create_by_userid
        ]
    );

    foreach ($rows as $row) {
        $existingComments[$row['student_id']] = $row;
    }
}

// ============================================================================
// GET SESSIONS AND TERMS
// ============================================================================
$sessions = db_get_rows("SELECT * FROM school_session WHERE create_by_userid = ? ORDER BY id DESC", [$create_by_userid]);
$terms = db_get_rows("SELECT * FROM school_term WHERE create_by_userid = ? ORDER BY id DESC", [$create_by_userid]);

// Dropdown options for rating fields
$ratingOptions = [
    'Poor' => 'Poor',
    'Satisfactory' => 'Satisfactory',
    'Good' => 'Good',
    'Very Good' => 'Very Good',
    'Excellent' => 'Excellent'
];
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

        .comment-container {
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
        COMMENT PANEL - MOBILE FIRST
        ============================================================ */
        .comment-panel {
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

        /* ============================================================
        COMMENT TABLE WRAPPER - MOBILE FIRST
        ============================================================ */
        .comment-table-wrapper {
            overflow-x: auto;
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
            -webkit-overflow-scrolling: touch;
            margin: 0 -4px;
            padding: 0 4px;
        }

        /* ============================================================
        COMMENT TABLE - MOBILE FIRST
        ============================================================ */
        .comment-table {
            width: 100%;
            min-width: 900px;
            border-collapse: collapse;
            font-size: 11px;
        }

        .comment-table th,
        .comment-table td {
            padding: 6px 4px;
            border: 1px solid #e0e0e0;
            vertical-align: top;
        }

        .comment-table th {
            background: #f8f9fa;
            font-weight: 700;
            color: #1B3058;
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            position: sticky;
            top: 0;
            z-index: 2;
            white-space: nowrap;
        }

        .comment-table td {
            background: #fff;
            font-size: 11px;
        }

        .comment-table .student-name-cell {
            font-weight: 600;
            background: #fafafa;
            min-width: 80px;
            font-size: 12px;
        }

        .comment-table .student-id-cell {
            color: #999;
            font-size: 10px;
            white-space: nowrap;
        }

        .comment-table .section-header {
            background: #e8eef5;
        }

        /* ============================================================
        FORM ELEMENTS INSIDE TABLE - MOBILE FIRST
        ============================================================ */
        .comment-textarea {
            width: 100%;
            padding: 4px 6px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 11px;
            resize: vertical;
            font-family: inherit;
            min-height: 40px;
            background: #fafafa;
            transition: all 0.2s;
        }

        .comment-textarea:focus {
            outline: none;
            border-color: #1B3058;
            background: #fff;
            box-shadow: 0 0 0 2px rgba(27, 48, 88, 0.1);
        }

        .comment-select {
            width: 100%;
            padding: 4px 6px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 10px;
            background: #fafafa;
            cursor: pointer;
            transition: all 0.2s;
            -webkit-appearance: none;
            appearance: none;
            background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 6px center;
            background-size: 12px;
        }

        .comment-select:focus {
            outline: none;
            border-color: #1B3058;
            background: #fff;
        }

        .comment-table .textarea-section {
            min-width: 120px;
        }

        .comment-table .dropdown-section {
            min-width: 80px;
        }

        .row-modified {
            background: #fffbe6 !important;
            transition: .3s;
        }

        .row-modified td {
            background: #fffbe6 !important;
        }

        /* ============================================================
        SAVE FOOTER - MOBILE FIRST
        ============================================================ */
        .save-footer {
            position: sticky;
            bottom: 0;
            background: #fff;
            padding: 12px 16px;
            border-top: 1px solid #ddd;
            text-align: center;
            z-index: 50;
            border-radius: 0 0 16px 16px;
        }

        .save-footer .btn {
            width: 100%;
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

        .btn-loading {
            opacity: 0.7;
            pointer-events: none;
        }

        /* ============================================================
        RESPONSIVE - TABLET (768px+)
        ============================================================ */
        @media (min-width: 768px) {
            .comment-container {
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

            .comment-panel {
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

            .comment-table {
                font-size: 12px;
                min-width: auto;
            }

            .comment-table th,
            .comment-table td {
                padding: 8px 10px;
            }

            .comment-table th {
                font-size: 10px;
            }

            .comment-table td {
                font-size: 12px;
            }

            .comment-table .student-name-cell {
                font-size: 13px;
                min-width: 100px;
            }

            .comment-textarea {
                font-size: 12px;
                padding: 6px 8px;
                min-height: 50px;
            }

            .comment-select {
                font-size: 11px;
                padding: 6px 8px;
            }

            .save-footer {
                text-align: right;
                padding: 15px 20px;
            }

            .save-footer .btn {
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
            .comment-container {
                padding: 30px;
            }

            .subject-list-panel {
                width: 320px;
            }

            .comment-table th,
            .comment-table td {
                padding: 10px 14px;
            }

            .comment-table th {
                font-size: 11px;
            }
        }

        /* ============================================================
        RESPONSIVE - SMALL MOBILE (480px-)
        ============================================================ */
        @media (max-width: 480px) {
            .comment-container {
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

            .comment-table {
                font-size: 10px;
                min-width: 700px;
            }

            .comment-table th,
            .comment-table td {
                padding: 4px 3px;
            }

            .comment-table th {
                font-size: 8px;
            }

            .comment-table td {
                font-size: 10px;
            }

            .comment-table .student-name-cell {
                font-size: 11px;
                min-width: 60px;
            }

            .comment-textarea {
                font-size: 10px;
                padding: 3px 4px;
                min-height: 30px;
            }

            .comment-select {
                font-size: 9px;
                padding: 3px 4px;
                background-size: 10px;
            }

            .comment-table .textarea-section {
                min-width: 80px;
            }

            .comment-table .dropdown-section {
                min-width: 60px;
            }

            .subject-item {
                padding: 10px 14px;
            }

            .subject-item .subject-icon {
                width: 34px;
                height: 34px;
                font-size: 14px;
            }

            .subject-item .subject-name {
                font-size: 13px;
            }

            .btn {
                font-size: 12px;
                padding: 8px 14px;
                min-height: 38px;
            }

            .save-footer {
                padding: 10px 12px;
            }
        }

        /* ============================================================
        PRINT STYLES
        ============================================================ */
        @media print {

            .subject-list-panel,
            .filter-card,
            .save-footer,
            .btn,
            .no-print {
                display: none !important;
            }

            .comment-table-wrapper {
                box-shadow: none !important;
                border: 1px solid #ddd;
            }

            body {
                background: white;
            }

            .comment-container {
                padding: 0;
            }

            .comment-table th {
                background: #f8f9fa !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .comment-textarea {
                border: 1px solid #ddd !important;
                background: transparent !important;
            }

            .comment-select {
                border: 1px solid #ddd !important;
                background: transparent !important;
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
                <div class="comment-container">

                    <!-- Page Header -->
                    <div class="page-header">
                        <h2><i class="fa fa-commenting-o"></i> <?= htmlspecialchars($PageTitle) ?></h2>
                        <p>Enter subject-specific comments, strengths, targets, and behavioral ratings for each student</p>
                    </div>

                    <?= showMessage($stat) ?>

                    <div class="two-column-layout">
                        <!-- LEFT: Subject List (based on teacher's assignments) -->
                        <div class="subject-list-panel">
                            <div class="panel-header">
                                <i class="fa fa-book"></i> My Subjects
                                <span class="count-badge"><?= count($teacherSubjects) ?></span>
                            </div>
                            <div class="subject-list">
                                <?php if (!empty($teacherSubjects)): ?>
                                    <?php foreach ($teacherSubjects as $subject): ?>
                                        <a href="?randomid=<?= urlencode($subject['randomid']) ?><?= $selectedSession ? '&session=' . urlencode($selectedSession) : '' ?><?= $selectedTerm ? '&term_id=' . urlencode($selectedTerm) : '' ?>"
                                            class="subject-item <?= ($randomid == $subject['randomid']) ? 'active' : '' ?>">
                                            <div class="subject-icon">
                                                <i class="fa fa-chalkboard-teacher"></i>
                                            </div>
                                            <div>
                                                <div class="subject-name"><?= htmlspecialchars($subject['subject']) ?></div>
                                                <div class="subject-meta"><i class="fa fa-graduation-cap"></i> <?= htmlspecialchars($subject['class_name'] ?? 'N/A') ?></div>
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

                        <!-- RIGHT: Comment Entry -->
                        <div class="comment-panel">
                            <?php if (!empty($subjectDetail) && !empty($classDetail)): ?>
                                <!-- Filters -->
                                <div class="filter-card">
                                    <form method="GET" action="" id="filterForm">
                                        <div class="filter-grid">
                                            <div class="filter-group">
                                                <label><i class="fa fa-calendar"></i> Session</label>
                                                <select name="session" class="filter-select" required onchange="document.getElementById('filterForm').submit()">
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
                                                <select name="term_id" class="filter-select" required onchange="document.getElementById('filterForm').submit()">
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
                                                <input type="text" class="filter-input" value="<?= htmlspecialchars($subjectDetail['subject']) ?>" disabled>
                                            </div>
                                            <div class="filter-group" style="flex:0.5;">
                                                <label><i class="fa fa-users"></i> Class</label>
                                                <input type="text" class="filter-input" value="<?= htmlspecialchars($classDetail['name']) ?>" disabled>
                                            </div>
                                        </div>
                                        <input type="hidden" name="randomid" value="<?= htmlspecialchars($randomid) ?>">
                                    </form>
                                </div>

                                <!-- Comments Table -->
                                <?php if (!empty($selectedSession) && !empty($selectedTerm) && !empty($students)): ?>
                                    <div class="comment-table-wrapper">
                                        <form method="POST" id="commentForm" onsubmit="showButtonLoading(this)">
                                            <table class="comment-table">
                                                <thead>
                                                    <tr class="section-header">
                                                        <th rowspan="2" style="vertical-align: middle; min-width:80px;">Student</th>
                                                        <th rowspan="2" style="vertical-align: middle; min-width:60px;">ID</th>
                                                        <th rowspan="2" style="vertical-align: middle; width:30px;">#</th>
                                                        <th colspan="2">Learning</th>
                                                        <th colspan="3">Subject Specific</th>
                                                        <th colspan="6">Ratings</th>
                                                    </tr>
                                                    <tr>
                                                        <th style="min-width:100px;">Strengths</th>
                                                        <th style="min-width:100px;">Targets</th>
                                                        <th style="min-width:100px;">Strengths</th>
                                                        <th style="min-width:100px;">Targets</th>
                                                        <th style="min-width:80px;">Remarks</th>
                                                        <th style="min-width:70px;">Att</th>
                                                        <th style="min-width:70px;">Punct</th>
                                                        <th style="min-width:70px;">Behav</th>
                                                        <th style="min-width:70px;">Effort</th>
                                                        <th style="min-width:70px;">Acad Prog</th>
                                                        <th style="min-width:70px;">Curr Achiev</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php
                                                    $sn = 1;
                                                    $studentIds = [];
                                                    foreach ($students as $student):
                                                        $studentIds[] = $student['id'];
                                                        $comments = $existingComments[$student['id']] ?? [];
                                                    ?>
                                                        <tr>
                                                            <td class="student-name-cell"><?= htmlspecialchars($student['first_name'] . ' ' . $student['last_name']) ?></td>
                                                            <td class="student-id-cell"><?= htmlspecialchars($student['student_id']) ?></td>
                                                            <td><?= $sn++ ?></td>

                                                            <td class="textarea-section">
                                                                <textarea class="comment-textarea" name="learning_strengths_<?= $student['id'] ?>" rows="2" placeholder="Learning strengths..."><?= htmlspecialchars($comments['learning_strengths'] ?? '') ?></textarea>
                                                            </td>

                                                            <td class="textarea-section">
                                                                <textarea class="comment-textarea" name="learning_targets_<?= $student['id'] ?>" rows="2" placeholder="Learning targets..."><?= htmlspecialchars($comments['learning_targets'] ?? '') ?></textarea>
                                                            </td>

                                                            <td class="textarea-section">
                                                                <textarea class="comment-textarea" name="subject_specific_strengths_<?= $student['id'] ?>" rows="2" placeholder="Subject strengths..."><?= htmlspecialchars($comments['subject_specific_strengths'] ?? '') ?></textarea>
                                                            </td>

                                                            <td class="textarea-section">
                                                                <textarea class="comment-textarea" name="subject_specific_target_<?= $student['id'] ?>" rows="2" placeholder="Subject target..."><?= htmlspecialchars($comments['subject_specific_target'] ?? '') ?></textarea>
                                                            </td>

                                                            <td class="textarea-section">
                                                                <textarea class="comment-textarea" name="remarks_<?= $student['id'] ?>" rows="2" placeholder="Remarks..."><?= htmlspecialchars($comments['remarks'] ?? '') ?></textarea>
                                                            </td>

                                                            <td class="dropdown-section">
                                                                <select class="comment-select" name="attendance_<?= $student['id'] ?>">
                                                                    <option value="">--</option>
                                                                    <?php foreach ($ratingOptions as $key => $label): ?>
                                                                        <option value="<?= $key ?>" <?= (($comments['attendance'] ?? '') == $key) ? 'selected' : '' ?>><?= $label ?></option>
                                                                    <?php endforeach; ?>
                                                                </select>
                                                            </td>

                                                            <td class="dropdown-section">
                                                                <select class="comment-select" name="punctuality_<?= $student['id'] ?>">
                                                                    <option value="">--</option>
                                                                    <?php foreach ($ratingOptions as $key => $label): ?>
                                                                        <option value="<?= $key ?>" <?= (($comments['punctuality'] ?? '') == $key) ? 'selected' : '' ?>><?= $label ?></option>
                                                                    <?php endforeach; ?>
                                                                </select>
                                                            </td>

                                                            <td class="dropdown-section">
                                                                <select class="comment-select" name="behaviour_<?= $student['id'] ?>">
                                                                    <option value="">--</option>
                                                                    <?php foreach ($ratingOptions as $key => $label): ?>
                                                                        <option value="<?= $key ?>" <?= (($comments['behaviour'] ?? '') == $key) ? 'selected' : '' ?>><?= $label ?></option>
                                                                    <?php endforeach; ?>
                                                                </select>
                                                            </td>

                                                            <td class="dropdown-section">
                                                                <select class="comment-select" name="effort_<?= $student['id'] ?>">
                                                                    <option value="">--</option>
                                                                    <?php foreach ($ratingOptions as $key => $label): ?>
                                                                        <option value="<?= $key ?>" <?= (($comments['effort'] ?? '') == $key) ? 'selected' : '' ?>><?= $label ?></option>
                                                                    <?php endforeach; ?>
                                                                </select>
                                                            </td>

                                                            <td class="dropdown-section">
                                                                <select class="comment-select" name="academic_progress_<?= $student['id'] ?>">
                                                                    <option value="">--</option>
                                                                    <?php foreach ($ratingOptions as $key => $label): ?>
                                                                        <option value="<?= $key ?>" <?= (($comments['academic_progress'] ?? '') == $key) ? 'selected' : '' ?>><?= $label ?></option>
                                                                    <?php endforeach; ?>
                                                                </select>
                                                            </td>

                                                            <td class="dropdown-section">
                                                                <select class="comment-select" name="curriculum_achievement_<?= $student['id'] ?>">
                                                                    <option value="">--</option>
                                                                    <?php foreach ($ratingOptions as $key => $label): ?>
                                                                        <option value="<?= $key ?>" <?= (($comments['curriculum_achievement'] ?? '') == $key) ? 'selected' : '' ?>><?= $label ?></option>
                                                                    <?php endforeach; ?>
                                                                </select>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>

                                            <!-- Hidden fields for bulk save -->
                                            <input type="hidden" name="student_ids" value="<?= implode(',', $studentIds) ?>">
                                            <input type="hidden" name="save_all_comments" value="1">

                                            <!-- Save Button -->
                                            <div class="save-footer">
                                                <button type="submit" id="saveBtn" class="btn btn-success">
                                                    <i class="fa fa-save"></i> Save All Comments
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
        // JAVASCRIPT FUNCTIONS
        // ============================================================================

        // Show loading state on button during form submission
        function showButtonLoading(form) {
            if (form) {
                const btn = form.querySelector('button[type="submit"]');
                if (btn && !btn.classList.contains('btn-loading')) {
                    btn.classList.add('btn-loading');
                    const originalText = btn.innerHTML;
                    btn.setAttribute('data-original-text', originalText);
                    btn.innerHTML = '<span class="spinner"></span> Saving...';

                    // Reset after 10 seconds in case form doesn't submit
                    setTimeout(function() {
                        if (btn.classList.contains('btn-loading')) {
                            btn.classList.remove('btn-loading');
                            btn.innerHTML = btn.getAttribute('data-original-text') || originalText;
                        }
                    }, 10000);
                }
            }
        }

        // Auto-resize textareas as user types
        document.addEventListener('DOMContentLoaded', function() {
            const textareas = document.querySelectorAll('.comment-textarea');
            textareas.forEach(function(textarea) {
                textarea.addEventListener('input', function() {
                    this.style.height = 'auto';
                    this.style.height = Math.min(this.scrollHeight, 80) + 'px';
                });
                // Trigger initial resize
                textarea.style.height = 'auto';
                textarea.style.height = Math.min(textarea.scrollHeight, 80) + 'px';
            });
        });

        // Modified row tracking
        document.querySelectorAll('.comment-textarea,.comment-select').forEach(function(el) {
            el.addEventListener('change', function() {
                this.closest('tr').classList.add('row-modified');
            });
        });

        // Dirty tracking for unsaved changes
        let dirty = false;
        document.querySelectorAll('.comment-textarea,.comment-select').forEach(function(el) {
            el.addEventListener('change', function() {
                dirty = true;
            });
        });

        window.addEventListener('beforeunload', function(e) {
            if (!dirty) return;
            e.preventDefault();
            e.returnValue = '';
        });

        document.getElementById('commentForm')?.addEventListener('submit', function() {
            dirty = false;
        });
    </script>
</body>

</html>