<?php include('../config.php');
$PageTitle="FAQ";
$FileName ='faq.php';
$validate=new Validation();
if($_SESSION['success']!="")
{
   $stat['success']=$_SESSION['success'];
   unset($_SESSION['success']);
}
if(isset($_POST['addnewrecord']))
 	{ 

		 
		$validate->addRule($_POST['question'],'','Question',true);
    	$validate->addRule($_POST['answer'],'','Answer',true);	
	  
	  
	  if($validate->validate() && count($stat)==0)
		{
			
			$aryData = array(		
								
								/*'category'     	=>	$_POST['category'],*/
								'question'     	=>	$_POST['question'],
								'answer'     	=>	$_POST['answer'],
								/*'faq_type'     	=>	$_POST['faq_type'],*/
								'status'     	=>	$_POST['status'],
							);  
					$flgIn = $db->insertAry("faq",$aryData);		
					$_SESSION['success']="Submited Successfully";
					redirect($FileName);
		}	
		else 
		{
			$stat['error'] = $validate->errors();
		}	
	} 
elseif(isset($_POST['updaterecord']))
	{ 	
		
 
		$validate->addRule($_POST['question'],'','Question',true);
		$validate->addRule($_POST['answer'],'','Answer',true);	
	  
      if($validate->validate() && count($stat)==0)
		{	
			$aryData = array(	
							
								/*'category'     	=>	$_POST['category'],*/
								'question'     	=>	$_POST['question'],
								'answer'     	=>	$_POST['answer'],
								'status'     	=>	$_POST['status'],
									/*'faq_type'     	=>	$_POST['faq_type'],*/
								
					        );  
				$flgIn = $db->updateAry("faq", $aryData , "where id='".$_GET['id']."'");
				$_SESSION['success']="Update Successfully";
					        redirect($FileName);
 		}
		
		else 
		{
			$stat['error'] = $validate->errors();
		}					
							
	} 
	
			                /* ----------ENDING INSERT(ADD NEW RECORD) SECTION ------------*/
	
	
			                       /* ----------STARTING DELETE SECTION ------------*/
	
elseif(($_REQUEST['action']=='delete'))
	{
			 	$flgIn = $db->delete("faq","where id='".$_GET['id']."'");
			 	$_SESSION['success'] = 'Deleted Successfully';
                redirect($FileName);
	} 
?>
 	
