<?php include('../config.php'); 
$PageTitle="Application form Setting";
$FileName = 'app_settings.php';
$validate=new Validation();
$getDetails=$db->getRow("select * from  application_form where randomid='".$_GET['randomid']."'");

if(isset($_POST['submit']))
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
								);  
					$flgI = $db->updateAry("app_settings", $arrayData , "where app_id='".$getDetails['id']."' ");
					$stat['success']="Update Successfully";
					
					redirect('application.php');
	}
?>

<!DOCTYPE html>

<html>
<head>
<?php include('inc.meta.php'); ?>
</head>
<style>
.switch {
  position: relative;
  display: inline-block;
  width: 60px;
  height: 34px;
}

.switch input { 
  opacity: 0;
  width: 0;
  height: 0;
}

.slider {
  position: absolute;
  cursor: pointer;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: #ccc;
  -webkit-transition: .4s;
  transition: .4s;
}

.slider:before {
  position: absolute;
  content: "";
  height: 26px;
  width: 26px;
  left: 4px;
  bottom: 4px;
  background-color: white;
  -webkit-transition: .4s;
  transition: .4s;
}

input:checked + .slider {
  background-color: #2196F3;
}

input:focus + .slider {
  box-shadow: 0 0 1px #2196F3;
}

input:checked + .slider:before {
  -webkit-transform: translateX(26px);
  -ms-transform: translateX(26px);
  transform: translateX(26px);
}

/* Rounded sliders */
.slider.round {
  border-radius: 34px;
}

.slider.round:before {
  border-radius: 50%;
}
</style>
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
        
        <!-- Basic Form Wizard -->
        
        <div class="row">
		 
          <div class="col-md-12">
           <?php $getsetting=$db->getRow("select * from  app_settings where app_id='".$getDetails['id']."'"); ?>
            <div class="card-box">
			<center><h3>Application form setting.</h3></center>
			   <hr>
              <form role="form" action="" method="post" enctype="multipart/form-data">
                <div>
                  <section>
                    <div class="form-group clearfix">
                      <label class="col-lg-2 control-label " for="userName">Enable admission requirement </label>
                      <div class="col-lg-10">
					<label class="switch">
					<input type="checkbox" name="admission_req" value="1" <?php if($getsetting['admission_req']=='1'){echo "checked"; }?>>
					<span class="slider round"></span>
					</label>

                      </div>
                    </div>
					<div class="form-group clearfix">
                      <label class="col-lg-2 control-label " for="userName">
					  Require and design additional questions.
                        </label>
                      <div class="col-lg-10">
					<label class="switch">
					<input type="checkbox" name="additional_quiz"value="1"<?php if($getsetting['design_additional_qeus']=='1'){echo "checked"; }?>>
					<span class="slider round"></span>
					</label>

                      </div>
                    </div>
					<div class="form-group clearfix">
                      <label class="col-lg-2 control-label " for="userName">
					  Require academic history.
                   </label>
                      <div class="col-lg-10">
					<label class="switch">
					<input type="checkbox" name="academic_history"value="1"<?php if($getsetting['academic_history']=='1'){echo "checked"; }?>>
					<span class="slider round"></span>
					</label>

                      </div>
                    </div> <hr>
					
					<div class="form-group clearfix">
                      <label class="col-lg-2 control-label " for="userName">
					  Require next of kin from applicant.
                    </label>
                      <div class="col-lg-10">
					<label class="switch">
					<input type="checkbox"name="kin_applicant"value="1"<?php if($getsetting['kin_from_appl']=='1'){echo "checked"; }?>>
					<span class="slider round"></span>
					</label>

                      </div>
                    </div>
					
					<div class="form-group clearfix">
                      <label class="col-lg-2 control-label " for="userName">
					  Require remarks field in basic info form 
					  </label>
                      <div class="col-lg-10">
					<label class="switch">
					<input type="checkbox"name="info_form"value="1"<?php if($getsetting['remarks_field']=='1'){echo "checked"; }?>>
					<span class="slider round"></span>
					</label>

                      </div>
                    </div>
					
					<div class="form-group clearfix">
                      <label class="col-lg-2 control-label " for="userName">
					  Require applicant city or address
                    </label>
                      <div class="col-lg-10">
					<label class="switch">
					<input type="checkbox"name="city_address"value="1"<?php if($getsetting['city_address']=='1'){echo "checked"; }?>>
					<span class="slider round"></span>
					</label>

                      </div>
                    </div>
					
					<div class="form-group clearfix">
                      <label class="col-lg-2 control-label " for="userName">
                     Require applicant place of birth
					  </label>
                      <div class="col-lg-10">
					<label class="switch">
					<input type="checkbox"name="birth_place"value="1"<?php if($getsetting['place_of_birth']=='1'){echo "checked"; }?>>
					<span class="slider round"></span>
					</label>

                      </div>
                    </div>
					
					<div class="form-group clearfix">
                      <label class="col-lg-2 control-label " for="userName">
					  Require applicant religion.
                    </label>
                      <div class="col-lg-10">
					<label class="switch">
					<input type="checkbox"name="religion"value="1"<?php if($getsetting['religion']=='1'){echo "checked"; }?>>
					<span class="slider round"></span>
					</label>

                      </div>
                    </div>
					
					
					<div class="form-group clearfix">
                      <label class="col-lg-2 control-label " for="userName">
					  Require applicant tribe.
                </label>
                      <div class="col-lg-10">
					<label class="switch">
					<input type="checkbox"name="tribe"value="1"<?php if($getsetting['tribe']=='1'){echo "checked"; }?>>
					<span class="slider round"></span>
					</label>

                      </div>
                    </div>
					
					
					<div class="form-group clearfix">
                      <label class="col-lg-2 control-label " for="userName">
					  Require applicant state of origin and local government
                      </label>
                      <div class="col-lg-10">
					<label class="switch">
					<input type="checkbox"name="state"value="1"<?php if($getsetting['state_local']=='1'){echo "checked"; }?>>
					<span class="slider round"></span>
					</label>

                      </div>
                    </div>
					
					<div class="form-group clearfix">
                      <label class="col-lg-2 control-label " for="userName">
					  Require second nationality from applicant

                      </label>
                      <div class="col-lg-10">
					<label class="switch">
					<input type="checkbox"name="second_nationality"value="1"<?php if($getsetting['second_nationality']=='1'){echo "checked"; }?>>
					<span class="slider round"></span>
					</label>

                      </div>
                    </div>
					<div class="form-group clearfix">
                      <label class="col-lg-2 control-label " for="userName">
                     Allow applicant to choose boarding type
                      </label>
                      <div class="col-lg-10">
					<label class="switch">
					<input type="checkbox"name="boarding" value="1"<?php if($getsetting['boarding_type']=='1'){echo "checked"; }?>>
					<span class="slider round"></span>
					</label>

                      </div>
                    </div>
					<button type="submit" name="submit" class="btn btn-default" style="float:right">Save Setting</button>
					<a  href="<?php echo ADMIN_URL; ?>application.php"  class="btn btn-default" >Back</a>
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