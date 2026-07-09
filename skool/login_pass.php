<?php include('../config.php'); 
include('inc.session-create.php'); 
$PageTitle="Change Password";
$FileName = 'login_pass.php';
$validate=new Validation();
if($_SESSION['success']!="")
{
   $stat['success']=$_SESSION['success'];
   unset($_SESSION['success']);
}
if(isset($_POST['update']))
 	{ 
$validate->addRule($_POST['current_pass'],'',"Current Password",true);
$validate->addRule($_POST['new_pass'],'',"New Password",true);
$validate->addRule($_POST['renew_pass'],'',"Confirm Password",true);
if($validate->validate())
{
 $aryDetail=$db->getVal("select id from admin_login where id='".$_SESSION[LOGIN_ADMIN]['id']."' and password = '".$_POST['current_pass']."'");
     if($aryDetail!='')
	   {
 	   if($_POST['new_pass']==$_POST['renew_pass']) {
  		   $aryData=array(	
											'password'     	 	         		=>	$_POST['new_pass'],
 						 );  
 			 $flgIn = $db->updateAry("admin_login", $aryData , "where id='".$_SESSION[LOGIN_ADMIN]['id']."'");
 		 $stat['success']="password change Successfully";
 				unset($_POST);
    }
		else {
	    $stat['error']="Confirm password do not match";   
        }	
 }
   else
   {
	$stat['error']="Current Password do not match";   
   }
}
	 else {
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
									<li>
										<a href="<?php echo $iClassName; ?>">Home</a>
									</li>
									 
									<li class="active">
										<?php echo $PageTitle; ?>
									</li>
								</ol>
							</div>
						</div>

                         <div class="row">
                            <div class="col-md-12">
							
                                 <div class="card-box aplhanewclass">
                            		<div class="row">
                            				<div class="col-md-9">
                                        		<?php echo msg($stat); ?>
                                        	</div>
                                   </div>
							</div>
						
                            	<div class="card-box">
 									<form role="form" action="" method="post" enctype="multipart/form-data">
                                        <div>
                                           
                                            <section>
                                                <div class="form-group clearfix">
                                                    <label class="col-lg-2 control-label " for="userName">Current Password *</label>
                                                    <div class="col-lg-10">
                                                        <input type="password" class="form-control required" id="userName" name="current_pass" value="">
                                                    </div>
                                                </div>
                                                <div class="form-group clearfix">
                                                    <label class="col-lg-2 control-label " for="userName">New Password *</label>
                                                    <div class="col-lg-10">
                                                        <input type="password" class="form-control required" id="userName" name="new_pass" value="">
                                                    </div>
                                                </div>
												                                                <div class="form-group clearfix">
                                                    <label class="col-lg-2 control-label " for="userName">Confirm Password *</label>
                                                    <div class="col-lg-10">
                                                        <input type="password" class="form-control required" id="userName" name="renew_pass" value="">
                                                    </div>
                                                </div>
										<button type="submit" name="update" class="btn btn-default">Update</button>
                                            </section>
                                              
                                        </div>
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
