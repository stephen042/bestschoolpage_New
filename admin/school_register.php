<?php
require_once '../config.php';
require_once 'inc.session-create.php';

$validate = new validation();
$pageTitle = 'School Register';
$FileName = 'school_register.php';

// ============================================================================
// INITIALIZATION
// ============================================================================
$stat = [];
$action = $_GET['action'] ?? '';
$id = $_GET['id'] ?? 0;

if (isset($_SESSION['success']) && $_SESSION['success'] != "") {
    $stat['success'] = $_SESSION['success'];
    unset($_SESSION['success']);
}

// ============================================================================
// FILE UPLOAD HANDLER
// ============================================================================
function handleImageUpload($file, $oldImage = '') {
    if (isset($file['name']) && !empty($file['name'])) {
        $filename = basename($file['name']);
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $allowed = ['jpg', 'png', 'jpeg', 'gif', 'webp'];
        
        if (in_array($ext, $allowed)) {
            $newFilename = md5(time() . uniqid()) . "_" . preg_replace('/[^a-zA-Z0-9._-]/', '_', $filename);
            $uploadPath = "../uploads/" . $newFilename;
            
            if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
                if (!empty($oldImage) && file_exists("../uploads/" . $oldImage)) {
                    unlink("../uploads/" . $oldImage);
                }
                return $newFilename;
            }
        }
    }
    return $oldImage;
}

