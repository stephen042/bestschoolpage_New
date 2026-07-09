<?php  include('config.php');
$iCMSPageDetailsPageDes=$db->getRow("select * from why_choose_us where  pageurl = '".$_GET['auothid']."' order by id desc");

?>
<html>
<head>
<?php include('inc.meta-new.php');	?>
</head>
<body class="home page-template page-template-tpl-home page-template-tpl-home-php page page-id-14">
<div id="page" class="site">
  <?php include('inc.header-new.php');	?>
  <div id="content" class="site-content">
    <div class="chovi">
      <section class="faq-area">
        <div class="container">
          <div class="row">
            <div class="col-md-12">
              <h2 class="accord"><?php echo $iCMSPageDetailsPageDes['title']; ?></h2>
           
          
              <div class="panel">
                <p style=" margin-top: 16px;">
                <?php if($iCMSPageDetailsPageDes['image']!='') { ?>
                <img src="<?php echo SITE_URL; ?>uploads/<?php echo $iCMSPageDetailsPageDes['image']; ?>" style="width:100%" >
                <?php } ?>
                </p>
                <p> <?php echo $iCMSPageDetailsPageDes['description']; ?></p>
              </div>
    
            </div>
          </div>
        </div>
      </section>
    </div>
  </div>
  <?php include('inc.footer-new.php');	?>
</div>
<?php include('inc.js-new.php');	?>
</body>
</html>
