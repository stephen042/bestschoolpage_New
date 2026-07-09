<?php
/**
 * Homepage - 2026 Modern Design
 * School management system landing page
 * All database connections and logic preserved
 */

require_once('config.php');

// ============================================================================
// INITIALIZATION - ALL ORIGINAL CODE PRESERVED
// ============================================================================
$stat = [];

// Get home page content settings
$homeContent = [];
try {
    $homeContent = db_get_row("SELECT * FROM home_content WHERE id = 1") ?: [];
} catch (Exception $e) {
    $homeContent = [];
}

// Get home images for features section
$homeImages = db_get_rows("SELECT * FROM home_image WHERE status = 1 ORDER BY id DESC");

// Keep only meaningful feature cards for homepage display.
$featureCards = [];
$seenFeatureTitles = [];
if (is_array($homeImages)) {
    foreach ($homeImages as $img) {
        $title = trim((string)($img['title'] ?? ''));
        $subtitle = trim((string)($img['title_1'] ?? ''));
        $icon = trim((string)($img['picons'] ?? ''));

        // Skip incomplete or weak cards.
        if ($icon === '' || ($title === '' && $subtitle === '')) {
            continue;
        }

        $labelKey = strtolower(trim($title . ' ' . $subtitle));
        if ($labelKey === '' || isset($seenFeatureTitles[$labelKey])) {
            continue;
        }

        $seenFeatureTitles[$labelKey] = true;
        $featureCards[] = $img;

        // Keep the section concise and relevant.
        if (count($featureCards) >= 6) {
            break;
        }
    }
}

// Get why choose us items
$whyChooseUs = db_get_rows("SELECT * FROM why_choose_us ORDER BY id DESC");

// Get best clients
$bestClients = db_get_rows("SELECT * FROM best_client ORDER BY id DESC");

