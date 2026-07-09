<?php
/**
 * JavaScript Includes - Rebuilt for PHP 8.x
 * Optimized with better organization and error handling
 */

// Prevent direct access
if (!defined('DS')) {
    require_once dirname(__DIR__) . '/config.php';
}
?>
<script>
// Global variables
var resizefunc = [];
var SITE_URL = '<?= SITE_URL ?>';
var ADMIN_URL = '<?= ADMIN_URL ?>';

// CSRF Token (for security)
var csrf_token = '<?= md5(session_id()) ?>';
</script>

<!-- Core jQuery -->
<script src="<?= SITE_URL ?>skool/assets-new/js/jquery.min.js"></script>
<script src="<?= SITE_URL ?>skool/assets-new/js/bootstrap.min.js"></script>

<!-- Utility Libraries -->
<script src="<?= SITE_URL ?>skool/assets-new/js/detect.js"></script>
<script src="<?= SITE_URL ?>skool/assets-new/js/fastclick.js"></script>
<script src="<?= SITE_URL ?>skool/assets-new/js/jquery.slimscroll.js"></script>
<script src="<?= SITE_URL ?>skool/assets-new/js/jquery.blockUI.js"></script>
<script src="<?= SITE_URL ?>skool/assets-new/js/waves.js"></script>
<script src="<?= SITE_URL ?>skool/assets-new/js/wow.min.js"></script>
<script src="<?= SITE_URL ?>skool/assets-new/js/jquery.nicescroll.js"></script>
<script src="<?= SITE_URL ?>skool/assets-new/js/jquery.scrollTo.min.js"></script>

<!-- Chart & Graph Libraries -->
<script src="<?= SITE_URL ?>skool/assets-new/plugins/peity/jquery.peity.min.js"></script>
<script src="<?= SITE_URL ?>skool/assets-new/plugins/waypoints/lib/jquery.waypoints.js"></script>
<script src="<?= SITE_URL ?>skool/assets-new/plugins/counterup/jquery.counterup.min.js"></script>
<script src="<?= SITE_URL ?>skool/assets-new/plugins/morris/morris.min.js"></script>
<script src="<?= SITE_URL ?>skool/assets-new/plugins/raphael/raphael-min.js"></script>
<script src="<?= SITE_URL ?>skool/assets-new/plugins/jquery-knob/jquery.knob.js"></script>

<!-- Core Application Scripts -->
<script src="<?= SITE_URL ?>skool/assets-new/js/jquery.core.js"></script>
<script src="<?= SITE_URL ?>skool/assets-new/js/jquery.app.js"></script>

<!-- DataTables -->
<script src="<?= SITE_URL ?>skool/assets-new/plugins/datatables/jquery.dataTables.min.js"></script>
<script src="<?= SITE_URL ?>skool/assets-new/plugins/datatables/dataTables.bootstrap.js"></script>

<!-- Form Plugins -->
<script src="<?= SITE_URL ?>skool/assets-new/plugins/multiselect/js/jquery.multi-select.js"></script>
<script src="<?= SITE_URL ?>skool/assets-new/plugins/select2/select2.min.js"></script>
<script src="<?= SITE_URL ?>skool/assets-new/plugins/timepicker/bootstrap-timepicker.min.js"></script>
<script src="<?= SITE_URL ?>skool/assets-new/plugins/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js"></script>
<script src="<?= SITE_URL ?>skool/assets-new/plugins/bootstrap-daterangepicker/daterangepicker.js"></script>

<!-- ============================================= -->
<!-- CUSTOM APPLICATION SCRIPTS -->
<!-- ============================================= -->

<script>
// ============================================================================
// DOCUMENT READY - MAIN INITIALIZATION
// ============================================================================
$(document).ready(function() {
    
    // Initialize DataTables
    if ($('#datatable').length) {
        $('#datatable').DataTable({
            "pageLength": 25,
            "order": [[0, "desc"]],
            "language": {
                "emptyTable": "No data available",
                "search": "Search:",
                "lengthMenu": "Show _MENU_ entries",
                "info": "Showing _START_ to _END_ of _TOTAL_ entries"
            }
        });
    }
    
    if ($('#datatable1').length) {
        $('#datatable1').DataTable({
            "pageLength": 25,
            "order": [[0, "desc"]]
        });
    }
    
    // Initialize Datepickers
    $('.datepicker').datepicker({
        autoclose: true,
        format: "yyyy-mm-dd",
        todayHighlight: true,
        todayBtn: true
    });
    
    // Initialize Timepickers
    $('.timepicker').timepicker({
        defaultTime: false,
        showMeridian: false
    });
    
    // Initialize Select2
    if ($('.select2').length) {
        $('.select2').select2({
            theme: 'bootstrap',
            width: '100%'
        });
    }
    
    // Initialize Tooltips
    $('[data-toggle="tooltip"]').tooltip();
    
    // Initialize Popovers
    $('[data-toggle="popover"]').popover();
    
    // Initialize CounterUp
    if ($('.counter').length) {
        $('.counter').counterUp({
            delay: 10,
            time: 1000
        });
    }
    
    // Initialize Peity charts
    if ($('.peity').length) {
        $('.peity').peity();
    }
    
    // Initialize Waves effect
    Waves.init();
    Waves.attach('.btn, .waves-effect');
    
    // Initialize Wow animations
    new WOW().init();
    
    // Delete confirmation handler
    window.del = function(url) {
        if (confirm('Are you sure you want to delete this record? This action cannot be undone.')) {
            window.location.href = url;
        }
    };
    
    // Close alert messages after 5 seconds
    $('.alert').delay(5000).fadeOut(500);
    
    // Sidebar toggle for mobile
    $('.button-menu-mobile').on('click', function() {
        $('body').toggleClass('sidebar-collapsed');
        $(window).trigger('resize');
    });
    
    // Handle AJAX requests with CSRF protection
    $(document).ajaxSend(function(event, jqxhr, settings) {
        if (settings.type === 'POST' || settings.type === 'post') {
            if (settings.url.indexOf('?') > -1) {
                settings.url += '&csrf_token=' + csrf_token;
            } else {
                settings.url += '?csrf_token=' + csrf_token;
            }
        }
    });
});

