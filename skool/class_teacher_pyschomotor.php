<?php

/**
 * ============================================================================
 * CLASS TEACHER - PSYCHOMOTOR SKILLS
 * ============================================================================
 * - Admin sees ALL classes
 * - Class Teacher sees ONLY assigned classes
 * - Parents redirected to parent login
 * - Dropdown ratings with "Select All" button
 * - Minimal clicks workflow
 * Version: 4.0 (Fully Mobile Responsive)
 * ============================================================================
 */

require_once('../config.php');
require_once('inc.session-create.php');

$PageTitle = "Psychomotor Skills";
$FileName = 'class_teacher_pyschomotor.php';

// ============================================================================
// PARENT REDIRECT - Parents cannot access this page
// ============================================================================
if ($_SESSION['usertype'] == '2' || $_SESSION['usertype'] == 'parent') {
    redirect(PARENT_URL);
    exit;
}

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
$userType = $_SESSION['usertype'] ?? '';

// Get selected filters
$selectedClassRandomid = $_GET['randomid'] ?? '';
$selectedSession = $_GET['session'] ?? '';
$selectedTerm = $_GET['term_id'] ?? '';
$selectedPsychomotor = $_GET['phycomotor'] ?? '';
$action = $_GET['action'] ?? '';

if (isset($_SESSION['success']) && $_SESSION['success'] != "") {
    $stat['success'] = $_SESSION['success'];
    unset($_SESSION['success']);
}

if (isset($_SESSION['error']) && $_SESSION['error'] != "") {
    $stat['error'] = $_SESSION['error'];
    unset($_SESSION['error']);
}

// ============================================================================
// GET SELECTED DATA
// ============================================================================
$selectedClass = [];
if (!empty($selectedClassRandomid)) {
    $selectedClass = db_get_row(
        "SELECT * FROM school_class WHERE randomid = ? AND create_by_userid = ?",
        [$selectedClassRandomid, $create_by_userid]
    );
}

$selectedPsychomotorData = [];
if (!empty($selectedPsychomotor)) {
    $selectedPsychomotorData = db_get_row(
        "SELECT * FROM manage_phycomotor WHERE randomid = ? AND create_by_userid = ?",
        [$selectedPsychomotor, $create_by_userid]
    );
}

$sessionData = [];
if (!empty($selectedSession)) {
    $sessionData = db_get_row(
        "SELECT * FROM school_session WHERE id = ? AND create_by_userid = ?",
        [$selectedSession, $create_by_userid]
    );
}

$termData = [];
if (!empty($selectedTerm)) {
    $termData = db_get_row(
        "SELECT * FROM school_term WHERE id = ? AND create_by_userid = ?",
        [$selectedTerm, $create_by_userid]
    );
}

// ============================================================================
// GET CLASSES - BASED ON USER TYPE
// ============================================================================
$classes = [];
if ($isSchoolOwnerSession) {
    $classes = db_get_rows(
        "SELECT * FROM school_class WHERE create_by_userid = ? ORDER BY name ASC",
        [$create_by_userid]
    );
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
            $classes = db_get_rows(
                "SELECT * FROM school_class WHERE create_by_userid = ? AND id IN ($assignedClassIds) ORDER BY name ASC",
                [$create_by_userid]
            );
        }
    }
}

// ============================================================================
// GET FILTER DATA
// ============================================================================
$sessions = db_get_rows(
    "SELECT * FROM school_session WHERE create_by_userid = ? ORDER BY id DESC",
    [$create_by_userid]
);
$terms = db_get_rows(
    "SELECT * FROM school_term WHERE create_by_userid = ? ORDER BY id ASC",
    [$create_by_userid]
);
$psychomotorSkills = db_get_rows(
    "SELECT * FROM manage_phycomotor WHERE create_by_userid = ? ORDER BY id ASC",
    [$create_by_userid]
);

// ============================================================================
// GET STUDENTS AND EXISTING RATINGS
// ============================================================================
$students = [];
$existingRatings = [];

