<?php
namespace Dompdf;
require_once ('dompdf_New/autoload.inc.php');
ob_start();
include('../config.php');
include('inc.session-create.php');
$iParent = $db->getRow("select * from school_register where id='".$_SESSION['userid']."'"); 
$iParent=$db->getRow("select * from student_guardian where id='".$_SESSION['userid']."' ");
$iSchool = $db->getRow("select * from school_register where id='".$iParent['create_by_userid']."'"); 
$iState = $db->getRow("select * from state where id='".$iSchool['state']."'");
$statename=$iState['title'];
$iStudent=$db->getRow("select * from manage_student where randomid='".$_GET['randomid']."'");
$iSession=$db->getRow("select * from school_session where id='".$_GET['session']."' and create_by_userid='".$iParent['create_by_userid']."' ");
$term_id=$db->getRow("select * from school_term where id='".$_GET['term_id']."' and create_by_userid='".$iParent['create_by_userid']."' ");
$iClass=$db->getRow("select * from school_class where id='".$iStudent['class']."'");
$iSchoolPDFsetting=$db->getRow("select * from school_pdfsetting where  create_by_userid='".$iParent['create_by_userid']."' and section_id = '".$iClass['id']."'");









$assesmentALL=$_GET['assesments'];
$totalAssesment=explode('-',$assesmentALL);
$assesmentIn=implode(',',$totalAssesment);
$iCountAsses = count($totalAssesment);
$iGetPositionAll=$db->getRows("select * from school_subject where class_id = '" .$iStudent['class']. "' and create_by_userid='".$iParent['create_by_userid']."'");
$iCountSubject = count($iGetPositionAll);


//-----------------------------------------------------------------------------------POstion Acording to subject---------------------------------------------------------------------

$subjectdetails=$db->getRows("select * from school_subject where class_id = '" .$iStudent['class']. "' and create_by_userid='".$iParent['create_by_userid']."'");
foreach ($subjectdetails as $Ilists) { 
$aiiisub=$Ilists['id'];
$i=0;
$tStuden=0;
$aryList11=$db->getRows("select * from manage_student  where class='".$iClass['id']."' and session='".$_GET['session']."' and term_id='".$_GET['term_id']."'  and create_by_userid='".$create_by_userid."' order by first_name asc");
foreach($aryList11 as $iList)
{
	$tStuden=$tStuden+1;
foreach($totalAssesment as $Val) 
{ 
$score=$db->getRow("select * from input_score_class_teacher where assesment_id='".$Val."' and student_id='".$iList['id']."' and subject_id='".$Ilists['id']."' and create_by_userid='".$create_by_userid."'");
${"$aiiisub".$iList['id']}+=$score['score'];
}
$a=$iList['id'];
$student_two["$a"] = ${"$aiiisub".$iList['id']}; 

${"classTotal".$Ilists['id']} += ${"$aiiisub".$iList['id']};

${"highLows".$Ilists['id']}[]= ${"$aiiisub".$iList['id']};

}

 $getClasAvg[]= ${"classTotal".$Ilists['id']}/$tStuden;


$rankedScoresNew = setPositionNew($student_two);
foreach ($rankedScoresNew as $studentwa => $data) {
	
	$rank=$data['rank'];
	if($iStudent['id']==$studentwa){
	 $_SESSION["$aiiisub"] = $rank;
	}
	
}
unset($student_two);
}



