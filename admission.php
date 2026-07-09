<?php include('config.php'); 
$validate = new Validation();
$getDetails=$db->getRow("select * from  	application_form where pageurl='".$_GET['auothid']."'");
$currentForm=$db->getRow("select * from  	app_settings where app_id='".$getDetails['id']."'");
$getsession=$db->getRow("select * from  	school_session where id='".$getDetails['session']."'");
$getsection=$db->getRow("select * from  	school_section where id='".$getDetails['section']."'");
$iTotalNumberOfGuardian=$getDetails['no_of_guardian'];

if(isset($_POST['addnewrecord']))
	{
		
		$tokenid=randomFix(30);
		
		$aryData=array(	
								'app_id'                                  => $getDetails['id'],
								'admission_fee'                           => $getDetails['fee'],
								'payment_method'                          => $getDetails['payment_method'],
								'session_id'                              => $getDetails['session'],
								'section_id'                              => $getDetails['section'],
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
								'v_transaction_id'  			          => '',
								'status'  			             		  => 0,
								'tokenid'  			              	  	  => $tokenid,
								'create_at'  			              	  	  => date("Y-m-d H:i:s"),
					            );  
			$flgIn1 = $db->insertAry("app_personalinfo",$aryData);
				
				
			$iLastInsertId = $flgIn1;	
			
			
			foreach($_POST['schoolName'] as $keyNew => $valNew)
				{
			
					if($_POST['schoolName'][$keyNew]!='') 
							{
										$aryData1=array(	
												'form_id'                                  => $iLastInsertId,
												'schoolName'                              => $_POST['schoolName'][$keyNew],
												'schoolAddress'                           => $_POST['schoolAddress'][$keyNew],
												'fromDate'                                => $_POST['fromDate'][$keyNew],
												'toDate'                                  => $_POST['toDate'][$keyNew],     
												'attClass'                                => $_POST['attClass'][$keyNew],
												'leaveReason'                             => $_POST['leaveReason'][$keyNew],
											); 
										$flgIn124 = $db->insertAry("app_acadamic",$aryData1);
										
			  			  }
				}
			
			
			foreach($_POST['guardian_type']as $key=>$val)
						{
		
					$aryData12=array(
						
							'form_id'                           	  => $iLastInsertId,
							
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
							
						); 
					$flgIn13 = $db->insertAry("app_guardianInfo",$aryData12);
					
				
						}
				
					
				if($getDetails['payment_method']=='2')
					{
								redirect(SITE_URL.'admission_voguepay.php?tokenid='.$tokenid);
					}
				else {
								redirect(SITE_URL.'admission_success.php?action=success');
				}
		
	}
?>
<!DOCTYPE html>
<html lang="en-US" prefix="#">
<meta http-equiv="content-type" content="text/html;charset=utf-8" />
<head>
<?php include('inc.meta-new.php'); ?>
<style>
.nav.nav-tabs, .tab-content {
	box-shadow: none!important;
	background: #fff;
	padding: 4px;
}
.btn.dropdown-toggle {
	border: 1px solid #E3E3E3!important;
	width: 90%;
}
.academic .row {
	margin-bottom: 20px!important
}
.guardian_breaker {
	border-top: 1px solid #E3E3E3;
	padding-top: 50px
}
.origin_area {
	width: 100%
}
input[type=checkbox], input[type=radio] {
	margin: -3px 0 0!important;
}
.datepicker { 
    z-index: 999 !important;
}
 
