<?php
require_once('config.php');

// Start session if not already started
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// Check if token is provided
if (empty($_GET['token'])) {
    $_SESSION['error'] = "Invalid payment link. No token provided.";
    redirect(SITE_URL . 'package_purchase.php');
    exit;
}

try {
    // Get payment details using PDO
    $ipackPayment = db_get_row(
        "SELECT * FROM school_purchased_pacakage WHERE success_token = ?",
        [$_GET['token']]
    );

    if (empty($ipackPayment)) {
        $_SESSION['error'] = "Invalid payment token. Please try again.";
        redirect(SITE_URL . 'package_purchase.php');
        exit;
    }

    // Check if payment is already completed
    if ($ipackPayment['status'] == 1) {
        $_SESSION['success'] = "This package has already been activated.";
        redirect(SITE_URL . 'skool/');
        exit;
    }

    // Get package details
    $ipackagePlan = db_get_row(
        "SELECT * FROM package WHERE id = ?",
        [$ipackPayment['plan_id']]
    );

    if (empty($ipackagePlan)) {
        $_SESSION['error'] = "Package not found.";
        redirect(SITE_URL . 'package_purchase.php');
        exit;
    }

    // Get user details
    $iUser = db_get_row(
        "SELECT * FROM school_register WHERE id = ?",
        [$ipackPayment['userid']]
    );

    if (empty($iUser)) {
        $_SESSION['error'] = "User not found.";
        redirect(SITE_URL . 'login.php');
        exit;
    }

    // VoguePay Configuration
    $Merchant_id = 'DEMO';
    $Memo = 'This payment is for Package Plan';
    $Item = $ipackagePlan['title'];
    $Description = 'This payment is for Package Plan';
    $Amount = $ipackPayment['price'];
    $Userid = $ipackPayment['userid'];
    $Email = $iUser['email'];
    $Name = $iUser['name'];
    $Phone = $iUser['contact_no'];
    $Address = $iUser['location'];
    $City = $iUser['state'];
    $State = $iUser['state'];
    $Zipcode = $iUser['contact_no'];
} catch (PDOException $e) {
    error_log("Package payment error: " . $e->getMessage());
    $_SESSION['error'] = "A database error occurred. Please try again.";
    redirect(SITE_URL . 'package_purchase.php');
    exit;
}
?>
<!DOCTYPE html>
<html>

