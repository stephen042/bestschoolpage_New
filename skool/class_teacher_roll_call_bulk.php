<?php
/**
 * ============================================================================
 * ATTENDANCE - BULK ENTRY (AJAX VERSION)
 * ============================================================================
 * Description: Real-time attendance entry with AJAX updates
 * Version: 3.0 (PHP 8.x Compatible)
 * ============================================================================
 */

include('../config.php');
include('inc.session-create.php');

$PageTitle = 'Attendance - Bulk Entry';
$FileName = 'class_teacher_roll_call_bulk.php';

// ============================================================================
// INITIALIZATION
// ============================================================================
$stat = [];
$create_by_userid = (int)($_SESSION['userid'] ?? 0);
$create_by_usertype = (string)($_SESSION['usertype'] ?? '');
$currentUserId = (int)($_SESSION['userid'] ?? 0);

// CSRF Protection
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// ============================================================================
// GET CLASS DETAILS
// ============================================================================
$classDetail = [];
if (!empty($_GET['randomid'])) {
    $classDetail = db_get_row(
        "SELECT * FROM school_class 
         WHERE randomid = ? AND create_by_userid = ?",
        [$_GET['randomid'], $create_by_userid]
    );
}

// ============================================================================
// GET SESSIONS AND TERMS
// ============================================================================
$sessions = db_get_rows(
    "SELECT * FROM school_session 
     WHERE create_by_userid = ? ORDER BY id DESC",
    [$create_by_userid]
);

$terms = db_get_rows(
    "SELECT * FROM school_term 
     WHERE create_by_userid = ? ORDER BY id ASC",
    [$create_by_userid]
);

// ============================================================================
// GET STUDENTS AND ATTENDANCE DATA
// ============================================================================
$students = [];
$totalDaysOpen = 0;
$presentCount = 0;
$absentCount = 0;
$attendancePercentage = 0;

if (!empty($_GET['session']) && !empty($_GET['term_id']) && !empty($classDetail)) {
    $students = db_get_rows(
        "SELECT * FROM manage_student 
         WHERE class = ? AND session = ? AND term_id = ? AND create_by_userid = ?
         ORDER BY first_name ASC",
        [$classDetail['id'], $_GET['session'], $_GET['term_id'], $create_by_userid]
    );
    
    // Get attendance data for each student
    foreach ($students as &$student) {
        $rollCall = db_get_row(
            "SELECT * FROM class_teacher_roll_call_bulk 
             WHERE student_id = ? AND session_id = ? AND term_id = ? AND class_id = ?
             AND create_by_userid = ?",
            [$student['student_id'], $_GET['session'], $_GET['term_id'], $classDetail['id'], $create_by_userid]
        );
        
        if ($rollCall) {
            $student['present'] = (int)$rollCall['present'];
            $student['absent'] = (int)$rollCall['absent'];
            $student['total_days_open'] = (int)$rollCall['total_days_open'];
            $student['roll_call_id'] = $rollCall['id'];
            
            $presentCount += (int)$rollCall['present'];
            $absentCount += (int)$rollCall['absent'];
            $totalDaysOpen = max($totalDaysOpen, (int)$rollCall['total_days_open']);
        } else {
            $student['present'] = 0;
            $student['absent'] = 0;
            $student['total_days_open'] = 0;
            $student['roll_call_id'] = null;
        }
    }
    
    $totalStudents = count($students);
    if ($totalStudents > 0) {
        $attendancePercentage = ($presentCount + $absentCount > 0) 
            ? round(($presentCount / ($presentCount + $absentCount)) * 100, 1) 
            : 0;
    }
}

// ============================================================================
// HELPER FUNCTIONS
// ============================================================================

function getSessionName($id) {
    global $create_by_userid;
    return db_get_val(
        "SELECT session FROM school_session WHERE id = ? AND create_by_userid = ?",
        [$id, $create_by_userid]
    );
}

