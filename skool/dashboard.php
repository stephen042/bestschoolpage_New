<?php
/**
 * ============================================================================
 * PRINCIPAL ANALYTICS DASHBOARD - PHP 8.x CONVERTED
 * ============================================================================
 * Shows: KPIs, class performance, subject performance, top/bottom students
 * Version: 2.0 (PHP 8.x Compatible) - PDO Converted
 * ============================================================================
 */

require_once('../config.php');
require_once('inc.session-create.php');

// ============================================================================
// FIX: USE CORRECT USER IDENTIFICATION
// ============================================================================
// Use the same method as class_teacher_roll_call_bulk.php
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
// GET FILTERS FROM URL
// ============================================================================
$selected_session = isset($_GET['session_id']) ? (int)$_GET['session_id'] : 0;
$selected_term = isset($_GET['term_id']) ? (int)$_GET['term_id'] : 0;
$selected_class = isset($_GET['class_id']) ? (int)$_GET['class_id'] : 0;

// If no session selected, get the latest session
if ($selected_session == 0) {
    $sessionData = db_get_row("SELECT id FROM school_session WHERE create_by_userid = ? ORDER BY id DESC LIMIT 1", [$create_by_userid]);
    $selected_session = $sessionData['id'] ?? 0;
}

// If no term selected, get first term
if ($selected_term == 0) {
    $termData = db_get_row("SELECT id FROM school_term WHERE create_by_userid = ? ORDER BY id LIMIT 1", [$create_by_userid]);
    $selected_term = $termData['id'] ?? 0;
}

// ============================================================================
// GET FILTER DATA
// ============================================================================
$sessions = db_get_rows("SELECT id, session FROM school_session WHERE create_by_userid = ? ORDER BY session DESC", [$create_by_userid]);
$terms = db_get_rows("SELECT id, term FROM school_term WHERE create_by_userid = ? ORDER BY id", [$create_by_userid]);
$classes = db_get_rows("SELECT id, name FROM school_class WHERE create_by_userid = ? ORDER BY name", [$create_by_userid]);

$sessionMap = [];
foreach ($sessions as $row) {
    $sessionMap[(int)$row['id']] = $row['session'];
}

$termMap = [];
foreach ($terms as $row) {
    $termMap[(int)$row['id']] = $row['term'];
}

$classMap = [];
foreach ($classes as $row) {
    $classMap[(int)$row['id']] = $row['name'];
}

$selected_class_name = ($selected_class > 0 && isset($classMap[$selected_class])) ? $classMap[$selected_class] : 'All Classes';

// Build reusable student filter condition to keep KPI stats consistent with selected filters.
$studentFilterSql = "ms.create_by_userid = ?";
$studentFilterParams = [$create_by_userid];

if ($selected_session > 0) {
    $studentFilterSql .= " AND ms.session = ?";
    $studentFilterParams[] = $selected_session;
}
if ($selected_term > 0) {
    $studentFilterSql .= " AND ms.term_id = ?";
    $studentFilterParams[] = $selected_term;
}
if ($selected_class > 0) {
    $studentFilterSql .= " AND ms.class = ?";
    $studentFilterParams[] = $selected_class;
}

// ============================================================================
// KPI: TOTAL STUDENTS (Male/Female breakdown)
// ============================================================================
$students_query = "SELECT 
                    COUNT(*) as total,
                    COALESCE(SUM(CASE WHEN ms.gender = 'Male' OR ms.gender = 'M' THEN 1 ELSE 0 END), 0) as male,
                    COALESCE(SUM(CASE WHEN ms.gender = 'Female' OR ms.gender = 'F' THEN 1 ELSE 0 END), 0) as female
                    FROM manage_student ms
                    WHERE $studentFilterSql";
$students_data = db_get_row($students_query, $studentFilterParams);
if (empty($students_data)) $students_data = ['total' => 0, 'male' => 0, 'female' => 0];

// ============================================================================
// KPI: TOTAL STAFF (Male/Female breakdown)
// ============================================================================
$staff_query = "SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN gender = 'Male' OR gender = 'M' THEN 1 ELSE 0 END) as male,
                SUM(CASE WHEN gender = 'Female' OR gender = 'F' THEN 1 ELSE 0 END) as female
                FROM staff_manage WHERE create_by_userid = ?";
$staff_data = db_get_row($staff_query, [$create_by_userid]);
if (empty($staff_data)) $staff_data = ['total' => 0, 'male' => 0, 'female' => 0];

// ============================================================================
// KPI: TOTAL SUBJECTS
// ============================================================================
$subjects_data = db_get_val("SELECT COUNT(*) FROM school_subject WHERE create_by_userid = ?", [$create_by_userid]);