///////////////************************ DONE BY SHAILENDRA ******************************************************************//////////////////////////////////////
///////////////************************ DONE BY SHAILENDRA ******************************************************************//////////////////////////////////////
///////////////************************ DONE BY SHAILENDRA ******************************************************************//////////////////////////////////////
$iShailsubjectdetails=$db->getRows("select * from school_subject where class_id = '" .$iStudent['class']. "' and create_by_userid='".$create_by_userid."'");
foreach ($iShailsubjectdetails as $iShailSbjDts) { 

$aryListMangStdnt=$db->getRows("select id from manage_student  where class='".$iClass['id']."' and session='".$_GET['session']."' and term_id='".$_GET['term_id']."'  and create_by_userid='".$create_by_userid."' order by first_name asc");
foreach($aryListMangStdnt as $iMngStdList)
	{		$iArrayStudentId[] = $iMngStdList['id'];
		foreach($totalAssesment as $Val) 
			{ 
				$iStdInpSrClsTch=$db->getRow("select * from input_score_class_teacher where  assesment_id='".$Val."'  and student_id='".$iMngStdList['id']."'  and subject_id='".$iShailSbjDts['id']."' and create_by_userid='".$create_by_userid."'");
				//echo $iShailSbjDts['subject'].'-----------'.$iStdInpSrClsTch['score'].'-----------'.$iMngStdList['id'];
				
				$student_one[$iMngStdList['id']]['subject'][] = 	 $iShailSbjDts['subject'];
				$student_one[$iMngStdList['id']]['score'][] = 	 $iStdInpSrClsTch['score'];
				 
				 
			}
				
			
	}

}
$iArrayStudentId=array_unique($iArrayStudentId);
foreach($iArrayStudentId as $karsdtid => $vlkstid)
	{
		$iHighestARayInCls[] = array_sum($student_one[$vlkstid]['score'])/count($iShailsubjectdetails);
		
	}
///////////////************************ DONE BY SHAILENDRA ******************************************************************//////////////////////////////////////
///////////////************************ DONE BY SHAILENDRA ******************************************************************//////////////////////////////////////
///////////////************************ DONE BY SHAILENDRA ******************************************************************//////////////////////////////////////


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

//--------------------------------------------------------Total Score------------------------------------------------------------------------------------------------------
$subdetaisForToal=$db->getRows("select * from school_subject where class_id = '" .$iStudent['class']. "' and create_by_userid='".$iParent['create_by_userid']."'");
$tSubtotal=0;
foreach ($subdetaisForToal as $IlistTotal) { 
$tSubtotal++;
foreach($totalAssesment as $Val) 
{ 	
$scoresTotal=$db->getRow("select * from input_score_class_teacher where assesment_id='".$Val."' and student_id='".$iStudent['id']."' and subject_id='".$IlistTotal['id']."' and create_by_userid='".$iParent['create_by_userid']."'");
${"stotal" . $IlistTotal['id']}+=$scoresTotal['score'];
} 
$grandTotalsss += ${"stotal" . $IlistTotal['id']};
}

//--------------------------------------------------------Final POstion------------------------------------------------------------------------------------------------------
$ifinala=$db->getRows("select * from manage_student  where class='".$iClass['id']."' and session='".$_GET['session']."' and term_id='".$_GET['term_id']."'  and create_by_userid='".$create_by_userid."' ");
	foreach($ifinala as $iifinalList)
	{
	 $bf=$iifinalList['id'];
	 $iiSubjectsNewf=$db->getRows("select * from school_subject where class_id='".$iClass['id']."'");
	 foreach($iiSubjectsNewf as $iffiListNew)
	 { 
	   $iInputScoree=$db->getVal("select SUM(score) from input_score_class_teacher where assesment_id IN ($assesmentIn) and student_id='".$iifinalList['id']."' and session_id='".$_GET['session']."' and term_id='".$_GET['term_id']."' and class_id='".$iifinalList['class']."' and subject_id='".$iffiListNew['id']."'");
	   $iScorefe = $iInputScoree; 
	    ${"dsp" . $iifinalList['id']}+= $iScorefe;
	  } 
      $ifiSum = ${"dsp" . $iifinalList['id']}; 
	   
	  $student_final["$bf"] = $ifiSum;
	}
	
	
	
	
	
function setPositionf($standingsf) {
    $rankings = array();
    arsort($standingsf);
    $rankf = 1;
    $tie_rank = 0;
    $prev_score = -1;

    foreach ($standingsf as $name => $score) {
        if ($score != $prev_score) { 
            $count = 0;
            $prev_score = $score;
            $rankings[$name] = array('score' => $score, 'rankf' => $rankf);
        } else { 
            $prev_score = $score;
            if ($count++ == 0) {
                $tie_rank = $rankf - 1;
            }
            $rankings[$name] = array('score' => $score, 'rankf' => $tie_rank);
        }
        $rankf++;
    }
    return $rankings;
}


$rankedScoresf = setPositionf($student_final);

foreach ($rankedScoresf as $studentwaf => $dataf)
 {
	if($iStudent['id']==$studentwaf){
	 $_SESSION["board_$studentwaf"]=$dataf['rankf'];
	}
	
}


