<?php include('../config.php'); 
include('inc.session-create.php'); 
$PageTitle="Application";
$FileName = 'application.php';
$validate=new Validation();
// initialize $stat to collect status messages
$stat = array();
if(!empty($_SESSION['success'])) {
	$stat['success'] = $_SESSION['success'];
	unset($_SESSION['success']);
}
$currentApp=$db->getRow("select * from  application_form where randomid='".$_GET['randomid']."'");	

$currentForm=$db->getRow("select * from  app_settings where app_id='".$currentApp['id']."'");


$getDetails=$db->getRow("select * from  application_form where randomid='".$_GET['randomid']."'");
$getpersonalinfo=$db->getRow("select * from   app_personalinfo where randomid='".$_GET['randomid']."'");
$getachademic=$db->getRow("select * from  app_acadamic where randomid='".$_GET['randomid']."'");

	
if(isset($_POST['submit']))
			{                
				/*$validate->addRule($_POST['name'],'','Name',true);
				$validate->addRule($_POST['session'],'',' Session',true);
				$validate->addRule($_POST['section'],'','Section',true);
				$validate->addRule($_POST['start_date'],'','Application start date',true);
				$validate->addRule($_POST['end_date'],'','Application end date',true);
				$validate->addRule($_POST['fee'],'','Fee',true);
				if(empty($_POST['payment'])){
				$validate->addRule($_POST['payment'],'','Payment Method',true);}
				//$validate->addRule($_POST['form_prefix'],'',' Form Pre',true);
				$validate->addRule($_POST['form_no_range'],'','Form Range',true);*/
				$ILastInerst=$db->getVal("select id from application_form");
		        $iPageUrl = PageUrl($_POST['name']).'-'.$ILastInerst;
				 $pay=$_POST['payment'];		
                 $payment=implode(",",$pay);
				 
				 $class=$_POST['selectclass'];		
                 $stclass=implode(",",$class);
									
			    if($validate->validate() && count($stat)==0)
				  {
				  	  
					  
					$aryData=array(	
								'name'                                      => $_POST['name'],
								'session'                                   => $_POST['session'],
								'section'                                   => $_POST['section'],
								'class'                                   	=> $stclass,
								'create_by_userid'							=> $create_by_userid,
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
					redirect($FileName);
					$stat['success']="Submited Successfully";
//redirect($FileName); 
					unset($_POST);
					 
				}
			else {
					$stat['error'] = $validate->errors();
				}
			}
			
elseif(isset($_POST['personal_info']))
	{                
				//$validate->addRule($_POST['class'],'','class',true);
				$validate->addRule($_POST['firstname'],'','First Name',true);
				$validate->addRule($_POST['gender'],'','gender',true);
				
			    if($validate->validate() && count($stat)==0)
				  {
					  if($getpersonalinfo['id']==''){
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
								'userid'  			              => $_SESSION['userid'],
								'randomid'  			              => $_GET['randomid'],
					            );  
					$flgIn1 = $db->insertAry("app_personalinfo",$aryData);
						
					$_SESSION['pform_id']=$flgIn1;
					  }
					  elseif($getpersonalinfo['id']!=''){
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
								'userid'  			              => $_SESSION['userid'],
								'randomid'  			              => $_GET['randomid'],
					            );  
					$flgIn1 = $db->updateAry("app_personalinfo",$aryData,"where randomid='".$_GET['randomid']."'");
					
					//$_SESSION['pform_id']=$flgIn1;
					  }
			
					//unset($_POST);
					//redirect($FileName.'?action=academics&randomid='.$currentApp['randomid']);
				    }
			else {
				    
					$stat['error'] = $validate->errors();
				 }
}




elseif(isset($_POST['guardian_info']))
{
	foreach($_POST['guardian_type']as $key=>$val)
	{
		print_r($_POST);
		$aryData12=array(
							'app_id'                                  => $currentApp['id'],
							'form_id'                           	  => $_POST['form_id'],
							'randomid'                                => $_GET['randomid'][$key],
							'guardian_type'                           => $_POST['guardian_type'][$key],
							'gender'                                  => $_POST['g_gender'][$key],
							'nationality'                             => $_POST['g_nationality'][$key],
							'stateOrigin'                             => $_POST['g_stateorigion'][$key],     
							'localGovt'                               => $_POST['g_localArea'][$key],
							'fname'                                   => $_POST['g_fname'][$key],
							'lname'                                   => $_POST['g_lname'][$key],
							'phone'                                   => $_POST['g_phone'][$key],
							'email'                                   => $_POST['g_email'][$key],
							'occupation'                              => $_POST['g_occupation'][$key],
							'contact_address'                         => $_POST['g_contAdd'][$key],
							'home_address'                            => $_POST['g_homeAdd'][$key],
							'userid'                                             => $_SESSION['userid'],
						); 
					         $flgIn13 = $db->insertAry("app_guardianInfo",$aryData12);
							 
							// echo  $flgIn13 = $db->getLastQuery();
							 //redirect($FileName.'?action=other_information&randomid='.$currentApp['randomid']);
	}
}
elseif(isset($_POST['editbtn']))
		{ 
		
		$validate->addRule($_POST['name'],'','Name',true);
		$validate->addRule($_POST['session'],'',' Session',true);
		$validate->addRule($_POST['section'],'','Section',true);
		$validate->addRule($_POST['fee'],'','Fee',true);
		if(empty($_POST['payment'])){
		$validate->addRule($_POST['payment'],'','Payment Method',true);}
		$validate->addRule($_POST['form_prefix'],'',' Form Pre',true);
		$validate->addRule($_POST['form_no_range'],'','Form Range',true);
		
		$pay=$_POST['payment'];		
		$payment=implode(",",$pay);
		 $class=$_POST['selectclass'];		
		 $stclass=implode(",",$class);
		
		if($validate->validate() && count($stat)==0)
		{ 
 			
					$aryData=array(	
					
							'name'                                      => $_POST['name'],
							'session'                                   => $_POST['session'],
							'section'                                   => $_POST['section'],
							'class'                                     => $stclass,
							'subject'                                   => $_POST['subject'],
							'start_date'                                => $_POST['start_date'],
							'end_date'                                  => $_POST['end_date'],     
							'fee'                                       => $_POST['fee'],
							'guardian'                                  => $_POST['guardian'],
							'no_of_guardian'                            => $_POST['no_of_guardian'],
							'payment_method'                            => $payment,	
							'form_prefix'  			                    => $_POST['form_prefix'],	
							'form_no_range'  			                => $_POST['form_no_range'],	
							'status'  			                        => 0,
                             'userid'                                    => $_SESSION['userid'],							
						); 
					$flgIn = $db->updateAry("application_form", $aryData , "where randomid='".$_GET['randomid']."' ");
					$_SESSION['success']="Update Successfully";
				//  echo	$flgIn = $db->getLastQuery();exit;
			 
					redirect($FileName);
 			 	
		}	  
			else {
				$stat['error'] = $validate->errors();
			}
		}
			
