<?php
/**
 * ============================================================================
 * PAYROLL MANAGEMENT SYSTEM
 * ============================================================================
 * Description: Manage staff salaries, process payroll, generate payslips
 * Features: Salary setup, monthly payroll, deductions, payslips
 * Version: 3.0 (PHP 8.x Compatible)
 * ============================================================================
 */

require_once('../config.php');
require_once('inc.session-create.php');

$PageTitle = "Payroll Management";
$FileName = 'payroll.php';

// ============================================================================
// INITIALIZATION
// ============================================================================
$stat = [];
$create_by_userid = (int)($_SESSION['userid'] ?? 0);
$create_by_usertype = (string)($_SESSION['usertype'] ?? '');

// ============================================================================
// GET FILTERS
// ============================================================================
$selectedMonth = $_GET['month'] ?? date('m');
$selectedYear = $_GET['year'] ?? date('Y');
$selectedStaff = $_GET['staff_id'] ?? '';
$selectedStatus = $_GET['status'] ?? '';

// ============================================================================
// HANDLE POST - SAVE SALARY SETUP
// ============================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_salary'])) {
    try {
        $staffId = (int)($_POST['staff_id'] ?? 0);
        $basicSalary = (float)($_POST['basic_salary'] ?? 0);
        $housingAllowance = (float)($_POST['housing_allowance'] ?? 0);
        $transportAllowance = (float)($_POST['transport_allowance'] ?? 0);
        $medicalAllowance = (float)($_POST['medical_allowance'] ?? 0);
        $feedingAllowance = (float)($_POST['feeding_allowance'] ?? 0);
        $otherAllowance = (float)($_POST['other_allowance'] ?? 0);
        
        $grossSalary = $basicSalary + $housingAllowance + $transportAllowance + $medicalAllowance + $feedingAllowance + $otherAllowance;
        
        // Check if salary already exists
        $existing = db_get_row(
            "SELECT id FROM salary_setup WHERE staff_id = ? AND is_active = 1",
            [$staffId]
        );
        
        if ($existing) {
            // Deactivate old salary
            db_update("salary_setup", ['is_active' => 0], "staff_id = ? AND id = ?", [$staffId, $existing['id']]);
        }
        
        $lastId = (int)db_get_val("SELECT MAX(id) FROM salary_setup") + 1;
        $randomId = randomFix(15) . $lastId;
        
        $data = [
            'staff_id' => $staffId,
            'basic_salary' => $basicSalary,
            'housing_allowance' => $housingAllowance,
            'transport_allowance' => $transportAllowance,
            'medical_allowance' => $medicalAllowance,
            'feeding_allowance' => $feedingAllowance,
            'other_allowance' => $otherAllowance,
            'gross_salary' => $grossSalary,
            'effective_date' => $_POST['effective_date'] ?? date('Y-m-d'),
            'is_active' => 1,
            'create_by_userid' => $create_by_userid,
            'create_by_usertype' => $create_by_usertype,
            'randomid' => $randomId
        ];
        
        db_insert("salary_setup", $data);
        
        $_SESSION['success'] = "Salary setup saved successfully! Gross Salary: ₦" . number_format($grossSalary, 2);
        redirect($FileName);
        
    } catch (Exception $e) {
        $stat['error'] = $e->getMessage();
    }
}