</style>
</head>
<body>
<div>
  <?php include('inc.header-new.php'); ?>
  <div id="content" class="site-content">
    <div class="call">
      <div class="call-a">
        <div class="container">
          <div class="row">
            <div class="us">
              <h2>Addmission Form</h2>
              <hr>
            </div>
          </div>
        </div>
      </div>
      <form method="post" class="academic" action="">
        <div class="call-b">
          <div id="contact_error"></div>
          <div class="container">
            <section id="content1" class="tab-content">
              <div class="row">
                <div class="col-md-12">
                  <section id="content1" class="tab-content">
                    <div class="row">
                      <div class="col-md-12 gappul">
                        <?php if($currentForm['boarding_type']=='1'){?>
                        <div class="row aqunt">
                          <div class="col-md-4 shemus">
                            <label class="active" for="gwt-uid-16">Boarding Type</label>
                            <select class="osama btn dropdown-toggle" name="bording">
                              <option value="1"<?php if($getpersonalinfo['bording']=='1'){ echo "selected";}?>>Day</option>
                              <option value="2"<?php if($getpersonalinfo['bording']=='2'){ echo "selected";}?>>Bording</option>
                              <option value="3"<?php if($getpersonalinfo['bording']=='3'){ echo "selected";}?>>Both</option>
                            </select>
                          </div>
                        </div>
                        <?php } ?>
                        <div class="row aqunt">
                          <div class="col-md-4 shemus">
                            <label class="active makethisheading" for="gwt-uid-16">School Session</label>
                            <input autocomplete="off" class="form-control"   name="session" value="<?php echo $getsession['session']; ?>" readonly placeholder="School Session" type="text">
                          </div>
                          <div class="col-md-4 shemus ">
                            <label class="active makethisheading" for="gwt-uid-16">School Section</label>
                            <input autocomplete="off" class="form-control"  readonly="readonly" name="section" value="<?php echo $getsection['section']; ?>" placeholder="School Section" type="text">
                          </div>
                          <div class="col-md-4">
                            <label class="active makethisheading" for="gwt-uid-16">Class to Apply</label>
                            <select class="adda btn dropdown-toggle">
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
                            <label class="active makethisheading" for="gwt-uid-16">First Name </label>
                            <input autocomplete="off" class="form-control" name="firstname" value="<?php echo $getpersonalinfo['firstname'];?>" placeholder="First Name" type="text">
                          </div>
                          <div class="col-md-4 ">
                            <label class="active makethisheading" for="gwt-uid-16">Last Name </label>
                            <input autocomplete="off" class="form-control"  name="lastname" value="<?php echo $getpersonalinfo['lastname'];?>" placeholder="Last Name" type="text">
                          </div>
                          <div class="col-md-4">
                            <label class="active makethisheading" for="gwt-uid-16">Other Names </label>
                            <input autocomplete="off" class="form-control" placeholder="Other Names" name="othername" value="<?php echo $getpersonalinfo['othername'];?>" type="text">
                          </div>
                        </div>
                        <div class="row">
                          <div class="col-md-4">
                            <label class="active makethisheading" for="gwt-uid-16">Date of Birth </label>
                            <input autocomplete="off" class="form-control datepicker" placeholder="Date of Birth" name="dob" value="<?php echo $getpersonalinfo['dob']; ?>" type="text">
                          </div>
                        </div>
                        <div class="row bsukna">
                          <?php if($currentForm['religion']=='1'){ ?>
                          <div class="col-md-4 ">
                            <label class="active" for="gwt-uid-16">Religion</label>
                            <select class="adda origin_area btn dropdown-toggle"  name="religion">
                              <?php $aryDetail=$db->getRows("select * from religion ");
                              if(is_array($aryDetail) && count($aryDetail) > 0) {
									foreach($aryDetail as $iList) { $i=$i+1;?>
                              <option value="<?php echo $iList['title']; ?>" <?php if($_POST['religion']==$iList['title']) { echo "Selected"; } ?>><?php echo $iList['title'];?></option>
                              <?php } 
                              }
                              ?>
                            </select>
                          </div>
                          <?php } ?>
                          <div class="col-md-1 ">
                            <div class="kaalin"> <span class="material-label">Gender</span></div>
                          </div>
                          <div class="col-md-3 ">
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
                      <div class="col-md-12 gappul">
                        <div class="row radspl">
                          <div class="col-md-12 ">
                            <label class="active" for="gwt-uid-16">Nationality</label>
                          </div>
                          <div class="col-md-12 "> 
                            <script>
						function showlocalarea()
							{
								var nationality = document.getElementById("nationality").value;
							
								if(nationality==150)
									{
								document.getElementById("showlocalgoverarea").style.display='block';	
									}
								else {
									document.getElementById("showlocalgoverarea").style.display='none';
								}
								
							}
						</script>
                            <select class="sonpit btn dropdown-toggle" name="nationality" id="nationality" onChange="showlocalarea()">
                              <?php
                                        $getcontry=$db->getRows("select * from  country order by country_name asc");
                                        foreach($getcontry as $ilist){
                                        
                                        ?>
                              <option value="<?php echo $ilist['id'];?>"<?php if($getpersonalinfo['nationality_id']==$ilist['id']){ echo "selected"; }?>><?php echo $ilist['country_name'];?></option>
                              <?php }?>
                            </select>
                          </div>
                        </div>
                        
                        
                        <br>
                        <?php if($currentForm['second_nationality']=='1'){ ?>
                        <div class="row dhst" style="display:none;"> 
                          <div class="col-md-12 ">
                            <label class="active" for="gwt-uid-16">Nationatlity Two</label>
                          </div>
                          <div class="col-md-12 ">
                            <select class="sonpit btn dropdown-toggle" name="nationalitytwo">
                              <option>Select Nationatlity</option>
                              <?php
                                        $getcontry=$db->getRows("select * from  country order by country_name asc");
                                        foreach($getcontry as $ilist){
                                        
                                        ?>
                              <option value="<?php echo $ilist['id'];?>"<?php if($getpersonalinfo['nationalitytwo_id']==$ilist['id']){ echo "selected"; }?>> <?php echo $ilist['country_name'];?></option>
                              <?php }?>
                            </select>
                          </div>
                        </div>
                        <?php  } ?>
                        <div class="row wllst">
                          <?php if($currentForm['state_local']=='1'){?>
                          <div class="col-md-6 ">
                            <label class="active" for="gwt-uid-16">State Of Origin</label>
                            <select class="adda origin_area btn dropdown-toggle" name="stateorigine">
                              <?php
                                        $getcontry=$db->getRows("select * from  local_government order by title asc");
                                        foreach($getcontry as $ilist){
                                        
                                        ?>
                              <option value="<?php echo $ilist['id'];?>"<?php if($getpersonalinfo['stateorigine']==$ilist['id']){ echo "selected"; }?>><?php echo $ilist['title'];?></option>
                              <?php }?>
                            </select>
                          </div>
                          <?php } ?>
                          <div class="col-md-6" id="showlocalgoverarea" style="display:none;">
                            <label class="active" for="gwt-uid-16">Local Government Area</label>
                            <select class="adda origin_area btn dropdown-toggle" name="local_state">
                              <option value="0">Select Local Goverment</option>
                              <?php
                                        $getstate=$db->getRows("select * from  local_government order by title asc");
                                        foreach($getstate as $ilist){
                                        
                                        ?>
                              <option value="<?php echo $ilist['id'];?>"<?php if($getpersonalinfo['local_state']==$ilist['id']){ echo "selected"; }?>> <?php echo $ilist['title'];?></option>
                              <?php }?>
                            </select>
                          </div>
                        </div>
                        <div class="row wllst">
                          <?php if($currentForm['tribe']=='1'){?>
                          <div class="col-md-4 joncena">
                            <label class="active" for="gwt-uid-16">Child email address </label>
                            <input autocomplete="off" class="form-control child_text" name="child_mail"  placeholder="Child email address" value="<?php echo $getpersonalinfo['child_mail']; ?>" type="text">
                          </div>
                          <div class="col-md-4">
                            <label class="active" for="gwt-uid-16">Tribe of child </label>
                            <input autocomplete="off" class="form-control" name="tribechild" placeholder="Tribe of child." value="<?php echo $getpersonalinfo['tribechild']; ?>"  type="text">
                          </div>
                          <?php } ?>
                          <?php if($currentForm['place_of_birth']=='1'){?>
                          <div class="col-md-4 tchaild ">
                            <label class="active" for="gwt-uid-16">Place of Birth </label>
                            <input autocomplete="off" class="form-control" name="birth_place" placeholder="Place of Birth" value="<?php echo $getpersonalinfo['birth_place']; ?>" type="text">
                          </div>
                          <?php } ?>
                        </div>
                        <div class="row wllst">
                          <?php if($currentForm['city_address']=='1'){ ?>
                          <div class="col-md-12 tchaild">
                            <label class="active" for="gwt-uid-16">Address of child </label>
                            <input autocomplete="off" class="form-control"   name="child_address" placeholder="Address of child" value="<?php echo $getpersonalinfo['child_address']; ?>" type="text">
                          </div>
                          <?php } ?>
                        </div>
                        <?php if($currentForm['kin_from_appl']=='1'){ ?>
                        <div class="row wllst">
                          <div class="col-md-4 tchaild">
                            <label class="active" for="gwt-uid-16">Next Of Kin </label>
                            <input autocomplete="off" class="form-control" name="nextKin"  placeholder="Next Of Kin"  value="<?php echo $getpersonalinfo['nextKin']; ?>" type="text">
                          </div>
                          <div class="col-md-4 tchaild">
                            <label class="active" for="gwt-uid-16">Relationship With Next of Kin </label>
                            <input autocomplete="off" class="form-control" name="relationOfKin" value="<?php echo $getpersonalinfo['relationOfKin']; ?>" placeholder="Relationship With Next of Kin" type="text">
                          </div>
                          <div class="col-md-4 tchaild">
                            <label class="active" for="gwt-uid-16">Next of Kin Phone No. </label>
                            <input autocomplete="off" class="form-control"  name="kinPhone"  value="<?php echo $getpersonalinfo['kinPhone']; ?>" placeholder="Next of Kin Phone No." type="text">
                          </div>
                        </div>
                        <?php } ?>
                        <div class="row wllst">
                          <div class="col-md-12 tchaild">
                            <label class="active" for="gwt-uid-16">Hobbies </label>
                            <input autocomplete="off" class="form-control" name="hobbies" placeholder="Hobbies" value="<?php echo $getpersonalinfo['hobbies']; ?>" type="text">
                          </div>
                        </div>
                        <?php if($currentForm['remarks_field']=='1'){?>
                        <div class="row wllst">
                          <div class="col-md-12">
                            <label class="active" for="gwt-uid-16">Other information about child. </label>
                            <textarea autocomplete="off" class="form-control" name="childOtherInfo" placeholder="Other information about child." type="text"> <?php echo $getpersonalinfo['childOtherInfo']; ?></textarea>
                          </div>
                        </div>
                        <?php } ?>
                      </div>
                    </div>
                  </section>
                </div>
              </div>
            </section>
          </div>
          <div class="container">
            <section id="content1" class="tab-content" style="    padding: 5px;">
              <div class="row">
                <div class="col-md-12">
                  <h3 class="pull-left">PREVIOUS SCHOOL INFORMATION </h3>
                  <table id="data1newFirst" class="table table-striped table-bordered">
                    <thead>
                      <tr>
                        <th>School name</th>
                        <th>School Address</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Class Attended</th>
                        <th>Leave Reason</th>
                        <th></th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr>
                        <td><input type="text" name="schoolName[]" ></td>
                        <td><input type="text" name="schoolAddress[]" ></td>
                        <td><input type="text" name="fromDate[]" style="width:90px;" ></td>
                        <td><input type="text" name="toDate[]"  style="width:90px;"></td>
                        <td><input type="text" name="attClass[]" style="width:90px;"></td>
                        <td><input type="text" name="leaveReason[]" ></td>
                        <td><a class="btn btn-danger" style="color:#fff!important;" onclick="javascript:deleteRow( this , 'data1newFirst')" ><i class="fa fa-times"></i> </a></td>
                      </tr>
                    </tbody>
                  </table>
                  <table>
                    <tr>
                      <td><a class="btn btn-primary" style="color:#fff!important;" onclick="javascript:appendRow('data1newFirst')" ><i class="fa fa-plus"></i> Add New Row</a></td>
                    </tr>
                  </table>
                </div>
              </div>
            </section>
          </div>
          <div class="container">
            <section id="content3" class="tab-content guardian_breaker">
              <div class="suzika">
                <div class="row">
                  <div class="col-md-12">
                    <?php  for ($x = 1; $x <=$iTotalNumberOfGuardian; $x++) 	{	 ?>
                    <h4 class="panel-title"> <a href="#" class="ing">Guardian <?php echo $x;?></a> </h4>
                    <div class="panel-body">
                      <div class="row">
                        <div class="col-md-12">
                          <div class="row mugambo">
                            <div class="col-md-6">
                              <label class="active" for="gwt-uid-16">Guardian Type</label>
                              <select class="adda btn dropdown-toggle"name="guardian_type[]">
                                <option value="">Select Type...</option>
                                <option value="Father" <?php if($iGetguardian['guardian_type']="Father"){echo "selected";}?>>Father</option>
                                <option value="Mother" <?php if($iGetguardian['guardian_type']="Mother"){echo "selected";}?>>Mother</option>
                                <option value="Uncle" <?php if($iGetguardian['guardian_type']="Uncle"){echo "selected";}?>>Uncle</option>
                                <option value="Aunt" <?php if($iGetguardian['guardian_type']="Aunt"){echo "selected";}?>>Aunt</option>
                                <option value="Other" <?php if($iGetguardian['guardian_type']="Other"){echo "selected";}?>>Other</option>
                              </select>
                            </div>
                            <div class="col-md-6">
                              <div class="kaalin"> <span class="material-label">Gender</span>
                                <div class="ortand">
                                  <label>
                                    <input type="radio" class="radio-inline" name="g_gender[]" <?php if($iGetguardian['gender']="male"){echo "checked";}?> value="male">
                                    <span class="outside"><span class="inside"></span></span>Male</label>
                                  <label>
                                    <input type="radio"class="radio-inline" name="g_gender[]"  <?php if($iGetguardian['gender']="female"){echo "checked";}?> value="female">
                                    <span class="outside"><span class="inside"></span></span>Female</label>
                                </div>
                              </div>
                            </div>
                          </div>
                          <div class="row mugambo">
                            <div class="col-md-3">
                              <label class="active" for="gwt-uid-16">Nationality</label>
                              <select class="adda btn dropdown-toggle" name="g_nationality[]" >
                                <option>Select Nationality </option>
                                <?php
                            $getcontry=$db->getRows("select * from  country order by country_name asc");
                            foreach($getcontry as $ilist){
                            
                            ?>
                                <option value="<?php echo $ilist['id'];?>"><?php echo $ilist['country_name'];?></option>
                                <?php }?>
                              </select>
                            </div>
                            <div class="col-md-3">
                              <label class="active" for="gwt-uid-16">State of origin</label>
                              <select class="adda btn dropdown-toggle" name="g_stateorigion[]">
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
                              <select class="adda btn dropdown-toggle"name="g_localArea[]">
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
                        <div class="col-md-4"> </div>
                      </div>
                    </div>
                    <?php  } ?>
                  </div>
                </div>
                <?php    if($getDetails['payment_method']=='2') { 	?>
                <hr>
                <div class="container">
                  <section id="content1" class="tab-content">
                    <div class="row">
                      <div class="col-md-12">
                        <h3 style="width:100%" class="pull-left">Fee Detail </h3>
                        <table  class="table table- table-bordered" style="width:400px">
                          <tbody>
                            <tr>
                              <td><B>Amount To Pay</B></td>
                              <td><B> ₦ <?php echo $getDetails['fee']; ?></B></td>
                            </tr>
                          </tbody>
                        </table>
                      </div>
                    </div>
                  </section>
                </div>
                <?php	} ?>
                <div class="row usodenil">
                  <div class="col-md-6 jarrt">
                    <button type="submit"  name="addnewrecord"  class="makethisbtn btn btn-info pull-right">Submit <i class="fa fa-arrow-right" aria-hidden="true"></i></button>
                  </div>
                </div>
              </div>
            </section>
          </div>
        </div>
      </form>
    </div>
    <div class="container"></div>
  </div>
  <?php include('inc.footer-new.php');	?>
