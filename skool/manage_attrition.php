<?php include('../config.php');
//include('inc.session-create.php');
$PageTitle = "Manage Attrition";
$FileName = 'manage_attrition.php';
$validate = new Validation();

if (isset($_POST['submit'])) {
    $validate->addRule($_POST['id'], '', 'id', true);
    $validate->addRule($_POST['description'], '', ' Description', true);


    if ($validate->validate() && count($stat) == 0) {


        $aryData = array(
            'a_id' => $_POST['id'],
            'description' => $_POST['description'],
            'randomid' => randomFix(10),

        );
        $flgIn1 = $db->insertAry("manage_attrition", $aryData);

        $stat['success'] = "Submited Successfully";
        redirect($FileName);
        unset($_POST);
    } else {
        $stat['error'] = $validate->errors();
    }
}

if ($_SESSION['success'] != "") {
    $stat['success'] = $_SESSION['success'];
    unset($_SESSION['success']);
} //$getDetails=$db->getRow("select * from  application_form where randomid='".$_GET['randomid']."'");


elseif (isset($_POST['update_student_details'])) {
    $validate->addRule($_POST['id'], '', 'id', true);
    $validate->addRule($_POST['description'], '', ' Description', true);

    if ($validate->validate() && count($stat) == 0) {
        $aryData = array(
            'a_id' => $_POST['id'],
            'description' => $_POST['description'],

        );

        $flgIn1 = $db->updateAry("manage_attrition", $aryData, "where randomid='" . $_GET['randomid'] . "'");


        $stat['success'] = "Updated Successfully";
        redirect($FileName);
        unset($_POST);

    } else {
        $stat['error'] = $validate->errors();
    }


    //Manage Student attrition
} elseif (isset($_POST['add_manage_student'])) {
    if ($validate->validate() && count($stat) == 0) {


        $aryData = array(
            'name' => $_POST['name'],
            'status' => $_POST['status'],
            'date' => $_POST['date'],
            'comments' => $_POST['commments'],
            'randomid' => randomFix(10),

        );
        $flgIn1 = $db->insertAry("manage_student_attrition", $aryData);

        //echo $flgIn1 = $db->getLastQuery();
        //exit;
        $stat['success'] = "Submited Successfully";
        unset($_POST);

    } else {
        $stat['error'] = $validate->errors();
    }

} elseif (($_GET['action'] == 'delete_attrition')) {
    $flgIn1 = $db->delete("manage_attrition", "where randomid='" . $_GET['randomid'] . "'");
    $_SESSION['success'] = 'Deleted Successfully';
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
                        <ol class="breadcrumb">
                            <li><a href="<?php echo $PageTitle; ?>">Home</a></li>
                            <li class="active"><?php echo $PageTitle; ?></li>
                        </ol>
                    </div>
                </div>

                <!-- Basic Form Wizard -->
                <div class="row">
                    <div class="col-md-12">
                        <div class="row">
                            <div class="col-md-1"></div>
                            <div class="col-md-1"></div>
                            <div class="col-md-1">
                                <div class="gokul">
                                    <a href="<?php echo $FileName; ?>?action=add_attrition" class="btn btn-default"
                                       style="float:right">Manage Attrition Status <i class="fa fa-plus"
                                                                                      aria-hidden="true"></i></a>
                                </div>
                            </div>
                            <div class="col-md-1"></div>

                            <div class="col-md-2">
                                <div class="gokul">
                                    <a href="<?php echo $FileName; ?>?action=add_view_attrition_record"
                                       class="btn btn-default" style="float:right">View Attrition Record
                                        <i class="fa fa-plus" aria-hidden="true"></i></a>
                                </div>
                            </div>
                            <div class="col-md-1"></div>

                            <div class="col-md-2">
                                <div class="gokul">
                                    <a href="<?php echo $FileName; ?>?action=add_manage_student" class="btn btn-default"
                                       style="float:right">Manage Student Attrition <i class="fa fa-plus"
                                                                                       aria-hidden="true"></i></a>
                                </div>
                            </div>

                        </div>
                    </div>
                    <div class="col-md-12 ">
                        <?php if ($_GET['action'] == 'add_attrition') { ?>
                            <div class="card-box">
                                <form role="form" action="" method="post" enctype="multipart/form-data">
                                    <div>
                                        <section>
                                            <div class="form-group clearfix">
                                                <label class="col-lg-2 control-label " for="price">ID:</label>
                                                <div class="col-lg-10">
                                                    <input type="text" class="form-control" name="id"
                                                           value="<?php echo $_POST['id']; ?>"/>
                                                </div>
                                            </div>

                                            <div class="form-group clearfix">
                                                <label class="col-lg-2 control-label " for="price">Description:</label>
                                                <div class="col-lg-10">
                                                    <input type="text" class="form-control" name="description"
                                                           value="<?php echo $_POST['description']; ?>"/>
                                                </div>
                                            </div>

                                            <div class="form-group clearfix bfrcs ">
                                                <div class="col-lg-12 sgot">
                                                    <div class="row">
                                                        <div class="savdtls">
                                                            <button type="submit" name="submit" class="btn btn-default">
                                                                Save details
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                    </div>
                                </form>
                            </div>


                            <div class="card-box">
                                <table class="table table-striped table-bordered">
                                    <thead>
                                    <tr>
                                        <th>#</th>
                                        <th> ID</th>
                                        <th>Description</th>
                                        <th>Action</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php $i = 0;
                                    $aryList = $db->getRows("select *from manage_attrition");
                                    foreach ($aryList as $iList) {
                                        $i = $i + 1;

                                        ?>
                                        <tr>
                                            <td><?php echo $i ?></td>
                                            <td><?php echo $iList['a_id']; ?></td>
                                            <td><?php echo $iList['description']; ?></td>
                                            <td>
                                                <a href="<?php echo $FileName; ?>?action=edit_attrition&randomid=<?php echo $iList['randomid'] ?>"
                                                   class="table-action-btn">
                                                    <i class="fa fa-pencil"></i> </a>
                                                <a href="javascript:del('<?php echo $FileName; ?>?action=delete_attrition&randomid=<?php echo $iList['randomid']; ?>')"
                                                   class="table-action-btn"> <i class="fa fa-times"></i> </a>
                                            </td>
                                        </tr>
                                    <?php } ?>
                                    </tbody>
                                </table>
                            </div>

                        <?php }
                        if ($_GET['action'] == 'edit_attrition') {

                            $aryDetail = $db->getRow("select * from  manage_attrition where randomid='" . $_GET['randomid'] . "'");

                            ?>
                            <div class="card-box">
                                <form role="form" action="" method="post" enctype="multipart/form-data">
                                    <div>
                                        <section>
                                            <div class="form-group clearfix">
                                                <label class="col-lg-2 control-label " for="price">ID:</label>
                                                <div class="col-lg-10">
                                                    <input type="text" class="form-control" name="id"
                                                           value="<?php echo $aryDetail['a_id']; ?>"/>
                                                </div>
                                            </div>

                                            <div class="form-group clearfix">
                                                <label class="col-lg-2 control-label " for="price">Description:</label>
                                                <div class="col-lg-10">
                                                    <input type="text" class="form-control" name="description"
                                                           value="<?php echo $aryDetail['description']; ?>"/>
                                                </div>
                                            </div>

                                            <div class="form-group clearfix bfrcs ">
                                                <div class="col-lg-12 sgot">
                                                    <div class="row">
                                                        <div class="savdtls">
                                                            <button type="submit" name="update_student_details"
                                                                    class="btn btn-default">Update Details
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                    </div>
                                </form>
                            </div>


                            <!------------------------View Student attrition------------------------>
                        <?php } elseif ($_GET['action'] == 'add_view_attrition_record') { ?>

                            <div class="card-box">
                                <table class="table table-striped table-bordered">
                                    <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Student ID</th>
                                        <th> First Name</th>
                                        <th> Last Name</th>
                                        <th>Other(s)</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                        <th> Comments</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php $i = 0;
                                    $aryList = $db->getRows("select *from manage_student_attrition order by id desc");
                                    foreach ($aryList as $iList) {
                                        $i = $i + 1;
                                        $aryStudent = $db->getRow("select * from manage_student where id ='" . $iList['name'] . "'");
                                        $aryId = $db->getRow("select * from manage_attrition where id ='". $iList['status'] ."'");

                                        ?>
                                        <tr>
                                            <td><?php echo $i ?></td>
                                            <td><?php echo $aryStudent['student_id']; ?></td>
                                            <td><?php echo $aryStudent['first_name']; ?></td>
                                            <td><?php echo $aryStudent['last_name']; ?></td>
                                            <td><?php echo $aryStudent['other_name']; ?></td>
                                            <td><?php echo $aryId['description']; ?></td>
                                            <td><?php echo $iList['date']; ?></td>
                                            <td><?php echo $iList['comments']; ?></td>
                                        </tr>
                                    <?php } ?>
                                    </tbody>
                                </table>
                            </div>


                            <!------------------------Manage Student attrition------------------------>
                        <?php } elseif ($_GET['action'] == 'add_manage_student') { ?>

                            <div class="card-box">
                                <form role="form" action="" method="POST" enctype="multipart/form-data">
                                    <div>
                                        <section>

                                            <div class="form-group clearfix">
                                                <label class="col-lg-2 control-label " for="price">Student Name</label>
                                                <div class="col-lg-4">
                                                    <select class="required form-control" name="name">
                                                        <option value="">Select Student</option>
                                                        <?php $aryDetail = $db->getRows("select * from  manage_student order by id desc");
                                                        foreach ($aryDetail as $iList) {
                                                            $i = $i + 1; ?>
                                                            <option value="<?php echo $iList['id']; ?>" <?php if ($_POST['manage_student'] == $iList['id']) {
                                                                echo "selected";
                                                            } ?>><?php echo $iList['first_name']; ?><?php echo $iList['last_name']; ?></option>
                                                        <?php } ?>
                                                    </select>
                                                </div>

                                                <label class="col-lg-2 control-label " for="price">Student
                                                    Status</label>
                                                <div class="col-lg-4">
                                                    <select class="required form-control" name="status">
                                                        <option value="">Select Status</option>
                                                        <?php $aryDetail = $db->getRows("select * from  manage_attrition order by id desc");
                                                        foreach ($aryDetail as $iList) {
                                                            $i = $i + 1; ?>
                                                            <option value="<?php echo $iList['id']; ?>" <?php if ($_POST['manage_attrition'] == $iList['id']) {
                                                                echo "selected";
                                                            } ?>><?php echo $iList['description']; ?></option>
                                                        <?php } ?>
                                                    </select>
                                                </div>
                                            </div>


                                            <div class="form-group clearfix">
                                                <label class="col-lg-2 control-label " for="price">Comments</label>
                                                <div class="col-lg-4">
                                                    <textarea class="form-control"
                                                              name="commments"> <?php echo $_POST['commments']; ?></textarea>

                                                </div>

                                                <label class="col-lg-2 control-label " for="price"> Date </label>
                                                <div class="col-lg-4">
                                                    <input type="text" class="form-control datepicker" name="date"
                                                           value="<?php echo $_POST['date']; ?>"/>
                                                </div>
                                            </div>

                                            <div class="form-group clearfix bfrcs ">
                                                <div class="col-lg-12 sgot">
                                                    <div class="row">
                                                        <div class="savdtls">
                                                            <button type="submit" name="add_manage_student"
                                                                    class="btn btn-default">Save details
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </section>
                                    </div>
                                </form>
                            </div>


                            <div class="card-box">
                                <table class="table table-striped table-bordered">
                                    <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Student name</th>
                                        <th>Student Status</th>
                                        <th>Comments</th>
                                        <th>Date</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php $i = 0;
                                    $aryList = $db->getRows("select *from  manage_student_attrition order by id desc");
                                    foreach ($aryList as $iList) {
                                        $i = $i + 1;
                                        $arystudent = $db->getRow("select * from manage_student where id = '" . $iList['name'] . "'");
                                        $arystatus = $db->getRow("select * from manage_attrition where id ='" . $iList['status'] . "'");


                                        ?>
                                        <tr>
                                            <td><?php echo $i ?></td>
                                            <td><?php echo $arystudent['first_name']; ?><?php echo $arystudent['last_name']; ?></td>
                                            <td><?php echo $arystatus['description']; ?></td>
                                            <td><?php echo $iList['comments']; ?></td>
                                            <td><?php echo $iList['date']; ?></td>
                                        </tr>
                                    <?php } ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php } ?>

                    </div>
                </div>
            </div>
            <?php include('inc.footer.php'); ?>
        </div>
    </div>
    <?php include('inc.js.php'); ?>
    <script>

        function getguardian() {
            document.getElementById("guardianno").style.display = "block";


        }
    </script>

    <script>
        $(function () {
            $("#datepicker").datepicker();
        });
    </script>
    <script>
        $(function () {
            $("#datepicker1").datepicker();
        });
    </script>
</body>
</html>
