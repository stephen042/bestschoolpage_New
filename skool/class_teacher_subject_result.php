<?php
// ============================================================================
// CLASS TEACHER SUBJECT RESULT
// ============================================================================
// Handles subject result management for class teachers
// ============================================================================

include('../config.php');
include('inc.session-create.php');

// ============================================================================
// SESSION VARIABLES INITIALIZATION
// ============================================================================
$sessionUserId = (int)($_SESSION['userid'] ?? 0);
$sessionUsername = (string)($_SESSION['username'] ?? '');
$sessionEmail = (string)($_SESSION['email'] ?? '');
$isSchoolOwnerSession = ($sessionUserId > 0 && $sessionUserId === (int)($_SESSION['create_by_userid'] ?? $sessionUserId));
$create_by_userid = (int)($_SESSION['create_by_userid'] ?? $_SESSION['userid'] ?? 0);
$create_by_usertype = (string)($_SESSION['usertype'] ?? '');

$PageTitle = "Class Teacher Subject Result";
$FileName = 'class_teacher_subject_result.php';

// ============================================================================
// GET CLASS AND SUBJECT DETAILS
// ============================================================================
$aryList = [];
$randomid = isset($_GET['randomid']) ? trim($_GET['randomid']) : '';

if (!empty($randomid)) {
    $aryList = db_get_row(
        "SELECT * FROM school_class WHERE randomid = ? AND create_by_userid = ?",
        [$randomid, $create_by_userid]
    );
}

$subjectid = [];
$subjectParam = isset($_GET['subject']) ? trim($_GET['subject']) : '';

if (!empty($subjectParam)) {
    $subjectid = db_get_row(
        "SELECT * FROM school_subject WHERE randomid = ? AND create_by_userid = ?",
        [$subjectParam, $create_by_userid]
    );
}

// ============================================================================
// VALIDATION
// ============================================================================
$validate = new Validation();

// ============================================================================
// SESSION MESSAGES
// ============================================================================
if (!empty($_SESSION['success'])) {
    $stat['success'] = $_SESSION['success'];
    unset($_SESSION['success']);
}

// ============================================================================
// PROCESS PDF BUTTON
// ============================================================================
if (isset($_POST['pdf'])) {
    if (empty($_POST['subject'])) {
        $stat['error'] = "Select a subject and open";
    }
}

// ============================================================================
// GET TEACHER'S ASSIGNED CLASSES
// ============================================================================
$classList = [];
$assignedClassIds = [];

if ($_SESSION['usertype'] == '1') {
    // Get staff ID from staff_manage
    $staffDetails = db_get_row(
        "SELECT id FROM staff_manage WHERE staff_id = ? OR id = ?",
        [$sessionUsername, $sessionUserId]
    );

    if (!empty($staffDetails)) {
        $staffId = (int)$staffDetails['id'];

        // Get assigned class IDs from class_teacher table
        $assignedClasses = db_get_rows(
            "SELECT school_class FROM class_teacher WHERE staff_id = ? AND create_by_userid = ?",
            [$staffId, $create_by_userid]
        );

        foreach ($assignedClasses as $class) {
            $assignedClassIds[] = (int)$class['school_class'];
        }

        if (!empty($assignedClassIds)) {
            $classIds = implode(',', array_map('intval', $assignedClassIds));
            $classList = db_get_rows(
                "SELECT * FROM school_class WHERE create_by_userid = ? AND id IN ($classIds) ORDER BY name ASC",
                [$create_by_userid]
            );
        }
    }
} else {
    $classList = db_get_rows(
        "SELECT * FROM school_class WHERE create_by_userid = ? ORDER BY name ASC",
        [$create_by_userid]
    );
}

// ============================================================================
// GET SESSIONS, TERMS, AND SUBJECTS FOR FORMS
// ============================================================================
$sessionList = db_get_rows(
    "SELECT * FROM school_session WHERE create_by_userid = ? ORDER BY id ASC",
    [$create_by_userid]
);

$termList = db_get_rows(
    "SELECT * FROM school_term WHERE create_by_userid = ? ORDER BY id ASC",
    [$create_by_userid]
);

// Get subjects for the selected class
$subjectList = [];
if (!empty($aryList) && isset($aryList['id'])) {
    $subjectList = db_get_rows(
        "SELECT * FROM school_subject WHERE class_id = ? AND create_by_userid = ? ORDER BY subject ASC",
        [(int)$aryList['id'], $create_by_userid]
    );
}