</div>
<?php include('inc.js-new.php');	?>
<script type="text/javascript" src="<?php echo SITE_URL; ?>skool/assets/js/bootstrap-datepicker.min.js"></script>
<link rel="stylesheet" href="<?php echo SITE_URL; ?>skool/assets/css/bootstrap-datepicker3.css"/>
<script>
$(document).ready(function() {
	jQuery('.datepicker').datepicker({
		autoclose: true,
		format: "yyyy-mm-dd",
		todayHighlight: true
    });
});
</script>
<script>
function appendRow(gethisii) 
{ 
    var tblBody = document.getElementById(gethisii).tBodies[0];
	var newNode = tblBody.rows[0].cloneNode(true);
	tblBody.appendChild(newNode);
}
</script> 
<script type="text/javascript">

 function deleteRow(link, getthis) 
	{
		 var totalRowCount = 0;
        var rowCount = 0;
		var table = document.getElementById("data1newFirst");
        var rows = table.getElementsByTagName("tr")
        for (var i = 0; i < rows.length; i++) {
            totalRowCount++;
            if (rows[i].getElementsByTagName("td").length > 0) {
                rowCount++;
            }
        }
        var message = "Total Row Count: " + totalRowCount;
        message += "\nRow Count: " + rowCount;
       
		if(rowCount>1)	
		{
			var row = link.parentNode.parentNode;
			var table = row.parentNode; 
			table.removeChild(row);
		} 
}
</script>
</body>
</html>