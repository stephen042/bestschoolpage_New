<?php
/**
 * ============================================================================
 * ATTENDANCE REPORT - PDF EXPORT
 * ============================================================================
 * Description: Generate PDF report for attendance
 * Version: 3.0 (PHP 8.x Compatible)
 * ============================================================================
 */

// ============================================================================
// DOMPDF AUTOLOAD
// ============================================================================
$dompdfPath = dirname(__DIR__) . '/dompdf_New/autoload.inc.php';

if (!file_exists($dompdfPath)) {
    die("Dompdf not found at: " . $dompdfPath);
}

require_once($dompdfPath);

use Dompdf\Dompdf;
use Dompdf\Options;

// ============================================================================
// CONFIGURATION & SESSION
// ============================================================================
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

// Get school details
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
$totalStudents = count($students);

foreach ($students as $s) {
    $totalPresent += (int)$s['present'];
    $totalAbsent += (int)$s['absent'];
}

$attendanceRate = ($totalPresent + $totalAbsent > 0) 
    ? round(($totalPresent / ($totalPresent + $totalAbsent)) * 100, 1) 
    : 0;

// ============================================================================
// GENERATE HTML
// ============================================================================
ob_start();
?>
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Attendance Report</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'DejaVu Sans', 'Segoe UI', Arial, sans-serif;
            font-size: 10px;
            padding: 20px;
            color: #333;
            background: #fff;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #1B3058;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        .school-name { font-size: 22px; font-weight: 700; color: #1B3058; }
        .school-moto { font-size: 10px; color: #666; font-style: italic; }
        .school-address { font-size: 9px; color: #888; }
        .report-title { font-size: 16px; font-weight: 700; color: #1B3058; margin: 10px 0 4px; }
        .report-subtitle { font-size: 11px; color: #666; }
        
        .summary-grid {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr 1fr;
            gap: 10px;
            margin-bottom: 20px;
        }
        .summary-card {
            padding: 12px 16px;
            border-radius: 6px;
            text-align: center;
            background: #f8f9fa;
            border-left: 4px solid #1B3058;
        }
        .summary-card .number { font-size: 18px; font-weight: 700; color: #1B3058; }
        .summary-card .label { font-size: 9px; color: #666; text-transform: uppercase; margin-top: 2px; }
        .summary-card.present { border-color: #28a745; }
        .summary-card.present .number { color: #28a745; }
        .summary-card.absent { border-color: #dc3545; }
        .summary-card.absent .number { color: #dc3545; }
        .summary-card.percentage { border-color: #17a2b8; }
        .summary-card.percentage .number { color: #17a2b8; }
        
        .table { width: 100%; border-collapse: collapse; font-size: 9px; margin-top: 10px; }
        .table th { background: #1B3058; color: #fff; padding: 6px 10px; text-align: left; font-weight: 600; }
        .table td { padding: 5px 10px; border-bottom: 1px solid #eee; }
        .table .text-right { text-align: right; }
        
        .footer {
            margin-top: 20px;
            padding-top: 15px;
            border-top: 1px solid #ddd;
            font-size: 8px;
            color: #888;
            display: flex;
            justify-content: space-between;
        }
        .signature-line {
            margin-top: 20px;
            padding-top: 5px;
            border-top: 1px solid #333;
            width: 200px;
            text-align: center;
            font-size: 9px;
        }
        
        .status-present { color: #28a745; font-weight: 700; }
        .status-absent { color: #dc3545; font-weight: 700; }
        
        @media print {
            body { padding: 10px; }
            .table th { background: #1B3058 !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .summary-card { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
    </style>
</head>
<body>

<div class="header">
    <div class="school-name"><?= htmlspecialchars($school['name'] ?? 'School Name') ?></div>
    <div class="school-moto"><?= htmlspecialchars($school['moto'] ?? 'Excellence in Education') ?></div>
    <div class="school-address">
        <?= htmlspecialchars($school['location'] ?? '') ?>
        <?php if (!empty($school['phone'])): ?> | Tel: <?= htmlspecialchars($school['phone']) ?><?php endif; ?>
    </div>
    <div class="report-title">ATTENDANCE REPORT</div>
    <div class="report-subtitle">
        Session: <?= htmlspecialchars($sessionName ?? 'N/A') ?> | 
        Term: <?= htmlspecialchars($termName ?? 'N/A') ?> | 
        Class: <?= htmlspecialchars($className ?? 'N/A') ?>
        <br>
        <span style="font-size:9px; color:#999;">Generated: <?= date('d M Y H:i:s') ?></span>
    </div>
</div>

<!-- Summary -->
<div class="summary-grid">
    <div class="summary-card">
        <div class="number"><?= $totalStudents ?></div>
        <div class="label">Total Students</div>
    </div>
    <div class="summary-card present">
        <div class="number"><?= $totalPresent ?></div>
        <div class="label">Total Present</div>
    </div>
    <div class="summary-card absent">
        <div class="number"><?= $totalAbsent ?></div>
        <div class="label">Total Absent</div>
    </div>
    <div class="summary-card percentage">
        <div class="number"><?= $attendanceRate ?>%</div>
        <div class="label">Attendance Rate</div>
    </div>
</div>

<!-- Table -->
<table class="table">
    <thead>
        <tr>
            <th style="width:50px;">#</th>
            <th style="width:120px;">Student ID</th>
            <th>Student Name</th>
            <th style="width:80px;" class="text-right">Present</th>
            <th style="width:80px;" class="text-right">Absent</th>
            <th style="width:80px;" class="text-right">Total Days</th>
            <th style="width:80px;" class="text-right">%</th>
            <th style="width:80px;">Status</th>
        </tr>
    </thead>
    <tbody>
        <?php if (!empty($students)): ?>
            <?php $i = 0; foreach ($students as $student): $i++; 
                $present = (int)$student['present'];
                $absent = (int)$student['absent'];
                $total = $present + $absent;
                $percent = $total > 0 ? round(($present / $total) * 100, 0) : 0;
                $status = $percent >= 80 ? '✅ Good' : ($percent >= 60 ? '⚠️ Average' : '❌ Poor');
                $statusClass = $percent >= 80 ? 'status-present' : 'status-absent';
            ?>
            <tr>
                <td><?= $i ?></td>
                <td><?= htmlspecialchars($student['student_id'] ?? '') ?></td>
                <td><?= htmlspecialchars(($student['first_name'] ?? '') . ' ' . ($student['last_name'] ?? '')) ?></td>
                <td class="text-right"><?= $present ?></td>
                <td class="text-right"><?= $absent ?></td>
                <td class="text-right"><?= $total ?></td>
                <td class="text-right"><?= $percent ?>%</td>
                <td class="<?= $statusClass ?>"><?= $status ?></td>
            </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="8" style="text-align:center; padding:15px; color:#999;">
                    No attendance data found
                </td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

<!-- Footer -->
<div class="footer">
    <div>
        <div><strong>Generated on:</strong> <?= date('d M Y H:i:s') ?></div>
        <div style="margin-top:3px; font-size:7px; color:#aaa;">
            This report is system-generated and requires verification.
        </div>
    </div>
    <div style="text-align:right;">
        <div style="font-size:10px; font-weight:600; color:#1B3058;">
            <?= htmlspecialchars($school['name'] ?? '') ?>
        </div>
        <div class="signature-line" style="margin-top:10px;">
            Authorized Signature<br>
            <span style="font-size:7px;">_______________________________</span>
        </div>
    </div>
</div>

</body>
</html>
<?php
$html = ob_get_clean();

// ============================================================================
// GENERATE PDF
// ============================================================================
$dompdf = new Dompdf();
$options = new Options();
$options->set('isRemoteEnabled', true);
$options->set('isJavascriptEnabled', true);
$dompdf->setOptions($options);

$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

$filename = 'Attendance_Report_' . date('Y-m-d') . '.pdf';
$dompdf->stream($filename, ['Attachment' => true]);
?>