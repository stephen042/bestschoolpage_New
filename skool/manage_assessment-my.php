<?php include('../config.php');
include('inc.session-create.php');
$PageTitle = "My Assessment";
$FileName = 'manage_assessment-my.php';
$validate = new Validation();
if($_SESSION['success']!="")
{
	$stat['success'] = $_SESSION['success'];
	unset($_SESSION['success']);
}
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
                  <div class="tab-content">
                    <?php if($_GET['action']=='view' ) { 
$iSchool = $db->getRow("select * from school_register where id='".$create_by_userid."'"); 
$iState = $db->getRow("select * from state where id='".$iSchool['state']."'");
$statename=$iState['title'];
$iAppraisalDetails=$db->getRow("select * from  appraisal_details where randomid='".$_GET['randomid']."'");
$_GET['teacher_id']=$iAppraisalDetails['teacher_id'];
$_GET['subject_id']=$iAppraisalDetails['subject_id'];
$_GET['session_id']=$iAppraisalDetails['session_id'];
$_GET['term_id']=$iAppraisalDetails['term_id'];
$_GET['month']=$iAppraisalDetails['month'];
$_GET['ddate']=$iAppraisalDetails['ddate'];				
				  
				  ?>
                    <div role="tabpanel" class="tab-pane active" id="home">
                      <div class="ab-1">
                        <div class="row">
                          <div class="col-md-12 col-xs-12">
                            <table style="width:100%;"> <tr><td align="right"><a class="btn btn-primary" style="color:#fff" href="<?php echo $FileName; ?>">Back</a></td></tr></table>
                            <table style="width:100%;">
                              <tr>
                                <td colspan="2" style="width: 120px;"><div>&nbsp;&nbsp;&nbsp; <img style="width:200px" src="../uploads/<?php echo $iSchool['logo']; ?>"> </div></td>
                                <td colspan="3"><p style="font-size: 35px;color: #2196F3;font-weight: bolder;text-align: center;font-family: sans-serif;margin-bottom: 0; margin-right:50px;"><?php echo $iSchool['name']; ?></p>
                                  <p style="font-size: 12px;font-weight:600;text-align:center;font-family: sans-serif;margin-top:0px; margin-right:50px;">MOTO:<?php echo $iSchool['moto']; ?></p>
                                  <p style="font-size: 12px;font-weight:600;text-align:center;font-family: sans-serif;margin-top:0px; margin-right:50px;"><?php echo $iSchool['location']; ?>, <?php echo $statename; ?></p>
                                  <p style="font-size: 15px;text-align:center;font-family: sans-serif;margin-top:30px;margin-bottom:0px; margin-right:50px;">REPORT SHEET FOR <?php echo $term_id['term']; ?> <?php echo $iSession['session']; ?> ACADEMIC SESSION</p>
                                  <br></td>
                                <td colspan="2" style="width: 120px;"><div>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; </div></td>
                              </tr>
                            </table>
                            <div class="row">
                              <div class="col-md-12 col-xs-12">
                                <div class="card-box table-responsive tablthisresponsive">
                                  <table class="table table-striped table-bordered teachedet" cellpadding="0" cellspacing="0" border="1">
                                    <tr>
                                      <td colspan="5">Teacher Name : <b><?php echo $db->getVal("select name from school_register where id='".$iAppraisalDetails['teacher_id']."'"); ?> </b></td>
                                    </tr>
                                    <tr>
                                      <td>Subject : <b><?php echo $db->getVal("select name from school_register where id='".$iAppraisalDetails['teacher_id']."'"); ?> </b></td>
                                      <td>Month : <b> <?php echo  date("F", strtotime($iAppraisalDetails['month']."/12/10")); ?> </b></td>
                                      <td>Date : <b><?php echo $iAppraisalDetails['ddate']; ?> </b></td>
                                      <td>Session : <b><?php echo $db->getVal("select session from school_session where id='".$iAppraisalDetails['session_id']."'"); ?> </b></td>
                                      <td>Term : <b><?php echo $db->getVal("select term from school_term where id='".$iAppraisalDetails['term_id']."'"); ?> </b></td>
                                    </tr>
                                  </table>
                                </div>
                              </div>
                            </div>
                            <h3 class="clsassmnt">CLASS ASSESSEMENT</h3>
                            <form action="" method="POST" enctype="multipart/form-data">
                              <div class="row">
                                <div class="col-md-12 col-xs-12">
                                  <div class="card-box table-responsive tablthisresponsive">
                                    <table class="table table-striped table-bordered" cellpadding="0" cellspacing="0" border="1">
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
                                          <td><?php echo $i ?></td>
                                          <td><?php  echo $iList['assessment'];   ?></td>
                                          <td><?php  echo $iList['mark'];   ?></td>
                                          <td><?php echo $iAppClassMark['week_1']; ?></td>
                                          <td><?php echo $iAppClassMark['week_2']; ?></td>
                                          <td><?php echo $iAppClassMark['week_3']; ?></td>
                                          <td><?php echo $iAppClassMark['week_4']; ?></td>
                                          <td><?php echo $iTotalMarks; ?></td>
                                        </tr>
                                        <?php } ?>
                                      </tbody>
                                    </table>
                                  </div>
                                </div>
                              </div>
                            </form>
                            <h3 class="clsassmnt">PUNCTUALITY</h3>
                            <form action="" method="POST" enctype="multipart/form-data">
                              <div class="row">
                                <div class="col-md-12 col-xs-12">
                                  <div class="card-box table-responsive tablthisresponsive">
                                    <table class="table table-striped table-bordered"   cellpadding="0" cellspacing="0" border="1">
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
                                          <td><?php echo $i ?></td>
                                          <td><?php  echo $iList['title'];   ?></td>
                                          <td><?php echo $iAppClassMark['week_1']; ?></td>
                                          <td><?php echo $iAppClassMark['week_2']; ?></td>
                                          <td><?php echo $iAppClassMark['week_3']; ?></td>
                                          <td><?php echo $iAppClassMark['week_4']; ?></td>
                                          <td><?php echo $iTotalMarks; ?></td>
                                        </tr>
                                        <?php } ?>
                                      </tbody>
                                    </table>
                                  </div>
                                </div>
                              </div>
                            </form>
                            <h3 class="clsassmnt">PERSONAL ASSESSMENT</h3>
                            <form action="" method="POST" enctype="multipart/form-data">
                              <div class="row">
                                <div class="col-md-12 col-xs-12">
                                  <div class="card-box table-responsive tablthisresponsive">
                                    <table class="table table-striped table-bordered"  cellpadding="0" cellspacing="0" border="1">
                                      <thead>
                                        <tr>
                                          <th>#</th>
                                          <th>Items</th>
                                          <th>Value</th>
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
                                          <td><?php echo $i ?></td>
                                          <td><?php  echo $iList['assessment'];   ?></td>
                                          <td><?php if($iAppClassMark['fieldvalue']=='1') { echo 'Fail'; }
                                                                                if($iAppClassMark['fieldvalue']=='2') { echo 'Pass'; }
                                                                                if($iAppClassMark['fieldvalue']=='3') { echo 'Good'; }
                                                                                if($iAppClassMark['fieldvalue']=='4') { echo 'Very Good'; }
                                                                      
                                                                       ?></td>
                                        </tr>
                                        <?php } ?>
                                      <input type="hidden" value="<?php echo $i; ?>" name="totalvalue" >
                                        </tbody>
                                      
                                    </table>
                                  </div>
                                </div>
                              </div>
                            </form>
                            <form action="" method="POST" enctype="multipart/form-data">
                              <div class="row">
                                <div class="col-md-12 col-xs-12">
                                  <table class="table table-striped table-bordered remarkremark">
                                    <tr>
                                      <td colspan="4" align="left">Remarks <?php echo $iAppraisalDetails['remarks']; ?></td>
                                    </tr>
                                    <tr>
                                      <td style="width:200px">Name of Evaluator </td>
                                      <td><?php echo $iAppraisalDetails['name_of_evalutor']; ?></td>
                                      <td style="width:150px">Date/Sign </td>
                                      <td><?php echo $iAppraisalDetails['date_sign']; ?></td>
                                    </tr>
                                    <tr>
                                      <td>Teachers Sign </td>
                                      <td><img src="../uploads/signuature/<?php echo $iAppraisalDetails['teacher_sign']; ?>"></td>
                                      <td>H/Teacher </td>
                                      <td><?php echo $iAppraisalDetails['head_teacher']; ?></td>
                                    </tr>
                                    <tr>
                                      <td>Principal Sign </td>
                                      <td><img src="../uploads/signuature/<?php echo $iAppraisalDetails['principal_sign']; ?>"></td>
                                      <td>Director's Sign </td>
                                      <td><img src="../uploads/signuature/<?php echo $iAppraisalDetails['director_sign']; ?>"></td>
                                    </tr>
                                  </table>
                                </div>
                              </div>
                            </form>
                          </div>
                        </div>
                      </div>
                    </div>
                    <?php }  else{ ?>
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
                                <th>Action</th>
                              </tr>
                            </thead>
                            <tbody>
                              <?php $i=0;
					$aryList = $db->getRows("select * from appraisal_details where create_by_userid='".$create_by_userid."' and teacher_id='".$_SESSION['userid']."' order by id desc");
					foreach($aryList as $iList) 
							{  $i=$i+1; ?>
                              <tr>
                                <td><?php echo $i ?></td>
                                <td><?php echo $db->getVal("select name from school_register where id='".$iList['teacher_id']."' "); ?></td>
                                <td><?php echo $db->getVal("select subject from  school_subject where create_by_userid='".$create_by_userid."' and id = '".$iList['subject_id']."'"); ?></td>
                                <td><?php echo $db->getVal("select session from  school_session where create_by_userid='".$create_by_userid."' and id = '".$iList['session_id']."'"); ?></td>
                                <td><?php echo $db->getVal("select term from  school_term where create_by_userid='".$create_by_userid."' and id = '".$iList['term_id']."'"); ?></td>
                                <td><?php    $monthNum = $iList['month'];	echo $monthName = date("F", mktime(0, 0, 0, $monthNum, 10)); ?></td>
                                <td><?php echo $iList['ddate'];?></td>
                                <td><a href="<?php echo $FileName; ?>?randomid=<?php echo $iList['randomid']; ?>&action=view" >View Detail</a></td>
                              </tr>
                              <?php } ?>
                            </tbody>
                          </table>
                        </div>
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
