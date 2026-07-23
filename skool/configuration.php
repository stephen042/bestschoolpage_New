<?php
/**
 * ============================================================================
 * CONFIGURATION PAGE - MODERN PHP 8.x (FIXED)
 * ============================================================================
 * Manage: School Info, Session, Term, Section, Class, Subject, PDF Settings
 * Version: 3.0 (PHP 8.x Compatible) - User Identification & Mobile Fixed
 * ============================================================================
 */

require_once('../config.php');
require_once('inc.session-create.php');

$PageTitle = "Configuration";
$FileName = 'configuration.php';
$uploadDir = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'uploads';
if (!is_dir($uploadDir)) {
    @mkdir($uploadDir, 0777, true);
}

// ============================================================================
// FIX: USE CORRECT USER IDENTIFICATION (Same as dashboard.php)
// ============================================================================
$create_by_userid = (int)($_SESSION['userid'] ?? 0);

// If create_by_userid is not set in session, try to get it from the user record
if ($create_by_userid == 0 && !empty($_SESSION['userid'])) {
    $userData = db_get_row("SELECT create_by_userid FROM users WHERE id = ?", [$_SESSION['userid']]);
    if ($userData && !empty($userData['create_by_userid'])) {
        $create_by_userid = (int)$userData['create_by_userid'];
    }
}

// Fallback: if still 0, use the user's own ID
if ($create_by_userid == 0) {
    $create_by_userid = (int)($_SESSION['userid'] ?? 0);
}

// ============================================================================
// INITIALIZATION
// ============================================================================
$stat = [];
$action = $_GET['action'] ?? '';
$randomid = $_GET['randomid'] ?? '';
$create_by_usertype = $_SESSION['usertype'] ?? '';

if (isset($_SESSION['success']) && $_SESSION['success'] != "") {
    $stat['success'] = $_SESSION['success'];
    unset($_SESSION['success']);
}

if (isset($_SESSION['error']) && $_SESSION['error'] != "") {
    $stat['error'] = $_SESSION['error'];
    unset($_SESSION['error']);
}

// ============================================================================
// SCHOOL INFO UPDATE
// ============================================================================
if (isset($_POST['configuration'])) {
    $name = trim($_POST['name'] ?? '');
    $about = trim($_POST['about'] ?? '');
    $schoolType = $_POST['school_type'] ?? '';
    $location = trim($_POST['location'] ?? '');
    $state = $_POST['state'] ?? '';
    $website = trim($_POST['website'] ?? '');
    $moto = trim($_POST['moto'] ?? '');
    
    // Handle logo upload
    $logo = $_POST['logo_old'] ?? '';
    if (isset($_FILES['logo']['name']) && !empty($_FILES['logo']['name'])) {
        $ext = strtolower(pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg', 'png', 'jpeg', 'gif', 'webp'])) {
            $logo = md5(time() . uniqid()) . "_" . preg_replace('/[^a-zA-Z0-9._-]/', '_', $_FILES['logo']['name']);
            $targetPath = $uploadDir . DIRECTORY_SEPARATOR . $logo;
            if (!move_uploaded_file($_FILES['logo']['tmp_name'], $targetPath)) {
                $_SESSION['error'] = "Failed to upload logo. Please try again.";
                redirect($FileName . '?action=configuration');
                exit;
            }
        } else {
            $_SESSION['error'] = "Only JPG, PNG, JPEG, GIF, and WEBP files are allowed.";
            redirect($FileName . '?action=configuration');
            exit;
        }
    }
    
    db_update("school_register", [
        'logo' => $logo,
        'name' => $name,
        'about' => $about,
        'school_type' => $schoolType,
        'location' => $location,
        'state' => $state,
        'website' => $website,
        'moto' => $moto,
    ], "id = ?", [$_SESSION['userid']]);
    
    $_SESSION['success'] = "School information updated successfully";
    redirect($FileName . '?action=configuration');
}

// ============================================================================
// SECTION MANAGEMENT (FIXED)
// ============================================================================
if (isset($_POST['savesection'])) {
    $section = $_POST['section'] ?? '';
    $shortName = $_POST['short_name'] ?? '';
    
    // Handle custom section name if "OTHERS" is selected
    if ($section == '0' && !empty($shortName)) {
        $displaySection = $shortName;
        $displayShort = $shortName;
    } else {
        $displaySection = $section;
        $displayShort = $section;
    }
    
    if (empty($displaySection)) {
        $stat['error'] = "Section name is required";
    } else {
        // Check if section already exists
        $existing = db_get_val(
            "SELECT id FROM school_section WHERE section = ? AND create_by_userid = ?",
            [$displaySection, $create_by_userid]
        );
        
        if ($existing) {
            $stat['error'] = "Section already exists";
        } else {
            $newRandomid = randomFix(15) . '-' . time();
            
            $result = db_insert("school_section", [
                'usertype' => $_SESSION['usertype'] ?? '',
                'userid' => $_SESSION['userid'] ?? 0,
                'section' => $displaySection,
                'short_name' => $displayShort,
                'create_by_usertype' => $create_by_usertype,
                'create_by_userid' => $create_by_userid,
                'randomid' => $newRandomid,
            ]);
            
            if ($result !== false) {
                $_SESSION['success'] = "Section '$displaySection' saved successfully";
            } else {
                $_SESSION['error'] = "Failed to save section. Please try again.";
            }
        }
    }
    redirect($FileName . '?action=section');
    exit;
}

if (isset($_POST['editsection']) && !empty($randomid)) {
    $shortName = trim($_POST['short_name'] ?? '');
    
    if (empty($shortName)) {
        $_SESSION['error'] = "Section name cannot be empty";
    } else {
        // First check if section exists with this randomid
        $sectionExists = db_get_val(
            "SELECT id FROM school_section WHERE randomid = ? AND create_by_userid = ?",
            [$randomid, $create_by_userid]
        );
        
        if ($sectionExists) {
            $result = db_update("school_section", ['short_name' => $shortName], "randomid = ?", [$randomid]);
            if ($result !== false) {
                $_SESSION['success'] = "Section updated successfully";
            } else {
                $_SESSION['error'] = "Failed to update section";
            }
        } else {
            $_SESSION['error'] = "Section not found";
        }
    }
    redirect($FileName . '?action=section');
    exit;
}

