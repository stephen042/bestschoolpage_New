<?php

/**
 * ============================================================================
 * MANAGE ASSESSMENT - MODERN REDESIGN
 * ============================================================================
 * Description: Manage staff assessments with CRUD operations
 * Version: 4.0 (Fully Mobile Responsive)
 * ============================================================================
 */

include('../config.php');
include('inc.session-create.php');

$PageTitle = "Manage Assessment";
$FileName = 'staff_assessment.php';

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
// ADD NEW ASSESSMENT
// ============================================================================
if (isset($_POST['addnewrecord'])) {
	$validate->addRule($_POST['assessment'] ?? '', '', 'Assessment', true);
	$validate->addRule($_POST['mark'] ?? '', 'num', 'Mark', true);
	$validate->addRule($_POST['min_mark'] ?? '', 'num', 'Min Mark', true);

	if ($validate->validate() && count($stat) == 0) {
		$iLastId = db_get_val("SELECT id FROM staff_assessment ORDER BY id DESC") + 1;
		$randomId = randomFix(15) . '-' . $iLastId;

		$aryData = [
			'assessment' => trim($_POST['assessment']),
			'min_mark' => floatval($_POST['min_mark']),
			'mark' => floatval($_POST['mark']),
			'create_by_userid' => $create_by_userid,
			'create_by_usertype' => $create_by_usertype,
			'randomid' => $randomId,
		];

		$flgIn = db_insert("staff_assessment", $aryData);

		if ($flgIn) {
			$_SESSION['success'] = "Assessment saved successfully.";
			redirect($FileName);
		} else {
			$stat['error'] = "Failed to save assessment. Please try again.";
		}
	} else {
		$stat['error'] = $validate->errors();
	}
}

// ============================================================================
// UPDATE ASSESSMENT
// ============================================================================
if (isset($_POST['updaterecord']) && !empty($_GET['randomid'])) {
	$validate->addRule($_POST['assessment'] ?? '', '', 'Assessment', true);
	$validate->addRule($_POST['mark'] ?? '', 'num', 'Mark', true);
	$validate->addRule($_POST['min_mark'] ?? '', 'num', 'Min Mark', true);

	if ($validate->validate() && count($stat) == 0) {
		$aryData = [
			'assessment' => trim($_POST['assessment']),
			'min_mark' => floatval($_POST['min_mark']),
			'mark' => floatval($_POST['mark']),
			'create_by_userid' => $create_by_userid,
			'create_by_usertype' => $create_by_usertype,
		];

		$flgIn = db_update("staff_assessment", $aryData, "randomid = ?", [$_GET['randomid']]);

		if ($flgIn !== false) {
			$_SESSION['success'] = "Assessment updated successfully.";
			redirect($FileName);
		} else {
			$stat['error'] = "Failed to update assessment. Please try again.";
		}
	} else {
		$stat['error'] = $validate->errors();
	}
}

// ============================================================================
// DELETE ASSESSMENT
// ============================================================================
if (isset($_GET['action']) && $_GET['action'] == 'delete_mas' && !empty($_GET['randomid'])) {
	$flgIn = db_delete("staff_assessment", "randomid = ?", [$_GET['randomid']]);
	if ($flgIn !== false) {
		$_SESSION['success'] = 'Assessment deleted successfully.';
	} else {
		$_SESSION['error'] = 'Failed to delete assessment.';
	}
	redirect($FileName);
}

// ============================================================================
// GET ASSESSMENTS
// ============================================================================
$assessments = db_get_rows(
	"SELECT * FROM staff_assessment WHERE create_by_userid = ? ORDER BY id DESC",
	[$create_by_userid]
);

