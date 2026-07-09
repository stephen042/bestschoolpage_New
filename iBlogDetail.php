<?php include('config.php');
$iBlogDetailsForThis=$db->getRow("select * from  school_blog where pageurl='".$_GET['auothid']."'");
?>
<html>
<head>
<?php include('inc.meta-new.php');	?>
</head>
<body class="home page-template page-template-tpl-home page-template-tpl-home-php page page-id-14">
<div id="page" class="site">
  <?php include('inc.header-new.php');	?>
  <div id="content" class="site-content">
  <?php include('inc.header.php'); ?>
  <div class="call">
    <div class="call-a">
      <div class="container">
        <div class="row">
          <div class="us">
          <br><br>
            <h2><?php echo $iBlogDetailsForThis['title']; ?></h2>
            <hr>
          </div>
        </div>
      </div>
    </div>
    <div class="call-b">
        <div class="container"> 
          <div class="row">
            <div class="col-md-12">
              <div class="sum">
                <div class="row">
                   <?php echo $iBlogDetailsForThis['description']; ?>
                    <br><br> <br><br>
                </div>
              </div>
            </div>
          </div>
        </div>
    </div>
  </div>
    </div>
  <?php include('inc.footer-new.php');	?>
</div>
<?php include('inc.js-new.php');	?>
</body>
</html>
