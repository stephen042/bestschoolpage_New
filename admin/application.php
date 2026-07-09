<?php include('../config.php'); 
include('inc.session-create.php'); 
$PageTitle="Aapplication Form";
$FileName = 'application.php';
$validate=new Validation();
if($_SESSION['success']!="")
{
$stat['success']=$_SESSION['success'];
unset($_SESSION['success']);
}
	if(isset($_POST['submit']))
{                
				$validate->addRule($_POST['name'],'','Name',true);
				$validate->addRule($_POST['session'],'',' Session',true);
				$validate->addRule($_POST['section'],'','Section',true);
				$validate->addRule($_POST['startdate'],'','Application start date',true);
				$validate->addRule($_POST['enddate'],'','Application end date',true);
				$validate->addRule($_POST['fee'],'','Fee',true);
				if(empty($_POST['payment'])){
				$validate->addRule($_POST['payment'],'','Payment Method',true);}
				$validate->addRule($_POST['formpre'],'',' Form Pre',true);
				$validate->addRule($_POST['formrange'],'','Form Range',true);
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
								'class'                                     => $stclass,
								'subject'                                   => $_POST['subject'],
								'start_date'                                => $_POST['startdate'],
								'end_date'                                  => $_POST['enddate'],     
								'fee'                                       => $_POST['fee'],
								'guardian'                                  => $_POST['guardian'],
								'no_of_guardian'                            => $_POST['no_of_guardian'],
								'payment_method'                            => $payment,	
								'form_prefix'  			                    => $_POST['formpre'],	
								'form_no_range'  			                => $_POST['formrange'],	
								'status'  			                        => $_POST['status'],	
								'create_at'  			                    => date("Y-m-d H:i:s"),	
								'randomid'  			                    => randomFix(10),	
								'pageurl'  			                        => $iPageUrl,
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
					
					$stat['success']="Submited Successfully";
					redirect($FileName);
					unset($_POST);
					 
				}
			else {
					$stat['error'] = $validate->errors();
				}
			}

