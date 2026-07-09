<?php
/**
 * ============================================================================
 * SUBJECT SPECIFIC COMMENT - MODERN REDESIGN (FIXED)
 * ============================================================================
 * Description: Subject teachers can enter qualitative comments for all students
 * Features: All students visible at once, inline editing, bulk save
 * Version: 2.1 (PHP 8.x Compatible) - Fixed Teacher Subject Assignment
 * ============================================================================
 */
 
require_once('../config.php');
require_once('inc.session-create.php');
$PageTitle = "Subject Specific Comment";
$FileName = 'subject_specific_comment.php';

// ============================================================================
// INITIALIZATION
// ============================================================================
$stat = [];
$create_by_userid = (int)($_SESSION['create_by_userid'] ?? $_SESSION['userid'] ?? 0);
$create_by_usertype = $_SESSION['usertype'] ?? '';
$sessionUserId = (int)($_SESSION['userid'] ?? 0);
$sessionUsername = (string)($_SESSION['username'] ?? '');
$sessionEmail = (string)($_SESSION['email'] ?? '');
$isSchoolOwnerSession = ($sessionUserId > 0 && $sessionUserId === $create_by_userid);
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

function randomFix($length = 10) {
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
            middle_name
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
    <style>
        * { box-sizing: border-box; }
        .comment-container { max-width: 1600px; margin: 0 auto; padding: 20px; }
        .page-header { margin-bottom: 30px; }
        .page-header h2 { color: #1B3058; margin: 0; }
        .page-header p { color: #666; margin-top: 5px; }
        
        /* Filter Card */
        .filter-card { background: #fff; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); padding: 20px; margin-bottom: 25px; }
        .filter-grid { display: flex; gap: 15px; flex-wrap: wrap; align-items: flex-end; }
        .filter-group { flex: 1; min-width: 150px; }
        .filter-group label { display: block; font-size: 12px; font-weight: 600; color: #666; margin-bottom: 5px; text-transform: uppercase; }
        .filter-select { width: 100%; padding: 10px 12px; border: 1px solid #e0e0e0; border-radius: 10px; font-size: 14px; }
        .filter-input { width: 100%; padding: 10px 12px; border: 1px solid #e0e0e0; border-radius: 10px; font-size: 14px; background: #f8f9fa; }
        
        /* Buttons */
        .btn { padding: 10px 20px; border: none; border-radius: 10px; cursor: pointer; font-weight: 600; transition: all 0.2s; display: inline-flex; align-items: center; gap: 8px; text-decoration: none; }
        .btn-primary { background: #1B3058; color: white; }
        .btn-primary:hover { background: #f21151; transform: translateY(-2px); }
        .btn-success { background: #28a745; color: white; }
        .btn-success:hover { background: #218838; transform: translateY(-2px); }
        
        /* Two Column Layout */
        .two-column-layout { display: flex; gap: 25px; flex-wrap: wrap; }
        .subject-list-panel { flex: 1; min-width: 280px; background: #fff; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); overflow: hidden; }
        .comment-panel { flex: 4; min-width: 600px; }
        .panel-header { padding: 15px 20px; background: #f8f9fa; border-bottom: 1px solid #eee; font-weight: 600; }
        .subject-list { max-height: 600px; overflow-y: auto; }
        .subject-item { padding: 15px 20px; border-bottom: 1px solid #f0f0f0; cursor: pointer; transition: all 0.2s; text-decoration: none; display: block; }
        .subject-item:hover { background: #f8f9ff; }
        .subject-item.active { background: #1B3058; color: white; }
        .subject-item.active small { color: rgba(255,255,255,0.7); }
        .subject-item small { display: block; font-size: 11px; color: #999; margin-top: 5px; }
        
        /* Comments Table */
        .comment-table-wrapper { overflow-x: auto; background: #fff; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); }
        .comment-table { width: 100%; border-collapse: collapse; }
        .comment-table th, .comment-table td { padding: 12px; border: 1px solid #e0e0e0; vertical-align: top; }
        .comment-table th { background: #f8f9fa; font-weight: 600; color: #1B3058; position: sticky; top: 0; font-size: 13px; }
        .comment-table td { background: #fff; }
        .student-name-cell { font-weight: 600; background: #fafafa; min-width: 120px; }
        
        /* Form Elements Inside Table */
        .comment-textarea { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 8px; font-size: 12px; resize: vertical; font-family: inherit; }
        .comment-textarea:focus { outline: none; border-color: #1B3058; }
        .comment-select { width: 100%; padding: 6px 8px; border: 1px solid #ddd; border-radius: 8px; font-size: 12px; background: white; }
        .comment-select:focus { outline: none; border-color: #1B3058; }
        
        /* Section Headers */
        .section-header { background: #e8eef5; font-weight: 700; font-size: 13px; }
        .textarea-section { min-width: 200px; }
        .dropdown-section { min-width: 120px; }
        
        /* Alert Messages */
        .alert { padding: 12px 18px; border-radius: 12px; margin-bottom: 20px; }
        .alert-success { background: #d4edda; color: #155724; border-left: 4px solid #28a745; }
        .alert-danger { background: #f8d7da; color: #721c24; border-left: 4px solid #dc3545; }
        .alert-info { background: #d1ecf1; color: #0c5460; border-left: 4px solid #17a2b8; }
        .alert-warning { background: #fff3cd; color: #856404; border-left: 4px solid #ffc107; }
        
        /* Loading Spinner */
        .spinner { display: inline-block; width: 16px; height: 16px; border: 2px solid #fff; border-top-color: transparent; border-radius: 50%; animation: spin 0.8s linear infinite; }
        @keyframes spin { to { transform: rotate(360deg); } }
        .btn-loading { opacity: 0.7; pointer-events: none; }
        
        /* Empty State */
        .empty-state { text-align: center; padding: 50px; color: #999; }
        .empty-state i { font-size: 48px; color: #ccc; margin-bottom: 15px; display: block; }

        .row-modified {
            background: #fffbe6 !important;
            transition: .3s;
        }
        
        @media (max-width: 1000px) { 
            .two-column-layout { flex-direction: column; } 
            .comment-table th, .comment-table td { padding: 8px; font-size: 11px; }
            .comment-textarea { font-size: 11px; }
        }

        .save-footer {
            position: sticky;
            bottom: 0;
            background: #fff;
            padding: 15px;
            border-top: 1px solid #ddd;
            text-align: right;
            z-index: 50;
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
                        <div class="panel-header"><i class="fa fa-book"></i> My Subjects</div>
                        <div class="subject-list">
                            <?php if (!empty($teacherSubjects)): ?>
                                <?php foreach ($teacherSubjects as $subject): ?>
                                    <a href="?randomid=<?= urlencode($subject['randomid']) ?><?= $selectedSession ? '&session='.urlencode($selectedSession) : '' ?><?= $selectedTerm ? '&term_id='.urlencode($selectedTerm) : '' ?>" class="subject-item <?= ($randomid == $subject['randomid']) ? 'active' : '' ?>">
                                        <i class="fa fa-chalkboard-teacher"></i> <?= htmlspecialchars($subject['subject']) ?>
                                        <small><i class="fa fa-graduation-cap"></i> <?= htmlspecialchars($subject['class_name'] ?? 'N/A') ?></small>
                                    </a>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="subject-item">No subjects assigned to you</div>
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
                                                <option value="">-- Select Session --</option>
                                                <?php foreach($sessions as $s): ?>
                                                    <option value="<?= $s['id'] ?>" <?= ($selectedSession == $s['id']) ? 'selected' : '' ?>><?= htmlspecialchars($s['session']) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="filter-group">
                                            <label><i class="fa fa-tag"></i> Term</label>
                                            <select name="term_id" class="filter-select" required onchange="document.getElementById('filterForm').submit()">
                                                <option value="">-- Select Term --</option>
                                                <?php foreach($terms as $t): ?>
                                                    <option value="<?= $t['id'] ?>" <?= ($selectedTerm == $t['id']) ? 'selected' : '' ?>><?= htmlspecialchars($t['term']) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="filter-group">
                                            <label><i class="fa fa-book"></i> Subject</label>
                                            <input type="text" class="filter-input" value="<?= htmlspecialchars($subjectDetail['subject']) ?>" disabled>
                                        </div>
                                        <div class="filter-group">
                                            <label><i class="fa fa-users"></i> Class</label>
                                            <input type="text" class="filter-input" value="<?= htmlspecialchars($classDetail['name']) ?>" disabled>
                                        </div>
                                    </div>
                                    <input type="hidden" name="randomid" value="<?= htmlspecialchars($randomid) ?>">
                                </form>
                            </div>

                            <!-- Comments Table -->
                            <?php if (!empty($selectedSession) && !empty($selectedTerm) && !empty($students)): ?>
                                <form method="POST" id="commentForm" onsubmit="showButtonLoading(this)">
                                    <div class="comment-table-wrapper">
                                        <table class="comment-table">
                                            <thead>
                                                <tr class="section-header">
                                                    <th rowspan="2" style="vertical-align: middle;">Student Name</th>
                                                    <th rowspan="2" style="vertical-align: middle;">Student ID</th>
                                                    <th rowspan="2" style="vertical-align: middle;">SN</th>
                                                    <th colspan="2">Learning</th>
                                                    <th colspan="3">Subject Specific</th>
                                                    <th colspan="6">Ratings</th>
                                                </tr>
                                                <tr>
                                                    <th>Strengths</th>
                                                    <th>Targets</th>
                                                    <th>Strengths</th>
                                                    <th>Targets</th>
                                                    <th>Remarks</th>
                                                    <th>Attendance</th>
                                                    <th>Punctuality</th>
                                                    <th>Behaviour</th>
                                                    <th>Effort</th>
                                                    <th>Academic<br>Progress</th>
                                                    <th>Curriculum<br>Achievement</th>
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
                                                        <td><?= htmlspecialchars($student['student_id']) ?></td>
                                                        <td><?= $sn++ ?></td>
                                                        <!-- Learning Strengths -->
                                                        <td class="textarea-section">
                                                            <textarea class="comment-textarea" name="learning_strengths_<?= $student['id'] ?>" rows="3" placeholder="Learning strengths..."><?= htmlspecialchars($comments['learning_strengths'] ?? '') ?></textarea>
                                                        </td>
                                                        
                                                        <!-- Learning Targets -->
                                                        <td class="textarea-section">
                                                            <textarea class="comment-textarea" name="learning_targets_<?= $student['id'] ?>" rows="3" placeholder="Learning targets..."><?= htmlspecialchars($comments['learning_targets'] ?? '') ?></textarea>
                                                        </td>
                                                        
                                                        <!-- Subject Specific Strengths -->
                                                        <td class="textarea-section">
                                                            <textarea class="comment-textarea" name="subject_specific_strengths_<?= $student['id'] ?>" rows="3" placeholder="Subject specific strengths..."><?= htmlspecialchars($comments['subject_specific_strengths'] ?? '') ?></textarea>
                                                        </td>
                                                        
                                                        <!-- Subject Specific Target -->
                                                        <td class="textarea-section">
                                                            <textarea class="comment-textarea" name="subject_specific_target_<?= $student['id'] ?>" rows="3" placeholder="Subject specific target..."><?= htmlspecialchars($comments['subject_specific_target'] ?? '') ?></textarea>
                                                        </td>

                                                        <td class="textarea-section">
                                                            <textarea class="comment-textarea" name="remarks_<?= $student['id'] ?>" rows="3" placeholder="General remarks..."><?= htmlspecialchars($comments['remarks'] ?? '') ?></textarea>
                                                        </td>
                                                        
                                                        <!-- Dropdown Ratings -->
                                                        <td class="dropdown-section">
                                                            <select class="comment-select" name="attendance_<?= $student['id'] ?>">
                                                                <option value="">-- Select --</option>
                                                                <?php foreach ($ratingOptions as $key => $label): ?>
                                                                    <option value="<?= $key ?>" <?= (($comments['attendance'] ?? '') == $key) ? 'selected' : '' ?>><?= $label ?></option>
                                                                <?php endforeach; ?>
                                                            </select>
                                                        </td>
                                                        
                                                        <td class="dropdown-section">
                                                            <select class="comment-select" name="punctuality_<?= $student['id'] ?>">
                                                                <option value="">-- Select --</option>
                                                                <?php foreach ($ratingOptions as $key => $label): ?>
                                                                    <option value="<?= $key ?>" <?= (($comments['punctuality'] ?? '') == $key) ? 'selected' : '' ?>><?= $label ?></option>
                                                                <?php endforeach; ?>
                                                            </select>
                                                        </td>
                                                        
                                                        <td class="dropdown-section">
                                                            <select class="comment-select" name="behaviour_<?= $student['id'] ?>">
                                                                <option value="">-- Select --</option>
                                                                <?php foreach ($ratingOptions as $key => $label): ?>
                                                                    <option value="<?= $key ?>" <?= (($comments['behaviour'] ?? '') == $key) ? 'selected' : '' ?>><?= $label ?></option>
                                                                <?php endforeach; ?>
                                                            </select>
                                                        </td>
                                                        
                                                        <td class="dropdown-section">
                                                            <select class="comment-select" name="effort_<?= $student['id'] ?>">
                                                                <option value="">-- Select --</option>
                                                                <?php foreach ($ratingOptions as $key => $label): ?>
                                                                    <option value="<?= $key ?>" <?= (($comments['effort'] ?? '') == $key) ? 'selected' : '' ?>><?= $label ?></option>
                                                                <?php endforeach; ?>
                                                            </select>
                                                        </td>
                                                        
                                                        <td class="dropdown-section">
                                                            <select class="comment-select" name="academic_progress_<?= $student['id'] ?>">
                                                                <option value="">-- Select --</option>
                                                                <?php foreach ($ratingOptions as $key => $label): ?>
                                                                    <option value="<?= $key ?>" <?= (($comments['academic_progress'] ?? '') == $key) ? 'selected' : '' ?>><?= $label ?></option>
                                                                <?php endforeach; ?>
                                                            </select>
                                                        </td>
                                                        
                                                        <td class="dropdown-section">
                                                            <select class="comment-select" name="curriculum_achievement_<?= $student['id'] ?>">
                                                                <option value="">-- Select --</option>
                                                                <?php foreach ($ratingOptions as $key => $label): ?>
                                                                    <option value="<?= $key ?>" <?= (($comments['curriculum_achievement'] ?? '') == $key) ? 'selected' : '' ?>><?= $label ?></option>
                                                                <?php endforeach; ?>
                                                            </select>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                    
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
                            <?php elseif (!empty($selectedSession) && !empty($selectedTerm) && empty($students)): ?>
                                <div class="alert alert-warning">
                                    <i class="fa fa-exclamation-triangle"></i> 
                                    No students found for this class. Please add students first.
                                </div>
                            <?php elseif (!empty($selectedSession) && empty($selectedTerm)): ?>
                                <div class="alert alert-info">
                                    <i class="fa fa-info-circle"></i> 
                                    Please select a term to continue.
                                </div>
                            <?php elseif (empty($selectedSession)): ?>
                                <div class="alert alert-info">
                                    <i class="fa fa-info-circle"></i> 
                                    Please select a session and term to continue.
                                </div>
                            <?php endif; ?>
                        <?php else: ?>
                            <div class="alert alert-info">
                                <i class="fa fa-info-circle"></i> 
                                Please select a subject from the left panel.
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
            this.style.height = Math.min(this.scrollHeight, 120) + 'px';
        });
        // Trigger initial resize
        textarea.style.height = 'auto';
        textarea.style.height = Math.min(textarea.scrollHeight, 120) + 'px';
    });
});

// Additional functionality for modified rows and dirty tracking
document.querySelectorAll('.comment-textarea').forEach(function(el) {
    function resize() {
        el.style.height = 'auto';
        el.style.height = el.scrollHeight + 'px';
    }
    resize();
    el.addEventListener('input', resize);
});

document.querySelectorAll('.comment-textarea,.comment-select').forEach(function(el) {
    el.addEventListener('change', function() {
        this.closest('tr').classList.add('row-modified');
    });
});

document.getElementById('commentForm').addEventListener('submit', function() {
    let btn = document.getElementById('saveBtn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Saving...';
});

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

document.getElementById('commentForm').addEventListener('submit', function() {
    dirty = false;
});
</script>
</body>
</html>