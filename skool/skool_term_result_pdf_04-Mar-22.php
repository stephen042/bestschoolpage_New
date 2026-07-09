<?php
namespace Dompdf;
include('../config.php');
require_once ('dompdf_New/autoload.inc.php');
ob_start();
define('WP_MEMORY_LIMIT', '512M');

include('inc.session-create.php');


$iSchool = $db->getRow("select * from school_register where id='".$create_by_userid."'"); 
$iState = $db->getRow("select * from state where id='".$iSchool['state']."'");
$statename=$iState['title'];
$iStudent=$db->getRow("select * from manage_student where randomid='".$_GET['randomid']."'");
$iSession=$db->getRow("select * from school_session where id='".$_GET['session']."' and create_by_userid='".$create_by_userid."' ");
$term_id=$db->getRow("select * from school_term where id='".$_GET['term_id']."' and create_by_userid='".$create_by_userid."' ");
$iClass=$db->getRow("select * from school_class where id='".$iStudent['class']."'  and create_by_userid='".$create_by_userid."'");
 $iSchoolPDFsetting=$db->getRow("select * from school_pdfsetting where section_id = '".$iClass['section_id']."'");


$assesmentALL=$_GET['assesments'];
$totalAssesment=explode('-',$assesmentALL);
$assesmentIn=implode(',',$totalAssesment);
$iCountAsses = count($totalAssesment);

function toUnique($array,$property) {
  $tempArray = array_unique(array_column($array, $property));
  $moreUniqueArray = array_values(array_intersect_key($array, $tempArray));
  return $moreUniqueArray;
}

$_query = "select student.id as studentId, student.*, schoolClass.*, classScore.* 
FROM input_score_class_teacher AS classScore 
INNER JOIN manage_student AS student ON classScore.student_id  = student.id 
INNER JOIN school_class AS  schoolClass ON student.class = schoolClass.id 
WHERE schoolClass.id ='".$iStudent['class']."' AND classScore.create_by_userid = '".$create_by_userid."' AND student.term_id = '".$_GET['term_id']."' AND student.session = '".$_GET['session']."'
 order by student.first_name asc;";
$allQuery = $db->getRows($_query);

// echo "QUERY QUERY  QUERY". $_query;
//    exit;  

//$iSchoolPDFsetting= $allQuery[0];
//$iStudent = $allQuery[0];
$subjectdetails=$db->getRows("select * from school_subject where class_id = '" .$iStudent['class']. "' and create_by_userid='".$create_by_userid."'");
//$subjectdetails = toUnique($allQuery, "subjectId");
$studentIds = toUnique($allQuery, "studentId");
$scoreData = $allQuery;

    $tStuden= count($studentIds);
    
    
