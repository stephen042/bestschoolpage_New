<?php
/**
 * Remove Subject from Student
 *
 * Rebuilt page with full student selector, subject removal controls,
 * audit history modal, and undo workflow.
 */

require_once('../config.php');
require_once('inc.session-create.php');

$PageTitle = 'Remove Subject';
$FileName = 'removesubject.php';
$create_by_userid = (int)($_SESSION['create_by_userid'] ?? $_SESSION['userid'] ?? 0);

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
    <style>
        * { box-sizing: border-box; }
        .remove-subject-wrap {
            display: flex;
            gap: 24px;
            padding: 20px;
            min-height: calc(100vh - 120px);
        }
        .students-panel {
            width: 34%;
            background: #fff;
            border-radius: 14px;
            box-shadow: 0 4px 16px rgba(0,0,0,0.05);
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }
        .subjects-panel {
            width: 66%;
            background: #fff;
            border-radius: 14px;
            box-shadow: 0 4px 16px rgba(0,0,0,0.05);
            overflow: hidden;
        }
        .panel-head {
            padding: 18px 20px;
            border-bottom: 1px solid #eef1f6;
            background: #f8fafc;
        }
        .panel-head h3 {
            margin: 0;
            color: #1B3058;
            font-size: 20px;
            font-weight: 700;
        }
        .search-input {
            width: 100%;
            border: 1px solid #d9e0ea;
            border-radius: 28px;
            padding: 11px 14px;
            margin-top: 12px;
            font-size: 14px;
        }
        .students-list {
            padding: 10px;
            overflow-y: auto;
            max-height: calc(100vh - 250px);
        }
        .student-card {
            border: 1px solid #e8edf4;
            border-radius: 10px;
            padding: 12px;
            margin-bottom: 10px;
            cursor: pointer;
            transition: 0.2s ease;
            display: flex;
            gap: 10px;
            text-decoration: none;
            color: inherit;
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
        .student-photo {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            overflow: hidden;
            background: #e7edf7;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
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
            font-size: 12px;
            opacity: 0.9;
        }
        .subject-body {
            padding: 20px;
        }
        .student-meta {
            display: flex;
            gap: 16px;
            flex-wrap: wrap;
            margin-bottom: 16px;
            font-size: 13px;
            color: #4c5d75;
        }
        .action-row {
            display: flex;
            gap: 10px;
            align-items: center;
            flex-wrap: wrap;
            margin-bottom: 14px;
        }
        .btn {
            border: none;
            border-radius: 8px;
            padding: 10px 14px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
        }
        .btn-primary {
            background: #1B3058;
            color: #fff;
        }
        .btn-warning {
            background: #f39c12;
            color: #fff;
        }
        .btn-outline {
            background: #fff;
            border: 1px solid #d9e0ea;
            color: #1B3058;
        }
        .empty-box {
            border: 1px dashed #ced6e0;
            border-radius: 10px;
            padding: 24px;
            text-align: center;
            color: #6f7f95;
            background: #f8fafc;
        }
        .loading-box {
            text-align: center;
            padding: 25px;
            color: #6f7f95;
        }
        @media (max-width: 980px) {
            .remove-subject-wrap { flex-direction: column; }
            .students-panel, .subjects-panel { width: 100%; }
            .students-list { max-height: 380px; }
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
                <div class="students-panel">
                    <div class="panel-head">
                        <h3><i class="fa fa-users"></i> Students</h3>
                        <form method="GET" action="" style="margin:0;">
                            <?php if ($selectedRandomid !== ''): ?>
                                <input type="hidden" name="student" value="<?= htmlspecialchars($selectedRandomid) ?>">
                            <?php endif; ?>
                            <input
                                type="text"
                                name="search"
                                class="search-input"
                                placeholder="Search by name or ID..."
                                value="<?= htmlspecialchars($search) ?>"
                                onchange="this.form.submit()"
                            >
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
                                    <div>
                                        <div class="student-name"><?= htmlspecialchars(($student['first_name'] ?? '') . ' ' . ($student['last_name'] ?? '')) ?></div>
                                        <div class="student-sub">ID: <?= htmlspecialchars($student['student_id'] ?? '-') ?></div>
                                        <div class="student-sub">Class: <?= htmlspecialchars($student['class_name'] ?? 'N/A') ?></div>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="empty-box">No students found.</div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="subjects-panel">
                    <div class="panel-head">
                        <h3><i class="fa fa-book"></i> Remove Subjects</h3>
                    </div>

                    <div class="subject-body">
                        <?= showMessage($stat) ?>
                        <div id="jsMessage"></div>

                        <?php if (!empty($selectedStudent)): ?>
                            <div class="student-meta">
                                <div><strong>Student:</strong> <?= htmlspecialchars(($selectedStudent['first_name'] ?? '') . ' ' . ($selectedStudent['last_name'] ?? '')) ?></div>
                                <div><strong>Student ID:</strong> <?= htmlspecialchars($selectedStudent['student_id'] ?? '-') ?></div>
                                <div><strong>Class:</strong> <?= htmlspecialchars($selectedStudent['class_name'] ?? 'N/A') ?></div>
                            </div>

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

                            <div id="subjectTableWrap" class="loading-box">
                                <i class="fa fa-spinner fa-spin"></i> Loading subjects...
                            </div>
                        <?php else: ?>
                            <div class="empty-box">Select a student from the left panel to manage removed subjects.</div>
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
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title"><i class="fa fa-history"></i> Subject Removal History</h4>
            </div>
            <div class="modal-body">
                <div id="auditContent" style="min-height: 150px;"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
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
    } else {
        html = '<div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> ' + text + '</div>';
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
            subjectWrap.innerHTML = '<div class="empty-box">Failed to load subjects. Please refresh.</div>';
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

    if (window.jQuery && typeof window.jQuery.fn.modal === 'function') {
        window.jQuery('#auditModal').modal('show');
    } else {
        var modalEl = document.getElementById('auditModal');
        if (modalEl) {
            modalEl.style.display = 'block';
            modalEl.classList.add('in');
        }
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

document.addEventListener('click', function(e) {
    var target = e.target;
    if (target && target.classList.contains('undo-removal-btn')) {
        var auditId = target.getAttribute('data-audit-id');
        undoRemoval(auditId);
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
