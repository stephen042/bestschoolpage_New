<?php include('../config.php'); 
include('inc.session-create.php'); 
$PageTitle="Subscribe";
$FileName = 'subscribe.php';
$iClassName = ADMIN_URL;
$validate=new Validation();
if($_SESSION['success']!="")
{
	$stat['success']=$_SESSION['success'];
	unset($_SESSION['success']);
}
 
elseif(($_GET['action']=='delete'))
{
	 
			
	$flgIn1 = $db->delete("subscribe","where id='".$_GET['id']."'");			
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
<div class="content">
<div class="container">
	<div class="row">
		<div class="col-sm-12">
			<h4 class="page-title"><?php echo $PageTitle; ?></h4>
			<ol class="breadcrumb">
				<li> <a href="<?php echo $iClassName; ?>">Home</a> </li>
				<li class="active"> <?php echo $PageTitle; ?> </li>
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
						<!--<a href="<?php echo $iClassName.$FileName; ?>?action=add"  class="btn btn-default" style="float:right">Add New Record</a>---> 
					</div>
				</div>
            </div>
			<!--------------------------view Section------------------------>
	 
            <div class="card-box">
			<table id="datatable" class="table table-striped table-bordered">
			<thead>
				<tr>
                    <th>#</th> 
					<th>Email Id</th> 
					<th>Create At</th> 
					<th>Action</th>
				</tr>
			</thead>
			<tbody>
			<?php $i=0;
			$aryList=$db->getRows("select * from subscribe  order by id desc");
				foreach($aryList as $iList)
				{ $i=$i+1;
				?>
				<tr>
					<td><?php echo $i ?></td>
					 
					<td><?php echo $iList['emailid']; ?></td>
				 
					<td><?php echo $iList['create_at']; ?></td>
					 
					<td> 
  						<a href="javascript:del('<?php echo $iClassName.$FileName; ?>?action=delete&id=<?php echo $iList['id']; ?>')"    class="table-action-btn" > <i class="fa fa-times"></i> </a>
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