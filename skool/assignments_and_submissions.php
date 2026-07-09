<?php
include('../config.php');
$validate = new validation();
$pageTitle = 'Assignments and Submissions';
$Filename = 'assignments_and_submissions.php';
if (isset($_POST['submit']))
{
    $validate->addRule($_POST['id'],'','ID',true);
    $validate->addRule($_POST['title'],'','title',true);
    $validate->addRule($_POST['deadline'],'','deadline',true);



    if ($validate->validate() && count($stat) == 0) {


        $aryData = array(
            'staff_id' => $_POST['id'],
            'title'    => $_POST['title'],
            'deadline' => $_POST['deadline'],
            'created_on'  => date('Y-m-d'),
            'randomid' => randomFix(10),

        );
        $flgIn1 = $db->insertAry("assignments_and_submissions", $aryData);

        $stat['success'] = "Submited Successfully";
        redirect($Filename);
        unset($_POST);
    } else {
        $stat['error'] = $validate->errors();
    }
}

elseif (isset($_POST['edit'])) {


    $validate->addRule($_POST['last_name'],'', 'Last Name', true);
    $validate->addRule($_POST['first_name'],'', 'First Name', true);
    $validate->addRule($_POST['other_name'], '','Other Name', true);



    if ($validate->validate() && count($stat) == 0) {


            $aryData = array(

                'staff_id'                  => $_POST['id'],
                'title'                => $_POST['title'],
                'deadline'                 => $_POST['deadline'],

            );
            $flgIn2 = $db->updateAry("assignments_and_submissions", $aryData);
            echo $flgIn2 = $db->getLastQuery();
            exit;


            $stat['success'] = "Submited successfully";
            unset($_POST);
            redirect($Filename);
        }


    else {
        $stat['error'] = $validate->errors();
    }
}


elseif (($_REQUEST['action'] == 'delete')) {
    $flgIn1 = $db->delete("assignments_and_submissions", "where randomid='" . $_GET['randomid'] . "' ");
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
                            <li><a href="<?php echo $Filename; ?>">Home</a></li>
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
                                            <label class="col-lg-2 control-label" for="employee_id">ID</label>
                                            <div class="col-lg-10">
                                                <input name="id" class="form-control" id="username"
                                                       value="<?php echo $_POST['id']; ?>"
                                                       placeholder="Enter your id" type="text"/>
                                            </div>
                                        </div>

                                        <div class="form-group clearfix">
                                            <label class="col-lg-2 control-label" for="employee_id"> Title </label>
                                            <div class="col-lg-10">
                                                <input name="title" class="form-control" id="username"
                                                       value="<?php echo $_POST['title']; ?>"
                                                       placeholder="Enter your title" type="text"/>
                                            </div>
                                        </div>

                                        <!----<div class="form-group clearfix">
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
                                            </div></div>---->


                                        <div class="form-group clearfix">
                                            <label class="col-lg-2 control-label " for="price">	Date </label>
                                            <div class="col-lg-4">
                                                <input type="text" class="form-control datepicker"  name="deadline" value="<?php echo $_POST['deadline'];?>" />
                                            </div>
                                        </div>


                                        <button type="submit" name="submit" class="btn btn-default">Submit</button>
                                        <a href="<?php echo $Filename; ?>" class="btn btn-default">Back</a>
                                    </section>
                                </div>
                                </form>
                            </div>


                        <?php }

                        elseif ($_GET['action'] == 'edit') {
                            $arydetail=$db->getRow("select * from  assignments_and_submissions where randomid='".$_GET['randomid']."'");?>


                            <div class="card-box">
                                <form action="" method="POST" enctype="multipart/form-data"/>
                                <div>
                                    <section>

                                        <div class="form-group clearfix">
                                            <label class="col-lg-2 control-label" for="employee_id">ID</label>
                                            <div class="col-lg-10">
                                                <input name="id" class="form-control" id="username"
                                                       value="<?php echo $arydetail['staff_id']; ?>"
                                                       placeholder="Enter your id" type="text"/>
                                            </div>
                                        </div>

                                        <div class="form-group clearfix">
                                            <label class="col-lg-2 control-label" for="employee_id"> Title </label>
                                            <div class="col-lg-10">
                                                <input name="title" class="form-control" id="username"
                                                       value="<?php echo $arydetail['title']; ?>"
                                                       placeholder="Enter your title" type="text"/>
                                            </div>
                                        </div>

                                        <!----<div class="form-group clearfix">
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
                                            </div></div>---->


                                        <div class="form-group clearfix">
                                            <label class="col-lg-2 control-label " for="price">	Date </label>
                                            <div class="col-lg-4">
                                                <input type="text" class="form-control datepicker"  name="deadline" value="<?php echo $arydetail['deadline'];?>" />
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
                                        <th>Staff Id</th>
                                        <th>Title</th>
                                        <th>Created On</th>
                                        <th>Deadline</th>
                                        <th>File Name</th>
                                        <th>Action</th>

                                    </tr>
                                    </thead>

                                    <tbody>
                                    <?php
                                    $aryList = $db->getRows("select * from assignments_and_submissions order by id desc");
                                    foreach ($aryList as $iList) {
                                        $i = $i + 1;
                                        $aryPgAct["id"] = $iList['id'];
                                        ?>
                                        <tr>
                                            <td><?php echo $i ?></td>
                                            <td><?php echo $iList['staff_id']?></td>
                                            <td><?php echo $iList['title']; ?></td>
                                            <td><?php echo $iList['created_on']; ?></td>
                                            <td><?php echo $iList['deadline']; ?></td>
                                            <td><?php echo $iList['filename']; ?></td>
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
