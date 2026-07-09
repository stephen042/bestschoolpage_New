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
$classDetail=$db->getRow("select * from school_class where randomid='".$_GET['randomid']."' and create_by_userid='".$create_by_userid."'");

//-----------------------
	$iaryListss = $db->getRows("select * from manage_student where class = '" .$classDetail['id']. "'and session= '" .$_GET['session']. "' and term_id= '" .$_GET['term_id']. "'  and create_by_userid='".$create_by_userid."' order by first_name asc");
	
	foreach ($iaryListss as $iaryStudent)
	{	
	    $schoolClassDetails=$db->getRows("select * from school_subject where class_id='".$classDetail['id']."' and create_by_userid='".$create_by_userid."'");
		foreach($schoolClassDetails as $iiListDetail)	
	     {
	       $totalscores = $db->getRow("select SUM(score) as total_score from input_score_class_teacher where class_id='".$classDetail['id']."' and subject_id='".$iiListDetail['id']."' and student_id = '" .$iaryStudent['id']. "' and session_id='".$_GET['session']."' and term_id='".$_GET['term_id']."' and create_by_userid='".$create_by_userid."'");
	        $itotaslscore=$totalscores['total_score'];
	           ${"dsp" . $iaryStudent['id']}+=$itotaslscore;
	     }
	           $igrandTotal = ${"dsp" . $iaryStudent['id']};
	             $a=$iaryStudent['id'];
	            $student_two["$a"] = $igrandTotal;
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

foreach ($rankedScores as $studentwa => $data) {
	 

	 $_SESSION["cum_board_$studentwa"]=$data['rank'];

}
?>		
<html>
<head>
  <title>CUMULATIVE BROAD SHEET REPORT</title>
  </head>
 
 <body>
   <table style="width:100%;">
<tbody>
<tr>
<td colspan="1" style="width:2%"> 
  <span> 
    <img style="width:90px; height:90px;" src="../uploads/<?php echo $iupdatedetails['logo']; ?>">
  </span>
</td>

<td> 
       <p style="font-size: 17px; text-align:center;font-family: roboto;margin: 8px;font-weight: 500;"><?php echo $iupdatedetails['name']; ?></p> 
       <p style="font-size: 15px; text-align:center;font-family: roboto;margin: 0;    padding: 3px;">Website:<?php echo $iupdatedetails['website']; ?>   </p>     
       <p style="font-size: 17px; text-align:center;font-family: roboto;margin: 0;"><?php echo $iupdatedetails['location']; ?>, <?php echo $statename; ?>.</p>
	   <p style="font-size: 17px; text-align:center;font-family: roboto;margin: 8px">CUMULATIVE BROAD SHEET REPORT</p>
	  
</td>


</tr>
 

</tbody></table>





  <table style="width:100%; text-align:center;">
<tbody><tr>


<td>Class:<?php echo $classDetail['name']; ?></td>

<td>Session: <?php echo $db->getVal("select session from school_session where id = '" . $_GET['session'] . "' "); ?>,
<?php echo $db->getVal("select term from school_term where id = '" . $_GET['term_id'] . "' "); ?>
 </td>


</tr>
</tbody>
</table>

      
	<table cellspacing="0" ;="" style="padding: 5px;    width: 100%;">
 <tbody style="
    font-family: sans-serif;">
  <tr style="height: 50px;">
    <td style="border: 1px solid #000000;">S/NO</td>
    <td style="border: 1px solid #000000;">Student ID</td>
    <td style="border: 1px solid #000000;">Name</td>
  
   <?php  $schoolClassDetail=$db->getRows("select * from school_subject where class_id='".$classDetail['id']."' and create_by_userid='".$create_by_userid."'");
                              $i=0;
                        foreach($schoolClassDetail as $iListDetail)	
						{
							 $i = $i + 1;
							 $subid.=$iListDetail['id'].',';
						?>
                    <td style="border: 1px solid #000000;"><?php echo $iListDetail['subject']; ?></td>
					
<?php } ?>
    <td style="  border: 1px solid #000000;">No. of Sub. </td>
    <td style="  border: 1px solid #000000;">Total Score </td>
    <td style="  border: 1px solid #000000;">Average </td>
    <td style="  border: 1px solid #000000;">Grade	 </td>
    <td style="  border: 1px solid #000000;">Position	 </td>
   
  </tr>
		<?php

		$aryListss = $db->getRows("select * from manage_student where class = '" .$classDetail['id']. "'and session='".$_GET['session']."' and term_id='".$_GET['term_id']."' and create_by_userid='".$create_by_userid."'order by first_name asc");
		$tStuden=0;
		$i=0;
		foreach ($aryListss as $aryStudent)
		{
		$i++;	
		$tStuden=$tStuden+1;
		?>

  <tr style="height: 35px;">
    <td style="  border: 1px solid #000000;"><?php echo $i; ?></td>
    <td style="  border: 1px solid #000000;"><?php echo $aryStudent['student_id'];?></td>
    <td style="  border: 1px solid #000000;"><?php echo $aryStudent['first_name'].' '.$aryStudent['last_name'];?></td>
	<?php  $schoolClassDetail=$db->getRows("select * from school_subject where class_id='".$classDetail['id']."' and create_by_userid='".$create_by_userid."'");
	$iTotalSub=0;
	foreach($schoolClassDetail as $iListDetail)	
	{
	$iTotalSub = $iTotalSub + 1;

	?>
    <td style="  border: 1px solid #000000;">
	<?php $totalscore = $db->getRow("select SUM(score) as total_score from input_score_class_teacher where subject_id='".$iListDetail['id']."' and student_id = '" .$aryStudent['id']. "' and  session_id='".$_GET['session']."' and term_id='".$_GET['term_id']."' and create_by_userid='".$create_by_userid."'");
	echo $totaslscore=$totalscore['total_score'];
	${"d" . $aryStudent['id']}+=$totaslscore;
	?>
	
	</td>

	<?php }
	$grandTotal = ${"d" . $aryStudent['id']};
	$a=$aryStudent['id'];
	?>
	
	
    <td style="  border: 1px solid #000000;"> <?php echo $iTotalSub; ?></td>
    <td style="  border: 1px solid #000000;"> <?php echo $grandTotal; ?></td>
    <td style="  border: 1px solid #000000;">
	<?php  $avg= $grandTotal/$iTotalSub;
	echo round($avg,2);
	$classTotal+=$avg; 
	$highLow[]=$avg;

	?>
	</td>
    <td style="  border: 1px solid #000000;">
	<?php  //echo  $db->getVal("select grade from school_grade where create_by_userid='".$create_by_userid."' and maximum_number > ".$avg." or maximum_number = ".$avg.""); ?>
     <?php 	echo $db->getVal("select grade from school_grade where create_by_userid='".$create_by_userid."'  and minimum_number <= ".$avg." and maximum_number >= ".$avg."");?>
	</td>
    <td style="border: 1px solid #000000;">
	<?php echo $_SESSION["cum_board_$a"]; unset($_SESSION["cum_board_$a"]); ?>
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
				<span>Class Average: <?php  $classAvg=$classTotal/$tStuden; echo round($classAvg,2);?></span>
				</td>
				</tr>
				
				<tr>
				<td>
				<span>Highest Average in Class:<?php echo round(max($highLow),2); ?></span>
				</td>
				
				<td>
				<span>Lowest Average in Class:<?php echo round(min($highLow),2); ?></span>
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
//$dompdf->stream("cummulative_broad_sheet");
?>