<head>
    <title>Payment - Package Purchase</title>
    <?php include('inc.meta-new.php'); ?>
    <script src="//voguepay.com/js/voguepay.js"></script>
    <style>
        .payment-container {
            padding: 60px 0;
            min-height: 400px;
            background: #f9f9f9;
        }

        .payment-box {
            background: #fff;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
            max-width: 600px;
            margin: 0 auto;
        }

        .payment-box h2 {
            color: #1B3058;
            text-align: center;
            margin-bottom: 30px;
        }

        .payment-details {
            margin-bottom: 30px;
        }

        .payment-details .detail-row {
            padding: 10px 0;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
        }

        .payment-details .detail-row .label {
            font-weight: 600;
            color: #555;
        }

        .payment-details .detail-row .value {
            color: #1B3058;
        }

        .btn-pay {
            display: block;
            width: 100%;
            padding: 15px;
            background: #1B3058;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 18px;
            cursor: pointer;
            transition: background 0.3s;
        }

        .btn-pay:hover {
            background: #f21151;
        }

        .btn-cancel {
            display: block;
            width: 100%;
            padding: 15px;
            background: #dc3545;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 18px;
            cursor: pointer;
            margin-top: 10px;
            text-decoration: none;
            text-align: center;
        }

        .btn-cancel:hover {
            background: #c82333;
            color: white;
            text-decoration: none;
        }

        .alert {
            padding: 12px;
            border-radius: 4px;
            margin-bottom: 20px;
        }

        .alert-danger {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .loader {
            text-align: center;
            padding: 20px;
        }

        .loader i {
            font-size: 48px;
            color: #1B3058;
        }

        .payment-status {
            text-align: center;
            padding: 20px;
        }

        .payment-status .fa-spinner {
            font-size: 48px;
            color: #1B3058;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        .loading-text {
            text-align: center;
            margin-top: 20px;
            color: #666;
        }
    </style>
</head>

<body class="home page-template page-template-tpl-home page-template-tpl-home-php page page-id-14">
    <div id="page" class="site">
        <?php include('inc.header-new.php'); ?>
        <div id="content" class="site-content">
            <section class="payment-container">
                <div class="container">
                    <div class="payment-box">
                        <h2>Complete Your Payment</h2>

                        <?php if (isset($_SESSION['error']) && !empty($_SESSION['error'])): ?>
                            <div class="alert alert-danger">
                                <?= e($_SESSION['error']) ?>
                            </div>
                            <?php unset($_SESSION['error']); ?>
                        <?php endif; ?>

                        <div class="payment-details">
                            <div class="detail-row">
                                <span class="label">Package:</span>
                                <span class="value"><?= e($Item) ?></span>
                            </div>
                            <div class="detail-row">
                                <span class="label">Amount:</span>
                                <span class="value">₦ <?= e($Amount) ?></span>
                            </div>
                            <div class="detail-row">
                                <span class="label">School:</span>
                                <span class="value"><?= e($Name) ?></span>
                            </div>
                            <div class="detail-row">
                                <span class="label">Email:</span>
                                <span class="value"><?= e($Email) ?></span>
                            </div>
                        </div>

                        <div id="payment-status" class="payment-status" style="display:none;">
                            <i class="fa fa-spinner fa-spin"></i>
                            <p>Redirecting to payment gateway...</p>
                        </div>

                        <form method='POST' id='payform' action='//voguepay.com/pay/' onsubmit='return false;'>
                            <input type='hidden' name='v_merchant_id' value='<?= e($Merchant_id) ?>' />
                            <input type='hidden' name='merchant_ref' value='<?= e($ipackPayment['success_token']) ?>' />
                            <input type='hidden' name='memo' value='<?= e($Memo) ?>' />
                            <input type='hidden' name='item_1' value='<?= e($Item) ?>' />
                            <input type='hidden' name='description_1' value='<?= e($Description) ?>' />
                            <input type='hidden' name='price_1' value='<?= e($Amount) ?>' />
                            <input type='hidden' name='success_url' value='<?= SITE_URL ?>package_success.php?token=<?= e($ipackPayment['success_token']) ?>' />
                            <input type='hidden' name='fail_url' value='<?= SITE_URL ?>package_success.php?action=cancel' />
                            <input type='hidden' name='developer_code' value='pq7778ehh9YbZ' />
                            <input type='hidden' name='store_id' value='25' />
                            <input type='hidden' name='notify_url' value='//www.mydomain.com/notification.php' />
                            <input type='hidden' name='cur' value='NGN' />
                            <input type='hidden' name='total' value='<?= e($Amount) ?>' />
                            <input type='hidden' name='name' value='<?= e($Name) ?>' />
                            <input type='hidden' name='address' value='<?= e($Address) ?>' />
                            <input type='hidden' name='city' value='<?= e($City) ?>' />
                            <input type='hidden' name='state' value='<?= e($State) ?>' />
                            <input type='hidden' name='zipcode' value='<?= e($Zipcode) ?>' />
                            <input type='hidden' name='email' value='<?= e($Email) ?>' />
                            <input type='hidden' name='phone' value='<?= e($Phone) ?>' />
                            <input type='hidden' name='closed' value='closedFunction'>
                            <input type='hidden' name='success' value='successFunction'>
                            <input type='hidden' name='failed' value='failedFunction'>
                            <input id="link" type='image' style="width:0px; height:0px;" src='https://voguepay.com/images/buttons/make_payment_blue.png' alt='Submit' />
                        </form>

                        <button type="button" class="btn-pay" onclick="initiatePayment()">
                            <i class="fa fa-credit-card"></i> Pay Now
                        </button>

                        <a href="<?= SITE_URL ?>package_purchase.php" class="btn-cancel">
                            <i class="fa fa-times"></i> Cancel and Go Back
                        </a>
                    </div>
                </div>
            </section>
        </div>
        <?php include('inc.footer-new.php'); ?>
    </div>
    <?php include('inc.js-new.php'); ?>

    <script>
        function closedFunction() {
            // User closed the payment window
            console.log('Payment window closed');
        }

        function successFunction(transaction_id) {
            // Payment successful
            window.location.href = "<?= SITE_URL ?>package_success.php?action=success&transaction_id=" + transaction_id + "&token=<?= e($_GET['token']) ?>";
        }

        function failedFunction(transaction_id) {
            // Payment failed
            window.location.href = "<?= SITE_URL ?>package_success.php?action=cancel";
        }

        function initiatePayment() {
            // Show loading indicator
            document.getElementById('payment-status').style.display = 'block';

            // Initialize VoguePay
            Voguepay.init({
                form: 'payform'
            });

            // Click the hidden submit button
            setTimeout(function() {
                var element = document.getElementById('link');
                if (element) {
                    element.click();
                }
            }, 500);
        }

        // Auto-initiate payment if page loads with redirect
        document.addEventListener('DOMContentLoaded', function() {
            // Check if we should auto-initiate (e.g., from package selection)
            var autoInitiate = true; // Set to false if you want user to click button
            if (autoInitiate) {
                // Small delay to ensure page is fully loaded
                setTimeout(function() {
                    initiatePayment();
                }, 1000);
            }
        });
    </script>
</body>

</html>