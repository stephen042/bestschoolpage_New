<?php
session_start();
$_SESSION['userid'] = 1;
$_SESSION['usertype'] = '1';
$_GET['action'] = 'table';
$_GET['randomid'] = 'SCk810606E4cZX6-89';
$_GET['session'] = '66';
$_GET['term_id'] = '22';
include 'skool/class_teacher_roll_call_bulk.php';
?>