// Get assessments for the selected class
$assessmentList = [];
if (!empty($aryList) && isset($aryList['id'])) {
    $assessmentList = db_get_rows(
        "SELECT * FROM school_assessment WHERE create_by_userid = ? AND class_id = ? ORDER BY id DESC",
        [$create_by_userid, (int)$aryList['id']]
    );
}

// ============================================================================
// GET STUDENTS AND SCORES FOR DISPLAY
// ============================================================================
$students = [];
$scoreData = [];
$grandTotal = 0;
$classTotal = 0;
$highLow = [];
$studentScores = [];

if (isset($_GET['action']) && $_GET['action'] == 'input_score' && !empty($aryList)) {
    $postSession = isset($_POST['session']) ? (int)$_POST['session'] : 0;
    $postTerm = isset($_POST['term_id']) ? (int)$_POST['term_id'] : 0;
    $postSubject = isset($_POST['subject']) ? (int)$_POST['subject'] : 0;
    $postAssessments = isset($_POST['assesment']) ? array_map('intval', $_POST['assesment']) : [];

    if ($postSession > 0 && $postTerm > 0 && $postSubject > 0 && !empty($postAssessments)) {
        // Get students for the class
        $students = db_get_rows(
            "SELECT * FROM manage_student 
             WHERE class = ? AND session = ? AND term_id = ? AND create_by_userid = ? 
             ORDER BY first_name ASC",
            [(int)$aryList['id'], $postSession, $postTerm, $create_by_userid]
        );

        // Get scores for each student
        foreach ($students as $student) {
            $studentId = (int)$student['id'];
            $totalScore = 0;
            $scores = [];

            foreach ($postAssessments as $assessmentId) {
                $score = db_get_row(
                    "SELECT score FROM input_score_class_teacher 
                     WHERE assesment_id = ? AND student_id = ? AND subject_id = ? AND create_by_userid = ?",
                    [$assessmentId, $studentId, $postSubject, $create_by_userid]
                );

                $scoreValue = isset($score['score']) ? (float)$score['score'] : 0;
                $scores[$assessmentId] = $scoreValue;
                $totalScore += $scoreValue;
            }

            $scoreData[$studentId] = [
                'student' => $student,
                'scores' => $scores,
                'total' => $totalScore
            ];

            $studentScores[$studentId] = $totalScore;
            $classTotal += $totalScore;
            $highLow[] = $totalScore;
        }
    }
}

// ============================================================================
// HELPER FUNCTION FOR RANKING
// ============================================================================
if (!function_exists('setPosition')) {
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
}

$rankedScores = [];
if (!empty($studentScores)) {
    $rankedScores = setPosition($studentScores);
}
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

        .sectionza input[type=checkbox],
        input[type=radio] {
            margin: 4px 5px 0;
        }

        .sectionza input[type=submit] {
            background: #1B3058;
            color: white;
            border: none;
            padding: 3px 19px;
            cursor: pointer;
        }

        .printer_pdf {
            background: #1B3058;
            color: white;
            border: none;
            padding: 3px 19px;
            text-decoration: none;
            display: inline-block;
            cursor: pointer;
        }

        .printer_pdf:hover {
            color: white;
            text-decoration: none;
        }

        .sectionza label {
            font-size: 17px;
            font-weight: 600;
        }

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
            height: auto;
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
            position: relative;
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

        .table-img {
            width: 50px;
        }

        .form-inline select,
        .form-inline input {
            margin: 5px 5px;
        }

        .btn-group-print {
            margin-top: 10px;
        }

        .btn-group-print a,
        .btn-group-print button {
            margin-right: 5px;
        }
    </style>
</head>

