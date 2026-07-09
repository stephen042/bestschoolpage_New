<?php include('../config.php');
include('inc.session-create.php');
$PageTitle = "SMS PLAN";
$FileName = 'sms_plan.php';
$validate = new Validation();
if($_SESSION['success']!="")
{
	$stat['success'] = $_SESSION['success'];
	unset($_SESSION['success']);
}
if(isset($_POST['buy_sms_plan']))
{
	$iSmsPlan=$db->getRow("select * from sms_plan where id='".$_POST['plan_id']."'");
	
    /*$iAlreadySms=$db->getVal("select id from sms_payment where create_by_userid='".$create_by_userid."' and plan_id='".$_POST['plan_id']."' and userid='".$_SESSION['userid']."' and status=1 and status=0");*/
	$iAlreadySms=$db->getVal("select id from sms_payment where create_by_userid='".$create_by_userid."' and no_of_sms >0" );
	
	$success_token = randomFix(15);
	
	$cancel_token = randomFix(15);
	
	
		$aryData = array(
						'plan_id'						=>	$iSmsPlan['id'],
						'plan_name'						=>	$iSmsPlan['title'],
						'price'							=>	$iSmsPlan['price'],
						'no_of_sms'						=>	$iSmsPlan['no_of_sms'],
						'exp_date'						=>	$iSmsPlan['exp_date'],
						'status'						=>	0,
						'create_at'						=>	date('Y-m-d H:i:s'),
						'usertype'						=>	$_SESSION['usertype'],
						'userid'						=>	$_SESSION['userid'],
						'create_by_userid'				=>	$create_by_userid,
						'create_by_usertype'			=>	$create_by_usertype,
						'success_token'					=>	$success_token,
						'cancel_token'					=>	$cancel_token,
						);
			$flgIn = $db->insertAry("sms_payment", $aryData);
			$_SESSION['payment_id'] = $flgIn;
			
			redirect(SKOOL_URL.'sms_voguepay.php');
	
	/*elseif($iAlreadySms=="")
	
	{
		$aryData = array(
						'plan_id'						=>	$iSmsPlan['id'],
						'plan_name'						=>	$iSmsPlan['title'],
						'price'							=>	$iSmsPlan['price'],
						'no_of_sms'						=>	$iSmsPlan['no_of_sms'],
						'exp_date'						=>	$iSmsPlan['exp_date'],
						'status'						=>	0,
						'create_at'						=>	date('Y-m-d H:i:s'),
						'usertype'						=>	$_SESSION['usertype'],
						'userid'						=>	$_SESSION['userid'],
						'create_by_userid'				=>	$create_by_userid,
						'create_by_usertype'			=>	$create_by_usertype,
						'success_token'					=>	$success_token,
						'cancel_token'					=>	$cancel_token,
						);
			$flgIn = $db->updateAry("sms_payment", $aryData,"where create_by_userid='".$create_by_userid."' and plan_id='".$_POST['plan_id']."' and userid='".$_SESSION['userid']."' and status=0");
			echo $flgIn = $db->getLastQuery();
			exit;
			
			$_SESSION['payment_id'] = $flgIn;
			
			redirect(SKOOL_URL.'sms_voguepay.php');
	}
	else {
	
	echo "<script>alert('You Have Already Purchased this Plan');</script>";
	}
	*/
	
}
?>
<!DOCTYPE html>
<html>
<head>
<style>
.d-ttt {
	width: 88%;
	margin: 0 auto;
}
</style>
	<?php include('inc.meta.php'); ?>
</head>
<body class="fixed-left">
<div id="wrapper">
	<?php include('inc.header.php'); ?>
	<?php include('inc.sideleft.php'); ?>
<div class="content-page">
	<div class="content">
		<div class="container">
			<div class="row">
				<div class="col-sm-12">
					<h4 class="page-title-3">Pricing Table</h4>
					<h3><p class="text-muted page-title-3-alt">Applicant, Configuration etc</p></h3>
				</div>
			</div>
			<h3><?php echo msg($stat);?></h3>
		</div>
		<?php if($_GET['action']=='axjsdhg12sd') { ?>
		<div class="col-md-11">
			<a  href="<?php echo $FileName; ?>" class="btn btn-default" style="float:right; color:#fff;">Back</a> 
		</div>
		<?php } else { ?>
		<div class="col-md-11">
			<a href="<?php echo $FileName; ?>?action=axjsdhg12sd" class="btn btn-default" style="float:right; color:#fff;">My Plan</a> 
		</div>
		<?php } ?>
	</div>
	<?php if($_GET['action']=='axjsdhg12sd') { ?>
	<div class="card-box d-ttt">
		<table id="datatable" class="table table-striped table-bordered">
			<thead>
                <tr>			
					<th>#</th>			
					<th>Plan Name</th>
					<th>No. Of Sms Remains</th>
					<th>Price</th>
					<th>Status</th>
				</tr>
			</thead>
			<tbody>
			<?php $i=0;
			$aryList=$db->getRows("select * from sms_payment  where create_by_userid='".$create_by_userid."' and status='1' ");
			foreach($aryList as $iList)
			{ $i++;
				?>
                <tr>
					<td><?php echo $i ?></td>
					<td><?php echo $iList['plan_name']; ?></td>
					<td><?php echo $iList['no_of_sms']; ?></td>
					<td><?php echo $iList['price']; ?></td>
					<td><?php if($iList['status']=='0') { echo "Pending"; } if($iList['status']=='1') { echo "Success"; } if($iList['status']=='2') { echo "Cancel"; } ?></td>
				</tr>
			<?php } ?>
            </tbody>
		</table>
	</div>
	<?php } else { ?>
<style>
@media only screen and (max-width: 768px) {
		.movmove {
			    padding-left: 0px!important;
		}
}
</style>
    
	<div class="container">
		<div class="row hhgg movmove">
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        	<div class="row ">
		<?php
		$iGetPlasAll=$db->getRows("select * from sms_plan where status='1'");
		foreach($iGetPlasAll as $iList)
		{
		?>
			<div class="col-xs-12 col-sm-6 col-md-3 col-lg-3">
			<form action="" method="POST">
				<div class="panel price panel-red">
					<div class="panel-heading  text-center">
						<input type="hidden" name="plan_id" style="color:black;" value="<?php echo $iList['id']; ?>" readonly>
						<h3><?php echo $iList['title']; ?></h3>
					</div>
					<div class="panel-body text-center">
						<p class="lead" style="font-size:40px"><strong><?php echo $iList['price']; ?></strong></p>
					</div>
					<ul class="list-group list-group-flush text-center">
						<li class="list-group-item"><i class="icon-ok text-danger"></i>No Of Sms <?php echo $iList['no_of_sms']; ?></li>
					</ul>
					<div class="panel-footer">
						<button type="submit" name="buy_sms_plan" class="btn btn-lg btn-block btn-danger">BUY NOW!</button>
					</div>
				</div>
			</form>	
			</div>
		<?php } ?>	
		</div>
    </div>
	<?php } ?>
	<?php include('inc.footer.php'); ?>
</div>
</div>
<?php include('inc.js.php'); ?>
</body>
</html>