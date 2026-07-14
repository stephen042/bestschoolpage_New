<?php

/**
 * ============================================================================
 * SEND SMS - MODERN REDESIGN
 * ============================================================================
 * Description: Send SMS messages to parents and staff
 * Version: 4.0 (Fully Mobile Responsive)
 * ============================================================================
 */

include('../config.php');
include('inc.session-create.php');

$PageTitle = "SEND SMS";
$FileName = 'send_sms.php';

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
// SMS FUNCTION
// ============================================================================
function sendSMS($number, $message, $schoolName)
{
	$messages = urlencode($message);
	$url = "http://login.betasms.com/api/?username=joseakplogan@gmail.com&password=12345678&message=$messages&sender=SCHOOLINFO&mobiles=$number";
	$ret = file_get_contents($url);
	return $ret;
}

// ============================================================================
// GET SCHOOL DETAILS
// ============================================================================
$schoolDetails = db_get_row("SELECT * FROM school_register WHERE create_by_userid = ?", [$create_by_userid]);
$schoolName = $schoolDetails['name'] ?? 'School';

// ============================================================================
// GET SMS BALANCE
// ============================================================================
$smsBalance = db_get_val(
	"SELECT SUM(no_of_sms) FROM sms_payment 
     WHERE create_by_userid = ? AND status = '1' AND no_of_sms > 0",
	[$create_by_userid]
);
$smsBalance = $smsBalance !== false ? (int)$smsBalance : 0;

// ============================================================================
// SEND SMS TO PARENTS
// ============================================================================
if (isset($_POST['parentSMS'])) {
	$smsBalance = db_get_val(
		"SELECT SUM(no_of_sms) FROM sms_payment 
         WHERE create_by_userid = ? AND status = '1' AND no_of_sms > 0",
		[$create_by_userid]
	);
	$smsBalance = $smsBalance !== false ? (int)$smsBalance : 0;

	if ($smsBalance > 0) {
		$validate->addRule($_POST['pSMS'], '', 'Message', true);

		if ($validate->validate() && count($stat) == 0) {
			$mainMessage = $_POST['pSMS'];
			$sentCount = 0;

			if (isset($_POST['sendSMSToParent']) && is_array($_POST['sendSMSToParent'])) {
				foreach ($_POST['sendSMSToParent'] as $number) {
					if (!empty($number)) {
						sendSMS($number, $mainMessage, $schoolName);
						$sentCount++;
					}
				}
			}

			// Deduct SMS from balance
			if ($sentCount > 0) {
				db_update(
					"sms_payment",
					"no_of_sms = no_of_sms - $sentCount",
					"create_by_userid = ? AND status = '1' AND no_of_sms > 0",
					[$create_by_userid]
				);
				$_SESSION['success'] = "Message sent to $sentCount parent(s) successfully!";
			} else {
				$_SESSION['error'] = "No parents selected. Please select at least one parent.";
			}
			redirect($FileName . '?action=parents');
		} else {
			$stat['error'] = $validate->errors();
		}
	} else {
		$stat['error'] = "Insufficient SMS balance. Please purchase more SMS credits.";
	}
}

