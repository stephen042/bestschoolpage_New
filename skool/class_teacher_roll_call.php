<?php
include('../config.php');
include('inc.session-create.php'); 
$sessionUserId = (int)($_SESSION['userid'] ?? 0);
$sessionUsername = (string)($_SESSION['username'] ?? '');
$sessionEmail = (string)($_SESSION['email'] ?? '');
$isSchoolOwnerSession = ($sessionUserId > 0 && $sessionUserId === (int)($_SESSION['create_by_userid'] ?? $sessionUserId));
$create_by_userid = (int)($_SESSION['create_by_userid'] ?? $_SESSION['userid'] ?? 0);
$studentDetail=$db->getRow("select * from school_class where randomid='".$_GET['randomid']."' and create_by_userid='".$create_by_userid."'");
// ============================================================
// FIX: TEACHER SHOULD ONLY SEE THEIR ASSIGNED CLASSES
// ============================================================

// Get the teacher's assigned class IDs
$assignedClassIds = [];

if ($_SESSION['usertype'] == '1') {
    // Get staff ID from staff_manage
    $staffDetails = db_get_row(
        "SELECT id FROM staff_manage WHERE staff_id = ? OR id = ?",
        [$_SESSION['username'], $_SESSION['userid']]
    );
    
    if (!empty($staffDetails)) {
        $staffId = $staffDetails['id'];
        
        // Get assigned class IDs from class_teacher table
        $assignedClasses = db_get_rows(
            "SELECT school_class FROM class_teacher WHERE staff_id = ? AND create_by_userid = ?",
            [$staffId, $create_by_userid]
        );
        
        foreach ($assignedClasses as $class) {
            $assignedClassIds[] = $class['school_class'];
        }
    }
}
$validate = new validation();
$PageTitle = 'Class Teacher Roll Call';
$Filename = 'class_teacher_roll_call.php';
if ($_SESSION['success'] != "") {
    $stat['success'] = $_SESSION['success'];
    unset($_SESSION['success']);
}	

if (isset($_POST['roll_call'])) 
{
	
		$validate->addRule($_GET['session'],'','session',true);
		$validate->addRule($_GET['term_id'],'','Term',true);
		$validate->addRule($_GET['date'],'','ROLL CALL DATE',true);
	
	
    if ($validate->validate() && count($stat) == 0) {
	
        $todayPresent=implode(",",$_POST['present']);
        $todayLate=implode(",",$_POST['late']);
		
		
           $randomid=randomFix(15);
            $aryData = array(
                'roll_call_date'          => $_GET['date'],
                'session_id'              => $_GET['session'],
				'term_id'                 => $_GET['term_id'],
                'class_id'                =>  $studentDetail['id'],
				//'student_id'            =>  $_POST['student_id'][$key],
                'present'                 =>  $todayPresent,
                'late'                    =>  $todayLate,
                'userid'                  => $_SESSION['userid'],
                'usertype'                => $_SESSION['usertype'],
                'create_by_userid'        => $create_by_userid,
                'create_by_usertype'      => $create_by_usertype,
				'randomid'                =>$randomid, 
                 
            );
            $flgIn = $db->insertAry("class_teacher_roll_call", $aryData);	 
		
		
		$_SESSIon['success'] = "Class Roll Submitted Successfully";
		
		 redirect($FileName.'?action=table&randomid='.$_GET['randomid'].'&date='.$_GET['date'].'&session='.$_GET['session'].'&term_id='.$_GET['term_id']);
		
		
    }
	
	$stat['error']= $validate->errors();
}