if (isset($_GET['action']) && $_GET['action'] == 'delete_section' && !empty($randomid)) {
    // First check if section exists
    $sectionExists = db_get_val(
        "SELECT id FROM school_section WHERE randomid = ? AND create_by_userid = ?",
        [$randomid, $create_by_userid]
    );
    
    if ($sectionExists) {
        // Check if there are classes under this section
        $classCount = db_get_val(
            "SELECT COUNT(*) FROM school_class WHERE section_id = ? AND create_by_userid = ?",
            [$sectionExists, $create_by_userid]
        );
        
        if ($classCount > 0) {
            $_SESSION['error'] = "Cannot delete section. Please delete all classes under this section first.";
        } else {
            $result = db_delete("school_section", "randomid = ? AND create_by_userid = ?", [$randomid, $create_by_userid]);
            if ($result !== false) {
                $_SESSION['success'] = "Section deleted successfully";
            } else {
                $_SESSION['error'] = "Failed to delete section";
            }
        }
    } else {
        $_SESSION['error'] = "Section not found";
    }
    redirect($FileName . '?action=section');
    exit;
}

// ============================================================================
// TERM MANAGEMENT
// ============================================================================
if (isset($_POST['saveterm'])) {
    $term = trim($_POST['term'] ?? '');
    if (!empty($term)) {
        $existing = db_get_val("SELECT id FROM school_term WHERE term = ? AND create_by_userid = ?", [$term, $create_by_userid]);
        if ($existing) {
            $stat['error'] = "Term already exists";
        } else {
            db_insert("school_term", [
                'usertype' => $_SESSION['usertype'] ?? '',
                'userid' => $_SESSION['userid'] ?? 0,
                'term' => $term,
                'create_by_usertype' => $create_by_usertype,
                'create_by_userid' => $create_by_userid,
                'randomid' => randomFix(15) . '-' . time(),
            ]);
            $_SESSION['success'] = "Term saved successfully";
        }
    }
    redirect($FileName . '?action=term');
}

if (isset($_POST['editterm']) && !empty($randomid)) {
    db_update("school_term", ['term' => $_POST['term']], "randomid = ?", [$randomid]);
    $_SESSION['success'] = "Term updated successfully";
    redirect($FileName . '?action=term');
}

if (isset($_GET['action']) && $_GET['action'] == 'deleteterm' && !empty($randomid)) {
    db_delete("school_term", "randomid = ?", [$randomid]);
    $_SESSION['success'] = "Term deleted successfully";
    redirect($FileName . '?action=term');
}

// ============================================================================
// SESSION MANAGEMENT
// ============================================================================
if (isset($_POST['savesession'])) {
    $session = trim($_POST['session'] ?? '');
    if (!empty($session)) {
        $existing = db_get_val("SELECT id FROM school_session WHERE session = ? AND create_by_userid = ?", [$session, $create_by_userid]);
        if ($existing) {
            $stat['error'] = "Session already exists";
        } else {
            db_insert("school_session", [
                'usertype' => $_SESSION['usertype'] ?? '',
                'userid' => $_SESSION['userid'] ?? 0,
                'session' => $session,
                'create_by_usertype' => $create_by_usertype,
                'create_by_userid' => $create_by_userid,
                'randomid' => randomFix(15) . '-' . time(),
            ]);
            $_SESSION['success'] = "Session saved successfully";
        }
    }
    redirect($FileName . '?action=session');
}

if (isset($_POST['editsession']) && !empty($randomid)) {
    db_update("school_session", ['session' => $_POST['session']], "randomid = ?", [$randomid]);
    $_SESSION['success'] = "Session updated successfully";
    redirect($FileName . '?action=session');
}

if (isset($_GET['action']) && $_GET['action'] == 'deletesession' && !empty($randomid)) {
    db_delete("school_session", "randomid = ?", [$randomid]);
    $_SESSION['success'] = "Session deleted successfully";
    redirect($FileName . '?action=session');
}

// ============================================================================
// CLASS MANAGEMENT
// ============================================================================
if (isset($_POST['saveclass'])) {
    $sectionId = $_POST['section_id'] ?? 0;
    $name = trim(preg_replace('/\s+/', '', $_POST['name'] ?? ''));
    $shortName = $_POST['short_name'] ?? '';
    
    $existing = db_get_val("SELECT id FROM school_class WHERE name = ? AND section_id = ? AND create_by_userid = ?", [$name, $sectionId, $create_by_userid]);
    if ($existing) {
        $stat['error'] = "Class already exists in this section";
    } else {
        db_insert("school_class", [
            'usertype' => $_SESSION['usertype'] ?? '',
            'userid' => $_SESSION['userid'] ?? 0,
            'section_id' => $sectionId,
            'name' => $name,
            'short_name' => $shortName,
            'create_by_usertype' => $create_by_usertype,
            'create_by_userid' => $create_by_userid,
            'randomid' => randomFix(15) . '-' . time(),
        ]);
        $_SESSION['success'] = "Class saved successfully";
    }
    redirect($FileName . '?action=class');
}

if (isset($_POST['editclass']) && !empty($randomid)) {
    db_update("school_class", [
        'name' => $_POST['name'],
        'short_name' => $_POST['short_name'],
    ], "randomid = ?", [$randomid]);
    $_SESSION['success'] = "Class updated successfully";
    redirect($FileName . '?action=class');
}

if (isset($_GET['action']) && $_GET['action'] == 'delete_class' && !empty($randomid)) {
    db_delete("school_class", "randomid = ?", [$randomid]);
    $_SESSION['success'] = "Class deleted successfully";
    redirect($FileName . '?action=class');
}

// ============================================================================
// SUBJECT MANAGEMENT
// ============================================================================
if (isset($_POST['savesubject'])) {
    $sectionId = $_POST['section_id'] ?? 0;
    $classId = $_POST['class_id'] ?? 0;
    $subject = trim($_POST['subject'] ?? '');
    
    $existing = db_get_val("SELECT id FROM school_subject WHERE subject = ? AND class_id = ? AND create_by_userid = ?", [$subject, $classId, $create_by_userid]);
    if ($existing) {
        $stat['error'] = "Subject already exists for this class";
    } else {
        db_insert("school_subject", [
            'usertype' => $_SESSION['usertype'] ?? '',
            'userid' => $_SESSION['userid'] ?? 0,
            'section_id' => $sectionId,
            'class_id' => $classId,
            'subject' => $subject,
            'create_by_usertype' => $create_by_usertype,
            'create_by_userid' => $create_by_userid,
            'randomid' => randomFix(15) . '-' . time(),
        ]);
        $_SESSION['success'] = "Subject saved successfully";
    }
    redirect($FileName . '?action=subject');
}

