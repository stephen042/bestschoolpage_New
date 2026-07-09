<?php include('../config.php'); 
//include('inc.session-create.php'); 
$PageTitle="Form";
$FileName = 'view_admission.php';
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


                   if(isset($_FILES["android_icon"]["name"]) && !empty($_FILES["android_icon"]["name"]))
					{	 
					$filename = basename($_FILES['android_icon']['name']);
					$ext1 = strtolower(substr($filename, strrpos($filename, '.')+1));
					if(in_array($ext1,array('jpg','png', 'gif','jpeg')))
					{ 	  
					$newfile1=md5(time())."_".$filename;
					move_uploaded_file($_FILES['android_icon']['tmp_name'],"../uploads/".$newfile1);
					}				
					} 					

					$aryData=array(	
					'title'     	 	         		=>	$_POST['title'],
					
					'android_icon'     	 	         		 =>	$newfile1,

					'description'     	 	            =>	$_POST['description'],

					'image'     	 	         		 =>	$newfile,

					'status'     	 	         		 =>	$_POST['status'],	

					);  
					$flgIn1 = $db->insertAry("slider",$aryData);
					
					$_SESSION['success']="Submited Successfully";
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
			
			if(isset($_FILES["android_icon"]["name"]) && !empty($_FILES["android_icon"]["name"]))
			{	 
			$filename = basename($_FILES['android_icon']['name']);
			$ext1 = strtolower(substr($filename, strrpos($filename, '.')+1));
				if(in_array($ext1,array('jpg','png', 'gif','jpeg')))
				{ 	  
					$newfile1=md5(time())."_".$filename;
					move_uploaded_file($_FILES['android_icon']['tmp_name'],"../uploads/".$newfile1);
				}				
			}         
			else { $newfile =$_POST['android_icon_old']; }

					$aryData=array(	
					
					'title'     	 	         		=>	$_POST['title'],
					
					'android_icon'     	 	         		 =>	$newfile1,

					'description'     	 	            =>	$_POST['description'],

					'image'     	 	         		 =>	$newfile,

					'status'     	 	         		 =>	$_POST['status'],	
					);  
					
					$flgIn = $db->updateAry("slider", $aryData , "where id='".$_GET['id']."' ");
					
					$_SESSION['success']="Update Successfully";
					unset($_POST);
					redirect($FileName);
 			 	
			}	  
			else {
				$stat['error'] = $validate->errors();
			}
		}
		elseif(($_REQUEST['action']=='delete'))
		{
		
			$flgIn1 = $db->delete("slider","where id='".$_GET['id']."' ");			
			$_SESSION['success'] = 'Deleted Successfully';
			redirect($FileName);
		} 
