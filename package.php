<?php
require_once('config.php');

if (!isset($_SESSION['userid'])) {
  redirect(SITE_URL . 'login.php');
}

// Get user details using PDO helper
$iLoginUserDetail = db_get_row("SELECT * FROM school_register WHERE id = ?", [$_SESSION['userid']]);
$create_by_usertype = is_array($iLoginUserDetail) ? $iLoginUserDetail['create_by_usertype'] : '';

if (is_array($iLoginUserDetail) && $iLoginUserDetail['create_by_userid'] == '0') {
  $create_by_userid = $_SESSION['userid'];
} else {
  $create_by_userid = is_array($iLoginUserDetail) ? $iLoginUserDetail['create_by_userid'] : $_SESSION['userid'];
}

if (isset($_POST['addnewrecord'])) {
  try {
    // Get package details using PDO
    $ipackagePlan = db_get_row("SELECT * FROM package WHERE id = ?", [$_POST['plan_id']]);

    if (empty($ipackagePlan)) {
      $_SESSION['error'] = "Invalid package selected.";
      redirect(SITE_URL . 'package.php');
      exit;
    }

    $iSuccessToken = randomFix(28);

    $NewArray = array(
      'create_custom_forms' => isset($_POST['create_custom_forms']) ? (int)$_POST['create_custom_forms'] : 0,
      'report_templates' => isset($_POST['report_templates']) ? (int)$_POST['report_templates'] : 0,
      'online_and_bank_payment' => isset($_POST['online_and_bank_payment']) ? (int)$_POST['online_and_bank_payment'] : 0,
      'dashboard' => isset($_POST['dashboard']) ? (int)$_POST['dashboard'] : 0,
      'exam_feature' => isset($_POST['exam_feature']) ? (int)$_POST['exam_feature'] : 0,
      'sms_alert' => isset($_POST['sms_alert']) ? (int)$_POST['sms_alert'] : 0,
      'email_notification' => isset($_POST['email_notification']) ? (int)$_POST['email_notification'] : 0,
      'document_upload' => isset($_POST['document_upload']) ? (int)$_POST['document_upload'] : 0,
    );
    $myJSON = json_encode($NewArray);

    // Determine price and days based on term selection
    if (isset($_POST['yearly_terms']) && $_POST['yearly_terms'] == '1') {
      $iPricePackage = $ipackagePlan['price_yearly'];
      $iNoDaysPackage = $ipackagePlan['days_yearly'];
    } else {
      $iPricePackage = $ipackagePlan['price_term'];
      $iNoDaysPackage = $ipackagePlan['days_term'];
    }

    // Calculate expiration date
    $Date = date('Y-m-d');
    $iExpDaTe = date('Y-m-d', strtotime($Date . " + " . $iNoDaysPackage . " days"));

    // Prepare data for insertion
    $aryData = array(
      'plan_id' => $ipackagePlan['id'],
      'plan_name' => $ipackagePlan['title'],
      'price' => $iPricePackage,
      'no_of_days' => $iNoDaysPackage,
      'file_allow' => $myJSON,
      'exp_date' => $iExpDaTe,
      'status' => 0,
      'usertype' => 0,
      'success_token' => $iSuccessToken,
      'cancel_token' => randomFix(28),
      'create_at' => date("Y-m-d H:i:s"),
      'userid' => $create_by_userid,
    );

    // Insert using PDO helper
    $flgIn = db_insert("school_purchased_pacakage", $aryData);

    if ($flgIn !== false) {
      redirect(SITE_URL . 'package_vogupay.php?token=' . $iSuccessToken);
    } else {
      $_SESSION['error'] = "Failed to process package selection. Please try again.";
      redirect(SITE_URL . 'package.php');
    }
  } catch (PDOException $e) {
    error_log("Package selection error: " . $e->getMessage());
    $_SESSION['error'] = "A database error occurred. Please try again later.";
    redirect(SITE_URL . 'package.php');
  }
  exit;
}

