<?php
include('../config.php');
$validate = new validation();
$pageTitle = 'Assess Subject trait';
$Filename = 'assess_subject_trait.php';
if (isset($_POST['register'])) {


    $validate->addRule($_POST['last_name'],'', 'Last Name', true);
    $validate->addRule($_POST['first_name'],'', 'First Name', true);
    
    $validate->addRule($_POST['other_name'], '','Other Name', true);
     
  

    if ($validate->validate() && count($stat) == 0) {
        $classid = $db->getRow("select * from class_member where class_id='" . $_POST['class_id'] . "' ");
        if ($classid['id'] == '') {
            //echo $_POST[session];
			//exit;
           
            $aryData = array(
			
			                 'session'                   => $_POST['session'],
                             'student_id'                => $_POST['student_id'],
                             'last_name'                 => $_POST['last_name'],
                             
							 'first_name'                => $_POST['first_name'],
                            'other_name'                 => $_POST['other_name'],
                             
							 'randomid'                  => randomFix(15),
                             'create_at'                 => date('Y-m-d H:m:s'),
               
            );
            $flgIn2 = $db->insertAry("assess_subject_trait", $aryData);
			//echo $flgIn2 = $db->getLastQuery();
			//exit;
			
			
            $stat['success'] = "Submited successfully";
            unset($_POST);
            redirect($Filename);
        } else {
            $stat['error'] = "Email are alerady registerd.";
        }
    } 
	
	else {
        $stat['error'] = $validate->errors();
    }
}

elseif (isset($_POST['edit'])) {


    $validate->addRule($_POST['last_name'],'', 'Last Name', true);
    $validate->addRule($_POST['first_name'],'', 'First Name', true);
    
    $validate->addRule($_POST['other_name'], '','Other Name', true);
     
  

    if ($validate->validate() && count($stat) == 0) {
        $classid = $db->getRow("select * from class_member where class_id='". $_POST['class_id'] . "' && randomid!='". $_GET['randomid']."'");
        if ($classid['id'] == '') {
            
           
            $aryData = array(
			
			                 'session'                  => $_POST['session'],
                             'student_id'                => $_POST['student_id'],
                             'last_name'                 => $_POST['last_name'],
                             
							 'first_name'                => $_POST['first_name'],
                            'other_name'                 => $_POST['other_name'],
                             
							 'randomid'                  => randomFix(15),
                             'create_at'                 => date('Y-m-d H:m:s'),
                             
               
            );
            $flgIn2 = $db->updateAry("assess_subject_trait", $aryData);
			echo $flgIn2 = $db->getLastQuery();
			exit;
			
			
            $stat['success'] = "Submited successfully";
            unset($_POST);
            redirect($Filename);
        } else {
            $stat['error'] = "Class ID are alerady registerd.";
        }
    } 
	
	else {
        $stat['error'] = $validate->errors();
    }
}


