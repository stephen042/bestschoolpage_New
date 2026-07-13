<?php
/**
 * Principal Remarks - Modern PHP 8.x
 * Add and manage principal remarks for students
 */

require_once('../config.php');
require_once('inc.session-create.php');

$PageTitle = "Principal Remarks";
$FileName = 'princple_remark.php';

// ============================================================================
// FIX: USE CORRECT USER IDENTIFICATION (Same as dashboard.php)
// ============================================================================
// Use the same method as class_teacher_roll_call_bulk.php
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

// Also get the usertype correctly
$create_by_usertype = $_SESSION['usertype'] ?? '';

// ============================================================================
// INITIALIZATION
// ============================================================================
$stat = [];
$randomid = $_GET['randomid'] ?? '';
$action = $_GET['action'] ?? '';
$session = $_GET['session'] ?? '';
$term_id = $_GET['term_id'] ?? '';
$currentPage = max(1, (int)($_GET['page'] ?? 1));
$perPage = 50;

if (isset($_SESSION['success']) && $_SESSION['success'] != "") {
    $stat['success'] = $_SESSION['success'];
    unset($_SESSION['success']);
}

// ============================================================================
// SAVE/UPDATE PRINCIPAL REMARKS
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
            db_insert("principle_remarks", $data);
        } else {
            db_update("principle_remarks", $data, "id = ?", [$primaryId]);
        }
    }
    $_SESSION['success'] = "Remarks saved successfully";
    redirect($FileName . '?randomid=' . $randomid . '&session=' . $session . '&term_id=' . $term_id . '&page=' . $redirectPage);
}

// ============================================================================
// GET CLASS DETAILS
// ============================================================================
$classDetail = [];
if (!empty($randomid)) {
    $classDetail = db_get_row("SELECT * FROM school_class WHERE randomid = ? AND create_by_userid = ?", [$randomid, $create_by_userid]);
}

// Get all classes for sidebar
$allClasses = db_get_rows("SELECT * FROM school_class WHERE create_by_userid = ? ORDER BY name ASC", [$create_by_userid]);

// Get sessions and terms for filters
$sessions = db_get_rows("SELECT * FROM school_session WHERE create_by_userid = ? ORDER BY id DESC", [$create_by_userid]);
$terms = db_get_rows("SELECT * FROM school_term WHERE create_by_userid = ? ORDER BY id DESC", [$create_by_userid]);

// Get students and calculate positions
$students = [];
$studentScores = [];
$studentRemarks = [];
$studentPositions = [];
$totalSubjects = 0;
$totalStudents = 0;
$totalPages = 1;

