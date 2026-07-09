<?php include('../config.php'); 
include('inc.session-create.php'); 
$PageTitle = "Slider";
$FileName = 'slider.php';
$validate=new Validation();
if($_SESSION['success']!="")
{
	$stat['success']=$_SESSION['success'];
	unset($_SESSION['success']);
}
if(isset($_POST['submit']))
{ 
	if($validate->validate() && count($stat)==0)
	{
		if(isset($_FILES["image"]["name"]) && !empty($_FILES["image"]["name"]))
		{	 
			$filename = basename($_FILES['image']['name']);
			$ext1 = strtolower(substr($filename, strrpos($filename, '.')+1));
			if(in_array($ext1,array('jpg','png', 'gif','jpeg')))
			{ 	  
				$newfile=md5(time())."_".$filename;
				move_uploaded_file($_FILES['image']['tmp_name'],"../uploads/".$newfile);
			}				
		}
		
		$iLastId=$db->getVal("select id from plogin_slider order by id desc")+1;		
		$randomId=randomFix(15).'-'.$iLastId;
			
		$aryData=array(	
						'usertype'			=>	$_SESSION['usertype'],
						'userid'			=>	$_SESSION['userid'],
						
						'image'				=>	$newfile,
						'status'			=>	$_POST['status'],
						'create_by_userid'	=>	$create_by_userid,
						'create_by_usertype'=>	$create_by_usertype,
						'randomid'			=>	$randomId,						
						);  
			$flgIn1 = $db->insertAry("plogin_slider",$aryData);
			echo $flgIn1 = $db->getLastQuery();
            exit;			
			$_SESSION['success']="Submitted Successfully";
			redirect($FileName);
			unset($_POST);
	}
	else 
	{
		$stat['error'] = $validate->errors();
	}
} 
elseif(isset($_POST['update']))
{ 
	if($validate->validate() && count($stat)==0)
	{ 
 		if(isset($_FILES["image"]["name"]) && !empty($_FILES["image"]["name"]))
		{	 
			$filename = basename($_FILES['image']['name']);
			$ext1 = strtolower(substr($filename, strrpos($filename, '.')+1));
			if(in_array($ext1,array('jpg','png', 'gif','jpeg')))
			{ 	  
				$newfile=md5(time())."_".$filename;
				move_uploaded_file($_FILES['image']['tmp_name'],"../uploads/".$newfile);
			}				
		}         
		else { $newfile =$_POST['image_old']; }
			
		$aryData=array(	
						
						'image'				=>	$newfile,
						'status'			=>	$_POST['status'],	
						);  
			$flgIn = $db->updateAry("plogin_slider", $aryData , "where randomid='".$_GET['randomid']."' ");
					
			$_SESSION['success']="Update Successfully";
			unset($_POST);
			redirect($FileName);
 	}	  
	else 
	{
		$stat['error'] = $validate->errors();
	}
}
elseif(($_REQUEST['action']=='delete'))
{
	$flgIn1 = $db->delete("plogin_slider","where randomid='".$_GET['randomid']."' ");			
	$_SESSION['success'] = 'Deleted Successfully';
	redirect($FileName);
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
		<!-- Page-Title -->
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
						<a href="<?php echo $FileName; ?>?action=add"  class="btn btn-default" style="float:right">Add New Record</a>
					</div>
				</div>
            </div>
            <?php if($_GET['action']=='add') { ?>
            <div class="card-box">
			<form role="form" action="" method="post" enctype="multipart/form-data">
            <section>
                     
				
				<div class="form-group clearfix">
					<label class="col-lg-2 control-label " for="userName">Image </label>
					<div class="col-lg-10">
						<input type="file" class="form-control" name="image" value="<?php echo $_POST['image']; ?>" required>
					</div>
				</div>

				<div class="form-group clearfix">
					<label class="col-lg-2 control-label " for="confirm">Status </label>
					<div class="col-lg-10">
						<select class=" form-control" name="status">
							<option value="1" <?php if($_POST['status']=='1') { echo "selected"; } ?>>Active</option>
							<option value="0" <?php if($_POST['status']=='0') { echo "selected"; } ?>>Inactive</option>
						</select>
					</div>
				</div>
				
				<button type="submit" name="submit" class="btn btn-default">Submit</button>
				<a  href="<?php echo $FileName; ?>"  class="btn btn-default" >Back</a>
			</section>
            </form>
            </div>
            <?php } elseif($_GET['action']=='edit') { 
			$aryDetail=$db->getRow("select * from  plogin_slider where randomid='".$_GET['randomid']."'");	
			?>
            <div class="card-box">
			<form role="form" action="" method="post" enctype="multipart/form-data">
			<section>
                  
				
				<div class="form-group clearfix">
					<label class="col-lg-2 control-label" for="userName">Image </label>
					<div class="col-lg-10">
						<input type="file" class="form-control" name="image">
						<input type="hidden" class="form-control" name="image_old" value="<?php echo $aryDetail['image'] ?>" >
						<img src="../uploads/<?php echo $aryDetail['image'] ?>" style="height:50px;">
					</div>
				</div>

				<div class="form-group clearfix">
					<label class="col-lg-2 control-label " for="confirm">Status </label>
					<div class="col-lg-10">
						<select class=" form-control" name="status">
							<option value="1" <?php if($aryDetail['status']=='1') { echo "selected"; } ?>>Active</option>
							<option value="0" <?php if($aryDetail['status']=='0') { echo "selected"; } ?>>Inactive</option>
						</select>
					</div>
				</div>
					
				<button type="submit" name="update" class="btn btn-default">Submit</button>
				<a href="<?php echo $FileName; ?>" class="btn btn-default" >Back</a> 
			</section>
			</form>
            </div>
            <?php } elseif($_GET['action']=='view') { 
			$GetEmailId=$db->getRow("select * from  plogin_slider where randomid='".$_GET['randomid']."'");
			?>
            <div class="card-box">
            <section>
				
			
				<div class="form-group clearfix">
					<label class="col-lg-2 control-label " for="userName">Image :</label>
					<img src="../uploads/<?php echo $GetEmailId['image']; ?>" style="height:50px;"> 
				</div>

				<div class="form-group clearfix">
					<label class="col-lg-2 control-label " for="userName">Status :</label>
					<?php if($GetEmailId['status']=='1'){echo "Active";}
					elseif($GetEmailId['status']=='0'){echo "Inactive";} ?>
				</div>
				<a href="<?php echo $FileName; ?>" class="btn btn-default">Back</a> 
			</section>
			</div>
			<?php } else { ?>
			<div class="card-box">
            <table id="datatable" class="table table-striped table-bordered">
              <thead>
                <tr>
                  <th>#</th>			
                  
                  <th>Image</th>
                  <th>Status</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                <?php
		$aryList=$db->getRows("select * from plogin_slider	order by id desc");
		foreach($aryList as $iList)
			{	
			$i=$i+1;
		$aryPgAct["id"]=$iList['id'];
			 ?>
			<tr>
                <td><?php echo $i; ?></td>
               
                <td><img src="../uploads/<?php echo $iList['image']; ?>" style="height:50px;"> </td>
				<td><?php if($iList['status']=='1'){echo "Active";}
					elseif($iList['status']=='0'){echo "Inactive";} ?>
                </td>
				<td>
					<!--
					<a href="<?php echo $FileName; ?>?action=view&id=<?php echo $iList['id']; ?>" class="table-action-btn"> <i class="fa fa-search"></i></a> 
					-->
					<a href="<?php echo $FileName; ?>?action=edit&randomid=<?php echo $iList['randomid']; ?>"  class="table-action-btn" > <i class="fa fa-pencil"></i> </a> 
					<a href="javascript:del('<?php echo $FileName; ?>?action=delete&randomid=<?php echo $iList['randomid']; ?>')" class="table-action-btn" > <i class="fa fa-times"></i> </a>
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
