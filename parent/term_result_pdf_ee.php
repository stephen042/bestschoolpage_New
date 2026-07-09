<?php
namespace Dompdf;
require_once ('dompdf_New/autoload.inc.php');
ob_start();
include('../config.php');
include('inc.session-create.php');

$iParent = $db->getRow("select * from school_register where id='".$_SESSION['userid']."'"); 

$iSchool = $db->getRow("select * from school_register where id='".$iParent['create_by_userid']."'"); 

$iState = $db->getRow("select * from state where id='".$iSchool['state']."'");
$statename=$iState['title'];

$iStudent=$db->getRow("select * from manage_student where randomid='".$_GET['randomid']."'");

$iSession=$db->getRow("select * from school_session where id='".$iStudent['session']."'");

$iClass=$db->getRow("select * from school_class where id='".$iStudent['class']."'");

$assesmentALL=$db->getVal("select GROUP_CONCAT(assesment_id) from score_entry_time_frame where create_by_userid='".$iParent['create_by_userid']."' and session='".$iStudent['session']."' order by id desc "); 
										
$totalAssesment=explode(',',$assesmentALL);

$iCountAsses = count($totalAssesment);
$max=$db->getRow("SELECT MAX(total) AS maximum FROM result_total where session_id='".$iStudent['session']."' and class_id='".$iStudent['class']."'");

$min=$db->getRow("SELECT MIN(total) AS minimum FROM result_total where session_id='".$iStudent['session']."' and class_id='".$iStudent['class']."'");


$iGetPositionAll=$db->getRows("select * from school_subject where class_id = '" .$iStudent['class']. "' and create_by_userid='".$iParent['create_by_userid']."'");

$iCountSubject = count($iGetPositionAll);

foreach($iGetPositionAll as $iList)
{
	foreach($totalAssesment as $Val) 
	{ 
		$scoreNew=$db->getRow("select * from input_score_class_teacher where assesment_id='".$Val."' and student_id='".$iStudent['id']."' and subject_id='".$iList['id']."' and create_by_userid='".$iParent['create_by_userid']."'");
	
		$sumNew=$scoreNew['score'];
		${"e" . $iList['id']}+=$sumNew;
	}
	$grandTotalNew = ${"e" . $iList['id']};
	
	$a=$iList['id'];
	$student_two_New["$a"] = $grandTotalNew;  
	
	$iAverage["$a"] = round($grandTotalNew/$iCountAsses,2);
	
	$iFinalScore += $grandTotalNew;
}

foreach($iAverage as $Key=>$Val)
{
	$_SESSION["Low".$Key] = $Val;
}

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

$rankedScoresNew = setPosition($student_two_New);

foreach ($rankedScoresNew as $studentwa => $data) {
	if($data['rank']=='1')
	{ $isubss="st"; }
	if($data['rank']=='2')
	{ $isubss="nd"; }
	if($data['rank']=='3')
	{ $isubss="rd"; }
	if($data['rank']!='1' and  $data['rank']!='2' and $data['rank']!='3' )
	{ $isubss="th"; }
	$rank=$data['rank'];
	
	$_SESSION["$studentwa"] = $rank;
}		
?>
<html>
<style>
   #watermark {
               position: absolute;
			    bottom:   10%;
                
				
				z-index:  -1000;
				opacity:0.1;
				
            }
			 * { font-family: DejaVu Sans, sans-serif; }
			</style>
			<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>

<body style="border: 1px solid; background-repeat: no-repeat;background-size: cover; background-position:center; opacity:1;">
<div id="watermark">
            <img src="../uploads/<?php echo $iSchool['logo']; ?>" height="100%" width="100%" />
        </div>

