<?php

/**
 * ============================================================================
 * MANAGE ASSESSMENT - MODERN REDESIGN
 * ============================================================================
 * Description: Manage staff assessments with multiple sections
 * Version: 4.0 (Fully Mobile Responsive)
 * ============================================================================
 */

include('../config.php');
include('inc.session-create.php');

$PageTitle = "Manage Assessment";
$FileName = 'manage_assessment.php';

// ============================================================================
// FIX: USE CORRECT USER IDENTIFICATION (Same as dashboard.php)
// ============================================================================
$create_by_userid = (int)($_SESSION['userid'] ?? 0);

// If create_by_userid is not set in session, try to get it from the user record
if ($create_by_userid == 0 && !empty($_SESSION['userid'])) {
	$userData = db_get_row("SELECT create_by_userid FROM users WHERE id = ?", [$_SESSION['userid']]);
	if ($userData && !empty($userData['create_by_userid'])) {
		$create_by_userid = (int)$userData['create_by_userid'];
	}
}

// Fallback: if still 0, use the user's own ID
if ($create_by_userid == 0) {
	$create_by_userid = (int)($_SESSION['userid'] ?? 0);
}

$create_by_usertype = (string)($_SESSION['usertype'] ?? '');
$sessionUserId = (int)($_SESSION['userid'] ?? 0);

$validate = new Validation();
$stat = [];

if (isset($_SESSION['success']) && $_SESSION['success'] != "") {
	$stat['success'] = $_SESSION['success'];
	unset($_SESSION['success']);
}

// ============================================================================
// FUNCTIONS
// ============================================================================
function addappraisaldetail($create_by_userid, $create_by_usertype)
{
	global $db;
	$iAppraisalAssement = $db->getVal(
		"SELECT id FROM appraisal_details WHERE teacher_id = ? AND subject_id = ? AND session_id = ? AND term_id = ? AND month = ? AND ddate = ?",
		[$_GET['teacher_id'] ?? 0, $_GET['subject_id'] ?? 0, $_GET['session_id'] ?? 0, $_GET['term_id'] ?? 0, $_GET['month'] ?? 0, $_GET['ddate'] ?? '']
	);

	$iLastId = $db->getVal("SELECT id FROM appraisal_details ORDER BY id DESC") + 1;
	$randomId = randomFix(15) . $iLastId;

	$aryData = [
		'teacher_id' => $_GET['teacher_id'] ?? 0,
		'subject_id' => $_GET['subject_id'] ?? 0,
		'session_id' => $_GET['session_id'] ?? 0,
		'term_id' => $_GET['term_id'] ?? 0,
		'month' => $_GET['month'] ?? 0,
		'ddate' => $_GET['ddate'] ?? '',
		'send_to_staff' => 0,
		'randomid' => $randomId,
		'create_by_userid' => $create_by_userid,
		'create_by_usertype' => $create_by_usertype,
	];

	if ($iAppraisalAssement == '') {
		$flgIn = $db->insertAry("appraisal_details", $aryData);
	}
}

// ============================================================================
// PROCESS FORM SUBMISSIONS
// ============================================================================

// Assessment Save
if (isset($_POST['addnewrecord'])) {
	if ($validate->validate() && count($stat) == 0) {
		$iAppraisalAssement = $db->getVal(
			"SELECT id FROM appraisal_assessment WHERE teacher_id = ? AND subject_id = ? AND session_id = ? AND term_id = ? AND month = ? AND ddate = ?",
			[$_POST['teacher_id'] ?? 0, $_POST['subject_id'] ?? 0, $_POST['session_id'] ?? 0, $_POST['term_id'] ?? 0, $_POST['month'] ?? 0, $_POST['ddate'] ?? '']
		);

		if ($iAppraisalAssement == '') {
			$iLastId = $db->getVal("SELECT id FROM appraisal_assessment ORDER BY id DESC") + 1;
			$randomId = randomFix(15) . '-' . $iLastId;

			$aryData = [
				'teacher_id' => $_POST['teacher_id'] ?? 0,
				'subject_id' => $_POST['subject_id'] ?? 0,
				'session_id' => $_POST['session_id'] ?? 0,
				'term_id' => $_POST['term_id'] ?? 0,
				'month' => $_POST['month'] ?? 0,
				'ddate' => $_POST['ddate'] ?? '',
				'create_by_userid' => $create_by_userid,
				'create_by_usertype' => $create_by_usertype,
				'randomid' => $randomId,
			];
			$flgIn = $db->insertAry("appraisal_assessment", $aryData);
			$iLastInsertId = $flgIn;

			foreach ($_POST['staff_assessment_id'] as $key => $val) {
				$week1 = !empty($_POST['week_1'][$key]) ? $_POST['week_1'][$key] : '0.00';
				$week2 = !empty($_POST['week_2'][$key]) ? $_POST['week_2'][$key] : '0.00';
				$week3 = !empty($_POST['week_3'][$key]) ? $_POST['week_3'][$key] : '0.00';
				$week4 = !empty($_POST['week_4'][$key]) ? $_POST['week_4'][$key] : '0.00';

				$aryData = [
					'appraisal_assessment_id' => $iLastInsertId,
					'staff_assessment_id' => $_POST['staff_assessment_id'][$key],
					'week_1' => $week1,
					'week_2' => $week2,
					'week_3' => $week3,
					'week_4' => $week4,
				];
				$flgInNew = $db->insertAry("appraisal_assessment_class_mark", $aryData);
			}
		} else {
			foreach ($_POST['staff_assessment_id'] as $key => $val) {
				$aryData = [
					'week_1' => $_POST['week_1'][$key] ?? '0.00',
					'week_2' => $_POST['week_2'][$key] ?? '0.00',
					'week_3' => $_POST['week_3'][$key] ?? '0.00',
					'week_4' => $_POST['week_4'][$key] ?? '0.00',
				];
				$flgInNew = $db->updateAry("appraisal_assessment_class_mark", $aryData, "WHERE id = '" . $_POST['primarykey'][$key] . "'");
			}
		}
		addappraisaldetail($create_by_userid, $create_by_usertype);
		$stat['success'] = "Save successfully";
	} else {
		$stat['error'] = $validate->errors();
	}
}

