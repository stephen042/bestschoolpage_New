<?php
// ============================================================================
// CLASS TEACHER ROLL CALL
// ============================================================================
// Handles attendance tracking for class teachers
// ============================================================================

include('../config.php');
include('inc.session-create.php');

// ============================================================================
// SESSION VARIABLES INITIALIZATION
// ============================================================================
$sessionUserId = (int)($_SESSION['userid'] ?? 0);
$sessionUsername = (string)($_SESSION['username'] ?? '');
$sessionEmail = (string)($_SESSION['email'] ?? '');
$isSchoolOwnerSession = ($sessionUserId > 0 && $sessionUserId === (int)($_SESSION['create_by_userid'] ?? $sessionUserId));
$create_by_userid = (int)($_SESSION['create_by_userid'] ?? $_SESSION['userid'] ?? 0);
$create_by_usertype = (string)($_SESSION['usertype'] ?? '');

// ============================================================================
// GET CLASS DETAILS
// ============================================================================
$studentDetail = [];
$randomid = isset($_GET['randomid']) ? trim($_GET['randomid']) : '';

if (!empty($randomid)) {
    $studentDetail = db_get_row(
        "SELECT * FROM school_class WHERE randomid = ? AND create_by_userid = ?",
        [$randomid, $create_by_userid]
    );
}

// ============================================================================
// GET TEACHER'S ASSIGNED CLASSES
// ============================================================================
$assignedClassIds = [];

if ($_SESSION['usertype'] == '1') {
    // Get staff ID from staff_manage
    $staffDetails = db_get_row(
        "SELECT id FROM staff_manage WHERE staff_id = ? OR id = ?",
        [$_SESSION['username'], $_SESSION['userid']]
    );
    
    if (!empty($staffDetails)) {
        $staffId = (int)$staffDetails['id'];
        
        // Get assigned class IDs from class_teacher table
        $assignedClasses = db_get_rows(
            "SELECT school_class FROM class_teacher WHERE staff_id = ? AND create_by_userid = ?",
            [$staffId, $create_by_userid]
        );
        
        foreach ($assignedClasses as $class) {
            $assignedClassIds[] = (int)$class['school_class'];
        }
    }
}

// ============================================================================
// VALIDATION
// ============================================================================
$validate = new Validation();
$PageTitle = 'Class Teacher Roll Call';
$FileName = 'class_teacher_roll_call.php';

// ============================================================================
// SESSION MESSAGES
// ============================================================================
if (!empty($_SESSION['success'])) {
    $stat['success'] = $_SESSION['success'];
    unset($_SESSION['success']);
}

// ============================================================================
// PROCESS ROLL CALL SUBMISSION
// ============================================================================
if (isset($_POST['roll_call'])) {
    $session_id = isset($_GET['session']) ? (int)$_GET['session'] : 0;
    $term_id = isset($_GET['term_id']) ? (int)$_GET['term_id'] : 0;
    $date = isset($_GET['date']) ? trim($_GET['date']) : '';
    
    $validate->addRule($session_id, 'num', 'session', true);
    $validate->addRule($term_id, 'num', 'Term', true);
    $validate->addRule($date, '', 'ROLL CALL DATE', true);
    
    if ($validate->validate()) {
        try {
            $todayPresent = isset($_POST['present']) ? implode(",", $_POST['present']) : '';
            $todayLate = isset($_POST['late']) ? implode(",", $_POST['late']) : '';
            
            $randomid = randomFix(15);
            
            $aryData = array(
                'roll_call_date'      => $date,
                'session_id'          => $session_id,
                'term_id'             => $term_id,
                'class_id'            => (int)($studentDetail['id'] ?? 0),
                'present'             => $todayPresent,
                'late'                => $todayLate,
                'userid'              => $sessionUserId,
                'usertype'            => (int)$_SESSION['usertype'],
                'create_by_userid'    => $create_by_userid,
                'create_by_usertype'  => $create_by_usertype,
                'randomid'            => $randomid,
            );
            
            $flgIn = db_insert("class_teacher_roll_call", $aryData);
            
            if ($flgIn) {
                $_SESSION['success'] = "Class Roll Submitted Successfully";
                redirect($FileName . '?action=table&randomid=' . urlencode($randomid) . '&date=' . urlencode($date) . '&session=' . $session_id . '&term_id=' . $term_id);
            } else {
                $stat['error'] = "Failed to submit roll call. Please try again.";
            }
        } catch (Exception $e) {
            $stat['error'] = "An error occurred while submitting roll call.";
        }
    } else {
        $stat['error'] = $validate->errors();
    }
}

