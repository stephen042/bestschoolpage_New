<?php include('../config.php'); 
//include('inc.session-create.php'); 
$PageTitle="Form";
$FileName = 'form.php';
$validate=new Validation();
if($_SESSION['success']!="")
{
$stat['success']=$_SESSION['success'];
unset($_SESSION['success']);
}

$getDetails=$db->getRow("select * from  application_form where randomid='".$_GET['randomid']."'");

	
	if(isset($_POST['submit']))
{                
				$validate->addRule($_POST['name'],'','Name',true);
				$validate->addRule($_POST['session'],'',' Session',true);
				$validate->addRule($_POST['section'],'','Section',true);
				$validate->addRule($_POST['start_date'],'','Application start date',true);
				$validate->addRule($_POST['end_date'],'','Application end date',true);
				$validate->addRule($_POST['fee'],'','Fee',true);
				if(empty($_POST['payment'])){
				$validate->addRule($_POST['payment'],'','Payment Method',true);}
				$validate->addRule($_POST['form_prefix'],'',' Form Pre',true);
				$validate->addRule($_POST['form_no_range'],'','Form Range',true);
				$ILastInerst=$db->getVal("select id from application_form");
		        $iPageUrl = PageUrl($_POST['name']).'-'.$ILastInerst;
				 $pay=$_POST['payment'];		
                 $payment=implode(",",$pay);
				 
				 $class=$_POST['class'];		
                  $stclass=implode(",",$class);
									
			    if($validate->validate() && count($stat)==0)
				  {
				  	  
					  
					$aryData=array(	
								'name'                                      => $_POST['name'],
								'session'                                   => $_POST['session'],
								'section'                                   => $_POST['section'],
								 
								'start_date'                                => $_POST['start_date'],
								'end_date'                                  => $_POST['end_date'],     
								'fee'                                       => $_POST['fee'],
								'guardian'                                  => $_POST['guardian'],
								'no_of_guardian'                            => $_POST['no_of_guardian'],
								'payment_method'                            => $payment,	
								'form_prefix'  			                    => $_POST['form_prefix'],	
								'form_no_range'  			                => $_POST['form_no_range'],	
								'status'  			                        => 0,	
								'create_at'  			                    => date("Y-m-d H:i:s"),	
								'randomid'  			                    => randomFix(10),	
								'pageurl'  			                        => $iPageUrl,
								'userid'                                    => $_SESSION['userid'],
								
					            );  
					$flgIn1 = $db->insertAry("application_form",$aryData);
				//echo 	$flgIn1 = $db->getLastQuery(); 
		 		
					$arrayData=array(	
								'app_id'                                             => $flgIn1,
								'admission_req'                                      => 0,
								'design_additional_qeus'                             => 0,
								'academic_history'                                   => 0,
								'kin_from_appl'                                      => 0,
								'remarks_field'                                      => 0,
								'city_address'                                       => 0,
								'place_of_birth'                                     => 0,
								'religion'                                           => 0,
								'tribe'                                              => 0,
								'state_local'                                        => 0,
								'second_nationality'                                 => 0,
								'boarding_type'                                      => 0,
								);  
					$flgI = $db->insertAry("app_settings",$arrayData);
					
					$stat['success']="Submited Successfully";
//redirect($FileName); 
					unset($_POST);
					 
				}
			else {
					$stat['error'] = $validate->errors();
				}
			}
			
			
