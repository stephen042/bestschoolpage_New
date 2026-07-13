<?php

/**
 * Meta Tags & CSS Includes - Rebuilt for PHP 8.x
 * Optimized with better organization and responsive design
 */

// Prevent direct access
if (!defined('DS')) {
    require_once dirname(__DIR__) . '/config.php';
}

// Page specific title (can be overridden by individual pages)
$pageTitle = $PageTitle ?? 'Administrator';
$siteName = 'School Management System';
$fullTitle = $pageTitle . ' - ' . $siteName;
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <!-- ============================================ -->
    <!-- BASIC META TAGS -->
    <!-- ============================================ -->
    <title><?php echo htmlspecialchars($fullTitle); ?></title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="robots" content="NOINDEX, NOFOLLOW">
    <meta name="description" content="School Management System - Administrator Panel">
    <meta name="author" content="School Management System">

    <!-- ============================================ -->
    <!-- FAVICON -->
    <!-- ============================================ -->
    <link rel="icon" href="<?php echo SITE_URL; ?>favicon.ico" type="image/x-icon">
    <link rel="shortcut icon" href="<?php echo SITE_URL; ?>favicon.ico" type="image/x-icon">

    <!-- ============================================ -->
    <!-- CSS STYLESHEETS -->
    <!-- ============================================ -->
    <?php
    // Prefer packaged assets path, fall back to local final.css if assets folder not present
    $skoolAssetsFinal = PATH_ROOT . DIRECTORY_SEPARATOR . 'skool' . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'css' . DIRECTORY_SEPARATOR . 'final.css';
    if (file_exists($skoolAssetsFinal)) {
        echo '<link href="' . SITE_URL . 'skool/assets-new/css/final.css?v=2" rel="stylesheet" type="text/css" />';
    } else {
        echo '<link href="' . SITE_URL . 'skool/final.css?v=2" rel="stylesheet" type="text/css" />';
    }
    ?>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/material-design-iconic-font/2.2.0/css/material-design-iconic-font.min.css">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/material-design-iconic-font/2.2.0/css/material-design-iconic-font.min.css">

    <!-- ============================================ -->
    <!-- CUSTOM STYLES -->
    <!-- ============================================ -->
    <style>
        /* ===== GLOBAL RESETS & TYPOGRAPHY ===== */
        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: #f4f4f4;
            overflow-x: hidden;
        }

        /* ===== ALERT MESSAGES ===== */
        .alert-error {
            color: #a94442;
            background-color: #f2dede;
            border-color: #ebccd1;
        }

        .alert-success {
            color: #3c763d;
            background-color: #dff0d8;
            border-color: #d6e9c6;
        }

        .alert-warning {
            color: #8a6d3b;
            background-color: #fcf8e3;
            border-color: #faebcc;
        }

        .alert-info {
            color: #31708f;
            background-color: #d9edf7;
            border-color: #bce8f1;
        }

        /* ===== TABLE STYLES ===== */
        .table>caption+thead>tr:first-child>td,
        .table>caption+thead>tr:first-child>th,
        .table>colgroup+thead>tr:first-child>td,
        .table>colgroup+thead>tr:first-child>th,
        .table>thead:first-child>tr:first-child>td,
        .table>thead:first-child>tr:first-child>th {
            border-bottom: none !important;
            border-top: none !important;
        }

        /* ===== USER DETAILS ===== */
        .user-details {
            padding: 0px;
        }

        /* ===== SIDEBAR MENU ===== */
        #sidebar-menu {
            padding-bottom: 0px;
            padding-top: 0px;
        }

        #sidebar-menu>ul>li>a {
            padding: 10px 20px;
        }

        #sidebar-menu .has_sub > ul{
            display:none;
        }

        #sidebar-menu .has_sub.open > ul{
            display:block;
        }

        /* ===== Z-INDEX FIXES ===== */
        #maknewside {
            z-index: 9999999999999999999999999999999;
        }

        /* ===== RESPONSIVE TABLES ===== */
        .tablthisresponsive {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        /* Custom Scrollbar */
        .tablthisresponsive::-webkit-scrollbar-track {
            -webkit-box-shadow: inset 0 0 1px rgba(0, 0, 0, 0.3);
            background-color: #F5F5F5;
            border-radius: 15px;
        }

        .tablthisresponsive::-webkit-scrollbar {
            width: 1px;
            background-color: #F5F5F5;
            height: 10px;
        }

        .tablthisresponsive::-webkit-scrollbar-thumb {
            background-color: #1b3058cc;
            border-radius: 15px;
            background-image: -webkit-linear-gradient(0deg,
                    rgba(255, 255, 255, 0.5) 25%,
                    transparent 25%,
                    transparent 50%,
                    rgba(255, 255, 255, 0.5) 50%,
                    rgba(255, 255, 255, 0.5) 75%,
                    transparent 75%,
                    transparent);
        }

        /* ===== DATA TABLES STYLES ===== */
        .paginate_button a {
            font-size: 0px;
            border: none !important;
            background: transparent !important;
        }

        #example_wrapper .col-sm-6 {
            width: 100% !important;
        }

        #example_wrapper ul.pagination {
            width: 100% !important;
        }

        #example_wrapper ul.pagination li:last-child {
            padding: 0px !important;
            float: right !important;
            margin-right: 25px !important;
        }

        .pagination li {
            cursor: pointer !important;
        }

        .pagination .next a {
            cursor: pointer !important;
        }

        div.dataTables_filter {
            margin-bottom: 15px;
        }

        div.dataTables_filter label {
            width: 90% !important;
            font-weight: normal;
        }

        div.dataTables_filter input {
            width: 100% !important;
            padding: 6px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        /* ===== RESPONSIVE MEDIA QUERIES ===== */
        @media only screen and (max-width: 768px) {
            .shclnmdcls {
                font-size: 15px !important;
            }

            .side-menu.left {
                top: 60px !important;
            }

            .topbar-left {
                display: none !important;
            }

            .content-page>.content {
                padding: 0px !important;
            }

            .zasw {
                height: auto !important;
            }

            .dashnewnn .count {
                font-size: 22px !important;
            }

            #maknewside {
                z-index: 0;
            }

            .sectionza {
                height: auto !important;
            }

            .dashnewnn .count {
                font-size: 22px !important;
            }

            /* Better touch targets for mobile */
            button,
            .btn,
            .table-action-btn {
                min-height: 44px;
            }

            /* Stack form elements on mobile */
            .form-group .col-lg-2,
            .form-group .col-lg-10 {
                width: 100%;
                margin-bottom: 10px;
            }
        }

        /* ===== TABLET RESPONSIVE ===== */
        @media only screen and (min-width: 769px) and (max-width: 1024px) {
            .content-page>.content {
                padding: 15px !important;
            }

            .col-md-3,
            .col-md-4,
            .col-md-6,
            .col-md-8,
            .col-md-9 {
                margin-bottom: 15px;
            }
        }

        /* ===== UTILITY CLASSES ===== */
        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .text-left {
            text-align: left;
        }

        .pull-right {
            float: right;
        }

        .pull-left {
            float: left;
        }

        .clearfix::after {
            content: "";
            clear: both;
            display: table;
        }

        /* Loading spinner */
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 9999;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .loading-spinner {
            background: white;
            padding: 20px 30px;
            border-radius: 8px;
            font-size: 16px;
            color: #1B3058;
        }

        /* Card styles */
        .card-box {
            background: white;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            padding: 20px;
        }

        /* Form styles */
        .form-control {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            transition: border-color 0.3s;
        }

        .form-control:focus {
            outline: none;
            border-color: #1B3058;
            box-shadow: 0 0 3px rgba(27, 48, 88, 0.2);
        }

        /* Button styles */
        .btn {
            display: inline-block;
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.3s;
        }

        .btn-primary {
            background: #1B3058;
            color: white;
        }

        .btn-primary:hover {
            background: #f21151;
        }

        .btn-default {
            background: #f0f0f0;
            color: #333;
        }

        .btn-default:hover {
            background: #e0e0e0;
        }

        /* Table action buttons */
        .table-action-btn {
            color: #1B3058;
            margin: 0 3px;
            text-decoration: none;
        }

        .table-action-btn:hover {
            color: #f21151;
        }

        /* Notification toast styles */
        .notification-toast {
            position: fixed;
            top: 80px;
            right: 20px;
            padding: 12px 20px;
            border-radius: 4px;
            color: white;
            z-index: 9999;
            display: none;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
        }

        .notification-toast.bg-success {
            background: #28a745;
        }

        .notification-toast.bg-danger {
            background: #dc3545;
        }

        .notification-toast.bg-info {
            background: #17a2b8;
        }

        .notification-toast i {
            margin-right: 8px;
        }
    </style>

    <!-- ============================================ -->
    <!-- JAVASCRIPT - LOADED IN HEADER -->
    <!-- ============================================ -->

    <!-- Core jQuery -->
    <script src="<?php echo SITE_URL; ?>skool/assets-new/js/jquery.min.js"></script>
    <script src="<?php echo SITE_URL; ?>skool/assets-new/js/bootstrap.min.js"></script>

    <!-- Utility Libraries -->
    <script src="<?php echo SITE_URL; ?>skool/assets-new/js/detect.js"></script>
    <script src="<?php echo SITE_URL; ?>skool/assets-new/js/fastclick.js"></script>
    <script src="<?php echo SITE_URL; ?>skool/assets-new/js/jquery.slimscroll.js"></script>
    <script src="<?php echo SITE_URL; ?>skool/assets-new/js/jquery.blockUI.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/node-waves/0.7.6/waves.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/wow/1.1.2/wow.min.js"></script>
    <script src="<?php echo SITE_URL; ?>skool/assets-new/js/jquery.nicescroll.js"></script>
    <script src="<?php echo SITE_URL; ?>skool/assets-new/js/jquery.scrollTo.min.js"></script>

    <!-- Chart & Graph Libraries -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/peity/3.3.0/jquery.peity.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/waypoints/4.0.1/jquery.waypoints.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Counter-Up/1.0.0/jquery.counterup.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/morris.js/0.5.1/morris.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/raphael/2.3.0/raphael.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jQuery-Knob/1.2.13/jquery.knob.min.js"></script>

    <!-- Core Application Scripts -->
    <!-- <script src="<?php echo SITE_URL; ?>skool/assets-new/js/jquery.core.js"></script> -->
    <!-- <script src="<?php echo SITE_URL; ?>skool/assets-new/js/jquery.app.js"></script> -->

    <!-- DataTables -->
    <script src="<?php echo SITE_URL; ?>skool/assets-new/plugins/datatables/jquery.dataTables.min.js"></script>
    <script src="<?php echo SITE_URL; ?>skool/assets-new/plugins/datatables/dataTables.bootstrap.js"></script>

    <!-- Form Plugins -->
    <script src="<?php echo SITE_URL; ?>skool/assets-new/plugins/multiselect/js/jquery.multi-select.js"></script>
    <script src="<?php echo SITE_URL; ?>skool/assets-new/plugins/select2/select2.min.js"></script>
    <script src="<?php echo SITE_URL; ?>skool/assets-new/plugins/timepicker/bootstrap-timepicker.min.js"></script>
    <!-- Moment JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.30.1/moment.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap-daterangepicker@3.1/daterangepicker.min.js"></script>
    <script src="<?php echo SITE_URL; ?>skool/assets-new/plugins/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js"></script>
    <script src="<?php echo SITE_URL; ?>skool/assets-new/plugins/bootstrap-daterangepicker/daterangepicker.js"></script>

    <!-- ============================================ -->
    <!-- CUSTOM JAVASCRIPT FUNCTIONS -->
    <!-- ============================================ -->
    <script type="text/javascript">
        /**
         * Delete confirmation function
         * @param {string} url - The URL to redirect to after confirmation
         */
        function del(url) {
            if (confirm('Really want to delete this record? This action cannot be undone.')) {
                window.location = url;
            }
        }

        /**
         * Show loading spinner
         */
        function showLoading() {
            if (!$('.loading-overlay').length) {
                $('body').append('<div class="loading-overlay"><div class="loading-spinner"><i class="fa fa-spinner fa-pulse"></i> Loading...</div></div>');
            }
        }

        /**
         * Hide loading spinner
         */
        function hideLoading() {
            $('.loading-overlay').fadeOut('fast', function() {
                $(this).remove();
            });
        }

        /**
         * Show notification toast
         */
        function showNotification(message, type) {
            var icon = type === 'success' ? 'fa-check-circle' : (type === 'error' ? 'fa-exclamation-circle' : 'fa-info-circle');
            var bgClass = type === 'success' ? 'bg-success' : (type === 'error' ? 'bg-danger' : 'bg-info');

            var toast = $('<div class="notification-toast ' + bgClass + '"><i class="fa ' + icon + '"></i> ' + message + '</div>');
            $('body').append(toast);
            toast.fadeIn(300).delay(3000).fadeOut(300, function() {
                $(this).remove();
            });
        }

        // Auto-hide alert messages after 5 seconds
        $(document).ready(function() {
            $('.alert').delay(5000).fadeOut(500);
        });
    </script>

</head>

<body>