// ============================================================================
// PROCESS ROLL CALL UPDATE
// ============================================================================
if (isset($_POST['update_roll_call'])) {
    $session_id = isset($_GET['session']) ? (int)$_GET['session'] : 0;
    $term_id = isset($_GET['term_id']) ? (int)$_GET['term_id'] : 0;
    $date = isset($_GET['date']) ? trim($_GET['date']) : '';
    
    $validate->addRule($session_id, 'num', 'session', true);
    $validate->addRule($term_id, 'num', 'Term', true);
    $validate->addRule($date, '', 'ROLL CALL DATE', true);
    
    if ($validate->validate()) {
        try {
            $todayPresent = isset($_POST['present']) ? implode(",", $_POST['present']) : '';
            $todayLate = isset($_POST['late']) ? implode(",", $_POST['late']) : '';
            
            $aryData = array(
                'roll_call_date'      => $date,
                'session_id'          => $session_id,
                'term_id'             => $term_id,
                'class_id'            => (int)($studentDetail['id'] ?? 0),
                'present'             => $todayPresent,
                'late'                => $todayLate,
                'userid'              => $sessionUserId,
                'usertype'            => (int)$_SESSION['usertype'],
                'create_by_userid'    => $create_by_userid,
                'create_by_usertype'  => $create_by_usertype,
            );
            
            $flgIn = db_update(
                "class_teacher_roll_call",
                $aryData,
                "roll_call_date = ? AND session_id = ? AND term_id = ? AND class_id = ? AND create_by_userid = ?",
                [$date, $session_id, $term_id, (int)($studentDetail['id'] ?? 0), $create_by_userid]
            );
            
            if ($flgIn !== false) {
                $_SESSION['success'] = "Class Roll Updated Successfully";
                redirect($FileName . '?action=table&randomid=' . urlencode($randomid) . '&date=' . urlencode($date) . '&session=' . $session_id . '&term_id=' . $term_id);
            } else {
                $stat['error'] = "Failed to update roll call. Please try again.";
            }
        } catch (Exception $e) {
            $stat['error'] = "An error occurred while updating roll call.";
        }
    } else {
        $stat['error'] = $validate->errors();
    }
}

// ============================================================================
// GET DATA FOR DISPLAY
// ============================================================================
// Get class list
$classList = [];
try {
    if ($_SESSION['usertype'] == '1') {
        if (!empty($assignedClassIds)) {
            $classIds = implode(',', array_map('intval', $assignedClassIds));
            $classList = db_get_rows(
                "SELECT * FROM school_class WHERE create_by_userid = ? AND id IN ($classIds) ORDER BY name ASC",
                [$create_by_userid]
            );
        }
    } else {
        $classList = db_get_rows(
            "SELECT * FROM school_class WHERE create_by_userid = ? ORDER BY name ASC",
            [$create_by_userid]
        );
    }
} catch (Exception $e) {
    $classList = [];
}

// Get roll call detail for the selected date
$rollDetail = [];
if (isset($_GET['action']) && $_GET['action'] == 'table' && !empty($studentDetail)) {
    $session_id = isset($_GET['session']) ? (int)$_GET['session'] : 0;
    $term_id = isset($_GET['term_id']) ? (int)$_GET['term_id'] : 0;
    $date = isset($_GET['date']) ? trim($_GET['date']) : '';
    
    if ($session_id > 0 && $term_id > 0 && !empty($date)) {
        $rollDetail = db_get_row(
            "SELECT * FROM class_teacher_roll_call 
             WHERE roll_call_date = ? AND session_id = ? AND term_id = ? 
             AND class_id = ? AND create_by_userid = ?",
            [$date, $session_id, $term_id, (int)($studentDetail['id'] ?? 0), $create_by_userid]
        );
    }
}

