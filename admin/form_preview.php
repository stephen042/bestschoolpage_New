<?php include('../config.php'); 
include('inc.session-create.php'); 
$PageTitle="APPLICATION FORMS";
$FileName = 'form_preview.php';
$validate=new Validation();
$currentApp=$db->getRow("select * from  application_form where randomid='".$_GET['randomid']."'");	
$currentForm=$db->getRow("select * from  app_settings where app_id='".$currentApp['id']."'");	

if(isset($_POST['personal_info']))
{                
				$validate->addRule($_POST['class'],'','class',true);
				$validate->addRule($_POST['firstname'],'','First Name',true);
				$validate->addRule($_POST['gender'],'','gender',true);
			    if($validate->validate() && count($stat)==0)
				  {
					$aryData=array(	
								'app_id'                                  => $currentApp['id'],
								'session_id'                              => $currentApp['session'],
								'section_id'                              => $currentApp['section'],
								'class_id'                                => $_POST['class'],
								'bording'                                 => $_POST['bording'],
								'firstname'                               => $_POST['firstname'],
								'lastname'                                => $_POST['lastname'],     
								'othername'                               => $_POST['othername'],
								'dob'                                     => $_POST['dob'],
								'religion'                                => $_POST['religion'],
								'gender'                                  => $_POST['gender'],	
								'nationality_id'  			              => $_POST['nationality'],	
								'nationalitytwo_id'  			          => $_POST['nationalitytwo'],	
								'stateorigine'  			              => $_POST['stateorigine'],	
								'local_state'  			                  => $_POST['local_state'],	
								'child_mail'  			                  => $_POST['child_mail'],	
								'tribechild'  			                  => $_POST['tribechild'],
								'birth_place'  			                  => $_POST['birth_place'],
								'child_address'  			              => $_POST['child_address'],
								'nextKin'  			                      => $_POST['nextKin'],
								'relationOfKin'  			              => $_POST['relationOfKin'],
								'kinPhone'  			                  => $_POST['kinPhone'],
								'hobbies'  			                      => $_POST['hobbies'],
								'childOtherInfo'  			              => $_POST['childOtherInfo'],
					            );  
					$flgIn1 = $db->insertAry("app_personalinfo",$aryData);
					$_SESSION['pform_id']=$flgIn1;
					unset($_POST);
					redirect($FileName.'?action=academics&randomid='.$currentApp['randomid']);
				    }
			else {
				    
					$stat['error'] = $validate->errors();
				 }
}

if(isset($_POST['academy_history']))
{
		if($validate->validate() && count($stat)==0)
		  { 
 			    if($_POST['schoolName']!='')
				{
					$aryData1=array(	
							'app_id'                                  => $currentApp['id'],
							'session_id'                              => $currentApp['session'],
							'section_id'                              => $currentApp['section'],
							'pform_id'                                => $_SESSION['pform_id'],
							'schoolName'                              => $_POST['schoolName'],
							'schoolAddress'                           => $_POST['schoolAddress'],
							'fromDate'                                => $_POST['fromDate'],
							'toDate'                                  => $_POST['toDate'],     
							'attClass'                                => $_POST['attClass'],
							'leaveReason'                             => $_POST['leaveReason'],
						); 
					$flgIn12 = $db->insertAry("app_acadamic",$aryData1);
					$_SESSION['aform_id']=$flgIn12;
					//echo $flgIn12 = $db->getLastQuery();
					redirect($FileName.'?action=gurdian&randomid='.$currentApp['randomid']);
			  }
			}	
			
		else
	{
		$stat['error'] = $validate->errors();
	}
}

