<?php
/**
 * ============================================================================
 * FEE STATUS REPORT - Defaulters & Paid Students
 * ============================================================================
 * Description: View and print students by payment status per term
 * Features: Filter by Session, Term, Class, Payment Status
 * Version: 3.0 (PHP 8.x Compatible)
 * ============================================================================
 */

require_once('../config.php');
require_once('inc.session-create.php');

$PageTitle = "Fee Status Report";
$FileName = 'fee_status_report.php';

// ============================================================================
// INITIALIZATION
// ============================================================================
$stat = [];
$create_by_userid = (int)($_SESSION['userid'] ?? 0);
$create_by_usertype = (string)($_SESSION['usertype'] ?? '');

// ============================================================================
// GET FILTERS
// ============================================================================
$selectedSession = $_GET['session'] ?? '';
$selectedTerm = $_GET['term_id'] ?? '';
$selectedClass = $_GET['class_id'] ?? '';
$selectedStatus = $_GET['status'] ?? 'all'; // all, paid, defaulters, partial, scholarship, no_fee
$exportAction = $_GET['export'] ?? ''; // pdf, excel

// ============================================================================
// GET DATA FOR FILTERS
// ============================================================================
$sessions = db_get_rows(
    "SELECT * FROM school_session 
     WHERE create_by_userid = ? 
     ORDER BY id DESC",
    [$create_by_userid]
);

$terms = db_get_rows(
    "SELECT * FROM school_term 
     WHERE create_by_userid = ? 
     ORDER BY id ASC",
    [$create_by_userid]
);

$classes = db_get_rows(
    "SELECT * FROM school_class 
     WHERE create_by_userid = ? 
     ORDER BY name ASC",
    [$create_by_userid]
);

// ============================================================================
// BUILD REPORT DATA
// ============================================================================
$reportData = [];
$summary = [
    'total_students' => 0,
    'paid' => 0,
    'defaulters' => 0,
    'partial' => 0,
    'scholarship' => 0,
    'no_fee' => 0,
    'total_outstanding' => 0,
    'total_collected' => 0
];

if (!empty($selectedSession) && !empty($selectedTerm) && !empty($selectedClass)) {
    // Build query
    $sql = "SELECT 
            ms.id,
            ms.student_id,
            ms.first_name,
            ms.last_name,
            ms.class,
            sc.name as class_name,
            sf.id as fee_id,
            sf.total_amount_to_pay,
            sf.discount_amount,
            sf.currently_paying_amount,
            sf.remain_amount,
            sf.student_status,
            sf.invoiceno,
            sf.payment_status as fee_payment_status,
            sf.randomid as fee_randomid,
            CASE 
                -- Scholarship students ALWAYS show as Scholarship regardless of balance
                WHEN sf.student_status = 3 THEN 'scholarship'
                -- No fee record
                WHEN sf.id IS NULL THEN 'no_fee'
                -- Fully paid (after discounts)
                WHEN sf.remain_amount = 0 THEN 'paid'
                -- Partial payment
                WHEN sf.remain_amount > 0 AND sf.currently_paying_amount > 0 THEN 'partial'
                -- Defaulter
                WHEN sf.remain_amount > 0 AND sf.currently_paying_amount = 0 THEN 'defaulter'
                ELSE 'no_fee'
            END as status_key,
            CASE 
                WHEN sf.student_status = 3 THEN 'Scholarship'
                WHEN sf.id IS NULL THEN 'No Fee Record'
                WHEN sf.remain_amount = 0 THEN 'Paid'
                WHEN sf.remain_amount > 0 AND sf.currently_paying_amount > 0 THEN 'Partial'
                WHEN sf.remain_amount > 0 AND sf.currently_paying_amount = 0 THEN 'Defaulter'
                ELSE 'Unknown'
            END as payment_status
        FROM manage_student ms
        LEFT JOIN school_class sc ON ms.class = sc.id
        LEFT JOIN student_fee sf ON ms.id = sf.student_id 
            AND sf.session = ? 
            AND sf.term_id = ?
            AND sf.class = ?
            AND sf.create_by_userid = ?
        WHERE ms.create_by_userid = ?
            AND ms.class = ?
            AND ms.session = ?
            AND ms.term_id = ?
        ORDER BY 
            CASE 
                WHEN sf.student_status = 3 THEN 1
                WHEN sf.remain_amount = 0 THEN 2
                ELSE 3
            END,
            ms.first_name ASC";
    $params = [
        $selectedSession, $selectedTerm, $selectedClass, $create_by_userid,
        $create_by_userid, $selectedClass, $selectedSession, $selectedTerm
    ];

    $reportData = db_get_rows($sql, $params);

    // Calculate summary
    $summary['total_students'] = count($reportData);
    $summary['total_outstanding'] = 0;
    $summary['total_collected'] = 0;

    foreach ($reportData as $row) {
        $status = $row['status_key'] ?? 'no_fee';
        $summary[$status] = ($summary[$status] ?? 0) + 1;
        $summary['total_outstanding'] += (float)($row['remain_amount'] ?? 0);
        $summary['total_collected'] += (float)($row['currently_paying_amount'] ?? 0);
    }
}
   // Filter by status
