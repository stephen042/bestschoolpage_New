<?php 
/**
 * School Dynamic Page - Rebuilt for PHP 8.x
 * Displays individual school information, gallery, events, etc.
 */

require_once('config.php');

// ============================================================================
// INITIALIZATION
// ============================================================================
$stat = [];
$pageUrl = $_GET['pageurl'] ?? '';

if (empty($pageUrl)) {
    redirect(SITE_URL);
    exit;
}

// Get school details by page URL
$school = db_get_row("SELECT * FROM school_register WHERE pageurl = ?", [$pageUrl]);

if (empty($school)) {
    redirect(SITE_URL);
    exit;
}

$create_by_usertype = $school['create_by_usertype'] ?? '';
$create_by_userid = $school['id'];

// Get school about information
$aboutSchool = db_get_row("SELECT * FROM school_about WHERE create_by_userid = ?", [$create_by_userid]);
if (empty($aboutSchool)) {
    $aboutSchool = [];
}

// Get school settings
$schoolSettings = db_get_row("SELECT * FROM skool_settings WHERE create_by_userid = ?", [$create_by_userid]);
if (empty($schoolSettings)) {
    $schoolSettings = [];
}

// Get sliders
$sliders = db_get_rows("SELECT * FROM school_slider WHERE create_by_userid = ?", [$create_by_userid]);

// Get gallery images
$gallery = db_get_rows("SELECT * FROM school_gallery WHERE create_by_userid = ?", [$create_by_userid]);

// Get upcoming events
$upcomingEvents = db_get_rows("SELECT * FROM school_upcoming_event WHERE create_by_userid = ? ORDER BY date ASC", [$create_by_userid]);

// Get blog posts
$blogPosts = db_get_rows("SELECT * FROM school_blog WHERE create_by_userid = ?", [$create_by_userid]);
?>
<!DOCTYPE html>
<html lang="en-US">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($school['name'] ?? 'School') ?> - Official Website</title>
    <meta name="description" content="<?= e($aboutSchool['short_description'] ?? 'Welcome to our school') ?>">
    
    <!-- CSS Files -->
    <link rel="stylesheet" href="<?= SITE_URL ?>owlcarousel/owl.carousel.css">
    <link rel="stylesheet" href="<?= SITE_URL ?>owlcarousel/owl.theme.css">
    <link rel='stylesheet' href="css/nces-css.css" type='text/css' media='all' />
    <?php include('inc.meta-new.php'); ?>
    
    <style>
        .dld { padding: 30px 0; }
        .flexslider { margin: 0; background: transparent; }
        .slides li img { width: 100%; height: 400px; object-fit: cover; }
        .first-row-attractors {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            margin: 30px 0;
        }
        .first-row-attractor {
            flex: 1;
            margin: 0 10px;
            background: #1B3058;
            padding: 15px;
            text-align: center;
            border-radius: 8px;
        }
        .first-row-attractor a {
            color: white;
            text-decoration: none;
            font-weight: bold;
        }
        .split-panel {
            display: flex;
            flex-wrap: wrap;
            margin: 30px 0;
        }
        .abhi-jee {
            flex: 1;
            padding: 20px;
            background: #f5f5f5;
            border-radius: 8px;
            margin-right: 20px;
        }
        .abhi-jee img {
            width: 100%;
            max-width: 200px;
            border-radius: 50%;
        }
        .icon-link {
            display: flex;
            align-items: center;
            padding: 15px;
            border-bottom: 1px solid #eee;
        }
        .link-icon {
            width: 50px;
            font-size: 24px;
            color: #1B3058;
        }
        .link-text {
            flex: 1;
        }
        .link-text a {
            color: #f21151;
            text-decoration: none;
        }
        .owl-carousel .item img {
            height: 200px;
            object-fit: cover;
            width: 100%;
        }
        .evnt {
            display: flex;
            margin-bottom: 20px;
            padding: 15px;
            background: #f9f9f9;
            border-radius: 8px;
        }
        .evnt .img img {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 8px;
            margin-right: 15px;
        }
        .evnt .head {
            flex: 1;
        }
        .evnt .head span {
            font-weight: bold;
            color: #1B3058;
        }
        .evnt .date p {
            color: #f21151;
            font-weight: bold;
        }
        .second-row-attractors {
            display: flex;
            flex-wrap: wrap;
            margin: 30px 0;
        }
        .second-row-attractor {
            flex: 1;
            margin: 0 10px;
            padding: 20px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
        }
        .second-row-attractor img {
            width: 100%;
            height: 150px;
            object-fit: cover;
            border-radius: 8px;
        }
        .learn-more {
            display: inline-block;
            margin-top: 10px;
            color: #f21151;
            text-decoration: none;
        }
        @media (max-width: 768px) {
            .first-row-attractors { flex-direction: column; }
            .first-row-attractor { margin: 10px 0; }
            .split-panel { flex-direction: column; }
            .abhi-jee { margin-right: 0; margin-bottom: 20px; }
            .second-row-attractors { flex-direction: column; }
            .second-row-attractor { margin: 10px; }
        }
    </style>
