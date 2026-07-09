<?php
session_start();
$_SESSION['userid'] = 1;
$_SESSION['usertype'] = '1';
include 'C:\xampp\htdocs\bestschoolpage\config.php';
$randomId = 'test-'.time();
$result = db_insert('class_teacher_roll_call_bulk', [
    'session_id' => 66,
    'term_id' => 22,
    'class_id' => 1,
    'student_id' => 'TEST-1',
    'present' => 2,
    'absent' => 3,
    'total_days_open' => 5,
    'userid' => 1,
    'usertype' => 1,
    'create_by_userid' => 1,
    'create_by_usertype' => 1,
    'randomid' => $randomId
]);
echo $result ? 'insert_ok' : 'insert_fail';
?>
