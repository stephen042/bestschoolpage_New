<?php

/**
 * ============================================================================
 * SEND EMAIL - MODERN REDESIGN
 * ============================================================================
 * Description: Send email messages to parents and staff
 * Version: 4.0 (Fully Mobile Responsive)
 * ============================================================================
 */

include('../config.php');
include('inc.session-create.php');

$PageTitle = "SEND EMAIL";
$FileName = 'send_mail.php';

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
// GET SCHOOL NAME
// ============================================================================
$schoolDetails = db_get_row("SELECT name FROM school_register WHERE create_by_userid = ?", [$create_by_userid]);
$schoolName = $schoolDetails['name'] ?? 'School';

// ============================================================================
// GET PARENTS FOR DISPLAY (with pagination)
// ============================================================================
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 25;
$offset = ($page - 1) * $perPage;

// Count total parents
$totalParents = db_get_val(
	"SELECT COUNT(DISTINCT parent_id) FROM student_guardian WHERE create_by_userid = ? AND parent_id IS NOT NULL AND parent_id != ''",
	[$create_by_userid]
);
$totalParents = (int)$totalParents;
$totalParentPages = ceil($totalParents / $perPage);

// Get parents with pagination
$parentList = db_get_rows(
	"SELECT DISTINCT parent_id, first_name, last_name, phone, email 
     FROM student_guardian 
     WHERE create_by_userid = ? AND parent_id IS NOT NULL AND parent_id != ''
     ORDER BY first_name ASC 
     LIMIT ? OFFSET ?",
	[$create_by_userid, $perPage, $offset]
);

// ============================================================================
// GET STAFF FOR DISPLAY (with pagination)
// ============================================================================
$staffPage = isset($_GET['staff_page']) ? (int)$_GET['staff_page'] : 1;
$staffOffset = ($staffPage - 1) * $perPage;

// Count total staff
$totalStaff = db_get_val(
	"SELECT COUNT(*) FROM staff_manage WHERE create_by_userid = ?",
	[$create_by_userid]
);
$totalStaff = (int)$totalStaff;
$totalStaffPages = ceil($totalStaff / $perPage);

// Get staff with pagination
$staffList = db_get_rows(
	"SELECT * FROM staff_manage 
     WHERE create_by_userid = ? 
     ORDER BY first_name ASC 
     LIMIT ? OFFSET ?",
	[$create_by_userid, $perPage, $staffOffset]
);

// ============================================================================
// PROCESS PARENT EMAIL
// ============================================================================
if (isset($_POST['parentMAIL'])) {
	$validate->addRule($_POST['pSubject'] ?? '', '', 'Subject', true);
	$validate->addRule($_POST['pMessage'] ?? '', '', 'Message', true);

	if ($validate->validate() && count($stat) == 0) {
		$mainMessage = $_POST['pMessage'];
		$subject = $_POST['pSubject'];
		$recipients = 0;

		if (isset($_POST['sendMAILToParent']) && is_array($_POST['sendMAILToParent'])) {
			foreach ($_POST['sendMAILToParent'] as $email) {
				if (!empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
					$headers = "From: " . $schoolName . " <mail@schoolinfo.com>\r\n";
					$headers .= "Reply-To: mail@schoolinfo.com\r\n";
					$headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";
					$headers .= "X-Priority: 1\r\n";
					$headers .= "MIME-Version: 1.0\r\n";
					$headers .= "Content-Type: text/html; charset=UTF-8\r\n";

					mail($email, $subject, $mainMessage, $headers);
					$recipients++;
				}
			}
			$_SESSION['success'] = "Email sent successfully to $recipients parent(s)!";
			redirect('send_mail.php');
		} else {
			$stat['error'] = "No parents selected.";
		}
	} else {
		$stat['error'] = $validate->errors();
	}
}

