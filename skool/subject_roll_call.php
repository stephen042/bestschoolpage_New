<?php
include('../config.php');
$validate = new validation();
$pageTitle = 'Manage Subject Roll';
$Filename = 'subject_roll_call.php';
if (isset($_POST['register'])) {

    $validate->addRule($_POST['subject_name'], '', 'Subject Name', true);
    $validate->addRule($_POST['session'],'','Session',true);

    if ($validate->validate() && count($stat) == 0) {

        $aryData = array(
            'session'      => $_POST['session'],
            'subject_code' => $_POST['subject_name'],
            'randomid'   => randomFix(10),
            'date'       => $_POST['date_of_admission'],

        );
        $flgIn2 = $db->insertAry("subject_roll_call", $aryData);
        //echo $flgIn2 = $db->getLastQuery();
        //exit;
        $stat['success'] = "Submited successfully";
        unset($_POST);
        redirect($Filename);
    }
}
elseif (isset($_POST['edit'])) {


    $validate->addRule($_POST['subject_name'], 'alpha', 'Subject', true);
    $validate->addRule($_POST['first_name'], 'alpha', 'First name', true);
    $validate->addRule($_POST['last_name'], 'alpha', 'Last name', true);
    $validate->addRule($_POST['student_id'], 'alpha', 'Student Id', true);
    $validate->addRule($_POST['other_name'], 'Num', 'Other Name', true);


    if ($validate->validate() && count($stat) == 0) {


        $aryData = array(
            'session'      => $_POST['session'],
            'subject_code' => $_POST['subject_name'],

        );
        $flgIn2 = $db->updateAry("subject_roll_call", $aryData, "where randomid='" . $_GET['randomid'] . "'");


        $stat['success'] = "Updated successfully";
        unset($_POST);
        redirect($Filename);
    }
} elseif (($_REQUEST['action'] == 'delete')) {
    $flgIn1 = $db->delete("subject_roll_call", "where randomid='" . $_GET['randomid'] . "' ");
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
                                            <label class="col-lg-2 control-label " for="price"> Session Name</label>
                                            <div class="col-lg-10">
                                                <select class="required form-control" name="session">
                                                    <option value="">Select Session</option>
                                                    <?php $aryDetail = $db->getRows("select * from  school_session order by id desc");
                                                    foreach ($aryDetail as $iList) {
                                                        $i = $i + 1; ?>
                                                        <option value="<?php echo $iList['id']; ?>" <?php if ($_POST['session'] == $iList['id']) {
                                                            echo "selected";
                                                        } ?>><?php echo $iList['session']; ?></option>
                                                    <?php } ?>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="form-group clearfix">
                                            <label class="col-lg-2 control-label " for="price"> Subject Name</label>
                                            <div class="col-lg-10">
                                                <select class="required form-control" name="subject_name">
                                                    <option value="">Select Subject</option>
                                                    <?php $aryDetail = $db->getRows("select * from  school_subject order by id desc");
                                                    foreach ($aryDetail as $iList) {
                                                        $i = $i + 1; ?>
                                                        <option value="<?php echo $iList['id']; ?>" <?php if ($_POST['subject_name'] == $iList['id']) {
                                                            echo "selected";
                                                        } ?>><?php echo $iList['subject']; ?></option>
                                                    <?php } ?>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="form-group clearfix">
                                        <label class="col-lg-2 control-label " for="price">
                                            Date of Admission: *</label>
                                        <div class="col-lg-10">
                                            <input type="text" class="form-control datepicker"  name="date_of_admission" value="<?php echo $_POST['date_of_admission'];?>" />
                                        </div>
                                </div>

                                        <button type="submit" name="register" class="btn btn-default">Submit</button>
                                        <a href="<?php echo $Filename; ?>" class="btn btn-default">Back</a>
                                    </section>
                                </div>
                                </form>
                            </div>


                        <?php } elseif ($_GET['action'] == 'edit') {
                            $arydetail = $db->getRow("select * from subject_roll_call where randomid='" .$_GET['randomid']. "' "); ?>


                            <div class="card-box">
                                <form action="" method="POST" enctype="multipart/form-data"/>
                                <div>
                                    <section>

                                        <div class="form-group clearfix">
                                            <label class="col-lg-2 control-label " for="price"> Session Name</label>
                                            <div class="col-lg-10">
                                                <select class="required form-control" name="session">
                                                    <option value="">Select Session</option>
                                                    <?php $aryDetail = $db->getRows("select * from  school_session order by id desc");
                                                    foreach ($aryDetail as $iList) {
                                                        $i = $i + 1; ?>
                                                        <option value="<?php echo $iList['id']; ?>" <?php if ($arydetail['session'] == $iList['id']) {
                                                            echo "selected";
                                                        } ?>><?php echo $iList['session']; ?></option>
                                                    <?php } ?>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="form-group clearfix">
                                            <label class="col-lg-2 control-label " for="price"> Subject Name</label>
                                            <div class="col-lg-10">
                                                <select class="required form-control" name="subject_name">
                                                    <option value="">Select Subject</option>
                                                    <?php $aryDetail = $db->getRows("select * from  school_subject order by id desc");
                                                    foreach ($aryDetail as $iList) {
                                                        $i = $i + 1; ?>
                                                        <option value="<?php echo $iList['id']; ?>" <?php if ($arydetail['subject_code'] == $iList['id']) {
                                                            echo "selected";
                                                        } ?>><?php echo $iList['subject']; ?></option>
                                                    <?php } ?>
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
                                        <th>Session</th>
                                        <th>Subject Code</th>
                                        <th>Date</th>
                                        <th>Action</th>

                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php
                                    $aryList = $db->getRows("select * from subject_roll_call order by id desc");
                                    foreach ($aryList as $iList) {
                                        $i = $i + 1;
                                        $aryPgAct["id"] = $iList['id'];
                                        $aryiList = $db->getRow("select * from 	school_subject where id ='".$iList['subject_code']."'");
                                        $arySession = $db-> getRow("select * from school_session where id='".$iList['session']."'");
                                        ?>
                                        <tr>
                                            <td><?php echo $i ?></td>
                                            <td><?php echo $arySession['session']; ?></td>
                                            <td><?php echo $aryiList['subject']; ?></td>
                                            <td><?php echo $iList['date']; ?></td>
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
