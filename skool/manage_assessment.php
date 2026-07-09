<?php include('../config.php');
include('inc.session-create.php');
$PageTitle = "Manage Assessment";
$FileName = 'manage_assessment.php';
$validate = new Validation();
if($_SESSION['success']!="")
{
	$stat['success'] = $_SESSION['success'];
	unset($_SESSION['success']);
}

function addappraisaldetail($create_by_userid, $create_by_usertype)
	{
		global $db;
$iAppraisalAssement=$db->getVal("select id from appraisal_details where teacher_id='".$_GET['teacher_id']."' and subject_id='".$_GET['subject_id']."' and session_id='".$_GET['session_id']."' and term_id='".$_GET['term_id']."' and month='".$_GET['month']."' and ddate='".$_GET['ddate']."'");				

	$iLastId=$db->getVal("select id from appraisal_details order by id desc")+1;		
	$randomId=randomFix(15).$iLastId;

$aryData = array(
					'teacher_id'     	 	         			 =>	$_GET['teacher_id'],
					'subject_id'     	 	         			 =>	$_GET['subject_id'],
					'session_id'     	 	         			 =>	$_GET['session_id'],
					'term_id'     	 	         			 	 =>	$_GET['term_id'],
					'month'		     	 	         			 =>	$_GET['month'],
					'ddate'		     	 	         			 =>	$_GET['ddate'],
					'send_to_staff'   	 	         			 =>	0,
 					'randomid'			                         => $randomId,
					'create_by_userid'                           => $create_by_userid,
					'create_by_usertype'                         => $create_by_usertype,
 					);
	if($iAppraisalAssement=='') 
		{
				 $flgIn = $db->insertAry("appraisal_details", $aryData);	
		} 

	}




if(isset($_POST['addnewrecord']))
{
    if($validate->validate() && count($stat) == 0)
		{

$iAppraisalAssement=$db->getVal("select id from appraisal_assessment where teacher_id='".$_POST['teacher_id']."' and subject_id='".$_POST['subject_id']."' and session_id='".$_POST['session_id']."' and term_id='".$_POST['term_id']."' and month='".$_POST['month']."' and ddate='".$_POST['ddate']."'");				
		
if($iAppraisalAssement=='') 
	{
		
	$iLastId=$db->getVal("select id from appraisal_assessment order by id desc")+1;		
	$randomId=randomFix(15).'-'.$iLastId;

		$aryData = array(
					'teacher_id'     	 	         			 =>	$_POST['teacher_id'],
					'subject_id'     	 	         			 =>	$_POST['subject_id'],
					'session_id'     	 	         			 =>	$_POST['session_id'],
					'term_id'     	 	         			 	 =>	$_POST['term_id'],
					'month'		     	 	         			 =>	$_POST['month'],
					'ddate'		     	 	         			 =>	$_POST['ddate'],
 					'create_by_userid'                           => $create_by_userid,
					'create_by_usertype'                         => $create_by_usertype,
					'randomid'									 =>	$randomId,
 					);
		$flgIn = $db->insertAry("appraisal_assessment", $aryData);		
		
		$iLastInsertId = $flgIn ;	
		
		foreach($_POST['staff_assessment_id'] as $key => $val)
				{


		if($_POST['week_1'][$key]!='') {			$iWeekNo_1 = $_POST['week_1'][$key];   } else { $iWeekNo_1 = '0.00' ; }
		if($_POST['week_2'][$key]!='') {			$iWeekNo_2 = $_POST['week_2'][$key];   } else { $iWeekNo_2 = '0.00' ; }
		if($_POST['week_3'][$key]!='') {			$iWeekNo_3 = $_POST['week_3'][$key];   } else { $iWeekNo_3 = '0.00' ; }
		if($_POST['week_4'][$key]!='') {			$iWeekNo_4 = $_POST['week_4'][$key];   } else { $iWeekNo_4 = '0.00' ; }
					
		$aryData = array(
					'appraisal_assessment_id'     	 	     =>	$iLastInsertId,
					'staff_assessment_id'     	 	         =>	$_POST['staff_assessment_id'][$key],
					'week_1'     	 	         			 =>	$iWeekNo_1,
					'week_2'     	 	         			 =>	$iWeekNo_2,
					'week_3'     	 	         			 =>	$iWeekNo_3,
					'week_4'     	 	         			 =>	$iWeekNo_4,
 					);
		$flgInNew = $db->insertAry("appraisal_assessment_class_mark", $aryData);		
		
				}
	  } else {
		  
		  
	foreach($_POST['staff_assessment_id'] as $key => $val)
			{
					
					
		$aryData = array(
					
					'week_1'     	 	         			 =>	$_POST['week_1'][$key],
					'week_2'     	 	         			 =>	$_POST['week_2'][$key],
					'week_3'     	 	         			 =>	$_POST['week_3'][$key],
					'week_4'     	 	         			 =>	$_POST['week_4'][$key],
 					);
		$flgInNew = $db->updateAry("appraisal_assessment_class_mark", $aryData , "where id = '".$_POST['primarykey'][$key]."'");		
				}  
	  }
	  
	  	addappraisaldetail($create_by_userid, $create_by_usertype);
		$stat['success']="Save successfully";
		
    }
    else
	{
		$stat['error'] = $validate->errors();
	}
}