elseif(isset($_POST['settingssubmit']))
 	{
$iAlreadyRegister=$db->getVal("select id from app_settings where app_id='".$getDetails['id']."' ");
		   
	if($iAlreadyRegister=='')
			{
				$arrayData=array(
								'app_id'                                      => $getDetails['id'],
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
			$flgI = $db->insertAry("app_settings", $arrayData );
				
			}
		else {
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
		}
								
				
					$stat['success']="Update Successfully";
				//echo	$flgI = $db->getLastQuery();exit;
					redirect('application.php');
	}
	
		
elseif(isset($_POST['academy_history']))
{  
	//echo "jdhjd";
  if($_POST['schoolName']!='') 
				{
					$aryData1=array(	
							'app_id'                                  => $currentApp['id'],
							'randomid'                                => $_GET['randomid'],
							'userid'                                  => $_SESSION['userid'],
							'schoolName'                              => $_POST['schoolName'],
							'schoolAddress'                           => $_POST['schoolAddress'],
							'fromDate'                                => $_POST['fromDate'],
							'toDate'                                  => $_POST['toDate'],     
							'attClass'                                => $_POST['attClass'],
							'leaveReason'                             => $_POST['leaveReason'],
						); 
					$flgIn124 = $db->insertAry("app_acadamic",$aryData1);
					//$_SESSION['aform_id']=$flgIn12;
			//	echo	$flgIn124 = $db->getLastQuery();exit;
					redirect($FileName.'?action=gurdian&randomid='.$currentApp['randomid']);
			    }
 

			
		 
}
		
elseif(isset($_POST['edit_academy_history']))
{  

  if($_POST['schoolName']!='' && $getachademic['id']!="")
				{
					$aryData12=array(	
							'app_id'                                  => $currentApp['id'],
							'randomid'                                => $_GET['randomid'],
							'userid'                                  => $_SESSION['userid'],
							'schoolName'                              => $_POST['editschoolName'],
							'schoolAddress'                           => $_POST['editschoolAddress'],
							'fromDate'                                => $_POST['editfromDate'],
							'toDate'                                  => $_POST['edittoDate'],     
							'attClass'                                => $_POST['editattClass'],
							'leaveReason'                             => $_POST['editleaveReason'],
						); 
					$flgIn124 = $db->updateAry("app_acadamic", $aryData12 , "where randomid='".$_GET['randomid']."'");
					//$_SESSION['aform_id']=$flgIn12;
					// echo $flgIn12 = $db->getLastQuery();exit;
					//redirect($FileName.'?action=gurdian&randomid='.$currentApp['randomid']);
			    }
			 
			
		else
	{
		$stat['error'] = $validate->errors();
	}
}
		  elseif(($_REQUEST['action']=='delete'))
		{
			$flgIn1 = $db->delete("application_form","where randomid='".$_GET['randomid']."'");			
			$_SESSION['success'] = 'Deleted Successfully';
			redirect($FileName);
		} 
		
		 elseif(($_REQUEST['action']=='deleteachademics'))
		{
			$flgIn23 = $db->delete("app_acadamic","where randomid='".$_GET['randomid']."'");			
	//	echo 	$flgIn23 = $db->getLastQuery();exit;
			
			$_SESSION['success'] = 'Deleted Successfully';
			redirect($FileName);
		} 
?>
<!DOCTYPE html>
<html>
<head>
<?php include('inc.meta.php'); ?>
<style>
a.btn.btn-primary.center-block {
	width: 225px;
}
</style>
</head>
<body class="fixed-left">
<div id="wrapper">
<?php include('inc.header.php'); ?>
<?php include('inc.sideleft.php');
if($iPackageJsoneDecodeAllowFile['create_custom_forms']!='1') {
	redirect(SKOOL_URL);
}
?>
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
      <div class="gokul"> <a href="<?php echo $FileName; ?>?action=add"  class="btn btn-default" style="float:right">Add New Record <i class="fa fa-plus" aria-hidden="true"></i></a> </div>
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
        <div class="apli"> <span class="romte">Enter Application Form Details. </span> </div>
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
                          $isession=$db->getRows("select * from school_session where create_by_userid	='".$create_by_userid."'");
						  foreach($isession as $list)
                              {
                               ?>
                <option value="<?php echo $list['id'];?>"<?php if($_POST['session']==$list['id']){echo "selected";}?>> <?php echo $list['session'];?></option>
                <?php } ?>
              </select>
            </div>
            <div class="col-md-4 selson">
              <label class="active" for="gwt-uid-16">Select Section</label>
              <select class="volsab" id="selectsection" name="section"  onchange="classchkbox();">
                <?php
                          $section=$db->getRows("select * from school_section where create_by_userid ='".$create_by_userid."'");
						  foreach($section as $list)
                              {
                               ?>
                <option value="<?php echo $list['id'];?>"<?php if($_POST['section']==$list['id']){echo "selected";}?>>
                <?php if($list['section']!='OTHERS') { echo $list['section'];}
						elseif($list['section']=='OTHERS') { echo $list['short_name'];}?>
                </option>
                <?php } ?>
              </select>
              </select>
            </div>
            <div class="col-md-4 selson"> <span id="selectclass" class="gwt-CheckBox" style="display: block;">
              <label  for="gwt-uid-9"></label>
              </span> </div>
            <div class="col-md-2 selson"> <span id="thissubject" class="gwt-CheckBox" style="display: block;">
              <label  for="gwt-uid-9"></label>
              </span> </div>
          </div>
        </div>
      </div>
      <div class="form-group clearfix plims ">
        <div class="col-lg-12">
          <div class="row">
            <div class="col-md-4">
              <input autocomplete="off" name="start_date" id="datepicker" class="form-control datepicker" placeholder="Application start date" type="text">
            </div>
            <div class="col-md-4">
              <input autocomplete="off" name="end_date" id="datepicker1" class="form-control datepicker" placeholder="Application end date" type="text">
            </div>
             <?php if($iPackageJsoneDecodeAllowFile['online_and_bank_payment']=='1') { ?>
            <div class="col-md-4">
              <input autocomplete="off"  name="fee"class="form-control" placeholder="Application Fee" type="text">
            </div>
             <?php } ?>
          </div>
        </div>
      </div>
      
      
      
      
      
      <?php if($iPackageJsoneDecodeAllowFile['online_and_bank_payment']=='1') { ?>
      <div class="form-group clearfix gardin">
        <div class="col-lg-12">
          <div class="row">
            <div class="col-md-5"> <span class="gwt-CheckBox" style="display: block;">
              <input type="checkbox" value="1" name="guardian" onclick="getguardian();" id="gwt-uid-guardian" tabindex="0">
              <label for="gwt-uid-guardian">Require Guardian</label>
              </span><br>
            </div>
            <div class="col-md-7"> <span class="green-text material-label">Select Payment Methods</span>
              <div class="dptim"><span class="gwt-CheckBox" style="display: block;"> 
                <!--<input type="checkbox" name="payment[]" value="1" <?php if(in_array("1",$_POST['payment'])){echo "checked";} ?>id="gwt-uid-9" tabindex="0"  >
                <label for="gwt-uid-9">Scratch Card</label>--> 
                </span> <span class="gwt-CheckBox" style="display: block;">
                <input type="radio"  value="2" <?php if(in_array("2",$_POST['payment'])){echo "checked";} ?>   name="payment[]" id="gwt-uid-10" tabindex="0" >
                <label for="gwt-uid-10">Online</label>
                </span> <span class="gwt-CheckBox gwt-CheckBox-disabled" style="display: block;">
                <input type="radio" value="0" <?php if(in_array("0",$_POST['payment'])){echo "checked";} ?> name="payment[]"  id="gwt-uid-11" tabindex="0"   checked>
                <label for="gwt-uid-11">No Payment</label>
                </span> </div>
            </div>
          </div>
        </div>
      </div>
      <?php } ?>
      
      
      
      <div class="form-group clearfix plims dokra" style="display:none;" id="guardianno">
        <div class="col-lg-12">
          <div class="row pots">
            <div class="col-md-5">
              <label class="active" for="gwt-uid-16">Select Max No. of guardian </label>
              <select class="volsab"  name="no_of_guardian">
                <option value="1" >1</option>
                <option value="2">2</option>
                <option value="3">3</option>
                <option value="4">4</option>
                <option value="5">5</option>
              </select>
            </div>
            <div class="col-md-7"> </div>
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
                <span aria-hidden="true" class="material-label" style="display: none;"></span> </div>
            </div>
            <div class="col-md-5">
              <div class="input-field">
                <label class="active" for="gwt-uid-16">Form No Range</label>
                <input type="text"   name="form_no_range" class="gwt-TextBox" placeholder="0000" id="gwt-uid-16">
                <span aria-hidden="true" class="material-label" style="display: none;"></span> </div>
            </div>
          </div>
        </div>
      </div>
      <div class="form-group clearfix bfrcs ">
        <div class="col-lg-12 sgot">
          <div class="row">
            <div class="savdtls">
              <button type="submit" name="submit" class="btn btn-default">Save details</button>
            </div>
          </div>
        </div>
      </div>
      </section>
    </div>
  </form>
</div>
<?php } 
elseif($_GET['action']=='settings') { 
			 ?>
<?php $getsetting=$db->getRow("select * from  app_settings where app_id='".$getDetails['id']."' "); ?>
<div class="card-box">
  <form action="" method="post">
    <div class="row">
      <div class="col-md-12">
        <div class="rmtig"> <span>Application form setting.</span> </div>
        <div class="row dimsn">
          <div class="col-md-12">
            <div class="nbleds"> <span class="material-label">Enable admission requirement</span>
              <div class="divaq">
                <label class="switch">
                  <input type="checkbox" name="admission_req" value="1" <?php if($getsetting['admission_req']=='1'){echo "checked"; }?>>
                  <span class="slider round"></span> </label>
              </div>
            </div>
          </div>
          <div class="col-md-12">
            <div class="nbleds"> <span class="material-label">Require and design additional questions.</span>
              <div class="divaq">
                <label class="switch">
                  <input type="checkbox" name="additional_quiz"value="1"<?php if($getsetting['design_additional_qeus']=='1'){echo "checked"; }?>>
                  <span class="slider round"></span> </label>
              </div>
            </div>
          </div>
          <div class="col-md-12">
            <div class="nbleds"> <span class="material-label">Require academic history.</span>
              <div class="divaq">
                <label class="switch">
                  <input type="checkbox" name="academic_history"value="1"<?php if($getsetting['academic_history']=='1'){echo "checked"; }?>>
                  <span class="slider round"></span> </label>
              </div>
            </div>
          </div>
          <hr>
          <div class="col-md-12">
            <div class="nbleds"> <span class="material-label">Require next of kin from applicant.</span>
              <div class="divaq">
                <label class="switch">
                  <input type="checkbox"name="kin_applicant"value="1"<?php if($getsetting['kin_from_appl']=='1'){echo "checked"; }?>>
                  <span class="slider round"></span> </label>
              </div>
            </div>
          </div>
          <div class="col-md-12">
            <div class="nbleds"> <span class="material-label">Require remarks field in basic info form</span>
              <div class="divaq">
                <label class="switch">
                  <input type="checkbox"name="info_form"value="1"<?php if($getsetting['remarks_field']=='1'){echo "checked"; }?>>
                  <span class="slider round"></span> </label>
              </div>
            </div>
          </div>
          <div class="col-md-12">
            <div class="nbleds"> <span class="material-label">Require applicant city or address</span>
              <div class="divaq">
                <label class="switch">
                  <input type="checkbox"name="city_address"value="1"<?php if($getsetting['city_address']=='1'){echo "checked"; }?>>
                  <span class="slider round"></span> </label>
              </div>
            </div>
          </div>
          <div class="col-md-12">
            <div class="nbleds"> <span class="material-label">Require applicant place of birth</span>
              <div class="divaq">
                <label class="switch">
                  <input type="checkbox"name="birth_place"value="1"<?php if($getsetting['place_of_birth']=='1'){echo "checked"; }?>>
                  <span class="slider round"></span> </label>
              </div>
            </div>
          </div>
          <div class="col-md-12">
            <div class="nbleds"> <span class="material-label">Require applicant religion.</span>
              <div class="divaq">
                <label class="switch">
                  <input type="checkbox"name="religion"value="1"<?php if($getsetting['religion']=='1'){echo "checked"; }?>>
                  <span class="slider round"></span> </label>
              </div>
            </div>
          </div>
          <div class="col-md-12">
            <div class="nbleds"> <span class="material-label">Require applicant tribe.</span>
              <div class="divaq">
                <label class="switch">
                  <input type="checkbox"name="tribe"value="1"<?php if($getsetting['tribe']=='1'){echo "checked"; }?>>
                  <span class="slider round"></span> </label>
              </div>
            </div>
          </div>
          <div class="col-md-12">
            <div class="nbleds"> <span class="material-label">Require applicant state of origin and local government</span>
              <div class="divaq">
                <label class="switch">
                  <input type="checkbox"name="state"value="1"<?php if($getsetting['state_local']=='1'){echo "checked"; }?>>
                  <span class="slider round"></span> </label>
              </div>
            </div>
          </div>
          <div class="col-md-12">
            <div class="nbleds"> <span class="material-label">Require second nationality from applicant</span>
              <div class="divaq">
                <label class="switch">
                  <input type="checkbox"name="second_nationality"value="1"<?php if($getsetting['second_nationality']=='1'){echo "checked"; }?>>
                  <span class="slider round"></span> </label>
              </div>
            </div>
          </div>
          <div class="col-md-12">
            <div class="nbleds"> <span class="material-label">Allow applicant to choose boarding type</span>
              <div class="divaq">
                <label class="switch">
                  <input type="checkbox"name="boarding" value="1"<?php if($getsetting['boarding_type']=='1'){echo "checked"; }?>>
                  <span class="slider round"></span> </label>
              </div>
            </div>
          </div>
          <div class="col-md-12">
            <div class="nbleds">
              <div class="row">
                <div class="col-md-9"> </div>
                <div class="col-md-3">
                  <p class="svet ">
                    <button  color="white;"type="submit" name="settingssubmit" >SAVE SETTINGS</button>
                  </p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </form>
</div>
<!-----PREVIEW FORM------> 
<!-----PREVIEW FORM------> 

<!-----PREVIEW FORM------> 
<!-----PREVIEW FORM------>
<?php } 
elseif($_GET['action']=="previewform"){ ?>
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
<form action="" method="post">
<div class="row">
<div class="col-md-12">
<div class="row mrita">
  <div class="col-md-12 nacpo">
    <button type="submit" name="personal_info" class="btn btn-default   pull-right">Next Form <i class="fa fa-arrow-right" aria-hidden="true"></i></button>
  </div>
</div>
<div class="row">
<div class="col-md-12">
<div class="tainerd">
<input id="tab1" type="radio" name="tabs" checked style="display:none;">
<label class="adifo" for="tab1">
<div class="prls">
  <div class="circle">1</div>
  <div class="title">Personal</div>
  <div class="line"></div>
  <div></div>
</div>
</label>
<?php 	$iGetDet=$db->getRow("select * from  application_form where randomid='".$_GET['randomid']."'");	?>
<input id="tab2" type="radio" name="tabs" style="display:none;">
<label class="adifo" for="tab2">
<div class="prls">
  <div class="circle">2</div>
  <div class="title">Academics</div>
  <div class="line"></div>
  <div></div>
</div>
</label>
<?php if($iGetDet['guardian']=="1"){?>
<input id="tab3" type="radio" name="tabs" style="display:none;">
<label class="adifo" for="tab3">
<div class="prls">
  <div class="circle">3</div>
  <div class="title">Guardian</div>
  <div class="line"></div>
  <div></div>
</div>
</label>
<?php } ?>
<input id="tab4" type="radio" name="tabs" style="display:none;">
<label class="adifo" for="tab4">
<div class="prls">
  <div class="circle">4</div>
  <div class="title">Additional info</div>
  <div></div>
</div>
</label>
<?php          $getDetails=$db->getRow("select * from  application_form where randomid='".$_GET['randomid']."'");
			   $getsession=$db->getRow("select * from  	school_session where id='".$getDetails['session']."'");
			   $getsection=$db->getRow("select * from  	school_section where id='".$getDetails['section']."'"); ?>
<section id="content1" class="tab-content">
  <div class="row">
    <?php if($currentForm['boarding_type']=='1'){?>
    <div class="col-md-4 atharo" name="bording">
      <label class="active" for="gwt-uid-16">Boarding Type</label>
      <select class="osama" name="bording">
        <option value="1"<?php if($getpersonalinfo['bording']=='1'){ echo "selected";}?>>Day</option>
        <option value="2"<?php if($getpersonalinfo['bording']=='2'){ echo "selected";}?>>Bording</option>
        <option value="3"<?php if($getpersonalinfo['bording']=='3'){ echo "selected";}?>>Both</option>
      </select>
    </div>
    <?php } ?>
    <div class="col-md-8 gappul">
      <div class="row aqunt">
        <div class="col-md-4 shemus">
          <input autocomplete="off" class="form-control"   name="session" value="<?php echo $getsession['session']; ?>" readonly placeholder="School Session" type="text">
        </div>
        <div class="col-md-4 shemus ">
          <input autocomplete="off" class="form-control"  readonly="readonly" name="section" value="<?php echo $getsection['section']; ?>" placeholder="School Section" type="text">
        </div>
        <div class="col-md-4">
          <label class="active" for="gwt-uid-16">Class to Apply</label>
          <select class="adda">
            <?php 
						$form_class=$getDetails['class'];
						$fclass=explode(",",$form_class);
						foreach($fclass as $listClass){
						$getClass=$db->getRows("select * from   school_class where id='".$listClass."'");
						foreach($getClass as $iclass){
						?>
            <option value="<?php echo $iclass['id'];?>"<?php if($getpersonalinfo['class']==$iclass['id']){ echo "selected";}?>><?php echo $iclass['name'];?></option>
            <?php } } ?>
          </select>
        </div>
      </div>
      <div class="row  usmant">
        <div class="col-md-4">
          <input autocomplete="off" class="form-control" name="firstname" value="<?php echo $getpersonalinfo['firstname'];?>" placeholder="First Name" type="text">
        </div>
        <div class="col-md-4 ">
          <input autocomplete="off" class="form-control"  name="lastname" value="<?php echo $getpersonalinfo['lastname'];?>" placeholder="Last Name" type="text">
        </div>
        <div class="col-md-4">
          <input autocomplete="off" class="form-control" placeholder="Other Names" name="othername" value="<?php echo $getpersonalinfo['othername'];?>" type="text">
        </div>
      </div>
      <div class="row">
        <div class="col-md-8"> </div>
        <div class="col-md-3 ">
          <input autocomplete="off" class="form-control" placeholder="Date of Birth" name="dob" value="<?php echo $getpersonalinfo['dob']; ?>" type="text">
        </div>
        <div class="col-md-1 "> </div>
      </div>
      <div class="row bsukna">
        <?php if($currentForm['religion']=='1'){ ?>
        <div class="col-md-3 ">
          <label class="active" for="gwt-uid-16">Religion</label>
          <select class="adda"  name="religion">
            <option value="">Select Religion</option>
            <option value="Hindu" <?php if($getpersonalinfo['religion']=="Hindu"){ echo "selected"; }?>>Hindu</option>
            <option value="Muslim" <?php if($getpersonalinfo['religion']=="Muslim"){ echo "selected"; }?>>Muslim</option>
            <option value="Sikh" <?php if($getpersonalinfo['religion']=="Sikh"){ echo "selected"; }?>>Sikh</option>
            <option value="Other" <?php if($getpersonalinfo['religion']=="Other"){ echo "selected"; }?>>Other</option>
          </select>
        </div>
        <?php } ?>
        <div class="col-md-9 ">
          <div class="kaalin"> <span class="material-label">Gender</span>
            <div class="ortand">
              <label>
                <input type="radio" name="gender" value="male"<?php if($getpersonalinfo['gender']=='male'){ echo "checked"; }?> class="radio-inline"   >
                <span class="outside"><span class="inside"></span></span>Male</label>
              <label>
                <input type="radio"class="radio-inline"  name="gender" value="female"<?php if($getpersonalinfo['gender']=='female'){ echo "checked"; }?>>
                <span class="outside"><span class="inside"></span></span>Female</label>
            </div>
          </div>
        </div>
      </div>
      <div class="row radspl">
        <div class="col-md-12 ">
          <label class="active" for="gwt-uid-16">Nationality</label>
          <select class="sonpit" name="nationality">
            <?php
						$getcontry=$db->getRows("select * from  country order by country_name asc");
						foreach($getcontry as $ilist){
						
						?>
            <option value="<?php echo $ilist['id'];?>"<?php if($getpersonalinfo['nationality_id']==$ilist['id']){ echo "selected"; }?>><?php echo $ilist['country_name'];?></option>
            <?php }?>
          </select>
        </div>
      </div>
      <?php if($currentForm['second_nationality']=='1'){ ?>
      <div class="row dhst">
        <div class="col-md-7 ">
          <label class="active" for="gwt-uid-16">Nationatlity Two</label>
          <select class="sonpit" name="nationalitytwo">
            <option>Select Nationatlity</option>
            <?php
						$getcontry=$db->getRows("select * from  country order by country_name asc");
						foreach($getcontry as $ilist){
						
						?>
            <option value="<?php echo $ilist['id'];?>"<?php if($getpersonalinfo['nationalitytwo_id']==$ilist['id']){ echo "selected"; }?>> <?php echo $ilist['country_name'];?></option>
            <?php }?>
          </select>
        </div>
        <div class="col-md-5 "> </div>
      </div>
      <?php  } ?>
      <div class="row wllst">
        <?php if($currentForm['state_local']=='1'){?>
        <div class="col-md-4 ">
          <label class="active" for="gwt-uid-16">State Of Origin</label>
          <select class="adda" name="stateorigine">
            <?php
						$getcontry=$db->getRows("select * from  local_government order by title asc");
						foreach($getcontry as $ilist){
						
						?>
            <option value="<?php echo $ilist['id'];?>"<?php if($getpersonalinfo['stateorigine']==$ilist['id']){ echo "selected"; }?>><?php echo $ilist['title'];?></option>
            <?php }?>
          </select>
        </div>
        <?php } ?>
        <div class="col-md-4">
          <label class="active" for="gwt-uid-16">Local Government Area</label>
          <select class="adda" name="local_state">
            <option>Select Local Goverment</option>
            <?php
						$getstate=$db->getRows("select * from  local_government order by title asc");
						foreach($getstate as $ilist){
						
						?>
            <option value="<?php echo $ilist['id'];?>"<?php if($getpersonalinfo['local_state']==$ilist['id']){ echo "selected"; }?>> <?php echo $ilist['title'];?></option>
            <?php }?>
          </select>
        </div>
        <div class="col-md-4 joncena">
          <input autocomplete="off" class="form-control" name="child_mail"  placeholder="Child email address" value="<?php echo $getpersonalinfo['child_mail']; ?>" type="text">
        </div>
      </div>
      <div class="row wllst">
        <?php if($currentForm['tribe']=='1'){?>
        <div class="col-md-3">
          <input autocomplete="off" class="form-control" name="tribechild" placeholder="Tribe of child." value="<?php echo $getpersonalinfo['tribechild']; ?>"  type="text">
        </div>
        <?php } ?>
        <?php if($currentForm['place_of_birth']=='1'){?>
        <div class="col-md-3 tchaild ">
          <input autocomplete="off" class="form-control" name="birth_place" placeholder="Place of Birth" value="<?php echo $getpersonalinfo['birth_place']; ?>" type="text">
        </div>
        <?php } ?>
        <div class="col-md-3"> </div>
      </div>
      <div class="row wllst">
        <?php if($currentForm['city_address']=='1'){ ?>
        <div class="col-md-12 tchaild">
          <input autocomplete="off" class="form-control"   name="child_address" placeholder="Address of child" value="<?php echo $getpersonalinfo['child_address']; ?>" type="text">
        </div>
        <?php } ?>
      </div>
      <?php if($currentForm['kin_from_appl']=='1'){ ?>
      <div class="row wllst">
        <div class="col-md-4 tchaild">
          <input autocomplete="off" class="form-control" name="nextKin"  placeholder="Next Of Kin"  value="<?php echo $getpersonalinfo['nextKin']; ?>" type="text">
        </div>
        <div class="col-md-4 tchaild">
          <input autocomplete="off" class="form-control" name="relationOfKin" value="<?php echo $getpersonalinfo['relationOfKin']; ?>" placeholder="Relationship With Next of Kin" type="text">
        </div>
        <div class="col-md-4 tchaild">
          <input autocomplete="off" class="form-control"  name="kinPhone"  value="<?php echo $getpersonalinfo['kinPhone']; ?>" placeholder="Next of Kin Phone No." type="text">
        </div>
      </div>
      <?php } ?>
      <div class="row wllst">
        <div class="col-md-12 tchaild">
          <input autocomplete="off" class="form-control" name="hobbies" placeholder="Hobbies" value="<?php echo $getpersonalinfo['hobbies']; ?>" type="text">
        </div>
      </div>
      <?php if($currentForm['remarks_field']=='1'){?>
      <div class="row wllst">
        <div class="col-md-12">
          <textarea autocomplete="off" class="form-control" name="childOtherInfo" placeholder="Other information about child." type="text"> <?php echo $getpersonalinfo['childOtherInfo']; ?></textarea>
        </div>
      </div>
      <?php } ?>
    </div>
  </div>
</section>


<!---- ACHADEMIC HISTORY--><!---- ACHADEMIC HISTORY--><!---- ACHADEMIC HISTORY--><!---- ACHADEMIC HISTORY-->
<!---- ACHADEMIC HISTORY--><!---- ACHADEMIC HISTORY--><!---- ACHADEMIC HISTORY--><!---- ACHADEMIC HISTORY-->

 <?php if($currentForm['academic_history']=='1'){ ?>
	<section id="content2" class="tab-content">
		<div class="donalduck">
             <div class="row">
           <div class="col-md-12">
			<div class="uktam"><h3>Previous Academic History</h3></div>	
			</div>
            </div>	
			 <div class="row">
           <div class="col-md-12">
			<div class="center ninjhtodi"><a data-toggle="modal" data-target="#squarespaceModal" class="btn btn-primary center-block"><i class="fa fa-plus" aria-hidden="true"></i> Add School Attended</a></div>


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
              <input autocomplete="off" class="form-control" placeholder="School Name" id="schoolName" name="schoolName" value="<?php echo $_POST['schoolName']; ?>" type="text">
              </div>
              <div class="form-group">
                <input autocomplete="off" class="form-control" id="schoolAddress" name="schoolAddress" value="<?php echo $_POST['schoolAddress']; ?>" placeholder="Address" type="text">
              </div>
			  <div class="form-group">
			  <div class="row">
			  <div class="col-md-6">
             <input autocomplete="off" class="form-control" id="datepickerachademics" name="fromDate" value="<?php echo $_POST['fromDate']; ?>" placeholder="From Date" type="text">
              </div>
			 <div class="col-md-6">
             <input autocomplete="off" class="form-control" id="datepickerachademics2" placeholder="To Date"  name="toDate" value="<?php echo $_POST['toDate']; ?>" type="text">
              </div>
			  </div>
			  </div>
              <div class="form-group">
             <input autocomplete="off" class="form-control" placeholder="Classes Attended" name="attClass" value="<?php echo $_POST['attClass']; ?>"type="text">
              </div>
             <div class="form-group">
             <input autocomplete="off" class="form-control" placeholder="Reason For Leaving"name="leaveReason"  value="<?php echo $_POST['leaveReason']; ?>" type="text">
              </div>
			   <div class="row">
			    
		   <div class="col-md-12">
			  <div class="pull-right" role="group">
							  <span class=" nogel"><button   class="btn btn-default  ">Cancel</button></span>	
		  			<span class="skoti">
		  <button type="submit"  name="academy_history" class="btn btn-default">Save</button>	
				</div>
				</div>
            </form>

		</div>
		
	         </div>
            </div>
             </div>
            </div>
			
		<?php	
		$getachademic=$db->getRow("select * from  app_acadamic where randomid='".$_GET['randomid']."'");
		?>
		
		
			<div class="modal fade" id="editspaceModal" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
  <div class="modal-dialog">
	<div class="modal-content">
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">×</span><span class="sr-only">Close</span></button>
			<h3 class="modal-title pull-left" id="lineModalLabel">Edit School Attended Details</h3>
		</div>
		<div class="modal-body">
			
            <!-- content goes here -->
			<form>
              <div class="form-group">
              <input autocomplete="off" class="form-control" placeholder="School Name" id="editschoolName" name="editschoolName" value="<?php echo $getachademic['schoolName']; ?>" type="text">
              </div>
              <div class="form-group">
                <input autocomplete="off" class="form-control" id="editschoolAddress" name="editschoolAddress" value="<?php echo $getachademic['schoolAddress']; ?>" placeholder="Address" type="text">
              </div>
			  <div class="form-group">
			  <div class="row">
			  <div class="col-md-6">
             <input autocomplete="off" class="form-control" id="datepickeredit" name="editfromDate" value="<?php echo $getachademic['fromDate']; ?>" placeholder="From Date" type="text">
              </div>
			 <div class="col-md-6">
             <input autocomplete="off" class="form-control" id="datepickeredit2" placeholder="To Date"  name="edittoDate" value="<?php echo $getachademic['toDate']; ?>" type="text">
              </div>
			  </div>
			  </div>
              <div class="form-group">
             <input autocomplete="off" class="form-control" placeholder="Classes Attended" name="editattClass" value="<?php echo $getachademic['attClass']; ?>"type="text">
              </div>
             <div class="form-group">
             <input autocomplete="off" class="form-control" placeholder="Reason For Leaving"name="editleaveReason"  value="<?php echo $getachademic['leaveReason']; ?>" type="text">
              </div>
			   <div class="row">
			    
		   <div class="col-md-12">
			  <div class="pull-right" role="group">
							  <span class=" nogel"><button   class="btn btn-default  ">Cancel</button></span>	
		  			<span class="skoti">
		  <button type="submit"  name="edit_academy_history" class="btn btn-default">Update</button>	
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
		  <span class=" nogel"><a class="btn btn-default"  data-toggle="modal" data-target="#editspaceModal"><i class="fa fa-clock-o" aria-hidden="true"></i>Edit</a></span>	
		  			<span class="skoti">
		  <button class="btn btn-default"><a href="javascript:del('<?php echo $FileName; ?>?action=deleteachademics&randomid=<?php echo $_GET['randomid']; ?>')"class="table-action-btn" ><i class="fa fa-trash-o" aria-hidden="true"></i> </a>Delete</button>	
			</span>
			</div>

			<div class="col-md-9">
		  
			</div>
            </div>	

			<div class="row loknth">
			<?php if($getachademic['id']==""){ ?>
           <div class="col-md-12">
		   <div class="cdm">
		   <img src="../image/cdm.png" alt="Smiley face">	
			</div>
			<div class="ystm">
			<span >No items on the list yet</span>
			</div>
			</div>
		<?php } elseif($getachademic['id']!=""){  ?>
			
			
			 <div class="col-md-12">
			 	  <div class="card-box">
            <table id="datatable" class="table table-striped table-bordered">
              <thead>
                <tr>			
                  <th>#</th>			
                  <th>School name</th>
				  <th>School Address</th>
				  <th>Start Date</th>
				  <th>End Date</th>
				  <th>Class Attended</th>
				  <th>Leave Reason</th>
				  <th>Action</th>
                </tr>
              </thead>
              <tbody>
			   
				<?php
				$i=0;
				$aryList=$db->getRows("select * from app_acadamic  where randomid='".$_GET['randomid']."'");
				foreach($aryList as $iList)
				{	
				$i++;
				 
				?>
                <tr>
				<td><?php echo $i ?></td>
				<td><?php echo $iList['schoolName']?></td>
				<td><?php echo $iList['schoolAddress']; ?></td>
				<td><?php echo $iList['fromDate']; ?></td>
				<td><?php echo $iList['toDate']; ?></td>
				<td><?php echo $iList['attClass']; ?></td>
				<td><?php echo $iList['leaveReason']; ?></td>
				   	
                  <td>
				  <a href="<?php echo $FileName; ?>?action=view&randomid=<?php echo $iList['randomid']; ?>" class="table-action-btn">
				  <i class="fa fa-search"></i>
				  </a> 
                  <a href="<?php echo $FileName; ?>?action=edit&randomid=<?php echo $iList['randomid']; ?>"  class="table-action-btn" >
				  <i class="fa fa-pencil"></i> </a> 
                  <a href="javascript:del('<?php echo $FileName; ?>?action=delete&randomid=<?php echo $iList['randomid']; ?>')"class="table-action-btn" >
				   <i class="fa fa-times"></i> </a>
				   
				    
				  </td>
                </tr>
                <?php } ?>
              </tbody>
            </table>
          </div>
			 </div>
			 
		<?php } ?> 
			 
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
		</div>
    </section>
	<?php } ?>
	
	
	
	 
			
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
		  <button type="submit"   class="btn btn-default pull-left "><i class="fa fa-chevron-left" aria-hidden="true"></i>Prev</button>	
			</div>
			<div class="col-md-6 jarrt">
		  <button type="submit"   class="btn btn-default pull-right">Next<i class="fa fa-arrow-right" aria-hidden="true"></i></button>	
			</div>
            </div>
			
			</div>
			</section>
<!---- GUARDIAN-->
<!---- GUARDIAN--><!---- GUARDIAN--><!---- GUARDIAN-->


<!---- GUARDIAN-->
<!---- GUARDIAN--><!---- GUARDIAN--><!---- GUARDIAN-->


<?php
				// if($_GET['action']=='guardian') {

                
				$iGetDet=$db->getRow("select * from  application_form where randomid='".$_GET['randomid']."'");
				$gurdian=$iGetDet['no_of_guardian'];
				?>
				
				
				
			<section id="content3" class="tab-content">
			<div class="suzika">
			<div class="row">
           <div class="col-md-12">
		   
		   <?php 
				for ($x = 1; $x <=$gurdian; $x++)
					{ ?>
			<?php
				$i=0;
				$aryList=$db->getRows("select * from app_guardianinfo  where app_id='".$iGetDet['id']."' and form_id='".$_POST['form_id']."' ");
				foreach($aryList as $iList)
						{	
				$i++;
				$iGetguardian=$db->getRow("select * from  app_guardianinfo where id='".$iList['id']."'");
				?>	
			    <div class="panel-group" id="faqAccordion<?php echo $x;?>">
        <div class="panel panel-default ">
            <div class="panel-heading accordion-toggle question-toggle collapsed" data-toggle="collapse" data-parent="#faqAccordion<?php echo $x;?>" data-target="#question0<?php echo $x;?>">
                 <h4 class="panel-title">
                    <a href="#" class="ing">Guardian <?php echo $x;?></a>
              </h4>

            </div>
			<input type="hidden" value="<?php echo $x;?>" name="form_id[]">
            <div id="question0<?php echo $x;?>" class="panel-collapse collapse" style="height: 0px;">
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
                    <select class="adda"name="guardian_type[]">
                    <option value="Father" <?php if($iGetguardian['guardian_type']="Father"){echo "selected";}?>>Father</option>
						<option value="Mother" <?php if($iGetguardian['guardian_type']="Mother"){echo "selected";}?>>Mother</option>
						<option value="Uncle" <?php if($iGetguardian['guardian_type']="Uncle"){echo "selected";}?>>Uncle</option>
						<option value="Aunt" <?php if($iGetguardian['guardian_type']="Aunt"){echo "selected";}?>>Aunt</option>
						<option value="Other" <?php if($iGetguardian['guardian_type']="Other"){echo "selected";}?>>Other</option>
                        </select>
			</div>
           <div class="col-md-6">
			<div class="kaalin">
			<span class="material-label">Gender</span>
			<div class="ortand">
			  <label><input type="radio" class="radio-inline" name="g_gender[]" <?php if($iGetguardian['gender']="male"){echo "checked";}?> value="male"><span class="outside"><span class="inside"></span></span>Male</label>
              <label><input type="radio"class="radio-inline" name="g_gender[]"  <?php if($iGetguardian['gender']="female"){echo "checked";}?> value="female"><span class="outside"><span class="inside"></span></span>Female</label>
			</div>
			</div>		
			</div>
            </div>	

			<div class="row mugambo">
			<div class="col-md-4">
			<label class="active" for="gwt-uid-16">Nationality</label>
                    <select class="adda" name="g_nationality[]" >
                    <option>Select Nationality </option>
						<?php
						$getcontry=$db->getRows("select * from  country order by country_name asc");
						foreach($getcontry as $ilist){
						
						?>
						<option value="<?php echo $ilist['id'];?>"><?php echo $ilist['country_name'];?></option>
						<?php }?>
                        </select>
			</div>
           <div class="col-md-4">
			<label class="active" for="gwt-uid-16">State of origin</label>
                    <select class="adda" name="g_stateorigion[]">
                   <?php
						$getcontry=$db->getRows("select * from  country order by country_name asc");
						foreach($getcontry as $ilist){
						
						?>
						<option value="<?php echo $ilist['id'];?>"><?php echo $ilist['country_name'];?></option>
						<?php }?>
                        </select>
			</div>
			<div class="col-md-4">
			<label class="active" for="gwt-uid-16">Local Government Area</label>
                    <select class="adda"name="g_localArea[]">
                    <?php
						$getcontry=$db->getRows("select * from  country order by country_name asc");
						foreach($getcontry as $ilist){
						
						?>
						<option value="<?php echo $ilist['id'];?>"><?php echo $ilist['country_name'];?></option>
						<?php }?>
                        </select>
			</div>
            </div>				
			
			<div class="row mugambo">
           <div class="col-md-6"> 
		   <label class="active" for="gwt-uid-16">First Name</label>
			<input autocomplete="off" class="form-control" placeholder="" value="<?php echo $iGetguardian['fname']; ?>" name="g_fname[]" type="text">		   
			</div>
           <div class="col-md-6">
		   <label class="active" for="gwt-uid-16">Last Name</label>
			<input autocomplete="off" class="form-control" placeholder="" value="<?php echo $iGetguardian['lname']; ?>" name="g_lname[]" type="text">		   
			</div>
            </div>

			<div class="row mugambo">
           <div class="col-md-6">
		    <label class="active" for="gwt-uid-16">Email Address</label>
			<input autocomplete="off" class="form-control" name="g_email[]" placeholder="" value="<?php echo $iGetguardian['email']; ?>"  type="text">		   
			</div>
           <div class="col-md-6">
		   <label class="active" for="gwt-uid-16">Phone Number( use , for multiple)</label>
			<input autocomplete="off" class="form-control" placeholder="" name="g_phone[]" value="<?php echo $iGetguardian['phone']; ?>"  type="text">		   
			</div>
            </div>
			
			<div class="row mugambo">
           <div class="col-md-12">
		   <label class="active" for="gwt-uid-16">Occupation</label>
			<input autocomplete="off" class="form-control" placeholder=""name="g_occupation[]" value="<?php echo $iGetguardian['occupation']; ?>" type="text">		   
			</div>
			</div>
			<div class="row mugambo">
           <div class="col-md-12">
		   <label class="active" for="gwt-uid-16">Contact Address</label>
			<input autocomplete="off" class="form-control" placeholder="" name="g_contAdd[]" value="<?php echo $iGetguardian['contact_address']; ?>" type="text">		   
			</div>
			</div>
			<div class="row mugambo">
           <div class="col-md-12">
		   <label class="active" for="gwt-uid-16">Home Address</label>
			<input autocomplete="off" class="form-control" placeholder="" name="g_homeAdd[]" value="<?php echo $iGetguardian['home_address']; ?>" type="text">		   
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
				<?php } ?>
				<?php  } ?>
			</div>
            </div>		
		    
			<div class="row usodenil">
           <div class="col-md-6 bndgiko">
		  <button type="submit" name="submit" class="btn btn-default pull-left "><i class="fa fa-chevron-left" aria-hidden="true"></i>Prev</button>	
			</div>
			<div class="col-md-6 jarrt">
		  <button type="submit"  name="guardian_info"  class="btn btn-default pull-right">Next<i class="fa fa-arrow-right" aria-hidden="true"></i></button>	
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
		  		<button type="submit"  class="btn btn-default pull-left"><i class="fa fa-chevron-left" aria-hidden="true"></i>Prev</button>	
			</div>
			<div class="col-md-6 bndgiko ">
		 	 	<button type="submit"   class="btn btn-default pull-right"><i class="fa fa-floppy-o" aria-hidden="true"></i>Save & Preview</button>	
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
</form>		   
		   </div>
  </div>
  
  
  
  <!--- edit-------->
    <!--- edit-------->
	  <!--- edit-------->
	    <!--- edit-------->
			<?php } elseif($_GET['action']=='editform') { 
			 $getDetails=$db->getRow("select * from  application_form where randomid='".$_GET['randomid']."'");
					?>
        
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
					 	<input autocomplete="off" name="name" class="form-control" value="<?php echo $getDetails['name']; ?>" placeholder="Application Name" type="text">
                      </div>
                    </div>
					<div class="form-group clearfix plims">
                      <div class="col-lg-12">
					  <div class="row">
					  <div class="col-md-4 selson">
					<label class="active" for="gwt-uid-16">Select Session</label>
                    <select class="volsab" name="session" >
                    <?php
                          $isession=$db->getRows("select * from school_session where create_by_userid ='".$create_by_userid."'");
						  foreach($isession as $list)
                              {
                               ?>
                        <option value="<?php echo $list['id'];?>"<?php if($getDetails['session']==$list['id']){echo "selected";}?>>
						<?php echo $list['session'];?></option>
                              <?php } ?></select>
                    </div>
					 <div class="col-md-4 selson">
					  <label class="active" for="gwt-uid-16">Select Section</label>
					 <select class="volsab" id="selectsection" name="section" onchange="classchkbox();">
					       <?php
                          $section=$db->getRows("select * from school_section where create_by_userid ='".$create_by_userid."'");
						  foreach($section as $list)
                              {
                               ?>
                        <option value="<?php echo $list['id'];?>"<?php if($getDetails['section']==$list['id']){echo "selected";}?>>
						<?php if($list['section']!='OTHERS') { echo $list['section'];}
						elseif($list['section']=='OTHERS') { echo $list['short_name'];}?></option>
						
                              <?php } ?></select>
					 
					
                    </select>
					 </div>
					 
					 <div class="col-md-4 selson">
					   <?php	 $iExploedValue = explode(',',$getDetails['class']);
                          $section=$db->getRows("select * from school_class  where section_id='".$getDetails['section']."'");
						  foreach($section as $list)
                              {
                               ?>
				 	  <span class="gwt-CheckBox" style="display: block;"><input type="checkbox" <?php if (in_array($list['id'], $iExploedValue)) { echo 'checked'; } ?> value="<?php echo $list['id'];?>" name="selectclass[]"   tabindex="0"  ><label  for="gwt-uid-9"></label>  <?php echo $list['name'];?> </span>
					 
							  <?php } ?>
					 </div>
					</div>
					</div>
                    </div>


					<div class="form-group clearfix plims ">
                      <div class="col-lg-12">
                    <div class="row">
					  <div class="col-md-4">
                    <input autocomplete="off" name="start_date" id="datepicker" value="<?php echo $getDetails['start_date']; ?>" class="form-control" placeholder="Application start date" type="text">
                    </div>
					 <div class="col-md-4">
					<input autocomplete="off" name="end_date" id="datepicker1"value="<?php echo $getDetails['end_date']; ?>" class="form-control" placeholder="Application end date" type="text">
					 </div>
					 <div class="col-md-4">
					<input autocomplete="off"  name="fee"class="form-control"value="<?php echo $getDetails['fee']; ?>" placeholder="Application Fee" type="text">
					 </div>
					</div>
                      </div>
                    </div>

					<div class="form-group clearfix gardin">
                      <div class="col-lg-12">
                       <div class="row">
					  <div class="col-md-5">
                    <span class="gwt-CheckBox" style="display: block;"><input type="checkbox"  <?php if($getDetails['guardian']=='1') { echo "checked"; } ?> value="1" name="guardian" onclick="getguardian();"  id="gwt-uid-guardian" tabindex="0">
					
					<label for="gwt-uid-guardian">Require Guardian</label></span><br>
					
                    </div>
					 <div class="col-md-7">
					<span class="green-text material-label">Select Payment Methods</span>
					<?php $pmethod= explode(",",$getDetails['payment_method']); ?>

					<div class="dptim">
                    
                  <!--  <span class="gwt-CheckBox" style="display: block;"><input type="checkbox" name="payment[]" value="1" <?php if(in_array("1",$pmethod)){echo "checked";} ?> id="gwt-uid-scratc" tabindex="0"  ><label for="gwt-uid-scratc">Scratch Card</label></span>-->
					
                    <span class="gwt-CheckBox" style="display: block;"><input type="radio"  value="2" name="payment[]" <?php if(in_array("2",$pmethod)){echo "checked";} ?>  id="gwt-uid-10" tabindex="0" ><label for="gwt-uid-10">Online</label></span>
                    
                    
					<span class="gwt-CheckBox gwt-CheckBox-disabled" style="display: block;"><input type="radio" value="0" <?php if(in_array("0",$pmethod)){echo "checked";} ?>  name="payment[]"  id="gwt-uid-11" tabindex="0"  ><label for="gwt-uid-11">No Payment</label></span>
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
                    <option value="1" <?php if($getDetails['no_of_guardian']=="1"){ echo "selected";} ?>>1</option>
                    <option value="2" <?php if($getDetails['no_of_guardian']=="2"){ echo "selected";} ?>>2</option>
					<option value="3" <?php if($getDetails['no_of_guardian']=="3"){ echo "selected";} ?>>3</option>
					<option value="4" <?php if($getDetails['no_of_guardian']=="4"){ echo "selected";} ?>>4</option>
					<option value="5" <?php if($getDetails['no_of_guardian']=="5"){ echo "selected";} ?>>5</option>
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
						<input type="text" name="form_prefix" class="gwt-TextBox" placeholder="SKL/2014/" value="<?php echo $getDetails['form_prefix']; ?>" id="gwt-uid-5">
						<span aria-hidden="true" class="material-label" style="display: none;"></span>
						</div>
						</div>
					  <div class="col-md-5">
					  <div class="input-field">
					   <label class="active" for="gwt-uid-16">Form No Range</label>
					  <input type="text"   name="form_no_range" class="gwt-TextBox" placeholder="0000" value="<?php echo $getDetails['form_no_range'];?>" id="gwt-uid-16">
					 <span aria-hidden="true" class="material-label" style="display: none;"></span>
					 </div>
					  </div>
						</div>
                      </div>
                    </div>
					<div class="form-group clearfix bfrcs ">
                      <div class="col-lg-12 sgot">
					   <div class="row">
                    <div class="savdtls"><button type="submit" name="editbtn" class="btn btn-default">Save details</button></div>
					</div>
					</div>
					</div>
                </div>
              </form>
            </div>
			
        
	 
          <?php } else { ?>
		  
		  	<?php
				$i=0;
				$aryList=$db->getRows("select * from application_form where create_by_userid = '".$create_by_userid."'  order by id desc");
				
				foreach($aryList as $iList)
				{	
				$i++;
				$getAppSetting=$db->getRow("select * from  app_settings where app_id='".$iList['id']."'");
				?>
          <div class="card-box">
		  
			
           <div class="row">
          <div class="col-md-12">
            <p class="dmo"><?php echo $iList['name']; ?></p>
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
           <a href="<?php echo $FileName; ?>?action=editform&randomid=<?php echo $iList['randomid']; ?>"> <h4 class="pesd">EDIT</h4></a>
			</div>
          <div class="col-md-2">
			<a href="<?php echo SITE_URL; ?>school/addmission/<?php echo $iList['pageurl']; ?>" target="_blank"><h4 class="pesd">PREVIEW FORM</h4></a>
			</div>
						<div class="col-md-4">

			<?php if($getAppSetting['design_additional_qeus']=='1') { ?>
			<!--<h4 class="pesd">DESIGN FORM</h4>-->
			<?php } ?>
            <a href="application-view.php?randomid=<?php echo $iList['randomid']; ?>"><h4 class="pesd">
            (<?php echo $db->getVal("select count(*) from   app_personalinfo where app_id='".$iList['id']."'");; ?>) TOTAL ENQUIRY</h4></a>
			</div>
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
			<h4 class="pesd pull-right"><a href="javascript:del('<?php echo $FileName; ?>?action=delete&randomid=<?php echo $iList['randomid']; ?>')"><i class="fa fa-trash" aria-hidden="true"></i></a></h4>
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
function classchkbox()
  {

  var sec_id= document.getElementById("selectsection").value;
 
  $.post("ajax.php",
  {
	 action:"getclasschkbox",
     sec_id:sec_id,   	 
  },
  function(data)
  { 
	  document.getElementById('selectclass').innerHTML=data;
  });
  }
  
  
  

function selectsubject()
  {

  var classid= document.getElementById("thisclass").value;
 
  $.post("ajax.php",
  {
	 action:"getsubject",
     classid:classid,   	 
  },
  function(data)
  { alert(data);
	  document.getElementById('thissubject').innerHTML=data;
  });
  }
  
  
function getguardian()

	{
	
 	
	
	if (document.getElementById('gwt-uid-guardian').checked) 
		{	
			document.getElementById("guardianno").style.display = "block";
			
		}
	else {
		
		document.getElementById("guardianno").style.display = "none";
	}
	
	
	 
	 
}
</script>


<script>
$("#checkAllDistrict").change(function () {
	$(".thisisdistrict").prop('checked', $(this).prop("checked"));
	check_getvidhan()
});
</script>

datepickeredit
<script>
  $( function() {
    $( "#datepickeredit" ).datepicker();
  } );
  </script>
  <script>
  $( function() {
    $( "#datepickeredit2" ).datepicker();
  } );
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
  <script>
  $( function() {
    $( "#datepickerachademics" ).datepicker();
  } );
  </script>
  <script>
  $( function() {
    $( "#datepickerachademics2" ).datepicker();
  } );
  </script>
<?php exit; ?>
</body>
</html>