<body class="fixed-left">
    <div id="wrapper">
        <?php include('inc.header.php'); ?>
        <?php include('inc.sideleft.php'); ?>
        <div class="content-page">
            <div class="content">
                <div class="container">
                    <div class="row">
                        <div class="col-sm-12">
                            <h4 class="page-title"><?php echo e($PageTitle); ?></h4>
                            <?php echo showMessage($stat); ?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="sectionza">
                            <div class="col-md-12 col-xm-12">
                                <div class="col-md-4 col-xm-12">
                                    <div class="zasw">
                                        <div class="zawq Wizard-a1">
                                            <table id="example" class="display">
                                                <thead class="setting">
                                                    <tr>
                                                        <th>Position</th>
                                                        <th>Position</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($classList as $iList): ?>
                                                        <tr>
                                                            <td style="padding:0px;"></td>
                                                            <td class="sectsab <?php if (isset($_GET['randomid']) && $_GET['randomid'] == $iList['randomid']) {
                                                                                    echo "active";
                                                                                } ?>">
                                                                <a href="<?php echo $FileName; ?>?action=input_score&randomid=<?php echo e($iList['randomid']); ?>">
                                                                    <ul>
                                                                        <li>
                                                                            <span class="zwq">
                                                                                <img class="table-img" src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTbRnETjp3LR55_QTrkge0ZW1VZwhnBGrZuDDM4DSh6dQSMFG21" alt="Class Icon">
                                                                            </span>
                                                                            <?php echo e($iList['name']); ?>
                                                                        </li>
                                                                    </ul>
                                                                </a>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
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

                                <!-- Filter Form -->
                                <form method="post" action="" class="form-inline">
                                    <select name="session" id="session" required>
                                        <option value="">Session</option>
                                        <?php foreach ($sessionList as $sList): ?>
                                            <option value="<?php echo (int)$sList['id']; ?>" <?php if (isset($_POST['session']) && $_POST['session'] == $sList['id']) {
                                                                                                    echo "selected";
                                                                                                } ?>>
                                                <?php echo e($sList['session']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>

                                    <select name="term_id" id="term_id" required>
                                        <option value="">Term</option>
                                        <?php foreach ($termList as $tList): ?>
                                            <option value="<?php echo (int)$tList['id']; ?>" <?php if (isset($_POST['term_id']) && $_POST['term_id'] == $tList['id']) {
                                                                                                    echo "selected";
                                                                                                } ?>>
                                                <?php echo e($tList['term']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>

                                    <select name="subject">
                                        <option value="">Subject</option>
                                        <?php foreach ($subjectList as $subList): ?>
                                            <option value="<?php echo (int)$subList['id']; ?>" <?php if (isset($_POST['subject']) && $_POST['subject'] == $subList['id']) {
                                                                                                    echo "selected";
                                                                                                } ?>>
                                                <?php echo e($subList['subject']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>

                                    <input type="hidden" value="<?php echo isset($_POST['randomid']) ? e($_POST['randomid']) : ''; ?>" name="randomid">
                                    <input type="hidden" value="<?php echo isset($_POST['action']) ? e($_POST['action']) : ''; ?>" name="action">
                                    <input type="hidden" value="<?php echo isset($iList['randomid']) ? e($iList['randomid']) : ''; ?>" name="new_randomid">

                                    <label>Ass:</label>
                                    <?php foreach ($assessmentList as $assList): ?>
                                        <span><?php echo e($assList['assesment'] ?? ''); ?></span>
                                        <input type="checkbox" name="assesment[]" value="<?php echo (int)$assList['id']; ?>"
                                            <?php if (isset($_POST['assesment']) && in_array($assList['id'], $_POST['assesment'])) {
                                                echo "checked";
                                            } ?>>
                                    <?php endforeach; ?>

                                    <input type="submit" value="Open" name="" />
                                </form>

                                <!-- Print Buttons -->
                                <form action="" method="post" class="btn-group-print">
                                    <br>
                                    <?php
                                    $subjectId = isset($_POST['subject']) ? (int)$_POST['subject'] : 0;
                                    $sessionId = isset($_POST['session']) ? (int)$_POST['session'] : 0;
                                    $termId = isset($_POST['term_id']) ? (int)$_POST['term_id'] : 0;
                                    $assesments = isset($_POST['assesment']) ? implode('-', array_map('intval', $_POST['assesment'])) : '';
                                    ?>

                                    <?php if ($subjectId == 0): ?>
                                        <button type="submit" name="pdf" class="printer_pdf">Print Subject Result</button>
                                    <?php else: ?>
                                        <a href="<?php echo SKOOL_URL; ?>class_teacher_subject_result_pdf.php?randomid=<?php echo isset($_GET['randomid']) ? e($_GET['randomid']) : ''; ?>&subject=<?php echo $subjectId; ?>&session=<?php echo $sessionId; ?>&term_id=<?php echo $termId; ?>&assesments=<?php echo $assesments; ?>" class="printer_pdf" target="_blank">Print Subject Result</a>
                                    <?php endif; ?>

                                    <?php if ($subjectId == 0): ?>
                                        <button type="submit" name="pdf" class="printer_pdf">Print Empty Score Sheet</button>
                                    <?php else: ?>
                                        <a href="<?php echo SKOOL_URL; ?>class_teacher_subject_empty_result_pdf.php?randomid=<?php echo isset($_GET['randomid']) ? e($_GET['randomid']) : ''; ?>&subject=<?php echo $subjectId; ?>&session=<?php echo $sessionId; ?>&term_id=<?php echo $termId; ?>&assesments=<?php echo $assesments; ?>" class="printer_pdf" target="_blank">Print Empty Score Sheet</a>
                                    <?php endif; ?>
                                </form>

                                <!-- Score Display -->
                                <?php if (isset($_GET['action']) && $_GET['action'] == 'input_score' && !empty($scoreData)): ?>
                                    <div class="col-md-8 col-xm-12">
                                        <div class="zasw1">
                                            <div class="card-box table-responsive tablthisresponsive">
                                                <table class="table table-striped table-bordered tablthisresponsive">
                                                    <thead>
                                                        <tr>
                                                            <th>Student id</th>
                                                            <th>First Name</th>
                                                            <th>Last Name</th>
                                                            <?php
                                                            $postAssessments = isset($_POST['assesment']) ? array_map('intval', $_POST['assesment']) : [];
                                                            foreach ($postAssessments as $assId):
                                                                $assDetail = db_get_row(
                                                                    "SELECT assesment, percentage FROM school_assessment WHERE id = ?",
                                                                    [$assId]
                                                                );
                                                            ?>
                                                                <th><?php echo e($assDetail['assesment'] ?? 'Ass') . ' ' . e($assDetail['percentage'] ?? ''); ?></th>
                                                            <?php endforeach; ?>
                                                            <th>Total</th>
                                                            <th>Grade</th>
                                                            <th>Position</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php
                                                        $tStudent = 0;
                                                        $classTotal = 0;
                                                        $highLow = [];
                                                        $studentTotals = [];

                                                        foreach ($scoreData as $studentId => $data):
                                                            $tStudent++;
                                                            $student = $data['student'];
                                                            $totalScore = $data['total'];
                                                            $studentTotals[$studentId] = $totalScore;
                                                            $classTotal += $totalScore;
                                                            $highLow[] = $totalScore;
                                                        ?>
                                                            <tr>
                                                                <td><?php echo e($student['student_id'] ?? ''); ?></td>
                                                                <td><?php echo e($student['first_name'] ?? ''); ?></td>
                                                                <td><?php echo e($student['last_name'] ?? ''); ?></td>

                                                                <?php foreach ($postAssessments as $assId): ?>
                                                                    <td><?php echo isset($data['scores'][$assId]) ? number_format($data['scores'][$assId], 2) : '0.00'; ?></td>
                                                                <?php endforeach; ?>

                                                                <td><?php echo number_format($totalScore, 2); ?></td>
                                                                <td>
                                                                    <?php
                                                                    $grade = db_get_val(
                                                                        "SELECT grade FROM school_grade 
                                                             WHERE create_by_userid = ? 
                                                             AND minimum_number <= ? 
                                                             AND maximum_number >= ?",
                                                                        [$create_by_userid, $totalScore, $totalScore]
                                                                    );
                                                                    echo e($grade);
                                                                    ?>
                                                                </td>
                                                                <td id="rank_<?php echo $studentId; ?>"></td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    </tbody>
                                                </table>

                                                <?php
                                                // Calculate and display rankings
                                                if (!empty($studentTotals)) {
                                                    $rankedScores = setPosition($studentTotals);
                                                    foreach ($rankedScores as $studentId => $data) {
                                                        $rank = $data['rank'];
                                                        $suffix = 'th';
                                                        if ($rank == 1) $suffix = 'st';
                                                        elseif ($rank == 2) $suffix = 'nd';
                                                        elseif ($rank == 3) $suffix = 'rd';
                                                        echo "<script>document.getElementById('rank_" . $studentId . "').innerHTML = '" . $rank . $suffix . "';</script>";
                                                    }
                                                }
                                                ?>

                                                <?php if ($tStudent > 0): ?>
                                                    <table class="table table-striped table-bordered">
                                                        <tr>
                                                            <td><span>No. of Students: <?php echo $tStudent; ?></span></td>
                                                            <td><span>Class Average: <?php echo $tStudent > 0 ? round($classTotal / $tStudent, 2) : '0.00'; ?></span></td>
                                                        </tr>
                                                        <tr>
                                                            <td><span>Highest Average in Class: <?php echo !empty($highLow) ? round(max($highLow), 2) : '0.00'; ?></span></td>
                                                            <td><span>Lowest Average in Class: <?php echo !empty($highLow) ? round(min($highLow), 2) : '0.00'; ?></span></td>
                                                        </tr>
                                                    </table>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php include('inc.footer.php'); ?>
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
                }
                return Toggle;
            }).call(this);
        }).call(this);

        // Add active class to the current button
        var header = document.getElementById("example");
        if (header) {
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
        }

        $(document).ready(function() {
            $('#example').DataTable({
                "pageLength": 5
            });
        });
    </script>
</body>

</html>