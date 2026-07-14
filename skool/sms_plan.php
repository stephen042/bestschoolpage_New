<?php

/**
 * ============================================================================
 * SMS PLAN - MODERN REDESIGN
 * ============================================================================
 * Description: View and purchase SMS plans for sending notifications
 * Version: 4.0 (Fully Mobile Responsive)
 * ============================================================================
 */

include('../config.php');
include('inc.session-create.php');

$PageTitle = "SMS Plan";
$FileName = 'sms_plan.php';

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

if (isset($_SESSION['error']) && $_SESSION['error'] != "") {
	$stat['error'] = $_SESSION['error'];
	unset($_SESSION['error']);
}

// ============================================================================
// BUY SMS PLAN
// ============================================================================
if (isset($_POST['buy_sms_plan'])) {
	$planId = (int)($_POST['plan_id'] ?? 0);
	$iSmsPlan = db_get_row("SELECT * FROM sms_plan WHERE id = ?", [$planId]);

	if ($iSmsPlan) {
		$success_token = randomFix(15);
		$cancel_token = randomFix(15);

		$aryData = [
			'plan_id' => $iSmsPlan['id'],
			'plan_name' => $iSmsPlan['title'],
			'price' => $iSmsPlan['price'],
			'no_of_sms' => $iSmsPlan['no_of_sms'],
			'exp_date' => $iSmsPlan['exp_date'],
			'status' => 0,
			'create_at' => date('Y-m-d H:i:s'),
			'usertype' => $_SESSION['usertype'] ?? '',
			'userid' => $_SESSION['userid'] ?? 0,
			'create_by_userid' => $create_by_userid,
			'create_by_usertype' => $create_by_usertype,
			'success_token' => $success_token,
			'cancel_token' => $cancel_token,
		];

		$flgIn = db_insert("sms_payment", $aryData);
		if ($flgIn) {
			$_SESSION['payment_id'] = $flgIn;
			redirect(SKOOL_URL . 'sms_voguepay.php');
		} else {
			$stat['error'] = "Failed to initiate payment. Please try again.";
		}
	}
}

