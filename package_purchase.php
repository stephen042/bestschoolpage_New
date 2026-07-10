<?php
include('config.php');
include('skool/inc.session-create.php');

$token = trim((string)($_GET['token'] ?? ''));
$action = trim((string)($_GET['action'] ?? ''));

$iPayment = [];
if ($token !== '') {
    $iPayment = db_get_row("SELECT * FROM school_purchased_pacakage WHERE success_token = ?", [$token]);
}

$isApproved = (!empty($iPayment) && (string)($iPayment['status'] ?? '0') === '1');

$reference = trim((string)($_GET['reference'] ?? ''));

$statusType = 'invalid';
if ($isApproved || $action === 'success') {
    $statusType = 'success';
} elseif ($action === 'cancel') {
    $statusType = 'cancel';
}

$statusTitle = 'Invalid URL';
$statusMessage = 'We could not validate this payment link. Please try again from your dashboard.';

if ($statusType === 'success') {
    $statusTitle = 'Payment Completed Successfully';
    $statusMessage = 'Your package purchase has been confirmed and your account has been updated.';
} elseif ($statusType === 'cancel') {
    $statusTitle = 'Payment Was Cancelled Or Failed';
    $statusMessage = 'No charge was applied to your package. You can retry payment at any time.';
}
?>

<!DOCTYPE html>
<html>

