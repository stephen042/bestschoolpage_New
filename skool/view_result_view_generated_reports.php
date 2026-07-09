<?php
include('../config.php');
$validate = new validation();
$pageTitle = 'Generated Reports';
$Filename = 'generated_reports.php';
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






                <div class="form-group clearfix">
                    <label class="col-lg-1 control-label" for="employee_id">Term</label>
                    <div class="col-lg-3">

                        <select class="required form-control" name="session" id="session">
                            <option>Select Session</option>
                            <?php $aryDetail = $db->getRows("select * from  school_session order by id desc");
                            foreach ($aryDetail as $iList) {
                                $i = $i + 1; ?>
                                <option value="<?php echo $iList['id']; ?>" <?php if ($_POST['session'] == $iList['id']) {
                                    echo "selected";
                                } ?>><?php echo $iList['session']; ?></option>
                            <?php } ?>
                        </select>
                    </div>

                    <label class="col-lg-1 control-label" for="employee_id">Section</label>
                    <div class="col-lg-2">

                        <select class="required form-control" name="section" id="section">
                            <option>Select Section</option>
                            <?php $aryDetail = $db->getRows("select * from  school_section order by id desc");
                            foreach ($aryDetail as $iList) {
                                $i = $i + 1; ?>
                                <option value="<?php echo $iList['id']; ?>" <?php if ($_POST['section'] == $iList['id']) {
                                    echo "selected";
                                } ?>><?php echo $iList['section']; ?></option>
                            <?php } ?>
                        </select>
                    </div>
                    <label class="col-lg-1 control-label" for="employee_id">Class</label>
                    <div class="col-lg-3">

                        <select class="required form-control" name="class" id="class">
                            <option>Select Class</option>
                            <?php $aryDetail = $db->getRows("select * from  school_class order by id desc");
                            foreach ($aryDetail as $iList) {
                                $i = $i + 1; ?>
                                <option value="<?php echo $iList['id']; ?>" <?php if ($_POST['class'] == $iList['id']) {
                                    echo "selected";
                                } ?>><?php echo $iList['name']; ?></option>
                            <?php } ?>
                        </select>
                    </div>
                </div>






                <!-- add section start -->

                    <div class="card-box">
                        <table id="datatable" class="table table-striped table-bordered">
                            <thead>
                            <tr>
                                <th>Session</th>
                                <th>Term</th>
                                <th>Class Id</th>
                                <th>Arm Id</th>
                                <th>Class Description</th>
                                <th>Report Name</th>
                                <th>date Request</th>
                                <th>Status</th>
                                <th>Date Completed</th>
                                <th>Email sent To</th>

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
                                    <td><?php echo $db->getVal("select session from school_session	 where id='" . $iList['session'] . "'"); ?></td>
                                    <td><?php echo $iList['student_id']; ?></td>
                                    <td><?php echo $iList['last_name']; ?></td>
                                    <td><?php echo $iList['first_name']; ?></td>
                                    <td><?php echo $iList['other_name']; ?></td>

                                    <td><?php echo $iList['create_at']; ?></td>
                                    <td><?php echo $iList['status']; ?></td>
                                    <td><?php echo $iList['complete_date']; ?></td>
                                    <td><?php echo $iList['email']; ?></td>

                                </tr>
                            <?php } ?>
                            </tbody>
                        </table>
                    </div>

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
