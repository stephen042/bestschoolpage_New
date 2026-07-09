<?php include('../config.php'); 
include('inc.session-create.php'); 
function randomFixnewEx($length)
{
	$random= "";
	srand((double)microtime()*1000000);
	$data = "1234567890";
	for($i = 0; $i < $length; $i++)
	{
		$random .= substr($data, (rand()%(strlen($data))), 1);
	}
	return $random;
}
$iClass=$db->getRow("select * from school_class where randomid='".$_POST['randomid']."'");
$filename = date("Y-m-d-h-i-s")."_".randomFixnewEx(8).".csv";





header("Content-type: text/csv");
header("Content-Disposition: attachment; filename=$filename");
header("Pragma: no-cache");
header("Expires: 0"); 




	 $pdfAssesment=implode('-',$_POST['assesment']); 
	 $assesment=$db->getRow("select * from score_entry_time_frame where assesment_id='".$_POST['assesment']."'"); 
	 $iAssesment=implode(',',$_POST['assesment']);

	$content=array();
	$iSubjects=$db->getRows("select * from school_subject where  class_id='".$iClass['id']."'");
	$iSubject='';
	$iCountSubject=count($iSubjects);
	$toalSub=0;
	foreach($iSubjects as $iList) { 
	$toalSub=$toalSub+1;
			$iSubject .=	'"'.$iList['subject'].'", ';
		}
	
	$Maintitle = 'student ID, First Name, Last Name,';
	
	foreach($iSubjects as $iList) { 
			$Maintitle .= $iList['subject'].', ';
		}
	$Maintitle .= 'Total, Average, Grade, Position';
	$title	 = explode (",", $Maintitle);

	$rw=array();
	$search= '';

$tStuden=0;
$aryList11=$db->getRows("select * from manage_student where class='".$iClass['id']."' and session='".$_POST['session']."' and term_id='".$_POST['term_id']."' and create_by_userid='".$create_by_userid."'");
foreach($aryList11 as $iList)
		{
			
			$tStuden=$tStuden+1;
$MaintitleData = $iList['student_id'].','.$iList['first_name'].','.$iList['last_name'].',';


$iSum=0;
$iSubjectsNew=$db->getRows("select * from school_subject where  class_id='".$iClass['id']."'");
				foreach($iSubjectsNew as $iListNew) { 
		 
$iInputScore=$db->getVal("select SUM(score) from input_score_class_teacher where assesment_id IN ($iAssesment) and student_id='".$iList['id']."' and session_id='".$_POST['session']."' and term_id='".$_POST['term_id']."' and class_id='".$iList['class']."' and subject_id='".$iListNew['id']."'");


$MaintitleData .= $iInputScore.','; 
					 		 $iScore = $iInputScore; 
					 		 $iSum += $iScore;

				}
				
$MaintitleData .= $iSum.',';
							$student_two["$a"] = $iSum;  
							$classTotal+=$iSum;				
				
				$avg=$iSum/$toalSub;
                $highLow[]=$avg;
$MaintitleData .= round($avg,2) .',' ;

$gradding=$db->getVal("select grade from school_grade where create_by_userid='".$create_by_userid."'  and minimum_number <= ".$avg." and maximum_number >= ".$avg."");

$MaintitleData .= $gradding .',' ;

$rw[]	 = explode (",", $MaintitleData);
				

			}

$rw[]	 = 'No. of Students:  ,'.$tStuden.',';


$content[]=$rw;
$output = fopen('php://output', 'w');
fputcsv($output, $title);
foreach($content[0] as $key => $val)
	{
		fputcsv($output, $val);
	}
?>