if (isset($_POST['editsubject']) && !empty($randomid)) {
    db_update("school_subject", ['subject' => $_POST['subject']], "randomid = ?", [$randomid]);
    $_SESSION['success'] = "Subject updated successfully";
    redirect($FileName . '?action=subject');
}

if (isset($_GET['action']) && $_GET['action'] == 'delete_subject' && !empty($randomid)) {
    db_delete("school_subject", "randomid = ?", [$randomid]);
    $_SESSION['success'] = "Subject deleted successfully";
    redirect($FileName . '?action=subject');
}

// ============================================================================
// PDF SETTINGS (FIXED)
// ============================================================================
if (isset($_POST['pdfsetting']) && !empty($randomid)) {
    // First, get the actual section ID from the randomid
    $sectionData = db_get_row("SELECT id FROM school_section WHERE randomid = ? AND create_by_userid = ?", [$randomid, $create_by_userid]);

    // Backward compatibility: accept legacy links that pass section numeric id as randomid.
    if (empty($sectionData) && ctype_digit((string)$randomid)) {
        $sectionData = db_get_row("SELECT id, randomid FROM school_section WHERE id = ? AND create_by_userid = ?", [(int)$randomid, $create_by_userid]);
        if (!empty($sectionData['randomid'])) {
            $randomid = $sectionData['randomid'];
        }
    }
    
    if (empty($sectionData)) {
        $_SESSION['error'] = "Section not found. Please select a valid section.";
        redirect($FileName . '?action=section');
        exit;
    }
    
    $sectionId = $sectionData['id'];
    
    $checkboxes = [
        'is_grade', 'is_class', 'is_position', 'is_totalstudent', 'is_addmission',
        'is_totalscore', 'is_session', 'is_finalaverage', 'is_terms', 'is_highestaverage',
        'is_lowestaverage', 'is_schoolopen', 'is_daypresent', 'is_dayabsent', 'is_profilepic',
        'is_affective', 'is_phycomotor', 'is_out', 'is_highest_avg', 'is_lowest_avg',
        'is_class_avg', 'is_grade_details', 'is_no_of_subjects', 'is_pos'
    ];
    
    $data = [];
    foreach ($checkboxes as $cb) {
        $data[$cb] = isset($_POST[$cb]) ? '1' : '0';
    }
    
    $data['title_1'] = trim($_POST['title_1'] ?? '');
    $data['title_2'] = trim($_POST['title_2'] ?? '');
    $data['title_3'] = trim($_POST['title_3'] ?? '');
    $data['title_4'] = trim($_POST['title_4'] ?? '');
    $data['title_5'] = trim($_POST['title_5'] ?? '');
    
    // Check if settings already exist for this section
    $existing = db_get_val(
        "SELECT id FROM school_pdfsetting WHERE section_id = ? AND create_by_userid = ?",
        [$sectionId, $create_by_userid]
    );
    
    if ($existing) {
        // UPDATE - use the record ID
        $result = db_update("school_pdfsetting", $data, "id = ?", [$existing]);
    } else {
        // INSERT - new record
        $data['section_id'] = $sectionId;
        $data['create_by_usertype'] = $create_by_usertype;
        $data['create_by_userid'] = $create_by_userid;
        $data['randomid'] = randomFix(15) . '-' . time();
        $result = db_insert("school_pdfsetting", $data);
    }
    
    if ($result !== false) {
        $_SESSION['success'] = "PDF settings saved successfully";
    } else {
        $_SESSION['error'] = "Failed to save PDF settings. Please try again.";
    }
    
    redirect($FileName . '?action=pdfsetting&randomid=' . $randomid);
    exit;
}

// ============================================================================
// GET DATA FOR DISPLAY
// ============================================================================
$schoolData = db_get_row("SELECT * FROM school_register WHERE id = ?", [$_SESSION['userid']]);
$sessions = db_get_rows("SELECT * FROM school_session WHERE create_by_userid = ? ORDER BY id DESC", [$create_by_userid]);
$terms = db_get_rows("SELECT * FROM school_term WHERE create_by_userid = ? ORDER BY id DESC", [$create_by_userid]);
$sections = db_get_rows("SELECT * FROM school_section WHERE create_by_userid = ? ORDER BY id DESC", [$create_by_userid]);
$classes = db_get_rows("SELECT * FROM school_class WHERE create_by_userid = ? ORDER BY id DESC", [$create_by_userid]);
$subjects = db_get_rows("SELECT * FROM school_subject WHERE create_by_userid = ? ORDER BY id DESC", [$create_by_userid]);

// Get PDF settings for the selected section (FIXED - get section ID from randomid first)
$pdfSettings = [];
if ($action == 'pdfsetting' && !empty($randomid)) {
    $currentSection = db_get_row("SELECT id FROM school_section WHERE randomid = ? AND create_by_userid = ?", [$randomid, $create_by_userid]);

    // Backward compatibility: accept legacy numeric section id in randomid query parameter.
    if (empty($currentSection) && ctype_digit((string)$randomid)) {
        $currentSection = db_get_row("SELECT id, randomid FROM school_section WHERE id = ? AND create_by_userid = ?", [(int)$randomid, $create_by_userid]);
        if (!empty($currentSection['randomid'])) {
            $randomid = $currentSection['randomid'];
        }
    }

    $currentSectionId = $currentSection['id'] ?? 0;
    if ($currentSectionId > 0) {
        $pdfSettings = db_get_row("SELECT * FROM school_pdfsetting WHERE section_id = ? AND create_by_userid = ?", [$currentSectionId, $create_by_userid]);
    }
    if (empty($pdfSettings)) {
        $pdfSettings = [];
    }
}

function getSectionName($id) { 
    return db_get_val("SELECT section FROM school_section WHERE id = ?", [$id]) ?: 'N/A'; 
}

function getClassName($id) { 
    return db_get_val("SELECT name FROM school_class WHERE id = ?", [$id]) ?: 'N/A'; 
}
?>

