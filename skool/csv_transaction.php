<?php
include('../config.php'); 
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


if($_GET['action']=='transaction_csv') {

	$content=array();
	$title = array("Fullname", "Session", "Class", "Term", "Invoice No", "Outstanding Balance","Amount Paid","Discount","Create at","status");


	$rw=array();
    $aryList = $db->getRows("select * from student_fee_transcation where create_by_userid='" . $create_by_userid . "' order by id desc");
     foreach($aryList as $iList) 
			{
                $iStudentFeeDetails = $db->getRow("select * from student_fee where create_by_userid='" . $create_by_userid . "' and id= '" . $iList['id'] . "'");

                $session = $db->getVal("select session from  school_session where create_by_userid='" . $create_by_userid . "' and id = '" . $iList['session'] . "'");
                $class = $db->getVal("select name from  school_class where create_by_userid='" . $create_by_userid . "' and id = '" . $iList['class'] . "'");
                $term =  $db->getVal("select term from  school_term where create_by_userid='" . $create_by_userid . "' and id = '" . $iList['term_id'] . "'");
                $date =  $db->getVal("select create_at from  student_fee_transcation where create_by_userid='" . $create_by_userid . "' ");

                if($iList['currently_paying_amount'] > 1 && $iList['remain_amount'] == 0 && $iStudentFeeDetails['student_status'] != 3) {$status = "Paid";}
                if($iList['currently_paying_amount'] > 1 && $iList['remain_amount'] != 0 ) {$status = "Pending";}
                if($iStudentFeeDetails['student_status'] == 3) {$status = "Scholarship";}


                $rw[]=array($iList['fullname'],$session,$class,$term, $iList['invoiceno'], $iList['remain_amount'],$iList['currently_paying_amount'],$iList['discount_amount'],$date,$status);
			}
}elseif($_GET['action']=='day_csv') {

	$content=array();
	$title = array("Fullname", "Session", "Class", "Term", "Invoice No", "Outstanding Balance","Amount Paid","Discount","Create at","status");


	$rw=array();
	
    $aryList = $db->getRows("select * from student_fee_transcation where create_by_userid='" . $create_by_userid . "' and create_at='".date('Y-m-d')."'  order by id desc");
	foreach($aryList as $iList) 
	{
		$iStudentFeeDetails = $db->getRow("select * from student_fee where create_by_userid='" . $create_by_userid . "' and id= '" . $iList['id'] . "'");

		$session = $db->getVal("select session from  school_session where create_by_userid='" . $create_by_userid . "' and id = '" . $iList['session'] . "'");
		$class = $db->getVal("select name from  school_class where create_by_userid='" . $create_by_userid . "' and id = '" . $iList['class'] . "'");
		$term =  $db->getVal("select term from  school_term where create_by_userid='" . $create_by_userid . "' and id = '" . $iList['term_id'] . "'");
		$date =  $db->getVal("select create_at from  student_fee_transcation where create_by_userid='" . $create_by_userid . "' ");

		if($iList['currently_paying_amount'] > 1 && $iList['remain_amount'] == 0 && $iStudentFeeDetails['student_status'] != 3) {$status = "Paid";}
		if($iList['currently_paying_amount'] > 1 && $iList['remain_amount'] != 0 ) {$status = "Pending";}
		if($iStudentFeeDetails['student_status'] == 3) {$status = "Scholarship";}


		$rw[]=array($iList['fullname'],$session,$class,$term, $iList['invoiceno'], $iList['remain_amount'],$iList['currently_paying_amount'],$iList['discount_amount'],$date,$status);
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