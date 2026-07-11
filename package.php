<?php
// ============================================================================
// PACKAGE SELECTION PAGE
// ============================================================================
// This file handles package selection and purchase processing
// ============================================================================

include('config.php');

// Check if user is logged in
if (!isset($_SESSION['userid']) || empty($_SESSION['userid'])) {
  redirect(SITE_URL . 'login.php');
}

// Get current user details using PDO
$iLoginUserDetail = db_get_row(
  "SELECT * FROM school_register WHERE id = ?",
  [$_SESSION['userid']]
);

// Determine user type and creator ID
$create_by_usertype = is_array($iLoginUserDetail) ? ($iLoginUserDetail['create_by_usertype'] ?? '') : '';
$loginCreateByUserId = is_array($iLoginUserDetail) ? (string)($iLoginUserDetail['create_by_userid'] ?? '0') : '0';

if ($loginCreateByUserId === '0') {
  $create_by_userid = (int)$_SESSION['userid'];
} else {
  $create_by_userid = (int)$loginCreateByUserId;
}

// ============================================================================
// PROCESS PURCHASE FORM SUBMISSION
// ============================================================================
if (isset($_POST['addnewrecord'])) {
  // Validate required fields
  $plan_id = isset($_POST['plan_id']) ? (int)$_POST['plan_id'] : 0;
  $yearly_terms = isset($_POST['yearly_terms']) ? (int)$_POST['yearly_terms'] : 1;

  if ($plan_id <= 0) {
    $_SESSION['error'] = 'Invalid plan selected. Please try again.';
    redirect(SITE_URL . 'package.php');
    exit;
  }

  // Get plan details using PDO
  $ipackagePlan = db_get_row(
    "SELECT * FROM package WHERE id = ?",
    [$plan_id]
  );

  // Check if plan exists
  if (empty($ipackagePlan)) {
    $_SESSION['error'] = 'Selected plan not found. Please try again.';
    redirect(SITE_URL . 'package.php');
    exit;
  }

  // Generate unique tokens
  $iSuccessToken = randomFix(28);
  $iCancelToken = randomFix(28);

  // Build features JSON array
  $featureArray = array(
    'create_custom_forms'   => isset($_POST['create_custom_forms']) ? (int)$_POST['create_custom_forms'] : 0,
    'report_templates'      => isset($_POST['report_templates']) ? (int)$_POST['report_templates'] : 0,
    'online_and_bank_payment' => isset($_POST['online_and_bank_payment']) ? (int)$_POST['online_and_bank_payment'] : 0,
    'dashboard'             => isset($_POST['dashboard']) ? (int)$_POST['dashboard'] : 0,
    'exam_feature'          => isset($_POST['exam_feature']) ? (int)$_POST['exam_feature'] : 0,
    'sms_alert'             => isset($_POST['sms_alert']) ? (int)$_POST['sms_alert'] : 0,
    'email_notification'    => isset($_POST['email_notification']) ? (int)$_POST['email_notification'] : 0,
    'document_upload'       => isset($_POST['document_upload']) ? (int)$_POST['document_upload'] : 0
  );
  $myJSON = json_encode($featureArray);

  // Determine price and duration based on yearly or term selection
  if ($yearly_terms == 1) {
    $iPricePackage = (float)($ipackagePlan['price_yearly'] ?? 0);
    $iNoDaysPackage = (int)($ipackagePlan['days_yearly'] ?? 365);
  } else {
    $iPricePackage = (float)($ipackagePlan['price_term'] ?? 0);
    $iNoDaysPackage = (int)($ipackagePlan['days_term'] ?? 90);
  }

  // Calculate expiration date
  $currentDate = date('Y-m-d');
  $iExpDate = date('Y-m-d', strtotime($currentDate . " + {$iNoDaysPackage} days"));

  // Prepare purchase data
  $purchaseData = array(
    'plan_id'           => (int)$ipackagePlan['id'],
    'plan_name'         => (string)($ipackagePlan['title'] ?? 'Unknown Plan'),
    'price'             => $iPricePackage,
    'no_of_days'        => $iNoDaysPackage,
    'file_allow'        => $myJSON,
    'exp_date'          => $iExpDate,
    'status'            => 0,
    'usertype'          => 0,
    'success_token'     => $iSuccessToken,
    'cancel_token'      => $iCancelToken,
    'create_at'         => date("Y-m-d H:i:s"),
    'userid'            => $create_by_userid
  );

  // Insert purchase record using PDO
  try {
    $flgIn = db_insert("school_purchased_pacakage", $purchaseData);

    if ($flgIn) {
      // Redirect to payment page with token
      redirect(SITE_URL . 'package_vogupay.php?token=' . $iSuccessToken);
    } else {
      $_SESSION['error'] = 'Failed to process your purchase. Please try again.';
      redirect(SITE_URL . 'package.php');
    }
  } catch (Exception $e) {
    $_SESSION['error'] = 'An error occurred while processing your request.';
    redirect(SITE_URL . 'package.php');
  }

  exit;
}

