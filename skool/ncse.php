<?php include('config.php'); 

$iLoginUserDetail=$db->getRow("select * from school_register where pageurl='".$_GET['pageurl']."'");

$create_by_usertype = $iLoginUserDetail['create_by_usertype'];

	$create_by_userid = $iLoginUserDetail['create_by_userid'];


$iAboutSchool=$db->getRow("select * from school_about where create_by_userid='".$create_by_userid."'");
$iupdatedetails = $db->getRow("select * from  skool_settings where create_by_userid='".$create_by_userid."'"); 

?>
<!DOCTYPE html>
<html lang="en-US" prefix="#">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
 

    <title>Eduction Website</title>
	<script>window._wca = window._wca || [];</script>


<!-- This site is optimized with the Yoast SEO plugin v9.2.1 - https://yoast.com/wordpress/plugins/seo/ -->
<meta name="description" content="Academica Pro 3.0 is a modern, flexible and responsive WordPress theme. Great for education websites – such as universities, colleges."/>
<link rel="canonical" href="index.html" />
<meta property="og:locale" content="en_US" />
<meta property="og:type" content="website" />
<meta property="og:title" content="Academica Pro 3.0 - Education WordPress Theme" />
<meta property="og:description" content="Academica Pro 3.0 is a modern, flexible and responsive WordPress theme. Great for education websites – such as universities, colleges." />
<meta property="og:url" content="https://demo.wpzoom.com/academica-pro-3/" />
<meta property="og:site_name" content="Academica Pro 3.0" />
<meta property="og:image" content="https://demo.wpzoom.com/academica-pro-3/files/2017/06/academica30.png" />
<meta property="og:image:secure_url" content="https://demo.wpzoom.com/academica-pro-3/files/2017/06/academica30.png" />
<meta property="og:image:width" content="1200" />
<meta property="og:image:height" content="840" />
<meta name="twitter:card" content="summary" />
<meta name="twitter:description" content="Academica Pro 3.0 is a modern, flexible and responsive WordPress theme. Great for education websites – such as universities, colleges." />
<meta name="twitter:title" content="Academica Pro 3.0 - Education WordPress Theme" />
<meta name="twitter:image" content="https://demo.wpzoom.com/academica-pro-3/files/2017/06/academica30.png" />
<meta name="twitter:creator" content="@wpzoom" />
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
<style>
.mySlides {display:none;}
</style>
<link rel='dns-prefetch' href='http://s0.wp.com/' />
<link rel='dns-prefetch' href='http://secure.gravatar.com/' />
<link rel='dns-prefetch' href='http://fonts.googleapis.com/' />
<link rel='dns-prefetch' href='http://s.w.org/' />
<link rel="stylesheet"
href="http://www.landmarkmlp.com/js-plugin/owl.carousel/owl-carousel/owl.carousel.css">

 <!-- Default Theme -->