// Get students for the selected class
$students = [];
if (!empty($studentDetail) && isset($_GET['session']) && isset($_GET['term_id'])) {
    $session_id = (int)$_GET['session'];
    $term_id = (int)$_GET['term_id'];
    $class_id = (int)($studentDetail['id'] ?? 0);
    
    if ($session_id > 0 && $term_id > 0 && $class_id > 0) {
        $students = db_get_rows(
            "SELECT * FROM manage_student 
             WHERE class = ? AND session = ? AND term_id = ? AND create_by_userid = ?",
            [$class_id, $session_id, $term_id, $create_by_userid]
        );
    }
}
?>
<!DOCTYPE html>
<html>
<head>
<?php include('inc.meta.php'); ?>
<link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/css?family=Droid+Serif" />
<style>
    body, label, span, a, .gwt-Button {
        font-family: 'Droid Serif' !important; 
    }
    .page-title {
        font-size: 20px;
        margin-bottom: 0;
        margin-top: 7px;
        text-align: center;
        background: white;
        padding: 23px 0 30px 0px;
        border-bottom: 5px solid gainsboro;
    }
    .zasw {
        border: 1px solid gainsboro;
        height: auto;
        margin-top: 18px;
    }
    .zasw1 {
        height: auto;
        margin-top: 18px;
    }
    .sectionza {
        background: white;
        height: 1000px;
    }
    .top-serche input {
        padding: 5px 49px 5px 14px;
        border: 2px solid gainsboro;
        border-radius: 4px;
        position: relative;
    }
    .top-serche {
        padding: 32px 0 9px 30px;
    }
    .content-page > .content {
        margin-bottom: 60px;
        margin-top: 60px;
        padding: 20px 30px 15px 78px;
        background: white;
    }
    .zswqas ul {
        list-style: none;
    }
    .zswqas li a span i {
        font-size: 29px;
        padding-top: 9px;
    }
    .zswqas li a span {
        padding-right: 16px;
    }
    .zswqas li a {
        width: 239px;
        display: block;
        padding: 16px 14px 14px 18px;
        border-bottom: 2px solid gainsboro;
    }
    .topside-section ul {
        display: inline-flex;
        list-style: none;
    }
    .topside-section li {
        padding: 0 11px 0 0;
    }
    .topside-section {
        padding-top: 8px;
        border: 1px solid gainsboro;
        box-shadow: 1px 6px 4px gainsboro;
        padding: 14px 8px 11px 1px;
    }
    .zqw22 .panel-success > .panel-heading {
        background: white;
    }
    .zqw22 .nav.nav-tabs > li > a:hover, .nav.tabs-vertical > li > a:hover {
        color: black !important;
        font-weight: 700;
    }
    .zqw22 .nav.nav-tabs > li > a, .nav.tabs-vertical > li > a {
        border-top-right-radius: 10px;
        border-top-left-radius: 10px;
        font-size: 10px;
        height: 38px;
        margin-top: 0;
    }
    div.dataTables_filter label {
        font-weight: 400;
        white-space: nowrap;
        text-align: left;
        border: 1px solid gainsboro;
        padding: 4px 13px 4px 0px;
        border-radius: 5px;
        color: black;
    }
    #example .active {
        background: #1B3058;
        color: white;
    }
    .zqw22 .nav-tabs > li.active > a, .nav-tabs > li.active > a:focus, .nav-tabs > li.active > a:hover, .tabs-vertical > li.active > a, .tabs-vertical > li.active > a:focus, .tabs-vertical > li.active > a:hover {
        color: black !important;
        font-weight: 700;
        line-height: 38px;
        background: gainsboro;
    }
    .zqw22 .panel-success > .panel-heading {
        background: white;
        padding: 0;
    }
    .zqw22 .panel .panel-body {
        border-right: none !important;
        border: 1px solid gainsboro;
    }
    .gwt-Label {
        padding: 8px;
    }
    .zqw22 input {
        padding: 8px 3px 10px 0;
        border: 1px solid gainsboro;
        background: #dcdcdc45;
        border-radius: 5px;
        margin-right: 0px;
        width: 156px;
        margin: 8px 0 11px 0;
        margin-bottom: 5px;
    }
    .sectsab a ul {
        padding: 0px;
    }
    .sectsab.active li {
        color: white;
        font-weight: 600;
    }
    .zqw22 button {
        border: 1px solid #1B3058;
        padding: 4px 5px 4px 5px;
        margin-right: 7px;
        background: transparent;
        color: #1B3058;
    }
    .zqw22 select {
        padding: 5px 0 8px 0;
        background: #dcdcdc2e;
    }
    .zqw22 .nav-tabs > li {
        padding: 0 4px 0 0;
    }
    #tab3success, #tab4success .middleCenterInner {
        border: 1px solid gainsboro;
        padding: 17px 11px 51px 19px;
    }
    #tab3success .middleCenterInner {
        border: 1px solid gainsboro;
        padding: 17px 11px 51px 19px;
    }
    #tab3success, #tab4success .BFOGCKB-c-h {
        border-bottom: 3px solid;
        width: 300px;
    }
    #tab3success .BFOGCKB-c-h {
        border-bottom: 3px solid;
        width: 300px;
    }
    #tab3success, #tab4success {
        border: 1px solid gainsboro;
        padding: 14px 4px 42px 11px;
        width: 361px;
    }
    #tab3success, #tab4success .gwt-DecoratorPanel {
        padding: 21px 21px 43px 4px;
    }
    #tab3success .gwt-DecoratorPanel {
        padding: 21px 21px 43px 4px;
    }
    .zqw22 .panel .panel-body {
        overflow-x: auto;
        border-bottom: 3px solid gainsboro !important;
    }
    .zqw22 .nav.nav-tabs > li > a, .nav.tabs-vertical > li > a {
        background: #dcdcdc4f !important;
        color: black !important;
        font-weight: 700;
        line-height: 38px;
        background: gainsboro;
    }
    .table-bordered > tbody > tr > td, .table-bordered > tbody > tr > th, .table-bordered > tfoot > tr > td, .table-bordered > tfoot > tr > th, .table-bordered > thead > tr > td, .table-bordered > thead > tr > th {
        border: 1px solid #ebeff200;
    }
    div.dataTables_info {
        margin-left: 7px;
    }
    table.dataTable {
        margin-top: 0px !important;
        margin-bottom: 0px !important;
    }
    .nav.nav-tabs > li > a, .nav.tabs-vertical > li > a {
        padding-left: 15px !important;
        padding-right: 15px !important;
    }
    .dataTables_paginate a {
        background-color: transparent;
        margin: 0 0px 0;
        padding: 8px 15px 9px;
        color: white;
        cursor: pointer;
        border: none;
    }
    .zqw22 .nav-tabs > li.active, .nav-tabs > li.active:focus, .nav-tabs > li.active:hover, .tabs-vertical > li.active, .tabs-vertical > li.active:focus, .tabs-vertical > li.active:hover {
        color: black !important;
        font-weight: 700;
    }
    .zswqas .activate a {
        width: 239px;
        display: block;
        padding: 16px 14px 14px 18px;
        border-bottom: 2px solid gainsboro;
        background: #1B3058;
        color: white;
    }
    .zqw22 .nav-tabs > li.active > a, .nav-tabs > li.active > a:focus, .nav-tabs > li.active > a:hover, .tabs-vertical > li.active > a, .tabs-vertical > li.active > a:focus, .tabs-vertical > li.active > a:hover {
        border-bottom: 3px solid #1B3058;
    }
    .topside-section li a {
        border: 1px solid #1B3058;
        padding: 5px 5px 4px 5px;
        display: block;
    }
    .zswqas li a:hover {
        width: 239px;
        display: block;
        padding: 16px 14px 14px 18px;
        border-bottom: 2px solid gainsboro;
        background: #1B3058;
        color: white;
    }
    .zswqas .active {
        width: 239px;
        display: block;
        padding: 16px 14px 14px 18px;
        border-bottom: 2px solid gainsboro;
        background: #1B3058;
        color: white;
    }
    .Wizard-a1 #example_length {
        display: none;
    }
    div.dataTables_filter label {
        font-weight: 400;
        white-space: nowrap;
        text-align: left;
    }
    div.dataTables_filter input {
        margin-left: .5em;
        display: inline-block;
        float: right;
        border: none;
    }
    div.dataTables_filter label {
        padding: 10px;
    }
    div.dataTables_filter input {
        margin-left: .5em;
        display: inline-block;
        float: right;
    }
    div.dataTables_filter {
        text-align: center;
    }
    .Wizard-a1 .zwq img {
        width: 50px;
    }
    .Wizard-a1 .zwq {
        padding-right: 8px;
        float: left;
    }
    .Wizard-a1 .setting {
        display: none;
    }
    .Wizard-a1 .dataTables_info {
        margin: 0 auto !important;
        text-align: center;
        font-size: 12px;
        float: initial;
        position: absolute;
        bottom: 11px;
        left: 0;
        right: 0;
    }
    #example {
        width: 85% !important;
        margin: 0 auto;
    }
    div.dataTables_filter input {
        width: 67%;
    }
    div.dataTables_filter label {
        line-height: 23px;
    }
    .dataTables_paginate #example_previous:before {
        content: "";
        width: 0;
        height: 0;
        border-top: 6px solid transparent;
        border-right: 12px solid #555;
        border-bottom: 6px solid transparent;
        position: absolute;
        z-index: 999999;
        left: 15px;
        bottom: 3px;
    }
    .Wizard-a1 .dataTables_info {
        position: sticky !important;
    }
    div.dataTables_paginate {
        position: relative;
        top: -47px;
    }
    .dataTables_paginate a {
        background-color: transparent;
        margin: 0 0px 0;
        padding: 8px 15px 9px;
        color: white;
        cursor: pointer;
        border: none;
        position: static;
    }
    .dataTables_paginate .next {
        background: none;
        border: navajowhite;
        position: relative;
        color: white !important;
        position: relative;
    }
    div.dataTables_paginate {
        margin: 0;
        white-space: nowrap;
        text-align: center !important;
        padding-top: 27px;
    }
    .dataTables_paginate .disabled {
        background: none;
        color: white;
        border: none !important;
        padding: unset;
        display: block;
        color: transparent !important;
    }
    .tab-content .dataTables_paginate .disabled, .tab-content .paginate_button.previous.disabled {
        bottom: -105px;
    }
    #example2_paginate .paginate_button.previous:before, #example1_paginate .paginate_button.previous:before {
        bottom: -140px !Important;
    }
    .paginate_button.previous.disabled {
        width: 10%;
        float: left;
    }
    .paginate_button.previous.disabled {
        width: 10%;
        float: right;
    }
    div.dataTables_info {
        white-space: nowrap;
        padding-top: 0px;
    }
    .dataTables_paginate #example_next:before, .dataTables_paginate #example1_next:before, .dataTables_paginate #example2_next:before {
        content: "";
        width: 0;
        height: 0;
        border-top: 6px solid transparent;
        border-left: 12px solid #555;
        border-bottom: 6px solid transparent;
        position: absolute;
        z-index: 999999;
        right: 15px;
        bottom: 9px;
        top: 4px;
    }
    div.dataTables_paginate {
        margin: 0;
        white-space: nowrap;
        text-align: center !important;
    }
    .paging_simple_numbers span {
        opacity: 0;
    }
    #example td {
        padding: 4px 11px 4px 13px;
        border-bottom: 3px solid;
        margin: 0 0 0;
    }
    #example .active:hover {
        background: #1B3058;
        color: white;
    }
    .Wizard-a1 .sorting_1 {
        display: none;
    }
    .dataTables_filter label:before {
        position: absolute;
        right: 46px;
        top: 62px !important;
        border: 1px solid #1B3058;
    }
    .dataTables_filter:before {
        content: '';
        position: absolute;
    }
    div.dataTables_filter label {
        position: relative;
        width: 85%;
        text-align: left;
    }
    div.dataTables_filter {
        margin-top: 20px;
    }
    .sectsab li {
        list-style: none;
    }
    div.dataTables_paginate {
        margin: 0 auto;
    }
    .gridTable {
        margin-bottom: 15px;
    }
    .gwt-Label {
        width: 90px;
        float: left;
        font-size: 13px;
    }
    #setB input {
        width: 15%;
    }
    .gwt-ListBox {
        width: 60%;
    }
    .beddy img {
        width: 100%;
    }
    .beddy-b input {
        height: 50px;
        width: 100%;
    }
    .hhf button {
        margin-top: 10px;
        margin-bottom: 20px;
    }
    .desh {
        border-bottom: 2px solid;
        border-bottom-style: dashed;
        margin: 20px 0 20px 0px;
    }
    .ssd {
        text-align: center;
        margin: 10px 0 0 0;
        padding-bottom: 10px;
    }
    #example2_paginate .paginate_button.previous:before, #example1_paginate .paginate_button.previous:before {
        content: "";
        width: 0;
        height: 0;
        border-top: 6px solid transparent;
        border-right: 12px solid #555;
        border-bottom: 6px solid transparent;
        position: absolute;
        z-index: 999999;
        left: 15px;
        bottom: 3px;
    }
    #example2_length {
        display: none;
    }
    #example2_paginate .paginate_button.next:before, #example1_paginate .paginate_button.next:before {
        content: "";
        width: 0;
        height: 0;
        border-top: 6px solid transparent;
        border-left: 12px solid #555;
        border-bottom: 6px solid transparent;
        position: absolute;
        z-index: 999999;
        right: 0;
        bottom: 9px;
        top: 4px;
    }
    #example2_paginate, #example1_paginate {
        height: 10px !important;
    }
    #example2_filter.dataTables_filter input,
    #example1_filter.dataTables_filter input, example1_filter.dataTables_filter input {
        width: 95%;
        float: left;
    }
    #example2_filter.dataTables_filter label, #example1_filter.dataTables_filter label {
        position: relative;
        width: 50%;
        color: transparent;
        padding: 0;
        vertical-align: bottom;
    }
    #example2_filter.dataTables_filter label, #example1_filter.dataTables_filter label, #example_filter.dataTables_filter label {
        min-height: 39px;
        max-height: 22px;
    }
    #example2_filter.dataTables_filter input, #example1_filter.dataTables_filter input {
        position: relative;
        bottom: 27px;
        height: 29px;
        color: black;
    }
    #example1 div.dataTables_filter, #example2 div.dataTables_filter, #example div.dataTables_filter {
        text-align: left;
        margin-bottom: 10px;
        margin-left: 12px;
    }
    #example1 tbody input, #example2 tbody input, #example tbody input {
        width: 100% !important;
    }
    #example1_wrapper, #example2_wrapper {
        position: relative;
        padding-bottom: 15px;
    }
    #example1 thead, #example2 thead, #example thead {
        position: absolute;
        bottom: -61px;
        width: 100%;
        border: 1px solid #e4e1e175;
        left: 0;
    }
    #example1_filter, #example2_filter {
        Position: absolute;
        bottom: 0px;
        width: 100%;
        margin-left: -13px;
        text-align: right;
        margin-top: 0;
    }
    .middleCenterInner .gwt-DecoratorPanel, .middleCenterInner table:first-child {
        width: 100% !important;
    }
    #example1_filter div.dataTables_filter {
        text-align: LEFT !IMPORTANT;
    }
    .zqw22 .panel .panel-body {
        padding-bottom: 82px;
    }
    .ddshgcfh {
        position: absolute;
        right: 170px;
        top: 77px;
        font-size: 50px;
    }
    .sectionza input[type=submit] {
        background: #1B3058;
        color: white;
        border: none;
        padding: 6px 15px;
        border-radius: 4px;
        cursor: pointer;
    }
    .sectionza label {
        font-size: 17px;
        font-weight: 600;
    }
    .sectionza input, .sectionza select {
        color: inherit;
        font: inherit;
        margin: 0;
        width: 100px;
        margin-left: 10px;
        margin-top: 5px;
        height: 30px;
    }
    .xza {
        margin: 0;
        width: 294px;
        border-bottom: 1px solid;
    }