$totalScore = array();
foreach ($subjectdetails as $Ilists) { 
$aiiisub=$Ilists['id'];
$i=0;





  $scoreTotal= 0;
  foreach($studentIds as $iList)
  {
      foreach($totalAssesment as $Val) 
      { 
              
        foreach($allQuery as $_All) {
          if($iList['subject_id'] == $Ilists['id'] && $iList['studentId'] == $_All['studentId'] && $_All['assesment_id'] == $Val){
            ${"$aiiisub".$iList['studentId']}+=$_All['score'];
            $scoreTotal += 1;;
          
            
              $iInputScore=$db->getVal("select SUM(score) from input_score_class_teacher where  student_id='".$_All['studentId']."'  and term_id='".$_GET['term_id']."' 
 and class_id='".$_GET['class_id']."' and create_by_userid='".$create_by_userid."'");


            $average = round($iInputScore/$tStuden,2);
            
				$totalScore[$iList['studentId']] = $average; 
               
            
            break;
          }
        }
      }
      $a=$iList['studentId'];
      $student_two["$a"] = ${"$aiiisub".$iList['studentId']}; 
  }
${"classTotal".$Ilists['id']} = $scoreTotal;
${"highLows".$Ilists['id']}[]= $scoreTotal;

$getClasAvg[]= ${"classTotal".$Ilists['id']}/$tStuden;




$rankedScoresNew = setPositionNew($student_two);


foreach ($rankedScoresNew as $studentwa => $data) {
	

	if($iStudent['id']==$studentwa){
	 $_SESSION["$aiiisub"] = $rank;
	}
	
}
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

//--------------------------------------------------------Total Score------------------------------------------------------------------------------------------------------
//$subdetaisForToal=$db->getRows("select id from school_subject where class_id = '" .$iStudent['class']. "' and create_by_userid='".$create_by_userid."'");
// $tSubtotal= count($subjectdetails);
// foreach ($subjectdetails as $IlistTotal) {
//   //  $iAlreadyRemoved = $db->getVal("select id from  student_subject_remove where create_by_userid='".$create_by_userid."' and subjectid='".$IlistTotal['id']."' and studentid='".$iStudent['id']."'");
  
//   // if($iAlreadyRemoved=='') {  
  
//           $scroreTotal = 0;
//       foreach($totalAssesment as $Val) 
//       {   
//         foreach($allQuery as $iAll) {
//             if($iAll['student_id'] == $iStudent['id'] && $iAll['subject_id'] == $IlistTotal['id'] && $iAll['assesment_id'] == $Val){
//               $scroreTotal += $iAll['score'];
//               break;
//             }
//         }
//       }
//       $grandTotalsss += $scroreTotal;

//    // }

// }

$subdetaisForToal=$db->getRows("select id from school_subject where class_id = '" .$iStudent['class']. "' and create_by_userid='".$create_by_userid."'");
$tSubtotal=0;
foreach ($subdetaisForToal as $IlistTotal) { 

   $iAlreadyRemoved = $db->getVal("select id from  student_subject_remove where create_by_userid='".$create_by_userid."' and subjectid='".$IlistTotal['id']."' and studentid='".$iStudent['id']."'");
  
  if($iAlreadyRemoved=='') {  
  
$tSubtotal++;

foreach($totalAssesment as $Val) 
{   

$scoresTotal=$db->getRow("select score from input_score_class_teacher where assesment_id='".$Val."' and student_id='".$iStudent['id']."' and subject_id='".$IlistTotal['id']."' and create_by_userid='".$create_by_userid."'");
${"stotal" . $IlistTotal['id']}+=$scoresTotal['score'];

} 
$grandTotalsss += ${"stotal" . $IlistTotal['id']};

    }

}

//--------------------------------------------------------Final POstion------------------------------------------------------------------------------------------------------
// foreach($allQuery as $query)
// {
//   $grandTotalsss += $query['score'];
//   $student_final["$bf"] += $query['score'];
// }
	//$ifinala=$db->getRows("select id, class from manage_student  where class='".$iClass['id']."' and session='".$_GET['session']."' and term_id='".$_GET['term_id']."'  and create_by_userid='".$create_by_userid."' ");
  foreach($studentIds as $iifinalList)
  {
   $bf=$iifinalList['id'];
     $iScorefe = $iifinalList['score']; 
      ${"dsp" . $iifinalList['id']}+= $iScorefe;
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
	// $_SESSION["board_$studentwaf"]=$dataf['rankf'];
	}
	
}
$val = $totalScore;

//print_r($val);
rsort($totalScore);
//print_r($totalScore);
$i = 0;
foreach($val as $key => $v){
   if($iStudent['id']==$key){
      $rank = $v;
	// echo $_SESSION["board_$studentwaf"]=$i;
       
	} 
	$i++;
}
foreach($totalScore as $key => $val){
    if($val == $rank){
        if($key == 0){
             $rank = 1;
        }else{
         $rank = $key+=1;
        }
    }
}

$iTotalStudents = $db->getVal("select count(*) from manage_student where class='".$iClass['id']."' and   create_by_userid='".$create_by_userid."' and session='".$_GET['session']."' and term_id='".$_GET['term_id']."'");
?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<style>
#watermark {
	position: absolute;
	bottom: 10%;
	z-index: -1000;
	opacity: 0.1;
}
.fontsubject{ font-family: DejaVu Sans, sans-serif; }
</style>
</head>
<body style="border: 1px solid; background-repeat: no-repeat;background-size: cover; background-position:center; opacity:1;">
<div id="watermark" > <img src="<?php echo 'https://'.$_SERVER['HTTP_HOST'].'/uploads/'.$iSchool['logo']; ?>" height="100%" width="100%" /> </div>
<table style="width:100%;">
  <tr>
    <td style="width:250px; max-width:20%; padding-top:80px;"><div>&nbsp;&nbsp;&nbsp; <img style="width:250px" src="<?php echo 'https://'.$_SERVER['HTTP_HOST'].'/uploads/'.$iSchool['logo']; ?>"> </div></td>
    <td style="max-width:80%; width:500px;"><p style="font-size: 35px;color: #2196F3;font-weight: bolder;text-align: center;font-family: sans-serif;margin-bottom: 0; margin-right:50px;"><?php echo str_replace('(', '<br/>(',$iSchool['name']); ?></p>      
      <p style="font-size: 12px;font-weight:600;text-align:center;font-family: sans-serif;margin-top:0px; margin-right:50px;">MOTO:<?php echo $iSchool['moto']; ?></p>
      <p style="font-size: 12px;font-weight:600;text-align:center;font-family: sans-serif;margin-top:0px; margin-right:50px;"><?php echo $iSchool['location']; ?>, <?php echo $statename; ?></p>
      <p style="font-size: 15px;text-align:center;font-family: sans-serif;margin-top:30px;margin-bottom:0px; margin-right:50px;">REPORT SHEET FOR <?php echo $term_id['term']; ?> <?php echo $iSession['session']; ?> ACADEMIC SESSION</p>
      <br>
   </td>
    <td style="width: 120px; max-width:20%;"><div>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        <?php if($iSchoolPDFsetting['is_profilepic']=='1') { ?>
        <img style="width:150px" src="<?php echo 'https://'.$_SERVER['HTTP_HOST'].'/uploads/'.$iStudent['picture']; ?>">
        <?php } ?>
      </div></td>
  </tr>
