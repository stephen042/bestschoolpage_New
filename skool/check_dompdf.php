<?php
echo "<h2>Checking Dompdf Location</h2>";

$paths = [
    __DIR__ . '/dompdf_New/autoload.inc.php',
    dirname(__DIR__) . '/dompdf_New/autoload.inc.php',
    __DIR__ . '/dompdf/autoload.inc.php',
    dirname(__DIR__) . '/dompdf/autoload.inc.php',
    __DIR__ . '/vendor/autoload.php',
    dirname(__DIR__) . '/vendor/autoload.php',
];

foreach ($paths as $path) {
    if (file_exists($path)) {
        echo "<p style='color:green'>✓ Found: " . $path . "</p>";
    } else {
        echo "<p style='color:red'>✗ Not found: " . $path . "</p>";
    }
}

echo "<h3>Current directory: " . __DIR__ . "</h3>";
echo "<h3>Parent directory: " . dirname(__DIR__) . "</h3>";
?>