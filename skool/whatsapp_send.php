<?php
/**
 * ============================================================================
 * WHATSAPP SEND FUNCTION - Twilio Integration (COMPLETE WORKING)
 * ============================================================================
 * Sends WhatsApp messages with PDF attachments to parents
 * Uses Twilio WhatsApp Business API with public URL from existing PDF generator
 * ============================================================================
 */

require_once('whatsapp_config.php');

// ============================================================================
// CONFIGURATION
// ============================================================================
// Values are loaded from whatsapp_config.php above.

// ============================================================================
// SEND WHATSAPP MESSAGE WITH PDF ATTACHMENT
// ============================================================================
function sendWhatsAppResult($parentPhone, $studentName, $session, $term, $pdfPath, $pdfName = 'report_sheet.pdf') {
    $sid = TWILIO_ACCOUNT_SID;
    $token = TWILIO_AUTH_TOKEN;
    $from = TWILIO_WHATSAPP_NUMBER;
    
    // Format phone number
    $parentPhone = trim($parentPhone);
    if (substr($parentPhone, 0, 1) !== '+') {
        $parentPhone = '+' . $parentPhone;
    }
    
    // Check if pdfPath is a URL or a file path
    if (filter_var($pdfPath, FILTER_VALIDATE_URL)) {
        // It's already a URL - use it directly
        $publicUrl = $pdfPath;
    } else {
        // It's a file path - check if it exists and generate URL
        if (!file_exists($pdfPath)) {
            return ['success' => false, 'error' => 'PDF file not found: ' . $pdfPath];
        }
        $publicUrl = getPublicUrlForFile($pdfPath);
    }
    
    if (!$publicUrl) {
        return ['success' => false, 'error' => 'Could not generate public URL for PDF'];
    }
    
    // Debug - log the URL
    error_log("Sending WhatsApp with MediaUrl: " . $publicUrl);
    
    // Build the API URL
    $url = "https://api.twilio.com/2010-04-01/Accounts/$sid/Messages.json";
    
    // Build the request data with the MediaUrl
    $data = [
        'To' => 'whatsapp:' . $parentPhone,
        'From' => 'whatsapp:' . $from,
        'ContentSid' => 'HXb8bf0eacfaa6392bf7c6971e4d1047f0',
        'ContentVariables' => json_encode([
            '1' => $studentName,
            '2' => $session,
            '3' => $term
        ]),
        'MediaUrl' => $publicUrl
    ];
    
    // Send the request
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_USERPWD, "$sid:$token");
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/x-www-form-urlencoded'
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    $result = json_decode($response, true);
    
    if ($httpCode == 201 || $httpCode == 200) {
        return ['success' => true, 'response' => $result];
    } else {
        $error = $result['message'] ?? 'Unknown error';
        return ['success' => false, 'error' => $error, 'response' => $result];
    }
}

// ============================================================================
// GET PUBLIC BASE URL
// ============================================================================
function getSiteBaseUrl() {
    if (defined('PUBLIC_BASE_URL') && trim(PUBLIC_BASE_URL) !== '') {
        $baseUrl = trim(PUBLIC_BASE_URL);
        return rtrim($baseUrl, '/');
    }

    if (defined('SITE_URL') && trim(SITE_URL) !== '') {
        return rtrim(SITE_URL, '/');
    }

    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
    return $scheme . ($_SERVER['HTTP_HOST'] ?? 'localhost');
}

// ============================================================================
// GENERATE PUBLIC URL FOR A FILE
// ============================================================================
function getPublicUrlForFile($filePath) {
    $baseUrl = getSiteBaseUrl();

    // Get the relative path from the document root
    $docRoot = $_SERVER['DOCUMENT_ROOT'];
    $relativePath = str_replace($docRoot, '', $filePath);
    $relativePath = str_replace('\\', '/', $relativePath); // Windows fix

    // Build the full public URL
    return $baseUrl . $relativePath;
}