// Punctuality Save
if (isset($_POST['addnewrecordpunctuality'])) {
	if ($validate->validate() && count($stat) == 0) {
		$iAppraisalAssement = $db->getVal(
			"SELECT id FROM appraisal_punctuality WHERE teacher_id = ? AND subject_id = ? AND session_id = ? AND term_id = ? AND month = ? AND ddate = ?",
			[$_POST['teacher_id'] ?? 0, $_POST['subject_id'] ?? 0, $_POST['session_id'] ?? 0, $_POST['term_id'] ?? 0, $_POST['month'] ?? 0, $_POST['ddate'] ?? '']
		);

		if ($iAppraisalAssement == '') {
			$iLastId = $db->getVal("SELECT id FROM appraisal_punctuality ORDER BY id DESC") + 1;
			$randomId = randomFix(15) . '-' . $iLastId;

			$aryData = [
				'teacher_id' => $_POST['teacher_id'] ?? 0,
				'subject_id' => $_POST['subject_id'] ?? 0,
				'session_id' => $_POST['session_id'] ?? 0,
				'term_id' => $_POST['term_id'] ?? 0,
				'month' => $_POST['month'] ?? 0,
				'ddate' => $_POST['ddate'] ?? '',
				'create_by_userid' => $create_by_userid,
				'create_by_usertype' => $create_by_usertype,
				'randomid' => $randomId,
			];
			$flgIn = $db->insertAry("appraisal_punctuality", $aryData);
			$iLastInsertId = $flgIn;

			foreach ($_POST['staff_punctuality_id'] as $key => $val) {
				$week1 = !empty($_POST['week_1'][$key]) ? $_POST['week_1'][$key] : '0.00';
				$week2 = !empty($_POST['week_2'][$key]) ? $_POST['week_2'][$key] : '0.00';
				$week3 = !empty($_POST['week_3'][$key]) ? $_POST['week_3'][$key] : '0.00';
				$week4 = !empty($_POST['week_4'][$key]) ? $_POST['week_4'][$key] : '0.00';

				$aryData = [
					'appraisal_punctuality_id' => $iLastInsertId,
					'staff_punctuality_id' => $_POST['staff_punctuality_id'][$key],
					'week_1' => $week1,
					'week_2' => $week2,
					'week_3' => $week3,
					'week_4' => $week4,
				];
				$flgInNew = $db->insertAry("appraisal_punctuality_class_mark", $aryData);
			}
		} else {
			foreach ($_POST['staff_punctuality_id'] as $key => $val) {
				$aryData = [
					'week_1' => $_POST['week_1'][$key] ?? '0.00',
					'week_2' => $_POST['week_2'][$key] ?? '0.00',
					'week_3' => $_POST['week_3'][$key] ?? '0.00',
					'week_4' => $_POST['week_4'][$key] ?? '0.00',
				];
				$flgInNew = $db->updateAry("appraisal_punctuality_class_mark", $aryData, "WHERE id = '" . $_POST['primarykey'][$key] . "'");
			}
		}
		addappraisaldetail($create_by_userid, $create_by_usertype);
		$stat['success'] = "Save successfully";
	} else {
		$stat['error'] = $validate->errors();
	}
}

// Personal Assessment Save
if (isset($_POST['addnewrecordpersonalassessment'])) {
	if ($validate->validate() && count($stat) == 0) {
		$iAppraisalAssement = $db->getVal(
			"SELECT id FROM appraisal_personal WHERE teacher_id = ? AND subject_id = ? AND session_id = ? AND term_id = ? AND month = ? AND ddate = ?",
			[$_POST['teacher_id'] ?? 0, $_POST['subject_id'] ?? 0, $_POST['session_id'] ?? 0, $_POST['term_id'] ?? 0, $_POST['month'] ?? 0, $_POST['ddate'] ?? '']
		);

		if ($iAppraisalAssement == '') {
			$iLastId = $db->getVal("SELECT id FROM appraisal_personal ORDER BY id DESC") + 1;
			$randomId = randomFix(15) . '-' . $iLastId;

			$aryData = [
				'teacher_id' => $_POST['teacher_id'] ?? 0,
				'subject_id' => $_POST['subject_id'] ?? 0,
				'session_id' => $_POST['session_id'] ?? 0,
				'term_id' => $_POST['term_id'] ?? 0,
				'month' => $_POST['month'] ?? 0,
				'ddate' => $_POST['ddate'] ?? '',
				'create_by_userid' => $create_by_userid,
				'create_by_usertype' => $create_by_usertype,
				'randomid' => $randomId,
			];
			$flgIn = $db->insertAry("appraisal_personal", $aryData);
			$iLastInsertId = $flgIn;
		} else {
			$iLastInsertId = $iAppraisalAssement;
		}

		$db->delete("appraisal_personal_class_mark", "WHERE appraisal_personal_id = '" . $iLastInsertId . "'");

		for ($i = 1; $i <= $_POST['totalvalue']; $i++) {
			$aryData = [
				'appraisal_personal_id' => $iLastInsertId,
				'personal_id' => $_POST['personal_id' . $i] ?? 0,
				'fieldname' => $_POST['fieldname' . $i] ?? '',
				'fieldvalue' => $_POST['fieldvalue' . $i] ?? 0,
			];
			$flgInNew = $db->insertAry("appraisal_personal_class_mark", $aryData);
		}
		addappraisaldetail($create_by_userid, $create_by_usertype);
		$stat['success'] = "Save successfully";
	} else {
		$stat['error'] = $validate->errors();
	}
}

// Save Details with Signatures
if (isset($_POST['addnewrecordsavedetails'])) {
	if ($validate->validate() && count($stat) == 0) {
		addappraisaldetail($create_by_userid, $create_by_usertype);
		$iAppraisalAssement = $db->getVal(
			"SELECT id FROM appraisal_details WHERE teacher_id = ? AND subject_id = ? AND session_id = ? AND term_id = ? AND month = ? AND ddate = ?",
			[$_POST['teacher_id'] ?? 0, $_POST['subject_id'] ?? 0, $_POST['session_id'] ?? 0, $_POST['term_id'] ?? 0, $_POST['month'] ?? 0, $_POST['ddate'] ?? '']
		);

		// Handle signature uploads
		$imagedata = base64_decode($_POST['img_data'] ?? '');
		$filename = md5(date("Y-m-d H:i:s")) . '.png';
		$file_name = '../uploads/signuature/' . $filename;
		if (!empty($imagedata)) {
			file_put_contents($file_name, $imagedata);
		}

		$imagedata1 = base64_decode($_POST['img_data1'] ?? '');
		$filename1 = md5(date("Y-m-d H:i:s")) . 'abc.png';
		$file_name1 = '../uploads/signuature/' . $filename1;
		if (!empty($imagedata1)) {
			file_put_contents($file_name1, $imagedata1);
		}

		$imagedata2 = base64_decode($_POST['img_data2'] ?? '');
		$filename2 = md5(date("Y-m-d H:i:s")) . 'xyz.png';
		$file_name2 = '../uploads/signuature/' . $filename2;
		if (!empty($imagedata2)) {
			file_put_contents($file_name2, $imagedata2);
		}

		$iTeacherSign = !empty($filename) && !empty($_POST['img_data']) ? $filename : ($_POST['teacher_sign_old'] ?? '');
		$iPrincipalSign = !empty($filename1) && !empty($_POST['img_data1']) ? $filename1 : ($_POST['principal_sign_old'] ?? '');
		$iDirectorSign = !empty($filename2) && !empty($_POST['img_data2']) ? $filename2 : ($_POST['director_sign_old'] ?? '');

		$aryData = [
			'teacher_id' => $_POST['teacher_id'] ?? 0,
			'subject_id' => $_POST['subject_id'] ?? 0,
			'session_id' => $_POST['session_id'] ?? 0,
			'term_id' => $_POST['term_id'] ?? 0,
			'month' => $_POST['month'] ?? 0,
			'ddate' => $_POST['ddate'] ?? '',
			'remarks' => $_POST['remarks'] ?? '',
			'name_of_evalutor' => $_POST['name_of_evalutor'] ?? '',
			'date_sign' => $_POST['date_sign'] ?? '',
			'teacher_sign' => $iTeacherSign,
			'head_teacher' => $_POST['head_teacher'] ?? '',
			'principal_sign' => $iPrincipalSign,
			'director_sign' => $iDirectorSign,
			'create_by_userid' => $create_by_userid,
			'create_by_usertype' => $create_by_usertype,
		];

		if ($iAppraisalAssement == '') {
			$flgIn = $db->insertAry("appraisal_details", $aryData);
		} else {
			$flgIn = $db->updateAry("appraisal_details", $aryData, "WHERE id='" . $iAppraisalAssement . "'");
		}
		$stat['success'] = "Save successfully";
	} else {
		$stat['error'] = $validate->errors();
	}
}