elseif(isset($_POST['addnewrecordpunctuality']))
{
    if($validate->validate() && count($stat) == 0)
		{

$iAppraisalAssement=$db->getVal("select id from appraisal_punctuality where teacher_id='".$_POST['teacher_id']."' and subject_id='".$_POST['subject_id']."' and session_id='".$_POST['session_id']."' and term_id='".$_POST['term_id']."' and month='".$_POST['month']."' and ddate='".$_POST['ddate']."'");				
		
if($iAppraisalAssement=='') 
	{
		
	$iLastId=$db->getVal("select id from appraisal_punctuality order by id desc")+1;		
	$randomId=randomFix(15).'-'.$iLastId;

		$aryData = array(
					'teacher_id'     	 	         			 =>	$_POST['teacher_id'],
					'subject_id'     	 	         			 =>	$_POST['subject_id'],
					'session_id'     	 	         			 =>	$_POST['session_id'],
					'term_id'     	 	         			 	 =>	$_POST['term_id'],
					'month'		     	 	         			 =>	$_POST['month'],
					'ddate'		     	 	         			 =>	$_POST['ddate'],
 					'create_by_userid'                           => $create_by_userid,
					'create_by_usertype'                         => $create_by_usertype,
					'randomid'									 =>	$randomId,
 					);
	$flgIn = $db->insertAry("appraisal_punctuality", $aryData);		
		
		$iLastInsertId = $flgIn ;	
		
		foreach($_POST['staff_punctuality_id'] as $key => $val)
				{
	
	
		if($_POST['week_1'][$key]!='') {			$iWeekNo_1 = $_POST['week_1'][$key];   } else { $iWeekNo_1 = '0.00' ; }
		if($_POST['week_2'][$key]!='') {			$iWeekNo_2 = $_POST['week_2'][$key];   } else { $iWeekNo_2 = '0.00' ; }
		if($_POST['week_3'][$key]!='') {			$iWeekNo_3 = $_POST['week_3'][$key];   } else { $iWeekNo_3 = '0.00' ; }
		if($_POST['week_4'][$key]!='') {			$iWeekNo_4 = $_POST['week_4'][$key];   } else { $iWeekNo_4 = '0.00' ; }
					
		$aryData = array(
					'appraisal_punctuality_id'     	 	     =>	$iLastInsertId,
					'staff_punctuality_id'     	 	         =>	$_POST['staff_punctuality_id'][$key],
					'week_1'     	 	         			 =>	$iWeekNo_1,
					'week_2'     	 	         			 =>	$iWeekNo_2,
					'week_3'     	 	         			 =>	$iWeekNo_3,
					'week_4'     	 	         			 =>	$iWeekNo_4,
 					);
		$flgInNew = $db->insertAry("appraisal_punctuality_class_mark", $aryData);		
	
				}
	  } else {
		  
		  
	foreach($_POST['staff_punctuality_id'] as $key => $val)
			{
					
					
		$aryData = array(
					
					'week_1'     	 	         			 =>	$_POST['week_1'][$key],
					'week_2'     	 	         			 =>	$_POST['week_2'][$key],
					'week_3'     	 	         			 =>	$_POST['week_3'][$key],
					'week_4'     	 	         			 =>	$_POST['week_4'][$key],
 					);
		$flgInNew = $db->updateAry("appraisal_punctuality_class_mark", $aryData , "where id = '".$_POST['primarykey'][$key]."'");		
				}  
	  }
	  	addappraisaldetail($create_by_userid, $create_by_usertype);
		$stat['success']="Save successfully";
		
    }
    else
	{
		$stat['error'] = $validate->errors();
	}
}
elseif(isset($_POST['addnewrecordpersonalassessment']))
{
    if($validate->validate() && count($stat) == 0)
		{

$iAppraisalAssement=$db->getVal("select id from appraisal_personal where teacher_id='".$_POST['teacher_id']."' and subject_id='".$_POST['subject_id']."' and session_id='".$_POST['session_id']."' and term_id='".$_POST['term_id']."' and month='".$_POST['month']."' and ddate='".$_POST['ddate']."'");				
		
	if($iAppraisalAssement=='') 
		{
		

		
	$iLastId=$db->getVal("select id from appraisal_personal order by id desc")+1;		
	$randomId=randomFix(15).'-'.$iLastId;

		$aryData = array(
					'teacher_id'     	 	         			 =>	$_POST['teacher_id'],
					'subject_id'     	 	         			 =>	$_POST['subject_id'],
					'session_id'     	 	         			 =>	$_POST['session_id'],
					'term_id'     	 	         			 	 =>	$_POST['term_id'],
					'month'		     	 	         			 =>	$_POST['month'],
					'ddate'		     	 	         			 =>	$_POST['ddate'],
 					'create_by_userid'                           => $create_by_userid,
					'create_by_usertype'                         => $create_by_usertype,
					'randomid'									 =>	$randomId,
 					);
	$flgIn = $db->insertAry("appraisal_personal", $aryData);		
		
	 $iLastInsertId = $flgIn ;	
	 
		} 
	else {
		
		 $iLastInsertId = $iAppraisalAssement ;	
	  }
	
		$db->delete("appraisal_personal_class_mark", "where appraisal_personal_id = '".$iLastInsertId."'");
		
		for($i=1; $i<=$_POST['totalvalue']; $i++)
				{
 					
		$aryData = array(
					'appraisal_personal_id'     	 	     =>	$iLastInsertId,
					'personal_id'     	 	         		 =>	$_POST['personal_id'.$i],
					'fieldname'     	 	         		 =>	$_POST['fieldname'.$i],
					'fieldvalue'		     	 	         =>	$_POST['fieldvalue'.$i],
 					);
		$flgInNew = $db->insertAry("appraisal_personal_class_mark", $aryData);		
				}
	 	addappraisaldetail($create_by_userid, $create_by_usertype);
		$stat['success']="Save successfully";
		
    }
    else
	{
		$stat['error'] = $validate->errors();
	}
}


