<?php include('config.php'); 
$iSmsPayment=$db->getRow("select * from app_personalinfo where tokenid = '".$_GET['tokenid']."'");

$Merchant_id = 'DEMO';
//$Merchant_Key = '234-567-890';
//$Developer_Code = 'pq7778ehh9YbZ';
//$Store_id = '25';

$Memo='Admission Fee';
$Item='Admission Fee Paid';
$Description='This payment is for Admission Fee ';
$Amount = $iSmsPayment['admission_fee'];

$Email = 	$iSmsPayment['email'];
$Name 	= 	$iSmsPayment['firstname'].' '.$iSmsPayment['lastname'];
$Phone 	= 	$iSmsPayment['contact_no'];
$Address='';
$City='';
$State='';
$Zipcode='';
?>
<script src="//voguepay.com/js/voguepay.js"></script>
<form method='POST' id='payform' action='//voguepay.com/pay/' onsubmit='return false;' >
    <input type='hidden' name='v_merchant_id' value='<?php echo $Merchant_id; ?>' />
	
    <input type='hidden' name='merchant_ref' value='234-567-890' />
	
    <input type='hidden' name='memo' value='<?php echo $Memo; ?>' />

	<input type='hidden' name='item_1' value='<?php echo $Item; ?>' />
    <input type='hidden' name='description_1' value='<?php echo $Description; ?>' />
    <input type='hidden' name='price_1' value='<?php echo $Amount; ?>' />
	
    <input type='hidden' name='success_url' value='<?php echo SKOOL_URL; ?>sms_success.php?success_token=<?php echo $iSmsPayment['success_token']; ?>' />
    <input type='hidden' name='fail_url' value='<?php echo SKOOL_URL; ?>sms_failed.php?cancel_token=<?php echo $iSmsPayment['cancel_token']; ?>' />

    <input type='hidden' name='developer_code' value='pq7778ehh9YbZ' />
    <input type='hidden' name='store_id' value='25' />

    <!----##Use notify url if you want a transaction response to be sent to the notify##---->
    <input type='hidden' name='notify_url' value='//www.mydomain.com/notification.php' />

    <!---##Success and fail URL are not required if integration method is inline.##
    ##These are to be uses if you are using the form redirect method.##
    <input type='hidden' name='success_url' value='//www.mydomain.com/thank_you.html' />
    <input type='hidden' name='fail_url' value='//www.mydomain.com/failed.html' />--->

    <input type='hidden' name='cur' value='NGN'/>
	
    <input type='hidden' name='total' value='<?php echo $Amount; ?>' />


    <input type='hidden' name='name' value='<?php echo $Name; ?>'/>
    <input type='hidden' name='address' value='<?php echo $Address; ?>'/>
    <input type='hidden' name='city' value='<?php echo $City; ?>'/>
    <input type='hidden' name='state' value='<?php echo $State; ?>'/>
    <input type='hidden' name='zipcode' value='<?php echo $Zipcode; ?>'/>
    <input type='hidden' name='email' value='<?php echo $Email; ?>'/>
    <input type='hidden' name='phone' value= '<?php echo $Phone; ?>'/>

   <!---- ##notification triggers for inline payments only##--->
    <input type='hidden' name='closed' value='closedFunction'>
    <input type='hidden' name='success' value='successFunction'>
    <input type='hidden' name='failed' value='failedFunction'>

    <input id="link" type='image' style="width:0px; height:0px;" src='https://voguepay.com/images/buttons/make_payment_blue.png' alt='Submit' />
</form> 

<script>
    closedFunction=function() {
		window.location.href="<?php echo SITE_URL; ?>admission_success.php?action=cancel";
		alert('window closed');
    }

	successFunction=function(transaction_id) {
		alert(transaction_id);
		//alert('Transaction was successful, Ref: '+transaction_id);
		window.location.href="<?php echo SITE_URL; ?>admission_success.php?transaction_id="+transaction_id+"tokenid=<?php echo $_GET['tokenid']; ?>";
    }

	failedFunction=function(transaction_id) {
		alert(transaction_id);
		//alert('Transaction was not successful, Ref: '+transaction_id);
		window.location.href="<?php echo SITE_URL; ?>admission_success.php?action=cancel";
    }
</script>
<script>
    Voguepay.init({form:'payform'});
	
	 setTimeout(function(){
		 var element = document.getElementById('link');
		 element.click();
	//document.getElementById("payform").submit();
	}, 1000);

</script>		 