if(isset($_POST['guardian_info']))
{
	foreach($_POST['guardian_type']as $key=>$val)
	{
		print_r($_POST);
		$aryData12=array(
							'app_id'                                  => $currentApp['id'],
							'session_id'                              => $currentApp['session'],
							'section_id'                              => $currentApp['section'],
							'aform_id'                                => $_SESSION['aform_id'],
							'pform_id'                                => $_SESSION['pform_id'],
							'guardian_type'                           => $_POST['guardian_type'][$key],
							'gender'                                  => $_POST['g_gender'][$key],
							'nationality'                             => $_POST['g_nationality'][$key],
							'stateOrigin'                             => $_POST['g_stateorigion'][$key],     
							'localGovt'                               => $_POST['g_localArea'][$key],
							'fname'                                   => $_POST['g_fname'][$key],
							'lname'                                   => $_POST['g_lname'][$key],
							'phone'                                   => $_POST['g_phone'][$key],
							'occupation'                              => $_POST['g_occupation'][$key],
							'contact_address'                         => $_POST['g_contAdd'][$key],
							'home_address'                            => $_POST['g_homeAdd'][$key],
						); 
					         $flgIn13 = $db->insertAry("app_guardianInfo",$aryData12);
							 //echo  $flgIn13 = $db->getLastQuery();
							 redirect($FileName.'?action=other_information&randomid='.$currentApp['randomid']);
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
    <!-- Start content -->
    <div class="content">
      <div class="container">
        <!-- Page-Title -->
        <div class="row">
          <div class="col-sm-12">
            <h4 class="page-title"><?php echo $PageTitle; ?></h4>
            <ol class="breadcrumb">
              <li> <a href="">Home</a> </li>
              <li class="active"> Personal Information</li>
            </ol>
          </div>
        </div>

        <div class="row">
          <div class="col-md-12">
            <div class="card-box aplhanewclass">
              <div class="row">
                <div class="col-md-9"> <?php echo msg($stat); ?> </div>
                <div class="col-md-3">
				
				</div>
              </div>
            </div>
			
			  <?php 
					if($_GET['action']=='academics') { 
					if($currentForm['academic_history']!='1'){
					redirect($FileName.'?action=gurdian&randomid='.$_GET['randomid']);
					}
					else {
			   ?>
			<div class="card-box">
			<h1>Previous Academic History</h1>
			   <hr>
              <form role="form" action="" method="POST" enctype="multipart/form-data">
			  <section>
			  
                    <div class="form-group clearfix">
                      <label class="col-lg-2 control-label " for="userName">Schoo Name:</label>
                      <div class="col-lg-10">
                        <input type="text" class="form-control" id="schoolName" name="schoolName" value="<?php echo $_POST['schoolName']; ?>">
                      </div>
                    </div>
					
					<div class="form-group clearfix">
                      <label class="col-lg-2 control-label " for="userName">Schoo Address:</label>
                      <div class="col-lg-10">
                        <input type="text" class="form-control" id="schoolAddress" name="schoolAddress" value="<?php echo $_POST['schoolAddress']; ?>">
                      </div>
                    </div>
                     
					
                      <div class="form-group clearfix">
                      <label class="col-lg-2 control-label " for="userName">From Date:</label>
                      <div class="col-lg-10">
                      <input type="text" class="form-control"  name="fromDate" value="<?php echo $_POST['fromDate']; ?>">
                      </div>
                      </div>
					
					<div class="form-group clearfix">
                      <label class="col-lg-2 control-label " for="userName">To Date:</label>
                      <div class="col-lg-10">
                      <input type="text" class="form-control"  name="toDate" value="<?php echo $_POST['toDate']; ?>">
                      </div>
                      </div>
					  
					  <div class="form-group clearfix">
                      <label class="col-lg-2 control-label " for="userName">Class Attanded:</label>
                      <div class="col-lg-10">
                      <input type="text" class="form-control"  name="attClass" value="<?php echo $_POST['attClass']; ?>">
                      </div>
                      </div>
					   
					   <div class="form-group clearfix">
                      <label class="col-lg-2 control-label " for="userName">Reson for leaving:</label>
                      <div class="col-lg-10">
                      <textarea class="form-control"  name="leaveReason" ><?php echo $_POST['leaveReason']; ?></textarea>
                      </div>
                      </div>
					 <input type="submit" name="academy_history" value="submit" class="btn btn-default" style="float:right;"/>
					<a  href="#"  class="btn btn-default" style="visibility:hidden;">Back</a>
					
			</section> 
				</form>
				</div>
			   <?php } ?>
				<?php  } 
				elseif($_GET['action']=='gurdian') {

                if($currentApp['guardian']!='1'){
				redirect($FileName.'?action=other_information&randomid='.$_GET['randomid']);
			     }
			   else
			   {
				$iGetDet=$db->getRow("select * from  application_form where randomid='".$_GET['randomid']."'");
				$gurdian=$currentApp['no_of_guardian'];
				?>
            <div class="card-box">
              <section>
               <h1>Guardian Information</h1>
			   <hr>
				<?php 
				for ($x = 1; $x <=$gurdian; $x++)
					{
				?>
				<h4>Guardian <?php echo $x;?>:</h4>
			   <hr>
			   <form role="form" action="" method="POST" enctype="multipart/form-data">
			   <div class="form-group clearfix">
                      <label class="col-lg-2 control-label " for="userName">Guardian Type:</label>
                      <div class="col-lg-10">
                        <select name="guardian_type[]"class="form-control">
						<option>Select Guardian</option>
						
						<option value="Father">Father</option>
						<option value="Mother">Mother</option>
						<option value="Uncle">Uncle</option>
						<option value="Aunt">Aunt</option>
						<option value="Other">Other</option>
                        </select>
                      </div>
               </div>
			         
				<div class="form-group clearfix">
                      <label class="col-lg-2 control-label " for="userName">Gender:</label>
                      <div class="col-lg-10">
                      <input type="radio"   name="g_gender[]" value="male">Male
					 <input type="radio"  name="g_gender[]" value="female"> Female
                      </div>
                </div>
			   
			   
			     <div class="form-group clearfix">
                      <label class="col-lg-2 control-label " for="userName">Nationality:</label>
                      <div class="col-lg-10">
                        <select name="g_nationality[]" class="form-control">
						<option>Select Nationality </option>
						<?php
						$getcontry=$db->getRows("select * from  country order by country_name asc");
						foreach($getcontry as $ilist){
						
						?>
						<option value="<?php echo $ilist['id'];?>"><?php echo $ilist['country_name'];?></option>
						<?php }?>
                        </select>
                      </div>
                 </div>
					
					
					
					<div class="form-group clearfix">
                      <label class="col-lg-2 control-label " for="userName">State of Origin:</label>
                      <div class="col-lg-10">
                        <select name="g_stateorigion[]" class="form-control">
						<option>Select state</option>
						<?php
						$getcontry=$db->getRows("select * from  country order by country_name asc");
						foreach($getcontry as $ilist){
						
						?>
						<option value="<?php echo $ilist['id'];?>"><?php echo $ilist['country_name'];?></option>
						<?php }?>
                        </select>
                      </div>
                    </div>
					
				<div class="form-group clearfix">
                      <label class="col-lg-2 control-label " for="userName">Local Goverment area:</label>
                      <div class="col-lg-10">
                        <select name="g_localArea[]" class="form-control">
						<option>Select Local Goverment</option>
						<?php
						$getcontry=$db->getRows("select * from  country order by country_name asc");
						foreach($getcontry as $ilist){
						
						?>
						<option value="<?php echo $ilist['id'];?>"><?php echo $ilist['country_name'];?></option>
						<?php }?>
                        </select>
                      </div>
                    </div>
					
					<div class="form-group clearfix">
                      <label class="col-lg-2 control-label " for="userName">First Name:</label>
                      <div class="col-lg-10">
                        <input type="text" name="g_fname[]" class="form-control"/>
                      </div>
                    </div>
					
					<div class="form-group clearfix">
                      <label class="col-lg-2 control-label " for="userName">Last Name:</label>
                      <div class="col-lg-10">
                        <input type="text" name="g_lname[]" class="form-control"/>
                      </div>
                    </div>
					
					<div class="form-group clearfix">
                      <label class="col-lg-2 control-label " for="userName">Phone Number:</label>
                      <div class="col-lg-10">
                         <input type="number" name="g_phone[]" class="form-control"/>
                      </div>
                    </div>
					
			   
					<div class="form-group clearfix">
					<label class="col-lg-2 control-label " for="userName">Occupation:</label>
					<div class="col-lg-10">
					<input type="text" class="form-control"  name="g_occupation[]" value="<?php echo $getDetails['fee']; ?>">
					</div>
					</div>
					
					
					<div class="form-group clearfix">
					<label class="col-lg-2 control-label " for="userName">Contact Address:</label>
					<div class="col-lg-10">
					<input type="text" class="form-control"  name="g_contAdd[]" value="<?php echo $getDetails['fee']; ?>">
					</div>
					</div>
					
					<div class="form-group clearfix">
					<label class="col-lg-2 control-label " for="userName">Home Address:</label>
					<div class="col-lg-10">
					<input type="text" class="form-control"  name="g_homeAdd[]" value="<?php echo $getDetails['fee']; ?>">
					</div>
					</div>
                   
					<hr>
				 <?php } ?>
				 <a href="" class="btn btn-default" style="visibility: hidden;"></a>
				<input type="submit" class="btn btn-default" name="guardian_info" value="submit" style="float:right;"/>
				
				</form>
				</section>
            </div>
			  
			   <?php } ?>
		 
		 <?php  } 
				elseif($_GET['action']=='other_information') { 
				$iGetDet=$db->getRow("select * from  application_form where randomid='".$_GET['randomid']."'");
				?>
<div class="card-box">
              <section>
               <h1>Additional  Information</h1>
			   <hr>
			   <form role="form" action="" method="POST" enctype="multipart/form-data">
			   <?php 
			   $formData=$db->getRows("select * from  custom_field where app_id='".$iGetDet['id']."'"); 
			   
			   foreach($formData as $formList){
				   
			   ?>
			   <?php 
			    if($formList['field_type']=='1'){
                $form=$db->getRows("select * from  custom_field where app_id='".$formList['app_id']."' and field_type='1'"); 
				foreach($iGetCustomForm as $form){
				?>
				<div class="form-group clearfix">
				<label class="col-lg-2 control-label" for="userName"><?php echo $form['field_name'];?></label>
				<div class="col-lg-10">
				<input type="text" name="fname" class="form-control"/>
				</div>
				</div>
				<?php } } ?>
				
				<?php
				if($formList['field_type']=='2'){
				$iGetCusqqq=$db->getRows("select * from  custom_field where app_id='".$formList['app_id']."' and field_type='2'"); 
                foreach($iGetCusqqq as $formcus){ ?>
				<div class="form-group clearfix">
				<label class="col-lg-2 control-label " for="userName"><?php echo $formcus['field_name'];?></label>
				<div class="col-lg-10">
				<select name="bording"class="form-control">
				<option>Select <?php echo $formcus['field_name'];?></option>
				<?php 
				$CustomFormVal=$db->getRows("select * from  custom_field_value where app_id='".$formcus['app_id']."' and field_id='".$formcus['id']."'");
				foreach($CustomFormVal as $formvalue){
				?>
				<option value="<?php $formvalue['id'];?>"><?php echo $formvalue['value'];?></option>
				<?php } ?>
				</select>
				</div>
				</div>
				<?php } ?>
				<?php } ?> 
					 
				
				<?php if($formList['field_type']=='3'){
                $gettFo11=$db->getRows("select * from  custom_field where app_id='".$formList['app_id']."' and field_type='3'"); 
                foreach($gettFo11 as $formcustt){ 
				?>
			  <div class="form-group clearfix">
                      <label class="col-lg-2 control-label " for="userName"><?php echo $formcustt['field_name'];?></label>
                     <div class="col-lg-10">
                      <textarea type="text" class="form-control"  name="gender"></textarea>
					</div>
               </div>
			    <?php } ?>
				<?php } ?>
				
			<?php if($formList['field_type']=='4'){
             $gettFo12=$db->getRows("select * from  custom_field where app_id='".$formList['app_id']."' and field_type='4'"); 
                foreach($gettFo12 as $formcusttt){
			?>	 
			  <div class="form-group clearfix">
                      <label class="col-lg-2 control-label " for="userName"><?php echo $formcusttt['field_name'];?></label>
                     <div class="col-lg-10">
					 <?php 
				$CustomForVal=$db->getRows("select * from  custom_field_value where app_id='".$formcusttt['app_id']."' and field_id='".$formcusttt['id']."'");
				foreach($CustomForVal as $formval)
				{
				?>
                      <input type="checkbox" name="gender" value="<?php echo $formval['value'];?>"><?php echo $formval['value'];?>
					<?php } ?>
					</div>
               </div>
			   
			    <?php } ?>
			    <?php } ?> 
				
				<?php if($formList['field_type']=='5'){
                 $gettFo15=$db->getRows("select * from  custom_field where app_id='".$formList['app_id']."' and field_type='5'"); 
                foreach($gettFo15 as $formcusttto){
				?>	 
			  <div class="form-group clearfix">
                      <label class="col-lg-2 control-label " for="userName"><?php echo $formcusttto['field_name'];?></label>
                     <div class="col-lg-10">
						<?php 
						$CustomForValo=$db->getRows("select * from  custom_field_value where app_id='".$formcusttto['app_id']."' and field_id='".$formcusttto['id']."'");
						foreach($CustomForValo as $formvalo)
						{
						?>
                      <input type="radio"   name="gender" value="<?php echo $formvalo['value'];?>"><?php echo $formvalo['value'];?>
					<?php } ?>
                     </div>
               </div>
			   
			    <?php } ?>   
				<?php } ?> 
				<?php } ?>  
				
				<input  type="submit" name="additional_info" value="submit & Preview" class="btn btn-default" style="float:right;"/>
				<a  href="<?php echo $FileName; ?>"  class="btn btn-default" style="visibility:hidden;">Next</a> 
				</form>
				</section>
</div>
         
			  <?php } else { ?>
			   <?php $getDetails=$db->getRow("select * from  application_form where randomid='".$_GET['randomid']."'");
			   $getseeion=$db->getRow("select * from  	school_session where id='".$getDetails['section']."'");
			   $getsection=$db->getRow("select * from  	school_section where id='".$getDetails['session']."'"); ?>
              
			 <div class="card-box">
			 <h1>Personal Information</h1>
			   <hr>
                <div>
				<form action="" method="POST">
                  <section>				 				  
				   <div class="form-group clearfix">
                      <label class="col-lg-2 control-label " for="userName">School Session:</label>
                      <div class="col-lg-10">
                        <input type="text" class="form-control" id="session"  name="session" value="<?php echo $getseeion['session']; ?>" disabled>
                      </div>
                    </div>
					<div class="form-group clearfix">
                      <label class="col-lg-2 control-label " for="userName">School Section:</label>
                      <div class="col-lg-10">
                        <input type="text" class="form-control" id="section"  name="section" value="<?php echo $getsection['section']; ?>"disabled>
                      </div>
                    </div>
					
					<div class="form-group clearfix">
                      <label class="col-lg-2 control-label " for="userName">Class to apply:</label>
                      <div class="col-lg-10">
                        <select name="class"  id="class" class="form-control">
						<option>Select class</option>
						<?php 
						$form_class=$getDetails['class'];
						$fclass=explode(",",$form_class);
						foreach($fclass as $listClass){
						$getClass=$db->getRows("select * from   school_class where id='$listClass'");
						foreach($getClass as $iclass){
						?>
						<option value="<?php echo $iclass['id'];?>"<?php if($_POST['class']==$iclass['id']){ echo "selected";}?>><?php echo $iclass['name'];?></option>
						<?php } } ?>
                        </select>
                      </div>
                    </div>
					<?php if($currentForm['boarding_type']=='1'){?>
					<div class="form-group clearfix">
                      <label class="col-lg-2 control-label " for="userName">Bording Type:</label>
                      <div class="col-lg-10">
                        <select name="bording"class="form-control">
						<option>Select Bording</option>
						
						<option value="1"<?php if($_POST['bording']=='1'){ echo "selected";}?>>Day</option>
						<option value="2"<?php if($_POST['bording']=='2'){ echo "selected";}?>>Bording</option>
						<option value="3"<?php if($_POST['bording']=='3'){ echo "selected";}?>>Both</option>
                        </select>
                      </div>
                    </div>
					<?php }?>
					<div class="form-group clearfix">
                      <label class="col-lg-2 control-label " for="userName">First Name:</label>
                      <div class="col-lg-10">
                        <input type="text" name="firstname" class="form-control" value="<?php echo $_POST['firstname'];?>"/>
                      </div>
                    </div>
					
					<div class="form-group clearfix">
                      <label class="col-lg-2 control-label " for="userName">Last Name:</label>
                      <div class="col-lg-10">
                        <input type="text" name="lastname" class="form-control" value="<?php echo $_POST['lastname'];?>"/>
                      </div>
                    </div>
					
					<div class="form-group clearfix">
                      <label class="col-lg-2 control-label " for="userName">Other Name:</label>
                      <div class="col-lg-10">
                         <input type="text" name="othername" class="form-control" value="<?php echo $_POST['othername'];?>"/>
                      </div>
                    </div>
					
                      <div class="form-group clearfix">
                      <label class="col-lg-2 control-label " for="userName">Date of birth:</label>
                      <div class="col-lg-10">
                      <input type="text" class="form-control" id="datepicker" name="dob" value="<?php echo $_POST['dob']; ?>">
                      </div>
                      </div>
					   <?php if($currentForm['religion']=='1'){ ?>
					 <div class="form-group clearfix">
                      <label class="col-lg-2 control-label " for="userName">Religion:</label>
                      <div class="col-lg-10">
                        <select name="religion"class="form-control">
						<option value="">Select Religion</option>
						<option value="Hindu" <?php if($_POST['religion']=="Hindu"){ echo "selected"; }?>>Hindu</option>
						<option value="Muslim" <?php if($_POST['religion']=="Muslim"){ echo "selected"; }?>>Muslim</option>
						<option value="Sikh" <?php if($_POST['religion']=="Sikh"){ echo "selected"; }?>>Sikh</option>
						<option value="Other" <?php if($_POST['religion']=="Other"){ echo "selected"; }?>>Other</option>
                        </select>
                      </div>
                    </div>
					   	 <?php } ?>
					   
					  <div class="form-group clearfix">
                      <label class="col-lg-2 control-label " for="userName">Gender:</label>
                      <div class="col-lg-10">
                      <input type="radio"   name="gender" value="male"<?php if($_POST['gender']=='male'){ echo "checked"; }?>>Male
					  <input type="radio"  name="gender" value="female"<?php if($_POST['gender']=='female'){ echo "checked"; }?>> Female
					  <input type="radio"  name="gender" value="other"<?php if($_POST['gender']=='other'){ echo "checked"; }?>> Other
                      </div>
                      </div>
					  
					  
					 <div class="form-group clearfix">
                      <label class="col-lg-2 control-label " for="userName">Nationality:</label>
                      <div class="col-lg-10">
                        <select name="nationality" class="form-control">
						<option>Select Nationality</option>
						<?php
						$getcontry=$db->getRows("select * from  country order by country_name asc");
						foreach($getcontry as $ilist){
						
						?>
						<option value="<?php echo $ilist['id'];?>"<?php if($_POST['nationality']==$ilist['id']){ echo "selected"; }?>><?php echo $ilist['country_name'];?></option>
						<?php }?>
                        </select>
                      </div>
                    </div>
					
					<?php if($currentForm['second_nationality']=='1'){ ?>
					<div class="form-group clearfix">
                      <label class="col-lg-2 control-label " for="userName">Nationality Two:</label>
                      <div class="col-lg-10">
                        <select name="nationalitytwo" class="form-control">
						<option>Select Nationality Two</option>
						<?php
						$getcontry=$db->getRows("select * from  country order by country_name asc");
						foreach($getcontry as $ilist){
						
						?>
						<option value="<?php echo $ilist['id'];?>"<?php if($_POST['nationalitytwo']==$ilist['id']){ echo "selected"; }?>>
						<?php echo $ilist['country_name'];?></option>
						<?php }?>
                        </select>
                      </div>
                    </div>
					<?php } ?>
					
					<?php if($currentForm['state_local']=='1'){?>
					<div class="form-group clearfix">
                      <label class="col-lg-2 control-label " for="userName">State of Origin:</label>
                      <div class="col-lg-10">
                        <select name="stateorigine" class="form-control">
						<option>Select state</option>
						<?php
						$getcontry=$db->getRows("select * from  local_government order by title asc");
						foreach($getcontry as $ilist){
						
						?>
						<option value="<?php echo $ilist['id'];?>"<?php if($_POST['stateorigine']==$ilist['id']){ echo "selected"; }?>><?php echo $ilist['title'];?></option>
						<?php }?>
                        </select>
                      </div>
                    </div>
					
						<div class="form-group clearfix">
                      <label class="col-lg-2 control-label " for="userName">Local Goverment area:</label>
                      <div class="col-lg-10">
                        <select name="local_state" class="form-control">
						<option>Select Local Goverment</option>
						<?php
						$getstate=$db->getRows("select * from  local_government order by title asc");
						foreach($getstate as $ilist){
						
						?>
						<option value="<?php echo $ilist['id'];?>"<?php if($_POST['local_state']==$ilist['id']){ echo "selected"; }?>>
						<?php echo $ilist['title'];?></option>
						<?php }?>
                        </select>
                      </div>
                    </div>
					<?php } ?>
	 					
					<div class="form-group clearfix">
                      <label class="col-lg-2 control-label " for="userName">Child Email:</label>
                      <div class="col-lg-10">
                         <input type="text" name="child_mail" class="form-control" value="<?php echo $_POST['child_mail']; ?>"/>
                      </div>
                    </div>
					
					<?php if($currentForm['tribe']=='1'){?>
					<div class="form-group clearfix">
                      <label class="col-lg-2 control-label " for="userName">Tribe of child:</label>
                      <div class="col-lg-10">
                         <input type="text" name="tribechild" class="form-control" value="<?php echo $_POST['tribechild']; ?>"/>
                      </div>
                    </div>
					<?php } ?>
					
					<?php if($currentForm['place_of_birth']=='1'){?>
					<div class="form-group clearfix">
                      <label class="col-lg-2 control-label " for="userName">Place of birth:</label>
                      <div class="col-lg-10">
                         <input type="text" name="birth_place" class="form-control" value="<?php echo $_POST['birth_place']; ?>"/>
                      </div>
                    </div>
					<?php } ?>
					<?php if($currentForm['city_address']=='1'){?>
					<div class="form-group clearfix">
                      <label class="col-lg-2 control-label " for="userName">Address of child:</label>
                      <div class="col-lg-10">
                         <input type="text" name="child_address" class="form-control" value="<?php echo $_POST['child_address']; ?>"/>
                      </div>
                    </div>
					<?php } ?>
					<?php if($currentForm['boarding_type']=='1'){ ?>
					<div class="form-group clearfix">
                      <label class="col-lg-2 control-label " for="userName">Next of kin:</label>
                      <div class="col-lg-10">
                         <input type="text" name="nextKin" class="form-control" value="<?php echo $_POST['nextKin']; ?>"/>
                      </div>
                    </div>
					
					<div class="form-group clearfix">
                      <label class="col-lg-2 control-label " for="userName">Relationship with Next of kin:</label>
                      <div class="col-lg-10">
                         <input type="text" name="relationOfKin" class="form-control" value="<?php echo $_POST['relationOfKin']; ?>"/>
                      </div>
                    </div>
					
					<div class="form-group clearfix">
                      <label class="col-lg-2 control-label " for="userName">Next of kin Phone</label>
                      <div class="col-lg-10">
                         <input type="text" name="kinPhone" class="form-control" value="<?php echo $_POST['kinPhone']; ?>"/>
                      </div>
                    </div>
					<?php } ?>
					<div class="form-group clearfix">
                      <label class="col-lg-2 control-label " for="userName">Hobbies:</label>
                      <div class="col-lg-10">
                         <input type="text" name="hobbies" class="form-control" value="<?php echo $_POST['hobbies']; ?>"/>
                      </div>
                    </div>
					  <?php if($currentForm['remarks_field']=='1'){?>
					 <div class="form-group clearfix">
                      <label class="col-lg-2 control-label " for="userName">Other Information about child</label>
                      <div class="col-lg-10">
                         <textarea  name="childOtherInfo" class="form-control">
						 <?php echo $_POST['childOtherInfo']; ?>
						 </textarea>
                      </div>
                    </div> 
					  <?php } ?>
					  
		             <a  href="<?php echo ADMIN_URL; ?>application.php" class="btn btn-default" >Back</a> 
					 <input type="submit" name="personal_info" class="btn btn-default" style="float:right;" value="submit"/>

					</section>
                </div>
              </form>
            </div>
			  <?php } ?>
        </div>
      </div>
    </div>
  </div>
 
  <script>
  $( function() {
    $( "#datepicker" ).datepicker();
  } );
  </script>
  <script>
  $( function() {
    $( "#datepicker1" ).datepicker();
  } );
  </script>
  <?php include('inc.footer.php'); ?>
</div>
</div>
<?php include('inc.js.php'); ?>
</body>
</html>