?>
<!DOCTYPE html>
<html>
<head>
<?php include('inc.meta.php'); ?>
<link href="assets/css/pak-page.css" rel="stylesheet" type="text/css" />
</head>
<body class="fixed-left">
<div id="wrapper">
  <?php include('inc.header.php'); ?>
  <?php include('inc.sideleft.php'); ?>
  <div class="content-page">
    <!-- Start content -->
	
	
    <div class="content">
      <div class="container">
       <div class="pak-page">
	   <main class="page-wrapper damv">
   
   <div class="secxl"><a style="cursor: pointer;"><i class="fa fa-arrow-left" aria-hidden="true"></i></a></div>
   <div class="grey lighten-3 z-depth-0 secxl" >
      <div class="nav-wrapper">
       <a class="" style="cursor: pointer; font-weight: bold;"><span>LOGOUT</span></a>
         <div class="clearfix"></div>
      </div>
   </div>
   <div>
      <div>
         <div class="row">
            <div class="s12 m12 l12 col">
			<div class="s34">
               <div class="row">
                  <div class="s12 m12 l2 col-sm-2"><img src="https://s3.amazonaws.com/skoolsresources.com/skools/510/logos/510" class="responsive-img" style="width: 100%; margin-top: 60px;"></div>
                  <div class="s12 m12 l10 col-sm-10">
                     <div class="teal-text" style="font-size: 0.4em; font-weight: bold;">
                        <h4 style="font-weight: 300; margin-top: 60px;">DEZVEN</h4>
                        <p></p>
                     </div>
                  </div>
               </div>
               
			   </div>
			   
			   <div class="row">
                  <div class="s12   m6 l6 col-sm-6">
                     <div class="row spokz">
                        <div class="z-depth-0 card">
                           <div class="card-content">
                              <span class="material-label"></span>
                              <div class="canc " style="margin-top: 10px;"><span class="col s2 m2 l2 teal-text material-label" style="font-size: 0.8em; font-weight: bold;">Address:</span><span class="col m10 l10 s10 black-text material-label justi" style="margin-bottom: 10px; font-size: 0.8em;">This is address line is just a sample</span><span class="col s2 m2 l2 teal-text material-label" style="font-size: 0.8em; font-weight: bold;">Contact:</span><span class="col s9  m9 l9 black-text material-label nave" style="margin-bottom: 20px; font-size: 0.8em; font-weight: bold;">788599996</span></div>
                           </div>
                           <div class="card-action"><a style="cursor: pointer;"><span class="icon-att"><i class="fa fa-paperclip" aria-hidden="true"></i></span><span>Visit Website</span></a></div>
                        </div>
                     </div>
                  </div>
                  <div class="s12 m12 l6 col-sm-6">
                     <div>
                        <span class="green-text material-label aply" style="font-size: 0.8em; font-weight: bold;">STEPS ON HOW TO APPLY.</span>
                        <div class="stepper">
                           <div class="step active">
                              <div>
                                 <div class="circle">1</div>
                                 <div class="line"></div>
                              </div>
                              <div>
                                 <div class="title">SELECT ADMISSION SECTION</div>
                                 <div></div>
                                 <div class="body"><span class="material-label">1. Select any of the open admission section</span><span class="material-label">2. Click the apply button to proceed</span></div>
                              </div>
                           </div>
                           <div class="step inactive">
                              <div>
                                 <div class="circle">2</div>
                                 <div class="line"></div>
                              </div>
                              <div>
                                 <div class="title">CREATE ACCOUNT</div>
                                 <div></div>
                                 <div class="body"><span class="material-label">1. Fill out sign up page</span><span class="material-label">3. An email will be sent to you, click on the link in the email to verify</span></div>
                              </div>
                           </div>
                           <div class="step inactive">
                              <div>
                                 <div class="circle">3</div>
                                 <div class="line"></div>
                              </div>
                              <div>
                                 <div class="title">EMAIL VERIFICATION</div>
                                 <div></div>
                                 <div class="body"><span class="material-label">1. Email verification successful</span><span class="material-label">2. Click the proceed to payment button</span></div>
                              </div>
                           </div>
                           <div class="step inactive">
                              <div>
                                 <div class="circle">4</div>
                                 <div class="line"></div>
                              </div>
                              <div>
                                 <div class="title">MAKE PAYMENT</div>
                                 <div></div>
                                 <div class="body"><span class="material-label">1. Select payment method</span><span class="material-label">2. If you selected scratch card, enter pin and serial number of scratch card you acquired from school</span><span class="material-label">3. If you selected online, click pay now to make payment with your ATM card</span><span class="material-label">4. Successful payments will direct you to the application form</span></div>
                              </div>
                           </div>
                           <div class="step inactive">
                              <div>
                                 <div class="circle">5</div>
                                 <div class="line"></div>
                              </div>
                              <div>
                                 <div class="title">FILL OUT FORM</div>
                                 <div></div>
                                 <div class="body"><span class="material-label">Fill out all information on application form</span><span class="material-label">1. After filling out form, preview to verify information</span><span class="material-label">2. Click on the submit button</span><span class="material-label">3. Await response from the school</span><span class="material-label">1. After filling out form, preview to verify information</span><span class="material-label">2. Click on the submit button</span><span class="material-label">3. Await response from the school</span></div>
                              </div>
                           </div>
                        </div>
                     </div>
                  </div>
               </div>
               <div class="row">
                  <div class="col">
                     <div class="teal-text" style="margin-top: 60px;">
                        <h4 style="font-weight: 300; margin-top: 60px;">ADMISSIONS</h4>
                        <p>Select any of the available admission</p>
                     </div>
                  </div>
               </div>
               <div class="row">
                  <div class="s12 l12 m12 col-sm-12">
                     <div class="z-depth-0 card">
                        <div class="black-text card-content">
                           <span class="card-title activator" style="font-size: 1.5em;"><span>Demo</span></span><span class="col s12 l12 m12 material-label"></span>
                           <div class="row">
                              <div class="s4 col lina"><span class="col s12 teal-text material-label deal" style="font-size: 1.2em;">Deadline</span><span class="col s12 material-label" style="font-size: 1em;">2019 Jan 19 - 2019 Jan 30</span></div>
                              <div class="s2 col"><span class="col s12 teal-text material-label deal" style="font-size: 1.2em;">Form Fees</span><span class="col s12 material-label" style="font-size: 0.8em;">₦10252</span></div>
                              <div class="s6 col">
                                 <span class="teal-text material-label valu" style="font-size: 1.2em;">Available Class</span>
                                 <div class="left chip" style="margin: 4px;"><span>Nursery</span></div>
                              </div>
                           </div>
                        </div>
                        <div class="card-action">
                           <div style=""><a class="blue-text" href="" style="cursor: pointer;"><span>Admission Requirements</span></a><button type="button" class="btn-flat green-text disabled" aria-hidden="true" disabled="" data-delay="350" data-position="bottom" data-tooltip-id="7f67bbb7-3cd9-e87a-754f-27d738790612" data-tooltip="You are logged in as  school admin." style="cursor: pointer; margin-right: 30px; font-weight: bolder; display: none;"><span>APPLY NOW</span></button></div>
                        </div>
                     </div>
                  </div>
               </div>
            </div>
         </div>
      </div>
   </div>
</main>
	   </div>
	</div>
  </div>

  
  
  
  <?php include('inc.footer.php'); ?>
</div>
</div>
<?php include('inc.js.php'); ?>
</body>
</html>