<head>
    <?php include('inc.meta-new.php'); ?>
    <title>Package Payment Status - Best School Page</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        * {
            font-family: 'Inter', sans-serif;
        }

        body {
            background: #f8fafc;
            overflow-x: hidden;
        }

        .glass-header {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-bottom: 1px solid rgba(226, 232, 240, 0.6);
            position: sticky;
            top: 0;
            z-index: 1000;
            transition: all 0.3s ease;
        }

        .glass-header.scrolled {
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        }

        .status-hero {
            position: relative;
            min-height: 44vh;
            display: flex;
            align-items: center;
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #0f172a 100%);
            overflow: hidden;
        }

        .status-hero::before {
            content: '';
            position: absolute;
            inset: 0;
            background: url('images/home-slider.png') center/cover no-repeat;
            opacity: 0.2;
        }

        .status-hero-overlay {
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg,
                    rgba(15, 23, 42, 0.92) 0%,
                    rgba(30, 41, 59, 0.85) 50%,
                    rgba(15, 23, 42, 0.92) 100%);
            z-index: 1;
        }

        .status-hero-content {
            position: relative;
            z-index: 2;
        }

        .status-wrap {
            margin-top: -70px;
            margin-bottom: 80px;
            position: relative;
            z-index: 3;
        }

        .status-card {
            background: rgba(255, 255, 255, 0.97);
            border: 1px solid #e2e8f0;
            border-radius: 24px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
            padding: 30px;
        }

        .status-icon {
            width: 68px;
            height: 68px;
            border-radius: 999px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.7rem;
        }

        .status-icon.success {
            background: #dcfce7;
            color: #15803d;
        }

        .status-icon.cancel {
            background: #fef2f2;
            color: #b91c1c;
        }

        .status-icon.invalid {
            background: #fff7ed;
            color: #c2410c;
        }

        .meta-row {
            display: grid;
            grid-template-columns: 1fr;
            gap: 12px;
            margin-top: 18px;
        }

        .meta-item {
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 12px 14px;
            background: #f8fafc;
        }

        .meta-label {
            font-size: 0.78rem;
            font-weight: 700;
            letter-spacing: 0.03em;
            color: #64748b;
            text-transform: uppercase;
        }

        .meta-value {
            margin-top: 4px;
            font-weight: 600;
            color: #0f172a;
            word-break: break-word;
        }

        .btn-primary-custom {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            border: none;
            border-radius: 12px;
            padding: 11px 16px;
            background: linear-gradient(135deg, #4f46e5 0%, #6366f1 100%);
            color: #fff;
            font-weight: 700;
            text-decoration: none;
        }

        .btn-primary-custom:hover {
            color: #fff;
            text-decoration: none;
        }

        .btn-muted-custom {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            border: 1px solid #cbd5e1;
            border-radius: 12px;
            padding: 11px 16px;
            background: #fff;
            color: #334155;
            font-weight: 700;
            text-decoration: none;
        }

        .btn-muted-custom:hover {
            color: #1e293b;
            text-decoration: none;
        }

        @media (max-width: 768px) {
            .status-wrap {
                margin-top: -45px;
                margin-bottom: 55px;
            }

            .status-card {
                padding: 20px;
                border-radius: 18px;
            }
        }
    </style>
</head>

<body class="home page-template page-template-tpl-home page-template-tpl-home-php page page-id-14">
    <div id="page" class="site">
        <?php include('inc.header-new.php'); ?>
        <div id="content" class="site-content">
            <section class="status-hero">
                <div class="status-hero-overlay"></div>
                <div class="container mx-auto px-4 status-hero-content">
                    <div class="max-w-4xl mx-auto text-center">
                        <span class="inline-flex items-center gap-2 px-4 py-1.5 bg-indigo-500/20 text-indigo-300 text-sm font-semibold rounded-full border border-indigo-500/20 mb-5">
                            <i class="fas fa-credit-card"></i>
                            Package Payment
                        </span>
                        <h1 class="text-white text-4xl md:text-5xl font-extrabold mb-3">Payment Status</h1>
                        <p class="text-slate-300">Review your package purchase result below.</p>
                    </div>
                </div>
            </section>

            <section class="status-wrap">
                <div class="container mx-auto px-4">
                    <div class="max-w-3xl mx-auto status-card">
                        <div class="text-center">
                            <span class="status-icon <?= $statusType === 'success' ? 'success' : ($statusType === 'cancel' ? 'cancel' : 'invalid') ?>">
                                <?php if ($statusType === 'success') { ?>
                                    <i class="fas fa-check"></i>
                                <?php } elseif ($statusType === 'cancel') { ?>
                                    <i class="fas fa-xmark"></i>
                                <?php } else { ?>
                                    <i class="fas fa-triangle-exclamation"></i>
                                <?php } ?>
                            </span>

                            <h2 class="text-2xl md:text-3xl font-extrabold text-slate-900 mt-4 mb-2"><?= e($statusTitle) ?></h2>
                            <p class="text-slate-600 text-base md:text-lg"><?= e($statusMessage) ?></p>
                        </div>

                        <div class="meta-row">
                            <div class="meta-item">
                                <div class="meta-label">Payment Reference</div>
                                <div class="meta-value"><?= e($reference !== '' ? $reference : 'N/A') ?></div>
                            </div>

                            <div class="meta-item">
                                <div class="meta-label">Plan</div>
                                <div class="meta-value"><?= e($iPayment['plan_name'] ?? 'N/A') ?></div>
                            </div>

                            <div class="meta-item">
                                <div class="meta-label">Amount</div>
                                <div class="meta-value">N<?= number_format((float)($iPayment['price'] ?? 0), 2) ?></div>
                            </div>
                        </div>

                        <div class="mt-6" style="display:flex; gap:10px; flex-wrap:wrap; justify-content:center;">
                            <a href="<?= SITE_URL ?>package.php" class="btn-primary-custom">
                                <i class="fas fa-layer-group"></i>
                                Back To Pricing
                            </a>
                            <a href="<?= SKOOL_URL ?>" class="btn-muted-custom">
                                <i class="fas fa-gauge"></i>
                                Go To Dashboard
                            </a>
                        </div>
                    </div>
                </div>
            </section>
        </div>
        <?php include('inc.footer-new.php'); ?>
    </div>
    <?php include('inc.js-new.php'); ?>
    <script>
        (function() {
            var header = document.querySelector('.glass-header');
            if (header) {
                window.addEventListener('scroll', function() {
                    if (window.scrollY > 50) {
                        header.classList.add('scrolled');
                    } else {
                        header.classList.remove('scrolled');
                    }
                });
            }
        })();
    </script>
</body>

</html>