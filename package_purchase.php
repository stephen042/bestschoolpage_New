<?php
// ============================================================================
// PACKAGE PURCHASE (Legacy - Keep for reference)
// ============================================================================
// This is an older version of package purchase - kept for backward compatibility
// ============================================================================

include('config.php');

$PageTitle = "Packages";
$FileName = 'package_purchase.php';
$validate = new Validation();

if (empty($_SESSION['userid'])) {
    redirect(LIVE_URL . 'login.php');
}

if (isset($_POST['pakages'])) {
    $planId = isset($_POST['plan_id']) ? (int)$_POST['plan_id'] : 0;

    if ($planId <= 0) {
        $_SESSION['error'] = 'Invalid plan selected.';
        redirect(SITE_URL . 'package_purchase.php');
        exit;
    }

    $iPlan = db_get_row("SELECT * FROM package WHERE id = ?", [$planId]);

    if (empty($iPlan)) {
        $_SESSION['error'] = 'Plan not found.';
        redirect(SITE_URL . 'package_purchase.php');
        exit;
    }

    $exdate = date('Y-m-d');

    // Check if user already has an active package using PDO
    $rSchool_Pakage = db_get_row(
        "SELECT id FROM school_purchased_pacakage WHERE userid = ? AND exp_date > ?",
        [(int)$_SESSION['userid'], $exdate]
    );

    if (empty($rSchool_Pakage)) {
        $success_token = randomFix(15);
        $cancel_token = randomFix(15);

        $date = date('Y-m-d');
        $days = (int)($iPlan['no_of_days'] ?? 30);
        $expDate = date('Y-m-d', strtotime($date . " + $days days"));

        $aryData = array(
            'plan_id'       => (int)$iPlan['id'],
            'plan_name'     => (string)($iPlan['title'] ?? 'Unknown Plan'),
            'price'         => (float)($iPlan['price'] ?? 0),
            'no_of_days'    => $days,
            'exp_date'      => $expDate,
            'usertype'      => (int)($_SESSION['usertype'] ?? 0),
            'userid'        => (int)$_SESSION['userid'],
            'success_token' => $success_token,
            'cancel_token'  => $cancel_token,
            'create_at'     => date('Y-m-d H:i:s'),
            'status'        => 0,
        );

        $flgIn = db_insert("school_purchased_pacakage", $aryData);

        if ($flgIn) {
            $_SESSION['payment_id'] = db_last_id();
            redirect(LIVE_URL . 'package_vogupay.php');
        } else {
            $_SESSION['error'] = 'Failed to create purchase. Please try again.';
            redirect(SITE_URL . 'package_purchase.php');
        }
        exit;
    } else {
        $_SESSION['error'] = 'You have already purchased a plan.';
        redirect(SITE_URL . 'package_purchase.php');
    }
}
?>
<!DOCTYPE html>
<html lang="en-US">