// ============================================================================
// HANDLE POST - PROCESS PAYROLL
// ============================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['process_payroll'])) {
    try {
        $month = (int)($_POST['month'] ?? date('m'));
        $year = (int)($_POST['year'] ?? date('Y'));
        $staffIds = $_POST['staff_ids'] ?? [];
        
        if (empty($staffIds)) {
            throw new Exception("No staff selected for payroll");
        }
        
        $processedCount = 0;
        
        foreach ($staffIds as $staffId) {
            // Get staff salary setup
            $salary = db_get_row(
                "SELECT * FROM salary_setup WHERE staff_id = ? AND is_active = 1",
                [$staffId]
            );
            
            if (empty($salary)) {
                continue;
            }
            
            // Get staff deductions
            $deductions = db_get_rows(
                "SELECT sd.*, dt.name, dt.type, dt.value 
                 FROM staff_deductions sd
                 LEFT JOIN deduction_types dt ON sd.deduction_type_id = dt.id
                 WHERE sd.staff_id = ? AND sd.is_active = 1",
                [$staffId]
            );
            
            // Calculate payroll
            $basicSalary = (float)$salary['basic_salary'];
            $housingAllowance = (float)$salary['housing_allowance'];
            $transportAllowance = (float)$salary['transport_allowance'];
            $medicalAllowance = (float)$salary['medical_allowance'];
            $feedingAllowance = (float)$salary['feeding_allowance'];
            $otherAllowance = (float)$salary['other_allowance'];
            
            $grossPay = $basicSalary + $housingAllowance + $transportAllowance + $medicalAllowance + $feedingAllowance + $otherAllowance;
            
            // Calculate deductions
            $payeTax = 0;
            $pension = 0;
            $nhf = 0;
            $unionDues = 0;
            $loanDeduction = 0;
            $otherDeductions = 0;
            
            foreach ($deductions as $ded) {
                $amount = (float)$ded['amount'];
                if ($ded['type'] == 'percentage') {
                    $amount = ($grossPay * (float)$ded['value']) / 100;
                }
                
                switch ($ded['code']) {
                    case 'PAYE':
                        $payeTax += $amount;
                        break;
                    case 'PENSION':
                        $pension += $amount;
                        break;
                    case 'NHF':
                        $nhf += $amount;
                        break;
                    case 'UNION':
                        $unionDues += $amount;
                        break;
                    default:
                        $otherDeductions += $amount;
                        break;
                }
            }
            
            $totalDeductions = $payeTax + $pension + $nhf + $unionDues + $loanDeduction + $otherDeductions;
            $netPay = $grossPay - $totalDeductions;
            
            // Check if payroll already exists for this staff month
            $existingPayroll = db_get_row(
                "SELECT id FROM payroll WHERE staff_id = ? AND month = ? AND year = ?",
                [$staffId, $month, $year]
            );
            
            if ($existingPayroll) {
                continue; // Skip if already processed
            }
            
            $lastId = (int)db_get_val("SELECT MAX(id) FROM payroll") + 1;
            $randomId = randomFix(15) . $lastId;
            
            $payrollData = [
                'staff_id' => $staffId,
                'month' => $month,
                'year' => $year,
                'basic_salary' => $basicSalary,
                'housing_allowance' => $housingAllowance,
                'transport_allowance' => $transportAllowance,
                'medical_allowance' => $medicalAllowance,
                'feeding_allowance' => $feedingAllowance,
                'other_allowance' => $otherAllowance,
                'gross_pay' => $grossPay,
                'paye_tax' => $payeTax,
                'pension' => $pension,
                'nhf' => $nhf,
                'union_dues' => $unionDues,
                'loan_deduction' => $loanDeduction,
                'other_deductions' => $otherDeductions,
                'total_deductions' => $totalDeductions,
                'net_pay' => $netPay,
                'payment_status' => 'pending',
                'create_by_userid' => $create_by_userid,
                'create_by_usertype' => $create_by_usertype,
                'randomid' => $randomId
            ];
            
            db_insert("payroll", $payrollData);
            $processedCount++;
        }
        
        $_SESSION['success'] = "Payroll processed for $processedCount staff members!";
        redirect($FileName . "?month=$month&year=$year");
        
    } catch (Exception $e) {
        $stat['error'] = $e->getMessage();
    }
}

// ============================================================================
// HANDLE POST - UPDATE PAYMENT STATUS
// ============================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    try {
        $payrollId = (int)($_POST['payroll_id'] ?? 0);
        $status = $_POST['status'] ?? '';
        
        if (empty($payrollId) || empty($status)) {
            throw new Exception("Invalid request");
        }
        
        $data = ['payment_status' => $status];
        
        if ($status == 'paid') {
            $data['payment_date'] = date('Y-m-d');
            $data['paid_by'] = $create_by_userid;
        }
        
        db_update("payroll", $data, "id = ?", [$payrollId]);
        
        $_SESSION['success'] = "Payment status updated successfully!";
        redirect($FileName . "?month=" . ($_GET['month'] ?? date('m')) . "&year=" . ($_GET['year'] ?? date('Y')));
        
    } catch (Exception $e) {
        $stat['error'] = $e->getMessage();
    }
}

// ============================================================================
// GET DATA
// ============================================================================