elseif(isset($_POST['update']))
		{ 
		
		$validate->addRule($_POST['name'],'','Name',true);
		$validate->addRule($_POST['session'],'',' Session',true);
		$validate->addRule($_POST['section'],'','Section',true);
		$validate->addRule($_POST['fee'],'','Fee',true);
		if(empty($_POST['payment'])){
		$validate->addRule($_POST['payment'],'','Payment Method',true);}
		$validate->addRule($_POST['formpre'],'',' Form Pre',true);
		$validate->addRule($_POST['formrange'],'','Form Range',true);
		
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
							'class'                                     => $stclass,
							'subject'                                   => $_POST['subject'],
							'start_date'                                => $_POST['startdate'],
							'end_date'                                  => $_POST['enddate'],     
							'fee'                                       => $_POST['fee'],
							'guardian'                                  => $_POST['guardian'],
							'no_of_guardian'                            => $_POST['no_of_guardian'],
							'payment_method'                            => $payment,	
							'form_prefix'  			                    => $_POST['formpre'],	
							'form_no_range'  			                => $_POST['formrange'],	
							'status'  			                        => $_POST['status'],	
						); 
					$flgIn = $db->updateAry("application_form", $aryData , "where randomid='".$_GET['randomid']."' ");
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
			$flgIn1 = $db->delete("application_form","where randomid='".$_GET['randomid']."'");			
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
            <h4 class="page-title"><?php echo $PageTitle; ?></h4>
            <ol class="breadcrumb">
              <li> <a href="attendancedashbord.php">Home</a> </li>
              <li class="active"> <?php echo $PageTitle; ?> </li>
            </ol>
          </div>
        </div>
        <!-- Basic Form Wizard -->
        <div class="row">
          <div class="col-md-12">
            <div class="card-box aplhanewclass">
              <div class="row">
                <div class="col-md-9"> <?php echo msg($stat); ?> </div>
                <div class="col-md-3">
				<a href="<?php echo $FileName; ?>?action=add"  class="btn btn-default" style="float:right">New Form +</a> 
				</div>
              </div>
            </div>
              <?php 	if($_GET['action']=='add') { ?>
              <div class="card-box">
              <form role="form" action="" method="post" enctype="multipart/form-data">
                <div>
                  <section>				 				  
				   <div class="form-group clearfix">
                      <label class="col-lg-2 control-label " for="userName">Application Name:</label>
                      <div class="col-lg-10">
                        <input type="text" class="form-control" id="title"  name="name" value="<?php echo $_POST['name']; ?>">
                      </div>
                    </div>
                     
                     
                    <div class="form-group clearfix">
                      <label class="col-lg-2 control-label " for="userName">Select Session:</label>
                      <div class="col-lg-10">
                        <select name="session" id="session" class="form-control" onchange="getsection()">
                       <option value="">select session</option>
                         <?php
                          $isession=$db->getRows("select * from school_session");
						  foreach($isession as $list)
                              {
                               ?>
                        <option value="<?php echo $list['id'];?>"<?php if($_POST['Session']==$list['id']){echo "selected";}?>>
						<?php echo $list['session'];?></option>
                              <?php } ?>
                            </select>
                      </div>
                    </div>
					
					
					
					<div class="form-group clearfix">
                      <label class="col-lg-2 control-label " for="userName">Select Section:</label>
                      <div class="col-lg-10"id="showsection">
                        <select name="section"  class="form-control" onchange="getclass()">
                       <option value="">select section</option>
                         
                        </select>
                      </div>
                    </div>
					
					<div class="form-group clearfix" id="showclass" >
                      
                    </div>
					
					
					<div class="form-group clearfix">
                      <label class="col-lg-2 control-label " for="userName">Select subject:</label>
                      <div class="col-lg-10">
                        <select name="subject" id="showsubject" class="form-control">
						<option>Select Subject</option>
						
                        </select>
                      </div>
                    </div>
					
                      <div class="form-group clearfix">
                      <label class="col-lg-2 control-label " for="userName">Application Start Date:</label>
                      <div class="col-lg-10">
                      <input type="text" class="form-control" id="datepicker" name="startdate" value="<?php echo $_POST['startdate']; ?>"></textarea>
                      </div>
                      </div>
					
					  <div class="form-group clearfix">
                      <label class="col-lg-2 control-label " for="userName">Application End Date:</label>
                      <div class="col-lg-10">
                      <input type="text" class="form-control" id="datepicker1" name="enddate" value="<?php echo $_POST['enddate']; ?>">
                      </div>
                      </div
					  
					  ><div class="form-group clearfix">
                      <label class="col-lg-2 control-label " for="userName">Application Fee:</label>
                      <div class="col-lg-10">
                      <input type="text" class="form-control"  name="fee"  value="<?php echo $_POST['fee']; ?>"></textarea>
                      </div>
                      </div>
	 					
					<div class="form-group clearfix">
					<label class="col-lg-2 control-label " for="confirm">Require Guardian:</label>
					<div class="col-lg-10">
					<input type="radio"  name="guardian" onclick="getguaidian('1')"  value="1" 
					<?php if($_POST['guardian']=='1') { echo "checked"; } ?>>Yes
					<input type="radio"  name="guardian" onclick="getguaidian('2')" required value="2" 
					<?php if($_POST['guardian']=='1') { echo "checked"; } ?> checked>No
					</div>
					</div>
					  
					  <div class="form-group clearfix" id="no_of_guardi" style="display:none;">
                    <label class="col-lg-2 control-label " for="confirm">No Of Guardian:</label>
                    <div class="col-lg-10">
					 <input type="number" class="form-control"  name="no_of_guardian" id="no_of_guardian"
					 value="<?php echo $_POST['no_of_guardian']; ?>">
                    </div>
                  </div>
					  
					  
                      <div class="form-group clearfix">
                      <label class="col-lg-2 control-label " for="userName">Payment Method:</label>
                      <div class="col-lg-10">
                      <input type="checkbox"name="payment[]" value="1"	>Bank Payment
					  <input type="checkbox" name="payment[]"value="2"<?php if(in_array("2",$_POST['payment'])){echo "checked";} ?>>Online
					 <input type="checkbox" name="payment[]" value="0"<?php if(in_array("0",$_POST['payment'])){echo "checked";} ?>checked>  No Payment
                      </div>
                      </div>

                      <div class="form-group clearfix">
                      <label class="col-lg-2 control-label " for="userName">Form Prefix </label>
                      <div class="col-lg-10">
					<input type="text" class="form-control"  placeholder="EBN/2019" name="formpre" value="<?php echo $_POST['formpre']; ?>" >
                      </div>
                      </div>
                      
					   <div class="form-group clearfix">
                      <label class="col-lg-2 control-label " for="userName">Form No Range </label>
                      <div class="col-lg-10">
                     <input type="text"  class="form-control" placeholder="0000" name="formrange" value="<?php echo $_POST['formrange'];?>"/><br>
                      </div>
                      </div>
					  
					  
						<div class="form-group clearfix">
						<label class="col-lg-2 control-label " for="confirm">Status :</label>
						<div class="col-lg-10">
						<select  class="required form-control" name="status">
						<option value="1" <?php if($_POST['status']=='1') { echo "selected"; } ?>>Active</option>
						<option value="0" <?php if($_POST['status']=='0') { echo "selected"; } ?>>Inactive</option>
						</select>
						</div>
						</div>
                  <button type="submit" name="submit" class="btn btn-default">Submit</button>
					<a  href="<?php echo ADMIN_URL; ?>application.php"  class="btn btn-default" >Back</a> </section>
                </div>
              </form>
            </div>
		
			  <?php } elseif($_GET['action']=='edit') {
               $getDetails=$db->getRow("select * from  application_form where randomid='".$_GET['randomid']."'");
			   
			  ?>
			<div class="card-box">
              <form role="form" action="" method="post" enctype="multipart/form-data">
			  <section>
                    <div class="form-group clearfix">
                      <label class="col-lg-2 control-label " for="userName">Name:</label>
                      <div class="col-lg-10">
                        <input type="text" class="form-control" id="title" name="name" value="<?php echo $getDetails['name']; ?>">
                      </div>
                    </div>
                     
                     
                    <div class="form-group clearfix">
                      <label class="col-lg-2 control-label " for="userName">Select Session:</label>
                      <div class="col-lg-10">
                        <select name="session" id="session" class="form-control" onchange="getsection()">
                       <option value="">select session</option>
                         <?php
                          $isession=$db->getRows("select * from school_session");
                          foreach($isession as $list)
                              {
                               ?>
                        <option value="<?php echo $list['id'];?>"<?php if($getDetails['session']==$list['id']){echo "selected";}?>>
						<?php echo $list['session'];?></option>
                              <?php } ?>
                            </select>
                      </div>
                    </div>
					
					
					
					<div class="form-group clearfix">
                      <label class="col-lg-2 control-label " for="userName">Select Section:</label>
                      <div class="col-lg-10">
						<select name="section" id="showsection" class="form-control" onchange="getclass()">
						<option value="">select section</option>
						<?php
						$isection=$db->getRows("select * from school_section");
						foreach($isection as $list)
						{
						?>
						<option value="<?php echo $list['id'];?>"<?php if($getDetails['section']==$list['id']){echo "selected";}?>>
						<?php echo $list['section'];?>
						</option>
						<?php } ?>
						</select>
                      </div>
                    </div>
					
					<div class="form-group clearfix" id="showclass" >
						<label class="col-lg-2 control-label " for="userName">Select Class:</label>
						<div class="col-lg-10">
						<?php
						$iclass=$getDetails['class'];
						$icc=explode(",",$iclass);
						$i=0;
						
						$iclass=$db->getRows("select * from school_class where section_id IN ('".$getDetails['class']."') ");
						foreach($iclass as $list)
						{
						$i++;
						?>
						<input type="checkbox" name="class[]" class="classList" onchange="getsubject()" value="<?php echo $id=$list['id'];?>"
						<?php if(in_array("$id",$icc)){echo "checked";} ?>>
						<?php echo $list['name'];
						} ?>

						</div>
                    </div>
					<div class="form-group clearfix" >
                      <label class="col-lg-2 control-label " for="userName">Select subject:</label>
                      <div class="col-lg-10">
                        <select name="subject"  id="showsubject" class="form-control">
						<option>Select Subject</option>
						<?php
						$isection=$db->getRows("select * from school_subject where id='".$getDetails['subject']."'");
						foreach($isection as $list)
						{?>
						<option value="<?php echo $list['id'];?>"<?php if($getDetails['subject']==$list['id']){echo "selected";}?>>
						<?php echo $list['subject'];?></option>
						<?php } ?>
                        </select>
                      </div>
                    </div>
					
					
                      <div class="form-group clearfix">
                      <label class="col-lg-2 control-label " for="userName">Start Date:</label>
                      <div class="col-lg-10">
                      <input type="text" class="form-control"  name="startdate" value="<?php echo $getDetails['start_date']; ?>">
                      </div>
                      </div>
					
					<div class="form-group clearfix">
                      <label class="col-lg-2 control-label " for="userName">End Date:</label>
                      <div class="col-lg-10">
                      <input type="text" class="form-control"  name="enddate" value="<?php echo $getDetails['end_date']; ?>">
                      </div>
                      </div>
					  
					  <div class="form-group clearfix">
                      <label class="col-lg-2 control-label " for="userName">Fee:</label>
                      <div class="col-lg-10">
                      <input type="text" class="form-control"  name="fee" value="<?php echo $getDetails['fee']; ?>">
                      </div>
                      </div>
					  

						<div class="form-group clearfix">
						<label class="col-lg-2 control-label " for="confirm">Require Guardian:</label>
						<div class="col-lg-10">
						<input type="radio"  name="guardian" onclick="getguaidian('1')"  value="1"
						<?php if($getDetails['guardian']=='1') { echo "checked"; } ?>>Yes
						<input type="radio"  name="guardian" onclick="getguaidian('0')"  value="0" 
						<?php if($getDetails['guardian']=='0') { echo "checked"; } ?>>No
						</div>
						</div>

						<div class="form-group clearfix" id="no_of_guardi" style="display:
						<?php if($getDetails['guardian']=='0'){ echo"none";}else{ echo "block";}?>">
						<label class="col-lg-2 control-label " for="confirm">No Of Guardian:</label>
						<div class="col-lg-10">
						<input type="number" class="form-control"  name="no_of_guardian" id="no_of_guardian" value="<?php echo $getDetails['no_of_guardian']; ?>">
						</div>
						</div>

						<div class="form-group clearfix">
                      <label class="col-lg-2 control-label " for="userName">Payment Method:</label>
                      <div class="col-lg-10">
					  <?php $pmethod= explode(",",$getDetails['payment_method']); ?>
                      <input type="checkbox"name="payment[]" value="1"<?php if(in_array("1",$pmethod)){echo "checked";} ?>>Bank Payment
					  <input type="checkbox" name="payment[]"value="2"<?php if(in_array("2",$pmethod)){echo "checked";} ?>>Online
					  <input type="checkbox" name="payment[]" value="0"<?php if(in_array("0",$pmethod)){echo "checked";}?>>No Payment
                      </div>
                      </div>

						<div class="form-group clearfix">
						<label class="col-lg-2 control-label " for="userName">Form Prefix </label>
						<div class="col-lg-10">
						<input type="text" class="form-control"  name="formpre" value="<?php echo $getDetails['form_prefix']; ?>" >
						</div>
						</div>

						<div class="form-group clearfix">
						<label class="col-lg-2 control-label " for="userName">Form No Range </label>
						<div class="col-lg-10">
						<input type="text"  class="form-control" name="formrange" value="<?php echo $getDetails['form_no_range'];?>"/><br>
						</div>
						</div>

					  
						<div class="form-group clearfix">
						<label class="col-lg-2 control-label " for="confirm">Status :</label>
						<div class="col-lg-10">
						<select  class="required form-control" name="status">
						<option value="1" <?php if($getDetails['status']=='1') { echo "selected"; } ?>>Active</option>
						<option value="0" <?php if($getDetails['status']=='0') { echo "selected"; } ?>>Inactive</option>
						</select>
						</div>
						</div>

					 <button type="submit" name="update" class="btn btn-default">Submit</button>
					<a  href="<?php echo $FileName; ?>"  class="btn btn-default" >Back</a>
					</section> 
				</div>
				<?php  } 
				elseif($_GET['action']=='view') { 
				$iGetDet=$db->getRow("select * from  application_form where randomid='".$_GET['randomid']."'");
				$skoolsession=$db->getRow("select * from school_session where id='".$iGetDet['session']."'");
				$skoolsection=$db->getRow("select * from school_section where id='".$iGetDet['section']."'");
				?>
            <div class="card-box">
              <section>

               <div class="form-group clearfix">
                  <label class="col-lg-2 control-label " for="userName">Name :</label>
                  <?php echo $iGetDet['name']; ?>
				  </div>

                <div class="form-group clearfix">
                  <label class="col-lg-2 control-label " for="userName">Session :</label>
                  <?php echo $skoolsession['session']; ?> </div>
				  

                 <div class="form-group clearfix">
                  <label class="col-lg-2 control-label " for="userName">Section:</label>
                  <?php echo $skoolsection['section']; ?> </div>

                  <div class="form-group clearfix">
                  <label class="col-lg-2 control-label" for="userName">Start Date :</label>
                  <?php echo $iGetDet['start_date']; ?> </div>


                  <div class="form-group clearfix">
                  <label class="col-lg-2 control-label " for="userName">End date :</label>
                  <?php echo $iGetDet['end_date']; ?> </div>


                   <div class="form-group clearfix">
                  <label class="col-lg-2 control-label " for="userName">Fee :</label>
                  <?php echo $iGetDet['fee']; ?> </div>
				  
				  
				   <div class="form-group clearfix">
                  <label class="col-lg-2 control-label " for="userName">Guardian :</label>
                  <?php echo $iGetDet['guardian']; ?> </div>
				  
				  
				  
				   <div class="form-group clearfix">
                  <label class="col-lg-2 control-label " for="userName">No Of Guardian :</label>
                  <?php echo $iGetDet['no_of_guardian']; ?> </div>
				  
				  
				   <div class="form-group clearfix">
                  <label class="col-lg-2 control-label " for="userName">Payment Method :</label>
                  <?php
				  $pmethod= explode(",",$iGetDet['payment_method']);
				  if(in_array("1",$pmethod)){echo "Bank Payment,";}
				  if(in_array("2",$pmethod)){ echo "Online,";} 
				  if(in_array("0",$pmethod)){ echo "No Payment";}
				  ?>
				  </div>
				  
				  
				   <div class="form-group clearfix">
                  <label class="col-lg-2 control-label " for="userName">Form Prefix :</label>
                  <?php echo $iGetDet['form_prefix']; ?> </div>
				  
				  
				   <div class="form-group clearfix">
                  <label class="col-lg-2 control-label " for="userName">Form No Range:</label>
                  <?php echo $iGetDet['form_no_range']; ?> </div>
				  
				  
                  <div class="form-group clearfix">
                  <label class="col-lg-2 control-label " for="userName">Create At:</label>
                  <?php echo $iGetDet['create_at']; ?> </div>

				  
				   <div class="form-group clearfix">
                  <label class="col-lg-2 control-label " for="userName">Status:</label>
                  <?php if($_POST['iGetDet']=='0') { echo "Inactive"; } else{echo "Active"; } ?></div>

                
							    
                <a  href="<?php echo $FileName; ?>"  class="btn btn-default" >Back</a> </section>
            </div>
         
			  <?php } else { ?>
			  <div class="card-box">
            <table id="datatable" class="table table-striped table-bordered">
              <thead>
                <tr>			
                  <th>#</th>			
                  <th>Application name</th>
				  <th>Start Date</th>
				  <th>End Date</th>
				  <th>Form Fee</th>
				  <th>Status</th>
				  <th>Action</th>
                </tr>
              </thead>
              <tbody>
			   
				<?php
				$i=0;
				$aryList=$db->getRows("select * from application_form  order by id desc");
				foreach($aryList as $iList)
				{	
				$i++;
				$getAppSetting=$db->getRow("select * from  app_settings where app_id='".$iList['id']."'");
				?>
                <tr>
				<td><?php echo $i ?></td>
				<td><?php echo $iList['name']?></td>
				<td><?php echo $iList['start_date']; ?></td>
				<td><?php echo $iList['end_date']; ?></td>
				<td><?php echo $iList['fee']; ?></td>
				<td><?php if($iList['status']=='0'){echo "Inactive";} else{ echo "Active";}?></td>
                  <td>
				  <a href="<?php echo $FileName; ?>?action=view&randomid=<?php echo $iList['randomid']; ?>" class="table-action-btn">
				  <i class="fa fa-search"></i>
				  </a> 
                  <a href="<?php echo $FileName; ?>?action=edit&randomid=<?php echo $iList['randomid']; ?>"  class="table-action-btn" >
				  <i class="fa fa-pencil"></i> </a> 
                  <a href="javascript:del('<?php echo $FileName; ?>?action=delete&randomid=<?php echo $iList['randomid']; ?>')"class="table-action-btn" >
				   <i class="fa fa-times"></i> </a>
				   
				   <a href="<?php echo ADMIN_URL; ?>app_settings.php?randomid=<?php echo $iList['randomid']; ?>" class="table-action-btn">
				  <i class="fa fa-cog" aria-hidden="true"></i> 
				  |<?php if($getAppSetting['design_additional_qeus']=='1') { ?>
				  <a href="<?php echo ADMIN_URL; ?>custom_field.php?randomid=<?php echo $iList['randomid']; ?>" class="table-action-btn">
				Design Form</a> 
				  |
				  <?php } ?>
				  <a href="<?php echo ADMIN_URL; ?>form_preview.php?randomid=<?php echo $iList['randomid']; ?>" class="table-action-btn">
				  Preview Form|</a>
				  </td>
                </tr>
                <?php } ?>
              </tbody>
            </table>
          </div>
          <?php } ?>
        </div>
      </div>
    </div>
  </div>
  <script>
  function getguaidian(getid)
  {
	  if(getid==1)
	  {
		
		  document.getElementById("no_of_guardi").style="display:block";
	  }
	  else
	  {
		  document.getElementById("no_of_guardi").style="display:none";
		  document.getElementById("no_of_guardian").value="0";
	  }
	  
  }
  </script>
  <script>
  function getsection()
  {
  var ses_id= document.getElementById("session").value;
  $.post("ajax.php",
  {
	 action:"getsection",
     ses_id:ses_id,   	 
  },
  function(data)
  {  
	  document.getElementById('showsection').innerHTML=data;
  });
  }
</script> 
<script>
  function getclass()
  {
	
  var sec_id= document.getElementById("section").value;
 
  $.post("ajax.php",
  {
	 action:"getclass",
     sec_id:sec_id,   	 
  },
  function(data)
  { 
	  document.getElementById('showclass').innerHTML=data;
  });
  }
</script>
<script>
function getsubject()
  {
		var selected = new Array();
		var chks = document.getElementsByClassName("classList");
		for (var i = 0; i < chks.length; i++)
		{
		if (chks[i].checked) {
		selected.push(chks[i].value);
		}
		}

		var class_iid=selected.join(",");

		$.post("ajax.php",
		{
		action:"getsubject",
		class_iid:class_iid,   	 
		},
		function(data)
		{ 
		
		document.getElementById('showsubject').innerHTML=data;
		});


};
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
  <?php include('inc.footer.php'); ?>
</div>
</div>
<?php include('inc.js.php'); ?>
</body>
</html>