elseif (isset($_POST['update_roll_call'])) {
	
		$validate->addRule($_GET['session'],'','session',true);
		$validate->addRule($_GET['term_id'],'','Term',true);
		$validate->addRule($_GET['date'],'','ROLL CALL DATE',true);

	
    if ($validate->validate() && count($stat) == 0) {	
	
		foreach($_POST['student_id'] as $key=>$val)
		{
           $randomid=randomFix(15);
           $todayPresent=implode(",",$_POST['present']);
           $todayLate=implode(",",$_POST['late']);
            $aryData = array(
                'roll_call_date'          => $_GET['date'],
                'session_id'              => $_GET['session'],
				'term_id'                 => $_GET['term_id'],
                'class_id'                =>  $studentDetail['id'],
				//'student_id'              =>  $_POST['student_id'][$key],
                'present'                 => $todayPresent,
                'late'                    => $todayLate,
                'userid'                  => $_SESSION['userid'],
                'usertype'                => $_SESSION['usertype'],
                'create_by_userid'        => $create_by_userid,
                'create_by_usertype'      => $create_by_usertype,
				'randomid'                =>$randomid, 
                 
            );
            $flgIn = $db->updateAry("class_teacher_roll_call", $aryData, "where roll_call_date='".$_GET['date']."'");
			
		}
		$_SESSION['success'] = "Class Roll Updated Successfully";
		redirect($FileName.'?action=table&randomid='.$_GET['randomid'].'&date='.$_GET['date'].'&session='.$_GET['session'].'&term_id='.$_GET['term_id']);
		
		
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

        .zqw22 .nav.nav-tabs > li > a, .nav.tabs-vertical > li > a {
            color: black !important;
            font-weight: 700;
            line-height: 38px;

            background: gainsboro;

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
			float:  left;
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
            /* display: none; */
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
            /* left: 0; */
            right: 46px;
            top: 62px !important;
            /* bottom: 0; */
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
            width: 60%
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
            /* margin: 0; */
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
    </style>
<style>
body, label, span, a, .gwt-Button {
	font-family: 'Droid Serif' !important; 
}
.ddshgcfh 
{
	position: absolute;
    right: 170px;
    top: 77px;
    font-size: 50px;
}
.sectionza input[type=submit] {
    background: #1B3058;
    color: white;
    border: none;
}
.sectionza label {
	font-size: 17px;
    font-weight: 600;
}
.sectionza input,.sectionza select {
    color: inherit;
    font: inherit;
    margin: 0;
    width: 100px;
    margin-left: 10px;
	margin-top: 5px;
    height: 30px;
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

.content-page>.content {
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

.zqw22 .panel-success>.panel-heading {
background: white;
}

.zqw22 .nav.nav-tabs>li>a:hover, .nav.tabs-vertical>li>a:hover {
    color:black!important;
	 font-weight: 700;
 
}


.zqw22 .nav.nav-tabs>li>a, .nav.tabs-vertical>li>a {
  
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
.zqw22 .nav-tabs>li.active>a, .nav-tabs>li.active>a:focus, .nav-tabs>li.active>a:hover, .tabs-vertical>li.active>a, .tabs-vertical>li.active>a:focus, .tabs-vertical>li.active>a:hover {
    color: black!important;
    font-weight: 700;
	    line-height: 38px;

    background: gainsboro;

}

.zqw22 .panel-success>.panel-heading {
    background: white;
    padding: 0;
} 
.zqw22 .panel .panel-body {
    border-right: none!important;
    border: 1px solid gainsboro;
}
.gwt-Label {
    padding: 8px;
}
.zqw22  input {
    padding: 8px 3px 10px 0;
    border: 1px solid gainsboro;
    background: #dcdcdc45;
    border-radius: 5px;
    margin-right: 8px;
	    margin-bottom: 5px;
}
.zqw22 button {
    border: 1px solid #1B3058;
    padding: 4px 3px 4px 3px;
    margin-right: 7px;
    background: transparent;
    color: #1B3058;
}
.zqw22 select {
    padding: 5px 0 8px 0;
    background: #dcdcdc2e;
}
.zqw22 .nav-tabs>li {
  
    padding: 0 4px 0 0;
}
#tab3success ,#tab4success .middleCenterInner{
    border: 1px solid gainsboro;
    padding: 17px 11px 51px 19px;
}
#tab3success  .middleCenterInner{
    border: 1px solid gainsboro;
    padding: 17px 11px 51px 19px;
}
#tab3success ,#tab4success .BFOGCKB-c-h{
    border-bottom: 3px solid;
    width: 300px;
}
#tab3success  .BFOGCKB-c-h{
    border-bottom: 3px solid;
    width: 300px;
}
#tab3success ,#tab4success  {
    border: 1px solid gainsboro;
    padding: 14px 4px 42px 11px;
    width: 361px;
}
#tab3success ,#tab4success .gwt-DecoratorPanel {
   
    padding: 21px 21px 43px 4px;
}
#tab3success .gwt-DecoratorPanel {
  
    padding: 21px 21px 43px 4px;
}
.zqw22 .panel .panel-body {

    border-bottom: 3px solid gainsboro!important;
}
.zqw22 .nav.nav-tabs>li>a, .nav.tabs-vertical>li>a {
    
    background: #dcdcdc4f!important;
}
.zqw22 .nav.nav-tabs>li>a, .nav.tabs-vertical>li>a {
    color: black!important;
    font-weight: 700;
	    line-height: 38px;

    background: gainsboro;
   
}
.xza{margin: 0;
    width: 294px;
    border-bottom: 1px solid;

}
.dataTables_paginate a {
    background-color: transparent;
    margin: 0 0px 0;
    padding: 8px 15px 9px;
    color: white;
    cursor: pointer;
    border: none;
}
.zqw22 .nav-tabs>li.active, .nav-tabs>li.active:focus, .nav-tabs>li.active:hover, .tabs-vertical>li.active, .tabs-vertical>li.active:focus, .tabs-vertical>li.active:hover {
    color: black!important;
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
.zqw22 .nav-tabs>li.active>a, .nav-tabs>li.active>a:focus, .nav-tabs>li.active>a:hover, .tabs-vertical>li.active>a, .tabs-vertical>li.active>a:focus, .tabs-vertical>li.active>a:hover {
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
.Wizard-a1 #example_length  {
  
	display:none;
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
	float:left;
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
    width: 85%!important;
    margin:0 auto;
}	

div.dataTables_filter input {
    width: 70%;
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
    position: sticky!important;
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
    color: white!important;
    position: relative;
  
}
div.dataTables_paginate {
    margin: 0;
    white-space: nowrap;
    text-align: center!important;
    padding-top: 27px;
}
.dataTables_paginate .disabled {
       background: none;
    color: white;
    border: none!important;
    padding: unset;
    display: block;
    width: 90%;
    color: transparent !important;
    margin: 0 auto;
}
div.dataTables_info {
    white-space: nowrap;
 padding-top: 0px;
}
.dataTables_paginate #example_next:before {
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

div.dataTables_paginate {
    margin: 0;
    white-space: nowrap;
    text-align: center!important;
}
 .paging_simple_numbers span{
    /* display: none; */
    opacity: 0;
}
	#example td {
    padding: 15px 11px 18px 13px;
    border-bottom: 3px solid;
    margin: 0 0 0;
}
#example .active:hover {
    background: #1B3058;
    color: white;
}
		.Wizard-a1	.sorting_1 {
    display: none;
}	
.dataTables_filter label:before {
    position: absolute;
    /* left: 0; */
    right: 46px;
    top: 62px!important;
    /* bottom: 0; */
    border: 1px solid #1B3058;
}



     
.dataTables_filter:before{
	content:'';
	position:absolute;
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
	
	list-style:none;
}