elseif(isset($_POST['addnewrecordsavedetails']))
{
    if($validate->validate() && count($stat) == 0)
		{
			addappraisaldetail($create_by_userid, $create_by_usertype);
$iAppraisalAssement=$db->getVal("select id from appraisal_details where teacher_id='".$_POST['teacher_id']."' and subject_id='".$_POST['subject_id']."' and session_id='".$_POST['session_id']."' and term_id='".$_POST['term_id']."' and month='".$_POST['month']."' and ddate='".$_POST['ddate']."'");				
		
		
		$imagedata = base64_decode($_POST['img_data']);
		$filename = md5(date("Y-m-d H:i:s")).'.png';
		$file_name = '../uploads/signuature/'.$filename;
		file_put_contents($file_name,$imagedata);
		
		
		
		
		$imagedata1 = base64_decode($_POST['img_data1']);
		$filename1 	= md5(date("Y-m-d H:i:s")).'abc.png';
		$file_name1 = '../uploads/signuature/'.$filename1;
		file_put_contents($file_name1,$imagedata1);
		
		
		
		$imagedata2 = base64_decode($_POST['img_data2']);
		$filename2 	= md5(date("Y-m-d H:i:s")).'xyz.png';
		$file_name2 = '../uploads/signuature/'.$filename2;
		file_put_contents($file_name2,$imagedata2);
		
		
		if($_POST['img_data']!='Yes') 		{  	$iTeacherSign   = $filename;			} 	else {   $iTeacherSign 	 = $_POST['teacher_sign_old'];	   }
		if($_POST['img_data1']!='Yes') 	{  	$iPrincipalSign = $filename1;			} 	else {   $iPrincipalSign = $_POST['principal_sign_old'];	   }
		if($_POST['img_data2']!='Yes') 	{  	$iDirectorSign 	= $filename2;			} 	else {   $iDirectorSign = $_POST['director_sign_old'];	   }
		
		 
		$aryData = array(
					'teacher_id'     	 	         			 =>	$_POST['teacher_id'],
					'subject_id'     	 	         			 =>	$_POST['subject_id'],
					'session_id'     	 	         			 =>	$_POST['session_id'],
					'term_id'     	 	         			 	 =>	$_POST['term_id'],
					'month'		     	 	         			 =>	$_POST['month'],
					'ddate'		     	 	         			 =>	$_POST['ddate'],
					'remarks'		     	 	         		 =>	$_POST['remarks'],
					'name_of_evalutor'		     	 	         =>	$_POST['name_of_evalutor'],
					'date_sign'		     	 	         		 =>	$_POST['date_sign'],
					'teacher_sign'		     	 	         	 =>	$iTeacherSign,
					'head_teacher'		     	 	         	 =>	$_POST['head_teacher'],
					'principal_sign'		     	 	         =>	$iPrincipalSign,
					'director_sign'		     	 	         	 =>	$iDirectorSign,
 					'create_by_userid'                           => $create_by_userid,
					'create_by_usertype'                         => $create_by_usertype,
 					);
	if($iAppraisalAssement=='') 
		{
				 $flgIn = $db->insertAry("appraisal_details", $aryData);	
		} 
	else {
				 $flgIn = $db->updateAry("appraisal_details", $aryData, "where id='".$iAppraisalAssement."'");	
	  }
		$stat['success']="Save successfully";
    }
    else
	{
		$stat['error'] = $validate->errors();
	}
}

if($_GET['action']=='sendthistostaff')
	{
$iAppraisalAssement=$db->getVal("select id from appraisal_details where teacher_id='".$_GET['teacher_id']."' and subject_id='".$_GET['subject_id']."' and session_id='".$_GET['session_id']."' and term_id='".$_GET['term_id']."' and month='".$_GET['month']."' and ddate='".$_GET['ddate']."'");				
	
		$aryData = array(
						'send_to_staff'   	 	         			 =>	1,
						);
		 
		 $flgIn = $db->updateAry("appraisal_details", $aryData, "where id='".$iAppraisalAssement."'");	
		 		
	}
$iAppraisalDetails=$db->getRow("select * from  appraisal_details where teacher_id='".$_GET['teacher_id']."' and subject_id='".$_GET['subject_id']."' and session_id='".$_GET['session_id']."' and term_id='".$_GET['term_id']."' and month='".$_GET['month']."' and ddate='".$_GET['ddate']."' order by id desc");				
?>
<!DOCTYPE html>
<html>
<head>
<?php include('inc.meta.php'); ?>
<script type="text/javascript">
	function sendstff(url)
	{  
		if(	confirm('Really want to send this to staff.') )
		{
			window.location=url;
		}
	}
</script>  
<script src="//code.jquery.com/jquery-1.11.1.min.js"></script>
<link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/css?family=Droid+Serif" />
<style>

body, label, span, a, .gwt-Button {
	
	 font-family: 'Droid Serif' !important; 
}
.abhi .nav-tabs {
	border-bottom: 2px solid #DDD;
}

.abhi .nav-tabs > li.active > a, .nav-tabs > li.active > a:focus, .nav-tabs > li.active > a:hover {
	border-width: 0;
}

.abhi .nav.nav-tabs > li > a:hover, .nav.tabs-vertical > li > a:hover {
	color: #1B3058 !important;
}

.abhi .nav-tabs > li > a {
	border: none;
	color: #1B3058 !important;
}

.abhi .nav-tabs > li.active > a, .nav-tabs > li.active > a:focus, .nav-tabs > li.active > a:hover, .tabs-vertical > li.active > a, .tabs-vertical > li.active > a:focus, .tabs-vertical > li.active > a:hover {
	color: #1B3058 !important;
}

.abhi .nav > li > a i {
	font-size: 16px;
	padding-right: 5px;
}

.abhi .nav-tabs > li > a::after {
	content: "";
	background: #1B3058;
	height: 2px;
	position: absolute;
	width: 100%;
	left: 0px;
	bottom: -1px;
	transition: all 250ms ease 0s;
	transform: scale(0);
}

.abhi .nav-tabs > li.active > a::after, .nav-tabs > a::after {
	transform: scale(1);
}

.abhi .tab-nav > li > a::after {
	background: # #5a4080 none repeat scroll 0% 0%;
	color: #fff;
}

.abhi .tab-pane {
	padding: 25px 0;
}

.abhi .tab-content {
	padding: 20px;
}

.abhi .nav-tabs > li {
	
	text-align: center;
}

.abhi .card {
	background: #FFF none repeat scroll 0% 0%;
	box-shadow: 0px 1px 3px rgba(0, 0, 0, 0.3);
	margin-bottom: 30px;
}

.abhi body {
	background: #EDECEC;
	padding: 50px;
}

.abhi .ass .select-hide {
	display: none;
}

.abhi .ass .custom-select select {
	display: none;
}

.abhi .ass .select-selected {
	border-bottom: 1px solid #9e9e9e;
}

.abhi .ass .ytr {
	margin-top: 22px;
}

.abhi .fdg {
	border-bottom: 1px solid #9e9e9e2b;
}

.abhi .shg p {
	color: #1B3058;
}

.abhi .ass .bgb i {
	color: #1B3058;
	font-size: 19px;
}

.abhi .ass .col-md-4 i {
	background: #F44336;
	padding: 8px;
	border-radius: 50%;
	color: #fff;
	font-size: 14px;
}

.abhi .ass .shg {
	padding-top: 29px;
}

.abhi .ass .select-items {
	border: 1px solid #ddd;
	padding: 9px;
	position: relative;
	bottom: 20px;
	background: #fff;
}

