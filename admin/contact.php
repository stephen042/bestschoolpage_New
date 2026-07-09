<?php include('../config.php'); 
include('inc.session-create.php'); 
$PageTitle="Contact";
$FileName = 'contact.php';
$validate=new Validation();
if($_SESSION['success']!="")
{
	$stat['success']=$_SESSION['success'];
	unset($_SESSION['success']);
}
if(($_REQUEST['action']=='delete'))
{
	$flgInd5 = $db->delete("contactus","where id='".$_GET['id']."'");					   
	$_SESSION['success'] = 'Deleted Successfully';
	redirect(ADMIN_URL.$FileName);	
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
<!-- Start content -->
<div class="content">
<div class="container">
	<div class="row">
		<div class="col-sm-12">
			<h4 class="page-title"><?php echo $PageTitle; ?></h4>
			<ol class="breadcrumb">
				<li>
					<a href="<?php echo $iClassName; ?>">Home</a>
				</li>									
				<li class="active">
					<?php echo $PageTitle; ?>
				</li>
			</ol>
		</div>
	</div>
		<!-- Basic Form Wizard -->
	<div class="row">
	<div class="col-md-12">                          
		<div class="card-box aplhanewclass">
			<div class="row">
				<div class="col-md-9">
					<?php echo msg($stat); ?>
				</div>
				<div class="col-md-3">
			  <!---<a href="<?php //echo $FileName; ?>?action=add"  class="btn btn-default" style="float:right">Add New Record</a> --->
				</div>
			  </div>
		</div>
		<?php if($_GET['action']=='view') { 
		$GetEmailId=$db->getRow("select * from contactus where id='".$_GET['id']."'");
		?>
		<div class="card-box">
            <section>
                 
				<div class="form-group clearfix">
				<label class="col-lg-2 control-label " for="userName">Name:</label>
				<?php echo $GetEmailId['name']; ?>
				</div>
				
				<div class="form-group clearfix">
				<label class="col-lg-2 control-label " for="userName">Email:</label>
				<?php echo $GetEmailId['email']; ?> 
				</div>
				
				<div class="form-group clearfix">
				<label class="col-lg-2 control-label " for="userName">Subject:</label>
				<?php echo $GetEmailId['subject']; ?> 
				</div>
				
				<div class="form-group clearfix">
				<label class="col-lg-2 control-label " for="userName">Message:</label>
				<?php echo $GetEmailId['message']; ?> </div>
				
				<div class="form-group clearfix">
				<label class="col-lg-2 control-label " for="userName">Create At:</label>
				<?php echo $GetEmailId['create_at']; ?> </div>
				
				<a  href="<?php echo $FileName; ?>"  class="btn btn-default" >Back</a>
			
			</section>
            </div>
          </div>
		
        <?php } else { ?>
		<div class="card-box">
		<table id="datatable" class="table table-striped table-bordered">
			<thead>
				<tr>
					<th>#</th>
					<th>Name</th>
					<th>Email</th>
					<th>Subject</th>
					<th>Message</th>
					<th>Create At</th>
					<th>Action</th>
				</tr>
			</thead>
			<tbody>
            <?php $i=0;
			$aryList=$db->getRows("select * from contactus order by id desc");
				foreach($aryList as $iList)
				{	$i=$i+1;
					$aryPgAct["id"]=$iList['id'];
					?>
                <tr>
					<td><?php echo $i; ?> </td>
					<td><?php echo $iList['name']; ?></td>
					<td><?php echo $iList['email']; ?></td>
					<td><?php echo $iList['subject']; ?></td>
					<td><?php echo $iList['message']; ?></td>
					<td><?php echo $iList['create_at']; ?></td>
					<td>
						<a href="<?php echo $FileName; ?>?action=view&id=<?php echo $iList['id']; ?>" class="table-action-btn"> <i class="fa fa-search"></i></a>
						<a href="javascript:del('<?php echo $iClassName.$FileName; ?>?action=delete&id=<?php echo $iList['id']; ?>')"    class="table-action-btn" > <i class="fa fa-times"></i> </a>
					</td>
                </tr>
                <?php } ?>
            </tbody>
		</table>
		</div>
        <?php } ?>
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