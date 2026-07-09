<?php
/**
 * Database Index Optimization Script
 * Run this once to add all necessary indexes
 */

require_once('config.php');

echo "========================================\n";
echo "  DATABASE INDEX OPTIMIZATION\n";
echo "========================================\n\n";

// Function to check if index exists
function indexExists($table, $indexName) {
    $result = db_get_rows("SHOW INDEX FROM $table WHERE Key_name = ?", [$indexName]);
    return !empty($result);
}

// List of indexes to add
$indexes = [
    // Most critical for performance
    [
        'table' => 'manage_student',
        'name' => 'idx_student_session_term',
        'sql' => "ALTER TABLE manage_student ADD INDEX idx_student_session_term (student_id, session, term_id)"
    ],
    [
        'table' => 'manage_student',
        'name' => 'idx_create_by_userid',
        'sql' => "ALTER TABLE manage_student ADD INDEX idx_create_by_userid (create_by_userid)"
    ],
    [
        'table' => 'input_score_class_teacher',
        'name' => 'idx_score_student_subject',
        'sql' => "ALTER TABLE input_score_class_teacher ADD INDEX idx_score_student_subject (student_id, subject_id, assesment_id)"
    ],
    [
        'table' => 'input_score_class_teacher',
        'name' => 'idx_score_session_term',
        'sql' => "ALTER TABLE input_score_class_teacher ADD INDEX idx_score_session_term (session_id, term_id, class_id)"
    ],
    [
        'table' => 'input_score_class_teacher',
        'name' => 'idx_score_create_by',
        'sql' => "ALTER TABLE input_score_class_teacher ADD INDEX idx_score_create_by (create_by_userid)"
    ],
    [
        'table' => 'school_class',
        'name' => 'idx_class_create_by',
        'sql' => "ALTER TABLE school_class ADD INDEX idx_class_create_by (create_by_userid)"
    ],
    [
        'table' => 'school_session',
        'name' => 'idx_session_create_by',
        'sql' => "ALTER TABLE school_session ADD INDEX idx_session_create_by (create_by_userid)"
    ],
    [
        'table' => 'school_term',
        'name' => 'idx_term_create_by',
        'sql' => "ALTER TABLE school_term ADD INDEX idx_term_create_by (create_by_userid)"
    ],
    [
        'table' => 'school_subject',
        'name' => 'idx_subject_class',
        'sql' => "ALTER TABLE school_subject ADD INDEX idx_subject_class (class_id)"
    ],
    [
        'table' => 'school_assessment',
        'name' => 'idx_assessment_class',
        'sql' => "ALTER TABLE school_assessment ADD INDEX idx_assessment_class (class_id)"
    ],
    [
        'table' => 'school_grade',
        'name' => 'idx_grade_create_by',
        'sql' => "ALTER TABLE school_grade ADD INDEX idx_grade_create_by (create_by_userid)"
    ]
];

$successCount = 0;
$skipCount = 0;
$errorCount = 0;

foreach ($indexes as $index) {
    echo "Checking index: {$index['name']} on table {$index['table']}... ";
    
    if (indexExists($index['table'], $index['name'])) {
        echo "✓ Already exists\n";
        $skipCount++;
        continue;
    }
    
    try {
        db_query($index['sql']);
        echo "✓ Added successfully\n";
        $successCount++;
    } catch (Exception $e) {
        echo "✗ Error: " . $e->getMessage() . "\n";
        $errorCount++;
    }
}

echo "\n========================================\n";
echo "RESULTS:\n";
echo "- Added: $successCount indexes\n";
echo "- Already existed: $skipCount indexes\n";
echo "- Failed: $errorCount indexes\n";
echo "========================================\n\n";

// Show current indexes
echo "Current indexes on key tables:\n\n";

$tables = ['manage_student', 'input_score_class_teacher', 'school_class', 'school_session', 'school_term'];
foreach ($tables as $table) {
    echo "Table: $table\n";
    $indexes = db_get_rows("SHOW INDEX FROM $table");
    foreach ($indexes as $idx) {
        echo "  - {$idx['Key_name']} (Column: {$idx['Column_name']})\n";
    }
    echo "\n";
}

echo "Optimization script completed!\n";
?>