if (!empty($selectedClass['id']) && !empty($selectedSession) && !empty($selectedTerm) && !empty($selectedPsychomotorData['id'])) {
    $students = db_get_rows(
        "SELECT * FROM manage_student 
         WHERE class = ? AND session = ? AND term_id = ? AND create_by_userid = ? 
         ORDER BY first_name ASC",
        [$selectedClass['id'], $selectedSession, $selectedTerm, $create_by_userid]
    );

    if (!empty($students)) {
        foreach ($students as $student) {
            $rating = db_get_val(
                "SELECT pyschmotor FROM student_pyschomotor_class_teacher 
                 WHERE session_id = ? AND term_id = ? AND class_id = ? 
                 AND pyschmotor_id = ? AND student_id = ? AND create_by_userid = ?",
                [$selectedSession, $selectedTerm, $selectedClass['id'], $selectedPsychomotorData['id'], $student['id'], $create_by_userid]
            );
            $existingRatings[$student['id']] = $rating ?: '';
        }
    }
}

// ============================================================================
// SAVE RATINGS (BULK)
// ============================================================================
if (isset($_POST['save_ratings'])) {
    $studentIds = explode(',', $_POST['student_ids'] ?? '');
    $successCount = 0;
    $errorCount = 0;

    // First, delete existing ratings for this combination
    db_delete(
        "student_pyschomotor_class_teacher",
        "session_id = ? AND term_id = ? AND class_id = ? AND pyschmotor_id = ? AND create_by_userid = ?",
        [$selectedSession, $selectedTerm, $selectedClass['id'], $selectedPsychomotorData['id'], $create_by_userid]
    );

    // Insert new ratings
    foreach ($studentIds as $studentId) {
        if (empty($studentId)) continue;

        $rating = $_POST["rating_{$studentId}"] ?? '';

        if (!empty($rating)) {
            $result = db_insert("student_pyschomotor_class_teacher", [
                'session_id' => $selectedSession,
                'term_id' => $selectedTerm,
                'class_id' => $selectedClass['id'],
                'pyschmotor_id' => $selectedPsychomotorData['id'],
                'student_id' => $studentId,
                'pyschmotor' => $rating,
                'usertype' => $_SESSION['usertype'] ?? '',
                'userid' => $_SESSION['userid'] ?? 0,
                'create_by_usertype' => $create_by_usertype,
                'create_by_userid' => $create_by_userid,
                'randomid' => randomFix(15)
            ]);

            if ($result !== false) {
                $successCount++;
            } else {
                $errorCount++;
            }
        }
    }

    if ($successCount > 0) {
        $_SESSION['success'] = "$successCount student rating(s) saved successfully!";
    }
    if ($errorCount > 0) {
        $_SESSION['error'] = "$errorCount student(s) failed to save.";
    }

    // Redirect to refresh the page
    $redirectUrl = $FileName . "?action=input_score&randomid=" . urlencode($selectedClassRandomid) . "&session=" . urlencode($selectedSession) . "&term_id=" . urlencode($selectedTerm) . "&phycomotor=" . urlencode($selectedPsychomotor);
    redirect($redirectUrl);
    exit;
}

// Rating options for dropdown
$ratingOptions = [
    '' => '-- Select Rating --',
    'Poor' => '🔴 Poor',
    'Satisfactory' => '🟡 Satisfactory',
    'Good' => '🟢 Good',
    'Very Good' => '🔵 Very Good',
    'Excellent' => '🟣 Excellent'
];

