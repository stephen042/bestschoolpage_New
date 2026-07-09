<?php
/**
 * ============================================================================
 * TEST WHATSAPP SEND - Complete Test
 * ============================================================================
 */

require_once('../config.php');
require_once('inc.session-create.php');
require_once('whatsapp_send.php');

// Only allow admin
if ($_SESSION['usertype'] != '1' && $_SESSION['usertype'] != '0') {
    die("Access denied");
}

echo "<h1>WhatsApp Send Test</h1>";
echo "<hr>";

// ============================================================================
// TEST 1: Generate PDF URL
// ============================================================================
echo "<h2>1. Generating PDF URL</h2>";

$testStudentId = 20479; // Student ID
$testSessionId = 71;    // Session ID
$testTermId = 90;       // Term ID
$testClassId = 398;     // Class ID
$schoolId = 463;        // School ID

$pdfUrl = generateStudentResultPDF($testStudentId, $testSessionId, $testTermId, $testClassId, $schoolId);

echo "Student ID: " . $testStudentId . "<br>";
echo "Session ID: " . $testSessionId . "<br>";
echo "Term ID: " . $testTermId . "<br>";
echo "Class ID: " . $testClassId . "<br>";
echo "School ID: " . $schoolId . "<br>";

if ($pdfUrl) {
    echo "✅ PDF URL generated: <a href='" . $pdfUrl . "' target='_blank'>" . $pdfUrl . "</a><br>";
    
    // Test if URL is accessible
    $headers = @get_headers($pdfUrl);
    echo "URL Status: " . ($headers && strpos($headers[0], '200') ? '✅ Accessible' : '❌ Not accessible') . "<br>";
} else {
    echo "❌ PDF generation failed<br>";
}

echo "<hr>";

// ============================================================================
// TEST 2: Send WhatsApp Message
// ============================================================================
echo "<h2>2. Sending WhatsApp Message</h2>";

if ($pdfUrl) {
    // Replace with your phone number for testing
    $testPhone = '+2347066768678';
    $studentName = 'Zina Suru';
    $session = '2025/2026';
    $term = 'First Term';
    
    echo "Sending to: " . $testPhone . "<br>";
    echo "Student: " . $studentName . "<br>";
    echo "Session: " . $session . "<br>";
    echo "Term: " . $term . "<br>";
    echo "PDF URL: " . $pdfUrl . "<br>";
    
    echo "<br><strong>Result:</strong><br>";
    
    $result = sendWhatsAppResult(
        $testPhone,
        $studentName,
        $session,
        $term,
        $pdfUrl,
        'test_report.pdf'
    );
    
    echo "<pre>";
    print_r($result);
    echo "</pre>";
    
    if ($result['success']) {
        echo "✅ Message sent successfully! Check your WhatsApp.<br>";
    } else {
        echo "❌ Error: " . ($result['error'] ?? 'Unknown error') . "<br>";
        if (!empty($result['response'])) {
            echo "Response: ";
            print_r($result['response']);
        }
    }
}

echo "<hr>";
echo "<h3>Next Steps:</h3>";
echo "<ul>";
echo "<li>If test works, go to <a href='bulk_send_whatsapp.php'>Bulk Send Results</a></li>";
echo "<li>If it fails, check the error message above</li>";
echo "</ul>";
?>