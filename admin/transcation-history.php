<?php include('../config.php'); 
include('inc.session-create.php'); 
$PageTitle="Transction History";
$FileName = 'transcation-history.php';
$iClassName = ADMIN_URL;
$validate=new Validation();
if($_SESSION['success']!="")
{
   $stat['success']=$_SESSION['success'];
   unset($_SESSION['success']);
}
?>
<!DOCTYPE html>
<html>
<head>
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
			<h4 class="page-title"><?php echo $PageTitle; ?></h4>
			<ol class="breadcrumb">
				<li><a href="<?php echo $iClassName; ?>">Home</a></li>
				<li class="active"><?php echo $PageTitle; ?></li>
			</ol>
		</div>
	</div>
	<!-- Basic Form Wizard -->
	<div class="row">
	<div class="col-md-12">
		<div class="card-box aplhanewclass">
			<div class="row">
				<div class="col-md-9"> <?php echo msg($stat); ?> </div>
				<div class="col-md-3"> 
				<!--
					<a href="<?php echo $iClassName.'withdrawal-request.php'; ?>?action=add"  class="btn btn-default" style="float:right">Send Withdraw Request</a> 
				--->
				</div>
			</div>
		</div>
		<!--------------------------Insert Section------------------------>
		<div class="card-box">
		<table id="datatable" class="table table-striped table-bordered">
			<thead>
				<tr>
					<th>id</th>
					<th>Transaction#</th>
					<th>For</th>
					<th>Event</th>
					<th>Mode</th>
					<th>Amount</th>
					<th>Invoice Id</th>
					<th>Create At</th>
					<th>Status</th>
                </tr>
			</thead>
			<tbody>
			<?php
			$aryList=$db->getRows("select * from transcation_histroy where usertype='1' order by id desc");
            	foreach($aryList as $iList)
				{ $i=$i+1;
			$iproducts=$db->getRow("select * from products where id='".$iList['pid']."'");
				?>
				<tr>
					<td><?php echo $i ?></td>
					<td><?php echo $iList['transaction_id']; ?></td>
					<td><?php 
							if($iList['typeid']=='1') { echo "Product Sell"; } 
							if($iList['typeid']=='2') { echo "Product Refund"; } 
							if($iList['typeid']=='3') { echo "Amount Withdraw"; } 
							if($iList['typeid']=='4') { echo "Plan Purchase"; } 
							if($iList['typeid']=='5') { echo "Amount Transfer To Merchant"; } 
							if($iList['typeid']=='6') { echo "Credit In To Wallet"; } 
							?>
                    </td>
					<td><?php if($iList['event']=='1') { echo "Withdraw"; }
							if($iList['event']=='2') { echo "Deposit"; } ?>
					</td>
					<td><?php if($iList['payment_mode']=='1') { echo "Paypal"; } 
						if($iList['payment_mode']=='2') { echo "Wallet"; } ?>
					</td>
					<td><?php showcurrency(); echo $iList['amount'];?></td>
					<td><?php if($iList['typeid']=='1' || $iList['typeid']=='2') { ?>
						<a href="<?php echo MERCHANT_URL; ?>order.php?action=view&invoiceno=<?php echo $iList['invoiceid']; ?>" class="table-action-btn"> <?php echo $iList['invoiceid'];?></a>	
						<?php } ?>
                    </td>
					<td><?php echo $iList['create_at'];?></td>
					<td>
						<?php if($iList['status']=='0') { ?><span class="label label-info" style="padding:8px;">Pending</span><?php } ?>
						<?php if($iList['status']=='1') { ?><span class="label label-success" style="padding:8px;">Success</span><?php } ?>
                    	<?php if($iList['status']=='2') { ?><span class="label label-danger" style="padding:8px;">Canceled</span><?php } ?>
                    </td>
				</tr>
				<?php } ?>
            </tbody>
		</table>
		</div>
	</div>
	</div>
</div>
</div>
<?php include('inc.footer.php'); ?>
</div>
</div>
<?php include('inc.js.php'); ?>
</body>
</html>