<?php
/**
 * AJAX Handler - Modern PHP 8.x Rebuild
 * Handles all AJAX requests with prepared statements and proper error handling
 */

require_once '../config.php';
require_once 'inc.session-create.php';

// ============================================================================
// INITIALIZATION
// ============================================================================
header('Content-Type: text/html; charset=utf-8');

$action = $_POST['action'] ?? '';
$stat = [];

// ============================================================================
// ACTION: CHANGE PASSWORD (Parent/Student Password Change)
// ============================================================================
if ($action == "Action_changepass") {
    $oldPassword = $_POST['old_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $userId = $_SESSION['userid'] ?? 0;
    
    // Validation
    $errors = [];
    
    if (empty($oldPassword)) {
        $errors[] = "Old password is required";
    }
    if (empty($newPassword)) {
        $errors[] = "New password is required";
    }
    if (empty($confirmPassword)) {
        $errors[] = "Confirm password is required";
    }
    if (strlen($newPassword) < 6) {
        $errors[] = "New password must be at least 6 characters";
    }
    if ($newPassword !== $confirmPassword) {
        $errors[] = "Confirm password does not match";
    }
    
    if (empty($errors)) {
        // Get user record
        $userRecord = db_get_row("SELECT * FROM student_guardian WHERE id = ?", [$userId]);
        
        if (!empty($userRecord)) {
            // Verify old password (supports both old plain text and new hashed)
            $passwordValid = false;
            $storedPassword = $userRecord['password'] ?? '';
            
            // Check if stored password is hashed (starts with $2y$)
            if (strpos($storedPassword, '$2y$') === 0) {
                // New hashed password
                $passwordValid = password_verify($oldPassword, $storedPassword);
            } else {
                // Old plain text password (for backward compatibility)
                $passwordValid = ($storedPassword === $oldPassword);
            }
            
            if ($passwordValid) {
                // Hash the new password
                $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                
                // Update password
                db_update("student_guardian", ['password' => $hashedPassword], "id = ?", [$userId]);
                
                echo "1";
                exit;
            } else {
                $stat['error'] = "Incorrect old password";
            }
        } else {
            $stat['error'] = "User not found";
        }
    } else {
        $stat['error'] = implode("<br>", $errors);
    }
    
    echo msg($stat);
    exit;
}

// ============================================================================
// ACTION: GET CLASS (Returns checkboxes for classes in a section)
// ============================================================================
elseif ($action == "getclass") {
    $secId = $_POST['sec_id'] ?? 0;
    
    if (!empty($secId)) {
        $classes = db_get_rows(
            "SELECT * FROM school_class WHERE section_id = ? ORDER BY name ASC",
            [$secId]
        );
        
        if (!empty($classes)) {
            ?>
            <label class="col-lg-2 control-label" for="userName">Select Class:</label>
            <div class="col-lg-10">
                <?php foreach ($classes as $class): ?>
                    <input type="checkbox" name="class[]" class="classList" onchange="getsubject()" value="<?= e($class['id']) ?>">
                    <?= e($class['name']) ?>
                <?php endforeach; ?>
            </div>
            <?php
        } else {
            echo '<div class="alert alert-warning">No classes found for this section.</div>';
        }
    } else {
        echo '<div class="alert alert-danger">Invalid section ID.</div>';
    }
    exit;
}

// ============================================================================
// ACTION: GET SECTION (Returns dropdown of sections for a session)
// ============================================================================
elseif ($action == "getsection") {
    $sesId = $_POST['ses_id'] ?? 0;
    $selectedSection = $_POST['section'] ?? '';
    
    if (!empty($sesId)) {
        $sections = db_get_rows(
            "SELECT * FROM school_section WHERE id = ?",
            [$sesId]
        );
        ?>
        <select name="section" id="section" class="form-control" onchange="getclass()">
            <option value="">Select Section</option>
            <?php foreach ($sections as $section): ?>
                <option value="<?= e($section['id']) ?>" <?= selected($selectedSection, $section['id']) ?>>
                    <?= e($section['section']) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <?php
    } else {
        echo '<select class="form-control"><option>No sections available</option></select>';
    }
    exit;
}

// ============================================================================
// ACTION: GET SUBJECT (Returns dropdown of subjects for selected classes)
// ============================================================================
elseif ($action == "getsubject") {
    $classIds = $_POST['class_iid'] ?? '';
    $selectedSubject = $_POST['subject'] ?? '';
    
    if (!empty($classIds)) {
        $classArray = explode(',', $classIds);
        $placeholders = implode(',', array_fill(0, count($classArray), '?'));
        
        $subjects = db_get_rows(
            "SELECT * FROM school_subject WHERE class_id IN ($placeholders) ORDER BY id DESC",
            $classArray
        );
        
        if (!empty($subjects)) {
            ?>
            <select name="subject" class="form-control">
                <option value="">Select Subject</option>
                <?php foreach ($subjects as $subject): ?>
                    <option value="<?= e($subject['id']) ?>" <?= selected($selectedSubject, $subject['id']) ?>>
                        <?= e($subject['subject']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <?php
        } else {
            echo '<select class="form-control"><option value="">No subjects found</option></select>';
        }
    } else {
        echo '<select class="form-control"><option value="">Please select a class first</option></select>';
    }
    exit;
}

// ============================================================================
// ACTION: GET SUBCLASS (Returns dropdown of classes for a section)
// ============================================================================
elseif ($action == "getsubclass") {
    $secId = $_POST['sec_id'] ?? 0;
    $selectedClass = $_POST['class'] ?? '';
    
    if (!empty($secId)) {
        $classes = db_get_rows(
            "SELECT * FROM school_class WHERE section_id = ? ORDER BY name ASC",
            [$secId]
        );
        ?>
        <option value="">Select Class</option>
        <?php foreach ($classes as $class): ?>
            <option value="<?= e($class['id']) ?>" <?= selected($selectedClass, $class['id']) ?>>
                <?= e($class['name']) ?>
            </option>
        <?php endforeach;
    } else {
        echo '<option value="">No classes available</option>';
    }
    exit;
}

// ============================================================================
// ACTION: GET CATEGORY NAME (For dynamic category fields)
// ============================================================================
elseif ($action == "getcategoryname") {
    $categoryId = $_POST['category'] ?? 0;
    
    if (!empty($categoryId)) {
        $customFields = db_get_rows(
            "SELECT * FROM custom_field WHERE category_id = ? ORDER BY id ASC",
            [$categoryId]
        );
        
        if (!empty($customFields)) {
            ?>
            <div class="row" style="margin-bottom: 10px;">
                <?php foreach ($customFields as $index => $field): ?>
                    <div class="col-md-4">
                        <label><?= e($field['field_label']) ?></label>
                        <input type="text" name="value[]" id="value<?= $index ?>" 
                               class="form-control" placeholder="Enter <?= e($field['field_label']) ?>" 
                               onchange="getdropvalue(<?= $index ?>)">
                    </div>
                <?php endforeach; ?>
            </div>
            <input type="hidden" name="newvalue" id="newvalue">
            <?php
        }
    }
    exit;
}

// ============================================================================
// ACTION: INVITED USERS (Toggle event invitation)
// ============================================================================
elseif ($action == "invited_users") {
    $eventId = $_POST['evid'] ?? 0;
    $userId = $_SESSION['userid'] ?? 0;
    
    if (empty($userId)) {
        echo '0';
        exit;
    }
    
    // Check if already invited
    $existing = db_get_val(
        "SELECT id FROM invited_users WHERE userid = ? AND event_id = ?",
        [$userId, $eventId]
    );
    
    if (!empty($existing)) {
        // Remove invitation
        db_delete("invited_users", "userid = ? AND event_id = ?", [$userId, $eventId]);
        echo '2'; // Removed
    } else {
        // Add invitation
        $randomId = randomFix(10);
        db_insert("invited_users", [
            'userid' => $userId,
            'event_id' => $eventId,
            'usertype' => $_SESSION['usertype'] ?? '',
            'create_by_userid' => $userId,
            'create_by_usertype' => $_SESSION['usertype'] ?? '',
            'randomid' => $randomId
        ]);
        echo '1'; // Added
    }
    exit;
}

// ============================================================================
// ACTION: GET ASSESSMENT (For score entry time frame)
// ============================================================================
elseif ($action == "Action_getassesment") {
    $classId = $_POST['class_id'] ?? 0;
    
    if (!empty($classId)) {
        $assessments = db_get_rows(
            "SELECT * FROM school_assessment WHERE class_id = ? ORDER BY assesment ASC",
            [$classId]
        );
        ?>
        <select class="form-control" name="assesment" id="assesment">
            <option value="">Select Assessment</option>
            <?php foreach ($assessments as $assessment): ?>
                <option value="<?= e($assessment['id']) ?>">
                    <?= e($assessment['assesment']) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <?php
    } else {
        echo '<select class="form-control"><option value="">Select class first</option></select>';
    }
    exit;
}

// ============================================================================
// DEFAULT: Unknown action
// ============================================================================
else {
    echo '<div class="alert alert-danger">Invalid action requested: ' . e($action) . '</div>';
    exit;
}

// ============================================================================
// HELPER FUNCTIONS (If not already defined)
// ============================================================================
if (!function_exists('e')) {
    function e($string) {
        return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('selected')) {
    function selected($value, $compare, $default = false) {
        if ($default && empty($value)) return '';
        return ($value == $compare) ? 'selected' : '';
    }
}

if (!function_exists('msg')) {
    function msg($msg = []) {
        if (!is_array($msg) || empty($msg)) {
            return '';
        }
        
        $output = '';
        foreach ($msg as $type => $content) {
            if (!empty($content)) {
                $alertClass = ($type == 'success') ? 'success' : (($type == 'error') ? 'danger' : $type);
                $output .= '<div class="alert alert-' . $alertClass . ' alert-dismissable">
                                <button type="button" class="close" data-dismiss="alert">×</button>
                                ' . $content . '
                            </div>';
            }
        }
        return $output;
    }
}
?>