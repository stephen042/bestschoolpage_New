<?php
/**
 * ============================================================================
 * BULK SEND RESULTS TO WHATSAPP - ADMIN PAGE
 * ============================================================================
 * School admin can select class and send results to all parents
 * ============================================================================
 */

require_once('../config.php');
require_once('inc.session-create.php');
require_once('whatsapp_send.php');

// ============================================================================
// ACCESS CONTROL - Only school admin can access
// ============================================================================
if (empty($_SESSION['userid'])) {
    redirect(SKOOL_URL . 'login.php');
    exit;
}

if ($_SESSION['usertype'] != '1' && $_SESSION['usertype'] != '0') {
    redirect(SKOOL_URL . 'index.php');
    exit;
}

$PageTitle = "Bulk Send Results to WhatsApp";
$FileName = 'bulk_send_whatsapp.php';

// ============================================================================
// GET SCHOOL ID
// ============================================================================
$schoolId = $_SESSION['userid'];

// ============================================================================
// GET FILTERS
// ============================================================================
$selectedSession = $_GET['session'] ?? '';
$selectedTerm = $_GET['term_id'] ?? '';
$selectedClass = $_GET['class_id'] ?? '';
$sendStatus = '';
$sendMessage = '';
$sendResults = [];

// ============================================================================
// PROCESS BULK SEND
// ============================================================================
if (isset($_POST['send_whatsapp'])) {
    $classId = $_POST['class_id'] ?? 0;
    $sessionId = $_POST['session_id'] ?? 0;
    $termId = $_POST['term_id'] ?? 0;
    
    if ($classId && $sessionId && $termId) {
        $result = bulkSendWhatsAppResults($classId, $sessionId, $termId, $schoolId);
        
        if ($result['success'] > 0) {
            $sendStatus = 'success';
            $sendMessage = "✅ {$result['success']} parent(s) received the results successfully!";
            if ($result['failed'] > 0) {
                $sendMessage .= " ❌ {$result['failed']} failed.";
            }
        } else {
            $sendStatus = 'error';
            $sendMessage = "❌ Failed to send results. Please check the logs.";
        }
        
        // Store results for display
        $sendResults = $result['results'] ?? [];
    }
}

// ============================================================================
// GET DATA FOR DROPDOWNS
// ============================================================================
$sessions = db_get_rows(
    "SELECT * FROM school_session WHERE create_by_userid = ? ORDER BY id DESC",
    [$schoolId]
);

$terms = db_get_rows(
    "SELECT * FROM school_term WHERE create_by_userid = ? ORDER BY id ASC",
    [$schoolId]
);

