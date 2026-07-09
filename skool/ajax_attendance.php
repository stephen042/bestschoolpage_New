<?php
/**
 * ============================================================================
 * AJAX ATTENDANCE HANDLER
 * ============================================================================
 * Description: Handles AJAX requests for attendance updates
 * Version: 3.0 (PHP 8.x Compatible)
 * ============================================================================
 */

include('../config.php');

// ============================================================================
// INITIALIZATION
// ============================================================================
$create_by_userid = (int)($_SESSION['userid'] ?? 0);
$currentUserId = (int)($_SESSION['userid'] ?? 0);

// Set JSON response header
header('Content-Type: application/json');

// ============================================================================
// CSRF PROTECTION
// ============================================================================
$postedCsrfToken = $_POST['csrf_token'] ?? '';
$sessionCsrfToken = $_SESSION['csrf_token'] ?? '';
if ($postedCsrfToken === '' || $postedCsrfToken !== $sessionCsrfToken) {
    echo json_encode(['success' => false, 'message' => 'Security error - Invalid CSRF token']);
    exit;
}

// ============================================================================
// GET ACTION
// ============================================================================
$action = $_POST['action'] ?? '';

// ============================================================================
// UPDATE ATTENDANCE
// ============================================================================
if ($action === 'update_attendance') {
    try {
        $studentId = $_POST['student_id'] ?? '';
        $session = (int)($_POST['session'] ?? 0);
        $term = (int)($_POST['term'] ?? 0);
        $classId = (int)($_POST['class_id'] ?? 0);
        $present = (int)($_POST['present'] ?? 0);
        $totalDays = (int)($_POST['total_days'] ?? 0);
        
        // Validate
        if (empty($studentId) || $session <= 0 || $term <= 0 || $classId <= 0) {
            throw new Exception("Invalid data provided");
        }
        
        if ($present < 0) {
            throw new Exception("Present days cannot be negative");
        }
        
        if ($totalDays > 0 && $present > $totalDays) {
            throw new Exception("Present days cannot exceed total days open");
        }
        
        // Check if student exists in this class
        $student = db_get_row(
            "SELECT first_name, last_name FROM manage_student 
             WHERE student_id = ? AND class = ? AND session = ? AND term_id = ? 
             AND create_by_userid = ?",
            [$studentId, $classId, $session, $term, $create_by_userid]
        );
        
        if (empty($student)) {
            throw new Exception("Student not found in this class");
        }
        
        $studentName = ($student['first_name'] ?? '') . ' ' . ($student['last_name'] ?? '');
        
        // Check if record exists
        $existing = db_get_row(
            "SELECT id FROM class_teacher_roll_call_bulk 
             WHERE student_id = ? AND session_id = ? AND term_id = ? AND class_id = ? 
             AND create_by_userid = ?",
            [$studentId, $session, $term, $classId, $create_by_userid]
        );
        
        $absent = max(0, $totalDays - $present);
        
        if ($existing) {
            // Update existing record
            db_update(
                "class_teacher_roll_call_bulk",
                [
                    'present' => $present,
                    'absent' => $absent,
                    'total_days_open' => $totalDays
                ],
                "id = ?",
                [$existing['id']]
            );
        } else {
            // Insert new record
            $randomId = randomFix(15) . time();
            db_insert("class_teacher_roll_call_bulk", [
                'session_id' => $session,
                'term_id' => $term,
                'class_id' => $classId,
                'student_id' => $studentId,
                'present' => $present,
                'absent' => $absent,
                'total_days_open' => $totalDays,
                'userid' => $currentUserId,
                'usertype' => (int)($_SESSION['usertype'] ?? 0),
                'create_by_userid' => $create_by_userid,
                'create_by_usertype' => (int)($_SESSION['usertype'] ?? 0),
                'randomid' => $randomId
            ]);
        }
        
        // Get updated summary
        $summary = db_get_row(
            "SELECT 
                COALESCE(SUM(present), 0) as totalPresent,
                COALESCE(SUM(absent), 0) as totalAbsent,
                CASE 
                    WHEN COALESCE(SUM(present), 0) + COALESCE(SUM(absent), 0) > 0 
                    THEN ROUND((COALESCE(SUM(present), 0) / (COALESCE(SUM(present), 0) + COALESCE(SUM(absent), 0))) * 100, 1)
                    ELSE 0 
                END as percentage
            FROM class_teacher_roll_call_bulk
            WHERE class_id = ? AND session_id = ? AND term_id = ? AND create_by_userid = ?",
            [$classId, $session, $term, $create_by_userid]
        );
        
        echo json_encode([
            'success' => true,
            'message' => 'Attendance updated successfully',
            'student_name' => $studentName,
            'summary' => $summary
        ]);
        
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
}

// ============================================================================
// GET SUMMARY
// ============================================================================
elseif ($action === 'get_summary') {
    try {
        $session = (int)($_POST['session'] ?? 0);
        $term = (int)($_POST['term'] ?? 0);
        $classId = (int)($_POST['class_id'] ?? 0);
        
        $summary = db_get_row(
            "SELECT 
                COALESCE(SUM(present), 0) as totalPresent,
                COALESCE(SUM(absent), 0) as totalAbsent,
                CASE 
                    WHEN COALESCE(SUM(present), 0) + COALESCE(SUM(absent), 0) > 0 
                    THEN ROUND((COALESCE(SUM(present), 0) / (COALESCE(SUM(present), 0) + COALESCE(SUM(absent), 0))) * 100, 1)
                    ELSE 0 
                END as percentage
            FROM class_teacher_roll_call_bulk
            WHERE class_id = ? AND session_id = ? AND term_id = ? AND create_by_userid = ?",
            [$classId, $session, $term, $create_by_userid]
        );
        
        echo json_encode([
            'success' => true,
            'summary' => $summary
        ]);
        
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
}

// ============================================================================
// UNKNOWN ACTION
// ============================================================================
else {
    echo json_encode([
        'success' => false,
        'message' => 'Unknown action'
    ]);
}
?>