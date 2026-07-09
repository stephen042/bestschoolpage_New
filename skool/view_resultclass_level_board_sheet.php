<?php
include('../config.php');
$validate = new validation();
$pageTitle = 'Class Board Sheet';
$Filename = 'class_level_board_sheet.php';
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
                                </div>
                            </div>
                        </div>

                        <div class="card-box">
                            <table id="datatable" class="table table-striped table-bordered">
                                <thead>
                                <tr>
                                    <th>Student ID</th>
                                    <th>First Name</th>
                                    <th>Last Name</th>
                                    <th>Other Name</th>
                                    <th>Agric</th>
                                    <th>BSCI</th>
                                    <th>BTECH</th>
                                    <th>CRS/IRS</th>
                                    <th>CEDU</th>
                                    <th>Computer Studies</th>
                                    <th>HECONS</th>
                                    <th>PHE</th>
                                    <th>Social Studies</th>
                                    <th>No. of Sub.</th>
                                    <th>Total Score</th>
                                    <th>Average(100%)</th>
                                    <th>Position</th>
                                    <th>Final Grade</th>


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
                                        <td><?php echo $iList['date']; ?></td>
                                        <td><?php echo $iList['date']; ?></td>
                                        <td><?php echo $iList['date']; ?></td>
                                        <td><?php echo $iList['date']; ?></td>
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