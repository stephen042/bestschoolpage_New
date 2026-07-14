<?php

require_once('../config.php');
require_once('inc.session-create.php');

$PageTitle = "Term Results";
$FileName = 'term_result.php';

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
$sessionUserId = (int)($_SESSION['userid'] ?? 0);

// Get selected filters from GET
$selectedSession = $_GET['session'] ?? '';
$selectedTerm = $_GET['term_id'] ?? '';
$selectedClass = $_GET['class_id'] ?? '';
$selectedStudentId = $_GET['student_id'] ?? '';
$selectedAssessments = $_GET['assesments'] ?? '';
$assessmentIdArray = [];

// ============================================================================
// GET ALL DATA FOR FILTERS
// ============================================================================

// Get sessions
$sessions = db_get_rows("SELECT * FROM school_session WHERE create_by_userid = ? ORDER BY id DESC", [$create_by_userid]);

// Get terms
$terms = db_get_rows("SELECT * FROM school_term WHERE create_by_userid = ? ORDER BY id ASC", [$create_by_userid]);

// Get classes
$classes = db_get_rows("SELECT * FROM school_class WHERE create_by_userid = ? ORDER BY name ASC", [$create_by_userid]);

// Get all students - DISTINCT by student_id, limited by class only when selected
$studentListConditions = ["create_by_userid = ?"];
$studentListParams = [$create_by_userid];

if (!empty($selectedClass)) {
    $studentListConditions[] = "class = ?";
    $studentListParams[] = $selectedClass;
}

$studentListWhere = implode(' AND ', $studentListConditions);
$allStudents = db_get_rows(
    "SELECT ms.id, ms.student_id, ms.first_name, ms.last_name, ms.picture, ms.class, ms.session, ms.term_id, sc.name as class_name 
     FROM (
         SELECT student_id, MAX(id) as latest_id 
         FROM manage_student 
         WHERE {$studentListWhere}
         GROUP BY student_id
     ) latest 
     INNER JOIN manage_student ms ON latest.latest_id = ms.id
     LEFT JOIN school_class sc ON ms.class = sc.id
     ORDER BY ms.first_name ASC",
    $studentListParams
);

// Get assessments for the selected class
$assessments = [];
$assessmentIds = [];
if (!empty($selectedClass)) {
    $assessments = db_get_rows(
        "SELECT * FROM school_assessment WHERE (class_id = ? OR class_id IS NULL OR class_id = 0) AND create_by_userid = ? ORDER BY id ASC",
        [$selectedClass, $create_by_userid]
    );
    foreach ($assessments as $ass) {
        $assessmentIds[] = $ass['id'];
    }
}

// If no assessments selected, select all
if (empty($selectedAssessments) && !empty($assessmentIds)) {
    $selectedAssessments = implode('-', $assessmentIds);
}

// ============================================================================
// GET SELECTED STUDENT DETAILS - Always filter by term_id AND session
// ============================================================================
$selectedStudent = [];
$studentNumericId = 0;

if (!empty($selectedStudentId) && !empty($selectedSession) && !empty($selectedTerm)) {
    // Get the student by student_id, session, and term_id (preserves historical data)
    $selectedStudent = db_get_row(
        "SELECT ms.*, sc.name as class_name 
         FROM manage_student ms
         LEFT JOIN school_class sc ON ms.class = sc.id
         WHERE ms.student_id = ? 
         AND ms.create_by_userid = ? 
         AND ms.session = ?
         AND ms.term_id = ?
         ORDER BY ms.id DESC
         LIMIT 1",
        [$selectedStudentId, $create_by_userid, $selectedSession, $selectedTerm]
    );

    // If not found, try without session (fallback)
    if (empty($selectedStudent)) {
        $selectedStudent = db_get_row(
            "SELECT ms.*, sc.name as class_name 
             FROM manage_student ms
             LEFT JOIN school_class sc ON ms.class = sc.id
             WHERE ms.student_id = ? 
             AND ms.create_by_userid = ? 
             AND ms.term_id = ?
             ORDER BY ms.id DESC
             LIMIT 1",
            [$selectedStudentId, $create_by_userid, $selectedTerm]
        );
    }

    // If still not found, try without term_id (legacy fallback)
    if (empty($selectedStudent)) {
        $selectedStudent = db_get_row(
            "SELECT ms.*, sc.name as class_name 
             FROM manage_student ms
             LEFT JOIN school_class sc ON ms.class = sc.id
             WHERE ms.student_id = ? 
             AND ms.create_by_userid = ?
             ORDER BY ms.id DESC
             LIMIT 1",
            [$selectedStudentId, $create_by_userid]
        );
    }

    // Store the numeric ID for score queries
    if (!empty($selectedStudent)) {
        $studentNumericId = $selectedStudent['id'];
    }
}

// Get student randomid for PDF
$studentRandomId = '';
if (!empty($selectedStudent)) {
    $studentRandomId = $selectedStudent['randomid'] ?? '';
}

// ============================================================================
// GET SUBJECTS AND SCORES FOR SELECTED STUDENT
// ============================================================================
$subjects = [];
$resultsData = [];

