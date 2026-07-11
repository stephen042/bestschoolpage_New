<?php
// ============================================================================
// PACKAGE PAYMENT INITIATION
// ============================================================================
// Initializes Paystack payment for package purchase
// ============================================================================

include('config.php');

// Paystack keys
define('PAYSTACK_SECRET_KEY', 'sk_test_53777abc4825089709409bf6c3ec86e9c76b5803');
define('PAYSTACK_CALLBACK_URL', SITE_URL . 'package_payment_callback.php');

$token = trim((string)($_GET['token'] ?? ''));
if ($token === '') {
    redirect(SITE_URL . 'package_succuess.php?action=cancel');
    exit;
}

// Get payment record using PDO
$ipackPayment = db_get_row("SELECT * FROM school_purchased_pacakage WHERE success_token = ?", [$token]);
if (empty($ipackPayment)) {
    redirect(SITE_URL . 'package_succuess.php?action=cancel');
    exit;
}

// Get plan details
$ipackagePlan = db_get_row("SELECT * FROM package WHERE id = ?", [(int)($ipackPayment['plan_id'] ?? 0)]);
$iUser = db_get_row("SELECT * FROM school_register WHERE id = ?", [(int)($ipackPayment['userid'] ?? 0)]);

// Prepare user details
$email = trim((string)($iUser['email'] ?? ($_SESSION['email'] ?? 'school@example.com')));
if ($email === '') {
    $email = 'school@example.com';
}

$name = trim((string)($iUser['name'] ?? ($_SESSION['school_name'] ?? 'School')));
$phone = trim((string)($iUser['contact_no'] ?? ''));
$state = trim((string)($iUser['state'] ?? ''));
$location = trim((string)($iUser['location'] ?? ''));

// Calculate amount
$amount = (float)($ipackPayment['price'] ?? 0);
$amountKobo = (int)round($amount * 100);
if ($amountKobo <= 0) {
    redirect(SITE_URL . 'package_succuess.php?action=cancel');
    exit;
}

// Generate reference
$reference = 'PKG_' . (int)$ipackPayment['id'] . '_' . time() . '_' . randomFix(6);

// Prepare metadata
$metadata = [
    'payment_id' => (int)$ipackPayment['id'],
    'plan_id' => (int)($ipackagePlan['id'] ?? 0),
    'school_id' => (int)($ipackPayment['userid'] ?? 0),
    'token' => $token,
    'name' => $name,
    'phone' => $phone,
    'state' => $state,
    'location' => $location,
];

// Prepare Paystack payload
$paystackData = [
    'email' => $email,
    'amount' => $amountKobo,
    'reference' => $reference,
    'callback_url' => PAYSTACK_CALLBACK_URL . '?token=' . urlencode($token),
    'metadata' => json_encode($metadata),
];

// Initialize Paystack transaction
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://api.paystack.co/transaction/initialize');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . PAYSTACK_SECRET_KEY,
    'Content-Type: application/json',
    'Cache-Control: no-cache'
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($paystackData));

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$result = json_decode((string)$response, true);

// Redirect to Paystack payment page or cancel
if ($httpCode === 200 && !empty($result['status']) && !empty($result['data']['authorization_url'])) {
    redirect($result['data']['authorization_url']);
    exit;
}

redirect(SITE_URL . 'package_succuess.php?action=cancel');
exit;