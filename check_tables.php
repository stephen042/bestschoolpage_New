<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

$host = "localhost";
$user = "root";
$pass = "";
$dbName = "bestsch3_skooling";

$conn = new mysqli($host, $user, $pass, $dbName);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "<h2>Database: $dbName</h2>";

// Get all tables
$tables = $conn->query("SHOW TABLES");
echo "<h3>Tables found (" . $tables->num_rows . "):</h3>";
echo "<ul>";
$tableList = [];
while ($row = $tables->fetch_array()) {
    $tableName = $row[0];
    $tableList[] = $tableName;
    echo "<li>$tableName</li>";
}
echo "</ul>";

// Check for missing expected tables
$expected = ['settings', 'home_content'];
echo "<h3>Checking for expected tables:</h3>";
foreach ($expected as $expectedTable) {
    if (in_array($expectedTable, $tableList)) {
        echo "✓ $expectedTable exists<br>";
    } else {
        echo "✗ $expectedTable is MISSING<br>";
    }
}

// Try to find similar tables
echo "<h3>Looking for similar table names:</h3>";
foreach ($tableList as $table) {
    if (strpos($table, 'setting') !== false || strpos($table, 'config') !== false || strpos($table, 'option') !== false) {
        echo "Possible settings table: $table<br>";
    }
    if (strpos($table, 'home') !== false || strpos($table, 'front') !== false || strpos($table, 'page') !== false) {
        echo "Possible home content table: $table<br>";
    }
}

$conn->close();
?>