<?php
namespace Dompdf;
require_once ('dompdf_New/autoload.inc.php');
ob_start();

include('../config.php');
include('inc.session-create.php');

$iupdatedetails = $db->getRow("select * from school_register where id='".$_SESSION['userid']."'"); 
$stae = $db->getRow("select * from state where id='".$iupdatedetails['state']."'");
$statename=$stae['title'];

$currentClass=$db->getRow("select * from school_class  where randomid='".$_GET['randomid']."' and create_by_userid='".$create_by_userid."'");

$subjectid=$db->getRow("select * from school_subject where id='".$_GET['subject']."' and create_by_userid='".$create_by_userid."'");
?>
<?php 
$assesments=$_GET['assesments'];
$allAssesment=explode('-',$assesments);
$allAssesments=implode(',',$againAss);

$iGetPositionAll=$db->getRows("select * from manage_student  where class='".$currentClass['id']."' and session='".$_GET['session']."' and term_id='".$_GET['term_id']."'  and create_by_userid='".$create_by_userid."'order by first_name asc");
foreach($iGetPositionAll as $iList)
{
	foreach($allAssesment as $Val) 
	{ 
		$scoreNew=$db->getRow("select * from input_score_class_teacher where assesment_id='".$Val."' and student_id='".$iList['id']."' and subject_id='".$_GET['subject']."' and create_by_userid='".$create_by_userid."'");
		
		$sumNew=$scoreNew['score'];
		${"e" . $iList['id']}+=$sumNew;
	}
	$grandTotalNew = ${"e" . $iList['id']};
	
	$a=$iList['id'];
	$student_two_New["$a"] = $grandTotalNew;  
}

function setPositionNew($standings) {
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

$rankedScoresNew = setPositionNew($student_two_New);

foreach ($rankedScoresNew as $studentwa => $data) {
	
	$rank=$data['rank'];
	
	$_SESSION["$studentwa"] = $rank;
}
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
				<p style="font-size: 17px; text-align:center;font-family: roboto;margin: 8px;font-weight: 500;">
					<?php echo $iupdatedetails['name']; ?>
				</p> 
				<p style="font-size: 15px; text-align:center;font-family: roboto;margin: 0;padding: 3px;">
					Website:<?php echo $iupdatedetails['website']; ?> 
				</p>     
				<p style="font-size: 17px; text-align:center;font-family: roboto;margin: 0;">
					<?php echo $iupdatedetails['location']; ?>, <?php echo $statename; ?>.
				</p>
				<p style="font-size: 17px; text-align:center;font-family: roboto;margin: 8px">SUBJECT REPORT SHEET</p>
			</td>
		</tr>
	</tbody>
</table>
<table style="width:100%; text-align:center;">
	<tbody>
		<tr>
			<td>Class:<?php echo $currentClass['name']; ?></td>
			<td>Subject:<?php echo $subjectid['subject']; ?></td>
			<td>Session:<?php echo $db->getVal("select session from school_session where id = '" . $_GET['session'] . "' "); ?> ,<?php echo $db->getVal("select term from school_term where id = '" . $_GET['term_id'] . "' "); ?> 
			</td>
		</tr>
	</tbody>
</table>
<table cellspacing="0"style="padding: 5px;    width: 100%;">
	<tbody style="font-family: sans-serif;">
		<tr style="height: 50px;">
			<td style="border: 1px solid #000000;">S/No</td>
			<td style="border: 1px solid #000000;">Student ID</td>
			<td style="border: 1px solid #000000;">First Name</td>
			<td style="border: 1px solid #000000;">Last Name</td>
			<td style="border: 1px solid #000000;">Other Name</td>
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
    <td style="  border: 1px solid #000000;">Grade </td>
    <td style="  border: 1px solid #000000;">Position </td>
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
	<td style="border: 1px solid #000000;"><?php echo $score['score']; ?></td>
	<?php $sum=$score['score'];
	${"d" . $iList['id']}+=$sum;
	?>									  

	<?php }
	$grandTotal = ${"d" . $iList['id']};
	?>
	<td style="border: 1px solid #000000;">
	<?php
	$a=$iList['id'];
	$student_two["$a"] = $grandTotal;  

	echo $grandTotal;
	$classTotal+=$grandTotal;
	$highLow[]=$grandTotal;

	?>
	</td>


	<td style="border: 1px solid #000000;">
	<?php //echo $db->getVal("select grade from school_grade where maximum_number > ".$grandTotal." or maximum_number = ".$grandTotal."");
	?>
    <?php 	echo $db->getVal("select grade from school_grade where create_by_userid='".$create_by_userid."'  and minimum_number <= ".$grandTotal." and maximum_number >= ".$grandTotal."");?>
	</td>
	<td style="border: 1px solid #000000;" id="<?php echo $a; ?>">
	<?php echo $_SESSION["$a"]; ?>	
	</td>
</tr>
	<?php unset($_SESSION["$a"]); } ?>
</tbody>
</table>
<table>
	<tr>
		<td>
			<span>No. of Students: <?php echo $tStuden; ?></span>
		</td>
		<td>
			<span>Class Average: <?php  $classAvg=$classTotal/$tStuden; echo round($classAvg,2); ?></span>
		</td>
	</tr>
	<tr>
		<td>
			<span>Highest Average in Class:<?php echo max($highLow); ?></span>
		</td>
		<td>
			<span>Lowest Average in Class:<?php echo min($highLow); ?></span>
		</td>
	</tr>
</table>
</body>
</html>
<?php

$html = ob_get_clean();
$dompdf = new Dompdf();

use Dompdf\Options;
$options = new Options();
$options->set('isJavascriptEnabled', TRUE);
//$dompdf->set_option( 'isJavascriptEnabled' , true );
$dompdf = new Dompdf($options);


$dompdf->load_html($html);
$dompdf->setPaper('A4', 'landscape');
$dompdf->render();
//For view
$dompdf->stream("",array("Attachment" => false));
//for download
//$dompdf->stream("Subject_report_sheet");
?>