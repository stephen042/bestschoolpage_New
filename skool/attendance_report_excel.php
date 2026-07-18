<?php

/**
 * ============================================================================
 * ATTENDANCE REPORT - EXCEL EXPORT (Mobile Compatible)
 * ============================================================================
 * Description: Export attendance report to CSV/Excel format
 * Version: 4.0 (Mobile Compatible + Correct User ID)
 * ============================================================================
 */

include('../config.php');
include('inc.session-create.php');

// ============================================================================
// FIX: USE CORRECT USER IDENTIFICATION (EXACTLY MATCHES dashboard.php)
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

// ============================================================================
// GET FILTERS
// ============================================================================
$sessionId = isset($_GET['session']) ? (int)$_GET['session'] : 0;
$termId = isset($_GET['term_id']) ? (int)$_GET['term_id'] : 0;
$classId = isset($_GET['class_id']) ? (int)$_GET['class_id'] : 0;

// ============================================================================
// VALIDATE FILTERS
// ============================================================================
if ($sessionId == 0 || $termId == 0 || $classId == 0) {
    die("Missing required filters: session, term, and class are required.");
}

// Get names
$sessionName = db_get_val("SELECT session FROM school_session WHERE id = ? AND create_by_userid = ?", [$sessionId, $create_by_userid]);
$termName = db_get_val("SELECT term FROM school_term WHERE id = ? AND create_by_userid = ?", [$termId, $create_by_userid]);
$className = db_get_val("SELECT name FROM school_class WHERE id = ? AND create_by_userid = ?", [$classId, $create_by_userid]);
$school = db_get_row("SELECT * FROM school_register WHERE id = ?", [$create_by_userid]);

// ============================================================================
// GET ATTENDANCE DATA
// ============================================================================
$students = db_get_rows(
    "SELECT 
        m.id,
        m.student_id,
        m.first_name,
        m.last_name,
        COALESCE(r.present, 0) as present,
        COALESCE(r.absent, 0) as absent,
        COALESCE(r.total_days_open, 0) as total_days_open
    FROM manage_student m
    LEFT JOIN class_teacher_roll_call_bulk r ON m.student_id = r.student_id 
        AND r.session_id = ? AND r.term_id = ? AND r.class_id = ?
        AND r.create_by_userid = ?
    WHERE m.class = ? AND m.session = ? AND m.term_id = ? AND m.create_by_userid = ?
    ORDER BY m.first_name ASC",
    [$sessionId, $termId, $classId, $create_by_userid, $classId, $sessionId, $termId, $create_by_userid]
);

// Calculate totals
$totalPresent = 0;
$totalAbsent = 0;

foreach ($students as $s) {
    $totalPresent += (int)$s['present'];
    $totalAbsent += (int)$s['absent'];
}
$totalStudents = count($students);
$totalDays = $totalPresent + $totalAbsent;
$attendanceRate = $totalDays > 0
    ? round(($totalPresent / $totalDays) * 100, 1)
    : 0;

// ============================================================================
// HEADERS FOR DOWNLOAD
// ============================================================================
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="Attendance_Report_' . date('Y-m-d') . '.csv"');
header('Pragma: no-cache');
header('Expires: 0');

$output = fopen('php://output', 'w');
fwrite($output, "\xEF\xBB\xBF"); // UTF-8 BOM

// ============================================================================
// HEADER SECTION
// ============================================================================
fputcsv($output, ['📋 ATTENDANCE REPORT']);
fputcsv($output, ['']);
fputcsv($output, ['School:', $school['name'] ?? '']);
fputcsv($output, ['Session:', $sessionName ?? 'N/A']);
fputcsv($output, ['Term:', $termName ?? 'N/A']);
fputcsv($output, ['Class:', $className ?? 'N/A']);
fputcsv($output, ['Generated:', date('d M Y H:i:s')]);
fputcsv($output, ['']);

// ============================================================================
// SUMMARY
// ============================================================================
fputcsv($output, ['📊 SUMMARY']);
fputcsv($output, ['Total Students', 'Total Present', 'Total Absent', 'Attendance Rate']);
fputcsv($output, [$totalStudents, $totalPresent, $totalAbsent, $attendanceRate . '%']);
fputcsv($output, ['']);

// ============================================================================
// DETAILED ATTENDANCE
// ============================================================================
fputcsv($output, ['📋 DETAILED ATTENDANCE']);
fputcsv($output, [
    '#',
    'Student ID',
    'Student Name',
    'Present',
    'Absent',
    'Total Days',
    'Percentage',
    'Status'
]);

// ============================================================================
// DETAILED DATA
// ============================================================================
$i = 0;
foreach ($students as $student) {
    $i++;
    $present = (int)$student['present'];
    $absent = (int)$student['absent'];
    $total = $present + $absent;
    $percent = $total > 0 ? round(($present / $total) * 100, 0) : 0;

    if ($percent >= 80) {
        $status = 'Good ✅';
    } elseif ($percent >= 60) {
        $status = 'Average ⚠️';
    } else {
        $status = 'Poor ❌';
    }

    fputcsv($output, [
        $i,
        $student['student_id'] ?? '',
        trim(($student['first_name'] ?? '') . ' ' . ($student['last_name'] ?? '')),
        $present,
        $absent,
        $total,
        $percent . '%',
        $status
    ]);
}

// ============================================================================
// FOOTER
// ============================================================================
fputcsv($output, ['']);
fputcsv($output, ['--- End of Report ---']);
fputcsv($output, ['Generated on:', date('d M Y H:i:s')]);
fputcsv($output, ['']);
fputcsv($output, ['Powered by Best School Page']);

fclose($output);
exit;