$iTotalStudents = $db->getVal("select count(*) from manage_student where  class='".$iClass['id']."' and  create_by_userid='".$create_by_userid."' and session='".$_GET['session']."' and term_id='".$_GET['term_id']."'");

?>
<html>
<head>
   <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>

<style>
#watermark {
   position: absolute;
	bottom:   10%;
	z-index:  -1000;
	opacity:0.1;
	
}
.showarabicc { font-family: DejaVu Sans, sans-serif!important; }
.fontsubject{ font-family: DejaVu Sans, sans-serif; }
</style>   
</head>
<body style="border: 1px solid; background-repeat: no-repeat;background-size: cover; background-position:center; opacity:1;">
<div id="watermark">
            <img src="<?php echo 'https://'.$_SERVER['HTTP_HOST'].'/uploads/'.$iSchool['logo']; ?>" height="100%" width="100%" />
        </div>

<table style="width:100%;">
	<tr>
		<td colspan="2" style="width: 120px;"> 
			<div>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 
				<img style="width:100px" src="<?php echo 'https://'.$_SERVER['HTTP_HOST'].'/uploads/'.$iSchool['logo']; ?>">
			</div>
		</td>
		<td colspan="3"> 
			<p style="font-size: 25px;color: #2196F3;font-weight: bolder;text-align: center;font-family: sans-serif;margin-bottom: 0;"><?php echo $iSchool['name']; ?></p> 
			<!--<p style="font-size: 22px;color: #2196F3;font-weight: bolder;text-align: center;font-family: sans-serif;margin-top:0px;margin-bottom:0px;">STAFF SECONDARY SCHOOL </p>-->     
			<p style="font-size: 12px;font-weight:600;text-align:center;font-family: sans-serif;margin-top:0px;">MOTO: <?php echo $iSchool['moto']; ?></p>
			<p style="font-size: 15px;font-weight:600;text-align:center;font-family: sans-serif;margin-top:0px;"><?php echo $iSchool['location']; ?>, <?php echo $statename; ?></p>
			<p style="font-size: 15px;text-align:center;font-family: sans-serif;margin-top:30px;margin-bottom:0px;">REPORT SHEET FOR <?php echo $term_id['term']; ?> <?php echo $iSession['session']; ?> ACADEMIC SESSION</p>
		   <br>
		</td>
        
        <td colspan="2" style="width: 120px;"> 
			<div style="margin-top:50px">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 
             <?php if($iSchoolPDFsetting['is_profilepic']=='1') { ?>
				<img style="width:150px" src="<?php echo 'https://'.$_SERVER['HTTP_HOST'].'/uploads/'.$iStudent['picture']; ?>">
             <?php } ?>   
			</div>
		</td>
        
	</tr>
	
	</table>
	
	<table cellspacing="0"; style="width: 100%;">
	<tbody style="font-family: sans-serif;">
		<tr>
			<td style="width:100%;" >
				<table style="width:100%;">
					<tr>
					
					    <td style=""><b>Name:</b></td>
						<td style=""><?php echo $iStudent['first_name'].' '.$iStudent['other_name'].' '.$iStudent['last_name']; ?></td>
                        
                         <?php if($iSchoolPDFsetting['is_grade']=='1') { ?>
						<td style=""><b>Final Grade:</b></td>
					  <?php	$ggrade=$grandTotalsss/$tSubtotal; ?>
						<td style=""><?php echo $graddingg=$db->getVal("select grade from school_grade where create_by_userid='".$iParent['create_by_userid']."'  and minimum_number <= ".$ggrade." and maximum_number >= ".$ggrade."");?> </td>
                           <?php } ?>
                        
						<td style=""></td>
						<td style=""></td>
					
					</tr>
					<tr>
                    	<?php if($iSchoolPDFsetting['is_class']=='1') { ?>
						<td style=""><b>Class:</b></td>
						<td style=""><?php echo $iClass['name']; ?></td>
                        <?php } ?>
                        <?php if($iSchoolPDFsetting['is_position']=='1') { ?>
						<td style=""><b>Final Postion:</b></td>
						<td style=""><?php $af= $iStudent['id']; echo $_SESSION["board_$af"]; unset($_SESSION["board_$af"]); ?> </td>
                        <?php } ?>
                         <?php if($iSchoolPDFsetting['is_totalstudent']=='1') { ?>
                        <td style=""><b>Total Student:</b></td>
						<td style=""><?php echo $iTotalStudents; ?> </td>
                        <?php } else {  ?> 
                         <td style=""></td>
                         <td style=""></td>
                         
                         <?php } ?>
                        
					</tr>
					
					<tr><td></td></tr>
					<tr>
                    <?php if($iSchoolPDFsetting['is_addmission']=='1') { ?>
						<td style=""><b>Admission No:</b></td>
						<td style=""><?php 
						echo $iStudent['student_id'];
						 ?></td>
                           <?php } ?>
                       
                         <?php if($iSchoolPDFsetting['is_totalscore']=='1') { ?> 
						<td style=""><b>Total Score:</b></td>
						<td style=""><?php echo $grandTotalsss; ?></td>
                            <?php } ?>
						<td style=""></td>
						<td style=""></td>
					</tr>
					<tr>
						<?php