if (!empty($studentNumericId) && !empty($selectedSession) && !empty($selectedTerm) && !empty($selectedClass) && !empty($selectedAssessments)) {
    $assessmentIdArray = explode('-', $selectedAssessments);
    $assessmentIdArray = array_values(array_filter(array_map('intval', $assessmentIdArray)));

    // Build assessment max-score map for ALL assessments (same as broad sheet)
    $assessmentMaxMap = [];
    $maxTotalPerSubject = 0;
    foreach ($assessments as $ass) {
        $assId = (int)($ass['id'] ?? 0);
        if ($assId <= 0) continue;
        // Include ALL assessments, not just selected ones
        $configuredMax = floatval($ass['percentage'] ?? 0);
        $assessmentMaxMap[$assId] = $configuredMax > 0 ? $configuredMax : 100;
        $maxTotalPerSubject += $configuredMax > 0 ? $configuredMax : 100;
    }
    // Get subjects for this class
    $subjects = db_get_rows(
        "SELECT * FROM school_subject WHERE class_id = ? AND create_by_userid = ? ORDER BY subject ASC",
        [$selectedClass, $create_by_userid]
    );

    $selectedScoresMap = [];
    if (!empty($subjects) && !empty($assessmentIdArray)) {
        $subjectIds = [];
        foreach ($subjects as $subject) {
            $subjectId = (int)($subject['id'] ?? 0);
            if ($subjectId > 0) {
                $subjectIds[] = $subjectId;
            }
        }

        if (!empty($subjectIds)) {
            $subjectPlaceholders = implode(',', array_fill(0, count($subjectIds), '?'));
            $assessmentPlaceholders = implode(',', array_fill(0, count($assessmentIdArray), '?'));
            $selectedScoreRows = db_get_rows(
                "SELECT subject_id, assesment_id, COALESCE(SUM(score), 0) AS total_score
                 FROM input_score_class_teacher
                 WHERE student_id = ?
                 AND class_id = ?
                 AND session_id = ?
                 AND term_id = ?
                 AND create_by_userid = ?
                 AND subject_id IN ($subjectPlaceholders)
                 AND assesment_id IN ($assessmentPlaceholders)
                 GROUP BY subject_id, assesment_id",
                array_merge([$studentNumericId, $selectedClass, $selectedSession, $selectedTerm, $create_by_userid], $subjectIds, $assessmentIdArray)
            );

            if (is_array($selectedScoreRows)) {
                foreach ($selectedScoreRows as $scoreRow) {
                    $subjectId = (int)($scoreRow['subject_id'] ?? 0);
                    $assessmentId = (int)($scoreRow['assesment_id'] ?? 0);
                    if ($subjectId > 0 && $assessmentId > 0) {
                        if (!isset($selectedScoresMap[$subjectId])) {
                            $selectedScoresMap[$subjectId] = [];
                        }
                        $selectedScoresMap[$subjectId][$assessmentId] = (float)($scoreRow['total_score'] ?? 0);
                    }
                }
            }
        }
    }

    // For each subject, get scores from selected assessments
    foreach ($subjects as $subject) {
        $subjectId = (int)($subject['id'] ?? 0);
        $subjectTotal = 0;
        $subjectScores = [];

        foreach ($assessmentIdArray as $assessmentId) {
            $score = (float)($selectedScoresMap[$subjectId][$assessmentId] ?? 0);
            $subjectScores[$assessmentId] = $score;
            $subjectTotal += $score;
        }

        // Calculate PERCENTAGE for display only
        $percentage = $maxTotalPerSubject > 0 ? round(($subjectTotal / $maxTotalPerSubject) * 100, 2) : 0;
        $percentage = max(0, min(100, (float)$percentage));

        // Grade must follow broad sheet basis (raw subject total / average scale)
        $grade = resolveGradeFromScale($create_by_userid, $subjectTotal);

        $resultsData[] = [
            'subject' => $subject['subject'],
            'subject_id' => $subject['id'],
            'scores' => $subjectScores,
            'total' => $subjectTotal,
            'percentage' => $percentage,
            'grade' => $grade
        ];
    }
}

// ============================================================================
// CALCULATE OVERALL STATS - PERCENTAGE BASED
// ============================================================================

// Calculate total raw score (sum of all subjects)
$overallTotal = 0;
foreach ($resultsData as $data) {
    $overallTotal += $data['total'];
}
$subjectCount = count($resultsData);

// Calculate max possible total (each subject max is 100)
$overallMaxTotal = $subjectCount * ($maxTotalPerSubject ?? 100);

// Calculate overall PERCENTAGE for display
$overallPercentage = $overallMaxTotal > 0 ? round(($overallTotal / $overallMaxTotal) * 100, 2) : 0;
$overallPercentage = max(0, min(100, (float)$overallPercentage));

// Broad sheet grade basis: average score per subject
$overallAverage = $subjectCount > 0 ? round(($overallTotal / $subjectCount), 2) : 0;
$overallGrade = resolveGradeFromScale($create_by_userid, $overallAverage);

// Get grade scale for color coding
$gradeScale = db_get_rows("SELECT * FROM school_grade WHERE create_by_userid = ? ORDER BY minimum_number ASC", [$create_by_userid]);

// ============================================================================
// SIMPLIFIED GRADE RESOLUTION FUNCTION
// ============================================================================

function resolveGradeFromScale($createByUserId, $score)
{
    $score = round((float)$score, 2);

    $grades = db_get_rows(
        "SELECT grade, minimum_number, maximum_number
         FROM school_grade
         WHERE create_by_userid = ?
         AND minimum_number <= maximum_number
         ORDER BY minimum_number DESC",
        [$createByUserId]
    );

    if (empty($grades)) {
        return ($score >= 70 ? 'A' : ($score >= 60 ? 'B' : ($score >= 50 ? 'C' : ($score >= 45 ? 'D' : ($score >= 40 ? 'E' : 'F')))));
    }

    foreach ($grades as $grade) {
        $min = (float)($grade['minimum_number'] ?? 0);
        $max = (float)($grade['maximum_number'] ?? 100);

        if ($score >= $min && $score <= $max) {
            return trim($grade['grade']);
        }
    }

    return ($score >= 70 ? 'A' : ($score >= 60 ? 'B' : ($score >= 50 ? 'C' : ($score >= 45 ? 'D' : ($score >= 40 ? 'E' : 'F')))));
}

// Function to get grade color
function getGradeColor($percentage, $gradeScale)
{
    if ($percentage >= 70) return '#28a745';
    if ($percentage >= 60) return '#20c997';
    if ($percentage >= 50) return '#ffc107';
    if ($percentage >= 45) return '#fd7e14';
    if ($percentage >= 40) return '#dc3545';
    return '#6c757d';
}

// Function to get student photo
function getStudentPhoto($picture)
{
    if (!empty($picture) && file_exists('../uploads/' . $picture)) {
        return '../uploads/' . $picture;
    }
    return '';
}

