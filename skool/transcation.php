<?php

/**
 * ============================================================================
 * TRANSACTION HISTORY - MODERN REDESIGN
 * ============================================================================
 * Description: View transaction history (deposits and withdrawals)
 * Version: 4.0 (Fully Mobile Responsive)
 * ============================================================================
 */

include('../config.php');
include('inc.session-create.php');

$PageTitle = "Transaction History";
$FileName = 'transcation.php';

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
// GET TRANSACTIONS WITH PAGINATION
// ============================================================================
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 25;
$offset = ($page - 1) * $perPage;

// Count total transactions
$totalTransactions = db_get_val(
	"SELECT COUNT(*) FROM transcation WHERE userid = ?",
	[$sessionUserId]
);
$totalTransactions = (int)$totalTransactions;
$totalPages = ceil($totalTransactions / $perPage);

// Get transactions with pagination
$transactions = db_get_rows(
	"SELECT * FROM transcation 
     WHERE userid = ? 
     ORDER BY id DESC 
     LIMIT ? OFFSET ?",
	[$sessionUserId, $perPage, $offset]
);

// Get wallet balance
$walletBalance = db_get_val("SELECT walletamount FROM school_register WHERE id = ?", [$sessionUserId]);
$walletBalance = $walletBalance !== false ? floatval($walletBalance) : 0;
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

		.transaction-container {
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
        SUMMARY CARDS - MOBILE FIRST
        ============================================================ */
		.summary-grid {
			display: grid;
			grid-template-columns: 1fr 1fr;
			gap: 12px;
			margin-bottom: 25px;
		}

		.summary-card {
			background: #fff;
			border-radius: 16px;
			padding: 16px;
			text-align: center;
			box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
			border-left: 4px solid #1B3058;
		}

		.summary-card .number {
			font-size: 24px;
			font-weight: 700;
			color: #1B3058;
			line-height: 1.2;
		}

		.summary-card .label {
			font-size: 11px;
			color: #888;
			text-transform: uppercase;
			font-weight: 600;
			margin-top: 4px;
		}

		.summary-card.deposit {
			border-color: #28a745;
		}

		.summary-card.deposit .number {
			color: #28a745;
		}

		.summary-card.withdrawal {
			border-color: #dc3545;
		}

		.summary-card.withdrawal .number {
			color: #dc3545;
		}

		.summary-card.balance {
			border-color: #17a2b8;
		}

		.summary-card.balance .number {
			color: #17a2b8;
		}

		/* ============================================================
        CARD - MOBILE FIRST
        ============================================================ */
		.card {
			background: #fff;
			border-radius: 16px;
			box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
			overflow: hidden;
		}

		.card-header {
			padding: 16px 20px;
			background: linear-gradient(135deg, #1B3058, #2a4780);
			color: white;
			display: flex;
			justify-content: space-between;
			align-items: center;
			flex-wrap: wrap;
			gap: 8px;
		}

		.card-header h3 {
			margin: 0;
			font-size: 16px;
			font-weight: 600;
		}

		.card-header h3 i {
			margin-right: 8px;
		}

		.card-header .count-badge {
			background: rgba(255, 255, 255, 0.2);
			padding: 2px 14px;
			border-radius: 20px;
			font-size: 12px;
			font-weight: 400;
		}

		.card-body {
			padding: 16px;
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
			min-width: 500px;
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
			z-index: 2;
		}

		.table td {
			font-size: 13px;
		}

		.table tr:hover td {
			background: #f8f9ff;
		}

		.table .amount-cell {
			font-weight: 700;
		}

		.table .amount-cell.positive {
			color: #28a745;
		}

		.table .amount-cell.negative {
			color: #dc3545;
		}

		.table .transaction-date {
			color: #666;
			font-size: 12px;
			white-space: nowrap;
		}

		.type-badge {
			display: inline-block;
			padding: 3px 12px;
			border-radius: 20px;
			font-size: 11px;
			font-weight: 600;
		}

		.type-badge.deposit {
			background: #d4edda;
			color: #155724;
		}

		.type-badge.withdrawal {
			background: #f8d7da;
			color: #721c24;
		}

		.purpose-text {
			font-size: 12px;
			color: #555;
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
        RESPONSIVE - TABLET (768px+)
        ============================================================ */
		@media (min-width: 768px) {
			.transaction-container {
				padding: 25px;
			}

			.page-header h2 {
				font-size: 28px;
			}

			.summary-grid {
				grid-template-columns: repeat(4, 1fr);
				gap: 16px;
			}

			.summary-card .number {
				font-size: 28px;
			}

			.card-body {
				padding: 20px;
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
			.transaction-container {
				padding: 30px;
			}

			.summary-card .number {
				font-size: 32px;
			}
		}

		/* ============================================================
        RESPONSIVE - SMALL MOBILE (480px-)
        ============================================================ */
		@media (max-width: 480px) {
			.transaction-container {
				padding: 10px;
			}

			.page-header h2 {
				font-size: 20px;
			}

			.page-header p {
				font-size: 12px;
			}

			.summary-grid {
				grid-template-columns: 1fr 1fr;
				gap: 8px;
			}

			.summary-card {
				padding: 12px 8px;
			}

			.summary-card .number {
				font-size: 20px;
			}

			.summary-card .label {
				font-size: 9px;
			}

			.card-header h3 {
				font-size: 14px;
			}

			.card-header .count-badge {
				font-size: 10px;
				padding: 1px 10px;
			}

			.card-body {
				padding: 10px 6px;
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

			.table .transaction-date {
				font-size: 10px;
			}

			.pagination a,
			.pagination span {
				padding: 4px 10px;
				font-size: 11px;
				min-height: 30px;
				min-width: 30px;
			}
		}

		/* ============================================================
        PRINT STYLES
        ============================================================ */
		@media print {

			.pagination,
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

			.summary-card {
				border: 1px solid #ddd;
			}

			body {
				background: white;
			}

			.transaction-container {
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
				<div class="transaction-container">

					<!-- Page Header -->
					<div class="page-header">
						<h2><i class="fa fa-exchange"></i> <?= htmlspecialchars($PageTitle) ?></h2>
						<p>View your complete transaction history</p>
					</div>

					<?= showMessage($stat) ?>

					<!-- Summary Cards -->
					<?php
					// Calculate totals
					$totalDeposits = db_get_val(
						"SELECT SUM(amount) FROM transcation WHERE userid = ? AND type = '1'",
						[$sessionUserId]
					);
					$totalWithdrawals = db_get_val(
						"SELECT SUM(amount) FROM transcation WHERE userid = ? AND type = '2'",
						[$sessionUserId]
					);
					$totalDeposits = $totalDeposits !== false ? floatval($totalDeposits) : 0;
					$totalWithdrawals = $totalWithdrawals !== false ? floatval($totalWithdrawals) : 0;
					?>
					<div class="summary-grid">
						<div class="summary-card balance">
							<div class="number">₦<?= number_format($walletBalance, 2) ?></div>
							<div class="label">Current Balance</div>
						</div>
						<div class="summary-card deposit">
							<div class="number">₦<?= number_format($totalDeposits, 2) ?></div>
							<div class="label">Total Deposits</div>
						</div>
						<div class="summary-card withdrawal">
							<div class="number">₦<?= number_format($totalWithdrawals, 2) ?></div>
							<div class="label">Total Withdrawals</div>
						</div>
						<div class="summary-card" style="border-color: #6c757d;">
							<div class="number" style="color: #6c757d;"><?= $totalTransactions ?></div>
							<div class="label">Total Transactions</div>
						</div>
					</div>

					<!-- Transactions Table -->
					<div class="card">
						<div class="card-header">
							<h3><i class="fa fa-list"></i> Transaction History</h3>
							<span class="count-badge"><?= $totalTransactions ?> transactions</span>
						</div>
						<div class="card-body">
							<?php if (!empty($transactions)): ?>
								<div class="table-wrapper">
									<table class="table" id="transactionTable">
										<thead>
											<tr>
												<th>#</th>
												<th>Amount</th>
												<th>Type</th>
												<th>Purpose</th>
												<th>Date</th>
											</tr>
										</thead>
										<tbody>
											<?php $i = $offset;
											foreach ($transactions as $transaction): $i++; ?>
												<tr>
													<td><?= $i ?></td>
													<td class="amount-cell <?= $transaction['type'] == '1' ? 'positive' : 'negative' ?>">
														<?= $transaction['type'] == '1' ? '+' : '-' ?>₦<?= number_format($transaction['amount'], 2) ?>
													</td>
													<td>
														<span class="type-badge <?= $transaction['type'] == '1' ? 'deposit' : 'withdrawal' ?>">
															<?= $transaction['type'] == '1' ? 'Deposit' : 'Withdrawal' ?>
														</span>
													</td>
													<td class="purpose-text">
														<?php
														$purposeText = 'N/A';
														if ($transaction['purpose'] == '1') {
															$purposeText = 'Payment Made in Admission';
														} elseif ($transaction['purpose'] == '2') {
															$purposeText = 'Withdrawal to Bank';
														} elseif ($transaction['purpose'] == '3') {
															$purposeText = 'SMS Purchase';
														} elseif ($transaction['purpose'] == '4') {
															$purposeText = 'Fee Payment';
														}
														echo htmlspecialchars($purposeText);
														?>
													</td>
													<td class="transaction-date">
														<?= date('d M Y, h:i A', strtotime($transaction['create_at'])) ?>
													</td>
												</tr>
											<?php endforeach; ?>
										</tbody>
									</table>
								</div>

								<!-- Pagination -->
								<?php if ($totalPages > 1): ?>
									<div class="pagination">
										<?php if ($page > 1): ?>
											<a href="?page=<?= $page - 1 ?>"><i class="fa fa-chevron-left"></i></a>
										<?php else: ?>
											<span class="disabled"><i class="fa fa-chevron-left"></i></span>
										<?php endif; ?>

										<?php for ($i = 1; $i <= $totalPages; $i++): ?>
											<a href="?page=<?= $i ?>" class="<?= ($i == $page) ? 'active' : '' ?>"><?= $i ?></a>
										<?php endfor; ?>

										<?php if ($page < $totalPages): ?>
											<a href="?page=<?= $page + 1 ?>"><i class="fa fa-chevron-right"></i></a>
										<?php else: ?>
											<span class="disabled"><i class="fa fa-chevron-right"></i></span>
										<?php endif; ?>

										<span class="info">Showing <?= $offset + 1 ?>-<?= min($offset + $perPage, $totalTransactions) ?> of <?= $totalTransactions ?></span>
									</div>
								<?php endif; ?>

							<?php else: ?>
								<div class="empty-state">
									<i class="fa fa-inbox"></i>
									<h4>No Transactions Found</h4>
									<p>You haven't made any transactions yet.</p>
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
</body>

</html>