// Send to Staff
if (isset($_GET['action']) && $_GET['action'] == 'sendthistostaff') {
	$iAppraisalAssement = $db->getVal(
		"SELECT id FROM appraisal_details WHERE teacher_id = ? AND subject_id = ? AND session_id = ? AND term_id = ? AND month = ? AND ddate = ?",
		[$_GET['teacher_id'] ?? 0, $_GET['subject_id'] ?? 0, $_GET['session_id'] ?? 0, $_GET['term_id'] ?? 0, $_GET['month'] ?? 0, $_GET['ddate'] ?? '']
	);
	$aryData = ['send_to_staff' => 1];
	$flgIn = $db->updateAry("appraisal_details", $aryData, "WHERE id='" . $iAppraisalAssement . "'");
}

// Get Appraisal Details
$iAppraisalDetails = [];
if (!empty($_GET['teacher_id']) && !empty($_GET['subject_id']) && !empty($_GET['session_id']) && !empty($_GET['term_id']) && !empty($_GET['month']) && !empty($_GET['ddate'])) {
	$iAppraisalDetails = $db->getRow(
		"SELECT * FROM appraisal_details WHERE teacher_id = ? AND subject_id = ? AND session_id = ? AND term_id = ? AND month = ? AND ddate = ? ORDER BY id DESC",
		[$_GET['teacher_id'], $_GET['subject_id'], $_GET['session_id'], $_GET['term_id'], $_GET['month'], $_GET['ddate']]
	);
}

// Get Appraisal Assessment ID
$iAppraisalAssement = 0;
if (!empty($_GET['teacher_id']) && !empty($_GET['subject_id']) && !empty($_GET['session_id']) && !empty($_GET['term_id']) && !empty($_GET['month']) && !empty($_GET['ddate'])) {
	$iAppraisalAssement = $db->getVal(
		"SELECT id FROM appraisal_assessment WHERE teacher_id = ? AND subject_id = ? AND session_id = ? AND term_id = ? AND month = ? AND ddate = ? ORDER BY id DESC",
		[$_GET['teacher_id'], $_GET['subject_id'], $_GET['session_id'], $_GET['term_id'], $_GET['month'], $_GET['ddate']]
	);
}

$iAppraisalPunctuality = 0;
if (!empty($_GET['teacher_id']) && !empty($_GET['subject_id']) && !empty($_GET['session_id']) && !empty($_GET['term_id']) && !empty($_GET['month']) && !empty($_GET['ddate'])) {
	$iAppraisalPunctuality = $db->getVal(
		"SELECT id FROM appraisal_punctuality WHERE teacher_id = ? AND subject_id = ? AND session_id = ? AND term_id = ? AND month = ? AND ddate = ? ORDER BY id DESC",
		[$_GET['teacher_id'], $_GET['subject_id'], $_GET['session_id'], $_GET['term_id'], $_GET['month'], $_GET['ddate']]
	);
}

$iAppraisalPersonal = 0;
if (!empty($_GET['teacher_id']) && !empty($_GET['subject_id']) && !empty($_GET['session_id']) && !empty($_GET['term_id']) && !empty($_GET['month']) && !empty($_GET['ddate'])) {
	$iAppraisalPersonal = $db->getVal(
		"SELECT id FROM appraisal_personal WHERE teacher_id = ? AND subject_id = ? AND session_id = ? AND term_id = ? AND month = ? AND ddate = ? ORDER BY id DESC",
		[$_GET['teacher_id'], $_GET['subject_id'], $_GET['session_id'], $_GET['term_id'], $_GET['month'], $_GET['ddate']]
	);
}

// Get all appraisal details for list
$appraisalList = db_get_rows(
	"SELECT * FROM appraisal_details WHERE create_by_userid = ? ORDER BY id DESC",
	[$create_by_userid]
);

// Get staff assessments, punctuality, and personal assessments
$staffAssessments = db_get_rows(
	"SELECT * FROM staff_assessment WHERE create_by_userid = ? ORDER BY id DESC",
	[$create_by_userid]
);

$punctualityItems = db_get_rows(
	"SELECT * FROM punctuality ORDER BY id DESC",
	[]
);

$personalAssessments = db_get_rows(
	"SELECT * FROM personal_assessment WHERE create_by_userid = ? ORDER BY id DESC",
	[$create_by_userid]
);

// Get teachers, subjects, sessions, terms for dropdowns
$teachers = db_get_rows(
	"SELECT * FROM school_register WHERE usertype = '1' AND create_by_userid = ?",
	[$create_by_userid]
);

$subjects = db_get_rows(
	"SELECT * FROM school_subject WHERE create_by_userid = ?",
	[$create_by_userid]
);

$sessionList = db_get_rows(
	"SELECT * FROM school_session WHERE create_by_userid = ? ORDER BY id DESC",
	[$create_by_userid]
);

$termList = db_get_rows(
	"SELECT * FROM school_term WHERE create_by_userid = ? ORDER BY id DESC",
	[$create_by_userid]
);

// Determine active tab
$activeTab = isset($_GET['action']) && $_GET['action'] == 'list' ? 'list' : 'add';
$searchData = isset($_GET['searchdata']) ? $_GET['searchdata'] : '';
?>
<!DOCTYPE html>
<html>