if (!empty($classDetail['id']) && !empty($session) && !empty($term_id)) {
    // Subject count for average calculation
    $totalSubjects = (int) db_get_val(
        "SELECT COUNT(*) FROM school_subject WHERE class_id = ? AND create_by_userid = ?",
        [$classDetail['id'], $create_by_userid]
    );

    // Student count for server-side pagination
    $totalStudents = (int) db_get_val(
        "SELECT COUNT(*) FROM manage_student WHERE class = ? AND session = ? AND term_id = ? AND create_by_userid = ?",
        [$classDetail['id'], $session, $term_id, $create_by_userid]
    );

    $totalPages = max(1, (int) ceil($totalStudents / $perPage));
    if ($currentPage > $totalPages) {
        $currentPage = $totalPages;
    }
    $offset = ($currentPage - 1) * $perPage;

    // Load only current page students
    $students = db_get_rows(
        "SELECT id, student_id, first_name, last_name
         FROM manage_student
         WHERE class = ? AND session = ? AND term_id = ? AND create_by_userid = ?
         ORDER BY first_name ASC
         LIMIT $perPage OFFSET $offset",
        [$classDetail['id'], $session, $term_id, $create_by_userid]
    );

    // Class-wide total scores (single query) to preserve accurate positions across pages
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
        [$classDetail['id'], $session, $term_id, $create_by_userid, $classDetail['id'], $session, $term_id, $create_by_userid]
    );

    foreach ($scoreRows as $row) {
        $studentScores[(int)$row['student_id']] = (float)$row['total_score'];
    }

    arsort($studentScores);
    $position = 1;
    $prevScore = null;
    $tiePosition = 1;

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

        $remarkRows = db_get_rows(
            "SELECT id, student_id, comments
             FROM principle_remarks
             WHERE create_by_userid = ?
               AND student_id IN ($studentPlaceholders)",
            array_merge([$create_by_userid], $studentIds)
        );

        foreach ($remarkRows as $remarkRow) {
            $studentRemarks[(int)$remarkRow['student_id']] = $remarkRow;
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <?php include('inc.meta.php'); ?>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <style>
        * { box-sizing: border-box; }
        .remarks-container { max-width: 1400px; margin: 0 auto; padding: 20px; }
        .remarks-card { background: #fff; border-radius: 24px; box-shadow: 0 10px 40px rgba(0,0,0,0.08); overflow: hidden; margin-bottom: 30px; }
        .remarks-header { background: linear-gradient(135deg, #1B3058 0%, #2a4780 100%); color: white; padding: 20px 25px; }
        .remarks-header h2 { margin: 0; font-size: 22px; font-weight: 600; }
        .remarks-header p { margin: 5px 0 0; opacity: 0.8; font-size: 13px; }
        .two-column-layout { display: flex; gap: 25px; }
        .sidebar { width: 280px; flex-shrink: 0; }
        .main-content { flex: 1; }
        .class-list { background: #fff; border-radius: 16px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); overflow: hidden; }
        .class-item { padding: 15px 20px; border-bottom: 1px solid #f0f0f0; cursor: pointer; transition: all 0.2s; display: flex; align-items: center; gap: 12px; }
        .class-item:hover { background: #f8f9ff; }
        .class-item.active { background: #1B3058; color: white; }
        .class-item.active .class-name { color: white; }
        .class-icon { width: 40px; height: 40px; background: #e8eef5; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-weight: bold; color: #1B3058; }
        .class-item.active .class-icon { background: rgba(255,255,255,0.2); color: white; }
        .class-name { font-weight: 600; font-size: 15px; }
        .filter-bar { background: #fff; border-radius: 16px; padding: 20px; margin-bottom: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); display: flex; gap: 15px; flex-wrap: wrap; align-items: flex-end; }
        .filter-group { flex: 1; min-width: 150px; }
        .filter-group label { display: block; font-size: 12px; font-weight: 600; color: #666; margin-bottom: 5px; text-transform: uppercase; }
        .filter-select { width: 100%; padding: 10px 12px; border: 1px solid #e0e0e0; border-radius: 10px; font-size: 14px; background: #fff; }
        .filter-btn { background: #1B3058; color: white; border: none; padding: 10px 24px; border-radius: 10px; cursor: pointer; font-weight: 600; }
        .filter-btn:hover { background: #f21151; }
        .data-table { width: 100%; border-collapse: collapse; }
        .data-table th, .data-table td { padding: 12px; text-align: left; border-bottom: 1px solid #f0f0f0; vertical-align: top; }
        .data-table th { background: #f8f9fa; font-weight: 600; color: #1B3058; position: sticky; top: 0; }
        .data-table textarea { width: 100%; padding: 8px; border: 1px solid #e0e0e0; border-radius: 8px; font-size: 13px; resize: vertical; }
        .data-table textarea:focus { outline: none; border-color: #1B3058; }
        .position-badge { display: inline-block; background: #1B3058; color: white; padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: 600; }
        .position-1 { background: linear-gradient(135deg, #FFD700, #FFA500); color: #333; }
        .position-2 { background: linear-gradient(135deg, #C0C0C0, #A9A9A9); }
        .position-3 { background: linear-gradient(135deg, #CD7F32, #B87333); }
        .btn-submit { background: #28a745; color: white; border: none; padding: 12px 30px; border-radius: 40px; cursor: pointer; font-weight: 600; margin-top: 20px; }
        .btn-submit:hover { background: #218838; transform: translateY(-2px); }
        .empty-state { text-align: center; padding: 60px; color: #999; }
        .alert { padding: 14px 18px; border-radius: 12px; margin-bottom: 20px; }
        .alert-success { background: #d4edda; color: #155724; border-left: 4px solid #28a745; }
        .table-meta { display: flex; justify-content: space-between; gap: 10px; align-items: center; margin: 12px 0 0; color: #666; font-size: 13px; flex-wrap: wrap; }
        .pagination { display: flex; gap: 8px; align-items: center; }
        .pagination a, .pagination span { padding: 6px 10px; border: 1px solid #e0e0e0; border-radius: 8px; text-decoration: none; color: #1B3058; background: #fff; }
        .pagination .active { background: #1B3058; color: #fff; border-color: #1B3058; }
        .pagination .disabled { color: #aaa; }
        @media (max-width: 900px) { .two-column-layout { flex-direction: column; } .sidebar { width: 100%; } .filter-bar { flex-direction: column; } .data-table { display: block; overflow-x: auto; } }
    </style>
</head>
<body>
<div id="wrapper">
    <?php include('inc.header.php'); ?>
    <?php include('inc.sideleft.php'); ?>
    <div class="content-page">
        <div class="content">
            <div class="remarks-container">
                <div style="margin-bottom: 20px;">
                    <h2 style="margin:0; color:#1B3058;">📝 <?= $PageTitle ?></h2>
                    <p style="color:#666; margin-top:5px;">Add end-of-term remarks for students</p>
                </div>

                <?= showMessage($stat) ?>

                <div class="two-column-layout">
                    <!-- LEFT SIDEBAR - CLASSES -->
                    <div class="sidebar">
                        <div class="remarks-card" style="margin-bottom: 0;">
                            <div class="remarks-header" style="padding: 15px 20px;">
                                <h2 style="font-size: 16px;"><i class="fa fa-graduation-cap"></i> Select Class</h2>
                            </div>
                            <div class="class-list">
                                <?php foreach ($allClasses as $class): ?>
                                    <a href="?randomid=<?= urlencode($class['randomid']) ?>&session=<?= urlencode($session) ?>&term_id=<?= urlencode($term_id) ?>&page=1" style="text-decoration: none;">
                                        <div class="class-item <?= ($randomid == $class['randomid']) ? 'active' : '' ?>">
                                            <div class="class-icon"><i class="fa fa-book"></i></div>
                                            <div class="class-name"><?= htmlspecialchars($class['name']) ?></div>
                                        </div>
                                    </a>
                                <?php endforeach; ?>
                                <?php if (empty($allClasses)): ?>
                                    <div class="empty-state" style="padding: 30px;">No classes found</div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- RIGHT MAIN CONTENT -->
                    <div class="main-content">
                        <?php if (!empty($classDetail)): ?>
                            <div class="remarks-card">
                                <div class="remarks-header">
                                    <h2><i class="fa fa-pencil-square-o"></i> <?= htmlspecialchars($classDetail['name']) ?> - Principal Remarks</h2>
                                    <p>Select session and term to view students</p>
                                </div>
                                <div style="padding: 20px;">
                                    <!-- Filter Bar -->
                                    <form method="GET" action="" class="filter-bar">
                                        <input type="hidden" name="randomid" value="<?= $randomid ?>">
                                        <input type="hidden" name="page" value="1">
                                        <div class="filter-group">
                                            <label><i class="fa fa-calendar"></i> Session</label>
                                            <select name="session" class="filter-select" required>
                                                <option value="">Select Session</option>
                                                <?php foreach ($sessions as $s): ?>
                                                    <option value="<?= $s['id'] ?>" <?= ($session == $s['id']) ? 'selected' : '' ?>><?= htmlspecialchars($s['session']) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="filter-group">
                                            <label><i class="fa fa-flag"></i> Term</label>
                                            <select name="term_id" class="filter-select" required>
                                                <option value="">Select Term</option>
                                                <?php foreach ($terms as $t): ?>
                                                    <option value="<?= $t['id'] ?>" <?= ($term_id == $t['id']) ? 'selected' : '' ?>><?= htmlspecialchars($t['term']) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="filter-group">
                                            <button type="submit" class="filter-btn"><i class="fa fa-search"></i> Load Students</button>
                                        </div>
                                    </form>

                                    <!-- Students Table -->
                                    <?php if (!empty($session) && !empty($term_id)): ?>
                                        <?php if (!empty($students)): ?>
                                            <form method="post">
                                                <input type="hidden" name="page" value="<?= (int)$currentPage ?>">
                                                <div style="overflow-x: auto;">
                                                    <table class="data-table">
                                                        <thead>
                                                            <tr>
                                                                <th>Student ID</th>
                                                                <th>First Name</th>
                                                                <th>Last Name</th>
                                                                <th>No. of Subjects</th>
                                                                <th>Total Score</th>
                                                                <th>Average</th>
                                                                <th>Position</th>
                                                                <th>Principal's Comment</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <?php 
                                                            foreach ($students as $student):
                                                                $totalScore = $studentScores[$student['id']] ?? 0;
                                                                $average = ($totalSubjects > 0) ? round($totalScore / $totalSubjects, 2) : 0;
                                                                $position = $studentPositions[$student['id']] ?? '-';
                                                                $positionSuffix = '';
                                                                if ($position == 1) $positionSuffix = 'st';
                                                                elseif ($position == 2) $positionSuffix = 'nd';
                                                                elseif ($position == 3) $positionSuffix = 'rd';
                                                                else $positionSuffix = 'th';

                                                                $existingRemark = $studentRemarks[$student['id']] ?? null;
                                                            ?>
                                                                <tr>
                                                                    <td><input type="hidden" name="student_id[]" value="<?= $student['id'] ?>">
                                                                        <input type="hidden" name="primaryid[]" value="<?= $existingRemark['id'] ?? '' ?>">
                                                                        <input type="hidden" name="no_of_subject[]" value="<?= $totalSubjects ?>">
                                                                        <input type="hidden" name="totalScore[]" value="<?= $totalScore ?>">
                                                                        <input type="hidden" name="position[]" value="<?= $position ?>">
                                                                        <?= htmlspecialchars($student['student_id']) ?>
                                                                    </td>
                                                                    <td><?= htmlspecialchars($student['first_name']) ?></td>
                                                                    <td><?= htmlspecialchars($student['last_name']) ?></td>
                                                                    <td><?= $totalSubjects ?></td>
                                                                    <td><?= $totalScore ?></td>
                                                                    <td><?= $average ?></td>
                                                                    <td><span class="position-badge <?= ($position == 1) ? 'position-1' : (($position == 2) ? 'position-2' : (($position == 3) ? 'position-3' : '')) ?>"><?= $position ?><?= $positionSuffix ?></span></td>
                                                                    <td><textarea name="comments[]" rows="2" placeholder="Add principal's remark..."><?= htmlspecialchars($existingRemark['comments'] ?? '') ?></textarea></td>
                                                                </tr>
                                                            <?php endforeach; ?>
                                                        </tbody>
                                                    </table>
                                                    <?php
                                                        $startNo = ($totalStudents > 0) ? (($currentPage - 1) * $perPage + 1) : 0;
                                                        $endNo = min($currentPage * $perPage, $totalStudents);
                                                        $baseParams = [
                                                            'randomid' => $randomid,
                                                            'session' => $session,
                                                            'term_id' => $term_id
                                                        ];
                                                    ?>
                                                    <div class="table-meta">
                                                        <div>Showing <?= $startNo ?>-<?= $endNo ?> of <?= (int)$totalStudents ?> students</div>
                                                        <div class="pagination">
                                                            <?php if ($currentPage > 1): ?>
                                                                <a href="?<?= http_build_query(array_merge($baseParams, ['page' => $currentPage - 1])) ?>">Previous</a>
                                                            <?php else: ?>
                                                                <span class="disabled">Previous</span>
                                                            <?php endif; ?>

                                                            <span class="active">Page <?= (int)$currentPage ?> / <?= (int)$totalPages ?></span>

                                                            <?php if ($currentPage < $totalPages): ?>
                                                                <a href="?<?= http_build_query(array_merge($baseParams, ['page' => $currentPage + 1])) ?>">Next</a>
                                                            <?php else: ?>
                                                                <span class="disabled">Next</span>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div style="text-align: right;">
                                                    <button type="submit" name="submitcomment" class="btn-submit"><i class="fa fa-save"></i> Save All Remarks</button>
                                                </div>
                                            </form>
                                        <?php else: ?>
                                            <div class="empty-state">
                                                <i class="fa fa-users" style="font-size: 48px; color: #ccc;"></i>
                                                <h3>No Students Found</h3>
                                                <p>No students enrolled in this class for the selected session and term.</p>
                                            </div>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <div class="empty-state">
                                            <i class="fa fa-filter" style="font-size: 48px; color: #ccc;"></i>
                                            <h3>Select Session and Term</h3>
                                            <p>Please select a session and term to view students.</p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="remarks-card">
                                <div class="remarks-header">
                                    <h2><i class="fa fa-hand-pointer-o"></i> Select a Class</h2>
                                    <p>Choose a class from the left sidebar to add principal remarks</p>
                                </div>
                                <div class="empty-state" style="padding: 60px;">
                                    <i class="fa fa-book" style="font-size: 64px; color: #ccc;"></i>
                                    <h3>No Class Selected</h3>
                                    <p>Click on a class name from the left menu to get started.</p>
                                </div>
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
</body>
</html>