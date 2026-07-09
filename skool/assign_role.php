<?php include('../config.php');

include('inc.session-create.php'); 

$PageTitle="Assign Roles";
$FileName ='assign_role.php';
$validate=new Validation();
if($_SESSION['success']!="")
{
	$stat['success']=$_SESSION['success'];
	unset($_SESSION['success']);
}
if(isset($_POST['submit']))
{ 
	$validate->addRule($_POST['staff_id'],'','Staff',true);
	$validate->addRule($_POST['role_id'],'','Role',true);
	$validate->addRule($_POST['status'],'','status',true);
      
		if($validate->validate() && count($stat)==0)
		{
				
			  $aryData=array(					
								'staff_id'     	 	         			    =>	$_POST['staff_id'],
								'role_id'     	 	         			    =>	$_POST['role_id'],
								'status'     	 	         			    =>	$_POST['status'],
								'create_by_userid'     	 	         	    =>	$create_by_userid,
								'create_by_usertype'     	 	            =>	$create_by_usertype,
								'randomid'     	 	         		        =>	randomFix(15),
																				
							);  
					$flgIn1 = $db->insertAry("assign_role",$aryData);
					//echo $flgIn1 = $db->getlastQuery();
					//exit;
					
					$_SESSION['success']="Submitted Successfully";
 					redirect($iClassName.$FileName);
					unset($_POST);
 		}
		 else {
			$stat['error'] = $validate->errors();
			}
    } 
 elseif(isset($_POST['update']))
	{  	
	
	
if($validate->validate() && count($stat)==0)
	{			
		

		 $aryData=array(					
								'staff_id'     	 	         			    =>	$_POST['staff_id'],
								'role_id'     	 	         			    =>	$_POST['role_id'],
								'status'     	 	         			    =>	$_POST['status'],
								'create_by_userid'     	 	         			    =>	$create_by_userid,
								'create_by_usertype'     	 	         			    =>	$create_by_usertype,
								'randomid'     	 	         		        =>	randomFix(15),
																				
							);  

					$flgIn = $db->updateAry("assign_role", $aryData , "where id='".$_GET['id']."'");
					$_SESSION['success']="Update Successfully";
					unset($_POST);
					redirect($iClassName.$FileName);
							 
		}	  
		 else {
			$stat['error'] = $validate->errors();
			}
	}

	
	

elseif(($_REQUEST['action']=='delete'))
	{
				 $flgInd5 = $db->delete("assign_role","where id='".$_GET['id']."'");					   
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
                      <label class="col-lg-2 control-label " for="userName">Staff Id </label>
                      <div class="col-lg-10">
                       <select name="staff_id" class="form-control">
                      <option >Select Staff </option>
                      <?php $i=0;
                      $aryList=$db->getRows("select * from staff_manage ");
                      foreach($aryList as $iList)
                      {	$i=$i+1;

                          ?>
						  
						  <option value="<?php echo $iList['id']; ?>" <?php if($_POST['staff_id']==$iList['id']){echo "selected";} ?>> <?php echo $iList['staff_id'].' '.$iList['first_name'].' '.$iList['last_name'];?></option>
						  
                         
                      <?php } ?>
                  </select>
                      </div>
                    </div>
					
					 <div class="form-group clearfix">
                      <label class="col-lg-2 control-label " for="userName">Role </label>
                      <div class="col-lg-10">
                       <select name="role_id" class="form-control">
                      <option >Select Role </option>
                      <?php $i=0;
                      $aryList=$db->getRows("select * from roles ");
                      foreach($aryList as $iList)
                      {	$i=$i+1;

                          ?>
						  
						  <option value="<?php echo $iList['id']; ?>" <?php if($_POST['role_id']==$iList['id']){echo "selected";} ?>> <?php echo $iList['role'];?></option>
						  
                         
                      <?php } ?>
                  </select>
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
					    $aryDetail=$db->getRow("select * from  assign_role where id='".$_GET['id']."'");
					   ?>
            <div class="card-box">
              <form role="form" action="" method="post" enctype="multipart/form-data">
                <div>
                  <section>
                    <div class="form-group clearfix">
                      <label class="col-lg-2 control-label " for="userName">Staff Id </label>
                      <div class="col-lg-10">
                       <select name="staff_id" class="form-control">
                      <option >Select Staff </option>
                      <?php $i=0;
                      $aryList=$db->getRows("select * from staff_manage ");
                      foreach($aryList as $iList)
                      {	$i=$i+1;

                          ?>
						  
						  <option value="<?php echo $iList['id']; ?>" <?php if($aryDetail['staff_id']==$iList['id']){echo "selected";} ?>> <?php echo $iList['staff_id'].' '.$iList['first_name'].' '.$iList['last_name'];?></option>
						  
                         
                      <?php } ?>
                  </select>
                      </div>
                    </div>
					
					 <div class="form-group clearfix">
                      <label class="col-lg-2 control-label " for="userName">Role </label>
                      <div class="col-lg-10">
                       <select name="role_id" class="form-control">
                      <option >Select Role </option>
                      <?php $i=0;
                      $aryList=$db->getRows("select * from roles ");
                      foreach($aryList as $iList)
                      {	$i=$i+1;

                          ?>
						  
						  <option value="<?php echo $iList['id']; ?>" <?php if($aryDetail['role_id']==$iList['id']){echo "selected";} ?>> <?php echo $iList['role'];?></option>
						  
                         
                      <?php } ?>
                  </select>
                      </div>
                    </div>
					
					
			
		
					<div class="form-group clearfix">
                        <label class="col-lg-2 control-label " for="confirm">Status </label>
                        <div class="col-lg-10">
                          <select  class="required form-control" name="status">
                            <option value="1" <?php if($aryDetail['status']=='1') { echo "selected"; } ?>>Active</option>
                            <option value="0" <?php if($aryDetail['status']=='0') { echo "selected"; } ?>>Inactive</option>
                          </select>
                        </div>
                    </div>

				       <button type="submit" name="update" class="btn btn-default">Update</button>
                    <a  href="<?php echo $iClassName.$FileName; ?>"  class="btn btn-default" >Back</a> </section>
                </div>
              </form>
            </div>
          </div>
	
		
          <?php } else { ?>
		  
          <div class="card-box">
            <table id="datatable" class="table table-striped table-bordered">
              <thead>
                <tr>
                  <th>#</th>
                  <th>Staff </th>
				   <th>Role </th>
				 
                  <th>Status</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                <?php $i=0;
				$aryList=$db->getRows("select * from assign_role order by id desc");
						foreach($aryList as $iList)
							{	$i=$i+1;
								$aryPgAct["id"]=$iList['id'];
							 ?>
                <tr>
                <td><?php echo $i; ?> </td>
                <td><?php echo $db->getVal("select staff_id from staff_manage where id='".$iList['staff_id']."'");  ?></td>
				  <td><?php echo $db->getVal("select role from roles where id='".$iList['role_id']."'");  ?></td>
				
			
				<td><?php if($iList['status']=='1') { echo "Active"; } 
						  else{ echo "Inactive";} ?> 
				</td>
                <td>
				
                <a href="<?php echo $iClassName.$FileName; ?>?action=edit&id=<?php echo $iList['id']; ?>"  class="table-action-btn" > <i class="fa fa-pencil"></i> </a> 
			<a href="javascript:del('<?php echo $FileName; ?>?action=delete&id=<?php echo $iList['id']; ?>')" class="table-action-btn" > <i class="fa fa-times"></i> </a>
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