.abhi .ass .select-items div {
	padding-bottom: 7px;
}

.abhi .ab-1 {
	text-align: center;
}

.abhi .icon i {
	color: #0b4587;
	background: #fff;
	font-size: 32px;
	border-radius: 50%;
	position: absolute;
	bottom: -25px;
	left: 0;
	right: 0;
	width: 100%;
	margin: 0 auto;
	padding: 15px 10px 15px 10px;
}

.abhi .icon input {
	position: absolute;
	left: 0;
	opacity: 0;
	width: 100%;
	right: 0;
	top: -18px;
}

.abhi .abhish .input-field {
	padding-bottom: 0;
}

.abhi .abh {
	margin-top: 35px;
}

.abhi .input-field input {
	background-color: transparent;
	border: none;
	border-bottom: 1px solid #9e9e9e;
	border-radius: 0;
	outline: none;
	width: 100%;
	margin: 0 0 15px 0;
	padding: 0;
	box-shadow: none;
	box-sizing: content-box;
	transition: all .3s;
}

.abhi .input-field label {
	color: #9e9e9e;
}

.abhi .icon {
	position: relative;
	left: 0;
	bottom: 0;
	width: 7%;
	margin: 0 auto;
	right: 0;
	height: 0;
	top: 0;
}

.abhi .ab-2 {
	background: #0b4587;
	color: #fff;
	width: 23%;
	padding: 28px;
	margin: 0 auto;
}

.abhi .imgage {
	padding-bottom: 13px;
}

.abhi .abb {
	text-align: center;
}

.abhi .ab-3 {
	margin-top: 30px;
}

.abhi .plp {
	margin-bottom: 80px;
}

.abhi .ab-3 .col-md-1 i {
	font-size: 17px;
	color: #000000d6;
}

.abhi .ab-3 .col-md-4 i {
	background: #F44336;
	padding: 8px;
	border-radius: 50%;
	color: #fff;
	font-size: 14px;
}

.abhi .ab-3 input {
	color: rgba(0, 0, 0, 0.26);
	border-bottom: 1px dotted rgba(0, 0, 0, 0.26);
}

.abhi button {
	cursor: pointer;
	float: right;
	background: #1B3058;
	color: #fff;
}

.abhi button:hover {

	background: #1B3058;
	color: #fff;
}

.abhi button i {
	padding-right: 45px;
	font-size: 13px;
}

.abhi .input-field {
	padding-bottom: 20px;
}

.abhi .assde {
	margin-top: 50px;
}

.abhi .ade {
	margin-top: 40px;
}

.abhi .bgb {
	text-align: center;
	padding-top: 3px;
}

.abhi .bgb i {
	color: #1B3058;
	font-size: 19px;
}

@media all and (max-width: 724px) {
	.abhi .nav-tabs > li > a > span {
		display: none;
	}

	.abhi .nav-tabs > li > a {
		padding: 5px 5px;
	}
}


