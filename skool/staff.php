<?php
/**
 * Staff Management - Modern UI with PDF Export
 * Sidebar layout matching Student page
 */

require_once('../config.php');
require_once('inc.session-create.php');

$PageTitle = "Staff Management";
$FileName = 'staff.php';
$create_by_userid = $_SESSION['userid'] ?? 0;

// ============================================================================
// INITIALIZATION
// ============================================================================
$stat = [];
$randomid = $_GET['randomid'] ?? '';
$search_name = $_GET['search'] ?? '';

if (isset($_SESSION['success']) && $_SESSION['success'] != "") {
    $stat['success'] = $_SESSION['success'];
    unset($_SESSION['success']);
}

// Get school info for PDF
$school = db_get_row("SELECT name, logo FROM school_register WHERE id = ?", [$create_by_userid]);

// ============================================================================
// ADD STAFF
// ============================================================================
if (isset($_POST['add_staff'])) {
    $staffId = trim($_POST['staff_id'] ?? '');
    $firstName = mb_strtoupper(trim($_POST['first_name'] ?? ''), 'UTF-8');
    $lastName = mb_strtoupper(trim($_POST['last_name'] ?? ''), 'UTF-8');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    
    $errors = [];
    if (empty($staffId)) $errors[] = "Staff ID is required";
    if (empty($firstName)) $errors[] = "First Name is required";
    if (empty($lastName)) $errors[] = "Last Name is required";
    if (empty($email)) $errors[] = "Email is required";
    
    if (empty($errors)) {
        $existing = db_get_val("SELECT id FROM staff_manage WHERE staff_id = ?", [$staffId]);
        if ($existing) {
            $stat['error'] = "Staff ID already exists";
        } else {
            // Handle picture upload
            $picture = '';
            if (isset($_FILES['picture']['name']) && !empty($_FILES['picture']['name'])) {
                $ext = strtolower(pathinfo($_FILES['picture']['name'], PATHINFO_EXTENSION));
                if (in_array($ext, ['jpg', 'png', 'jpeg', 'gif', 'webp'])) {
                    $picture = md5(time() . uniqid()) . "_" . preg_replace('/[^a-zA-Z0-9._-]/', '_', $_FILES['picture']['name']);
                    move_uploaded_file($_FILES['picture']['tmp_name'], "../uploads/" . $picture);
                }
            }
            
            $randomId = randomFix(15) . '-' . time();
            
            $staffData = [
                'staff_id' => $staffId,
                'first_name' => $firstName,
                'last_name' => $lastName,
                'email' => $email,
                'phone' => $phone,
                'picture' => $picture,
                'create_by_userid' => $create_by_userid,
                'randomid' => $randomId,
            ];
            
            $insertId = db_insert("staff_manage", $staffData);
            
            if ($insertId) {
                $_SESSION['success'] = "Staff added successfully!";
                redirect($FileName . '?randomid=' . $randomId);
            } else {
                $stat['error'] = "Failed to add staff";
            }
        }
    } else {
        $stat['error'] = implode("<br>", $errors);
    }
}