<!DOCTYPE html>
<html>
<head>
    <?php include('inc.meta.php'); ?>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
    <style>
        /* ============================================================
        MOBILE-FIRST CONFIGURATION STYLES
        ============================================================ */
        * { box-sizing: border-box; }
        
        /* Base */
        .config-container { 
            padding: 10px; 
            max-width: 1400px; 
            margin: 0 auto; 
        }
        
        /* Mobile-first tabs - horizontal scroll */
        .config-tabs { 
            display: flex; 
            flex-wrap: nowrap;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            gap: 4px; 
            margin-bottom: 15px; 
            padding-bottom: 8px;
            border-bottom: 2px solid #e0e0e0;
            scrollbar-width: thin;
        }
        
        .config-tabs::-webkit-scrollbar {
            height: 3px;
        }
        
        .config-tabs::-webkit-scrollbar-thumb {
            background: #ccc;
            border-radius: 10px;
        }
        
        .config-tab { 
            padding: 8px 12px; 
            background: #f5f5f5; 
            border-radius: 8px 8px 0 0; 
            text-decoration: none; 
            color: #333; 
            font-size: 12px;
            white-space: nowrap;
            transition: all 0.2s; 
            flex-shrink: 0;
        }
        
        .config-tab:hover { 
            background: #e0e0e0; 
        }
        
        .config-tab.active { 
            background: #1B3058; 
            color: white; 
        }
        
        .config-card { 
            background: #fff; 
            border-radius: 12px; 
            box-shadow: 0 2px 12px rgba(0,0,0,0.06); 
            padding: 16px; 
            margin-bottom: 16px; 
        }
        
        /* Form - mobile first */
        .form-grid { 
            display: grid; 
            grid-template-columns: 1fr; 
            gap: 12px; 
        }
        
        .form-group { 
            margin-bottom: 12px; 
        }
        
        .form-group label { 
            display: block; 
            font-weight: 500; 
            margin-bottom: 4px; 
            color: #333; 
            font-size: 13px;
        }
        
        .form-control, .form-select { 
            width: 100%; 
            padding: 10px 12px; 
            border: 1px solid #ddd; 
            border-radius: 8px; 
            font-size: 14px; 
            background: white;
            -webkit-appearance: none;
            appearance: none;
        }
        
        .form-control:focus, .form-select:focus { 
            outline: none; 
            border-color: #1B3058; 
            box-shadow: 0 0 0 3px rgba(27,48,88,0.1); 
        }
        
        /* Buttons - touch friendly */
        .btn { 
            padding: 12px 20px; 
            border: none; 
            border-radius: 8px; 
            cursor: pointer; 
            font-weight: 500; 
            font-size: 14px;
            transition: all 0.2s;
            touch-action: manipulation;
            min-height: 44px;
        }
        
        .btn-primary { 
            background: #1B3058; 
            color: white; 
        }
        
        .btn-primary:hover { 
            background: #f21151; 
        }
        
        .btn-primary:active {
            transform: scale(0.97);
        }
        
        .btn-sm { 
            padding: 6px 12px; 
            font-size: 12px;
            min-height: 34px;
        }
        
        .btn-danger {
            background: #dc3545;
            color: white;
        }
        
        .btn-danger:hover {
            background: #c82333;
        }
        
        .btn-outline-secondary {
            background: transparent;
            color: #6c757d;
            border: 1px solid #6c757d;
        }
        
        /* Tables - responsive */
        .data-table { 
            width: 100%; 
            border-collapse: collapse; 
            font-size: 13px;
        }
        
        .data-table th, .data-table td { 
            padding: 8px 6px; 
            text-align: left; 
            border-bottom: 1px solid #eee; 
            word-break: break-word;
        }
        
        .data-table th { 
            background: #f8f9fa; 
            font-weight: 600; 
            font-size: 11px;
            text-transform: uppercase;
            color: #555;
        }
        
        .data-table .action-icons {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }
        
        .data-table .action-icons a { 
            padding: 4px 8px;
            color: #1B3058; 
            text-decoration: none;
            border-radius: 4px;
            transition: all 0.2s;
        }
        
        .data-table .action-icons a:hover { 
            background: #f0f0f0;
        }
        
        .data-table .action-icons a i {
            font-size: 14px;
        }
        
        /* Logo preview */
        .logo-preview { 
            width: 100px; 
            height: 100px; 
            object-fit: cover; 
            border-radius: 12px; 
            margin-bottom: 12px; 
            border: 2px solid #1B3058; 
        }
        
        /* Checkbox group - mobile friendly */
        .checkbox-group { 
            display: grid; 
            grid-template-columns: 1fr 1fr; 
            gap: 8px; 
            margin-bottom: 12px; 
        }
        
        .checkbox-label { 
            display: flex; 
            align-items: center; 
            gap: 6px; 
            cursor: pointer; 
            font-size: 13px;
            padding: 4px 0;
        }
        
        .checkbox-label input[type="checkbox"] {
            width: 18px;
            height: 18px;
            flex-shrink: 0;
            accent-color: #1B3058;
        }
        
        /* Alerts */
        .alert { 
            padding: 12px 16px; 
            border-radius: 10px; 
            margin-bottom: 16px; 
            font-size: 14px;
        }
        
        .alert-success { 
            background: #d4edda; 
            color: #155724; 
            border: 1px solid #c3e6cb; 
        }
        
        .alert-danger { 
            background: #f8d7da; 
            color: #721c24; 
            border: 1px solid #f5c6cb; 
        }
        
        /* Mobile inline forms */
        .inline-form {
            display: flex;
            flex-direction: column;
            gap: 8px;
            width: 100%;
        }
        
        .inline-form .form-control {
            font-size: 13px;
        }
        
        .inline-form .btn {
            align-self: flex-start;
            min-height: 36px;
            padding: 8px 16px;
            font-size: 13px;
        }
        
        /* ============================================================
        DESKTOP BREAKPOINTS
        ============================================================ */
        @media (min-width: 768px) {
            .config-container { 
                padding: 20px; 
            }
            
            .config-tabs { 
                flex-wrap: wrap; 
                overflow-x: visible;
                gap: 5px; 
                padding-bottom: 10px;
            }
            
            .config-tab { 
                padding: 10px 20px; 
                font-size: 14px;
            }
            
            .config-card { 
                padding: 25px; 
                margin-bottom: 20px; 
            }
            
            .form-grid { 
                grid-template-columns: repeat(2, 1fr); 
                gap: 20px; 
            }
            
            .checkbox-group { 
                grid-template-columns: repeat(3, 1fr); 
                gap: 10px; 
            }
            
            .data-table th, .data-table td { 
                padding: 12px; 
                font-size: 14px;
            }
            
            .data-table th {
                font-size: 12px;
            }
            
            .inline-form {
                flex-direction: row;
                align-items: center;
                flex-wrap: wrap;
            }
            
            .inline-form .form-control {
                width: auto;
                min-width: 150px;
                flex: 1;
            }
            
            .logo-preview { 
                width: 120px; 
                height: 120px; 
            }
        }
        
        @media (min-width: 1024px) {
            .config-container { 
                padding: 30px; 
            }
            
            .config-card { 
                padding: 30px; 
            }
            
            .checkbox-group { 
                grid-template-columns: repeat(4, 1fr); 
            }
        }
        
        /* ============================================================
        VERY SMALL SCREENS
        ============================================================ */
        @media (max-width: 400px) {
            .checkbox-group { 
                grid-template-columns: 1fr 1fr; 
            }
            
            .config-tab { 
                font-size: 11px;
                padding: 6px 10px;
            }
            
            .data-table { 
                font-size: 12px;
            }
            
            .data-table th, .data-table td { 
                padding: 6px 4px; 
            }
            
            .btn { 
                font-size: 13px;
                padding: 10px 16px;
            }
        }
        
        /* ============================================================
        PRINT STYLES
        ============================================================ */
        @media print {
            .config-tabs { display: none; }
            .btn { display: none; }
            .config-card { box-shadow: none; border: 1px solid #ddd; }
            .action-icons a { display: none; }
        }
    </style>
</head>
<body>
<div id="wrapper">
    <?php include('inc.header.php'); ?>
    <?php include('inc.sideleft.php'); ?>
    <div class="content-page">
        <div class="content">
            <div class="config-container">
                <h2 style="margin-bottom: 16px; font-size: 20px;">⚙️ Configuration</h2>
                <?= showMessage($stat) ?>
                
                <div class="config-tabs">
                    <a href="?action=configuration" class="config-tab <?= ($action == '' || $action == 'configuration') ? 'active' : '' ?>">🏫 School</a>
                    <a href="?action=session" class="config-tab <?= ($action == 'session') ? 'active' : '' ?>">📅 Session</a>
                    <a href="?action=term" class="config-tab <?= ($action == 'term') ? 'active' : '' ?>">📖 Term</a>
                    <a href="?action=section" class="config-tab <?= ($action == 'section') ? 'active' : '' ?>">📂 Sections</a>
                    <a href="?action=class" class="config-tab <?= ($action == 'class') ? 'active' : '' ?>">🏷️ Classes</a>
                    <a href="?action=subject" class="config-tab <?= ($action == 'subject') ? 'active' : '' ?>">📚 Subjects</a>
                    <?php if ($action == 'pdfsetting'): ?>
                        <a href="?action=pdfsetting&randomid=<?= urlencode($randomid) ?>" class="config-tab active">📄 PDF</a>
                    <?php endif; ?>
                </div>

                <!-- ============================================================ -->
                <!-- SCHOOL INFO TAB -->
                <!-- ============================================================ -->
                <?php if ($action == '' || $action == 'configuration'): ?>
                <div class="config-card">
                    <form method="post" enctype="multipart/form-data">
                        <div style="text-align: center; margin-bottom: 16px;">
                            <div>
                                <img src="../uploads/<?= htmlspecialchars($schoolData['logo'] ?? '') ?>" class="logo-preview" onerror="this.src='assets/image/default-logo.png'">
                            </div>
                            <div style="margin-top: 8px;">
                                <input type="file" name="logo" accept="image/*" style="font-size: 13px; max-width: 100%;">
                            </div>
                            <input type="hidden" name="logo_old" value="<?= htmlspecialchars($schoolData['logo'] ?? '') ?>">
                        </div>
                        <div class="form-grid">
                            <div class="form-group">
                                <label>School Name</label>
                                <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($schoolData['name'] ?? '') ?>">
                            </div>
                            <div class="form-group">
                                <label>About School</label>
                                <input type="text" name="about" class="form-control" value="<?= htmlspecialchars($schoolData['about'] ?? '') ?>">
                            </div>
                            <div class="form-group">
                                <label>School Type</label>
                                <select name="school_type" class="form-select">
                                    <?php $types = db_get_rows("SELECT * FROM school_type"); 
                                    foreach($types as $t): ?>
                                        <option value="<?= $t['id'] ?>" <?= ($schoolData['school_type'] == $t['id']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($t['school_type']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Location</label>
                                <input type="text" name="location" class="form-control" value="<?= htmlspecialchars($schoolData['location'] ?? '') ?>">
                            </div>
                            <div class="form-group">
                                <label>State</label>
                                <select name="state" class="form-select">
                                    <?php $states = db_get_rows("SELECT * FROM state WHERE status='1'"); 
                                    foreach($states as $s): ?>
                                        <option value="<?= $s['id'] ?>" <?= ($schoolData['state'] == $s['id']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($s['title']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Motto</label>
                                <input type="text" name="moto" class="form-control" value="<?= htmlspecialchars($schoolData['moto'] ?? '') ?>">
                            </div>
                            <div class="form-group">
                                <label>Website</label>
                                <input type="text" name="website" class="form-control" value="<?= htmlspecialchars($schoolData['website'] ?? '') ?>">
                            </div>
                        </div>
                        <button type="submit" name="configuration" class="btn btn-primary" style="width: 100%;">💾 Save School Info</button>
                    </form>
                </div>
                <?php endif; ?>

                <!-- ============================================================ -->
                <!-- SESSION TAB -->
                <!-- ============================================================ -->
                <?php if ($action == 'session'): ?>
                <div class="config-card">
                    <form method="post" action="<?= $FileName ?>?action=session">
                        <div class="form-group">
                            <div style="display: flex; flex-direction: column; gap: 8px;">
                                <input type="text" name="session" class="form-control" placeholder="e.g., 2024-2025" required>
                                <button type="submit" name="savesession" class="btn btn-primary">+ Add Session</button>
                            </div>
                        </div>
                    </form>
                    
                    <div style="overflow-x: auto; -webkit-overflow-scrolling: touch;">
                        <table class="data-table">
                            <thead><tr><th>#</th><th>Session</th><th>Action</th></tr></thead>
                            <tbody>
                                <?php 
                                $sessions = db_get_rows("SELECT * FROM school_session WHERE create_by_userid = ? ORDER BY id DESC", [$create_by_userid]);
                                $i=1; 
                                foreach($sessions as $s): 
                                ?>
                                <tr>
                                    <td><?= $i++ ?></td>
                                    <td>
                                        <?php if ($randomid == $s['randomid']): ?>
                                            <form method="post" class="inline-form" style="display:flex; flex-wrap:wrap; gap:6px;">
                                                <input type="text" name="session" value="<?= htmlspecialchars($s['session']) ?>" class="form-control" style="flex:1; min-width:100px;">
                                                <button type="submit" name="editsession" class="btn btn-primary btn-sm">Save</button>
                                                <a href="<?= $FileName ?>?action=session" class="btn btn-outline-secondary btn-sm" style="text-decoration:none; text-align:center;">Cancel</a>
                                            </form>
                                        <?php else: ?>
                                            <?= htmlspecialchars($s['session']) ?>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="action-icons">
                                            <?php if ($randomid != $s['randomid']): ?>
                                                <a href="<?= $FileName ?>?action=session&randomid=<?= urlencode($s['randomid']) ?>" title="Edit">
                                                    <i class="fa fa-pencil"></i>
                                                </a>
                                            <?php endif; ?>
                                            <a href="javascript:void(0);" onclick="confirmDelete('<?= $FileName ?>?action=deletesession&randomid=<?= urlencode($s['randomid']) ?>', 'session')" title="Delete">
                                                <i class="fa fa-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if (empty($sessions)): ?>
                                    <tr><td colspan="3" style="text-align:center; color:#999; padding:20px;">No sessions found.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <?php endif; ?>

                <!-- ============================================================ -->
                <!-- TERM TAB -->
                <!-- ============================================================ -->
                <?php if ($action == 'term'): ?>
                <div class="config-card">
                    <form method="post" action="<?= $FileName ?>?action=term">
                        <div class="form-group">
                            <div style="display: flex; flex-direction: column; gap: 8px;">
                                <input type="text" name="term" class="form-control" placeholder="e.g., First Term" required>
                                <button type="submit" name="saveterm" class="btn btn-primary">+ Add Term</button>
                            </div>
                        </div>
                    </form>
                    
                    <div style="overflow-x: auto; -webkit-overflow-scrolling: touch;">
                        <table class="data-table">
                            <thead><tr><th>#</th><th>Term</th><th>Action</th></tr></thead>
                            <tbody>
                                <?php 
                                $terms = db_get_rows("SELECT * FROM school_term WHERE create_by_userid = ? ORDER BY id DESC", [$create_by_userid]);
                                $i=1; 
                                foreach($terms as $t): 
                                ?>
                                <tr>
                                    <td><?= $i++ ?></td>
                                    <td>
                                        <?php if ($randomid == $t['randomid']): ?>
                                            <form method="post" class="inline-form" style="display:flex; flex-wrap:wrap; gap:6px;">
                                                <input type="text" name="term" value="<?= htmlspecialchars($t['term']) ?>" class="form-control" style="flex:1; min-width:100px;">
                                                <button type="submit" name="editterm" class="btn btn-primary btn-sm">Save</button>
                                                <a href="<?= $FileName ?>?action=term" class="btn btn-outline-secondary btn-sm" style="text-decoration:none; text-align:center;">Cancel</a>
                                            </form>
                                        <?php else: ?>
                                            <?= htmlspecialchars($t['term']) ?>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="action-icons">
                                            <?php if ($randomid != $t['randomid']): ?>
                                                <a href="<?= $FileName ?>?action=term&randomid=<?= urlencode($t['randomid']) ?>" title="Edit">
                                                    <i class="fa fa-pencil"></i>
                                                </a>
                                            <?php endif; ?>
                                            <a href="javascript:void(0);" onclick="confirmDelete('<?= $FileName ?>?action=deleteterm&randomid=<?= urlencode($t['randomid']) ?>', 'term')" title="Delete">
                                                <i class="fa fa-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if (empty($terms)): ?>
                                    <tr><td colspan="3" style="text-align:center; color:#999; padding:20px;">No terms found.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <?php endif; ?>

                <!-- ============================================================ -->
                <!-- SECTION TAB -->
                <!-- ============================================================ -->
                <?php if ($action == 'section'): ?>
                <div class="config-card">
                    <form method="post" action="<?= $FileName ?>?action=section">
                        <div class="form-group">
                            <select name="section" class="form-select" id="sectionSelect">
                                <option value="CRECHE">CRECHE</option>
                                <option value="NURSERY">NURSERY</option>
                                <option value="PRIMARY">PRIMARY</option>
                                <option value="SECONDARY">SECONDARY</option>
                                <option value="0">OTHERS</option>
                            </select>
                            <div id="otherSection" style="display:none; margin-top:8px;">
                                <input type="text" name="short_name" class="form-control" placeholder="Enter Section Name">
                            </div>
                            <button type="submit" name="savesection" class="btn btn-primary" style="margin-top:10px; width:100%;">+ Add Section</button>
                        </div>
                    </form>
                    
                    <div style="overflow-x: auto; -webkit-overflow-scrolling: touch;">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Section Name</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $sections = db_get_rows("SELECT * FROM school_section WHERE create_by_userid = ? ORDER BY id DESC", [$create_by_userid]);
                                $i=1; 
                                foreach($sections as $sec): 
                                ?>
                                <tr>
                                    <td><?= $i++ ?></td>
                                    <td>
                                        <?php if ($randomid == $sec['randomid']): ?>
                                            <form method="post" class="inline-form" style="display:flex; flex-wrap:wrap; gap:6px;">
                                                <input type="hidden" name="randomid" value="<?= $sec['randomid'] ?>">
                                                <input type="text" name="short_name" value="<?= htmlspecialchars($sec['short_name']) ?>" class="form-control" style="flex:1; min-width:80px;" required>
                                                <button type="submit" name="editsection" class="btn btn-primary btn-sm">Save</button>
                                                <a href="<?= $FileName ?>?action=section" class="btn btn-outline-secondary btn-sm" style="text-decoration:none; text-align:center;">Cancel</a>
                                            </form>
                                        <?php else: ?>
                                            <?= htmlspecialchars($sec['short_name']) ?>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="action-icons">
                                            <?php if ($randomid != $sec['randomid']): ?>
                                                <a href="<?= $FileName ?>?action=section&randomid=<?= urlencode($sec['randomid']) ?>" title="Edit">
                                                    <i class="fa fa-pencil"></i>
                                                </a>
                                            <?php endif; ?>
                                            <a href="javascript:void(0);" onclick="confirmDelete('<?= $FileName ?>?action=delete_section&randomid=<?= urlencode($sec['randomid']) ?>', 'section')" title="Delete">
                                                <i class="fa fa-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if (empty($sections)): ?>
                                    <tr><td colspan="3" style="text-align:center; color:#999; padding:20px;">No sections found.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <?php endif; ?>

                <!-- ============================================================ -->
                <!-- CLASS TAB -->
                <!-- ============================================================ -->
                <?php if ($action == 'class'): ?>
                <div class="config-card">
                    <form method="post" action="<?= $FileName ?>?action=class">
                        <div class="form-grid">
                            <div class="form-group">
                                <select name="section_id" class="form-select" required>
                                    <option value="">Select Section</option>
                                    <?php foreach($sections as $sec): ?>
                                        <option value="<?= $sec['id'] ?>"><?= htmlspecialchars($sec['section']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <input type="text" name="name" class="form-control" placeholder="Class Name" required>
                            </div>
                            <div class="form-group">
                                <input type="text" name="short_name" class="form-control" placeholder="Short Name" required>
                            </div>
                        </div>
                        <button type="submit" name="saveclass" class="btn btn-primary" style="width:100%;">+ Add Class</button>
                    </form>
                    
                    <div style="overflow-x: auto; -webkit-overflow-scrolling: touch; margin-top:16px;">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Name</th>
                                    <th>Short</th>
                                    <th>Section</th>
                                    <th>PDF</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $classes = db_get_rows("SELECT * FROM school_class WHERE create_by_userid = ? ORDER BY id DESC", [$create_by_userid]);
                                $i=1; 
                                foreach($classes as $cls): 
                                    $sectionRandomid = db_get_val("SELECT randomid FROM school_section WHERE id = ? AND create_by_userid = ?", [$cls['section_id'], $create_by_userid]);
                                ?>
                                <tr>
                                    <td><?= $i++ ?></td>
                                    <td>
                                        <?php if ($randomid == $cls['randomid']): ?>
                                            <form method="post" class="inline-form" style="display:flex; flex-wrap:wrap; gap:4px;">
                                                <input type="hidden" name="randomid" value="<?= $cls['randomid'] ?>">
                                                <input type="text" name="name" value="<?= htmlspecialchars($cls['name']) ?>" class="form-control" style="flex:1; min-width:60px;" required>
                                                <input type="text" name="short_name" value="<?= htmlspecialchars($cls['short_name']) ?>" class="form-control" style="flex:1; min-width:60px;" required>
                                                <button type="submit" name="editclass" class="btn btn-primary btn-sm">Save</button>
                                                <a href="<?= $FileName ?>?action=class" class="btn btn-outline-secondary btn-sm" style="text-decoration:none; text-align:center;">Cancel</a>
                                            </form>
                                        <?php else: ?>
                                            <?= htmlspecialchars($cls['name']) ?>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($cls['short_name']) ?></td>
                                    <td><?= htmlspecialchars(getSectionName($cls['section_id'])) ?></td>
                                    <td>
                                        <?php if (!empty($sectionRandomid)): ?>
                                            <a href="<?= $FileName ?>?action=pdfsetting&randomid=<?= urlencode($sectionRandomid) ?>" title="Configure PDF">
                                                <i class="fa fa-file-pdf-o"></i>
                                            </a>
                                        <?php else: ?>
                                            <span style="color:#ccc;">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="action-icons">
                                            <?php if ($randomid != $cls['randomid']): ?>
                                                <a href="<?= $FileName ?>?action=class&randomid=<?= urlencode($cls['randomid']) ?>" title="Edit">
                                                    <i class="fa fa-pencil"></i>
                                                </a>
                                            <?php endif; ?>
                                            <a href="javascript:void(0);" onclick="confirmDelete('<?= $FileName ?>?action=delete_class&randomid=<?= urlencode($cls['randomid']) ?>', 'class')" title="Delete">
                                                <i class="fa fa-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if (empty($classes)): ?>
                                    <tr><td colspan="6" style="text-align:center; color:#999; padding:20px;">No classes found.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <?php endif; ?>

                <!-- ============================================================ -->
                <!-- SUBJECT TAB -->
                <!-- ============================================================ -->
                <?php if ($action == 'subject'): ?>
                <div class="config-card">
                    <form method="post" action="<?= $FileName ?>?action=subject">
                        <div class="form-grid">
                            <div class="form-group">
                                <select name="section_id" class="form-select" required onchange="loadClasses(this.value)">
                                    <option value="">Select Section</option>
                                    <?php foreach($sections as $sec): ?>
                                        <option value="<?= $sec['id'] ?>"><?= htmlspecialchars($sec['section']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <select name="class_id" id="classSelect" class="form-select" required>
                                    <option value="">Select Class First</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <input type="text" name="subject" class="form-control" placeholder="Subject Name" required>
                            </div>
                        </div>
                        <button type="submit" name="savesubject" class="btn btn-primary" style="width:100%;">+ Add Subject</button>
                    </form>
                    
                    <div style="overflow-x: auto; -webkit-overflow-scrolling: touch; margin-top:16px;">
                        <table class="data-table">
                            <thead><tr><th>#</th><th>Section</th><th>Class</th><th>Subject</th><th>Action</th></tr></thead>
                            <tbody>
                                <?php 
                                $subjects = db_get_rows("SELECT * FROM school_subject WHERE create_by_userid = ? ORDER BY id DESC", [$create_by_userid]);
                                $i=1; 
                                foreach($subjects as $sub): 
                                ?>
                                <tr>
                                    <td><?= $i++ ?></td>
                                    <td><?= getSectionName($sub['section_id']) ?></td>
                                    <td><?= getClassName($sub['class_id']) ?></td>
                                    <td>
                                        <?php if ($randomid == $sub['randomid']): ?>
                                            <form method="post" class="inline-form" style="display:flex; flex-wrap:wrap; gap:6px;">
                                                <input type="text" name="subject" value="<?= htmlspecialchars($sub['subject']) ?>" class="form-control" style="flex:1; min-width:80px;">
                                                <button type="submit" name="editsubject" class="btn btn-primary btn-sm">Save</button>
                                                <a href="<?= $FileName ?>?action=subject" class="btn btn-outline-secondary btn-sm" style="text-decoration:none; text-align:center;">Cancel</a>
                                            </form>
                                        <?php else: ?>
                                            <?= htmlspecialchars($sub['subject']) ?>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="action-icons">
                                            <?php if ($randomid != $sub['randomid']): ?>
                                                <a href="<?= $FileName ?>?action=subject&randomid=<?= urlencode($sub['randomid']) ?>" title="Edit">
                                                    <i class="fa fa-pencil"></i>
                                                </a>
                                            <?php endif; ?>
                                            <a href="javascript:void(0);" onclick="confirmDelete('<?= $FileName ?>?action=delete_subject&randomid=<?= urlencode($sub['randomid']) ?>', 'subject')" title="Delete">
                                                <i class="fa fa-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if (empty($subjects)): ?>
                                    <tr><td colspan="5" style="text-align:center; color:#999; padding:20px;">No subjects found.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <?php endif; ?>

                <!-- ============================================================ -->
                <!-- PDF SETTINGS TAB -->
                <!-- ============================================================ -->
                <?php if ($action == 'pdfsetting' && !empty($randomid)): ?>
                <div class="config-card">
                    <form method="post" action="<?= $FileName ?>?action=pdfsetting&randomid=<?= urlencode($randomid) ?>">
                        <div class="checkbox-group">
                            <?php 
                            $fields = [
                                'is_pos' => 'Position', 
                                'is_out' => 'Out of', 
                                'is_highest_avg' => 'Highest Avg', 
                                'is_lowest_avg' => 'Lowest Avg', 
                                'is_class_avg' => 'Class Avg', 
                                'is_grade_details' => 'Grade Details', 
                                'is_no_of_subjects' => 'No. of Subjects', 
                                'is_grade' => 'Final Grade', 
                                'is_class' => 'Class', 
                                'is_position' => 'Final Position', 
                                'is_totalstudent' => 'Total Students', 
                                'is_addmission' => 'Admission No', 
                                'is_totalscore' => 'Total Score', 
                                'is_session' => 'Session', 
                                'is_finalaverage' => 'Final Avg', 
                                'is_terms' => 'Term', 
                                'is_highestaverage' => 'Highest Avg', 
                                'is_lowestaverage' => 'Lowest Avg', 
                                'is_schoolopen' => 'Days Open', 
                                'is_daypresent' => 'Days Present', 
                                'is_dayabsent' => 'Days Absent', 
                                'is_profilepic' => 'Profile Pic', 
                                'is_affective' => 'Affective', 
                                'is_phycomotor' => 'Psychomotor'
                            ]; 
                            ?>
                            <?php foreach($fields as $key => $label): ?>
                                <label class="checkbox-label">
                                    <input type="checkbox" name="<?= $key ?>" value="1" <?= ((($pdfSettings[$key] ?? '0') == '1') ? 'checked' : '') ?>> 
                                    <?= $label ?>
                                </label>
                            <?php endforeach; ?>
                        </div>
                        <div class="form-grid">
                            <div class="form-group">
                                <label>Title 1 (Class Teacher)</label>
                                <input type="text" name="title_1" class="form-control" value="<?= htmlspecialchars($pdfSettings['title_1'] ?? 'Class Teacher') ?>">
                            </div>
                            <div class="form-group">
                                <label>Title 2 (Teacher's Remarks)</label>
                                <input type="text" name="title_2" class="form-control" value="<?= htmlspecialchars($pdfSettings['title_2'] ?? "Class Teacher's Remarks") ?>">
                            </div>
                            <div class="form-group">
                                <label>Title 3 (Principal's Remarks)</label>
                                <input type="text" name="title_3" class="form-control" value="<?= htmlspecialchars($pdfSettings['title_3'] ?? "Principal's Remarks") ?>">
                            </div>
                            <div class="form-group">
                                <label>Title 4 (Affective Traits)</label>
                                <input type="text" name="title_4" class="form-control" value="<?= htmlspecialchars($pdfSettings['title_4'] ?? 'AFFECTIVE TRAITS') ?>">
                            </div>
                            <div class="form-group">
                                <label>Title 5 (Psychomotor)</label>
                                <input type="text" name="title_5" class="form-control" value="<?= htmlspecialchars($pdfSettings['title_5'] ?? 'PSYCHOMOTOR') ?>">
                            </div>
                        </div>
                        <button type="submit" name="pdfsetting" class="btn btn-primary" style="width:100%;">💾 Save PDF Settings</button>
                    </form>
                </div>
                <?php endif; ?>

            </div>
        </div>
        <?php include('inc.footer.php'); ?>
    </div>
</div>
<?php include('inc.js.php'); ?>

<script>
// Show/hide custom section input
document.getElementById('sectionSelect').addEventListener('change', function() {
    var otherDiv = document.getElementById('otherSection');
    if (this.value == '0') {
        otherDiv.style.display = 'block';
    } else {
        otherDiv.style.display = 'none';
    }
});

// Load classes for subject
function loadClasses(sectionId) {
    fetch('ajax.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'action=getsubclass&sec_id=' + sectionId
    }).then(r => r.text()).then(html => {
        document.getElementById('classSelect').innerHTML = html;
    });
}

// Improved delete confirmation
function confirmDelete(url, itemName) {
    if (confirm('Are you sure you want to delete this ' + itemName + '?\n\nThis action cannot be undone.')) {
        window.location.href = url;
    }
}

// Legacy function for backward compatibility
function del(url) {
    if (confirm('Are you sure you want to delete this item? This action cannot be undone.')) {
        window.location.href = url;
    }
}
</script>
</body>
</html>