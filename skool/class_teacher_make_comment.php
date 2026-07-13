<?php

/**
 * Class Teacher Make Comment - Modern PHP 8.x
 * Add comments/remarks for students with position and total scores
 * Version: 4.0 (Fully Mobile Responsive)
 */

require_once('../config.php');
require_once('inc.session-create.php');

$PageTitle = "Make Comment";
$FileName = 'class_teacher_make_comment.php';

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
$currentPage = max(1, (int)($_GET['page'] ?? 1));
$perPage = 50;

if (isset($_SESSION['success']) && $_SESSION['success'] != "") {
    $stat['success'] = $_SESSION['success'];
    unset($_SESSION['success']);
}

// ============================================================================
// GET CLASS DETAILS
// ============================================================================
$classDetail = [];
if (!empty($randomid)) {
    $classDetail = db_get_row("SELECT * FROM school_class WHERE randomid = ? AND create_by_userid = ?", [$randomid, $create_by_userid]);
}

// ============================================================================
// SAVE COMMENTS
// ============================================================================
if (isset($_POST['submitcomment']) && isset($_POST['student_id'])) {
    $redirectPage = max(1, (int)($_POST['page'] ?? $currentPage));
    foreach ($_POST['student_id'] as $key => $studentId) {
        $primaryId = $_POST['primaryid'][$key] ?? '';
        $noOfSubject = $_POST['no_of_subject'][$key] ?? 0;
        $totalScore = $_POST['totalScore'][$key] ?? 0;
        $position = $_POST['position'][$key] ?? 0;
        $comments = $_POST['comments'][$key] ?? '';

        $data = [
            'usertype' => $_SESSION['usertype'] ?? '',
            'userid' => $_SESSION['userid'] ?? 0,
            'create_by_usertype' => $create_by_usertype,
            'create_by_userid' => $create_by_userid,
            'student_id' => $studentId,
            'no_of_subject' => $noOfSubject,
            'totalScore' => $totalScore,
            'position' => $position,
            'comments' => $comments,
        ];

        if (empty($primaryId)) {
            db_insert("clas_teacher_make_comment", $data);
        } else {
            db_update("clas_teacher_make_comment", $data, "id = ?", [$primaryId]);
        }
    }
    $_SESSION['success'] = "Comments saved successfully!";
    redirect($FileName . "?randomid=" . $randomid . "&session=" . $selectedSession . "&term_id=" . $selectedTerm . "&page=" . $redirectPage);
    exit;
}

// ============================================================================
// GET CLASSES FOR SIDEBAR
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

// Get sessions and terms for filters
$sessions = db_get_rows("SELECT * FROM school_session WHERE create_by_userid = ? ORDER BY id DESC", [$create_by_userid]);
$terms = db_get_rows("SELECT * FROM school_term WHERE create_by_userid = ? ORDER BY id DESC", [$create_by_userid]);

// Get students and calculate data
$students = [];
$studentScores = [];
$studentComments = [];
$studentPositions = [];
$totalSubjects = 0;
$totalStudents = 0;
$totalPages = 1;