// Get all staff
$staffList = db_get_rows(
    "SELECT * FROM staff_manage 
     WHERE create_by_userid = ? 
     ORDER BY first_name ASC",
    [$create_by_userid]
);

// Get salary setups
$salarySetups = db_get_rows(
    "SELECT ss.*, sm.first_name, sm.last_name, sm.staff_id as staff_number
     FROM salary_setup ss
     LEFT JOIN staff_manage sm ON ss.staff_id = sm.id
     WHERE ss.create_by_userid = ? AND ss.is_active = 1
     ORDER BY sm.first_name ASC",
    [$create_by_userid]
);

// Get deduction types
$deductionTypes = db_get_rows(
    "SELECT * FROM deduction_types 
     WHERE create_by_userid = ? AND is_active = 1
     ORDER BY name ASC",
    [$create_by_userid]
);

// Get payroll records
$payrollQuery = "SELECT 
                    p.*,
                    sm.first_name,
                    sm.last_name,
                    sm.staff_id as staff_number
                FROM payroll p
                LEFT JOIN staff_manage sm ON p.staff_id = sm.id
                WHERE p.create_by_userid = ?";

$payrollParams = [$create_by_userid];

if (!empty($selectedMonth)) {
    $payrollQuery .= " AND p.month = ?";
    $payrollParams[] = (int)$selectedMonth;
}
if (!empty($selectedYear)) {
    $payrollQuery .= " AND p.year = ?";
    $payrollParams[] = (int)$selectedYear;
}
if (!empty($selectedStatus)) {
    $payrollQuery .= " AND p.payment_status = ?";
    $payrollParams[] = $selectedStatus;
}

$payrollQuery .= " ORDER BY p.created_at DESC";

$payrollRecords = db_get_rows($payrollQuery, $payrollParams);

// Get summary statistics
$summaryQuery = "SELECT 
                    COUNT(*) as total_processed,
                    SUM(gross_pay) as total_gross,
                    SUM(total_deductions) as total_deductions,
                    SUM(net_pay) as total_net
                FROM payroll
                WHERE create_by_userid = ? AND month = ? AND year = ?";

$summary = db_get_row($summaryQuery, [$create_by_userid, $selectedMonth, $selectedYear]);

// Get staff with no salary setup
$staffWithoutSalary = db_get_rows(
    "SELECT sm.* FROM staff_manage sm
     LEFT JOIN salary_setup ss ON sm.id = ss.staff_id AND ss.is_active = 1
     WHERE sm.create_by_userid = ? AND ss.id IS NULL
     ORDER BY sm.first_name ASC",
    [$create_by_userid]
);

// Get all salary setups for dropdown
$salarySetupsDropdown = db_get_rows(
    "SELECT ss.*, sm.first_name, sm.last_name
     FROM salary_setup ss
     LEFT JOIN staff_manage sm ON ss.staff_id = sm.id
     WHERE ss.create_by_userid = ? AND ss.is_active = 1
     ORDER BY sm.first_name ASC",
    [$create_by_userid]
);

// ============================================================================
// HELPER FUNCTIONS
// ============================================================================

function getStatusBadge($status) {
    $badges = [
        'pending' => '<span class="badge badge-warning">⏳ Pending</span>',
        'approved' => '<span class="badge badge-info">✅ Approved</span>',
        'paid' => '<span class="badge badge-success">💰 Paid</span>',
        'failed' => '<span class="badge badge-danger">❌ Failed</span>'
    ];
    return $badges[$status] ?? '<span class="badge badge-secondary">Unknown</span>';
}

