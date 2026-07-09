<?php include('../config.php'); 
include('inc.session-create.php'); 
$PageTitle="Assessment";
$FileName ='assessment.php';
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
				
			  $aryData=array(					
								'usertype'     	 	         			    =>	$_SESSION['usertype'],
								'userid'     	 	         			    =>	$_SESSION['userid'],
								'session'     	 	         			    =>	$_POST['session'],
								'section'     	 	         			    =>	$_POST['section'],
								'name'     	 	         			        =>	$_POST['name'],
								'create_by_userid'     	 	         	    =>	$create_by_userid,
								'create_by_usertype'     	 	            =>	$create_by_usertype,
								'randomid'     	 	         		        =>	randomFix(15),
																									
							);  
					$flgIn1 = $db->insertAry("school_assessment",$aryData);
					//echo $flgIn1 = $db->getLastQuery();
					//exit;
					$_SESSION['success']="Submitted Successfully";
 					redirect($FileName);
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
						'usertype'     	 	         			    =>	$_SESSION['usertype'],
								'userid'     	 	         			    =>	$_SESSION['userid'],
								'session'     	 	         			    =>	$_POST['session'],
								'section'     	 	         			    =>	$_POST['section'],
								'name'     	 	         			        =>	$_POST['name'],
								'create_by_userid'     	 	         	    =>	$create_by_userid,
								'create_by_usertype'     	 	            =>	$create_by_usertype,
								'randomid'     	 	         		        =>	randomFix(15),
						);  

					$flgIn = $db->updateAry("school_assessment", $aryData , "where randomid='".$_GET['randomid']."'");
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
				 $flgInd5 = $db->delete("school_assessment","where randomid='".$_GET['randomid']."'");					   
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
                      <label class="col-lg-2 control-label " for="userName">Name </label>
                      <div class="col-lg-10">
                        <input type="text" class="form-control required" id="name" name="name" value="<?php echo $_POST['name']; ?>">
                      </div>
                    </div>
					
					
			
			
				<div class="form-group clearfix">
                                            <label class="col-lg-2 control-label" for="employee_id">Section</label>
											<div class="col-lg-10">
											
                                            <select  class="required form-control" name="section" id="section" >
			  <option>Select Section</option>
			  <?php $aryDetail=$db->getRows("select * from  school_section order by id desc");
					   foreach($aryDetail as $iList)
									{	$i=$i+1;?>
             <option value="<?php echo $iList['id']; ?>" <?php  if($_POST['section']==$iList['id']) { echo "selected"; }  ?>><?php echo $iList['section']; ?></option>
									<?php }?>
            </select>
                                        </div></div>
										
										
										
										
											<div class="form-group clearfix">
                                            <label class="col-lg-2 control-label" for="employee_id">Session</label>
                                            <div class="col-lg-10">

                                                <select  class="required form-control" name="session" id="session" >
                                                    <option>Select Session</option>
                                                    <?php $aryDetail=$db->getRows("select * from  school_session order by id desc");
                                                    foreach($aryDetail as $iList)
                                                    {	$i=$i+1;?>
                                                        <option value="<?php echo $iList['id']; ?>" <?php  if($_POST['session']==$iList['id']) { echo "selected"; }  ?>><?php echo $iList['session']; ?></option>
                                                    <?php }?>
                                                </select>
                                            </div></div>
			
		
					

				       <button type="submit" name="submit" class="btn btn-default">Submit</button>
                    <a  href="<?php echo $iClassName.$FileName; ?>"  class="btn btn-default" >Back</a> </section>
                </div>
              </form>
            </div>
            <?php } elseif($_GET['action']=='edit') { 
					    $aryDetail11=$db->getRow("select * from  school_assessment where randomid='".$_GET['randomid']."'");
					   ?>
            <div class="card-box">
              <form role="form" action="" method="post" enctype="multipart/form-data">
                <div>
                  <section>
                    <div class="form-group clearfix">
                      <label class="col-lg-2 control-label " for="userName">Name </label>
                      <div class="col-lg-10">
                        <input type="text" class="form-control required" id="name" name="name" value="<?php echo $aryDetail11['name']; ?>">
                      </div>
                    </div>
					
					
			
			
				<div class="form-group clearfix">
                                            <label class="col-lg-2 control-label" for="employee_id">Section</label>
											<div class="col-lg-10">
											
                                            <select  class="required form-control" name="section" id="section" >
			  <option>Select Section</option>
			  <?php $aryDetail=$db->getRows("select * from  school_section order by id desc");
					   foreach($aryDetail as $iList)
									{	$i=$i+1;?>
             <option value="<?php echo $iList['id']; ?>" <?php  if($aryDetail11['section']==$iList['id']) { echo "selected"; }  ?>><?php echo $iList['section']; ?></option>
									<?php }?>
            </select>
                                        </div></div>
										
										
										
										
											<div class="form-group clearfix">
                                            <label class="col-lg-2 control-label" for="employee_id">Session</label>
                                            <div class="col-lg-10">

                                                <select  class="required form-control" name="session" id="session" >
                                                    <option>Select Session</option>
                                                    <?php $aryDetail=$db->getRows("select * from  school_session order by id desc");
                                                    foreach($aryDetail as $iList)
                                                    {	$i=$i+1;?>
                                                        <option value="<?php echo $iList['id']; ?>" <?php  if($aryDetail11['session']==$iList['id']) { echo "selected"; }  ?>><?php echo $iList['session']; ?></option>
                                                    <?php }?>
                                                </select>
                                            </div></div>
			
                <a  href="<?php echo $FileName; ?>"  class="btn btn-default" >Back</a> </section>
            </div>
          </div>
		
          <?php } else { ?>
		  
          <div class="card-box">
            <table id="datatable" class="table table-striped table-bordered">
              <thead>
                <tr>
                  <th>#</th>
                  <th>Name </th>
				
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                <?php $i=0;
				$aryList=$db->getRows("select * from school_assessment order by id desc");
						foreach($aryList as $iList)
							{	$i=$i+1;
								$aryPgAct["id"]=$iList['id'];
							 ?>
                <tr>
                <td><?php echo $i; ?> </td>
                <td><?php echo $iList['name']; ?></td>
				
			
				
                <td>
			
                <a href="<?php echo $iClassName.$FileName; ?>?action=edit&randomid=<?php echo $iList['randomid']; ?>"  class="table-action-btn" > <i class="fa fa-pencil"></i> </a> 
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