// Get class name for display
$className = $selectedClass['name'] ?? 'Select a Class';
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

        .psychomotor-container {
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
        CLASS LIST PANEL - MOBILE FIRST
        ============================================================ */
        .classes-panel {
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

        .class-item .class-meta {
            font-size: 11px;
            color: #999;
            margin-top: 2px;
        }

        .class-item .class-arrow {
            margin-left: auto;
            color: #ccc;
            font-size: 14px;
        }

        /* ============================================================
        RATINGS PANEL - MOBILE FIRST
        ============================================================ */
        .ratings-panel {
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
        STUDENTS CARD - MOBILE FIRST
        ============================================================ */
        .students-card {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
            overflow: hidden;
        }

        .students-card .card-footer {
            padding: 12px 16px;
            background: #f8f9fa;
            border-top: 1px solid #eee;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        /* ============================================================
        SELECT ALL ROW - MOBILE FIRST
        ============================================================ */
        .select-all-row {
            background: #f8f9fa;
            padding: 12px 16px;
            border-bottom: 1px solid #eee;
            display: flex;
            flex-direction: column;
            gap: 10px;
            align-items: stretch;
        }

        .select-all-row .select-all-label {
            font-weight: 600;
            color: #333;
            font-size: 13px;
        }

        .select-all-row .select-all-dropdown {
            padding: 8px 12px;
            border: 2px solid #ddd;
            border-radius: 10px;
            font-size: 13px;
            width: 100%;
            background: white;
        }

        .select-all-row .apply-all-btn {
            background: #1B3058;
            color: white;
            border: none;
            padding: 10px 16px;
            border-radius: 10px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.2s;
            min-height: 44px;
        }

        .select-all-row .apply-all-btn:active {
            transform: scale(0.97);
        }

        .select-all-row .apply-all-btn:hover {
            background: #f21151;
        }

        .select-all-row .hint-text {
            font-size: 11px;
            color: #999;
            text-align: center;
        }

        /* ============================================================
        RATINGS TABLE - MOBILE FIRST
        ============================================================ */
        .table-wrapper {
            overflow-x: auto;
            padding: 10px 6px;
            -webkit-overflow-scrolling: touch;
        }

        .ratings-table {
            width: 100%;
            min-width: 400px;
            border-collapse: collapse;
            font-size: 12px;
        }

        .ratings-table th,
        .ratings-table td {
            padding: 8px 6px;
            text-align: left;
            border-bottom: 1px solid #eee;
            vertical-align: middle;
        }

        .ratings-table th {
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

        .ratings-table td {
            font-size: 12px;
        }

        .ratings-table .student-name {
            font-weight: 600;
            font-size: 13px;
        }

        .ratings-table .student-id {
            color: #999;
            font-size: 11px;
        }

        .ratings-table .rating-select {
            padding: 6px 8px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 12px;
            width: 100%;
            max-width: 140px;
            cursor: pointer;
            background: white;
            transition: all 0.2s;
            -webkit-appearance: none;
            appearance: none;
            background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 8px center;
            background-size: 14px;
        }

        .ratings-table .rating-select:focus {
            outline: none;
            border-color: #1B3058;
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
            .psychomotor-container {
                padding: 25px;
            }

            .two-column-layout {
                flex-direction: row;
                gap: 25px;
            }

            .classes-panel {
                width: 280px;
                flex-shrink: 0;
            }

            .class-list {
                max-height: 70vh;
            }

            .ratings-panel {
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

            .select-all-row {
                flex-direction: row;
                align-items: center;
                flex-wrap: wrap;
                padding: 12px 20px;
            }

            .select-all-row .select-all-dropdown {
                width: auto;
                min-width: 160px;
            }

            .select-all-row .apply-all-btn {
                width: auto;
                padding: 8px 20px;
                min-height: auto;
            }

            .select-all-row .hint-text {
                margin-left: auto;
                text-align: right;
            }

            .ratings-table {
                font-size: 13px;
                min-width: auto;
            }

            .ratings-table th,
            .ratings-table td {
                padding: 10px 12px;
            }

            .ratings-table th {
                font-size: 11px;
            }

            .ratings-table .student-name {
                font-size: 14px;
            }

            .ratings-table .rating-select {
                font-size: 13px;
                max-width: 160px;
                padding: 8px 12px;
            }

            .students-card .card-footer {
                flex-direction: row;
                justify-content: flex-end;
                padding: 15px 20px;
            }

            .students-card .card-footer .btn {
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
            .psychomotor-container {
                padding: 30px;
            }

            .classes-panel {
                width: 320px;
            }

            .ratings-table th,
            .ratings-table td {
                padding: 12px 16px;
            }
        }

        /* ============================================================
        RESPONSIVE - SMALL MOBILE (480px-)
        ============================================================ */
        @media (max-width: 480px) {
            .psychomotor-container {
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

            .ratings-table {
                font-size: 11px;
                min-width: 320px;
            }

            .ratings-table th,
            .ratings-table td {
                padding: 6px 4px;
            }

            .ratings-table th {
                font-size: 9px;
            }

            .ratings-table td {
                font-size: 11px;
            }

            .ratings-table .student-name {
                font-size: 12px;
            }

            .ratings-table .rating-select {
                font-size: 11px;
                max-width: 100px;
                padding: 4px 6px;
            }

            .select-all-row {
                padding: 10px 12px;
                gap: 8px;
            }

            .select-all-row .select-all-label {
                font-size: 12px;
            }

            .select-all-row .select-all-dropdown {
                font-size: 12px;
                padding: 6px 10px;
            }

            .select-all-row .apply-all-btn {
                font-size: 12px;
                padding: 8px 12px;
                min-height: 38px;
            }

            .students-card .card-footer {
                padding: 10px 12px;
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

            .classes-panel,
            .filter-card,
            .select-all-row,
            .card-footer,
            .btn,
            .no-print {
                display: none !important;
            }

            .students-card {
                box-shadow: none !important;
                border: 1px solid #ddd;
            }

            body {
                background: white;
            }

            .psychomotor-container {
                padding: 0;
            }

            .ratings-table th {
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
                <div class="psychomotor-container">

                    <!-- Page Header -->
                    <div class="page-header">
                        <h2><i class="fa fa-hand-paper-o"></i> <?= htmlspecialchars($PageTitle) ?></h2>
                        <p>Rate students on psychomotor skills (Handwriting, Drawing, Sports, etc.)</p>
                    </div>

                    <?= showMessage($stat) ?>

                    <div class="two-column-layout">

                        <!-- LEFT PANEL: Classes -->
                        <div class="classes-panel">
                            <div class="panel-header">
                                <i class="fa fa-graduation-cap"></i>
                                <?php if ($userType == '1' || $userType == '0'): ?>
                                    All Classes
                                <?php else: ?>
                                    My Classes
                                <?php endif; ?>
                                <span class="count-badge"><?= count($classes) ?></span>
                            </div>
                            <div class="class-list">
                                <?php if (!empty($classes)): ?>
                                    <?php foreach ($classes as $class): ?>
                                        <a href="?action=input_score&randomid=<?= urlencode($class['randomid']) ?><?= $selectedSession ? '&session=' . urlencode($selectedSession) : '' ?><?= $selectedTerm ? '&term_id=' . urlencode($selectedTerm) : '' ?><?= $selectedPsychomotor ? '&phycomotor=' . urlencode($selectedPsychomotor) : '' ?>"
                                            class="class-item <?= ($selectedClassRandomid == $class['randomid']) ? 'active' : '' ?>">
                                            <div class="class-icon">
                                                <i class="fa fa-book"></i>
                                            </div>
                                            <div>
                                                <div class="class-name"><?= htmlspecialchars($class['name']) ?></div>
                                                <div class="class-meta">Click to select</div>
                                            </div>
                                            <div class="class-arrow"><i class="fa fa-chevron-right"></i></div>
                                        </a>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="empty-state" style="padding: 30px 20px;">
                                        <i class="fa fa-folder-open"></i>
                                        <h4>No Classes Found</h4>
                                        <?php if ($userType == '1' || $userType == '0'): ?>
                                            <p>Please add classes in Configuration.</p>
                                        <?php else: ?>
                                            <p>No classes assigned to you. Please contact the administrator.</p>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- RIGHT PANEL: Ratings Entry -->
                        <div class="ratings-panel">

                            <?php if (!empty($selectedClass)): ?>

                                <!-- Filter Bar -->
                                <div class="filter-card">
                                    <form method="GET" action="" id="filterForm">
                                        <input type="hidden" name="action" value="input_score">
                                        <input type="hidden" name="randomid" value="<?= htmlspecialchars($selectedClassRandomid) ?>">
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
                                            <div class="filter-group">
                                                <label><i class="fa fa-hand-paper-o"></i> Skill</label>
                                                <select name="phycomotor" class="filter-select" onchange="this.form.submit()">
                                                    <option value="">-- Select --</option>
                                                    <?php foreach ($psychomotorSkills as $skill): ?>
                                                        <option value="<?= $skill['randomid'] ?>" <?= ($selectedPsychomotor == $skill['randomid']) ? 'selected' : '' ?>>
                                                            <?= htmlspecialchars($skill['phycomotor']) ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        </div>
                                    </form>
                                </div>

                                <?php if (!empty($selectedSession) && !empty($selectedTerm) && !empty($selectedPsychomotorData)): ?>

                                    <?php if (!empty($students)): ?>

                                        <form method="POST" id="ratingsForm">
                                            <input type="hidden" name="student_ids" id="studentIdsHidden" value="">

                                            <div class="students-card">
                                                <!-- Select All Row -->
                                                <div class="select-all-row">
                                                    <span class="select-all-label">
                                                        <i class="fa fa-magic"></i> Set all to:
                                                    </span>
                                                    <select id="selectAllRating" class="select-all-dropdown">
                                                        <option value="">-- Select Rating --</option>
                                                        <?php foreach ($ratingOptions as $value => $label): ?>
                                                            <?php if ($value !== ''): ?>
                                                                <option value="<?= $value ?>"><?= $label ?></option>
                                                            <?php endif; ?>
                                                        <?php endforeach; ?>
                                                    </select>
                                                    <button type="button" class="apply-all-btn" onclick="applyToAll()">
                                                        <i class="fa fa-check-circle"></i> Apply to All
                                                    </button>
                                                    <span class="hint-text">
                                                        <i class="fa fa-info-circle"></i> Select rating above
                                                    </span>
                                                </div>

                                                <!-- Table -->
                                                <div class="table-wrapper">
                                                    <table class="ratings-table">
                                                        <thead>
                                                            <tr>
                                                                <th style="width:40px;">#</th>
                                                                <th style="min-width:80px;">ID</th>
                                                                <th style="min-width:100px;">Student</th>
                                                                <th style="min-width:120px;">Rating</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <?php
                                                            $studentIdList = [];
                                                            $counter = 1;
                                                            foreach ($students as $student):
                                                                $studentIdList[] = $student['id'];
                                                                $currentRating = $existingRatings[$student['id']] ?? '';
                                                            ?>
                                                                <tr>
                                                                    <td><?= $counter++ ?></td>
                                                                    <td class="student-id"><?= htmlspecialchars($student['student_id']) ?></td>
                                                                    <td class="student-name"><?= htmlspecialchars($student['first_name'] . ' ' . $student['last_name']) ?></td>
                                                                    <td>
                                                                        <select name="rating_<?= $student['id'] ?>" class="rating-select" data-student-id="<?= $student['id'] ?>">
                                                                            <?php foreach ($ratingOptions as $value => $label): ?>
                                                                                <option value="<?= $value ?>" <?= ($currentRating == $value) ? 'selected' : '' ?>>
                                                                                    <?= $label ?>
                                                                                </option>
                                                                            <?php endforeach; ?>
                                                                        </select>
                                                                    </td>
                                                                </tr>
                                                            <?php endforeach; ?>
                                                        </tbody>
                                                    </table>
                                                </div>

                                                <!-- Footer -->
                                                <div class="card-footer">
                                                    <button type="submit" name="save_ratings" class="btn btn-success btn-block" onclick="prepareSubmit()">
                                                        <i class="fa fa-save"></i> Save All Ratings
                                                    </button>
                                                </div>
                                            </div>
                                        </form>

                                        <script>
                                            var studentIds = <?= json_encode($studentIdList) ?>;

                                            function prepareSubmit() {
                                                document.getElementById('studentIdsHidden').value = studentIds.join(',');
                                            }

                                            function applyToAll() {
                                                var selectAll = document.getElementById('selectAllRating');
                                                var selectedValue = selectAll.value;

                                                if (selectedValue === '') {
                                                    alert('Please select a rating to apply to all students.');
                                                    return;
                                                }

                                                var allSelects = document.querySelectorAll('.rating-select');
                                                allSelects.forEach(function(select) {
                                                    select.value = selectedValue;
                                                });

                                                var applyBtn = document.querySelector('.apply-all-btn');
                                                var originalText = applyBtn.innerHTML;
                                                applyBtn.innerHTML = '<i class="fa fa-check"></i> Applied!';
                                                setTimeout(function() {
                                                    applyBtn.innerHTML = originalText;
                                                }, 1500);
                                            }
                                        </script>

                                    <?php else: ?>
                                        <div class="alert alert-info">
                                            <i class="fa fa-info-circle"></i>
                                            <div>No students found in this class for the selected session and term.</div>
                                        </div>
                                    <?php endif; ?>

                                <?php elseif (!empty($selectedSession) && !empty($selectedTerm) && empty($selectedPsychomotor)): ?>
                                    <div class="alert alert-info">
                                        <i class="fa fa-hand-paper-o"></i>
                                        <div>Please select a psychomotor skill to continue.</div>
                                    </div>
                                <?php elseif (!empty($selectedSession) && empty($selectedTerm)): ?>
                                    <div class="alert alert-info">
                                        <i class="fa fa-tag"></i>
                                        <div>Please select a term to continue.</div>
                                    </div>
                                <?php elseif (empty($selectedSession)): ?>
                                    <div class="alert alert-info">
                                        <i class="fa fa-calendar"></i>
                                        <div>Please select a session to continue.</div>
                                    </div>
                                <?php endif; ?>

                            <?php else: ?>
                                <div class="alert alert-info">
                                    <i class="fa fa-folder-open"></i>
                                    <div>Please select a class from the left panel to begin.</div>
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
                    var skill = filterForm.querySelector('select[name="phycomotor"]');
                    if (session && term && skill && session.value && term.value && skill.value) {
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