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
$studentidFirstTerm = $_GET['studentidFirstTerm'];
$studentidSecondTerm = $_GET['studentidSecondTerm'];
$studentidThirdTerm = $_GET['studentidThirdTerm'];
$termid = $_GET['termid'];
$iStudent=$db->getRow("select * from manage_student where randomid='".$_GET['randomid']."'");
$iStudentThirdTerm= $iStudent;//$db->getRow("select * from manage_student where randomid='".$_GET['randomidThirdTerm']."'");
$iSession=$db->getRow("select * from school_session where id='".$_GET['session']."' and create_by_userid='".$create_by_userid."' ");
$iClass=$db->getRow("select * from school_class where id='".$iStudent['class']."'  and create_by_userid='".$create_by_userid."'");
 $iSchoolPDFsetting=$db->getRow("select * from school_pdfsetting where  section_id = '".$iClass['section_id']."'");
 
 $iManageStudentId=$db->getRows("select id from manage_student where student_id = '" .$_GET['student_id']. "' ");
 $iManageStudentIds=$db->getRows("select id, student_id from manage_student where create_by_userid='".$create_by_userid."' and session='".$_GET['session']."'");
 foreach ($iManageStudentId as $iStuDeIdS) {	
		 $iCommaSeparatedStudent .= $iStuDeIdS['id'].',';
 }
 foreach ($iManageStudentIds as $iStuDeIdS) {	
		 $iCommaSeparatedStudent2 .= $iStuDeIdS['id'].',';
 }
$iCommaSeparatedStudent = rtrim($iCommaSeparatedStudent,',');
$iCommaSeparatedStudentArr = explode(',',$iCommaSeparatedStudent);
$iCommaSeparatedStudent2 = rtrim($iCommaSeparatedStudent2,',');
$iCommaSeparatedStudent2Arr = explode(',',$iCommaSeparatedStudent2Arr);


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
WHERE schoolClass.id ='".$iStudent['class']."' AND classScore.create_by_userid = '".$create_by_userid."' AND student.session = '".$_GET['session']."'";
$orderBy = " order by student.first_name asc";
$allQuery = $db->getRows($_query . $orderBy);
$allQuery1 = $db->getRows($_query . " AND student.term_id ='".$studentidFirstTerm."'" . $orderBy);
$allQuery2 = $db->getRows($_query . " AND student.term_id ='".$studentidSecondTerm."'" . $orderBy);
$allQuery3 = $db->getRows($_query . " AND student.term_id ='".$studentidThirdTerm."'" . $orderBy);

// echo "QUERY QUERY  QUERY". $_query;
//    exit;  

//$iSchoolPDFsetting= $allQuery[0];
//$iStudent = $allQuery[0];
$subjectdetails=$db->getRows("select * from school_subject where class_id = '" .$iStudent['class']. "' and create_by_userid='".$create_by_userid."'");
//$subjectdetails = toUnique($allQuery, "subjectId");
$studentIds = toUnique($allQuery, "studentId");
$scoreData = $allQuery;

    $tStuden= count($studentIds);
    
    $scorelist1=$db->getRows("select * from input_score_class_teacher where class_id='".$iClass['id']."' and create_by_userid='".$create_by_userid."'");
 
$totalScore = array();

$iTotalStudents0 = $db->getRows("select * from manage_student where class='".$iClass['id']."' and   create_by_userid='".$create_by_userid."' and session='".$_GET['session']."' and term_id='".$termid."'");

function getScoreSum($list, $sid){
     $sum = 0;
     foreach ($list as $scorelistItem) {
         if($scorelistItem['student_id'] == $sid){
             $sum += $scorelistItem['score'];
         }
     }
     return $sum;
 }