<table style="width:100%;">
	<tr>
		<td colspan="2" style="width: 120px;"> 
			<div>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 
				<img style="width:100px" src="../uploads/<?php echo $iSchool['logo']; ?>">
			</div>
		</td>
		<td colspan="3"> 
			<p style="font-size: 22px;color: #2196F3;font-weight: bolder;text-align: center;font-family: sans-serif;margin-bottom: 0;"><?php echo $iSchool['name']; ?></p> 
			<p style="font-size: 22px;color: #2196F3;font-weight: bolder;text-align: center;font-family: sans-serif;margin-top:0px;margin-bottom:0px;">STAFF SECONDARY SCHOOL </p>     
			<p style="font-size: 12px;font-weight:600;text-align:center;font-family: sans-serif;margin-top:0px;">Website:<?php echo $iSchool['website']; ?></p>
			<p style="font-size: 12px;font-weight:600;text-align:center;font-family: sans-serif;margin-top:0px;"><?php echo $iSchool['location']; ?>, <?php echo $statename; ?></p>
			<p style="font-size: 15px;text-align:center;font-family: sans-serif;margin-top:30px;margin-bottom:0px;">REPORT SHEET FOR <?php echo $iSession['session']; ?> ACADEMIC SESSION</p>
		   <br>
		</td>
	</tr>
	
	</table>
	
	<table cellspacing="0"; style="width: 100%;">
	<tbody style="font-family: sans-serif;">
		<tr>
			<td style="width:100%;" >
				<table style="width:100%;">
					<tr>
					
					    <td style=""><b>NAME:</b></td>
						<td style=""><?php echo $iStudent['first_name'].' '.$iStudent['last_name']; ?></td>
						<td style=""><b>Final Grade:</b></td>
					<?php	$ggrade=$iFinalScore/$iCountSubject; ?>
						<td style=""><?php echo $graddingg=$db->getVal("select grade from school_grade where maximum_number > ".$ggrade." or maximum_number = ".$ggrade."");?> </td>
						<td style=""></td>
						<td style=""></td>
					
					</tr>
					<tr>
						<td style=""><b>Class:</b></td>
						<td style=""><?php echo $iClass['name']; ?></td>
						<td style=""><b>Final Position:</b></td>
						<td style=""><?php $IDD=$iStudent['id'];
						echo $_SESSION["$IDD"]; ?></td>
					</tr>
					
					<tr><td></td></tr>
					<tr>
						<td style=""><b></b></td>
						<td style=""></td>
						<td style=""><b>Total Score:</b></td>
						<td style=""><?php echo $iFinalScore; ?></td>
						<td style=""></td>
						<td style=""></td>
					</tr>
					<tr>
						<td style=""><b>Session:</b></td>
						<td style=""><?php echo $iSession['session']; ?></td>
						<td style=""><b>Final Average:</b></td>
						<td style=""><?php echo round($iFinalScore/$iCountSubject,2); ?></td>
						
					</tr>
					<tr>
						<td style=""><b>Term:</b></td>
						<td style=""><?php echo $iSession['session']; ?></td>
						<!--<td style=""><b>No. in Class:</b></td>
						<td style="">54</td>-->
						
					</tr>
				<!--	<tr>
						
						<td style=""><b>Highest Ave. in Class:</b></td>
						<td style="">12<?php //echo ceil($max/$tSub); ?></td>
						<td style=""><b>Lowest Ave. in Class:</b></td>
						<td style="">14<?php //echo ceil($max/$tSub); ?></td>
					</tr>-->
					
				</table>
			</td>
		</tr>
	</tbody>	
	</table>

	<table cellspacing="0"; style="width: 100%;">
	<tbody style="font-size: 18px;font-weight: bolder;font-family: sans-serif;">
	
		<tr style="height: 50px;">
			<td style="border: 1px solid #000000;text-align: center;">SUBJECT</td>
			<?php
			foreach($totalAssesment as $Val) 
			{
			?>
			<td style="border: 1px solid #000000;text-align: center;"><?php echo $db->getVal("select assesment from school_assessment where id='".$Val."'"); ?> <br>(<?php echo $db->getVal("select percentage from score_entry_time_frame where assesment_id='".$Val."'") ?>)</td>
			<?php } ?>
			<td style="border: 1px solid #000000;text-align: center;">TOTAL<br>(100%)</td>
			<td style="border: 1px solid #000000;text-align: center;">GRD</td>
			<td style="border: 1px solid #000000;text-align: center;">POS</td>
			<td style="border: 1px solid #000000;text-align: center;">OUT OF</td>
			<td style="border: 1px solid #000000;text-align: center;">LOW.IN<br>CLASS</td>
			<td style="border: 1px solid #000000;text-align: center;">HIGH.IN<br>CLASS</td>
			<td style="border: 1px solid #000000;text-align: center;">CLASS<br>AVE</td>
			<td style="border: 1px solid #000000;text-align: center;">COMMENT</td>
		</tr>
		<?php  
		$subjectdetail=$db->getRows("select * from school_subject where class_id = '" .$iStudent['class']. "' and create_by_userid='".$iParent['create_by_userid']."'");
		$tSub=0;
		foreach ($subjectdetail as $Ilist) { 
		$tSub++;
		?>
		<tr style="height: 35px;">
			<td style="font-weight: 100; border: 1px solid #000000;"><?php echo $Ilist['subject']; ?></td>
			<?php $count=0;
			foreach($totalAssesment as $Val) 
			{ 	$count=$count+1;
				$score=$db->getRow("select * from input_score_class_teacher where assesment_id='".$Val."' and student_id='".$iStudent['id']."' and subject_id='".$Ilist['id']."' and create_by_userid='".$iParent['create_by_userid']."'");
				?>
			<td style="font-weight: 100; text-align: center;  border: 1px solid #000000;"><?php echo $score['score']; ?></td>
			<?php $sum=$score['score'];
				${"d" . $Ilist['id']}+=$sum;
			} 
			$grandTotal = ${"d" . $Ilist['id']};
			?>
			<td style="  font-weight: 100; text-align: center;  border: 1px solid #000000;">
				<?php
				$a=$Ilist['id'];
				echo round($grandTotal);
				$totalScore+=$grandTotal;
				?>
			</td>
			<td style="font-weight: 100; text-align: center;  border: 1px solid #000000;">
			<?php echo $gradding=$db->getVal("select grade from school_grade where maximum_number > ".$grandTotal." or maximum_number = ".$grandTotal."");
			?>
			</td>
			<td style="font-weight: 100; text-align: center;  border: 1px solid #000000;">
				<?php echo $_SESSION["$a"]; ?>	
			</td>
			<td style="font-weight: 100; text-align: center;  border: 1px solid #000000;">
			<?php echo $db->getVal("select count(id) from input_score_class_teacher where subject_id='".$Ilist['id']."' and create_by_userid='".$iParent['create_by_userid']."'"); ?>
			</td>
			<td style="font-weight: 100; text-align: center;  border: 1px solid #000000;">
				<?php echo $_SESSION["Low".$a]; ?>
			</td>
			<td style="font-weight: 100; text-align: center;  border: 1px solid #000000;">
				<?php echo $_SESSION["Low".$a]; ?>
			</td>
			<td style="font-weight: 100; text-align: center;  border: 1px solid #000000;">
				<?php echo $_SESSION["Low".$a]; ?>
			</td>
			<td style="font-weight: 100; text-align: center;  border: 1px solid #000000;">
			<?php echo $db->getVal("select learning_strengths from subject_specific_comments where student_id='".$iStudent['id']."' and subject_id='".$Ilist['id']."' and create_by_userid='".$iParent['create_by_userid']."'"); ?> 
			</td>
		</tr>
		<?php } ?>
	</tbody>
	</table>
	
	<table cellspacing="0"; style="width: 100%;">
	<tbody style="font-family: sans-serif;">
		<tr>
			<td style="width:100%;" >
				<table style="width:100%;">
					<br>
					<tr>
						<td style="" colspan="3">GRADE DETAILS:</td>
						<td style="text-align: center;" colspan="3">No. of Subjects: <?php echo $tSub; ?></td>
					</tr>
					<tr>
						<td style="" colspan="3">
						<?php
						$iGradingAll=$db->getRows("select * from school_grade where create_by_userid='".$iParent['create_by_userid']."' order by id desc");
						foreach($iGradingAll as $iList)
						{
						?>
							<?php echo $iList['grade']; ?> = <?php echo $iList['minimum_number']; ?>-<?php echo $iList['maximum_number'].','; ?>
						<?php } ?>	
						</td>
						<td style="text-align: center;" colspan="3"></td>
					</tr>
				</table>
			</td>
		</tr>
	</tbody>
	</table>

	<table cellspacing="0"; style="width: 100%;">
	<tbody style="font-family: sans-serif;">
		<tr>
		<td style="width:100%;" >
		<table style="width:100%;">
		<br>
		<tr>
			<td style="padding: 0;vertical-align: baseline;width: 50%;">
				<table cellspacing="0"; style="width: 95%;">
				<tbody style="font-size: 16px;font-family: sans-serif;">
					<tr style="height: 40px;">
						<td style="  border: 1px solid #000000;">AFFECTIVE TRAITS</td>
						<td style="  border: 1px solid #000000;text-align: center;">RATING</td>
					</tr>	
					<?php
					$iManageTraitsAll=$db->getRows("select * from manage_traits where create_by_userid='".$iParent['create_by_userid']."'");
					foreach($iManageTraitsAll as $iList)
					{
						$iTraits=$db->getRow("select * from student_traits_class_teacher where traits_id='".$iList['id']."' and student_id='".$iStudent['id']."'");
					?>
					<tr style="height: 40px;">
						<td style="border: 1px solid #000000;"><?php echo $iList['trait']; ?></td>
						<td style="border: 1px solid #000000;text-align: center;"><?php echo $iTraits['trait']; ?></td>
					</tr>
					<?php } ?>
				</tbody>
				</table>
			</td>

			<td style="padding: 0;vertical-align: baseline;width: 50%;">
				<table cellspacing="0"; style="width: 95%;">
				<tbody style="font-size: 16px;font-family: sans-serif;">
					<tr style="height: 40px;">
						<td style="  border: 1px solid #000000;">PSYCHOMETER</td>
						<td style="  border: 1px solid #000000;text-align: center;">RATING</td>
					</tr>	
					<?php
					$iManagePsychometerAll=$db->getRows("select * from manage_phycomotor where create_by_userid='".$iParent['create_by_userid']."'");
					foreach($iManagePsychometerAll as $iList)
					{
						$iPsychometer=$db->getRow("select * from student_pyschomotor_class_teacher where pyschmotor_id='".$iList['id']."' and student_id='".$iStudent['id']."'");
					?>
					<tr style="height: 40px;">
						<td style="border: 1px solid #000000;"><?php echo $iList['phycomotor']; ?></td>
						<td style="border: 1px solid #000000;text-align: center;"><?php echo $iPsychometer['pyschmotor']; ?></td>
					</tr>
					<?php } ?>
				</tbody>
				</table>
			</td>
		</tr>
		</table>
		</td>
		</tr>
	</tbody>
	</table>

	<table cellspacing="0"; style="width: 100%;">
	
	<tbody style="font-family: sans-serif;">
		<tr>
			<td style="width:100%;" >
				<table style="width:100%;">
					<tr>
					<?php
					$iFormTeacher=$db->getRow("select * from class_teacher where school_class='".$iStudent['class']."' and school_session='".$iStudent['session']."'");
					
					$iTeacher=$db->getRow("select * from staff_manage where id='".$iFormTeacher['staff_id']."'");
					?>
						<td style="">FORM TEACHER:</td>
						<td style="">
						<?php echo $iTeacher['first_name'].' '.$iTeacher['last_name']; ?> 
						</td>
					</tr>
					<tr>
						<td style="">FORM TEACHER'S REMARKS:</td>
						<td style="">
						<?php echo $db->getVal("select comments from clas_teacher_make_comment where student_id='".$iStudent['id']."' "); ?>
						</td>
					</tr>
					<tr>
						<?php
						$iPrinciple=$db->getRow("select * from assign_role where principal='1' and create_by_userid='".$iParent['create_by_userid']."'");
						
						$iStaffPrinciple=$db->getRow("select * from staff_manage where id='".$iPrinciple['staff_id']."'");
						?>
						<td style="">PRINCIPAL'S REMARKS:</td>
						<td style="">
						<?php echo $db->getVal("select comments from clas_teacher_make_comment where student_id='".$iStudent['id']."' and userid='".$iPrinciple['staff_id']."'"); ?>
						</td>
					</tr>
					<tr>
						
						<td style="width: 90px;"> 
							<img style="width:40%" src="../uploads/<?php echo $iStaffPrinciple['signature']; ?>">
						</td>
					</tr>
					
				</table>
			</td>
</tr>

</tbody>
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
$dompdf->setPaper('A3', 'portrait');
$dompdf->render();
//For view
$dompdf->stream("",array("Attachment" => false));
//for download
//$dompdf->stream("Term_result_pdf");
?>