<head>
    <?php include('inc.meta.php'); ?>
    <style>
        /* Keep existing styles */
        .price-table .services {
            height: 515px;
        }

        .vijju {
            padding-bottom: 30px;
            background: #cecece82;
        }

        .main-navbar {
            margin-bottom: 0;
        }

        .price-table {
            margin: 0px 143px 0px 143px;
            padding: 13px 0 10px 0;
        }

        .vijju .price-table .sec1-head span {
            font-size: 27px;
            font-family: arial;
            font-weight: 700;
            color: #1B3058;
            margin-left: 101px;
            cursor: default;
        }

        .price-table h2 {
            text-align: center;
            font-family: 'Open Sans', sans-serif;
            font-size: 32px;
            font-weight: bold;
            color: tomato;
        }

        .price-table .tables-rj {
            margin: 20px 100px 0px 100px;
        }

        .price-table .table-vj {
            text-align: center;
            padding-bottom: 12px;
            background: white;
            box-shadow: 0px 0px 6px 1px #f8f4f4;
            transition: transform .5s;
            box-shadow: 0px 0px 1px 0px grey;
        }

        .price-table .table-vj hr {
            margin: 0;
        }

        .price-table .basic {
            background: #d39d05;
            padding: 5px;
        }

        .price-table .basic h3 {
            font-family: calibri;
            color: #ffffffd9;
            font-size: 35px;
            margin: 5px;
        }

        .price-table .basic-price {
            background: #ebb413;
            border-bottom: 1px solid black;
        }

        .price-table .basic-price h1 {
            font-family: sans-serif;
            font-size: 40px;
            color: #ffffffe8;
        }

        .price-table .basic-price p {
            color: #ffffffd1;
            font-size: 15px;
            font-family: Microsoft JhengHei UI heavy;
            padding-bottom: 2px;
        }

        .price-table .standard- {
            background: #7d1e4a;
            padding: 5px;
        }

        .price-table .standard- h3 {
            font-family: calibri;
            color: #ffffffd9;
            font-size: 35px;
            margin: 5px;
        }

        .price-table .standard-price {
            background: #97285b;
            border-bottom: 1px solid black;
        }

        .price-table .standard-price h1 {
            font-family: sans-serif;
            font-size: 40px;
            color: #ffffffe8;
        }

        .price-table .standard-price p {
            color: #ffffffd1;
            font-size: 15px;
            font-family: Microsoft JhengHei UI heavy;
            padding-bottom: 2px;
        }

        .price-table .premium {
            background: #14646d;
            padding: 5px;
        }

        .price-table .premium h3 {
            font-family: calibri;
            color: #ffffffd9;
            font-size: 35px;
            margin: 5px;
        }

        .price-table .premium-price {
            background: #2d818b;
            border-bottom: 1px solid black;
        }

        .price-table .premium-price h1 {
            font-family: sans-serif;
            font-size: 40px;
            color: #ffffffe8;
        }

        .price-table .premium-price p {
            color: #ffffffd1;
            font-size: 15px;
            font-family: Microsoft JhengHei UI heavy;
            padding-bottom: 2px;
        }

        .price-table .services {
            padding: 20px 0 0 0;
            background: white;
            line-height: 30px;
        }

        .price-table .services p {
            font-size: 15px;
            color: #0000009e;
            font-family: 'Fira Sans Condensed', sans-serif;
        }

        .price-table .sing-up-1 {
            padding-top: 20px;
        }

        .price-table .sing-up-2 {
            padding-top: 20px;
        }

        .price-table .sing-up-3 {
            padding-top: 20px;
        }

        .price-table .sing-up-1 button {
            padding: 10px 20px 10px 20px;
            border: 0px solid;
            background: #ebb413;
            font-weight: 800;
            border-radius: 4px;
            color: white;
            font-size: 12px;
            font-family: Microsoft JhengHei UI heavy;
        }

        .price-table .sing-up-2 button {
            padding: 10px 20px 10px 20px;
            border: 0px solid;
            background: #97285b;
            font-weight: 800;
            color: white;
            border-radius: 4px;
            font-size: 12px;
            font-family: Microsoft JhengHei UI heavy;
        }

        .price-table .sing-up-3 button {
            padding: 10px 20px 10px 20px;
            border: 0px solid;
            background: #2d818b;
            font-weight: 800;
            border-radius: 4px;
            color: white;
            font-size: 12px;
            font-family: Microsoft JhengHei UI heavy;
        }

        .price-table .table-vj:hover {
            transform: scale(1.1);
        }

        @media (max-width:767px) {
            .price-table {
                width: 100%;
                padding: 0;
                margin: 0;
            }

            .vijju .price-table .sec1-head {
                padding-bottom: 20px;
            }

            .price-table .tables-rj {
                margin: 0;
            }

            .price-table .table-vj {
                margin-bottom: 60px;
                border: 4px solid #817d7d4a;
            }

            .price-table .table-vj:hover {
                transform: scale(1);
            }
        }

        @media (min-width:768px) and (max-width:1024px) {
            .price-table {
                width: 100%;
                padding: 0;
                margin: 0;
            }

            .price-table .tables-rj {
                margin: 0;
            }

            .price-table .services {
                line-height: 15px;
            }

            .vijju .price-table .sec1-head span {
                margin: 0;
            }

            .vijju .price-table .sec1-head {
                padding-bottom: 20px;
            }
        }
    </style>
</head>