<link rel="stylesheet"
href="http://www.landmarkmlp.com/js-plugin/owl.carousel/owl-carousel/owl.theme.css">
<link rel="alternate" type="application/rss+xml" title="Academica Pro 3.0 &raquo; Feed" href="feed/index.html" />
<link rel="alternate" type="application/rss+xml" title="Academica Pro 3.0 &raquo; Comments Feed" href="comments/feed/index.html" />
<link rel="alternate" type="text/calendar" title="Academica Pro 3.0 &raquo; iCal Feed" href="events/indexedf3.html?ical=1" />
<style type="text/css">
img.wp-smiley,
img.emoji {
	display: inline !important;
	border: none !important;
	box-shadow: none !important;
	height: 1em !important;
	width: 1em !important;
	margin: 0 .07em !important;
	vertical-align: -0.1em !important;
	background: none !important;
	padding: 0 !important;
}
</style>
<link href="css/validationEngine.jquery.css" rel="stylesheet">
<link href="css/widget.css" rel="stylesheet">
<link href="css/sidebar-login.css" rel="stylesheet">
<link href="css/woocommerce-layout.css" rel="stylesheet">
<style id='woocommerce-layout-inline-css' type='text/css'>
.infinite-scroll .woocommerce-pagination {
	display: none;
}
</style>
<link rel="stylesheet" href="css/woocommerce-smallscreen.css" type="text/css" media="only screen and (max-width: 768px)" />
<link rel="stylesheet" href="css/woocommerce.css" type='text/css' media='all' />
<style id='woocommerce-inline-inline-css' type="text/css">
.woocommerce form .form-row .required { visibility: visible; }
</style>
<link rel='stylesheet' href="css/theme-utils.css" type='text/css' media='all' />
<link rel='stylesheet' href='http://fonts.googleapis.com/css?family=Roboto%3Aregular%2Citalic%2C100%2C300%2C500%2C700%2C900%7CLibre+Baskerville%3Aregular%2Citalic%2C700%26subset%3Dlatin%2C&amp;ver=4.9.9' type='text/css' media='all' />
<link rel='stylesheet' href="css/style.css" type='text/css' media='all' />
<link rel='stylesheet' href="css/media-queries.css" type='text/css' media='all' />
<link rel='stylesheet' id='academica-google-font-default-css'  href='http://fonts.googleapis.com/css?family=Libre+Baskerville%3A400%2C400i%2C700%7CRoboto%3A400%2C400i%2C500%2C500i%2C700%2C700i&amp;subset=cyrillic%2Clatin-ext&amp;ver=4.9.9' type='text/css' media='all' />
<link rel='stylesheet' href="css/dashicons.min.css" type='text/css' media='all' />
<link rel='stylesheet' href="css/frontend-grid.css" type='text/css' media='all' />
<link rel='stylesheet' href="css/frontend.css" type='text/css' media='all' />
<link rel='stylesheet' href="css/instagram-widget.css" type='text/css' media='all' />
<link rel='stylesheet' href="css/socicon.css" type='text/css' media='all' />
<link rel='stylesheet' href="css/genericons.css" type='text/css' media='all' />
<link rel='stylesheet' href="css/font-awesome.min.css" type='text/css' media='all' />
<link rel='stylesheet' href="css/wzslider.css" type='text/css' media='all' />
<link rel='stylesheet' href="css/shortcodes.css" type='text/css' media='all' />
<link rel='stylesheet' href="css/font-awesome.minn.css" type='text/css' media='all' />
<link rel='stylesheet' href="css/custom.css" type='text/css' media='all' />
<link rel='stylesheet' href="css/abhi.css" type='text/css' media='all' />
<link rel='stylesheet' href="css/nces-css.css" type='text/css' media='all' />
<link href="css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<link rel='stylesheet' href="css/ytprefs.min.css" type='text/css' media='all' />
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.2.1/css/bootstrap.min.css">
<style id='__EPYT__style-inline-css' type='text/css'>

                .epyt-gallery-thumb {
                        width: 33.333%;
                }
.carousel-inner img {
      width: 100%;
      height: 100%;
  }				
                
</style>
<link rel='stylesheet' href="css/jetpack.css" type='text/css' media='all' />
<script type='text/javascript' src="js/jquery.js"></script>
<script type='text/javascript' src="js/jquery-migrate.min.js"></script>
<script type='text/javascript' src="js/login-with-ajax.js"></script>
<script type='text/javascript' src="js/jquery.blockUI.min.js"></script>
<script type='text/javascript' src="js/sidebar-login.min.js"></script>
<script type='text/javascript' src="js/affwp-external-referral-links.min.js"></script>
<script type='text/javascript' src="js/init.js"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.6/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.2.1/js/bootstrap.min.js"></script>
<script type='text/javascript' src="js/instagram-widget.js"></script>

<script type='text/javascript' src="js/ytprefs.min.js"></script>


