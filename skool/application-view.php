<?php include('../config.php'); 
include('inc.session-create.php'); 
$PageTitle="Application";
$FileName = 'application.php';
$validate=new Validation();
if($_SESSION['success']!="")
{
$stat['success']=$_SESSION['success'];
unset($_SESSION['success']);
}


$getpersonalinfo=$db->getRow("select * from   app_personalinfo where tokenid='".$_GET['tokenid']."'");
 
$currentApp=$db->getRow("select * from  application_form where id='".$getpersonalinfo['app_id']."'");	
$currentForm=$db->getRow("select * from  app_settings where app_id='".$currentApp['id']."'");
$getDetails=$db->getRow("select * from  application_form where randomid='".$currentApp['randomid']."'");



$getachademic=$db->getRows("select * from  app_acadamic where form_id='".$getpersonalinfo['id']."'");
$iGuardianInfo=$db->getRows("select * from  app_guardianinfo where form_id='".$getpersonalinfo['id']."'");
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
<style>
.nav.nav-tabs, .tab-content {
	box-shadow: none!important;
	background: #fff;
	padding: 4px;
}
.btn.dropdown-toggle {
	border: none;
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
</style>
<script type="text/javascript">
    $(document).ready(function(){
        $("form :input").prop("disabled", true);
    });
</script>
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
        
        <?php if($_GET['action']=='view') {  ?>
        <div class="row">
          <div class="col-sm-12">
            <h4 class="page-title licat">APPLICATION FORMS DETAIL</h4>
            <ol class="breadcrumb">
              <li class="dippi"> <a class="btn btn-info" style="color:#fff;" href="application-view.php?randomid=<?php echo $_GET['randomid']; ?>">Back</a> </li>
            </ol>
          </div>
        </div>
        
        <!-- Basic Form Wizard -->
        <div class="row">
          <div class="col-md-12 ">
            <div class="card-box">
              <div class="row">
                <div class="col-md-12">
                  <p class="dmo">Demo</p>
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
                                    <input autocomplete="off" class="form-control" placeholder="Date of Birth" name="dob" value="<?php echo $getpersonalinfo['dob']; ?>" type="text">
                                  </div>
                                </div>
                                <div class="row bsukna">
                                  <?php if($currentForm['religion']=='1'){ ?>
                                  <div class="col-md-4 ">
                                    <label class="active" for="gwt-uid-16">Religion</label>
                                    <select class="adda origin_area btn dropdown-toggle"  name="religion">
                                      <option value="">Select Religion</option>
                                      <option value="Hindu" <?php if($getpersonalinfo['religion']=="Hindu"){ echo "selected"; }?>>Hindu</option>
                                      <option value="Muslim" <?php if($getpersonalinfo['religion']=="Muslim"){ echo "selected"; }?>>Muslim</option>
                                      <option value="Sikh" <?php if($getpersonalinfo['religion']=="Sikh"){ echo "selected"; }?>>Sikh</option>
                                      <option value="Other" <?php if($getpersonalinfo['religion']=="Other"){ echo "selected"; }?>>Other</option>
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
                                    <select class="sonpit btn dropdown-toggle" name="nationality">
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
                                <div class="row dhst">
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
                                  <div class="col-md-6">
                                    <label class="active" for="gwt-uid-16">Local Government Area</label>
                                    <select class="adda origin_area btn dropdown-toggle" name="local_state">
                                      <option>Select Local Goverment</option>
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
                          <h3 class="pull-left">School Information</h3>
                          <table id="data1newFirst" class="table table-striped table-bordered">
                            <thead>
                              <tr>
                                <th>School name</th>
                                <th>School Address</th>
                                <th>Start Date</th>
                                <th>End Date</th>
                                <th>Class Attended</th>
                                <th>Leave Reason</th>
                              </tr>
                            </thead>
                            <tbody>
                              <?php  foreach($getachademic as $getAcd) { ?>
                              <tr>
                                <td><input type="text" name="schoolName[]" value="<?php echo $getAcd['schoolName']; ?>" ></td>
                                <td><input type="text" name="schoolAddress[]" value="<?php echo $getAcd['schoolAddress']; ?>" ></td>
                                <td><input type="text" name="fromDate[]" style="width:90px;" value="<?php echo $getAcd['fromDate']; ?>" ></td>
                                <td><input type="text" name="toDate[]"  style="width:90px;" value="<?php echo $getAcd['toDate']; ?>" ></td>
                                <td><input type="text" name="attClass[]" style="width:90px;" value="<?php echo $getAcd['attClass']; ?>" ></td>
                                <td><input type="text" name="leaveReason[]" value="<?php echo $getAcd['attClass']; ?>" ></td>
                              </tr>
                              <?php  } ?>
                            </tbody>
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
                            <?php  foreach($iGuardianInfo as $iGetguardian) { ?>
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
          </div>
        </div>
        <?php } else { ?>
        <div class="card-box">
          <table id="datatable" class="table table-striped table-bordered">
            <thead>
              <tr>
                <th>#</th>
                <th>Application form</th>
                <th>First Name</th>
                <th>Last Name</th>
               
                <th>Date</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              <?php
			  if($_GET['randomid']=='') {
		 $iAppDetail=$db->getVal("select group_concat(id) from application_form where create_by_userid='".$create_by_userid."' order by id desc");
		$aryList=$db->getRows("select * from app_personalinfo where app_id IN (".$iAppDetail.") order by id desc");
			  } else {
		$iAppDetail=$db->getRow("select * from application_form where randomid='".$_GET['randomid']."' order by id desc");
		$aryList=$db->getRows("select * from app_personalinfo where app_id='".$iAppDetail['id']."' order by id desc");
			  }
		foreach($aryList as $iList)
			{	
			$i=$i+1;
		$aryPgAct["id"]=$iList['id'];
			 ?>
              <tr>
                <td><?php echo $i; ?></td>
                <td><?php echo $db->getVal("select name from application_form where id='".$iList['app_id']."' order by id desc");; ?></td>
                <td><?php echo $iList['firstname']; ?></td>
                <td><?php echo $iList['lastname']; ?></td>
               
              <td><?php echo $iList['create_at']; ?></td>
                <td><a href="application-view.php?action=view&tokenid=<?php echo $iList['tokenid']; ?>&randomid=<?php echo $_GET['randomid']; ?>" class="table-action-btn"> <i class="fa fa-search"></i></a> </td>
              </tr>
              <?php } ?>
            </tbody>
          </table>
        </div>
        <?php } ?>
      </div>
    </div>
    <?php include('inc.footer.php'); ?>
  </div>
</div>
<?php include('inc.js.php'); ?>
<style>
input,textarea:disabled {
  background: #fff!important;
  border:none!important;
}
.makethisbtn  { display:none; }
</style>