</style>
</head>

<body class="fixed-left">
<div id="wrapper">
    <?php include('inc.header.php'); ?>
    <?php include('inc.sideleft.php'); ?>
    <div class="content-page">
        <div class="content">
            <div class="container">
                <div class="row">
                    <div class="col-sm-12">
                        <h4 class="page-title"><?php echo e($PageTitle); ?></h4>
                    </div>
                </div>
                <span><?php echo showMessage($stat); ?></span>
                <div class="row">
                    <div class="sectionza">
                        <div class="col-md-12 col-xm-12">
                            <div class="col-md-3 col-xm-12">
                                <div class="zasw">
                                    <div class="zawq Wizard-a1">
                                        <table id="example" class="display">
                                            <thead class="setting">
                                                <tr>
                                                    <th>Position</th>
                                                    <th>Position</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($classList as $iList): ?>
                                                <tr>
                                                    <td style="padding:0px;"></td>
                                                    <td class="sectsab <?php if (isset($_GET['randomid']) && $_GET['randomid'] == $iList['randomid']) { echo "active"; } ?>">
                                                        <a href="<?php echo $FileName; ?>?action=table&randomid=<?php echo e($iList['randomid']); ?>">
                                                            <ul>
                                                                <li>
                                                                    <span class="zwq"><i class="fa fa-book" style="font-size:48px"></i></span>
                                                                    <span class="subject"><?php echo e($iList['name']); ?></span>
                                                                </li>
                                                            </ul>
                                                        </a>
                                                    </td>
                                                </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                            <tfoot>
                                                <tr class="setting">
                                                    <th>Name</th>
                                                    <th>Position</th>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            
                            <?php if (isset($_GET['action']) && $_GET['action'] == 'table'): ?>
                            <form method="GET" action="">
                                <label>Session</label>
                                <select name="session" id="session" required>
                                    <option value="">Select Session</option>
                                    <?php 
                                    $sessionList = db_get_rows("SELECT * FROM school_session WHERE create_by_userid = ?", [$create_by_userid]);
                                    foreach ($sessionList as $sList): 
                                    ?>
                                    <option value="<?php echo (int)$sList['id']; ?>" <?php if (isset($_GET['session']) && $_GET['session'] == $sList['id']) { echo "selected"; } ?>>
                                        <?php echo e($sList['session']); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                                
                                <label>Term</label>
                                <select name="term_id" id="term_id" required>
                                    <option value="">Select Term</option>
                                    <?php 
                                    $termList = db_get_rows("SELECT * FROM school_term WHERE create_by_userid = ?", [$create_by_userid]);
                                    foreach ($termList as $tList): 
                                    ?>
                                    <option value="<?php echo (int)$tList['id']; ?>" <?php if (isset($_GET['term_id']) && $_GET['term_id'] == $tList['id']) { echo "selected"; } ?>>
                                        <?php echo e($tList['term']); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                                
                                <input type="hidden" name="action" value="table">
                                <input type="hidden" name="randomid" value="<?php echo isset($_GET['randomid']) ? e($_GET['randomid']) : ''; ?>">
                                
                                Roll Call Date:
                                <input type="text" class="datepicker" placeholder="YYYY-MM-DD" name="date" value="<?php echo isset($_GET['date']) ? e($_GET['date']) : ''; ?>" autocomplete="off" required />
                                
                                <input type="submit" value="Click Here" name="" />
                            </form>
                            <?php endif; ?>
                            
                            <?php if (isset($_GET['action']) && $_GET['action'] == 'table'): ?>
                                <?php if (empty($rollDetail)): ?>
                                    <!-- New Roll Call Form -->
                                    <form method="post" action="">
                                        <div class="col-md-9 col-xm-12">
                                            <div class="zqw22">
                                                <div class="panel with-nav-tabs panel-success">
                                                    <div class="panel-heading">
                                                        <div class="topside-section">
                                                            <div class="card-box table-responsive tablthisresponsive">
                                                                <table class="table table-striped table-bordered tablthisresponsive">
                                                                    <thead>
                                                                        <tr>
                                                                            <th>#</th>
                                                                            <th>Student Id</th>
                                                                            <th>Name</th>
                                                                            <th>Present/Absent</th>
                                                                            <th>Late</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                        <?php 
                                                                        $i = 0;
                                                                        foreach ($students as $iList):
                                                                            $i++;
                                                                            $istudent = db_get_row(
                                                                                "SELECT * FROM manage_student WHERE student_id = ? AND create_by_userid = ?",
                                                                                [$iList['student_id'], $create_by_userid]
                                                                            );
                                                                        ?>
                                                                        <tr class="<?php echo ($i % 2 == 0) ? 'info' : 'success'; ?>">
                                                                            <td><?php echo $i; ?></td>
                                                                            <td><?php echo e($istudent['student_id'] ?? ''); ?></td>
                                                                            <td><?php echo e(($istudent['first_name'] ?? '') . ' ' . ($istudent['last_name'] ?? '')); ?></td>
                                                                            <td>
                                                                                <input type="checkbox" name="present[]" value="<?php echo (int)$iList['id']; ?>">
                                                                            </td>
                                                                            <td>
                                                                                <input type="checkbox" name="late[]" value="<?php echo (int)$iList['id']; ?>">
                                                                            </td>
                                                                        </tr>
                                                                        <?php endforeach; ?>
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                            <button type="submit" name="roll_call" class="gwt-Button">Lock Attendance</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                <?php else: ?>
                                    <!-- Update Roll Call Form -->
                                    <form method="post" action="">
                                        <div class="col-md-9 col-xm-12">
                                            <div class="zqw22">
                                                <div class="panel with-nav-tabs panel-success">
                                                    <div class="panel-heading">
                                                        <div class="topside-section">
                                                            <div class="card-box table-responsive tablthisresponsive">
                                                                <table class="table table-striped table-bordered tablthisresponsive">
                                                                    <thead>
                                                                        <tr>
                                                                            <th>#</th>
                                                                            <th>Student Id</th>
                                                                            <th>Name</th>
                                                                            <th>Present/Absent</th>
                                                                            <th>Late</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                        <?php 
                                                                        $i = 0;
                                                                        $updateRollP = $rollDetail['present'] ?? '';
                                                                        $checkExistP = !empty($updateRollP) ? explode(",", $updateRollP) : [];
                                                                        $updateRollL = $rollDetail['late'] ?? '';
                                                                        $checkExistL = !empty($updateRollL) ? explode(",", $updateRollL) : [];
                                                                        
                                                                        foreach ($students as $iList):
                                                                            $i++;
                                                                            $istudent = db_get_row(
                                                                                "SELECT * FROM manage_student WHERE student_id = ? AND create_by_userid = ?",
                                                                                [$iList['student_id'], $create_by_userid]
                                                                            );
                                                                        ?>
                                                                        <tr class="<?php echo ($i % 2 == 0) ? 'info' : 'success'; ?>">
                                                                            <td><?php echo $i; ?></td>
                                                                            <td><?php echo e($istudent['student_id'] ?? ''); ?></td>
                                                                            <input type="hidden" value="<?php echo (int)$iList['id']; ?>" name="student_id[]">
                                                                            <td><?php echo e(($istudent['first_name'] ?? '') . ' ' . ($istudent['last_name'] ?? '')); ?></td>
                                                                            <td>
                                                                                <input type="checkbox" name="present[]" value="<?php echo (int)$iList['id']; ?>" <?php echo in_array((string)$iList['id'], $checkExistP) ? 'checked' : ''; ?>>
                                                                            </td>
                                                                            <td>
                                                                                <input type="checkbox" name="late[]" value="<?php echo (int)$iList['id']; ?>" <?php echo in_array((string)$iList['id'], $checkExistL) ? 'checked' : ''; ?>>
                                                                            </td>
                                                                        </tr>
                                                                        <?php endforeach; ?>
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                            <button type="submit" name="update_roll_call" class="gwt-Button">Update Attendance</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include('inc.footer.php'); ?>
<?php include('inc.js.php'); ?>