// ============================================================================
// SEND SMS TO STAFF
// ============================================================================
if (isset($_POST['staffSMS'])) {
	$smsBalance = db_get_val(
		"SELECT SUM(no_of_sms) FROM sms_payment 
         WHERE create_by_userid = ? AND status = '1' AND no_of_sms > 0",
		[$create_by_userid]
	);
	$smsBalance = $smsBalance !== false ? (int)$smsBalance : 0;

	if ($smsBalance > 0) {
		$validate->addRule($_POST['sSMS'], '', 'Message', true);

		if ($validate->validate() && count($stat) == 0) {
			$mainMessage = $_POST['sSMS'];
			$sentCount = 0;

			if (isset($_POST['sendSsmsToStaff']) && is_array($_POST['sendSsmsToStaff'])) {
				foreach ($_POST['sendSsmsToStaff'] as $number) {
					if (!empty($number)) {
						sendSMS($number, $mainMessage, $schoolName);
						$sentCount++;
					}
				}
			}

			// Deduct SMS from balance
			if ($sentCount > 0) {
				db_update(
					"sms_payment",
					"no_of_sms = no_of_sms - $sentCount",
					"create_by_userid = ? AND status = '1' AND no_of_sms > 0",
					[$create_by_userid]
				);
				$_SESSION['success'] = "Message sent to $sentCount staff member(s) successfully!";
			} else {
				$_SESSION['error'] = "No staff selected. Please select at least one staff member.";
			}
			redirect($FileName . '?action=staff');
		} else {
			$stat['error'] = $validate->errors();
		}
	} else {
		$stat['error'] = "Insufficient SMS balance. Please purchase more SMS credits.";
	}
}

// ============================================================================
// GET DATA
// ============================================================================
$action = $_GET['action'] ?? 'parents';

// Get parents
$parents = db_get_rows(
	"SELECT DISTINCT sg.parent_id, sg.first_name, sg.last_name, sg.phone, sg.email 
     FROM student_guardian sg
     WHERE sg.create_by_userid = ? AND sg.phone IS NOT NULL AND sg.phone != ''
     ORDER BY sg.first_name ASC",
	[$create_by_userid]
);