<head>
	<?php include('inc.meta.php'); ?>
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
	<style>
		/* ============================================================
        RESET & BASE - MOBILE FIRST
        ============================================================ */
		* {
			margin: 0;
			padding: 0;
			box-sizing: border-box;
		}

		body {
			background: #f0f2f5;
			font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
		}

		.assessment-container {
			max-width: 1400px;
			margin: 0 auto;
			padding: 15px;
		}

		/* ============================================================
        PAGE HEADER - MOBILE FIRST
        ============================================================ */
		.page-header {
			margin-bottom: 20px;
		}

		.page-header h2 {
			color: #1B3058;
			margin: 0;
			font-size: 22px;
			font-weight: 700;
		}

		.page-header h2 i {
			margin-right: 8px;
		}

		.page-header p {
			color: #666;
			margin-top: 4px;
			font-size: 14px;
		}

		/* ============================================================
        TABS - MOBILE FIRST
        ============================================================ */
		.tabs-container {
			background: #fff;
			border-radius: 16px;
			box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
			overflow: hidden;
		}

		.tabs-header {
			display: flex;
			border-bottom: 2px solid #f0f0f0;
			background: #f8fafc;
			overflow-x: auto;
			-webkit-overflow-scrolling: touch;
		}

		.tab-btn {
			flex: 1;
			padding: 12px 10px;
			text-align: center;
			font-weight: 600;
			font-size: 13px;
			color: #888;
			cursor: pointer;
			transition: all 0.3s;
			border: none;
			background: transparent;
			position: relative;
			min-height: 44px;
			touch-action: manipulation;
			white-space: nowrap;
			min-width: 80px;
		}

		.tab-btn:active {
			transform: scale(0.97);
		}

		.tab-btn i {
			margin-right: 6px;
			font-size: 16px;
		}

		.tab-btn.active {
			color: #1B3058;
		}

		.tab-btn.active::after {
			content: '';
			position: absolute;
			bottom: -2px;
			left: 10%;
			right: 10%;
			height: 3px;
			background: #1B3058;
			border-radius: 3px;
		}

		.tab-btn:hover {
			color: #1B3058;
		}

		.tab-content {
			padding: 16px;
		}

		/* ============================================================
        FORM - MOBILE FIRST
        ============================================================ */
		.form-grid {
			display: grid;
			grid-template-columns: 1fr;
			gap: 12px;
		}

		.form-group {
			display: flex;
			flex-direction: column;
			gap: 4px;
		}

		.form-group label {
			font-weight: 600;
			font-size: 13px;
			color: #333;
		}

		.form-group select,
		.form-group input[type="text"],
		.form-group input[type="date"] {
			width: 100%;
			padding: 10px 12px;
			border: 2px solid #e0e0e0;
			border-radius: 10px;
			font-size: 14px;
			transition: all 0.2s;
			background: #fafafa;
		}

		.form-group select:focus,
		.form-group input[type="text"]:focus,
		.form-group input[type="date"]:focus {
			outline: none;
			border-color: #1B3058;
			background: #fff;
			box-shadow: 0 0 0 3px rgba(27, 48, 88, 0.1);
		}

		.form-actions {
			display: flex;
			flex-direction: column;
			gap: 10px;
			margin-top: 12px;
		}

		/* ============================================================
        BUTTONS - MOBILE FIRST
        ============================================================ */
		.btn {
			display: inline-flex;
			align-items: center;
			justify-content: center;
			gap: 8px;
			padding: 10px 16px;
			border: none;
			border-radius: 10px;
			font-size: 13px;
			font-weight: 600;
			cursor: pointer;
			transition: all 0.3s;
			text-decoration: none;
			min-height: 44px;
			touch-action: manipulation;
		}

		.btn:active {
			transform: scale(0.97);
		}

		.btn-primary {
			background: #1B3058;
			color: white;
		}

		.btn-primary:hover {
			background: #f21151;
		}

		.btn-success {
			background: #28a745;
			color: white;
		}

		.btn-success:hover {
			background: #218838;
		}

		.btn-danger {
			background: #dc3545;
			color: white;
		}

		.btn-danger:hover {
			background: #c82333;
		}

		.btn-info {
			background: #17a2b8;
			color: white;
		}

		.btn-info:hover {
			background: #138496;
		}

		.btn-outline {
			background: transparent;
			color: #1B3058;
			border: 2px solid #1B3058;
		}

		.btn-outline:hover {
			background: #1B3058;
			color: white;
		}

		.btn-sm {
			padding: 4px 10px;
			font-size: 11px;
			min-height: 28px;
		}

		.btn-block {
			width: 100%;
			justify-content: center;
		}

		.btn-group {
			display: flex;
			flex-direction: column;
			gap: 8px;
			flex-wrap: wrap;
		}

		/* ============================================================
        TABLE - MOBILE FIRST
        ============================================================ */
		.table-wrapper {
			overflow-x: auto;
			-webkit-overflow-scrolling: touch;
			margin: 0 -4px;
			padding: 0 4px;
		}

		.table {
			width: 100%;
			min-width: 600px;
			border-collapse: collapse;
			font-size: 12px;
		}

		.table th,
		.table td {
			padding: 8px 6px;
			text-align: left;
			border-bottom: 1px solid #f0f0f0;
			vertical-align: middle;
		}

		.table th {
			background: #f8f9fa;
			font-weight: 700;
			color: #1B3058;
			font-size: 10px;
			text-transform: uppercase;
			letter-spacing: 0.3px;
			position: sticky;
			top: 0;
			z-index: 2;
		}

		.table td {
			font-size: 12px;
		}

		.table tr:hover td {
			background: #f8f9ff;
		}

		.table .form-control {
			width: 100%;
			padding: 6px 8px;
			border: 1px solid #ddd;
			border-radius: 6px;
			font-size: 12px;
		}

		.table .form-control:focus {
			outline: none;
			border-color: #1B3058;
		}

		.table .form-control.valid {
			border-color: #28a745;
			background: #d4edda;
		}

		.table .form-control.invalid {
			border-color: #dc3545;
			background: #f8d7da;
		}

		.section-title {
			font-size: 18px;
			font-weight: 700;
			color: #1B3058;
			margin: 20px 0 12px;
			padding-bottom: 8px;
			border-bottom: 2px solid #1B3058;
			display: flex;
			align-items: center;
			gap: 10px;
		}

		.section-title i {
			color: #f21151;
		}

		/* ============================================================
        SIGNATURE CANVAS - MOBILE FIRST
        ============================================================ */
		.signature-section {
			margin-top: 16px;
			padding: 12px;
			background: #f8f9fa;
			border-radius: 12px;
			border: 1px solid #eee;
		}

		.signature-section .sign-pad {
			width: 100%;
			height: 100px;
			border: 2px solid #ddd;
			border-radius: 8px;
			cursor: pointer;
			background: #fff;
			max-width: 400px;
		}

		.signature-section .sign-label {
			font-weight: 600;
			font-size: 13px;
			color: #333;
			margin-bottom: 6px;
		}

		.signature-section .signature-row {
			display: flex;
			flex-direction: column;
			gap: 12px;
		}

		.signature-section .signature-item {
			flex: 1;
		}

		.signature-section .signature-item img {
			max-height: 60px;
			border: 1px solid #ddd;
			border-radius: 4px;
			margin-top: 4px;
		}

		.signature-grid {
			display: grid;
			grid-template-columns: 1fr;
			gap: 16px;
			margin-top: 12px;
		}

		/* ============================================================
        ALERTS - MOBILE FIRST
        ============================================================ */
		.alert {
			padding: 12px 16px;
			border-radius: 12px;
			margin-bottom: 16px;
			font-size: 13px;
			display: flex;
			align-items: flex-start;
			gap: 10px;
		}

		.alert i {
			font-size: 18px;
			margin-top: 2px;
			flex-shrink: 0;
		}

		.alert-success {
			background: #d4edda;
			color: #155724;
			border-left: 4px solid #28a745;
		}

		.alert-danger {
			background: #f8d7da;
			color: #721c24;
			border-left: 4px solid #dc3545;
		}

		.alert-info {
			background: #d1ecf1;
			color: #0c5460;
			border-left: 4px solid #17a2b8;
		}

		.alert-warning {
			background: #fff3cd;
			color: #856404;
			border-left: 4px solid #ffc107;
		}

		/* ============================================================
        EMPTY STATE - MOBILE FIRST
        ============================================================ */
		.empty-state {
			text-align: center;
			padding: 30px 20px;
			color: #999;
		}

		.empty-state i {
			font-size: 40px;
			color: #ddd;
			display: block;
			margin-bottom: 10px;
		}

		.empty-state h4 {
			color: #666;
			font-size: 15px;
			margin-bottom: 4px;
		}

		.empty-state p {
			font-size: 13px;
		}

		/* ============================================================
        RESPONSIVE - TABLET (768px+)
        ============================================================ */
		@media (min-width: 768px) {
			.assessment-container {
				padding: 25px;
			}

			.page-header h2 {
				font-size: 28px;
			}

			.form-grid {
				grid-template-columns: 1fr 1fr 1fr;
				gap: 15px;
			}

			.form-grid .full-width {
				grid-column: 1 / -1;
			}

			.form-actions {
				flex-direction: row;
				justify-content: flex-end;
			}

			.form-actions .btn {
				width: auto;
			}

			.tab-content {
				padding: 24px;
			}

			.table {
				min-width: auto;
			}

			.table th,
			.table td {
				padding: 10px 12px;
			}

			.signature-grid {
				grid-template-columns: 1fr 1fr 1fr;
			}

			.btn-group {
				flex-direction: row;
			}

			.signature-section .signature-row {
				flex-direction: row;
			}
		}

		/* ============================================================
        RESPONSIVE - DESKTOP (1024px+)
        ============================================================ */
		@media (min-width: 1024px) {
			.assessment-container {
				padding: 30px;
			}

			.table th,
			.table td {
				padding: 12px 16px;
			}
		}

		/* ============================================================
        RESPONSIVE - SMALL MOBILE (480px-)
        ============================================================ */
		@media (max-width: 480px) {
			.assessment-container {
				padding: 10px;
			}

			.page-header h2 {
				font-size: 18px;
			}

			.page-header p {
				font-size: 12px;
			}

			.tab-btn {
				font-size: 11px;
				padding: 8px 6px;
				min-width: 60px;
			}

			.tab-btn i {
				font-size: 14px;
				margin-right: 3px;
			}

			.tab-content {
				padding: 10px;
			}

			.table {
				font-size: 10px;
				min-width: 480px;
			}

			.table th,
			.table td {
				padding: 4px 3px;
			}

			.table th {
				font-size: 8px;
			}

			.table td {
				font-size: 10px;
			}

			.table .form-control {
				font-size: 10px;
				padding: 3px 4px;
			}

			.section-title {
				font-size: 15px;
			}

			.btn {
				font-size: 12px;
				padding: 8px 12px;
				min-height: 36px;
			}

			.signature-section .sign-pad {
				height: 70px;
			}

			.form-group select,
			.form-group input[type="text"],
			.form-group input[type="date"] {
				font-size: 13px;
				padding: 8px 10px;
			}
		}

		/* ============================================================
        PRINT STYLES
        ============================================================ */
		@media print {

			.btn,
			.btn-group,
			.form-actions,
			.no-print {
				display: none !important;
			}

			.tabs-container {
				box-shadow: none !important;
				border: 1px solid #ddd;
			}

			body {
				background: white;
			}

			.assessment-container {
				padding: 0;
			}

			.table th {
				background: #f8f9fa !important;
				-webkit-print-color-adjust: exact;
				print-color-adjust: exact;
			}
		}
	</style>