if ($selectedStatus !== 'all' && !empty($reportData)) {
    $reportData = array_filter($reportData, function($row) use ($selectedStatus) {
        $statusKey = (string)($row['status_key'] ?? '');
        $selected = (string)$selectedStatus;
        return $statusKey === $selected;
    });
    $reportData = array_values($reportData);
}

// ============================================================================
// EXPORT HANDLERS
// ============================================================================
if ($exportAction === 'excel' && !empty($reportData)) {
    // Excel export
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="Fee_Status_Report_' . date('Y-m-d') . '.csv"');
    header('Pragma: no-cache');
    header('Expires: 0');
    
    $output = fopen('php://output', 'w');
    fwrite($output, "\xEF\xBB\xBF"); // UTF-8 BOM
    
    // Headers
    fputcsv($output, [
        'S/N', 'Student ID', 'Student Name', 'Class', 'Total Fee', 
        'Discount', 'Amount Paid', 'Outstanding', 'Status'
    ]);
    
    $counter = 0;
    foreach ($reportData as $row) {
        $counter++;
        fputcsv($output, [
            $counter,
            $row['student_id'] ?? '',
            trim(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? '')),
            $row['class_name'] ?? '',
            number_format((float)($row['total_amount_to_pay'] ?? 0), 2),
            number_format((float)($row['discount_amount'] ?? 0), 2),
            number_format((float)($row['currently_paying_amount'] ?? 0), 2),
            number_format((float)($row['remain_amount'] ?? 0), 2),
            $row['payment_status'] ?? 'Unknown'
        ]);
    }
    fclose($output);
    exit;
}

// ============================================================================
// HELPER FUNCTIONS
// ============================================================================
function getStatusBadge($status) {
    $badges = [
        'paid' => '<span class="badge badge-success">✅ Paid</span>',
        'defaulter' => '<span class="badge badge-danger">❌ Defaulter</span>',
        'partial' => '<span class="badge badge-warning">⏳ Partial</span>',
        'scholarship' => '<span class="badge badge-info">🎓 Scholarship</span>',
        'no_fee' => '<span class="badge badge-secondary">No Fee</span>',
    ];
    return $badges[(string)$status] ?? '<span class="badge badge-secondary">Unknown</span>';
}
function getStatusColor($status) {
    $colors = [
        'paid' => '#28a745',
        'defaulter' => '#dc3545',
        'partial' => '#ffc107',
        'scholarship' => '#17a2b8',
        'no_fee' => '#6c757d',
    ];
    return $colors[$status] ?? '#6c757d';
}

function getPaymentStatusLabel($status) {
    $labels = [
        'paid' => 'Paid',
        'defaulter' => 'Defaulter',
        'partial' => 'Partial Payment',
        'scholarship' => 'Scholarship',
        'no_fee' => 'No Fee Record',
    ];
    return $labels[$status] ?? 'Unknown';
}

