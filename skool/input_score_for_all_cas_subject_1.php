<?php
include('../config.php');
$validate = new validation();
$pageTitle = 'Input Score for All CAs';
$Filename = 'input_score_for_all_cas.php';
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
                                        <label class="col-lg-2 control-label " for="sorting">Sort by:</label>
                                        <div class="col-lg-4">
                                            <select  class="required form-control" name="sorting" id="sorting">
                                                <option>Student Id</option>
                                                <option>First Name</option>
                                                <option>Other Name</option>
                                                <option>Position</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>


                            <div class="card-box">
                                <table id="datatable" class="table table-striped table-bordered">
                                    <thead>
                                    <tr>
                                        <th>#</th>
                                        <th> Studeent ID </th>
                                        <th> Full Name </th>
                                        <th> Other Name </th>
                                        <th> CA1(30%) </th>
                                        <th> CA2(30%) </th>
                                        <th> CA3(40%) </th>
                                        <th> Total(100%) </th>

                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php
                                    $aryList = $db->getRows("select * from input_score order by id desc");
                                    foreach ($aryList as $iList) {
                                        $i = $i + 1;
                                        $aryPgAct["id"] = $iList['id'];
                                        $aryStudent= $db->getRow("select * from manage_student where id ='".$iList['id']."'");
                                        ?>
                                        <tr>
                                            <td><?php echo $i ?></td>
                                            <td><?php echo $aryStudent['student_id']; ?></td>
                                            <td><?php echo $aryStudent['first_name']; ?><?php echo $aryStudent['last_name']; ?></td>
                                            <td><?php echo $aryStudent['other_name']; ?></td>
                                            <td><?php echo $iList['ca_1']; ?></td>
                                            <td><?php echo $iList['ca_2']; ?></td>
                                            <td><?php echo $iList['ca_3']; ?></td>
                                            <td><?php echo $iList['total']; ?></td>
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
