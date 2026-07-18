<?php

/**
 * ============================================================================
 * ATTENDANCE REPORT - PDF EXPORT (Mobile Compatible)
 * ============================================================================
 * Description: Generate PDF report for attendance with mobile-friendly layout
 * Version: 4.2 (Clean Summary - No Icons, Flex Layout)
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
// VALIDATE FILTERS - Redirect if missing
// ============================================================================
if ($sessionId == 0 || $termId == 0 || $classId == 0) {
    die("Missing required filters: session, term, and class are required.");
}

// Get names
$sessionName = db_get_val("SELECT session FROM school_session WHERE id = ? AND create_by_userid = ?", [$sessionId, $create_by_userid]);
$termName = db_get_val("SELECT term FROM school_term WHERE id = ? AND create_by_userid = ?", [$termId, $create_by_userid]);
$className = db_get_val("SELECT name FROM school_class WHERE id = ? AND create_by_userid = ?", [$classId, $create_by_userid]);

// Get school details
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

// If no students found
if (empty($students)) {
    // Get student count to show helpful message
    $studentCount = db_get_val(
        "SELECT COUNT(*) FROM manage_student WHERE class = ? AND session = ? AND term_id = ? AND create_by_userid = ?",
        [$classId, $sessionId, $termId, $create_by_userid]
    );

    if ($studentCount == 0) {
        die("No students found for this class, session, and term.");
    }
}

// Calculate totals
$totalPresent = 0;
$totalAbsent = 0;
$totalStudents = count($students);

foreach ($students as $s) {
    $totalPresent += (int)$s['present'];
    $totalAbsent += (int)$s['absent'];
}

$totalDays = $totalPresent + $totalAbsent;
$attendanceRate = $totalDays > 0
    ? round(($totalPresent / $totalDays) * 100, 1)
    : 0;

// Determine attendance rate color
$rateColor = '#28a745'; // Green for good
if ($attendanceRate < 60) {
    $rateColor = '#dc3545'; // Red for poor
} elseif ($attendanceRate < 80) {
    $rateColor = '#f59e0b'; // Amber for average
}

// ============================================================================
// GENERATE HTML - MOBILE COMPATIBLE
// ============================================================================
ob_start();
?>
<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Attendance Report</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', 'Segoe UI', Arial, sans-serif;
            font-size: 10px;
            padding: 12px;
            color: #333;
            background: #fff;
            -webkit-text-size-adjust: 100%;
        }

        /* ============================================================
        HEADER
        ============================================================ */
        .header {
            text-align: center;
            border-bottom: 2px solid #1B3058;
            padding-bottom: 10px;
            margin-bottom: 14px;
        }

        .school-name {
            font-size: 20px;
            font-weight: 700;
            color: #1B3058;
            word-wrap: break-word;
        }

        .school-moto {
            font-size: 9px;
            color: #666;
            font-style: italic;
        }

        .school-address {
            font-size: 8px;
            color: #888;
            word-wrap: break-word;
        }

        .report-title {
            font-size: 14px;
            font-weight: 700;
            color: #1B3058;
            margin: 6px 0 2px;
        }

        .report-subtitle {
            font-size: 9px;
            color: #666;
            line-height: 1.4;
        }

        .report-subtitle .generated {
            font-size: 7px;
            color: #999;
            display: block;
            margin-top: 1px;
        }

        /* ============================================================
        SUMMARY - CLEAN FLEX LAYOUT (NO ICONS)
        ============================================================ */
        .summary-bar {
            display: flex;
            flex-wrap: wrap;
            align-items: stretch;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 0;
            margin-bottom: 14px;
            overflow: hidden;
        }

        .summary-item {
            flex: 1 1 0;
            min-width: 80px;
            padding: 10px 14px;
            text-align: center;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            border-right: 1px solid #e2e8f0;
        }

        .summary-item:last-child {
            border-right: none;
        }

        .summary-item .value {
            font-size: 20px;
            font-weight: 700;
            color: #1B3058;
            line-height: 1.2;
        }

        .summary-item .label {
            font-size: 7.5px;
            color: #888;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 500;
            margin-top: 2px;
        }

        /* Color-coded items */
        .summary-item.present .value {
            color: #28a745;
        }

        .summary-item.absent .value {
            color: #dc3545;
        }

        .summary-item.rate .value {
            color: <?= $rateColor ?>;
        }

        /* ============================================================
        TABLE - MOBILE COMPATIBLE
        ============================================================ */
        .table-wrapper {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            margin: 0 -4px;
            padding: 0 4px;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8px;
            min-width: 500px;
        }

        .table th {
            background: #1B3058;
            color: #fff;
            padding: 5px 6px;
            text-align: left;
            font-weight: 600;
            font-size: 7.5px;
            white-space: nowrap;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .table td {
            padding: 4px 6px;
            border-bottom: 1px solid #eee;
            vertical-align: middle;
            font-size: 8px;
        }

        .table .text-right {
            text-align: right;
        }

        .table .text-center {
            text-align: center;
        }

        /* ============================================================
        STATUS BADGES
        ============================================================ */
        .status-badge {
            display: inline-block;
            padding: 1px 10px;
            border-radius: 12px;
            font-size: 7px;
            font-weight: 600;
            text-align: center;
            white-space: nowrap;
        }

        .status-badge.good {
            background: #d4edda;
            color: #155724;
        }

        .status-badge.average {
            background: #fff3cd;
            color: #856404;
        }

        .status-badge.poor {
            background: #f8d7da;
            color: #721c24;
        }

        /* ============================================================
        FOOTER
        ============================================================ */
        .footer {
            margin-top: 14px;
            padding-top: 10px;
            border-top: 1px solid #e2e8f0;
            font-size: 7px;
            color: #888;
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            align-items: center;
        }

        .footer .footer-left {
            font-size: 6.5px;
        }

        .footer .footer-right {
            text-align: right;
        }

        .signature-line {
            margin-top: 4px;
            padding-top: 4px;
            border-top: 1px solid #333;
            width: 150px;
            text-align: center;
            font-size: 7px;
            margin-left: auto;
        }

        .signature-line .line {
            display: block;
            font-size: 6px;
            color: #999;
        }

        /* ============================================================
        NO DATA
        ============================================================ */
        .no-data {
            text-align: center;
            padding: 20px 10px;
            color: #999;
            font-size: 10px;
        }

        .no-data .icon {
            font-size: 28px;
            display: block;
            margin-bottom: 6px;
        }

        /* ============================================================
        PRINT STYLES
        ============================================================ */
        @media print {
            body {
                padding: 8px;
                font-size: 9px;
            }

            .table th {
                background: #1B3058 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .summary-bar {
                border: 1px solid #ddd;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .status-badge {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .table {
                font-size: 7.5px;
                min-width: auto;
            }
        }

        /* ============================================================
        MOBILE (≤480px) - STACKED SUMMARY
        ============================================================ */
        @media (max-width: 480px) {
            body {
                padding: 6px;
            }

            .school-name {
                font-size: 15px;
            }

            .report-title {
                font-size: 11px;
            }

            .summary-bar {
                flex-direction: column;
                border-radius: 6px;
            }

            .summary-item {
                border-right: none;
                border-bottom: 1px solid #e2e8f0;
                padding: 8px 12px;
                flex-direction: row;
                justify-content: space-between;
                align-items: center;
                min-width: unset;
            }

            .summary-item:last-child {
                border-bottom: none;
            }

            .summary-item .value {
                font-size: 16px;
                order: 2;
            }

            .summary-item .label {
                font-size: 7px;
                order: 1;
                margin-top: 0;
            }

            .table td,
            .table th {
                padding: 3px 4px;
                font-size: 6.5px;
            }

            .table th {
                font-size: 6px;
            }

            .status-badge {
                font-size: 5.5px;
                padding: 1px 6px;
            }
        }

        /* ============================================================
        TABLET (≥768px)
        ============================================================ */
        @media (min-width: 768px) {
            body {
                padding: 20px;
                font-size: 10px;
            }

            .summary-item {
                padding: 12px 20px;
            }

            .summary-item .value {
                font-size: 24px;
            }

            .summary-item .label {
                font-size: 8.5px;
            }

            .table {
                font-size: 9px;
                min-width: auto;
            }

            .table td,
            .table th {
                padding: 6px 10px;
            }

            .table th {
                font-size: 8.5px;
            }
        }
    </style>
</head>

<body>

    <!-- ============================================================
HEADER
============================================================ -->
    <div class="header">
        <div class="school-name"><?= htmlspecialchars($school['name'] ?? 'School Name') ?></div>
        <div class="school-moto"><?= htmlspecialchars($school['moto'] ?? 'Excellence in Education') ?></div>
        <div class="school-address">
            <?= htmlspecialchars($school['location'] ?? '') ?>
            <?php if (!empty($school['phone'])): ?> | Tel: <?= htmlspecialchars($school['phone']) ?><?php endif; ?>
        </div>
        <div class="report-title">ATTENDANCE REPORT</div>
        <div class="report-subtitle">
            <?= htmlspecialchars($sessionName ?? 'N/A') ?> · <?= htmlspecialchars($termName ?? 'N/A') ?> · <?= htmlspecialchars($className ?? 'N/A') ?>
            <span class="generated">Generated: <?= date('d M Y H:i') ?></span>
        </div>
    </div>

    <!-- ============================================================
SUMMARY - CLEAN FLEX LAYOUT (NO ICONS)
============================================================ -->
    <div class="summary-bar">
        <div class="summary-item">
            <span class="value"><?= $totalStudents ?></span>
            <span class="label">Students</span>
        </div>

        <div class="summary-item present">
            <span class="value"><?= $totalPresent ?></span>
            <span class="label">Present</span>
        </div>

        <div class="summary-item absent">
            <span class="value"><?= $totalAbsent ?></span>
            <span class="label">Absent</span>
        </div>

        <div class="summary-item rate">
            <span class="value"><?= $attendanceRate ?>%</span>
            <span class="label">Attendance</span>
        </div>
    </div>

    <!-- ============================================================
TABLE
============================================================ -->
    <div class="table-wrapper">
        <table class="table">
            <thead>
                <tr>
                    <th style="width:30px;">#</th>
                    <th style="min-width:70px;">Student ID</th>
                    <th style="min-width:90px;">Student Name</th>
                    <th style="width:45px;" class="text-right">Present</th>
                    <th style="width:45px;" class="text-right">Absent</th>
                    <th style="width:45px;" class="text-right">Total</th>
                    <th style="width:45px;" class="text-right">%</th>
                    <th style="min-width:55px;">Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($students)): ?>
                    <?php $i = 0;
                    foreach ($students as $student): $i++;
                        $present = (int)$student['present'];
                        $absent = (int)$student['absent'];
                        $total = $present + $absent;
                        $percent = $total > 0 ? round(($present / $total) * 100, 0) : 0;

                        if ($percent >= 80) {
                            $status = 'Good';
                            $statusClass = 'good';
                        } elseif ($percent >= 60) {
                            $status = 'Average';
                            $statusClass = 'average';
                        } else {
                            $status = 'Poor';
                            $statusClass = 'poor';
                        }
                    ?>
                        <tr>
                            <td class="text-center"><?= $i ?></td>
                            <td><?= htmlspecialchars($student['student_id'] ?? '') ?></td>
                            <td><?= htmlspecialchars(trim(($student['first_name'] ?? '') . ' ' . ($student['last_name'] ?? ''))) ?></td>
                            <td class="text-right"><?= $present ?></td>
                            <td class="text-right"><?= $absent ?></td>
                            <td class="text-right"><?= $total ?></td>
                            <td class="text-right"><?= $percent ?>%</td>
                            <td>
                                <span class="status-badge <?= $statusClass ?>">
                                    <?= $status ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8">
                            <div class="no-data">
                                <span class="icon">📭</span>
                                No attendance data found
                            </div>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- ============================================================
FOOTER
============================================================ -->
    <div class="footer">
        <div class="footer-left">
            <strong>Generated:</strong> <?= date('d M Y H:i') ?>
            <div style="margin-top:1px; color:#aaa; font-size:6px;">
                System-generated · Requires verification
            </div>
        </div>
        <div class="footer-right">
            <div style="font-size:8px; font-weight:600; color:#1B3058;">
                <?= htmlspecialchars($school['name'] ?? '') ?>
            </div>
            <div class="signature-line">
                Authorized Signature
                <span class="line">_________________________</span>
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
$options->set('defaultFont', 'DejaVu Sans');
$dompdf->setOptions($options);

$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

$filename = 'Attendance_Report_' . date('Y-m-d') . '.pdf';
$dompdf->stream($filename, ['Attachment' => true]);
?>