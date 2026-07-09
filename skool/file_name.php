<?php include('../config.php');
 include('inc.session-create.php');
$PageTitle="File Name";
$FileName="file_name.php";
$validate=new Validation();
if($_SESSION['success']!="")
{
   $stat['success']=$_SESSION['success'];
   unset($_SESSION['success']);
}
if(isset($_POST['addnewrecord']))
 	{ 
	
	 $validate->addRule($_POST['title'],'','Title',true);
	 $validate->addRule($_POST['file_name'],'','File Name',true);
	 
	 if($validate->validate() && count($stat)==0)
			{
				$ILastInerst=$db->getVal("select id from school_filename order by id DESC")+1;
		$iRandom = randomFix(15).'-'.$ILastInerst;
				
  			  $aryData=array(	'usertype'     	 	         		=>	$_SESSION['usertype'],
								'userid'     	 	         	 =>	$_SESSION['userid'],			  
								'title'     	 	         	 =>	$_POST['title'],
								'file_name'     	 	         =>	$_POST['file_name'],
								'create_by_userid'     	 	         	    =>	$create_by_userid,
								'create_by_usertype'     	 	            =>	$create_by_usertype,
								'randomid'     	 	         	  =>	$iRandom,
							 );  
					$flgIn = $db->insertAry("school_filename",$aryData);
					//echo $flgIn = $db->getLastQuery();
					//exit;
			    
					$_SESSION['success']="Registered Successfully";
					redirect($FileName);

			}
			else {
			$stat['error'] = $validate->errors();
			}	
				
  	} 
elseif(isset($_POST['updaterecord']))
	 { 
	  
		  
	 if($validate->validate() && count($stat)==0)
			{
 			  		$aryData=array(	'usertype'     	 	         		=>	$_SESSION['usertype'],
								'userid'     	 	         	 =>	$_SESSION['userid'],			  
								'title'     	 	         	 =>	$_POST['title'],
								'file_name'     	 	         =>	$_POST['file_name'],
								'create_by_userid'     	 	         	    =>	$create_by_userid,
								'create_by_usertype'     	 	            =>	$create_by_usertype,
								'randomid'     	 	         	  =>	$iRandom,  	 
												
							 );  
							$flgIn = $db->updateAry("school_filename", $aryData , "where id='".$_GET['id']."'");
							
 							$_SESSION['success']="Update Successfully";
							redirect($FileName);
							
							
							
			}
			else {
			$stat['error'] = $validate->errors();
			}					
							
							
							
							
 	} 