</table>
<table cellspacing="0"; style="width: 100%;">
  <tbody style="font-family: sans-serif;">
    <tr>
      <td style="width:100%;" ><table style="width:100%;">
          <tr>
            <td style=""><b>Name:</b></td>
            <td style=""><?php echo $iStudent['first_name'].' '.$iStudent['other_name'].' '.$iStudent['last_name']; ?></td>
            <?php if($iSchoolPDFsetting['is_grade']=='1') { ?>
            <td style=""><b>Final Grade:</b></td>
            <?php $ggrade= floor($grandTotalsss/$tSubtotal); ?>
            <td style=""><?php echo $graddingg=$db->getVal("select grade from school_grade where create_by_userid='".$create_by_userid."' and minimum_number <= ".$ggrade." and maximum_number >= ".$ggrade."");?></td>
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
            <td style=""><?php $af= $iStudent['id']; echo $rank; unset($_SESSION["board_$af"]); ?></td>
            <?php } ?>
            <?php if($iSchoolPDFsetting['is_totalstudent']=='1') { ?>
            <td style=""><b>Total Student:</b></td>
            <td style=""><?php echo $iTotalStudents; ?></td>
            <?php } else {  ?>
            <td style=""></td>
            <td style=""></td>
            <?php } ?>
          </tr>
          <tr>
            <td></td>
          </tr>
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
            <td style=""><?php echo round(max($iHighestARayInCls),2); ?></td>
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
            <td style=""><?php echo $db->getVal("select total_days_open from class_teacher_roll_call_bulk where student_id='".$iStudent['student_id']."' and session_id='".$_GET['session']."' and term_id='".$_GET['term_id']."' and class_id='".$iStudent['class']."' and create_by_userid='".$create_by_userid."'"); ?></td>
            <?php } ?>
            <?php if($iSchoolPDFsetting['is_daypresent']=='1') { ?>
            <td style=""><b>Day(s) Present:</b></td>
            <td style=""><?php echo $db->getVal("select present from class_teacher_roll_call_bulk where student_id='".$iStudent['student_id']."' and session_id='".$_GET['session']."' and term_id='".$_GET['term_id']."' and class_id='".$iStudent['class']."' and create_by_userid='".$create_by_userid."'"); ?></td>
            <?php } ?>
            <?php if($iSchoolPDFsetting['is_dayabsent']=='1') { ?>
            <td style=""><b>Day(s) Absent:</b></td>
            <td style=""><?php echo $db->getVal("select absent from class_teacher_roll_call_bulk where student_id='".$iStudent['student_id']."' and session_id='".$_GET['session']."' and term_id='".$_GET['term_id']."' and class_id='".$iStudent['class']."' and create_by_userid='".$create_by_userid."'"); ?></td>
            <?php } else {  ?>
            <td style=""></td>
            <td style=""></td>
            <?php } ?>
          </tr>
        </table></td>
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
      <td style="border: 1px solid #000000;text-align: center;"><?php echo $db->getVal("select assesment from school_assessment where id='".$Val."'"); ?> <br>
        (<?php echo $db->getVal("select percentage from score_entry_time_frame where assesment_id='".$Val."'") ?>)</td>
      <?php } ?>
      <td style="border: 1px solid #000000;text-align: center;">TOTAL<br> (100%)</td>
      <td style="border: 1px solid #000000;text-align: center;">GRD</td>
      <td style="border: 1px solid #000000;text-align: center; <?php if($iSchoolPDFsetting['is_pos']!='1') { echo 'display:none'; }  ?>">POS</td>
      <td style="border: 1px solid #000000;text-align: center; <?php if($iSchoolPDFsetting['is_out']!='1') { echo 'display:none'; } ?>">OUT OF</td>
      <td style="border: 1px solid #000000;text-align: center;  <?php if($iSchoolPDFsetting['is_lowest_avg']!='1') { echo 'display:none'; }  ?>">LOW.IN<br>
        CLASS</td>
      <td style="border: 1px solid #000000;text-align: center;  <?php if($iSchoolPDFsetting['is_highest_avg']!='1') { echo 'display:none'; }  ?>">HIGH.IN<br>
        CLASS</td>
      <td style="border: 1px solid #000000;text-align: center;  <?php if($iSchoolPDFsetting['is_class_avg']!='1') { echo 'display:none'; }  ?>">CLASS<br>
        AVG</td>
      <td style="border: 1px solid #000000;text-align: center;">REMARKS</td>
    </tr>
    <?php  
		//$subjectdetail=$db->getRows("select * from school_subject where class_id = '" .$iStudent['class']. "' and create_by_userid='".$create_by_userid."'");
		$tSub=0;
		foreach ($subjectdetails as $Ilist) { 
		
		
											 
  $iAlreadyRemoved = $db->getVal("select id from  student_subject_remove where create_by_userid='".$create_by_userid."' and subjectid='".$Ilist['id']."'and studentid='".$iStudent['id']."'");
 
					if($iAlreadyRemoved=='') {	
				$tSub++;	
		?>
    <tr style="height: 35px;">
      <td class="fontsubject" style="font-weight: 100; border: 1px solid #000000;"><?php echo ($Ilist['subject']); ?></td>
      <?php $count=0;
          $avgTotal = 0;
			foreach($totalAssesment as $Val) 
      {   
        $count=$count+1;
        $hasScore = false;
        foreach($allQuery as $iAll) {
            if($iAll['student_id'] == $iStudent['id'] && $iAll['subject_id'] == $Ilist['id'] && $iAll['assesment_id'] == $Val){
              $scores = $iAll;
              $hasScore = true;
            }
            if($iAll['subject_id'] == $Ilist['id'] && $iAll['assesment_id'] == $Val){
              $avgTotal += $iAll['score'];
            }
        }
     
				?>
      <td style="font-weight: 100; text-align: center;  border: 1px solid #000000; text-transform:capitalize"><?php  echo $hasScore ? $scores['score'] : 0;  // if($scores['inpgradescore']=='1') {  echo $scores['score']; } else { echo $scores['score_grade']; } ?></td>
      <?php 
				${"s" . $Ilist['id']}+= $hasScore ? $scores['score'] : 0;
			} 
			$grandTotal = ${"s" . $Ilist['id']};
			?>
      <td style="  font-weight: 100; text-align: center;  border: 1px solid #000000;"><?php
				echo round($grandTotal);
				?></td>
      <td style="font-weight: 100; text-align: center;  border: 1px solid #000000;">
	  <?php  $gradding=$db->getRow("select grade, description, comment from school_grade where  create_by_userid='".$create_by_userid."' and minimum_number <= ".$grandTotal." and maximum_number >= ".$grandTotal."");
	 		echo $gradding['grade'];
		?></td>
      <td style="font-weight: 100; text-align: center;  border: 1px solid #000000; <?php if($iSchoolPDFsetting['is_pos']!='1') { echo 'display:none'; }  ?>"><?php   $aiiisubs=$Ilist['id']; echo $_SESSION["$aiiisubs"]; unset($_SESSION["$aiiisubs"]); ?></td>
      <td style="font-weight: 100; text-align: center;  border: 1px solid #000000;  <?php if($iSchoolPDFsetting['is_out']!='1') { echo 'display:none'; }  ?>"><?php echo $outOf= count($studentIds); ?></td>
      <td style="font-weight: 100; text-align: center;  border: 1px solid #000000; <?php if($iSchoolPDFsetting['is_lowest_avg']!='1') { echo 'display:none'; }  ?>"><?php echo round(min( ${"highLows".$Ilist['id']} ),2); ?></td>
      <td style="font-weight: 100; text-align: center;  border: 1px solid #000000; <?php if($iSchoolPDFsetting['is_highest_avg']!='1') { echo 'display:none'; }  ?>"><?php  echo round(max( ${"highLows".$Ilist['id']}  ),2);  ?></td>
      <td style="font-weight: 100; text-align: center;  border: 1px solid #000000; <?php if($iSchoolPDFsetting['is_class_avg']!='1') { echo 'display:none'; }  ?>"><?php 
                 $getClasAv= $avgTotal/$outOf;
				 echo round($getClasAv,2);
				?></td>
      <td style="font-weight: 100; text-align: center;  border: 1px solid #000000;"><?php echo $gradding['description']; //$db->getVal("select description from school_grade where create_by_userid='".$create_by_userid."' and maximum_number > ".$grandTotal." or maximum_number = ".$grandTotal."");?></td>
    </tr>
   
   
    <?php } } ?>
  </tbody>
