<?php
include('../config.php');
include('inc.session-create.php'); 
$validate = new validation();
$pageTitle = 'Manage User';
$Filename = 'manageuser.php';
if (isset($_POST['register'])) {


    $validate->addRule($_POST['name'],'alpha', 'Full name', true);
    $validate->addRule($_POST['email'],'Email', 'Email', true);
    $validate->addRule($_POST['contact_no'],'Num', 'Mobile Number', true);
    $validate->addRule($_POST['username'], '','Username', true);
     $validate->addRule($_POST['status'], '', 'Status', true);
  

    if ($validate->validate() && count($stat) == 0) {
        $iEmailCheckId = $db->getRow("select * from school_register where email='" . $_POST['email'] . "' ");
        if ($iEmailCheckId['id'] == '') {
            
           
            $aryData = array(
			
			                 'username'                   => $_POST['username'],
                             'name'                      => $_POST['name'],
                             'email'                    => $_POST['email'],
                             'contact_no'                 => $_POST['contact_no'],
							 'usertype'                 => $_POST['usertype'],
                            'verifyid'                   => randomFix(12),
                             'status'                    => $_POST['status'],
							 'randomid'                    => randomFix(15),
							 'create_by_userid'                    => $create_by_userid,
							 'create_by_usertype'                    => $create_by_usertype,
                             'create_at'                => date('Y-m-d H:m:s'),
                             
               
            );
            $flgIn2 = $db->insertAry("school_register", $aryData);
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


    $validate->addRule($_POST['name'],'alpha', 'Full name', true);
    $validate->addRule($_POST['email'],'Email', 'Email', true);
    $validate->addRule($_POST['contact_no'],'Num', 'Mobile Number', true);
    $validate->addRule($_POST['username'], '','Username', true);
     $validate->addRule($_POST['status'], '', 'Status', true);
  

    if ($validate->validate() && count($stat) == 0) {
        $iEmailCheckId = $db->getRow("select * from school_register where email='" . $_POST['email']."'  && id!='".$_SESSION['userid']."'");
        if ($iEmailCheckId['id'] == '' && id!=$GET['randomid']) {
            
           
            $aryData = array(
			
			                 'username'                   => $_POST['username'],
                             'name'                      => $_POST['name'],
                             'email'                    => $_POST['email'],
                             'contact_no'                 => $_POST['contact_no'],
							 'usertype'                 => $_POST['usertype'],
                            'verifyid'                   => randomFix(12),
                             'status'                    => $_POST['status'],
							 'randomid'                    => randomFix(15),
                             'create_at'                => date('Y-m-d H:m:s'),
                             
               
            );
            $flgIn2 = $db->updateAry("school_register", $aryData, "where randomid='".$_GET['randomid']."'");
			
			
            $stat['success'] = "Updated successfully";
            unset($_POST);
            redirect($Filename);
        } 
		
		else {
            $stat['error'] = "Email are alerady registerd.";
        }
	}
}

elseif (($_REQUEST['action'] == 'delete')) {
    $flgIn1 = $db->delete("school_register", "where randomid='" . $_GET['randomid'] . "' ");
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
                                            <label class="col-lg-2 control-label" for="employee_id">User Type</label>
											<div class="col-lg-10">
											
                                            <select  class="required form-control" name="usertype" id="usertype" >
			  <option>Select UserType</option>
			  <?php $aryDetail=$db->getRows("select * from  roles where create_by_userid='".$create_by_userid."'");
					   foreach($aryDetail as $iList)
									{	$i=$i+1;?>
             <option value="<?php echo $iList['id']; ?>" <?php  if($_POST['usertype']==$iList['id']) { echo "selected"; }  ?>><?php echo $iList['role']; ?></option>
									<?php }?>
            </select>
                                        </div></div>

                                        <div class="form-group clearfix">
                                            <label class="col-lg-2 control-label" for="employee_id">User Name</label>
                                            <div class="col-lg-10">
                                                <input name="username" class="form-control" id="username"
                                                       value="<?php echo $_POST['username']; ?>"
                                                       placeholder="Enter User Name" type="text"/>
                                            </div>
                                        </div>
										  <div class="form-group clearfix">
                                            <label class="col-lg-2 control-label" for="employee_id">Full Name</label>
                                            <div class="col-lg-10">
                                                <input name="name" class="form-control" id="name"
                                                       value="<?php echo $_POST['name']; ?>"
                                                       placeholder="Enter your Name" type="text"/>
                                            </div>
                                        </div>

                                        <div class="form-group clearfix">
                                            <label class="col-lg-2 control-label" for="employee_id">Email</label>
                                            <div class="col-lg-10">
                                                <input name="email" class="form-control" id="title"
                                                       value="<?php echo $_POST['email']; ?>"
                                                       placeholder="Enter your Email" type="email"/>
                                            </div>
                                        </div>

                                        <div class="form-group clearfix">
                                            <label class="col-lg-2 control-label" for="employee_id">Contact
                                                Number </label>
                                            <div class="col-lg-10">
                                                <input name="contact_no" class="form-control" id="contact_no"
                                                       value="<?php echo $_POST['contact_no']; ?>"
                                                       placeholder="Enter your Contact Number" type="text"/>
                                            </div>
                                        </div>

                                       
                                        
                                         
										  <div class="form-group clearfix">
                                                <label class="col-lg-2 control-label " for="confirm">Verification status </label>
                                                <div class="col-lg-10">
                                                    <select class=" form-control" name="status">
                                                        <option name="status"
                                                                value="0" <?php if ($_POST['status'] == '0') {
                                                            echo "selected";
                                                        } ?>>VERIFICATION SENT
                                                        </option>
                                                        <option name="status"
                                                                value="1" <?php if ($_POST['status'] == '1') {
                                                            echo "selected";
                                                        } ?>>PENDING
                                                        </option>
                                                    </select>
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
				$arydetail=$db->getRow("select * from  school_register where randomid='".$_GET['randomid']."' and create_by_userid='".$create_by_userid."'");?>								
												
												
                            <div class="card-box">
                                <form action="" method="POST" enctype="multipart/form-data"/>
                                <div>
                                    <section>
									
									
									<div class="form-group clearfix">
                                            <label class="col-lg-2 control-label" for="employee_id">User Type</label>
											<div class="col-lg-10">
											
                                            <select  class="required form-control" name="usertype" id="usertype" >
			  <option>Select UserType</option>
			  <?php $aryDetail=$db->getRows("select * from  roles where create_by_userid='".$create_by_userid."'");
					   foreach($aryDetail as $iList)
									{	$i=$i+1;?>
             <option value="<?php echo $iList['id']; ?>" <?php  if($arydetail['usertype']==$iList['id']) { echo "selected"; }  ?>><?php echo $iList['role']; ?></option>
									<?php }?>
            </select>
               						
			</div>
										
			</div>
									
									
									

                                        <div class="form-group clearfix">
                                            <label class="col-lg-2 control-label" for="employee_id">User Name</label>
                                            <div class="col-lg-10">
                                                <input name="username" class="form-control" id="username"
                                                       value="<?php echo $arydetail['username']; ?>"
                                                       placeholder="Enter User Name" type="text"/>
                                            </div>
                                        </div>
										  <div class="form-group clearfix">
                                            <label class="col-lg-2 control-label" for="employee_id">Full Name</label>
                                            <div class="col-lg-10">
                                                <input name="name" class="form-control" id="name"
                                                       value="<?php echo $arydetail['name']; ?>"
                                                       placeholder="Enter your Name" type="text"/>
                                            </div>
                                        </div>

                                        <div class="form-group clearfix">
                                            <label class="col-lg-2 control-label" for="employee_id">Email</label>
                                            <div class="col-lg-10">
                                                <input name="email" class="form-control" id="title"
                                                       value="<?php echo $arydetail['email']; ?>"
                                                       placeholder="Enter your Email" type="email"/>
                                            </div>
                                        </div>

                                        <div class="form-group clearfix">
                                            <label class="col-lg-2 control-label" for="employee_id">Contact
                                                Number </label>
                                            <div class="col-lg-10">
                                                <input name="contact_no" class="form-control" id="contact_no"
                                                       value="<?php echo $arydetail['contact_no']; ?>"
                                                       placeholder="Enter your Contact Number" type="text"/>
                                            </div>
                                        </div>

                                       
                                        
                                          
										  <div class="form-group clearfix">
                                                <label class="col-lg-2 control-label " for="confirm">Verification status </label>
                                                <div class="col-lg-10">
                                                    <select class=" form-control" name="status">
                                                        <option name="status"
                                                                value="0" <?php if ($arydetail['status'] == '0') {
                                                            echo "selected";
                                                        } ?>>Active
                                                        </option>
                                                        <option name="status"
                                                                value="1" <?php if ($arydetail['status'] == '1') {
                                                            echo "selected";
                                                        } ?>>InActive
                                                        </option>
                                                    </select>
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
                                        <th>User Name</th>
                                        <th>Full Name</th>
                                        <th>Email</th>
                                        <th>Contact number</th>
                                        <th>User Type</th>
                                        <th> status</th>
                                       
                                        <th>Action</th>
                                        
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php
                                    $aryList = $db->getRows("select * from school_register  where create_by_userid='".$_SESSION['userid']."' and create_by_userid='".$create_by_userid."'");
									//echo "select * from school_register  where create_by_userid='".$_SESSION['userid']."'";
                                    foreach ($aryList as $iList) {
                                        $i = $i + 1;
                                        $aryPgAct["id"] = $iList['id'];
                                        ?>
                                        <tr>
                                            <td><?php echo $i ?></td>
                                            <td><?php echo $iList['username']; ?></td>
                                            <td><?php echo $iList['name']; ?></td>
                                            <td><?php echo $iList['email']; ?></td>
                                            <td><?php echo $iList['contact_no']; ?></td>
                                            <td><?php echo $db->getVal("select role from roles where id='".$iList['usertype']."'"); ?></td>
                                            <td><?php if($iList['status']=='0') {echo "Active";} else { "InActive";} ?></td>
                                           
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
