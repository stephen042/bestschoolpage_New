<?php include('../config.php'); 

$iSmsPayment=$db->getRow("select * from sms_payment where id = '".$_SESSION['payment_id']."'");


$aryData = array(
				'status'						=>	2,
				);
	$flgIn = $db->updateAry("sms_payment", $aryData,"where id='".$iSmsPayment['id']."'");
	redirect(SKOOL_URL.'sms_plan.php?action=axjsdhg12sd');
?>