// Get staff
$staff = db_get_rows(
	"SELECT * FROM staff_manage 
     WHERE create_by_userid = ? AND phone IS NOT NULL AND phone != ''
     ORDER BY first_name ASC",
	[$create_by_userid]
);
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

		.sms-container {
			max-width: 1200px;
			margin: 0 auto;
			padding: 15px;
		}

		/* ============================================================
        PAGE HEADER - MOBILE FIRST
        ============================================================ */
		.page-header {
			margin-bottom: 25px;
		}

		.page-header h2 {
			color: #1B3058;
			margin: 0;
			font-size: 24px;
			font-weight: 700;
		}

		.page-header h2 i {
			margin-right: 8px;
		}

		.page-header .balance-badge {
			display: inline-flex;
			align-items: center;
			gap: 8px;
			background: #1B3058;
			color: white;
			padding: 6px 16px;
			border-radius: 20px;
			font-size: 14px;
			font-weight: 600;
			margin-top: 8px;
		}

		.page-header .balance-badge .balance-number {
			color: #4ade80;
		}

		/* ============================================================
        SMS CARD - MOBILE FIRST
        ============================================================ */
		.sms-card {
			background: #fff;
			border-radius: 20px;
			box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
			overflow: hidden;
		}

		.sms-card .card-header {
			padding: 14px 18px;
			background: linear-gradient(135deg, #1B3058, #2a4780);
			color: white;
			font-weight: 600;
			font-size: 15px;
			display: flex;
			align-items: center;
			justify-content: space-between;
			flex-wrap: wrap;
			gap: 8px;
		}

		.sms-card .card-header i {
			font-size: 18px;
			margin-right: 8px;
		}

		.sms-card .card-body {
			padding: 16px;
		}

		/* ============================================================
        TABS - MOBILE FIRST
        ============================================================ */
		.sms-tabs {
			display: flex;
			gap: 4px;
			margin-bottom: 20px;
			background: #f0f2f5;
			border-radius: 12px;
			padding: 4px;
		}

		.sms-tabs .tab-link {
			flex: 1;
			padding: 10px 16px;
			border: none;
			border-radius: 10px;
			font-size: 14px;
			font-weight: 600;
			cursor: pointer;
			transition: all 0.3s;
			background: transparent;
			color: #666;
			text-decoration: none;
			text-align: center;
			min-height: 44px;
			display: flex;
			align-items: center;
			justify-content: center;
			gap: 8px;
		}

		.sms-tabs .tab-link:active {
			transform: scale(0.97);
		}

		.sms-tabs .tab-link i {
			font-size: 16px;
		}

		.sms-tabs .tab-link.active {
			background: #1B3058;
			color: white;
			box-shadow: 0 2px 8px rgba(27, 48, 88, 0.25);
		}

		.sms-tabs .tab-link:hover:not(.active) {
			background: #e8eef5;
		}

		/* ============================================================
        MESSAGE AREA - MOBILE FIRST
        ============================================================ */
		.message-area {
			margin-bottom: 16px;
		}

		.message-area label {
			display: block;
			font-weight: 600;
			color: #333;
			margin-bottom: 6px;
			font-size: 14px;
		}

		.message-area textarea {
			width: 100%;
			padding: 12px 14px;
			border: 2px solid #e0e0e0;
			border-radius: 12px;
			font-size: 14px;
			font-family: inherit;
			resize: vertical;
			min-height: 100px;
			transition: all 0.2s;
		}

		.message-area textarea:focus {
			outline: none;
			border-color: #1B3058;
			box-shadow: 0 0 0 3px rgba(27, 48, 88, 0.1);
		}

		.message-area .char-count {
			text-align: right;
			font-size: 12px;
			color: #999;
			margin-top: 4px;
		}

		/* ============================================================
        RECIPIENTS TABLE - MOBILE FIRST
        ============================================================ */
		.recipients-section {
			margin-top: 4px;
		}

		.recipients-section .section-label {
			display: flex;
			align-items: center;
			justify-content: space-between;
			flex-wrap: wrap;
			gap: 8px;
			margin-bottom: 12px;
		}

		.recipients-section .section-label h4 {
			margin: 0;
			font-size: 15px;
			color: #333;
		}

		.recipients-section .section-label .count {
			font-size: 13px;
			color: #999;
			background: #f8f9fa;
			padding: 2px 12px;
			border-radius: 12px;
		}

		.table-wrapper {
			overflow-x: auto;
			-webkit-overflow-scrolling: touch;
			border: 1px solid #e0e0e0;
			border-radius: 12px;
		}

		.recipients-table {
			width: 100%;
			min-width: 400px;
			border-collapse: collapse;
			font-size: 13px;
		}

		.recipients-table th,
		.recipients-table td {
			padding: 10px 12px;
			text-align: left;
			border-bottom: 1px solid #f0f0f0;
			vertical-align: middle;
		}

		.recipients-table th {
			background: #f8f9fa;
			font-weight: 700;
			color: #1B3058;
			font-size: 11px;
			text-transform: uppercase;
			letter-spacing: 0.3px;
		}

		.recipients-table tr:hover td {
			background: #f8f9ff;
		}

		.recipients-table tr:last-child td {
			border-bottom: none;
		}

		.recipients-table input[type="checkbox"] {
			width: 18px;
			height: 18px;
			cursor: pointer;
			accent-color: #1B3058;
		}

		.recipients-table .checkbox-cell {
			text-align: center;
			width: 40px;
		}

		.recipients-table .select-all-label {
			display: flex;
			align-items: center;
			gap: 6px;
			font-size: 12px;
			color: #666;
			cursor: pointer;
		}

		.recipients-table .select-all-label input {
			width: 16px;
			height: 16px;
			cursor: pointer;
		}

		/* ============================================================
        BUTTONS - MOBILE FIRST
        ============================================================ */
		.btn {
			display: inline-flex;
			align-items: center;
			justify-content: center;
			gap: 8px;
			padding: 10px 20px;
			border: none;
			border-radius: 10px;
			font-size: 14px;
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

		.btn-outline {
			background: transparent;
			color: #1B3058;
			border: 2px solid #1B3058;
		}

		.btn-outline:hover {
			background: #1B3058;
			color: white;
		}

		.btn-block {
			width: 100%;
			justify-content: center;
		}

		.send-btn {
			margin-top: 16px;
			padding: 14px;
			font-size: 16px;
			border-radius: 12px;
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

		/* ============================================================
        EMPTY STATE - MOBILE FIRST
        ============================================================ */
		.empty-state {
			text-align: center;
			padding: 40px 20px;
			color: #999;
		}

		.empty-state i {
			font-size: 48px;
			color: #ddd;
			display: block;
			margin-bottom: 12px;
		}

		.empty-state h4 {
			color: #666;
			font-size: 16px;
			margin-bottom: 4px;
		}

		.empty-state p {
			font-size: 13px;
		}

		/* ============================================================
        RESPONSIVE - TABLET (768px+)
        ============================================================ */
		@media (min-width: 768px) {
			.sms-container {
				padding: 25px;
			}

			.page-header h2 {
				font-size: 28px;
			}

			.page-header .balance-badge {
				margin-top: 0;
				float: right;
			}

			.sms-card .card-body {
				padding: 24px;
			}

			.recipients-table {
				min-width: auto;
			}

			.recipients-table th,
			.recipients-table td {
				padding: 12px 16px;
			}

			.send-btn {
				width: auto;
				padding: 12px 40px;
			}
		}

		/* ============================================================
        RESPONSIVE - DESKTOP (1024px+)
        ============================================================ */
		@media (min-width: 1024px) {
			.sms-container {
				padding: 30px;
			}
		}

		/* ============================================================
        RESPONSIVE - SMALL MOBILE (480px-)
        ============================================================ */
		@media (max-width: 480px) {
			.sms-container {
				padding: 10px;
			}

			.page-header h2 {
				font-size: 20px;
			}

			.page-header .balance-badge {
				font-size: 12px;
				padding: 4px 12px;
			}

			.sms-tabs .tab-link {
				font-size: 12px;
				padding: 8px 10px;
			}

			.sms-tabs .tab-link span {
				display: none;
			}

			.sms-tabs .tab-link i {
				font-size: 18px;
			}

			.message-area textarea {
				font-size: 13px;
				min-height: 80px;
			}

			.recipients-table {
				font-size: 11px;
				min-width: 320px;
			}

			.recipients-table th,
			.recipients-table td {
				padding: 6px 8px;
			}

			.btn {
				font-size: 13px;
				padding: 8px 14px;
				min-height: 38px;
			}
		}

		/* ============================================================
        PRINT STYLES
        ============================================================ */
		@media print {

			.btn,
			.no-print {
				display: none !important;
			}

			.sms-card {
				box-shadow: none !important;
				border: 1px solid #ddd;
			}

			.sms-card .card-header {
				-webkit-print-color-adjust: exact;
				print-color-adjust: exact;
			}

			body {
				background: white;
			}

			.sms-container {
				padding: 0;
			}
		}
	</style>
</head>

<body>
	<div id="wrapper">
		<?php include('inc.header.php'); ?>
		<?php include('inc.sideleft.php'); ?>
		<div class="content-page">
			<div class="content">
				<div class="sms-container">

					<!-- Page Header -->
					<div class="page-header">
						<div style="display: flex; flex-wrap: wrap; justify-content: space-between; align-items: flex-start; gap: 10px;">
							<div>
								<h2><i class="fa fa-envelope"></i> <?= htmlspecialchars($PageTitle) ?></h2>
							</div>
							<div class="balance-badge">
								<i class="fa fa-credit-card"></i>
								SMS Balance: <span class="balance-number"><?= number_format($smsBalance) ?></span>
							</div>
						</div>
					</div>

					<?= showMessage($stat) ?>

					<!-- SMS Card -->
					<div class="sms-card">
						<div class="card-header">
							<span><i class="fa fa-send-o"></i> Compose Message</span>
							<span style="font-size: 12px; opacity: 0.8;">
								<i class="fa fa-info-circle"></i> Select recipients below
							</span>
						</div>
						<div class="card-body">

							<!-- Tabs -->
							<div class="sms-tabs">
								<a href="<?= $FileName ?>?action=parents" class="tab-link <?= ($action == 'parents' || $action == '') ? 'active' : '' ?>">
									<i class="fa fa-users"></i> <span>Parents</span>
								</a>
								<a href="<?= $FileName ?>?action=staff" class="tab-link <?= ($action == 'staff') ? 'active' : '' ?>">
									<i class="fa fa-user-md"></i> <span>Staff</span>
								</a>
							</div>

							<?php if ($action == 'parents' || $action == ''): ?>
								<!-- Parents Tab -->
								<form method="POST" action="">
									<div class="message-area">
										<label for="pSMS"><i class="fa fa-pencil"></i> Message</label>
										<textarea id="pSMS" name="pSMS" placeholder="Type your message here..." oninput="updateCharCount(this, 'parentCharCount')"><?= htmlspecialchars($_POST['pSMS'] ?? '') ?></textarea>
										<div class="char-count"><span id="parentCharCount">0</span> characters</div>
									</div>

									<div class="recipients-section">
										<div class="section-label">
											<h4><i class="fa fa-phone"></i> Select Parents</h4>
											<span class="count"><?= count($parents) ?> available</span>
										</div>

										<?php if (!empty($parents)): ?>
											<div class="table-wrapper">
												<table class="recipients-table">
													<thead>
														<tr>
															<th class="checkbox-cell">
																<label class="select-all-label">
																	<input type="checkbox" id="selectAllParents" onchange="toggleAll(this, 'sendSMSToParent')">
																	All
																</label>
															</th>
															<th>Parent ID</th>
															<th>First Name</th>
															<th>Last Name</th>
															<th>Phone</th>
															<th>Email</th>
														</tr>
													</thead>
													<tbody>
														<?php $i = 0;
														foreach ($parents as $parent): ?>
															<tr>
																<td class="checkbox-cell">
																	<input type="checkbox" name="sendSMSToParent[]" value="<?= htmlspecialchars($parent['phone']) ?>">
																</td>
																<td><?= htmlspecialchars($parent['parent_id'] ?? 'N/A') ?></td>
																<td><?= htmlspecialchars($parent['first_name'] ?? '') ?></td>
																<td><?= htmlspecialchars($parent['last_name'] ?? '') ?></td>
																<td><?= htmlspecialchars($parent['phone'] ?? '') ?></td>
																<td><?= htmlspecialchars($parent['email'] ?? '') ?></td>
															</tr>
														<?php endforeach; ?>
													</tbody>
												</table>
											</div>
										<?php else: ?>
											<div class="empty-state">
												<i class="fa fa-user-slash"></i>
												<h4>No Parents Found</h4>
												<p>No parents with phone numbers available. Please add parent records.</p>
											</div>
										<?php endif; ?>
									</div>

									<button type="submit" name="parentSMS" class="btn btn-success send-btn" <?= empty($parents) ? 'disabled' : '' ?>>
										<i class="fa fa-send"></i> Send SMS to Parents
									</button>
								</form>

							<?php else: ?>
								<!-- Staff Tab -->
								<form method="POST" action="">
									<div class="message-area">
										<label for="sSMS"><i class="fa fa-pencil"></i> Message</label>
										<textarea id="sSMS" name="sSMS" placeholder="Type your message here..." oninput="updateCharCount(this, 'staffCharCount')"><?= htmlspecialchars($_POST['sSMS'] ?? '') ?></textarea>
										<div class="char-count"><span id="staffCharCount">0</span> characters</div>
									</div>

									<div class="recipients-section">
										<div class="section-label">
											<h4><i class="fa fa-phone"></i> Select Staff</h4>
											<span class="count"><?= count($staff) ?> available</span>
										</div>

										<?php if (!empty($staff)): ?>
											<div class="table-wrapper">
												<table class="recipients-table">
													<thead>
														<tr>
															<th class="checkbox-cell">
																<label class="select-all-label">
																	<input type="checkbox" id="selectAllStaff" onchange="toggleAll(this, 'sendSsmsToStaff')">
																	All
																</label>
															</th>
															<th>Staff ID</th>
															<th>First Name</th>
															<th>Last Name</th>
															<th>Phone</th>
															<th>Email</th>
														</tr>
													</thead>
													<tbody>
														<?php $i = 0;
														foreach ($staff as $staffMember): ?>
															<tr>
																<td class="checkbox-cell">
																	<input type="checkbox" name="sendSsmsToStaff[]" value="<?= htmlspecialchars($staffMember['phone']) ?>">
																</td>
																<td><?= htmlspecialchars($staffMember['staff_id'] ?? 'N/A') ?></td>
																<td><?= htmlspecialchars($staffMember['first_name'] ?? '') ?></td>
																<td><?= htmlspecialchars($staffMember['last_name'] ?? '') ?></td>
																<td><?= htmlspecialchars($staffMember['phone'] ?? '') ?></td>
																<td><?= htmlspecialchars($staffMember['email'] ?? '') ?></td>
															</tr>
														<?php endforeach; ?>
													</tbody>
												</table>
											</div>
										<?php else: ?>
											<div class="empty-state">
												<i class="fa fa-user-slash"></i>
												<h4>No Staff Found</h4>
												<p>No staff members with phone numbers available. Please add staff records.</p>
											</div>
										<?php endif; ?>
									</div>

									<button type="submit" name="staffSMS" class="btn btn-success send-btn" <?= empty($staff) ? 'disabled' : '' ?>>
										<i class="fa fa-send"></i> Send SMS to Staff
									</button>
								</form>

							<?php endif; ?>

						</div>
					</div>

					<!-- Info Section -->
					<div style="background: #fff; border-radius: 16px; padding: 16px 20px; box-shadow: 0 2px 12px rgba(0,0,0,0.08); margin-top: 16px;">
						<div style="display: flex; align-items: flex-start; gap: 12px; flex-wrap: wrap;">
							<i class="fa fa-info-circle" style="font-size: 20px; color: #17a2b8; margin-top: 2px;"></i>
							<div>
								<strong style="color: #1B3058; display: block; font-size: 14px;">SMS Usage Tips</strong>
								<span style="color: #666; font-size: 13px;">
									• Each SMS credit allows you to send one message to one recipient.<br>
									• Messages are limited to 160 characters per SMS.<br>
									• Purchase more SMS credits from the <a href="sms_plan.php" style="color: #1B3058; font-weight: 600;">SMS Plan</a> page.
								</span>
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
		// ============================================================================
		// JAVASCRIPT FUNCTIONS
		// ============================================================================

		// Toggle all checkboxes
		function toggleAll(masterCheckbox, checkboxName) {
			var checkboxes = document.querySelectorAll('input[name="' + checkboxName + '[]"]');
			checkboxes.forEach(function(cb) {
				cb.checked = masterCheckbox.checked;
			});
		}

		// Update character count
		function updateCharCount(textarea, counterId) {
			var count = textarea.value.length;
			document.getElementById(counterId).textContent = count;
		}

		// Auto-resize textareas
		document.addEventListener('DOMContentLoaded', function() {
			var textareas = document.querySelectorAll('textarea[name="pSMS"], textarea[name="sSMS"]');
			textareas.forEach(function(textarea) {
				textarea.addEventListener('input', function() {
					this.style.height = 'auto';
					this.style.height = Math.min(this.scrollHeight, 200) + 'px';
				});
				// Trigger initial resize
				textarea.style.height = 'auto';
				textarea.style.height = Math.min(textarea.scrollHeight, 200) + 'px';
			});
		});
	</script>
</body>

</html>