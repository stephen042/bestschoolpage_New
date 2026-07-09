<?php
include('../config.php');
include('inc.session-create.php'); 
$validate = new validation();
$pageTitle ='Package';
$Filename ='package.php';
if (isset($_POST['submit']))
  {
    $date=date('Y-m-d H:m:s');
    $validate->addRule($_POST['title'],'alpha','Title',true);
   /* $validate->addRule($_POST['price'],'','price',true);
    $validate->addRule($_POST['no_of_days'],'','No of days',true);
    $validate->addRule($_POST['create_custom_forms'],'','Username',true);
    $validate->addRule($_POST['online_and_bank_payment'],'','Password',true);
    $validate->addRule($_POST['dashboard'],'','Location',true);
    $validate->addRule($_POST['exam_feature'],'','About',true);
    $validate->addRule($_POST['sms_alert'],'','Website Name',true);
    $validate->addRule($_POST['email_notification'],'','Status',true);
    $validate->addRule($_POST['document_upload'],'','Status',true);
    $validate->addRule($_POST['sms_campaigns'],'','Status',true);
    $validate->addRule($_POST['email_campaigns'],'','Status',true);
    $validate->addRule($_POST['report_and_data_export'],'','Status',true);
    $validate->addRule($_POST['attendance_module'],'','Status',true);*/
 

    if($validate->validate() && count($stat) == 0)
				{
          $aryData = array(
                          'title'                    => $_POST['title'],
                          'price_term'               => $_POST['price_term'],
                          'days_term'                => $_POST['days_term'],
						  'price_yearly'             => $_POST['price_yearly'],
                          'days_yearly'              => $_POST['days_yearly'],
                          'create_custom_forms'      => $_POST['create_custom_forms'],
                          'report_templates'         => $_POST['report_templates'],
                          'online_and_bank_payment'  => $_POST['online_and_bank_payment'],
                          'dashboard'                => $_POST['dashboard'],
                          'exam_feature'             => $_POST['exam_feature'],
                          'sms_alert'                => $_POST['sms_alert'],
                          'email_notification'       => $_POST['email_notification'],
                          'document_upload'          => $_POST['document_upload'],
                          'sms_campaigns'            => $_POST['sms_campaigns'],
                          'email_campaigns'          => $_POST['email_campaigns'],
                          'report_and_data_export'   => $_POST['report_and_data_export'],
                          'attendance_module'        => $_POST['attendance_module'],
                          'status'                   => 1,
                          'create_at'                => date('Y-m-d H:m:s'),
                        );
                        $flgIn2 = $db->insertAry("package",$aryData);
                        $stat['success']="Submited successfully";
                        unset($_POST);
						            redirect('package.php');
                      }
                      else {$stat['error'] = $validate->errors();}
          }

      // Update Details for The User// 
	     elseif(isset($_POST['update']))
        {
          $validate->addRule($_POST['title'],'alpha','Title',true);
         /* $validate->addRule($_POST['price'],'','price',true);
           $validate->addRule($_POST['no_of_days'],'','No of days',true);
          $validate->addRule($_POST['create_custom_forms'],'','Username',true);
          $validate->addRule($_POST['online_and_bank_payment'],'','Password',true);
          $validate->addRule($_POST['dashboard'],'','Location',true);
          $validate->addRule($_POST['exam_feature'],'','About',true);
          $validate->addRule($_POST['sms_alert'],'','Website Name',true);
          $validate->addRule($_POST['email_notification'],'','Status',true);
          $validate->addRule($_POST['document_upload'],'','Status',true);
          $validate->addRule($_POST['sms_campaigns'],'','Status',true);
          $validate->addRule($_POST['email_campaigns'],'','Status',true);
          $validate->addRule($_POST['report_and_data_export'],'','Status',true);
          $validate->addRule($_POST['attendance_module'],'','Status',true);*/
      

          if($validate->validate() && count($stat) == 0)
            {
              $aryData = array(
                          'title'                    => $_POST['title'],
                          'price_term'               => $_POST['price_term'],
                          'days_term'                => $_POST['days_term'],
						  'price_yearly'             => $_POST['price_yearly'],
                          'days_yearly'              => $_POST['days_yearly'],
                          'create_custom_forms'      => $_POST['create_custom_forms'],
                          'report_templates'         => $_POST['report_templates'],
                          'online_and_bank_payment'  => $_POST['online_and_bank_payment'],
                          'dashboard'                => $_POST['dashboard'],
                          'exam_feature'             => $_POST['exam_feature'],
                          'sms_alert'                => $_POST['sms_alert'],
                          'email_notification'       => $_POST['email_notification'],
                          'document_upload'          => $_POST['document_upload'],
                          'sms_campaigns'            => $_POST['sms_campaigns'],
                          'email_campaigns'          => $_POST['email_campaigns'],
                          'report_and_data_export'   => $_POST['report_and_data_export'],
                          'attendance_module'        => $_POST['attendance_module'],
                          'status'                   => 1,
                        );
              $flgIn1 = $db->updateAry("package", $aryData, "where id='".$_GET['id']."' ");
				      $Stat['success']="Update Successfully";
				      unset($_POST);
				      redirect('package.php');
            }
            else {$stat['error'] = $validate->errors();}
          }
    elseif(($_REQUEST['action']=='delete'))
      {
        $flgIn1 = $db->delete("package","where id='".$_GET['id']."' ");			
			  $stat['success'] = 'Deleted Successfully';
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
            <h4 class="page-title"><?php echo $pageTitle; ?></h4>
            <ol class="breadcrumb">
              <li> <a href="<?php echo $Filename;?>">Home</a> </li>
              <li class="active"><?php echo $pageTitle;?></li>
            </ol>
          </div>
        </div>
        
        <!-- Basic Form Wizard -->
        
        <div class="row">
          <div class="col-md-12">
            <div class="card-box aplhanewclass">
              <div class="row">
                <div class="col-md-9"> <?php echo msg($stat);?> </div>
                <div class="col-md-3"> <a href="<?php echo $Filename;?>?action=add" class="btn btn-default" style="float:right">Add New Record</a> </div>
              </div>
            </div>
            
            <!-- add section start -->
            <?php if($_GET['action']=='add') { ?>
            <div class="card-box">
              <form action="" method="POST" enctype="multipart/form-data"/>
              
              <div>
                <section>
                  <div class="form-group clearfix">
                    <label class="col-lg-2 control-label" for="title"> Title: </label>
                    <div class="col-lg-10">
                      <input type="text" name="title" class="form-control" id="title" value="<?php echo $_POST['title'];?>">
                    </div>
                  </div>
                  <div class="form-group clearfix">
                    <label class="col-lg-2 control-label" for="price"> Price  (In Terms): </label>
                    <div class="col-lg-10">
                      <input type="text" name="price_term" class="form-control"  value="<?php echo $_POST['price_term'];?>">
                    </div>
                  </div>
                  <div class="form-group clearfix">
                    <label class="col-lg-2 control-label" for="no_of_days"> Number of days (In Terms): </label>
                    <div class="col-lg-10">
                      <input type="text" name="days_term" class="form-control"   value="<?php echo $_POST['days_term'];?>">
                    </div>
                  </div>
                  
                    <div class="form-group clearfix">
                    <label class="col-lg-2 control-label" for="price"> Price  (In Yearly): </label>
                    <div class="col-lg-10">
                      <input type="text" name="price_yearly" class="form-control"  value="<?php echo $_POST['price_yearly'];?>">
                    </div>
                  </div>
                  <div class="form-group clearfix">
                    <label class="col-lg-2 control-label" for="no_of_days"> Number of days (In Yearly): </label>
                    <div class="col-lg-10">
                      <input type="text" name="days_yearly" class="form-control"   value="<?php echo $_POST['days_yearly'];?>">
                    </div>
                  </div>
                  
                  
                  
                  <div class="form-group clearfix">
                    <label class="col-lg-2 control-label" for="create_custom_forms"> Create custom forms: </label>
                    <div class="col-lg-10"> Yes:
                      <input type="radio" name="create_custom_forms"  id="create_custom_forms" value="1" checked>
                      No:
                      <input type="radio" name="create_custom_forms"   id="create_custom_forms" value="0"  >
                    </div>
                  </div>
                  <div class="form-group clearfix">
                    <label class="col-lg-2 control-label" for="report_templates"> Report Templates: </label>
                    <div class="col-lg-10"> Yes:
                      <input type="radio" name="report_templates"  id="report_templates" value="1" checked>
                      No:
                      <input type="radio" name="report_templates"   id="report_templates" value="0"  >
                    </div>
                  </div>
                  <div class="form-group clearfix">
                    <label class="col-lg-2 control-label" for="online_and_bank_payment"> Online Bank Payment: </label>
                    <div class="col-lg-10"> Yes:
                      <input type="radio" name="online_and_bank_payment" id="online_and_bank_payment" value="1" checked>
                      No:
                      <input type="radio" name="online_and_bank_payment"   id="online_and_bank_payment" value="0"  >
                    </div>
                  </div>
                  <div class="form-group clearfix">
                    <label class="col-lg-2 control-label" for="dashboard"> Dashboard: </label>
                    <div class="col-lg-10"> Yes:
                      <input type="radio" name="dashboard"   id="dashboard" value="1" checked>
                      No:
                      <input type="radio" name="dashboard"  id="dashboard" value="0"  >
                    </div>
                  </div>
                  <div class="form-group clearfix">
                    <label class="col-lg-2 control-label" for="exam_feature"> Exam Feature: </label>
                    <div class="col-lg-10"> Yes:
                      <input type="radio" name="exam_feature"   id="dashboard" value="1" checked>
                      No:
                      <input type="radio" name="exam_feature"   id="dashboard" value="0"  >
                    </div>
                  </div>
                  <div class="form-group clearfix">
                    <label class="col-lg-2 control-label" for="sms_alert"> Sms alert: </label>
                    <div class="col-lg-10"> Yes:
                      <input type="radio" name="sms_alert"   id="dashboard" value="1" checked>
                      No:
                      <input type="radio" name="sms_alert"   id="dashboard" value="0"  >
                    </div>
                  </div>
                  <div class="form-group clearfix">
                    <label class="col-lg-2 control-label" for="email_notification"> Email Notification: </label>
                    <div class="col-lg-10"> Yes:
                      <input type="radio" name="email_notification"   id="dashboard" value="1" checked>
                      No:
                      <input type="radio" name="email_notification"   id="dashboard" value="0"  >
                    </div>
                  </div>
                  <div class="form-group clearfix">
                    <label class="col-lg-2 control-label" for="document_upload"> Document Upload: </label>
                    <div class="col-lg-10"> Yes:
                      <input type="radio" name="document_upload"   id="dashboard" value="1" checked>
                      No:
                      <input type="radio" name="document_upload"   id="dashboard" value="0"  >
                    </div>
                  </div>
                  
                  <!--    <div class="form-group clearfix">
                            <label class="col-lg-2 control-label" for="sms_campaigns"> Sms campaigns: </label>
                            <div class="col-lg-10">
                             
    Yes: <input type="radio" name="sms_campaigns"   id="dashboard" value="1">
                             No: <input type="radio" name="sms_campaigns"   id="dashboard" value="0"  >
                             </div>
                          </div>

                          <div class="form-group clearfix">
                            <label class="col-lg-2 control-label" for="email_campaigns"> Email Campaigns: </label>
                            <div class="col-lg-10">
                            
 Yes: <input type="radio" name="email_campaigns"   id="dashboard" value="1">
                             No: <input type="radio" name="email_campaigns"   id="dashboard" value="0"  >
                             
						   </div>
                          </div>

                     <div class="form-group clearfix">
                            <label class="col-lg-2 control-label" for="report_and_data_export"> Report and data export: </label>
                            <div class="col-lg-10"> 
							 <input type="text" name="report_and_data_export"   id="dashboard" value="<?php echo $_POST['report_and_data_export'];?>">
                             
               
							</div>
                          </div

                          <div class="form-group clearfix">
                            <label class="col-lg-2 control-label" for="attendance_module"> Attendance Module: </label>
                            <div class="col-lg-10">
Yes: <input type="radio" name="attendance_module"  id="dashboard" value="1">
                             No: <input type="radio" name="attendance_module"   id="dashboard" value="0"  >
                                          
                             </div>
                          </div> >
                  
                  <div class="form-group clearfix">
                    <label class="col-lg-2 control-label " for="confirm">Status: </label>
                    <div class="col-lg-10">
                      <select  class=" form-control" name="status">
           
                        <option name="status" value="1" <?php if($_POST['status']=='1') { echo "active" ; } else { echo "Inactive" ; } ?>>Active</option>
                        <option name="status" value="0" <?php if($_POST['status']=='0') { echo "active" ; } else { echo "Inactive" ; } ?>>Inactive</option>
                      </select>
                    </div>
                  </div>-->
                  <button type="submit" name="submit" class="btn btn-default">Submit</button>
                  <a href="<?php echo $Filename; ?>" class="btn btn-default">Back</a> </section>
              </div>
              </form>
            </div>
            <?php }

                            // Update User for admin //
            elseif($_GET['action']=='edit') { 
			       $aryDetail=$db->getRow("select * from package where id='".$_GET['id']."'");	
					     ?>
            <div class="card-box">
              <form role="form" action="" method="post" enctype="multipart/form-data">
                <div>
                  <section>
                    <div class="form-group clearfix">
                      <label class="col-lg-2 control-label" for="title"> Title: </label>
                      <div class="col-lg-10">
                        <input type="text" name="title" class="form-control" id="title" value="<?php echo $aryDetail['title'];?>">
                      </div>
                    </div>
                     
                     
                     
                     
                     
                 <div class="form-group clearfix">
                    <label class="col-lg-2 control-label" for="price"> Price  (In Terms): </label>
                    <div class="col-lg-10">
                      <input type="text" name="price_term" class="form-control"  value="<?php echo $aryDetail['price_term'];?>">
                    </div>
                  </div>
                  <div class="form-group clearfix">
                    <label class="col-lg-2 control-label" for="no_of_days"> Number of days (In Terms): </label>
                    <div class="col-lg-10">
                      <input type="text" name="days_term" class="form-control"   value="<?php echo $aryDetail['days_term'];?>">
                    </div>
                  </div>
                  
                    <div class="form-group clearfix">
                    <label class="col-lg-2 control-label" for="price"> Price  (In Yearly): </label>
                    <div class="col-lg-10">
                      <input type="text" name="price_yearly" class="form-control"  value="<?php echo $aryDetail['price_yearly'];?>">
                    </div>
                  </div>
                  <div class="form-group clearfix">
                    <label class="col-lg-2 control-label" for="no_of_days"> Number of days (In Yearly): </label>
                    <div class="col-lg-10">
                      <input type="text" name="days_yearly" class="form-control"   value="<?php echo $aryDetail['days_yearly'];?>">
                    </div>
                  </div>      
                     
                     
                     
                     
                     
                     
                     
                     
                     
                     
                     
                     
                     
                     
                     
                     
                    <div class="form-group clearfix">
                      <label class="col-lg-2 control-label" for="create_custom_forms"> Create custom forms: </label>
                      <div class="col-lg-10"> Yes:
                        <input type="radio" name="create_custom_forms"   id="create_custom_forms" value="1" <?php if($aryDetail['create_custom_forms']=='1'){echo  "checked";}?>>
                        No:
                        <input type="radio" name="create_custom_forms"  id="create_custom_forms" value="0" <?php if($aryDetail['create_custom_forms']=='0'){echo  "checked";}?>  >
                      </div>
                    </div>
                    <div class="form-group clearfix">
                      <label class="col-lg-2 control-label" for="report_templates"> Report Templates: </label>
                      <div class="col-lg-10"> Yes:
                        <input type="radio" name="report_templates"  id="report_templates" value="1" <?php if($aryDetail['report_templates']=='1'){echo  "checked";}?>>
                        No:
                        <input type="radio" name="report_templates"   id="report_templates" value="0"  <?php if($aryDetail['report_templates']=='0'){echo  "checked";}?>>
                      </div>
                    </div>
                    <div class="form-group clearfix">
                      <label class="col-lg-2 control-label" for="online_and_bank_payment"> Online Bank Payment: </label>
                      <div class="col-lg-10"> Yes:
                        <input type="radio" name="online_and_bank_payment"   id="create_custom_forms" value="1" <?php if($aryDetail['online_and_bank_payment']=='1'){echo  "checked";}?>>
                        No:
                        <input type="radio" name="online_and_bank_payment"   id="create_custom_forms" value="0" <?php if($aryDetail['online_and_bank_payment']=='0'){echo  "checked";}?>  >
                      </div>
                    </div>
                    <div class="form-group clearfix">
                      <label class="col-lg-2 control-label" for="dashboard"> Dashboard: </label>
                      <div class="col-lg-10"> Yes:
                        <input type="radio" name="dashboard"  id="create_custom_forms" value="1" <?php if($aryDetail['dashboard']=='1'){echo  "checked";}?>>
                        No:
                        <input type="radio" name="dashboard"   id="create_custom_forms" value="0" <?php if($aryDetail['dashboard']=='0'){echo  "checked";}?>  >
                      </div>
                    </div>
                    <div class="form-group clearfix">
                      <label class="col-lg-2 control-label" for="exam_feature"> Exam Feature: </label>
                      <div class="col-lg-10"> Yes:
                        <input type="radio" name="exam_feature" id="create_custom_forms" value="1" <?php if($aryDetail['exam_feature']=='1'){echo  "checked";}?>>
                        No:
                        <input type="radio" name="exam_feature"  id="create_custom_forms" value="0" <?php if($aryDetail['exam_feature']=='0'){echo  "checked";}?>  >
                      </div>
                    </div>
                    <div class="form-group clearfix">
                      <label class="col-lg-2 control-label" for="sms_alert"> Sms alert: </label>
                      <div class="col-lg-10"> Yes:
                        <input type="radio" name="sms_alert"  id="create_custom_forms" value="1" checked <?php if($aryDetail['sms_alert']=='1'){echo  "checked";}?>>
                        No:
                        <input type="radio" name="sms_alert"   id="create_custom_forms" value="0" <?php if($aryDetail['sms_alert']=='0'){echo  "checked";}?>  >
                      </div>
                    </div>
                    <div class="form-group clearfix">
                      <label class="col-lg-2 control-label" for="email_notification"> Email Notification: </label>
                      <div class="col-lg-10"> Yes:
                        <input type="radio" name="email_notification"  id="create_custom_forms" value="1" <?php if($aryDetail['email_notification']=='1'){echo  "checked";}?>>
                        No:
                        <input type="radio" name="email_notification"  id="create_custom_forms" value="0" <?php if($aryDetail['email_notification']=='0'){echo  "checked";}?>  >
                      </div>
                    </div>
                    <div class="form-group clearfix">
                      <label class="col-lg-2 control-label" for="document_upload"> Document Upload: </label>
                      <div class="col-lg-10"> Yes:
                        <input type="radio" name="document_upload"  id="create_custom_forms" value="1" <?php if($aryDetail['document_upload']=='1'){echo  "checked";}?>>
                        No:
                        <input type="radio" name="document_upload"  id="create_custom_forms" value="0" <?php if($aryDetail['document_upload']=='0'){echo  "checked";}?>  >
                      </div>
                    </div>
                    
                    <!--   <div class="form-group clearfix">
                            <label class="col-lg-2 control-label" for="sms_campaigns"> Sms Campaigns: </label>
                            <div class="col-lg-10">
                             
 
							   Yes: <input type="radio" name="sms_campaigns"  id="create_custom_forms" value="1" <?php if($aryDetail['sms_campaigns']=='1'){echo  "checked";}?>>
                             No: <input type="radio" name="sms_campaigns"   id="create_custom_forms" value="0" <?php if($aryDetail['sms_campaigns']=='0'){echo  "checked";}?>  >
       

                             </div>
                          </div>

                          <div class="form-group clearfix">
                            <label class="col-lg-2 control-label" for="email_campaigns"> Email Campaigns: </label>
                            <div class="col-lg-10">
 
							   Yes: <input type="radio" name="email_campaigns"   id="create_custom_forms" value="1" <?php if($aryDetail['email_campaigns']=='1'){echo  "checked";}?>>
                             No: <input type="radio" name="email_campaigns"  id="create_custom_forms" value="0" <?php if($aryDetail['email_campaigns']=='0'){echo  "checked";}?>  >
                                   

                             </div>
                          </div>

                          <div class="form-group clearfix">
                            <label class="col-lg-2 control-label" for="report_and_data_export"> Report and data export: </label>
                            <div class="col-lg-10">
                              
							   
							   Yes: <input type="radio" name="report_and_data_export"  id="create_custom_forms" value="1" <?php if($aryDetail['report_and_data_export']=='1'){echo  "checked";}?>>
                             No: <input type="radio" name="report_and_data_export"  id="create_custom_forms" value="0" <?php if($aryDetail['report_and_data_export']=='0'){echo  "checked";}?>  >
       
                             </div>
                          </div>

                          <div class="form-group clearfix">
                            <label class="col-lg-2 control-label" for="attendance_module"> Attendance Module: </label>
                            <div class="col-lg-10">
                               
							   Yes: <input type="radio" name="attendance_module"   id="create_custom_forms" value="1" <?php if($aryDetail['attendance_module']=='1'){echo  "checked";}?>>
                             No: <input type="radio" name="attendance_module"   id="create_custom_forms" value="0" <?php if($aryDetail['attendance_module']=='0'){echo  "checked";}?>  >
       
                             </div>
                          </div> 
                    <div class="form-group clearfix">
                      <label class="col-lg-2 control-label " for="confirm">Status: </label>
                      <div class="col-lg-10">
                        <select  class=" form-control" name="status">
                        
                          <option name="status" value="1" <?php if( $aryDetail['status']=='1') { echo "selected" ; }?>>Active</option>
                          <option name="status" value="0" <?php if( $aryDetail['status']=='0') { echo "selected" ; }?>>Inactive</option>
                        </select>
                      </div>
                    </div>-->
                    <button type="submit" name="update" class="btn btn-default">Submit</button>
                    <a href="<?php echo $Filename;?>"  class="btn btn-default" >Back</a> </section>
                </div>
              </form>
            </div>
            <?php }

                    // View User for admin // 
	             elseif($_GET['action']=='view') {
                $GetViewId=$db->getRow("select * from package where id='".$_GET['id']."'");
	               ?>
            <div class="card-box">
              <form method="POST" action="" role="form" enctype="multipart/form-data">
                <div>
                  <section>
                    <div class="form-group clearfix">
                      <label class="col-lg-2 control-label" for="title"> Title </label>
                      <div class="col-lg-10"> <?php echo $GetViewId['title'];?> </div>
                    </div>
                     
                      <div class="form-group clearfix">
                    <label class="col-lg-2 control-label" for="price"> Price  (In Terms): </label>
                    <div class="col-lg-10">
                    <?php echo $GetViewId['price_term'];?>
                    </div>
                  </div>
                  <div class="form-group clearfix">
                    <label class="col-lg-2 control-label" for="no_of_days"> Number of days (In Terms): </label>
                    <div class="col-lg-10">
                    <?php echo $GetViewId['days_term'];?>
                    </div>
                  </div>
                  
                    <div class="form-group clearfix">
                    <label class="col-lg-2 control-label" for="price"> Price  (In Yearly): </label>
                    <div class="col-lg-10">
                      <?php echo $GetViewId['price_yearly'];?>
                    </div>
                  </div>
                  <div class="form-group clearfix">
                    <label class="col-lg-2 control-label" for="no_of_days"> Number of days (In Yearly): </label>
                    <div class="col-lg-10">
                   <?php echo $GetViewId['days_yearly'];?>
                    </div>
                  </div>      
                  
                  
                  
                  
                  
                  
                    <div class="form-group clearfix">
                      <label class="col-lg-2 control-label" for="create_custom_forms"> Create custom forms </label>
                      <div class="col-lg-10">&nbsp;
                        <?php if($GetViewId['create_custom_forms']=='1'){echo "Yes";} else {echo "No";}?>
                      </div>
                    </div>
                    <div class="form-group clearfix">
                      <label class="col-lg-2 control-label" for="report_templates"> Report Templates </label>
                      <div class="col-lg-10">
                        <?php if($GetViewId['report_templates']=='1'){echo "Yes";} else {echo "No";}?>
                      </div>
                    </div>
                    <div class="form-group clearfix">
                      <label class="col-lg-2 control-label" for="online_and_bank_payment"> Online Bank Payment </label>
                      <div class="col-lg-10">
                        <?php if($GetViewId['online_and_bank_payment']=='1'){echo "Yes";} else {echo "No";}?>
                      </div>
                    </div>
                    <div class="form-group clearfix">
                      <label class="col-lg-2 control-label" for="dashboard"> Dashboard </label>
                      <div class="col-lg-10">
                        <?php if($GetViewId['dashboard']=='1'){echo "Yes";} else {echo "No";}?>
                      </div>
                    </div>
                    <div class="form-group clearfix">
                      <label class="col-lg-2 control-label" for="exam_feature"> Exam Feature </label>
                      <div class="col-lg-10">
                        <?php if($GetViewId['exam_feature']=='1'){echo "Yes";} else {echo "No";}?>
                      </div>
                    </div>
                    <div class="form-group clearfix">
                      <label class="col-lg-2 control-label" for="sms_alert"> SMS Alert </label>
                      <div class="col-lg-10">
                        <?php if($GetViewId['sms_alert']=='1'){echo "Yes";} else {echo "No";}?>
                      </div>
                    </div>
                    <div class="form-group clearfix">
                      <label class="col-lg-2 control-label" for="email_notification"> Email Notification </label>
                      <div class="col-lg-10">
                        <?php if($GetViewId['email_notification']=='1'){echo "Yes";} else {echo "No";}?>
                      </div>
                    </div>
                    <div class="form-group clearfix">
                      <label class="col-lg-2 control-label" for="document_upload"> Document Upload </label>
                      <div class="col-lg-10">
                        <?php if($GetViewId['document_upload']=='1'){echo "Yes";} else {echo "No";}?>
                      </div>
                    </div>
                   <!-- <div class="form-group clearfix">
                      <label class="col-lg-2 control-label" for="sms_campaigns"> sms_campaigns </label>
                      <div class="col-lg-10">
                        <?php if($GetViewId['sms_campaigns']=='1'){echo "Yes";} else {echo "No";}?>
                      </div>
                    </div>
                    <div class="form-group clearfix">
                      <label class="col-lg-2 control-label" for="email_campaigns"> Email Campaigns </label>
                      <div class="col-lg-10">
                        <?php if($GetViewId['email_campaigns']=='1'){echo "Yes";} else {echo "No";}?>
                      </div>
                    </div>
                    
                      <div class="form-group clearfix">
                            <label class="col-lg-2 control-label" for="report_and_data_export"> Report and data export </label>
                            <div class="col-lg-10">
                              <?php echo $GetViewId['report_and_data_export'];?>
							   
                            </div>
                          </div>
                    
                    <div class="form-group clearfix">
                      <label class="col-lg-2 control-label" for="attendance_module"> Attendance Module </label>
                      <div class="col-lg-10"> <?php echo $GetViewId['attendance_module'];?> </div>
                    </div>-->
                    <a href="package.php" class="btn btn-default" >Back</a> </section>
                </div>
              </form>
            </div>
            <?php } else{?>
            <div class="card-box">
              <table id="datatable" class="table table-striped table-bordered">
                <thead>
                  <tr>
                    <th>#</th>
                    <th>Title</th>
                    <th>Price(In Terms)</th>
                    <th>No of days (In Terms)</th>
                    <th>Price(In Yearly)</th>
                    <th>No of days (In Yearly)</th>
                    
                   
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody>
                  <?php
                          $aryList=$db->getRows("select * from package order by id desc");
                          foreach($aryList as $iList)
                            {
                              $i=$i+1;
                              $aryPgAct["id"]=$iList['id'];
                              $aryState=$db->getRow("select * from state where id='".$iList['state']."' ");
                              ?>
                  <tr>
                    <td><?php echo $i ?></td>
                    <td><?php echo $iList['title'];?></td>
                    <td><?php echo $iList['price_term'];?></td>
                    <td><?php echo $iList['days_term'];?></td>
                    <td><?php echo $iList['price_yearly'];?></td>
                    <td><?php echo $iList['days_yearly'];?></td>
                    
                   
                    <td><a href="<?php echo $Filename;?>?action=view&id=<?php echo $iList['id']; ?>" class="table-action-btn"> <i class="fa fa-search"></i></a> <a href="<?php echo $Filename;?>?action=edit&id=<?php echo $iList['id']; ?>"  class="table-action-btn" > <i class="fa fa-pencil"></i> </a> <a href="javascript:del('<?php echo $Filename;?>?action=delete&id=<?php echo $iList['id']; ?>')"    class="table-action-btn" > <i class="fa fa-times"></i> </a></td>
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
  </div>
</div>
</div>
</div>
<?php include('inc.js.php'); ?>
<?php include('inc.footer.php'); ?>
</body>
</html>
