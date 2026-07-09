<?php
/**
 * Class Teacher Make Comment - Modern PHP 8.x
 * Add comments/remarks for students with position and total scores
 */

require_once('../config.php');
require_once('inc.session-create.php');

$PageTitle = "Make Comment";
$FileName = 'class_teacher_make_comment.php';

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
    <style>
        * { box-sizing: border-box; }
        .comment-container { max-width: 1400px; margin: 0 auto; padding: 20px; }
        .page-header { margin-bottom: 30px; }
        .page-header h2 { color: #1B3058; margin: 0; }
        .page-header p { color: #666; margin-top: 5px; }
        .two-column-layout { display: flex; gap: 25px; flex-wrap: wrap; }
        .class-list-panel { flex: 1; min-width: 280px; background: #fff; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); overflow: hidden; }
        .comment-panel { flex: 3; min-width: 500px; }
        .panel-header { padding: 15px 20px; background: #f8f9fa; border-bottom: 1px solid #eee; font-weight: 600; }
        .class-list { max-height: 600px; overflow-y: auto; }
        .class-item { padding: 15px 20px; border-bottom: 1px solid #f0f0f0; cursor: pointer; transition: all 0.2s; }
        .class-item:hover { background: #f8f9ff; }
        .class-item.active { background: #1B3058; color: white; }
        .filter-card { background: #fff; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); padding: 20px; margin-bottom: 25px; }
        .filter-grid { display: flex; gap: 15px; flex-wrap: wrap; align-items: flex-end; }
        .filter-group { flex: 1; min-width: 150px; }
        .filter-group label { display: block; font-size: 12px; font-weight: 600; color: #666; margin-bottom: 5px; text-transform: uppercase; }
        .filter-select { width: 100%; padding: 10px 12px; border: 1px solid #e0e0e0; border-radius: 10px; font-size: 14px; }
        .btn { padding: 10px 20px; border: none; border-radius: 10px; cursor: pointer; font-weight: 600; transition: all 0.2s; }
        .btn-primary { background: #1B3058; color: white; }
        .btn-primary:hover { background: #f21151; transform: translateY(-2px); }
        .btn-success { background: #28a745; color: white; }
        .data-table { width: 100%; border-collapse: collapse; }
        .data-table th, .data-table td { padding: 12px; text-align: center; border: 1px solid #e0e0e0; vertical-align: middle; }
        .data-table th { background: #f8f9fa; font-weight: 600; color: #1B3058; position: sticky; top: 0; }
        .data-table textarea { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 8px; resize: vertical; min-height: 60px; }
        .data-table textarea:focus { outline: none; border-color: #1B3058; }
        .position-badge { display: inline-block; background: #1B3058; color: white; padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: 600; }
        .position-1 { background: linear-gradient(135deg, #FFD700, #FFA500); color: #333; }
        .position-2 { background: linear-gradient(135deg, #C0C0C0, #A9A9A9); }
        .position-3 { background: linear-gradient(135deg, #CD7F32, #B87333); }
        .alert { padding: 12px 18px; border-radius: 12px; margin-bottom: 20px; }
        .alert-success { background: #d4edda; color: #155724; border-left: 4px solid #28a745; }
        .alert-info { background: #d1ecf1; color: #0c5460; border-left: 4px solid #17a2b8; }
        .table-meta { display: flex; justify-content: space-between; gap: 10px; align-items: center; margin: 12px 0 0; color: #666; font-size: 13px; flex-wrap: wrap; }
        .pagination { display: flex; gap: 8px; align-items: center; }
        .pagination a, .pagination span { padding: 6px 10px; border: 1px solid #e0e0e0; border-radius: 8px; text-decoration: none; color: #1B3058; background: #fff; }
        .pagination .active { background: #1B3058; color: #fff; border-color: #1B3058; }
        .pagination .disabled { color: #aaa; }
        @media (max-width: 900px) { .two-column-layout { flex-direction: column; } .filter-grid { flex-direction: column; } .data-table { display: block; overflow-x: auto; } }
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
                        <div class="panel-header"><i class="fa fa-graduation-cap"></i> Select Class</div>
                        <div class="class-list">
                            <?php foreach ($allClasses as $class): ?>
                                <a href="?randomid=<?= urlencode($class['randomid']) ?><?= $selectedSession ? '&session='.urlencode($selectedSession) : '' ?><?= $selectedTerm ? '&term_id='.urlencode($selectedTerm) : '' ?>&page=1" style="text-decoration: none;">
                                    <div class="class-item <?= ($randomid == $class['randomid']) ? 'active' : '' ?>">
                                        <i class="fa fa-book"></i> <?= htmlspecialchars($class['name']) ?>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                            <?php if (empty($allClasses)): ?>
                                <div class="class-item">No classes found</div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- RIGHT: Comments Panel -->
                    <div class="comment-panel">
                        <?php if (!empty($classDetail)): ?>
                            <!-- Filters -->
                            <div class="filter-card">
                                <form method="GET" action="">
                                    <input type="hidden" name="randomid" value="<?= htmlspecialchars($randomid) ?>">
                                    <input type="hidden" name="page" value="1">
                                    <div class="filter-grid">
                                        <div class="filter-group">
                                            <label>Session</label>
                                            <select name="session" class="filter-select" required>
                                                <option value="">-- Select Session --</option>
                                                <?php foreach($sessions as $s): ?>
                                                    <option value="<?= $s['id'] ?>" <?= ($selectedSession == $s['id']) ? 'selected' : '' ?>><?= htmlspecialchars($s['session']) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="filter-group">
                                            <label>Term</label>
                                            <select name="term_id" class="filter-select" required>
                                                <option value="">-- Select Term --</option>
                                                <?php foreach($terms as $t): ?>
                                                    <option value="<?= $t['id'] ?>" <?= ($selectedTerm == $t['id']) ? 'selected' : '' ?>><?= htmlspecialchars($t['term']) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="filter-group">
                                            <button type="submit" class="btn btn-primary"><i class="fa fa-filter"></i> Load Students</button>
                                        </div>
                                    </div>
                                </form>
                            </div>

                            <!-- Students Table -->
                            <?php if (!empty($selectedSession) && !empty($selectedTerm)): ?>
                                <?php if (!empty($students)): ?>
                                    <form method="POST">
                                        <input type="hidden" name="page" value="<?= (int)$currentPage ?>">
                                        <div style="background: #fff; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); overflow: hidden;">
                                            <div class="panel-header">
                                                <strong><i class="fa fa-users"></i> <?= htmlspecialchars($classDetail['name']) ?> - Student Comments</strong>
                                                <span style="float:right; font-size:12px;">Enter comments for each student below</span>
                                            </div>
                                            <div style="padding: 20px; overflow-x: auto;">
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
                                                            <th>Comment / Remark</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php foreach ($students as $student): 
                                                            $totalScore = $studentScores[$student['id']] ?? 0;
                                                            $average = ($totalSubjects > 0) ? round($totalScore / $totalSubjects, 2) : 0;
                                                            $position = $studentPositions[$student['id']] ?? '-';
                                                            $positionSuffix = '';
                                                            if ($position == 1) $positionSuffix = 'st';
                                                            elseif ($position == 2) $positionSuffix = 'nd';
                                                            elseif ($position == 3) $positionSuffix = 'rd';
                                                            else $positionSuffix = 'th';

                                                            $existingComment = $studentComments[$student['id']] ?? null;
                                                        ?>
                                                            <tr>
                                                                <input type="hidden" name="student_id[]" value="<?= $student['id'] ?>">
                                                                <input type="hidden" name="primaryid[]" value="<?= $existingComment['id'] ?? '' ?>">
                                                                <input type="hidden" name="no_of_subject[]" value="<?= $totalSubjects ?>">
                                                                <input type="hidden" name="totalScore[]" value="<?= $totalScore ?>">
                                                                <input type="hidden" name="position[]" value="<?= $position ?>">
                                                                
                                                                <td><?= htmlspecialchars($student['student_id']) ?></td>
                                                                <td><?= htmlspecialchars($student['first_name']) ?></td>
                                                                <td><?= htmlspecialchars($student['last_name']) ?></td>
                                                                <td><?= $totalSubjects ?></td>
                                                                <td><?= $totalScore ?></td>
                                                                <td><?= $average ?></td>
                                                                <td><span class="position-badge <?= ($position == 1) ? 'position-1' : (($position == 2) ? 'position-2' : (($position == 3) ? 'position-3' : '')) ?>"><?= $position ?><?= $positionSuffix ?></span></td>
                                                                <td><textarea name="comments[]" rows="2" placeholder="Enter comment/remark for this student..."><?= htmlspecialchars($existingComment['comments'] ?? '') ?></textarea></td>
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
                                            <div style="padding: 15px 20px; background: #f8f9fa; border-top: 1px solid #eee; text-align: right;">
                                                <button type="submit" name="submitcomment" class="btn btn-success"><i class="fa fa-save"></i> Save All Comments</button>
                                            </div>
                                        </div>
                                    </form>
                                <?php else: ?>
                                    <div class="alert alert-info">No students found in this class for the selected session and term.</div>
                                <?php endif; ?>
                            <?php elseif (!empty($selectedSession) && empty($selectedTerm)): ?>
                                <div class="alert alert-info">Please select a term to continue.</div>
                            <?php elseif (empty($selectedSession)): ?>
                                <div class="alert alert-info">Please select a session and term to continue.</div>
                            <?php endif; ?>
                        <?php else: ?>
                            <div class="alert alert-info">Please select a class from the left panel.</div>
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