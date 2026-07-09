<?php
echo "<h2>Project Structure Check</h2>";
echo "<pre>";

echo "Current directory: " . __DIR__ . "\n\n";

echo "Checking for required folders:\n";
$folders = ['library', 'includes', 'classes', 'config', 'uploads', 'images'];
foreach ($folders as $folder) {
    $path = __DIR__ . DIRECTORY_SEPARATOR . $folder;
    if (is_dir($path)) {
        echo "✓ Found folder: $folder\n";
        // List contents of library if it exists
        if ($folder == 'library') {
            echo "  Contents of library:\n";
            $files = scandir($path);
            foreach ($files as $file) {
                if ($file != '.' && $file != '..') {
                    echo "    - $file\n";
                }
            }
        }
    } else {
        echo "✗ Missing folder: $folder\n";
    }
}

echo "\nLooking for database class files:\n";
$search_files = ['class.database.php', 'database.php', 'db.class.php', 'Database.php'];
foreach ($search_files as $file) {
    // Search recursively
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(__DIR__));
    foreach ($iterator as $found_file) {
        if ($found_file->getFilename() == $file) {
            echo "✓ Found: " . $found_file->getPathname() . "\n";
        }
    }
}

echo "</pre>";
?>