// ============================================================================
// GENERATE STUDENT RESULT PDF - USES YOUR EXISTING SKOOL TERM RESULT PDF
// ============================================================================
function generateStudentResultPDF($studentId, $sessionId, $termId, $classId, $schoolId) {
    // Get student's alphanumeric ID
    $studentData = db_get_row("SELECT student_id, randomid FROM manage_student WHERE id = ?", [$studentId]);
    
    if (empty($studentData['student_id'])) {
        return false;
    }
    
    // Get assessments for this class
    $assessments = db_get_rows(
        "SELECT id FROM school_assessment WHERE (class_id = ? OR class_id IS NULL OR class_id = 0) AND create_by_userid = ? ORDER BY id ASC",
        [$classId, $schoolId]
    );
    
    $assessmentIds = [];
    foreach ($assessments as $ass) {
        $assessmentIds[] = $ass['id'];
    }
    $assessmentParam = implode('-', $assessmentIds);
    
    // Get session and term names for the URL
    $sessionName = db_get_val("SELECT session FROM school_session WHERE id = ?", [$sessionId]);
    $termName = db_get_val("SELECT term FROM school_term WHERE id = ?", [$termId]);
    
    // Create the URL to your PDF generator
    $baseUrl = getSiteBaseUrl();
    $pdfUrl = $baseUrl . '/skool/skool_term_result_pdf.php?';
    $pdfUrl .= 'randomid=' . urlencode($studentData['randomid'] ?? '');
    $pdfUrl .= '&student_id=' . urlencode($studentData['student_id']);
    $pdfUrl .= '&session=' . urlencode($sessionId);
    $pdfUrl .= '&term_id=' . urlencode($termId);
    $pdfUrl .= '&class_id=' . urlencode($classId);
    $pdfUrl .= '&assesments=' . urlencode($assessmentParam);
    
    return $pdfUrl;
}

// ============================================================================
// BULK SEND RESULTS TO ALL PARENTS IN A CLASS
// ============================================================================
function bulkSendWhatsAppResults($classId, $sessionId, $termId, $schoolId) {
    // Get all students with parent phone numbers in this class
    $students = db_get_rows(
        "SELECT 
            ms.id as student_id,
            ms.student_id,
            ms.first_name,
            ms.last_name,
            sg.phone as parent_phone,
            sg.parent_id
         FROM manage_student ms
         LEFT JOIN student_guardian sg ON ms.parent_id = sg.parent_id
         WHERE ms.class = ? 
         AND ms.session = ?
         AND ms.term_id = ?
         AND ms.create_by_userid = ?
         AND sg.phone IS NOT NULL 
         AND sg.phone != ''
         AND sg.phone != 'NULL'
         ORDER BY ms.first_name ASC",
        [$classId, $sessionId, $termId, $schoolId]
    );
    
    $results = [];
    $successCount = 0;
    $failCount = 0;
    
    foreach ($students as $student) {
        $parentPhone = $student['parent_phone'];
        $studentName = $student['first_name'] . ' ' . $student['last_name'];
        
        // Get session and term names
        $sessionName = db_get_val("SELECT session FROM school_session WHERE id = ?", [$sessionId]);
        $termName = db_get_val("SELECT term FROM school_term WHERE id = ?", [$termId]);
        
        // Generate PDF URL (uses your existing PDF generator)
        $pdfUrl = generateStudentResultPDF($student['student_id'], $sessionId, $termId, $classId, $schoolId);
        
        if ($pdfUrl) {
            $result = sendWhatsAppResult(
                $parentPhone,
                $studentName,
                $sessionName,
                $termName,
                $pdfUrl,
                'report_' . $student['student_id'] . '.pdf'
            );
            
            if ($result['success']) {
                $successCount++;
                $results[] = [
                    'student' => $studentName,
                    'phone' => $parentPhone,
                    'status' => 'sent'
                ];
            } else {
                $failCount++;
                $results[] = [
                    'student' => $studentName,
                    'phone' => $parentPhone,
                    'status' => 'failed',
                    'error' => $result['error']
                ];
            }
        } else {
            $failCount++;
            $results[] = [
                'student' => $studentName,
                'phone' => $parentPhone,
                'status' => 'failed',
                'error' => 'PDF generation failed'
            ];
        }
    }
    
    return [
        'total' => count($students),
        'success' => $successCount,
        'failed' => $failCount,
        'results' => $results
    ];
}
?>