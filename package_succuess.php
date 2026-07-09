<?php
require_once('config.php');

// Start session if not already started
if (session_status() !== PHP_SESSION_ACTIVE) {
	session_start();
}

// Check if token is provided
if (empty($_GET['token'])) {
	$isValid = false;
	$message = "Invalid URL. No token provided.";
} else {
	try {
		// Get payment details using PDO
		$iPayment = db_get_row(
			"SELECT * FROM school_purchased_pacakage WHERE success_token = ?",
			[$_GET['token']]
		);

		if (empty($iPayment)) {
			$isValid = false;
			$message = "Invalid payment token.";
		} else {
			$isValid = true;
			$isAPproved = '0';

			// Verify payment with VoguePay
			if (!empty($_GET['transaction_id'])) {
				$url = 'https://voguepay.com/?v_transaction_id=' . $_GET['transaction_id'] . '&type=json&demo=true';

				$ch = curl_init();
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch, CURLOPT_URL, $url);
				$result = curl_exec($ch);
				curl_close($ch);

				$obj = json_decode($result, true);

				if ($obj['status'] == 'Approved') {
					// Update payment status using PDO
					$aryData = array(
						'status' => 1,
						'transaction_id' => $_GET['transaction_id'],
						'payment_date' => date('Y-m-d H:i:s')
					);

					$flgIn = db_update(
						"school_purchased_pacakage",
						$aryData,
						"id = ?",
						[$iPayment['id']]
					);

					if ($flgIn !== false) {
						$isAPproved = '1';
						$_SESSION['success'] = "Payment has been completed successfully!";
					} else {
						error_log("Failed to update payment status for transaction: " . $_GET['transaction_id']);
						$isAPproved = '0';
					}
				} else {
					$isAPproved = '0';
					error_log("Payment not approved for transaction: " . $_GET['transaction_id'] . " - Status: " . $obj['status']);
				}
			} else {
				$isAPproved = '0';
				$message = "No transaction ID provided.";
			}
		}
	} catch (PDOException $e) {
		error_log("Payment verification error: " . $e->getMessage());
		$isValid = false;
		$message = "A database error occurred. Please contact support.";
		$isAPproved = '0';
	}
}

$pageTitle = "Payment Status";
?>
<!DOCTYPE html>
<html>

<head>
	<?php include('inc.meta-new.php'); ?>
	<style>
		.payment-status {
			padding: 60px 0;
			min-height: 400px;
		}

		.status-box {
			background: #fff;
			padding: 40px;
			border-radius: 8px;
			box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
			text-align: center;
		}

		.status-box h2 {
			color: #1B3058;
			margin-bottom: 20px;
		}

		.status-box .success {
			color: #28a745;
		}

		.status-box .error {
			color: #dc3545;
		}

		.status-box .info {
			color: #17a2b8;
		}

		.btn-home {
			display: inline-block;
			padding: 12px 24px;
			background: #1B3058;
			color: white;
			border-radius: 4px;
			text-decoration: none;
			margin-top: 20px;
		}

		.btn-home:hover {
			background: #f21151;
			color: white;
		}
	</style>
</head>

<body class="home page-template page-template-tpl-home page-template-tpl-home-php page page-id-14">
	<div id="page" class="site">
		<?php include('inc.header-new.php'); ?>
		<div id="content" class="site-content">
			<section class="payment-status">
				<div class="container">
					<div class="row">
						<div class="col-md-8 col-md-offset-2">
							<div class="status-box">
								<?php if (isset($isValid) && $isValid && $isAPproved == '1'): ?>
									<h2 class="success">
										<i class="fa fa-check-circle" style="font-size: 48px;"></i><br>
										Congratulations! Payment has been completed successfully.
									</h2>
									<p>Your package has been activated. You can now access all the features of your selected plan.</p>
									<a href="<?= SITE_URL ?>skool/" class="btn-home">Go to Dashboard</a>

								<?php elseif (isset($_GET['action']) && $_GET['action'] == 'cancel'): ?>
									<h2 class="error">
										<i class="fa fa-times-circle" style="font-size: 48px;"></i><br>
										Payment has been cancelled.
									</h2>
									<p>You cancelled the payment process. You can try again when you're ready.</p>
									<a href="<?= SITE_URL ?>package_purchase.php" class="btn-home">Try Again</a>

								<?php elseif (isset($_GET['action']) && $_GET['action'] == 'success'): ?>
									<h2 class="success">
										<i class="fa fa-check-circle" style="font-size: 48px;"></i><br>
										Registration completed successfully!
									</h2>
									<p>Your school has been registered and your package is being processed.</p>
									<a href="<?= SITE_URL ?>skool/" class="btn-home">Go to Dashboard</a>

								<?php else: ?>
									<h2 class="error">
										<i class="fa fa-exclamation-triangle" style="font-size: 48px;"></i><br>
										<?= isset($message) ? e($message) : 'Invalid URL.' ?>
									</h2>
									<p>Please check the payment link and try again.</p>
									<a href="<?= SITE_URL ?>package_purchase.php" class="btn-home">Back to Packages</a>
								<?php endif; ?>

								<?php if (isset($_SESSION['success'])): ?>
									<div class="alert alert-success" style="margin-top: 20px;">
										<?= e($_SESSION['success']) ?>
									</div>
									<?php unset($_SESSION['success']); ?>
								<?php endif; ?>

								<?php if (isset($_SESSION['error'])): ?>
									<div class="alert alert-danger" style="margin-top: 20px;">
										<?= e($_SESSION['error']) ?>
									</div>
									<?php unset($_SESSION['error']); ?>
								<?php endif; ?>

								<br>
								<br>
							</div>
						</div>
					</div>
				</div>
			</section>
		</div>
		<?php include('inc.footer-new.php'); ?>
	</div>
	<?php include('inc.js-new.php'); ?>
</body>

</html>