</table>
<table cellspacing="0"; style="width: 100%;">
  <tbody style="font-family: sans-serif;">
    <tr>
      <td style="width:100%;" ><table style="width:100%;">
          <br>
          <tr style=" <?php if($iSchoolPDFsetting['is_no_of_subjects']!='1') { echo 'display:none'; }  ?> ">
            <td style="" colspan="3">GRADE DETAILS:</td>
            <td style="text-align: center;" colspan="3">No. of Subjects: <?php echo $tSub; ?></td>
          </tr>
          <tr style=" <?php if($iSchoolPDFsetting['is_grade_details']!='1') { echo 'display:none'; }  ?> ">
            <td style="" colspan="3"><?php
						$iGradingAll=$db->getRows("select * from school_grade where create_by_userid='".$create_by_userid."' order by id desc");
						foreach($iGradingAll as $iList)
						{
						?>
              <?php echo $iList['grade']; ?> = <?php echo $iList['minimum_number']; ?>-<?php echo $iList['maximum_number'].','; ?>
              <?php } ?></td>
            <td style="text-align: center;" colspan="3"></td>
          </tr>
        </table></td>
    </tr>
  </tbody>
</table>
<table cellspacing="0"; style="width: 100%;">
  <tbody style="font-family: sans-serif;">
    <tr>
      <td style="width:100%;" ><table style="width:100%;">
          <br>
          <tr>
            <?php if($iSchoolPDFsetting['is_affective']=='1') { ?>
            <td style="padding: 0;vertical-align: baseline;width: 50%;">
            <table cellspacing="0"; style="width: 95%;">
                <tbody style="font-size: 16px;font-family: sans-serif;">
                  <tr style="height: 40px;">
                    <td style="  border: 1px solid #000000;"><?php if($iSchoolPDFsetting['title_4']=='') { echo "AFFECTIVE TRAITS"; } else { echo $iSchoolPDFsetting['title_4']; } ?></td>
                    <td style="  border: 1px solid #000000;text-align: center;">RATING</td>
                  </tr>
                  <?php
					$iManageTraitsAll=$db->getRows("select * from manage_traits where create_by_userid='".$create_by_userid."'");
					foreach($iManageTraitsAll as $iList)
					{
						$iTraits=$db->getRow("select * from student_traits_class_teacher where traits_id='".$iList['id']."' and student_id='".$iStudent['id']."'");
					?>
                  <tr style="height: 40px;">
                    <td style="border: 1px solid #000000;"><?php echo $iList['trait']; ?></td>
                    <td style="border: 1px solid #000000;text-align: center;text-transform: capitalize;"><?php echo $iTraits['trait']; ?></td>
                  </tr>
                  <?php } ?>
                </tbody>
              </table>
             </td>
            <?php } ?>
          
            <td style="padding: 0;vertical-align: baseline;width: 50%;"><?php if($iSchoolPDFsetting['is_phycomotor']=='1') { ?>
              <table cellspacing="0";  style="width:500px">
                <tbody style="font-size: 16px;font-family: sans-serif;">
                  <tr style="height: 40px;">
                    <td style="  border: 1px solid #000000;width:300px;"><?php if($iSchoolPDFsetting['title_5']=='') { echo "PSYCHOMOTOR"; } else { echo $iSchoolPDFsetting['title_5']; } ?></td>
                    <td style="  border: 1px solid #000000;text-align: center;">RATING</td>
                    
                  </tr>
                  <?php
					$iManagePsychometerAll=$db->getRows("select * from manage_phycomotor where create_by_userid='".$create_by_userid."'");
					foreach($iManagePsychometerAll as $iList)
					{
						$iPsychometer=$db->getRow("select * from student_pyschomotor_class_teacher where pyschmotor_id='".$iList['id']."' and student_id='".$iStudent['id']."'");
					?>
                  <tr style="height: 40px;">
                    <td style="border: 1px solid #000000;width:300px;" ><?php echo $iList['phycomotor']; ?></td>
                    <td style="border: 1px solid #000000;text-transform: capitalize;text-align: center;"><?php echo $iPsychometer['pyschmotor']; ?></td>
                  
                  </tr>
                  <?php } ?>
                </tbody>
              </table>
              <?php } ?>
              <br>
              <table cellspacing="0"  style="width:500px">
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
                    <td style="border:1px solid #000000;"><?php echo $iList['rating']; ?>-
                      <?php   echo $iList['review']; ?></td>
                  </tr>
                  <?php } ?>
                </tbody>
              </table>
             </td>
          </tr>
        </table></td>
    </tr>
    <tr> </tr>
  </tbody>