// ============================================================================
// PROCESS STAFF EMAIL
// ============================================================================
if (isset($_POST['staffEMAIL'])) {
	$validate->addRule($_POST['sSubject'] ?? '', '', 'Subject', true);
	$validate->addRule($_POST['sMessage'] ?? '', '', 'Message', true);

	if ($validate->validate() && count($stat) == 0) {
		$mainMessage = $_POST['sMessage'];
		$subject = $_POST['sSubject'];
		$recipients = 0;

		if (isset($_POST['sendMAILToStaff']) && is_array($_POST['sendMAILToStaff'])) {
			foreach ($_POST['sendMAILToStaff'] as $email) {
				if (!empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
					$headers = "From: " . $schoolName . " <mail@schoolinfo.com>\r\n";
					$headers .= "Reply-To: mail@schoolinfo.com\r\n";
					$headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";
					$headers .= "X-Priority: 1\r\n";
					$headers .= "MIME-Version: 1.0\r\n";
					$headers .= "Content-Type: text/html; charset=UTF-8\r\n";

					mail($email, $subject, $mainMessage, $headers);
					$recipients++;
				}
			}
			$_SESSION['success'] = "Email sent successfully to $recipients staff member(s)!";
			redirect('send_mail.php?action=staff');
		} else {
			$stat['error'] = "No staff members selected.";
		}
	} else {
		$stat['error'] = $validate->errors();
	}
}