<!DOCTYPE html>
<html lang="en">
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
                  <div class="col-sm-12"> <a href="<?php echo $FileName; ?>?action=add"  class="btn btn-default" style="float:right">Add New Record</a> </div>
                </div>
              </div>
            </div>
          </div>
                         <!-------------ENDING DELETE SECTION ------------>
		  
			        <!----------    STARTING ADD NEW RECORD SECTION  ------------->		
		  
		  
		  <?php  
					    if($_GET['action']=='add') { ?>
          <div class="card-box">
            <div class="row">
              <div class="col-sm-8"> </div>
            </div>
            <form id="basic-form"  action="" method="post" enctype="multipart/form-data">
               
			<!--	<div class="form-group clearfix">
                <label class="col-lg-2 control-label " for="price">Category*</label>
                <div class="col-lg-10">
					<select name="category" class="form-control required">
						<option value="">Select</option>
					<?php 
					$aryList=$db->getRows("select * from faq_category where status='1' order by id desc");
						foreach($aryList as $iList)
						{ ?>
						<option value="<?php echo $iList['id']; ?>" <?php if($_POST['category']==$iList['id']) { echo "selected"; } ?>><?php echo $iList['title']; ?></option>
					<?php } ?>
					</select>
                </div>
                </div>-->
				
				<div class="form-group clearfix">
                <label class="col-lg-2 control-label " for="price">Question*</label>
                <div class="col-lg-10">
                <input type="text" class="form-control required" name="question" value="<?php echo $_POST['question']; ?>"/>
                </div>
                </div>
				
				<div class="form-group clearfix">
				<label class="col-lg-2 control-label " for="userName">Answer*</label>
				<div class="col-lg-10">
				<textarea type="text" class="form-control required" name="answer"><?php echo $_POST['answer']; ?></textarea>
				</div>
				</div>

				<!--
					<div class="form-group clearfix">
                <label class="col-lg-2 control-label " for="confirm">Type </label>
                <div class="col-lg-10">
                <select  class="required form-control" name="faq_type">
                <option value="1" <?php if($_POST['faq_type']=='1') { echo "selected"; } ?>>For FAQ Page</option>
                <option value="0" <?php if($_POST['faq_type']=='0') { echo "selected"; } ?>>For Pcakge Page</option>
                </select>
                </div>
                </div>-->
				
				<div class="form-group clearfix">
				<label class="col-lg-2 control-label " for="confirm">Status </label>
				<div class="col-lg-10">
				<select  class="required form-control" name="status">
				<option value="1" <?php if($_POST['status']=='1') { echo "selected"; } ?>>Active</option>
				<option value="0" <?php if($_POST['status']=='0') { echo "selected"; } ?>>In Active</option>
				</select>
				</div>
				</div>				
				
			<button type="submit" name="addnewrecord" class="btn btn-default">Submit</button>
            <a  href="<?php echo  $FileName;  ?>"  class="btn btn-default" >Back</a>
            </form>
          </div>
          <?php }
					    
					 /*----------    ENDING ADD NEW RECORD SECTION  -------------*/			
						
					/*--------------    STARTING EDIT/UPDATE SECTION   ------------->	*/
						
						
						
						
						elseif($_GET['action']=='edit') {
						  $aryDetail=$db->getRow("select * from faq where id='".$_GET['id']."'");
						  ?>
          <div class="card-box">
            <form id="basic-form"  action="" method="post" enctype="multipart/form-data">
              
				<!--<div class="form-group clearfix">
                <label class="col-lg-2 control-label " for="price">Category*</label>
                <div class="col-lg-10">
					<select name="category" class="form-control required">
						<option value="">Select</option>
					<?php 
					$aryList=$db->getRows("select * from faq_category where status='1' order by id desc");
						foreach($aryList as $iList)
						{ ?>
						<option value="<?php echo $iList['id']; ?>" <?php if($aryDetail['category']==$iList['id']) { echo "selected"; } ?>><?php echo $iList['title']; ?></option>
					<?php } ?>
					</select>
                </div>
                </div>
				-->
				<div class="form-group clearfix">
                <label class="col-lg-2 control-label " for="price">Question</label>
                <div class="col-lg-10">
                <input type="text" name="question" value="<?php echo $aryDetail['question']; ?>" class="form-control "/>
                </div>
				</div>
				
				<div class="form-group clearfix">
				<label class="col-lg-2 control-label " for="userName">Answer</label>
				<div class="col-lg-10">
				<textarea type="text" class="form-control required" name="answer"><?php echo $aryDetail['answer']; ?></textarea>
				</div>
				</div>
  
   
				<!--<div class="form-group clearfix">
                <label class="col-lg-2 control-label " for="confirm">Type </label>
                <div class="col-lg-10">
                <select  class="required form-control" name="faq_type">
                <option value="1" <?php if($aryDetail['faq_type']=='1') { echo "selected"; } ?>>For FAQ Page</option>
                <option value="0" <?php if($aryDetail['faq_type']=='0') { echo "selected"; } ?>>For Pcakge Page</option>
                </select>
                </div>
                </div>-->
				
				<div class="form-group clearfix">
                <label class="col-lg-2 control-label " for="confirm">Status </label>
                <div class="col-lg-10">
                <select  class="required form-control" name="status">
                <option value="1" <?php if($aryDetail['status']=='1') { echo "selected"; } ?>>Active</option>
                <option value="0" <?php if($aryDetail['status']=='0') { echo "selected"; } ?>>In Active</option>
                </select>
                </div>
                </div>
  
			<button type="submit" name="updaterecord" class="btn btn-default">Update</button>
            <a  href="<?php echo  $FileName;  ?>"  class="btn btn-default" >Back</a>
            
			</form>
			</div>
            
		
			<?php  } 
			  		 	
					/*--------------    ENDING EDIT/UPDATE SECTION   ------------->	*/	
						
						/*--------------    STARTING VIEW SECTION   ------------->	*/
						
						
						
						
						
						elseif($_GET['action']=='view') { 
			    $GetEmailId=$db->getRow("select * from  faq where id='".$_GET['id']."'");
		?>
          <div class="card-box">
            <div>
			
				<!--<div class="form-group clearfix">
					<label class="col-lg-2 control-label " for="price">Category:</label>
					<div class="col-lg-10">
						<?php echo $db->getVal("select title from faq_category where id='".$GetEmailId['category']."'"); ?>
					</div> 
				</div>
				-->
				<div class="form-group clearfix">
                <label class="col-lg-2 control-label " for="price">Question:</label>
                <div class="col-lg-10"><?php echo $GetEmailId['question']; ?></div> 
				</div>
				
				<div class="form-group clearfix">
                <label class="col-lg-2 control-label " for="price">Answer:</label>
                <div class="col-lg-10"><?php echo $GetEmailId['answer']; ?></div> 
				</div>
				
				
				<!--<div class="form-group clearfix">
                <label class="col-lg-2 control-label " for="price">Type :</label>
                <div class="col-lg-10"><?php if($GetEmailId['faq_type']==1){echo "For FAQ Page";} else{echo "For Package Page";} ?>
				</div> 
				</div>-->
				
				
				<div class="form-group clearfix">
                <label class="col-lg-2 control-label " for="price">Status :</label>
                <div class="col-lg-10"><?php if($GetEmailId['status']==1){echo "Active";} else{echo "In Active";} ?>
				</div> 
				</div>
				
              <a  href="<?php echo  $FileName;  ?>"  class="btn btn-default" >Back</a> </div>
          </div>
        </div>
        <?php } 
					 	else {	 ?>
						
						
						<!----------------    ENDING VIEW SECTION   ------------->
						
						<!----------------    STARTING SHOW SECTION  ------------>
						
						
						
						
        <div class="card-box">
          <table id="datatable" class="table table-striped table-bordered">
            <thead>
              <tr>
                <th>Id</th>
               <!-- <th>Category</th>-->
                <th>Question</th>
				<th>Answer</th>
				<!--<th>Type</th>-->
				<th>Status</th>
				<th>Action</th>
              </tr>
            </thead>
            <tbody>
				  <?php
						 $aryList=$db->getRows("select * from faq order by id desc");
								 foreach($aryList as $iList)
										{	$i=$i+1;
										$aryPgAct["id"]=$iList['id'];
								 ?>
              <tr>
                <td><?php echo $i ?></td>

			<!--	<td><?php echo $db->getVal("select title from faq_category where id='".$iList['category']."'"); ?></td>-->
				<td><?php echo $iList['question'];?></td>
				<td><?php echo $iList['answer'];?></td>
				<!--	<td><?php if($iList['faq_type']=='1'){echo "For FAQ Page";}
						  if($iList['faq_type']=='0'){echo "For Package Page";}?> 
				</td>-->
				<td><?php if($iList['status']=='1'){echo "Active";}
						  if($iList['status']=='0'){echo "In Active";}?> 
				</td>
				
				<td><a href="<?php echo $FileName; ?>?action=view&id=<?php echo $iList['id']; ?>" class="table-action-btn"> <i class="fa fa-search"></i></a>
					<a href="<?php echo $FileName; ?>?action=edit&id=<?php echo $iList['id']; ?>"  class="table-action-btn" > <i class="fa fa-pencil"></i></a>
					<a href="javascript:del('<?php echo $FileName; ?>?action=delete&id=<?php echo $iList['id']; ?>')" class="table-action-btn" > <i class="fa fa-times"></i> </a></td>
              </tr>
              <?php } ?>
            </tbody>
          </table>
        </div>
		
					<!----------------    ENDING SHOW SECTION  ------------>
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
