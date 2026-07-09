<?php
/**
 * ============================================================================
 * ATTENDANCE REPORT - EXCEL EXPORT
 * ============================================================================
 * Description: Export attendance report to CSV/Excel format
 * Version: 3.0 (PHP 8.x Compatible)
 * ============================================================================
 */

include('../config.php');
include('inc.session-create.php');

// ============================================================================
// GET FILTERS
// ============================================================================
$sessionId = $_GET['session'] ?? 0;
$termId = $_GET['term_id'] ?? 0;
$classId = $_GET['class_id'] ?? 0;

// Get names
$sessionName = db_get_val("SELECT session FROM school_session WHERE id = ? AND create_by_userid = ?", [$sessionId, $create_by_userid]);
$termName = db_get_val("SELECT term FROM school_term WHERE id = ? AND create_by_userid = ?", [$termId, $create_by_userid]);
$className = db_get_val("SELECT name FROM school_class WHERE id = ? AND create_by_userid = ?", [$classId, $create_by_userid]);
$school = db_get_row("SELECT * FROM school_register WHERE id = ?", [$create_by_userid]);

// Get attendance data
$students = db_get_rows(
    "SELECT 
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
$attendanceRate = ($totalPresent + $totalAbsent > 0) 
    ? round(($totalPresent / ($totalPresent + $totalAbsent)) * 100, 1) 
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
fputcsv($output, ['ATTENDANCE REPORT']);
fputcsv($output, ['School:', $school['name'] ?? '']);
fputcsv($output, ['Session:', $sessionName ?? 'N/A']);
fputcsv($output, ['Term:', $termName ?? 'N/A']);
fputcsv($output, ['Class:', $className ?? 'N/A']);
fputcsv($output, ['Generated:', date('d M Y H:i:s')]);
fputcsv($output, []);
fputcsv($output, ['SUMMARY']);
fputcsv($output, ['Total Students', 'Total Present', 'Total Absent', 'Attendance Rate']);
fputcsv($output, [$totalStudents, $totalPresent, $totalAbsent, $attendanceRate . '%']);
fputcsv($output, []);
fputcsv($output, ['DETAILED ATTENDANCE']);
fputcsv($output, [
    '#', 'Student ID', 'Student Name', 'Present', 'Absent', 'Total Days', 'Percentage', 'Status'
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
    $status = $percent >= 80 ? 'Good' : ($percent >= 60 ? 'Average' : 'Poor');
    
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
fputcsv($output, []);
fputcsv($output, ['End of Report']);
fputcsv($output, ['Generated on:', date('d M Y H:i:s')]);

fclose($output);
exit;
?>