<?php include('config.php');

if (!isset($_SESSION['userid'])) {
  redirect(SITE_URL . 'login.php');
}

$iLoginUserDetail = $db->getRow("select * from school_register where id='" . $_SESSION['userid'] . "'");
$create_by_usertype = is_array($iLoginUserDetail) ? ($iLoginUserDetail['create_by_usertype'] ?? '') : '';
$loginCreateByUserId = is_array($iLoginUserDetail) ? (string)($iLoginUserDetail['create_by_userid'] ?? '0') : '0';
if ($loginCreateByUserId === '0') {
  $create_by_userid = $_SESSION['userid'];
} else {
  $create_by_userid = $loginCreateByUserId;
}

if (isset($_POST['addnewrecord'])) {


  $ipackagePlan = $db->getRow("select * from package where id = '" . $_POST['plan_id'] . "'");
  $iSuccessToken = randomFix(28);

  $NewArray = array(
    'create_custom_forms'              =>  $_POST['create_custom_forms'],
    'report_templates'                =>  $_POST['report_templates'],
    'online_and_bank_payment'            =>  $_POST['online_and_bank_payment'],
    'dashboard'                    =>  $_POST['dashboard'],
    'exam_feature'                  =>  $_POST['exam_feature'],
    'sms_alert'                    =>  $_POST['sms_alert'],
    'email_notification'              =>  $_POST['email_notification'],
    'document_upload'                =>  $_POST['document_upload'],
    /*'online_adverts'								=>	$_POST['online_adverts'],
        'sms_campaigns'									=>	$_POST['sms_campaigns'],
        'email_campaigns'								=>	$_POST['email_campaigns'],*/
  );
  $myJSON = json_encode($NewArray);



  if ($_POST['yearly_terms'] == '1') {
    $iPricePackage = $ipackagePlan['price_yearly'];
    $iNoDaysPackage = $ipackagePlan['days_yearly'];
  } else {

    $iPricePackage = $ipackagePlan['price_term'];
    $iNoDaysPackage = $ipackagePlan['days_term'];
  }

  $Date = date('Y-m-d');
  $iExpDaTe =  date('Y-m-d', strtotime($Date . " + $iNoDaysPackage days"));


  $aryData = array(
    'plan_id'            =>  $ipackagePlan['id'],
    'plan_name'            =>  $ipackagePlan['title'],
    'price'              =>  $iPricePackage,
    'no_of_days'          =>  $iNoDaysPackage,
    'file_allow'          =>  $myJSON,
    'exp_date'          =>  $iExpDaTe,
    'status'            =>  0,
    'usertype'            =>  0,
    'success_token'          =>  $iSuccessToken,
    'cancel_token'          =>  randomFix(28),
    'create_at'            =>  date("Y-m-d H:i:s"),
    'userid'            =>  $create_by_userid,
  );
  $flgIn = $db->insertAry("school_purchased_pacakage", $aryData);
  redirect(SITE_URL . 'package_vogupay.php?token=' . $iSuccessToken);
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
    }

    .feature-list {
      list-style: none;
      margin: 0;
      padding: 0;
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
    }

    .choose-btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 22px rgba(79, 70, 229, 0.35);
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
          <div class="text-center mb-8">
            <div class="switcher" role="group" aria-label="Pricing period switcher">
              <button type="button" id="btul1" class="switcher-btn active" onclick="yearlytermswise('1');">Yearly</button>
              <button type="button" id="btul2" class="switcher-btn" onclick="yearlytermswise('2');">Terms Wise</button>
            </div>
          </div>

          <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php
            $i = 0;
            $aryList = $db->getRows("select * from package");
            foreach ($aryList as $iList) {
              $i = $i + 1;
            ?>
              <div>
                <form action="" method="post" class="h-full">
                  <input type="hidden" value="1" name="yearly_terms" class="yearly_terms">
                  <div class="plan-card <?php if ($i == '2') {
                                          echo 'plan-featured';
                                        } ?>">
                    <div class="plan-head">
                      <div class="plan-price pricesyearly"><span class="plan-currency">&#8358;</span><?php echo $iList['price_yearly']; ?></div>
                      <div class="plan-price pricesterms" style="display:none;"><span class="plan-currency">&#8358;</span><?php echo $iList['price_term']; ?></div>
                      <h3 class="plan-title"><?php echo $iList['title']; ?></h3>
                    </div>

                    <div class="plan-body">
                      <ul class="feature-list">
                        <li class="feature-item">
                          <?php if ($iList['create_custom_forms'] == "1") { ?>
                            <i class="fas fa-circle-check icon-yes" aria-hidden="true"></i>
                          <?php } else { ?>
                            <i class="fas fa-circle-xmark icon-no" aria-hidden="true"></i>
                          <?php } ?>
                          <input type="hidden" name="create_custom_forms" value="<?php echo $iList['create_custom_forms']; ?>">
                          <span>Create custom forms</span>
                        </li>

                        <li class="feature-item">
                          <?php if ($iList['report_templates'] == "1") { ?>
                            <i class="fas fa-circle-check icon-yes" aria-hidden="true"></i>
                          <?php } else { ?>
                            <i class="fas fa-circle-xmark icon-no" aria-hidden="true"></i>
                          <?php } ?>
                          <input type="hidden" name="report_templates" value="<?php echo $iList['report_templates']; ?>">
                          <span>Report templates</span>
                        </li>

                        <li class="feature-item">
                          <?php if ($iList['online_and_bank_payment'] == "1") { ?>
                            <i class="fas fa-circle-check icon-yes" aria-hidden="true"></i>
                          <?php } else { ?>
                            <i class="fas fa-circle-xmark icon-no" aria-hidden="true"></i>
                          <?php } ?>
                          <input type="hidden" name="online_and_bank_payment" value="<?php echo $iList['online_and_bank_payment']; ?>">
                          <span>Online and Bank Payment</span>
                        </li>

                        <li class="feature-item">
                          <?php if ($iList['dashboard'] == "1") { ?>
                            <i class="fas fa-circle-check icon-yes" aria-hidden="true"></i>
                          <?php } else { ?>
                            <i class="fas fa-circle-xmark icon-no" aria-hidden="true"></i>
                          <?php } ?>
                          <input type="hidden" name="dashboard" value="<?php echo $iList['dashboard']; ?>">
                          <span>Dashboard</span>
                        </li>

                        <li class="feature-item">
                          <?php if ($iList['exam_feature'] == "1") { ?>
                            <i class="fas fa-circle-check icon-yes" aria-hidden="true"></i>
                          <?php } else { ?>
                            <i class="fas fa-circle-xmark icon-no" aria-hidden="true"></i>
                          <?php } ?>
                          <input type="hidden" name="exam_feature" value="<?php echo $iList['exam_feature']; ?>">
                          <span>Exam Feature</span>
                        </li>

                        <li class="feature-item">
                          <?php if ($iList['sms_alert'] == "1") { ?>
                            <i class="fas fa-circle-check icon-yes" aria-hidden="true"></i>
                          <?php } else { ?>
                            <i class="fas fa-circle-xmark icon-no" aria-hidden="true"></i>
                          <?php } ?>
                          <input type="hidden" name="sms_alert" value="<?php echo $iList['sms_alert']; ?>">
                          <span>SMS Alerts</span>
                        </li>

                        <li class="feature-item">
                          <?php if ($iList['email_notification'] == "1") { ?>
                            <i class="fas fa-circle-check icon-yes" aria-hidden="true"></i>
                          <?php } else { ?>
                            <i class="fas fa-circle-xmark icon-no" aria-hidden="true"></i>
                          <?php } ?>
                          <input type="hidden" name="email_notification" value="<?php echo $iList['email_notification']; ?>">
                          <span>E-mail Notifications</span>
                        </li>

                        <li class="feature-item">
                          <?php if ($iList['document_upload'] == "1") { ?>
                            <i class="fas fa-circle-check icon-yes" aria-hidden="true"></i>
                          <?php } else { ?>
                            <i class="fas fa-circle-xmark icon-no" aria-hidden="true"></i>
                          <?php } ?>
                          <input type="hidden" name="document_upload" value="<?php echo $iList['document_upload']; ?>">
                          <span>Document Upload</span>
                        </li>
                      </ul>

                      <input type="hidden" value="<?php echo $iList['id']; ?>" name="plan_id">
                      <input type="submit" class="choose-btn" name="addnewrecord" value="Choose Plan">
                    </div>
                  </div>
                </form>
              </div>
            <?php } ?>
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