// ============================================================================
// CLASS PERFORMANCE
// ============================================================================
$class_performance = [];
if ($selected_session > 0 && $selected_term > 0) {
    $class_query = "SELECT sc.id, sc.name as class_name,
                    COUNT(DISTINCT ms.id) as student_count,
                    ROUND(AVG(CAST(ist.score AS DECIMAL(5,2))), 1) as avg_score
                    FROM school_class sc
                    LEFT JOIN manage_student ms ON sc.id = ms.class 
                        AND ms.create_by_userid = ?
                        AND ms.session = ?
                        AND ms.term_id = ?
                    LEFT JOIN input_score_class_teacher ist ON ms.id = ist.student_id 
                        AND ist.session_id = ? AND ist.term_id = ?
                        AND ist.class_id = sc.id
                        AND ist.create_by_userid = ?
                    WHERE sc.create_by_userid = ?
                    " . ($selected_class > 0 ? " AND sc.id = ?" : "") . "
                    GROUP BY sc.id
                    ORDER BY avg_score DESC, sc.name ASC";
    
    $classParams = [
        $create_by_userid,
        $selected_session,
        $selected_term,
        $selected_session,
        $selected_term,
        $create_by_userid,
        $create_by_userid
    ];
    if ($selected_class > 0) {
        $classParams[] = $selected_class;
    }
    $class_performance = db_get_rows($class_query, $classParams);
}

// Add position to class performance
$position = 1;
foreach ($class_performance as &$class) {
    $class['position'] = $position++;
}

// ============================================================================
// SUBJECT PERFORMANCE
// ============================================================================
$subject_performance = [];
if ($selected_session > 0 && $selected_term > 0) {
    $subject_query = "SELECT ss.subject,
                      ROUND(AVG(CAST(ist.score AS DECIMAL(5,2))), 1) as avg_score
                      FROM school_subject ss
                      LEFT JOIN input_score_class_teacher ist ON ss.id = ist.subject_id
                          AND ist.session_id = ? AND ist.term_id = ?
                          AND ist.create_by_userid = ?
                          " . ($selected_class > 0 ? " AND ist.class_id = ?" : "") . "
                      WHERE ss.create_by_userid = ?
                      " . ($selected_class > 0 ? " AND ss.class_id = ?" : "") . "
                      GROUP BY ss.id
                      ORDER BY avg_score DESC, ss.subject ASC";

    $subjectParams = [$selected_session, $selected_term, $create_by_userid];
    if ($selected_class > 0) {
        $subjectParams[] = $selected_class;
    }
    $subjectParams[] = $create_by_userid;
    if ($selected_class > 0) {
        $subjectParams[] = $selected_class;
    }

    $subject_performance = db_get_rows($subject_query, $subjectParams);
}

// ============================================================================
// TOP 10 STUDENTS
// ============================================================================
$top_students = [];
if ($selected_session > 0 && $selected_term > 0) {
    $top_query = "SELECT 
                  ms.id, 
                  ms.first_name, 
                  ms.last_name,
                  ROUND(AVG(CAST(ist.score AS DECIMAL(5,2))), 1) as avg_score,
                  sc.name as class_name
                  FROM input_score_class_teacher ist
                  JOIN manage_student ms ON ist.student_id = ms.id
                  JOIN school_class sc ON ms.class = sc.id
                  WHERE ms.create_by_userid = ?
                  AND ms.session = ?
                  AND ms.term_id = ?
                  AND ist.session_id = ? 
                  AND ist.term_id = ?
                  AND ist.create_by_userid = ?
                  " . ($selected_class > 0 ? " AND ms.class = ? AND ist.class_id = ?" : "") . "
                  GROUP BY ms.id
                  ORDER BY avg_score DESC, ms.first_name ASC, ms.last_name ASC
                  LIMIT 10";

    $topParams = [$create_by_userid, $selected_session, $selected_term, $selected_session, $selected_term, $create_by_userid];
    if ($selected_class > 0) {
        $topParams[] = $selected_class;
        $topParams[] = $selected_class;
    }
    $top_students = db_get_rows($top_query, $topParams);
}