// ============================================================================
// WINDOW LOAD - ADDITIONAL INITIALIZATION
// ============================================================================
$(window).on('load', function() {
    // Hide loading spinner
    $('.loading-spinner').fadeOut('slow');
    
    // Initialize niceScroll for sidebars
    if ($('.left-side').length) {
        $('.left-side').niceScroll({
            cursorcolor: '#1B3058',
            cursorwidth: '5px',
            cursorborder: 'none'
        });
    }
});

// ============================================================================
// WINDOW RESIZE - RESPONSIVE ADJUSTMENTS
// ============================================================================
$(window).on('resize', function() {
    var winWidth = $(window).width();
    
    // Adjust sidebar for mobile
    if (winWidth <= 768) {
        $('body').addClass('sidebar-mobile');
    } else {
        $('body').removeClass('sidebar-mobile');
    }
});

// ============================================================================
// HELPER FUNCTIONS FOR AJAX
// ============================================================================

/**
 * Generic AJAX POST function
 */
function ajaxPost(url, data, successCallback, errorCallback) {
    $.ajax({
        url: url,
        type: 'POST',
        data: data,
        dataType: 'json',
        success: function(response) {
            if (successCallback) successCallback(response);
        },
        error: function(xhr, status, error) {
            console.error('AJAX Error:', error);
            if (errorCallback) errorCallback(error);
        }
    });
}

/**
 * Generic AJAX GET function
 */
function ajaxGet(url, successCallback, errorCallback) {
    $.ajax({
        url: url,
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            if (successCallback) successCallback(response);
        },
        error: function(xhr, status, error) {
            console.error('AJAX Error:', error);
            if (errorCallback) errorCallback(error);
        }
    });
}

/**
 * Show loading spinner
 */
function showLoading() {
    $('body').append('<div class="loading-overlay"><div class="loading-spinner"><i class="fa fa-spinner fa-pulse"></i> Loading...</div></div>');
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
    var icon = (type === 'success') ? 'fa-check-circle' : (type === 'error' ? 'fa-exclamation-circle' : 'fa-info-circle');
    var bgClass = (type === 'success') ? 'bg-success' : (type === 'error' ? 'bg-danger' : 'bg-info');
    
    var toast = $('<div class="notification-toast ' + bgClass + '"><i class="fa ' + icon + '"></i> ' + message + '</div>');
    $('body').append(toast);
    toast.fadeIn(300).delay(3000).fadeOut(300, function() {
        $(this).remove();
    });
}

// ============================================================================
// FORM VALIDATION HELPERS
// ============================================================================

/**
 * Validate email format
 */
function isValidEmail(email) {
    var regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return regex.test(email);
}

/**
 * Validate phone number
 */
function isValidPhone(phone) {
    var regex = /^[0-9]{10,15}$/;
    return regex.test(phone);
}

/**
 * Validate password strength
 */
function isStrongPassword(password) {
    return password.length >= 6;
}

// ============================================================================
// DROPDOWN FUNCTIONS (For AJAX loading)
// ============================================================================

/**
 * Load classes based on selected section
 */
function getClass() {
    var secId = $('#selectsection').val();
    if (secId) {
        $.ajax({
            url: SITE_URL + 'skool/ajax.php',
            type: 'POST',
            data: {
                action: 'getsubclass',
                sec_id: secId
            },
            success: function(data) {
                $('#selectclass').html(data);
            }
        });
    }
}

/**
 * Load subjects based on selected class
 */
function getSubject() {
    var classId = $('#selectclass').val();
    if (classId) {
        $.ajax({
            url: SITE_URL + 'skool/ajax.php',
            type: 'POST',
            data: {
                action: 'getsubject',
                class_id: classId
            },
            success: function(data) {
                $('#showsubject').html(data);
            }
        });
    }
}

// ============================================================================
// STYLES FOR DYNAMIC ELEMENTS
// ============================================================================

var dynamicStyles = `
.loading-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.5);
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
.loading-spinner i {
    margin-right: 10px;
}
.notification-toast {
    position: fixed;
    top: 80px;
    right: 20px;
    padding: 12px 20px;
    border-radius: 4px;
    color: white;
    z-index: 9999;
    display: none;
    box-shadow: 0 2px 10px rgba(0,0,0,0.2);
}
.notification-toast.bg-success { background: #28a745; }
.notification-toast.bg-danger { background: #dc3545; }
.notification-toast.bg-info { background: #17a2b8; }
.notification-toast i { margin-right: 8px; }
`;

$('head').append('<style>' + dynamicStyles + '</style>');
</script>

<!-- Optional: Dashboard specific scripts (uncomment if needed) -->
<!-- <script src="<?= SITE_URL ?>skool/assets-new/pages/jquery.dashboard.js"></script> -->