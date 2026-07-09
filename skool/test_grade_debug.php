<?php
/**
 * ============================================================================
 * TEST FILE - Grade Debugging for JOS/0003/2026
 * ============================================================================
 */

require_once('../config.php');
require_once('inc.session-create.php');

$create_by_userid = $_SESSION['userid'] ?? 463;

echo "<h1>🔍 Grade Debug Test - JOS/0003/2026</h1>";
echo "<hr>";

// ============================================================================
// 1. GET STUDENT DATA
// ============================================================================
$studentAlphanumeric = 'JOS/0003/2026';

echo "<h2>1. Student Record</h2>";
$student = db_get_row(
    "SELECT * FROM manage_student WHERE student_id = ? AND create_by_userid = ?",
    [$studentAlphanumeric, $create_by_userid]
);

if (!$student) {
    echo "❌ Student NOT found!";
    exit;
}

$studentNumericId = $student['id'];
echo "✅ Student found!<br>";
echo "Numeric ID: " . $studentNumericId . "<br>";
echo "Name: " . $student['first_name'] . " " . $student['last_name'] . "<br>";
echo "Class ID: " . $student['class'] . "<br>";
echo "Session: " . $student['session'] . "<br>";
echo "Term ID: " . $student['term_id'] . "<br>";

echo "<hr>";

// ============================================================================
// 2. GET SUBJECTS FOR THIS CLASS
// ============================================================================
echo "<h2>2. Subjects</h2>";
$subjects = db_get_rows(
    "SELECT * FROM school_subject WHERE class_id = ? AND create_by_userid = ?",
    [$student['class'], $create_by_userid]
);

foreach ($subjects as $subject) {
    echo $subject['subject'] . " (ID: " . $subject['id'] . ")<br>";
}

echo "<hr>";

// ============================================================================
// 3. GET SCORES FOR EACH SUBJECT
// ============================================================================
echo "<h2>3. Scores for Each Subject</h2>";

$assessmentIds = [796, 797, 798];
$maxTotalPerSubject = 0;

// Get max total per subject from assessments
$assessments = db_get_rows(
    "SELECT * FROM school_assessment WHERE id IN (" . implode(',', $assessmentIds) . ") AND create_by_userid = ?",
    [$create_by_userid]
);
foreach ($assessments as $ass) {
    $maxTotalPerSubject += floatval($ass['percentage'] ?? 100);
}

echo "Max Total Per Subject: " . $maxTotalPerSubject . "<br><br>";

echo "<table border='1' cellpadding='5'>";
echo "<tr><th>Subject</th><th>CA1 (796)</th><th>CA2 (797)</th><th>Exam (798)</th><th>Total</th><th>Percentage</th><th>Grade</th></tr>";

foreach ($subjects as $subject) {
    $subjectTotal = 0;
    $subjectScores = [];
    
    foreach ($assessmentIds as $assessmentId) {
        $score = db_get_val(
            "SELECT score FROM input_score_class_teacher 
             WHERE student_id = ? AND subject_id = ? AND assesment_id = ? 
             AND session_id = ? AND term_id = ? AND class_id = ? 
             AND create_by_userid = ?",
            [$studentNumericId, $subject['id'], $assessmentId, 
             $student['session'], $student['term_id'], $student['class'], $create_by_userid]
        );
        $score = $score !== false ? floatval($score) : 0;
        $subjectScores[$assessmentId] = $score;
        $subjectTotal += $score;
    }
    
    // Calculate percentage
    $percentage = $maxTotalPerSubject > 0 ? round(($subjectTotal / $maxTotalPerSubject) * 100) : 0;
    
    // Get grade
    $grade = '';
    if ($percentage > 0) {
        $gradeData = db_get_row(
            "SELECT grade FROM school_grade 
             WHERE create_by_userid = ? 
             AND minimum_number <= ? 
             AND maximum_number >= ? 
             ORDER BY minimum_number DESC 
             LIMIT 1",
            [$create_by_userid, $percentage, $percentage]
        );
        $grade = $gradeData['grade'] ?? ($percentage >= 70 ? 'A' : ($percentage >= 60 ? 'B' : ($percentage >= 50 ? 'C' : ($percentage >= 45 ? 'D' : ($percentage >= 40 ? 'E' : 'F')))));
    }
    
    echo "<tr>";
    echo "<td>" . $subject['subject'] . "</td>";
    echo "<td>" . ($subjectScores[796] ?? 0) . "</td>";
    echo "<td>" . ($subjectScores[797] ?? 0) . "</td>";
    echo "<td>" . ($subjectScores[798] ?? 0) . "</td>";
    echo "<td><strong>" . $subjectTotal . "</strong></td>";
    echo "<td>" . $percentage . "%</td>";
    echo "<td><strong style='color:" . ($grade == 'A' ? 'green' : ($grade == 'F' ? 'red' : 'orange')) . ";'>" . ($grade ?: '--') . "</strong></td>";
    echo "</tr>";
}