<body>
    <div id="container">
        <?php include('inc.header.php'); ?>
        <div class="vijju">
            <section class="price-table">
                <div class="container">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="sec1-head">
                                <span>Packages</span>
                            </div>
                        </div>
                    </div>

                    <?php if (!empty($_SESSION['error'])): ?>
                        <div class="alert alert-danger"><?= e($_SESSION['error']) ?></div>
                        <?php $_SESSION['error'] = ''; ?>
                    <?php endif; ?>

                    <div class="tables-rj">
                        <div class="row">
                            <?php
                            $i = 0;
                            $aryDetail = db_get_rows("SELECT * FROM package WHERE status != 0 ORDER BY id ASC");
                            foreach ($aryDetail as $ilist):
                                $i++;
                                $bgColor = '';
                                if ($i == 1) {
                                    $bgColor = '#ebb313';
                                } elseif ($i == 2) {
                                    $bgColor = '#96285a';
                                } elseif ($i == 3) {
                                    $bgColor = '#35818b';
                                } else {
                                    $bgColor = '#ebb313';
                                }
                            ?>
                                <div class="col-md-4 col-sm-4 col-xs-12">
                                    <div class="table-vj" style="text-align:center;">
                                        <div class="<?php if ($i == '1') {
                                                        echo "basic";
                                                    } elseif ($i == '2') {
                                                        echo "standard-";
                                                    } elseif ($i == '3') {
                                                        echo "premium";
                                                    } else {
                                                        echo "basic";
                                                    } ?>">
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <h3><?= e($ilist['title'] ?? 'Unknown Plan') ?></h3>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="<?php if ($i == '1') {
                                                        echo "basic";
                                                    } elseif ($i == '2') {
                                                        echo "standard";
                                                    } elseif ($i == '3') {
                                                        echo "premium";
                                                    } else {
                                                        echo "basic";
                                                    } ?>-price">
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <h1>₦ <?= number_format((float)($ilist['price'] ?? 0), 2) ?></h1>
                                                    <p></p>
                                                </div>
                                            </div>
                                        </div>
                                        <hr>
                                        <div class="services">
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <?php if (isset($ilist['create_custom_forms']) && $ilist['create_custom_forms'] == '1'): ?>
                                                        <p>Create custom forms</p>
                                                    <?php endif; ?>
                                                    <?php if (isset($ilist['report_templates']) && $ilist['report_templates'] == '1'): ?>
                                                        <p>Report Templates</p>
                                                    <?php endif; ?>
                                                    <?php if (isset($ilist['online_and_bank_payment']) && $ilist['online_and_bank_payment'] == '1'): ?>
                                                        <p>Online Bank Payment</p>
                                                    <?php endif; ?>
                                                    <?php if (isset($ilist['dashboard']) && $ilist['dashboard'] == '1'): ?>
                                                        <p>Dashboard</p>
                                                    <?php endif; ?>
                                                    <?php if (isset($ilist['exam_feature']) && $ilist['exam_feature'] == '1'): ?>
                                                        <p>Exam Feature</p>
                                                    <?php endif; ?>
                                                    <?php if (isset($ilist['sms_alert']) && $ilist['sms_alert'] == '1'): ?>
                                                        <p>Sms Alert</p>
                                                    <?php endif; ?>
                                                    <?php if (isset($ilist['email_notification']) && $ilist['email_notification'] == '1'): ?>
                                                        <p>Email Notification</p>
                                                    <?php endif; ?>
                                                    <?php if (isset($ilist['document_upload']) && $ilist['document_upload'] == '1'): ?>
                                                        <p>Document Upload</p>
                                                    <?php endif; ?>
                                                    <?php if (isset($ilist['sms_campaigns']) && $ilist['sms_campaigns'] == '1'): ?>
                                                        <p>Sms campaigns</p>
                                                    <?php endif; ?>
                                                    <?php if (isset($ilist['email_campaigns']) && $ilist['email_campaigns'] == '1'): ?>
                                                        <p>Email Campaigns</p>
                                                    <?php endif; ?>
                                                    <?php if (isset($ilist['report_and_data_export']) && $ilist['report_and_data_export'] != ''): ?>
                                                        <p>Report and data export (<?= e($ilist['report_and_data_export']) ?>)</p>
                                                    <?php endif; ?>
                                                    <?php if (isset($ilist['attendance_module']) && $ilist['attendance_module'] == '1'): ?>
                                                        <p>Attendance Module</p>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="sing-up-1">
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <form action="" method="post">
                                                        <input type="hidden" name="plan_id" value="<?= (int)($ilist['id'] ?? 0) ?>">
                                                        <button style="background-color:<?= $bgColor ?>" type="submit" name="pakages">Buy Now</button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </section>
        </div>
        <?php include('inc.footer.php'); ?>
    </div>
    <?php include('inc.js.php'); ?>
</body>

</html>