if (!empty($classDetail['id']) && !empty($selectedSession) && !empty($selectedTerm)) {
    // Get subjects count for this class
    $subjects = db_get_rows("SELECT id FROM school_subject WHERE class_id = ? AND create_by_userid = ?", [$classDetail['id'], $create_by_userid]);
    $totalSubjects = count($subjects);

    // Count students for pagination
    $totalStudents = (int) db_get_val(
        "SELECT COUNT(*) FROM manage_student WHERE class = ? AND session = ? AND term_id = ? AND create_by_userid = ?",
        [$classDetail['id'], $selectedSession, $selectedTerm, $create_by_userid]
    );

    $totalPages = max(1, (int) ceil($totalStudents / $perPage));
    if ($currentPage > $totalPages) {
        $currentPage = $totalPages;
    }
    $offset = ($currentPage - 1) * $perPage;

    // Get current page students only
    $students = db_get_rows(
        "SELECT id, student_id, first_name, last_name FROM manage_student WHERE class = ? AND session = ? AND term_id = ? AND create_by_userid = ? ORDER BY first_name ASC LIMIT $perPage OFFSET $offset",
        [$classDetail['id'], $selectedSession, $selectedTerm, $create_by_userid]
    );

    // Build total scores for the whole class to keep positions accurate across pages.
    $scoreRows = db_get_rows(
        "SELECT ms.id AS student_id, COALESCE(SUM(isc.score), 0) AS total_score
         FROM manage_student ms
         LEFT JOIN input_score_class_teacher isc
           ON isc.student_id = ms.id
          AND isc.class_id = ?
          AND isc.session_id = ?
          AND isc.term_id = ?
          AND isc.create_by_userid = ?
         WHERE ms.class = ?
           AND ms.session = ?
           AND ms.term_id = ?
           AND ms.create_by_userid = ?
         GROUP BY ms.id
         ORDER BY total_score DESC, ms.first_name ASC, ms.last_name ASC",
        [
            $classDetail['id'],
            $selectedSession,
            $selectedTerm,
            $create_by_userid,
            $classDetail['id'],
            $selectedSession,
            $selectedTerm,
            $create_by_userid
        ]
    );

    foreach ($scoreRows as $row) {
        $studentScores[(int)$row['student_id']] = (float)$row['total_score'];
    }

    // Sort and calculate positions
    arsort($studentScores);
    $position = 1;
    $prevScore = null;
    $tiePosition = 1;
    $studentPositions = [];

    foreach ($studentScores as $studentId => $score) {
        if ($prevScore === null || $score != $prevScore) {
            $studentPositions[$studentId] = $position;
            $tiePosition = $position;
        } else {
            $studentPositions[$studentId] = $tiePosition;
        }
        $position++;
        $prevScore = $score;
    }

    if (!empty($students)) {
        $studentIds = array_column($students, 'id');
        $studentPlaceholders = implode(',', array_fill(0, count($studentIds), '?'));

        // Bulk fetch existing comments for visible students only.
        $commentRows = db_get_rows(
            "SELECT id, student_id, comments
             FROM clas_teacher_make_comment
             WHERE create_by_userid = ?
               AND student_id IN ($studentPlaceholders)",
            array_merge([$create_by_userid], $studentIds)
        );

        foreach ($commentRows as $commentRow) {
            $studentComments[(int)$commentRow['student_id']] = $commentRow;
        }
    }
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

        .comment-container {
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
        .class-list-panel {
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
        COMMENT CARD - MOBILE FIRST
        ============================================================ */
        .comment-card {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
            overflow: hidden;
        }

        .comment-card .card-header {
            padding: 14px 18px;
            background: linear-gradient(135deg, #1B3058, #2a4780);
            color: white;
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .comment-card .card-header .title {
            font-weight: 600;
            font-size: 15px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .comment-card .card-header .subtitle {
            font-size: 12px;
            opacity: 0.8;
            font-weight: 400;
        }

        .comment-card .card-body {
            padding: 12px 8px;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        .comment-card .card-footer {
            padding: 12px 16px;
            background: #f8f9fa;
            border-top: 1px solid #eee;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        /* ============================================================
        DATA TABLE - MOBILE FIRST
        ============================================================ */
        .data-table {
            width: 100%;
            min-width: 700px;
            border-collapse: collapse;
            font-size: 12px;
        }

        .data-table th,
        .data-table td {
            padding: 8px 6px;
            text-align: center;
            border: 1px solid #e0e0e0;
            vertical-align: middle;
        }

        .data-table th {
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

        .data-table td {
            font-size: 12px;
        }

        .data-table .student-name-cell {
            text-align: left;
            font-weight: 600;
            font-size: 13px;
        }

        .data-table .student-id-cell {
            color: #999;
            font-size: 11px;
        }

        .data-table textarea {
            width: 100%;
            padding: 6px 8px;
            border: 2px solid #e0e0e0;
            border-radius: 6px;
            resize: vertical;
            min-height: 50px;
            font-size: 12px;
            font-family: inherit;
            transition: all 0.2s;
        }

        .data-table textarea:focus {
            outline: none;
            border-color: #1B3058;
            box-shadow: 0 0 0 3px rgba(27, 48, 88, 0.1);
        }

        .data-table .position-badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
            background: #1B3058;
            color: white;
        }

        .data-table .position-badge.position-1 {
            background: linear-gradient(135deg, #FFD700, #FFA500);
            color: #333;
        }

        .data-table .position-badge.position-2 {
            background: linear-gradient(135deg, #C0C0C0, #A9A9A9);
            color: #333;
        }

        .data-table .position-badge.position-3 {
            background: linear-gradient(135deg, #CD7F32, #B87333);
            color: white;
        }

        .data-table .total-cell {
            font-weight: 700;
            color: #2e7d32;
        }

        .data-table .average-cell {
            font-weight: 600;
        }

        /* ============================================================
        TABLE META & PAGINATION - MOBILE FIRST
        ============================================================ */
        .table-meta {
            display: flex;
            flex-direction: column;
            gap: 10px;
            align-items: center;
            margin: 12px 0 0;
            color: #666;
            font-size: 13px;
        }

        .pagination {
            display: flex;
            gap: 6px;
            align-items: center;
            flex-wrap: wrap;
            justify-content: center;
        }

        .pagination a,
        .pagination span {
            padding: 6px 12px;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            text-decoration: none;
            color: #1B3058;
            background: #fff;
            font-size: 13px;
            min-height: 36px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .pagination .active {
            background: #1B3058;
            color: #fff;
            border-color: #1B3058;
        }

        .pagination .disabled {
            color: #aaa;
            cursor: not-allowed;
        }

        .pagination a:active {
            transform: scale(0.95);
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
            .comment-container {
                padding: 25px;
            }

            .two-column-layout {
                flex-direction: row;
                gap: 25px;
            }

            .class-list-panel {
                width: 280px;
                flex-shrink: 0;
            }

            .class-list {
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

            .filter-actions {
                flex: 0 0 auto;
            }

            .filter-actions .btn {
                flex: 0 0 auto;
                padding: 10px 24px;
            }

            .data-table {
                font-size: 13px;
                min-width: auto;
            }

            .data-table th,
            .data-table td {
                padding: 10px 12px;
            }

            .data-table th {
                font-size: 11px;
            }

            .data-table textarea {
                min-height: 60px;
                font-size: 13px;
            }

            .comment-card .card-header {
                flex-direction: row;
                justify-content: space-between;
                align-items: center;
                padding: 16px 24px;
            }

            .comment-card .card-body {
                padding: 16px 20px;
            }

            .comment-card .card-footer {
                flex-direction: row;
                justify-content: flex-end;
                padding: 15px 24px;
            }

            .comment-card .card-footer .btn {
                width: auto;
            }

            .table-meta {
                flex-direction: row;
                justify-content: space-between;
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

            .class-list-panel {
                width: 320px;
            }

            .data-table th,
            .data-table td {
                padding: 12px 16px;
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

            .data-table {
                font-size: 10px;
                min-width: 550px;
            }

            .data-table th,
            .data-table td {
                padding: 5px 3px;
            }

            .data-table th {
                font-size: 8px;
            }

            .data-table td {
                font-size: 10px;
            }

            .data-table .student-name-cell {
                font-size: 11px;
            }

            .data-table textarea {
                min-height: 35px;
                font-size: 10px;
                padding: 4px 6px;
            }

            .data-table .position-badge {
                font-size: 9px;
                padding: 2px 6px;
            }

            .comment-card .card-header .title {
                font-size: 13px;
            }

            .comment-card .card-header .subtitle {
                font-size: 10px;
            }

            .btn {
                font-size: 12px;
                padding: 8px 14px;
                min-height: 38px;
            }

            .pagination a,
            .pagination span {
                padding: 4px 8px;
                font-size: 11px;
                min-height: 30px;
            }
        }

        /* ============================================================
        PRINT STYLES
        ============================================================ */
        @media print {

            .class-list-panel,
            .filter-card,
            .card-footer,
            .btn,
            .no-print,
            .pagination,
            .table-meta {
                display: none !important;
            }

            .comment-card {
                box-shadow: none !important;
                border: 1px solid #ddd;
            }

            .comment-card .card-header {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            body {
                background: white;
            }

            .comment-container {
                padding: 0;
            }

            .data-table th {
                background: #f8f9fa !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .data-table .position-badge {
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
                <div class="comment-container">
                    <div class="page-header">
                        <h2><i class="fa fa-commenting-o"></i> <?= htmlspecialchars($PageTitle) ?></h2>
                        <p>Add end-of-term comments and remarks for students</p>
                    </div>

                    <?= showMessage($stat) ?>

                    <div class="two-column-layout">
                        <!-- LEFT: Class List -->
                        <div class="class-list-panel">
                            <div class="panel-header">
                                <i class="fa fa-graduation-cap"></i> Select Class
                                <span class="count-badge"><?= count($allClasses) ?></span>
                            </div>
                            <div class="class-list">
                                <?php if (!empty($allClasses)): ?>
                                    <?php foreach ($allClasses as $class): ?>
                                        <a href="?randomid=<?= urlencode($class['randomid']) ?><?= $selectedSession ? '&session=' . urlencode($selectedSession) : '' ?><?= $selectedTerm ? '&term_id=' . urlencode($selectedTerm) : '' ?>&page=1"
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

                        <!-- RIGHT: Comments Panel -->
                        <div class="comment-panel">
                            <?php if (!empty($classDetail)): ?>
                                <!-- Filters -->
                                <div class="filter-card">
                                    <form method="GET" action="" id="filterForm">
                                        <input type="hidden" name="randomid" value="<?= htmlspecialchars($randomid) ?>">
                                        <input type="hidden" name="page" value="1">
                                        <div class="filter-grid">
                                            <div class="filter-group">
                                                <label><i class="fa fa-calendar"></i> Session</label>
                                                <select name="session" class="filter-select" required>
                                                    <option value="">-- Select Session --</option>
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
                                                    <option value="">-- Select Term --</option>
                                                    <?php foreach ($terms as $t): ?>
                                                        <option value="<?= $t['id'] ?>" <?= ($selectedTerm == $t['id']) ? 'selected' : '' ?>>
                                                            <?= htmlspecialchars($t['term']) ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="filter-actions">
                                                <button type="submit" class="btn btn-primary btn-block">
                                                    <i class="fa fa-filter"></i> Load Students
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                </div>

                                <!-- Students Table -->
                                <?php if (!empty($selectedSession) && !empty($selectedTerm)): ?>
                                    <?php if (!empty($students)): ?>
                                        <div class="comment-card">
                                            <form method="POST" id="commentForm">
                                                <input type="hidden" name="page" value="<?= (int)$currentPage ?>">

                                                <div class="card-header">
                                                    <div class="title">
                                                        <i class="fa fa-users"></i>
                                                        <?= htmlspecialchars($classDetail['name']) ?>
                                                    </div>
                                                    <div class="subtitle">
                                                        <i class="fa fa-info-circle"></i>
                                                        <?= count($students) ?> student(s) · <?= $totalPages ?> page(s)
                                                    </div>
                                                </div>

                                                <div class="card-body">
                                                    <table class="data-table" id="commentTable">
                                                        <thead>
                                                            <tr>
                                                                <th style="min-width:70px;">ID</th>
                                                                <th style="min-width:100px;">First Name</th>
                                                                <th style="min-width:100px;">Last Name</th>
                                                                <th style="min-width:40px;">Subj</th>
                                                                <th style="min-width:60px;">Total</th>
                                                                <th style="min-width:60px;">Avg</th>
                                                                <th style="min-width:60px;">Pos</th>
                                                                <th style="min-width:150px;">Comment / Remark</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <?php foreach ($students as $student):
                                                                $totalScore = $studentScores[$student['id']] ?? 0;
                                                                $average = ($totalSubjects > 0) ? round($totalScore / $totalSubjects, 2) : 0;
                                                                $position = $studentPositions[$student['id']] ?? '-';
                                                                $positionSuffix = '';
                                                                $positionClass = '';
                                                                if ($position == 1) {
                                                                    $positionSuffix = 'st';
                                                                    $positionClass = 'position-1';
                                                                } elseif ($position == 2) {
                                                                    $positionSuffix = 'nd';
                                                                    $positionClass = 'position-2';
                                                                } elseif ($position == 3) {
                                                                    $positionSuffix = 'rd';
                                                                    $positionClass = 'position-3';
                                                                } else {
                                                                    $positionSuffix = 'th';
                                                                }

                                                                $existingComment = $studentComments[$student['id']] ?? null;
                                                            ?>
                                                                <tr>
                                                                    <input type="hidden" name="student_id[]" value="<?= $student['id'] ?>">
                                                                    <input type="hidden" name="primaryid[]" value="<?= $existingComment['id'] ?? '' ?>">
                                                                    <input type="hidden" name="no_of_subject[]" value="<?= $totalSubjects ?>">
                                                                    <input type="hidden" name="totalScore[]" value="<?= $totalScore ?>">
                                                                    <input type="hidden" name="position[]" value="<?= $position ?>">

                                                                    <td class="student-id-cell"><?= htmlspecialchars($student['student_id']) ?></td>
                                                                    <td class="student-name-cell"><?= htmlspecialchars($student['first_name']) ?></td>
                                                                    <td class="student-name-cell"><?= htmlspecialchars($student['last_name']) ?></td>
                                                                    <td><?= $totalSubjects ?></td>
                                                                    <td class="total-cell"><?= $totalScore ?></td>
                                                                    <td class="average-cell"><?= $average ?></td>
                                                                    <td>
                                                                        <span class="position-badge <?= $positionClass ?>">
                                                                            <?= $position ?><?= $positionSuffix ?>
                                                                        </span>
                                                                    </td>
                                                                    <td>
                                                                        <textarea name="comments[]" rows="2" placeholder="Enter comment/remark..."><?= htmlspecialchars($existingComment['comments'] ?? '') ?></textarea>
                                                                    </td>
                                                                </tr>
                                                            <?php endforeach; ?>
                                                        </tbody>
                                                    </table>
                                                    <?php
                                                    $startNo = ($totalStudents > 0) ? (($currentPage - 1) * $perPage + 1) : 0;
                                                    $endNo = min($currentPage * $perPage, $totalStudents);
                                                    $baseParams = [
                                                        'randomid' => $randomid,
                                                        'session' => $selectedSession,
                                                        'term_id' => $selectedTerm
                                                    ];
                                                    ?>
                                                    <div class="table-meta">
                                                        <div>Showing <?= $startNo ?>-<?= $endNo ?> of <?= (int)$totalStudents ?> students</div>
                                                        <div class="pagination">
                                                            <?php if ($currentPage > 1): ?>
                                                                <a href="?<?= http_build_query(array_merge($baseParams, ['page' => $currentPage - 1])) ?>">
                                                                    <i class="fa fa-chevron-left"></i> Previous
                                                                </a>
                                                            <?php else: ?>
                                                                <span class="disabled"><i class="fa fa-chevron-left"></i> Previous</span>
                                                            <?php endif; ?>

                                                            <span class="active"><?= (int)$currentPage ?> / <?= (int)$totalPages ?></span>

                                                            <?php if ($currentPage < $totalPages): ?>
                                                                <a href="?<?= http_build_query(array_merge($baseParams, ['page' => $currentPage + 1])) ?>">
                                                                    Next <i class="fa fa-chevron-right"></i>
                                                                </a>
                                                            <?php else: ?>
                                                                <span class="disabled">Next <i class="fa fa-chevron-right"></i></span>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="card-footer">
                                                    <button type="submit" name="submitcomment" class="btn btn-success btn-block">
                                                        <i class="fa fa-save"></i> Save All Comments
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    <?php else: ?>
                                        <div class="alert alert-info">
                                            <i class="fa fa-info-circle"></i>
                                            <div>No students found in this class for the selected session and term.</div>
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
                                        <div>Please select a session and term to continue.</div>
                                    </div>
                                <?php endif; ?>
                            <?php else: ?>
                                <div class="alert alert-info">
                                    <i class="fa fa-info-circle"></i>
                                    <div>Please select a class from the left panel.</div>
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
                item.addEventListener('click', function(e) {
                    var current = document.querySelector('.class-item.active');
                    if (current) {
                        current.classList.remove('active');
                    }
                    // The active class will be applied on page load via PHP
                });
            });
        });

        // Auto-resize textareas
        document.addEventListener('DOMContentLoaded', function() {
            var textareas = document.querySelectorAll('.data-table textarea');
            textareas.forEach(function(textarea) {
                textarea.addEventListener('input', function() {
                    this.style.height = 'auto';
                    this.style.height = this.scrollHeight + 'px';
                });
            });
        });
    </script>
</body>

</html>