// Get all packages using PDO
try {
  $aryList = db_get_rows("SELECT * FROM package ORDER BY id ASC");
  if (empty($aryList)) {
    $aryList = array();
  }
} catch (PDOException $e) {
  $aryList = array();
  error_log("Failed to load packages: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html>

<head>
  <title>Package Purchase</title>
  <?php include('inc.meta-new.php'); ?>
  <link rel='stylesheet' href="<?php echo SITE_URL; ?>css/abhi.css" type='text/css' media='all' />
  <script>
    function yearlytermswise(getid) {
      $(".yearly_terms").val(getid);

      if (getid == '1') {
        $("#btul2").removeClass('yrltrmwse');
        $("#btul1").addClass('yrltrmwse');
        $(".pricesyearly").css('display', 'block');
        $(".pricesterms").css('display', 'none');
      } else {
        $("#btul1").removeClass('yrltrmwse');
        $("#btul2").addClass('yrltrmwse');
        $(".pricesterms").css('display', 'block');
        $(".pricesyearly").css('display', 'none');
      }
    }
  </script>
  <style>
    .yrltrmwse {
      background: #acf3eb !important;
      border: 1px solid #acf3eb !important;
    }

    .abhii-3.pricing-table-1 .pricing {
      display: block;
      justify-content: center;
      width: 100%;
      margin: auto;
      padding: 10%;
    }

    .abhii-3.pricing-table-1 .pricing-palden .pricing-price {
      font-size: 3em;
    }

    @media only screen and (max-width: 768px) {
      .abhii-3.pricing-table-1 .pricing {
        padding: 5%;
      }
    }
  </style>
</head>

<body class="home page-template page-template-tpl-home page-template-tpl-home-php page page-id-14">
  <div id="page" class="site">
    <?php include('inc.header-new.php'); ?>
    <div id="content" class="site-content">
      <section class="abhii-2">
        <div class="container-fluid">
          <div class="row">
            <div class="col-md-12">
              <h1>FIND A PLAN THAT'S RIGHT FOR YOU.</h1>
            </div>
          </div>
        </div>
      </section>

      <?php if (isset($_SESSION['error']) && !empty($_SESSION['error'])): ?>
        <div class="container">
          <div class="alert alert-danger" style="margin-top: 20px;">
            <?= e($_SESSION['error']) ?>
          </div>
        </div>
        <?php unset($_SESSION['error']); ?>
      <?php endif; ?>

      <?php if (isset($_SESSION['success']) && !empty($_SESSION['success'])): ?>
        <div class="container">
          <div class="alert alert-success" style="margin-top: 20px;">
            <?= e($_SESSION['success']) ?>
          </div>
        </div>
        <?php unset($_SESSION['success']); ?>
      <?php endif; ?>

      <section class="abhii-3 pricing-table-1">
        <div style="text-align: center;">
          <input type="button" id="btul1" value="Yearly" onClick="yearlytermswise('1');" style="width:120px; height:40px; font-size:15px;background: #f9f9f9;" class="btn btn-default yrltrmwse">
          <input type="button" id="btul2" value="Terms Wise" onClick="yearlytermswise('2');" style="width:120px; height:40px; font-size:15px;background: #f9f9f9;" class="btn btn-default">
        </div>
        <br><br>
        <div class="container">
          <div class="row">
            <div class="col-md-12 col-sm-12 col-xs-12">
              <div class="row">
                <?php
                $i = 0;
                foreach ($aryList as $iList):
                  $i++;
                ?>
                  <div class="col-md-4 col-sm-12 col-xs-4">
                    <div class="pricing pricing-palden">
                      <form action="" method="post">
                        <input type="hidden" value="1" name="yearly_terms" class="yearly_terms">
                        <div class="pricing-item <?= ($i == '2') ? 'pricing__item--featured' : ''; ?>">
                          <div class="pricing-deco" style="background: rgba(0, 0, 0, 0) url(image/pricing1.jpeg) center bottom no-repeat; -webkit-background-size: cover; -moz-background-size: cover; -o-background-size: cover; background-size: cover;">
                            <div class="pricing-price pricesyearly">
                              <span class="pricing-currency">&#8358;</span><?= e($iList['price_yearly']); ?>
                            </div>
                            <div class="pricing-price pricesterms" style="display:none;">
                              <span class="pricing-currency">&#8358;</span><?= e($iList['price_term']); ?>
                            </div>
                            <h3 class="pricing-title"><?= e($iList['title']); ?></h3>
                          </div>
                          <ul class="pricing-feature-list">
                            <li class="pricing-feature">
                              <?php if ($iList['create_custom_forms'] == "1"): ?>
                                <i class="fa fa-check-square-o" aria-hidden="true"></i>
                              <?php else: ?>
                                <i class="fa fa-times" aria-hidden="true"></i>
                              <?php endif; ?>
                              <input type="hidden" name="create_custom_forms" value="<?= e($iList['create_custom_forms']); ?>">
                              Create custom forms
                            </li>
                            <li class="pricing-feature">
                              <?php if ($iList['report_templates'] == "1"): ?>
                                <i class="fa fa-check-square-o" aria-hidden="true"></i>
                              <?php else: ?>
                                <i class="fa fa-times" aria-hidden="true"></i>
                              <?php endif; ?>
                              <input type="hidden" name="report_templates" value="<?= e($iList['report_templates']); ?>">
                              Report templates
                            </li>
                            <li class="pricing-feature">
                              <?php if ($iList['online_and_bank_payment'] == "1"): ?>
                                <i class="fa fa-check-square-o" aria-hidden="true"></i>
                              <?php else: ?>
                                <i class="fa fa-times" aria-hidden="true"></i>
                              <?php endif; ?>
                              <input type="hidden" name="online_and_bank_payment" value="<?= e($iList['online_and_bank_payment']); ?>">
                              Online and Bank Payment
                            </li>
                            <li class="pricing-feature">
                              <?php if ($iList['dashboard'] == "1"): ?>
                                <i class="fa fa-check-square-o" aria-hidden="true"></i>
                              <?php else: ?>
                                <i class="fa fa-times" aria-hidden="true"></i>
                              <?php endif; ?>
                              <input type="hidden" name="dashboard" value="<?= e($iList['dashboard']); ?>">
                              Dashboard
                            </li>
                            <li class="pricing-feature">
                              <?php if ($iList['exam_feature'] == "1"): ?>
                                <i class="fa fa-check-square-o" aria-hidden="true"></i>
                              <?php else: ?>
                                <i class="fa fa-times" aria-hidden="true"></i>
                              <?php endif; ?>
                              <input type="hidden" name="exam_feature" value="<?= e($iList['exam_feature']); ?>">
                              Exam Feature
                            </li>
                            <li class="pricing-feature">
                              <?php if ($iList['sms_alert'] == "1"): ?>
                                <i class="fa fa-check-square-o" aria-hidden="true"></i>
                              <?php else: ?>
                                <i class="fa fa-times" aria-hidden="true"></i>
                              <?php endif; ?>
                              <input type="hidden" name="sms_alert" value="<?= e($iList['sms_alert']); ?>">
                              SMS Alerts
                            </li>
                            <li class="pricing-feature">
                              <?php if ($iList['email_notification'] == "1"): ?>
                                <i class="fa fa-check-square-o" aria-hidden="true"></i>
                              <?php else: ?>
                                <i class="fa fa-times" aria-hidden="true"></i>
                              <?php endif; ?>
                              <input type="hidden" name="email_notification" value="<?= e($iList['email_notification']); ?>">
                              E-mail Notifications
                            </li>
                            <li class="pricing-feature">
                              <?php if ($iList['document_upload'] == "1"): ?>
                                <i class="fa fa-check-square-o" aria-hidden="true"></i>
                              <?php else: ?>
                                <i class="fa fa-times" aria-hidden="true"></i>
                              <?php endif; ?>
                              <input type="hidden" name="document_upload" value="<?= e($iList['document_upload']); ?>">
                              Document Upload
                            </li>
                          </ul>
                          <input type="hidden" value="<?= e($iList['id']); ?>" name="plan_id">
                          <input type="submit" class="pricing-action sonu-button-2534 sb_add_cart" name="addnewrecord" value="Choose plan">
                        </div>
                      </form>
                    </div>
                  </div>
                <?php endforeach; ?>
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