elseif (($_REQUEST['action'] == 'delete')) {
    $flgIn1 = $db->delete("class_member", "where randomid='" . $_GET['randomid'] . "' ");
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
                        <div class="card-box aplhanewclass">
                            <div class="row">
                                <div class="col-md-9">
                                    <?php echo msg($stat); ?>
                                </div>
                                <div class="col-md-3">
                                    <a href="<?php echo $Filename; ?>?action=add" class="btn btn-default"
                                       style="float:right">Add New Record</a>
                                </div>
                            </div>
                        </div>

                        <!-- add section start -->
                        <?php if ($_GET['action'] == 'add') { ?>
                            <div class="card-box">
                                <form action="" method="POST" enctype="multipart/form-data"/>
                                <div>
                                    <section>
									
									
									
									<div class="form-group clearfix">
                                            <label class="col-lg-2 control-label" for="employee_id">Session</label>
											<div class="col-lg-10">
											
                                            <select  class="required form-control" name="session" id="session" >
			  <option>Select Session</option>
			  <?php $aryDetail=$db->getRows("select * from  school_session order by id desc");
					   foreach($aryDetail as $iList)
									{	$i=$i+1;?>
             <option value="<?php echo $iList['id']; ?>" <?php  if($_POST['session']==$iList['id']) { echo "selected"; }  ?>><?php echo $iList['session']; ?></option>
									<?php }?>
            </select>
                                        </div></div>

                                        <div class="form-group clearfix">
                                            <label class="col-lg-2 control-label" for="employee_id">Student ID</label>
                                            <div class="col-lg-10">
                                                <input name="student_id" class="form-control" id="username"
                                                       value="<?php echo $_POST['student_id']; ?>"
                                                       placeholder="Enter User Name" type="text"/>
                                            </div>
                                        </div>
										
										
										  <div class="form-group clearfix">
                                            <label class="col-lg-2 control-label" for="employee_id">Last Name</label>
                                            <div class="col-lg-10">
                                                <input name="last_name" class="form-control" id="name"
                                                       value="<?php echo $_POST['last_name']; ?>"
                                                       placeholder="Enter  Last Name" type="text"/>
                                            </div>
                                        </div>
										
										

                                        <div class="form-group clearfix">
                                            <label class="col-lg-2 control-label" for="employee_id">First Name</label>
                                            <div class="col-lg-10">
                                                <input name="first_name" class="form-control" id="title"
                                                       value="<?php echo $_POST['first_name']; ?>"
                                                       placeholder="Enter  First Name" type="text"/>
                                            </div>
                                        </div>
										
										

                                        <div class="form-group clearfix">
                                            <label class="col-lg-2 control-label" for="employee_id">Other Name </label>
                                            <div class="col-lg-10">
                                                <input name="other_name" class="form-control" id="other_name"
                                                       value="<?php echo $_POST['other_name']; ?>"
                                                       placeholder="Enter Other Name" type="text"/>
                                            </div>
                                        </div>

                                       
                                        
                                         
										  
									   
									   
                                        <button type="submit" name="register" class="btn btn-default">Submit</button>
                                        <a href="<?php echo $Filename; ?>" class="btn btn-default">Back</a>
                                    </section>
                                </div>
                                </form>
                            </div>
							
								
                        <?php } 
						
				 elseif ($_GET['action'] == 'edit') { 
				$arydetail=$db->getRow("select * from  class_member where randomid='".$_GET['randomid']."'");?>								
												
												
                            <div class="card-box">
                                <form action="" method="POST" enctype="multipart/form-data"/>
                                <div>
                                    <section>
									
									
									
									<div class="form-group clearfix">
                                            <label class="col-lg-2 control-label" for="employee_id">Session</label>
											<div class="col-lg-10">
											
                                            <select  class="required form-control" name="session" id="session" >
			  <option>Select Session</option>
			  <?php $aryDetail=$db->getRows("select * from  school_session order by id desc");
					   foreach($aryDetail as $iList)
									{	$i=$i+1;?>
             <option value="<?php echo $iList['id']; ?>" <?php  if($_POST['session']==$iList['id']) { echo "selected"; }  ?>><?php echo $iList['session']; ?></option>
									<?php }?>
            </select>
                                        </div></div>

                                        <div class="form-group clearfix">
                                            <label class="col-lg-2 control-label" for="employee_id">Student ID</label>
                                            <div class="col-lg-10">
                                                <input name="student_id" class="form-control" id="username"
                                                       value="<?php echo $_POST['student_id']; ?>"
                                                       placeholder="Enter User Name" type="text"/>
                                            </div>
                                        </div>
										
										
										  <div class="form-group clearfix">
                                            <label class="col-lg-2 control-label" for="employee_id">Last Name</label>
                                            <div class="col-lg-10">
                                                <input name="last_name" class="form-control" id="name"
                                                       value="<?php echo $_POST['last_name']; ?>"
                                                       placeholder="Enter  Last Name" type="text"/>
                                            </div>
                                        </div>
										
										

                                        <div class="form-group clearfix">
                                            <label class="col-lg-2 control-label" for="employee_id">First Name</label>
                                            <div class="col-lg-10">
                                                <input name="first_name" class="form-control" id="title"
                                                       value="<?php echo $_POST['first_name']; ?>"
                                                       placeholder="Enter  First Name" type="text"/>
                                            </div>
                                        </div>
										
										

                                        <div class="form-group clearfix">
                                            <label class="col-lg-2 control-label" for="employee_id">Other Name </label>
                                            <div class="col-lg-10">
                                                <input name="other_name" class="form-control" id="other_name"
                                                       value="<?php echo $_POST['other_name']; ?>"
                                                       placeholder="Enter Other Name" type="text"/>
                                            </div>
                                        </div>

                                       
                                        
                                         
										  
									   
									   
                                        <button type="submit" name="edit" class="btn btn-default">Update</button>
                                        <a href="<?php echo $Filename; ?>" class="btn btn-default">Back</a>
                                    </section>
                                </div>
                                </form>
                            </div>
						
							
                       
                        <?php } else { ?>
                            <div class="card-box">
                                <table id="datatable" class="table table-striped table-bordered">
                                    <thead>
                                    <tr>
                                        <th>#</th>
										<th>Session</th>
                                        <th>Student Id</th>
                                        <th>Last Name</th>
                                        <th>First Name</th>
                                        <th>Other Name</th>
                                        <th>Create At</th>
                                        <th>Action</th>
	                                    
                                    </tr>
                                    </thead>
									
                                    <tbody>
                                    <?php
                                    $aryList = $db->getRows("select * from assess_subject_trait order by id desc");
                                    foreach ($aryList as $iList) {
                                        $i = $i + 1;
                                        $aryPgAct["id"] = $iList['id'];
                                        ?>
                                        <tr>
                                            <td><?php echo $i ?></td>
											<td><?php echo $db->getVal("select session from school_session	 where id='".$iList['session']."'"); ?></td>
                                            <td><?php echo $iList['student_id']; ?></td>
                                            <td><?php echo $iList['last_name']; ?></td>
                                            <td><?php echo $iList['first_name']; ?></td>
                                            <td><?php echo $iList['other_name']; ?></td>
                                            
                                            
											<td><?php echo $iList['create_at']; ?></td>
											<td>
                                               
                                                <a href="<?php echo $Filename; ?>?action=edit&randomid=<?php echo $iList['randomid']; ?>"
                                                   class="table-action-btn"> <i class="fa fa-pencil"></i> </a>
                                                <a href="javascript:del('<?php echo $Filename; ?>?action=delete&randomid=<?php echo $iList['randomid']; ?>')"
                                                   class="table-action-btn"> <i class="fa fa-times"></i> </a>
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

    </div>
</div>

</div>
</div>

<?php include('inc.js.php'); ?>
<?php include('inc.footer.php'); ?>
</body>
</html>