// ============================================================================
// ADD SCHOOL REGISTER
// ============================================================================
if (isset($_POST['Register'])) {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $contactNo = trim($_POST['contact_no'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $location = trim($_POST['location'] ?? '');
    $stateId = $_POST['stateid'] ?? '';
    $about = trim($_POST['about'] ?? '');
    $website = trim($_POST['website'] ?? '');
    $status = $_POST['status'] ?? '1';
    
    $errors = [];
    if (empty($name)) $errors[] = "School name is required";
    if (empty($email)) $errors[] = "Email is required";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Valid email is required";
    if (empty($contactNo)) $errors[] = "Contact number is required";
    if (empty($username)) $errors[] = "Username is required";
    if (empty($password)) $errors[] = "Password is required";
    if (strlen($password) < 6) $errors[] = "Password must be at least 6 characters";
    
    if (empty($errors)) {
        $existing = db_get_val("SELECT id FROM school_register WHERE email = ?", [$email]);
        
        if (empty($existing)) {
            $image = handleImageUpload($_FILES['image'] ?? []);
            
            $lastId = db_get_val("SELECT id FROM school_register ORDER BY id DESC") ?? 0;
            $newId = $lastId + 1;
            $pageUrl = PageUrl($name) . '-' . $newId;
            
            $data = [
                'name' => $name,
                'email' => $email,
                'contact_no' => $contactNo,
                'username' => $username,
                'password' => password_hash($password, PASSWORD_DEFAULT),
                'location' => $location,
                'state' => $stateId,
                'about' => $about,
                'website' => $website,
                'verifyid' => randomFix(12),
                'pageurl' => $pageUrl,
                'status' => $status,
                'create_at' => date('Y-m-d H:i:s'),
                'logo' => $image,
                'usertype' => 0,
                'create_by_userid' => $_SESSION['userid'] ?? 0,
                'create_by_usertype' => $_SESSION['usertype'] ?? '',
            ];
            
            db_insert("school_register", $data);
            $_SESSION['success'] = "Submitted successfully";
            redirect('school_register.php');
        } else {
            $stat['error'] = "Email is already registered";
        }
    } else {
        $stat['error'] = implode("<br>", $errors);
    }
}

// ============================================================================
// UPDATE SCHOOL REGISTER
// ============================================================================
elseif (isset($_POST['update']) && !empty($id)) {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $contactNo = trim($_POST['contact_no'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $location = trim($_POST['location'] ?? '');
    $stateId = $_POST['stateid'] ?? '';
    $about = trim($_POST['about'] ?? '');
    $website = trim($_POST['website'] ?? '');
    $status = $_POST['status'] ?? '1';
    $oldImage = $_POST['image_old'] ?? '';
    
    $errors = [];
    if (empty($name)) $errors[] = "School name is required";
    if (empty($email)) $errors[] = "Email is required";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Valid email is required";
    if (empty($contactNo)) $errors[] = "Contact number is required";
    if (empty($username)) $errors[] = "Username is required";
    
    if (empty($errors)) {
        $existing = db_get_val("SELECT id FROM school_register WHERE email = ? AND id != ?", [$email, $id]);
        
        if (empty($existing)) {
            $image = handleImageUpload($_FILES['image'] ?? [], $oldImage);
            
            $data = [
                'name' => $name,
                'email' => $email,
                'contact_no' => $contactNo,
                'username' => $username,
                'location' => $location,
                'state' => $stateId,
                'about' => $about,
                'website' => $website,
                'status' => $status,
                'logo' => $image,
            ];
            
            if (!empty($password)) {
                $data['password'] = password_hash($password, PASSWORD_DEFAULT);
            }
            
            db_update("school_register", $data, "id = ?", [$id]);
            $_SESSION['success'] = "Updated successfully";
            redirect('school_register.php');
        } else {
            $stat['error'] = "Email already exists for another user";
        }
    } else {
        $stat['error'] = implode("<br>", $errors);
    }
}

// ============================================================================
// DELETE RECORDS
// ============================================================================
elseif ($action == 'delete' && !empty($id)) {
    $image = db_get_val("SELECT logo FROM school_register WHERE id = ?", [$id]);
    if (!empty($image) && file_exists("../uploads/" . $image)) {
        unlink("../uploads/" . $image);
    }
    db_delete("school_register", "id = ?", [$id]);
    $_SESSION['success'] = 'Deleted Successfully';
    redirect('school_register.php');
}
elseif ($action == 'deletesession' && !empty($id)) {
    db_delete("school_session", "id = ?", [$id]);
    $_SESSION['success'] = 'Deleted Successfully';
    redirect('school_register.php?action=view&id=' . ($_GET['sid'] ?? 0));
}
elseif ($action == 'deletesection' && !empty($id)) {
    db_delete("school_section", "id = ?", [$id]);
    $_SESSION['success'] = 'Deleted Successfully';
    redirect('school_register.php?action=view&id=' . ($_GET['sid'] ?? 0));
}
elseif ($action == 'deleteclass' && !empty($id)) {
    db_delete("school_class", "id = ?", [$id]);
    $_SESSION['success'] = 'Deleted Successfully';
    redirect('school_register.php?action=view&id=' . ($_GET['sid'] ?? 0));
}
elseif ($action == 'deletesubject' && !empty($id)) {
    db_delete("school_subject", "id = ?", [$id]);
    $_SESSION['success'] = 'Deleted Successfully';
    redirect('school_register.php?action=view&id=' . ($_GET['sid'] ?? 0));
}

// ============================================================================
// GET DATA FOR EDIT/VIEW
// ============================================================================
$userData = null;
if (($action == 'edit' || $action == 'view') && !empty($id)) {
    $userData = db_get_row("SELECT * FROM school_register WHERE id = ?", [$id]);
    if (empty($userData)) {
        $_SESSION['error'] = "Record not found";
        redirect('school_register.php');
    }
}

// Get states for dropdown
$states = db_get_rows("SELECT * FROM state ORDER BY id DESC");

// Get related data for view page
$sessions = [];
$sections = [];
$classes = [];
$subjects = [];

if ($action == 'view' && !empty($id)) {
    $sessions = db_get_rows("SELECT * FROM school_session WHERE userid = ? AND sid = ?", [$id, $id]);
    $sections = db_get_rows("SELECT * FROM school_section WHERE userid = ? AND sid = ?", [$id, $id]);
    $classes = db_get_rows("SELECT * FROM school_class WHERE userid = ? AND sid = ?", [$id, $id]);
    $subjects = db_get_rows("SELECT * FROM school_subject WHERE userid = ? AND sid = ?", [$id, $id]);
}

// Get all schools for listing
$allSchools = db_get_rows("SELECT * FROM school_register WHERE usertype = 0 ORDER BY id DESC");
?>
<!DOCTYPE html>
<html>
<head>
    <?php include('inc.meta.php'); ?>
    <style>
        .school-image {
            max-width: 50px;
            max-height: 50px;
            border-radius: 4px;
            object-fit: cover;
        }
        .section-card {
            margin-top: 20px;
        }
        .section-title {
            background: #f5f5f5;
            padding: 10px 15px;
            margin-bottom: 15px;
            border-left: 4px solid #1B3058;
        }
    </style>
</head>
<body class="fixed-left">
<div id="wrapper">
    <?php include('inc.header.php'); ?>
    <?php include('inc.sideleft.php'); ?>
    <div class="content-page">
        <div class="content">
            <div class="container">
                <div class="row">
                    <div class="col-sm-12">
                        <h4 class="page-title"><?= e($pageTitle) ?></h4>
                        <ol class="breadcrumb">
                            <li><a href="<?= ADMIN_URL ?>">Home</a></li>
                            <li class="active"><?= e($pageTitle) ?></li>
                        </ol>
                        <?= showMessage($stat) ?>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="card-box aplhanewclass">
                            <div class="row">
                                <div class="col-md-9"></div>
                                <div class="col-md-3">
                                    <a href="<?= e($FileName) ?>?action=add" class="btn btn-default" style="float:right">
                                        <i class="fa fa-plus"></i> Add New Record
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- ==================== ADD FORM ==================== -->
                        <?php if ($action == 'add'): ?>
                            <div class="card-box">
                                <form action="" method="POST" enctype="multipart/form-data">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>School Name <span class="text-danger">*</span></label>
                                                <input name="name" class="form-control" value="<?= e($_POST['name'] ?? '') ?>" required>
                                            </div>

                                            <div class="form-group">
                                                <label>Email <span class="text-danger">*</span></label>
                                                <input name="email" type="email" class="form-control" value="<?= e($_POST['email'] ?? '') ?>" required>
                                            </div>

                                            <div class="form-group">
                                                <label>Contact Number <span class="text-danger">*</span></label>
                                                <input name="contact_no" class="form-control" value="<?= e($_POST['contact_no'] ?? '') ?>" required>
                                            </div>

                                            <div class="form-group">
                                                <label>Username <span class="text-danger">*</span></label>
                                                <input name="username" class="form-control" value="<?= e($_POST['username'] ?? '') ?>" required>
                                            </div>

                                            <div class="form-group">
                                                <label>Password <span class="text-danger">*</span></label>
                                                <input name="password" type="password" class="form-control" required>
                                                <small class="text-muted">Minimum 6 characters</small>
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Location</label>
                                                <input name="location" class="form-control" value="<?= e($_POST['location'] ?? '') ?>">
                                            </div>

                                            <div class="form-group">
                                                <label>State</label>
                                                <select name="stateid" class="form-control">
                                                    <option value="">Select State</option>
                                                    <?php foreach ($states as $state): ?>
                                                        <option value="<?= e($state['id']) ?>" <?= selected($_POST['stateid'] ?? '', $state['id']) ?>>
                                                            <?= e($state['title']) ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>

                                            <div class="form-group">
                                                <label>Website Name</label>
                                                <input name="website" class="form-control" value="<?= e($_POST['website'] ?? '') ?>">
                                            </div>

                                            <div class="form-group">
                                                <label>About the school</label>
                                                <textarea name="about" class="form-control" rows="3"><?= e($_POST['about'] ?? '') ?></textarea>
                                            </div>

                                            <div class="form-group">
                                                <label>Logo Image</label>
                                                <input type="file" name="image" class="form-control" accept="image/*">
                                            </div>

                                            <div class="form-group">
                                                <label>Status</label>
                                                <select name="status" class="form-control">
                                                    <option value="1" <?= selected($_POST['status'] ?? '1', '1') ?>>Active</option>
                                                    <option value="0" <?= selected($_POST['status'] ?? '', '0') ?>>Inactive</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <button type="submit" name="Register" class="btn btn-primary">Submit</button>
                                        <a href="<?= e($FileName) ?>" class="btn btn-default">Back</a>
                                    </div>
                                </form>
                            </div>

                        <!-- ==================== EDIT FORM ==================== -->
                        <?php elseif ($action == 'edit' && $userData): ?>
                            <div class="card-box">
                                <form action="" method="POST" enctype="multipart/form-data">
                                    <input type="hidden" name="image_old" value="<?= e($userData['logo']) ?>">
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>School Name <span class="text-danger">*</span></label>
                                                <input name="name" class="form-control" value="<?= e($userData['name']) ?>" required>
                                            </div>

                                            <div class="form-group">
                                                <label>Email <span class="text-danger">*</span></label>
                                                <input name="email" type="email" class="form-control" value="<?= e($userData['email']) ?>" required>
                                            </div>

                                            <div class="form-group">
                                                <label>Contact Number <span class="text-danger">*</span></label>
                                                <input name="contact_no" class="form-control" value="<?= e($userData['contact_no']) ?>" required>
                                            </div>

                                            <div class="form-group">
                                                <label>Username <span class="text-danger">*</span></label>
                                                <input name="username" class="form-control" value="<?= e($userData['username']) ?>" required>
                                            </div>

                                            <div class="form-group">
                                                <label>Password (leave empty to keep current)</label>
                                                <input name="password" type="password" class="form-control" placeholder="Enter new password to change">
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Location</label>
                                                <input name="location" class="form-control" value="<?= e($userData['location']) ?>">
                                            </div>

                                            <div class="form-group">
                                                <label>State</label>
                                                <select name="stateid" class="form-control">
                                                    <option value="">Select State</option>
                                                    <?php foreach ($states as $state): ?>
                                                        <option value="<?= e($state['id']) ?>" <?= selected($userData['state'], $state['id']) ?>>
                                                            <?= e($state['title']) ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>

                                            <div class="form-group">
                                                <label>Website Name</label>
                                                <input name="website" class="form-control" value="<?= e($userData['website']) ?>">
                                            </div>

                                            <div class="form-group">
                                                <label>About the school</label>
                                                <textarea name="about" class="form-control" rows="3"><?= e($userData['about']) ?></textarea>
                                            </div>

                                            <div class="form-group">
                                                <label>Logo Image</label>
                                                <input type="file" name="image" class="form-control" accept="image/*">
                                                <?php if (!empty($userData['logo'])): ?>
                                                    <div class="current-image" style="margin-top: 10px;">
                                                        <img src="../uploads/<?= e($userData['logo']) ?>" style="height: 50px;">
                                                    </div>
                                                <?php endif; ?>
                                            </div>

                                            <div class="form-group">
                                                <label>Status</label>
                                                <select name="status" class="form-control">
                                                    <option value="1" <?= selected($userData['status'], '1') ?>>Active</option>
                                                    <option value="0" <?= selected($userData['status'], '0') ?>>Inactive</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <button type="submit" name="update" class="btn btn-primary">Update</button>
                                        <a href="<?= e($FileName) ?>" class="btn btn-default">Back</a>
                                    </div>
                                </form>
                            </div>

                        <!-- ==================== VIEW DETAILS ==================== -->
                        <?php elseif ($action == 'view' && $userData): ?>
                            <div class="card-box">
                                <div class="row">
                                    <div class="col-md-6">
                                        <table class="table table-bordered">
                                            <tr><th width="35%">School Name</th><td><?= e($userData['name']) ?></td></tr>
                                            <tr><th>Email</th><td><?= e($userData['email']) ?></td></tr>
                                            <tr><th>Contact Number</th><td><?= e($userData['contact_no']) ?></td></tr>
                                            <tr><th>Username</th><td><?= e($userData['username']) ?></td></tr>
                                            <tr><th>Location</th><td><?= e($userData['location']) ?></td></tr>
                                            <tr><th>Website</th><td><?= e($userData['website']) ?></td></tr>
                                            <tr><th>Status</th><td><?= $userData['status'] == '1' ? 'Active' : 'Inactive' ?></td></tr>
                                            <tr><th>Created At</th><td><?= e($userData['create_at']) ?></td></tr>
                                        </table>
                                    </div>
                                    <div class="col-md-6">
                                        <table class="table table-bordered">
                                            <tr><th>Logo</th><td>
                                                <?php if (!empty($userData['logo'])): ?>
                                                    <img src="../uploads/<?= e($userData['logo']) ?>" style="max-height: 100px;">
                                                <?php else: ?>
                                                    No image
                                                <?php endif; ?>
                                             </th></tr>
                                            <tr><th>About</th><td><?= nl2br(e($userData['about'])) ?></th></tr>
                                        </table>
                                    </div>
                                </div>

                                <!-- Sessions Section -->
                                <div class="section-card">
                                    <div class="section-title">
                                        <h5><i class="fa fa-calendar"></i> Sessions</h5>
                                    </div>
                                    <table class="table table-striped table-bordered">
                                        <thead><tr><th>#</th><th>Session</th><th>Action</th></tr></thead>
                                        <tbody>
                                            <?php $i = 0; foreach ($sessions as $session): $i++; ?>
                                                <tr><td><?= $i ?></td><td><?= e($session['session']) ?></td>
                                                <td><a href="javascript:del('<?= e($FileName) ?>?action=deletesession&id=<?= e($session['id']) ?>&sid=<?= e($id) ?>')" class="table-action-btn"><i class="fa fa-times"></i></a></td></tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>

                                <!-- Sections Section -->
                                <div class="section-card">
                                    <div class="section-title">
                                        <h5><i class="fa fa-columns"></i> Sections</h5>
                                    </div>
                                    <table class="table table-striped table-bordered">
                                        <thead><tr><th>#</th><th>Section</th><th>Short Name</th><th>Action</th></tr></thead>
                                        <tbody>
                                            <?php $i = 0; foreach ($sections as $section): $i++; ?>
                                                <tr><td><?= $i ?></td><td><?= e($section['section']) ?></td><td><?= e($section['short_name']) ?></td>
                                                <td><a href="javascript:del('<?= e($FileName) ?>?action=deletesection&id=<?= e($section['id']) ?>&sid=<?= e($id) ?>')" class="table-action-btn"><i class="fa fa-times"></i></a></td></tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>

                                <!-- Classes Section -->
                                <div class="section-card">
                                    <div class="section-title">
                                        <h5><i class="fa fa-graduation-cap"></i> Classes</h5>
                                    </div>
                                    <table class="table table-striped table-bordered">
                                        <thead><tr><th>#</th><th>Class Name</th><th>Short Name</th><th>Action</th></tr></thead>
                                        <tbody>
                                            <?php $i = 0; foreach ($classes as $class): $i++; ?>
                                                <tr><td><?= $i ?></td><td><?= e($class['name']) ?></td><td><?= e($class['short_name']) ?></td>
                                                <td><a href="javascript:del('<?= e($FileName) ?>?action=deleteclass&id=<?= e($class['id']) ?>&sid=<?= e($id) ?>')" class="table-action-btn"><i class="fa fa-times"></i></a></td></tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>

                                <!-- Subjects Section -->
                                <div class="section-card">
                                    <div class="section-title">
                                        <h5><i class="fa fa-book"></i> Subjects</h5>
                                    </div>
                                    <table class="table table-striped table-bordered">
                                        <thead><tr><th>#</th><th>Session</th><th>Section</th><th>Class</th><th>Subject</th><th>Action</th></tr></thead>
                                        <tbody>
                                            <?php 
                                            $i = 0; 
                                            foreach ($subjects as $subject): 
                                                $i++;
                                                $sessionName = db_get_val("SELECT session FROM school_session WHERE id = ?", [$subject['session_id']]);
                                                $sectionName = db_get_val("SELECT section FROM school_section WHERE id = ?", [$subject['section_id']]);
                                                $className = db_get_val("SELECT name FROM school_class WHERE id = ?", [$subject['class_id']]);
                                            ?>
                                                <tr>
                                                    <td><?= $i ?></td>
                                                    <td><?= e($sessionName) ?></td>
                                                    <td><?= e($sectionName) ?></td>
                                                    <td><?= e($className) ?></td>
                                                    <td><?= e($subject['subject']) ?></td>
                                                    <td><a href="javascript:del('<?= e($FileName) ?>?action=deletesubject&id=<?= e($subject['id']) ?>&sid=<?= e($id) ?>')" class="table-action-btn"><i class="fa fa-times"></i></a></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>

                                <a href="<?= e($FileName) ?>" class="btn btn-default">Back</a>
                            </div>

                        <!-- ==================== LIST ALL SCHOOLS ==================== -->
                        <?php else: ?>
                            <div class="card-box">
                                <table id="datatable" class="table table-striped table-bordered">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>School Name</th>
                                            <th>Contact</th>
                                            <th>Username</th>
                                            <th>Location</th>
                                            <th>Logo</th>
                                            <th>Status</th>
                                            <th>Created At</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php $i = 0; foreach ($allSchools as $school): $i++; ?>
                                            <tr>
                                                <td><?= $i ?></td>
                                                <td><?= e($school['name']) ?></td>
                                                <td><?= e($school['contact_no']) ?></td>
                                                <td><?= e($school['username']) ?></td>
                                                <td><?= e($school['location']) ?></td>
                                                <td>
                                                    <?php if (!empty($school['logo'])): ?>
                                                        <img src="../uploads/<?= e($school['logo']) ?>" class="school-image">
                                                    <?php else: ?>
                                                        <span class="text-muted">No image</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?= $school['status'] == '1' ? '<span class="label label-success">Active</span>' : '<span class="label label-danger">Inactive</span>' ?></td>
                                                <td><?= e($school['create_at']) ?></td>
                                                <td>
                                                    <a href="<?= e($FileName) ?>?action=view&id=<?= e($school['id']) ?>" class="table-action-btn"><i class="fa fa-search"></i></a>
                                                    <a href="<?= e($FileName) ?>?action=edit&id=<?= e($school['id']) ?>" class="table-action-btn"><i class="fa fa-pencil"></i></a>
                                                    <a href="javascript:del('<?= e($FileName) ?>?action=delete&id=<?= e($school['id']) ?>')" class="table-action-btn"><i class="fa fa-times"></i></a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
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
</body>
</html>