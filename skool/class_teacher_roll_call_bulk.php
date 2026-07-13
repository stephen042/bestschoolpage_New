<?php

/**
 * ============================================================================
 * ATTENDANCE - BULK ENTRY (AJAX VERSION)
 * ============================================================================
 * Description: Real-time attendance entry with AJAX updates
 * Version: 4.0 (Fully Mobile Responsive)
 * ============================================================================
 */

include('../config.php');
include('inc.session-create.php');

$PageTitle = 'Attendance - Bulk Entry';
$FileName = 'class_teacher_roll_call_bulk.php';

// ============================================================================
// FIX: USE CORRECT USER IDENTIFICATION (Same as dashboard.php)
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

// Get all classes for sidebar
$classList = db_get_rows(
    "SELECT * FROM school_class 
     WHERE create_by_userid = ? 
     ORDER BY name ASC",
    [$create_by_userid]
);

// ============================================================================
// HELPER FUNCTIONS
// ============================================================================

function getSessionName($id)
{
    global $create_by_userid;
    return db_get_val(
        "SELECT session FROM school_session WHERE id = ? AND create_by_userid = ?",
        [$id, $create_by_userid]
    );
}

function getTermName($id)
{
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
        /* ============================================================
        RESET & BASE
        ============================================================ */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: #f0f2f5;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
            line-height: 1.6;
        }

        .attendance-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 15px;
        }

        /* ============================================================
        PAGE HEADER - MOBILE FIRST
        ============================================================ */
        .page-header {
            margin-bottom: 20px;
        }

        .page-header h2 {
            color: #1B3058;
            font-size: 22px;
            margin: 0;
            font-weight: 700;
        }

        .page-header h2 i {
            margin-right: 8px;
        }

        .page-header p {
            color: #666;
            margin-top: 4px;
            font-size: 14px;
        }

        /* ============================================================
        LAYOUT - MOBILE FIRST
        ============================================================ */
        .layout {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        /* Sidebar - Full width on mobile */
        .sidebar {
            width: 100%;
            background: white;
            border-radius: 16px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
            overflow: hidden;
        }

        .sidebar-header {
            padding: 14px 18px;
            background: linear-gradient(135deg, #1B3058, #2a4780);
            color: white;
            font-weight: 600;
            font-size: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .sidebar-header i {
            font-size: 18px;
        }

        .sidebar-header .count-badge {
            margin-left: auto;
            background: rgba(255, 255, 255, 0.2);
            padding: 2px 12px;
            border-radius: 12px;
            font-size: 12px;
        }

        .class-list {
            max-height: 300px;
            overflow-y: auto;
            -webkit-overflow-scrolling: touch;
        }

        .class-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 18px;
            border-bottom: 1px solid #f0f0f0;
            cursor: pointer;
            text-decoration: none;
            color: #333;
            transition: all 0.2s;
        }

        .class-item:active {
            background: #e8eef5;
        }

        .class-item:hover {
            background: #f8f9ff;
        }

        .class-item.active {
            background: #e8eef5;
            border-left: 4px solid #1B3058;
        }

        .class-item .class-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            background: #e8eef5;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            color: #1B3058;
            flex-shrink: 0;
        }

        .class-item.active .class-icon {
            background: #1B3058;
            color: white;
        }

        .class-item .class-name {
            font-weight: 600;
            font-size: 14px;
        }

        .class-item .class-meta {
            font-size: 11px;
            color: #999;
        }

        .class-item .class-arrow {
            margin-left: auto;
            color: #ccc;
            font-size: 14px;
        }

        /* Main Content */
        .main-content {
            flex: 1;
            width: 100%;
        }

        /* ============================================================
        CARDS - MOBILE FIRST
        ============================================================ */
        .card {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
            margin-bottom: 20px;
            overflow: hidden;
        }

        .card-header {
            padding: 14px 18px;
            background: linear-gradient(135deg, #1B3058, #2a4780);
            color: #fff;
            font-weight: 600;
            font-size: 15px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 8px;
        }

        .card-header i {
            margin-right: 8px;
        }

        .card-header .card-badge {
            background: rgba(255, 255, 255, 0.2);
            padding: 2px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 400;
        }

        .card-body {
            padding: 16px;
        }

        /* ============================================================
        FILTERS - MOBILE FIRST
        ============================================================ */
        .filter-row {
            display: flex;
            flex-direction: column;
            gap: 12px;
            margin-bottom: 12px;
        }

        .filter-group {
            width: 100%;
        }

        .filter-group label {
            display: block;
            font-size: 11px;
            font-weight: 700;
            color: #888;
            text-transform: uppercase;
            margin-bottom: 4px;
            letter-spacing: 0.5px;
        }

        .filter-select {
            width: 100%;
            padding: 10px 14px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 14px;
            background: #fff;
            transition: all 0.2s;
            -webkit-appearance: none;
            appearance: none;
            background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 12px center;
            background-size: 16px;
        }

        .filter-select:focus {
            border-color: #1B3058;
            outline: none;
            box-shadow: 0 0 0 3px rgba(27, 48, 88, 0.1);
        }

        .filter-actions {
            display: flex;
            gap: 10px;
            margin-top: 4px;
        }

        .filter-actions .btn {
            flex: 1;
            justify-content: center;
        }

        /* ============================================================
        BUTTONS - MOBILE FIRST
        ============================================================ */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 10px 16px;
            border: none;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            min-height: 44px;
            touch-action: manipulation;
            user-select: none;
        }

        .btn:active {
            transform: scale(0.97);
        }

        .btn-primary {
            background: #1B3058;
            color: #fff;
        }

        .btn-primary:hover {
            background: #f21151;
        }

        .btn-success {
            background: #28a745;
            color: #fff;
        }

        .btn-success:hover {
            background: #218838;
        }

        .btn-danger {
            background: #dc3545;
            color: #fff;
        }

        .btn-danger:hover {
            background: #c82333;
        }

        .btn-warning {
            background: #ffc107;
            color: #333;
        }

        .btn-warning:hover {
            background: #e0a800;
        }

        .btn-info {
            background: #17a2b8;
            color: #fff;
        }

        .btn-info:hover {
            background: #138496;
        }

        .btn-outline {
            background: transparent;
            color: #1B3058;
            border: 2px solid #1B3058;
        }

        .btn-outline:hover {
            background: #1B3058;
            color: white;
        }

        .btn-sm {
            padding: 6px 12px;
            font-size: 12px;
            min-height: 32px;
        }

        .btn-block {
            width: 100%;
            justify-content: center;
        }

        /* ============================================================
        SUMMARY GRID - MOBILE FIRST
        ============================================================ */
        .summary-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin-bottom: 16px;
        }

        .summary-card {
            background: #fff;
            border-radius: 12px;
            padding: 14px 12px;
            text-align: center;
            border-left: 4px solid #1B3058;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }

        .summary-card .number {
            font-size: 24px;
            font-weight: 700;
            color: #1B3058;
            line-height: 1.2;
        }

        .summary-card .label {
            font-size: 10px;
            color: #888;
            text-transform: uppercase;
            font-weight: 600;
            margin-top: 2px;
        }

        .summary-card .sub {
            font-size: 10px;
            color: #666;
            margin-top: 3px;
        }

        .summary-card .sub .badge {
            font-size: 10px;
            padding: 2px 8px;
        }

        .summary-card.present {
            border-color: #28a745;
        }

        .summary-card.present .number {
            color: #28a745;
        }

        .summary-card.absent {
            border-color: #dc3545;
        }

        .summary-card.absent .number {
            color: #dc3545;
        }

        .summary-card.percentage {
            border-color: #17a2b8;
        }

        .summary-card.percentage .number {
            color: #17a2b8;
        }

        /* ============================================================
        ACTION BAR - MOBILE FIRST
        ============================================================ */
        .action-bar {
            display: flex;
            flex-direction: column;
            gap: 10px;
            margin: 12px 0 16px;
        }

        .action-bar .action-row {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            align-items: center;
        }

        .action-bar .action-row .btn {
            flex: 1;
            min-width: 80px;
            justify-content: center;
            font-size: 12px;
            padding: 8px 12px;
        }

        .action-bar .days-input-group {
            display: flex;
            align-items: center;
            gap: 8px;
            background: #f8f9fa;
            padding: 6px 12px;
            border-radius: 10px;
            flex: 1;
            min-width: 150px;
        }

        .action-bar .days-input-group label {
            font-size: 12px;
            font-weight: 600;
            color: #555;
            white-space: nowrap;
        }

        .action-bar .days-input-group input {
            width: 70px;
            padding: 6px 8px;
            border: 2px solid #e0e0e0;
            border-radius: 6px;
            font-size: 14px;
            text-align: center;
            flex-shrink: 0;
        }

        .action-bar .days-input-group input:focus {
            border-color: #1B3058;
            outline: none;
        }

        /* ============================================================
        TABLE - MOBILE FIRST
        ============================================================ */
        .table-wrapper {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            margin: 0 -4px;
            padding: 0 4px;
        }

        .table {
            width: 100%;
            min-width: 500px;
            border-collapse: collapse;
            font-size: 12px;
        }

        .table thead th {
            background: #f8f9fa;
            color: #1B3058;
            padding: 8px 10px;
            text-align: left;
            font-weight: 700;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            border-bottom: 2px solid #e0e0e0;
            position: sticky;
            top: 0;
            z-index: 2;
        }

        .table tbody td {
            padding: 8px 10px;
            border-bottom: 1px solid #eee;
            vertical-align: middle;
        }

        .table tbody tr:active {
            background: #f8f9ff;
        }

        .table tbody tr:nth-child(even) {
            background: #fafbfc;
        }

        .table .student-name {
            font-weight: 600;
            font-size: 13px;
        }

        .table .student-id {
            color: #999;
            font-size: 11px;
        }

        /* Attendance Input */
        .attendance-input {
            width: 64px;
            padding: 6px 4px;
            border: 2px solid #e0e0e0;
            border-radius: 6px;
            font-size: 14px;
            text-align: center;
            transition: all 0.3s;
            -webkit-appearance: none;
            appearance: none;
        }

        .attendance-input:focus {
            border-color: #1B3058;
            outline: none;
            box-shadow: 0 0 0 3px rgba(27, 48, 88, 0.1);
        }

        .attendance-input.success {
            border-color: #28a745;
            background: #d4edda;
        }

        .attendance-input.error {
            border-color: #dc3545;
            background: #f8d7da;
        }

        .attendance-input.saving {
            border-color: #ffc107;
            background: #fff3cd;
        }

        .absent-display {
            font-weight: 700;
            color: #dc3545;
            font-size: 14px;
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
        }

        .status-badge.present {
            background: #d4edda;
            color: #155724;
        }

        .status-badge.absent {
            background: #f8d7da;
            color: #721c24;
        }

        .status-badge .dot {
            display: inline-block;
            width: 8px;
            height: 8px;
            border-radius: 50%;
        }

        .status-badge .dot.present {
            background: #28a745;
        }

        .status-badge .dot.absent {
            background: #dc3545;
        }

        /* ============================================================
        BADGES
        ============================================================ */
        .badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 10px;
            font-weight: 600;
        }

        .badge-success {
            background: #d4edda;
            color: #155724;
        }

        .badge-danger {
            background: #f8d7da;
            color: #721c24;
        }

        .badge-warning {
            background: #fff3cd;
            color: #856404;
        }

        .badge-info {
            background: #d1ecf1;
            color: #0c5460;
        }

        /* ============================================================
        ALERTS
        ============================================================ */
        .alert {
            padding: 12px 16px;
            border-radius: 12px;
            margin-bottom: 16px;
            font-size: 13px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .alert-info {
            background: #d1ecf1;
            color: #0c5460;
            border-left: 4px solid #17a2b8;
        }

        .alert-warning {
            background: #fff3cd;
            color: #856404;
            border-left: 4px solid #ffc107;
        }

        .alert-danger {
            background: #f8d7da;
            color: #721c24;
            border-left: 4px solid #dc3545;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border-left: 4px solid #28a745;
        }

        /* ============================================================
        TOAST NOTIFICATIONS
        ============================================================ */
        .toast-message {
            position: fixed;
            bottom: 80px;
            left: 16px;
            right: 16px;
            padding: 14px 20px;
            border-radius: 12px;
            color: #fff;
            font-weight: 600;
            font-size: 14px;
            z-index: 9999;
            animation: slideUp 0.4s ease;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
            text-align: center;
        }

        @keyframes slideUp {
            from {
                transform: translateY(100px);
                opacity: 0;
            }

            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        @keyframes slideDown {
            from {
                transform: translateY(0);
                opacity: 1;
            }

            to {
                transform: translateY(100px);
                opacity: 0;
            }
        }

        /* ============================================================
        EMPTY STATE
        ============================================================ */
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #999;
        }

        .empty-state i {
            font-size: 48px;
            color: #ddd;
            display: block;
            margin-bottom: 12px;
        }

        .empty-state h4 {
            color: #666;
            font-size: 16px;
            margin-bottom: 4px;
        }

        .empty-state p {
            font-size: 13px;
        }

        /* ============================================================
        RESPONSIVE - TABLET (768px+)
        ============================================================ */
        @media (min-width: 768px) {
            .attendance-container {
                padding: 25px;
            }

            .layout {
                flex-direction: row;
                gap: 25px;
            }

            .sidebar {
                width: 280px;
                flex-shrink: 0;
            }

            .class-list {
                max-height: 70vh;
            }

            .main-content {
                min-width: 0;
            }

            .filter-row {
                flex-direction: row;
                gap: 15px;
            }

            .filter-group {
                flex: 1;
                min-width: 150px;
            }

            .filter-actions {
                flex: 0 0 auto;
            }

            .filter-actions .btn {
                flex: 0 0 auto;
                padding: 10px 24px;
            }

            .summary-grid {
                grid-template-columns: repeat(4, 1fr);
                gap: 15px;
            }

            .summary-card .number {
                font-size: 28px;
            }

            .action-bar {
                flex-direction: row;
                flex-wrap: wrap;
                align-items: center;
            }

            .action-bar .action-row .btn {
                flex: 0 0 auto;
            }

            .table {
                font-size: 13px;
                min-width: auto;
            }

            .table thead th {
                padding: 10px 14px;
                font-size: 11px;
            }

            .table tbody td {
                padding: 10px 14px;
            }

            .attendance-input {
                width: 80px;
                padding: 8px 10px;
            }

            .toast-message {
                left: auto;
                right: 30px;
                bottom: 30px;
                max-width: 400px;
                text-align: left;
            }
        }

        /* ============================================================
        RESPONSIVE - DESKTOP (1024px+)
        ============================================================ */
        @media (min-width: 1024px) {
            .attendance-container {
                padding: 30px;
            }

            .summary-card {
                padding: 18px 20px;
            }

            .summary-card .number {
                font-size: 32px;
            }

            .card-body {
                padding: 24px;
            }
        }

        /* ============================================================
        RESPONSIVE - SMALL MOBILE (480px-)
        ============================================================ */
        @media (max-width: 480px) {
            .attendance-container {
                padding: 10px;
            }

            .page-header h2 {
                font-size: 18px;
            }

            .page-header p {
                font-size: 12px;
            }

            .summary-grid {
                grid-template-columns: 1fr 1fr;
                gap: 8px;
            }

            .summary-card {
                padding: 10px 8px;
            }

            .summary-card .number {
                font-size: 20px;
            }

            .summary-card .label {
                font-size: 9px;
            }

            .action-bar .action-row {
                flex-direction: column;
            }

            .action-bar .action-row .btn {
                width: 100%;
            }

            .action-bar .days-input-group {
                width: 100%;
                justify-content: center;
            }

            .table {
                font-size: 11px;
                min-width: 400px;
            }

            .table thead th {
                padding: 6px 6px;
                font-size: 9px;
            }

            .table tbody td {
                padding: 6px 6px;
            }

            .table .student-name {
                font-size: 11px;
            }

            .attendance-input {
                width: 50px;
                padding: 4px 2px;
                font-size: 12px;
            }

            .absent-display {
                font-size: 12px;
            }

            .status-badge {
                font-size: 9px;
                padding: 1px 6px;
            }

            .card-header {
                padding: 12px 14px;
                font-size: 13px;
            }

            .card-body {
                padding: 10px 8px;
            }
        }

        /* ============================================================
        LOADING SPINNER
        ============================================================ */
        .loading-spinner {
            display: inline-block;
            width: 16px;
            height: 16px;
            border: 2px solid #fff;
            border-top-color: transparent;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        /* ============================================================
        PRINT STYLES
        ============================================================ */
        @media print {

            .sidebar,
            .action-bar,
            .filter-actions,
            .btn,
            .attendance-input,
            .no-print {
                display: none !important;
            }

            .layout {
                display: block;
            }

            .main-content {
                width: 100%;
            }

            .card {
                box-shadow: none !important;
                border: 1px solid #ddd;
            }

            .card-header {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .summary-card {
                border: 1px solid #ddd;
            }

            .summary-card .number {
                color: #1B3058 !important;
            }

            .attendance-container {
                padding: 0;
            }

            body {
                background: white;
            }
        }
    </style>
</head>

<body>
    <div id="wrapper">
        <?php include('inc.header.php'); ?>
        <?php include('inc.sideleft.php'); ?>

        <div class="content-page">
            <div class="content">
                <div class="attendance-container">

                    <!-- Page Header -->
                    <div class="page-header">
                        <h2><i class="fa fa-calendar-check-o"></i> <?= htmlspecialchars($PageTitle) ?></h2>
                        <p>Real-time attendance entry with automatic calculations</p>
                    </div>

                    <!-- Layout -->
                    <div class="layout">

                        <!-- LEFT SIDEBAR - Classes -->
                        <div class="sidebar">
                            <div class="sidebar-header">
                                <i class="fa fa-graduation-cap"></i> Select Class
                                <span class="count-badge"><?= count($classList) ?></span>
                            </div>
                            <div class="class-list">
                                <?php if (!empty($classList)): ?>
                                    <?php foreach ($classList as $class): ?>
                                        <a href="?action=table&randomid=<?= $class['randomid'] ?><?= isset($_GET['session']) ? '&session=' . $_GET['session'] : '' ?><?= isset($_GET['term_id']) ? '&term_id=' . $_GET['term_id'] : '' ?>"
                                            class="class-item <?= ($classDetail['id'] ?? '') == $class['id'] ? 'active' : '' ?>">
                                            <div class="class-icon">
                                                <i class="fa fa-book"></i>
                                            </div>
                                            <div>
                                                <div class="class-name"><?= htmlspecialchars($class['name']) ?></div>
                                                <div class="class-meta">Click to select</div>
                                            </div>
                                            <div class="class-arrow"><i class="fa fa-chevron-right"></i></div>
                                        </a>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="empty-state" style="padding: 30px 20px;">
                                        <i class="fa fa-book"></i>
                                        <p>No classes found</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- RIGHT MAIN CONTENT -->
                        <div class="main-content">

                            <?php if (!empty($classDetail)): ?>

                                <!-- Filters -->
                                <div class="card">
                                    <div class="card-body" style="padding: 16px;">
                                        <form method="GET" action="" id="filterForm">
                                            <input type="hidden" name="action" value="table">
                                            <input type="hidden" name="randomid" value="<?= htmlspecialchars($_GET['randomid'] ?? '') ?>">
                                            <div class="filter-row">
                                                <div class="filter-group">
                                                    <label><i class="fa fa-calendar"></i> Session</label>
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
                                                    <label><i class="fa fa-tag"></i> Term</label>
                                                    <select name="term_id" class="filter-select" required>
                                                        <option value="">-- Select Term --</option>
                                                        <?php foreach ($terms as $t): ?>
                                                            <option value="<?= $t['id'] ?>" <?= (isset($_GET['term_id']) && $_GET['term_id'] == $t['id']) ? 'selected' : '' ?>>
                                                                <?= htmlspecialchars($t['term']) ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                                <div class="filter-actions">
                                                    <button type="submit" class="btn btn-primary btn-block"><i class="fa fa-filter"></i> Load</button>
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
                                        <div class="action-row">
                                            <div class="days-input-group">
                                                <label><i class="fa fa-calendar"></i> Days:</label>
                                                <input type="number" id="totalDaysOpen"
                                                    value="<?= $totalDaysOpen > 0 ? $totalDaysOpen : 1 ?>"
                                                    min="1" max="365"
                                                    onchange="updateAllDays(this)">
                                            </div>
                                            <button class="btn btn-success" onclick="saveAllAttendance()">
                                                <i class="fa fa-save"></i> Save All
                                            </button>
                                            <button class="btn btn-info" onclick="resetAttendance()">
                                                <i class="fa fa-refresh"></i> Reset
                                            </button>
                                        </div>
                                        <div class="action-row">
                                            <a href="attendance_report.php?session=<?= $_GET['session'] ?>&term_id=<?= $_GET['term_id'] ?>&class_id=<?= $classDetail['id'] ?>"
                                                target="_blank" class="btn btn-danger">
                                                <i class="fa fa-file-pdf-o"></i> PDF
                                            </a>
                                            <a href="attendance_report_excel.php?session=<?= $_GET['session'] ?>&term_id=<?= $_GET['term_id'] ?>&class_id=<?= $classDetail['id'] ?>"
                                                class="btn btn-success">
                                                <i class="fa fa-file-excel-o"></i> Excel
                                            </a>
                                            <button onclick="window.print()" class="btn btn-primary">
                                                <i class="fa fa-print"></i> Print
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Attendance Table -->
                                    <div class="card">
                                        <div class="card-header">
                                            <span><i class="fa fa-list"></i> Attendance Entry</span>
                                            <span class="card-badge">
                                                <i class="fa fa-info-circle"></i> Enter days present
                                            </span>
                                        </div>
                                        <div class="card-body" style="padding: 12px 8px;">
                                            <div class="table-wrapper">
                                                <table class="table" id="attendanceTable">
                                                    <thead>
                                                        <tr>
                                                            <th style="width:40px;">#</th>
                                                            <th style="min-width:90px;">Student</th>
                                                            <th style="min-width:80px;">Present</th>
                                                            <th style="min-width:60px;">Absent</th>
                                                            <th style="min-width:70px;">Status</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php if (!empty($students)): ?>
                                                            <?php $i = 0;
                                                            foreach ($students as $student): $i++; ?>
                                                                <tr id="row-<?= $student['student_id'] ?>">
                                                                    <td><?= $i ?></td>
                                                                    <td>
                                                                        <div class="student-name"><?= htmlspecialchars(($student['first_name'] ?? '') . ' ' . ($student['last_name'] ?? '')) ?></div>
                                                                        <div class="student-id">ID: <?= htmlspecialchars($student['student_id']) ?></div>
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
                                                                            onkeyup="updateAttendance(this)"
                                                                            inputmode="numeric"
                                                                            pattern="[0-9]*">
                                                                    </td>
                                                                    <td>
                                                                        <span class="absent-display" id="absent-<?= $student['student_id'] ?>">
                                                                            <?= $student['absent'] ?? 0 ?>
                                                                        </span>
                                                                    </td>
                                                                    <td>
                                                                        <?php
                                                                        $p = (int)($student['present'] ?? 0);
                                                                        $a = (int)($student['absent'] ?? 0);
                                                                        $total = $p + $a;
                                                                        $percent = $total > 0 ? round(($p / $total) * 100, 0) : 0;
                                                                        $statusClass = $percent >= 80 ? 'present' : 'absent';
                                                                        ?>
                                                                        <span class="status-badge <?= $statusClass ?>">
                                                                            <span class="dot <?= $statusClass ?>"></span>
                                                                            <?= $percent ?>%
                                                                        </span>
                                                                    </td>
                                                                </tr>
                                                            <?php endforeach; ?>
                                                        <?php else: ?>
                                                            <tr>
                                                                <td colspan="5" style="text-align:center; padding:30px 10px; color:#999;">
                                                                    <i class="fa fa-users" style="font-size:32px; display:block; margin-bottom:8px;"></i>
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
                showToast('Present cannot exceed Total Days (' + totalDays + ')', 'error');
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

                        // Update status badge
                        updateStatusBadge(studentId, present, absent);

                        showToast('✅ Saved', 'success');

                        setTimeout(function() {
                            element.classList.remove('success');
                        }, 1200);
                    } else {
                        element.classList.remove('saving', 'success');
                        element.classList.add('error');
                        showToast('❌ ' + data.message, 'error');
                    }
                })
                .catch(function(error) {
                    element.classList.remove('saving', 'success');
                    element.classList.add('error');
                    console.error('Error:', error);
                    showToast('❌ Network error. Please try again.', 'error');
                });
        }

        /**
         * Update status badge for a student
         */
        function updateStatusBadge(studentId, present, absent) {
            var total = present + absent;
            var percent = total > 0 ? Math.round((present / total) * 100) : 0;
            var badge = document.querySelector('#row-' + studentId + ' .status-badge');
            if (badge) {
                var statusClass = percent >= 80 ? 'present' : 'absent';
                badge.className = 'status-badge ' + statusClass;
                badge.innerHTML = '<span class="dot ' + statusClass + '"></span> ' + percent + '%';
            }
        }

        /**
         * Update summary cards
         */
        function updateSummary(summary) {
            if (summary) {
                var totalPresent = summary.totalPresent || 0;
                var totalAbsent = summary.totalAbsent || 0;
                var percentage = summary.percentage || 0;

                document.getElementById('totalPresent').textContent = totalPresent;
                document.getElementById('totalAbsent').textContent = totalAbsent;
                document.getElementById('attendancePercent').textContent = percentage + '%';

                // Update attendance rate badge
                var badge = document.querySelector('.summary-card.percentage .sub .badge');
                if (badge) {
                    if (percentage >= 80) {
                        badge.className = 'badge badge-success';
                        badge.textContent = '✅ Good';
                    } else if (percentage >= 60) {
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

            if (count === 0) {
                showToast('No students to save', 'info');
                return;
            }

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
                    .then(function(response) {
                        return response.json();
                    })
                    .then(function(data) {
                        if (data.success) {
                            saved++;
                            input.classList.add('success');
                            setTimeout(function() {
                                input.classList.remove('success');
                            }, 1000);
                        } else {
                            errors++;
                            input.classList.add('error');
                            setTimeout(function() {
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
                    .catch(function(error) {
                        errors++;
                        console.error('Error:', error);
                    });
            });
        }

        /**
         * Fetch updated summary
         */
        function fetchSummary() {
            var firstInput = document.querySelector('.present-input');
            if (!firstInput) return;

            var session = firstInput.dataset.session || '';
            var term = firstInput.dataset.term || '';
            var classId = firstInput.dataset.class || '';

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
                .then(function(response) {
                    return response.json();
                })
                .then(function(data) {
                    if (data.success) {
                        updateSummary(data.summary);
                    }
                })
                .catch(function(error) {
                    console.error('Error:', error);
                });
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
            // Remove existing toasts
            var existingToasts = document.querySelectorAll('.toast-message');
            existingToasts.forEach(function(toast) {
                toast.remove();
            });

            var toast = document.createElement('div');
            toast.className = 'toast-message';

            var colors = {
                success: '#28a745',
                error: '#dc3545',
                info: '#17a2b8',
                warning: '#ffc107'
            };

            toast.style.background = colors[type] || '#1B3058';
            toast.textContent = message;

            document.body.appendChild(toast);

            setTimeout(function() {
                toast.style.animation = 'slideDown 0.4s ease';
                setTimeout(function() {
                    toast.remove();
                }, 400);
            }, 2500);
        }

        // Auto-submit filters on change
        document.addEventListener('DOMContentLoaded', function() {
            var filterSelects = document.querySelectorAll('.filter-select');
            filterSelects.forEach(function(select) {
                select.addEventListener('change', function() {
                    var form = document.getElementById('filterForm');
                    var session = form.querySelector('select[name="session"]');
                    var term = form.querySelector('select[name="term_id"]');
                    if (session && term && session.value && term.value) {
                        form.submit();
                    }
                });
            });
        });
    </script>
</body>

</html>