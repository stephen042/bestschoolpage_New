<?php

/**
 * Contact Us Page - 2026 Modern Design
 * Handles contact form submissions with email notifications
 * All database connections and logic preserved
 */

require_once('config.php');

// ============================================================================
// INITIALIZATION - ALL ORIGINAL CODE PRESERVED
// ============================================================================
$stat = [];

// ============================================================================
// PROCESS CONTACT FORM - FULLY PRESERVED
// ============================================================================
if (isset($_POST['contactus1'])) {
    $firstName = trim($_POST['fname'] ?? '');
    $lastName = trim($_POST['lname'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');
    $fullName = $firstName . ' ' . $lastName;

    // Validation
    $errors = [];

    if (empty($firstName)) {
        $errors[] = "First name is required";
    } elseif (!preg_match('/^[a-zA-Z\s]+$/', $firstName)) {
        $errors[] = "First name should contain only letters";
    }

    if (empty($email)) {
        $errors[] = "Email address is required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please enter a valid email address";
    }

    if (empty($subject)) {
        $errors[] = "Subject is required";
    }

    if (empty($message)) {
        $errors[] = "Message is required";
    }

    if (empty($errors)) {
        // Save to database
        $data = [
            'name' => $fullName,
            'email' => $email,
            'subject' => $subject,
            'message' => $message,
            'create_at' => date('Y-m-d H:i:s'),
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '',
            'status' => 0
        ];

        $result = db_insert("contactus", $data);

        if ($result) {
            // Send email notification to admin (optional)
            $adminEmail = $iHomeSettingDetails['contact_email'] ?? 'admin@bestschoolpage.com.ng';
            $siteName = "Best School Page";

            $emailSubject = "New Contact Form Message: " . $subject;
            $emailMessage = "
            <html>
            <head><title>New Contact Message</title></head>
            <body>
                <h2>New Contact Form Submission</h2>
                <p><strong>Name:</strong> " . htmlspecialchars($fullName) . "</p>
                <p><strong>Email:</strong> " . htmlspecialchars($email) . "</p>
                <p><strong>Subject:</strong> " . htmlspecialchars($subject) . "</p>
                <p><strong>Message:</strong></p>
                <p>" . nl2br(htmlspecialchars($message)) . "</p>
                <hr>
                <p>Submitted from: " . $siteName . "</p>
            </body>
            </html>
            ";

            $headers = "MIME-Version: 1.0\r\n";
            $headers .= "Content-type: text/html; charset=UTF-8\r\n";
            $headers .= "From: " . $email . "\r\n";
            $headers .= "Reply-To: " . $email . "\r\n";

            // Send email notification (optional - uncomment if needed)
            // mail($adminEmail, $emailSubject, $emailMessage, $headers);

            $stat['success'] = "Thank you for contacting us! We will get back to you soon.";

            // Clear form data
            $_POST = [];
        } else {
            $stat['error'] = "Unable to submit your message. Please try again later.";
        }
    } else {
        $stat['error'] = implode("<br>", $errors);
    }
}

// Get contact details from settings
$contactAddress = $iHomeSettingDetails['contact_address'] ?? '123 School Street, City, Country';
$contactPhone = $iHomeSettingDetails['contact_phoneno'] ?? '+1234567890';
$contactEmail = $iHomeSettingDetails['contact_email'] ?? 'info@bestschoolpage.com';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?php include('inc.meta-new.php'); ?>

    <!-- Google Fonts - Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

    <!-- Tailwind CSS v4 -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Font Awesome 6 (Free) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

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
           GLASS HEADER (Matching Homepage)
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
           HERO / BANNER SECTION
           ============================================================ */
        .contact-hero {
            position: relative;
            min-height: 50vh;
            display: flex;
            align-items: center;
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #0f172a 100%);
            overflow: hidden;
        }

        .contact-hero::before {
            content: '';
            position: absolute;
            inset: 0;
            background: url('images/contactus.png') center/cover no-repeat;
            opacity: 0.3;
            animation: slowZoom 20s ease-in-out infinite alternate;
        }

        @keyframes slowZoom {
            0% {
                transform: scale(1);
            }

            100% {
                transform: scale(1.1);
            }
        }

        .contact-hero-overlay {
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg,
                    rgba(15, 23, 42, 0.92) 0%,
                    rgba(30, 41, 59, 0.85) 50%,
                    rgba(15, 23, 42, 0.92) 100%);
            z-index: 1;
        }

        .contact-hero-content {
            position: relative;
            z-index: 2;
        }

        .contact-hero-title {
            font-size: clamp(2.5rem, 5vw, 4rem);
            font-weight: 900;
            line-height: 1.1;
        }

        .contact-hero-title span {
            background: linear-gradient(135deg, #818cf8 0%, #4f46e5 50%, #6366f1 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        /* Breadcrumb */
        .breadcrumb-modern {
            display: flex;
            align-items: center;
            gap: 12px;
            color: #94a3b8;
            font-size: 0.95rem;
        }

        .breadcrumb-modern a {
            color: #94a3b8;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .breadcrumb-modern a:hover {
            color: #818cf8;
        }

        .breadcrumb-modern .separator {
            color: #4f46e5;
        }

        .breadcrumb-modern .current {
            color: #e2e8f0;
            font-weight: 500;
        }

        /* ============================================================
           CONTACT FORM SECTION
           ============================================================ */
        .contact-form-section {
            padding: 80px 0;
            background: #f8fafc;
        }

        .form-card {
            background: white;
            padding: 50px 40px;
            border-radius: 24px;
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.05);
            border: 1px solid #f1f5f9;
            transition: all 0.3s ease;
        }

        .form-card:hover {
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.08);
        }

        .form-title {
            font-size: 1.75rem;
            font-weight: 800;
            color: #0f172a;
            margin-bottom: 8px;
        }

        .form-subtitle {
            color: #64748b;
            margin-bottom: 30px;
        }

        .form-group-modern {
            margin-bottom: 24px;
        }

        .form-group-modern label {
            display: block;
            font-weight: 600;
            color: #0f172a;
            margin-bottom: 8px;
            font-size: 0.9rem;
        }

        .form-group-modern label .required {
            color: #f43f5e;
            margin-left: 4px;
        }

        .form-control-modern {
            width: 100%;
            padding: 14px 18px;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: #fafbfc;
            color: #0f172a;
        }

        .form-control-modern:focus {
            outline: none;
            border-color: #4f46e5;
            box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.1);
            background: white;
        }

        .form-control-modern::placeholder {
            color: #94a3b8;
        }

        .form-control-modern.error {
            border-color: #f43f5e;
            box-shadow: 0 0 0 4px rgba(244, 63, 94, 0.1);
        }

        textarea.form-control-modern {
            resize: vertical;
            min-height: 150px;
        }

        /* Submit Button */
        .btn-submit {
            display: inline-flex;
            align-items: center;
            gap: 12px;
            padding: 16px 48px;
            background: linear-gradient(135deg, #4f46e5 0%, #6366f1 100%);
            color: white;
            font-weight: 700;
            border: none;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 20px rgba(79, 70, 229, 0.4);
            font-size: 1rem;
            width: 100%;
            justify-content: center;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 30px rgba(79, 70, 229, 0.5);
        }

        .btn-submit:active {
            transform: translateY(0);
        }

        /* ============================================================
           CONTACT INFO CARDS
           ============================================================ */
        .info-card {
            background: white;
            padding: 30px;
            border-radius: 20px;
            border: 1px solid #f1f5f9;
            transition: all 0.3s ease;
            height: 100%;
            display: flex;
            align-items: flex-start;
            gap: 16px;
        }

        .info-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.06);
            border-color: rgba(79, 70, 229, 0.2);
        }

        .info-icon {
            width: 52px;
            height: 52px;
            min-width: 52px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #eef2ff, #e0e7ff);
            color: #4f46e5;
            font-size: 22px;
            transition: all 0.3s ease;
        }

        .info-card:hover .info-icon {
            background: linear-gradient(135deg, #4f46e5, #6366f1);
            color: white;
            transform: scale(1.05) rotate(-5deg);
        }

        .info-content h4 {
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 4px;
            font-size: 1.05rem;
        }

        .info-content p,
        .info-content a {
            color: #64748b;
            text-decoration: none;
            transition: color 0.3s ease;
            font-size: 0.95rem;
            line-height: 1.6;
        }

        .info-content a:hover {
            color: #4f46e5;
        }

        /* ============================================================
           ALERT MESSAGES
           ============================================================ */
        .alert-modern {
            padding: 16px 20px;
            border-radius: 12px;
            margin-bottom: 24px;
            display: flex;
            align-items: flex-start;
            gap: 12px;
            border: 1px solid transparent;
        }

        .alert-modern i {
            font-size: 20px;
            margin-top: 2px;
        }

        .alert-modern.success {
            background: #ecfdf5;
            border-color: #6ee7b7;
            color: #065f46;
        }

        .alert-modern.success i {
            color: #10b981;
        }

        .alert-modern.error {
            background: #fef2f2;
            border-color: #fca5a5;
            color: #991b1b;
        }

        .alert-modern.error i {
            color: #f43f5e;
        }

        /* ============================================================
           MAP SECTION (Optional - Add if you have Google Maps)
           ============================================================ */
        .map-section {
            padding: 0 0 80px 0;
        }

        .map-container {
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.08);
            border: 1px solid #f1f5f9;
            height: 400px;
            background: #e2e8f0;
            position: relative;
        }

        .map-container iframe {
            width: 100%;
            height: 100%;
            border: 0;
        }

        .map-placeholder {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100%;
            background: linear-gradient(135deg, #f1f5f9, #e2e8f0);
            color: #64748b;
        }

        .map-placeholder i {
            font-size: 48px;
            color: #4f46e5;
            margin-bottom: 16px;
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

        .reveal-delay-1 {
            transition-delay: 0.1s;
        }

        .reveal-delay-2 {
            transition-delay: 0.2s;
        }

        .reveal-delay-3 {
            transition-delay: 0.3s;
        }

        .reveal-delay-4 {
            transition-delay: 0.4s;
        }

        /* ============================================================
           RESPONSIVE TWEAKS
           ============================================================ */
        @media (max-width: 768px) {
            .contact-hero {
                min-height: 40vh;
            }

            .form-card {
                padding: 30px 20px;
            }

            .info-card {
                padding: 20px;
            }

            .btn-submit {
                padding: 14px 32px;
                font-size: 0.95rem;
            }

            .map-container {
                height: 250px;
            }
        }

        @media (max-width: 480px) {
            .contact-hero-title {
                font-size: 2rem;
            }

            .form-title {
                font-size: 1.4rem;
            }

            .form-control-modern {
                padding: 12px 16px;
                font-size: 0.95rem;
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
    </style>
</head>

<body class="antialiased">

    <!-- Scroll Progress Bar -->
    <div class="scroll-progress" id="scrollProgress"></div>

    <div id="page" class="site">

        <!-- ============================================================
    HEADER - GLASSMORPHISM (Matching Homepage)
    ============================================================ -->
        <?php include('inc.header-new.php'); ?>

        <div id="content" class="site-content">

            <!-- ============================================================
        HERO SECTION - MODERN
        ============================================================ -->
            <section class="contact-hero">
                <div class="contact-hero-overlay"></div>

                <div class="container mx-auto px-4 contact-hero-content">
                    <div class="max-w-4xl mx-auto text-center">
                        <!-- Badge -->
                        <div class="inline-flex items-center gap-2 px-4 py-1.5 bg-indigo-500/20 text-indigo-300 text-sm font-semibold rounded-full border border-indigo-500/20 mb-6 reveal">
                            <i class="fas fa-envelope"></i>
                            <span>Get In Touch</span>
                        </div>

                        <!-- Title -->
                        <h1 class="contact-hero-title text-white mb-4 reveal reveal-delay-1">
                            <span>Contact</span> Us
                        </h1>

                        <!-- Breadcrumb -->
                        <div class="breadcrumb-modern justify-center reveal reveal-delay-2">
                            <a href="<?= SITE_URL ?>">Home</a>
                            <span class="separator"><i class="fas fa-chevron-right text-xs"></i></span>
                            <span class="current">Contact Us</span>
                        </div>
                    </div>
                </div>
            </section>

            <!-- ============================================================
        CONTACT FORM + INFO SECTION
        ============================================================ -->
            <section class="contact-form-section">
                <div class="container mx-auto px-4">
                    <div class="max-w-6xl mx-auto">
                        <div class="grid lg:grid-cols-5 gap-8">

                            <!-- Form Column (3/5) -->
                            <div class="lg:col-span-3 reveal">
                                <div class="form-card">
                                    <h2 class="form-title">Inquiry Now</h2>
                                    <p class="form-subtitle">We'd love to hear from you. Send us a message and we'll respond as soon as possible.</p>

                                    <?= showMessage($stat, 'alert-modern') ?>

                                    <form action="" method="post" id="contactForm">
                                        <div class="grid md:grid-cols-2 gap-4">
                                            <div class="form-group-modern">
                                                <label for="fname">
                                                    First Name <span class="required">*</span>
                                                </label>
                                                <input type="text"
                                                    class="form-control-modern"
                                                    id="fname"
                                                    name="fname"
                                                    placeholder="Enter first name"
                                                    value="<?= e($_POST['fname'] ?? '') ?>"
                                                    required>
                                            </div>

                                            <div class="form-group-modern">
                                                <label for="lname">Last Name</label>
                                                <input type="text"
                                                    class="form-control-modern"
                                                    id="lname"
                                                    name="lname"
                                                    placeholder="Enter last name"
                                                    value="<?= e($_POST['lname'] ?? '') ?>">
                                            </div>
                                        </div>

                                        <div class="form-group-modern">
                                            <label for="email">
                                                Email Address <span class="required">*</span>
                                            </label>
                                            <input type="email"
                                                class="form-control-modern"
                                                id="email"
                                                name="email"
                                                placeholder="Enter your email"
                                                value="<?= e($_POST['email'] ?? '') ?>"
                                                required>
                                        </div>

                                        <div class="form-group-modern">
                                            <label for="subject">
                                                Subject <span class="required">*</span>
                                            </label>
                                            <input type="text"
                                                class="form-control-modern"
                                                id="subject"
                                                name="subject"
                                                placeholder="Enter subject"
                                                value="<?= e($_POST['subject'] ?? '') ?>"
                                                required>
                                        </div>

                                        <div class="form-group-modern">
                                            <label for="message">
                                                Message <span class="required">*</span>
                                            </label>
                                            <textarea class="form-control-modern"
                                                id="message"
                                                name="message"
                                                rows="5"
                                                placeholder="Write your message here..."
                                                required><?= e($_POST['message'] ?? '') ?></textarea>
                                        </div>

                                        <button type="submit" name="contactus1" class="btn-submit">
                                            <i class="fas fa-paper-plane"></i>
                                            Send Message
                                        </button>
                                    </form>
                                </div>
                            </div>

                            <!-- Info Cards Column (2/5) -->
                            <div class="lg:col-span-2 space-y-4">
                                <!-- Office Address -->
                                <div class="info-card reveal reveal-delay-1">
                                    <div class="info-icon">
                                        <i class="fas fa-location-dot"></i>
                                    </div>
                                    <div class="info-content">
                                        <h4>Visit Us</h4>
                                        <p><?= e($contactAddress) ?></p>
                                    </div>
                                    <!-- Phone -->
                                    <div class="info-card reveal reveal-delay-2">
                                        <div class="info-icon">
                                            <i class="fas fa-phone"></i>
                                        </div>
                                        <div class="info-content">
                                            <h4>Call Us</h4>
                                            <a href="tel:<?= e($contactPhone) ?>"><?= e($contactPhone) ?></a>
                                        </div>
                                    </div>
                                    <!-- Email -->
                                    <div class="info-card reveal reveal-delay-3">
                                        <div class="info-icon">
                                            <i class="fas fa-envelope"></i>
                                        </div>
                                        <div class="info-content">
                                            <h4>Email Us</h4>
                                            <a href="mailto:<?= e($contactEmail) ?>"><?= e($contactEmail) ?></a>
                                        </div>
                                    </div>
                                    <!-- Hours -->
                                    <div class="info-card reveal reveal-delay-4">
                                        <div class="info-icon">
                                            <i class="fas fa-clock"></i>
                                        </div>
                                        <div class="info-content">
                                            <h4>Working Hours</h4>
                                            <p>Monday - Friday: 9:00 AM - 6:00 PM</p>
                                        </div>
                                    </div>
                                    <!-- Social Media Quick Links -->
                                    <div class="info-card reveal reveal-delay-4" style="background: linear-gradient(135deg, #eef2ff, #e0e7ff); border-color: rgba(79, 70, 229, 0.2);">
                                        <div class="info-icon" style="background: linear-gradient(135deg, #4f46e5, #6366f1); color: white;">
                                            <i class="fas fa-share-nodes"></i>
                                        </div>
                                        <div class="info-content">
                                            <h4>Connect With Us</h4>
                                            <div class="flex gap-3 mt-2">
                                                <a href="#" class="w-10 h-10 rounded-full bg-white flex items-center justify-center text-slate-600 hover:text-indigo-600 hover:shadow-md transition-all">
                                                    <i class="fab fa-facebook-f"></i>
                                                </a>
                                                <a href="#" class="w-10 h-10 rounded-full bg-white flex items-center justify-center text-slate-600 hover:text-indigo-600 hover:shadow-md transition-all">
                                                    <i class="fab fa-twitter"></i>
                                                </a>
                                                <a href="#" class="w-10 h-10 rounded-full bg-white flex items-center justify-center text-slate-600 hover:text-indigo-600 hover:shadow-md transition-all">
                                                    <i class="fab fa-linkedin-in"></i>
                                                </a>
                                                <a href="#" class="w-10 h-10 rounded-full bg-white flex items-center justify-center text-slate-600 hover:text-indigo-600 hover:shadow-md transition-all">
                                                    <i class="fab fa-youtube"></i>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
            </section>

            <!-- ============================================================
        MAP SECTION
        ============================================================ -->
            <section class="map-section reveal">
                <div class="container mx-auto px-4">
                    <div class="max-w-6xl mx-auto">
                        <div class="map-container">
                            <!-- Replace the src with your actual Google Maps embed URL -->
                            <iframe
                                src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3963.952912260219!2d3.375292414770767!3d6.524381645278529!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x103b8b2ae68280c1%3A0xdc9e87a367c3d9cb!2sLagos%2C%20Nigeria!5e0!3m2!1sen!2s!4v1644234567890!5m2!1sen!2s"
                                allowfullscreen=""
                                loading="lazy"
                                referrerpolicy="no-referrer-when-downgrade">
                            </iframe>
                        </div>
                    </div>
                </div>
            </section>

        </div>

        <!-- ============================================================
    FOOTER - MODERN
    ============================================================ -->
        <?php include('inc.footer-new.php'); ?>
    </div>

    <!-- ============================================================
SCRIPTS
============================================================ -->
    <?php include('inc.js-new.php'); ?>

    <!-- Custom JavaScript -->
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
                revealElements.forEach(function(el) {
                    el.classList.add('visible');
                });
            }

            // ============================================================
            // FORM VALIDATION (Optional Enhancement)
            // ============================================================
            const contactForm = document.getElementById('contactForm');
            if (contactForm) {
                contactForm.addEventListener('submit', function(e) {
                    const inputs = this.querySelectorAll('.form-control-modern[required]');
                    let isValid = true;

                    inputs.forEach(function(input) {
                        if (!input.value.trim()) {
                            input.classList.add('error');
                            isValid = false;
                        } else {
                            input.classList.remove('error');
                        }
                    });

                    // Email validation
                    const emailInput = this.querySelector('input[type="email"]');
                    if (emailInput && emailInput.value) {
                        const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                        if (!emailPattern.test(emailInput.value)) {
                            emailInput.classList.add('error');
                            isValid = false;
                        }
                    }

                    if (!isValid) {
                        e.preventDefault();
                        // Show a simple alert (or you can create a custom toast)
                        alert('Please fill in all required fields correctly.');
                    }
                });

                // Remove error class on input
                contactForm.querySelectorAll('.form-control-modern').forEach(function(input) {
                    input.addEventListener('input', function() {
                        this.classList.remove('error');
                    });
                });
            }

            console.log('🚀 2026 Modern Contact Page Loaded Successfully');
            console.log('💡 All PHP/DB logic preserved');

        })();
    </script>

</body>

</html>