echo "</table>";

echo "<hr>";

// ============================================================================
// 4. OVERALL TOTALS
// ============================================================================
echo "<h2>4. Overall Totals</h2>";

$totalScore = 0;
foreach ($subjects as $subject) {
    $subjectTotal = 0;
    foreach ($assessmentIds as $assessmentId) {
        $score = db_get_val(
            "SELECT score FROM input_score_class_teacher 
             WHERE student_id = ? AND subject_id = ? AND assesment_id = ? 
             AND session_id = ? AND term_id = ? AND class_id = ? 
             AND create_by_userid = ?",
            [$studentNumericId, $subject['id'], $assessmentId, 
             $student['session'], $student['term_id'], $student['class'], $create_by_userid]
        );
        $subjectTotal += $score !== false ? floatval($score) : 0;
    }
    $totalScore += $subjectTotal;
}

$maxTotal = count($subjects) * $maxTotalPerSubject;
$overallPercentage = $maxTotal > 0 ? round(($totalScore / $maxTotal) * 100) : 0;

echo "Total Score: " . $totalScore . "<br>";
echo "Max Possible: " . $maxTotal . "<br>";
echo "Overall Percentage: " . $overallPercentage . "%<br>";

// Get overall grade
$overallGrade = '';
if ($overallPercentage > 0) {
    $gradeData = db_get_row(
        "SELECT grade FROM school_grade 
         WHERE create_by_userid = ? 
         AND minimum_number <= ? 
         AND maximum_number >= ? 
         ORDER BY minimum_number DESC 
         LIMIT 1",
        [$create_by_userid, $overallPercentage, $overallPercentage]
    );
    $overallGrade = $gradeData['grade'] ?? ($overallPercentage >= 70 ? 'A' : ($overallPercentage >= 60 ? 'B' : ($overallPercentage >= 50 ? 'C' : ($overallPercentage >= 45 ? 'D' : ($overallPercentage >= 40 ? 'E' : 'F')))));
}

echo "Overall Grade: <strong style='color:" . ($overallGrade == 'A' ? 'green' : ($overallGrade == 'F' ? 'red' : 'orange')) . ";'>" . ($overallGrade ?: '--') . "</strong><br>";

echo "<hr>";

// ============================================================================
// 5. SUMMARY
// ============================================================================
echo "<h2>5. Summary</h2>";

if ($totalScore > 0 && $overallPercentage > 0 && $overallGrade == 'A') {
    echo "✅ All checks PASSED!<br>";
    echo "Student JOS/0003/2026 should show grade A.<br>";
    echo "If the page shows F, the bug is in the page's code logic.";
} elseif ($totalScore > 0 && $overallPercentage > 0 && $overallGrade != 'A') {
    echo "⚠️ Grade lookup returned: " . $overallGrade . "<br>";
    echo "Check your school_grade table for percentage " . $overallPercentage;
} else {
    echo "❌ No scores found!";
}
echo "<hr>";
?>