</table>
<br>
<hr>
<br>
<table cellspacing="0"; style="width: 100%;">
  <tbody style="font-family: sans-serif;">
    <tr>
      <td style="width:100%;" ><table style="width:100%;">
          <tr>
            <?php
					$iFormTeacher=$db->getRow("select * from class_teacher where school_class='".$iStudent['class']."' and school_session='".$_GET['session']."'");
					
					$iTeacher=$db->getRow("select * from staff_manage where id='".$iFormTeacher['staff_id']."'");
					?>
            <td style=""><?php if($iSchoolPDFsetting['title_1']=='') { echo 'Class Teacher'; } else { echo $iSchoolPDFsetting['title_1']; } ?>
              :</td>
            <td style=""><?php echo $iTeacher['first_name'].' '.$iTeacher['last_name']; ?></td>
          </tr>
          <tr>
            <td style=""><?php if($iSchoolPDFsetting['title_2']=='') { echo "Class Teacher's Remarks"; } else { echo $iSchoolPDFsetting['title_2']; } ?>
              :</td>
            <td style=""><?php echo $db->getVal("select comments from clas_teacher_make_comment where student_id='".$iStudent['id']."' "); ?></td>
          </tr>
          <tr>
           <!--  <?php
						$iPrinciple=$db->getRow("select * from assign_role where principal='1' and create_by_userid='".$create_by_userid."'");
						
						$iStaffPrinciple=$db->getRow("select * from staff_manage where id='".$iPrinciple['staff_id']."'");
						?> -->
            <td style=""><?php if($iSchoolPDFsetting['title_3']=='') { echo "Principal's Remarks"; } else { echo $iSchoolPDFsetting['title_3']; } ?>
              :</td>
            <td style=""><?php  $iPrinciPleRemarkComment =  $db->getVal("select comments from principle_remarks where student_id='".$iStudent['id']."'");
				if($iPrinciPleRemarkComment!='') { echo $iPrinciPleRemarkComment; } 
				else {                                        
				echo $gradding['comment'];
				}
			 ?></td>
            <?php $sign_term=$db->getRow("select sign from  principal_sign_nextTerm where  create_by_userid='".$create_by_userid."' order by id desc"); ?>
            <?php $sign_termdate=$db->getRow("select nextTerm from  principal_set_nextTerm where  create_by_userid='".$create_by_userid."' and session_id='".$_GET['session']."' and term_id='".$_GET['term_id']."' order by id desc"); ?>
            <td style="width: 200px;"><img  src="<?php echo 'https://'.$_SERVER['HTTP_HOST'].'/uploads/'.$sign_term['sign']; ?>" style="width: 200px;"></td>
          </tr>
          <tr>
            <td style="">Next Term Begins:</td>
            <td style=""><?php echo $sign_termdate['nextTerm']; ?></td>
          </tr>
        </table></td>
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
$options->set('isRemoteEnabled', TRUE);
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