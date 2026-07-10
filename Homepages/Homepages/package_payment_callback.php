<?php
require_once('config.php');

// Paystack secret reused from existing project payment flows.
define('PAYSTACK_SECRET_KEY', 'sk_test_53777abc4825089709409bf6c3ec86e9c76b5803');

$reference = trim((string)($_GET['reference'] ?? $_POST['reference'] ?? ''));
$token = trim((string)($_GET['token'] ?? $_POST['token'] ?? ''));

if ($reference === '') {
    redirect(SITE_URL . 'package_succuess.php?action=cancel' . ($token !== '' ? '&token=' . urlencode($token) : ''));
    exit;
}

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://api.paystack.co/transaction/verify/' . rawurlencode($reference));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . PAYSTACK_SECRET_KEY,
    'Content-Type: application/json'
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$result = json_decode((string)$response, true);

$paymentId = 0;
if ($httpCode === 200 && isset($result['data']['status']) && $result['data']['status'] === 'success') {
    $metadataRaw = $result['data']['metadata'] ?? [];
    if (is_array($metadataRaw)) {
        $metadata = $metadataRaw;
    } elseif (is_string($metadataRaw) && $metadataRaw !== '') {
        $metadata = json_decode($metadataRaw, true);
        if (!is_array($metadata)) {
            $metadata = [];
        }
    } else {
        $metadata = [];
    }

    $paymentId = (int)($metadata['payment_id'] ?? 0);
    if ($token === '') {
        $token = trim((string)($metadata['token'] ?? ''));
    }

    if ($paymentId > 0) {
        db_update('school_purchased_pacakage', ['status' => 1], 'id = ?', [$paymentId]);
    } elseif ($token !== '') {
        db_update('school_purchased_pacakage', ['status' => 1], 'success_token = ?', [$token]);
    }

    $target = SITE_URL . 'package_succuess.php?action=success&reference=' . urlencode($reference);
    if ($token !== '') {
        $target .= '&token=' . urlencode($token);
    }
    redirect($target);
    exit;
}

$target = SITE_URL . 'package_succuess.php?action=cancel&reference=' . urlencode($reference);
if ($token !== '') {
    $target .= '&token=' . urlencode($token);
}
redirect($target);
exit;
