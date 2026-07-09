<?php
// ============================================================================
// DEBUG VERSION - Find where the wrong logo is coming from
// ============================================================================

require_once('../config.php');
require_once('inc.session-create.php');

echo "<h1>DEBUGGING SCHOOL LOGO ISSUE</h1>";

// Get student from URL
$studentIdParam = $_GET['student_id'] ?? '';
echo "<h3>1. Student ID from URL: " . htmlspecialchars($studentIdParam) . "</h3>";

// Get student data
$student = db_get_row("SELECT * FROM manage_student WHERE student_id = ?", [$studentIdParam]);
if ($student) {
    echo "<p><strong>Student found:</strong> " . htmlspecialchars($student['first_name'] . ' ' . $student['last_name']) . "</p>";
    echo "<p><strong>Student create_by_userid:</strong> " . $student['create_by_userid'] . "</p>";
} else {
    echo "<p style='color:red'>Student NOT found!</p>";
    exit;
}

// Get school from student's create_by_userid
$schoolFromStudent = db_get_row("SELECT * FROM school_register WHERE id = ?", [$student['create_by_userid']]);
echo "<h3>2. School from Student's create_by_userid (" . $student['create_by_userid'] . "):</h3>";
if ($schoolFromStudent) {
    echo "<p><strong>Name:</strong> " . htmlspecialchars($schoolFromStudent['name']) . "</p>";
    echo "<p><strong>Logo:</strong> " . htmlspecialchars($schoolFromStudent['logo']) . "</p>";
    echo "<p><strong>Logo path:</strong> ../uploads/" . $schoolFromStudent['logo'] . "</p>";
    echo "<p><strong>File exists:</strong> " . (file_exists("../uploads/" . $schoolFromStudent['logo']) ? 'YES' : 'NO') . "</p>";
} else {
    echo "<p style='color:red'>School NOT found!</p>";
}

// Get school ID 64 directly
$school64 = db_get_row("SELECT * FROM school_register WHERE id = 64");
echo "<h3>3. School ID 64 (EVERBEST):</h3>";
if ($school64) {
    echo "<p><strong>Name:</strong> " . htmlspecialchars($school64['name']) . "</p>";
    echo "<p><strong>Logo:</strong> " . htmlspecialchars($school64['logo']) . "</p>";
} else {
    echo "<p>School ID 64 not found</p>";
}

// Check all schools in database
$allSchools = db_get_rows("SELECT id, name, logo FROM school_register");
echo "<h3>4. All Schools in Database:</h3>";
echo "<table border='1' cellpadding='5'>";
echo "<tr><th>ID</th><th>School Name</th><th>Logo</th></tr>";
foreach ($allSchools as $school) {
    echo "<tr>";
    echo "<td>" . $school['id'] . "</td>";
    echo "<td>" . htmlspecialchars($school['name']) . "</td>";
    echo "<td>" . htmlspecialchars($school['logo']) . "</td>";
    echo "</tr>";
}
echo "</table>";

// Show current session data
echo "<h3>5. Session Data:</h3>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

echo "<h3>6. URL Parameters:</h3>";
echo "<pre>";
print_r($_GET);
echo "</pre>";

echo "<hr>";
echo "<p><strong>Conclusion:</strong> The PDF should be using school ID <strong>" . ($student['create_by_userid'] ?? 'unknown') . "</strong> (" . htmlspecialchars($schoolFromStudent['name'] ?? 'N/A') . ")</p>";
echo "<p>If you see a different school logo, check your PDF file for hardcoded values like '64' or 'EVERBEST'.</p>";
?>