$classes = db_get_rows(
    "SELECT * FROM school_class WHERE create_by_userid = ? ORDER BY name ASC",
    [$schoolId]
);
?>
<!DOCTYPE html>
<html>
<head>
    <?php include('inc.meta.php'); ?>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { background: #f0f2f5; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        
        .container-modern { max-width: 1200px; margin: 0 auto; padding: 20px; }
        .page-header { margin-bottom: 30px; }
        .page-header h2 { color: #1B3058; margin: 0; font-size: 24px; }
        .page-header p { color: #666; margin-top: 5px; }
        
        .card { background: #fff; border-radius: 20px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); overflow: hidden; margin-bottom: 30px; }
        .card-header { background: linear-gradient(135deg, #1B3058 0%, #2a4780 100%); color: white; padding: 18px 25px; }
        .card-header h3 { margin: 0; font-size: 18px; font-weight: 600; }
        .card-body { padding: 25px; }
        
        .form-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; }
        .form-group { margin-bottom: 5px; }
        .form-group label { display: block; font-weight: 600; margin-bottom: 6px; color: #333; font-size: 13px; }
        .form-control, .form-select {
            width: 100%; padding: 10px 12px; border: 1px solid #ddd; border-radius: 10px; font-size: 14px;
        }
        .btn { padding: 10px 24px; border: none; border-radius: 12px; cursor: pointer; font-weight: 600; transition: all 0.2s; }
        .btn-success { background: #28a745; color: white; }
        .btn-success:hover { background: #218838; transform: translateY(-2px); }
        .btn-primary { background: #1B3058; color: white; }
        .btn-primary:hover { background: #f21151; transform: translateY(-2px); }
        
        .alert { padding: 15px 20px; border-radius: 12px; margin-bottom: 20px; }
        .alert-success { background: #d4edda; color: #155724; border-left: 4px solid #28a745; }
        .alert-danger { background: #f8d7da; color: #721c24; border-left: 4px solid #dc3545; }
        .alert-info { background: #d1ecf1; color: #0c5460; border-left: 4px solid #17a2b8; }
        
        .results-table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .results-table th, .results-table td { padding: 10px; text-align: left; border-bottom: 1px solid #eee; }
        .results-table th { background: #f8f9fa; font-weight: 600; }
        .status-sent { color: #28a745; font-weight: 600; }
        .status-failed { color: #dc3545; font-weight: 600; }
        
        .summary-box { padding: 15px; background: #f8f9fa; border-radius: 12px; margin-bottom: 20px; }
        .summary-box strong { font-size: 18px; }
        
        @media (max-width: 768px) { .form-grid { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
<div id="wrapper">
    <?php include('inc.header.php'); ?>
    <?php include('inc.sideleft.php'); ?>
    <div class="content-page">
        <div class="content">
            <div class="container-modern">
                
                <!-- Page Header -->
                <div class="page-header">
                    <h2><i class="fa fa-whatsapp" style="color:#25D366;"></i> <?= $PageTitle ?></h2>
                    <p>Select a class and send report sheets to all parents via WhatsApp</p>
                </div>

                <!-- Send Form -->
                <div class="card">
                    <div class="card-header">
                        <h3><i class="fa fa-paper-plane"></i> Send Results to Parents</h3>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <div class="form-grid">
                                <div class="form-group">
                                    <label>Select Session *</label>
                                    <select name="session_id" class="form-select" required>
                                        <option value="">-- Select Session --</option>
                                        <?php foreach ($sessions as $session): ?>
                                            <option value="<?= $session['id'] ?>" <?= ($selectedSession == $session['id']) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($session['session']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Select Term *</label>
                                    <select name="term_id" class="form-select" required>
                                        <option value="">-- Select Term --</option>
                                        <?php foreach ($terms as $term): ?>
                                            <option value="<?= $term['id'] ?>" <?= ($selectedTerm == $term['id']) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($term['term']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Select Class *</label>
                                    <select name="class_id" class="form-select" required>
                                        <option value="">-- Select Class --</option>
                                        <?php foreach ($classes as $class): ?>
                                            <option value="<?= $class['id'] ?>" <?= ($selectedClass == $class['id']) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($class['name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div style="margin-top: 20px; text-align: right;">
                                <button type="submit" name="send_whatsapp" class="btn btn-success" onclick="return confirm('Are you sure you want to send results to all parents in this class?')">
                                    <i class="fa fa-whatsapp"></i> Send Results via WhatsApp
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Send Results -->
                <?php if (!empty($sendStatus) && !empty($sendMessage)): ?>
                    <div class="card">
                        <div class="card-header" style="background: linear-gradient(135deg, #25D366 0%, #128C7E 100%);">
                            <h3><i class="fa fa-check-circle"></i> Send Results Summary</h3>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-<?= $sendStatus == 'success' ? 'success' : 'danger' ?>">
                                <?= $sendMessage ?>
                            </div>
                            
                            <?php if (!empty($sendResults)): ?>
                                <div class="summary-box">
                                    <strong>Total: <?= count($sendResults) ?></strong> |
                                    ✅ Sent: <?= count(array_filter($sendResults, function($r) { return $r['status'] == 'sent'; })) ?> |
                                    ❌ Failed: <?= count(array_filter($sendResults, function($r) { return $r['status'] == 'failed'; })) ?>
                                </div>
                                
                                <table class="results-table">
                                    <thead>
                                        <tr>
                                            <th>Student</th>
                                            <th>Phone Number</th>
                                            <th>Status</th>
                                            <th>Error</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($sendResults as $result): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($result['student']) ?></td>
                                                <td><?= htmlspecialchars($result['phone']) ?></td>
                                                <td class="status-<?= $result['status'] ?>">
                                                    <?= $result['status'] == 'sent' ? '✅ Sent' : '❌ Failed' ?>
                                                </td>
                                                <td style="font-size: 11px; color: #999;">
                                                    <?= htmlspecialchars($result['error'] ?? '') ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Info Box -->
                <div class="card">
                    <div class="card-header" style="background: linear-gradient(135deg, #6c757d 0%, #495057 100%);">
                        <h3><i class="fa fa-info-circle"></i> Information</h3>
                    </div>
                    <div class="card-body">
                        <ul style="padding-left: 20px; line-height: 1.8;">
                            <li><strong>📱 WhatsApp Sender:</strong> <?= TWILIO_WHATSAPP_NUMBER ?></li>
                            <li><strong>📄 Template:</strong> <?= WHATSAPP_TEMPLATE_NAME ?></li>
                            <li><strong>⚠️ Important:</strong> Only parents with phone numbers in their profile will receive messages.</li>
                            <li><strong>📎 PDF Size:</strong> Max 5MB per file.</li>
                            <li><strong>💰 Cost:</strong> ~$0.008 per parent per message.</li>
                        </ul>
                    </div>
                </div>

            </div>
        </div>
        <?php include('inc.footer.php'); ?>
    </div>
</div>
<?php include('inc.js.php'); ?>
</body>
</html>