// ============================================================================
// UPDATE STAFF
// ============================================================================
if (isset($_POST['edit_staff']) && !empty($randomid)) {
    $staffId = trim($_POST['staff_id'] ?? '');
    $firstName = mb_strtoupper(trim($_POST['first_name'] ?? ''), 'UTF-8');
    $lastName = mb_strtoupper(trim($_POST['last_name'] ?? ''), 'UTF-8');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    
    $picture = $_POST['picture_old'] ?? '';
    if (isset($_FILES['picture']['name']) && !empty($_FILES['picture']['name'])) {
        $ext = strtolower(pathinfo($_FILES['picture']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg', 'png', 'jpeg', 'gif', 'webp'])) {
            $picture = md5(time() . uniqid()) . "_" . preg_replace('/[^a-zA-Z0-9._-]/', '_', $_FILES['picture']['name']);
            move_uploaded_file($_FILES['picture']['tmp_name'], "../uploads/" . $picture);
            $oldStaff = db_get_row("SELECT picture FROM staff_manage WHERE randomid = ?", [$randomid]);
            if (!empty($oldStaff['picture']) && file_exists("../uploads/" . $oldStaff['picture'])) {
                unlink("../uploads/" . $oldStaff['picture']);
            }
        }
    }
    
    db_update("staff_manage", [
        'staff_id' => $staffId,
        'first_name' => $firstName,
        'last_name' => $lastName,
        'email' => $email,
        'phone' => $phone,
        'picture' => $picture,
    ], "randomid = ?", [$randomid]);
    
    $_SESSION['success'] = "Staff updated successfully";
    redirect($FileName . '?randomid=' . $randomid);
}

// ============================================================================
// DELETE STAFF
// ============================================================================
if (isset($_GET['delete']) && !empty($_GET['delete'])) {
    $staff = db_get_row("SELECT * FROM staff_manage WHERE randomid = ?", [$_GET['delete']]);
    if ($staff) {
        if (!empty($staff['picture']) && file_exists("../uploads/" . $staff['picture'])) {
            unlink("../uploads/" . $staff['picture']);
        }
        db_delete("staff_manage", "randomid = ?", [$_GET['delete']]);
        $_SESSION['success'] = "Staff deleted successfully";
    }
    redirect($FileName);
}

// ============================================================================
// EXPORT STAFF TO PDF
// ============================================================================
if (isset($_GET['export']) && $_GET['export'] === 'pdf') {
    $exportStaff = db_get_rows("SELECT * FROM staff_manage WHERE create_by_userid = ? ORDER BY first_name ASC", [$create_by_userid]);
    
    if (!empty($exportStaff)) {
        // Generate HTML for PDF
        $html = '<html>';
        $html .= '<head><meta charset="UTF-8"><title>Staff List</title></head>';
        $html .= '<body style="font-family: Arial, sans-serif; margin: 20px;">';
        
        // School header with logo
        $html .= '<div style="text-align: center; margin-bottom: 30px; border-bottom: 2px solid #1B3058; padding-bottom: 15px;">';
        if (!empty($school['logo']) && file_exists("../uploads/" . $school['logo'])) {
            $html .= '<img src="../uploads/' . htmlspecialchars($school['logo']) . '" style="height: 60px; margin-bottom: 10px;">';
        }
        $html .= '<h1 style="margin: 5px 0; color: #1B3058;">' . htmlspecialchars($school['name'] ?? 'School') . '</h1>';
        $html .= '<h2 style="margin: 5px 0; color: #666; font-size: 18px;">Staff List</h2>';
        $html .= '<p style="color: #999; margin: 5px 0;">Generated on ' . date('d M Y, H:i') . '</p>';
        $html .= '</div>';
        
        // Staff Table
        $html .= '<table style="width: 100%; border-collapse: collapse; margin-top: 20px;">';
        $html .= '<thead>';
        $html .= '<tr style="background: #1B3058; color: white;">';
        $html .= '<th style="padding: 12px; border: 1px solid #ccc; text-align: left;">Staff ID</th>';
        $html .= '<th style="padding: 12px; border: 1px solid #ccc; text-align: left;">Name</th>';
        $html .= '<th style="padding: 12px; border: 1px solid #ccc; text-align: left;">Email</th>';
        $html .= '<th style="padding: 12px; border: 1px solid #ccc; text-align: left;">Phone</th>';
        $html .= '</tr>';
        $html .= '</thead>';
        $html .= '<tbody>';
        
        foreach ($exportStaff as $staff) {
            $html .= '<tr style="border: 1px solid #ddd;">';
            $html .= '<td style="padding: 10px; border: 1px solid #ddd;">' . htmlspecialchars($staff['staff_id']) . '</td>';
            $html .= '<td style="padding: 10px; border: 1px solid #ddd;">' . htmlspecialchars($staff['first_name'] . ' ' . $staff['last_name']) . '</td>';
            $html .= '<td style="padding: 10px; border: 1px solid #ddd;">' . htmlspecialchars($staff['email']) . '</td>';
            $html .= '<td style="padding: 10px; border: 1px solid #ddd;">' . htmlspecialchars($staff['phone']) . '</td>';
            $html .= '</tr>';
        }
        
        $html .= '</tbody>';
        $html .= '</table>';
        
        $html .= '<p style="margin-top: 30px; color: #999; font-size: 12px; text-align: center;">Total Staff: ' . count($exportStaff) . '</p>';
        $html .= '</body></html>';
        
        // Check if DOMPDF is available
        $dompdfPath = dirname(__DIR__) . '/dompdf_New/autoload.inc.php';
        if (file_exists($dompdfPath)) {
            require_once($dompdfPath);
            
            // Use Dompdf namespace (inside PHP block, after require)
            $dompdf = new \Dompdf\Dompdf();
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();
            
            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="staff_list_' . date('Y-m-d_H-i-s') . '.pdf"');
            echo $dompdf->output();
            exit;
        } else {
            // Fallback: Send as HTML document
            header('Content-Type: text/html; charset=utf-8');
            header('Content-Disposition: attachment; filename="staff_list_' . date('Y-m-d_H-i-s') . '.html"');
            echo $html;
            exit;
        }
    } else {
        $_SESSION['error'] = "No staff found to export";
        redirect($FileName);
    }
}
// ============================================================================
// GET DATA FOR DISPLAY
// ============================================================================
$editStaff = null;
if (!empty($randomid)) {
    $editStaff = db_get_row("SELECT * FROM staff_manage WHERE randomid = ?", [$randomid]);
}

$searchSQL = "WHERE create_by_userid = ?";
$searchParams = [$create_by_userid];
if (!empty($search_name)) {
    $searchSQL .= " AND (first_name LIKE ? OR last_name LIKE ? OR staff_id LIKE ?)";
    $searchParams[] = "%$search_name%";
    $searchParams[] = "%$search_name%";
    $searchParams[] = "%$search_name%";
}

$allStaff = db_get_rows("SELECT * FROM staff_manage $searchSQL ORDER BY first_name ASC", $searchParams);

function showMessage($stat) {
    $html = '';
    if (!empty($stat['success'])) {
        $html .= '<div class="alert alert-success"><i class="fa fa-check-circle"></i> ' . htmlspecialchars($stat['success']) . '</div>';
    }
    if (!empty($stat['error'])) {
        $html .= '<div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> ' . htmlspecialchars($stat['error']) . '</div>';
    }
    return $html;
}
?>
<!DOCTYPE html>
<html>
<head>
    <?php include('inc.meta.php'); ?>
    <style>
        * { box-sizing: border-box; }
        .staff-container { display: flex; gap: 25px; padding: 20px; min-height: calc(100vh - 120px); }
        .staff-sidebar { width: 32%; background: #fff; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); overflow: hidden; display: flex; flex-direction: column; }
        .staff-main { width: 68%; background: #fff; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); overflow: hidden; }
        .sidebar-header { padding: 20px; border-bottom: 1px solid #eee; background: #f8f9fa; }
        .sidebar-search { width: 100%; padding: 12px 16px; border: 1px solid #e0e0e0; border-radius: 30px; font-size: 14px; margin-bottom: 15px; }
        .staff-list { flex: 1; overflow-y: auto; padding: 10px; max-height: calc(100vh - 280px); }
        .staff-card { display: flex; align-items: center; padding: 15px; margin-bottom: 10px; border-radius: 12px; cursor: pointer; transition: all 0.2s; background: #fff; border: 1px solid #f0f0f0; }
        .staff-card:hover { background: #f8f9ff; border-color: #1B3058; transform: translateX(3px); }
        .staff-card.active { background: #1B3058; border-color: #1B3058; }
        .staff-card.active .staff-name, .staff-card.active .staff-details { color: white; }
        .staff-avatar { width: 48px; height: 48px; border-radius: 50%; background: #e8eef5; display: flex; align-items: center; justify-content: center; margin-right: 14px; font-weight: bold; color: #1B3058; overflow: hidden; flex-shrink: 0; }
        .staff-avatar img { width: 100%; height: 100%; object-fit: cover; }
        .staff-info { flex: 1; }
        .staff-name { font-weight: 600; font-size: 15px; color: #1a2a3a; margin-bottom: 4px; }
        .staff-details { font-size: 12px; color: #6c757d; }
        .staff-id { font-size: 11px; color: #1B3058; font-weight: 500; margin-top: 3px; }
        .main-header { padding: 20px 25px; border-bottom: 1px solid #eee; background: #f8f9fa; }
        .main-header h2 { margin: 0 0 5px; font-size: 22px; color: #1B3058; }
        .main-content { padding: 25px; }
        .form-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px; }
        .form-group { margin-bottom: 5px; }
        .form-group label { display: block; font-weight: 500; margin-bottom: 6px; color: #333; font-size: 13px; }
        .form-control, .form-select { width: 100%; padding: 10px 12px; border: 1px solid #ddd; border-radius: 8px; font-size: 14px; transition: all 0.2s; }
        .form-control:focus, .form-select:focus { outline: none; border-color: #1B3058; box-shadow: 0 0 0 3px rgba(27,48,88,0.1); }
        .btn { padding: 10px 18px; border: none; border-radius: 8px; cursor: pointer; font-size: 14px; font-weight: 500; transition: all 0.2s; }
        .btn-primary { background: #1B3058; color: white; }
        .btn-primary:hover { background: #f21151; }
        .btn-danger { background: #dc3545; color: white; }
        .btn-outline { background: transparent; border: 1px solid #ddd; }
        .action-buttons { display: flex; gap: 12px; margin-top: 25px; padding-top: 20px; border-top: 1px solid #eee; }
        .profile-preview { width: 100px; height: 100px; border-radius: 50%; object-fit: cover; margin-top: 10px; border: 2px solid #1B3058; }
        .alert { padding: 12px 16px; border-radius: 10px; margin-bottom: 20px; }
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-danger { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .btn-group { display: flex; gap: 10px; margin-bottom: 15px; flex-wrap: wrap; }
        .btn-group .btn { flex: 1; min-width: 100px; }
        .empty-state { text-align: center; padding: 40px; color: #999; }
        @media (max-width: 900px) { .staff-container { flex-direction: column; } .staff-sidebar, .staff-main { width: 100%; } .form-grid { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
<div id="wrapper">
    <?php include('inc.header.php'); ?>
    <?php include('inc.sideleft.php'); ?>
    <div class="content-page">
        <div class="content">
            <div class="staff-container">
                
                <!-- LEFT SIDEBAR -->
                <div class="staff-sidebar">
                    <div class="sidebar-header">
                        <h4 style="margin:0 0 10px">Staff</h4>
                        <form method="GET" action="" style="margin:0">
                            <input type="text" name="search" class="sidebar-search" placeholder="🔍 Search by name or ID..." value="<?= htmlspecialchars($search_name) ?>" onchange="this.form.submit()">
                        </form>
                        <div class="btn-group">
                            <a href="?action=add" class="btn btn-primary" style="flex:1"><i class="fa fa-plus"></i> Add Staff</a>
                            <a href="?export=pdf" class="btn btn-primary" style="background:#e74c3c" title="Download as PDF"><i class="fa fa-file-pdf-o"></i> PDF</a>
                        </div>
                    </div>
                    <div class="staff-list" id="staffList">
                        <?php if (!empty($allStaff)): ?>
                            <?php foreach ($allStaff as $staff): ?>
                                <div class="staff-card <?= ($randomid === $staff['randomid']) ? 'active' : '' ?>" onclick="window.location.href='?randomid=<?= $staff['randomid'] ?>'">
                                    <div class="staff-avatar">
                                        <?php if (!empty($staff['picture'])): ?>
                                            <img src="../uploads/<?= htmlspecialchars($staff['picture']) ?>">
                                        <?php else: ?>
                                            <i class="fa fa-user" style="font-size:24px"></i>
                                        <?php endif; ?>
                                    </div>
                                    <div class="staff-info">
                                        <div class="staff-name"><?= htmlspecialchars($staff['first_name'] . ' ' . $staff['last_name']) ?></div>
                                        <div class="staff-details"><?= htmlspecialchars($staff['email']) ?></div>
                                        <div class="staff-id">ID: <?= htmlspecialchars($staff['staff_id']) ?></div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="empty-state">No staff found.<br>Click "Add Staff" to create one.</div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- RIGHT MAIN PANEL -->
                <div class="staff-main">
                    <?php if ($_GET['action'] ?? '' === 'add' || empty($randomid)): ?>
                        <!-- ADD STAFF FORM -->
                        <div class="main-header"><h2><i class="fa fa-user-plus"></i> Add New Staff</h2></div>
                        <div class="main-content">
                            <?= showMessage($stat) ?>
                            
                            <form method="post" enctype="multipart/form-data">
                                <div class="form-grid">
                                    <div class="form-group">
                                        <label>Staff ID *</label>
                                        <input type="text" name="staff_id" class="form-control" required>
                                    </div>
                                    <div class="form-group">
                                        <label>First Name *</label>
                                        <input type="text" name="first_name" class="form-control" style="text-transform: uppercase;" oninput="this.value=this.value.toUpperCase()" required>
                                    </div>
                                    <div class="form-group">
                                        <label>Last Name *</label>
                                        <input type="text" name="last_name" class="form-control" style="text-transform: uppercase;" oninput="this.value=this.value.toUpperCase()" required>
                                    </div>
                                    <div class="form-group">
                                        <label>Email Address *</label>
                                        <input type="email" name="email" class="form-control" required>
                                    </div>
                                    <div class="form-group">
                                        <label>Phone Number</label>
                                        <input type="text" name="phone" class="form-control">
                                    </div>
                                    <div class="form-group">
                                        <label>Profile Picture</label>
                                        <input type="file" name="picture" class="form-control" accept="image/*">
                                    </div>
                                </div>
                                <div class="action-buttons">
                                    <button type="submit" name="add_staff" class="btn btn-primary"><i class="fa fa-save"></i> Add Staff</button>
                                    <a href="<?= $FileName ?>" class="btn btn-outline"><i class="fa fa-times"></i> Cancel</a>
                                </div>
                            </form>
                        </div>
                    <?php else: ?>
                        <!-- EDIT STAFF FORM -->
                        <?php if ($editStaff): ?>
                        <div class="main-header"><h2><i class="fa fa-edit"></i> Edit Staff</h2></div>
                        <div class="main-content">
                            <?= showMessage($stat) ?>
                            
                            <form method="post" enctype="multipart/form-data">
                                <div class="form-grid">
                                    <div class="form-group">
                                        <label>Staff ID *</label>
                                        <input type="text" name="staff_id" class="form-control" value="<?= htmlspecialchars($editStaff['staff_id']) ?>" required>
                                    </div>
                                    <div class="form-group">
                                        <label>First Name *</label>
                                        <input type="text" name="first_name" class="form-control" style="text-transform: uppercase;" oninput="this.value=this.value.toUpperCase()" value="<?= htmlspecialchars($editStaff['first_name']) ?>" required>
                                    </div>
                                    <div class="form-group">
                                        <label>Last Name *</label>
                                        <input type="text" name="last_name" class="form-control" style="text-transform: uppercase;" oninput="this.value=this.value.toUpperCase()" value="<?= htmlspecialchars($editStaff['last_name']) ?>" required>
                                    </div>
                                    <div class="form-group">
                                        <label>Email Address *</label>
                                        <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($editStaff['email']) ?>" required>
                                    </div>
                                    <div class="form-group">
                                        <label>Phone Number</label>
                                        <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($editStaff['phone']) ?>">
                                    </div>
                                    <div class="form-group">
                                        <label>Profile Picture</label>
                                        <input type="file" name="picture" class="form-control" accept="image/*">
                                        <?php if (!empty($editStaff['picture'])): ?>
                                            <div style="margin-top: 10px;">
                                                <img src="../uploads/<?= htmlspecialchars($editStaff['picture']) ?>" class="profile-preview">
                                                <input type="hidden" name="picture_old" value="<?= htmlspecialchars($editStaff['picture']) ?>">
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="action-buttons">
                                    <button type="submit" name="edit_staff" class="btn btn-primary"><i class="fa fa-save"></i> Update Staff</button>
                                    <a href="<?= $FileName ?>" class="btn btn-outline"><i class="fa fa-times"></i> Cancel</a>
                                    <a href="<?= $FileName ?>?delete=<?= htmlspecialchars($randomid) ?>" class="btn btn-danger" onclick="return confirm('Are you sure?')"><i class="fa fa-trash"></i> Delete</a>
                                </div>
                            </form>
                        </div>
                        <?php else: ?>
                        <div class="main-header"><h2>No Staff Selected</h2></div>
                        <div class="main-content"><p>Select a staff member from the list or add a new one.</p></div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include('inc.footer.php'); ?>
<?php include('inc.js.php'); ?>
</body>
</html>