// ============================================================================
// HELPER FUNCTION FOR SAFE OUTPUT - PRESERVED
// ============================================================================
if (!function_exists('e')) {
    function e($string) {
        return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include('inc.meta-new.php'); ?>
    
    <!-- Google Fonts - Inter (Modern & Clean) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <!-- Tailwind CSS v4 -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Font Awesome 6 (Free) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <!-- Custom Styles -->
    <style>
        /* ============================================================
           BASE STYLES
           ============================================================ */
        * {
            font-family: 'Inter', sans-serif;
        }
        
        body {
            background: #f8fafc;
            overflow-x: hidden;
        }
        
        /* ============================================================
           GLASSMORPHISM HEADER
           ============================================================ */
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
        
        /* ============================================================
           HERO SECTION
           ============================================================ */
        .hero-section {
            position: relative;
            min-height: 90vh;
            display: flex;
            align-items: center;
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #0f172a 100%);
            overflow: hidden;
        }
        
        .hero-section::before {
            content: '';
            position: absolute;
            inset: 0;
            background: url('images/home-slider.png') center/cover no-repeat;
            opacity: 0.35;
            animation: slowZoom 20s ease-in-out infinite alternate;
        }
        
        @keyframes slowZoom {
            0% { transform: scale(1); }
            100% { transform: scale(1.1); }
        }
        
        .hero-overlay {
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, 
                rgba(15, 23, 42, 0.92) 0%,
                rgba(30, 41, 59, 0.85) 50%,
                rgba(15, 23, 42, 0.92) 100%
            );
            z-index: 1;
        }
        
        .hero-content {
            position: relative;
            z-index: 2;
        }
        
        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(79, 70, 229, 0.2);
            backdrop-filter: blur(8px);
            border: 1px solid rgba(79, 70, 229, 0.3);
            padding: 8px 20px;
            border-radius: 100px;
            color: #818cf8;
            font-size: 0.875rem;
            font-weight: 500;
            letter-spacing: 0.5px;
            animation: pulseGlow 2s ease-in-out infinite;
        }
        
        @keyframes pulseGlow {
            0%, 100% { box-shadow: 0 0 20px rgba(79, 70, 229, 0.1); }
            50% { box-shadow: 0 0 40px rgba(79, 70, 229, 0.2); }
        }
        
        .hero-title {
            font-size: clamp(2.5rem, 6vw, 4.5rem);
            font-weight: 900;
            line-height: 1.1;
            background: linear-gradient(135deg, #ffffff 0%, #94a3b8 50%, #ffffff 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .hero-title .highlight {
            background: linear-gradient(135deg, #818cf8 0%, #4f46e5 50%, #6366f1 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .hero-subtitle {
            color: #cbd5e1;
            font-size: clamp(1rem, 1.5vw, 1.25rem);
            line-height: 1.8;
            max-width: 600px;
        }
        
        /* Hero Buttons */
        .btn-primary-hero {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 16px 36px;
            background: linear-gradient(135deg, #4f46e5 0%, #6366f1 100%);
            color: white;
            font-weight: 600;
            border-radius: 12px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 20px rgba(79, 70, 229, 0.4);
            text-decoration: none;
            font-size: 1rem;
        }
        
        .btn-primary-hero:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 30px rgba(79, 70, 229, 0.5);
            color: white;
        }
        
        .btn-secondary-hero {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 16px 36px;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(8px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: white;
            font-weight: 600;
            border-radius: 12px;
            transition: all 0.3s ease;
            text-decoration: none;
            font-size: 1rem;
        }
        
        .btn-secondary-hero:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateY(-2px);
            color: white;
        }
        
        /* Floating Stats */
        .stat-card-hero {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(8px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            padding: 20px 30px;
            border-radius: 16px;
            text-align: center;
            transition: all 0.3s ease;
        }
        
        .stat-card-hero:hover {
            background: rgba(255, 255, 255, 0.1);
            transform: translateY(-5px);
        }
        
        .stat-number {
            font-size: 2.5rem;
            font-weight: 800;
            background: linear-gradient(135deg, #818cf8, #4f46e5);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        /* ============================================================
           FEATURES SECTION
           ============================================================ */
        .section-title {
            font-size: clamp(2rem, 4vw, 2.8rem);
            font-weight: 800;
            color: #0f172a;
        }
        
        .section-title span {
            background: linear-gradient(135deg, #4f46e5, #6366f1);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .feature-card {
            background: white;
            padding: 30px;
            border-radius: 20px;
            border: 1px solid #f1f5f9;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
            height: 100%;
        }
        
        .feature-card::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, rgba(79, 70, 229, 0.03), rgba(99, 102, 241, 0.05));
            opacity: 0;
            transition: opacity 0.4s ease;
        }
        
        .feature-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.08);
            border-color: rgba(79, 70, 229, 0.2);
        }
        
        .feature-card:hover::before {
            opacity: 1;
        }
        
        .feature-icon {
            width: 60px;
            height: 60px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            margin-bottom: 16px;
            background: linear-gradient(135deg, #eef2ff, #e0e7ff);
            color: #4f46e5;
            transition: all 0.3s ease;
        }
        
        .feature-card:hover .feature-icon {
            transform: scale(1.1) rotate(-5deg);
            background: linear-gradient(135deg, #4f46e5, #6366f1);
            color: white;
        }
        
        .feature-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 8px;
        }
        
        .feature-title span {
            color: #4f46e5;
        }
        
        .feature-desc {
            color: #64748b;
            line-height: 1.6;
            font-size: 0.95rem;
        }
        
        /* ============================================================
           WHY CHOOSE US - 3D TILT CARDS
           ============================================================ */
        .why-choose-section {
            background: #f1f5f9;
            padding: 80px 0;
        }
        
        .why-card {
            background: white;
            padding: 40px 30px;
            border-radius: 20px;
            text-align: center;
            transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            border: 1px solid #e2e8f0;
            height: 100%;
            cursor: default;
        }
        
        .why-card:hover {
            transform: perspective(1000px) rotateX(3deg) rotateY(3deg) translateY(-10px);
            box-shadow: 0 30px 80px rgba(0, 0, 0, 0.1);
            border-color: rgba(79, 70, 229, 0.3);
        }
        
        .why-card::after {
            content: '';
            position: absolute;
            inset: -1px;
            border-radius: 20px;
            padding: 2px;
            background: linear-gradient(135deg, transparent 40%, #4f46e5 100%);
            -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
            -webkit-mask-composite: xor;
            mask-composite: exclude;
            opacity: 0;
            transition: opacity 0.4s ease;
        }
        
        .why-card:hover::after {
            opacity: 1;
        }
        
        .why-icon-wrap {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            background: linear-gradient(135deg, #eef2ff, #e0e7ff);
            transition: all 0.3s ease;
        }
        
        .why-card:hover .why-icon-wrap {
            background: linear-gradient(135deg, #4f46e5, #6366f1);
            transform: scale(1.1) rotate(10deg);
        }
        
        .why-icon-wrap img {
            width: 40px;
            height: 40px;
            object-fit: contain;
            transition: all 0.3s ease;
        }
        
        .why-card:hover .why-icon-wrap img {
            filter: brightness(0) invert(1);
        }
        
        .why-title {
            font-size: 1.3rem;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 12px;
        }
        
        .why-desc {
            color: #64748b;
            line-height: 1.7;
            font-size: 0.95rem;
            margin-bottom: 20px;
        }
        
        .why-more-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: #4f46e5;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        
        .why-more-link:hover {
            gap: 14px;
            color: #4338ca;
        }
        
        /* ============================================================
           ABOUT SECTION
           ============================================================ */
        .about-section {
            padding: 80px 0;
            background: white;
        }
        
        .about-icon-box {
            width: 64px;
            height: 64px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #eef2ff, #e0e7ff);
            color: #4f46e5;
            font-size: 28px;
            flex-shrink: 0;
            transition: all 0.3s ease;
        }
        
        .about-item:hover .about-icon-box {
            background: linear-gradient(135deg, #4f46e5, #6366f1);
            color: white;
            transform: scale(1.05) rotate(-5deg);
        }
        
        .about-item {
            transition: all 0.3s ease;
            padding: 12px;
            border-radius: 12px;
        }
        
        .about-item:hover {
            background: #f8fafc;
        }
        
        .stat-about-number {
            font-size: 2.5rem;
            font-weight: 800;
            color: #0f172a;
        }
        
        .stat-about-label {
            color: #64748b;
            font-weight: 500;
        }
        
        /* ============================================================
           CTA SECTION
           ============================================================ */
        .cta-section {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #0f172a 100%);
            padding: 80px 0;
            position: relative;
            overflow: hidden;
        }
        
        .cta-section::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -20%;
            width: 600px;
            height: 600px;
            background: radial-gradient(circle, rgba(79, 70, 229, 0.15), transparent 70%);
            border-radius: 50%;
            animation: floatBg 8s ease-in-out infinite alternate;
        }
        
        .cta-section::after {
            content: '';
            position: absolute;
            bottom: -40%;
            left: -10%;
            width: 500px;
            height: 500px;
            background: radial-gradient(circle, rgba(99, 102, 241, 0.1), transparent 70%);
            border-radius: 50%;
            animation: floatBg 10s ease-in-out infinite alternate-reverse;
        }
        
        @keyframes floatBg {
            0% { transform: translate(0, 0) scale(1); }
            100% { transform: translate(30px, -30px) scale(1.1); }
        }
        
        .cta-content {
            position: relative;
            z-index: 2;
        }
        
        .btn-cta {
            display: inline-flex;
            align-items: center;
            gap: 12px;
            padding: 18px 48px;
            background: linear-gradient(135deg, #4f46e5 0%, #6366f1 100%);
            color: white;
            font-weight: 700;
            border-radius: 16px;
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 30px rgba(79, 70, 229, 0.4);
            text-decoration: none;
            font-size: 1.1rem;
            animation: pulseBtn 2s ease-in-out infinite;
        }
        
        @keyframes pulseBtn {
            0%, 100% { box-shadow: 0 4px 30px rgba(79, 70, 229, 0.4); }
            50% { box-shadow: 0 8px 50px rgba(79, 70, 229, 0.6); }
        }
        
        .btn-cta:hover {
            transform: translateY(-3px) scale(1.02);
            box-shadow: 0 8px 40px rgba(79, 70, 229, 0.6);
            color: white;
        }
        
        /* ============================================================
           CLIENTS SECTION - INFINITE SCROLL
           ============================================================ */
        .clients-section {
            background: white;
            padding: 60px 0;
            border-top: 1px solid #f1f5f9;
            border-bottom: 1px solid #f1f5f9;
        }
        
        .marquee-wrapper {
            overflow: hidden;
            position: relative;
            width: 100%;
        }
        
        .marquee-wrapper::before,
        .marquee-wrapper::after {
            content: '';
            position: absolute;
            top: 0;
            width: 80px;
            height: 100%;
            z-index: 10;
            pointer-events: none;
        }
        
        .marquee-wrapper::before {
            left: 0;
            background: linear-gradient(to right, white, transparent);
        }
        
        .marquee-wrapper::after {
            right: 0;
            background: linear-gradient(to left, white, transparent);
        }
        
        .marquee-track {
            display: flex;
            gap: 60px;
            animation: scroll 25s linear infinite;
            width: max-content;
        }
        
        .marquee-track:hover {
            animation-play-state: paused;
        }
        
        @keyframes scroll {
            0% { transform: translateX(0); }
            100% { transform: translateX(-50%); }
        }
        
        .client-logo-item {
            flex-shrink: 0;
            width: 180px;
            height: 80px;
            display: flex;
            align-items: center;
            justify-content: center;
            filter: grayscale(100%);
            opacity: 0.5;
            transition: all 0.4s ease;
        }
        
        .client-logo-item:hover {
            filter: grayscale(0);
            opacity: 1;
        }
        
        .client-logo-item img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }
        
        /* ============================================================
           FOOTER STYLES
           ============================================================ */
        .footer-modern {
            background: #0f172a;
            color: #94a3b8;
            padding: 60px 0 30px;
        }
        
        .footer-modern h5 {
            color: white;
            font-weight: 700;
            margin-bottom: 20px;
            font-size: 1.1rem;
        }
        
        .footer-modern a {
            color: #94a3b8;
            text-decoration: none;
            transition: color 0.3s ease;
        }
        
        .footer-modern a:hover {
            color: #818cf8;
        }
        
        .social-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.05);
            color: #94a3b8;
            transition: all 0.3s ease;
            text-decoration: none;
        }
        
        .social-icon:hover {
            background: rgba(79, 70, 229, 0.2);
            color: #818cf8;
            transform: translateY(-3px);
        }
        
        /* ============================================================
           SCROLL REVEAL ANIMATIONS
           ============================================================ */
        .reveal {
            opacity: 0;
            transform: translateY(40px);
            transition: all 0.8s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .reveal.visible {
            opacity: 1;
            transform: translateY(0);
        }
        
        .reveal-delay-1 { transition-delay: 0.1s; }
        .reveal-delay-2 { transition-delay: 0.2s; }
        .reveal-delay-3 { transition-delay: 0.3s; }
        .reveal-delay-4 { transition-delay: 0.4s; }
        .reveal-delay-5 { transition-delay: 0.5s; }
        
        /* ============================================================
           MOBILE RESPONSIVE TWEAKS
           ============================================================ */
        @media (max-width: 768px) {
            .hero-section {
                min-height: 100vh;
                padding: 120px 0 60px;
            }
            
            .stat-card-hero {
                padding: 15px 20px;
            }
            
            .stat-number {
                font-size: 1.8rem;
            }
            
            .feature-card {
                padding: 24px;
            }
            
            .why-card {
                padding: 30px 20px;
            }
            
            .marquee-wrapper::before,
            .marquee-wrapper::after {
                width: 40px;
            }
            
            .client-logo-item {
                width: 120px;
                height: 60px;
            }
            
            .marquee-track {
                gap: 30px;
            }
            
            .btn-primary-hero,
            .btn-secondary-hero {
                padding: 14px 28px;
                font-size: 0.95rem;
                width: 100%;
                justify-content: center;
            }
            
            .btn-cta {
                padding: 16px 32px;
                font-size: 1rem;
                width: 100%;
                justify-content: center;
            }
        }
        
        @media (max-width: 480px) {
            .hero-title {
                font-size: 2rem;
            }
            
            .stat-number {
                font-size: 1.5rem;
            }
            
            .stat-card-hero {
                padding: 12px 16px;
            }
        }
        
        /* ============================================================
           SCROLL PROGRESS BAR
           ============================================================ */
        .scroll-progress {
            position: fixed;
            top: 0;
            left: 0;
            height: 3px;
            background: linear-gradient(to right, #4f46e5, #6366f1, #818cf8);
            z-index: 10000;
            transition: width 0.1s ease;
            width: 0%;
        }
        
        /* ============================================================
           MODAL OVERRIDE FOR DEMO
           ============================================================ */
        .modal-content {
            border-radius: 20px;
            border: none;
            box-shadow: 0 40px 80px rgba(0, 0, 0, 0.2);
        }
    </style>
</head>
<body class="antialiased">

<!-- Scroll Progress Bar -->
<div class="scroll-progress" id="scrollProgress"></div>

<div id="page" class="site">
    
    <!-- ============================================================
    HEADER - GLASSMORPHISM
    ============================================================ -->
    <?php include('inc.header-new.php'); ?>

    <div id="content" class="site-content">
        
        <!-- ============================================================
        HERO SECTION - 2026 MODERN
        ============================================================ -->
        <section class="hero-section">
            <div class="hero-overlay"></div>
            
            <div class="container mx-auto px-4 hero-content">
                <div class="max-w-6xl mx-auto">
                    <!-- Badge -->
                    <!-- <div class="hero-badge mb-6 reveal">
                        <i class="fas fa-sparkles text-indigo-400"></i>
                        <span>AI-Powered School Management</span>
                    </div> -->
                    
                    <!-- Title -->
                    <h1 class="hero-title mb-6 reveal reveal-delay-1">
                        Smart School<br>
                        <span class="highlight">Management System</span>
                    </h1>
                    
                    <!-- Subtitle -->
                    <p class="hero-subtitle mb-10 reveal reveal-delay-2">
                        Streamline operations, automate processes, and enhance learning outcomes
                        with our all-in-one educational platform trusted by 500+ institutions worldwide.
                    </p>
                    
                    <!-- Buttons -->
                    <div class="flex flex-wrap gap-4 mb-12 reveal reveal-delay-3">
                        <a href="tel:<?= e($homeContent['call_number'] ?? '') ?>" class="btn-primary-hero">
                            <i class="fas fa-phone"></i>
                            Call Now: <?= e($homeContent['call_number'] ?? '+1 (555) 123-4567') ?>
                        </a>
                        <a href="<?= SITE_URL ?>contact-us.php" class="btn-secondary-hero">
                            <i class="fas fa-play-circle"></i>
                            Request Demo
                        </a>
                    </div>
                    
                    <!-- Stats -->
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 reveal reveal-delay-4 mb-6">
                        <div class="stat-card-hero">
                            <div class="stat-number">500+</div>
                            <div class="text-slate-400 text-sm font-medium">Schools</div>
                        </div>
                        <div class="stat-card-hero">
                            <div class="stat-number">98%</div>
                            <div class="text-slate-400 text-sm font-medium">Satisfaction</div>
                        </div>
                        <div class="stat-card-hero">
                            <div class="stat-number">24/7</div>
                            <div class="text-slate-400 text-sm font-medium">Support</div>
                        </div>
                        <div class="stat-card-hero">
                            <div class="stat-number">4.9★</div>
                            <div class="text-slate-400 text-sm font-medium">Rating</div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ============================================================
        APPLICATION INFO / ABOUT PREVIEW
        ============================================================ -->
        <section class="py-16 md:py-24 bg-white reveal">
            <div class="container mx-auto px-4">
                <div class="max-w-6xl mx-auto">
                    <div class="grid md:grid-cols-2 gap-12 items-center">
                        <div>
                            <span class="inline-block px-4 py-1.5 bg-indigo-50 text-indigo-600 text-sm font-semibold rounded-full mb-4">
                                <i class="fas fa-bullhorn mr-2"></i> Marketing Expert
                            </span>
                            <h2 class="text-3xl md:text-4xl font-bold text-slate-900 mb-4">
                                <?= e($homeContent['marketing_expert'] ?? 'Marketing Expert') ?>
                            </h2>
                            <p class="text-slate-600 text-lg mb-6">
                                <?= nl2br(e($homeContent['banner_description'] ?? 'Transform your school management with cutting-edge technology')) ?>
                            </p>
                            <div class="flex items-center gap-4 p-4 bg-slate-50 rounded-2xl">
                                <div class="w-12 h-12 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600">
                                    <i class="fas fa-phone-volume text-xl"></i>
                                </div>
                                <div>
                                    <div class="text-sm text-slate-500">Call Us</div>
                                    <a href="tel:<?= e($homeContent['marketing_callno'] ?? '') ?>" class="text-slate-900 font-semibold text-lg hover:text-indigo-600 transition">
                                        <?= e($homeContent['marketing_callno'] ?? '+1 (555) 987-6543') ?>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="relative">
                            <div class="absolute inset-0 bg-gradient-to-tr from-indigo-500/10 to-purple-500/10 rounded-3xl blur-2xl"></div>
                            <img src="images/abooutt.PNG" alt="About" class="relative rounded-3xl shadow-2xl w-full">
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ============================================================
        FEATURES SECTION - MODERN MASONRY
        ============================================================ -->
        <section class="py-16 md:py-24 bg-slate-50">
            <div class="container mx-auto px-4">
                <div class="max-w-6xl mx-auto">
                    <div class="text-center mb-16">
                        <span class="inline-block px-4 py-1.5 bg-indigo-50 text-indigo-600 text-sm font-semibold rounded-full mb-4">
                            <i class="fas fa-cubes mr-2"></i> Features
                        </span>
                        <h2 class="section-title">
                            Powerful <span>Features</span> for Modern Education
                        </h2>
                        <p class="text-slate-600 max-w-2xl mx-auto mt-4">
                            Everything you need to manage your institution efficiently in one platform
                        </p>
                    </div>

                    <?php if (!empty($featureCards)): ?>
                        <div class="grid md:grid-cols-2 gap-6">
                            <?php $counter = 0; foreach ($featureCards as $image): $counter++; ?>
                                <div class="feature-card reveal reveal-delay-<?= min($counter % 3 + 1, 5) ?>">
                                    <div class="flex items-start gap-4">
                                        <div class="feature-icon flex-shrink-0">
                                            <img src="uploads/<?= e($image['picons']) ?>" alt="<?= e($image['title']) ?>" class="w-8 h-8 object-contain">
                                        </div>
                                        <div>
                                            <h3 class="feature-title">
                                                <span><?= e($image['title']) ?></span>
                                                <?= e($image['title_1']) ?>
                                            </h3>
                                            <?php if (!empty(trim((string)($image['short_description'] ?? '')))): ?>
                                                <p class="feature-desc"><?= e($image['short_description']) ?></p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-12 bg-white rounded-2xl shadow-sm">
                            <i class="fas fa-cubes text-4xl text-slate-300 mb-4"></i>
                            <p class="text-slate-500">No features available. Please add in admin panel.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </section>

        <!-- ============================================================
        WHY CHOOSE US - 3D TILT CARDS
        ============================================================ -->
        <section class="why-choose-section">
            <div class="container mx-auto px-4">
                <div class="max-w-6xl mx-auto">
                    <div class="text-center mb-16 reveal">
                        <span class="inline-block px-4 py-1.5 bg-indigo-50 text-indigo-600 text-sm font-semibold rounded-full mb-4">
                            <i class="fas fa-question-circle mr-2"></i> Why Choose Us
                        </span>
                        <h2 class="section-title">
                            Why <span>Choose</span> Us?
                        </h2>
                        <p class="text-slate-600 max-w-2xl mx-auto mt-4">
                            We provide innovative solutions that empower educational institutions
                        </p>
                    </div>

                    <?php if (!empty($whyChooseUs)): ?>
                        <div class="grid md:grid-cols-3 gap-8">
                            <?php $counter = 0; foreach ($whyChooseUs as $item): $counter++; ?>
                                <div class="why-card reveal reveal-delay-<?= min($counter % 3 + 1, 5) ?>">
                                    <div class="why-icon-wrap">
                                        <img src="uploads/<?= e($item['picons']) ?>" alt="<?= e($item['title']) ?>">
                                    </div>
                                    <h3 class="why-title"><?= e($item['title']) ?></h3>
                                    <p class="why-desc">
                                        <?= e($item['short_description'] ?? 'Professional solution for your institution') ?>
                                    </p>
                                    <?php $whySlug = trim((string)($item['pageurl'] ?? '')); ?>
                                    <?php if ($whySlug !== ''): ?>
                                        <a href="<?= SITE_URL ?>iWhyChooseUs.php?auothid=<?= urlencode($whySlug) ?>" class="why-more-link">
                                            Read More
                                            <i class="fas fa-arrow-right"></i>
                                        </a>
                                    <?php else: ?>
                                        <span class="why-more-link" style="opacity:.5; cursor:not-allowed;">
                                            Read More
                                            <i class="fas fa-arrow-right"></i>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-12 bg-white rounded-2xl shadow-sm">
                            <i class="fas fa-question-circle text-4xl text-slate-300 mb-4"></i>
                            <p class="text-slate-500">No content available. Please add in admin panel.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </section>

        <!-- ============================================================
        ABOUT US SECTION        ============================================================ -->
        <section class="about-section reveal">
            <div class="container mx-auto px-4">
                <div class="max-w-6xl mx-auto">
                    <div class="grid lg:grid-cols-2 gap-16 items-center">
                        <div>
                            <span class="inline-block px-4 py-1.5 bg-indigo-50 text-indigo-600 text-sm font-semibold rounded-full mb-4">
                                <i class="fas fa-info-circle mr-2"></i> About Us
                            </span>
                            <h2 class="text-3xl md:text-4xl font-bold text-slate-900 mb-6">
                                Revolutionizing School Management
                            </h2>
                            <p class="text-slate-600 text-lg mb-8">
                                Our platform automates and streamlines all aspects of school administration
                            </p>

                            <div class="space-y-4">
                                <div class="about-item flex items-start gap-4">
                                    <div class="about-icon-box">
                                        <i class="fas fa-bell"></i>
                                    </div>
                                    <div>
                                        <h4 class="font-bold text-slate-900"><?= e($homeContent['automate_1'] ?? 'Smart Automation') ?></h4>
                                        <p class="text-slate-500"><?= e($homeContent['institute_process_1'] ?? 'Automated institute processes') ?></p>
                                    </div>
                                </div>
                                <div class="about-item flex items-start gap-4">
                                    <div class="about-icon-box">
                                        <i class="fas fa-bullhorn"></i>
                                    </div>
                                    <div>
                                        <h4 class="font-bold text-slate-900"><?= e($homeContent['automate_2'] ?? 'Communication') ?></h4>
                                        <p class="text-slate-500"><?= e($homeContent['institute_process_2'] ?? 'Enhanced communication tools') ?></p>
                                    </div>
                                </div>
                                <div class="about-item flex items-start gap-4">
                                    <div class="about-icon-box">
                                        <i class="fas fa-cog"></i>
                                    </div>
                                    <div>
                                        <h4 class="font-bold text-slate-900"><?= e($homeContent['automate_3'] ?? 'Advanced Analytics') ?></h4>
                                        <p class="text-slate-500"><?= e($homeContent['institute_process_3'] ?? 'Data-driven insights') ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div>
                            <div class="relative">
                                <div class="absolute -top-4 -right-4 w-32 h-32 bg-indigo-200/30 rounded-full blur-2xl"></div>
                                <div class="absolute -bottom-4 -left-4 w-40 h-40 bg-purple-200/30 rounded-full blur-2xl"></div>
                                <img src="images/laptop.png" alt="Laptop" class="relative rounded-3xl shadow-2xl w-full">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ============================================================
        CTA SECTION - REQUEST DEMO
        ============================================================ -->
        <section class="cta-section">
            <div class="container mx-auto px-4">
                <div class="max-w-4xl mx-auto text-center cta-content">
                    <div class="inline-block px-4 py-1.5 bg-indigo-500/20 text-indigo-300 text-sm font-semibold rounded-full mb-6 border border-indigo-500/20">
                        <i class="fas fa-rocket mr-2"></i> Get Started
                    </div>
                    <h2 class="text-3xl md:text-4xl font-bold text-white mb-6">
                        <?= e($homeContent['request_demo_content'] ?? 'Ready to Transform Your School?') ?>
                    </h2>
                    <p class="text-slate-300 text-lg mb-8 max-w-2xl mx-auto">
                        Join 500+ institutions already using our platform. Start your free demo today.
                    </p>
                    <a href="<?= SITE_URL ?>contact-us.php" class="btn-cta">
                        <i class="fas fa-play-circle"></i>
                        Request Demo
                    </a>
                </div>
            </div>
        </section>

        <!-- ============================================================
        CLIENTS SECTION - INFINITE MARQUEE
        ============================================================ -->
        <?php if (!empty($bestClients)): ?>
        <section class="clients-section">
            <div class="container mx-auto px-4">
                <div class="max-w-6xl mx-auto">
                    <div class="text-center mb-12 reveal">
                        <span class="inline-block px-4 py-1.5 bg-indigo-50 text-indigo-600 text-sm font-semibold rounded-full mb-4">
                            <i class="fas fa-users mr-2"></i> Trusted By
                        </span>
                        <h3 class="text-2xl font-bold text-slate-900">
                            Our <span class="text-indigo-600">Best Clients</span>
                        </h3>
                    </div>

                    <div class="marquee-wrapper">
                        <div class="marquee-track">
                            <?php foreach ($bestClients as $client): ?>
                                <a href="<?= e($client['urllink'] ?? '#') ?>" target="_blank" class="client-logo-item">
                                    <img src="uploads/<?= e($client['picons']) ?>" alt="<?= e($client['title'] ?? 'Client') ?>">
                                </a>
                            <?php endforeach; ?>
                            <!-- Duplicate for seamless loop -->
                            <?php foreach ($bestClients as $client): ?>
                                <a href="<?= e($client['urllink'] ?? '#') ?>" target="_blank" class="client-logo-item">
                                    <img src="uploads/<?= e($client['picons']) ?>" alt="<?= e($client['title'] ?? 'Client') ?>">
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <?php else: ?>
        <section class="clients-section">
            <div class="container mx-auto px-4">
                <div class="text-center py-8">
                    <i class="fas fa-users text-4xl text-slate-300 mb-4"></i>
                    <p class="text-slate-500">No clients available. Please add in admin panel.</p>
                </div>
            </div>
        </section>
        <?php endif; ?>

    </div>

    <!-- ============================================================
    FOOTER
    ============================================================ -->
    <?php include('inc.footer-new.php'); ?>
</div>

<!-- ============================================================
SCRIPTS
============================================================ -->
<?php include('inc.js-new.php'); ?>

<!-- Custom JavaScript for Animations -->
<script>
    (function() {
        'use strict';
        
        // ============================================================
        // SCROLL PROGRESS BAR
        // ============================================================
        const progressBar = document.getElementById('scrollProgress');
        if (progressBar) {
            window.addEventListener('scroll', function() {
                const winScroll = document.documentElement.scrollTop || document.body.scrollTop;
                const height = document.documentElement.scrollHeight - document.documentElement.clientHeight;
                const scrolled = (winScroll / height) * 100;
                progressBar.style.width = scrolled + '%';
            });
        }
        
        // ============================================================
        // GLASS HEADER SCROLL EFFECT
        // ============================================================
        const header = document.querySelector('.glass-header');
        if (header) {
            window.addEventListener('scroll', function() {
                if (window.scrollY > 50) {
                    header.classList.add('scrolled');
                } else {
                    header.classList.remove('scrolled');
                }
            });
        }
        
        // ============================================================
        // SCROLL REVEAL - INTERSECTION OBSERVER
        // ============================================================
        const revealElements = document.querySelectorAll('.reveal');
        
        if (revealElements.length && 'IntersectionObserver' in window) {
            const revealObserver = new IntersectionObserver(function(entries) {
                entries.forEach(function(entry) {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('visible');
                    }
                });
            }, {
                threshold: 0.15,
                rootMargin: '0px 0px -50px 0px'
            });
            
            revealElements.forEach(function(el) {
                revealObserver.observe(el);
            });
        } else {
            // Fallback: show all if no IntersectionObserver support
            revealElements.forEach(function(el) {
                el.classList.add('visible');
            });
        }
        
        // ============================================================
        // SMOOTH SCROLL FOR ANCHOR LINKS
        // ============================================================
        document.querySelectorAll('a[href^="#"]').forEach(function(anchor) {
            anchor.addEventListener('click', function(e) {
                const href = this.getAttribute('href');
                if (href !== '#') {
                    const target = document.querySelector(href);
                    if (target) {
                        e.preventDefault();
                        target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    }
                }
            });
        });
        
        // ============================================================
        // PARALLAX EFFECT ON HERO (Optional)
        // ============================================================
        const heroSection = document.querySelector('.hero-section');
        if (heroSection && window.innerWidth > 768) {
            window.addEventListener('scroll', function() {
                const scrolled = window.scrollY;
                const rate = scrolled * 0.5;
                heroSection.style.backgroundPositionY = rate + 'px';
            });
        }
        
        console.log('🚀 2026 Modern Homepage Loaded Successfully');
        console.log('💡 All PHP/DB logic preserved');
        
    })();
</script>

</body>
</html>