elseif(isset($_POST['settingssubmit']))
 	{
		       $arrayData=array(
								
								'admission_req'                                      => $_POST['admission_req'],
								'design_additional_qeus'                             => $_POST['additional_quiz'],
								'academic_history'                                   => $_POST['academic_history'],
								'kin_from_appl'                                      => $_POST['kin_applicant'],
								'remarks_field'                                      => $_POST['info_form'],
								'city_address'                                       => $_POST['city_address'],
								'place_of_birth'                                     => $_POST['birth_place'],
								'religion'                                           => $_POST['religion'],
								'tribe'                                              => $_POST['tribe'],
								'state_local'                                        => $_POST['state'],
								'second_nationality'                                 => $_POST['second_nationality'],
								'boarding_type'                                      => $_POST['boarding'],
								'userid'                                             => $_SESSION['userid'],
								);  
					$flgI = $db->updateAry("app_settings", $arrayData , "where app_id='".$getDetails['id']."'");
					$stat['success']="Update Successfully";
				//echo	$flgI = $db->getLastQuery();exit;
					//redirect('application.php');
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
            <h4 class="page-title licat">APPLICATION FORMS</h4>
            <ol class="breadcrumb">
              <li class="dippi"> <a href="<?php echo $iClassName; ?>">Create, edit and design application forms</a> </li>
              
            </ol>
          </div>
        </div>
		
        <!-- Basic Form Wizard -->
        <div class="row">
          <div class="col-md-12">
              <div class="row">
                <div class="col-md-9"> <?php echo msg($stat); ?> </div>
                <div class="col-md-3">
				<div class="gokul">
				<a href="<?php echo $FileName; ?>?action=add"  class="btn btn-default" style="float:right">Add New Record <i class="fa fa-plus" aria-hidden="true"></i></a>
				</div>
              </div>
            </div>
			</div>
			 <div class="col-md-12 ">
            <?php if($_GET['action']=='add') { ?>
            <div class="card-box">
              <form role="form" action="" method="post" enctype="multipart/form-data">
                <div>
                  <section>
				  <div class="col-lg-12">
				  <div class="apli">
				  <span class="romte">Enter Application Form Details. </span>
				  </div>
				  </div>
                     
                    <div class="form-group clearfix plims">
                      <div class="col-lg-12">
					 	<input autocomplete="off" name="name" class="form-control" placeholder="Application Name" type="text">
                      </div>
                    </div>
					<div class="form-group clearfix plims">
                      <div class="col-lg-12">
					  <div class="row">
					  <div class="col-md-4 selson">
					<label class="active" for="gwt-uid-16">Select Session</label>
                    <select class="volsab" name="session">
                    <?php
                          $isession=$db->getRows("select * from school_session where userid!='0'");
						  foreach($isession as $list)
                              {
                               ?>
                        <option value="<?php echo $list['id'];?>"<?php if($_POST['session']==$list['id']){echo "selected";}?>>
						<?php echo $list['session'];?></option>
                              <?php } ?></select>
                    </div>
					 <div class="col-md-4 selson">
					  <label class="active" for="gwt-uid-16">Select Section</label>
					 <select class="volsab"  name="section">
					       <?php
                          $section=$db->getRows("select * from school_section where userid!='0'");
						  foreach($section as $list)
                              {
                               ?>
                        <option value="<?php echo $list['id'];?>"<?php if($_POST['section']==$list['id']){echo "selected";}?>>
						<?php if($list['section']!='OTHERS') { echo $list['section'];}
						elseif($list['section']=='OTHERS') { echo $list['short_name'];}?></option>
						
                              <?php } ?></select>
					 
					
                    </select>
					 </div>
					 
					 <div class="col-md-4 selson">
					   <?php
                          $section=$db->getRows("select * from school_class where section userid!='0'");
						  foreach($section as $list)
                              {
                               ?>
				 	  <span class="gwt-CheckBox" style="display: block;"><input type="checkbox" name="showclass" value=" "  id="gwt-uid-9" tabindex="0"  ><label for="gwt-uid-9">Scratch Card</label>   </span>
					 
							  <?php } ?>
					 </div>
					</div>
					</div>
                    </div>


					<div class="form-group clearfix plims ">
                      <div class="col-lg-12">
                    <div class="row">
					  <div class="col-md-4">
                    <input autocomplete="off" name="start_date" id="datepicker" class="form-control" placeholder="Application start date" type="text">
                    </div>
					 <div class="col-md-4">
					<input autocomplete="off" name="end_date" id="datepicker1" class="form-control" placeholder="Application end date" type="text">
					 </div>
					 <div class="col-md-4">
					<input autocomplete="off"  name="fee"class="form-control" placeholder="Application Fee" type="text">
					 </div>
					</div>
                      </div>
                    </div>

					<div class="form-group clearfix gardin">
                      <div class="col-lg-12">
                       <div class="row">
					  <div class="col-md-5">
                    <span class="gwt-CheckBox" style="display: block;"><input type="checkbox" value="1" name="guardian" onclick="getguardian();" value="on" id="gwt-uid-2" tabindex="0">
					
					<label for="gwt-uid-2">Require Guardian</label></span><br>
					
                    </div>
					 <div class="col-md-7">
					<span class="green-text material-label">Select Payment Methods</span>
					<div class="dptim"><span class="gwt-CheckBox" style="display: block;"><input type="checkbox" name="payment[]" value="1" <?php if(in_array("1",$_POST['payment'])){echo "checked";} ?>id="gwt-uid-9" tabindex="0"  ><label for="gwt-uid-9">Scratch Card</label></span>
					<span class="gwt-CheckBox" style="display: block;"><input type="checkbox"  value="2" <?php if(in_array("2",$_POST['payment'])){echo "checked";} ?>  id="gwt-uid-10" tabindex="0" ><label for="gwt-uid-10">Online</label></span>
					<span class="gwt-CheckBox gwt-CheckBox-disabled" style="display: block;"><input type="checkbox" value="0" <?php if(in_array("0",$_POST['payment'])){echo "checked";} ?>  name="payment[]"  id="gwt-uid-11" tabindex="0"   disabled=""><label for="gwt-uid-11">No Payment</label></span>
					</div>
					 </div>
					 
					</div>
                      </div>
                    </div>

					
					<div class="form-group clearfix plims dokra" style="display:none;" id="guardianno">
                      <div class="col-lg-12">
					   <div class="row pots">
					  <div class="col-md-5">
                       <label class="active" for="gwt-uid-16">Select Max No. of guardian  </label>
					 <select class="volsab"  name="no_of_guardian">
                    <option value="1" >1</option>
                    <option value="2">2</option>
					<option value="3">3</option>
					<option value="4">4</option>
					<option value="5">5</option>
                    </select>
						</div>
					  <div class="col-md-7">
					  
					</div>
					</div>
                    </div>
                    </div>
					
                    <div class="form-group clearfix plims dokra">
                      <div class="col-lg-12">
					   <div class="row pots">
					  <div class="col-md-7">
                        <div class="input-field">
						<label class="active" for="gwt-uid-5">Application Form Prefix</label>
						<input type="text" name="form_prefix" class="gwt-TextBox" placeholder="SKL/2014/" id="gwt-uid-5">
						<span aria-hidden="true" class="material-label" style="display: none;"></span>
						</div>
						</div>
					  <div class="col-md-5">
					  <div class="input-field">
					   <label class="active" for="gwt-uid-16">Form No Range</label>
					  <input type="text"   name="form_no_range" class="gwt-TextBox" placeholder="0000" id="gwt-uid-16">
					 <span aria-hidden="true" class="material-label" style="display: none;"></span>
					 </div>
					  </div>
						</div>
                      </div>
                    </div>
					<div class="form-group clearfix bfrcs ">
                      <div class="col-lg-12 sgot">
					   <div class="row">
                    <div class="savdtls"><button type="submit" name="submit" class="btn btn-default">Save details</button></div>
					</div>
					</div>
					</div>
                </div>
              </form>
            </div>
			
			<?php } elseif($_GET['action']=='settings') { 
			 ?>
			 <?php $getsetting=$db->getRow("select * from  app_settings where app_id='".$getDetails['id']."' "); ?>
		   <div class="card-box">
		   <form action="" method="post">
           <div class="row">
           <div class="col-md-12">
		   <div class="rmtig">
		   <span>Application form setting.</span>
		   </div>
		   <div class="row dimsn">
		   <div class="col-md-12">
		   <div class="nbleds">
		  <span class="material-label">Enable admission requirement</span>
		  <div class="divaq">
		  <label class="switch">
            <input type="checkbox" name="admission_req" value="1" <?php if($getsetting['admission_req']=='1'){echo "checked"; }?>>
            <span class="slider round"></span>
            </label>
			</div>
		   </div>
		   </div>
		   
		   <div class="col-md-12">
		   <div class="nbleds">
		<span class="material-label">Require and design additional questions.</span>
		  <div class="divaq">
		  <label class="switch">
					<input type="checkbox" name="additional_quiz"value="1"<?php if($getsetting['design_additional_qeus']=='1'){echo "checked"; }?>>
            <span class="slider round"></span>
            </label>
			</div>
		   </div>
		   </div>
		   
		   <div class="col-md-12">
		   <div class="nbleds">
		      <span class="material-label">Require academic history.</span>
		  <div class="divaq">
		  <label class="switch">
					<input type="checkbox" name="academic_history"value="1"<?php if($getsetting['academic_history']=='1'){echo "checked"; }?>>
            <span class="slider round"></span>
            </label>
			</div>
		   </div>
		   </div>
		   <hr>
		   
		   <div class="col-md-12">
		   <div class="nbleds">
           <span class="material-label">Require next of kin from applicant.</span>
		  <div class="divaq">
		  <label class="switch">
					<input type="checkbox"name="kin_applicant"value="1"<?php if($getsetting['kin_from_appl']=='1'){echo "checked"; }?>>
            <span class="slider round"></span>
            </label>
			</div>
			</div>
		   </div>
		   
		   
		   <div class="col-md-12">
		   <div class="nbleds">
		  <span class="material-label">Require remarks field in basic info form</span>
		  <div class="divaq">
		  <label class="switch">
					<input type="checkbox"name="info_form"value="1"<?php if($getsetting['remarks_field']=='1'){echo "checked"; }?>>
            <span class="slider round"></span>
            </label>
			</div>
		   </div>
		   </div>
		   
		   <div class="col-md-12">
		   <div class="nbleds">
		  <span class="material-label">Require applicant city or address</span>
		  <div class="divaq">
		  <label class="switch">
					<input type="checkbox"name="city_address"value="1"<?php if($getsetting['city_address']=='1'){echo "checked"; }?>>
            <span class="slider round"></span>
            </label>
			</div>
		   </div>
		   </div>
		   
		   <div class="col-md-12">
		   <div class="nbleds">
		  <span class="material-label">Require applicant place of birth</span>
		  <div class="divaq">
		  <label class="switch">
					<input type="checkbox"name="birth_place"value="1"<?php if($getsetting['place_of_birth']=='1'){echo "checked"; }?>>
            <span class="slider round"></span>
            </label>
			</div>
		   </div>
		   </div>
		   
		   <div class="col-md-12">
		   <div class="nbleds">
		  <span class="material-label">Require applicant religion.</span>
		  <div class="divaq">
		  <label class="switch">
					<input type="checkbox"name="religion"value="1"<?php if($getsetting['religion']=='1'){echo "checked"; }?>>
            <span class="slider round"></span>
            </label>
			</div>
			</div>
		   </div>
		   
		   <div class="col-md-12">
		   <div class="nbleds">
		  <span class="material-label">Require applicant tribe.</span>
		  <div class="divaq">
		  <label class="switch">
					<input type="checkbox"name="tribe"value="1"<?php if($getsetting['tribe']=='1'){echo "checked"; }?>>
            <span class="slider round"></span>
            </label>
			</div>
			</div>
		   </div>
		   
		   <div class="col-md-12">
		   <div class="nbleds">
		  <span class="material-label">Require applicant state of origin and local government</span> 
		  <div class="divaq">
		  <label class="switch">
					<input type="checkbox"name="state"value="1"<?php if($getsetting['state_local']=='1'){echo "checked"; }?>>
            <span class="slider round"></span>
            </label>
			</div>
		   </div>
		   </div>

		   <div class="col-md-12">
		   <div class="nbleds">
		  <span class="material-label">Require second nationality from applicant</span>
		  <div class="divaq">
		  <label class="switch">
					<input type="checkbox"name="second_nationality"value="1"<?php if($getsetting['second_nationality']=='1'){echo "checked"; }?>>
            <span class="slider round"></span>
            </label>
			</div>
		   </div>
		   </div>

		   <div class="col-md-12">
		   <div class="nbleds">
		  <span class="material-label">Allow applicant to choose boarding type</span>
		  <div class="divaq">
		  <label class="switch">
					<input type="checkbox"name="boarding" value="1"<?php if($getsetting['boarding_type']=='1'){echo "checked"; }?>>
            <span class="slider round"></span>
            </label>
			</div>
		   </div>
		   </div>
		  
          <div class="col-md-12">
		  <div class="nbleds">
		   <div class="row">
		   <div class="col-md-9">
           </div>
		   <div class="col-md-3">
		   
		   <p class="svet "> <button  color="white;"type="submit" name="settingssubmit" >SAVE SETTINGS</button></p>
		   </div>
		   </div>
		   </div>
		   </div>		  
		   
		   </div>
		   </div>
		   </div>
		   </form>
           </div>  
			<?php } elseif($_GET['action']=="previewform"){ ?>
  


          <div class="romnring">
           <div class="row">
		   <div class="col-sm-12 ">
            <h4 class="page-title licat">APPLICATION FORMS</h4>
            <ol class="breadcrumb">
              <li class="dippi"> <a href="">Create, edit and design application forms</a> </li>
              
            </ol>
          </div>
           </div>
  
			<div class=" vidol">
           <div class="row">
           <div class="col-md-12">
		    <div class="row mrita">
           <div class="col-md-12 nacpo">
           <button type="submit" name="submit" class="btn btn-default   pull-right">Next Form <i class="fa fa-arrow-right" aria-hidden="true"></i></button>
          </div>
		 </div>
		   
		  <div class="row">
           <div class="col-md-12">
          <div class="tainerd">
		
			<input id="tab1" type="radio" name="tabs" checked style="display:none;">
			<label class="adifo" for="tab1"><div class="prls">
			<div class="circle">1</div>
			<div class="title">Personal</div>
			<div class="line"></div>
			<div></div>
			</div>
			</label>

			<input id="tab2" type="radio" name="tabs" style="display:none;">
			<label class="adifo" for="tab2"><div class="prls">
			<div class="circle">2</div>
			<div class="title">Academics</div>
			<div class="line"></div>
			<div></div>
			</div></label>

			<input id="tab3" type="radio" name="tabs" style="display:none;">
			<label class="adifo" for="tab3"><div class="prls">
			<div class="circle">3</div>
			<div class="title">Guardian</div>
			<div class="line"></div>
			<div></div>
			</div></label>

			<input id="tab4" type="radio" name="tabs" style="display:none;">
			<label class="adifo" for="tab4">
			<div class="prls">
			<div class="circle">4</div>
			<div class="title">Additional info</div>
			<div></div>
			</div>
			</label>


			<section id="content1" class="tab-content">
		   <div class="row">
           <div class="col-md-4 atharo">
					<label class="active" for="gwt-uid-16">Boarding Type</label>
                    <select class="osama">
					<option value="both">Day</option>
                    <option value="volvo" selected="">Boarding</option>
                    <option value="saab">Both</option>
                    </select>
            </div>
				
		
			<div class="col-md-8 gappul">
		   <div class="row aqunt">			
			<div class="col-md-4 shemus">	
			<input autocomplete="off" class="form-control" placeholder="School Session" type="text">
			</div>
			<div class="col-md-4 shemus ">	
			<input autocomplete="off" class="form-control" placeholder="School Section" type="text">
			</div>
			<div class="col-md-4">	
			<label class="active" for="gwt-uid-16">Class to apply</label>
                    <select class="adda">
                    <option value="volvo" selected="">Nursery</option>
                    </select>
			</div>
			</div>	
			
			
			<div class="row  usmant">			
			<div class="col-md-4">	
			<input autocomplete="off" class="form-control" placeholder="First Name" type="text">
			</div>
			<div class="col-md-4 ">	
			<input autocomplete="off" class="form-control" placeholder="Last Name" type="text">
			</div>
			<div class="col-md-4">	
			<input autocomplete="off" class="form-control" placeholder="Other Names" type="text">
			</div>
			</div>	
			
			
			<div class="row">			
			<div class="col-md-8">	

			</div>
			<div class="col-md-3 ">	
			<input autocomplete="off" class="form-control" placeholder="Date of Birth" type="text">
			</div>
			<div class="col-md-1 ">	
			
			</div>
			</div>	
			
			<div class="row bsukna">			
			<div class="col-md-3 ">	
			<label class="active" for="gwt-uid-16">Religion</label>
                    <select class="adda">
                    <option value="volvo" selected="">SELECTED</option>
					<option value="volvo">ISLAM</option>
					<option value="volvo">CHRISTIANITY</option>
					<option value="volvo">OTHER</option>
                    </select>
			</div>
			<div class="col-md-9 ">	
			<div class="kaalin">
			<span class="material-label">Gender</span>
			<div class="ortand">
			  <label><input type="radio" class="radio-inline" name="radios" value=""><span class="outside"><span class="inside"></span></span>Male</label>
              <label><input type="radio"class="radio-inline" name="radios" value=""><span class="outside"><span class="inside"></span></span>Female</label>
			</div>
			</div>
			</div>
			</div>
			
			<div class="row radspl">			
			<div class="col-md-12 ">	
			<label class="active" for="gwt-uid-16">Nationatlity</label>
                    <select class="sonpit">
                    <option value="volvo" selected="">Select Nationatlity</option>
					<option value="volvo">Aruba</option>
					<option value="volvo">Afghanistan</option>
					<option value="volvo">Angola</option>
					<option value="volvo">Brazil</option>
					<option value="volvo">Australia</option>
					<option value="volvo">Armenia</option>
					<option value="volvo">Bermuda</option>
					<option value="volvo">Bolivia</option>
					<option value="volvo">Antartica</option>
					<option value="volvo">Austria</option>
					<option value="volvo">India</option>
					<option value="volvo">England</option>
					<option value="volvo">China</option>
					<option value="volvo">Japan</option>
					<option value="volvo">Burundi</option>
					<option value="volvo">Bulgaria</option>
					<option value="volvo">Pakistan</option>
					<option value="volvo">Bangladesh</option>
					<option value="volvo">Bhutan</option>
					<option value="volvo">China</option>
					<option value="volvo">Korea</option>
                    </select>
			</div>
			</div>
			<div class="row dhst">			
			<div class="col-md-7 ">	
			<label class="active" for="gwt-uid-16">Nationatlity Two</label>
                    <select class="sonpit">
                  <option value="volvo" selected="">Select Nationatlity</option>
					<option value="volvo">Aruba</option>
					<option value="volvo">Afghanistan</option>
					<option value="volvo">Angola</option>
					<option value="volvo">Brazil</option>
					<option value="volvo">Australia</option>
					<option value="volvo">Armenia</option>
					<option value="volvo">Bermuda</option>
					<option value="volvo">Bolivia</option>
					<option value="volvo">Antartica</option>
					<option value="volvo">Austria</option>
					<option value="volvo">India</option>
					<option value="volvo">England</option>
					<option value="volvo">China</option>
					<option value="volvo">Japan</option>
					<option value="volvo">Burundi</option>
					<option value="volvo">Bulgaria</option>
					<option value="volvo">Pakistan</option>
					<option value="volvo">Bangladesh</option>
					<option value="volvo">Bhutan</option>
					<option value="volvo">China</option>
					<option value="volvo">Korea</option>
                    </select>
			</div>
			<div class="col-md-5 ">	
			</div>
			</div>
			
			<div class="row wllst">	
            <div class="col-md-4 ">	
			<label class="active" for="gwt-uid-16">State Of Origin</label>
                    <select class="adda">
                    <option value="volvo" selected="">Abia</option>
					<option value="volvo">Anambra</option>
					<option value="volvo">Adamava</option>
					<option value="volvo">Bauchi</option>
					<option value="volvo">Bauni</option>
					<option value="volvo">Delta</option>
                    </select>
			</div>			
			<div class="col-md-4">	
			<label class="active" for="gwt-uid-16">Local Government Area</label>
                    <select class="adda">
                    <option value="volvo" selected="">Awka south</option>
					<option value="volvo">Njikoka</option>
					<option value="volvo">Adamava</option>
					<option value="volvo">Bauchi</option>
					<option value="volvo">Bauni</option>
					<option value="volvo">Delta</option>
					<option value="volvo">Awka North</option>
					<option value="volvo">Dunkofia</option>
					<option value="volvo">Ogbaru</option>
					<option value="volvo">Oyi</option>
					<option value="volvo">Abia</option>
                    </select>
			</div>
			<div class="col-md-4 joncena">	
			<input autocomplete="off" class="form-control" placeholder="Child email address" type="text">
			</div>
			</div>	
			
			<div class="row wllst">			
			<div class="col-md-3">	
			<input autocomplete="off" class="form-control" placeholder="Tribe of child." type="text">
			</div>
			<div class="col-md-3 tchaild ">	
			<input autocomplete="off" class="form-control" placeholder="Place of Birth" type="text">
			</div>
			<div class="col-md-3">	
			</div>
			</div>	
			<div class="row wllst">			
			<div class="col-md-12 tchaild">	
			<input autocomplete="off" class="form-control" placeholder="Address of child" type="text">
			</div>
			</div>	
			
			<div class="row wllst">			
			<div class="col-md-4 tchaild">	
			<input autocomplete="off" class="form-control" placeholder="Next Of Kin" type="text">
			</div>
			<div class="col-md-4 tchaild">	
			<input autocomplete="off" class="form-control" placeholder="Relationship With Next of Kin" type="text">
			</div>
			<div class="col-md-4 tchaild">	
			<input autocomplete="off" class="form-control" placeholder="Next of Kin Phone No." type="text">
			</div>
			</div>	
			<div class="row wllst">			
			<div class="col-md-12 tchaild">	
			<input autocomplete="off" class="form-control" placeholder="Hobbies" type="text">
			</div>
			</div>				
            <div class="row wllst">			
			<div class="col-md-12">	
			<textarea autocomplete="off" class="form-control" placeholder="Other information about child." type="text"></textarea>
			</div>
			</div>	
			
			</div>
            </div>			
				
			</section>

			<section id="content2" class="tab-content">
			<div class="donalduck">
             <div class="row">
           <div class="col-md-12">
			<div class="uktam"><h3>Previous Academic History</h3></div>	
			</div>
            </div>	
			
			 <div class="row">
           <div class="col-md-12">
			<div class="center ninjhtodi"><button data-toggle="modal" data-target="#squarespaceModal" class="btn btn-primary center-block"><i class="fa fa-plus" aria-hidden="true"></i> Add School Attentance</button></div>


<!-- line modal -->
<div class="modal fade" id="squarespaceModal" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
  <div class="modal-dialog">
	<div class="modal-content">
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">×</span><span class="sr-only">Close</span></button>
			<h3 class="modal-title pull-left" id="lineModalLabel">Add School Attended</h3>
		</div>
		<div class="modal-body">
			
            <!-- content goes here -->
			<form>
              <div class="form-group">
              <input autocomplete="off" class="form-control" placeholder="School Name" type="text">
              </div>
              <div class="form-group">
                <input autocomplete="off" class="form-control" placeholder="Address" type="text">
              </div>
			  <div class="form-group">
			  <div class="row">
			  <div class="col-md-6">
             <input autocomplete="off" class="form-control" placeholder="From Date" type="text">
              </div>
			 <div class="col-md-6">
             <input autocomplete="off" class="form-control" placeholder="To Date" type="text">
              </div>
			  </div>
			  </div>
              <div class="form-group">
             <input autocomplete="off" class="form-control" placeholder="Classes Attended" type="text">
              </div>
             <div class="form-group">
             <input autocomplete="off" class="form-control" placeholder="Reason For Leaving" type="text">
              </div>
			   <div class="row">
			    
		   <div class="col-md-12">
			  <div class="pull-right" role="group">
							  <span class=" nogel"><button type="submit" name="submit" class="btn btn-default  ">Cancel</button></span>	
		  			<span class="skoti">
		  <button type="submit" name="submit" class="btn btn-default">Save</button>	
				</div>
				</div>
            </form>

		</div>
		
	         </div>
            </div>
             </div>
            </div>
            </div>
			
			<div class="row">
           <div class="col-md-3">
		  <span class=" nogel"><button type="submit" name="submit" class="btn btn-default  "><i class="fa fa-clock-o" aria-hidden="true"></i>Edit</button></span>	
		  			<span class="skoti">
		  <button type="submit" name="submit" class="btn btn-default"><i class="fa fa-trash-o" aria-hidden="true"></i> Delete</button>	
			</span>
			</div>

			<div class="col-md-9">
		  
			</div>
            </div>	

			<div class="row loknth">
           <div class="col-md-12">
		   <div class="cdm">
		   <img src="../image/cdm.png" alt="Smiley face">	
			</div>
			<div class="ystm">
			<span >No items on the list yet</span>
			</div>
			</div>
            </div>	
			
			
			<div class="row zebra">
           <div class="col-md-6 tigr">
		  <button type="submit" name="submit" class="btn btn-default pull-left "><i class="fa fa-arrow-left" aria-hidden="true"></i>Prev</button>	
			</div>
			<div class="col-md-6 lion">
		  <button type="submit" name="submit" class="btn btn-default pull-right">Next<i class="fa fa-arrow-right" aria-hidden="true"></i></button>	
			</div>
            </div>	
			
			</div>
			</section>

			<section id="content3" class="tab-content">
			<div class="suzika">
			<div class="row">
           <div class="col-md-12">
			    <div class="panel-group" id="faqAccordion">
        <div class="panel panel-default ">
            <div class="panel-heading accordion-toggle question-toggle collapsed" data-toggle="collapse" data-parent="#faqAccordion" data-target="#question0">
                 <h4 class="panel-title">
                    <a href="#" class="ing">Guardian 1</a>
              </h4>

            </div>
            <div id="question0" class="panel-collapse collapse" style="height: 0px;">
                <div class="panel-body">
                  <div class="row">
           <div class="col-md-8">
			<div class="row">
           <div class="col-md-12">
		   <div class="comki"><h3 class="pull-left" >Guardian Information</h3></div>	
			</div>
            </div>
			
			<div class="row mugambo">
			<div class="col-md-6">
			<label class="active" for="gwt-uid-16">Guardian Type</label>
                    <select class="adda">
                    <option value="volvo" selected="">FATHER</option>
					<option value="volvo">MOTHER</option>
					<option value="volvo">UNCLE</option>
					<option value="volvo">AUNTY</option>
					<option value="volvo">OTHER</option>
                    </select>
			</div>
           <div class="col-md-6">
			<div class="kaalin">
			<span class="material-label">Gender</span>
			<div class="ortand">
			  <label><input type="radio" class="radio-inline" name="radios" value=""><span class="outside"><span class="inside"></span></span>Male</label>
              <label><input type="radio"class="radio-inline" name="radios" value=""><span class="outside"><span class="inside"></span></span>Female</label>
			</div>
			</div>		
			</div>
            </div>	

			<div class="row mugambo">
			<div class="col-md-4">
			<label class="active" for="gwt-uid-16">Nationality</label>
                    <select class="adda">
                    <option value="volvo" selected="">Select Nationatlity</option>
					<option value="volvo">Aruba</option>
					<option value="volvo">Afghanistan</option>
					<option value="volvo">Angola</option>
					<option value="volvo">Brazil</option>
					<option value="volvo">Australia</option>
					<option value="volvo">Armenia</option>
					<option value="volvo">Bermuda</option>
					<option value="volvo">Bolivia</option>
					<option value="volvo">Antartica</option>
					<option value="volvo">Austria</option>
					<option value="volvo">India</option>
					<option value="volvo">England</option>
					<option value="volvo">China</option>
					<option value="volvo">Japan</option>
					<option value="volvo">Burundi</option>
					<option value="volvo">Bulgaria</option>
					<option value="volvo">Pakistan</option>
					<option value="volvo">Bangladesh</option>
					<option value="volvo">Bhutan</option>
					<option value="volvo">China</option>
					<option value="volvo">Korea</option>
                    </select>
			</div>
           <div class="col-md-4">
			<label class="active" for="gwt-uid-16">State of origin</label>
                    <select class="adda">
                   <option value="volvo" selected="">Abia</option>
					<option value="volvo">Anambra</option>
					<option value="volvo">Adamava</option>
					<option value="volvo">Bauchi</option>
					<option value="volvo">Bauni</option>
					<option value="volvo">Delta</option>
                    </select>
			</div>
			<div class="col-md-4">
			<label class="active" for="gwt-uid-16">Local Government Area</label>
                    <select class="adda">
                    <option value="volvo" selected="">Awka south</option>
					<option value="volvo">Njikoka</option>
					<option value="volvo">Adamava</option>
					<option value="volvo">Bauchi</option>
					<option value="volvo">Bauni</option>
					<option value="volvo">Delta</option>
					<option value="volvo">Awka North</option>
					<option value="volvo">Dunkofia</option>
					<option value="volvo">Ogbaru</option>
					<option value="volvo">Oyi</option>
					<option value="volvo">Abia</option>
                    </select>
			</div>
            </div>				
			
			<div class="row mugambo">
           <div class="col-md-6">
		   <label class="active" for="gwt-uid-16">First Name</label>
			<input autocomplete="off" class="form-control" placeholder="" type="text">		   
			</div>
           <div class="col-md-6">
		   <label class="active" for="gwt-uid-16">Last Name</label>
			<input autocomplete="off" class="form-control" placeholder="" type="text">		   
			</div>
            </div>

			<div class="row mugambo">
           <div class="col-md-6">
		    <label class="active" for="gwt-uid-16">Email Address</label>
			<input autocomplete="off" class="form-control" placeholder="" type="text">		   
			</div>
           <div class="col-md-6">
		   <label class="active" for="gwt-uid-16">Address Number( use , for multiple)</label>
			<input autocomplete="off" class="form-control" placeholder="" type="text">		   
			</div>
            </div>
			
			<div class="row mugambo">
           <div class="col-md-12">
		   <label class="active" for="gwt-uid-16">Occupation</label>
			<input autocomplete="off" class="form-control" placeholder="" type="text">		   
			</div>
			</div>
			<div class="row mugambo">
           <div class="col-md-12">
		   <label class="active" for="gwt-uid-16">Contact Address</label>
			<input autocomplete="off" class="form-control" placeholder="" type="text">		   
			</div>
			</div>
			<div class="row mugambo">
           <div class="col-md-12">
		   <label class="active" for="gwt-uid-16">Home Address</label>
			<input autocomplete="off" class="form-control" placeholder="" type="text">		   
			</div>
            </div>
			</div>
			 <div class="col-md-4">
				
			</div>
            </div>   
               </div>
              </div>
              </div>
               </div>	

			</div>
            </div>		
		    
			<div class="row usodenil">
           <div class="col-md-6 bndgiko">
		  <button type="submit" name="submit" class="btn btn-default pull-left "><i class="fa fa-chevron-left" aria-hidden="true"></i>Prev</button>	
			</div>
			<div class="col-md-6 jarrt">
		  <button type="submit" name="submit" class="btn btn-default pull-right">Next<i class="fa fa-arrow-right" aria-hidden="true"></i></button>	
			</div>
            </div>
			
			</div>
			</section>

			<section id="content4" class="tab-content">
			<div class="sharukh">
			<div class="row namika">
           <div class="col-md-12 ">
		  	<div class="brod-btm"></div>
			</div>
            </div>	
			
			<div class="row usodenil">
           <div class="col-md-6 bndgiko">
		  <button type="submit" name="submit" class="btn btn-default pull-left><i class="fa fa-chevron-left" aria-hidden="true"></i>Prev</button>	
			</div>
			<div class="col-md-6 bndgiko ">
		  <button type="submit" name="submit" class="btn btn-default pull-right"><i class="fa fa-floppy-o" aria-hidden="true"></i>Save & Preview</button>	
			</div>
            </div>	
		     </div> 	
			</section>

			
		</div>




          </div>
		 </div>

		  </div>
		   </div>
           </div>  
		   </div>
  </div>
			<?php } elseif($_GET['action']=='edit') { 
			$aryDetail=$db->getRow("select * from  slider where id='".$_GET['id']."'");	
					?>
            <div class="card-box">
              <form role="form" action="" method="post" enctype="multipart/form-data">
                <div>
                  <section>
                   
                    <div class="form-group clearfix">
                      <label class="col-lg-2 control-label " for="userName">Title</label>
                      <div class="col-lg-10">
                        <input type="text" class="form-control " id="title" name="title" value="<?php echo $aryDetail['title']; ?>">
                      </div>
                    </div>
					
					<div class="form-group clearfix">
                      <label class="col-lg-2 control-label " for="userName">Android Icon </label>
                      <div class="col-lg-10">
                        <input type="file" class="form-control "  id="android_icon" name="android_icon">
                        <input type="hidden" class="form-control "  id="android_icon_old" name="android_icon_old"  value="<?php echo $aryDetail['android_icon'] ?>" >
                        <img src="../uploads/<?php echo $aryDetail['android_icon'] ?>" style="height:50px;"> </div>
                    </div>

					<div class="form-group clearfix">
                      <label class="col-lg-2 control-label " for="userName">Description </label>
                      <div class="col-lg-10">
                     <textarea class="form-control  "  name="description" ><?php echo $aryDetail['description']; ?></textarea>
                      </div>
                    </div>

					<div class="form-group clearfix">
                      <label class="col-lg-2 control-label " for="userName">Image </label>
                      <div class="col-lg-10">
                        <input type="file" class="form-control "  id="image" name="image">
                        <input type="hidden" class="form-control "  id="image_old" name="image_old"  value="<?php echo $aryDetail['image'] ?>" >
                        <img src="../uploads/<?php echo $aryDetail['image'] ?>" style="height:50px;"> </div>
                    </div>

                    <div class="form-group clearfix">
                      <label class="col-lg-2 control-label " for="confirm">Status </label>
                      <div class="col-lg-10">
                        <select  class=" form-control" name="status">
                          <option value="1" <?php if($aryDetail['status']=='1') { echo "selected"; } ?>>Active</option>
                          <option value="0" <?php if($aryDetail['status']=='0') { echo "selected"; } ?>>Inactive</option>
                        </select>
                      </div>
                    </div>
                    <button type="submit" name="update" class="btn btn-default">Submit</button>
                    <a  href="<?php echo $FileName; ?>"  class="btn btn-default" >Back</a> </section>
                </div>
              </form>
			  
            </div>
			
            <?php  } 
	elseif($_GET['action']=='view') { 
	$GetEmailId=$db->getRow("select * from  slider where id='".$_GET['id']."'");
	?>
            <div class="card-box">
              <section>
                 
                <div class="form-group clearfix">
                  <label class="col-lg-2 control-label " for="userName">Title :</label>
                  <?php echo $GetEmailId['title']; ?> </div>
				  
				  
				   <div class="form-group clearfix">
                  <label class="col-lg-2 control-label " for="userName">Android Icon :</label>
                  <img src="../uploads/<?php echo $GetEmailId['android_icon']; ?>" style="height:50px;"> </div>

                <div class="form-group clearfix">
                  <label class="col-lg-2 control-label " for="userName">Description :</label>
                  <?php echo $GetEmailId['description']; ?> </div>

                <div class="form-group clearfix">
                  <label class="col-lg-2 control-label " for="userName">Image :</label>
                  <img src="../uploads/<?php echo $GetEmailId['image']; ?>" style="height:50px;"> </div>

                <div class="form-group clearfix">
                  <label class="col-lg-2 control-label " for="userName">Status :</label>
                  <?php if($GetEmailId['status']=='1'){echo "Active";}
				  elseif($GetEmailId['status']=='0'){echo "Inactive";} ?>
                </div>
                <a  href="<?php echo $FileName; ?>"  class="btn btn-default" >Back</a> </section>
            </div>
          </div>
          <?php } else { ?>
		  
		  	<?php
				$i=0;
				$aryList=$db->getRows("select * from application_form  order by id desc");
				foreach($aryList as $iList)
				{	
				$i++;
				$getAppSetting=$db->getRow("select * from  app_settings where app_id='".$iList['id']."'");
				?>
          <div class="card-box">
		  
			
           <div class="row">
          <div class="col-md-12">
            <p class="dmo">Demo</p>
          </div>
        </div> 
		<div class="dlin">
        <div class="row">
		 <div class="row">
          <div class="col-md-12">
          <div class="col-md-4">
            <h4 class="lido">Deadline</h4>
			<p class="jannin"><?php echo $iList['start_date']; ?> - <?php echo $iList['end_date']; ?></p>
          </div>
		  <div class="col-md-2">
            <h4 class="lido">Form Fees</h4>
			<p class="fivtn">₦<?php echo $iList['fee']; ?></p>
          </div>
		            <div class="col-md-6">
            <h4 class="lido">Available Class</h4>
			<p class="nrsry">Nursery</p>
          </div>
        </div> 
		</div>
		</div>
		</div>
		
		
		
		<div class="nile">
		 <div class="row">
          <div class="col-md-9">
		  <div class="row">
          <div class="col-md-2">
            <h4 class="pesd">EDIT</h4>
			</div>
          <div class="col-md-2">
			<a href="<?php echo $FileName; ?>?action=previewform"><h4 class="pesd">PREVIEW FORM</h4></a>
			</div>
			<?php if($getAppSetting['design_additional_qeus']=='1') { ?>
			<div class="col-md-4">
			<h4 class="pesd">DESIGN FORM</h4>
			</div>
			<?php } ?>
			<div class="col-md-4">
			<h4 class="tats">STATUS  </h4>
			<label class="switch">
			
            <input type="checkbox" <?php if($iList['status']=='1'){echo "checked";} else{ echo "unchecked";}?>  >
            <span class="slider round"></span>
            </label>
			</div>
			</div>
          </div>
		  <div class="col-md-3">
		   <div class="row">
          <div class="col-md-6">
            <a href="<?php echo $FileName; ?>?action=settings&randomid=<?php echo $iList['randomid'];?>"><h4 class="sets pull-left"><i class="fa fa-cog" aria-hidden="true"></i>Settings</h4></a>
			</div>
			<div class="col-md-6">
			<h4 class="pesd pull-right"><i class="fa fa-trash" aria-hidden="true"></i></h4>
			</div>
			</div>
          </div>      
        </div> 
        </div>









   </div>
   
   
              <?php } ?>
          <?php } ?>
        </div>

		
		
		
		</div>
    </div>
  </div>
  <?php include('inc.footer.php'); ?>
</div>
</div>
<?php include('inc.js.php'); ?>
<script>

function getguardian(){
	document.getElementById("guardianno").style.display = "block";
	 
	 
}
</script>

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
</body>
</html>
