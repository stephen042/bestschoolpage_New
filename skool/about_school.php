<?php include('../config.php'); 
include('inc.session-create.php'); 
$PageTitle = "About School";
$FileName = 'about_school.php';
$validate=new Validation();
if($_SESSION['success']!="")
{
	$stat['success']=$_SESSION['success'];
	unset($_SESSION['success']);
}
if(isset($_POST['submit']))
{ 
	//$validate->addRule($_POST['title'],'','Title',true);
	
	if($validate->validate() && count($stat)==0)
	{
		if(isset($_FILES["about_image"]["name"]) && !empty($_FILES["about_image"]["name"]))
		{	 
			$filename = basename($_FILES['about_image']['name']);
			$ext1 = strtolower(substr($filename, strrpos($filename, '.')+1));
			if(in_array($ext1,array('jpg','png', 'gif','jpeg')))
			{ 	  
				$newfile=md5(time())."_".$filename;
				move_uploaded_file($_FILES['about_image']['tmp_name'],"../uploads/".$newfile);
			}				
		}
		else { $newfile =$_POST['about_image_old']; }
		
		if(isset($_FILES["image"]["name"]) && !empty($_FILES["image"]["name"]))
		{	 
			$filename = basename($_FILES['image']['name']);
			$ext1 = strtolower(substr($filename, strrpos($filename, '.')+1));
			if(in_array($ext1,array('jpg','png', 'gif','jpeg')))
			{ 	  
				$newfile1=md5(time())."_".$filename;
				move_uploaded_file($_FILES['image']['tmp_name'],"../uploads/".$newfile1);
			}				
		}
		else { $newfile1 =$_POST['image_old']; }
		
		
		
		$iLastId=$db->getVal("select id from school_about order by id desc")+1;		
		$randomId=randomFix(15).'-'.$iLastId;
			
		$aryData=array(	
						'usertype'			=>	$_SESSION['usertype'],
						'userid'			=>	$_SESSION['userid'],
						'about_image'				=>	$newfile,
						'about_description'		=>	$_POST['about_description'],
						'promo_video'				=>	$_POST['promo_video'],
						'title'				=>	$_POST['title'],
						'short_description' =>	$_POST['short_description'],
						'image'				=>	$newfile1,
						
						
						'link_apply_addmission'				=>	$_POST['link_apply_addmission'],
						'link_school_fee'				=>	$_POST['link_school_fee'],
						
						
						'create_by_userid'	=>	$create_by_userid,
						'create_by_usertype'=>	$create_by_usertype,
						'randomid'			=>	$randomId,						
						);  
			$flgIn1 = $db->insertAry("school_about",$aryData);
			
			
			  
				
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
 		if(isset($_FILES["about_image"]["name"]) && !empty($_FILES["about_image"]["name"]))
		{	 
			$filename = basename($_FILES['about_image']['name']);
			$ext1 = strtolower(substr($filename, strrpos($filename, '.')+1));
			if(in_array($ext1,array('jpg','png', 'gif','jpeg')))
			{ 	  
				$newfile=md5(time())."_".$filename;
				move_uploaded_file($_FILES['about_image']['tmp_name'],"../uploads/".$newfile);
			}				
		}
		else { $newfile =$_POST['about_image_old']; }
		
		if(isset($_FILES["image"]["name"]) && !empty($_FILES["image"]["name"]))
		{	 
			$filename = basename($_FILES['image']['name']);
			$ext1 = strtolower(substr($filename, strrpos($filename, '.')+1));
			if(in_array($ext1,array('jpg','png', 'gif','jpeg')))
			{ 	  
				$newfile1=md5(time())."_".$filename;
				move_uploaded_file($_FILES['image']['tmp_name'],"../uploads/".$newfile1);
			}				
		}
		else { $newfile1 =$_POST['image_old']; }
		
		
			
		$aryData=array(	
						'about_image'				=>	$newfile,
						'about_description'		=>	$_POST['about_description'],
						'promo_video'				=>	$_POST['promo_video'],
						'title'				=>	$_POST['title'],
						'link_apply_addmission'			=>	$_POST['link_apply_addmission'],
						'link_school_fee'				=>	$_POST['link_school_fee'],
						'short_description' =>	$_POST['short_description'],
						'image'				=>	$newfile1,
						
						);  
			$flgIn = $db->updateAry("school_about", $aryData , "where create_by_userid='".$create_by_userid."' ");
					
			$_SESSION['success']="Update Successfully";
			unset($_POST);
			redirect($FileName);
 	}	  
	else 
	{
		$stat['error'] = $validate->errors();
	}
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
		
        <div class="row">
			<div class="col-md-12">
          
			<?php echo msg($stat); ?>
			<?php
			$aryDetail=$db->getRow("select * from school_about where create_by_userid='".$create_by_userid."'");
			?>
            <div class="card-box">
			<form role="form" action="" method="post" enctype="multipart/form-data">
			<section>
            <h3>About School</h3>      
				<div class="form-group clearfix">
					<label class="col-lg-2 control-label" for="userName">Image </label>
					<div class="col-lg-10">
						<input type="file" class="form-control" name="about_image">
						<input type="hidden" class="form-control" name="about_image_old" value="<?php echo $aryDetail['about_image'] ?>" >
						<img src="../uploads/<?php echo $aryDetail['about_image'] ?>" style="height:50px;">
					</div>
				</div>
				
				<div class="form-group clearfix">
					<label class="col-lg-2 control-label " for="userName">Description </label>
					<div class="col-lg-10">
						<textarea class="form-control" name="about_description"><?php echo $aryDetail['about_description']; ?></textarea>
					</div>
				</div>
			<hr>	
				<div class="form-group clearfix">
					<label class="col-lg-2 control-label " for="userName">Promo Video </label>
					<div class="col-lg-10">
						<input type="text" class="form-control" name="promo_video" value="<?php echo $aryDetail['promo_video']; ?>">
					</div>
				</div>
			<hr>
			<h3>Achievements</h3>  
				<div class="form-group clearfix">
					<label class="col-lg-2 control-label " for="userName">Title</label>
					<div class="col-lg-10">
						<input type="text" class="form-control" name="title" value="<?php echo $aryDetail['title']; ?>">
					</div>
				</div>
				
				<div class="form-group clearfix">
					<label class="col-lg-2 control-label " for="userName">Short Description </label>
					<div class="col-lg-10">
						<textarea class="form-control" name="short_description"><?php echo $aryDetail['short_description']; ?></textarea>
					</div>
				</div>
				
				<div class="form-group clearfix">
					<label class="col-lg-2 control-label" for="userName">Image </label>
					<div class="col-lg-10">
						<input type="file" class="form-control" name="image">
						<input type="hidden" class="form-control" name="image_old" value="<?php echo $aryDetail['image'] ?>" >
						<img src="../uploads/<?php echo $aryDetail['image'] ?>" style="height:50px;">
					</div>
				</div>
			<hr>
			<h3>External Links</h3>  	
				 
				
				<div class="form-group clearfix">
					<label class="col-lg-2 control-label " for="userName">Link For Apply Addmission </label>
					<div class="col-lg-10">
						<input type="text" class="form-control" name="link_apply_addmission" value="<?php echo $aryDetail['link_apply_addmission']; ?>">
					</div>
				</div>
				
				<div class="form-group clearfix">
					<label class="col-lg-2 control-label " for="userName">Link For Pay School Fees </label>
					<div class="col-lg-10">
						<input type="text" class="form-control" name="link_school_fee" value="<?php echo $aryDetail['link_school_fee']; ?>">
					</div>
				</div>
				<?php if($aryDetail['id']=='') { ?>
				<button type="submit" name="submit" class="btn btn-default" style="color:#fff;">Submit</button>
				<?php } else { ?>
				<button type="submit" name="update" class="btn btn-default"  style="color:#fff;">Update</button>
				<?php } ?>
				<a href="<?php echo $FileName; ?>" class="btn btn-default" style="color:#fff;" >Back</a> 
			</section>
			</form>
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