// ============================================================================
// BOTTOM 10 STUDENTS
// ============================================================================
$bottom_students = [];
if ($selected_session > 0 && $selected_term > 0) {
    $bottom_query = "SELECT ms.id, ms.first_name, ms.last_name,
                     ROUND(AVG(CAST(ist.score AS DECIMAL(5,2))), 1) as avg_score
                     FROM input_score_class_teacher ist
                     JOIN manage_student ms ON ms.id = ist.student_id
                     WHERE ms.create_by_userid = ?
                     AND ms.session = ?
                     AND ms.term_id = ?
                     AND ist.session_id = ?
                     AND ist.term_id = ?
                     AND ist.create_by_userid = ?
                     " . ($selected_class > 0 ? " AND ms.class = ? AND ist.class_id = ?" : "") . "
                     GROUP BY ms.id
                     ORDER BY avg_score ASC, ms.first_name ASC, ms.last_name ASC
                     LIMIT 10";

    $bottomParams = [$create_by_userid, $selected_session, $selected_term, $selected_session, $selected_term, $create_by_userid];
    if ($selected_class > 0) {
        $bottomParams[] = $selected_class;
        $bottomParams[] = $selected_class;
    }
    $bottom_students = db_get_rows($bottom_query, $bottomParams);
}

// ============================================================================
// STUDENTS PER CLASS
// ============================================================================
$students_per_class = db_get_rows(
    "SELECT sc.name as class_name,
            COUNT(ms.id) as student_count,
            COALESCE(SUM(CASE WHEN ms.gender = 'Male' OR ms.gender = 'M' THEN 1 ELSE 0 END), 0) as male_count,
            COALESCE(SUM(CASE WHEN ms.gender = 'Female' OR ms.gender = 'F' THEN 1 ELSE 0 END), 0) as female_count
     FROM school_class sc
     LEFT JOIN manage_student ms ON sc.id = ms.class 
         AND ms.create_by_userid = ?
         " . ($selected_session > 0 ? " AND ms.session = ?" : "") . "
         " . ($selected_term > 0 ? " AND ms.term_id = ?" : "") . "
     WHERE sc.create_by_userid = ?
     " . ($selected_class > 0 ? " AND sc.id = ?" : "") . "
     GROUP BY sc.id
     ORDER BY student_count DESC",
    array_merge(
        [$create_by_userid],
        $selected_session > 0 ? [$selected_session] : [],
        $selected_term > 0 ? [$selected_term] : [],
        [$create_by_userid],
        $selected_class > 0 ? [$selected_class] : []
    )
);