$scoreNew=$db->getRow("select * from input_score_class_teacher where assesment_id IN($assesmentIn) and student_id='".$iStudent['id']."'  and create_by_userid='".$iParent['create_by_userid']."'");

					 ?>
                      <?php if($iSchoolPDFsetting['is_session']=='1') { ?> 
						<td style=""><b>Session:</b></td>
						<td style=""><?php echo $iSession['session']; ?></td>
                        
                       <?php } ?>   
                            <?php if($iSchoolPDFsetting['is_finalaverage']=='1') { ?> 
						<td style=""><b>Final Average:</b></td>
						<td style=""><?php echo round($grandTotalsss/$tSubtotal,2); ?></td>
                         <?php } else {  ?> 
                         <td style=""></td>
                         <td style=""></td>
                         
                         <?php } ?>
						
					</tr>
					<tr>
                     <?php if($iSchoolPDFsetting['is_terms']=='1') { ?> 
						<td style=""><b>Term:</b></td>
						<td style=""><?php echo $term_id['term']; ?></td>
                            <?php } ?>  
                         <?php if($iSchoolPDFsetting['is_highestaverage']=='1') { ?> 
						<td style=""><b>Highest Avg. in Class:</b></td>						
						<td style=""> <?php echo round(max($iHighestARayInCls),2); ?></td>
                            <?php } ?>  
                        
                         <?php if($iSchoolPDFsetting['is_lowestaverage']=='1') { ?> 
						<td style=""><b>Lowest Avg. in Class:</b></td>
						<td style=""><?php echo round(min($iHighestARayInCls),2); ?></td>
						    <?php } else {  ?> 
                         <td style=""></td>
                         <td style=""></td>
                         
                         <?php } ?>
						
					</tr>
					<tr>
						
						  <?php if($iSchoolPDFsetting['is_schoolopen']=='1') { ?>
						<td style=""><b>Days School Open:</b></td>
						<td style="">	
						<?php echo $totalOpen=$db->getVal("select count(id) from class_teacher_roll_call where session_id='".$_GET['session']."' and term_id='".$_GET['term_id']."' and class_id='".$iStudent['class']."' and create_by_userid='".$iParent['create_by_userid']."'"); ?>
                       </td>
                        <?php } ?> 
                       
                       
                         <?php if($iSchoolPDFsetting['is_daypresent']=='1') { ?>
						<td style=""><b>Day(s) Present:</b></td>
						<td style="">
						<?php 
						$ipresentDays=0;
						$iStudentID=$iStudent['id'];
						$totalPressent= $db->getRows("select * from class_teacher_roll_call where  session_id='".$_GET['session']."' and term_id='".$_GET['term_id']."' and class_id='".$iStudent['class']."' and create_by_userid='".$iParent['create_by_userid']."'");
						foreach($totalPressent as $iiPresent){
							$updateRollP=$iiPresent['present'];
							$checkExistP=explode(",",$updateRollP);	
							if(in_array($iStudentID,$checkExistP)) { $ipresentDays=$ipresentDays+1; }
						}
						echo $ipresentDays;
						?>
						</td>
                         <?php } ?> 
                        
                        
                     <?php if($iSchoolPDFsetting['is_dayabsent']=='1') { ?>
						<td style=""><b>Day(s) Absent:</b></td>
						<td style=""><?php echo $totalOpen-$ipresentDays;  ?></td>
                        
                         <?php } else {  ?> 
                         <td style=""></td>
                         <td style=""></td>
                         
                         <?php } ?>
					</tr>
					
				</table>
			</td>
		</tr>
	</tbody>	
	</table>

	<table cellspacing="0"; style="width: 100%;" class="showarabicc">
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
			<td style="border: 1px solid #000000;text-align: center; <?php if($iSchoolPDFsetting['is_pos']!='1') { echo 'display:none'; }  ?>">POS</td>
			<td style="border: 1px solid #000000;text-align: center; <?php if($iSchoolPDFsetting['is_out']!='1') { echo 'display:none'; } ?>">OUT OF</td>
			<td style="border: 1px solid #000000;text-align: center;  <?php if($iSchoolPDFsetting['is_lowest_avg']!='1') { echo 'display:none'; }  ?>">LOW.IN<br>CLASS</td>
			<td style="border: 1px solid #000000;text-align: center;  <?php if($iSchoolPDFsetting['is_highest_avg']!='1') { echo 'display:none'; }  ?>">HIGH.IN<br>CLASS</td>
			<td style="border: 1px solid #000000;text-align: center;  <?php if($iSchoolPDFsetting['is_class_avg']!='1') { echo 'display:none'; }  ?>">CLASS<br>AVG</td>
			<td style="border: 1px solid #000000;text-align: center;">COMMENT</td>
		</tr>
		<?php  
		$subjectdetail=$db->getRows("select * from school_subject where class_id = '" .$iStudent['class']. "' and create_by_userid='".$iParent['create_by_userid']."'");
		$tSub=0;
		foreach ($subjectdetail as $Ilist) { 
		$tSub++;
		?>
		<tr style="height: 35px;">
			<td class="fontsubject" style="font-weight: 100; border: 1px solid #000000;"><?php echo $Ilist['subject']; ?></td>
			<?php $count=0;
			foreach($totalAssesment as $Val) 
			{ 	$count=$count+1;
				$scores=$db->getRow("select * from input_score_class_teacher where assesment_id='".$Val."' and student_id='".$iStudent['id']."' and subject_id='".$Ilist['id']."' and create_by_userid='".$iParent['create_by_userid']."'");
				?>
			<td style="font-weight: 100; text-align: center;  border: 1px solid #000000;"><?php echo $scores['score']; ?></td>
			<?php 
				${"s" . $Ilist['id']}+=$scores['score'];
			} 
			$grandTotal = ${"s" . $Ilist['id']};
			?>
			<td style="  font-weight: 100; text-align: center;  border: 1px solid #000000;">
				<?php
				echo round($grandTotal);
				?>
			</td>
			<td style="font-weight: 100; text-align: center;  border: 1px solid #000000;">
			<?php  $gradding=$db->getRow("select grade , description from school_grade where  create_by_userid='".$iParent['create_by_userid']."'  and minimum_number <= ".$grandTotal." and maximum_number >= ".$grandTotal."");
			echo $gradding['grade'];
			?>
			</td>
            
            
			<td style="font-weight: 100; text-align: center;  border: 1px solid #000000; <?php if($iSchoolPDFsetting['is_pos']!='1') { echo 'display:none'; }  ?>">
	         <?php   $aiiisubs=$Ilist['id']; echo $_SESSION["$aiiisubs"]; unset($_SESSION["$aiiisubs"]); ?>		
			</td>
			<td style="font-weight: 100; text-align: center;  border: 1px solid #000000;  <?php if($iSchoolPDFsetting['is_out']!='1') { echo 'display:none'; }  ?>">
			<?php echo $outOf=$db->getVal("select count(id) from  manage_student where session='".$_GET['session']."' and term_id='".$_GET['term_id']."' and class='".$iStudent['class']."' and create_by_userid='".$iParent['create_by_userid']."'"); ?>
			</td>
			<td style="font-weight: 100; text-align: center;  border: 1px solid #000000; <?php if($iSchoolPDFsetting['is_lowest_avg']!='1') { echo 'display:none'; }  ?>">
			<?php echo round(min( ${"highLows".$Ilist['id']} ),2); ?> 
			</td>
			<td style="font-weight: 100; text-align: center;  border: 1px solid #000000; <?php if($iSchoolPDFsetting['is_highest_avg']!='1') { echo 'display:none'; }  ?>">
				<?php  echo round(max( ${"highLows".$Ilist['id']}  ),2);  ?>
			</td>
			<td style="font-weight: 100; text-align: center;  border: 1px solid #000000; <?php if($iSchoolPDFsetting['is_class_avg']!='1') { echo 'display:none'; }  ?>">
				<?php 
                 $getClasAv= ${"classTotal".$Ilist['id']}/$outOf;
				 echo round($getClasAv,2);
				?>
	
			</td>
			<td style="font-weight: 100; text-align: center;  border: 1px solid #000000;">
            <?php echo $gradding['description']; // $db->getVal("select description from school_grade where create_by_userid='".$iParent['create_by_userid']."' and maximum_number > ".$grandTotal." or maximum_number = ".$grandTotal."");?>   
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
					<tr style=" <?php if($iSchoolPDFsetting['is_no_of_subjects']!='1') { echo 'display:none'; }  ?> ">
						<td style="" colspan="3">GRADE DETAILS:</td>
						<td style="text-align: center;" colspan="3">No. of Subjects: <?php echo $tSub; ?></td>
					</tr>
					<tr style=" <?php if($iSchoolPDFsetting['is_grade_details']!='1') { echo 'display:none'; }  ?> ">
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
         <?php if($iSchoolPDFsetting['is_affective']=='1') { ?>
        
			<td style="padding: 0;vertical-align: baseline;width: 50%;">
				<table cellspacing="0"; style="width: 500px;">
				<tbody style="font-size: 16px;font-family: sans-serif;">
					<tr style="height: 40px;">
						<td style="  border: 1px solid #000000;width:300px;"><?php if($iSchoolPDFsetting['title_4']=='') { echo "AFFECTIVE TRAITS"; } else { echo $iSchoolPDFsetting['title_4']; } ?></td>
						<td style="  border: 1px solid #000000;text-align: center;">RATING</td>
					</tr>	
					<?php
					$iManageTraitsAll=$db->getRows("select * from manage_traits where create_by_userid='".$iParent['create_by_userid']."'");
					foreach($iManageTraitsAll as $iList)
					{
						$iTraits=$db->getRow("select * from student_traits_class_teacher where traits_id='".$iList['id']."' and student_id='".$iStudent['id']."'");
					?>
					<tr style="height: 40px;">
						<td style="border: 1px solid #000000;width:300px;"><?php echo $iList['trait']; ?></td>
						<td style="border: 1px solid #000000;text-align: center;"><?php echo $iTraits['trait']; ?></td>
					</tr>
					<?php } ?>
				</tbody>
				</table>
			</td>

		<?php } ?>
       
		  
			<td style="padding: 0;vertical-align: baseline;width: 50%;">
            
         <?php if($iSchoolPDFsetting['is_phycomotor']=='1') { ?>    
				<table cellspacing="0";  style="width:500px">
				<tbody style="font-size: 16px;font-family: sans-serif;">
					<tr style="height: 40px;">
						<td style="  border: 1px solid #000000;width:300px;"><?php if($iSchoolPDFsetting['title_5']=='') { echo "PSYCHOMOTOR"; } else { echo $iSchoolPDFsetting['title_5']; } ?></td>
						<td style="  border: 1px solid #000000;text-align: center;">RATING</td>
					</tr>	
					<?php
					$iManagePsychometerAll=$db->getRows("select * from manage_phycomotor where create_by_userid='".$iParent['create_by_userid']."'");
					foreach($iManagePsychometerAll as $iList)
					{
						$iPsychometer=$db->getRow("select * from student_pyschomotor_class_teacher where pyschmotor_id='".$iList['id']."' and student_id='".$iStudent['id']."'");
					?>
					<tr style="height: 40px;">
						<td style="border: 1px solid #000000;width:300px;"><?php echo $iList['phycomotor']; ?></td>
						<td style="border: 1px solid #000000;text-align: center;text-transform: capitalize;"><?php echo $iPsychometer['pyschmotor']; ?></td>
					</tr>
					<?php } ?>
				</tbody>
				</table>
		<?php } ?>		
				<br>
				<table cellspacing="0"   style="width:500px">
 <tbody>
 <tr style="height: 40px;">
    <td style="border: 1px solid #000000;">SCALE </td>
   
  </tr>	
<?php $i=0;
$aryList=$db->getRows("select * from school_scale where create_by_userid='".$create_by_userid."'");
foreach($aryList as $iList)
{	$i=$i+1;
$aryPgAct["id"]=$iList['id'];
?>
  <tr style="height: 40px;">
    <td style="border:1px solid #000000;"><?php echo $iList['rating']; ?>-<?php   echo $iList['review']; ?></td>
    </tr>
  
 <?php } ?>

</tbody>
</table>

			</td>
		</tr>
		</table>
		</td>
		</tr>
		<tr>

</tr>
		
		
	</tbody>
	</table>
	<br>
<hr><br>
	<table cellspacing="0"; style="width: 100%;">
	
	<tbody style="font-family: sans-serif;">
		<tr>
			<td style="width:100%;" >
				<table style="width:100%; font-size:17px">
					<tr>
					<?php
					$iFormTeacher=$db->getRow("select * from class_teacher where school_class='".$iStudent['class']."' and school_session='".$_GET['session']."'");
					
					$iTeacher=$db->getRow("select * from staff_manage where id='".$iFormTeacher['staff_id']."'");
					?>
						<td style=""><?php if($iSchoolPDFsetting['title_1']=='') { echo 'Class Teacher'; } else { echo $iSchoolPDFsetting['title_1']; } ?>:</td>
						<td style="">
						<?php echo $iTeacher['first_name'].' '.$iTeacher['last_name']; ?> 
						</td>
					</tr>
					<tr>
						<td style=""><?php if($iSchoolPDFsetting['title_2']=='') { echo "Class Teacher's Remarks"; } else { echo $iSchoolPDFsetting['title_2']; } ?>:</td>
						<td style="">
						<?php echo $db->getVal("select comments from clas_teacher_make_comment where student_id='".$iStudent['id']."' "); ?>
						</td>
					</tr>
					<tr>
						<?php
						$iPrinciple=$db->getRow("select * from assign_role where principal='1' and create_by_userid='".$iParent['create_by_userid']."'");
						
						$iStaffPrinciple=$db->getRow("select * from staff_manage where id='".$iPrinciple['staff_id']."'");
						?>
						<td style=""><?php if($iSchoolPDFsetting['title_3']=='') { echo "Principal's Remarks"; } else { echo $iSchoolPDFsetting['title_3']; } ?>:</td>
						<td style="">
						<?php echo $db->getVal("select comments from principle_remarks where student_id='".$iStudent['id']."'"); ?>
						</td>
						<?php $sign_term=$db->getRow("select nextTerm,sign from  principal_sign_nextTerm where  create_by_userid='".$iParent['create_by_userid']."' order by id desc"); ?>
						<td style="width: 90px;">
							<img  src="<?php echo 'https://'.$_SERVER['HTTP_HOST'].'/uploads/'.$sign_term['sign']; ?>" style="width: 70px;">
						</td>
					</tr>
					<tr>
						
						<td style="">Next Term Begins:</td>
						<td style="">
						<?php echo $sign_term['nextTerm']; ?>
						</td>
					</tr>
					
				</table>
			</td>
</tr>

</tbody>
</table>
<!-- <br><br><br><br><br><br>
Printed On: <?php  echo date("d, M Y", strtotime(date("Y-m-d H:i:s"))); ?> -->
</body>

</html>
<?php

get_magic_quotes_gpc();
$old_limit = ini_set("memory_limit", "1024M");
$html = ob_get_clean();

$dompdf = new Dompdf();

use Dompdf\Options;
$options = new Options();
$options->set('isRemoteEnabled', TRUE);
$options->set('isJavascriptEnabled', TRUE);
//$dompdf->set_option( 'isJavascriptEnabled' , true );
$dompdf = new Dompdf($options);


$dompdf->load_html($html);
 $dompdf->load_html($html,'UTF-8');
$customPaper = array(0,0,1000,1400);
$dompdf->set_paper($customPaper);
$dompdf->render();
//For view
$dompdf->stream("",array("Attachment" => false));
//for download
//$dompdf->stream("Term_result_pdf");
?>