<script>
(function() {
    $(function() {
        var toggle;
        return toggle = new Toggle('.zswqas');
    });

    this.Toggle = (function() {
        class Toggle {
            constructor(toggleClass) {
                this.el = $(toggleClass);
                this.tabs = this.el.find(".xz");
                this.panels = this.el.find(".panel");
                this.bind();
            }

            show(index) {
                var activePanel, activeTab;
                this.tabs.removeClass('activate');
                activeTab = this.tabs.get(index);
                $(activeTab).addClass('activate');
                this.panels.hide();
                activePanel = this.panels.get(index);
                return $(activePanel).show();
            }

            bind() {
                return this.tabs.unbind('click').bind('click', (e) => {
                    return this.show($(e.currentTarget).index());
                });
            }
        }
        return Toggle;
    }).call(this);
}).call(this);

// Add active class to the current button
var header = document.getElementById("example");
var btns = header.getElementsByClassName("sectsab");
for (var i = 0; i < btns.length; i++) {
    btns[i].addEventListener("click", function() {
        var current = document.getElementsByClassName("active");
        if (current.length > 0) {
            current[0].className = current[0].className.replace(" active", "");
        }
        this.className += " active";
    });
}

$(document).ready(function() {
    $('#example1').DataTable();
    $('#example').DataTable({
        "pageLength": 5
    });
    $('#example2').DataTable();
});

function check() {
    document.getElementById("myCheck").checked = true;
}
</script>
</body>
</html>