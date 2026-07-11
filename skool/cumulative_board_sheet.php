<?php
include('../config.php');
include('inc.session-create.php');
$create_by_userid = (int)($_SESSION['create_by_userid'] ?? $_SESSION['userid'] ?? 0);
$classDetail = $db->getRow("select * from school_class where randomid='" . ($_GET['randomid'] ?? '') . "' and create_by_userid='" . $create_by_userid . "'");

$validate = new validation();
$pageTitle = 'Cumulative Broad Sheet';
$Filename = 'cumulative_board_sheet.php';

// Safe GET/POST parameter defaults
$get_randomid = $_GET['randomid'] ?? '';
$get_action   = $_GET['action'] ?? '';
$post_session = $_POST['session'] ?? '';
$post_term_id = $_POST['term_id'] ?? '';
?>
<!DOCTYPE html>
<html>

<head>
    <?php include('inc.meta.php'); ?>
    <link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/css?family=Droid+Serif" />
    <style>
        body,
        label,
        span,
        a,
        .gwt-Button {
            font-family: 'Droid Serif' !important;
        }

        .ddshgcfh {
            position: absolute;
            right: 170px;
            top: 77px;
            font-size: 50px;
        }

        .sectionza input[type=submit] {
            background: #1B3058;
            color: white;
            border: none;
        }

        .sectionza label {
            font-size: 17px;
            font-weight: 600;
        }

        .sectionza input,
        .sectionza select {
            color: inherit;
            font: inherit;
            margin: 0;
            width: 100px;
            margin-left: 10px;
            margin-top: 5px;
            height: 30px;
        }

        .page-title {
            font-size: 20px;
            margin-bottom: 0;
            margin-top: 7px;
            text-align: center;
            background: white;
            padding: 23px 0 30px 0px;
            border-bottom: 5px solid gainsboro;
        }

        .zasw {
            border: 1px solid gainsboro;
            height: 1000px;
            margin-top: 18px;
        }

        .zasw1 {
            height: auto;
            margin-top: 18px;
        }

        .sectionza {
            background: white;
            height: 1000px;
        }

        .top-serche input {
            padding: 5px 49px 5px 14px;
            border: 2px solid gainsboro;
            border-radius: 4px;
            position: relative;
        }

        .top-serche {
            padding: 32px 0 9px 30px;
        }

        .content-page>.content {
            margin-bottom: 60px;
            margin-top: 60px;
            padding: 20px 30px 15px 78px;
            background: white;
        }

        .zswqas ul {
            list-style: none;
        }

        .zswqas li a span i {
            font-size: 29px;
            padding-top: 9px;
        }

        .zswqas li a span {
            padding-right: 16px;
        }

        .zswqas li a {
            width: 239px;
            display: block;
            padding: 16px 14px 14px 18px;
            border-bottom: 2px solid gainsboro;
        }

        .topside-section ul {
            display: inline-flex;
            list-style: none;
        }

        .topside-section li {
            padding: 0 11px 0 0;
        }

        .topside-section {
            padding-top: 8px;
            border: 1px solid gainsboro;
            box-shadow: 1px 6px 4px gainsboro;
            padding: 14px 8px 11px 1px;
        }

        .zqw22 .panel-success>.panel-heading {
            background: white;
        }

        .zqw22 .nav.nav-tabs>li>a:hover,
        .nav.tabs-vertical>li>a:hover {
            color: black !important;
            font-weight: 700;
        }

        .zqw22 .nav.nav-tabs>li>a,
        .nav.tabs-vertical>li>a {
            border-top-right-radius: 10px;
            border-top-left-radius: 10px;
            font-size: 10px;
            height: 38px;
            margin-top: 0;
        }

        div.dataTables_filter label {
            font-weight: 400;
            white-space: nowrap;
            text-align: left;
            border: 1px solid gainsboro;
            padding: 4px 13px 4px 0px;
            border-radius: 5px;
            color: black;
        }

        #example .active {
            background: #1B3058;
            color: white;
        }

        .zqw22 .nav-tabs>li.active>a,
        .nav-tabs>li.active>a:focus,
        .nav-tabs>li.active>a:hover,
        .tabs-vertical>li.active>a,
        .tabs-vertical>li.active>a:focus,
        .tabs-vertical>li.active>a:hover {
            color: black !important;
            font-weight: 700;
            line-height: 38px;
            background: gainsboro;
        }

        .zqw22 .panel-success>.panel-heading {
            background: white;
            padding: 0;
        }

        .zqw22 .panel .panel-body {
            border-right: none !important;
            border: 1px solid gainsboro;
        }

        .gwt-Label {
            padding: 8px;
        }

        .zqw22 input {
            padding: 8px 3px 10px 0;
            border: 1px solid gainsboro;
            background: #dcdcdc45;
            border-radius: 5px;
            margin-right: 8px;
            margin-bottom: 5px;
        }

        .zqw22 button {
            border: 1px solid #1B3058;
            padding: 4px 3px 4px 3px;
            margin-right: 7px;
            background: transparent;
            color: #1B3058;
        }

        .zqw22 select {
            padding: 5px 0 8px 0;
            background: #dcdcdc2e;
        }

        .zqw22 .nav-tabs>li {
            padding: 0 4px 0 0;
        }

        #tab3success,
        #tab4success .middleCenterInner {
            border: 1px solid gainsboro;
            padding: 17px 11px 51px 19px;
        }

        #tab3success .middleCenterInner {
            border: 1px solid gainsboro;
            padding: 17px 11px 51px 19px;
        }

        #tab3success,
        #tab4success .BFOGCKB-c-h {
            border-bottom: 3px solid;
            width: 300px;
        }

        #tab3success .BFOGCKB-c-h {
            border-bottom: 3px solid;
            width: 300px;
        }

        #tab3success,
        #tab4success {
            border: 1px solid gainsboro;
            padding: 14px 4px 42px 11px;
            width: 361px;
        }

        #tab3success,
        #tab4success .gwt-DecoratorPanel {
            padding: 21px 21px 43px 4px;
        }

        #tab3success .gwt-DecoratorPanel {
            padding: 21px 21px 43px 4px;
        }

        .zqw22 .panel .panel-body {
            border-bottom: 3px solid gainsboro !important;
        }

        .zqw22 .nav.nav-tabs>li>a,
        .nav.tabs-vertical>li>a {
            background: #dcdcdc4f !important;
        }

        .zqw22 .nav.nav-tabs>li>a,
        .nav.tabs-vertical>li>a {
            color: black !important;
            font-weight: 700;
            line-height: 38px;
            background: gainsboro;
        }

        .xza {
            margin: 0;
            width: 294px;
            border-bottom: 1px solid;
        }

        .dataTables_paginate a {
            background-color: transparent;
            margin: 0 0px 0;
            padding: 8px 15px 9px;
            color: white;
            cursor: pointer;
            border: none;
        }

        .zqw22 .nav-tabs>li.active,
        .nav-tabs>li.active:focus,
        .nav-tabs>li.active:hover,
        .tabs-vertical>li.active,
        .tabs-vertical>li.active:focus,
        .tabs-vertical>li.active:hover {
            color: black !important;
            font-weight: 700;
        }

        .zswqas .activate a {
            width: 239px;
            display: block;
            padding: 16px 14px 14px 18px;
            border-bottom: 2px solid gainsboro;
            background: #1B3058;
            color: white;
        }

        .zqw22 .nav-tabs>li.active>a,
        .nav-tabs>li.active>a:focus,
        .nav-tabs>li.active>a:hover,
        .tabs-vertical>li.active>a,
        .tabs-vertical>li.active>a:focus,
        .tabs-vertical>li.active>a:hover {
            border-bottom: 3px solid #1B3058;
        }

        .topside-section li a {
            border: 1px solid #1B3058;
            padding: 5px 5px 4px 5px;
            display: block;
        }

        .zswqas li a:hover {
            width: 239px;
            display: block;
            padding: 16px 14px 14px 18px;
            border-bottom: 2px solid gainsboro;
            background: #1B3058;
            color: white;
        }

        .zswqas .active {
            width: 239px;
            display: block;
            padding: 16px 14px 14px 18px;
            border-bottom: 2px solid gainsboro;
            background: #1B3058;
            color: white;
        }

        .Wizard-a1 #example_length {
            display: none;
        }

        div.dataTables_filter label {
            font-weight: 400;
            white-space: nowrap;
            text-align: left;
        }

        div.dataTables_filter input {
            margin-left: .5em;
            display: inline-block;
            float: right;
            border: none;
        }

        div.dataTables_filter label {
            padding: 10px;
        }

        div.dataTables_filter input {
            margin-left: .5em;
            display: inline-block;
            float: right;
        }

        div.dataTables_filter {
            text-align: center;
        }

        .Wizard-a1 .zwq img {
            width: 50px;
        }

        .Wizard-a1 .zwq {
            padding-right: 8px;
            float: left;
        }

        .Wizard-a1 .setting {
            display: none;
        }

        .Wizard-a1 .dataTables_info {
            margin: 0 auto !important;
            text-align: center;
            font-size: 12px;
            float: initial;
            position: absolute;
            bottom: 11px;
            left: 0;
            right: 0;
        }

        #example {
            width: 85% !important;
            margin: 0 auto;
        }

        div.dataTables_filter input {
            width: 70%;
        }

        div.dataTables_filter label {
            line-height: 23px;
        }

        .dataTables_paginate #example_previous:before {
            content: "";
            width: 0;
            height: 0;
            border-top: 6px solid transparent;
            border-right: 12px solid #555;
            border-bottom: 6px solid transparent;
            position: absolute;
            z-index: 999999;
            left: 15px;
            bottom: 3px;
        }

        .Wizard-a1 .dataTables_info {
            position: sticky !important;
        }

        div.dataTables_paginate {
            position: relative;
            top: -47px;
        }

        .dataTables_paginate a {
            background-color: transparent;
            margin: 0 0px 0;
            padding: 8px 15px 9px;
            color: white;
            cursor: pointer;
            border: none;
            position: static;
        }

        .dataTables_paginate .next {
            background: none;
            border: navajowhite;
            position: relative;
            color: white !important;
        }

        div.dataTables_paginate {
            margin: 0;
            white-space: nowrap;
            text-align: center !important;
            padding-top: 27px;
        }

        .dataTables_paginate .disabled {
            background: none;
            color: white;
            border: none !important;
            padding: unset;
            display: block;
            width: 90%;
            color: transparent !important;
            margin: 0 auto;
        }

        div.dataTables_info {
            white-space: nowrap;
            padding-top: 0px;
        }

        .dataTables_paginate #example_next:before {
            content: "";
            width: 0;
            height: 0;
            border-top: 6px solid transparent;
            border-left: 12px solid #555;
            border-bottom: 6px solid transparent;
            position: absolute;
            z-index: 999999;
            right: 0;
            bottom: 9px;
            top: 4px;
        }

        div.dataTables_paginate {
            margin: 0;
            white-space: nowrap;
            text-align: center !important;
        }

        .paging_simple_numbers span {
            opacity: 0;
        }

        #example td {
            padding: 15px 11px 18px 13px;
            border-bottom: 3px solid;
            margin: 0 0 0;
        }

        #example .active:hover {
            background: #1B3058;
            color: white;
        }

        .Wizard-a1 .sorting_1 {
            display: none;
        }

        .dataTables_filter label:before {
            position: absolute;
            right: 46px;
            top: 62px !important;
            border: 1px solid #1B3058;
        }

        .dataTables_filter:before {
            content: '';
            position: absolute;
        }

        div.dataTables_filter label {
            position: relative;
            width: 85%;
            text-align: left;
        }

        div.dataTables_filter {
            margin-top: 20px;
        }

        .sectsab li {
            list-style: none;
        }

        div.dataTables_paginate {
            margin: 0 auto;
        }
    </style>
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
                            <h4 class="page-title"><?php echo $pageTitle; ?> <?php echo is_array($stat) ? '' : $stat; ?></h4>
                        </div>
                    </div>
                    <div class="row">
                        <div class="sectionza">
                            <div class="col-md-12 col-xm-12">
                                <div class="col-md-4 col-xm-12">
                                    <div class="zasw ">
                                        <div class="zawq Wizard-a1">
                                            <table id="example" class="display">
                                                <thead class="setting">
                                                    <tr>
                                                        <th>Position</th>
                                                        <th>Position</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php
                                                    if (($_SESSION['usertype'] ?? '') == '1') {
                                                        $iSchoolRegisterStaffid = $db->getVal("select username from school_register where id='" . $_SESSION['userid'] . "' order by id desc");
                                                        $iStaffManageId = $db->getVal("select id from staff_manage where staff_id ='" . $iSchoolRegisterStaffid . "'");
                                                        $iConCatScholCls = $db->getVal("select GROUP_CONCAT(school_class) from class_teacher where staff_id='" . $iStaffManageId . "'");
                                                        $iConCatScholCls = $iConCatScholCls ? $iConCatScholCls : '0';
                                                        $aryDetail = $db->getRows("select * from school_class where create_by_userid='" . $create_by_userid . "' and id IN ($iConCatScholCls)");
                                                    } else {
                                                        $aryDetail = $db->getRows("select * from school_class where create_by_userid='" . $create_by_userid . "'");
                                                    }

                                                    foreach ($aryDetail as $iList) {
                                                    ?>
                                                        <tr>
                                                            <td style="padding:0px;"></td>
                                                            <td class="sectsab <?php if ($get_randomid == $iList['randomid']) {
                                                                                    echo "active";
                                                                                } ?>">
                                                                <a href="<?php echo $Filename; ?>?action=board_sheet&randomid=<?php echo $iList['randomid']; ?>">
                                                                    <ul>
                                                                        <span class="zwq"> <i class="fa fa-book" style="font-size:48px"></i> </span>
                                                                        <span class="subject"> <?php echo $iList['name']; ?> <br /></span>
                                                                    </ul>
                                                                </a>
                                                            </td>
                                                        </tr>
                                                    <?php } ?>
                                                </tbody>
                                                <tfoot>
                                                    <tr class="setting">
                                                        <th>Name</th>
                                                        <th>Position</th>
                                                    </tr>
                                                </tfoot>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-8 col-xm-12">
                                    <form method="POST" action="">
                                        <label>Session:</label>
                                        <select name="session" id="session" required>
                                            <option value=""> Session</option>
                                            <?php
                                            $aryDetail = $db->getRows("select * from school_session where create_by_userid='" . $create_by_userid . "'");
                                            foreach ($aryDetail as $iList) {
                                            ?>
                                                <option value="<?php echo $iList['id']; ?>" <?php if ($post_session == $iList['id']) {
                                                                                                echo "selected";
                                                                                            } ?>><?php echo $iList['session']; ?></option>
                                            <?php } ?>
                                        </select>

                                        <label>Term:</label>
                                        <select name="term_id" id="term_id" required>
                                            <option value=""> Term</option>
                                            <?php
                                            $aryDetail = $db->getRows("select * from school_term where create_by_userid='" . $create_by_userid . "'");
                                            foreach ($aryDetail as $iList) {
                                            ?>
                                                <option value="<?php echo $iList['id']; ?>" <?php if ($post_term_id == $iList['id']) {
                                                                                                echo "selected";
                                                                                            } ?>><?php echo $iList['term']; ?></option>
                                            <?php } ?>
                                        </select>

                                        <input type="hidden" value="<?php echo e($get_randomid); ?>" name="randomid">
                                        <input type="hidden" value="<?php echo e($get_action); ?>" name="action">
                                        <input type="submit" value="Click Here" name="" />
                                    </form>
                                    <br>
                                    <table cellspacing="5" cellpadding="0">
                                        <tbody>
                                            <tr>
                                                <td align="left" style="vertical-align: top;">
                                                    <a href="<?php echo SKOOL_URL; ?>cummulative_broad_sheet_pfd.php?randomid=<?php echo $get_randomid; ?>&session=<?php echo $post_session; ?>&term_id=<?php echo $post_term_id; ?>" class="gwt-Button" style="background: #1b3058;color: #fff; padding: 10px; font-size: 12px;" target="_blank">Print Cumulative Broad Sheet</a>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                    <div class="zasw1">
                                        <div class="card-box">
                                            <tr>
                                                <td>
                                                    <div class="boldInfoLabel"> SESSION: <?php echo $db->getVal("select session from school_session where id= '" . $post_session . "'"); ?>
                                                        <?php echo $db->getVal("select term from school_term where id= '" . $post_term_id . "'"); ?>
                                                    </div>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <div class="infoLabel"> Class: <?php echo $db->getVal("select name from school_class where id ='" . ($classDetail['id'] ?? 0) . "' and create_by_userid='" . $create_by_userid . "' "); ?> </div>
                                                </td>
                                            </tr>
                                            <div class="card-box table-responsive tablthisresponsive">
                                                <table class="table table-striped table-bordered tablthisresponsive">
                                                    <thead>
                                                        <tr>
                                                            <th>Student ID</th>
                                                            <th>First Name</th>
                                                            <th>Last Name</th>
                                                            <th>Other Name</th>
                                                            <?php
                                                            $schoolClassDetail = $db->getRows("select * from school_subject where class_id = '" . ($classDetail['id'] ?? 0) . "' and create_by_userid='" . $create_by_userid . "'");
                                                            $i = 0;
                                                            $subid = ''; // FIXED: Explicitly initialized as string to prevent Array to string conversion warning
                                                            foreach ($schoolClassDetail as $iListDetail) {
                                                                $i = $i + 1;
                                                                $subid .= $iListDetail['id'] . ',';
                                                            ?>
                                                                <th><?php echo $iListDetail['subject']; ?></th>
                                                            <?php } ?>
                                                            <th>No. of Sub.</th>
                                                            <th>Total Score</th>
                                                            <th>Average(100%)</th>
                                                            <th>Grade</th>
                                                            <th>Position</th>
                                                        </tr>
                                                        <?php
                                                        $aryListss = array();
                                                        if (!empty($classDetail['id']) && !empty($post_session) && !empty($post_term_id)) {
                                                            $aryListss = $db->getRows("select * from manage_student where class = '" . $classDetail['id'] . "' and session= '" . $post_session . "' and term_id= '" . $post_term_id . "' and create_by_userid='" . $create_by_userid . "' order by first_name asc");
                                                        }

                                                        $tStuden = 0;
                                                        $classTotal = 0;
                                                        $highLow = array();
                                                        $student_two = array();
                                                        $grandTotal = '';

                                                        foreach ($aryListss as $aryStudent) {
                                                            $tStuden = $tStuden + 1;
                                                            $s_id = $aryStudent['id'];
                                                            ${"d" . $s_id} = 0; // Initialize dynamic variable for total calculation
                                                        ?>
                                                            <tr class="flexTable-OddRow">
                                                                <td>
                                                                    <div class="clickableElement" style="width: 100%;"> <?php echo $aryStudent['student_id']; ?> </div>
                                                                </td>
                                                                <td>
                                                                    <div class="resultDataCell" style="width: 100%;"> <?php echo $aryStudent['first_name']; ?> </div>
                                                                </td>
                                                                <td>
                                                                    <div class="resultDataCell" style="width: 100%;"> <?php echo $aryStudent['last_name']; ?> </div>
                                                                </td>
                                                                <td>
                                                                    <div class="resultDataCell" style="width: 100%;"> <?php echo $aryStudent['other_name']; ?> </div>
                                                                </td>
                                                                <?php
                                                                $totalSub = 0;
                                                                foreach ($schoolClassDetail as $iListDetail) {
                                                                    $totalSub = $totalSub + 1;
                                                                ?>
                                                                    <td>
                                                                        <div class="gwt-Label" style="width: 100%;">
                                                                            <?php
                                                                            $totalscore = $db->getRow("select SUM(score) as total_score from input_score_class_teacher where class_id='" . $classDetail['id'] . "' and subject_id='" . $iListDetail['id'] . "' and student_id = '" . $aryStudent['id'] . "' and create_by_userid='" . $create_by_userid . "'");
                                                                            $total_val = $totalscore['total_score'] ?? 0;
                                                                            echo $total_val;
                                                                            ${"d" . $s_id} += $total_val;
                                                                            ?>
                                                                        </div>
                                                                    </td>
                                                                <?php }
                                                                $grandTotal = ${"d" . $s_id};
                                                                ?>
                                                                <td><?php echo $totalSub; ?></td>
                                                                <td>
                                                                    <div class="resultDataCell" style="width: 100%;">
                                                                        <?php
                                                                        echo $grandTotal;
                                                                        $student_two["$s_id"] = $grandTotal;
                                                                        ?>
                                                                    </div>
                                                                </td>
                                                                <td>
                                                                    <div class="resultDataCell" style="width: 100%;">
                                                                        <?php
                                                                        $avg = ($i > 0) ? ($grandTotal / $i) : 0;
                                                                        echo round($avg, 2);
                                                                        $classTotal += $avg;
                                                                        $highLow[] = $avg;
                                                                        ?>
                                                                    </div>
                                                                </td>
                                                                <td>
                                                                    <?php echo $db->getVal("select grade from school_grade where create_by_userid='" . $create_by_userid . "' and minimum_number <= " . $avg . " and maximum_number >= " . $avg . ""); ?>
                                                                </td>
                                                                <td id="<?php echo $s_id; ?>"></td>
                                                            </tr>
                                                        <?php } ?>
                                                    </thead>
                                                </table>
                                                <?php
                                                function setPosition($standings)
                                                {
                                                    $rankings = array();
                                                    arsort($standings);
                                                    $rank = 1;
                                                    $tie_rank = 0;
                                                    $prev_score = -1;
                                                    $count = 0;

                                                    foreach ($standings as $name => $score) {
                                                        if ($score != $prev_score) {
                                                            $count = 0;
                                                            $prev_score = $score;
                                                            $rankings[$name] = array('score' => $score, 'rank' => $rank);
                                                        } else {
                                                            $prev_score = $score;
                                                            if ($count++ == 0) {
                                                                $tie_rank = $rank - 1;
                                                            }
                                                            $rankings[$name] = array('score' => $score, 'rank' => $tie_rank);
                                                        }
                                                        $rank++;
                                                    }
                                                    return $rankings;
                                                }

                                                $rankedScores = array();
                                                if ($grandTotal !== '' && !empty($student_two)) {
                                                    $rankedScores = setPosition($student_two);
                                                }

                                                foreach ($rankedScores as $studentwa => $data) {
                                                    $rank = $data['rank'];
                                                    echo "<script>document.getElementById('$studentwa').innerHTML=$rank;</script>";
                                                }
                                                ?>

                                                <?php if ($tStuden > 0) { ?>
                                                    <table class="table table-striped table-bordered">
                                                        <tr>
                                                            <td><span>No. of Students: <?php echo $tStuden; ?></span></td>
                                                            <td><span>Class Average:
                                                                    <?php $classAvg = $classTotal / $tStuden;
                                                                    echo round($classAvg, 2); ?>
                                                                </span></td>
                                                        </tr>
                                                        <tr>
                                                            <td><span>Highest Average in Class: <?php echo !empty($highLow) ? round(max($highLow), 2) : 0; ?></span></td>
                                                            <td><span>Lowest Average in Class: <?php echo !empty($highLow) ? round(min($highLow), 2) : 0; ?></span></td>
                                                        </tr>
                                                    </table>
                                                <?php } ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php include('inc.footer.php'); ?>
        </div>
    </div>

    <?php include('inc.js.php'); ?>
    <script>
        (function() {
            $(function() {
                var toggle;
                return toggle = new Toggle('.zswqas');
            });

            this.Toggle = (function() {
                class Toggle {
                    constructor(toggleClass) {
                        this.el = $(toggleClass);
                        this.tabs = this.el.find(".xz");
                        this.panels = this.el.find(".panel");
                        this.bind();
                    }

                    show(index) {
                        var activePanel, activeTab;
                        this.tabs.removeClass('activate');
                        activeTab = this.tabs.get(index);
                        $(activeTab).addClass('activate');
                        this.panels.hide();
                        activePanel = this.panels.get(index);
                        return $(activePanel).show();
                    }

                    bind() {
                        return this.tabs.unbind('click').bind('click', (e) => {
                            return this.show($(e.currentTarget).index());
                        });
                    }

                };

                Toggle.prototype.el = null;
                Toggle.prototype.tabs = null;
                Toggle.prototype.panels = null;

                return Toggle;

            }).call(this);

        }).call(this);
    </script>
    <script>
        var header = document.getElementById("example");
        var btns = header.getElementsByClassName("sectsab");
        for (var i = 0; i < btns.length; i++) {
            btns[i].addEventListener("click", function() {
                var current = document.getElementsByClassName("active");
                if (current.length > 0) {
                    current[0].className = current[0].className.replace(" active", "");
                }
                this.className += " active";
            });
        }
    </script>
    <script>
        $('#example').dataTable({
            "pageLength": 5
        });
    </script>
</body>

</html>