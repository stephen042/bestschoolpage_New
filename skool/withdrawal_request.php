<?php

/**
 * ============================================================================
 * WITHDRAWAL REQUEST - MODERN REDESIGN
 * ============================================================================
 * Description: Request withdrawal of funds from wallet
 * Version: 4.0 (Fully Mobile Responsive)
 * ============================================================================
 */

include('../config.php');
include('inc.session-create.php');

$PageTitle = "Withdrawal Request";
$FileName = 'withdrawal_request.php';

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
// GET WALLET BALANCE
// ============================================================================
$userWallet = db_get_row("SELECT walletamount FROM school_register WHERE id = ?", [$sessionUserId]);
$walletBalance = $userWallet['walletamount'] ?? 0;

// ============================================================================
// PROCESS NEW WITHDRAWAL REQUEST
// ============================================================================
if (isset($_POST['addnewrecore'])) {
	$amount = floatval($_POST['amount'] ?? 0);

	$validate->addRule($amount, 'num', 'Amount', true);

	if ($validate->validate() && count($stat) == 0) {
		// Check if there's already a pending request
		$pendingRequest = db_get_val(
			"SELECT id FROM withdrawal_request WHERE request_by = ? AND status = '0'",
			[$sessionUserId]
		);

		if (!$pendingRequest) {
			if ($amount <= $walletBalance && $amount > 0) {
				$iLastId = db_get_val("SELECT id FROM withdrawal_request ORDER BY id DESC") + 1;
				$iRandomId = randomFix(15) . '-' . $iLastId;

				$aryData = [
					'request_by_usertype' => $_SESSION['usertype'] ?? '',
					'request_by' => $sessionUserId,
					'request_for_usertype' => $_SESSION['usertype'] ?? '',
					'request_for' => $sessionUserId,
					'amount' => $amount,
					'create_by_usertype' => $create_by_usertype,
					'create_by_userid' => $create_by_userid,
					'randomid' => $iRandomId,
					'status' => 0,
					'create_at' => date("Y-m-d H:i:s"),
					'update_at' => date("Y-m-d H:i:s"),
				];

				$flgIn = db_insert("withdrawal_request", $aryData);
				if ($flgIn) {
					$_SESSION['success'] = "Withdrawal request submitted successfully!";
					redirect($FileName);
				} else {
					$stat['error'] = "Failed to submit withdrawal request. Please try again.";
				}
			} else {
				$stat['error'] = "Insufficient balance or invalid amount. You have ₦" . number_format($walletBalance, 2) . " available.";
			}
		} else {
			$stat['error'] = "You already have a pending withdrawal request. Please wait for it to be processed.";
		}
	} else {
		$stat['error'] = $validate->errors();
	}
}

// ============================================================================
// DELETE WITHDRAWAL REQUEST
// ============================================================================
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['randomid'])) {
	$flgIn = db_delete("withdrawal_request", "randomid = ?", [$_GET['randomid']]);
	if ($flgIn !== false) {
		$_SESSION['success'] = 'Withdrawal request deleted successfully.';
	} else {
		$_SESSION['error'] = 'Failed to delete withdrawal request.';
	}
	redirect($FileName);
}

// ============================================================================
// GET WITHDRAWAL REQUESTS
// ============================================================================
$withdrawals = db_get_rows(
	"SELECT * FROM withdrawal_request WHERE request_by = ? ORDER BY id DESC",
	[$sessionUserId]
);