elseif(($_REQUEST['action']=='delete'))
	{
			 	 $flgIn1 = $db->delete("school_filename","where id='".$_GET['id']."'");	
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
              <?php echo $isubId; ?>
              <ol class="breadcrumb">
                <li> <a href="<?php echo $iClassName; ?>">Home</a> </li>
                <li class="active"> <?php echo $PageTitle; ?> </li>
              </ol>
            </div>
          </div>
          <!-- Basic Form Wizard -->
          <div class="row">
            <div class="col-md-12">
              <div class="card-box">
                <div class="row">
                  <div class="col-sm-8"> <?php echo msg($stat); ?> </div>
                  <div class="col-sm-4"> <a href="<?php echo $FileName; ?>?action=add"  class="btn btn-default" style="float:right">Add New Record</a> </div>
                </div>
              </div>
            </div>
          </div>
          <?php  
					    if($_GET['action']=='add') { ?>
          <div class="card-box">
            <div class="row">
              <div class="col-sm-8"> </div>
            </div>
            <form id="basic-form"  action="" method="post" enctype="multipart/form-data">
              <div class="form-group clearfix">
                <label class="col-lg-2 control-label " for="price">Title*</label>
                <div class="col-lg-10">
                  <input type="text" id="" name="title" value="<?php echo $_POST['title']; ?>" class="form-control ">
                </div>
              </div>
			  <div class="form-group clearfix">
                <label class="col-lg-2 control-label " for="price">File Name*</label>
                <div class="col-lg-10">
                  <input type="text" id="" name="file_name" value="<?php echo $_POST['file_name']; ?>" class="form-control ">
                </div>
              </div>
			 
			<!-- <div class="form-group clearfix">
                <label class="col-lg-2 control-label " for="price">Status</label>
                <div class="col-lg-10">
                  <select name="status" class="form-control ">		  
                  <option value="1" <?php if($_POST['status']=='1'){echo 'selected';} ?>>Approved</option>
                  <option value="0" <?php if($_POST['status']=='0'){echo 'selected';} ?>>Decline</option> 
                  </select>
                </div>
              </div> 
              -->
              
              <button type="submit" name="addnewrecord" class="btn btn-default">Submit</button>
              <a  href="<?php echo  $FileName;  ?>"  class="btn btn-default" >Back</a>
            </form>
          </div>
          <?php }
					    elseif($_GET['action']=='edit') {
						  $aryDetail=$db->getRow("select * from  school_filename where id='".$_GET['id']."'");
						 ?>
          <div class="card-box">
            <form id="basic-form"  action="" method="post" enctype="multipart/form-data">
            <div class="form-group clearfix">
                <label class="col-lg-2 control-label " for="price">Title*</label>
                <div class="col-lg-10">
                  <input type="text" id="" name="title" value="<?php echo $aryDetail['title']; ?>" class="form-control ">
                </div>
              </div>
			  <div class="form-group clearfix">
                <label class="col-lg-2 control-label " for="price">File Name*</label>
                <div class="col-lg-10">
                  <input type="text" id="" name="file_name" value="<?php echo $aryDetail['file_name']; ?>" class="form-control ">
                </div>
              </div>
			 
			 <!--<div class="form-group clearfix">
                <label class="col-lg-2 control-label " for="price">Status</label>
                <div class="col-lg-10">
                  <select name="status" class="form-control ">		  
                  <option value="1" <?php if($aryDetail['status']=='1'){echo 'selected';} ?>>Approved</option>
                  <option value="0" <?php if($aryDetail['status']=='0'){echo 'selected';} ?>>Decline</option> 
                  </select>
                </div>
              </div> -->
              
              <button type="submit" name="updaterecord" class="btn btn-default">Update</button>
              <a  href="<?php echo  $FileName;  ?>"  class="btn btn-default" >Back</a>
            </form>
          </div>
          <?php  } 
			  		 	elseif($_GET['action']=='view') { 
			    $GetEmailId=$db->getRow("select * from  school_filename where id='".$_GET['id']."'");
		?>
          <div class="card-box">
            <div>
              <div class="form-group clearfix">
                <label class="col-lg-2 control-label " for="price">Title:</label>
                <?php echo $GetEmailId['title']; ?> </div>
              
				<div class="form-group clearfix">
                <label class="col-lg-2 control-label " for="price">File Name :</label>
                <?php echo $GetEmailId['file_name']; ?> </div>
				
				<!--<div class="form-group clearfix">
                <label class="col-lg-2 control-label " for="price">Status :</label>
                <?php if($GetEmailId['status']=='1'){echo "Approved";}
						else{echo "Decline";}	?> </div>
				-->
				
              <a  href="<?php echo  $FileName;  ?>"  class="btn btn-default" >Back</a> </div>
          </div>
        </div>
        <?php } 
					 	else {	 ?>
        <div class="card-box">
          <table id="datatable" class="table table-striped table-bordered">
            <thead>
              <tr>
                <th>#</th>
                <th>Title</th>
				<th>File Name</th>
				<th>Action</th>
              </tr>
            </thead>
            <tbody>
              <?php
					 $aryList=$db->getRows("select * from school_filename order by id desc");
							 foreach($aryList as $iList)
									{	$i=$i+1;
									$aryPgAct["id"]=$iList['id'];
							 ?>
              <tr class="<?php if($i%2=='0'){ echo "success"; } else { echo "warning"; } ?>">
                <td><?php echo $i ?></td>
                <td><?php echo $iList['title']; ?></td>
				<td><?php echo $iList['file_name']; ?></td>
				<!--<td><?php if($iList['status']=='1') { echo "Approved"; }
						else { echo "Decline"; }?></td>
				-->
                <td><a href="<?php echo $FileName; ?>?action=view&id=<?php echo $iList['id']; ?>" class="table-action-btn"> <i class="fa fa-search"></i></a> <a href="<?php echo $FileName; ?>?action=edit&id=<?php echo $iList['id']; ?>"  class="table-action-btn" > <i class="fa fa-pencil"></i> </a> <a href="javascript:del('<?php echo $FileName; ?>?action=delete&id=<?php echo $iList['id']; ?>')"    class="table-action-btn" > <i class="fa fa-times"></i> </a></td>
              </tr>
              <?php } ?>
            </tbody>
          </table>
        </div>
      </div>
      <?php  }  ?>
    </div>
  </div>
</div>
</div>
</div>
<?php include('inc.footer.php'); ?>
<?php include('inc.js.php'); ?>
</body>
</html>
?>