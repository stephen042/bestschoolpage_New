<?php include('../config.php'); 
include('inc.session-create.php'); 
$PageTitle="CMS";
$FileName = 'cms.php';
$validate=new Validation();
if($_SESSION['success']!="")
{
	$stat['success']=$_SESSION['success'];
	unset($_SESSION['success']);
}
if(isset($_POST['submit']))
{ 
	$validate->addRule($_POST['title'],'','Title',true);
	$validate->addRule($_POST['description'],'','Description',true);
	
	if($validate->validate() && count($stat)==0)
	{
		$iLastInserted=$db->getVal("select id from cms order by id desc");			
		$iLast=$iLastInserted+1;
			
		$iPageUrl=PageUrl($_POST['title']).'-'.$iLast;
				
		if(isset($_FILES["image"]["name"]) && !empty($_FILES["image"]["name"]))
		{	 
			$filename = basename($_FILES['image']['name']);
			$ext1 = strtolower(substr($filename, strrpos($filename, '.')+1));
			if(in_array($ext1,array('jpg','png', 'gif')))
			{ 	  
				$newfile=md5(time())."_".$filename;
				move_uploaded_file($_FILES['image']['tmp_name'],"../uploads/".$newfile);
			}				
		} 
				
		$aryData=array(					
						'title'     	 	         			    =>	$_POST['title'],
						'description'     	 	         			=>	$_POST['description'],
						'show_in_footer'     	 	         	    =>	$_POST['show_in_footer'],
						'image'     	 	         			    =>	$newfile,
						'pageurl'     	 	         			    =>	$iPageUrl,
						'create_at'     	 	         		    =>  date("Y-m-d H:i:s"),
						'status'     	 	         		        =>	$_POST['status'],												
						);  
				$flgIn1 = $db->insertAry("cms",$aryData);
				$_SESSION['success']="Submited Successfully";
				redirect($iClassName.$FileName);
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
		$iPageUrl=PageUrl($_POST['title']);

	    if(isset($_FILES["image"]["name"]) && !empty($_FILES["image"]["name"]))
		{	
			$filename1 = basename($_FILES['image']['name']);
			$ext1 = strtolower(substr($filename1, strrpos($filename1, '.')+1));
			if(in_array($ext1,array('jpg','png', 'jpeg', 'gif')))
			{ 	 
				$newfile=md5(time())."_".$filename1;
				move_uploaded_file($_FILES['image']['tmp_name'],"../uploads/".$newfile);
			}				
 		}
 	    else { $newfile = $_POST['image_old']; }

		$aryData=array(	
						'title'     	 	         			    =>	$_POST['title'],
						'description'     	 	         			=>	$_POST['description'],
						'show_in_footer'     	 	         	    =>	$_POST['show_in_footer'],
						'image'     	 	         			    =>	$newfile,
						'pageurl'     	 	         			    =>	$iPageUrl,
						'status'     	 	         		        =>	$_POST['status'],
						);  
				$flgIn = $db->updateAry("cms", $aryData , "where id='".$_GET['id']."'");
				$_SESSION['success']="Update Successfully";
				unset($_POST);
				redirect($iClassName.$FileName);
							 
	}	  
	else 
	{
		$stat['error'] = $validate->errors();
	}
}
elseif(($_REQUEST['action']=='delete'))
{
	$flgInd5 = $db->delete("cms","where id='".$_GET['id']."'");					   
	$_SESSION['success'] = 'Deleted Successfully';
	redirect(ADMIN_URL.$FileName);	
}	
elseif(($_REQUEST['action']=='image'))
{
	$aryData=array(	
					'image'     	 	         			    =>	'',
					);  
			$flgIn = $db->updateAry("cms", $aryData , "where id='".$_GET['id']."'");
			$_SESSION['success']="Update Successfully";
			unset($_POST);
			redirect($iClassName.$FileName);
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
<div class="container-bh">
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
					<a href="<?php echo $FileName; ?>?action=add"  class="btn btn-default" style="float:right">Add New Record</a> 
				</div>
			</div>
		</div>
		<?php if($_GET['action']=='add') { ?>
		<div class="card-box">
		<form role="form" action="" method="post" enctype="multipart/form-data">
                <div>
                  <section>
                    <div class="form-group clearfix">
                      <label class="col-lg-2 control-label " for="userName">Title </label>
                      <div class="col-lg-10">
                        <input type="text" class="form-control required" id="userName" name="title" value="<?php echo $_POST['title']; ?>">
                      </div>
                    </div>
			
                    <div class="form-group clearfix">
                      <label class="col-lg-2 control-label " for="userName">Description </label>
                      <div class="col-lg-10">
                        <textarea type="text" class="form-control required ckeditor" id="userName" name="description"><?php echo $_POST['description']; ?></textarea>
                      </div>
                    </div>
					<div class="form-group clearfix">
                      <label class="col-lg-2 control-label " for="userName">Show In Footer</label>
                      <div class="col-lg-10">
                        <input type="checkbox" class="form-control required "  name="show_in_footer" value="1" <?php if($_POST['show_in_footer']=='1') {echo "checked";} ?>/>
                      </div>
                    </div>
					<div class="form-group clearfix">
					<label class="col-lg-2 control-label " for="userName"> Image </label>
					<div class="col-lg-10">
						<input type="file" class="form-control"  id="image" name="image" value="<?php echo $_POST['image'];?>">
					</div>
					</div>
		
					<div class="form-group clearfix">
                        <label class="col-lg-2 control-label " for="confirm">Status </label>
                        <div class="col-lg-10">
                          <select  class="required form-control" name="status">
                            <option value="1" <?php if($_POST['status']=='1') { echo "selected"; } ?>>Active</option>
                            <option value="0" <?php if($_POST['status']=='0') { echo "selected"; } ?>>Inactive</option>
                          </select>
                        </div>
                    </div>

				       <button type="submit" name="submit" class="btn btn-default">Submit</button>
                    <a  href="<?php echo $iClassName.$FileName; ?>"  class="btn btn-default" >Back</a> </section>
                </div>
              </form>
            </div>
            <?php } elseif($_GET['action']=='edit') { 
					    $aryDetail=$db->getRow("select * from  cms where id='".$_GET['id']."'");
					   ?>
            <div class="card-box">
              <form role="form" action="" method="post" enctype="multipart/form-data">
                <div>
                  <section>
                    <div class="form-group clearfix">
                      <label class="col-lg-2 control-label " for="userName">Title </label>
                      <div class="col-lg-10">
                        <input type="text" class="form-control required" id="userName" name="title" value="<?php echo $aryDetail['title']; ?>">
                      </div>
                    </div>
			
                    <div class="form-group clearfix">
                      <label class="col-lg-2 control-label " for="userName">Description </label>
                      <div class="col-lg-10">
                        <textarea type="text" class="form-control required ckeditor" id="userName" name="description"><?php echo $aryDetail['description']; ?></textarea>
                      </div>
                    </div>
					 
					
					<div class="form-group clearfix">
                      <label class="col-lg-2 control-label " for="userName">Show In Footer</label>
                      <div class="col-lg-10">
                        <input type="checkbox" class="form-control required ckeditor"  name="show_in_footer" value="1" <?php if($aryDetail['show_in_footer']==1){echo "checked";}?>>
                      </div>
                    </div>
					
						<div class="form-group clearfix">
                  <label class="col-lg-2 control-label " for="userName"> Image </label>
                  <div class="col-lg-10">
                    <input type="file" class="form-control"  id="image" name="image" >
                    <input type="hidden" class="form-control" value="<?php echo $aryDetail['image']; ?>"   id="image_old" name="image_old" >
                    <img src="../uploads/<?php echo$aryDetail['image']; ?>" style="height:100px; width:100px;" /><a href="<?php echo $FileName; ?>?action=image&id=<?php echo $_GET['id']; ?>" class="fa fa-times"></a></div>
                </div>
			
					<div class="form-group clearfix">
                        <label class="col-lg-2 control-label " for="confirm">Status </label>
                        <div class="col-lg-10">
                          <select  class="required form-control" name="status">
                            <option value="1" <?php if($_POST['status']=='1') { echo "selected"; } ?>>Active</option>
                            <option value="0" <?php if($_POST['status']=='0') { echo "selected"; } ?>>Inactive</option>
                          </select>
                        </div>
                      </div>
					
					
                    <button type="submit" name="update" class="btn btn-default">Submit</button>
                    <a  href="<?php echo $iClassName.$FileName; ?>"  class="btn btn-default" >Back</a> </section>
                </div>
              </form>
            </div>
          </div>
		   <?php  } 
	elseif($_GET['action']=='view') { 
	$GetEmailId=$db->getRow("select * from  cms where id='".$_GET['id']."'");

	?>
		 <div class="card-box">
              <section>
                 
                <div class="form-group clearfix">
                  <label class="col-lg-2 control-label " for="userName">Title :</label>
                  <?php echo $GetEmailId['title']; ?> </div>
		
				  <div class="form-group clearfix">
                  <label class="col-lg-2 control-label " for="userName">Description :</label>
                  <?php echo $GetEmailId['description']; ?> </div>
		
                <div class="form-group clearfix">
                  <label class="col-lg-2 control-label " for="userName">Image :</label>
                  <img src="../uploads/<?php echo $GetEmailId['image']; ?>" style="height:50px;"> </div>
				<div class="form-group clearfix">
                  <label class="col-lg-2 control-label " for="userName">Show In Footer :</label>
                  <?php	if($GetEmailId['show_in_footer']=='1') { echo "Yes"; } 
							else{ echo "No"; } ?> </div>
				  <div class="form-group clearfix">
                  <label class="col-lg-2 control-label " for="userName">Create At :</label>
                  <?php echo $GetEmailId['create_at']; ?> </div>
                <div class="form-group clearfix">
                  <label class="col-lg-2 control-label " for="userName">Status :</label>
                  <?php if($GetEmailId['status']=='1'){echo "Active";}if($GetEmailId['status']=='0'){echo "Inactive";} ?>
                </div>
                <a  href="<?php echo $FileName; ?>"  class="btn btn-default" >Back</a> </section>
            </div>
          </div>
		
          <?php } else { ?>
		  
          <div class="card-box">
            <table id="datatable" class="table table-striped table-bordered">
              <thead>
                <tr>
                  <th>#</th>
                  <th>Title </th>
                  <th>Show In Footer</th>
                  <th>Image</th>
                  <th>Status</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                <?php $i=0;
				$aryList=$db->getRows("select * from cms where status!=2 order by id desc");
						foreach($aryList as $iList)
							{	$i=$i+1;
								$aryPgAct["id"]=$iList['id'];
							 ?>
                <tr>
                <td><?php echo $i; ?> </td>
                <td><?php echo $iList['title']; ?></td>
				<td><?php  if($iList['show_in_footer']=='1') { echo "Yes"; } 
							else{ echo "No"; } ?> 
				</td>
				<td>
                 <?php if($iList['image']!='') { ?>
                <img src="../uploads/<?php echo $iList['image']; ?>" style="height:50px;">
                <?php } ?>
                </td>
				<td><?php if($iList['status']=='1') { echo "Active"; } 
						  else{ echo "Inactive";} ?> 
				</td>
                <td>
				<a href="<?php echo $FileName; ?>?action=view&id=<?php echo $iList['id']; ?>" class="table-action-btn"> <i class="fa fa-search"></i></a>
                <a href="<?php echo $iClassName.$FileName; ?>?action=edit&id=<?php echo $iList['id']; ?>"  class="table-action-btn" > <i class="fa fa-pencil"></i> </a> 
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