// ============================================================================
// GET PLANS
// ============================================================================
$plans = db_get_rows("SELECT * FROM sms_plan WHERE status = '1' ORDER BY price ASC");
$myPlans = db_get_rows(
	"SELECT * FROM sms_payment 
     WHERE create_by_userid = ? AND status = '1' 
     ORDER BY id DESC",
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

		.plan-container {
			max-width: 1200px;
			margin: 0 auto;
			padding: 15px;
		}

		/* ============================================================
        PAGE HEADER - MOBILE FIRST
        ============================================================ */
		.page-header {
			margin-bottom: 25px;
			display: flex;
			flex-direction: column;
			gap: 10px;
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
			margin-top: 2px;
			font-size: 14px;
		}

		.page-header .header-actions {
			display: flex;
			gap: 10px;
			flex-wrap: wrap;
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

		/* ============================================================
        PLAN CARDS - MOBILE FIRST
        ============================================================ */
		.plans-grid {
			display: grid;
			grid-template-columns: 1fr;
			gap: 20px;
			margin-bottom: 30px;
		}

		.plan-card {
			background: #fff;
			border-radius: 20px;
			box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
			overflow: hidden;
			transition: all 0.3s;
			display: flex;
			flex-direction: column;
		}

		.plan-card:hover {
			transform: translateY(-5px);
			box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
		}

		.plan-card:active {
			transform: scale(0.98);
		}

		.plan-card .plan-header {
			padding: 24px 20px 16px;
			text-align: center;
			background: linear-gradient(135deg, #1B3058, #2a4780);
			color: white;
		}

		.plan-card .plan-header .plan-icon {
			font-size: 36px;
			margin-bottom: 8px;
			display: block;
		}

		.plan-card .plan-header h3 {
			margin: 0;
			font-size: 20px;
			font-weight: 700;
		}

		.plan-card .plan-header .plan-subtitle {
			font-size: 13px;
			opacity: 0.8;
			margin-top: 4px;
		}

		.plan-card .plan-body {
			padding: 20px;
			flex: 1;
			display: flex;
			flex-direction: column;
			align-items: center;
		}

		.plan-card .plan-price {
			font-size: 42px;
			font-weight: 700;
			color: #1B3058;
			margin-bottom: 4px;
		}

		.plan-card .plan-price small {
			font-size: 16px;
			font-weight: 400;
			color: #999;
		}

		.plan-card .plan-features {
			list-style: none;
			padding: 0;
			margin: 16px 0;
			width: 100%;
		}

		.plan-card .plan-features li {
			padding: 10px 12px;
			border-bottom: 1px solid #f0f0f0;
			display: flex;
			align-items: center;
			justify-content: center;
			gap: 8px;
			font-size: 14px;
			color: #555;
		}

		.plan-card .plan-features li:last-child {
			border-bottom: none;
		}

		.plan-card .plan-features li i {
			color: #28a745;
			font-size: 16px;
		}

		.plan-card .plan-footer {
			padding: 16px 20px 20px;
			border-top: 1px solid #f0f0f0;
			background: #f8fafc;
		}

		/* Featured Plan */
		.plan-card.featured {
			border: 2px solid #f21151;
			position: relative;
		}

		.plan-card.featured .plan-header {
			background: linear-gradient(135deg, #f21151, #e01a4f);
		}

		.plan-card.featured .featured-badge {
			position: absolute;
			top: 5px;
			right: 10px;
			background: #f21151;
			color: white;
			padding: 4px 16px;
			border-radius: 20px;
			font-size: 12px;
			font-weight: 700;
			text-transform: uppercase;
			letter-spacing: 0.5px;
		}

		/* ============================================================
        MY PLANS TABLE - MOBILE FIRST
        ============================================================ */
		.my-plans-card {
			background: #fff;
			border-radius: 20px;
			box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
			overflow: hidden;
			margin-top: 20px;
		}

		.my-plans-card .card-header {
			padding: 16px 20px;
			background: linear-gradient(135deg, #1B3058, #2a4780);
			color: white;
			font-weight: 600;
			font-size: 16px;
			display: flex;
			align-items: center;
			gap: 10px;
		}

		.my-plans-card .card-header i {
			font-size: 18px;
		}

		.my-plans-card .card-header .badge-count {
			margin-left: auto;
			background: rgba(255, 255, 255, 0.2);
			padding: 2px 14px;
			border-radius: 20px;
			font-size: 12px;
			font-weight: 400;
		}

		.my-plans-card .table-wrapper {
			overflow-x: auto;
			padding: 12px 8px;
			-webkit-overflow-scrolling: touch;
		}

		.plan-table {
			width: 100%;
			min-width: 450px;
			border-collapse: collapse;
			font-size: 13px;
		}

		.plan-table th,
		.plan-table td {
			padding: 10px 12px;
			text-align: left;
			border-bottom: 1px solid #f0f0f0;
		}

		.plan-table th {
			background: #f8f9fa;
			font-weight: 700;
			color: #1B3058;
			font-size: 11px;
			text-transform: uppercase;
			letter-spacing: 0.3px;
		}

		.plan-table td {
			font-size: 13px;
		}

		.plan-table tr:hover td {
			background: #f8f9ff;
		}

		.status-badge {
			display: inline-block;
			padding: 3px 14px;
			border-radius: 20px;
			font-size: 11px;
			font-weight: 600;
		}

		.status-badge.success {
			background: #d4edda;
			color: #155724;
		}

		.status-badge.pending {
			background: #fff3cd;
			color: #856404;
		}

		.status-badge.cancelled {
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
			.plan-container {
				padding: 25px;
			}

			.page-header {
				flex-direction: row;
				justify-content: space-between;
				align-items: center;
				flex-wrap: wrap;
			}

			.page-header h2 {
				font-size: 28px;
			}

			.plans-grid {
				grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
				gap: 25px;
			}

			.plan-card .plan-price {
				font-size: 48px;
			}

			.my-plans-card .table-wrapper {
				padding: 16px 20px;
			}

			.plan-table {
				min-width: auto;
			}
		}

		/* ============================================================
        RESPONSIVE - DESKTOP (1024px+)
        ============================================================ */
		@media (min-width: 1024px) {
			.plan-container {
				padding: 30px;
			}

			.plans-grid {
				grid-template-columns: repeat(4, 1fr);
				gap: 30px;
			}
		}

		/* ============================================================
        RESPONSIVE - SMALL MOBILE (480px-)
        ============================================================ */
		@media (max-width: 480px) {
			.plan-container {
				padding: 10px;
			}

			.page-header h2 {
				font-size: 20px;
			}

			.page-header p {
				font-size: 12px;
			}

			.plan-card .plan-header {
				padding: 18px 16px 12px;
			}

			.plan-card .plan-header h3 {
				font-size: 17px;
			}

			.plan-card .plan-price {
				font-size: 34px;
			}

			.plan-card .plan-features li {
				font-size: 13px;
				padding: 8px 10px;
			}

			.plan-table {
				font-size: 11px;
				min-width: 350px;
			}

			.plan-table th,
			.plan-table td {
				padding: 6px 8px;
			}

			.btn {
				font-size: 12px;
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

			.plan-card {
				box-shadow: none !important;
				border: 1px solid #ddd;
				break-inside: avoid;
			}

			.plan-card .plan-header {
				-webkit-print-color-adjust: exact;
				print-color-adjust: exact;
			}

			.my-plans-card {
				box-shadow: none !important;
				border: 1px solid #ddd;
			}

			.my-plans-card .card-header {
				-webkit-print-color-adjust: exact;
				print-color-adjust: exact;
			}

			body {
				background: white;
			}

			.plan-container {
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
				<div class="plan-container">

					<!-- Page Header -->
					<div class="page-header">
						<div>
							<h2><i class="fa fa-mobile"></i> <?= htmlspecialchars($PageTitle) ?></h2>
							<p>Purchase SMS credits to send notifications to parents and staff</p>
						</div>
						<div class="header-actions">
							<?php if (isset($_GET['action']) && $_GET['action'] == 'axjsdhg12sd'): ?>
								<a href="<?= $FileName ?>" class="btn btn-primary"><i class="fa fa-arrow-left"></i> Back to Plans</a>
							<?php else: ?>
								<a href="<?= $FileName ?>?action=axjsdhg12sd" class="btn btn-outline"><i class="fa fa-history"></i> My Plans</a>
							<?php endif; ?>
						</div>
					</div>

					<?= showMessage($stat) ?>

					<!-- My Plans View -->
					<?php if (isset($_GET['action']) && $_GET['action'] == 'axjsdhg12sd'): ?>
						<div class="my-plans-card">
							<div class="card-header">
								<i class="fa fa-history"></i> My SMS Plans
								<span class="badge-count"><?= count($myPlans) ?></span>
							</div>
							<div class="table-wrapper">
								<?php if (!empty($myPlans)): ?>
									<table class="plan-table">
										<thead>
											<tr>
												<th>#</th>
												<th>Plan Name</th>
												<th>SMS Remaining</th>
												<th>Price</th>
												<th>Status</th>
											</tr>
										</thead>
										<tbody>
											<?php $i = 0;
											foreach ($myPlans as $plan): $i++; ?>
												<tr>
													<td><?= $i ?></td>
													<td><strong><?= htmlspecialchars($plan['plan_name']) ?></strong></td>
													<td><?= number_format($plan['no_of_sms']) ?></td>
													<td>₦<?= number_format($plan['price'], 2) ?></td>
													<td>
														<?php if ($plan['status'] == '0'): ?>
															<span class="status-badge pending">Pending</span>
														<?php elseif ($plan['status'] == '1'): ?>
															<span class="status-badge success">Active</span>
														<?php elseif ($plan['status'] == '2'): ?>
															<span class="status-badge cancelled">Cancelled</span>
														<?php endif; ?>
													</td>
												</tr>
											<?php endforeach; ?>
										</tbody>
									</table>
								<?php else: ?>
									<div class="empty-state">
										<i class="fa fa-inbox"></i>
										<h4>No Plans Purchased</h4>
										<p>You haven't purchased any SMS plans yet. Browse the plans below to get started.</p>
									</div>
								<?php endif; ?>
							</div>
						</div>
					<?php else: ?>

						<!-- Plans Grid -->
						<div class="plans-grid">
							<?php if (!empty($plans)): ?>
								<?php foreach ($plans as $index => $plan):
									$isFeatured = ($index == 1); // Middle plan as featured
									$icon = ['fa-envelope', 'fa-envelope-o', 'fa-paper-plane', 'fa-rocket'][$index % 4];
									$bgColors = ['#1B3058', '#f21151', '#28a745', '#17a2b8'];
									$bgColor = $bgColors[$index % 4];
								?>
									<div class="plan-card <?= $isFeatured ? 'featured' : '' ?>">
										<form method="POST" action="">
											<?php if ($isFeatured): ?>
												<span class="featured-badge">⭐ Best Value</span>
											<?php endif; ?>
											<input type="hidden" name="plan_id" value="<?= $plan['id'] ?>">

											<div class="plan-header" style="background: linear-gradient(135deg, <?= $bgColor ?>, <?= $bgColor ?>cc);">
												<span class="plan-icon"><i class="fa <?= $icon ?>"></i></span>
												<h3><?= htmlspecialchars($plan['title']) ?></h3>
												<div class="plan-subtitle">SMS Credit Package</div>
											</div>

											<div class="plan-body">
												<div class="plan-price">
													₦<?= number_format($plan['price'], 0) ?>
													<small>/plan</small>
												</div>
												<ul class="plan-features">
													<li><i class="fa fa-check-circle"></i> <?= number_format($plan['no_of_sms']) ?> SMS Credits</li>
													<li><i class="fa fa-check-circle"></i> Valid for <?= htmlspecialchars($plan['exp_date'] ?? '30') ?> days</li>
													<li><i class="fa fa-check-circle"></i> Instant delivery</li>
													<li><i class="fa fa-check-circle"></i> Track usage</li>
												</ul>
											</div>

											<div class="plan-footer">
												<button type="submit" name="buy_sms_plan" class="btn btn-block <?= $isFeatured ? 'btn-danger' : 'btn-primary' ?>">
													<i class="fa fa-shopping-cart"></i> Buy Now
												</button>
											</div>
										</form>
									</div>
								<?php endforeach; ?>
							<?php else: ?>
								<div class="empty-state" style="grid-column: 1 / -1;">
									<i class="fa fa-exclamation-triangle"></i>
									<h4>No SMS Plans Available</h4>
									<p>Please contact the administrator to set up SMS plans.</p>
								</div>
							<?php endif; ?>
						</div>

						<!-- Info Section -->
						<div style="background: #fff; border-radius: 16px; padding: 20px; box-shadow: 0 2px 12px rgba(0,0,0,0.08); margin-top: 10px;">
							<div style="display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">
								<i class="fa fa-info-circle" style="font-size: 24px; color: #17a2b8;"></i>
								<div>
									<strong style="color: #1B3058;">How it works:</strong>
									<span style="color: #666; font-size: 14px;">
										Purchase a plan to get SMS credits. Each credit allows you to send one SMS message.
										Credits are valid for <?= $plans[0]['exp_date'] ?? '30' ?> days from purchase.
									</span>
								</div>
							</div>
						</div>

					<?php endif; ?>

				</div>
			</div>
			<?php include('inc.footer.php'); ?>
		</div>
	</div>

	<?php include('inc.js.php'); ?>
	<script>
		// Add active class to plan cards on click
		document.querySelectorAll('.plan-card').forEach(function(card) {
			card.addEventListener('click', function(e) {
				// Don't trigger if clicking on the button
				if (e.target.closest('button') || e.target.closest('form')) {
					return;
				}
				// Find the form and submit it
				var form = this.querySelector('form');
				if (form) {
					form.submit();
				}
			});
		});
	</script>
</body>

</html>