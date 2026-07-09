<?php  include('config.php');?>
<html>
<head>
<?php include('inc.meta-new.php');	?>
<style type="text/css">
.chovi .accordion {
	padding: 10px;
	width: 96.5%;
	text-align: left;
	font-size: 15px;
	background-color: #f5f5f5;
	border-color: #ddd;
	box-shadow: 0 1px 1px rgba(0, 0, 0, .05);
	margin-top: 17px;
	font-size: 16px;
	color: inherit;
	margin-left: 20px;
}
.chovi .active, .accordion:hover {
	background-color: #f5f5f5 !important;
}
.chovi .accordion.active {
	background-color: #1b3058 !important;
	color: #fff;
}
.chovi .faq-area {
	background-color:#fdfdfd;
	padding: 46px 0 44px 0;
}
.chovi .accordion:after {
	content: '\002B';
	color: #777;
	font-weight: bold;
	float: right;
	margin-left: 5px;
}
.chovi .active:after {
	content: '\002B';
	color: white;
	font-weight: bold;
	float: right;
	margin-left: 5px;
}
.chovi .active:after {
	content: "\2212";
}
.chovi .accordion:active {
	background-color: #fff;
}
.chovi .panel {
	padding: 0 18px;
	background-color: white;
	max-height: 0;
	overflow: hidden;
	transition: max-height 0.2s ease-out;
	margin-left: 22px;
	   
}
.chovi .accord {
	padding: 0 0 0 450px;
}
.chovi .accord {
	padding: 0 0 0 33px;
}
.chovi .accordion {
	padding: 10px;
	width: 96.5%;
	text-align: left;
	font-size: 15px;
	background-color: #f5f5f5;
	border-color: #ddd;
	box-shadow: 0 1px 1px rgba(0, 0, 0, .05);
	margin-top: 17px;
	font-size: 16px;
	color: inherit;
	margin-left: 6px;
}
.chovi .panel {
	padding: 0 18px;
	background-color: white;
	max-height: 0;
	overflow: hidden;
	transition: max-height 0.2s ease-out;
	margin-left: 6px;
	width: 100%;
}
.active> {
	display: block;
}

</style>
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
              <h2 class="accord">FAQ</h2>
              <?php
						 $aryList=$db->getRows("select * from faq   ");
								 foreach($aryList as $iList)
										{	$i=$i+1;
										$aryPgAct["id"]=$iList['id'];
								 ?>
              <button class="accordion"><?php echo $iList['question'];?></button>
              <div class="panel">
                <p style=" margin-top: 16px;"><?php echo $iList['answer'];?></p>
              </div>
              <?php } ?>
            </div>
          </div>
        </div>
      </section>
    </div>
  </div>
  <?php include('inc.footer-new.php');	?>
</div>
<?php include('inc.js-new.php');	?>
<script>
var acc = document.getElementsByClassName("accordion");
var i;

for (i = 0; i < acc.length; i++) {
  acc[i].addEventListener("click", function() {
    this.classList.toggle("active");
    var panel = this.nextElementSibling;
    if (panel.style.maxHeight){
      panel.style.maxHeight = null;
    } else {
      panel.style.maxHeight = panel.scrollHeight + "px";
    } 
  });
}
</script>
</body>
</html>