</head>

<body class="fixed-left">
	<div id="wrapper">
		<?php include('inc.header.php'); ?>
		<?php include('inc.sideleft.php'); ?>
		<div class="content-page">
			<div class="content">
				<div class="assessment-container">

					<!-- Page Header -->
					<div class="page-header">
						<h2><i class="fa fa-tasks"></i> <?= htmlspecialchars($PageTitle) ?></h2>
						<p>Manage staff assessments, punctuality, and personal assessments</p>
					</div>

					<?= showMessage($stat) ?>

					<!-- Tabs -->
					<div class="tabs-container">
						<div class="tabs-header">
							<button class="tab-btn <?= ($activeTab == 'list') ? 'active' : '' ?>" onclick="window.location.href='?action=list'">
								<i class="fa fa-list"></i> <span>List</span>
							</button>
							<button class="tab-btn <?= ($activeTab == 'add') ? 'active' : '' ?>" onclick="window.location.href='<?= $FileName ?>'">
								<i class="fa fa-plus"></i> <span>Add New</span>
							</button>
						</div>

						<div class="tab-content">
							<?php if ($activeTab == 'list'): ?>
								<!-- LIST VIEW -->
								<div class="table-wrapper">
									<?php if (!empty($appraisalList)): ?>
										<table class="table" id="appraisalTable">
											<thead>
												<tr>
													<th>#</th>
													<th>Teacher</th>
													<th>Subject</th>
													<th>Session</th>
													<th>Term</th>
													<th>Month</th>
													<th>Date</th>
													<th>Sent</th>
													<th>Action</th>
												</tr>
											</thead>
											<tbody>
												<?php $i = 0;
												foreach ($appraisalList as $item): $i++; ?>
													<tr>
														<td><?= $i ?></td>
														<td><?= htmlspecialchars(db_get_val("SELECT name FROM school_register WHERE id = ?", [$item['teacher_id']]) ?: 'N/A') ?></td>
														<td><?= htmlspecialchars(db_get_val("SELECT subject FROM school_subject WHERE id = ?", [$item['subject_id']]) ?: 'N/A') ?></td>
														<td><?= htmlspecialchars(db_get_val("SELECT session FROM school_session WHERE id = ?", [$item['session_id']]) ?: 'N/A') ?></td>
														<td><?= htmlspecialchars(db_get_val("SELECT term FROM school_term WHERE id = ?", [$item['term_id']]) ?: 'N/A') ?></td>
														<td><?= date("F", mktime(0, 0, 0, $item['month'], 10)) ?></td>
														<td><?= htmlspecialchars($item['ddate']) ?></td>
														<td>
															<?php if (empty($item['send_to_staff']) || $item['send_to_staff'] == '0'): ?>
																<span class="btn btn-danger btn-sm" style="cursor:default;">No</span>
															<?php else: ?>
																<span class="btn btn-success btn-sm" style="cursor:default;">Yes</span>
															<?php endif; ?>
														</td>
														<td>
															<a href="<?= $FileName ?>?teacher_id=<?= $item['teacher_id'] ?>&subject_id=<?= $item['subject_id'] ?>&session_id=<?= $item['session_id'] ?>&term_id=<?= $item['term_id'] ?>&month=<?= $item['month'] ?>&ddate=<?= $item['ddate'] ?>&searchdata=Save" class="btn btn-primary btn-sm">
																<i class="fa fa-eye"></i>
															</a>
														</td>
													</tr>
												<?php endforeach; ?>
											</tbody>
										</table>
									<?php else: ?>
										<div class="empty-state">
											<i class="fa fa-inbox"></i>
											<h4>No Assessments Found</h4>
											<p>Create a new assessment using the "Add New" tab.</p>
										</div>
									<?php endif; ?>
								</div>

							<?php else: ?>
								<!-- ADD/EDIT VIEW -->

								<?php if (empty($searchData)): ?>
									<!-- SEARCH FORM -->
									<form method="GET" action="" id="searchForm">
										<div class="form-grid">
											<div class="form-group">
												<label><i class="fa fa-user"></i> Teacher</label>
												<select name="teacher_id" required>
													<option value="">-- Select Teacher --</option>
													<?php foreach ($teachers as $teacher): ?>
														<option value="<?= $teacher['id'] ?>"><?= htmlspecialchars($teacher['name']) ?></option>
													<?php endforeach; ?>
												</select>
											</div>
											<div class="form-group">
												<label><i class="fa fa-book"></i> Subject</label>
												<select name="subject_id" required>
													<option value="0">-- Select Subject --</option>
													<?php foreach ($subjects as $subject): ?>
														<option value="<?= $subject['id'] ?>"><?= htmlspecialchars($subject['subject']) ?></option>
													<?php endforeach; ?>
												</select>
											</div>
											<div class="form-group">
												<label><i class="fa fa-calendar"></i> Session</label>
												<select name="session_id" required>
													<option value="">-- Select Session --</option>
													<?php foreach ($sessionList as $s): ?>
														<option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['session']) ?></option>
													<?php endforeach; ?>
												</select>
											</div>
											<div class="form-group">
												<label><i class="fa fa-tag"></i> Term</label>
												<select name="term_id" required>
													<option value="">-- Select Term --</option>
													<?php foreach ($termList as $t): ?>
														<option value="<?= $t['id'] ?>"><?= htmlspecialchars($t['term']) ?></option>
													<?php endforeach; ?>
												</select>
											</div>
											<div class="form-group">
												<label><i class="fa fa-calendar-o"></i> Month</label>
												<select name="month" required>
													<option value="">-- Select Month --</option>
													<?php for ($i = 1; $i <= 12; $i++): ?>
														<option value="<?= $i ?>"><?= date("F", mktime(0, 0, 0, $i, 10)) ?></option>
													<?php endfor; ?>
												</select>
											</div>
											<div class="form-group">
												<label><i class="fa fa-calendar-check-o"></i> Date</label>
												<input type="date" name="ddate" required>
											</div>
										</div>
										<div class="form-actions">
											<button type="submit" name="searchdata" value="Save" class="btn btn-primary btn-block">
												<i class="fa fa-search"></i> Search
											</button>
										</div>
									</form>

								<?php else: ?>
									<!-- ASSESSMENT ENTRY FORM -->

									<!-- Action Buttons -->
									<div class="btn-group" style="margin-bottom:16px;">
										<a href="pdf_staff_assessment.php?teacher_id=<?= urlencode($_GET['teacher_id'] ?? '') ?>&subject_id=<?= urlencode($_GET['subject_id'] ?? '') ?>&session_id=<?= urlencode($_GET['session_id'] ?? '') ?>&term_id=<?= urlencode($_GET['term_id'] ?? '') ?>&month=<?= urlencode($_GET['month'] ?? '') ?>&ddate=<?= urlencode($_GET['ddate'] ?? '') ?>" target="_blank" class="btn btn-danger">
											<i class="fa fa-file-pdf-o"></i> Print PDF
										</a>

										<?php if (empty($iAppraisalDetails['send_to_staff']) || $iAppraisalDetails['send_to_staff'] == '0'): ?>
											<a href="javascript:void(0)" onclick="sendToStaff()" class="btn btn-success">
												<i class="fa fa-paper-plane"></i> Send to Staff
											</a>
										<?php else: ?>
											<span class="btn btn-info" style="cursor:default;">
												<i class="fa fa-check-circle"></i> Sent to Staff
											</span>
										<?php endif; ?>
									</div>

									<!-- CLASS ASSESSMENT -->
									<div class="section-title">
										<i class="fa fa-clipboard"></i> Class Assessment
									</div>
									<form method="POST" action="" id="assessmentForm">
										<div class="table-wrapper">
											<table class="table">
												<thead>
													<tr>
														<th>#</th>
														<th>Items</th>
														<th>Marks</th>
														<th>Week 1</th>
														<th>Week 2</th>
														<th>Week 3</th>
														<th>Week 4</th>
														<th>Total</th>
													</tr>
												</thead>
												<tbody>
													<?php if (!empty($staffAssessments)): ?>
														<?php $i = 0;
														foreach ($staffAssessments as $item): $i++;
															$appClassMark = db_get_row(
																"SELECT * FROM appraisal_assessment_class_mark WHERE appraisal_assessment_id = ? AND staff_assessment_id = ?",
																[$iAppraisalAssement, $item['id']]
															);
															$total = ($appClassMark['week_1'] ?? 0) + ($appClassMark['week_2'] ?? 0) + ($appClassMark['week_3'] ?? 0) + ($appClassMark['week_4'] ?? 0);
															$minMark = floatval($item['min_mark'] ?? 0);
														?>
															<tr>
																<td>
																	<?= $i ?>
																	<input type="hidden" name="staff_assessment_id[]" value="<?= $item['id'] ?>">
																	<input type="hidden" name="primarykey[]" value="<?= $appClassMark['id'] ?? '' ?>">
																</td>
																<td><?= htmlspecialchars($item['assessment']) ?></td>
																<td><?= $item['mark'] ?></td>
																<td><input type="number" class="form-control <?= (($appClassMark['week_1'] ?? 0) < $minMark) ? 'invalid' : 'valid' ?>" name="week_1[]" value="<?= $appClassMark['week_1'] ?? 0 ?>" step="0.01"></td>
																<td><input type="number" class="form-control <?= (($appClassMark['week_2'] ?? 0) < $minMark) ? 'invalid' : 'valid' ?>" name="week_2[]" value="<?= $appClassMark['week_2'] ?? 0 ?>" step="0.01"></td>
																<td><input type="number" class="form-control <?= (($appClassMark['week_3'] ?? 0) < $minMark) ? 'invalid' : 'valid' ?>" name="week_3[]" value="<?= $appClassMark['week_3'] ?? 0 ?>" step="0.01"></td>
																<td><input type="number" class="form-control <?= (($appClassMark['week_4'] ?? 0) < $minMark) ? 'invalid' : 'valid' ?>" name="week_4[]" value="<?= $appClassMark['week_4'] ?? 0 ?>" step="0.01"></td>
																<td><input type="text" class="form-control" name="total[]" value="<?= $total ?>" readonly></td>
															</tr>
														<?php endforeach; ?>
													<?php else: ?>
														<tr>
															<td colspan="8" style="text-align:center; padding:20px; color:#999;">
																<i class="fa fa-info-circle"></i> No staff assessments found. Please add assessments first.
															</td>
														</tr>
													<?php endif; ?>
												</tbody>
											</table>
										</div>

										<!-- Hidden fields -->
										<input type="hidden" name="teacher_id" value="<?= htmlspecialchars($_GET['teacher_id'] ?? '') ?>">
										<input type="hidden" name="subject_id" value="<?= htmlspecialchars($_GET['subject_id'] ?? '') ?>">
										<input type="hidden" name="session_id" value="<?= htmlspecialchars($_GET['session_id'] ?? '') ?>">
										<input type="hidden" name="term_id" value="<?= htmlspecialchars($_GET['term_id'] ?? '') ?>">
										<input type="hidden" name="month" value="<?= htmlspecialchars($_GET['month'] ?? '') ?>">
										<input type="hidden" name="ddate" value="<?= htmlspecialchars($_GET['ddate'] ?? '') ?>">

										<div class="form-actions">
											<button type="submit" name="addnewrecord" class="btn btn-primary"><i class="fa fa-save"></i> Save Assessment</button>
											<a href="<?= $FileName ?>" class="btn btn-outline"><i class="fa fa-arrow-left"></i> Back</a>
										</div>
									</form>

									<!-- PUNCTUALITY -->
									<div class="section-title">
										<i class="fa fa-clock-o"></i> Punctuality
									</div>
									<form method="POST" action="" id="punctualityForm">
										<div class="table-wrapper">
											<table class="table">
												<thead>
													<tr>
														<th>#</th>
														<th>Items</th>
														<th>Week 1</th>
														<th>Week 2</th>
														<th>Week 3</th>
														<th>Week 4</th>
														<th>Total</th>
													</tr>
												</thead>
												<tbody>
													<?php if (!empty($punctualityItems)): ?>
														<?php $i = 0;
														foreach ($punctualityItems as $item): $i++;
															$appClassMark = db_get_row(
																"SELECT * FROM appraisal_punctuality_class_mark WHERE appraisal_punctuality_id = ? AND staff_punctuality_id = ?",
																[$iAppraisalPunctuality, $item['id']]
															);
															$total = ($appClassMark['week_1'] ?? 0) + ($appClassMark['week_2'] ?? 0) + ($appClassMark['week_3'] ?? 0) + ($appClassMark['week_4'] ?? 0);
														?>
															<tr>
																<td>
																	<?= $i ?>
																	<input type="hidden" name="staff_punctuality_id[]" value="<?= $item['id'] ?>">
																	<input type="hidden" name="primarykey[]" value="<?= $appClassMark['id'] ?? '' ?>">
																</td>
																<td><?= htmlspecialchars($item['title']) ?></td>
																<td><input type="number" class="form-control" name="week_1[]" value="<?= $appClassMark['week_1'] ?? 0 ?>" step="0.01"></td>
																<td><input type="number" class="form-control" name="week_2[]" value="<?= $appClassMark['week_2'] ?? 0 ?>" step="0.01"></td>
																<td><input type="number" class="form-control" name="week_3[]" value="<?= $appClassMark['week_3'] ?? 0 ?>" step="0.01"></td>
																<td><input type="number" class="form-control" name="week_4[]" value="<?= $appClassMark['week_4'] ?? 0 ?>" step="0.01"></td>
																<td><input type="text" class="form-control" name="total[]" value="<?= $total ?>" readonly></td>
															</tr>
														<?php endforeach; ?>
													<?php else: ?>
														<tr>
															<td colspan="7" style="text-align:center; padding:20px; color:#999;">
																<i class="fa fa-info-circle"></i> No punctuality items found.
															</td>
														</tr>
													<?php endif; ?>
												</tbody>
											</table>
										</div>

										<!-- Hidden fields -->
										<input type="hidden" name="teacher_id" value="<?= htmlspecialchars($_GET['teacher_id'] ?? '') ?>">
										<input type="hidden" name="subject_id" value="<?= htmlspecialchars($_GET['subject_id'] ?? '') ?>">
										<input type="hidden" name="session_id" value="<?= htmlspecialchars($_GET['session_id'] ?? '') ?>">
										<input type="hidden" name="term_id" value="<?= htmlspecialchars($_GET['term_id'] ?? '') ?>">
										<input type="hidden" name="month" value="<?= htmlspecialchars($_GET['month'] ?? '') ?>">
										<input type="hidden" name="ddate" value="<?= htmlspecialchars($_GET['ddate'] ?? '') ?>">

										<div class="form-actions">
											<button type="submit" name="addnewrecordpunctuality" class="btn btn-primary"><i class="fa fa-save"></i> Save Punctuality</button>
											<a href="<?= $FileName ?>" class="btn btn-outline"><i class="fa fa-arrow-left"></i> Back</a>
										</div>
									</form>

									<!-- PERSONAL ASSESSMENT -->
									<div class="section-title">
										<i class="fa fa-user"></i> Personal Assessment
									</div>
									<form method="POST" action="" id="personalForm">
										<div class="table-wrapper">
											<table class="table">
												<thead>
													<tr>
														<th>#</th>
														<th>Items</th>
														<th>Fail</th>
														<th>Pass</th>
														<th>Good</th>
														<th>Very Good</th>
													</tr>
												</thead>
												<tbody>
													<?php if (!empty($personalAssessments)): ?>
														<?php $i = 0;
														foreach ($personalAssessments as $item): $i++;
															$appClassMark = db_get_row(
																"SELECT * FROM appraisal_personal_class_mark WHERE appraisal_personal_id = ? AND fieldname = ?",
																[$iAppraisalPersonal, $i]
															);
														?>
															<tr>
																<td>
																	<?= $i ?>
																	<input type="hidden" name="fieldname<?= $i ?>" value="<?= $i ?>">
																	<input type="hidden" name="personal_id<?= $i ?>" value="<?= $item['id'] ?>">
																</td>
																<td><?= htmlspecialchars($item['assessment']) ?></td>
																<td><input type="radio" name="fieldvalue<?= $i ?>" value="1" <?= ($appClassMark['fieldvalue'] ?? 0) == 1 ? 'checked' : '' ?>></td>
																<td><input type="radio" name="fieldvalue<?= $i ?>" value="2" <?= ($appClassMark['fieldvalue'] ?? 0) == 2 ? 'checked' : '' ?>></td>
																<td><input type="radio" name="fieldvalue<?= $i ?>" value="3" <?= ($appClassMark['fieldvalue'] ?? 0) == 3 ? 'checked' : '' ?>></td>
																<td><input type="radio" name="fieldvalue<?= $i ?>" value="4" <?= ($appClassMark['fieldvalue'] ?? 0) == 4 ? 'checked' : '' ?>></td>
															</tr>
														<?php endforeach; ?>
														<input type="hidden" value="<?= $i ?>" name="totalvalue">
													<?php else: ?>
														<tr>
															<td colspan="6" style="text-align:center; padding:20px; color:#999;">
																<i class="fa fa-info-circle"></i> No personal assessments found.
															</td>
														</tr>
													<?php endif; ?>
												</tbody>
											</table>
										</div>

										<!-- Hidden fields -->
										<input type="hidden" name="teacher_id" value="<?= htmlspecialchars($_GET['teacher_id'] ?? '') ?>">
										<input type="hidden" name="subject_id" value="<?= htmlspecialchars($_GET['subject_id'] ?? '') ?>">
										<input type="hidden" name="session_id" value="<?= htmlspecialchars($_GET['session_id'] ?? '') ?>">
										<input type="hidden" name="term_id" value="<?= htmlspecialchars($_GET['term_id'] ?? '') ?>">
										<input type="hidden" name="month" value="<?= htmlspecialchars($_GET['month'] ?? '') ?>">
										<input type="hidden" name="ddate" value="<?= htmlspecialchars($_GET['ddate'] ?? '') ?>">

										<div class="form-actions">
											<button type="submit" name="addnewrecordpersonalassessment" class="btn btn-primary"><i class="fa fa-save"></i> Save Personal Assessment</button>
											<a href="<?= $FileName ?>" class="btn btn-outline"><i class="fa fa-arrow-left"></i> Back</a>
										</div>
									</form>

									<!-- SIGNATURES -->
									<div class="section-title">
										<i class="fa fa-pencil-square-o"></i> Signatures
									</div>
									<form method="POST" action="" id="signatureForm" enctype="multipart/form-data">
										<div class="signature-section">
											<div class="signature-grid">
												<div class="signature-item">
													<div class="sign-label">Teacher's Signature</div>
													<input type="hidden" name="teacher_sign_old" value="<?= htmlspecialchars($iAppraisalDetails['teacher_sign'] ?? '') ?>">
													<canvas class="sign-pad" id="sign-pad" width="380" height="100" style="border: 2px solid #ddd; border-radius: 8px; background:#fff;"></canvas>
													<input type="hidden" name="img_data" id="img_data" value="Yes">
													<?php if (!empty($iAppraisalDetails['teacher_sign'])): ?>
														<img src="../uploads/signuature/<?= htmlspecialchars($iAppraisalDetails['teacher_sign']) ?>" alt="Teacher Signature" style="max-height:60px; margin-top:4px;">
													<?php endif; ?>
												</div>
												<div class="signature-item">
													<div class="sign-label">Principal's Signature</div>
													<input type="hidden" name="principal_sign_old" value="<?= htmlspecialchars($iAppraisalDetails['principal_sign'] ?? '') ?>">
													<canvas class="sign-pad" id="sign-pad1" width="380" height="100" style="border: 2px solid #ddd; border-radius: 8px; background:#fff;"></canvas>
													<input type="hidden" name="img_data1" id="img_data1" value="Yes">
													<?php if (!empty($iAppraisalDetails['principal_sign'])): ?>
														<img src="../uploads/signuature/<?= htmlspecialchars($iAppraisalDetails['principal_sign']) ?>" alt="Principal Signature" style="max-height:60px; margin-top:4px;">
													<?php endif; ?>
												</div>
												<div class="signature-item">
													<div class="sign-label">Director's Signature</div>
													<input type="hidden" name="director_sign_old" value="<?= htmlspecialchars($iAppraisalDetails['director_sign'] ?? '') ?>">
													<canvas class="sign-pad" id="sign-pad2" width="380" height="100" style="border: 2px solid #ddd; border-radius: 8px; background:#fff;"></canvas>
													<input type="hidden" name="img_data2" id="img_data2" value="Yes">
													<?php if (!empty($iAppraisalDetails['director_sign'])): ?>
														<img src="../uploads/signuature/<?= htmlspecialchars($iAppraisalDetails['director_sign']) ?>" alt="Director Signature" style="max-height:60px; margin-top:4px;">
													<?php endif; ?>
												</div>
											</div>

											<div style="margin-top:16px; display:grid; grid-template-columns:1fr; gap:12px;">
												<div>
													<label class="sign-label">Remarks</label>
													<textarea class="form-control" name="remarks" rows="3" style="width:100%; padding:10px; border:2px solid #e0e0e0; border-radius:10px; font-size:14px;"><?= htmlspecialchars($iAppraisalDetails['remarks'] ?? '') ?></textarea>
												</div>
												<div style="display:grid; grid-template-columns:1fr; gap:12px;">
													<div>
														<label class="sign-label">Name of Evaluator</label>
														<input type="text" class="form-control" name="name_of_evalutor" value="<?= htmlspecialchars($iAppraisalDetails['name_of_evalutor'] ?? '') ?>">
													</div>
													<div>
														<label class="sign-label">Date/Sign</label>
														<input type="date" class="form-control" name="date_sign" value="<?= htmlspecialchars($iAppraisalDetails['date_sign'] ?? '') ?>">
													</div>
												</div>
												<div>
													<label class="sign-label">Head Teacher</label>
													<input type="text" class="form-control" name="head_teacher" value="<?= htmlspecialchars($iAppraisalDetails['head_teacher'] ?? '') ?>">
												</div>
											</div>

											<div style="margin-top:16px; display:flex; flex-direction:column; gap:10px;">
												<button type="button" id="btnSaveSign" class="btn btn-primary btn-block"><i class="fa fa-save"></i> Save Details</button>
												<a href="<?= $FileName ?>" class="btn btn-outline btn-block"><i class="fa fa-arrow-left"></i> Back</a>
											</div>
										</div>

										<!-- Hidden fields -->
										<input type="hidden" name="teacher_id" value="<?= htmlspecialchars($_GET['teacher_id'] ?? '') ?>">
										<input type="hidden" name="subject_id" value="<?= htmlspecialchars($_GET['subject_id'] ?? '') ?>">
										<input type="hidden" name="session_id" value="<?= htmlspecialchars($_GET['session_id'] ?? '') ?>">
										<input type="hidden" name="term_id" value="<?= htmlspecialchars($_GET['term_id'] ?? '') ?>">
										<input type="hidden" name="month" value="<?= htmlspecialchars($_GET['month'] ?? '') ?>">
										<input type="hidden" name="ddate" value="<?= htmlspecialchars($_GET['ddate'] ?? '') ?>">
										<input type="hidden" name="addnewrecordsavedetails" value="addnewrecordsavedetails">
									</form>

								<?php endif; ?>

							<?php endif; ?>
						</div>
					</div>
				</div>
			</div>
			<?php include('inc.footer.php'); ?>
		</div>
	</div>

	<?php include('inc.js.php'); ?>

	<script>
		// ============================================================================
		// SIGNATURE PAD FUNCTIONALITY
		// ============================================================================

		<?php if (!empty($searchData)): ?>
			// Send to staff function
			function sendToStaff() {
				if (confirm('Really want to send this to staff?')) {
					var url = '<?= $FileName ?>?teacher_id=<?= urlencode($_GET['teacher_id'] ?? '') ?>&subject_id=<?= urlencode($_GET['subject_id'] ?? '') ?>&session_id=<?= urlencode($_GET['session_id'] ?? '') ?>&term_id=<?= urlencode($_GET['term_id'] ?? '') ?>&month=<?= urlencode($_GET['month'] ?? '') ?>&ddate=<?= urlencode($_GET['ddate'] ?? '') ?>&searchdata=Save&action=sendthistostaff';
					window.location.href = url;
				}
			}

			// Signature Pad setup
			document.addEventListener('DOMContentLoaded', function() {
				// Initialize signature pads
				var canvas1 = document.getElementById('sign-pad');
				var canvas2 = document.getElementById('sign-pad1');
				var canvas3 = document.getElementById('sign-pad2');

				if (canvas1) {
					var signaturePad1 = new SignaturePad(canvas1);
				}
				if (canvas2) {
					var signaturePad2 = new SignaturePad(canvas2);
				}
				if (canvas3) {
					var signaturePad3 = new SignaturePad(canvas3);
				}
			});

			// Save signatures
			document.getElementById('btnSaveSign')?.addEventListener('click', function(e) {
				// Capture signatures from canvases
				var canvas1 = document.getElementById('sign-pad');
				var canvas2 = document.getElementById('sign-pad1');
				var canvas3 = document.getElementById('sign-pad2');

				// Teacher signature
				var imgData1 = document.getElementById('img_data').value;
				if (canvas1 && imgData1 == 'Yes') {
					var dataURL1 = canvas1.toDataURL('image/png');
					var img_data1 = dataURL1.replace(/^data:image\/(png|jpg);base64,/, "");
					document.getElementById('img_data').value = img_data1;
				}

				// Principal signature
				var imgData2 = document.getElementById('img_data1').value;
				if (canvas2 && imgData2 == 'Yes') {
					var dataURL2 = canvas2.toDataURL('image/png');
					var img_data2 = dataURL2.replace(/^data:image\/(png|jpg);base64,/, "");
					document.getElementById('img_data1').value = img_data2;
				}

				// Director signature
				var imgData3 = document.getElementById('img_data2').value;
				if (canvas3 && imgData3 == 'Yes') {
					var dataURL3 = canvas3.toDataURL('image/png');
					var img_data3 = dataURL3.replace(/^data:image\/(png|jpg);base64,/, "");
					document.getElementById('img_data2').value = img_data3;
				}

				// Submit the form
				setTimeout(function() {
					document.getElementById('signatureForm').submit();
				}, 500);
			});

			// Clear signature on click
			function editsign(imgdata) {
				document.getElementById(imgdata).value = '';
			}

			// Clear signatures when clicking on canvas
			document.querySelectorAll('.sign-pad').forEach(function(canvas) {
				canvas.addEventListener('click', function() {
					var id = this.id;
					if (id == 'sign-pad') {
						document.getElementById('img_data').value = '';
					} else if (id == 'sign-pad1') {
						document.getElementById('img_data1').value = '';
					} else if (id == 'sign-pad2') {
						document.getElementById('img_data2').value = '';
					}
				});
			});

			// Auto-calculate totals for assessment rows
			document.querySelectorAll('#assessmentForm .form-control[name^="week_"]').forEach(function(input) {
				input.addEventListener('input', function() {
					var row = this.closest('tr');
					var week1 = parseFloat(row.querySelector('[name^="week_1"]')?.value) || 0;
					var week2 = parseFloat(row.querySelector('[name^="week_2"]')?.value) || 0;
					var week3 = parseFloat(row.querySelector('[name^="week_3"]')?.value) || 0;
					var week4 = parseFloat(row.querySelector('[name^="week_4"]')?.value) || 0;
					var total = week1 + week2 + week3 + week4;
					var totalInput = row.querySelector('[name^="total"]');
					if (totalInput) {
						totalInput.value = total.toFixed(2);
					}
				});
			});

			// Auto-calculate totals for punctuality rows
			document.querySelectorAll('#punctualityForm .form-control[name^="week_"]').forEach(function(input) {
				input.addEventListener('input', function() {
					var row = this.closest('tr');
					var week1 = parseFloat(row.querySelector('[name^="week_1"]')?.value) || 0;
					var week2 = parseFloat(row.querySelector('[name^="week_2"]')?.value) || 0;
					var week3 = parseFloat(row.querySelector('[name^="week_3"]')?.value) || 0;
					var week4 = parseFloat(row.querySelector('[name^="week_4"]')?.value) || 0;
					var total = week1 + week2 + week3 + week4;
					var totalInput = row.querySelector('[name^="total"]');
					if (totalInput) {
						totalInput.value = total.toFixed(2);
					}
				});
			});
		<?php endif; ?>
	</script>
</body>

</html>