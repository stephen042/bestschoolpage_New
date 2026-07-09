<?php
namespace Dompdf;
require_once ('dompdf_New/autoload.inc.php');
ob_start();

include('../config.php');
include('inc.session-create.php');

$iupdatedetails = $db->getRow("select * from  school_register where id='".$_SESSION['userid']."'"); 
$stae = $db->getRow("select * from state where id='".$iupdatedetails['state']."'");
$statename=$stae['title'];
//--------------------------
$currentClass=$db->getRow("select * from school_class  where randomid='".$_GET['randomid']."' and create_by_userid='".$create_by_userid."'");

$subjectid=$db->getRow("select * from school_subject where id='".$_GET['subject']."' and create_by_userid='".$create_by_userid."'");

?>
<?php 
$assesments=$_GET['assesments'];
$allAssesment=explode('-',$assesments);
$allAssesments=implode(',',$againAss);
?>
<html>
<head>
  <title>SUBJECT REPORT SHEET</title>
  </head>
<body>
   <table style="width:100%;">
<tbody>
<tr>
<td colspan="3" style="width:7%;"> 
 
    <img style="width:90px; height:90px;" src="../uploads/<?php echo $iupdatedetails['logo']; ?>">
  
</td>
<td> 
       <p style="font-size: 17px; text-align:center;font-family: roboto;margin: 8px;font-weight: 500;"><?php echo $iupdatedetails['name']; ?></p> 
       <p style="font-size: 15px; text-align:center;font-family: roboto;margin: 0;    padding: 3px;">Website:<?php echo $iupdatedetails['website']; ?> </p>     
       <p style="font-size: 17px; text-align:center;font-family: roboto;margin: 0;"><?php echo $iupdatedetails['location']; ?>, <?php echo $statename; ?>.</p>
	   <p style="font-size: 17px; text-align:center;font-family: roboto;margin: 8px">SUBJECT REPORT SHEET</p>
	  
</td>


</tr>
 

</tbody>
</table>

  <table style="width:100%; text-align:center;">
<tbody>
<tr>


<td>Class:<?php echo $currentClass['name']; ?> </td>
<td>Subject:<?php echo $subjectid['subject']; ?>  </td>
<td>Session: <?php echo $db->getVal("select session from school_session where id = '" . $currentClass['session_id'] . "' "); ?> </td>


</tr>
</tbody>
</table>
	<table cellspacing="0"style="padding: 5px;    width: 100%;">
 <tbody style="
    font-family: sans-serif;">
  <tr style="height: 50px;">
    <td style="border: 1px solid #000000;">S/No</td>
    <td style="  border: 1px solid #000000;">Student ID</td>
    <td style="  border: 1px solid #000000;">First Name</td>
    <td style="  border: 1px solid #000000;">Last Name</td>
    <td style="  border: 1px solid #000000;">Other Name </td>
	<?php
	foreach($allAssesment as $Val) 
	{
	?>
	<td style="border: 1px solid #000000;">
	<?php
	echo $db->getVal("select assesment from school_assessment where id='".$Val."'").' '.$db->getVal("select percentage from score_entry_time_frame where assesment_id='".$Val."'"); 
	?>
	</td>
	<?php } ?>
    <td style="  border: 1px solid #000000;">Total </td>
    
	</tr>


	<?php 
	
	$tStuden=0;
	$aryList11=$db->getRows("select * from manage_student  where class='".$currentClass['id']."' and session='".$_GET['session']."' and term_id='".$_GET['term_id']."'  and create_by_userid='".$create_by_userid."'order by first_name asc");
   	$assesment=$db->getRow("select * from input_score_class_teacher  where assesment_id	='".$assesmentId['id']."' and create_by_userid='".$create_by_userid."'"); 
    foreach($aryList11 as $iList)
	{
	
	$tStuden=$tStuden+1;
	?>
	<tr  style="height: 35px;">
	<td style="border: 1px solid #000000;">
	<?php echo $tStuden; ?></td>
	<td style="border: 1px solid #000000;">
	<?php echo $iList['student_id']; ?>
	</td>


	<td style="border: 1px solid #000000;"><?php echo $iList['first_name']; ?></td>
	<td style="border: 1px solid #000000;"><?php echo $iList['last_name']; ?></td>
	<td style="border: 1px solid #000000;"><?php echo $iList['other_name']; ?></td>
	<?php


	foreach($allAssesment as $Val) 
	{ 
	$score=$db->getRow("select * from input_score_class_teacher where assesment_id='".$Val."' and student_id='".$iList['id']."' and subject_id='".$_GET['subject']."' and create_by_userid='".$create_by_userid."'");

	?>
	<td style="border: 1px solid #000000;">	</td>
										  

	<?php } ?>
	<td style="border: 1px solid #000000;">
	</td>




	</tr>

	<?php   } ?>
<?php

function setPosition($standings) {
    $rankings = array();
    arsort($standings);
    $rank = 1;
    $tie_rank = 0;
    $prev_score = -1;

    foreach ($standings as $name => $score) {
        if ($score != $prev_score) { 
            $count = 0;
            $prev_score = $score;
            $rankings[$name] = array('score' => $score, 'rank' => $rank);
        } else { 
            $prev_score = $score;
            if ($count++ == 0) {
                $tie_rank = $rank - 1;
            }
            $rankings[$name] = array('score' => $score, 'rank' => $tie_rank);
        }
        $rank++;
    }
    return $rankings;
}


$rankedScores = setPosition($student_two);

foreach ($rankedScores as $studentwa => $data) {
	 if($data['rank']=='1')
	{ $isubss="st"; }
	if($data['rank']=='2')
	{ $isubss="nd"; }
	if($data['rank']=='3')
	{ $isubss="rd"; }
	if($data['rank']!='1' and  $data['rank']!='2' and $data['rank']!='3' )
	{ $isubss="th"; }
	$rank=$data['rank'];
  
	
	echo "<script>document.getElementById('$studentwa').innerHTML=$rank;</script>";
	
}
?>

  


  </tbody>
  </table>
  
<table>

	    <tr>
				<td>
				<span>No. of Students: <?php echo $tStuden; ?></span>
				</td>
				
				<td>
				<span>Class Average: </span>
				</td>
				</tr>
				
				<tr>
				<td>
				<span>Highest Average in Class:</span>
				</td>
				
				<td>
				<span>Lowest Average in Class:</span>
				</td>
				</tr>
            

</table>
</body>
</html>
<?php
$html = ob_get_clean();
$dompdf = new Dompdf();

$dompdf->setPaper('A4', 'landscape');
$dompdf->load_html($html);
$dompdf->render();
//For view
$dompdf->stream("",array("Attachment" => false));
// for download
//$dompdf->stream("cummulative_broad_sheet");
?>