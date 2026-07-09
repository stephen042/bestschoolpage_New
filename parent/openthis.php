<?php
/**
 * Voice Call API Handler - Rebuilt for PHP 8.x
 * Sends voice calls using external API with proper error handling
 */

// ============================================================================
// CONFIGURATION
// ============================================================================
define('API_BASE_URL', 'http://182.70.254.8:4000/SENDVOICEAPI');
define('API_KEY', '78d289d3-ff36-416a-a135-70606d51c8b6');
define('API_USERNAME', 'user');
define('API_SERVICE', 'VOICEJAR');
define('DEFAULT_CALLER_ID', '9999999999');

// ============================================================================
// FUNCTION TO SEND VOICE CALL
// ============================================================================
function sendVoiceCall($fileUrl, $phoneNumber, $messageId = 'atest1') {
    // Validate phone number
    if (empty($phoneNumber) || !preg_match('/^[0-9]{10}$/', $phoneNumber)) {
        return ['error' => true, 'message' => 'Invalid phone number. Must be 10 digits.'];
    }
    
    // Validate file URL
    if (empty($fileUrl) || !filter_var($fileUrl, FILTER_VALIDATE_URL)) {
        return ['error' => true, 'message' => 'Invalid file URL.'];
    }
    
    // Build API URL
    $params = [
        'fileurl' => $fileUrl,
        'numbers' => $phoneNumber,
        'apikey' => API_KEY,
        'username' => API_USERNAME,
        'service' => API_SERVICE,
        'callerid' => DEFAULT_CALLER_ID,
        'msgid' => $messageId
    ];
    
    $apiUrl = API_BASE_URL . '?' . http_build_query($params);
    
    // Initialize cURL
    $ch = curl_init();
    
    // Set cURL options
    curl_setopt_array($ch, [
        CURLOPT_URL => $apiUrl,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CONNECTTIMEOUT => 7,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_USERAGENT => "Mozilla/5.0 (compatible; VoiceCallAPI/1.0)",
        CURLOPT_HEADER => false,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_SSL_VERIFYPEER => false, // Only if API doesn't have valid SSL
        CURLOPT_SSL_VERIFYHOST => false
    ]);
    
    // Execute cURL request
    $result = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    
    curl_close($ch);
    
    // Handle response
    if ($curlError) {
        return [
            'error' => true,
            'message' => 'cURL Error: ' . $curlError,
            'http_code' => null
        ];
    }
    
    if ($httpCode !== 200) {
        return [
            'error' => true,
            'message' => 'API returned HTTP code: ' . $httpCode,
            'response' => $result,
            'http_code' => $httpCode
        ];
    }
    
    // Try to decode JSON response
    $decodedResult = json_decode($result, true);
    
    return [
        'error' => false,
        'message' => 'Call sent successfully',
        'response' => $decodedResult ?: $result,
        'http_code' => $httpCode,
        'api_url' => $apiUrl // Remove in production for security
    ];
}

// ============================================================================
// FUNCTION TO SEND BULK VOICE CALLS
// ============================================================================
function sendBulkVoiceCalls($fileUrl, $phoneNumbers, $messageIdPrefix = 'bulk') {
    $results = [];
    $successCount = 0;
    $failureCount = 0;
    
    foreach ($phoneNumbers as $index => $phoneNumber) {
        $messageId = $messageIdPrefix . '_' . ($index + 1);
        $result = sendVoiceCall($fileUrl, $phoneNumber, $messageId);
        
        $results[] = [
            'phone' => $phoneNumber,
            'success' => !$result['error'],
            'message' => $result['message']
        ];
        
        if (!$result['error']) {
            $successCount++;
        } else {
            $failureCount++;
        }
        
        // Small delay to avoid rate limiting
        usleep(500000); // 0.5 seconds delay
    }
    
    return [
        'total' => count($phoneNumbers),
        'success' => $successCount,
        'failed' => $failureCount,
        'details' => $results
    ];
}

// ============================================================================
// EXAMPLE USAGE
// ============================================================================