// ============================================================================
// FALLBACK: If no resultsData, try getting from result_total table
// ============================================================================
if (empty($resultsData) && !empty($studentNumericId) && !empty($selectedClass) && !empty($selectedSession) && !empty($selectedTerm)) {
    $storedResult = db_get_row(
        "SELECT total FROM result_total 
         WHERE student_id = ? AND class_id = ? AND session_id = ? AND term_id = ? AND create_by_userid = ?",
        [$studentNumericId, $selectedClass, $selectedSession, $selectedTerm, $create_by_userid]
    );
    if ($storedResult) {
        $overallTotal = (float)$storedResult['total'];
        $subjectCount = count($subjects) > 0 ? count($subjects) : 1;
        $overallMaxTotal = $subjectCount * $maxTotalPerSubject;
        $overallPercentage = $overallMaxTotal > 0 ? round(($overallTotal / $overallMaxTotal) * 100, 2) : 0;
        $overallPercentage = max(0, min(100, (float)$overallPercentage));
        $overallAverage = $subjectCount > 0 ? round(($overallTotal / $subjectCount), 2) : 0;
        $overallGrade = resolveGradeFromScale($create_by_userid, $overallAverage);
    }
}
?>
<!DOCTYPE html>
<html>

<head>
    <?php include('inc.meta.php'); ?>
    <!-- ============================================================
    CSS STYLES - MOBILE FIRST
    ============================================================ -->
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
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        /* ============================================================
        CONTAINER
        ============================================================ */
        .results-container {
            max-width: 1600px;
            margin: 0 auto;
            padding: 20px;
        }

        /* ============================================================
        PAGE HEADER
        ============================================================ */
        .page-header {
            margin-bottom: 30px;
        }

        .page-header h2 {
            color: #1B3058;
            margin: 0;
            font-size: 28px;
        }

        .page-header p {
            color: #666;
            margin-top: 5px;
        }

        /* ============================================================
        FILTER BAR - MOBILE FIRST
        ============================================================ */
        .filter-bar {
            background: white;
            border-radius: 20px;
            padding: 20px 25px;
            margin-bottom: 25px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        .filter-row {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            align-items: flex-end;
        }

        .filter-group {
            flex: 1;
            min-width: 150px;
        }

        .filter-group label {
            display: block;
            font-size: 11px;
            font-weight: 700;
            color: #888;
            margin-bottom: 5px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .filter-select {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 14px;
            font-size: 14px;
            background: white;
            cursor: pointer;
            transition: all 0.2s;
        }

        .filter-select:focus {
            outline: none;
            border-color: #1B3058;
        }

        .assessment-group {
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #e0e0e0;
        }

        .assessment-label {
            font-size: 12px;
            font-weight: 600;
            color: #666;
            margin-bottom: 10px;
            display: block;
        }

        .assessment-checkboxes {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
        }

        .assessment-checkbox {
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
        }

        .assessment-checkbox input {
            width: 18px;
            height: 18px;
            cursor: pointer;
        }

        .assessment-checkbox span {
            font-size: 13px;
            color: #333;
        }

        .apply-btn {
            background: #1B3058;
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            margin-top: 15px;
        }

        .apply-btn:hover {
            background: #f21151;
            transform: translateY(-2px);
        }

        /* ============================================================
        TWO COLUMN LAYOUT - MOBILE FIRST
        ============================================================ */
        .two-column-layout {
            display: flex;
            gap: 25px;
            flex-wrap: wrap;
        }

        .students-panel {
            flex: 1;
            min-width: 300px;
            background: white;
            border-radius: 24px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            overflow: hidden;
        }

        .results-panel {
            flex: 3;
            min-width: 500px;
        }

        .panel-header {
            padding: 18px 20px;
            background: linear-gradient(135deg, #1B3058 0%, #2a4780 100%);
            color: white;
            font-weight: 600;
            font-size: 16px;
        }

        .search-box {
            padding: 15px 20px;
            border-bottom: 1px solid #f0f0f0;
        }

        .search-input {
            width: 100%;
            padding: 10px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 30px;
            font-size: 14px;
            transition: all 0.2s;
        }

        .search-input:focus {
            outline: none;
            border-color: #1B3058;
        }

        .students-list {
            max-height: 60vh;
            overflow-y: auto;
        }

        .student-card {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 20px;
            border-bottom: 1px solid #f0f0f0;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
        }

        .student-card:hover {
            background: #f8f9ff;
        }

        .student-card.active {
            background: #e8eef5;
            border-left: 4px solid #1B3058;
        }

        .student-avatar {
            width: 48px;
            height: 48px;
            border-radius: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 18px;
            background: linear-gradient(135deg, #1B3058 0%, #2a4780 100%);
            color: white;
            overflow: hidden;
            flex-shrink: 0;
        }

        .student-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .student-info h4 {
            margin: 0;
            font-size: 15px;
            color: #333;
        }

        .student-info p {
            margin: 5px 0 0;
            font-size: 11px;
            color: #888;
        }

        /* ============================================================
        RESULTS CARD - MOBILE FIRST
        ============================================================ */
        .results-card {
            background: white;
            border-radius: 24px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            overflow: hidden;
        }

        /* Student Header - Mobile First */
        .student-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px 16px;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            gap: 12px;
        }

        .student-header .student-info-wrap {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 12px;
            width: 100%;
        }

        .student-header .student-avatar-lg {
            width: 70px;
            height: 70px;
            border-radius: 50%;
            overflow: hidden;
            border: 3px solid rgba(255, 255, 255, 0.4);
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(255, 255, 255, 0.2);
            font-size: 30px;
            font-weight: bold;
        }

        .student-header .student-avatar-lg img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .student-header .student-details h3 {
            margin: 0;
            font-size: 20px;
            font-weight: 600;
        }

        .student-header .student-details p {
            margin: 4px 0 0;
            opacity: 0.9;
            font-size: 13px;
        }

        .student-header .student-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 8px 16px;
            justify-content: center;
            font-size: 12px;
            opacity: 0.85;
        }

        .student-header .student-meta span {
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        /* Results Table Wrapper - Mobile First */
        .results-table-wrapper {
            overflow-x: auto;
            padding: 12px 8px;
            -webkit-overflow-scrolling: touch;
        }

        .results-table {
            width: 100%;
            min-width: 600px;
            border-collapse: collapse;
            font-size: 12px;
        }

        .results-table th,
        .results-table td {
            padding: 8px 4px;
            text-align: center;
            border-bottom: 1px solid #eee;
            white-space: nowrap;
        }

        .results-table th {
            background: #f8f9fa;
            font-weight: 700;
            color: #1B3058;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .results-table th small {
            font-weight: 400;
            opacity: 0.7;
        }

        .results-table td {
            font-size: 12px;
        }

        .subject-name {
            text-align: left;
            font-weight: 600;
            color: #333;
            min-width: 80px;
            white-space: normal;
        }

        .progress-bar-container {
            width: 50px;
            height: 5px;
            background: #e0e0e0;
            border-radius: 10px;
            overflow: hidden;
            margin: 0 auto 3px;
        }

        .progress-bar-fill {
            height: 100%;
            border-radius: 10px;
            transition: width 0.5s ease;
        }

        .grade-badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 20px;
            font-weight: 700;
            font-size: 10px;
            min-width: 28px;
        }

        /* ============================================================
        OVERALL STATS - MOBILE FIRST
        ============================================================ */
        .overall-stats {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            padding: 16px;
            background: #f8f9fa;
            border-top: 1px solid #eee;
        }

        .stat-card {
            text-align: center;
            padding: 12px 8px;
            background: white;
            border-radius: 14px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }

        .stat-card .label {
            font-size: 9px;
            color: #888;
            margin-bottom: 3px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .stat-card .value {
            font-size: 20px;
            font-weight: 700;
            color: #1B3058;
        }

        .stat-card .sub {
            font-size: 10px;
            color: #666;
            margin-top: 3px;
        }

        .stat-card .progress-bar-container {
            width: 80%;
            margin: 6px auto 0;
            height: 4px;
        }

        .stat-card .grade-badge {
            font-size: 16px;
            padding: 4px 14px;
        }

        /* ============================================================
        BUTTONS - MOBILE FIRST
        ============================================================ */
        .print-btn {
            background: #28a745;
            color: white;
            border: none;
            padding: 10px 18px;
            border-radius: 12px;
            font-weight: 600;
            font-size: 13px;
            cursor: pointer;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            margin: 12px 16px 16px auto;
            width: 100%;
            justify-content: center;
        }

        .print-btn:hover {
            background: #218838;
            transform: translateY(-2px);
        }

        .print-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        .bulk-btn {
            background: #1B3058;
            color: white;
            border: none;
            padding: 10px 16px;
            border-radius: 12px;
            font-weight: 600;
            font-size: 13px;
            cursor: pointer;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            width: 100%;
            justify-content: center;
        }

        .bulk-btn:hover {
            background: #16305f;
            transform: translateY(-2px);
        }

        .bulk-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        .bulk-status {
            display: none;
            margin: 12px 0 0;
            padding: 12px;
            border-radius: 12px;
            background: #f1f5fb;
            border: 1px solid #d7e2f3;
            color: #1B3058;
            font-size: 13px;
        }

        .bulk-status .bulk-line {
            font-size: 13px;
            margin-bottom: 8px;
        }

        .bulk-progress-bar {
            height: 8px;
            width: 100%;
            background: #d9e3f2;
            border-radius: 999px;
            overflow: hidden;
        }

        .bulk-progress-fill {
            height: 100%;
            width: 0%;
            background: linear-gradient(90deg, #1B3058, #2a4f8e);
            transition: width 0.25s ease;
        }

        .bulk-download-link {
            display: inline-block;
            margin-top: 8px;
            font-weight: 600;
            color: #0b5ed7;
            text-decoration: none;
        }

        .bulk-download-link:hover {
            text-decoration: underline;
        }

        .btn-group-mobile {
            display: flex;
            flex-direction: column;
            gap: 8px;
            padding: 0 16px 12px;
        }

        .btn-group-mobile .print-btn,
        .btn-group-mobile .bulk-btn {
            width: 100%;
            margin: 0;
        }

        .spinner {
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
        EMPTY STATE / ALERTS
        ============================================================ */
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #999;
        }

        .empty-state i {
            font-size: 48px;
            color: #ddd;
            margin-bottom: 12px;
            display: block;
        }

        .empty-state h3 {
            font-size: 16px;
            margin-bottom: 6px;
            color: #666;
        }

        .empty-state p {
            font-size: 13px;
            color: #999;
        }

        .alert {
            padding: 12px 16px;
            border-radius: 16px;
            margin-bottom: 20px;
            font-size: 13px;
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

        /* ============================================================
        RESPONSIVE - TABLET (768px+)
        ============================================================ */
        @media (min-width: 768px) {
            .results-container {
                padding: 25px;
            }

            .student-header {
                flex-direction: row;
                padding: 25px 30px;
                text-align: left;
                justify-content: space-between;
                flex-wrap: wrap;
            }

            .student-header .student-info-wrap {
                flex-direction: row;
                align-items: center;
                gap: 18px;
                width: auto;
            }

            .student-header .student-details h3 {
                font-size: 24px;
            }

            .student-header .student-meta {
                justify-content: flex-start;
            }

            .overall-stats {
                grid-template-columns: repeat(4, 1fr);
                gap: 15px;
                padding: 20px 25px;
            }

            .stat-card .value {
                font-size: 24px;
            }

            .print-btn {
                width: auto;
                padding: 12px 25px;
                margin: 0 25px 20px auto;
                font-size: 14px;
            }

            .bulk-btn {
                width: auto;
                padding: 12px 20px;
                font-size: 14px;
            }

            .btn-group-mobile {
                flex-direction: row;
                justify-content: flex-end;
                padding: 0 20px 16px;
                gap: 12px;
            }

            .btn-group-mobile .print-btn,
            .btn-group-mobile .bulk-btn {
                width: auto;
                margin: 0;
            }

            .results-table-wrapper {
                padding: 16px 20px;
            }

            .results-table {
                font-size: 13px;
                min-width: auto;
            }

            .results-table th,
            .results-table td {
                padding: 10px 12px;
            }

            .results-table th {
                font-size: 11px;
            }

            .results-table td {
                font-size: 13px;
            }

            .subject-name {
                min-width: 120px;
            }

            .progress-bar-container {
                width: 70px;
                height: 6px;
            }

            .grade-badge {
                font-size: 12px;
                padding: 4px 12px;
            }

            .filter-row {
                flex-wrap: nowrap;
            }
        }

        /* ============================================================
        RESPONSIVE - DESKTOP (1024px+)
        ============================================================ */
        @media (min-width: 1024px) {
            .results-container {
                padding: 30px;
            }

            .student-header {
                padding: 30px 40px;
            }

            .student-header .student-avatar-lg {
                width: 80px;
                height: 80px;
            }

            .student-header .student-details h3 {
                font-size: 28px;
            }

            .overall-stats {
                padding: 25px 40px;
                gap: 20px;
            }

            .stat-card {
                padding: 16px 20px;
            }

            .stat-card .value {
                font-size: 28px;
            }

            .results-table-wrapper {
                padding: 20px 30px;
            }

            .results-table th,
            .results-table td {
                padding: 12px 16px;
            }
        }

        /* ============================================================
        RESPONSIVE - SMALL MOBILE (480px-)
        ============================================================ */
        @media (max-width: 480px) {
            .results-container {
                padding: 10px;
            }

            .filter-bar {
                padding: 14px 16px;
                border-radius: 16px;
            }

            .filter-group {
                min-width: 100%;
            }

            .filter-select {
                font-size: 13px;
                padding: 10px 12px;
            }

            .assessment-checkboxes {
                gap: 10px;
            }

            .assessment-checkbox span {
                font-size: 12px;
            }

            .apply-btn {
                width: 100%;
                justify-content: center;
                font-size: 13px;
                padding: 12px;
            }

            .student-header {
                padding: 16px 12px;
            }

            .student-header .student-avatar-lg {
                width: 56px;
                height: 56px;
                font-size: 22px;
            }

            .student-header .student-details h3 {
                font-size: 17px;
            }

            .student-header .student-details p {
                font-size: 12px;
            }

            .student-header .student-meta {
                font-size: 11px;
                gap: 6px 12px;
            }

            .results-table-wrapper {
                padding: 8px 4px;
            }

            .results-table {
                font-size: 10px;
                min-width: 480px;
            }

            .results-table th,
            .results-table td {
                padding: 5px 3px;
            }

            .results-table th {
                font-size: 8px;
            }

            .results-table td {
                font-size: 10px;
            }

            .subject-name {
                min-width: 60px;
                font-size: 10px;
            }

            .progress-bar-container {
                width: 30px;
                height: 4px;
            }

            .grade-badge {
                font-size: 8px;
                padding: 2px 5px;
                min-width: 20px;
            }

            .overall-stats {
                grid-template-columns: 1fr 1fr;
                gap: 6px;
                padding: 10px 8px;
            }

            .stat-card {
                padding: 8px 4px;
                border-radius: 10px;
            }

            .stat-card .label {
                font-size: 8px;
            }

            .stat-card .value {
                font-size: 16px;
            }

            .stat-card .sub {
                font-size: 8px;
            }

            .stat-card .grade-badge {
                font-size: 13px;
                padding: 3px 10px;
            }

            .students-panel {
                min-width: 100%;
            }

            .results-panel {
                min-width: 100%;
            }

            .student-card {
                padding: 10px 14px;
            }

            .student-avatar {
                width: 40px;
                height: 40px;
                font-size: 14px;
            }

            .student-info h4 {
                font-size: 13px;
            }

            .student-info p {
                font-size: 10px;
            }

            .btn-group-mobile {
                padding: 0 12px 12px;
                gap: 6px;
            }

            .btn-group-mobile .print-btn,
            .btn-group-mobile .bulk-btn {
                font-size: 12px;
                padding: 8px 14px;
            }

            .bulk-status {
                font-size: 12px;
                padding: 10px;
            }

            .bulk-status .bulk-line {
                font-size: 12px;
            }
        }

        /* ============================================================
        PRINT STYLES
        ============================================================ */
        @media print {

            .filter-bar,
            .students-panel,
            .print-btn,
            .sidebar,
            .header,
            .bulk-btn,
            .bulk-status,
            .btn-group-mobile {
                display: none !important;
            }

            .results-panel {
                margin: 0;
                padding: 0;
                width: 100%;
                min-width: 100%;
            }

            .results-container {
                padding: 0;
            }

            body {
                background: white;
            }

            .results-card {
                box-shadow: none;
                border: 1px solid #ddd;
            }

            .student-header {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .grade-badge {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .progress-bar-fill {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .overall-stats {
                break-inside: avoid;
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
                <div class="results-container">

                    <!-- Page Header -->
                    <div class="page-header">
                        <h2><i class="fa fa-graduation-cap"></i> <?= htmlspecialchars($PageTitle) ?></h2>
                        <p>Select session, term, class, and assessments, then click a student to view results</p>
                    </div>

                    <?= showMessage($stat) ?>

                    <!-- Filter Bar -->
                    <div class="filter-bar">
                        <form method="GET" action="" id="filterForm">
                            <div class="filter-row">
                                <div class="filter-group">
                                    <label><i class="fa fa-calendar"></i> SESSION</label>
                                    <select name="session" class="filter-select" onchange="this.form.submit()">
                                        <option value="">-- Select Session --</option>
                                        <?php foreach ($sessions as $s): ?>
                                            <option value="<?= $s['id'] ?>" <?= ($selectedSession == $s['id']) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($s['session']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="filter-group">
                                    <label><i class="fa fa-tag"></i> TERM</label>
                                    <select name="term_id" class="filter-select" onchange="this.form.submit()">
                                        <option value="">-- Select Term --</option>
                                        <?php foreach ($terms as $t): ?>
                                            <option value="<?= $t['id'] ?>" <?= ($selectedTerm == $t['id']) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($t['term']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="filter-group">
                                    <label><i class="fa fa-building"></i> CLASS</label>
                                    <select name="class_id" class="filter-select" onchange="this.form.submit()">
                                        <option value="">-- Select Class --</option>
                                        <?php foreach ($classes as $c): ?>
                                            <option value="<?= $c['id'] ?>" <?= ($selectedClass == $c['id']) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($c['name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <!-- Assessment Selection -->
                            <?php if (!empty($selectedClass) && !empty($assessments)): ?>
                                <div class="assessment-group">
                                    <label class="assessment-label"><i class="fa fa-check-square-o"></i> SELECT ASSESSMENTS TO INCLUDE</label>
                                    <div class="assessment-checkboxes">
                                        <?php
                                        $selectedAssArray = explode('-', $selectedAssessments);
                                        foreach ($assessments as $ass):
                                            $isChecked = in_array($ass['id'], $selectedAssArray);
                                        ?>
                                            <label class="assessment-checkbox">
                                                <input type="checkbox" name="assesments_checkbox[]" value="<?= $ass['id'] ?>" <?= $isChecked ? 'checked' : '' ?> onchange="updateAssessments()">
                                                <span><?= htmlspecialchars($ass['assesment']) ?> (<?= $ass['percentage'] ?? 100 ?>%)</span>
                                            </label>
                                        <?php endforeach; ?>
                                    </div>
                                    <input type="hidden" name="assesments" id="assesmentsHidden" value="<?= htmlspecialchars($selectedAssessments) ?>">
                                    <button type="button" class="apply-btn" onclick="updateAndSubmit()"><i class="fa fa-check"></i> Apply Assessments</button>
                                </div>
                            <?php endif; ?>

                            <input type="hidden" name="student_id" id="selectedStudentInput" value="<?= htmlspecialchars($selectedStudentId) ?>">
                        </form>

                        <!-- Bulk Download Button -->
                        <?php if (!empty($selectedSession) && !empty($selectedTerm) && !empty($selectedClass) && !empty($selectedAssessments)): ?>
                            <div style="margin-top: 12px;">
                                <button type="button" class="bulk-btn" id="bulkPdfBtn" onclick="startBulkDownload()">
                                    <i class="fa fa-files-o"></i> Bulk Download Class Results (ZIP)
                                </button>
                                <div class="bulk-status" id="bulkStatusBox">
                                    <div class="bulk-line" id="bulkStatusText">Preparing export...</div>
                                    <div class="bulk-progress-bar">
                                        <div class="bulk-progress-fill" id="bulkProgressFill"></div>
                                    </div>
                                    <a href="#" target="_blank" id="bulkDownloadLink" class="bulk-download-link" style="display:none;">Download ZIP</a>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="two-column-layout">
                        <!-- LEFT: Student Cards with Search -->
                        <div class="students-panel">
                            <div class="panel-header">
                                <i class="fa fa-users"></i> Students
                            </div>
                            <div class="search-box">
                                <input type="text" id="studentSearch" class="search-input" placeholder="🔍 Search by name or ID...">
                            </div>
                            <div class="students-list" id="studentsList">
                                <?php if (!empty($allStudents)): ?>
                                    <?php foreach ($allStudents as $student):
                                        $photoPath = getStudentPhoto($student['picture'] ?? '');
                                    ?>
                                        <a href="?session=<?= urlencode($selectedSession) ?>&term_id=<?= urlencode($selectedTerm) ?>&class_id=<?= urlencode($selectedClass) ?>&student_id=<?= urlencode($student['student_id']) ?>&assesments=<?= urlencode($selectedAssessments) ?>"
                                            class="student-card <?= ($selectedStudentId == $student['student_id']) ? 'active' : '' ?>"
                                            data-name="<?= strtolower(($student['first_name'] ?? '') . ' ' . ($student['last_name'] ?? '')) ?>"
                                            data-id="<?= strtolower($student['student_id'] ?? '') ?>">
                                            <div class="student-avatar">
                                                <?php if (!empty($photoPath)): ?>
                                                    <img src="<?= $photoPath ?>" alt="<?= htmlspecialchars($student['first_name'] ?? '') ?>">
                                                <?php else: ?>
                                                    <?= strtoupper(substr($student['first_name'] ?? '?', 0, 1)) ?>
                                                <?php endif; ?>
                                            </div>
                                            <div class="student-info">
                                                <h4><?= htmlspecialchars(($student['first_name'] ?? '') . ' ' . ($student['last_name'] ?? '')) ?></h4>
                                                <p><i class="fa fa-id-card"></i> <?= htmlspecialchars($student['student_id'] ?? '') ?> | <i class="fa fa-building"></i> <?= htmlspecialchars($student['class_name'] ?? 'N/A') ?></p>
                                            </div>
                                        </a>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="empty-state">
                                        <i class="fa fa-user-slash"></i>
                                        <h3>No Students Found</h3>
                                        <p>Please select a class to see students.</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- RIGHT: Results Display -->
                        <div class="results-panel">
                            <?php if (empty($selectedSession)): ?>
                                <div class="alert alert-info">Please select a session to view results.</div>
                            <?php elseif (empty($selectedTerm)): ?>
                                <div class="alert alert-info">Please select a term to view results.</div>
                            <?php elseif (empty($selectedClass)): ?>
                                <div class="alert alert-info">Please select a class to view results.</div>
                            <?php elseif (empty($selectedAssessments) && !empty($assessments)): ?>
                                <div class="alert alert-warning">Please select at least one assessment above.</div>
                            <?php elseif (empty($selectedStudentId)): ?>
                                <div class="alert alert-info">Please click on a student card to view their results.</div>
                            <?php elseif (!empty($selectedStudent) && !empty($resultsData)): ?>

                                <div class="results-card">
                                    <!-- Student Header -->
                                    <div class="student-header">
                                        <div class="student-info-wrap">
                                            <div class="student-avatar-lg">
                                                <?php
                                                $studentPhoto = getStudentPhoto($selectedStudent['picture'] ?? '');
                                                if (!empty($studentPhoto)): ?>
                                                    <img src="<?= $studentPhoto ?>" alt="<?= htmlspecialchars($selectedStudent['first_name'] ?? '') ?>">
                                                <?php else: ?>
                                                    <?= strtoupper(substr($selectedStudent['first_name'] ?? '?', 0, 1)) ?>
                                                <?php endif; ?>
                                            </div>
                                            <div class="student-details">
                                                <h3><?= htmlspecialchars(($selectedStudent['first_name'] ?? '') . ' ' . ($selectedStudent['last_name'] ?? '')) ?></h3>
                                                <p><i class="fa fa-id-card"></i> <?= htmlspecialchars($selectedStudent['student_id'] ?? '') ?></p>
                                                <div class="student-meta">
                                                    <span><i class="fa fa-building"></i> <?= htmlspecialchars($selectedStudent['class_name'] ?? 'N/A') ?></span>
                                                    <span><i class="fa fa-calendar"></i> <?= htmlspecialchars($selectedStudent['session'] ?? '') ?></span>
                                                    <span><i class="fa fa-tag"></i> <?= htmlspecialchars($selectedStudent['term_id'] ?? '') ?></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Results Table -->
                                    <div class="results-table-wrapper">
                                        <table class="results-table">
                                            <thead>
                                                <tr>
                                                    <th>Subject</th>
                                                    <?php
                                                    $assessmentIdArray = explode('-', $selectedAssessments);
                                                    foreach ($assessments as $assessment):
                                                        if (in_array($assessment['id'], $assessmentIdArray)):
                                                    ?>
                                                            <th><?= htmlspecialchars($assessment['assesment']) ?><br><small>(<?= $assessment['percentage'] ?? 100 ?>%)</small></th>
                                                    <?php
                                                        endif;
                                                    endforeach;
                                                    ?>
                                                    <th>Total</th>
                                                    <th>%</th>
                                                    <th>Grade</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($resultsData as $data): ?>
                                                    <tr>
                                                        <td class="subject-name"><?= htmlspecialchars($data['subject']) ?></td>
                                                        <?php foreach ($assessmentIdArray as $assId): ?>
                                                            <td><?= $data['scores'][$assId] ?? 0 ?></td>
                                                        <?php endforeach; ?>
                                                        <td><strong><?= $data['total'] ?></strong></td>
                                                        <td>
                                                            <div class="progress-bar-container">
                                                                <div class="progress-bar-fill" style="width: <?= $data['percentage'] ?>%; background: <?= getGradeColor($data['percentage'], $gradeScale) ?>;"></div>
                                                            </div>
                                                            <small><?= $data['percentage'] ?>%</small>
                                                        </td>
                                                        <td>
                                                            <span class="grade-badge" style="background: <?= getGradeColor($data['percentage'], $gradeScale) ?>20; color: <?= getGradeColor($data['percentage'], $gradeScale) ?>;">
                                                                <?= $data['grade'] ?: '--' ?>
                                                            </span>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>

                                    <!-- Overall Stats -->
                                    <div class="overall-stats">
                                        <div class="stat-card">
                                            <div class="label">Subjects</div>
                                            <div class="value"><?= $subjectCount ?></div>
                                        </div>
                                        <div class="stat-card">
                                            <div class="label">Total Score</div>
                                            <div class="value"><?= $overallTotal ?></div>
                                            <div class="sub">of <?= $overallMaxTotal ?></div>
                                        </div>
                                        <div class="stat-card">
                                            <div class="label">Percentage</div>
                                            <div class="value"><?= $overallPercentage ?>%</div>
                                            <div class="progress-bar-container">
                                                <div class="progress-bar-fill" style="width: <?= $overallPercentage ?>%; background: <?= getGradeColor($overallPercentage, $gradeScale) ?>;"></div>
                                            </div>
                                        </div>
                                        <div class="stat-card">
                                            <div class="label">Grade</div>
                                            <div class="value">
                                                <span class="grade-badge" style="background: <?= getGradeColor($overallPercentage, $gradeScale) ?>20; color: <?= getGradeColor($overallPercentage, $gradeScale) ?>; font-size: 20px;">
                                                    <?= $overallGrade ?: '--' ?>
                                                </span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Action Buttons -->
                                    <div class="btn-group-mobile">
                                        <button class="print-btn" id="printPdfBtn" onclick="openPDF()">
                                            <i class="fa fa-file-pdf-o"></i> Print Report Sheet
                                        </button>
                                    </div>
                                </div>

                            <?php elseif (!empty($selectedStudentId)): ?>
                                <div class="results-card">
                                    <div class="empty-state">
                                        <i class="fa fa-file-text-o"></i>
                                        <h3>No Results Found</h3>
                                        <p>No scores have been entered for this student.</p>
                                        <small>Please ensure scores have been entered for the selected session, term, class, and assessments.</small>
                                    </div>
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
    <script>
        // ============================================================================
        // FILTER FUNCTIONS
        // ============================================================================
        function updateAssessments() {
            var checkboxes = document.querySelectorAll('input[name="assesments_checkbox[]"]:checked');
            var values = [];
            checkboxes.forEach(function(cb) {
                values.push(cb.value);
            });
            document.getElementById('assesmentsHidden').value = values.join('-');
        }

        function updateAndSubmit() {
            updateAssessments();
            document.getElementById('filterForm').submit();
        }

        // ============================================================================
        // PDF GENERATION
        // ============================================================================
        function openPDF() {
            var btn = document.getElementById('printPdfBtn');
            if (!btn) return;

            btn.innerHTML = '<span class="spinner"></span> Generating...';
            btn.disabled = true;

            var sessionId = '<?= $selectedSession ?>';
            var termId = '<?= $selectedTerm ?>';
            var classId = '<?= $selectedClass ?>';
            var studentId = '<?= $selectedStudentId ?>';
            var studentRandomId = '<?= $studentRandomId ?>';
            var assessments = document.getElementById('assesmentsHidden') ? document.getElementById('assesmentsHidden').value : '<?= htmlspecialchars($selectedAssessments, ENT_QUOTES) ?>';

            var pdfUrl = 'skool_term_result_pdf.php?';
            pdfUrl += 'randomid=' + encodeURIComponent(studentRandomId);
            pdfUrl += '&student_id=' + encodeURIComponent(studentId);
            pdfUrl += '&session=' + encodeURIComponent(sessionId);
            pdfUrl += '&term_id=' + encodeURIComponent(termId);
            pdfUrl += '&class_id=' + encodeURIComponent(classId);
            pdfUrl += '&assesments=' + encodeURIComponent(assessments);
            pdfUrl += '&paper_mode=legacy_auto';

            window.open(pdfUrl, '_blank');

            setTimeout(function() {
                btn.innerHTML = '<i class="fa fa-file-pdf-o"></i> Print Report Sheet';
                btn.disabled = false;
            }, 1000);
        }

        // ============================================================================
        // BULK DOWNLOAD FUNCTIONALITY
        // ============================================================================
        var bulkJobId = '';
        var bulkIsRunning = false;
        var bulkChunkSize = 5;

        function getBulkChunkSize(totalStudents) {
            var total = parseInt(totalStudents || 0, 10);
            if (isNaN(total) || total <= 0) return 5;
            if (total > 40) return 3;
            if (total > 25) return 4;
            if (total > 15) return 5;
            return 8;
        }

        function setBulkStatus(message, progress, downloadUrl) {
            var box = document.getElementById('bulkStatusBox');
            var text = document.getElementById('bulkStatusText');
            var fill = document.getElementById('bulkProgressFill');
            var link = document.getElementById('bulkDownloadLink');

            if (!box || !text || !fill || !link) return;

            box.style.display = 'block';
            text.textContent = message || 'Processing...';

            var p = parseFloat(progress || 0);
            if (isNaN(p) || p < 0) p = 0;
            if (p > 100) p = 100;
            fill.style.width = p + '%';

            if (downloadUrl) {
                link.style.display = 'inline-block';
                link.href = downloadUrl;
                link.textContent = 'Download ZIP';
            } else {
                link.style.display = 'none';
                link.href = '#';
            }
        }

        function setBulkButtonState(running, label) {
            var btn = document.getElementById('bulkPdfBtn');
            if (!btn) return;
            btn.disabled = !!running;
            btn.innerHTML = label || '<i class="fa fa-files-o"></i> Bulk Download Class Results (ZIP)';
        }

        function startBulkDownload() {
            if (bulkIsRunning) return;

            // Update assessments before starting
            updateAssessments();

            var sessionId = '<?= (int)$selectedSession ?>';
            var termId = '<?= (int)$selectedTerm ?>';
            var classId = '<?= (int)$selectedClass ?>';
            var assessments = document.getElementById('assesmentsHidden') ? document.getElementById('assesmentsHidden').value : '<?= htmlspecialchars($selectedAssessments, ENT_QUOTES) ?>';

            if (!sessionId || !termId || !classId || !assessments) {
                alert('Please select session, term, class, and assessments before bulk export.');
                return;
            }

            bulkIsRunning = true;
            setBulkButtonState(true, '<span class="spinner"></span> Starting Bulk Export...');
            setBulkStatus('Starting job...', 0, '');

            var payload = new URLSearchParams();
            payload.append('session', sessionId);
            payload.append('term_id', termId);
            payload.append('class_id', classId);
            payload.append('assesments', assessments);
            payload.append('create_by_userid', '<?= $create_by_userid ?>');

            fetch('bulk_result_export.php?action=start', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
                    },
                    body: payload.toString()
                })
                .then(function(res) {
                    if (!res.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return res.json();
                })
                .then(function(data) {
                    if (!data || !data.ok) {
                        throw new Error((data && data.message) ? data.message : 'Unable to start bulk export');
                    }
                    bulkJobId = data.job_id || '';
                    bulkChunkSize = getBulkChunkSize(data.total_students || 0);
                    setBulkStatus('Job created. Processing students in batches of ' + bulkChunkSize + '...', 0, '');
                    runBulkChunk();
                })
                .catch(function(err) {
                    bulkIsRunning = false;
                    setBulkButtonState(false);
                    setBulkStatus('Failed to start: ' + (err && err.message ? err.message : 'Unknown error'), 0, '');
                    console.error('Bulk download error:', err);
                });
        }

        function runBulkChunk() {
            if (!bulkJobId) {
                bulkIsRunning = false;
                setBulkButtonState(false);
                return;
            }

            var payload = new URLSearchParams();
            payload.append('job_id', bulkJobId);
            payload.append('chunk', String(bulkChunkSize || 5));
            payload.append('create_by_userid', '<?= $create_by_userid ?>');

            fetch('bulk_result_export.php?action=process', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
                    },
                    body: payload.toString()
                })
                .then(function(res) {
                    if (!res.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return res.json();
                })
                .then(function(data) {
                    if (!data || !data.ok) {
                        throw new Error((data && data.message) ? data.message : 'Bulk processing failed');
                    }

                    var status = data.status || 'processing';
                    var total = parseInt(data.total_students || 0, 10);
                    var processed = parseInt(data.processed_students || 0, 10);
                    var failed = parseInt(data.failed_students || 0, 10);
                    var progress = parseFloat(data.progress || 0);

                    if (status === 'completed') {
                        bulkIsRunning = false;
                        setBulkButtonState(false);
                        setBulkStatus('Completed: ' + processed + '/' + total + ' processed, failed: ' + failed, 100, data.download_url || '');
                        return;
                    }

                    if (status === 'failed') {
                        bulkIsRunning = false;
                        setBulkButtonState(false);
                        setBulkStatus('Bulk export failed. ' + (data.message || ''), progress, '');
                        return;
                    }

                    setBulkStatus('Processing: ' + processed + '/' + total + ' students, failed: ' + failed, progress, '');
                    setBulkButtonState(true, '<span class="spinner"></span> Processing Bulk Export...');
                    setTimeout(runBulkChunk, 250);
                })
                .catch(function(err) {
                    bulkIsRunning = false;
                    setBulkButtonState(false);
                    setBulkStatus('Processing error: ' + (err && err.message ? err.message : 'Unknown error'), 0, '');
                    console.error('Bulk processing error:', err);
                });
        }

        // ============================================================================
        // SEARCH FUNCTIONALITY
        // ============================================================================
        var searchInput = document.getElementById('studentSearch');
        if (searchInput) {
            searchInput.addEventListener('keyup', function() {
                var searchTerm = this.value.toLowerCase();
                var studentCards = document.querySelectorAll('#studentsList .student-card');
                studentCards.forEach(function(card) {
                    var name = card.getAttribute('data-name') || '';
                    var id = card.getAttribute('data-id') || '';
                    card.style.display = (name.includes(searchTerm) || id.includes(searchTerm)) ? 'flex' : 'none';
                });
            });
        }

        // ============================================================================
        // DOM READY - SCROLL TO ACTIVE STUDENT
        // ============================================================================
        document.addEventListener('DOMContentLoaded', function() {
            var activeCard = document.querySelector('#studentsList .student-card.active');
            if (activeCard) {
                activeCard.scrollIntoView({
                    behavior: 'auto',
                    block: 'center'
                });
            }
        });
    </script>
</body>

</html>