// Get editing record if any
$editRecord = null;
if (!empty($_GET['randomid'])) {
	$editRecord = db_get_row(
		"SELECT * FROM staff_assessment WHERE randomid = ? AND create_by_userid = ?",
		[$_GET['randomid'], $create_by_userid]
	);
}
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
			max-width: 1000px;
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
        CARD - MOBILE FIRST
        ============================================================ */
		.card {
			background: #fff;
			border-radius: 16px;
			box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
			overflow: hidden;
			margin-bottom: 20px;
		}

		.card-header {
			padding: 14px 18px;
			background: linear-gradient(135deg, #1B3058, #2a4780);
			color: white;
			display: flex;
			align-items: center;
			gap: 10px;
		}

		.card-header i {
			font-size: 18px;
		}

		.card-header h3 {
			margin: 0;
			font-size: 16px;
			font-weight: 600;
		}

		.card-body {
			padding: 16px;
		}

		/* ============================================================
        FORM - MOBILE FIRST
        ============================================================ */
		.form-grid {
			display: grid;
			grid-template-columns: 1fr;
			gap: 14px;
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

		.form-group label .required {
			color: #dc3545;
		}

		.form-group input[type="text"],
		.form-group input[type="number"] {
			width: 100%;
			padding: 10px 14px;
			border: 2px solid #e0e0e0;
			border-radius: 10px;
			font-size: 14px;
			transition: all 0.2s;
			background: #fafafa;
		}

		.form-group input[type="text"]:focus,
		.form-group input[type="number"]:focus {
			outline: none;
			border-color: #1B3058;
			background: #fff;
			box-shadow: 0 0 0 3px rgba(27, 48, 88, 0.1);
		}

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

		.btn-sm {
			padding: 6px 12px;
			font-size: 12px;
			min-height: 32px;
		}

		.btn-block {
			width: 100%;
			justify-content: center;
		}

		.form-actions {
			display: flex;
			flex-direction: column;
			gap: 10px;
			margin-top: 10px;
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
			min-width: 400px;
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

		.table .action-cell {
			white-space: nowrap;
			display: flex;
			gap: 6px;
			flex-wrap: wrap;
		}

		.table .action-cell .btn {
			min-height: 30px;
			padding: 4px 10px;
			font-size: 12px;
		}

		.table .edit-input {
			width: 100%;
			padding: 6px 10px;
			border: 2px solid #1B3058;
			border-radius: 6px;
			font-size: 13px;
			background: #fff;
		}

		.table .edit-input:focus {
			outline: none;
			box-shadow: 0 0 0 3px rgba(27, 48, 88, 0.1);
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
			.assessment-container {
				padding: 25px;
			}

			.page-header h2 {
				font-size: 28px;
			}

			.form-grid {
				grid-template-columns: 1fr 1fr;
				gap: 18px;
			}

			.form-group.full-width {
				grid-column: 1 / -1;
			}

			.form-actions {
				flex-direction: row;
				justify-content: flex-end;
			}

			.form-actions .btn {
				width: auto;
			}

			.card-body {
				padding: 24px;
			}

			.table {
				min-width: auto;
			}

			.table th,
			.table td {
				padding: 12px 14px;
			}

			.table .action-cell {
				flex-wrap: nowrap;
			}
		}

		/* ============================================================
        RESPONSIVE - DESKTOP (1024px+)
        ============================================================ */
		@media (min-width: 1024px) {
			.assessment-container {
				padding: 30px;
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
				font-size: 20px;
			}

			.page-header p {
				font-size: 12px;
			}

			.card-body {
				padding: 12px;
			}

			.table {
				font-size: 11px;
				min-width: 350px;
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

			.table .edit-input {
				font-size: 11px;
				padding: 4px 6px;
			}

			.table .action-cell .btn {
				font-size: 10px;
				padding: 3px 8px;
				min-height: 26px;
			}

			.btn {
				font-size: 13px;
				padding: 8px 16px;
				min-height: 40px;
			}

			.form-group input[type="text"],
			.form-group input[type="number"] {
				font-size: 13px;
				padding: 8px 12px;
			}
		}

		/* ============================================================
        PRINT STYLES
        ============================================================ */
		@media print {

			.btn,
			.form-actions,
			.no-print {
				display: none !important;
			}

			.card {
				box-shadow: none !important;
				border: 1px solid #ddd;
			}

			.card-header {
				-webkit-print-color-adjust: exact;
				print-color-adjust: exact;
			}

			body {
				background: white;
			}

			.assessment-container {
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
				<div class="assessment-container">

					<!-- Page Header -->
					<div class="page-header">
						<h2><i class="fa fa-tasks"></i> <?= htmlspecialchars($PageTitle) ?></h2>
						<p>Create and manage staff assessment types with their marks</p>
					</div>

					<?= showMessage($stat) ?>

					<!-- Add/Edit Form -->
					<div class="card">
						<div class="card-header">
							<i class="fa fa-<?= (!empty($editRecord)) ? 'pencil' : 'plus-circle' ?>"></i>
							<h3><?= (!empty($editRecord)) ? 'Edit Assessment' : 'Add New Assessment' ?></h3>
						</div>
						<div class="card-body">
							<form method="POST" action="<?= (!empty($editRecord)) ? '?randomid=' . urlencode($editRecord['randomid']) : '' ?>" id="assessmentForm">
								<div class="form-grid">
									<div class="form-group full-width">
										<label>Assessment Name <span class="required">*</span></label>
										<input type="text" name="assessment" placeholder="Enter assessment name..."
											value="<?= htmlspecialchars($editRecord['assessment'] ?? $_POST['assessment'] ?? '') ?>" required>
									</div>
									<div class="form-group">
										<label>Mark (Max) <span class="required">*</span></label>
										<input type="number" name="mark" placeholder="Maximum mark..." step="0.01" min="0"
											value="<?= htmlspecialchars($editRecord['mark'] ?? $_POST['mark'] ?? '') ?>" required>
									</div>
									<div class="form-group">
										<label>Minimum Mark <span class="required">*</span></label>
										<input type="number" name="min_mark" placeholder="Minimum mark..." step="0.01" min="0"
											value="<?= htmlspecialchars($editRecord['min_mark'] ?? $_POST['min_mark'] ?? '') ?>" required>
									</div>
								</div>
								<div class="form-actions">
									<?php if (!empty($editRecord)): ?>
										<button type="submit" name="updaterecord" class="btn btn-primary">
											<i class="fa fa-save"></i> Update Assessment
										</button>
										<a href="<?= $FileName ?>" class="btn btn-outline">
											<i class="fa fa-times"></i> Cancel
										</a>
									<?php else: ?>
										<button type="submit" name="addnewrecord" class="btn btn-primary">
											<i class="fa fa-plus"></i> Save Assessment
										</button>
									<?php endif; ?>
								</div>
							</form>
						</div>
					</div>

					<!-- Assessments List -->
					<div class="card">
						<div class="card-header">
							<i class="fa fa-list"></i>
							<h3>Assessment List</h3>
							<span style="margin-left: auto; background: rgba(255,255,255,0.2); padding: 2px 14px; border-radius: 20px; font-size: 12px;">
								<?= count($assessments) ?>
							</span>
						</div>
						<div class="card-body">
							<?php if (!empty($assessments)): ?>
								<div class="table-wrapper">
									<table class="table" id="assessmentTable">
										<thead>
											<tr>
												<th style="width:40px;">#</th>
												<th>Assessment</th>
												<th>Max Mark</th>
												<th>Min Mark</th>
												<th style="width:140px;">Actions</th>
											</tr>
										</thead>
										<tbody>
											<?php $i = 0;
											foreach ($assessments as $assessment): $i++; ?>
												<tr>
													<td><?= $i ?></td>
													<td><strong><?= htmlspecialchars($assessment['assessment']) ?></strong></td>
													<td><?= number_format($assessment['mark'], 2) ?></td>
													<td><?= number_format($assessment['min_mark'], 2) ?></td>
													<td>
														<div class="action-cell">
															<a href="?action=manage_trait&randomid=<?= urlencode($assessment['randomid']) ?>" class="btn btn-primary btn-sm">
																<i class="fa fa-pencil"></i>
															</a>
															<a href="javascript:void(0)" onclick="confirmDelete('<?= $FileName ?>?action=delete_mas&randomid=<?= urlencode($assessment['randomid']) ?>')" class="btn btn-danger btn-sm">
																<i class="fa fa-times"></i>
															</a>
														</div>
													</td>
												</tr>
											<?php endforeach; ?>
										</tbody>
									</table>
								</div>
							<?php else: ?>
								<div class="empty-state">
									<i class="fa fa-inbox"></i>
									<h4>No Assessments Found</h4>
									<p>Add your first assessment using the form above.</p>
								</div>
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
		function confirmDelete(url) {
			if (confirm('Are you sure you want to delete this assessment? This action cannot be undone.')) {
				window.location.href = url;
			}
		}

		// Auto-calculate min_mark when mark is entered
		document.addEventListener('DOMContentLoaded', function() {
			var markInput = document.querySelector('input[name="mark"]');
			var minMarkInput = document.querySelector('input[name="min_mark"]');

			if (markInput && minMarkInput) {
				markInput.addEventListener('input', function() {
					var val = parseFloat(this.value);
					if (!isNaN(val) && val > 0) {
						// Set min_mark to a percentage of max (e.g., 40%)
						var minVal = Math.round(val * 0.4);
						minMarkInput.value = minVal;
					}
				});
			}
		});
	</script>
</body>

</html>