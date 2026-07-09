<?php
/**
 * AJAX Handler - Updated with Audit Log for Subject Removal
 */

require_once('../config.php');
include('inc.session-create.php');

$validate = new Validation();
$create_by_userid = (int)($_SESSION['userid'] ?? 0);
$action = $_POST['action'] ?? '';

// ============================================================================
// ACTION: GET STUDENT SUBJECTS (Existing functionality)
// ============================================================================
if ($action == 'getstusubj') {
    $randomid = trim($_POST['randomid'] ?? '');
    $iStudentDetails = db_get_row(
        "SELECT id, student_id, first_name, last_name, session, class, term_id, randomid
         FROM manage_student
         WHERE create_by_userid = ? AND randomid = ?
         LIMIT 1",
        [$create_by_userid, $randomid]
    );

    if (empty($iStudentDetails)) {
        echo '<div class="alert alert-danger">Student not found or session expired. Refresh the page and try again.</div>';
        exit;
    }
    
    ?>
    <table class="table table-striped table-bordered">
        <thead>
            <tr>
                <th>#</th>
                <th>Subject</th>
                <th>Select To Remove</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $i = 0;
            $aryList = db_get_rows(
                "SELECT * FROM school_subject WHERE create_by_userid = ? AND class_id = ? ORDER BY id DESC",
                [$create_by_userid, $iStudentDetails['class']]
            );
            foreach($aryList as $iList) { 
                $i++;
                $iAlreadyRemoved = db_get_row(
                    "SELECT id FROM student_subject_remove
                     WHERE create_by_userid = ? AND subjectid = ? AND randomid = ? AND studentid = ?
                     LIMIT 1",
                    [$create_by_userid, $iList['id'], $randomid, $iStudentDetails['id']]
                );
            ?>
                <tr>
                    <td><?php echo $i; ?></td>
                    <td><?php echo $iList['subject']; ?></td>
                    <td><input type="checkbox" name="subjectlist[]" value="<?php echo $iList['id']; ?>" <?php if(!empty($iAlreadyRemoved) && !empty($iAlreadyRemoved['id'])) { echo 'checked'; } ?>/></td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
    <?php
    exit;
}

// ============================================================================
// ACTION: UPDATE SUBJECT (Existing functionality with Audit Log added)
// ============================================================================
elseif ($action == 'updatesubject') {
    $randomid = trim($_POST['randomid'] ?? '');
    $iStudentDetails = db_get_row(
        "SELECT id, student_id, first_name, last_name
         FROM manage_student
         WHERE create_by_userid = ? AND randomid = ?
         LIMIT 1",
        [$create_by_userid, $randomid]
    );

    if (empty($iStudentDetails)) {
        echo '<div class="alert alert-danger">Student not found.</div>';
        exit;
    }
    
    // Get existing removed subjects before deletion
    $existingRemoved = db_get_rows(
        "SELECT * FROM student_subject_remove WHERE randomid = ? AND studentid = ? AND create_by_userid = ?",
        [$randomid, $iStudentDetails['id'], $create_by_userid]
    );
    $existingIds = array_column($existingRemoved, 'subjectid');
    
    // Delete all existing records
    db_delete(
        "student_subject_remove",
        "randomid = ? AND studentid = ? AND create_by_userid = ?",
        [$randomid, $iStudentDetails['id'], $create_by_userid]
    );
    
    // Get selected subjects to remove
    $subjectCsv = trim($_POST['subjectid'] ?? '');
    $iSubjectId = array();
    if ($subjectCsv !== '') {
        foreach (explode(',', $subjectCsv) as $subjectId) {
            $subjectId = (int)$subjectId;
            if ($subjectId > 0) {
                $iSubjectId[] = $subjectId;
            }
        }
    }
    
    // ===== AUDIT LOG: Track removed subjects =====
    foreach ($iSubjectId as $key => $val) {
        // Insert into student_subject_remove table
        $aryData = array(
            'studentid'             => $iStudentDetails['id'],
            'randomid'              => $randomid,
            'subjectid'             => $val,
            'create_by_userid'      => $create_by_userid,
        );
        db_insert("student_subject_remove", $aryData);
        
        // ===== NEW: Add to audit log if subject was not previously removed =====
        if (!in_array($val, $existingIds)) {
            $subjectName = db_get_val("SELECT subject FROM school_subject WHERE id = ?", [$val]);
            $userName = $_SESSION['username'] ?? $_SESSION['email'] ?? 'Unknown';
            
            $auditData = array(
                'user_id' => $create_by_userid,
                'user_name' => $userName,
                'student_id' => $iStudentDetails['id'],
                'student_name' => $iStudentDetails['first_name'] . ' ' . $iStudentDetails['last_name'],
                'subject_id' => $val,
                'subject_name' => $subjectName,
                'session_id' => 0,
                'term_id' => 0,
                'action' => 'removed',
                'removed_at' => date('Y-m-d H:i:s')
            );
            db_insert("subject_removal_audit", $auditData);
        }
    }
    
    // ===== AUDIT LOG: Track restored subjects (subjects that were unchecked) =====
    $removedSubjects = array_diff($existingIds, $iSubjectId);
    foreach ($removedSubjects as $restoredSubjectId) {
        $subjectName = db_get_val("SELECT subject FROM school_subject WHERE id = ?", [$restoredSubjectId]);
        $userName = $_SESSION['username'] ?? $_SESSION['email'] ?? 'Unknown';
        
        // Check if there's an existing audit record to update
        $existingAudit = db_get_row(
            "SELECT id FROM subject_removal_audit WHERE student_id = ? AND subject_id = ? AND action = 'removed' ORDER BY id DESC LIMIT 1",
            [$iStudentDetails['id'], $restoredSubjectId]
        );
        
        if ($existingAudit) {
            // Update existing audit record as restored
            db_update("subject_removal_audit", array(
                'action' => 'restored',
                'restored_at' => date('Y-m-d H:i:s'),
                'restored_by' => $create_by_userid
            ), "id = ?", [$existingAudit['id']]);
        } else {
            // Create new audit record for restore
            $auditData = array(
                'user_id' => $create_by_userid,
                'user_name' => $userName,
                'student_id' => $iStudentDetails['id'],
                'student_name' => $iStudentDetails['first_name'] . ' ' . $iStudentDetails['last_name'],
                'subject_id' => $restoredSubjectId,
                'subject_name' => $subjectName,
                'session_id' => 0,
                'term_id' => 0,
                'action' => 'restored',
                'removed_at' => date('Y-m-d H:i:s')
            );
            db_insert("subject_removal_audit", $auditData);
        }
    }

    echo '<div class="alert alert-success">Record updated successfully</div>';
    exit;
}

// ============================================================================
// ACTION: GET AUDIT LOG (New functionality)
// ============================================================================
elseif ($action == 'get_audit_log') {
    $studentId = (int)$_POST['student_id'];
    
    $logs = db_get_rows("SELECT * FROM subject_removal_audit WHERE student_id = ? AND user_id = ? ORDER BY removed_at DESC", [$studentId, $create_by_userid]);
    
    if (!empty($logs)) {
        echo '<table class="table table-bordered">';
        echo '<thead>';
        echo '<tr>';
        echo '<th>Subject</th>';
        echo '<th>Action</th>';
        echo '<th>Date/Time</th>';
        echo '<th>Restored By</th>';
        echo '<th>Undo</th>';
        echo '</tr>';
        echo '</thead>';
        echo '<tbody>';
        
        foreach ($logs as $log) {
            $hoursSince = (time() - strtotime($log['removed_at'])) / 3600;
            $canUndo = ($log['action'] == 'removed' && $hoursSince <= 24);
            
            echo '<tr>';
            echo '<td>' . htmlspecialchars($log['subject_name'] ?? 'N/A') . '</td>';
            echo '<td>' . ucfirst($log['action'] ?? 'N/A') . '</td>';
            echo '<td>' . date('d M Y H:i:s', strtotime($log['removed_at'])) . '</td>';
            echo '<td>' . htmlspecialchars($log['restored_by'] ?? '-') . '</td>';
            echo '<td>';
            if ($canUndo) {
                echo '<button type="button" class="btn btn-sm btn-warning undo-removal-btn" data-audit-id="' . (int)$log['id'] . '">Undo</button>';
            } else if ($log['action'] == 'removed') {
                echo '<span class="text-muted">Expired</span>';
            } else {
                echo '-';
            }
            echo '</td>';
            echo '</tr>';
        }
        
        echo '</tbody>';
        echo '</table>';
    } else {
        echo '<div class="alert alert-info">No removal history found for this student.</div>';
    }
    exit;
}

// ============================================================================
// ACTION: UNDO REMOVAL (Called from audit modal)
// ============================================================================
elseif ($action == 'undo_removal') {
    $auditId = (int)($_POST['audit_id'] ?? 0);
    
    if ($auditId > 0) {
        $audit = db_get_row(
            "SELECT * FROM subject_removal_audit WHERE id = ? AND action = 'removed' AND user_id = ?",
            [$auditId, $create_by_userid]
        );
        
        if ($audit) {
            $removedAt = strtotime($audit['removed_at']);
            $now = time();
            $hoursPassed = ($now - $removedAt) / 3600;
            
            if ($hoursPassed <= 24) {
                // Delete removed-subject mapping for this student+subject under this school.
                db_delete(
                    "student_subject_remove",
                    "studentid = ? AND subjectid = ? AND create_by_userid = ?",
                    [$audit['student_id'], $audit['subject_id'], $create_by_userid]
                );
                
                // Update audit log
                db_update("subject_removal_audit", array(
                    'action' => 'restored',
                    'restored_at' => date('Y-m-d H:i:s'),
                    'restored_by' => $create_by_userid
                ), "id = ?", [$auditId]);
                
                echo '1';
            } else {
                echo '0'; // Expired
            }
        } else {
            echo '0'; // Not found
        }
    } else {
        echo '0'; // Invalid ID
    }
    exit;
}
?>