<link rel="wlwmanifest" type="application/wlwmanifest+xml" href="wp-includes/wlwmanifest.xml" /> 
<meta name="generator" content="WordPress 4.9.9" />
<meta name="generator" content="WooCommerce 3.5.2" />
<link rel='shortlink' href='index.html' />
<link rel="alternate" type="application/json+oembed" href="wp-json/oembed/1.0/embedb58a.json?url=https%3A%2F%2Fdemo.wpzoom.com%2Facademica-pro-3%2F" />
<link rel="alternate" type="text/xml+oembed" href="wp-json/oembed/1.0/embed61c2?url=https%3A%2F%2Fdemo.wpzoom.com%2Facademica-pro-3%2F&amp;format=xml" />
<meta name="tec-api-version" content="v1"><meta name="tec-api-origin" content="https://demo.wpzoom.com/academica-pro-3"><link rel="https://theeventscalendar.com/" href="wp-json/tribe/events/v1/index.html" />
<link rel='dns-prefetch' href='http://v0.wordpress.com/'/>
<!-- WPZOOM Theme / Framework -->
<meta name="generator" content="Academica Pro 3.0 3.0.0" />
<meta name="generator" content="WPZOOM Framework 1.8.3" />
<noscript><style>.woocommerce-product-gallery{ opacity: 1 !important; }</style></noscript>
<style>
.milestone-widget {
	margin-bottom: 1em;
}
.milestone-content {
	line-height: 2;
	margin-top: 5px;
	max-width: 100%;
	padding: 0;
	text-align: center;
}
.milestone-header {
	background-color: #333333;
	color: #ffffff;
	line-height: 1.3;
	margin: 0;
	padding: .8em;
}
.milestone-header .event,
.milestone-header .date {
	display: block;
}
.milestone-header .event {
	font-size: 120%;
}
.milestone-countdown .difference {
	display: block;
	font-size: 500%;
	font-weight: bold;
	line-height: 1.2;
}
.milestone-countdown,
.milestone-message {
	background-color: #ffffff;
	border: 1px solid #cccccc;
	border-top: 0;
	color: #333333;
	padding-bottom: 1em;
}
.milestone-message {
	padding-top: 1em
}
.slider .flex-control-paging {

    display: none;
}
.flex-direction-nav {
    list-style: none;
}
</style>
<script type='text/javascript' src="js/s-201903.js"></script>
<script src="http://flexslider.woothemes.com/js/modernizr.js"></script>
</head>
<body data-rsssl=1 class="home page-template page-template-page-templates page-template-home page-template-page-templateshome-php page page-id-5780 woocommerce-no-js tribe-no-js">
<div id="container">
	<?php include('inc.header.php'); ?>
	<div class="container">
	<div class="dld">
	<div id="container" class="cf">
		<div id="main" role="main">
		<section class="slider">
			<div class="flexslider carousel">
				<ul class="slides">
				<?php
				$iSlider=$db->getRows("select * from school_slider where create_by_userid='".$create_by_userid."'");
				foreach($iSlider as $iList)
				{ $i=$i+1;
				?>
					<li>
						<img src="<?php echo SITE_URL; ?>uploads/<?php echo $iList['image']; ?>">
					</li>
				<?php } ?>	
				</ul>
			</div>
		</section>
		</div>
	</div>
	<div id="MainContent">
		<div id="ContentArea_base" tabindex="-1">
			<div id="ctl00_PlaceHolderMain_DisplayModePanel">
				<div class="naep-panel naep-image-slider-panel merge-bottom">
					<div class="panel-body">
						<div class="carousel flexslider">
							<div class="shadow-box">&nbsp;</div>
						</div>
					</div>
				</div>
				<div class="naep-panel first-row-attractors">
					<div class="first-row-attractor"> 
						<a href="#" class="row-attractor-title">pay school fees </a> 
					</div>
					<div class="first-row-attractor"> 
						<a href="#" class="row-attractor-title">Apply for admission</a> 
					</div>
					<div class="first-row-attractor"> 
						<a href="<?php echo PARENT_URL; ?>" class="row-attractor-title"> Check result </a> 
					</div>
					<div class="first-row-attractor"> 
						<a href="<?php echo PARENT_URL; ?>" class="row-attractor-title"> Check admission </a> 
					</div>
				</div>
				<div class="naep-panel split-panel">
					<div class="abhi-jee">
						<div class="abhii-j">
						<?php if($iLoginUserDetail['logo']=='') { ?>
							<img src="image/black-wheat-and-mortarboard.png">
						<?php } else { ?>
							<img src="<?php echo SITE_URL; ?>uploads/<?php echo $iLoginUserDetail['logo']; ?>">	
						<?php } ?>		
						</div>
						<div class="gtg">
							<div class="heas"><h2>Contact Details</h2></div>
							<div class="gghu"><p><?php echo $iLoginUserDetail['name']; ?></p></div>
							<div class="ggh"><p><?php echo $iLoginUserDetail['location']; ?></p></div>
							<div class="maill">
								<p><i class="fa fa-envelope" aria-hidden="true"></i><?php echo $iLoginUserDetail['email']; ?></p>
							</div>
							<div class="maill">
								<p><i class="fa fa-phone" aria-hidden="true"></i><?php echo $iLoginUserDetail['contact_no']; ?></p>
							</div>
							<div class="2tn"><button><a href="#">Apply Now</a></button></div>
						</div>
					</div>
<div class="panel-body naep-icon-link-panel">
<div class="panel-body">
<div class="whats-new-header"><h2>PDF Download</h2>
<div class="subscribe-newsflash"> 
<a href="https://ies.ed.gov/newsflash/#nces">
<span>Subscribe to NewsFlash</span>
</a>
 </div>
 </div>
<div class="icon-link pdf-no-size" >
<div class="link-icon report-release"><i class="fa fa-graduation-cap" aria-hidden="true"></i></div> 
<div class="link-text"> 
<span>School syllabus</span>
<a href="uploads/<?php echo $iupdatedetails['skool_syllabus']; ?>" download >