foreach ($subjectdetails as $Ilists) { 
$aiiisub=$Ilists['id'];
$i=0;

  $scoreTotal= 0;
      foreach($totalAssesment as $Val) 
      { 
        foreach($scorelist1 as $sItem) {
            if($sItem['assesment_id'] == $Val && $sItem['subject_id'] == $Ilists['id']){
                 foreach ($iManageStudentIds as $iStuDeIdS) {	
                     if($iStuDeIdS['id'] == $sItem['student_id']){
                          $iInputScore = round(($sItem['score']+0)/3); 
                             $totalScore[$iStuDeIdS['student_id']] += $iInputScore; 
                         ${"$aiiisub".$iStuDeIdS['student_id']}+=$iInputScore;
                		 break;
                     }
                 }
            }
        }
      }
      $a=$iList['studentId'];
      $student_two["$a"] = ${"$aiiisub".$iList['studentId']}; 
${"classTotal".$Ilists['id']} = $scoreTotal;
${"highLows".$Ilists['id']}[]= $scoreTotal;

$getClasAvg[]= ${"classTotal".$Ilists['id']}/$tStuden;


$rankedScoresNew = setPositionNew($student_two);


foreach ($rankedScoresNew as $studentwa => $data) {
	

	if($iStudent['id']==$studentwa){
	 $_SESSION["$aiiisub"] = $data['rank'];
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

$subdetaisForToal=$db->getRows("select id from school_subject where class_id = '" .$iStudent['class']. "' and create_by_userid='".$create_by_userid."'");
$tSubtotal=0;
foreach ($subdetaisForToal as $IlistTotal) { 

   $iAlreadyRemoved = $db->getVal("select id from  student_subject_remove where create_by_userid='".$create_by_userid."' and subjectid='".$IlistTotal['id']."' and studentid='".$iStudent['id']."'");
  
  if($iAlreadyRemoved=='') {  
  
$tSubtotal++;

foreach($totalAssesment as $Val) 
{   
    $iInputScore=$db->getVal("select SUM(score) from input_score_class_teacher where  assesment_id='".$Val."' and student_id IN ($iCommaSeparatedStudent) 
 and subject_id='".$IlistTotal['id']."' and create_by_userid='".$create_by_userid."'");

${"stotal" . $IlistTotal['id']}+= round($iInputScore/3);
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
arsort($totalScore);
//print_r($totalScore);
// $i = 0;

// foreach($val as $key => $v){
//       $rank = 11;
//   if($iStudent['id']==$key){
//       $rank = $v;
// 	// echo $_SESSION["board_$studentwaf"]=$i;
       
//       $rank = 41;
// 	} 
// 	$i++;
// }
// foreach($totalScore as $key => $val){
//     if($val == $rank){
//         if($key == 0){
//              $rank = 1;
//         }else{
//          $rank = $key+=1;
//         }
//     }
// }

$rank = array_search($iStudent['student_id'].'',array_keys($totalScore)) + 1;

$iTotalStudents = count($iTotalStudents0);// $db->getVal("select count(*) from manage_student where class='".$iClass['id']."' and   create_by_userid='".$create_by_userid."' and session='".$_GET['session']."' and term_id='".$termid."'");
?>



      <?php
      ${"s3bThird"} = 0;
      foreach ($subjectdetails as $Ilist) {
      $count = 0;
      
      foreach($totalAssesment as $Val) 
      {   
        $count=$count+1;
        foreach($allQuery as $iAll0) {
            if($iAll0['subject_id'] == $Ilist['id'] && $iAll0['assesment_id'] == $Val){
                if($studentidThirdTerm == $iAll0['student_id']) // third term
              ${"s3bThird"} += round($iAll0['score']);
              
               // $iscoreThirdTerm .= ' '. $iAll0['student_id'];
            }
        }
        $count++;
	  } 
    } 
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
            <td style=""><?php echo ${"s3bThird"} == 0 ? "--" : $graddingg=$db->getVal("select grade from school_grade where create_by_userid='".$create_by_userid."' and minimum_number <= ".$ggrade." and maximum_number >= ".$ggrade."");?></td>
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
            <td style=""><b>Final Position:</b></td>
            <td style=""><?php $af= $iStudent['id']; echo  ${"s3bThird"} == 0 ? "--" :  $rank; unset($_SESSION["board_$af"]); ?></td>
            <?php } ?>
            <?php if($iSchoolPDFsetting['is_totalstudent']=='1') { ?>
            <td style=""><b>Total Student:</b></td>
            <td style=""><?php echo $iTotalStudents > 0 ? $iTotalStudents : "---"; ?></td>
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
            <td style="">CUMULATIVE</td>
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
            <td style=""><?php echo $db->getVal("select sum(total_days_open) from class_teacher_roll_call_bulk where student_id='".$iStudent['student_id']."' and session_id='".$_GET['session']."' and class_id='".$iStudent['class']."' and create_by_userid='".$create_by_userid."'"); ?></td>
            <?php } ?>
            <?php if($iSchoolPDFsetting['is_daypresent']=='1') { ?>
            <td style=""><b>Day(s) Present:</b></td>
            <td style=""><?php echo $db->getVal("select sum(present) from class_teacher_roll_call_bulk where student_id='".$iStudent['student_id']."' and session_id='".$_GET['session']."' and class_id='".$iStudent['class']."' and create_by_userid='".$create_by_userid."'"); ?></td>
            <?php } ?>
            <?php if($iSchoolPDFsetting['is_dayabsent']=='1') { ?>
            <td style=""><b>Day(s) Absent:</b></td>
            <td style=""><?php echo $db->getVal("select sum(absent) from class_teacher_roll_call_bulk where student_id='".$iStudent['student_id']."' and session_id='".$_GET['session']."' and class_id='".$iStudent['class']."' and create_by_userid='".$create_by_userid."'"); ?></td>
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
      <td style="border: 1px solid #000000;text-align: center;">FIRST TERM</td>
      <td style="border: 1px solid #000000;text-align: center;">SECOND TERM</td>
      <td style="border: 1px solid #000000;text-align: center;">THIRD TERM</td>
      
      <td style="border: 1px solid #000000;text-align: center; width:150px" colspan="2">
          <table cellspacing="0"; style="text-align: center; width: 100%;">
      <tr> <td colspan="2" style="white-space: nowrap;">THIRD TERM</td></tr><tr>
      <?php
			foreach($totalAssesment as $Val) 
			{
			?>
      <td style="border: 1px solid #000000;text-align: center; font-size: 11px;"><?php echo $db->getVal("select assesment from school_assessment where id='".$Val."'"); ?> <br>
        (<?php echo $db->getVal("select percentage from score_entry_time_frame where assesment_id='".$Val."'") ?>)</td>
      <?php } ?></tr>
      </table></td>
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
      
      <?php
      $count = 0;
      $scoreTotalCont = 0;
      $scoreTotalExam = 0;
      $scoreTotalContAll = 0;
      $scoreTotalExamAll = 0;
      $scoreTotalAll = 0;
      $avgTotal = 0;
			foreach($totalAssesment as $Val) 
      {
              $scoreFirstTerm = 0;
              $scoreSecondTerm = 0;
              $scoreThirdTerm = 0;
        foreach($allQuery as $iAll) {
            if($iAll['subject_id'] == $Ilist['id'] && $iAll['assesment_id'] == $Val){
                if($studentidFirstTerm == $iAll['student_id']) // First term
              $scoreFirstTerm += $iAll['score'];
                else if($studentidSecondTerm == $iAll['student_id']) // Second term
              $scoreSecondTerm += $iAll['score'];
              else if($studentidThirdTerm == $iAll['student_id']) { // Third third
                $scoreThirdTerm += $iAll['score'];
               // $iscoreThirdTerm .= ' '. $iAll['student_id'];
              }
               // break;
                
              $avgTotal += $iAll['score'];
            }
        }
        $calculateTot = ($scoreFirstTerm + $scoreSecondTerm + $scoreThirdTerm) / 3;
        if($count == 0) $scoreTotalCont =  round($scoreThirdTerm);//round($calculateTot);
        else $scoreTotalExam = round($scoreThirdTerm); // round($calculateTot);
        $scoreTotalThirdTerm = $scoreTotalCont + $scoreTotalExam;
        if($count == 0) $scoreTotalContAll =  round($calculateTot);
        else $scoreTotalExamAll = round($calculateTot);
        $scoreTotalAll = $scoreTotalContAll + $scoreTotalExamAll;
        
        ${"stot3rd" . $Ilist['id']}=$scoreTotalThirdTerm;
        ${"stot" . $Ilist['id']}=$scoreTotalAll;
        ${"scon" . $Ilist['id']}=$scoreTotalCont;
        ${"sexa" . $Ilist['id']}=$scoreTotalExam;
        
        ${"s1" . $Ilist['id']}+=$scoreFirstTerm;
        ${"s2" . $Ilist['id']}+=$scoreSecondTerm;
        ${"s3" . $Ilist['id']} += $scoreThirdTerm;//$scoreTotalCont + $scoreTotalExam;
        ${"s3total" . $Ilist['id']} += ${"s3" . $Ilist['id']};
        $count++;
			} 
			?>
			
      <td class="fontsubject" style="font-weight: 100; border: 1px solid #000000;"><?php echo ${"s1" . $Ilist['id']};?></td>
      <td class="fontsubject" style="font-weight: 100; border: 1px solid #000000;"><?php echo ${"s2" . $Ilist['id']};?></td>
      <td class="fontsubject" style="font-weight: 100; border: 1px solid #000000;"><?php echo ${"s3bThird"} == 0 ? "--" : ${"s3" . $Ilist['id']};?></td>
      <td class="fontsubject" style="font-weight: 100; border: 1px solid #000000;"><?php echo ${"s3bThird"} == 0 ? "--" : ${"scon" . $Ilist['id']};?></td>
      <td class="fontsubject" style="font-weight: 100; border: 1px solid #000000;"><?php echo ${"s3bThird"} == 0 ? "--" : ${"sexa" . $Ilist['id']};?></td>
      <td class="fontsubject" style="font-weight: 100; border: 1px solid #000000;"><?php echo ${"stot" . $Ilist['id']};?></td>
      <?php 
			$grandTotal = ${"stot" . $Ilist['id']};
			?>
      <td style="font-weight: 100; text-align: center;  border: 1px solid #000000;">
	  <?php  $gradding=$db->getRow("select grade, description, comment from school_grade where  create_by_userid='".$create_by_userid."' and minimum_number <= ".$grandTotal." and maximum_number >= ".$grandTotal."");
	 		echo ${"s3bThird"} == 0 ? "--" : $gradding['grade'];
		?></td>
      <td style="font-weight: 100; text-align: center;  border: 1px solid #000000; <?php if($iSchoolPDFsetting['is_pos']!='1') { echo 'display:none'; }  ?>"><?php   $aiiisubs=$Ilist['id']; echo ${"s3" . $Ilist['id']} == 0 ? "--" : $_SESSION["$aiiisubs"]; unset($_SESSION["$aiiisubs"]); ?></td>
      <td style="font-weight: 100; text-align: center;  border: 1px solid #000000;  <?php if($iSchoolPDFsetting['is_out']!='1') { echo 'display:none'; }  ?>"><?php $outOf= count($studentIds); echo ${"s3" . $Ilist['id']} == 0 ? "--" : $outof; ?></td>
      <td style="font-weight: 100; text-align: center;  border: 1px solid #000000; <?php if($iSchoolPDFsetting['is_lowest_avg']!='1') { echo 'display:none'; }  ?>"><?php echo ${"s3" . $Ilist['id']} == 0 ? "--" : round(min( ${"highLows".$Ilist['id']} ),2); ?></td>
      <td style="font-weight: 100; text-align: center;  border: 1px solid #000000; <?php if($iSchoolPDFsetting['is_highest_avg']!='1') { echo 'display:none'; }  ?>"><?php  echo ${"s3" . $Ilist['id']} == 0 ? "--" :  round(max( ${"highLows".$Ilist['id']}  ),2);  ?></td>
      <td style="font-weight: 100; text-align: center;  border: 1px solid #000000; <?php if($iSchoolPDFsetting['is_class_avg']!='1') { echo 'display:none'; }  ?>"><?php 
                 $getClasAv= $avgTotal/$outOf;
				 echo ${"s3bThird"} == 0 ? "--": round($getClasAv,2);
				?></td>
      <td style="font-weight: 100; text-align: center;  border: 1px solid #000000;"><?php echo ${"s3bThird"} == 0 ? "--" : $gradding['description']; //$db->getVal("select description from school_grade where create_by_userid='".$create_by_userid."' and maximum_number > ".$grandTotal." or maximum_number = ".$grandTotal."");?></td>
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
                    <td style="border: 1px solid #000000;text-align: center;text-transform: capitalize;"><?php echo ${"s3" . $Ilist['id']} == 0 ? "" : $iTraits['trait']; ?></td>
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
                    <td style="border: 1px solid #000000;text-transform: capitalize;text-align: center; "><?php echo ${"s3" . $Ilist['id']} == 0 ? "" : $iPsychometer['pyschmotor']; ?></td>
                  
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
            <td style=""><?php if(${"s3bThird"} > 0) echo $db->getVal("select comments from clas_teacher_make_comment where student_id='".$studentidThirdTerm."' "); ?></td>
          </tr>
          <tr>
           <!--  <?php
						$iPrinciple=$db->getRow("select * from assign_role where principal='1' and create_by_userid='".$create_by_userid."'");
						
						$iStaffPrinciple=$db->getRow("select * from staff_manage where id='".$iPrinciple['staff_id']."'");
						?> -->
            <td style=""><?php if($iSchoolPDFsetting['title_3']=='') { echo "Principal's Remarks"; } else { echo $iSchoolPDFsetting['title_3']; } ?>
              :</td>
            <td style=""><?php if(${"s3bThird"} > 0){
                $iPrinciPleRemarkComment =  $db->getVal("select comments from principle_remarks where student_id='".$studentidThirdTerm."'");
				if($iPrinciPleRemarkComment!='') { echo ${"s3bThird"} == 0 ? "" : $iPrinciPleRemarkComment; } 
				else {                                        
				echo ${"s3bThird"} == 0 ? "" : $gradding['comment'];
				}
            }  
			 ?></td>
            <?php $sign_term=$db->getRow("select sign from  principal_sign_nextTerm where  create_by_userid='".$create_by_userid."' order by id desc"); ?>
            <?php $sign_termdate=$db->getRow("select nextTerm from  principal_set_nextTerm where  create_by_userid='".$create_by_userid."' and session_id='".$_GET['session']."' and term_id='".$_GET['termid']."' order by id desc"); ?>
            <td style="width: 200px;"><img  src="<?php echo 'https://'.$_SERVER['HTTP_HOST'].'/uploads/'.$sign_term['sign']; ?>" style="width: 200px;"></td>
          </tr>
          <tr>
            <td style="">Next Term Begins:</td>
            <td style=""><?php 
            echo $sign_termdate['nextTerm']; 
            // echo json_encode($totalScore);
            ?></td>
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