<?php
echo "Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "<br>";
echo "Logo path check: " . (file_exists($_SERVER['DOCUMENT_ROOT'] . '/bestschoolpage/uploads/ca4c3f910260fa208a45b5661e4fdf83_school_logo.png') ? 'EXISTS' : 'NOT FOUND') . "<br>";
echo "Student picture check: " . (file_exists($_SERVER['DOCUMENT_ROOT'] . '/bestschoolpage/uploads/' . ($iStudent['picture'] ?? 'empty')) ? 'EXISTS' : 'NOT FOUND') . "<br>";
?>