div.dataTables_paginate {
  

    margin: 0 auto;
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
            <h4 class="page-title"><?php echo $PageTitle; ?></h4>
          </div>
        </div>
        <span> <?php echo msg($stat) ?></span>
        <div class="row">
          <div class="sectionza">
            <div class="col-md-12   col-xm-12">
              <div class="col-md-3   col-xm-12">
                <div class="zasw ">
                  <div class="zawq Wizard-a1">
                    <table id="example" class="display">
                      <thead class="setting">
                        <tr>
                          <th>Position</th>
                          <th>Position</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php 
						if($_SESSION['usertype']=='1')
							{
                                    $iSchoolRegisterStaffid=$db->getVal("select username from school_register where id='".$sessionUserId."' order by id desc");
                                    $iStaffManageId=$db->getVal("select id from staff_manage where create_by_userid='".$create_by_userid."' and (staff_id ='".$iSchoolRegisterStaffid."' or email='".$sessionEmail."' or id='".$sessionUserId."') order by id desc limit 1");	
                                    $iConCatScholCls = $db->getVal("select GROUP_CONCAT(school_class) from class_teacher where staff_id='".$iStaffManageId."'");
                                    $aryDetail = !empty($iConCatScholCls) ? $db->getRows("select * from school_class where create_by_userid='".$create_by_userid."' and id IN ($iConCatScholCls)") : [];
							}
						else {
									$aryDetail = $db->getRows("select * from school_class where create_by_userid='".$create_by_userid."'");
							}
							
							                foreach ($aryDetail as $iList) {

                                                ?>
                        <tr>
                          <td style="padding:0px;"></td>
                          <td class="sectsab <?php if ($_GET['randomid'] == $iList['randomid']) {
                                                        echo "active";
                                                    } ?>"><a href="<?php echo $FileName; ?>?action=table&randomid=<?php echo $iList['randomid'];?> ">
                            <ul>
                              <li> <span class="zwq"> <i class="fa fa-book"
                                                                           style="font-size:48px"></i> </span> <span class="subject"> <?php echo $iList['name']; ?><br>
                                <?php // echo $db->getVal("select session from school_session where id = '" . $iList['session_id'] . "' "); ?>
                                </span> </li>
                            </ul>
                            </a></td>
                        </tr>
                        <?php } ?>
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
              <?php  if($_GET['action']=='table') { ?>
              <form method="GET" action="">
                <label>Session</label>
                <select name="session" id="session"  required>
                  <option value="">Select Session</option>
                  <?php $aryDetail=$db->getRows("select * from  school_session  where create_by_userid='".$create_by_userid."'");

							foreach($aryDetail as $iList)
							{	$i=$i+1;?>
                  <option value="<?php echo $iList['id']; ?>" <?php  if($_GET['session']==$iList['id']) { echo "selected"; }  ?>><?php echo $iList['session']; ?></option>
                  <?php }?>
                </select>
                <label>Term</label>
                <select name="term_id" id="term_id" required>
                  <option value="">Select Term</option>
                  <?php $aryDetail=$db->getRows("select * from  school_term where create_by_userid='".$create_by_userid."'");
							foreach($aryDetail as $iList)
							{	$i=$i+1;?>
                  <option value="<?php echo $iList['id']; ?>" <?php  if($_GET['term_id']==$iList['id']) { echo "selected"; }  ?>><?php echo $iList['term']; ?></option>
                  <?php }?>
                </select>
                <input type="hidden" name="action" value="table">
                <input type="hidden" name="randomid" value="<?php echo $_GET['randomid'] ?>">
                Roll Call Date:
                <input type="text" class="datepicker" placeholder="YYYY-MM-DD" name="date" value="<?php echo $_GET['date'];  ?>" autocomplete="off" required />
                <input type="submit" value="Click Here" name="" />
              </form>
              <?php } ?>
              <?php if ($_GET['action']=='table') { 
								$rollDetail=$db->getRow("select * from class_teacher_roll_call where roll_call_date='".$_GET['date']."' and session_id='".$_GET['session']."' and term_id='".$_GET['term_id']."' and class_id='".$studentDetail['id']."' and create_by_userid='".$create_by_userid."'");
								if($rollDetail['id']=="")
								{
								?>
              <form method="post" action="">
                <div class="col-md-9   col-xm-12">
                  <div class="zqw22">
                    <div class="panel with-nav-tabs panel-success">
                      <div class="panel-heading">
                        <div class="topside-section">
                          <div class="card-box table-responsive tablthisresponsive">
                            <table  class="table table-striped table-bordered tablthisresponsive">
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
								$i=0;
								$iclaas=$db->getRows("select * from manage_student where class='".$studentDetail['id']."'and session='".$_GET['session']."'and term_id='".$_GET['term_id']."' and create_by_userid='".$create_by_userid."' ");
																
								foreach($iclaas as $iList)
								{  
								$i=$i+1;
								$istudent=$db->getRow("select * from  manage_student where student_id='".$iList['student_id']."' and create_by_userid='".$create_by_userid."'");
								?>
                                <tr class="<?php if($i%2=='0'){echo "info";}else{echo "success";}?>" >
                                  <td><?php echo $i;?></td>
                                  <td><?php echo $istudent['student_id'];?></td>
                                  <td><?php  echo $istudent['first_name']."  ".$istudent['last_name'];?></td>
                                  <td><input type="checkbox" name="present[]" value="<?php echo $stuidP= $iList['id']; ?>" <?php if(in_array($stuid,$_POST['present'])) { echo "checked"; } ?> ></td>
                                  <td><input type="checkbox" name="late[]" value="<?php echo $stuidL= $iList['id']; ?>" <?php if(in_array($stuidL,$_POST['late'])) { echo "checked"; } ?> ></td>
                                </tr>
                                <?php } ?>
                              </tbody>
                            </table>
                          </div>
                          <button type="submit" name="roll_call" class="gwt-Button"> Lock Attendence </button>
                          <Br>
                          <Br>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </form>
              <?php } else { 
								$rollDetail=$db->getRow("select * from class_teacher_roll_call where roll_call_date='".$_GET['date']."' and session_id='".$_GET['session']."' and term_id='".$_GET['term_id']."' and class_id='".$studentDetail['id']."' and create_by_userid='".$create_by_userid."'");
								?>
              <form method="post" action="">
                <div class="col-md-9 col-xm-12"  >
                  <div class="zqw22">
                    <div class="panel with-nav-tabs panel-success">
                      <div class="panel-heading">
                        <div class="topside-section">
                          <div class="card-box table-responsive tablthisresponsive">
                            <table  class="table table-striped table-bordered tablthisresponsive">
                              <thead>
                                <tr>
                                  <th>#</th>
                                  <th> Student Id</th>
                                  <th>Name</th>
                                  <th>Present/Absent</th>
                                  <th>Late</th>
                                </tr>
                              </thead>
                              <tbody>
                              <input type="hidden" value="<?php echo $_GET['date']; ?>" name="roll_call_date[]" >
                              <input type="hidden" value="<?php echo $studentDetail['id']; ?>" name="class_id[][]" >
                              <input type="hidden" value="<?php echo $_GET['session']; ?>" name="session_id[][]" >
                              <?php
								$i=0;
								$iclaas=$db->getRows("select * from manage_student where class='".$studentDetail['id']."' and session='".$_GET['session']."'and term_id='".$_GET['term_id']."' and  create_by_userid='".$create_by_userid."' ");
								
								foreach($iclaas as $iList)
								{  
								$i=$i+1;
								$istudent=$db->getRow("select * from  manage_student where student_id='".$iList['student_id']."' and create_by_userid='".$create_by_userid."'");
								?>
                              <tr class="<?php if($i%2=='0'){echo "info";}else{echo "success";}?>" >
                                <td><?php echo $i;?></td>
                                <td><?php echo $istudent['student_id'];?></td>
                                <input type="hidden" value="<?php echo $iList['id']; ?>" name="student_id[]" >
                                <td><?php  echo $istudent['first_name']."  ".$istudent['last_name'];?></td>
                                <td><?php
									$updateRollP=$rollDetail['present'];
                                    $checkExistP=explode(",",$updateRollP);	
									$updateRollL=$rollDetail['late'];
                                    $checkExistL=explode(",",$updateRollL);									
									
									?>
                                  <input type="checkbox" name="present[]" value="<?php echo $upstuidP= $iList['id']; ?>" <?php if(in_array($upstuidP,$checkExistP)) { echo "checked"; } ?> /></td>
                                <td><input type="checkbox" name="late[]" value="<?php echo $upstuidL=$iList['id']; ?>" <?php if(in_array($upstuidL,$checkExistL)) { echo "checked"; } ?> /></td>
                              </tr>
                              <?php } ?>
                                </tbody>
                              
                            </table>
                          </div>
                          <button type="submit" name="update_roll_call" class="gwt-Button"> Update Attendence </button>
                          <Br>
                          <Br>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </form>
              <?php }?>
              <?php }?>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  </div>
  </div>
  <?php include('inc.footer.php'); ?>
  </div>
  </div>
  <?php include('inc.js.php'); ?>
  <script>
    (function () {
        $(function () {
            var toggle;
            return toggle = new Toggle('.zswqas');
        });

        this.Toggle = (function () {
            class Toggle {
                constructor(toggleClass) {
                    this.el = $(toggleClass);
                    this.tabs = this.el.find(".xz");
                    this.panels = this.el.find(".panel");
                    this.bind();
                }

                show(index) {
                    var activePanel, activeTab;
//update tabs
                    this.tabs.removeClass('activate');
                    activeTab = this.tabs.get(index);
                    $(activeTab).addClass('activate');
//update panels
                    this.panels.hide();
                    activePanel = this.panels.get(index);
                    return $(activePanel).show();
                }

                bind() {
                    return this.tabs.unbind('click').bind('click', (e) => {
                        return this.show($(e.currentTarget).index());
                    });
                }

            };

            Toggle.prototype.el = null;

            Toggle.prototype.tabs = null;

            Toggle.prototype.panels = null;

            return Toggle;

        }).call(this);

    }).call(this);
</script> 
  <script>
    // Add active class to the current button (highlight it)
    var header = document.getElementById("example");
    var btns = header.getElementsByClassName("sectsab");
    for (var i = 0; i < btns.length; i++) {
        btns[i].addEventListener("click", function () {
            var current = document.getElementsByClassName("active");
            current[0].className = current[0].className.replace(" active", "");
            this.className += " active";
        });
    }
</script> 
  <script>

    $(document).ready(function () {
        $('#example1').DataTable();
    });
</script> 
  <script>

    $(document).ready(function () {
        $('#example').DataTable();
    });
</script> 
  <script>

    $(document).ready(function () {
        $('#example2').DataTable();
    });
</script> 
  <script>
$('#example').dataTable( {
    "pageLength": 5
});
</script> 
  <script>
    function check() {
        document.getElementById("myCheck").checked = true;
    }
</script>
</body>
</html>