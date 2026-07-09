<?php
/**
 * Principal Signature - Modern PHP 8.x
 * Upload and manage principal's signature only (no dates)
 */

require_once('../config.php');
require_once('inc.session-create.php');

$PageTitle = "Principal Signature";
$FileName = 'principal_sign_term.php';

// ============================================================================
// INITIALIZATION
// ============================================================================
$stat = [];
$action = $_GET['action'] ?? '';
$randomid = $_GET['randomid'] ?? '';
$create_by_userid = $_SESSION['userid'] ?? 0;
$create_by_usertype = $_SESSION['usertype'] ?? '';

if (isset($_SESSION['success']) && $_SESSION['success'] != "") {
    $stat['success'] = $_SESSION['success'];
    unset($_SESSION['success']);
}

// ============================================================================
// UPLOAD PRINCIPAL SIGNATURE
// ============================================================================
if (isset($_POST['add_signature'])) {
    // Handle signature upload
    $signature = '';
    if (isset($_FILES['principal_sign']['name']) && !empty($_FILES['principal_sign']['name'])) {
        $ext = strtolower(pathinfo($_FILES['principal_sign']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg', 'png', 'jpeg', 'gif', 'webp'])) {
            $signature = md5(time() . uniqid()) . "_" . preg_replace('/[^a-zA-Z0-9._-]/', '_', $_FILES['principal_sign']['name']);
            move_uploaded_file($_FILES['principal_sign']['tmp_name'], "../uploads/" . $signature);
        } else {
            $stat['error'] = "Invalid file format. Please upload JPG, PNG, or GIF.";
        }
    } else {
        $stat['error'] = "Please select a signature image to upload.";
    }
    
    if (empty($stat['error']) && !empty($signature)) {
        $lastId = db_get_val("SELECT id FROM principal_sign_nextTerm ORDER BY id DESC") ?? 0;
        $newId = $lastId + 1;
        $randomId = randomFix(15) . '-' . $newId;
        
        db_insert("principal_sign_nextTerm", [
            'usertype' => $_SESSION['usertype'] ?? '',
            'userid' => $_SESSION['userid'] ?? 0,
            'sign' => $signature,
            'create_by_userid' => $create_by_userid,
            'create_by_usertype' => $create_by_usertype,
            'randomid' => $randomId,
        ]);
        
        $_SESSION['success'] = "Signature saved successfully";
        redirect($FileName);
    }
}

// ============================================================================
// DELETE SIGNATURE
// ============================================================================
if (isset($_GET['action']) && $_GET['action'] == 'delete_mas' && !empty($randomid)) {
    // Get signature file to delete
    $record = db_get_row("SELECT sign FROM principal_sign_nextTerm WHERE randomid = ?", [$randomid]);
    if (!empty($record['sign']) && file_exists("../uploads/" . $record['sign'])) {
        unlink("../uploads/" . $record['sign']);
    }
    db_delete("principal_sign_nextTerm", "randomid = ?", [$randomid]);
    $_SESSION['success'] = "Signature deleted successfully";
    redirect($FileName);
}

// ============================================================================
// GET DATA FOR DISPLAY
// ============================================================================
$records = db_get_rows("SELECT * FROM principal_sign_nextTerm WHERE create_by_userid = ? ORDER BY id DESC", [$create_by_userid]);
?>
<!DOCTYPE html>
<html>
<head>
    <?php include('inc.meta.php'); ?>
    <style>
        * { box-sizing: border-box; }
        .signature-container { max-width: 1000px; margin: 0 auto; padding: 20px; }
        .signature-card { background: #fff; border-radius: 24px; box-shadow: 0 10px 40px rgba(0,0,0,0.08); overflow: hidden; margin-bottom: 30px; }
        .signature-header { background: linear-gradient(135deg, #1B3058 0%, #2a4780 100%); color: white; padding: 25px 30px; }
        .signature-header h2 { margin: 0; font-size: 24px; font-weight: 600; }
        .signature-header p { margin: 8px 0 0; opacity: 0.8; }
        .signature-body { padding: 30px; }
        .upload-area { border: 2px dashed #ddd; border-radius: 16px; padding: 40px; text-align: center; transition: all 0.2s; cursor: pointer; background: #fafafa; margin-bottom: 20px; }
        .upload-area:hover { border-color: #1B3058; background: #f5f7ff; }
        .upload-area i { font-size: 48px; color: #1B3058; margin-bottom: 15px; display: block; }
        .upload-area p { margin: 0; color: #666; }
        .preview-area { text-align: center; margin-top: 20px; padding: 20px; background: #f8f9fa; border-radius: 16px; display: none; }
        .signature-preview { max-width: 250px; max-height: 100px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .btn { padding: 12px 28px; border: none; border-radius: 40px; cursor: pointer; font-weight: 600; font-size: 14px; transition: all 0.2s; }
        .btn-primary { background: #1B3058; color: white; }
        .btn-primary:hover { background: #f21151; transform: translateY(-2px); box-shadow: 0 5px 15px rgba(242,17,81,0.3); }
        .data-table { width: 100%; border-collapse: collapse; }
        .data-table th, .data-table td { padding: 15px; text-align: left; border-bottom: 1px solid #f0f0f0; vertical-align: middle; }
        .data-table th { background: #f8f9fa; font-weight: 600; color: #1B3058; }
        .data-table tr:hover { background: #fafafa; }
        .signature-img { max-width: 100px; max-height: 60px; border-radius: 6px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        .action-icons a { margin: 0 5px; color: #666; text-decoration: none; font-size: 16px; }
        .action-icons a:hover { color: #f21151; }
        .alert { padding: 14px 18px; border-radius: 12px; margin-bottom: 20px; }
        .alert-success { background: #d4edda; color: #155724; border-left: 4px solid #28a745; }
        .alert-danger { background: #f8d7da; color: #721c24; border-left: 4px solid #dc3545; }
        .empty-state { text-align: center; padding: 60px; color: #999; }
        .empty-state i { font-size: 64px; color: #ccc; margin-bottom: 15px; display: block; }
        @media (max-width: 768px) { .data-table { display: block; overflow-x: auto; } .upload-area { padding: 20px; } }
    </style>
</head>
<body>
<div id="wrapper">
    <?php include('inc.header.php'); ?>
    <?php include('inc.sideleft.php'); ?>
    <div class="content-page">
        <div class="content">
            <div class="signature-container">
                <!-- Header -->
                <div style="margin-bottom: 20px;">
                    <h2 style="margin:0; color:#1B3058;">✍️ <?= $PageTitle ?></h2>
                    <p style="color:#666; margin-top:5px;">Upload and manage the principal's signature for official documents</p>
                </div>

                <?= showMessage($stat) ?>

                <!-- Upload Signature Card -->
                <div class="signature-card">
                    <div class="signature-header">
                        <h2><i class="fa fa-upload"></i> Upload Principal Signature</h2>
                        <p>Upload a clear image of the principal's signature (JPG, PNG, or GIF)</p>
                    </div>
                    <div class="signature-body">
                        <form method="post" enctype="multipart/form-data" id="signatureForm">
                            <div class="upload-area" id="uploadArea">
                                <i class="fa fa-cloud-upload"></i>
                                <p>Click or drag and drop to upload signature</p>
                                <p style="font-size: 12px; margin-top: 10px;">Supported formats: JPG, PNG, GIF, JPEG</p>
                                <input type="file" name="principal_sign" id="signatureInput" accept="image/*" style="display: none;" required>
                            </div>
                            <div class="preview-area" id="previewArea">
                                <p style="margin-bottom: 10px;">Preview:</p>
                                <img id="signaturePreview" class="signature-preview" src="#" alt="Signature Preview">
                            </div>
                            <div style="text-align: right; margin-top: 20px;">
                                <button type="submit" name="add_signature" class="btn btn-primary" id="submitBtn" disabled><i class="fa fa-save"></i> Save Signature</button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Signatures List Card -->
                <div class="signature-card">
                    <div class="signature-header" style="background: linear-gradient(135deg, #2c3e50 0%, #3498db 100%);">
                        <h2><i class="fa fa-list"></i> Saved Signatures</h2>
                        <p>List of all uploaded principal signatures</p>
                    </div>
                    <div class="signature-body">
                        <?php if (!empty($records)): ?>
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Signature</th>
                                        <th style="width: 100px;">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $i = 1; foreach ($records as $record): ?>
                                        <tr>
                                            <td><?= $i++ ?></td>
                                            <td><img src="../uploads/<?= htmlspecialchars($record['sign']) ?>" class="signature-img" alt="Signature"></td>
                                            <td class="action-icons">
                                                <a href="javascript:del('?action=delete_mas&randomid=<?= $record['randomid'] ?>')" title="Delete" onclick="return confirm('Delete this signature?')"><i class="fa fa-trash"></i></a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php else: ?>
                            <div class="empty-state">
                                <i class="fa fa-pencil-square-o"></i>
                                <h3>No Signatures Found</h3>
                                <p>Upload the principal's signature using the form above.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php include('inc.footer.php'); ?>
    </div>
</div>
<?php include('inc.js.php'); ?>
<script>
    // Upload area click handler
    const uploadArea = document.getElementById('uploadArea');
    const signatureInput = document.getElementById('signatureInput');
    const previewArea = document.getElementById('previewArea');
    const signaturePreview = document.getElementById('signaturePreview');
    const submitBtn = document.getElementById('submitBtn');
    
    if (uploadArea) {
        uploadArea.addEventListener('click', function() {
            signatureInput.click();
        });
        
        // Drag and drop
        uploadArea.addEventListener('dragover', function(e) {
            e.preventDefault();
            uploadArea.style.borderColor = '#1B3058';
            uploadArea.style.background = '#f5f7ff';
        });
        
        uploadArea.addEventListener('dragleave', function(e) {
            e.preventDefault();
            uploadArea.style.borderColor = '#ddd';
            uploadArea.style.background = '#fafafa';
        });
        
        uploadArea.addEventListener('drop', function(e) {
            e.preventDefault();
            uploadArea.style.borderColor = '#ddd';
            uploadArea.style.background = '#fafafa';
            const file = e.dataTransfer.files[0];
            if (file && file.type.match('image.*')) {
                signatureInput.files = e.dataTransfer.files;
                previewImage(file);
            }
        });
    }
    
    if (signatureInput) {
        signatureInput.addEventListener('change', function(e) {
            if (this.files && this.files[0]) {
                previewImage(this.files[0]);
            }
        });
    }
    
    function previewImage(file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            signaturePreview.src = e.target.result;
            previewArea.style.display = 'block';
            submitBtn.disabled = false;
        }
        reader.readAsDataURL(file);
    }
    
    function del(url) {
        if (confirm('Are you sure you want to delete this signature? This action cannot be undone.')) {
            window.location.href = url;
        }
    }
</script>
</body>
</html>