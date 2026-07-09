<?php include('../config.php'); 

$iSmsPayment=$db->getRow("select * from sms_payment where id = '".$_SESSION['payment_id']."'");

if($iSmsPayment['success_token']==$_GET['success_token'])
{
	$aryData = array(
					'status'						=>	1,
					);
		$flgIn = $db->updateAry("sms_payment", $aryData,"where success_token='".$_GET['success_token']."'");
}
?>