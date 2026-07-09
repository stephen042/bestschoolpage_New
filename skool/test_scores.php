<?php
require_once('../config.php');
require_once('inc.session-create.php');

echo "<h2>Test Score Save</h2>";

// Get the student ID
$student = db_get_row("SELECT * FROM manage_student WHERE first_name = 'Jose'");
echo "<p>Student ID: " . ($student['id'] ?? 'Not found') . "</p>";

// Get subject ID
$subject = db_get_row("SELECT * FROM school_subject WHERE subject = 'Social Studies'");
echo "<p>Subject ID: " . ($subject['id'] ?? 'Not found') . "</p>";

// Get assessment IDs
$assessments = db_get_rows("SELECT * FROM school_assessment WHERE assesment IN ('CA1', 'CA2', 'EXAM')");
foreach ($assessments as $ass) {
    echo "<p>Assessment: {$ass['assesment']} - ID: {$ass['id']}</p>";
}

// Test insert a score
if ($student && $subject) {
    $testData = [
        'session_id' => 71,
        'term_id' => 90,
        'class_id' => 398,
        'subject_id' => $subject['id'],
        'student_id' => $student['id'],
        'assesment_id' => 793, // CA1
        'score' => 20,
        'create_by_userid' => $_SESSION['userid'],
        'create_by_usertype' => $_SESSION['usertype'],
        'randomid' => randomFix(15)
    ];
    
    echo "<h3>Attempting to insert test score...</h3>";
    $result = db_insert("input_score_class_teacher", $testData);
    
    if ($result) {
        echo "<p style='color:green'>✓ Test score inserted successfully!</p>";
    } else {
        echo "<p style='color:red'>✗ Failed to insert test score</p>";
    }
}

// Check if scores exist
$scores = db_get_rows("SELECT * FROM input_score_class_teacher WHERE student_id = ?", [$student['id'] ?? 0]);
echo "<h3>Current scores for student:</h3>";
if (!empty($scores)) {
    echo "<pre>";
    print_r($scores);
    echo "</pre>";
} else {
    echo "<p>No scores found</p>";
}
?>