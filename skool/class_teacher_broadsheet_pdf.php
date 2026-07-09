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
$getAssessment=$_GET['assesment'];
$againAss=explode('-',$getAssessment);
$assesment=implode(',',$againAss);
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
	   <p style="font-size: 17px; text-align:center;font-family: roboto;margin: 8px">BROAD SHEET REPORT</p>
	  
</td>


</tr>
 

</tbody>
</table>

  <table style="width:100%; text-align:center;">
<tbody>
<tr>

<td>Class:<?php echo $currentClass['name']; ?>  </td>

<td>Session: <?php echo $db->getVal("select session from school_session where id = '" . $_GET['session'] . "' "); ?>, <?php echo $db->getVal("select term from school_term where id = '" . $_GET['term_id'] . "' "); ?></td>

</tr>
</tbody>
</table>
	<table cellspacing="0"style="padding: 5px;    width: 100%;">
 <tbody style="
    font-family: sans-serif;">
  <tr style="height: 50px;">
    <td style="  border: 1px solid #000000;">S/NO</td>
    <td style="  border: 1px solid #000000;">Student Id</td>
    <td style="  border: 1px solid #000000;">First  Name	</td>
    <td style="  border: 1px solid #000000;">Last Name	</td>
	<?php 
	$iSubjects=$db->getRows("select * from school_subject where  class_id='".$currentClass['id']."'");
	$iCountSubject=count($iSubjects);
	$toalSub=0;
	foreach($iSubjects as $iList) { 
	$toalSub=$toalSub+1;
	?>
	<td style="  border: 1px solid #000000;">
	<?php echo $iList['subject']; ?>
	</td>
	<?php } ?>
	<td style="  border: 1px solid #000000;">Total </td>
    <td style="  border: 1px solid #000000;">Average	 </td>
    <td style="  border: 1px solid #000000;">Grade </td> 
	<td style="  border: 1px solid #000000;">Position </td>
   
  </tr>
 <?php
		$i=0;
		$tStuden=0;
		$aryList11=$db->getRows("select * from manage_student where class='".$currentClass['id']."' and session='".$_GET['session']."' and term_id='".$_GET['term_id']."' and create_by_userid='".$create_by_userid."'");
		foreach($aryList11 as $iList)
{
			$tStuden=$tStuden+1;
	?>
  <tr style="height: 35px;">
    <td style="border: 1px solid #000000;"><?php echo $tStuden; ?></td>
    <td style="border: 1px solid #000000;">
	<?php echo $iList['student_id']; $a=$iList['id']; ?>
	</td>
	<td style="border: 1px solid #000000;">
	<?php echo $iList['first_name']; ?>
	</td>
	<td style="border: 1px solid #000000;">
	<?php echo $iList['last_name'];  ?>
	</td>
	<?php $iSum=0;
	$iSubjectsNew=$db->getRows("select * from school_subject where  class_id='".$currentClass['id']."'");
	foreach($iSubjectsNew as $iListNew) { ?>
	<td style="border: 1px solid #000000;">
	<?php

	$iInputScore=$db->getVal("select SUM(score) from input_score_class_teacher where assesment_id IN ($assesment) and student_id='".$iList['id']."' and session_id='".$_GET['session']."' and term_id='".$_GET['term_id']."' and class_id='".$currentClass['id']."' and subject_id='".$iListNew['id']."'");
	echo $iScore = $iInputScore; 
	?>
	</td>
	<?php  $iSum += $iScore; } ?>
	<td style="border: 1px solid #000000;">
	<?php echo $iSum;
	$classTotal+=$iSum;

	?>
	</td>
	<td style="border: 1px solid #000000;">
	<?php 
	 $avg=$iSum/$toalSub;
	 echo round($avg,2);
	$highLow[]=$avg;
	?>
	</td>
	<td style="border: 1px solid #000000;">
	<?php
//	echo  $db->getVal("select grade from school_grade where create_by_userid='".$create_by_userid."' and maximum_number > ".$avg." or maximum_number = ".$avg.""); 
	?>
    <?php echo $gradding=$db->getVal("select grade from school_grade where create_by_userid='".$create_by_userid."'  and minimum_number <= ".$avg." and maximum_number >= ".$avg."");?>
	</td>
	<td style="border: 1px solid #000000;" id=<?php echo $a; ?>>
	<?php echo $_SESSION["board_$a"]; unset($_SESSION["board_$a"]); ?>
	</td>

  </tr>
<?php } ?>

  </tbody>
  </table>
  <table>

	    <tr>
				<td>
				<span>No. of Students: <?php echo $tStuden; ?></span>
				</td>
				
				<td>
				<span>Class Average: <?php echo $classAvg=$classTotal/$tStuden; ?></span>
				</td>
				</tr>
				
				<tr>
				<td>
				<span>Highest Average in Class:<?php  echo round(max($highLow),2); ?></span>
				</td>
				
				<td>
				<span>Lowest Average in Class:<?php  echo round(min($highLow),2); ?></span>
				</td>
				</tr>
            

</table>
</body>
</html>
<?php
$html = ob_get_clean();
$dompdf = new Dompdf();
$customPaper = array(0,0,1200,1000);
$dompdf->set_paper($customPaper);
$dompdf->load_html($html);
$dompdf->render();
//For view
$dompdf->stream("",array("Attachment" => false));
// for download
//$dompdf->stream("Broad_sheet");
?>