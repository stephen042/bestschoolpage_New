<?php  
include('../config.php'); 
include('inc.session-create.php'); 
$PageTitle="settings";
$FileName = 'settings.php';
$iClassName = SKOOL_URL;
$validate=new Validation();
if($_SESSION['success']!="")
{
   $stat['success']=$_SESSION['success'];
   unset($_SESSION['success']);
}

if(isset($_POST['update']))
	{ 
                           if(isset($_FILES["companylogo"]["name"]) && !empty($_FILES["companylogo"]["name"]))
                            {  
                               $filename = basename($_FILES['companylogo']['name']);
                              $ext1 = strtolower(substr($filename, strrpos($filename, '.')+1));
                               if(in_array($ext1,array('jpg','png','jpeg', 'gif')))
                            {     
                                $newfile=md5(time())."_".$filename;

                                move_uploaded_file($_FILES['companylogo']['tmp_name'],"../uploads/".$newfile);
                            }       
                            }
                              else
                             { 
                                 $newfile =$_POST['companylogo_old'];
                             }
     $aryData=array(	
					'headerphone'     	 	     	 =>	$_POST['headerphone'],
					'emailid'     	 	         	 =>	$_POST['emailid'],
					'headeraddress'     	 	     =>	$_POST['headeraddress'],
					'companylogo'     	 	         =>	$newfile,
					'facebook_link'     	 	     =>	$_POST['facebook_link'],
					'tweeter_link'     	 	         =>	$_POST['tweeter_link'],
					'google_link'     	 	         =>	$_POST['google_link'],
					'instagram_link'     	 	     =>	$_POST['instagram_link'],
					'customer_service_email'     	 =>	$_POST['customer_service_email'],
					'customer_service_phone'     	 =>	$_POST['customer_service_phone'],
					'customer_service_timing'     	 =>	$_POST['customer_service_timing'],
					'technical_support_email'     	 =>	$_POST['technical_support_email'],
					'technical_support_phone'     	 =>	$_POST['technical_support_phone'],
					'technical_support_timing'     	 =>	$_POST['technical_support_timing'],
					'minreturnday'     	 			 =>	$_POST['minreturnday'],
					'footer_timing'     	         =>	$_POST['footer_timing'],
					'footer_copyrights'     	     =>	$_POST['footer_copyrights'],
					);  
		$flgIn = $db->updateAry("settings", $aryData , "where id='".$_SESSION[LOGIN_ADMIN]['id']."'");
					
					$_SESSION['success']="Profile updatd successfully";
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
            <div class="card-box aplhanewclass">
              <div class="row">
                <div class="col-md-9"> <?php echo msg($stat); ?> </div>
              </div>
            </div>
            <?php   $aryDetail=$db->getRow("select * from  settings");  ?>
            <div class="card-box">
              <form role="form" action="" method="post" enctype="multipart/form-data">
                <div>
                  <section>
                    <div class="form-group clearfix">
                      <label class="col-lg-2 control-label " for="userName">Header Phone*</label>
                      <div class="col-lg-10">
                        <input type="text" class="form-control required" id="headerphone" name="headerphone" value="<?php echo $aryDetail['headerphone']; ?>">
                      </div>
                    </div>
                    <div class="form-group clearfix">
                      <label class="col-lg-2 control-label " for="userName">Header Email Id *</label>
                      <div class="col-lg-10">
                        <input type="text" class="form-control required" id="userName" name="emailid" value="<?php echo $aryDetail['emailid']; ?>">
                      </div>
                    </div>
                    <div class="form-group clearfix">
                      <label class="col-lg-2 control-label " for="userName">Header Address*</label>
                      <div class="col-lg-10">
                        <input type="text" class="form-control required" name="headeraddress" value="<?php echo $aryDetail['headeraddress']; ?>">
                      </div>
                    </div>
                    <div class="form-group clearfix">
                      <label class="col-lg-2 control-label " for="userName">Company Logo*</label>
                      <div class="col-lg-10">
                        <input type="file" class="form-control required" name="companylogo" value="<?php echo $aryDetail['companylogo']; ?>">
					   <input type="hidden" class="form-control required"  id="companylogo_old" name="companylogo_old"  value="<?php echo $aryDetail['companylogo'] ?>" >
					    <img src="../uploads/<?php echo $aryDetail['companylogo'] ?>" style="height:50px;">
                      </div>
                    </div>
                    
                     <div class="form-group clearfix">
                      <label class="col-lg-2 control-label " for="userName">Facebook*</label>
                      <div class="col-lg-10">
                        <input type="text" class="form-control required" name="facebook_link" value="<?php echo $aryDetail['facebook_link']; ?>">
                      </div>
                    </div>
					 <div class="form-group clearfix">
                      <label class="col-lg-2 control-label " for="userName">Tweeter*</label>
                      <div class="col-lg-10">
                        <input type="text" class="form-control required" name="tweeter_link" value="<?php echo $aryDetail['tweeter_link']; ?>">
                      </div>
                    </div>
					 <div class="form-group clearfix">
                      <label class="col-lg-2 control-label " for="userName">Google-Plus*</label>
                      <div class="col-lg-10">
                        <input type="text" class="form-control required" name="google_link" value="<?php echo $aryDetail['google_link']; ?>">
                      </div>
                    </div>
					 <div class="form-group clearfix">
                      <label class="col-lg-2 control-label " for="userName">Instagram*</label>
                      <div class="col-lg-10">
                        <input type="text" class="form-control required" name="instagram_link" value="<?php echo $aryDetail['instagram_link']; ?>">
                      </div>
                    </div>
                    
                     
                     <div class="form-group clearfix">
                      <label class="col-lg-2 control-label " for="userName">Customer Service Email*</label>
                      <div class="col-lg-10">
                        <input type="text" class="form-control required" name="customer_service_email" value="<?php echo $aryDetail['customer_service_email']; ?>">
                      </div>
                    </div>
					<div class="form-group clearfix">
                      <label class="col-lg-2 control-label " for="userName">Customer Service Phone**</label>
                      <div class="col-lg-10">
                        <input type="text" class="form-control required" name="customer_service_phone" value="<?php echo $aryDetail['customer_service_phone']; ?>">
                      </div>
                    </div>
					<div class="form-group clearfix">
                      <label class="col-lg-2 control-label " for="userName">Customer Service Business Day*</label>
                      <div class="col-lg-10">
                        <input type="text" class="form-control required" name="customer_service_timing" value="<?php echo $aryDetail['customer_service_timing']; ?>">
                      </div>
                    </div>
					<div class="form-group clearfix">
                      <label class="col-lg-2 control-label " for="userName">Technical Support Email*</label>
                      <div class="col-lg-10">
                        <input type="text" class="form-control required" name="technical_support_email" value="<?php echo $aryDetail['technical_support_email']; ?>">
                      </div>
                    </div>
					<div class="form-group clearfix">
                      <label class="col-lg-2 control-label " for="userName">Technical Support Phone*</label>
                      <div class="col-lg-10">
                        <input type="text" class="form-control required" name="technical_support_phone" value="<?php echo $aryDetail['technical_support_phone']; ?>">
                      </div>
                    </div>
					<div class="form-group clearfix">
                      <label class="col-lg-2 control-label " for="userName">Technical Support Timing*</label>
                      <div class="col-lg-10">
                        <input type="text" class="form-control required" name="technical_support_timing" value="<?php echo $aryDetail['technical_support_timing']; ?>">
                      </div>
                    </div>
					<div class="form-group clearfix">
                      <label class="col-lg-2 control-label " for="userName">Minimum Days For Return*</label>
                      <div class="col-lg-10">
                        <input type="text" class="form-control required" name="minreturnday" value="<?php echo $aryDetail['minreturnday']; ?>">
                      </div>
                    </div>
					<div class="form-group clearfix">
                      <label class="col-lg-2 control-label " for="userName">Footer Timing*</label>
                      <div class="col-lg-10">
                        <input type="text" class="form-control required" name="footer_timing" value="<?php echo $aryDetail['footer_timing']; ?>">
                      </div>
                    </div>
					<div class="form-group clearfix">
                      <label class="col-lg-2 control-label " for="userName">Footer Copyrights*</label>
                      <div class="col-lg-10">
                        <input type="text" class="form-control required" name="footer_copyrights" value="<?php echo $aryDetail['footer_copyrights']; ?>">
                      </div>
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