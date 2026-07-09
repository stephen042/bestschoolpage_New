<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h2>Database Connection Test</h2>";

// Database configuration
$host = "localhost";
$user = "root";
$pass = "";
$dbName = "bestsch3_skooling";

echo "Attempting to connect to MySQL...<br>";

$conn = new mysqli($host, $user, $pass);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "✓ Connected to MySQL successfully!<br>";

// Check if database exists
$result = $conn->query("SHOW DATABASES LIKE '$dbName'");
if ($result->num_rows > 0) {
    echo "✓ Database '$dbName' exists!<br>";
    
    // Select the database
    $conn->select_db($dbName);
    
    // Check if tables exist
    $tables = $conn->query("SHOW TABLES");
    echo "✓ Found " . $tables->num_rows . " tables in the database.<br>";
    
    // Check app_settings table specifically
    $settings = $conn->query("SELECT * FROM app_settings WHERE id='1'");
    if ($settings && $settings->num_rows > 0) {
        echo "✓ app_settings table row id=1 exists.<br>";
    } else {
        echo "⚠ Warning: app_settings table row with id=1 not found.<br>";
    }

    // Check required app tables for registration/login
    $requiredTables = ['school_register', 'state', 'school_type'];
    foreach ($requiredTables as $table) {
        $res = $conn->query("SHOW TABLES LIKE '$table'");
        if ($res && $res->num_rows > 0) {
            echo "✓ Table '$table' exists.<br>";
        } else {
            echo "✗ Table '$table' is missing.<br>";
        }
    }
} else {
    echo "✗ Database '$dbName' does NOT exist!<br>";
    echo "You need to import your database first.<br>";
}

$conn->close();
?>