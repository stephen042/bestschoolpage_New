<?php

/**
 * Remove Subject from Student
 *
 * Rebuilt page with full student selector, subject removal controls,
 * audit history modal, and undo workflow.
 * Version: 4.0 (Fully Mobile Responsive)
 */

require_once('../config.php');
require_once('inc.session-create.php');

$PageTitle = 'Remove Subject';
$FileName = 'removesubject.php';

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

$stat = [];
$search = trim($_GET['search'] ?? '');
$selectedRandomid = trim($_GET['student'] ?? '');

if (!empty($_SESSION['success'])) {
    $stat['success'] = $_SESSION['success'];
    unset($_SESSION['success']);
}

if (!empty($_SESSION['error'])) {
    $stat['error'] = $_SESSION['error'];
    unset($_SESSION['error']);
}

$searchSql = '';
$params = [$create_by_userid];
if ($search !== '') {
    $searchSql = ' AND (ms.first_name LIKE ? OR ms.last_name LIKE ? OR ms.student_id LIKE ?)';
    $like = '%' . $search . '%';
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
}

$students = db_get_rows(
    "SELECT ms.id, ms.randomid, ms.student_id, ms.first_name, ms.last_name, ms.class,
            ms.picture, sc.name AS class_name
     FROM manage_student ms
     LEFT JOIN school_class sc ON ms.class = sc.id
     WHERE ms.create_by_userid = ? $searchSql
     ORDER BY ms.first_name ASC, ms.last_name ASC",
    $params
);

if ($selectedRandomid === '' && !empty($students)) {
    $selectedRandomid = $students[0]['randomid'];
}

