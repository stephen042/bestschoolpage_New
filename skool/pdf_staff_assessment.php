<?php
namespace Dompdf;
require_once ('dompdf_New/autoload.inc.php');
ob_start();

include('../config.php');

include('inc.session-create.php');

$iSchool = $db->getRow("select * from school_register where id='".$create_by_userid."'"); 
$iState = $db->getRow("select * from state where id='".$iSchool['state']."'");
$statename=$iState['title'];

$iAppraisalDetails=$db->getRow("select * from  appraisal_details where teacher_id='".$_GET['teacher_id']."' and subject_id='".$_GET['subject_id']."' and session_id='".$_GET['session_id']."' and term_id='".$_GET['term_id']."' and month='".$_GET['month']."' and ddate='".$_GET['ddate']."' order by id desc");				

?>
<html>
<style>
#watermark {
	position: absolute;
	bottom: 10%;
	z-index: -1000;
	opacity: 0.1;
}
table { width:100%; }
table tr td{
	text-align:center;
	font-size: 25px;
	 }
table tr th{
	text-align:center;
	font-size: 27px;
	 } 
h3 {     font-size: 25px; } 
table.remarkremark tr td { text-align:left;     padding: 25px 5px; }
table.teachedet tr td { text-align:left;     padding: 10px 2px; }
</style>
<body style="border: 1px solid; background-repeat: no-repeat;background-size: cover; background-position:center; opacity:1;    padding: 10px;">
<div id="watermark"> <img src="../uploads/<?php echo $iSchool['logo']; ?>" height="100%" width="100%" /> </div>
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
        <tr><td colspan="5">Teacher Name : <b><?php echo $db->getVal("select name from school_register where id='".$iAppraisalDetails['teacher_id']."'"); ?> </b></td></tr>
        <tr><td>Subject : <b><?php echo $db->getVal("select name from school_register where id='".$iAppraisalDetails['teacher_id']."'"); ?> </b></td>
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
          <td colspan="4" align="left">Remarks
            <?php echo $iAppraisalDetails['remarks']; ?></td>
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
</body>
</html>
<?php
$html = ob_get_clean();
$dompdf = new Dompdf();

use Dompdf\Options;
$options = new Options();
$options->set('isJavascriptEnabled', TRUE);
$dompdf = new Dompdf($options);

if($_SERVER['HTTP_HOST']=='localhost' || $_SERVER['HTTP_HOST']=='127.0.0.1')
	{
	 	echo $html;
	 	exit;	
		
	}

$dompdf->load_html($html);

$customPaper = array(0,0,1000,1400);
$dompdf->set_paper($customPaper);
$dompdf->render();
$dompdf->stream("",array("Attachment" => false));
?>