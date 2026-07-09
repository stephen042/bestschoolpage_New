<?php include('config.php'); 
$validate=new Validation();
if($_SESSION['success']!="")
{
	$stat['success']=$_SESSION['success'];
	unset($_SESSION['success']);
}
if(isset($_POST['submit']))
{
	
	
	$validate->addRule($_POST['school_name'],'','School Name ',true);
	$validate->addRule($_POST['school_address'],'','School Address',true);
	$validate->addRule($_POST['email'],'email','Email ',true);
	$validate->addRule($_POST['password'],'','Password',true);
	$validate->addRule($_POST['confirm_pass'],'','Confirm Password',true);
	$validate->addRule($_POST['school_type'],'','User Type',true);
	$validate->addRule($_POST['contact_no'],'num','Contact No',true,10,10);
	$validate->addRule($_POST['username'],'','User name',true);
		$validate->addRule($_POST['state'],'','state ',true);
		
	if($validate->validate() && count($stat)==0)
	{	
		$iVerifyId=randomFix(10);			
	
		$iCheckEmailId=$db->getVal("select email from  school_register where email='".$_POST['email']."'");
		$iCheckusername=$db->getVal("select username from  school_register where username='".$_POST['username']."'");
$iCheckcontactno=$db->getVal("select contact_no from  school_register where contact_no='".$_POST['contact_no']."'");
   		if($iCheckEmailId!='')
		{ 
			$stat['error']="This email id already exit.";
		}
		elseif($iCheckusername['username']!="")
		{
			$stat['error']="This Username is Already Registered.";
		}
		
		elseif($iCheckcontactno['contact_no']!="")
		{
			$stat['error']="This Contact No. is Already Registered.";
		}
		
		elseif($_POST['password']!=$_POST['confirm_pass'])
		{
			$stat['error']="Password do not match.";
		}
		else
		{
			
			$aryData=array(	
							'name'     	 	         	        =>	$_POST['school_name'],							 
							'username'     	 	         	    =>	$_POST['username'],							 
							'state'     	 	         	    =>	$_POST['state'],
							'contact_no'     	 	            =>	$_POST['contact_no'],
							'email'     	 	         	    =>	$_POST['email'],							 
							'location'     	 	         	    =>	$_POST['school_address'],
							'password'     	 	         	    =>	$_POST['password'],
							'school_type'     	 	            =>	$_POST['school_type'],
							'status'     	 	                =>	0,
							'verifyid'     	 	            	=>	$iVerifyId,
							'create_at'     	 	            =>	date('Y-m-d H:i:s'),
							);  
					$flgIn1 = $db->insertAry("school_register",$aryData);
					echo $flgIn1 = $db->getLastQuery();
					$_SESSION['success'] = "Submitted Successfully!";
					redirect(SITE_URL.'registration.php');		
		}
	}
	else
	{
		$stat['error'] = $validate->errors();
	}
	
}	
?>
<!DOCTYPE html>
<html lang="en-US" prefix="#">
<head>
<?php include('inc.meta.php'); ?>
</head>
<body data-rsssl=1 class="home page-template page-template-page-templates page-template-home page-template-page-templateshome-php page page-id-5780 woocommerce-no-js tribe-no-js">
<div id="container">
<?php include('inc.header.php'); ?>	
	
<section class="registartion-area">
<div class="container">
	<div class="row">	
		<div class="col-md-3"></div>	  
		<div class="col-md-6">
		<form action="" method="POST" class="learnpro-register-form">
			<div class="center-align green-text"><h4>Register your school</h4><p></p></div>
			<div class="center-align green-text"><h4><?php echo msg($stat);?></h4><p></p></div>
			<div class="form-group">
				<input autocomplete="off" class="form-control" placeholder="School Name *"  name="school_name" type="text">
			</div>
			<div class="form-group">
				<input autocomplete="off" class="form-control" placeholder="School Address *"  name="school_address"type="text">
			</div>	
			<div class="form-group">
				<div class="row">	
					<div class="col-md-4">
						<input class="required form-control" placeholder="State*" name=" state" type="text">
					</div>
					<div class="col-md-8">
						<input class="required form-control" placeholder="Email *" name="email" type="email">
					</div>
				</div>
			</div>						
			<div class="form-group"> 
				<div class="row">	
					<div class="col-md-6">
						<input autocomplete="off" class="required form-control" name="contact_no" placeholder="Contact No. *" type="text">
					</div>	
					
					<div class="col-md-6">
						<select class="required form-control" name="school_type" >
						<option>Select School Type</option>
						<?php $i=0;
			$aryList=$db->getRows("select * from school_type ");
				foreach($aryList as $iList)
				{ $i=$i+1;
				?>
							
							<option value="<?php echo $iList['id']; ?>"><?php echo $iList['school_type']; ?></option>
				<?php } ?> 
						</select>
					</div>	
				</div>	
			</div> 
			<div class="admin">
				<span>(Admin login details.)</span>
			</div>
			<div class="form-group">
				<input class="required form-control" placeholder="User Name *" name="username" type="text">
			</div>		
			<div class="form-group">
				<div class="row">	
					<div class="col-md-6">
						<input class="required form-control" name="password" placeholder="Password *" type="password">
					</div>
					<div class="col-md-6">
						<input class="required form-control" name="confirm_pass" placeholder="Confirm Password *" type="password">
					</div>
				</div>
			</div>	
			<div class="form-group sclacu">
				<div class="row">	
					<div class="col-md-6">
						<p class="reva">Already have a school account.</p>
					</div>
					<div class="col-md-6">
						<div class="here"><a href="<?php SITE_URL;?>login.php "><span>login here</span></a></div>
					</div>
				</div>
			</div>	
			<div class="form-group register-btn">
				<div class="row">	
					<div class="col-md-7"></div>
					<div class="col-md-5">
						<button type="submit" name="submit" class="btn btn-primary btn-lg">register school<i class="fa fa-pencil" aria-hidden="true"></i></button>
					</div>
				</div>
			</div>			
		</form>
		</div>
		<div class="col-md-3"></div>
	</div>
</div>
</section>
</div>
<?php include('inc.footer.php'); ?>
<?php include('inc.js.php'); ?>
</body>
<!-- Mirrored from demo.wpzoom.com/academica-pro-3/ by HTTrack Website Copier/3.x [XR&CO'2014], Thu, 17 Jan 2019 11:24:31 GMT -->
</html>