// ============================================================================
// GET AVAILABLE PACKAGES
// ============================================================================
try {
  $aryList = db_get_rows("SELECT * FROM package ORDER BY id ASC");
} catch (Exception $e) {
  $aryList = array();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <title>Pricing Plans - Best School Page</title>
  <?php include('inc.meta-new.php'); ?>
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

    .pricing-hero {
      position: relative;
      min-height: 44vh;
      display: flex;
      align-items: center;
      background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #0f172a 100%);
      overflow: hidden;
      margin-bottom: 80px;
    }

    .pricing-hero::before {
      content: '';
      position: absolute;
      inset: 0;
      background: url('images/home-slider.png') center/cover no-repeat;
      opacity: 0.2;
    }

    .pricing-hero-overlay {
      position: absolute;
      inset: 0;
      background: linear-gradient(135deg,
          rgba(15, 23, 42, 0.92) 0%,
          rgba(30, 41, 59, 0.85) 50%,
          rgba(15, 23, 42, 0.92) 100%);
      z-index: 1;
    }

    .pricing-hero-content {
      position: relative;
      z-index: 2;
    }

    .pricing-wrap {
      margin-top: -70px;
      margin-bottom: 80px;
      position: relative;
      z-index: 3;
    }

    .switcher {
      display: inline-flex;
      gap: 8px;
      background: #e2e8f0;
      padding: 6px;
      border-radius: 999px;
    }

    .switcher-btn {
      border: none;
      border-radius: 999px;
      padding: 10px 18px;
      background: transparent;
      color: #334155;
      font-weight: 700;
      transition: all 0.25s ease;
      cursor: pointer;
    }

    .switcher-btn.active {
      background: linear-gradient(135deg, #4f46e5 0%, #6366f1 100%);
      color: #fff;
      box-shadow: 0 6px 18px rgba(79, 70, 229, 0.35);
    }

    .plan-card {
      background: rgba(255, 255, 255, 0.97);
      border: 1px solid #e2e8f0;
      border-radius: 20px;
      overflow: hidden;
      box-shadow: 0 10px 28px rgba(2, 6, 23, 0.08);
      transition: all 0.3s ease;
      height: 100%;
      display: flex;
      flex-direction: column;
    }

    .plan-card:hover {
      transform: translateY(-6px);
      box-shadow: 0 20px 40px rgba(2, 6, 23, 0.14);
    }

    .plan-featured {
      border-color: #818cf8;
      box-shadow: 0 20px 45px rgba(79, 70, 229, 0.2);
    }

    .plan-head {
      padding: 24px;
      background: linear-gradient(145deg, #eef2ff 0%, #ffffff 100%);
      border-bottom: 1px solid #e2e8f0;
    }

    .plan-price {
      font-size: 2.1rem;
      line-height: 1;
      font-weight: 800;
      color: #111827;
    }

    .plan-currency {
      font-size: 1.2rem;
      vertical-align: top;
    }

    .plan-title {
      margin-top: 10px;
      font-size: 1.2rem;
      font-weight: 800;
      color: #1e293b;
    }

    .plan-body {
      padding: 20px 24px 24px;
      flex: 1;
      display: flex;
      flex-direction: column;
    }

    .feature-list {
      list-style: none;
      margin: 0;
      padding: 0;
      flex: 1;
    }

    .feature-item {
      display: flex;
      align-items: flex-start;
      gap: 10px;
      font-size: 0.96rem;
      color: #334155;
      padding: 9px 0;
      border-bottom: 1px dashed #e2e8f0;
    }

    .feature-item:last-child {
      border-bottom: 0;
    }

    .icon-yes {
      color: #16a34a;
      margin-top: 2px;
    }

    .icon-no {
      color: #ef4444;
      margin-top: 2px;
    }

    .choose-btn {
      width: 100%;
      border: 0;
      border-radius: 12px;
      background: linear-gradient(135deg, #4f46e5 0%, #6366f1 100%);
      color: #fff;
      font-weight: 700;
      padding: 12px 14px;
      margin-top: 16px;
      transition: all 0.3s ease;
      cursor: pointer;
    }

    .choose-btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 22px rgba(79, 70, 229, 0.35);
    }

    /* Responsive */
    @media (max-width: 1024px) {
      .pricing-wrap {
        margin-top: -55px;
      }
    }

    @media (max-width: 768px) {
      .pricing-wrap {
        margin-top: -45px;
        margin-bottom: 55px;
      }

      .plan-head,
      .plan-body {
        padding: 18px;
      }

      .plan-price {
        font-size: 1.6rem;
      }
    }

    @media (max-width: 480px) {
      .pricing-wrap {
        margin-top: -35px;
        margin-bottom: 40px;
      }

      .plan-head {
        padding: 14px;
      }

      .plan-body {
        padding: 14px;
      }

      .feature-item {
        font-size: 0.85rem;
        padding: 7px 0;
      }

      .choose-btn {
        padding: 10px 12px;
        font-size: 0.9rem;
      }
    }
  </style>
</head>

<body class="home page-template page-template-tpl-home page-template-tpl-home-php page page-id-14">
  <div id="page" class="site">
    <?php include('inc.header-new.php'); ?>
    <div id="content" class="site-content">
      <section class="pricing-hero">
        <div class="pricing-hero-overlay"></div>
        <div class="container mx-auto px-4 pricing-hero-content">
          <div class="max-w-4xl mx-auto text-center">
            <span class="inline-flex items-center gap-2 px-4 py-1.5 bg-indigo-500/20 text-indigo-300 text-sm font-semibold rounded-full border border-indigo-500/20 mb-5">
              <i class="fas fa-layer-group"></i>
              Flexible Plans
            </span>
            <h1 class="text-white text-4xl md:text-5xl font-extrabold mb-3">Find A Plan That Is Right For You</h1>
            <p class="text-slate-300">Switch between yearly and term pricing, then choose the package that best fits your school.</p>
          </div>
        </div>
      </section>

      <section class="pricing-wrap">
        <div class="container mx-auto px-4">
          <?php if (!empty($_SESSION['error'])): ?>
            <div class="max-w-3xl mx-auto mb-6">
              <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
                <?php echo e($_SESSION['error']); ?>
              </div>
            </div>
            <?php $_SESSION['error'] = ''; ?>
          <?php endif; ?>

          <?php if (!empty($_SESSION['success'])): ?>
            <div class="max-w-3xl mx-auto mb-6">
              <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg">
                <?php echo e($_SESSION['success']); ?>
              </div>
            </div>
            <?php $_SESSION['success'] = ''; ?>
          <?php endif; ?>

          <div class="text-center mb-8">
            <div class="switcher" role="group" aria-label="Pricing period switcher">
              <button type="button" id="btul1" class="switcher-btn active" onclick="yearlytermswise('1');">Yearly</button>
              <button type="button" id="btul2" class="switcher-btn" onclick="yearlytermswise('2');">Terms Wise</button>
            </div>
          </div>

          <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php if (!empty($aryList)):
              $planCounter = 0;
              foreach ($aryList as $iList):
                $planCounter++;
                $isFeatured = ($planCounter == 2);
            ?>
                <div>
                  <form action="" method="post" class="h-full">
                    <input type="hidden" value="1" name="yearly_terms" class="yearly_terms">
                    <div class="plan-card <?php echo $isFeatured ? 'plan-featured' : ''; ?>">
                      <div class="plan-head">
                        <div class="plan-price pricesyearly"><span class="plan-currency">&#8358;</span><?php echo number_format((float)($iList['price_yearly'] ?? 0), 2); ?></div>
                        <div class="plan-price pricesterms" style="display:none;"><span class="plan-currency">&#8358;</span><?php echo number_format((float)($iList['price_term'] ?? 0), 2); ?></div>
                        <h3 class="plan-title"><?php echo e($iList['title'] ?? 'Untitled Plan'); ?></h3>
                      </div>

                      <div class="plan-body">
                        <ul class="feature-list">
                          <?php
                          $features = array(
                            'create_custom_forms' => 'Create custom forms',
                            'report_templates' => 'Report templates',
                            'online_and_bank_payment' => 'Online and Bank Payment',
                            'dashboard' => 'Dashboard',
                            'exam_feature' => 'Exam Feature',
                            'sms_alert' => 'SMS Alerts',
                            'email_notification' => 'E-mail Notifications',
                            'document_upload' => 'Document Upload'
                          );
                          foreach ($features as $field => $label):
                            $value = isset($iList[$field]) ? (int)$iList[$field] : 0;
                          ?>
                            <li class="feature-item">
                              <?php if ($value == 1): ?>
                                <i class="fas fa-circle-check icon-yes" aria-hidden="true"></i>
                              <?php else: ?>
                                <i class="fas fa-circle-xmark icon-no" aria-hidden="true"></i>
                              <?php endif; ?>
                              <input type="hidden" name="<?php echo $field; ?>" value="<?php echo $value; ?>">
                              <span><?php echo $label; ?></span>
                            </li>
                          <?php endforeach; ?>
                        </ul>

                        <input type="hidden" value="<?php echo (int)($iList['id'] ?? 0); ?>" name="plan_id">
                        <button type="submit" class="choose-btn" name="addnewrecord" value="1">Choose Plan</button>
                      </div>
                    </div>
                  </form>
                </div>
              <?php
              endforeach;
            else:
              ?>
              <div class="col-span-full text-center py-12">
                <p class="text-slate-500 text-lg">No packages available at the moment. Please check back later.</p>
              </div>
            <?php endif; ?>
          </div>
        </div>
      </section>
    </div>
    <?php include('inc.footer-new.php'); ?>
  </div>
  <?php include('inc.js-new.php'); ?>
  <script>
    function yearlytermswise(getid) {
      var yearlyInput = document.querySelectorAll('.yearly_terms');
      var yearlyPrices = document.querySelectorAll('.pricesyearly');
      var termPrices = document.querySelectorAll('.pricesterms');
      var yearlyBtn = document.getElementById('btul1');
      var termBtn = document.getElementById('btul2');

      for (var i = 0; i < yearlyInput.length; i++) {
        yearlyInput[i].value = getid;
      }

      if (getid === '1') {
        if (yearlyBtn) yearlyBtn.classList.add('active');
        if (termBtn) termBtn.classList.remove('active');

        for (var y = 0; y < yearlyPrices.length; y++) {
          yearlyPrices[y].style.display = 'block';
        }
        for (var t = 0; t < termPrices.length; t++) {
          termPrices[t].style.display = 'none';
        }
      } else {
        if (yearlyBtn) yearlyBtn.classList.remove('active');
        if (termBtn) termBtn.classList.add('active');

        for (var y2 = 0; y2 < yearlyPrices.length; y2++) {
          yearlyPrices[y2].style.display = 'none';
        }
        for (var t2 = 0; t2 < termPrices.length; t2++) {
          termPrices[t2].style.display = 'block';
        }
      }
    }

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