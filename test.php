<?php
require_once 'config.php';
echo "<h2>System Check</h2>";
echo "PHP Version: " . PHP_VERSION . "<br>";
echo "Database: " . (isset($pdo) ? "Connected ✓" : "Failed ✗") . "<br>";
echo "Session ID: " . session_id() . "<br>";
echo "Config loaded successfully!";
?>