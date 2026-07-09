<?php
include('../config.php');
$validate = new validation();
$pageTitle = 'Subject Grade Analysis';
$Filename = 'view_result_subject_grade_analysis.php';
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
                                    <div class="col-lg-6">
                                    <select name="term_id" id="term_id" required>
                                    <option value=""> Term</option>
                                    <?php $aryDetail=$db->getRows("select * from  school_term where create_by_userid='".$create_by_userid."'");
                                    foreach($aryDetail as $iList)
                                    {	$i=$i+1;?>
                                    <option value="<?php echo $iList['id']; ?>" <?php  if($_GET['term_id']==$iList['id']) { echo "selected"; }  ?>><?php echo $iList['term']; ?></option>
                                    <?php }?>
                                    </select>
                                        <select class="required form-control" name="subject">
                                            <option value="">Select Class</option>
                                            <?php $aryDetail = $db->getRows("select * from school_subject order by id desc");
                                            foreach ($aryDetail as $iList) {
                                                $i = $i + 1; ?>
                                                <option value="<?php echo $iList['id']; ?>" <?php if ($_POST['subject'] == $iList['id']) {
                                                    echo "selected";
                                                } ?>><?php echo $iList['subject']; ?></option>
                                            <?php } ?>
                                        </select>
                                    </div>

                                    <div class="col-lg-6">
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
                                </div>

                            </div>
                        </div>

                        <div class="card-box">
                            <table id="datatable" class="table table-striped table-bordered">
                                <thead>
                                <tr style="10px;">
                                    <th>Subject</th>
                                    <th>1st A </th>
                                    <th>1st B</th>
                                    <th>1st C</th>
                                    <th>1st D</th>
                                    <th>1st F</th>
                                    <th>Total Student</th>
                                    <th>2nd A </th>
                                    <th>2nd B</th>
                                    <th>2nd C</th>
                                    <th>2nd D</th>
                                    <th>2nd F</th>
                                    <th>Total Student</th>
                                    <th>3rd A </th>
                                    <th>3rd B</th>
                                    <th>3rd C</th>
                                    <th>3rd D</th>
                                    <th>3rd F</th>
                                    <th>Total Student</th>
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
                                        <td><?php echo $aryiList['subject']; ?></td>
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