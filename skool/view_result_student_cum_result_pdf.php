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

$student=$db->getRow("select * from manage_student where randomid='".$_GET['randomid']."' and create_by_userid='".$create_by_userid."' ");
$iclass=$db->getRow("select * from school_class where id='".$student['class']."' and create_by_userid='".$create_by_userid."' ");
$iAssesment=$db->getVal("select GROUP_CONCAT(id) from school_assessment where class_id='".$iclass['id']."' and create_by_userid='".$create_by_userid."' ");


?>
<?php 
	
	$aryList1=$db->getRows("select * from manage_student where class='".$currentClass['id']."' and session='".$_GET['session']."' and term_id='".$_GET['term_id']."' and create_by_userid='".$create_by_userid."'");
	foreach($aryList1 as $iiList)
	{
	 $b=$iiList['id'];
	 $iiSubjectsNew=$db->getRows("select * from school_subject where class_id='".$currentClass['id']."'");
	 foreach($iiSubjectsNew as $iiListNew)
	 { 
	   $iInputScoree=$db->getVal("select SUM(score) from input_score_class_teacher where assesment_id IN ($assesment) and student_id='".$iiList['id']."' and session_id='".$_GET['session']."' and term_id='".$_GET['term_id']."' and class_id='".$iiList['class']."' and subject_id='".$iiListNew['id']."'");
	   $iScoree = $iInputScoree; 
	    ${"dsp" . $iiList['id']}+= $iScoree;
	  } 
      $iiSum = ${"dsp" . $iiList['id']}; 
	   
	  $student_two["$b"] = $iiSum;
	}
	
	
	?>
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

foreach ($rankedScores as $studentwa => $data)
 {
	//$rank=$data['rank'];
 	//echo "<script>document.getElementById('$studentwa').innerHTML=$rank;</script>";
	$_SESSION["board_$studentwa"]=$data['rank'];
	
	
}
?>
	
	
<html>
<head>
  <title>Broad Sheet</title>
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
	   <p style="font-size: 17px; text-align:center;font-family: roboto;margin: 8px">STUDENT CUMMULATIVE RESULT</p>
	  
</td>


</tr>
 

</tbody>
</table>

  <table style="width:100%; text-align:center;">
<tbody>
<tr>
<?php
$studentName = $db->getRow("select * from manage_student where randomid='".$_GET['randomid']."' and create_by_userid='".$create_by_userid."'");
?>
<td>Nane: <?php echo $studentName['first_name'].' '.$studentName['last_name']; ?> </td>

<td>Session: <?php echo $db->getVal("select session from school_session where id = '" . $_GET['session'] . "' "); ?></td>

</tr>
</tbody>
</table>
	<table cellspacing="0"style="padding: 5px;    width: 100%;">
 <tbody style="
    font-family: sans-serif;">
  <tr style="height: 50px;">
    <td style="border: 1px solid #000000;">Subject</td>
	<?php 
	$iterm=$db->getRows("select * from school_term  where create_by_userid='".$create_by_userid."'");
	foreach($iterm as $iList) { 
	?>
	<td style="border:1px solid #000000;"><?php echo $iList['term']; ?></td>
	<?php } ?>
    <td style="border: 1px solid #000000;">Total</td>
    <td style="border: 1px solid #000000;">Average</td>
    <td style="border: 1px solid #000000;">Grade</td>
	<td style="border: 1px solid #000000;">Out of</td>
	<td style="border: 1px solid #000000;"> Class Avg.</td>
	
  </tr>
  <?php
		$i=0;
		$tStuden=0;
		$aryList11=$db->getRows("select * from 	school_subject where class_id='".$student['class']."'  and create_by_userid='".$create_by_userid."'");
		foreach($aryList11 as $iList)
		{
			$tStuden=$tStuden+1;
			?>
  <tr style="height: 35px;">
    <td style="border: 1px solid #000000;"><?php echo $iList['subject'];  ?>	</td>
   

		<?php $iSum=0;
		$totalterm=0;
		$iSubjectsNew=$db->getRows("select * from school_term   where create_by_userid='".$create_by_userid."'");
		foreach($iSubjectsNew as $iListNew) {
		$totalterm=$totalterm+1;
		$termstudent=$db->getRow("select * from manage_student where student_id='".$student['student_id']."' and session='".$_GET['session']."'and term_id='".$iListNew['id']."' and class='".$student['class']."' and create_by_userid='".$create_by_userid."' ");

		?>
   <td style="border: 1px solid #000000;">
	<?php
	$iInputScore=$db->getVal("select SUM(score) from input_score_class_teacher where assesment_id IN ($iAssesment) and student_id='".$termstudent['id']."' and session_id='".$_GET['session']."' and term_id='".$iListNew['id']."' and class_id='".$iclass['id']."' and subject_id='".$iList['id']."'");
	echo $iScore = $iInputScore; 
	?>
	</td>
	
	<?php  $iSum += $iScore; } ?>
	
	<td style="border: 1px solid #000000;">
		<?php echo $iSum;
		$student_two["$a"] = $iSum;  
		$classTotal+=$iSum;

		?>

	</td>
	
	<td style="border: 1px solid #000000;">
		<?php  $avg=$iSum/$totalterm;
		
		echo round($avg,2);
		?>
	</td>
	
	<td style="border: 1px solid #000000;">
		<?php
		echo  $gradding=$db->getVal("select grade from school_grade where create_by_userid='".$create_by_userid."' and maximum_number > ".$avg." or maximum_number = ".$avg."");
		?>
	</td>
	<td style="border: 1px solid #000000;">
	<?php echo $_GET['outof']; ?>
	</td>
	<td style="border: 1px solid #000000;">
	<?php 
	$classAvg=$db->getVal("SELECT AVG(score) AS classAvg from input_score_class_teacher where assesment_id IN ($iAssesment)  and session_id='".$_GET['session']."'  and class_id='".$iclass['id']."' and subject_id='".$iList['id']."'");
	$highLow[]=$classAvg;
	echo round($classAvg,2);
	?>
	</td>
	
  </tr>
<?php } ?>

  </tbody>
  </table>
  <table>

	    <tr>
				<td>
				<span>No. of Subjects: <?php echo $tStuden; ?></span>
				</td>
				
				<td>
				<span>Class Average:
				<?php  
                $classaAvg=$db->getVal("SELECT AVG(score) AS classAvg from input_score_class_teacher where assesment_id IN ($iAssesment)  and session_id='".$_GET['session']."'  and class_id='".$iclass['id']."' ");
                echo round($classaAvg,2); ?>
				</span>
				</td>
		</tr>
				
				<tr>
				<td>
				<span>Highest Average in Class:<?php $mmm= max($highLow); echo round($mmm,2);   ?></span>
				</td>
				
				<td>
				<span>Lowest Average in Class:<?php $msds= min($highLow); echo round($msds,2); ?></span>
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
//$dompdf->stream("Broad_sheet");
?>