<span>PDF Download </span>
</a>
 </div>
</div>
<div class="icon-link">
<div class="link-icon naep-highlight"><i class="fa fa-book" aria-hidden="true"></i></div> 
<div class="link-text"> 
<span>Learning material for all classes</span>
<a class="icon-link " href="uploads/<?php echo $iupdatedetails['learning_materials']; ?>" class="col-lg-2" download >
<span>PDF Download</span>
</a>
 </div>
</div>

<div class="icon-link external"  >
<div class="link-icon naep-highlight"><i class="fa fa-table" aria-hidden="true"></i></div> 
<div class="link-text"> 
<span>Exam timetable</span>
<a  href="uploads/<?php echo $iupdatedetails['exam_timetable']; ?>" class="col-lg-2" download >
<span>PDF Download</span>
</a>
 </div>
</div>

<div class="icon-link pdf-no-size">
<div class="link-icon naep-highlight"><i class="fa fa-cloud-download" aria-hidden="true"></i></div> 
<div class="link-text"> 
<span>Download exam pass questions </span>
<a  href="uploads/<?php echo $iupdatedetails['exam_pass_question']; ?>" class="col-lg-2" download >
PDF Download
</a>
 </div>
 </div>
 </div>
</div>
</div>
</div>

		<div class="kfg">
			<div class="abhi-k">
				<div class="naep-panel">
					<div class="headinggg"><h2>Gallery</h2></div>
					<div class="owl-carousel">
					<?php
					$iGallery=$db->getRows("select * from school_gallery where create_by_userid='".$create_by_userid."'");
					foreach($iGallery as $iList)
					{ $i=$i+1;
						?>
						<div><img src="<?php echo SITE_URL; ?>uploads/<?php echo $iList['image']; ?>"></div>
					<?php } ?>	
					</div>
				</div>
			</div>
		</div>

		<div class="kfg hfh">
			<div class="naep-panel">
				<div class="container">
					<div class="row">
						<div class="col-md-7">
							<div class="abhi-k hgi">
								<div class="headinggg"><h2>About School</h2></div>
								<div> <img src="<?php echo SITE_URL; ?>uploads/<?php echo $iAboutSchool['about_image']; ?>"> </div>
								<div class="para">
									<p><?php echo $iAboutSchool['about_description']; ?></p>
								</div>
							</div>
						</div>
						<div class="col-md-5">
							<div class="headinggg"><h2>Upcoming Event</h2></div>
							<div class="hgii">
							<?php
							$iUpcomingEvent=$db->getRows("select * from school_upcoming_event where create_by_userid='".$create_by_userid."'");
							foreach($iUpcomingEvent as $iList)
							{ $i=$i+1;
								?>
								<div class="evnt">
									<div class="img">
										<img src="<?php echo SITE_URL; ?>uploads/<?php echo $iList['image']; ?>">
									</div>
									<div class="head">
										<span><?php echo $iList['title']; ?></span>
										<p><?php echo $iList['description']; ?></p>
									</div>
									<div class="date"><p><?php echo $iList['date']; ?></p></div>
								</div>
							<?php } ?>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>

		<div class="kfg">
			<div class="naep-panel">
				<div class="container">
					<div class="row">
						<div class="col-md-6 fgd">
							<div class="panel-body what-is-naep">
								<div class="panel-body">
									<div class="lkj">
										<h2>What is NAEP?</h2>
										<p>The National Assessment of Educational Progress (NAEP) is the largest nationally representative and continuing assessment of what America's students know and can do in various subject areas.</p> 
										<a class="learn-more" href="/nationsreportcard/about/">Learn More</a> 
									</div>
									<div>
										<h2 class="kiuj">Promo Video</h2>
										<div class="video-asset"> 
											<iframe src="<?php echo $iAboutSchool['promo_video']; ?>" width="500" height="300" youtube="" video="" player="" id="">
												<p>Your browser does not support iFrame.</p>
											</iframe>
										</div>
									</div>
								</div>
							</div>
						</div>
						<!--<div class="col-md-6">
							<div class="lkj">
								<h2>What is NAEP?</h2>
								<p>The National Assessment of Educational Progress (NAEP) is the largest nationally representative and continuing assessment of what America's students know and can do in various subject areas.</p> 
								<a class="learn-more" href="/nationsreportcard/about/">Learn More</a> 
							</div>
							<div class="kjh">
								<h3>Reminder</h3>
								<hr>
								<div class="fhy">
									<div class="headin">
										<h4>Summer Vacation<br><span>June1, 2019</span></h4>
									</div>
									<div class="dohu">
										<h1>4</br><span>months to go</span></h1>
									</div>
								</div>
							</div>
						</div>-->
					</div>
				</div>
			</div>
		</div>
			
		<div class="naep-panel second-row-attractors">
			<div class="panel-body">
			<?php
			$iBlogList=$db->getRows("select * from school_blog where create_by_userid='".$create_by_userid."'");
			foreach($iBlogList as $iList)
			{ 
			?>
				<div class="second-row-attractor"> 
					<img src="<?php echo SITE_URL; ?>uploads/<?php echo $iList['image']; ?>" alt=""> 
					<h2 class="center"><?php echo $iList['title']; ?></h2>
					<p><?php echo $iList['short_description']; ?></p> 
					<a class="learn-more center" href="#">Learn More</a> 
				</div>
			<?php } ?>
			</div>
		</div>
		<div class="naep-panel image-attractors right">
			<div class="panel-body"> 
				<img src="<?php echo SITE_URL; ?>uploads/<?php echo $iAboutSchool['image']; ?>" alt=""> 
				<h2><?php echo $iAboutSchool['title']; ?></h2>
				<p><?php echo $iAboutSchool['short_description']; ?></p> 
				<a class="learn-more" href="#">Learn More</a> 
			</div>
		</div>
		<div class="naep-panel image-attractors left">
			<div class="panel-body"> 
				<img src="<?php echo SITE_URL; ?>uploads/<?php echo $iAboutSchool['image_1']; ?>" alt=""> 
				<h2><?php echo $iAboutSchool['title_1']; ?></h2>
				<p><?php echo $iAboutSchool['short_description_1']; ?></p> 
				<a class="learn-more" href="#">Learn More</a> 
			</div>
		</div>
		<div class="naep-panel naep-video-gallery">
		<h2>NAEP Videos</h2>
		 				<!-- end clip -->
						
							<div class="span12">
							