// Get current action
$action = $_GET['action'] ?? 'parents';
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

		.email-container {
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
		}

		.tab-btn {
			flex: 1;
			padding: 14px 12px;
			text-align: center;
			font-weight: 600;
			font-size: 14px;
			color: #888;
			cursor: pointer;
			transition: all 0.3s;
			border: none;
			background: transparent;
			position: relative;
			min-height: 48px;
			touch-action: manipulation;
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
			left: 20%;
			right: 20%;
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
		.form-group {
			margin-bottom: 16px;
		}

		.form-group label {
			display: block;
			font-weight: 600;
			font-size: 13px;
			color: #333;
			margin-bottom: 6px;
		}

		.form-group label .required {
			color: #dc3545;
		}

		.form-group input[type="text"] {
			width: 100%;
			padding: 10px 14px;
			border: 2px solid #e0e0e0;
			border-radius: 12px;
			font-size: 14px;
			transition: all 0.2s;
		}

		.form-group input[type="text"]:focus {
			outline: none;
			border-color: #1B3058;
			box-shadow: 0 0 0 3px rgba(27, 48, 88, 0.1);
		}

		.form-group textarea {
			width: 100%;
			padding: 12px 14px;
			border: 2px solid #e0e0e0;
			border-radius: 12px;
			font-size: 14px;
			font-family: inherit;
			resize: vertical;
			min-height: 120px;
			transition: all 0.2s;
		}

		.form-group textarea:focus {
			outline: none;
			border-color: #1B3058;
			box-shadow: 0 0 0 3px rgba(27, 48, 88, 0.1);
		}

		.btn-send {
			background: linear-gradient(135deg, #1B3058, #2a4780);
			color: white;
			border: none;
			padding: 12px 30px;
			border-radius: 12px;
			font-weight: 600;
			font-size: 15px;
			cursor: pointer;
			transition: all 0.3s;
			display: inline-flex;
			align-items: center;
			gap: 8px;
			width: 100%;
			justify-content: center;
			min-height: 48px;
			touch-action: manipulation;
		}

		.btn-send:active {
			transform: scale(0.97);
		}

		.btn-send:hover {
			background: linear-gradient(135deg, #f21151, #c90d44);
			transform: translateY(-2px);
		}

		.btn-send:disabled {
			opacity: 0.6;
			cursor: not-allowed;
			transform: none;
		}

		.btn-select-all {
			background: #1B3058;
			color: white;
			border: none;
			padding: 8px 16px;
			border-radius: 8px;
			font-weight: 600;
			font-size: 12px;
			cursor: pointer;
			transition: all 0.3s;
			min-height: 36px;
			touch-action: manipulation;
		}

		.btn-select-all:active {
			transform: scale(0.97);
		}

		.btn-select-all:hover {
			background: #f21151;
		}

		/* ============================================================
        TABLE - MOBILE FIRST
        ============================================================ */
		.table-wrapper {
			overflow-x: auto;
			-webkit-overflow-scrolling: touch;
			margin: 12px -4px 0;
			padding: 0 4px;
		}

		.table {
			width: 100%;
			min-width: 550px;
			border-collapse: collapse;
			font-size: 13px;
		}

		.table th,
		.table td {
			padding: 10px 8px;
			text-align: left;
			border-bottom: 1px solid #f0f0f0;
			vertical-align: middle;
		}

		.table th {
			background: #f8f9fa;
			font-weight: 700;
			color: #1B3058;
			font-size: 11px;
			text-transform: uppercase;
			letter-spacing: 0.3px;
			position: sticky;
			top: 0;
		}

		.table td {
			font-size: 13px;
		}

		.table tr:hover td {
			background: #f8f9ff;
		}

		.table .checkbox-cell {
			text-align: center;
			width: 40px;
		}

		.table input[type="checkbox"] {
			width: 18px;
			height: 18px;
			cursor: pointer;
			accent-color: #1B3058;
		}

		.table .parent-name {
			font-weight: 600;
		}

		.table .parent-email {
			color: #1B3058;
			font-size: 12px;
		}

		/* ============================================================
        PAGINATION - MOBILE FIRST
        ============================================================ */
		.pagination {
			display: flex;
			flex-wrap: wrap;
			gap: 6px;
			justify-content: center;
			margin-top: 16px;
			padding: 12px 0;
		}

		.pagination a,
		.pagination span {
			display: inline-flex;
			align-items: center;
			justify-content: center;
			padding: 6px 14px;
			border: 1px solid #e0e0e0;
			border-radius: 8px;
			text-decoration: none;
			color: #1B3058;
			background: #fff;
			font-size: 13px;
			font-weight: 500;
			min-height: 36px;
			min-width: 36px;
			transition: all 0.2s;
		}

		.pagination a:active {
			transform: scale(0.95);
		}

		.pagination a:hover {
			background: #f8f9ff;
			border-color: #1B3058;
		}

		.pagination .active {
			background: #1B3058;
			color: #fff;
			border-color: #1B3058;
		}

		.pagination .disabled {
			color: #ccc;
			cursor: not-allowed;
		}

		.pagination .info {
			background: transparent;
			border: none;
			color: #666;
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
        RECIPIENT COUNT - MOBILE FIRST
        ============================================================ */
		.recipient-info {
			display: flex;
			gap: 10px;
			flex-wrap: wrap;
			align-items: center;
			margin-bottom: 12px;
			padding: 10px 14px;
			background: #f8f9fa;
			border-radius: 10px;
		}

		.recipient-info .count {
			font-size: 13px;
			color: #666;
		}

		.recipient-info .count strong {
			color: #1B3058;
		}

		/* ============================================================
        RESPONSIVE - TABLET (768px+)
        ============================================================ */
		@media (min-width: 768px) {
			.email-container {
				padding: 25px;
			}

			.page-header h2 {
				font-size: 28px;
			}

			.tab-content {
				padding: 24px;
			}

			.btn-send {
				width: auto;
				padding: 12px 40px;
			}

			.table {
				min-width: auto;
			}

			.table th,
			.table td {
				padding: 12px 14px;
			}
		}

		/* ============================================================
        RESPONSIVE - DESKTOP (1024px+)
        ============================================================ */
		@media (min-width: 1024px) {
			.email-container {
				padding: 30px;
			}
		}

		/* ============================================================
        RESPONSIVE - SMALL MOBILE (480px-)
        ============================================================ */
		@media (max-width: 480px) {
			.email-container {
				padding: 10px;
			}

			.page-header h2 {
				font-size: 20px;
			}

			.page-header p {
				font-size: 12px;
			}

			.tab-btn {
				font-size: 12px;
				padding: 10px 8px;
			}

			.tab-btn i {
				font-size: 14px;
				margin-right: 4px;
			}

			.tab-btn span {
				display: none;
			}

			.table {
				font-size: 11px;
				min-width: 400px;
			}

			.table th,
			.table td {
				padding: 6px 4px;
			}

			.table th {
				font-size: 9px;
			}

			.table td {
				font-size: 11px;
			}

			.table input[type="checkbox"] {
				width: 16px;
				height: 16px;
			}

			.pagination a,
			.pagination span {
				padding: 4px 10px;
				font-size: 11px;
				min-height: 30px;
				min-width: 30px;
			}

			.form-group input[type="text"] {
				font-size: 13px;
				padding: 8px 12px;
			}

			.form-group textarea {
				font-size: 13px;
				padding: 10px 12px;
				min-height: 100px;
			}

			.btn-send {
				font-size: 13px;
				padding: 10px 20px;
				min-height: 42px;
			}

			.recipient-info {
				padding: 8px 12px;
				font-size: 12px;
			}
		}

		/* ============================================================
        PRINT STYLES
        ============================================================ */
		@media print {

			.btn-send,
			.btn-select-all,
			.pagination,
			.no-print {
				display: none !important;
			}

			.tabs-container {
				box-shadow: none !important;
				border: 1px solid #ddd;
			}

			.tab-content {
				padding: 10px;
			}

			body {
				background: white;
			}

			.email-container {
				padding: 0;
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
				<div class="email-container">

					<!-- Page Header -->
					<div class="page-header">
						<h2><i class="fa fa-envelope"></i> <?= htmlspecialchars($PageTitle) ?></h2>
						<p>Send email messages to parents and staff members</p>
					</div>

					<?= showMessage($stat) ?>

					<!-- Tabs -->
					<div class="tabs-container">
						<div class="tabs-header">
							<button class="tab-btn <?= ($action == 'parents' || $action == '') ? 'active' : '' ?>" onclick="window.location.href='?action=parents'">
								<i class="fa fa-users"></i> <span>Parents</span>
							</button>
							<button class="tab-btn <?= ($action == 'staff') ? 'active' : '' ?>" onclick="window.location.href='?action=staff'">
								<i class="fa fa-user-md"></i> <span>Staff</span>
							</button>
						</div>

						<div class="tab-content">
							<?php if ($action == 'parents' || $action == ''): ?>
								<!-- PARENTS TAB -->
								<form method="POST" action="" id="parentEmailForm">
									<div class="form-group">
										<label><i class="fa fa-tag"></i> Subject <span class="required">*</span></label>
										<input type="text" name="pSubject" placeholder="Enter email subject..." value="<?= htmlspecialchars($_POST['pSubject'] ?? '') ?>" required>
									</div>

									<div class="form-group">
										<label><i class="fa fa-pencil"></i> Message <span class="required">*</span></label>
										<textarea name="pMessage" placeholder="Type your message here..." required><?= htmlspecialchars($_POST['pMessage'] ?? '') ?></textarea>
									</div>

									<div class="recipient-info">
										<span class="count"><i class="fa fa-users"></i> <strong><?= $totalParents ?></strong> parent(s) found</span>
										<button type="button" class="btn-select-all" onclick="toggleAllParents()">
											<i class="fa fa-check-square-o"></i> Select All
										</button>
									</div>

									<div class="table-wrapper">
										<table class="table" id="parentTable">
											<thead>
												<tr>
													<th class="checkbox-cell"><input type="checkbox" id="selectAllParents" onchange="toggleAllParents()"></th>
													<th>Parent ID</th>
													<th>Name</th>
													<th>Phone</th>
													<th>Email</th>
												</tr>
											</thead>
											<tbody>
												<?php if (!empty($parentList)): ?>
													<?php foreach ($parentList as $parent): ?>
														<tr>
															<td class="checkbox-cell">
																<input type="checkbox" name="sendMAILToParent[]" value="<?= htmlspecialchars($parent['email']) ?>" class="parent-checkbox" <?= !empty($parent['email']) ? '' : 'disabled' ?>>
															</td>
															<td><strong><?= htmlspecialchars($parent['parent_id']) ?></strong></td>
															<td class="parent-name"><?= htmlspecialchars($parent['first_name'] . ' ' . $parent['last_name']) ?></td>
															<td><?= htmlspecialchars($parent['phone'] ?? 'N/A') ?></td>
															<td class="parent-email"><?= htmlspecialchars($parent['email'] ?? 'N/A') ?></td>
														</tr>
													<?php endforeach; ?>
												<?php else: ?>
													<tr>
														<td colspan="5" style="text-align:center; padding:30px; color:#999;">
															<i class="fa fa-users" style="font-size:32px; display:block; margin-bottom:8px;"></i>
															No parents found
														</td>
													</tr>
												<?php endif; ?>
											</tbody>
										</table>
									</div>

									<!-- Pagination -->
									<?php if ($totalParentPages > 1): ?>
										<div class="pagination">
											<?php if ($page > 1): ?>
												<a href="?action=parents&page=<?= $page - 1 ?>"><i class="fa fa-chevron-left"></i></a>
											<?php else: ?>
												<span class="disabled"><i class="fa fa-chevron-left"></i></span>
											<?php endif; ?>

											<?php for ($i = 1; $i <= $totalParentPages; $i++): ?>
												<a href="?action=parents&page=<?= $i ?>" class="<?= ($i == $page) ? 'active' : '' ?>"><?= $i ?></a>
											<?php endfor; ?>

											<?php if ($page < $totalParentPages): ?>
												<a href="?action=parents&page=<?= $page + 1 ?>"><i class="fa fa-chevron-right"></i></a>
											<?php else: ?>
												<span class="disabled"><i class="fa fa-chevron-right"></i></span>
											<?php endif; ?>
										</div>
									<?php endif; ?>

									<button type="submit" name="parentMAIL" class="btn-send">
										<i class="fa fa-paper-plane"></i> Send Email to Selected Parents
									</button>
								</form>

								<script>
									function toggleAllParents() {
										var checkbox = document.getElementById('selectAllParents');
										var checkboxes = document.querySelectorAll('.parent-checkbox:not(:disabled)');
										checkboxes.forEach(function(cb) {
											cb.checked = checkbox.checked;
										});
									}

									document.querySelectorAll('.parent-checkbox').forEach(function(cb) {
										cb.addEventListener('change', function() {
											var all = document.querySelectorAll('.parent-checkbox:not(:disabled)');
											var checked = document.querySelectorAll('.parent-checkbox:checked');
											document.getElementById('selectAllParents').checked = all.length === checked.length;
										});
									});
								</script>

							<?php else: ?>
								<!-- STAFF TAB -->
								<form method="POST" action="" id="staffEmailForm">
									<div class="form-group">
										<label><i class="fa fa-tag"></i> Subject <span class="required">*</span></label>
										<input type="text" name="sSubject" placeholder="Enter email subject..." value="<?= htmlspecialchars($_POST['sSubject'] ?? '') ?>" required>
									</div>

									<div class="form-group">
										<label><i class="fa fa-pencil"></i> Message <span class="required">*</span></label>
										<textarea name="sMessage" placeholder="Type your message here..." required><?= htmlspecialchars($_POST['sMessage'] ?? '') ?></textarea>
									</div>

									<div class="recipient-info">
										<span class="count"><i class="fa fa-user-md"></i> <strong><?= $totalStaff ?></strong> staff member(s) found</span>
										<button type="button" class="btn-select-all" onclick="toggleAllStaff()">
											<i class="fa fa-check-square-o"></i> Select All
										</button>
									</div>

									<div class="table-wrapper">
										<table class="table" id="staffTable">
											<thead>
												<tr>
													<th class="checkbox-cell"><input type="checkbox" id="selectAllStaff" onchange="toggleAllStaff()"></th>
													<th>Staff ID</th>
													<th>Name</th>
													<th>Phone</th>
													<th>Email</th>
												</tr>
											</thead>
											<tbody>
												<?php if (!empty($staffList)): ?>
													<?php foreach ($staffList as $staff): ?>
														<tr>
															<td class="checkbox-cell">
																<input type="checkbox" name="sendMAILToStaff[]" value="<?= htmlspecialchars($staff['email']) ?>" class="staff-checkbox" <?= !empty($staff['email']) ? '' : 'disabled' ?>>
															</td>
															<td><strong><?= htmlspecialchars($staff['staff_id']) ?></strong></td>
															<td class="parent-name"><?= htmlspecialchars($staff['first_name'] . ' ' . $staff['last_name']) ?></td>
															<td><?= htmlspecialchars($staff['phone'] ?? 'N/A') ?></td>
															<td class="parent-email"><?= htmlspecialchars($staff['email'] ?? 'N/A') ?></td>
														</tr>
													<?php endforeach; ?>
												<?php else: ?>
													<tr>
														<td colspan="5" style="text-align:center; padding:30px; color:#999;">
															<i class="fa fa-user-md" style="font-size:32px; display:block; margin-bottom:8px;"></i>
															No staff members found
														</td>
													</tr>
												<?php endif; ?>
											</tbody>
										</table>
									</div>

									<!-- Staff Pagination -->
									<?php if ($totalStaffPages > 1): ?>
										<div class="pagination">
											<?php if ($staffPage > 1): ?>
												<a href="?action=staff&staff_page=<?= $staffPage - 1 ?>"><i class="fa fa-chevron-left"></i></a>
											<?php else: ?>
												<span class="disabled"><i class="fa fa-chevron-left"></i></span>
											<?php endif; ?>

											<?php for ($i = 1; $i <= $totalStaffPages; $i++): ?>
												<a href="?action=staff&staff_page=<?= $i ?>" class="<?= ($i == $staffPage) ? 'active' : '' ?>"><?= $i ?></a>
											<?php endfor; ?>

											<?php if ($staffPage < $totalStaffPages): ?>
												<a href="?action=staff&staff_page=<?= $staffPage + 1 ?>"><i class="fa fa-chevron-right"></i></a>
											<?php else: ?>
												<span class="disabled"><i class="fa fa-chevron-right"></i></span>
											<?php endif; ?>
										</div>
									<?php endif; ?>

									<button type="submit" name="staffEMAIL" class="btn-send">
										<i class="fa fa-paper-plane"></i> Send Email to Selected Staff
									</button>
								</form>

								<script>
									function toggleAllStaff() {
										var checkbox = document.getElementById('selectAllStaff');
										var checkboxes = document.querySelectorAll('.staff-checkbox:not(:disabled)');
										checkboxes.forEach(function(cb) {
											cb.checked = checkbox.checked;
										});
									}

									document.querySelectorAll('.staff-checkbox').forEach(function(cb) {
										cb.addEventListener('change', function() {
											var all = document.querySelectorAll('.staff-checkbox:not(:disabled)');
											var checked = document.querySelectorAll('.staff-checkbox:checked');
											document.getElementById('selectAllStaff').checked = all.length === checked.length;
										});
									});
								</script>

							<?php endif; ?>
						</div>
					</div>
				</div>
			</div>
			<?php include('inc.footer.php'); ?>
		</div>
	</div>

	<?php include('inc.js.php'); ?>
</body>

</html>