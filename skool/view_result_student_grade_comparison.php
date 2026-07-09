<?php
include('../config.php');
$validate = new validation();
$pageTitle = 'Student Grade comparison';
$Filename = 'view_result_student_grade_comparison.php';
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
                        <div class="row">
                            <div class="col-md-9">

                                <div class="form-group clearfix">
                                    <div class="col-lg-4">
                                        <select class="required form-control" name="student">
                                            <option value="">Select Student</option>
                                            <?php $aryDetail = $db->getRows("select * from manage_student order by id desc");
                                            foreach ($aryDetail as $iList) {
                                                $i = $i + 1; ?>
                                                <option value="<?php echo $iList['id']; ?>" <?php if ($_POST['student'] == $iList['id']) {
                                                    echo "selected";
                                                } ?>><?php echo $iList['first_name']; ?></option>
                                            <?php } ?>
                                        </select>
                                    </div>

                                    <div class="col-lg-3">
                                        <select class="required form-control" name="subject">
                                            <option value="">Select Subject</option>
                                            <?php $aryDetail = $db->getRows("select * from school_subject order by id desc");
                                            foreach ($aryDetail as $iList) {
                                                $i = $i + 1; ?>
                                                <option value="<?php echo $iList['id']; ?>" <?php if ($_POST['subject'] == $iList['id']) {
                                                    echo "selected";
                                                } ?>><?php echo $iList['subject']; ?></option>
                                            <?php } ?>
                                        </select>
                                    </div>

                                    <div class="col-lg-3">
                                        <select class="required form-control" name="session">
                                            <option value="">Select Session</option>
                                            <?php $aryDetail = $db->getRows("select * from school_session order by id desc");
                                            foreach ($aryDetail as $iList) {
                                                $i = $i + 1; ?>
                                                <option value="<?php echo $iList['id']; ?>" <?php if ($_POST['session'] == $iList['id']) {
                                                    echo "selected";
                                                } ?>><?php echo $iList['session']; ?></option>
                                            <?php } ?>
                                        </select>
                                    </div>
									<div class="col-lg-2">
                                        <input type="submit" value="Click Here" name="">
                                    </div>
									<div class="col-lg-4">
                                     <span><b>Amina Sani Mukhtar (05/014)</b></span>

                                    </div>
                                </div>

                            </div>
                        </div>

                        <div class="card-box">
                            <table id="datatable" class="table table-striped table-bordered">
                                <thead>
                                <tr style="10px;">
                                    <th>Subject</th>
                                    <th>First Term</th>
                                    <th>Second Term</th>
                                    <th>Third Term</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php
                                $aryList = $db->getRows("select * from table name order by id desc");
                                foreach ($aryList as $iList) {
                                    $i = $i + 1;
                                    $aryPgAct["id"] = $iList['id'];
                                    $aryiList = $db->getRow("select * from 	table name where id ='".$iList['subject_code']."'");
                                    $arySession = $db-> getRow("select * from table name where id='".$iList['session']."'");
                                    ?>
                                    <tr>
                                        <td><?php echo $i ?></td>
                                        <td><?php echo $arySession['session']; ?></td>
                                        <td><?php echo $iList['subject']; ?></td>
                                        <td><?php echo $iList['date']; ?></td>
                                        <td><?php echo $iList['date']; ?></td>
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