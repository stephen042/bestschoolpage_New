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


if($_GET['action']=='verification_status') {

	$content=array();
	$title = array("Username", "Full Name", "Mobile No", "UserType", "Email", "Password", "Status");


	$rw=array();

	$aryList = $db->getRows("select * from school_register where create_by_userid='".$create_by_userid."' order by id desc");
     foreach($aryList as $iList) 
			{
				
				if($iList['usertype']=='1') { $iUserType =  "STAFF"; } 
				if($iList['usertype']=='2') { $iUserType =  "PARENT"; }
				
				if($iList['status']=='1') { $iUserStatus = "Active"; } 
				if($iList['status']=='0') { $iUserStatus = "InActive"; }  
				if($iList['status']=='2') { $iUserStatus = "Deactivated"; }

  $rw[]=array($iList['username'], stripslashes($iList['name']), stripslashes($iList['contact_no']), stripslashes($iUserType),  stripslashes($iList['email']), stripslashes($iList['password']), stripslashes($iUserStatus));

			}
}
elseif($_GET['action']=='staff_account') {

	$content=array();
	$title = array("Parent Id", "Full Name",  "Email", "Password", "Status");


	$rw=array();

	$aryList=$db->getRows("select DISTINCT parent_id  from student_guardian where create_by_userid='".$create_by_userid."' order by id asc");
     foreach($aryList as $iList) 
		{
$manageParent=$db->getRow("select * from student_guardian where parent_id = '".$iList['parent_id']."' and  create_by_userid='".$create_by_userid."' order by id asc");
  
				
				
				if($manageParent['status']=='1') { $iUserStatus = "Active"; } 
				if($manageParent['status']=='0') { $iUserStatus = "InActive"; }  
				if($manageParent['status']=='2') { $iUserStatus = "Deactivated"; }

$rw[]=array($manageParent['parent_id'], stripslashes($manageParent['first_name'].' '.$manageParent['last_name']), stripslashes($manageParent['email']), stripslashes($manageParent['password']), stripslashes($iUserStatus));

			}
}
elseif($_GET['action']=='view_staff') {

	$content=array();
	$title = array("User Name", "Role", "Full Name",  "Email", "Password", "Status");


	$rw=array();

$aryList = $db->getRows("select * from school_register where usertype='1' and create_by_userid='".$create_by_userid."'");
	foreach($aryList as $iList) 
			{ 
	$iFindRole=$db->getRow("select id,role_id from  assign_role where create_by_userid='".$create_by_userid."' and staff_id='".$iList['id']."'");
	$iRoleName=$db->getRow("select role from roles where create_by_userid='".$create_by_userid."' and id='".$iFindRole['id']."'");
  
				
				
				if($iList['status']=='1') { $iUserStatus = "Active"; } 
				if($iList['status']=='0') { $iUserStatus = "InActive"; }  
				if($iList['status']=='2') { $iUserStatus = "Deactivated"; }

$rw[]=array($iList['username'], stripslashes($iRoleName['role']), stripslashes($iList['name']), stripslashes($iList['email']), stripslashes($iList['password']), stripslashes($iUserStatus));

			}
}



$content[]=$rw;
$output = fopen('php://output', 'w');
fputcsv($output, $title);
foreach($content[0] as $key => $val)
	{
		fputcsv($output, $val);
	}
?>