$selectedStudent = null;
if ($selectedRandomid !== '') {
    $selectedStudent = db_get_row(
        "SELECT ms.id, ms.randomid, ms.student_id, ms.first_name, ms.last_name, ms.class,
                ms.picture, sc.name AS class_name
         FROM manage_student ms
         LEFT JOIN school_class sc ON ms.class = sc.id
         WHERE ms.create_by_userid = ? AND ms.randomid = ?
         LIMIT 1",
        [$create_by_userid, $selectedRandomid]
    );
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

        .remove-subject-wrap {
            display: flex;
            flex-direction: column;
            gap: 20px;
            padding: 15px;
            min-height: calc(100vh - 120px);
        }

        /* ============================================================
        PANELS - MOBILE FIRST
        ============================================================ */
        .students-panel {
            width: 100%;
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        .subjects-panel {
            width: 100%;
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
            overflow: hidden;
        }

        /* ============================================================
        PANEL HEADERS - MOBILE FIRST
        ============================================================ */
        .panel-head {
            padding: 14px 16px;
            border-bottom: 1px solid #eef1f6;
            background: #f8fafc;
        }

        .panel-head h3 {
            margin: 0;
            color: #1B3058;
            font-size: 18px;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .panel-head h3 i {
            font-size: 18px;
        }

        .panel-head .badge {
            margin-left: auto;
            background: #1B3058;
            color: white;
            padding: 2px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 400;
        }

        /* ============================================================
        SEARCH - MOBILE FIRST
        ============================================================ */
        .search-input {
            width: 100%;
            border: 2px solid #e0e0e0;
            border-radius: 30px;
            padding: 10px 14px;
            margin-top: 10px;
            font-size: 14px;
            transition: all 0.2s;
        }

        .search-input:focus {
            outline: none;
            border-color: #1B3058;
            box-shadow: 0 0 0 3px rgba(27, 48, 88, 0.1);
        }

        /* ============================================================
        STUDENTS LIST - MOBILE FIRST
        ============================================================ */
        .students-list {
            padding: 10px;
            overflow-y: auto;
            max-height: 300px;
        }

        .student-card {
            border: 2px solid #e8edf4;
            border-radius: 12px;
            padding: 12px;
            margin-bottom: 8px;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
            color: inherit;
        }

        .student-card:active {
            transform: scale(0.98);
        }

        .student-card:hover {
            border-color: #1B3058;
            background: #f7faff;
        }

        .student-card.active {
            border-color: #1B3058;
            background: #1B3058;
            color: #fff;
        }

        .student-card.active .student-sub {
            color: rgba(255, 255, 255, 0.8);
        }

        .student-photo {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            overflow: hidden;
            background: #e7edf7;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            font-size: 18px;
            color: #1B3058;
        }

        .student-photo img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .student-name {
            font-size: 14px;
            font-weight: 700;
            margin-bottom: 2px;
        }

        .student-sub {
            font-size: 11px;
            opacity: 0.8;
            color: #666;
        }

        .student-card.active .student-sub {
            color: rgba(255, 255, 255, 0.8);
        }

        /* ============================================================
        SUBJECT BODY - MOBILE FIRST
        ============================================================ */
        .subject-body {
            padding: 16px;
        }

        /* ============================================================
        STUDENT META - MOBILE FIRST
        ============================================================ */
        .student-meta {
            display: flex;
            flex-direction: column;
            gap: 6px;
            margin-bottom: 14px;
            font-size: 13px;
            color: #4c5d75;
            background: #f8fafc;
            padding: 12px 14px;
            border-radius: 10px;
        }

        .student-meta strong {
            color: #1B3058;
        }

        /* ============================================================
        ACTION ROW - MOBILE FIRST
        ============================================================ */
        .action-row {
            display: flex;
            flex-direction: column;
            gap: 8px;
            align-items: stretch;
            margin-bottom: 14px;
        }

        .action-row .btn {
            width: 100%;
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

        .btn-warning {
            background: #f39c12;
            color: white;
        }

        .btn-warning:hover {
            background: #d68910;
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
        SUBJECT TABLE - MOBILE FIRST
        ============================================================ */
        .subject-table-wrap {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            margin: 0 -4px;
            padding: 0 4px;
        }

        .subject-table {
            width: 100%;
            min-width: 300px;
            border-collapse: collapse;
            font-size: 13px;
        }

        .subject-table th,
        .subject-table td {
            padding: 10px 8px;
            text-align: left;
            border-bottom: 1px solid #eee;
            vertical-align: middle;
        }

        .subject-table th {
            background: #f8f9fa;
            font-weight: 700;
            color: #1B3058;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .subject-table td {
            font-size: 13px;
        }

        .subject-table .subject-name {
            font-weight: 600;
        }

        .subject-table .status-badge {
            display: inline-block;
            padding: 2px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
        }

        .subject-table .status-badge.active {
            background: #d4edda;
            color: #155724;
        }

        .subject-table .status-badge.removed {
            background: #f8d7da;
            color: #721c24;
        }

        .subject-table input[type="checkbox"] {
            width: 18px;
            height: 18px;
            cursor: pointer;
            accent-color: #1B3058;
        }

        .subject-table .checkbox-cell {
            text-align: center;
        }

        /* ============================================================
        EMPTY BOX - MOBILE FIRST
        ============================================================ */
        .empty-box {
            border: 2px dashed #ced6e0;
            border-radius: 12px;
            padding: 30px 20px;
            text-align: center;
            color: #6f7f95;
            background: #f8fafc;
        }

        .empty-box i {
            font-size: 40px;
            color: #ddd;
            display: block;
            margin-bottom: 10px;
        }

        .empty-box h4 {
            color: #666;
            font-size: 16px;
            margin-bottom: 4px;
        }

        .empty-box p {
            font-size: 13px;
        }

        /* ============================================================
        LOADING BOX - MOBILE FIRST
        ============================================================ */
        .loading-box {
            text-align: center;
            padding: 30px;
            color: #6f7f95;
        }

        .loading-box i {
            font-size: 24px;
            margin-right: 8px;
        }

        /* ============================================================
        ALERTS - MOBILE FIRST
        ============================================================ */
        .alert {
            padding: 12px 16px;
            border-radius: 12px;
            margin-bottom: 14px;
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

        /* ============================================================
        MODAL - MOBILE FIRST
        ============================================================ */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .modal.in {
            display: flex;
        }

        .modal-dialog {
            width: 100%;
            max-width: 700px;
        }

        .modal-content {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
        }

        .modal-header {
            padding: 16px 20px;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #f8fafc;
        }

        .modal-header h4 {
            margin: 0;
            font-size: 18px;
            color: #1B3058;
        }

        .modal-header .close {
            background: none;
            border: none;
            font-size: 28px;
            cursor: pointer;
            color: #999;
            padding: 0 4px;
        }

        .modal-header .close:hover {
            color: #333;
        }

        .modal-body {
            padding: 20px;
            max-height: 60vh;
            overflow-y: auto;
        }

        .modal-footer {
            padding: 12px 20px;
            border-top: 1px solid #eee;
            text-align: right;
            background: #f8fafc;
        }

        .modal-footer .btn {
            min-height: 38px;
            padding: 8px 20px;
        }

        /* ============================================================
        RESPONSIVE - TABLET (768px+)
        ============================================================ */
        @media (min-width: 768px) {
            .remove-subject-wrap {
                flex-direction: row;
                gap: 24px;
                padding: 20px;
            }

            .students-panel {
                width: 35%;
                max-width: 400px;
            }

            .subjects-panel {
                flex: 1;
            }

            .students-list {
                max-height: calc(100vh - 250px);
            }

            .action-row {
                flex-direction: row;
                flex-wrap: wrap;
            }

            .action-row .btn {
                width: auto;
            }

            .student-meta {
                flex-direction: row;
                flex-wrap: wrap;
                gap: 16px;
                padding: 14px 18px;
            }

            .panel-head {
                padding: 18px 24px;
            }

            .panel-head h3 {
                font-size: 20px;
            }

            .subject-body {
                padding: 24px;
            }

            .subject-table {
                min-width: auto;
            }

            .subject-table th,
            .subject-table td {
                padding: 12px 14px;
            }

            .modal-dialog {
                max-width: 700px;
            }
        }

        /* ============================================================
        RESPONSIVE - DESKTOP (1024px+)
        ============================================================ */
        @media (min-width: 1024px) {
            .students-panel {
                width: 30%;
            }

            .remove-subject-wrap {
                padding: 25px;
            }
        }

        /* ============================================================
        RESPONSIVE - SMALL MOBILE (480px-)
        ============================================================ */
        @media (max-width: 480px) {
            .remove-subject-wrap {
                padding: 10px;
                gap: 12px;
            }

            .panel-head {
                padding: 12px 14px;
            }

            .panel-head h3 {
                font-size: 15px;
            }

            .subject-body {
                padding: 12px;
            }

            .student-card {
                padding: 10px;
            }

            .student-photo {
                width: 36px;
                height: 36px;
                font-size: 14px;
            }

            .student-name {
                font-size: 13px;
            }

            .student-sub {
                font-size: 10px;
            }

            .subject-table {
                font-size: 11px;
            }

            .subject-table th,
            .subject-table td {
                padding: 6px 4px;
            }

            .subject-table th {
                font-size: 9px;
            }

            .subject-table td {
                font-size: 11px;
            }

            .subject-table input[type="checkbox"] {
                width: 16px;
                height: 16px;
            }

            .subject-table .status-badge {
                font-size: 9px;
                padding: 1px 8px;
            }

            .btn {
                font-size: 12px;
                padding: 8px 14px;
                min-height: 38px;
            }

            .modal-header h4 {
                font-size: 16px;
            }

            .modal-body {
                padding: 14px;
            }
        }

        /* ============================================================
        PRINT STYLES
        ============================================================ */
        @media print {

            .students-panel,
            .action-row,
            .btn,
            .no-print {
                display: none !important;
            }

            .subjects-panel {
                width: 100% !important;
                box-shadow: none !important;
                border: 1px solid #ddd;
            }

            .remove-subject-wrap {
                padding: 0;
            }

            body {
                background: white;
            }

            .panel-head {
                background: #f8fafc !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .subject-table th {
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
                <div class="remove-subject-wrap">
                    <!-- LEFT: Students Panel -->
                    <div class="students-panel">
                        <div class="panel-head">
                            <h3>
                                <i class="fa fa-users"></i> Students
                                <span class="badge"><?= count($students) ?></span>
                            </h3>
                            <form method="GET" action="" style="margin:0;">
                                <?php if ($selectedRandomid !== ''): ?>
                                    <input type="hidden" name="student" value="<?= htmlspecialchars($selectedRandomid) ?>">
                                <?php endif; ?>
                                <input
                                    type="text"
                                    name="search"
                                    class="search-input"
                                    placeholder="🔍 Search by name or ID..."
                                    value="<?= htmlspecialchars($search) ?>"
                                    onchange="this.form.submit()">
                            </form>
                        </div>

                        <div class="students-list">
                            <?php if (!empty($students)): ?>
                                <?php foreach ($students as $student): ?>
                                    <?php
                                    $isActive = $selectedRandomid === $student['randomid'];
                                    $studentUrl = $FileName . '?student=' . urlencode($student['randomid']);
                                    if ($search !== '') {
                                        $studentUrl .= '&search=' . urlencode($search);
                                    }
                                    ?>
                                    <a href="<?= htmlspecialchars($studentUrl) ?>" class="student-card <?= $isActive ? 'active' : '' ?>">
                                        <div class="student-photo">
                                            <?php if (!empty($student['picture']) && file_exists('../uploads/' . $student['picture'])): ?>
                                                <img src="../uploads/<?= htmlspecialchars($student['picture']) ?>" alt="Student">
                                            <?php else: ?>
                                                <i class="fa fa-user"></i>
                                            <?php endif; ?>
                                        </div>
                                        <div style="flex:1; min-width:0;">
                                            <div class="student-name"><?= htmlspecialchars(($student['first_name'] ?? '') . ' ' . ($student['last_name'] ?? '')) ?></div>
                                            <div class="student-sub">ID: <?= htmlspecialchars($student['student_id'] ?? '-') ?></div>
                                            <div class="student-sub">Class: <?= htmlspecialchars($student['class_name'] ?? 'N/A') ?></div>
                                        </div>
                                    </a>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="empty-box">
                                    <i class="fa fa-user-slash"></i>
                                    <h4>No Students Found</h4>
                                    <p>Try adjusting your search or add a new student.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- RIGHT: Subjects Panel -->
                    <div class="subjects-panel">
                        <div class="panel-head">
                            <h3>
                                <i class="fa fa-book"></i> Remove Subjects
                            </h3>
                        </div>

                        <div class="subject-body">
                            <?= showMessage($stat) ?>
                            <div id="jsMessage"></div>

                            <?php if (!empty($selectedStudent)): ?>
                                <!-- Student Meta -->
                                <div class="student-meta">
                                    <div><strong>Student:</strong> <?= htmlspecialchars(($selectedStudent['first_name'] ?? '') . ' ' . ($selectedStudent['last_name'] ?? '')) ?></div>
                                    <div><strong>ID:</strong> <?= htmlspecialchars($selectedStudent['student_id'] ?? '-') ?></div>
                                    <div><strong>Class:</strong> <?= htmlspecialchars($selectedStudent['class_name'] ?? 'N/A') ?></div>
                                </div>

                                <!-- Action Row -->
                                <div class="action-row">
                                    <button type="button" class="btn btn-outline" onclick="toggleAll()">
                                        <i class="fa fa-check-square-o"></i> Toggle All
                                    </button>
                                    <button type="button" class="btn btn-primary" onclick="saveSubjectRemoval()">
                                        <i class="fa fa-save"></i> Save Changes
                                    </button>
                                    <button type="button" class="btn btn-warning" onclick="showAuditLog(<?= (int)$selectedStudent['id'] ?>)">
                                        <i class="fa fa-history"></i> View History
                                    </button>
                                </div>

                                <!-- Subject Table -->
                                <div id="subjectTableWrap" class="loading-box">
                                    <i class="fa fa-spinner fa-spin"></i> Loading subjects...
                                </div>
                            <?php else: ?>
                                <div class="empty-box">
                                    <i class="fa fa-hand-pointer-o"></i>
                                    <h4>Select a Student</h4>
                                    <p>Choose a student from the left panel to manage removed subjects.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Audit Log Modal -->
    <div id="auditModal" class="modal fade" role="dialog">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h4><i class="fa fa-history"></i> Subject Removal History</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div id="auditContent" style="min-height: 150px;"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <?php include('inc.js.php'); ?>
    <script>
        var selectedRandomid = '<?= !empty($selectedStudent['randomid']) ? htmlspecialchars($selectedStudent['randomid']) : '' ?>';
        var selectedStudentId = <?= !empty($selectedStudent['id']) ? (int)$selectedStudent['id'] : 0 ?>;

        function postForm(url, data, onSuccess, onError) {
            var formBody = [];
            for (var key in data) {
                if (!Object.prototype.hasOwnProperty.call(data, key)) continue;
                formBody.push(encodeURIComponent(key) + '=' + encodeURIComponent(data[key]));
            }

            var xhr = new XMLHttpRequest();
            xhr.open('POST', url, true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
            xhr.onreadystatechange = function() {
                if (xhr.readyState !== 4) return;
                if (xhr.status >= 200 && xhr.status < 300) {
                    if (typeof onSuccess === 'function') onSuccess(xhr.responseText);
                } else {
                    if (typeof onError === 'function') onError(xhr);
                }
            };
            xhr.send(formBody.join('&'));
        }

        function showJsMessage(type, text) {
            var html = '';
            if (type === 'success') {
                html = '<div class="alert alert-success"><i class="fa fa-check-circle"></i> ' + text + '</div>';
            } else if (type === 'error') {
                html = '<div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> ' + text + '</div>';
            } else {
                html = '<div class="alert alert-info"><i class="fa fa-info-circle"></i> ' + text + '</div>';
            }
            var messageEl = document.getElementById('jsMessage');
            if (messageEl) {
                messageEl.innerHTML = html;
            }
        }

        function loadStudentSubjects() {
            if (!selectedRandomid) {
                return;
            }

            var subjectWrap = document.getElementById('subjectTableWrap');
            if (subjectWrap) {
                subjectWrap.innerHTML = '<div class="loading-box"><i class="fa fa-spinner fa-spin"></i> Loading subjects...</div>';
            }

            postForm('ajaxing.php', {
                action: 'getstusubj',
                randomid: selectedRandomid
            }, function(response) {
                if (subjectWrap) {
                    subjectWrap.innerHTML = response;
                }
            }, function() {
                if (subjectWrap) {
                    subjectWrap.innerHTML = '<div class="empty-box"><i class="fa fa-exclamation-triangle"></i><h4>Failed to Load</h4><p>Please refresh and try again.</p></div>';
                }
            });
        }

        function toggleAll() {
            var checkboxes = document.querySelectorAll('#subjectTableWrap input[name="subjectlist[]"]');
            if (checkboxes.length === 0) return;

            var allChecked = true;
            for (var i = 0; i < checkboxes.length; i++) {
                if (!checkboxes[i].checked) {
                    allChecked = false;
                    break;
                }
            }

            for (var j = 0; j < checkboxes.length; j++) {
                checkboxes[j].checked = !allChecked;
            }
        }

        function saveSubjectRemoval() {
            if (!selectedRandomid) {
                showJsMessage('error', 'Please select a student first.');
                return;
            }

            var checked = document.querySelectorAll('#subjectTableWrap input[name="subjectlist[]"]:checked');
            var subjectIds = [];
            for (var i = 0; i < checked.length; i++) {
                subjectIds.push(checked[i].value);
            }

            if (!confirm('Save subject removal changes for this student?')) {
                return;
            }

            postForm('ajaxing.php', {
                action: 'updatesubject',
                randomid: selectedRandomid,
                subjectid: subjectIds.join(',')
            }, function(response) {
                if (response && response.indexOf('success') !== -1) {
                    showJsMessage('success', 'Subject removal updated successfully.');
                } else {
                    showJsMessage('success', 'Changes saved.');
                }
                loadStudentSubjects();
            }, function() {
                showJsMessage('error', 'Failed to save changes. Please try again.');
            });
        }

        function showAuditLog(studentId) {
            if (!studentId) return;

            var auditContent = document.getElementById('auditContent');
            if (auditContent) {
                auditContent.innerHTML = '<div class="loading-box"><i class="fa fa-spinner fa-spin"></i> Loading history...</div>';
            }

            // Show modal
            var modalEl = document.getElementById('auditModal');
            if (modalEl) {
                modalEl.style.display = 'flex';
                modalEl.classList.add('in');
            }

            postForm('ajaxing.php', {
                action: 'get_audit_log',
                student_id: studentId
            }, function(response) {
                if (auditContent) {
                    auditContent.innerHTML = response;
                }
            }, function() {
                if (auditContent) {
                    auditContent.innerHTML = '<div class="alert alert-danger">Failed to load history.</div>';
                }
            });
        }

        function undoRemoval(auditId) {
            if (!auditId) return;

            if (!confirm('Restore this subject?')) {
                return;
            }

            postForm('ajaxing.php', {
                action: 'undo_removal',
                audit_id: auditId
            }, function(response) {
                if (String(response).trim() === '1') {
                    showJsMessage('success', 'Subject restored successfully.');
                    if (selectedStudentId) {
                        showAuditLog(selectedStudentId);
                    }
                    loadStudentSubjects();
                } else {
                    showJsMessage('error', 'Unable to restore subject. Undo window may have expired.');
                }
            }, function() {
                showJsMessage('error', 'Restore failed. Please try again.');
            });
        }

        // Close modal on backdrop click
        document.addEventListener('click', function(e) {
            var modal = document.getElementById('auditModal');
            if (e.target === modal) {
                modal.style.display = 'none';
                modal.classList.remove('in');
            }
        });

        // Close modal with Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                var modal = document.getElementById('auditModal');
                if (modal && modal.classList.contains('in')) {
                    modal.style.display = 'none';
                    modal.classList.remove('in');
                }
            }
        });

        // Handle undo button clicks
        document.addEventListener('click', function(e) {
            var target = e.target;
            if (target && target.classList.contains('undo-removal-btn')) {
                var auditId = target.getAttribute('data-audit-id');
                undoRemoval(auditId);
            }
        });

        // Handle close button
        document.addEventListener('click', function(e) {
            var target = e.target;
            if (target && target.getAttribute('data-dismiss') === 'modal') {
                var modal = document.getElementById('auditModal');
                if (modal) {
                    modal.style.display = 'none';
                    modal.classList.remove('in');
                }
            }
        });

        document.addEventListener('DOMContentLoaded', function() {
            if (selectedRandomid) {
                loadStudentSubjects();
            }
        });
    </script>
</body>

</html>