.page-title {
    margin-bottom: 30px;
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
            <h4 class="page-title licat" style="text-align: center;"><?php echo $PageTitle ?></h4>
            <?php echo msg($stat);?> </div>
        </div>
        <div class="abhi">
          <div class="container">
            <div class="row">
              <div class="col-md-12">
                <div class="card">
                 <ul class="nav nav-tabs" role="tablist">
                    <li role="presentation" class="<?php if($_GET['action']=='list') { echo "active"; } ?>"> <a href="<?php echo $FileName; ?>?action=list"> <i class="fa fa-list-ol" aria-hidden="true"></i><span>Manage Assessment</span> </a> </li>
                    <li role="presentation" class="<?php if($_GET['action']!='list' ) { echo "active"; } ?>"> <a href="<?php echo $FileName; ?>"> <i class="fa fa-plus" aria-hidden="true"></i><span>Add New Assessment</span> </a> </li>
                  </ul>
                  <div class="tab-content">
                  
                  <?php if($_GET['action']=='list' ) { ?>  
                
                    <div role="tabpanel" class="tab-pane active" id="profilxe">
                      <div class="abhish">
                        <div class="card-box">
                          <table class="table table-striped table-bordered"  style="text-align:center" id="datatable">
                            <thead>
                              <tr>
                                <th>#</th>
                                <th>Teacher</th>
                                <th>Subject</th>
                                <th>Session</th>
                                <th>Terms</th>
                                <th>Month</th>
                                <th>Date</th>
                                <th>Sended To Staff</th>
                                <th>Action</th>
                              </tr>
                            </thead>
                            <tbody>
                              <?php $i=0;
					$aryList = $db->getRows("select * from appraisal_details where create_by_userid='".$create_by_userid."' order by id desc");
					foreach($aryList as $iList) 
							{  $i=$i+1; ?>
                              <tr>
                                <td><?php echo $i ?></td>
                                <td><?php echo $db->getVal("select name from school_register where id='".$iList['teacher_id']."' "); ?></td>
                                <td><?php echo $db->getVal("select subject from  school_subject where create_by_userid='".$create_by_userid."' and id = '".$iList['subject_id']."'"); ?></td>
                                <td><?php echo $db->getVal("select session from  school_session where create_by_userid='".$create_by_userid."' and id = '".$iList['session_id']."'"); ?></td>
                                <td> <?php echo $db->getVal("select term from  school_term where create_by_userid='".$create_by_userid."' and id = '".$iList['term_id']."'"); ?></td> 
                                <td><?php    $monthNum = $iList['month'];	echo $monthName = date("F", mktime(0, 0, 0, $monthNum, 10)); ?></td>
                                <td><?php echo $iList['ddate'];?></td>
                                <td><?php if($iList['send_to_staff']=='' || $iList['send_to_staff']=='0') { ?>
                                <a class="btn btn-danger btn-xs" style="color:#fff;">No</a>
                                	<?php } else { ?>
                               <a class="btn btn-info btn-xs"  style="color:#fff;">Yes</a>  
                                    <?php } ?></td>
                                 
                               <td><a href="<?php echo $FileName; ?>?teacher_id=<?php echo $iList['teacher_id']; ?>&subject_id=<?php echo $iList['subject_id']; ?>&session_id=<?php echo $iList['session_id']; ?>&term_id=<?php echo $iList['term_id']; ?>&month=<?php echo $iList['month']; ?>&ddate=<?php echo $iList['ddate']; ?>&searchdata=Save" >View Detail</a></td>
                              </tr>
                              <?php } ?>
                            </tbody>
                          </table>
                        </div>
                      </div>
                    </div> 
                    
                  <?php }  else{ ?>
                  
                
                    
                    <div role="tabpanel" class="tab-pane active" id="home">
                      <div class="ab-1">
                        <?php if($_GET['searchdata']=='') { ?>
                        <form action="" method="GET" enctype="multipart/form-data">
                          <div class="row">
                            <div class="col-md-12 col-xs-12">
                              <div class="form-group clearfix">
                                <div class="col-lg-3 col-md-3">
                                  <select class="form-control" name="teacher_id" required>
                                    <option value="">Select Teacher..</option>
                                    <?php 
							$aryList = $db->getRows("select * from school_register where usertype='1' and create_by_userid='".$create_by_userid."'");
							foreach($aryList as $iList)  {  ?>
                                    <option value="<?php echo $iList['id']; ?>"><?php echo $iList['name']; ?></option>
                                    <?php } ?>
                                  </select>
                                </div>
                                <div class="col-lg-3 col-md-3">
                                  <select class="form-control" name="subject_id" required>
                                    <option value="0">Select Subject..</option>
                                    <?php 	$aryDetail=$db->getRows("select * from  school_subject  where create_by_userid='".$create_by_userid."'");
								foreach($aryDetail as $iList) {	?>
                                    <option value="<?php echo $iList['id']; ?>" <?php  if($_POST['subject_id']==$iList['id']) { echo "selected"; }  ?>><?php echo $iList['subject']; ?></option>
                                    <?php }?>
                                  </select>
                                </div>
                                <div class="col-lg-3 col-md-3">
                                  <select class="form-control" name="session_id" required>
                                    <option value="">Select Session..</option>
                                    <?php 	$aryDetail=$db->getRows("select * from  school_session  where create_by_userid='".$create_by_userid."'");
								foreach($aryDetail as $iList) {	?>
                                    <option value="<?php echo $iList['id']; ?>" <?php  if($_POST['session']==$iList['id']) { echo "selected"; }  ?>><?php echo $iList['session']; ?></option>
                                    <?php }?>
                                  </select>
                                </div>
                                <div class="col-lg-3 col-md-3">
                                  <select class="form-control" name="term_id" required>
                                    <option value="">Select Term..</option>
                                    <?php $aryDetail=$db->getRows("select * from  school_term where create_by_userid='".$create_by_userid."'");
						foreach($aryDetail as $iList) 	{	?>
                                    <option value="<?php echo $iList['id']; ?>" <?php  if($_POST['term_id']==$iList['id']) { echo "selected"; }  ?>><?php echo $iList['term']; ?></option>
                                    <?php }?>
                                  </select>
                                </div>
                              </div>
                            </div>
                            <div class="col-md-12 col-xs-12">
                              <div class="form-group clearfix">
                                <div class="col-lg-3 col-md-3">
                                  <select class="form-control" name="month" required>
                                    <option value="">Select month..</option>
                                    <?php for($i=1; $i<=12; $i++) { ?>
                                    <option value="<?php echo $i; ?>"><?php echo  date("F", strtotime("$i/12/10")); ?></option>
                                    <?php } ?>
                                  </select>
                                </div>
                                <div class="col-lg-3 col-md-3">
                                  <input type="text" class="form-control datepicker" name="ddate" required>
                                </div>
                                <div class="col-lg-3 col-md-3 pull-left">
                                  <button type="submit" class="btn btn-primary pull-left" name="searchdata" style="color:#fff" value="Save" >Search </button>
                                </div>
                              </div>
                            </div>
                          </div>
                        </form>
                        <?php }
						 else { ?>
<a href="pdf_staff_assessment.php?teacher_id=<?php echo $_GET['teacher_id']; ?>&subject_id=<?php echo $_GET['subject_id']; ?>&session_id=<?php echo $_GET['session_id']; ?>&term_id=<?php echo $_GET['term_id']; ?>&month=<?php echo $_GET['month']; ?>&ddate=<?php echo $_GET['ddate']; ?>"  target="_blank">Print PDF</a>
 
<?php if($iAppraisalDetails['send_to_staff']=='' || $iAppraisalDetails['send_to_staff']=='0') { ?>                
<a  onClick="sendstff('<?php echo $FileName; ?>?teacher_id=<?php echo $_GET['teacher_id']; ?>&subject_id=<?php echo $_GET['subject_id']; ?>&session_id=<?php echo $_GET['session_id']; ?>&term_id=<?php echo $_GET['term_id']; ?>&month=<?php echo $_GET['month']; ?>&ddate=<?php echo $_GET['ddate']; ?>&searchdata=Save&action=sendthistostaff')" class="btn btn-primary" style="color:#fff; float:right"><i class="fa fa-paper-plane-o" aria-hidden="true"></i> &nbsp; SEND THIS TO STAFF</a>
<?php }  elseif($iAppraisalDetails['send_to_staff']!='' || $iAppraisalDetails['send_to_staff']=='1') { ?>
<a class="btn btn-default" style="color:#fff; float:right; cursor:none">SEND TO STAFF SUCCESSFULLY</a>
<?php } ?>
                        <h3 class="clsassmnt">CLASS ASSESSEMENT</h3>
                        <form action="" method="POST" enctype="multipart/form-data">
                          <div class="row">
                            <div class="col-md-12 col-xs-12">
                              <div class="card-box table-responsive tablthisresponsive">
                                <table class="table table-striped table-bordered">
                                  <thead>
                                    <tr>
                                      <th>#</th>
                                      <th>Items</th>
                                      <th>Marks</th>
                                      <th>Week 1</th>
                                      <th>Week 2</th>
                                      <th>Week 3</th>
                                      <th>Week 4</th>
                                      <th>Total</th>
                                    </tr>
                                  </thead>
                                  <tbody>
                                    <?php $i=0;
$iAppraisalAssement=$db->getVal("select id from appraisal_assessment where teacher_id='".$_GET['teacher_id']."' and subject_id='".$_GET['subject_id']."' and session_id='".$_GET['session_id']."' and term_id='".$_GET['term_id']."' and month='".$_GET['month']."' and ddate='".$_GET['ddate']."' order by id desc");				
								
				$NewcrearyLisCredtet=$db->getRows("select * from staff_assessment where create_by_userid='".$create_by_userid."' order by id desc");
						foreach($NewcrearyLisCredtet as $iList)
							{	$i=$i+1;
							
							
$iAppClassMark=$db->getRow("select * from appraisal_assessment_class_mark where appraisal_assessment_id='".$iAppraisalAssement."' and staff_assessment_id='".$iList['id']."'");				
$iTotalMarks = $iAppClassMark['week_1']+$iAppClassMark['week_2']+$iAppClassMark['week_3']+$iAppClassMark['week_4'];
							 ?>
                                    <tr>
                                      <td><?php echo $i ?>
                                        <input type="hidden" class="form-control" name="staff_assessment_id[]"  value="<?php  echo $iList['id'];   ?>">
                                        <input type="hidden" class="form-control" name="primarykey[]"  value="<?php  echo $iAppClassMark['id'];   ?>"></td>
                                      <td><?php  echo $iList['assessment'];   ?></td>
                                      <td><?php  echo $iList['mark'];   ?></td>
                                      <td><input type="text" class="form-control" name="week_1[]" value="<?php echo $iAppClassMark['week_1']; ?>" <?php if($iAppClassMark['week_1']<$iList['min_mark']) { ?> style="color:red; border:1px solid red;" <?php } ?> ></td>
                                      <td><input type="text" class="form-control" name="week_2[]" value="<?php echo $iAppClassMark['week_2']; ?>"  <?php if($iAppClassMark['week_2']<$iList['min_mark']) { ?> style="color:red; border:1px solid red;" <?php } ?>></td>
                                      <td><input type="text" class="form-control" name="week_3[]" value="<?php echo $iAppClassMark['week_3']; ?>"  <?php if($iAppClassMark['week_3']<$iList['min_mark']) { ?> style="color:red; border:1px solid red;" <?php } ?>></td>
                                      <td><input type="text" class="form-control" name="week_4[]" value="<?php echo $iAppClassMark['week_4']; ?>"  <?php if($iAppClassMark['week_4']<$iList['min_mark']) { ?> style="color:red; border:1px solid red;" <?php } ?>></td>
                                      <td><input type="text" class="form-control" name="total[]"  value="<?php echo $iTotalMarks; ?>" readonly ></td>
                                    </tr>
                                    <?php } ?>
                                  </tbody>
                                </table>
                                <table>
                                  <tr>
                                    <td><button type="submit" name="addnewrecord" class="btn btn-primary" style="color:#fff; float:left; margin-right:50px;" value="Save" ><i style="padding-right: 5px;" class="fa fa-plus" aria-hidden="true"></i> Save </button>
                                      <a href="<?php echo $FileName; ?>"   class="btn btn-primary" style="color:#fff; float:left; margin-right:50px;" >Back </a></td>
                                  </tr>
                                </table>
                              </div>
                            </div>
                          </div>
                          <input type="hidden" name="teacher_id" value="<?php echo $_GET['teacher_id']; ?>" >
                          <input type="hidden" name="subject_id" value="<?php echo $_GET['subject_id']; ?>" >
                          <input type="hidden" name="session_id" value="<?php echo $_GET['session_id']; ?>" >
                          <input type="hidden" name="term_id" value="<?php echo $_GET['term_id']; ?>" >
                          <input type="hidden" name="month" value="<?php echo $_GET['month']; ?>" >
                          <input type="hidden" name="ddate" value="<?php echo $_GET['ddate']; ?>" >
                        </form>
                        <h3 class="clsassmnt">PUNCTUALITY</h3>
                        <form action="" method="POST" enctype="multipart/form-data">
                          <div class="row">
                            <div class="col-md-12 col-xs-12">
                              <div class="card-box table-responsive tablthisresponsive">
                                <table class="table table-striped table-bordered">
                                  <thead>
                                    <tr>
                                      <th>#</th>
                                      <th>Items</th>
                                      <th>Week 1</th>
                                      <th>Week 2</th>
                                      <th>Week 3</th>
                                      <th>Week 4</th>
                                      <th>Total</th>
                                    </tr>
                                  </thead>
                                  <tbody>
                                    <?php $i=0;
$iAppraisalAssement=$db->getVal("select id from appraisal_punctuality where teacher_id='".$_GET['teacher_id']."' and subject_id='".$_GET['subject_id']."' and session_id='".$_GET['session_id']."' and term_id='".$_GET['term_id']."' and month='".$_GET['month']."' and ddate='".$_GET['ddate']."' order by id desc");				
								
				$NewcrearyLisCredtet=$db->getRows("select * from punctuality order by id desc");
						foreach($NewcrearyLisCredtet as $iList)
							{	$i=$i+1;
							
$iAppClassMark=$db->getRow("select * from appraisal_punctuality_class_mark where appraisal_punctuality_id = '".$iAppraisalAssement."' and staff_punctuality_id='".$iList['id']."'");				
$iTotalMarks = $iAppClassMark['week_1']+$iAppClassMark['week_2']+$iAppClassMark['week_3']+$iAppClassMark['week_4'];
							 ?>
                                    <tr>
                                      <td><?php echo $i ?>
                                        <input type="hidden" class="form-control" name="staff_punctuality_id[]"  value="<?php  echo $iList['id'];   ?>">
                                        <input type="hidden" class="form-control" name="primarykey[]"  value="<?php  echo $iAppClassMark['id'];   ?>"></td>
                                      <td><?php  echo $iList['title'];   ?></td>
                                      <td><input type="text" class="form-control" name="week_1[]" value="<?php echo $iAppClassMark['week_1']; ?>" ></td>
                                      <td><input type="text" class="form-control" name="week_2[]" value="<?php echo $iAppClassMark['week_2']; ?>" ></td>
                                      <td><input type="text" class="form-control" name="week_3[]" value="<?php echo $iAppClassMark['week_3']; ?>" ></td>
                                      <td><input type="text" class="form-control" name="week_4[]" value="<?php echo $iAppClassMark['week_4']; ?>" ></td>
                                      <td><input type="text" class="form-control" name="total[]"  value="<?php echo $iTotalMarks; ?>" readonly ></td>
                                    </tr>
                                    <?php } ?>
                                  </tbody>
                                </table>
                                <table>
                                  <tr>
                                    <td><button type="submit" name="addnewrecordpunctuality" class="btn btn-primary" style="color:#fff; float:left; margin-right:50px;" value="Save" ><i style="padding-right: 5px;" class="fa fa-plus" aria-hidden="true"></i> Save </button>
                                      <a href="<?php echo $FileName; ?>"   class="btn btn-primary" style="color:#fff; float:left; margin-right:50px;" >Back </a></td>
                                  </tr>
                                </table>
                              </div>
                            </div>
                          </div>
                          <input type="hidden" name="teacher_id" value="<?php echo $_GET['teacher_id']; ?>" >
                          <input type="hidden" name="subject_id" value="<?php echo $_GET['subject_id']; ?>" >
                          <input type="hidden" name="session_id" value="<?php echo $_GET['session_id']; ?>" >
                          <input type="hidden" name="term_id" value="<?php echo $_GET['term_id']; ?>" >
                          <input type="hidden" name="month" value="<?php echo $_GET['month']; ?>" >
                          <input type="hidden" name="ddate" value="<?php echo $_GET['ddate']; ?>" >
                        </form>
                        <h3 class="clsassmnt">PERSONAL ASSESSMENT</h3>
                        <form action="" method="POST" enctype="multipart/form-data">
                          <div class="row">
                            <div class="col-md-12 col-xs-12">
                              <div class="card-box table-responsive tablthisresponsive">
                                <table class="table table-striped table-bordered">
                                  <thead>
                                    <tr>
                                      <th>#</th>
                                      <th>Items</th>
                                      <th>Fail</th>
                                      <th>Pass</th>
                                      <th>Good</th>
                                      <th>Very Good</th>
                                    </tr>
                                  </thead>
                                  <tbody>
                                    <?php $i=0;
$iAppraisalAssement=$db->getVal("select id from  appraisal_personal where teacher_id='".$_GET['teacher_id']."' and subject_id='".$_GET['subject_id']."' and session_id='".$_GET['session_id']."' and term_id='".$_GET['term_id']."' and month='".$_GET['month']."' and ddate='".$_GET['ddate']."' order by id desc");				
								
				$NewcrearyLisCredtet=$db->getRows("select * from  personal_assessment where create_by_userid='".$create_by_userid."' order by id desc");
						foreach($NewcrearyLisCredtet as $iList)
							{	$i=$i+1;
							
$iAppClassMark=$db->getRow("select * from  appraisal_personal_class_mark where appraisal_personal_id = '".$iAppraisalAssement."' and fieldname='".$i."'");
 			
 
							 ?>
                                    <tr>
                                      <td><?php echo $i ?>
                                        <input type="hidden" class="form-control" name="fieldname<?php echo $i; ?>"  value="<?php echo $i; ?>">
                                        <input type="hidden" class="form-control" name="personal_id<?php echo $i; ?>"  value="<?php  echo $iList['id'];   ?>"></td>
                                      <td><?php  echo $iList['assessment'];   ?></td>
                                      <td><input type="radio"  name="fieldvalue<?php echo $i; ?>" value="1" <?php if($iAppClassMark['fieldvalue']=='1') { echo 'checked'; } ?> ></td>
                                      <td><input type="radio"  name="fieldvalue<?php echo $i; ?>" value="2" <?php if($iAppClassMark['fieldvalue']=='2') { echo 'checked'; } ?>></td>
                                      <td><input type="radio"  name="fieldvalue<?php echo $i; ?>" value="3" <?php if($iAppClassMark['fieldvalue']=='3') { echo 'checked'; } ?>></td>
                                      <td><input type="radio"  name="fieldvalue<?php echo $i; ?>" value="4" <?php if($iAppClassMark['fieldvalue']=='4') { echo 'checked'; } ?>></td>
                                    </tr>
                                    <?php } ?>
                                  <input type="hidden" value="<?php echo $i; ?>" name="totalvalue" >
                                    </tbody>
                                  
                                </table>
                                <table>
                                  <tr>
                                    <td><button type="submit" name="addnewrecordpersonalassessment" class="btn btn-primary" style="color:#fff; float:left; margin-right:50px;" value="Save" ><i style="padding-right: 5px;" class="fa fa-plus" aria-hidden="true"></i> Save </button>
                                      <a href="<?php echo $FileName; ?>"   class="btn btn-primary" style="color:#fff; float:left; margin-right:50px;" >Back </a></td>
                                  </tr>
                                </table>
                              </div>
                            </div>
                          </div>
                          <input type="hidden" name="teacher_id" value="<?php echo $_GET['teacher_id']; ?>" >
                          <input type="hidden" name="subject_id" value="<?php echo $_GET['subject_id']; ?>" >
                          <input type="hidden" name="session_id" value="<?php echo $_GET['session_id']; ?>" >
                          <input type="hidden" name="term_id" value="<?php echo $_GET['term_id']; ?>" >
                          <input type="hidden" name="month" value="<?php echo $_GET['month']; ?>" >
                          <input type="hidden" name="ddate" value="<?php echo $_GET['ddate']; ?>" >
                        </form>
                        <form action="" method="POST" enctype="multipart/form-data" id="basic-form">

                          <div class="row">
                            <div class="col-md-12 col-xs-12">
                              <table class="table table-striped table-bordered">
                                <tr>
                                  <td colspan="4">Remarks
                                    <textarea class="form-control" name="remarks"><?php echo $iAppraisalDetails['remarks']; ?></textarea></td>
                                </tr>
                                <tr>
                                  <td style="width:200px">Name of Evaluator </td>
                                  <td><input type="text" class="form-control"  name="name_of_evalutor" value="<?php echo $iAppraisalDetails['name_of_evalutor']; ?>"></td>
                                  <td style="width:150px">Date/Sign </td>
                                  <td><input type="text" class="form-control"  name="date_sign" value="<?php echo $iAppraisalDetails['date_sign']; ?>"></td>
                                </tr>
                                <tr>
                                  <td>Teachers Sign </td>
                                  <td><input type="hidden" class="form-control"  name="teacher_sign_old" value="<?php echo $iAppraisalDetails['teacher_sign']; ?>">
                                  <canvas class="sign-pad" id="sign-pad" width="380" height="100" style="border: black 1px solid;"   onClick="editsign('img_data')"></canvas>
                                   <input type="hidden" name="img_data" id="img_data" value="Yes">
                                   <?php if($iAppraisalDetails['teacher_sign']!='') { ?>
                                   <img src="../uploads/signuature/<?php echo $iAppraisalDetails['teacher_sign']; ?>" >
                                  <?php } ?>
                                  </td>
                                  <td>H/Teacher </td>
                                  <td><input type="text" class="form-control"  name="head_teacher" value="<?php echo $iAppraisalDetails['head_teacher']; ?>"></td>
                                </tr>
                                <tr>
                                  <td>Principal Sign </td>
                                  <td><input type="hidden" class="form-control"  name="principal_sign_old" value="<?php echo $iAppraisalDetails['principal_sign']; ?>">
                                  
                                   <canvas class="sign-pad" id="sign-pad1" width="380" height="100" style="border: black 1px solid;" onClick="editsign('img_data1')"></canvas>
                                   <input type="hidden" name="img_data1" id="img_data1" value="Yes">
                                    <?php if($iAppraisalDetails['principal_sign']!='') { ?>
                                    <img src="../uploads/signuature/<?php echo $iAppraisalDetails['principal_sign']; ?>" >
                                    <?php } ?>
                                  </td>
                                 
                                  <td>Director's Sign </td>
                                  <td><input type="hidden" class="form-control"  name="director_sign_old" value="<?php echo $iAppraisalDetails['director_sign']; ?>">
                                  <canvas class="sign-pad" id="sign-pad2" width="380" height="100" style="border: black 1px solid;"  onClick="editsign('img_data2')">Signuature Here</canvas>
            	 				  <input type="hidden" name="img_data2" id="img_data2" value="Yes">
                                   <?php if($iAppraisalDetails['director_sign']!='') { ?>
                                   <img src="../uploads/signuature/<?php echo $iAppraisalDetails['director_sign']; ?>" >
                                    <?php } ?>
                                  </td>
                                </tr>
                              </table>
                              <table>
                                <tr>
                                  <td>
                                  <input type="hidden" name="addnewrecordsavedetails" value="addnewrecordsavedetails" >
                                  <button type="button" id="btnSaveSign"  class="btn btn-primary" style="color:#fff; float:left; margin-right:50px;" value="Save" ><i style="padding-right: 5px;" class="fa fa-plus" aria-hidden="true"></i> Save </button>
                                    <a href="<?php echo $FileName; ?>"   class="btn btn-primary" style="color:#fff; float:left; margin-right:50px;" >Back </a></td>
                                </tr>
                              </table>
                            </div>
                          </div>
                          <input type="hidden" name="teacher_id" value="<?php echo $_GET['teacher_id']; ?>" >
                          <input type="hidden" name="subject_id" value="<?php echo $_GET['subject_id']; ?>" >
                          <input type="hidden" name="session_id" value="<?php echo $_GET['session_id']; ?>" >
                          <input type="hidden" name="term_id" value="<?php echo $_GET['term_id']; ?>" >
                          <input type="hidden" name="month" value="<?php echo $_GET['month']; ?>" >
                          <input type="hidden" name="ddate" value="<?php echo $_GET['ddate']; ?>" >
                        </form>
                        <?php } ?>
                      </div>
                    </div>
                    
                   <?php } ?>  
                  </div>
                </div>
              </div>
            </div>
          </div>
          <?php include('inc.footer.php'); ?>
        </div>
      </div>
    </div>
  </div>
  <?php include('inc.js.php'); ?>
<?php if($_GET['searchdata']!='') { ?>
<link href="signature/jquery.signaturepad.css" rel="stylesheet">
<script src="signature/signature_pad.min.js"></script> 
<script src="signature/html2canvas.js"></script> 
<script>
jQuery(document).ready(function($){
    var canvas = document.getElementById("sign-pad");
    var signaturePad = new SignaturePad(canvas);
	var canvas = document.getElementById("sign-pad1");
    var signaturePad = new SignaturePad(canvas);
	
	var canvas = document.getElementById("sign-pad2");
    var signaturePad = new SignaturePad(canvas);
});
</script> 
<script>
jQuery(document).ready(function() {
	jQuery('#signArea').signaturePad({drawOnly:true, drawBezierCurves:true, lineTop:90});
	jQuery('#signArea1').signaturePad({drawOnly:true, drawBezierCurves:true, lineTop:90});
	jQuery('#signArea2').signaturePad({drawOnly:true, drawBezierCurves:true, lineTop:90});
});

jQuery("#btnSaveSign").click(function(e){ 
	html2canvas([document.getElementById('sign-pad')], {
		onrendered: function (canvas) {
			var canvas_img_data = canvas.toDataURL('image/png');
			var img_data = canvas_img_data.replace(/^data:image\/(png|jpg);base64,/, "");
			
			var img_dataBew = document.getElementById("img_data").value;
			if(img_dataBew=='')
				{
			document.getElementById("img_data").value=img_data;
				
				}
			 
		}
	});
	html2canvas([document.getElementById('sign-pad1')], {
		onrendered: function (canvas) {
			var canvas_img_data = canvas.toDataURL('image/png');
			var img_data = canvas_img_data.replace(/^data:image\/(png|jpg);base64,/, "");
			
			var img_dataBew = document.getElementById("img_data1").value;
			if(img_dataBew=='')
				{
			document.getElementById("img_data1").value=img_data;
				
				}
		 
			 
		}
	});

html2canvas([document.getElementById('sign-pad2')], {
		onrendered: function (canvas) {
			var canvas_img_data = canvas.toDataURL('image/png');
			var img_data = canvas_img_data.replace(/^data:image\/(png|jpg);base64,/, "");
			 
			 var img_dataBew = document.getElementById("img_data2").value;
			if(img_dataBew=='')
				{
			document.getElementById("img_data2").value=img_data;
				
				}
		 
			 
		}
	});

setTimeout(function () {
 document.getElementById("basic-form").submit();
}, 1000);
			
	
});
</script>
<script>
  function editsign(imgdata)
	{
		document.getElementById(imgdata).value='';
	}
</script>
<?php } ?>
</body>
</html>