// ============================================================================
// GET CURRENT SESSION & TERM NAMES
// ============================================================================
$current_session_name = $sessionMap[$selected_session] ?? '';
$current_term_name = $termMap[$selected_term] ?? '';
?>
<!DOCTYPE html>
<html>
<head>
    <?php include('inc.meta.php'); ?>
    <title>Principal Dashboard - Best School Page</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        /* ============================================================
        RESET & BASE
        ============================================================ */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
            background: #f0f2f5;
            line-height: 1.6;
            color: #333;
        }
        
        /* ============================================================
        SIDEBAR TOGGLE - FIX (Remove conflicting styles)
        ============================================================ */
        /* Let inc.header.php handle the sidebar toggle - don't override */
        
        /* Remove the conflicting .fixed-left styles */
        
        #wrapper {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        
        .content-page {
            flex: 1;
            padding-top: 60px;
            transition: margin-left 0.3s ease;
        }
        
        /* Keep the sidebar margin working with the existing system */
        .content-page {
            margin-left: 240px;
        }
        
        /* When sidebar is collapsed via the toggle button */
        body.sidebar-collapsed .content-page {
            margin-left: 60px;
        }
        
        /* Mobile: adjust margin */
        @media (max-width: 768px) {
            .content-page {
                margin-left: 0 !important;
            }
            .content-page.sidebar-collapsed {
                margin-left: 0 !important;
            }
        }
        
        .content {
            padding: 15px;
        }
        
        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 10px;
        }
        
        /* ============================================================
        FILTER BAR - MOBILE FIRST
        ============================================================ */
        .filter-bar {
            background: white;
            padding: 15px;
            border-radius: 12px;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
            display: flex;
            flex-direction: column;
            gap: 12px;
        }
        
        .filter-row {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        
        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }
        
        .filter-group label {
            font-weight: 600;
            font-size: 13px;
            color: #555;
        }
        
        .filter-group select {
            padding: 10px 12px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 14px;
            background: white;
            width: 100%;
            -webkit-appearance: none;
            appearance: none;
            background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 12px center;
            background-size: 16px;
            cursor: pointer;
        }
        
        .filter-group select:focus {
            border-color: #667eea;
            outline: none;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.15);
        }
        
        .filter-actions {
            display: flex;
            gap: 10px;
            margin-top: 4px;
        }
        
        .filter-actions .btn {
            flex: 1;
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.3s;
            text-align: center;
            text-decoration: none;
        }
        
        .btn-primary {
            background: #667eea;
            color: white;
        }
        .btn-primary:hover {
            background: #5a6fd6;
            transform: translateY(-1px);
        }
        
        .btn-outline {
            background: transparent;
            color: #667eea;
            border: 2px solid #667eea;
        }
        .btn-outline:hover {
            background: #667eea;
            color: white;
        }
        
        .scope-badge {
            font-size: 12px;
            color: #888;
            text-align: center;
            padding: 6px 12px;
            background: #f5f6fa;
            border-radius: 20px;
            font-weight: 500;
        }
        
        /* ============================================================
        KPI GRID - MOBILE FIRST
        ============================================================ */
        .kpi-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            margin-bottom: 20px;
        }
        
        .kpi-card {
            background: white;
            padding: 16px 12px;
            border-radius: 12px;
            text-align: center;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            transition: transform 0.2s;
        }
        
        .kpi-card:active {
            transform: scale(0.97);
        }
        
        .kpi-icon {
            font-size: 28px;
            margin-bottom: 4px;
        }
        
        .kpi-value {
            font-size: 24px;
            font-weight: 700;
            color: #667eea;
            line-height: 1.2;
        }
        
        .kpi-label {
            color: #666;
            font-size: 12px;
            font-weight: 500;
            margin-top: 2px;
        }
        
        .kpi-card small {
            font-size: 10px;
            color: #999;
            display: block;
            margin-top: 4px;
        }
        
        /* ============================================================
        CHART CARDS - MOBILE FIRST
        ============================================================ */
        .chart-card {
            background: white;
            border-radius: 12px;
            padding: 16px;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }
        
        .chart-card h3 {
            font-size: 16px;
            margin-bottom: 12px;
            padding-bottom: 10px;
            border-bottom: 2px solid #667eea;
            color: #333;
        }
        
        .chart-wrapper {
            position: relative;
            height: 200px;
            margin-bottom: 10px;
        }
        
        .chart-wrapper canvas {
            width: 100% !important;
            height: 100% !important;
        }
        
        /* ============================================================
        PROGRESS BARS
        ============================================================ */
        .progress-item {
            margin-bottom: 10px;
        }
        
        .progress-item .label-row {
            display: flex;
            justify-content: space-between;
            font-size: 13px;
            margin-bottom: 3px;
        }
        
        .progress-item .label-row .class-name {
            font-weight: 500;
        }
        
        .progress-item .label-row .score {
            font-weight: 600;
            color: #667eea;
        }
        
        .progress-bar {
            background: #e8ecf1;
            border-radius: 20px;
            overflow: hidden;
            height: 20px;
        }
        
        .progress-fill {
            height: 100%;
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: flex-end;
            padding-right: 6px;
            color: white;
            font-size: 11px;
            font-weight: 600;
            transition: width 0.6s ease;
            min-width: 24px;
        }
        
        .progress-meta {
            font-size: 11px;
            color: #888;
            margin-top: 2px;
        }
        
        /* ============================================================
        STUDENT TABLES - MOBILE FIRST
        ============================================================ */
        .two-columns {
            display: grid;
            grid-template-columns: 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .students-table {
            background: white;
            border-radius: 12px;
            padding: 16px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }
        
        .students-table h3 {
            font-size: 16px;
            margin-bottom: 12px;
            padding-bottom: 10px;
            border-bottom: 2px solid #667eea;
        }
        
        .table-responsive {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            margin: 0 -4px;
            padding: 0 4px;
        }
        
        .students-table table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
            min-width: 300px;
        }
        
        .students-table th {
            background: #f8f9fa;
            font-weight: 600;
            padding: 10px 8px;
            text-align: left;
            border-bottom: 2px solid #e0e0e0;
            font-size: 12px;
            text-transform: uppercase;
            color: #555;
            letter-spacing: 0.3px;
        }
        
        .students-table td {
            padding: 10px 8px;
            border-bottom: 1px solid #eee;
            vertical-align: middle;
        }
        
        .students-table tr:last-child td {
            border-bottom: none;
        }
        
        .students-table .rank-1 { background: #fff8e1; }
        .students-table .rank-2 { background: #f5f5f5; }
        .students-table .rank-3 { background: #fafafa; }
        
        .students-table .mini-bar {
            width: 60px;
            height: 14px;
            background: #e8ecf1;
            border-radius: 10px;
            overflow: hidden;
            display: inline-block;
            vertical-align: middle;
        }
        
        .students-table .mini-bar .fill {
            height: 100%;
            border-radius: 10px;
            transition: width 0.4s ease;
        }
        
        .students-table .rank-number {
            display: inline-block;
            width: 24px;
            height: 24px;
            line-height: 24px;
            text-align: center;
            border-radius: 50%;
            background: #667eea;
            color: white;
            font-size: 12px;
            font-weight: 700;
        }
        
        .students-table .rank-number.gold { background: #ffc107; color: #333; }
        .students-table .rank-number.silver { background: #bdbdbd; color: #333; }
        .students-table .rank-number.bronze { background: #cd7f32; color: white; }
        
        /* ============================================================
        EMPTY STATE
        ============================================================ */
        .empty-state {
            text-align: center;
            padding: 30px 20px;
            color: #999;
        }
        .empty-state .icon {
            font-size: 40px;
            margin-bottom: 10px;
            display: block;
        }
        .empty-state p {
            font-size: 14px;
        }
        
        /* ============================================================
        TOAST / ALERT
        ============================================================ */
        .alert-info {
            background: #e3f2fd;
            border-radius: 8px;
            padding: 12px 16px;
            color: #0d47a1;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .alert-info .icon {
            font-size: 20px;
        }
        
        /* ============================================================
        DESKTOP BREAKPOINT (768px+)
        ============================================================ */
        @media (min-width: 768px) {
            .content {
                padding: 25px;
            }
            
            .container {
                padding: 0 20px;
            }
            
            /* Filter bar - horizontal layout */
            .filter-bar {
                flex-direction: row;
                flex-wrap: wrap;
                align-items: flex-end;
                padding: 18px 24px;
                gap: 16px;
            }
            
            .filter-row {
                flex-direction: row;
                flex-wrap: wrap;
                flex: 1;
                gap: 16px;
                align-items: flex-end;
            }
            
            .filter-group {
                min-width: 160px;
                flex: 1;
            }
            
            .filter-group select {
                padding: 8px 32px 8px 12px;
            }
            
            .filter-actions {
                flex: 0 0 auto;
                gap: 10px;
            }
            
            .filter-actions .btn {
                flex: 0 0 auto;
                padding: 8px 24px;
            }
            
            .scope-badge {
                flex: 0 0 auto;
                padding: 6px 16px;
            }
            
            /* KPI grid - 4 columns */
            .kpi-grid {
                grid-template-columns: repeat(4, 1fr);
                gap: 20px;
            }
            
            .kpi-card {
                padding: 20px;
            }
            
            .kpi-icon {
                font-size: 32px;
            }
            
            .kpi-value {
                font-size: 28px;
            }
            
            /* Two columns for tables */
            .two-columns {
                grid-template-columns: 1fr 1fr;
                gap: 24px;
            }
            
            .chart-wrapper {
                height: 250px;
            }
            
            .students-table table {
                font-size: 14px;
                min-width: auto;
            }
        }
        
        /* ============================================================
        LARGE DESKTOP (1024px+)
        ============================================================ */
        @media (min-width: 1024px) {
            .content {
                padding: 30px;
            }
            
            .container {
                padding: 0 30px;
            }
            
            .kpi-card {
                padding: 24px;
            }
            
            .kpi-value {
                font-size: 32px;
            }
            
            .chart-wrapper {
                height: 280px;
            }
        }
        
        /* ============================================================
        VERY SMALL SCREENS (320px-)
        ============================================================ */
        @media (max-width: 400px) {
            .kpi-grid {
                grid-template-columns: 1fr 1fr;
                gap: 8px;
            }
            
            .kpi-card {
                padding: 12px 8px;
            }
            
            .kpi-value {
                font-size: 20px;
            }
            
            .kpi-icon {
                font-size: 22px;
            }
            
            .filter-group select {
                font-size: 13px;
                padding: 8px 28px 8px 10px;
            }
            
            .students-table table {
                font-size: 12px;
                min-width: 250px;
            }
            
            .students-table th,
            .students-table td {
                padding: 6px 4px;
            }
        }
        
        /* ============================================================
        LOADING / ANIMATIONS
        ============================================================ */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .kpi-card, .chart-card, .students-table {
            animation: fadeInUp 0.4s ease forwards;
        }
        
        .kpi-card:nth-child(2) { animation-delay: 0.05s; }
        .kpi-card:nth-child(3) { animation-delay: 0.1s; }
        .kpi-card:nth-child(4) { animation-delay: 0.15s; }
        
        /* ============================================================
        SCROLLBAR STYLING
        ============================================================ */
        .table-responsive::-webkit-scrollbar {
            height: 4px;
        }
        .table-responsive::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }
        .table-responsive::-webkit-scrollbar-thumb {
            background: #c1c7d0;
            border-radius: 4px;
        }
        .table-responsive::-webkit-scrollbar-thumb:hover {
            background: #a0a7b0;
        }
        
        /* ============================================================
        PRINT STYLES
        ============================================================ */
        @media print {
            .filter-bar,
            .filter-actions .btn,
            .btn {
                display: none !important;
            }
            .kpi-card {
                box-shadow: none !important;
                border: 1px solid #ddd;
            }
            .chart-card,
            .students-table {
                box-shadow: none !important;
                border: 1px solid #ddd;
                break-inside: avoid;
            }
            .content-page {
                margin-left: 0 !important;
                padding-top: 0 !important;
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
            <div class="container">
                
                <!-- ============================================================
                FILTER BAR - Responsive
                ============================================================ -->
                <div class="filter-bar">
                    <div class="filter-row">
                        <div class="filter-group">
                            <label for="session_filter">📅 Session</label>
                            <select id="session_filter" onchange="applyFilters()">
                                <?php foreach($sessions as $session): ?>
                                    <option value="<?php echo $session['id']; ?>" <?php echo $selected_session == $session['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($session['session']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="filter-group">
                            <label for="term_filter">📆 Term</label>
                            <select id="term_filter" onchange="applyFilters()">
                                <?php foreach($terms as $term): ?>
                                    <option value="<?php echo $term['id']; ?>" <?php echo $selected_term == $term['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($term['term']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="filter-group">
                            <label for="class_filter">🏫 Class</label>
                            <select id="class_filter" onchange="applyFilters()">
                                <option value="0">All Classes</option>
                                <?php foreach($classes as $class): ?>
                                    <option value="<?php echo $class['id']; ?>" <?php echo $selected_class == $class['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($class['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="filter-actions">
                        <button class="btn btn-primary" onclick="applyFilters()">🔍 Apply</button>
                        <button class="btn btn-outline" onclick="resetFilters()">↺ Reset</button>
                    </div>
                    
                    <div class="scope-badge">
                        📌 <?= htmlspecialchars($current_session_name ?: 'N/A') ?> / <?= htmlspecialchars($current_term_name ?: 'N/A') ?> / <?= htmlspecialchars($selected_class_name) ?>
                    </div>
                </div>
                
                <!-- ============================================================
                KPI CARDS
                ============================================================ -->
                <div class="kpi-grid">
                    <div class="kpi-card">
                        <div class="kpi-icon">👨‍🎓</div>
                        <div class="kpi-value"><?php echo number_format($students_data['total'] ?? 0); ?></div>
                        <div class="kpi-label">Students</div>
                        <small>♂ <?php echo number_format($students_data['male'] ?? 0); ?> · ♀ <?php echo number_format($students_data['female'] ?? 0); ?></small>
                    </div>
                    <div class="kpi-card">
                        <div class="kpi-icon">👨‍🏫</div>
                        <div class="kpi-value"><?php echo number_format($staff_data['total'] ?? 0); ?></div>
                        <div class="kpi-label">Staff</div>
                        <small>♂ <?php echo number_format($staff_data['male'] ?? 0); ?> · ♀ <?php echo number_format($staff_data['female'] ?? 0); ?></small>
                    </div>
                    <div class="kpi-card">
                        <div class="kpi-icon">📚</div>
                        <div class="kpi-value"><?php echo number_format($subjects_data ?? 0); ?></div>
                        <div class="kpi-label">Subjects</div>
                        <small>Active subjects</small>
                    </div>
                    <div class="kpi-card">
                        <div class="kpi-icon">🏆</div>
                        <div class="kpi-value" style="font-size:16px;"><?php echo htmlspecialchars($current_session_name ?: 'N/A'); ?></div>
                        <div class="kpi-label">Session</div>
                        <small><?php echo htmlspecialchars($current_term_name ?: 'N/A'); ?> Term</small>
                    </div>
                </div>
                
                <!-- ============================================================
                CLASS PERFORMANCE
                ============================================================ -->
                <div class="chart-card">
                    <h3>📊 Class Performance</h3>
                    
                    <?php if (!empty($class_performance)): ?>
                        <div class="chart-wrapper">
                            <canvas id="classPerformanceChart"></canvas>
                        </div>
                        
                        <div style="margin-top:16px;">
                            <?php foreach($class_performance as $class): 
                                $score = min($class['avg_score'] ?? 0, 100);
                                $color = ($class['position'] ?? 1) == 1 ? '#48bb78' : (($class['position'] ?? 2) == 2 ? '#4299e1' : '#ed8936');
                            ?>
                                <div class="progress-item">
                                    <div class="label-row">
                                        <span class="class-name"><?php echo htmlspecialchars($class['class_name'] ?? 'Class'); ?></span>
                                        <span class="score"><?php echo $class['avg_score'] ?? 0; ?>%</span>
                                    </div>
                                    <div class="progress-bar">
                                        <div class="progress-fill" style="width: <?php echo $score; ?>%; background: <?php echo $color; ?>;">
                                            <?php if ($score >= 40): ?><?php echo $score; ?>%<?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="progress-meta">
                                        Rank #<?php echo $class['position'] ?? 'N/A'; ?> · <?php echo $class['student_count'] ?? 0; ?> students
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <span class="icon">📊</span>
                            <p>No class performance data available.<br>Please enter scores first.</p>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- ============================================================
                SUBJECT PERFORMANCE
                ============================================================ -->
                <div class="chart-card">
                    <h3>📈 Subject Performance</h3>
                    
                    <?php if (!empty($subject_performance)): ?>
                        <div class="chart-wrapper">
                            <canvas id="subjectPerformanceChart"></canvas>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <span class="icon">📈</span>
                            <p>No subject performance data available.<br>Please enter scores first.</p>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- ============================================================
                TOP & BOTTOM STUDENTS
                ============================================================ -->
                <div class="two-columns">
                    <div class="students-table">
                        <h3>🏆 Top 10 Students</h3>
                        
                        <?php if (!empty($top_students)): ?>
                            <div class="table-responsive">
                                <table>
                                    <thead>
                                        <tr>
                                            <th style="width:50px;">#</th>
                                            <th>Student</th>
                                            <th style="width:70px;">Score</th>
                                            <th style="width:80px;">Progress</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach($top_students as $index => $student): 
                                            $rankClass = $index == 0 ? 'gold' : ($index == 1 ? 'silver' : ($index == 2 ? 'bronze' : ''));
                                        ?>
                                        <tr class="<?php echo $index < 3 ? 'rank-' . ($index + 1) : ''; ?>">
                                            <td>
                                                <span class="rank-number <?php echo $rankClass; ?>">
                                                    <?php echo $index + 1; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?>
                                                <?php if (!empty($student['class_name'])): ?>
                                                    <br><small style="color:#999; font-size:10px;"><?php echo htmlspecialchars($student['class_name']); ?></small>
                                                <?php endif; ?>
                                            </td>
                                            <td><strong><?php echo $student['avg_score']; ?>%</strong></td>
                                            <td>
                                                <div class="mini-bar">
                                                    <div class="fill" style="width: <?php echo min($student['avg_score'], 100); ?>%; background: #48bb78;"></div>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="empty-state">
                                <span class="icon">🏆</span>
                                <p>No student data available.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="students-table">
                        <h3>⚠️ Needs Improvement</h3>
                        
                        <?php if (!empty($bottom_students)): ?>
                            <div class="table-responsive">
                                <table>
                                    <thead>
                                        <tr>
                                            <th style="width:50px;">#</th>
                                            <th>Student</th>
                                            <th style="width:70px;">Score</th>
                                            <th style="width:80px;">Progress</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach($bottom_students as $index => $student): ?>
                                        <tr>
                                            <td><span class="rank-number" style="background:#e53e3e;"><?php echo $index + 1; ?></span></td>
                                            <td><?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></td>
                                            <td><strong><?php echo $student['avg_score']; ?>%</strong></td>
                                            <td>
                                                <div class="mini-bar">
                                                    <div class="fill" style="width: <?php echo min($student['avg_score'], 100); ?>%; background: #e53e3e;"></div>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="empty-state">
                                <span class="icon">✅</span>
                                <p>No students needing improvement.<br>Great job!</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- ============================================================
                STUDENTS PER CLASS
                ============================================================ -->
                <div class="chart-card">
                    <h3>📋 Students Per Class</h3>
                    
                    <?php if (!empty($students_per_class)): ?>
                        <div class="chart-wrapper">
                            <canvas id="studentsPerClassChart"></canvas>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <span class="icon">📋</span>
                            <p>No class data available.</p>
                        </div>
                    <?php endif; ?>
                </div>
                
            </div>
        </div>
        <?php include('inc.footer.php'); ?>
    </div>
</div>

<?php include('inc.js.php'); ?>

<script>
    // ============================================================
    // FILTER FUNCTIONS
    // ============================================================
    function applyFilters() {
        const session = document.getElementById('session_filter').value;
        const term = document.getElementById('term_filter').value;
        const classId = document.getElementById('class_filter').value;
        window.location.href = `?session_id=${session}&term_id=${term}&class_id=${classId}`;
    }
    
    function resetFilters() {
        window.location.href = window.location.pathname;
    }
    
    // Auto-submit on select change (desktop)
    function autoSubmit() {
        applyFilters();
    }
    
    // ============================================================
    // CHART.JS - RESPONSIVE CONFIGURATION
    // ============================================================
    const chartDefaults = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'top',
                labels: {
                    boxWidth: 12,
                    padding: 10,
                    font: { size: 11 }
                }
            }
        }
    };
    
    <?php if (!empty($class_performance)): ?>
    // Class Performance Chart
    const classLabels = <?php echo json_encode(array_column($class_performance, 'class_name')); ?>;
    const classScores = <?php echo json_encode(array_column($class_performance, 'avg_score')); ?>;
    const colors = ['#48bb78', '#4299e1', '#ed8936', '#ecc94b', '#e53e3e', '#667eea', '#764ba2', '#38b2ac'];
    
    new Chart(document.getElementById('classPerformanceChart'), {
        type: 'bar',
        data: {
            labels: classLabels,
            datasets: [{
                label: 'Average Score (%)',
                data: classScores,
                backgroundColor: classLabels.map((_, i) => colors[i % colors.length]),
                borderRadius: 6,
                barPercentage: 0.6
            }]
        },
        options: {
            ...chartDefaults,
            scales: {
                y: {
                    beginAtZero: true,
                    max: 100,
                    title: { display: true, text: 'Score (%)', font: { size: 11 } },
                    ticks: { font: { size: 10 } }
                },
                x: {
                    ticks: { 
                        font: { size: 10 },
                        maxRotation: 45,
                        minRotation: 30
                    }
                }
            },
            plugins: {
                ...chartDefaults.plugins,
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.raw + '%';
                        }
                    }
                }
            }
        }
    });
    <?php endif; ?>
    
    <?php if (!empty($subject_performance)): ?>
    // Subject Performance Chart
    const subjectLabels = <?php echo json_encode(array_column($subject_performance, 'subject')); ?>;
    const subjectScores = <?php echo json_encode(array_column($subject_performance, 'avg_score')); ?>;
    
    new Chart(document.getElementById('subjectPerformanceChart'), {
        type: 'bar',
        data: {
            labels: subjectLabels,
            datasets: [{
                label: 'Average Score (%)',
                data: subjectScores,
                backgroundColor: '#667eea',
                borderRadius: 6,
                barPercentage: 0.65
            }]
        },
        options: {
            ...chartDefaults,
            scales: {
                y: {
                    beginAtZero: true,
                    max: 100,
                    title: { display: true, text: 'Score (%)', font: { size: 11 } },
                    ticks: { font: { size: 10 } }
                },
                x: {
                    ticks: { 
                        font: { size: 10 },
                        maxRotation: 45,
                        minRotation: 30
                    }
                }
            },
            plugins: {
                ...chartDefaults.plugins,
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.raw + '%';
                        }
                    }
                }
            }
        }
    });
    <?php endif; ?>
    
    <?php if (!empty($students_per_class)): ?>
    // Students Per Class Chart
    const spcLabels = <?php echo json_encode(array_column($students_per_class, 'class_name')); ?>;
    const spcMaleCounts = <?php echo json_encode(array_column($students_per_class, 'male_count')); ?>;
    const spcFemaleCounts = <?php echo json_encode(array_column($students_per_class, 'female_count')); ?>;
    const spcCounts = <?php echo json_encode(array_column($students_per_class, 'student_count')); ?>;
    
    new Chart(document.getElementById('studentsPerClassChart'), {
        type: 'bar',
        data: {
            labels: spcLabels,
            datasets: [{
                label: 'Male',
                data: spcMaleCounts,
                backgroundColor: '#4299e1',
                borderRadius: 4,
                barPercentage: 0.4
            }, {
                label: 'Female',
                data: spcFemaleCounts,
                backgroundColor: '#ed64a6',
                borderRadius: 4,
                barPercentage: 0.4
            }]
        },
        options: {
            ...chartDefaults,
            scales: {
                x: { 
                    stacked: true,
                    ticks: { 
                        font: { size: 10 },
                        maxRotation: 45,
                        minRotation: 30
                    }
                },
                y: { 
                    stacked: true, 
                    beginAtZero: true,
                    title: { display: true, text: 'Students', font: { size: 11 } },
                    ticks: { font: { size: 10 } }
                }
            },
            plugins: {
                ...chartDefaults.plugins,
                tooltip: {
                    callbacks: {
                        footer: function(items) {
                            if (items.length > 0) {
                                const i = items[0].dataIndex;
                                return 'Total: ' + spcCounts[i];
                            }
                            return '';
                        }
                    }
                }
            }
        }
    });
    <?php endif; ?>
</script>

</body>
</html>