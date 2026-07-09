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


$filename = date("Y-m-d-h-i-s")."_".randomFixnewEx(8).".csv";
header("Content-type: text/csv");
header("Content-Disposition: attachment; filename=$filename");
header("Pragma: no-cache");
header("Expires: 0"); 

	$content=array();
	$title = array("student ID", "First Name", "Other Name", "Last Name", "Gender", "Class", "Session", "Term", "Nationality", "LGA", "Religion", "Father ID", "Father First Name", "Father Last Name", "Phone Number", "Email Id", "Address Line 1");

	$rw=array();
	$search= '';
	 if($_GET['session']!=''){
				$search.="and session='".$_GET['session']."'";
			}
	 if($_GET['term_id']!=''){
				$search.="and term_id='".$_GET['term_id']."'";
			}
	 $aryList = $db->getRows("select * from manage_student where  create_by_userid='".$create_by_userid."' $search order by id desc");
	 $allIdsWithParent = '';
	 foreach($aryList as $iList) {
	     $iFather0 = $db->getRow("select * from student_guardian where student_id='".$iList['id']."' and type=1 order by id asc");
        if(count($iFather0) > 0 && $iFather0['student_id_str'] == null){
            $iFather0['student_id_str'] = $iList['student_id'];
            $db->updateAry("student_guardian", $iFather0,  "where  student_id='".$iList['id']."'");
        }
	 }
     foreach($aryList as $iList) 
			{
				
				if($iList['usertype']=='1') { $iUserType =  "STAFF"; } 
				if($iList['usertype']=='2') { $iUserType =  "PARENT"; }
				
				if($iList['status']=='1') { $iUserStatus = "Active"; } 
				if($iList['status']=='0') { $iUserStatus = "InActive"; }  
				if($iList['status']=='2') { $iUserStatus = "Deactivated"; }
				
				
$iUserSession 	= $db->getVal("select session from school_session where id='".$iList['session']."' and create_by_userid='".$create_by_userid."'"); 
$iUserTerms 	= $db->getVal("select term from school_term where id='".$iList['term_id']."' and create_by_userid='".$create_by_userid."'"); 
$iUserClass 	= $db->getVal("select short_name from school_class where id='".$iList['class']."' and create_by_userid='".$create_by_userid."'"); 
$iNationality   = $db->getVal("select country_name from nationality where id='".$iList['nationality']."'");
$iLGAorgin   	= $db->getVal("select title from local_government where id='".$iList['lga_of_origin']."'");

$iReligion   	= $db->getVal("select title from religion where id='".$iList['religion']."'");

$iFather=$db->getRow("select * from student_guardian where student_id_str='".$iList['student_id']."' and type=1 order by id asc");
				
$rw[]=array($iList['student_id'], $iList['first_name'], $iList['other_name'], $iList['last_name'], $iList['gender'], $iUserClass, $iUserSession,$iUserTerms, $iNationality, $iLGAorgin, $iReligion, $iFather['parent_id'], $iFather['first_name'], $iFather['last_name'], $iFather['phone'], $iFather['email'], $iFather['home_Address_1']);

			}

$content[]=$rw;
$output = fopen('php://output', 'w');
fputcsv($output, $title);
foreach($content[0] as $key => $val)
	{
		fputcsv($output, $val);
	}
?>