function getMonthName($month) {
    $months = ['', 'January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
    return $months[(int)$month] ?? 'Unknown';
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
        .container { max-width: 1400px; margin: 0 auto; padding: 20px; }
        
        .page-header { margin-bottom: 25px; }
        .page-header h2 { color: #1B3058; font-size: 28px; margin: 0; }
        .page-header p { color: #666; margin-top: 5px; }
        
        .card { background: #fff; border-radius: 14px; box-shadow: 0 2px 12px rgba(0,0,0,0.08); margin-bottom: 25px; overflow: hidden; }
        .card-header { padding: 16px 24px; background: linear-gradient(135deg, #1B3058, #2a4780); color: #fff; font-weight: 600; font-size: 16px; }
        .card-body { padding: 24px; }
        
        .filter-row { display: flex; flex-wrap: wrap; gap: 15px; align-items: flex-end; margin-bottom: 20px; }
        .filter-group { flex: 1; min-width: 150px; }
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
        .btn-warning { background: #ffc107; color: #333; }
        .btn-warning:hover { background: #e0a800; transform: translateY(-2px); }
        .btn-info { background: #17a2b8; color: #fff; }
        .btn-info:hover { background: #138496; transform: translateY(-2px); }
        .btn-sm { padding: 5px 12px; font-size: 12px; }
        
        .table-wrapper { overflow-x: auto; }
        .table { width: 100%; border-collapse: collapse; font-size: 13px; }
        .table th { background: #f8f9fa; color: #1B3058; padding: 12px 16px; text-align: left; font-weight: 600; border-bottom: 2px solid #e0e0e0; }
        .table td { padding: 10px 16px; border-bottom: 1px solid #eee; vertical-align: middle; }
        .table tr:hover { background: #f8f9ff; }
        
        .badge { display: inline-block; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; }
        .badge-success { background: #d4edda; color: #155724; }
        .badge-danger { background: #f8d7da; color: #721c24; }
        .badge-warning { background: #fff3cd; color: #856404; }
        .badge-info { background: #d1ecf1; color: #0c5460; }
        .badge-secondary { background: #e2e3e5; color: #383d41; }
        
        .summary-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 15px; margin-bottom: 20px; }
        .summary-box { background: #fff; border-radius: 10px; padding: 15px 20px; text-align: center; border-left: 4px solid #1B3058; box-shadow: 0 2px 8px rgba(0,0,0,0.05); }
        .summary-box .number { font-size: 24px; font-weight: 700; color: #1B3058; }
        .summary-box .label { font-size: 11px; color: #888; text-transform: uppercase; font-weight: 600; margin-top: 4px; }
        
        .nav-tabs { display: flex; list-style: none; margin: 0; padding: 0; background: #f8f9fa; border-bottom: 2px solid #e0e0e0; flex-wrap: wrap; }
        .nav-tabs li a { display: block; padding: 12px 24px; color: #555; text-decoration: none; font-weight: 600; border-bottom: 3px solid transparent; transition: all 0.3s; }
        .nav-tabs li a:hover { color: #1B3058; background: #f0f0f0; }
        .nav-tabs li.active a { color: #1B3058; border-bottom-color: #1B3058; background: #fff; }
        
        .text-center { text-align: center; }
        .text-muted { color: #999; }
        .mt-20 { margin-top: 20px; }
        .mb-20 { margin-bottom: 20px; }
        
        .salary-form { background: #f8f9fa; padding: 20px; border-radius: 10px; }
        .form-row { display: flex; flex-wrap: wrap; gap: 15px; }
        .form-group { flex: 1; min-width: 150px; }
        .form-group label { display: block; font-size: 12px; font-weight: 600; color: #555; margin-bottom: 4px; }
        .form-control { width: 100%; padding: 8px 12px; border: 2px solid #e0e0e0; border-radius: 8px; font-size: 14px; transition: all 0.2s; }
        .form-control:focus { border-color: #1B3058; outline: none; }
        
        @media (max-width: 768px) {
            .filter-row { flex-direction: column; }
            .filter-group { min-width: 100%; }
            .form-row { flex-direction: column; }
            .form-group { min-width: 100%; }
            .summary-grid { grid-template-columns: 1fr 1fr; }
            .nav-tabs { flex-direction: column; }
        }
        @media (max-width: 480px) {
            .summary-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
<div id="wrapper">
    <?php include('inc.header.php'); ?>
    <?php include('inc.sideleft.php'); ?>
    
    <div class="content-page">
        <div class="content">
            <div class="container">
                
                <!-- Page Header -->
                <div class="page-header">
                    <h2><i class="fa fa-money"></i> <?= htmlspecialchars($PageTitle) ?></h2>
                    <p>Manage staff salaries, process payroll, and generate payslips</p>
                </div>
                
                <?= msg($stat) ?>
                
                <!-- Tabs -->
                <div class="card">
                    <ul class="nav-tabs">
                        <li class="<?= (!isset($_GET['tab']) || $_GET['tab'] == 'dashboard') ? 'active' : '' ?>">
                            <a href="<?= $FileName ?>?tab=dashboard"><i class="fa fa-dashboard"></i> Dashboard</a>
                        </li>
                        <li class="<?= (isset($_GET['tab']) && $_GET['tab'] == 'salary') ? 'active' : '' ?>">
                            <a href="<?= $FileName ?>?tab=salary"><i class="fa fa-cog"></i> Salary Setup</a>
                        </li>
                        <li class="<?= (isset($_GET['tab']) && $_GET['tab'] == 'process') ? 'active' : '' ?>">
                            <a href="<?= $FileName ?>?tab=process"><i class="fa fa-calculator"></i> Process Payroll</a>
                        </li>
                        <li class="<?= (isset($_GET['tab']) && $_GET['tab'] == 'history') ? 'active' : '' ?>">
                            <a href="<?= $FileName ?>?tab=history"><i class="fa fa-history"></i> Payroll History</a>
                        </li>
                    </ul>
                    
                    <div class="card-body">
                        
                        <!-- ============================================================ -->
                        <!-- TAB: DASHBOARD -->
                        <!-- ============================================================ -->
                        <?php if (!isset($_GET['tab']) || $_GET['tab'] == 'dashboard'): ?>
                        
                        <div class="summary-grid">
                            <div class="summary-box">
                                <div class="number"><?= number_format($summary['total_processed'] ?? 0) ?></div>
                                <div class="label">Staff Processed</div>
                            </div>
                            <div class="summary-box">
                                <div class="number">₦<?= number_format($summary['total_gross'] ?? 0, 2) ?></div>
                                <div class="label">Total Gross Pay</div>
                            </div>
                            <div class="summary-box">
                                <div class="number">₦<?= number_format($summary['total_deductions'] ?? 0, 2) ?></div>
                                <div class="label">Total Deductions</div>
                            </div>
                            <div class="summary-box" style="border-color: #28a745;">
                                <div class="number" style="color: #28a745;">₦<?= number_format($summary['total_net'] ?? 0, 2) ?></div>
                                <div class="label">Total Net Pay</div>
                            </div>
                        </div>
                        
                        <!-- Quick Stats -->
                        <div class="filter-row">
                            <div class="filter-group">
                                <label>Month</label>
                                <select class="filter-select" onchange="window.location.href='<?= $FileName ?>?month='+this.value+'&year=<?= $selectedYear ?>'">
                                    <?php for ($m = 1; $m <= 12; $m++): ?>
                                        <option value="<?= $m ?>" <?= ($selectedMonth == $m) ? 'selected' : '' ?>>
                                            <?= getMonthName($m) ?>
                                        </option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                            <div class="filter-group">
                                <label>Year</label>
                                <select class="filter-select" onchange="window.location.href='<?= $FileName ?>?month=<?= $selectedMonth ?>&year='+this.value">
                                    <?php for ($y = date('Y') - 5; $y <= date('Y'); $y++): ?>
                                        <option value="<?= $y ?>" <?= ($selectedYear == $y) ? 'selected' : '' ?>>
                                            <?= $y ?>
                                        </option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                            <div class="filter-group" style="flex: 0 0 auto;">
                                <a href="<?= $FileName ?>?tab=process" class="btn btn-success">
                                    <i class="fa fa-calculator"></i> Process New Payroll
                                </a>
                            </div>
                        </div>
                        
                        <!-- Recent Payroll -->
                        <h4>Recent Payroll Records</h4>
                        <div class="table-wrapper">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Staff</th>
                                        <th>Month/Year</th>
                                        <th>Gross Pay</th>
                                        <th>Deductions</th>
                                        <th>Net Pay</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($payrollRecords)): ?>
                                        <?php $i = 0; foreach ($payrollRecords as $record): $i++; ?>
                                        <tr>
                                            <td><?= $i ?></td>
                                            <td>
                                                <strong><?= htmlspecialchars(($record['first_name'] ?? '') . ' ' . ($record['last_name'] ?? '')) ?></strong>
                                                <br><small style="color:#888;"><?= htmlspecialchars($record['staff_number'] ?? '') ?></small>
                                            </td>
                                            <td><?= getMonthName($record['month']) ?> <?= $record['year'] ?></td>
                                            <td>₦<?= number_format((float)$record['gross_pay'], 2) ?></td>
                                            <td>₦<?= number_format((float)$record['total_deductions'], 2) ?></td>
                                            <td style="font-weight:700; color:#28a745;">₦<?= number_format((float)$record['net_pay'], 2) ?></td>
                                            <td><?= getStatusBadge($record['payment_status']) ?></td>
                                            <td>
                                                <a href="payslip.php?id=<?= $record['randomid'] ?>" target="_blank" class="btn btn-primary btn-sm">
                                                    <i class="fa fa-file-pdf-o"></i> Payslip
                                                </a>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="8" class="text-center text-muted" style="padding:30px;">
                                                No payroll records found for <?= getMonthName($selectedMonth) ?> <?= $selectedYear ?>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- ============================================================ -->
                        <!-- TAB: SALARY SETUP -->
                        <!-- ============================================================ -->
                        <?php elseif (isset($_GET['tab']) && $_GET['tab'] == 'salary'): ?>
                        
                        <h4><i class="fa fa-cog"></i> Staff Salary Setup</h4>
                        
                        <form method="POST" action="" class="salary-form">
                            <div class="form-row">
                                <div class="form-group">
                                    <label>Staff *</label>
                                    <select class="form-control" name="staff_id" required>
                                        <option value="">-- Select Staff --</option>
                                        <?php foreach ($staffList as $staff): ?>
                                            <option value="<?= $staff['id'] ?>">
                                                <?= htmlspecialchars(($staff['first_name'] ?? '') . ' ' . ($staff['last_name'] ?? '')) ?>
                                                (<?= htmlspecialchars($staff['staff_id'] ?? '') ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Effective Date</label>
                                    <input type="date" class="form-control" name="effective_date" value="<?= date('Y-m-d') ?>">
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label>Basic Salary (₦)</label>
                                    <input type="number" class="form-control" name="basic_salary" value="0" step="0.01" min="0">
                                </div>
                                <div class="form-group">
                                    <label>Housing Allowance (₦)</label>
                                    <input type="number" class="form-control" name="housing_allowance" value="0" step="0.01" min="0">
                                </div>
                                <div class="form-group">
                                    <label>Transport Allowance (₦)</label>
                                    <input type="number" class="form-control" name="transport_allowance" value="0" step="0.01" min="0">
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label>Medical Allowance (₦)</label>
                                    <input type="number" class="form-control" name="medical_allowance" value="0" step="0.01" min="0">
                                </div>
                                <div class="form-group">
                                    <label>Feeding Allowance (₦)</label>
                                    <input type="number" class="form-control" name="feeding_allowance" value="0" step="0.01" min="0">
                                </div>
                                <div class="form-group">
                                    <label>Other Allowance (₦)</label>
                                    <input type="number" class="form-control" name="other_allowance" value="0" step="0.01" min="0">
                                </div>
                            </div>
                            
                            <div style="margin-top: 15px;">
                                <button type="submit" name="save_salary" class="btn btn-success">
                                    <i class="fa fa-save"></i> Save Salary Setup
                                </button>
                            </div>
                        </form>
                        
                        <hr>
                        
                        <h5>Current Salary Setups</h5>
                        <div class="table-wrapper">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Staff</th>
                                        <th>Basic</th>
                                        <th>Housing</th>
                                        <th>Transport</th>
                                        <th>Medical</th>
                                        <th>Gross</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($salarySetupsDropdown)): ?>
                                        <?php $i = 0; foreach ($salarySetupsDropdown as $setup): $i++; ?>
                                        <tr>
                                            <td><?= $i ?></td>
                                            <td>
                                                <strong><?= htmlspecialchars(($setup['first_name'] ?? '') . ' ' . ($setup['last_name'] ?? '')) ?></strong>
                                            </td>
                                            <td>₦<?= number_format((float)$setup['basic_salary'], 2) ?></td>
                                            <td>₦<?= number_format((float)$setup['housing_allowance'], 2) ?></td>
                                            <td>₦<?= number_format((float)$setup['transport_allowance'], 2) ?></td>
                                            <td>₦<?= number_format((float)$setup['medical_allowance'], 2) ?></td>
                                            <td style="font-weight:700;">₦<?= number_format((float)$setup['gross_salary'], 2) ?></td>
                                            <td><span class="badge badge-success">Active</span></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="8" class="text-center text-muted" style="padding:20px;">
                                                No salary setups found. Please add staff salaries.
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <?php if (!empty($staffWithoutSalary)): ?>
                        <div class="alert alert-warning">
                            <strong>⚠️ Staff without salary setup:</strong>
                            <?php foreach ($staffWithoutSalary as $staff): ?>
                                <?= htmlspecialchars(($staff['first_name'] ?? '') . ' ' . ($staff['last_name'] ?? '')) ?>,
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                        
                        <!-- ============================================================ -->
                        <!-- TAB: PROCESS PAYROLL -->
                        <!-- ============================================================ -->
                        <?php elseif (isset($_GET['tab']) && $_GET['tab'] == 'process'): ?>
                        
                        <h4><i class="fa fa-calculator"></i> Process Payroll</h4>
                        
                        <form method="POST" action="">
                            <div class="filter-row">
                                <div class="filter-group">
                                    <label>Month *</label>
                                    <select class="filter-select" name="month" required>
                                        <?php for ($m = 1; $m <= 12; $m++): ?>
                                            <option value="<?= $m ?>" <?= ($selectedMonth == $m) ? 'selected' : '' ?>>
                                                <?= getMonthName($m) ?>
                                            </option>
                                        <?php endfor; ?>
                                    </select>
                                </div>
                                <div class="filter-group">
                                    <label>Year *</label>
                                    <select class="filter-select" name="year" required>
                                        <?php for ($y = date('Y') - 5; $y <= date('Y'); $y++): ?>
                                            <option value="<?= $y ?>" <?= ($selectedYear == $y) ? 'selected' : '' ?>>
                                                <?= $y ?>
                                            </option>
                                        <?php endfor; ?>
                                    </select>
                                </div>
                                <div class="filter-group" style="flex: 0 0 auto; display: flex; align-items: flex-end; gap: 8px;">
                                    <button type="submit" name="process_payroll" class="btn btn-success" 
                                            onclick="return confirm('Process payroll for selected staff? This will calculate all salaries and deductions.')">
                                        <i class="fa fa-calculator"></i> Process Payroll
                                    </button>
                                </div>
                            </div>
                            
                            <div class="table-wrapper">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th style="width:40px;">
                                                <input type="checkbox" id="selectAll" onchange="toggleAll(this)">
                                            </th>
                                            <th>Staff</th>
                                            <th>Staff ID</th>
                                            <th>Gross Salary</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($salarySetupsDropdown)): ?>
                                            <?php foreach ($salarySetupsDropdown as $setup): 
                                                // Check if already processed for this month
                                                $alreadyProcessed = db_get_val(
                                                    "SELECT id FROM payroll WHERE staff_id = ? AND month = ? AND year = ?",
                                                    [$setup['staff_id'], $selectedMonth, $selectedYear]
                                                );
                                            ?>
                                            <tr>
                                                <td>
                                                    <input type="checkbox" name="staff_ids[]" value="<?= $setup['staff_id'] ?>" 
                                                           <?= $alreadyProcessed ? 'disabled' : '' ?>
                                                           class="staff-checkbox">
                                                </td>
                                                <td>
                                                    <strong><?= htmlspecialchars(($setup['first_name'] ?? '') . ' ' . ($setup['last_name'] ?? '')) ?></strong>
                                                </td>
                                                <td><?= htmlspecialchars($setup['staff_id'] ?? '') ?></td>
                                                <td>₦<?= number_format((float)$setup['gross_salary'], 2) ?></td>
                                                <td>
                                                    <?php if ($alreadyProcessed): ?>
                                                        <span class="badge badge-success">✅ Already Processed</span>
                                                    <?php else: ?>
                                                        <span class="badge badge-warning">⏳ Pending</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="5" class="text-center text-muted" style="padding:20px;">
                                                    No staff with salary setup found. Please set up salaries first.
                                                </td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </form>
                        
                        <script>
                        function toggleAll(source) {
                            const checkboxes = document.querySelectorAll('.staff-checkbox:not(:disabled)');
                            checkboxes.forEach(cb => cb.checked = source.checked);
                        }
                        </script>
                        
                        <!-- ============================================================ -->
                        <!-- TAB: PAYROLL HISTORY -->
                        <!-- ============================================================ -->
                        <?php elseif (isset($_GET['tab']) && $_GET['tab'] == 'history'): ?>
                        
                        <h4><i class="fa fa-history"></i> Payroll History</h4>
                        
                        <div class="filter-row">
                            <div class="filter-group">
                                <label>Month</label>
                                <select class="filter-select" onchange="window.location.href='<?= $FileName ?>?tab=history&month='+this.value+'&year=<?= $selectedYear ?>&status=<?= $selectedStatus ?>'">
                                    <option value="">-- All --</option>
                                    <?php for ($m = 1; $m <= 12; $m++): ?>
                                        <option value="<?= $m ?>" <?= ($selectedMonth == $m) ? 'selected' : '' ?>>
                                            <?= getMonthName($m) ?>
                                        </option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                            <div class="filter-group">
                                <label>Year</label>
                                <select class="filter-select" onchange="window.location.href='<?= $FileName ?>?tab=history&month=<?= $selectedMonth ?>&year='+this.value+'&status=<?= $selectedStatus ?>'">
                                    <option value="">-- All --</option>
                                    <?php for ($y = date('Y') - 5; $y <= date('Y'); $y++): ?>
                                        <option value="<?= $y ?>" <?= ($selectedYear == $y) ? 'selected' : '' ?>>
                                            <?= $y ?>
                                        </option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                            <div class="filter-group">
                                <label>Status</label>
                                <select class="filter-select" onchange="window.location.href='<?= $FileName ?>?tab=history&month=<?= $selectedMonth ?>&year=<?= $selectedYear ?>&status='+this.value">
                                    <option value="">-- All --</option>
                                    <option value="pending" <?= ($selectedStatus == 'pending') ? 'selected' : '' ?>>Pending</option>
                                    <option value="approved" <?= ($selectedStatus == 'approved') ? 'selected' : '' ?>>Approved</option>
                                    <option value="paid" <?= ($selectedStatus == 'paid') ? 'selected' : '' ?>>Paid</option>
                                    <option value="failed" <?= ($selectedStatus == 'failed') ? 'selected' : '' ?>>Failed</option>
                                </select>
                            </div>
                            <div class="filter-group" style="flex: 0 0 auto; display: flex; gap: 8px; align-items: flex-end;">
                                <a href="<?= $FileName ?>?tab=history" class="btn btn-danger btn-sm">Reset</a>
                            </div>
                        </div>
                        
                        <div class="table-wrapper">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Staff</th>
                                        <th>Month/Year</th>
                                        <th>Gross Pay</th>
                                        <th>Deductions</th>
                                        <th>Net Pay</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($payrollRecords)): ?>
                                        <?php $i = 0; foreach ($payrollRecords as $record): $i++; ?>
                                        <tr>
                                            <td><?= $i ?></td>
                                            <td>
                                                <strong><?= htmlspecialchars(($record['first_name'] ?? '') . ' ' . ($record['last_name'] ?? '')) ?></strong>
                                            </td>
                                            <td><?= getMonthName($record['month']) ?> <?= $record['year'] ?></td>
                                            <td>₦<?= number_format((float)$record['gross_pay'], 2) ?></td>
                                            <td>₦<?= number_format((float)$record['total_deductions'], 2) ?></td>
                                            <td style="font-weight:700; color:#28a745;">₦<?= number_format((float)$record['net_pay'], 2) ?></td>
                                            <td><?= getStatusBadge($record['payment_status']) ?></td>
                                            <td><?= date('d M Y', strtotime($record['created_at'])) ?></td>
                                            <td>
                                                <a href="payslip.php?id=<?= $record['randomid'] ?>" target="_blank" class="btn btn-primary btn-sm">
                                                    <i class="fa fa-file-pdf-o"></i>
                                                </a>
                                                <?php if ($record['payment_status'] == 'pending'): ?>
                                                    <form method="POST" action="" style="display:inline;" onsubmit="return confirm('Mark as paid?')">
                                                        <input type="hidden" name="payroll_id" value="<?= $record['id'] ?>">
                                                        <input type="hidden" name="status" value="paid">
                                                        <button type="submit" name="update_status" class="btn btn-success btn-sm">
                                                            <i class="fa fa-check"></i>
                                                        </button>
                                                    </form>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="9" class="text-center text-muted" style="padding:30px;">
                                                No payroll records found
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
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