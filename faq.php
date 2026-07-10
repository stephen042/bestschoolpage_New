<?php include('config.php'); ?>
<!DOCTYPE html>
<html lang="en">

<head>
	<?php include('inc.meta-new.php'); ?>
	<title>FAQ - Best School Page</title>
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
	<script src="https://cdn.tailwindcss.com"></script>
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
	<style type="text/css">
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

		.faq-hero {
			position: relative;
			min-height: 44vh;
			display: flex;
			align-items: center;
			background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #0f172a 100%);
			overflow: hidden;
		}

		.faq-hero::before {
			content: '';
			position: absolute;
			inset: 0;
			background: url('images/home-slider.png') center/cover no-repeat;
			opacity: 0.2;
		}

		.faq-hero-overlay {
			position: absolute;
			inset: 0;
			background: linear-gradient(135deg,
					rgba(15, 23, 42, 0.92) 0%,
					rgba(30, 41, 59, 0.85) 50%,
					rgba(15, 23, 42, 0.92) 100%);
			z-index: 1;
		}

		.faq-hero-content {
			position: relative;
			z-index: 2;
		}

		.faq-wrap {
			margin-top: -70px;
			margin-bottom: 80px;
			position: relative;
			z-index: 3;
		}

		.faq-card {
			background: rgba(255, 255, 255, 0.97);
			border: 1px solid #e2e8f0;
			border-radius: 24px;
			box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
			padding: 30px;
		}

		.faq-item {
			border: 1px solid #e2e8f0;
			border-radius: 14px;
			background: #fff;
			margin-bottom: 12px;
			overflow: hidden;
		}

		.accordion {
			width: 100%;
			border: 0;
			background: #fff;
			padding: 16px 18px;
			text-align: left;
			font-size: 1rem;
			font-weight: 600;
			color: #0f172a;
			display: flex;
			align-items: center;
			justify-content: space-between;
			gap: 16px;
			transition: background-color 0.2s ease;
		}

		.accordion:hover {
			background: #f8fafc;
		}

		.accordion .icon {
			color: #64748b;
			font-size: 0.9rem;
			transition: transform 0.2s ease;
		}

		.accordion.active {
			background: #eef2ff;
			color: #3730a3;
		}

		.accordion.active .icon {
			transform: rotate(180deg);
			color: #4338ca;
		}

		.panel {
			max-height: 0;
			overflow: hidden;
			transition: max-height 0.25s ease;
		}

		.panel-content {
			padding: 0 18px 18px;
			color: #334155;
			line-height: 1.7;
		}

		@media (max-width: 768px) {
			.faq-card {
				padding: 20px;
				border-radius: 18px;
			}

			.faq-wrap {
				margin-top: -45px;
				margin-bottom: 55px;
			}
		}
	</style>
</head>

<body class="home page-template page-template-tpl-home page-template-tpl-home-php page page-id-14">
	<div id="page" class="site">
		<?php include('inc.header-new.php'); ?>
		<div id="content" class="site-content">
			<section class="faq-hero">
				<div class="faq-hero-overlay"></div>
				<div class="container mx-auto px-4 faq-hero-content">
					<div class="max-w-4xl mx-auto text-center">
						<span class="inline-flex items-center gap-2 px-4 py-1.5 bg-indigo-500/20 text-indigo-300 text-sm font-semibold rounded-full border border-indigo-500/20 mb-5">
							<i class="fas fa-circle-question"></i>
							Help Center
						</span>
						<h1 class="text-white text-4xl md:text-5xl font-extrabold mb-3">Frequently Asked Questions</h1>
						<p class="text-slate-300">Quick answers about pricing, onboarding, and using Best School Page.</p>
					</div>
				</div>
			</section>

			<section class="faq-wrap">
				<div class="container mx-auto px-4">
					<div class="max-w-4xl mx-auto faq-card">
						<?php
						$aryList = $db->getRows("select * from faq");
						foreach ($aryList as $iList) {
						?>
							<div class="faq-item">
								<button class="accordion" type="button">
									<span><?php echo $iList['question']; ?></span>
									<i class="fas fa-chevron-down icon"></i>
								</button>
								<div class="panel">
									<div class="panel-content"><?php echo $iList['answer']; ?></div>
								</div>
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

			var acc = document.getElementsByClassName('accordion');
			var i;

			for (i = 0; i < acc.length; i++) {
				acc[i].addEventListener('click', function() {
					this.classList.toggle('active');
					var panel = this.nextElementSibling;
					if (panel.style.maxHeight) {
						panel.style.maxHeight = null;
					} else {
						panel.style.maxHeight = panel.scrollHeight + 'px';
					}
				});
			}
		})();
	</script>
</body>

</html>