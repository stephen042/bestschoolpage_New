<?php
include('../config.php');
$validate = new validation();
$pageTitle = 'Update Parents Phone-Emails';
$Filename = 'update_parent_phone_emails.php';

if (isset($_POST['update'])) {

		foreach($_POST['sid'] as $key=>$val)
		{
            $aryData = array(
			
			                 'mobile'                  => $_POST['student_phone'][$key],
                             );
				   
               $flgIn2 = $db->updateAry("manage_student", $aryData, "where id='".$_POST['sid'][$key]."'");
			   //echo $flgIn2 = $db->getLastQuery();
		}
		
		
		foreach($_POST['fid'] as $key=>$val)
		{
			  
				$aryData = array(
			
                             'phone'                   => $_POST['father_phone'][$key],
                             'email'                   => $_POST['father_email'][$key],
							
                            );
				 
               $flgIn2 = $db->updateAry("student_father", $aryData, "where id='".$_POST['fid'][$key]."'");


			   }
			
			
		foreach($_POST['mid'] as $key=>$val)
		{
			   
				$aryData = array(
			
                             'phone'                   => $_POST['mother_phone'][$key],
                             'email'                   => $_POST['mother_email'][$key],
                            );
		
				   
               $flgIn2 = $db->updateAry("student_mother", $aryData, "where id='".$_POST['mid'][$key]."'");
						
					
			   
		}

			
				   foreach($_POST['pid'] as $key=>$val)
		{
				$aryData = array(
			
                             'phone'                   => $_POST['guardian_phone'][$key],
                             'email'                   => $_POST['guardian_email'][$key],
                            );

               $flgIn2 = $db->updateAry("student_guardian", $aryData, "where id='".$_POST['pid'][$key]."'");
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
                            <li><a href="<?php echo $PageTitle; ?>">Home</a></li>
                            <li class="active"><?php echo $pageTitle; ?></li>
                        </ol>
                    </div>
                </div>

                <!-- Basic Form Wizard -->

                <div class="row">
                    <div class="col-md-12">
                       

                        <!-- add section start -->
             
                    <form action="" method="post">
                            <div class="card-box">
                                <table id="datatable" class="table table-striped table-bordered">
                                    <thead>
                                    <tr>
                                        <th>#</th>
										
                                        <th>Student Id</th>
										<th>Student Name</th>
                                        <th>Student's Phone</th>
                                        <th>Father's Phone</th>
										<th>Father's Email</th>
										<th>Mother's Phone</th>
										<th>Mother's Email</th>
										<th>Guardian's Phone</th>
										<th>Guardian's Email</th>
                                        
                                        
                                    </tr>
                                    </thead>
                                    <tbody>
									
                                    <?php 
									
                                    $aryList = $db->getRows("select * from manage_student where create_by_userid='".$create_by_userid."'");
									
                                    foreach ($aryList as $iList) {
                                        $i = $i + 1;
                                        $aryPgAct["id"] = $iList['id'];
                                        ?>
                                        <tr>
                                            <td><?php echo $i ?></td>
											<input  name="sid[]" type="hidden" value="<?php echo $iList['id']; ?>"  >
											 <td><?php echo $iList['student_id']; ?></td>
                                            <td><?php echo $iList['first_name'].' '.$iList['last_name']; ?></td>
											
                                            <td><input class="form-control" type="num" name="student_phone[]" value="<?php echo $iList['mobile']; ?>" ></td>
											<input  name="fid[]" type="hidden" value="<?php echo $iList['id']; ?>"  >
																					
											<td><input type="text" class="form-control" placeholder="no phone" name="father_phone[]" value="<?php echo $db->getVal("select phone from student_father where student_id='".$iList['id']."'"); ?>" ></td>
											
                                          <td><input type="text" class="form-control" class="form-control" placeholder="no email" name="father_email[]" value="<?php echo $db->getVal("select email from student_father where student_id='".$iList['id']."'"); ?>" ></td>
										   
										   <input  name="mid[]" type="hidden" value="<?php echo $iList['id']; ?>"  >
										  <td><input type="text" class="form-control"  placeholder="no phone" name="mother_phone[]" value="<?php echo $db->getVal("select phone from student_mother where student_id='".$iList['id']."'"); ?>" ></td>
										   
										  <td><input type="text" class="form-control" placeholder="no email" name="mother_email[]" value="<?php echo $db->getVal("select email from student_mother where student_id='".$iList['id']."'"); ?>" ></td>
										   
										   <input  name="pid[]" type="hidden" value="<?php echo $iList['id']; ?>"  >
										 <td><input type="text" class="form-control"  placeholder="no phone" name="guardian_phone[]" value="<?php echo $db->getVal("select phone from student_guardian where student_id='".$iList['id']."'"); ?>" ></td>
										 
										    <td><input type="text" class="form-control"  placeholder="no email" name="guardian_email[]" value="<?php echo $db->getVal("select email from student_guardian where student_id ='".$iList['id']."'"); ?>" ></td>
											
											
                                        </tr>
                                    <?php } ?>
									
                                    </tbody>
                                </table>
                            </div>
							
							 
							  <div class="savdtls" align="right"><button type="submit" name="update" class="btn btn-default">Save details</button></div>
					</form>
							
							
												
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