function getTermName($id) {
    global $create_by_userid;
    return db_get_val(
        "SELECT term FROM school_term WHERE id = ? AND create_by_userid = ?",
        [$id, $create_by_userid]
    );
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
        .filter-select { width: 100%; padding: 10px 14px; border: 2px solid #e0e0e0; border-radius: 10px; font-size: 14px; background: #fff; transition: all 0.2s; }
        .filter-select:focus { border-color: #1B3058; outline: none; }
        
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
        
        .summary-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 15px; margin-bottom: 20px; }
        .summary-card { background: #fff; border-radius: 12px; padding: 18px 20px; text-align: center; border-left: 4px solid #1B3058; box-shadow: 0 2px 8px rgba(0,0,0,0.05); }
        .summary-card .number { font-size: 28px; font-weight: 700; color: #1B3058; }
        .summary-card .label { font-size: 11px; color: #888; text-transform: uppercase; font-weight: 600; margin-top: 4px; }
        .summary-card .sub { font-size: 11px; color: #666; margin-top: 4px; }
        .summary-card.present { border-color: #28a745; }
        .summary-card.present .number { color: #28a745; }
        .summary-card.absent { border-color: #dc3545; }
        .summary-card.absent .number { color: #dc3545; }
        .summary-card.percentage { border-color: #17a2b8; }
        .summary-card.percentage .number { color: #17a2b8; }
        
        .table-wrapper { overflow-x: auto; }
        .table { width: 100%; border-collapse: collapse; font-size: 13px; }
        .table th { background: #f8f9fa; color: #1B3058; padding: 12px 16px; text-align: left; font-weight: 600; border-bottom: 2px solid #e0e0e0; }
        .table td { padding: 10px 16px; border-bottom: 1px solid #eee; vertical-align: middle; }
        .table tr:hover { background: #f8f9ff; }
        
        .attendance-input { 
            width: 80px; padding: 6px 10px; border: 2px solid #e0e0e0; border-radius: 6px; 
            font-size: 14px; text-align: center; transition: all 0.3s; 
        }
        .attendance-input:focus { border-color: #1B3058; outline: none; box-shadow: 0 0 0 3px rgba(27,48,88,0.1); }
        .attendance-input.success { border-color: #28a745; background: #d4edda; }
        .attendance-input.error { border-color: #dc3545; background: #f8d7da; }
        .attendance-input.saving { border-color: #ffc107; background: #fff3cd; }
        
        .absent-display { font-weight: 700; color: #dc3545; }
        .present-display { font-weight: 700; color: #28a745; }
        
        .badge { display: inline-block; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; }
        .badge-success { background: #d4edda; color: #155724; }
        .badge-danger { background: #f8d7da; color: #721c24; }
        .badge-warning { background: #fff3cd; color: #856404; }
        .badge-info { background: #d1ecf1; color: #0c5460; }
        
        .sidebar { width: 280px; background: white; border-radius: 12px; box-shadow: 0 2px 12px rgba(0,0,0,0.08); overflow: hidden; }
        .sidebar-header { padding: 15px 20px; background: #1B3058; color: white; font-weight: 600; }
        .class-list { max-height: 70vh; overflow-y: auto; }
        .class-item { padding: 12px 20px; border-bottom: 1px solid #eee; cursor: pointer; text-decoration: none; display: block; color: #333; transition: all 0.2s; }
        .class-item:hover { background: #f0f4ff; }
        .class-item.active { background: #1B3058; color: white; }
        .class-item .class-icon { font-size: 18px; margin-right: 10px; }
        
        .action-bar { display: flex; gap: 10px; flex-wrap: wrap; margin: 15px 0; }
        
        .loading-spinner { display: inline-block; width: 16px; height: 16px; border: 2px solid #fff; border-top-color: transparent; border-radius: 50%; animation: spin 0.8s linear infinite; }
        @keyframes spin { to { transform: rotate(360deg); } }
        
        .layout { display: flex; gap: 25px; flex-wrap: wrap; }
        .main-content { flex: 1; min-width: 500px; }
        .status-dot { display: inline-block; width: 10px; height: 10px; border-radius: 50%; margin-right: 5px; }
        .status-dot.present { background: #28a745; }
        .status-dot.absent { background: #dc3545; }
        
        @media (max-width: 768px) {
            .filter-row { flex-direction: column; }
            .filter-group { min-width: 100%; }
            .summary-grid { grid-template-columns: 1fr 1fr; }
            .layout { flex-direction: column; }
            .sidebar { width: 100%; }
        }
        @media (max-width: 480px) {
            .summary-grid { grid-template-columns: 1fr; }
            .attendance-input { width: 60px; padding: 4px 6px; font-size: 12px; }
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
                    <h2><i class="fa fa-calendar-check-o"></i> <?= htmlspecialchars($PageTitle) ?></h2>
                    <p>Real-time attendance entry with automatic calculations</p>
                </div>
                
                
                
                <!-- Layout -->
                <div class="layout">
                    
                    <!-- LEFT SIDEBAR - Classes -->
                    <div class="sidebar">
                        <div class="sidebar-header"><i class="fa fa-graduation-cap"></i> Select Class</div>
                        <div class="class-list">
                            <?php 
                            $classList = db_get_rows(
                                "SELECT * FROM school_class 
                                 WHERE create_by_userid = ? 
                                 ORDER BY name ASC",
                                [$create_by_userid]
                            );
                            foreach ($classList as $class): ?>
                                <a href="?action=table&randomid=<?= $class['randomid'] ?><?= isset($_GET['session']) ? '&session='.$_GET['session'] : '' ?><?= isset($_GET['term_id']) ? '&term_id='.$_GET['term_id'] : '' ?>" 
                                   class="class-item <?= ($classDetail['id'] ?? '') == $class['id'] ? 'active' : '' ?>">
                                    <i class="fa fa-book class-icon"></i> <?= htmlspecialchars($class['name']) ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <!-- RIGHT MAIN CONTENT -->
                    <div class="main-content">
                        
                        <?php if (!empty($classDetail)): ?>
                        
                        <!-- Filters -->
                        <div class="card">
                            <div class="card-body">
                                <form method="GET" action="" id="filterForm">
                                    <input type="hidden" name="action" value="table">
                                    <input type="hidden" name="randomid" value="<?= htmlspecialchars($_GET['randomid'] ?? '') ?>">
                                    <div class="filter-row">
                                        <div class="filter-group">
                                            <label>Session</label>
                                            <select name="session" class="filter-select" required>
                                                <option value="">-- Select Session --</option>
                                                <?php foreach ($sessions as $s): ?>
                                                    <option value="<?= $s['id'] ?>" <?= (isset($_GET['session']) && $_GET['session'] == $s['id']) ? 'selected' : '' ?>>
                                                        <?= htmlspecialchars($s['session']) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="filter-group">
                                            <label>Term</label>
                                            <select name="term_id" class="filter-select" required>
                                                <option value="">-- Select Term --</option>
                                                <?php foreach ($terms as $t): ?>
                                                    <option value="<?= $t['id'] ?>" <?= (isset($_GET['term_id']) && $_GET['term_id'] == $t['id']) ? 'selected' : '' ?>>
                                                        <?= htmlspecialchars($t['term']) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="filter-group" style="flex: 0 0 auto;">
                                            <button type="submit" class="btn btn-primary"><i class="fa fa-filter"></i> Load</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                        
                        <?php if (!empty($_GET['session']) && !empty($_GET['term_id'])): ?>
                        
                        <!-- Summary Cards -->
                        <div class="summary-grid">
                            <div class="summary-card">
                                <div class="number"><?= count($students) ?></div>
                                <div class="label">Total Students</div>
                            </div>
                            <div class="summary-card present">
                                <div class="number" id="totalPresent"><?= $presentCount ?></div>
                                <div class="label">Present</div>
                                <div class="sub"><?= round(($presentCount / max(1, $presentCount + $absentCount)) * 100, 1) ?>%</div>
                            </div>
                            <div class="summary-card absent">
                                <div class="number" id="totalAbsent"><?= $absentCount ?></div>
                                <div class="label">Absent</div>
                                <div class="sub"><?= round(($absentCount / max(1, $presentCount + $absentCount)) * 100, 1) ?>%</div>
                            </div>
                            <div class="summary-card percentage">
                                <div class="number" id="attendancePercent"><?= $attendancePercentage ?>%</div>
                                <div class="label">Attendance Rate</div>
                                <div class="sub">
                                    <?php if ($attendancePercentage >= 80): ?>
                                        <span class="badge badge-success">✅ Good</span>
                                    <?php elseif ($attendancePercentage >= 60): ?>
                                        <span class="badge badge-warning">⚠️ Average</span>
                                    <?php else: ?>
                                        <span class="badge badge-danger">❌ Poor</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Action Bar -->
                        <div class="action-bar">
                            <div>
                                <label style="font-size:13px; font-weight:600; margin-right:10px;">Total Days Open:</label>
                                <input type="number" id="totalDaysOpen" 
                                       value="<?= $totalDaysOpen > 0 ? $totalDaysOpen : 1 ?>" 
                                       min="1" max="365" 
                                       style="width:80px; padding:6px 10px; border:2px solid #e0e0e0; border-radius:6px; font-size:14px;"
                                       onchange="updateAllDays(this)">
                            </div>
                            <button class="btn btn-success" onclick="saveAllAttendance()">
                                <i class="fa fa-save"></i> Save All
                            </button>
                            <a href="attendance_report.php?session=<?= $_GET['session'] ?>&term_id=<?= $_GET['term_id'] ?>&class_id=<?= $classDetail['id'] ?>" 
                               target="_blank" class="btn btn-danger">
                                <i class="fa fa-file-pdf-o"></i> PDF Report
                            </a>
                            <a href="attendance_report_excel.php?session=<?= $_GET['session'] ?>&term_id=<?= $_GET['term_id'] ?>&class_id=<?= $classDetail['id'] ?>" 
                               class="btn btn-success">
                                <i class="fa fa-file-excel-o"></i> Excel Export
                            </a>
                            <button onclick="window.print()" class="btn btn-primary">
                                <i class="fa fa-print"></i> Print
                            </button>
                            <button class="btn btn-info" onclick="resetAttendance()">
                                <i class="fa fa-refresh"></i> Reset
                            </button>
                        </div>
                        
                        <!-- Attendance Table -->
                        <div class="card">
                            <div class="card-header">
                                <i class="fa fa-list"></i> Attendance Entry
                                <span style="float:right; font-size:12px; font-weight:400;">
                                    <i class="fa fa-info-circle"></i> Enter number of days present
                                </span>
                            </div>
                            <div class="card-body">
                                <div class="table-wrapper">
                                    <table class="table" id="attendanceTable">
                                        <thead>
                                            <tr>
                                                <th style="width:50px;">#</th>
                                                <th style="width:120px;">Student ID</th>
                                                <th>Student Name</th>
                                                <th style="width:130px;">Present</th>
                                                <th style="width:100px;">Absent</th>
                                                <th style="width:80px;">Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (!empty($students)): ?>
                                                <?php $i = 0; foreach ($students as $student): $i++; ?>
                                                <tr id="row-<?= $student['student_id'] ?>">
                                                    <td><?= $i ?></td>
                                                    <td><?= htmlspecialchars($student['student_id']) ?></td>
                                                    <td>
                                                        <strong><?= htmlspecialchars(($student['first_name'] ?? '') . ' ' . ($student['last_name'] ?? '')) ?></strong>
                                                    </td>
                                                    <td>
                                                        <input type="number" 
                                                               class="attendance-input present-input" 
                                                               data-student-id="<?= $student['student_id'] ?>"
                                                               data-session="<?= htmlspecialchars($_GET['session']) ?>"
                                                               data-term="<?= htmlspecialchars($_GET['term_id']) ?>"
                                                               data-class="<?= $classDetail['id'] ?>"
                                                               value="<?= $student['present'] ?? 0 ?>"
                                                               min="0" 
                                                               max="365"
                                                               onchange="updateAttendance(this)"
                                                               onkeyup="updateAttendance(this)">
                                                    </td>
                                                    <td>
                                                        <span class="absent-display" id="absent-<?= $student['student_id'] ?>">
                                                            <?= $student['absent'] ?? 0 ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <?php if (($student['present'] ?? 0) > 0): ?>
                                                            <span class="status-dot present"></span> 
                                                            <span style="color:#28a745; font-weight:600;">
                                                                <?= round(($student['present'] / max(1, $student['present'] + $student['absent'])) * 100, 0) ?>%
                                                            </span>
                                                        <?php else: ?>
                                                            <span class="status-dot absent"></span>
                                                            <span style="color:#dc3545; font-weight:600;">0%</span>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <tr>
                                                    <td colspan="6" style="text-align:center; padding:40px; color:#999;">
                                                        <i class="fa fa-users" style="font-size:40px; display:block; margin-bottom:10px;"></i>
                                                        No students found in this class
                                                    </td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        
                        <?php else: ?>
                            <div class="alert alert-info">
                                <i class="fa fa-info-circle"></i> Please select a session and term to view attendance.
                            </div>
                        <?php endif; ?>
                        
                        <?php else: ?>
                            <div class="alert alert-info">
                                <i class="fa fa-info-circle"></i> Please select a class from the left sidebar.
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

<!-- ============================================================================
     AJAX JAVASCRIPT
     ============================================================================ -->
<script>
// CSRF Token for security
var csrfToken = '<?= $_SESSION['csrf_token'] ?>';

/**
 * Update attendance via AJAX
 */
function updateAttendance(element) {
    var studentId = element.dataset.studentId;
    var session = element.dataset.session;
    var term = element.dataset.term;
    var classId = element.dataset.class;
    var present = parseInt(element.value) || 0;
    var totalDays = parseInt(document.getElementById('totalDaysOpen').value) || 0;
    
    // Validate
    if (present < 0) {
        element.classList.add('error');
        element.classList.remove('success', 'saving');
        showToast('Please enter a valid number', 'error');
        return;
    }
    
    if (totalDays > 0 && present > totalDays) {
        element.classList.add('error');
        element.classList.remove('success', 'saving');
        showToast('Present cannot exceed Total Days Open (' + totalDays + ')', 'error');
        return;
    }
    
    // Show saving state
    element.classList.add('saving');
    element.classList.remove('success', 'error');
    
    // Calculate absent
    var absent = totalDays - present;
    if (absent < 0) absent = 0;
    
    // Update absent display immediately (optimistic UI)
    document.getElementById('absent-' + studentId).textContent = absent;
    
    // AJAX Request
    fetch('ajax_attendance.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'action=update_attendance' +
              '&csrf_token=' + encodeURIComponent(csrfToken) +
              '&student_id=' + encodeURIComponent(studentId) +
              '&session=' + encodeURIComponent(session) +
              '&term=' + encodeURIComponent(term) +
              '&class_id=' + encodeURIComponent(classId) +
              '&present=' + encodeURIComponent(present) +
              '&total_days=' + encodeURIComponent(totalDays)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            element.classList.remove('saving', 'error');
            element.classList.add('success');
            
            // Update summary
            updateSummary(data.summary);
            
            // Show success toast
            showToast('✅ Attendance saved for ' + data.student_name, 'success');
            
            // Reset success state after 2 seconds
            setTimeout(() => {
                element.classList.remove('success');
            }, 1500);
        } else {
            element.classList.remove('saving', 'success');
            element.classList.add('error');
            showToast('❌ ' + data.message, 'error');
        }
    })
    .catch(error => {
        element.classList.remove('saving', 'success');
        element.classList.add('error');
        console.error('Error:', error);
        showToast('❌ Network error. Please try again.', 'error');
    });
}

/**
 * Update summary cards
 */
function updateSummary(summary) {
    if (summary) {
        document.getElementById('totalPresent').textContent = summary.totalPresent || 0;
        document.getElementById('totalAbsent').textContent = summary.totalAbsent || 0;
        document.getElementById('attendancePercent').textContent = (summary.percentage || 0) + '%';
        
        // Update attendance rate badge
        var badge = document.querySelector('.summary-card.percentage .sub .badge');
        var percent = summary.percentage || 0;
        if (badge) {
            if (percent >= 80) {
                badge.className = 'badge badge-success';
                badge.textContent = '✅ Good';
            } else if (percent >= 60) {
                badge.className = 'badge badge-warning';
                badge.textContent = '⚠️ Average';
            } else {
                badge.className = 'badge badge-danger';
                badge.textContent = '❌ Poor';
            }
        }
    }
}

/**
 * Update all days open for all students
 */
function updateAllDays(element) {
    var totalDays = parseInt(element.value) || 0;
    if (totalDays < 1) {
        element.value = 1;
        totalDays = 1;
    }
    
    var inputs = document.querySelectorAll('.present-input');
    inputs.forEach(function(input) {
        var present = parseInt(input.value) || 0;
        if (present > totalDays) {
            input.value = totalDays;
            present = totalDays;
        }
        // Trigger update
        updateAttendance(input);
    });
}

/**
 * Save all attendance at once
 */
function saveAllAttendance() {
    var inputs = document.querySelectorAll('.present-input');
    var count = inputs.length;
    var saved = 0;
    var errors = 0;
    
    showToast('Saving ' + count + ' records...', 'info');
    
    inputs.forEach(function(input) {
        var studentId = input.dataset.studentId;
        var session = input.dataset.session;
        var term = input.dataset.term;
        var classId = input.dataset.class;
        var present = parseInt(input.value) || 0;
        var totalDays = parseInt(document.getElementById('totalDaysOpen').value) || 0;
        
        fetch('ajax_attendance.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'action=update_attendance' +
                  '&csrf_token=' + encodeURIComponent(csrfToken) +
                  '&student_id=' + encodeURIComponent(studentId) +
                  '&session=' + encodeURIComponent(session) +
                  '&term=' + encodeURIComponent(term) +
                  '&class_id=' + encodeURIComponent(classId) +
                  '&present=' + encodeURIComponent(present) +
                  '&total_days=' + encodeURIComponent(totalDays) +
                  '&bulk=1'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                saved++;
                input.classList.add('success');
                setTimeout(() => {
                    input.classList.remove('success');
                }, 1000);
            } else {
                errors++;
                input.classList.add('error');
                setTimeout(() => {
                    input.classList.remove('error');
                }, 1000);
            }
            
            if (saved + errors === count) {
                var message = '✅ ' + saved + ' records saved';
                if (errors > 0) {
                    message += ', ❌ ' + errors + ' errors';
                }
                showToast(message, errors > 0 ? 'error' : 'success');
                
                // Refresh summary
                fetchSummary();
            }
        })
        .catch(error => {
            errors++;
            console.error('Error:', error);
        });
    });
}

/**
 * Fetch updated summary
 */
function fetchSummary() {
    var session = document.querySelector('.present-input')?.dataset.session || '';
    var term = document.querySelector('.present-input')?.dataset.term || '';
    var classId = document.querySelector('.present-input')?.dataset.class || '';
    
    if (!session || !term || !classId) return;
    
    fetch('ajax_attendance.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'action=get_summary' +
              '&csrf_token=' + encodeURIComponent(csrfToken) +
              '&session=' + encodeURIComponent(session) +
              '&term=' + encodeURIComponent(term) +
              '&class_id=' + encodeURIComponent(classId)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            updateSummary(data.summary);
        }
    })
    .catch(error => console.error('Error:', error));
}

/**
 * Reset all attendance
 */
function resetAttendance() {
    if (!confirm('Are you sure you want to reset all attendance entries? This will set all values to 0.')) {
        return;
    }
    
    var inputs = document.querySelectorAll('.present-input');
    var totalDays = parseInt(document.getElementById('totalDaysOpen').value) || 0;
    
    inputs.forEach(function(input) {
        input.value = 0;
        updateAttendance(input);
    });
    
    showToast('Attendance reset successfully', 'info');
}

/**
 * Toast notification system
 */
function showToast(message, type) {
    var toast = document.createElement('div');
    toast.className = 'toast-message ' + type;
    toast.style.cssText = `
        position: fixed;
        bottom: 30px;
        right: 30px;
        padding: 15px 25px;
        border-radius: 10px;
        color: #fff;
        font-weight: 600;
        font-size: 14px;
        z-index: 9999;
        animation: slideIn 0.5s ease;
        box-shadow: 0 4px 20px rgba(0,0,0,0.2);
        background: ${type === 'success' ? '#28a745' : type === 'error' ? '#dc3545' : type === 'info' ? '#17a2b8' : '#1B3058'};
    `;
    toast.textContent = message;
    
    document.body.appendChild(toast);
    
    setTimeout(function() {
        toast.style.animation = 'slideOut 0.5s ease';
        setTimeout(function() {
            toast.remove();
        }, 500);
    }, 3000);
}

// Add CSS animations
var style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from { transform: translateX(100%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }
    @keyframes slideOut {
        from { transform: translateX(0); opacity: 1; }
        to { transform: translateX(100%); opacity: 0; }
    }
`;
document.head.appendChild(style);

// Auto-submit filters on change
document.addEventListener('DOMContentLoaded', function() {
    var filterSelects = document.querySelectorAll('.filter-select');
    filterSelects.forEach(function(select) {
        select.addEventListener('change', function() {
            var form = document.getElementById('filterForm');
            if (form.querySelector('select[name="session"]').value && 
                form.querySelector('select[name="term_id"]').value) {
                form.submit();
            }
        });
    });
});
</script>
</body>
</html>