function getStatusOptions($selected = 'all') {
    $options = [
        'all' => 'All Students',
        'paid' => '✅ Paid Only',
        'defaulter' => '❌ Defaulters Only',
        'partial' => '⏳ Partial Payment Only',
        'scholarship' => '🎓 Scholarship Only',
        'no_fee' => 'No Fee Record Only'
    ];
    
    $html = '';
    foreach ($options as $value => $label) {
        $sel = ($selected == $value) ? 'selected' : '';
        $html .= '<option value="' . $value . '" ' . $sel . '>' . $label . '</option>';
    }
    return $html;
}
?>
<!DOCTYPE html>
<html>
<head>
    <?php include('inc.meta.php'); ?>
    <title><?= htmlspecialchars($PageTitle) ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <style>
        * { box-sizing: border-box; }
        body { background: #f0f2f5; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .report-container { max-width: 1400px; margin: 0 auto; padding: 20px; }
        
        .page-header { margin-bottom: 25px; }
        .page-header h2 { color: #1B3058; font-size: 28px; margin: 0; }
        .page-header p { color: #666; margin-top: 5px; }
        
        .filter-card { background: #fff; border-radius: 16px; padding: 25px; box-shadow: 0 2px 12px rgba(0,0,0,0.08); margin-bottom: 25px; }
        .filter-row { display: flex; flex-wrap: wrap; gap: 15px; align-items: flex-end; }
        .filter-group { flex: 1; min-width: 170px; }
        .filter-group label { display: block; font-size: 12px; font-weight: 700; color: #888; text-transform: uppercase; margin-bottom: 5px; letter-spacing: 0.5px; }
        .filter-select, .filter-input { width: 100%; padding: 10px 14px; border: 2px solid #e0e0e0; border-radius: 10px; font-size: 14px; background: #fff; transition: all 0.2s; }
        .filter-select:focus, .filter-input:focus { border-color: #1B3058; outline: none; }
        
        .btn { display: inline-flex; align-items: center; gap: 8px; padding: 10px 22px; border: none; border-radius: 10px; font-size: 14px; font-weight: 600; cursor: pointer; transition: all 0.3s; text-decoration: none; }
        .btn-primary { background: #1B3058; color: #fff; }
        .btn-primary:hover { background: #f21151; transform: translateY(-2px); }
        .btn-success { background: #28a745; color: #fff; }
        .btn-success:hover { background: #218838; transform: translateY(-2px); }
        .btn-danger { background: #dc3545; color: #fff; }
        .btn-danger:hover { background: #c82333; transform: translateY(-2px); }
        .btn-info { background: #17a2b8; color: #fff; }
        .btn-info:hover { background: #138496; transform: translateY(-2px); }
        .btn-warning { background: #ffc107; color: #333; }
        .btn-warning:hover { background: #e0a800; transform: translateY(-2px); }
        .btn-sm { padding: 5px 12px; font-size: 12px; }
        
        .summary-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 15px; margin-bottom: 20px; }
        .summary-box { background: #fff; border-radius: 12px; padding: 18px 20px; text-align: center; border-left: 4px solid #1B3058; box-shadow: 0 2px 8px rgba(0,0,0,0.05); }
        .summary-box .number { font-size: 28px; font-weight: 700; color: #1B3058; }
        .summary-box .label { font-size: 12px; color: #888; text-transform: uppercase; font-weight: 600; margin-top: 4px; }
        .summary-box.paid { border-color: #28a745; }
        .summary-box.paid .number { color: #28a745; }
        .summary-box.defaulter { border-color: #dc3545; }
        .summary-box.defaulter .number { color: #dc3545; }
        .summary-box.partial { border-color: #ffc107; }
        .summary-box.partial .number { color: #856404; }
        .summary-box.scholarship { border-color: #17a2b8; }
        .summary-box.scholarship .number { color: #17a2b8; }
        .summary-box.total { border-color: #1B3058; }
        
        .table-wrapper { background: #fff; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 12px rgba(0,0,0,0.08); }
        .table-scroll { overflow-x: auto; }
        .report-table { width: 100%; border-collapse: collapse; font-size: 13px; }
        .report-table th { background: #1B3058; color: #fff; padding: 12px 16px; text-align: left; font-weight: 600; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px; position: sticky; top: 0; }
        .report-table td { padding: 10px 16px; border-bottom: 1px solid #eee; vertical-align: middle; }
        .report-table tr:hover { background: #f8f9ff; }
        
        .badge { display: inline-block; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; }
        .badge-success { background: #d4edda; color: #155724; }
        .badge-danger { background: #f8d7da; color: #721c24; }
        .badge-warning { background: #fff3cd; color: #856404; }
        .badge-info { background: #d1ecf1; color: #0c5460; }
        .badge-secondary { background: #e2e3e5; color: #383d41; }
        
        .action-bar { display: flex; gap: 10px; flex-wrap: wrap; margin: 15px 0 10px; justify-content: flex-end; }
        
        .text-muted { color: #999; text-align: center; padding: 40px; }
        .text-center { text-align: center; }
        
        @media print {
            .filter-card, .action-bar, .btn { display: none; }
            body { background: #fff; }
            .report-container { padding: 10px; }
            .table-wrapper { box-shadow: none; border: 1px solid #ddd; }
            .report-table th { background: #1B3058 !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
        @media (max-width: 768px) {
            .filter-row { flex-direction: column; }
            .filter-group { min-width: 100%; }
            .summary-grid { grid-template-columns: 1fr 1fr; }
            .report-table th, .report-table td { padding: 8px 10px; font-size: 12px; }
        }
    </style>
</head>
<body>
<div id="wrapper">
    <?php include('inc.header.php'); ?>
    <?php include('inc.sideleft.php'); ?>
    
    <div class="content-page">
        <div class="content">
            <div class="report-container">
                
                <!-- Page Header -->
                <div class="page-header">
                    <h2><i class="fa fa-file-text-o"></i> <?= htmlspecialchars($PageTitle) ?></h2>
                    <p>View and print students by payment status per term</p>
                </div>
                
                <?= msg($stat) ?>
                
                <!-- Filter Bar -->
                <div class="filter-card">
                    <form method="GET" action="" id="filterForm">
                        <div class="filter-row">
                            <div class="filter-group">
                                <label>Session *</label>
                                <select name="session" class="filter-select" required>
                                    <option value="">-- Select Session --</option>
                                    <?php foreach ($sessions as $s): ?>
                                        <option value="<?= $s['id'] ?>" <?= ($selectedSession == $s['id']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($s['session']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="filter-group">
                                <label>Term *</label>
                                <select name="term_id" class="filter-select" required>
                                    <option value="">-- Select Term --</option>
                                    <?php foreach ($terms as $t): ?>
                                        <option value="<?= $t['id'] ?>" <?= ($selectedTerm == $t['id']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($t['term']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="filter-group">
                                <label>Class *</label>
                                <select name="class_id" class="filter-select" required>
                                    <option value="">-- Select Class --</option>
                                    <?php foreach ($classes as $c): ?>
                                        <option value="<?= $c['id'] ?>" <?= ($selectedClass == $c['id']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($c['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="filter-group">
                                <label>Payment Status</label>
                                <select name="status" class="filter-select">
                                    <?= getStatusOptions($selectedStatus) ?>
                                </select>
                            </div>
                            <div class="filter-group" style="flex: 0 0 auto; display: flex; gap: 8px; align-items: flex-end;">
                                <button type="submit" class="btn btn-primary"><i class="fa fa-filter"></i> Filter</button>
                                <a href="<?= $FileName ?>" class="btn btn-danger"><i class="fa fa-refresh"></i></a>
                            </div>
                        </div>
                    </form>
                </div>
                
                <?php if (!empty($reportData)): ?>
                
                <!-- Summary Cards -->
                <div class="summary-grid">
                    <div class="summary-box total">
                        <div class="number"><?= $summary['total_students'] ?></div>
                        <div class="label">Total Students</div>
                    </div>
                    <div class="summary-box paid">
                        <div class="number"><?= ($summary['paid'] ?? 0) + ($summary['scholarship'] ?? 0) ?></div>
                        <div class="label">Paid (incl. Scholarship)</div>
                    </div>
                    <div class="summary-box defaulter">
                        <div class="number"><?= ($summary['defaulter'] ?? 0) + ($summary['partial'] ?? 0) ?></div>
                        <div class="label">Defaulters (incl. Partial)</div>
                    </div>
                    <div class="summary-box">
                        <div class="number" style="color: #28a745;">₦<?= number_format($summary['total_collected'], 0) ?></div>
                        <div class="label">Total Collected</div>
                    </div>
                    <div class="summary-box">
                        <div class="number" style="color: #dc3545;">₦<?= number_format($summary['total_outstanding'], 0) ?></div>
                        <div class="label">Total Outstanding</div>
                    </div>
                </div>
                
                <!-- Action Bar -->
                <div class="action-bar">
                    <a href="<?= $FileName ?>?session=<?= $selectedSession ?>&term_id=<?= $selectedTerm ?>&class_id=<?= $selectedClass ?>&status=<?= $selectedStatus ?>&export=excel" 
                       class="btn btn-success"><i class="fa fa-file-excel-o"></i> Export Excel</a>
                    <button onclick="window.print()" class="btn btn-primary"><i class="fa fa-print"></i> Print Report</button>
                    <a href="fee_status_report_pdf.php?session=<?= $selectedSession ?>&term_id=<?= $selectedTerm ?>&class_id=<?= $selectedClass ?>&status=<?= $selectedStatus ?>" 
                       target="_blank" class="btn btn-danger"><i class="fa fa-file-pdf-o"></i> Download PDF</a>
                </div>
                
                <!-- Table -->
                <div class="table-wrapper">
                    <div class="table-scroll">
                        <table class="report-table" id="reportTable">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Student ID</th>
                                    <th>Student Name</th>
                                    <th>Class</th>
                                    <th>Total Fee (₦)</th>
                                    <th>Discount (₦)</th>
                                    <th>Amount Paid (₦)</th>
                                    <th>Outstanding (₦)</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $counter = 0; foreach ($reportData as $row): $counter++; ?>
                                <tr>
                                    <td><?= $counter ?></td>
                                    <td><?= htmlspecialchars($row['student_id'] ?? '') ?></td>
                                    <td><strong><?= htmlspecialchars(trim(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? ''))) ?></strong></td>
                                    <td><?= htmlspecialchars($row['class_name'] ?? '') ?></td>
                                    <td><?= number_format((float)($row['total_amount_to_pay'] ?? 0), 2) ?></td>
                                    <td><?= number_format((float)($row['discount_amount'] ?? 0), 2) ?></td>
                                    <td><strong><?= number_format((float)($row['currently_paying_amount'] ?? 0), 2) ?></strong></td>
                                    <td style="font-weight:700; color:<?= ((float)($row['remain_amount'] ?? 0) > 0) ? '#dc3545' : '#28a745' ?>;">
                                        <?= number_format((float)($row['remain_amount'] ?? 0), 2) ?>
                                    </td>
                                    <td><?= getStatusBadge($row['status_key'] ?? 'no_fee') ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <?php elseif (!empty($selectedSession) && !empty($selectedTerm) && !empty($selectedClass)): ?>
                    <div class="text-muted" style="padding:60px 20px; text-align:center;">
                        <i class="fa fa-inbox" style="font-size:48px; display:block; margin-bottom:15px; color:#ddd;"></i>
                        <h4>No students found</h4>
                        <p style="color:#999;">No students match the selected filters. Try changing your filters.</p>
                    </div>
                <?php else: ?>
                    <div class="text-muted" style="padding:60px 20px; text-align:center;">
                        <i class="fa fa-filter" style="font-size:48px; display:block; margin-bottom:15px; color:#ddd;"></i>
                        <h4>Select Filters</h4>
                        <p style="color:#999;">Please select Session, Term, and Class to view the report.</p>
                    </div>
                <?php endif; ?>
                
            </div>
        </div>
        <?php include('inc.footer.php'); ?>
    </div>
</div>

<?php include('inc.js.php'); ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-submit on filter change (optional)
    // Remove this if you want manual submission
    const selects = document.querySelectorAll('.filter-select');
    selects.forEach(select => {
        select.addEventListener('change', function() {
            // Only auto-submit if all required fields have values
            const session = document.querySelector('select[name="session"]').value;
            const term = document.querySelector('select[name="term_id"]').value;
            const classId = document.querySelector('select[name="class_id"]').value;
            if (session && term && classId) {
                document.getElementById('filterForm').submit();
            }
        });
    });
});
</script>
</body>
</html>