<div class="main">



<h2>gallery</h2>


<!-- Portfolio Gallery Grid -->
<div class="row">
  <div class="column">
    <div class="content">
      <img src="http://www.svrpublicschool.com/images/building.jpg" alt="Mountains" style="width:100%">
      <h3>About us (school)</h3>
      <p>Lorem ipsum dolor sit amet, tempor prodesset eos no. Temporibus necessitatibus sea ei, at tantas oporteat nam. Lorem ipsum dolor sit amet, tempor prodesset eos no.</p>
    </div>
  </div>
  <div class="column">
    <div class="content">
    <img src="http://www.svrpublicschool.com/images/building.jpg" alt="Lights" style="width:100%">
      <h3>Upcoming event</h3>
      <p>Lorem ipsum dolor sit amet, tempor prodesset eos no. Temporibus necessitatibus sea ei, at tantas oporteat nam. Lorem ipsum dolor sit amet, tempor prodesset eos no.</p>
    </div>
  </div>
  <div class="column">
    <div class="content">
    <img src="http://www.svrpublicschool.com/images/building.jpg" alt="Nature" style="width:100%">
      <h3>School logo</h3>
      <p>Lorem ipsum dolor sit amet, tempor prodesset eos no. Temporibus necessitatibus sea ei, at tantas oporteat nam. Lorem ipsum dolor sit amet, tempor prodesset eos no.</p>
    </div>
  </div>
  <div class="column">
    <div class="content">
    <img src="http://www.svrpublicschool.com/images/building.jpg" alt="Mountains" style="width:100%">
      <h3> school vacatio</h3>
      <p>Lorem ipsum dolor sit amet, tempor prodesset eos no. Temporibus necessitatibus sea ei, at tantas oporteat nam. Lorem ipsum dolor sit amet, tempor prodesset eos no.</p>
    </div>
  </div>
<!-- END GRID -->
</div>

</div>
		
		</div>

						<!--END CONTENT-->
							<div class="pageupdated"><hr noshade="" size="1"><span>Last updated 14 January 2019 (DS)</span></div>
                                        
			    
</div>

			</div> <!-- ContentArea -->	

		</div>


</div>
</div>
</div>
  
  </div>
  
  
<?php include('inc.footer.php'); ?>

</div><!-- end #container -->