// Get current action
$action = $_GET['action'] ?? '';
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

		.withdrawal-container {
			max-width: 1000px;
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
        WALLET BALANCE CARD - MOBILE FIRST
        ============================================================ */
		.wallet-card {
			background: linear-gradient(135deg, #1B3058, #2a4780);
			color: white;
			border-radius: 20px;
			padding: 24px 20px;
			margin-bottom: 25px;
			display: flex;
			flex-direction: column;
			gap: 8px;
			align-items: center;
			text-align: center;
			box-shadow: 0 4px 20px rgba(27, 48, 88, 0.3);
		}

		.wallet-card .wallet-label {
			font-size: 14px;
			opacity: 0.8;
			font-weight: 400;
		}

		.wallet-card .wallet-balance {
			font-size: 36px;
			font-weight: 700;
		}

		.wallet-card .wallet-balance small {
			font-size: 18px;
			font-weight: 400;
			opacity: 0.8;
		}

		.wallet-card .wallet-hint {
			font-size: 13px;
			opacity: 0.7;
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
			padding: 14px 12px;
			text-align: center;
			font-weight: 600;
			font-size: 13px;
			color: #888;
			cursor: pointer;
			transition: all 0.3s;
			border: none;
			background: transparent;
			position: relative;
			min-height: 48px;
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

		.tab-btn .badge {
			display: inline-block;
			background: #dc3545;
			color: white;
			font-size: 10px;
			padding: 1px 8px;
			border-radius: 12px;
			margin-left: 4px;
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
			font-size: 14px;
			color: #333;
			margin-bottom: 6px;
		}

		.form-group label .required {
			color: #dc3545;
		}

		.form-group .input-group {
			position: relative;
		}

		.form-group .input-group .currency-symbol {
			position: absolute;
			left: 14px;
			top: 50%;
			transform: translateY(-50%);
			font-weight: 700;
			color: #1B3058;
			font-size: 18px;
		}

		.form-group input[type="text"],
		.form-group input[type="number"] {
			width: 100%;
			padding: 12px 14px 12px 38px;
			border: 2px solid #e0e0e0;
			border-radius: 12px;
			font-size: 16px;
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

		.form-group .input-help {
			font-size: 12px;
			color: #888;
			margin-top: 6px;
			display: block;
		}

		.form-group .input-help strong {
			color: #1B3058;
		}

		.btn-submit {
			background: linear-gradient(135deg, #28a745, #218838);
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

		.btn-submit:active {
			transform: scale(0.97);
		}

		.btn-submit:hover {
			background: linear-gradient(135deg, #218838, #1a6e2e);
			transform: translateY(-2px);
			box-shadow: 0 4px 15px rgba(40, 167, 69, 0.3);
		}

		.btn-submit:disabled {
			opacity: 0.6;
			cursor: not-allowed;
			transform: none;
		}

		.btn-danger {
			background: #dc3545;
			color: white;
			border: none;
			padding: 6px 14px;
			border-radius: 8px;
			font-size: 12px;
			font-weight: 600;
			cursor: pointer;
			transition: all 0.3s;
			touch-action: manipulation;
		}

		.btn-danger:active {
			transform: scale(0.95);
		}

		.btn-danger:hover {
			background: #c82333;
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
			min-width: 450px;
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

		.table .amount-cell {
			font-weight: 700;
			color: #1B3058;
		}

		.status-badge {
			display: inline-block;
			padding: 4px 14px;
			border-radius: 20px;
			font-size: 11px;
			font-weight: 600;
		}

		.status-badge.pending {
			background: #fff3cd;
			color: #856404;
		}

		.status-badge.approved {
			background: #d4edda;
			color: #155724;
		}

		.status-badge.completed {
			background: #cce5ff;
			color: #004085;
		}

		.status-badge.rejected {
			background: #f8d7da;
			color: #721c24;
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
			.withdrawal-container {
				padding: 25px;
			}

			.page-header h2 {
				font-size: 28px;
			}

			.wallet-card {
				flex-direction: row;
				justify-content: space-between;
				padding: 24px 30px;
				text-align: left;
			}

			.wallet-card .wallet-balance {
				font-size: 40px;
			}

			.tab-content {
				padding: 24px;
			}

			.btn-submit {
				width: auto;
				padding: 12px 50px;
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
			.withdrawal-container {
				padding: 30px;
			}

			.wallet-card .wallet-balance {
				font-size: 44px;
			}
		}

		/* ============================================================
        RESPONSIVE - SMALL MOBILE (480px-)
        ============================================================ */
		@media (max-width: 480px) {
			.withdrawal-container {
				padding: 10px;
			}

			.page-header h2 {
				font-size: 20px;
			}

			.page-header p {
				font-size: 12px;
			}

			.wallet-card {
				padding: 18px 16px;
				border-radius: 16px;
			}

			.wallet-card .wallet-balance {
				font-size: 28px;
			}

			.wallet-card .wallet-label {
				font-size: 12px;
			}

			.tab-btn {
				font-size: 11px;
				padding: 10px 8px;
				min-width: 60px;
			}

			.tab-btn i {
				font-size: 14px;
				margin-right: 4px;
			}

			.table {
				font-size: 11px;
				min-width: 380px;
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

			.form-group input[type="text"],
			.form-group input[type="number"] {
				font-size: 14px;
				padding: 10px 12px 10px 34px;
			}

			.form-group .input-group .currency-symbol {
				font-size: 16px;
				left: 12px;
			}

			.btn-submit {
				font-size: 13px;
				padding: 10px 20px;
				min-height: 42px;
			}
		}

		/* ============================================================
        PRINT STYLES
        ============================================================ */
		@media print {

			.btn-submit,
			.btn-danger,
			.tabs-header,
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

			.wallet-card {
				-webkit-print-color-adjust: exact;
				print-color-adjust: exact;
			}

			body {
				background: white;
			}

			.withdrawal-container {
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
				<div class="withdrawal-container">

					<!-- Page Header -->
					<div class="page-header">
						<h2><i class="fa fa-money"></i> <?= htmlspecialchars($PageTitle) ?></h2>
						<p>Request withdrawal of funds from your wallet</p>
					</div>

					<?= showMessage($stat) ?>

					<!-- Wallet Balance -->
					<div class="wallet-card">
						<div>
							<div class="wallet-label"><i class="fa fa-credit-card"></i> Available Balance</div>
							<div class="wallet-balance">₦<?= number_format($walletBalance, 2) ?></div>
						</div>
						<div class="wallet-hint">
							<i class="fa fa-info-circle"></i> Minimum withdrawal: ₦100
						</div>
					</div>

					<!-- Tabs -->
					<div class="tabs-container">
						<div class="tabs-header">
							<button class="tab-btn <?= ($action == '' || $action == 'verification_status') ? 'active' : '' ?>" onclick="window.location.href='?action=verification_status'">
								<i class="fa fa-list"></i> <span>History</span>
							</button>
							<button class="tab-btn <?= ($action == 'add') ? 'active' : '' ?>" onclick="window.location.href='?action=add'">
								<i class="fa fa-plus-circle"></i> <span>New Request</span>
							</button>
							<button class="tab-btn" style="flex:0.5; min-width:auto; background:#f0f4ff; color:#1B3058;">
								<i class="fa fa-clock-o"></i>
								<?php
								$pendingCount = db_get_val("SELECT COUNT(*) FROM withdrawal_request WHERE request_by = ? AND status = '0'", [$sessionUserId]);
								?>
								<span><?= $pendingCount ?></span>
							</button>
						</div>

						<div class="tab-content">
							<?php if ($action == 'add'): ?>
								<!-- NEW REQUEST FORM -->
								<form method="POST" action="" id="withdrawalForm">
									<div class="form-group">
										<label><i class="fa fa-money"></i> Amount to Withdraw <span class="required">*</span></label>
										<div class="input-group">
											<span class="currency-symbol">₦</span>
											<input type="number" name="amount" placeholder="0.00" min="100" step="100" required autofocus>
										</div>
										<span class="input-help">
											<i class="fa fa-info-circle"></i>
											Available balance: <strong>₦<?= number_format($walletBalance, 2) ?></strong> |
											Minimum withdrawal: <strong>₦100</strong>
										</span>
									</div>

									<?php if ($walletBalance < 100): ?>
										<div class="alert alert-info" style="margin-bottom:16px;">
											<i class="fa fa-info-circle"></i>
											<div>Your balance is below the minimum withdrawal amount (₦100).</div>
										</div>
									<?php endif; ?>

									<button type="submit" name="addnewrecore" class="btn-submit" <?= $walletBalance < 100 ? 'disabled' : '' ?>>
										<i class="fa fa-paper-plane"></i> Submit Withdrawal Request
									</button>
								</form>

							<?php else: ?>
								<!-- WITHDRAWAL HISTORY -->
								<div class="table-wrapper">
									<?php if (!empty($withdrawals)): ?>
										<table class="table" id="withdrawalTable">
											<thead>
												<tr>
													<th>#</th>
													<th>Amount</th>
													<th>Date</th>
													<th>Status</th>
													<th class="no-print">Action</th>
												</tr>
											</thead>
											<tbody>
												<?php $i = 0;
												foreach ($withdrawals as $withdrawal): $i++; ?>
													<tr>
														<td><?= $i ?></td>
														<td class="amount-cell">₦<?= number_format($withdrawal['amount'], 2) ?></td>
														<td><?= date('d M Y, h:i A', strtotime($withdrawal['create_at'])) ?></td>
														<td>
															<?php if ($withdrawal['status'] == '0'): ?>
																<span class="status-badge pending"><i class="fa fa-clock-o"></i> Pending</span>
															<?php elseif ($withdrawal['status'] == '1'): ?>
																<span class="status-badge approved"><i class="fa fa-check-circle"></i> Approved</span>
															<?php elseif ($withdrawal['status'] == '2'): ?>
																<span class="status-badge completed"><i class="fa fa-check-circle"></i> Completed</span>
															<?php else: ?>
																<span class="status-badge rejected"><i class="fa fa-times-circle"></i> Rejected</span>
															<?php endif; ?>
														</td>
														<td class="no-print">
															<?php if ($withdrawal['status'] == '0'): ?>
																<a href="?action=delete&randomid=<?= urlencode($withdrawal['randomid']) ?>" class="btn-danger" onclick="return confirm('Are you sure you want to cancel this withdrawal request?')">
																	<i class="fa fa-times"></i> Cancel
																</a>
															<?php else: ?>
																<span style="color:#999; font-size:12px;">N/A</span>
															<?php endif; ?>
														</td>
													</tr>
												<?php endforeach; ?>
											</tbody>
										</table>
									<?php else: ?>
										<div class="empty-state">
											<i class="fa fa-inbox"></i>
											<h4>No Withdrawal Requests</h4>
											<p>You haven't made any withdrawal requests yet.</p>
											<a href="?action=add" class="btn-submit" style="display:inline-flex; width:auto; margin-top:10px; padding:10px 24px;">
												<i class="fa fa-plus"></i> Make a Request
											</a>
										</div>
									<?php endif; ?>
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
		// Auto-format amount input
		document.querySelector('input[name="amount"]')?.addEventListener('blur', function() {
			var val = parseFloat(this.value);
			if (!isNaN(val) && val > 0) {
				this.value = Math.round(val / 100) * 100;
			}
		});

		// Prevent form submission if amount exceeds balance
		document.getElementById('withdrawalForm')?.addEventListener('submit', function(e) {
			var amount = parseFloat(document.querySelector('input[name="amount"]').value);
			var balance = <?= $walletBalance ?>;

			if (amount > balance) {
				e.preventDefault();
				alert('Insufficient balance. You have ₦' + balance.toFixed(2) + ' available.');
			}
		});
	</script>
</body>

</html>