</head>
<body class="home page-template page-template-tpl-home page-template-tpl-home-php page page-id-14">
<div id="page" class="site">
    <?php include('inc.header-new.php'); ?>
    <div id="content" class="site-content">
        <div class="container">
            <div class="dld">
                <div id="container" class="cf">
                    <div id="main" role="main">
                        
                        <!-- Slider Section -->
                        <section class="slider">
                            <div class="flexslider carousel">
                                <ul class="slides">
                                    <?php if (!empty($sliders)): ?>
                                        <?php foreach ($sliders as $slider): ?>
                                            <li>
                                                <img src="<?= SITE_URL ?>uploads/<?= e($slider['image']) ?>" alt="School Slider">
                                            </li>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <li><img src="images/default-slider.jpg" alt="Default Slider"></li>
                                    <?php endif; ?>
                                </ul>
                            </div>
                        </section>
                    </div>
                </div>
                
                <div id="MainContent">
                    <div id="ContentArea_base" tabindex="-1">
                        
                        <!-- Quick Links Section -->
                        <div class="naep-panel first-row-attractors">
                            <div class="first-row-attractor">
                                <a href="<?= e($aboutSchool['link_school_fee'] ?? '#') ?>" target="_blank">
                                    <i class="fa fa-money"></i> Pay School Fees
                                </a>
                            </div>
                            <div class="first-row-attractor">
                                <a href="<?= e($aboutSchool['link_apply_addmission'] ?? '#') ?>" target="_blank">
                                    <i class="fa fa-pencil-square-o"></i> Apply for Admission
                                </a>
                            </div>
                            <div class="first-row-attractor">
                                <a href="<?= PARENT_URL ?>">
                                    <i class="fa fa-bar-chart"></i> Check Result
                                </a>
                            </div>
                            <div class="first-row-attractor">
                                <a href="<?= LIVE_URL ?>ckeck_admission.php?id=<?= e($schoolSettings['id'] ?? '') ?>">
                                    <i class="fa fa-check-circle-o"></i> Check Admission
                                </a>
                            </div>
                        </div>
                        
                        <!-- School Info & Downloads Section -->
                        <div class="naep-panel split-panel">
                            <div class="abhi-jee">
                                <div class="abhii-j">
                                    <?php if (empty($school['logo'])): ?>
                                        <img src="image/black-wheat-and-mortarboard.png" alt="School Logo">
                                    <?php else: ?>
                                        <img src="<?= SITE_URL ?>uploads/<?= e($school['logo']) ?>" alt="School Logo">
                                    <?php endif; ?>
                                </div>
                                <div class="gtg">
                                    <div class="heas">
                                        <h2>Contact Details</h2>
                                    </div>
                                    <div class="gghu">
                                        <p><strong><?= e($school['name']) ?></strong></p>
                                    </div>
                                    <div class="ggh">
                                        <p><i class="fa fa-map-marker"></i> <?= e($school['location']) ?></p>
                                    </div>
                                    <div class="maill">
                                        <p><i class="fa fa-envelope"></i> <?= e($school['email']) ?></p>
                                    </div>
                                    <div class="maill">
                                        <p><i class="fa fa-phone"></i> <?= e($school['contact_no']) ?></p>
                                    </div>
                                    <div class="2tn">
                                        <button class="btn btn-primary">
                                            <a href="#" style="color:white;">Apply Now</a>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- PDF Downloads Section -->
                            <div class="panel-body naep-icon-link-panel">
                                <div class="panel-body">
                                    <div class="whats-new-header">
                                        <h2>PDF Download</h2>
                                    </div>
                                    
                                    <?php if (!empty($schoolSettings['skool_syllabus'])): ?>
                                    <div class="icon-link pdf-no-size">
                                        <div class="link-icon report-release"><i class="fa fa-graduation-cap"></i></div>
                                        <div class="link-text">
                                            <span>School Syllabus</span>
                                            <a href="uploads/<?= e($schoolSettings['skool_syllabus']) ?>" download>PDF Download</a>
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($schoolSettings['learning_materials'])): ?>
                                    <div class="icon-link">
                                        <div class="link-icon naep-highlight"><i class="fa fa-book"></i></div>
                                        <div class="link-text">
                                            <span>Learning Material for All Classes</span>
                                            <a href="uploads/<?= e($schoolSettings['learning_materials']) ?>" download>PDF Download</a>
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($schoolSettings['exam_timetable'])): ?>
                                    <div class="icon-link external">
                                        <div class="link-icon naep-highlight"><i class="fa fa-table"></i></div>
                                        <div class="link-text">
                                            <span>Exam Timetable</span>
                                            <a href="uploads/<?= e($schoolSettings['exam_timetable']) ?>" download>PDF Download</a>
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($schoolSettings['exam_pass_question'])): ?>
                                    <div class="icon-link pdf-no-size">
                                        <div class="link-icon naep-highlight"><i class="fa fa-cloud-download"></i></div>
                                        <div class="link-text">
                                            <span>Download Exam Past Questions</span>
                                            <a href="uploads/<?= e($schoolSettings['exam_pass_question']) ?>" download>PDF Download</a>
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Gallery Section -->
                        <div class="kfg">
                            <div class="abhi-k">
                                <div class="naep-panel">
                                    <div class="headinggg">
                                        <h2>Gallery</h2>
                                    </div>
                                    <div class="owl-carousel">
                                        <?php if (!empty($gallery)): ?>
                                            <?php foreach ($gallery as $image): ?>
                                                <div class="item">
                                                    <img src="<?= SITE_URL ?>uploads/<?= e($image['image']) ?>" alt="Gallery Image">
                                                </div>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <div class="item"><p>No gallery images available.</p></div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- About School & Events Section -->
                        <div class="kfg hfh">
                            <div class="naep-panel">
                                <div class="container">
                                    <div class="row">
                                        <div class="col-md-7">
                                            <div class="abhi-k hgi">
                                                <div class="headinggg">
                                                    <h2>About School</h2>
                                                </div>
                                                <?php if (!empty($aboutSchool['about_image'])): ?>
                                                    <div>
                                                        <img src="<?= SITE_URL ?>uploads/<?= e($aboutSchool['about_image']) ?>" alt="About School" class="img-responsive">
                                                    </div>
                                                <?php endif; ?>
                                                <div class="para">
                                                    <p><?= nl2br(e($aboutSchool['about_description'] ?? 'No description available.')) ?></p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-5">
                                            <div class="headinggg">
                                                <h2>Upcoming Events</h2>
                                            </div>
                                            <div class="hgii">
                                                <?php if (!empty($upcomingEvents)): ?>
                                                    <?php foreach ($upcomingEvents as $event): ?>
                                                        <div class="evnt">
                                                            <div class="img">
                                                                <img src="<?= SITE_URL ?>uploads/<?= e($event['image']) ?>" alt="<?= e($event['title']) ?>">
                                                            </div>
                                                            <div class="head">
                                                                <span><?= e($event['title']) ?></span>
                                                                <p><?= e($event['description']) ?></p>
                                                            </div>
                                                            <div class="date">
                                                                <p><?= date('d M Y', strtotime($event['date'])) ?></p>
                                                            </div>
                                                        </div>
                                                    <?php endforeach; ?>
                                                <?php else: ?>
                                                    <p>No upcoming events scheduled.</p>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Promo Video Section -->
                        <div class="kfg">
                            <div class="naep-panel">
                                <div class="container">
                                    <div class="row">
                                        <div class="col-md-12 fgd">
                                            <div class="panel-body what-is-naep">
                                                <div class="panel-body">
                                                    <div class="lkj">
                                                        <h2>Promo Video</h2>
                                                        <div class="video-asset">
                                                            <?php if (!empty($aboutSchool['promo_video'])): ?>
                                                                <?php
                                                                // Extract YouTube video ID
                                                                $videoUrl = $aboutSchool['promo_video'];
                                                                preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/', $videoUrl, $matches);
                                                                $videoId = $matches[1] ?? '';
                                                                ?>
                                                                <?php if (!empty($videoId)): ?>
                                                                    <iframe src="https://www.youtube.com/embed/<?= e($videoId) ?>" width="100%" height="400" frameborder="0" allowfullscreen></iframe>
                                                                <?php else: ?>
                                                                    <p>Video URL not available.</p>
                                                                <?php endif; ?>
                                                            <?php else: ?>
                                                                <p>No promo video available.</p>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Blog Section -->
                        <div class="naep-panel second-row-attractors">
                            <div class="panel-body">
                                <?php if (!empty($blogPosts)): ?>
                                    <?php foreach ($blogPosts as $blog): ?>
                                        <div class="second-row-attractor">
                                            <img src="<?= SITE_URL ?>uploads/<?= e($blog['image']) ?>" alt="<?= e($blog['title']) ?>">
                                            <h2 class="center"><?= e($blog['title']) ?></h2>
                                            <p><?= e($blog['short_description']) ?></p>
                                            <a class="learn-more center" href="<?= SITE_URL ?>school/learnmore/<?= e($blog['pageurl']) ?>">Learn More →</a>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="second-row-attractor">
                                        <p>No blog posts available.</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <!-- Bottom Image Section -->
                        <?php if (!empty($aboutSchool['image'])): ?>
                        <div class="naep-panel image-attractors right">
                            <div class="panel-body">
                                <img src="<?= SITE_URL ?>uploads/<?= e($aboutSchool['image']) ?>" alt="School Image" class="img-responsive">
                                <h2><?= e($aboutSchool['title'] ?? '') ?></h2>
                                <p><?= e($aboutSchool['short_description'] ?? '') ?></p>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php include('inc.footer-new.php'); ?>
</div>
<?php include('inc.js-new.php'); ?>

<!-- JavaScript Files -->
<script src="<?= SITE_URL ?>owlcarousel/jquery.min.js"></script>
<script defer src="<?= SITE_URL ?>owlcarousel/jquery.flexslider.js"></script>
<script src="<?= SITE_URL ?>owlcarousel/owl.carousel.min.js"></script>

<script type="text/javascript">
    $(window).load(function(){
        $('.flexslider').flexslider({
            animation: "slide",
            animationLoop: true,
            item: 1,
            pausePlay: true,
            slideshowSpeed: 5000
        });
    });
    
    $(document).ready(function() {
        $(".owl-carousel").owlCarousel({
            autoPlay: 3000,
            items: 4,
            itemsDesktop: [1199, 3],
            itemsDesktopSmall: [979, 2],
            itemsMobile: [479, 1],
            pagination: true
        });
    });
</script>
</body>
</html>