// Single call example
$fileUrl = 'http://nagendramishra.com/voicecallnew/uploads/e400b5461f75735d4f670cdf5fd50525_8520190107113312.wav';
$phoneNumber = '9893304801';
$messageId = 'atest1';

// Display request info (for debugging - remove in production)
echo "<!-- Voice Call API Request -->\n";
echo "File URL: " . htmlspecialchars($fileUrl) . "<br>\n";
echo "Phone Number: " . htmlspecialchars($phoneNumber) . "<br>\n";

// Send the voice call
$response = sendVoiceCall($fileUrl, $phoneNumber, $messageId);

// Display response
if ($response['error']) {
    echo "<div style='color: red; padding: 10px; border: 1px solid red; margin: 10px 0;'>\n";
    echo "<strong>Error:</strong> " . htmlspecialchars($response['message']) . "<br>\n";
    if (isset($response['http_code'])) {
        echo "<strong>HTTP Code:</strong> " . $response['http_code'] . "<br>\n";
    }
    echo "</div>\n";
} else {
    echo "<div style='color: green; padding: 10px; border: 1px solid green; margin: 10px 0;'>\n";
    echo "<strong>Success:</strong> " . htmlspecialchars($response['message']) . "<br>\n";
    
    if (is_array($response['response'])) {
        echo "<strong>API Response:</strong><pre>";
        print_r($response['response']);
        echo "</pre>\n";
    } else {
        echo "<strong>API Response:</strong> " . htmlspecialchars($response['response']) . "<br>\n";
    }
    echo "</div>\n";
}

// ============================================================================
// BULK CALL EXAMPLE (Uncomment to use)
// ============================================================================
/*
$phoneNumbers = [
    '9893304801',
    '9893304802',
    '9893304803'
];

$bulkResponse = sendBulkVoiceCalls($fileUrl, $phoneNumbers, 'batch1');
echo "<pre>";
print_r($bulkResponse);
echo "</pre>";
*/
?>

<!-- Optional: Simple HTML form for testing -->
<div style="margin-top: 20px; padding: 15px; border: 1px solid #ccc; border-radius: 5px;">
    <h3>Test Voice Call</h3>
    <form method="post" action="">
        <div style="margin-bottom: 10px;">
            <label>File URL:</label><br>
            <input type="text" name="fileurl" style="width: 100%;" value="<?= htmlspecialchars($_POST['fileurl'] ?? $fileUrl) ?>">
        </div>
        <div style="margin-bottom: 10px;">
            <label>Phone Number:</label><br>
            <input type="text" name="phone" style="width: 100%;" value="<?= htmlspecialchars($_POST['phone'] ?? $phoneNumber) ?>">
        </div>
        <div style="margin-bottom: 10px;">
            <label>Message ID:</label><br>
            <input type="text" name="msgid" style="width: 100%;" value="<?= htmlspecialchars($_POST['msgid'] ?? $messageId) ?>">
        </div>
        <button type="submit" name="send_call" style="background: #1B3058; color: white; padding: 10px 20px; border: none; cursor: pointer;">
            Send Voice Call
        </button>
    </form>
</div>

<?php
// Handle form submission
if (isset($_POST['send_call'])) {
    $fileUrl = $_POST['fileurl'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $msgId = $_POST['msgid'] ?? 'web_form';
    
    if (!empty($fileUrl) && !empty($phone)) {
        echo "<hr><h3>Form Submission Result:</h3>";
        $formResponse = sendVoiceCall($fileUrl, $phone, $msgId);
        
        if ($formResponse['error']) {
            echo "<div style='color: red; padding: 10px; border: 1px solid red;'>";
            echo "Error: " . htmlspecialchars($formResponse['message']);
            echo "</div>";
        } else {
            echo "<div style='color: green; padding: 10px; border: 1px solid green;'>";
            echo "Success: Voice call sent successfully!";
            echo "</div>";
        }
    } else {
        echo "<div style='color: red; padding: 10px; border: 1px solid red;'>";
        echo "Please provide both File URL and Phone Number.";
        echo "</div>";
    }
}
?>