<div class="wpzoom-style-picker no_display closed" style="display: block;">

    <div class="content">

        <h2 class="picker-title">Homepage Template</h2>


        <ul>
            <li>
                <div>

                    <p><strong>Academica Pro 3.0</strong> includes <strong>4</strong> Page Templates for Home page.</p>

                    <label class="style-option">
                        <a href="index.html" class="active"><img src="../../www.wpzoom.com/wp-content/uploads/2017/06/default.png" alt="Slider Top"><span>Default</span></a>

                    </label>

                    <label class="style-option">
                        <a href="homepage-2/index.html"><img src="../../www.wpzoom.com/wp-content/uploads/2017/06/top.png" alt="Slider Top"><span>Slider at the Top</span></a>

                    </label>

                    <label class="style-option">
                        <a href="homepage-2-2/index.html"><img src="../../www.wpzoom.com/wp-content/uploads/2017/06/full.png" alt="Slider Top"><span>Full-width Slider</span></a>

                    </label>


                    <label class="style-option">
                        <a href="homepage-3/index.html"><img src="../../www.wpzoom.com/wp-content/uploads/2017/06/middle.png" alt="Slider Top"><span>Slider in the Middle</span></a>

                    </label>


                </div>
            </li>
        </ul>


    </div>

    <div class="close-button">
        <a href="#">
         </a>
    </div>


    <style>



    .wpzoom-style-picker{
        box-shadow: 0 2px 32px 10px rgba(0,0,0,.14);
        position: fixed;
        top: 0;
        bottom: 0;
        left: 0px;
        height: 100vh;
        z-index: 999;
        background: #F4F6F8;
        transition: all 0.3s ease 0s;
        border-right: 3px solid #193058;
    }

    .wpzoom-style-picker.closed {
        left: -290px;
        box-shadow: none;
    }


    .wpzoom-style-picker .content{

        height: 100%;
        overflow-y: auto;
        position: relative;
        z-index: 1;
        width: 280px; }


    .wpzoom-style-picker .picker-title{margin: 0 0 15px; background: #193058; padding: 8px 15px; font-weight: 600; color: #fff; text-align: center;  text-transform: none; font-size: 16px;}



    .wpzoom-style-picker .content ul {
        margin: 0;
    }

    @-moz-keyframes bounce {
      0%, 20%, 50%, 80%, 100% {
        -moz-transform: translateX(0);
        transform: translateX(0);
      }
      40% {
        -moz-transform: translateX(10px);
        transform: translateX(100px);
      }
      60% {
        -moz-transform: translateX(5px);
        transform: translateX(5px);
      }
    }
    @-webkit-keyframes bounce {
      0%, 20%, 50%, 80%, 100% {
        -webkit-transform: translateX(0);
        transform: translateX(0);
      }
      40% {
        -webkit-transform: translateX(10px);
        transform: translateX(10px);
      }
      60% {
        -webkit-transform: translateX(5px);
        transform: translateX(5px);
      }
    }
    @keyframes bounce {
      0%, 20%, 50%, 80%, 100% {
        -moz-transform: translateX(0);
        -ms-transform: translateX(0);
        -webkit-transform: translateX(0);
        transform: translateX(0);
      }
      40% {
        -moz-transform: translateX(10px);
        -ms-transform: translateX(10px);
        -webkit-transform: translateX(10px);
        transform: translateX(10px);
      }
      60% {
        -moz-transform: translateX(5px);
        -ms-transform: translateX(5px);
        -webkit-transform: translateX(5px);
        transform: translateX(5px);
      }
    }

    .wpzoom-style-picker.closed .close-button{
        -moz-animation: bounce 2s infinite;
        -webkit-animation: bounce 2s infinite;
        animation: bounce 2s infinite;
        padding-left: 10px;
        width: 50px;
        right: -49px;
    }

    .wpzoom-style-picker .close-button {
        border-radius: 0 4px 4px 0;
        background: #193058;
        overflow: hidden;
        width: 40px;
        height: 44px;
        padding: 4px 0;
        text-align: center;
        position: absolute;
        right: -43px;
        top: 88px;
  }

    .wpzoom-style-picker .close-button a{
        display: inline-block;
        width: 20px;
         text-align: center;
         color: #fff;
         font-family: 'dashicons';
         speak: none;
         font-style: normal;
         font-size: 22px;
         font-weight: normal;
         font-variant: normal;
         text-transform: none;
         -webkit-font-smoothing: antialiased;
         text-decoration: none;
    }

    .wpzoom-style-picker.closed .close-button a {
        -webkit-animation: fa-spin 2s infinite linear;
        animation: fa-spin 2s infinite linear;
    }

    @keyframes fa-spin {
      0% {
        -webkit-transform: rotate(0deg);
        transform: rotate(0deg);
      }
      100% {
        -webkit-transform: rotate(359deg);
        transform: rotate(359deg);
      }
    }
    .wpzoom-style-picker .close-button a:before {
        content: "\f335";
    }
    .wpzoom-style-picker.closed .close-button a:before {
        content: "\f111";
     }

    .wpzoom-style-picker li{display: block; clear: both; overflow: hidden; padding: 15px 30px; text-align: center; }
    .wpzoom-style-picker li p { text-align: left; margin-bottom: 20px; font-size: 14px; }
    .wpzoom-style-picker li .setting-title{clear: both;  margin: 10px 0; color: #2E75AF; font-size: 14px; font-weight: 600;  }

    .wpzoom-style-picker li label.style-option{  float: left; margin-bottom: 20px; cursor: pointer; transition: all .15s ease-in-out;}

    .wpzoom-style-picker li label { transition: all .2s ease;}

    .wpzoom-style-picker li label select{clear: both;}


    .wpzoom-style-picker li label img {
        border: solid 2px #c0cdd6;
        border-radius: 3px;
        background: #fefefe;
    }

    .wpzoom-style-picker li label.active img {
        border-color: #3173b2;
    }

    .wpzoom-style-picker li label.active span {
        background: #3173b2;
    }
    .wpzoom-style-picker li label span {
        margin: 6px auto 0;
        font-size: 14px;
        padding: 1px 15px;
        text-align: center;
        border-radius: 20px;
        background: #8FB2C9;
        color: #fff;
        font-weight: 600;
        display: inline-block;
    }


    .wpzoom-style-picker .content img {
        max-width: 100%;
        height: auto;
    }

    .wpzoom-style-picker .content a:hover img {
        border-color: #3173b2;
    }

    .wpzoom-style-picker .content label a:hover span {
        background:#3173b2;
    }

    .wpzoom-style-picker .content a.active img {
        border-color: #3173b2;
    }

    .wpzoom-style-picker .content label a.active  span  {
        background: #3173b2;
    }


    @media only screen and (max-width: 768px){
        .wpzoom-style-picker{display: none !important;}
    }

    </style>


    <script>

    jQuery(document).ready(function(){
        jQuery(".wpzoom-style-picker").fadeIn();

        jQuery(".wpzoom-style-picker .close-button").bind("click", function(e){
            jQuery(".wpzoom-style-picker").toggleClass("closed");
            e.preventDefault();
        });

    });

    </script>
</div>



  
  
  
  
  
  <!-- jQuery -->
  <script src="http://ajax.googleapis.com/ajax/libs/jquery/1/jquery.min.js"></script>
  <script>window.jQuery || document.write('<script src="js/libs/jquery-1.7.min.js">\x3C/script>')</script>

  <!-- FlexSlider -->
  <script defer src="http://flexslider.woothemes.com/js/jquery.flexslider.js"></script>

  <script type="text/javascript">
    $(function(){
      SyntaxHighlighter.all();
    });
    $(window).load(function(){
      $('.flexslider').flexslider({
        animation: "slide",
        animationLoop: false,
        item: 1,
        pausePlay: true,
        start: function(slider){
          $('body').removeClass('loading');
        }
      });
    });
  </script>

<script>
$(document).ready(function() {
 
  $("#owl-demo").owlCarousel({
 
      autoPlay: 3000, //Set AutoPlay to 3 seconds
 
      items : 4,
      itemsDesktop : [1199,3],
      itemsDesktopSmall : [979,3]
 
  });
 
});
</script>
  <!-- Syntax Highlighter -->

 


  <!-- Optional FlexSlider Additions -->
  <!-- Global site tag (gtag.js) - Google Analytics -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=UA-3078969-5"></script>
    <script>
      window.dataLayer = window.dataLayer || [];
      function gtag(){dataLayer.push(arguments);}
      gtag('js', new Date());

      gtag('config', 'UA-3078969-5');
      gtag('config', 'AW-1029774828');
    </script>


    		<script>
		( function ( body ) {
			'use strict';
			body.className = body.className.replace( /\btribe-no-js\b/, 'tribe-js' );
		} )( document.body );
		</script>
			<div style="display:none">
	</div>
	
<script> /* <![CDATA[ */var tribe_l10n_datatables = {"aria":{"sort_ascending":": activate to sort column ascending","sort_descending":": activate to sort column descending"},"length_menu":"Show _MENU_ entries","empty_table":"No data available in table","info":"Showing _START_ to _END_ of _TOTAL_ entries","info_empty":"Showing 0 to 0 of 0 entries","info_filtered":"(filtered from _MAX_ total entries)","zero_records":"No matching records found","search":"Search:","all_selected_text":"All items on this page were selected. ","select_all_link":"Select all pages","clear_selection":"Clear Selection.","pagination":{"all":"All","next":"Next","previous":"Previous"},"select":{"rows":{"0":"","_":": Selected %d rows","1":": Selected 1 row"}},"datepicker":{"dayNames":["Sunday","Monday","Tuesday","Wednesday","Thursday","Friday","Saturday"],"dayNamesShort":["Sun","Mon","Tue","Wed","Thu","Fri","Sat"],"dayNamesMin":["S","M","T","W","T","F","S"],"monthNames":["January","February","March","April","May","June","July","August","September","October","November","December"],"monthNamesShort":["January","February","March","April","May","June","July","August","September","October","November","December"],"monthNamesMin":["Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec"],"nextText":"Next","prevText":"Prev","currentText":"Today","closeText":"Done","today":"Today","clear":"Clear"}};var tribe_system_info = {"sysinfo_optin_nonce":"6dc846bcdb","clipboard_btn_text":"Copy to clipboard","clipboard_copied_text":"System info copied","clipboard_fail_text":"Press \"Cmd + C\" to copy"};/* ]]> */ </script><script>(function($){$(document).ready(function(){});})(jQuery);</script>	<script type="text/javascript">
		var c = document.body.className;
		c = c.replace(/woocommerce-no-js/, 'woocommerce-js');
		document.body.className = c;
	</script>

	<!--[if lte IE 8]>
<link rel='stylesheet' id='jetpack-carousel-ie8fix-css'  href='https://demo.wpzoom.com/academica-pro-3/wp-content/plugins/jetpack/modules/carousel/jetpack-carousel-ie8fix.css?ver=20121024' type='text/css' media='all' />
<![endif]-->

<script type='text/javascript' src="js/devicepx-jetpack.js"></script>

<script type='text/javascript' src="js/add-to-cart.min.js"></script>
<script type='text/javascript' src="js/js.cookie.min.js"></script>

<script type='text/javascript' src="js/woocommerce.min.js"></script>

<script type='text/javascript' src="js/cart-fragments.min.js"></script>
<script type='text/javascript' src="js/cart-fragments.minn.js"></script>

<script type='text/javascript' src="js/gprofiles.js"></script>

<script type='text/javascript' src="js/wpgrohod.js"></script>
<script type='text/javascript' src="js/comment-reply.min.js"></script>
<script type='text/javascript' src="js/jquery.slicknav.min.js"></script>
<script type='text/javascript' src="js/dropdown.js"></script>
<script type='text/javascript' src="js/flickity.pkgd.min.js"></script>
<script type='text/javascript' src="js/jquery.fitvids.js"></script>
<script type='text/javascript' src="js/search_button.js"></script>

<script type='text/javascript' src="js/functions.js"></script>
<script type='text/javascript' src="js/functionss.js"></script>
<script type='text/javascript' src="js/social-icons-widget-frontend.js"></script>

<script type='text/javascript' src="js/milestone.min.js"></script>
<script type='text/javascript' src="js/new-tab.min.js"></script>
<script type='text/javascript' src="js/galleria.js"></script>
<script type='text/javascript' src="js/wzslider.js"></script>
<script type='text/javascript' src="js/fitvids.min.js"></script>
<script type='text/javascript' src="js/wp-embed.min.js"></script>
<script type='text/javascript' src="js/spin.min.js"></script>
<script type='text/javascript' src="js/jquery.spin.min.js"></script>

<script type='text/javascript' src="js/jetpack-carousel.min.js"></script>
<script type='text/javascript' src="js/tiled-gallery.min.js"></script>

<script>
var slideIndex = 1;
showDivs(slideIndex);

function plusDivs(n) {
  showDivs(slideIndex += n);
}

function showDivs(n) {
  var i;
  var x = document.getElementsByClassName("mySlides");
  if (n > x.length) {slideIndex = 1}
  if (n < 1) {slideIndex = x.length}
  for (i = 0; i < x.length; i++) {
    x[i].style.display = "none";  
  }
  x[slideIndex-1].style.display = "block";  
}
</script>


<script src="http://www.landmarkmlp.com/js-plugin/owl.carousel/assets/js/jquery-1.9.1.min.js"></script>
<script src="http://www.landmarkmlp.com/js-plugin/owl.carousel/owl-carousel/owl.carousel.min.js"></script>

<script>
$(".owl-carousel").owlCarousel();
</script>



</body>
</html>