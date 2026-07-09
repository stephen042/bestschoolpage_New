<?php

/**
 * Student Information Page - Modern PHP 8.x
 * Features: Auto Student ID generation, Sibling pairing (Add + Edit), Auto-create missing parent
 * NEW: Assign Parent from existing students
 * PARENT ACCOUNTS STORED IN student_guardian TABLE
 */

require_once('../config.php');
require_once('inc.session-create.php');

$PageTitle = "Student Information";
$FileName = 'student.php';

// ============================================================================
// INITIALIZATION
// ============================================================================
$stat = [];
$action = $_GET['action'] ?? '';
$randomid = $_GET['randomid'] ?? '';
$search_session = $_GET['session'] ?? '';
$search_term = $_GET['term_id'] ?? '';
$create_by_userid = (int)($_SESSION['create_by_userid'] ?? ($_SESSION['userid'] ?? 0));
$create_by_usertype = $_SESSION['usertype'] ?? '';

if (isset($_SESSION['success']) && $_SESSION['success'] != "") {
    $stat['success'] = $_SESSION['success'];
    unset($_SESSION['success']);
}

// Get school name for ID prefix
$school = db_get_row("SELECT name FROM school_register WHERE id = ?", [$create_by_userid]);
$schoolPrefix = strtoupper(substr(preg_replace('/[^A-Za-z]/', '', $school['name'] ?? 'SCH'), 0, 3));
if (empty($schoolPrefix)) $schoolPrefix = 'SCH';

// ============================================================================
// LEGACY REPAIR: BACKFILL EMPTY parent_id FROM student_guardian LINKS
// ============================================================================
function backfillMissingParentIds(int $schoolId): void
{
    if ($schoolId <= 0) {
        return;
    }

    $missing = db_get_rows(
        "SELECT ms.id, ms.student_id,
                (SELECT sg.parent_id
                   FROM student_guardian sg
                  WHERE sg.type = 1
                    AND sg.parent_id IS NOT NULL
                    AND sg.parent_id != ''
                    AND (sg.student_id_str = ms.student_id OR sg.student_id = ms.id)
                  ORDER BY sg.id DESC
                  LIMIT 1) AS resolved_parent
           FROM manage_student ms
          WHERE ms.create_by_userid = ?
            AND (ms.parent_id IS NULL OR ms.parent_id = '')",
        [$schoolId]
    ) ?: [];

    foreach ($missing as $row) {
        $resolvedParent = trim((string)($row['resolved_parent'] ?? ''));
        $studentRowId = (int)($row['id'] ?? 0);
        if ($studentRowId > 0 && $resolvedParent !== '') {
            db_update('manage_student', ['parent_id' => $resolvedParent], 'id = ?', [$studentRowId]);
        }
    }
}

backfillMissingParentIds($create_by_userid);

// ============================================================================
// FUNCTION: Generate Student ID
// ============================================================================
function generateStudentID($prefix, $year)
{
    $pattern = $prefix . '/%/' . $year;
    $lastStudent = db_get_val(
        "SELECT student_id FROM manage_student WHERE student_id LIKE ? ORDER BY id DESC LIMIT 1",
        [$pattern]
    );

    if ($lastStudent) {
        preg_match('/\/(\d{4})\//', $lastStudent, $matches);
        $lastNum = isset($matches[1]) ? (int)$matches[1] : 0;
        $newNum = $lastNum + 1;
    } else {
        $newNum = 1;
    }

    $serial = str_pad($newNum, 4, '0', STR_PAD_LEFT);
    return $prefix . '/' . $serial . '/' . $year;
}

// ============================================================================
// FUNCTION: Generate Random String with Prefix
// ============================================================================
function randomFix($length = 15) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $randomString;
}

// ============================================================================
// FUNCTION: Generate Secure Password (Uppercase + Numbers + Symbols)
// ============================================================================
function generateSecurePassword($length = 10)
{
    $uppercase = 'ABCDEFGHJKLMNPQRSTUVWXYZ';
    $numbers = '0123456789';
    $symbols = '!@$&?';
    $all = $uppercase . $numbers . $symbols;

    $password = '';
    for ($i = 0; $i < $length; $i++) {
        $password .= $all[random_int(0, strlen($all) - 1)];
    }
    return $password;
}

// ============================================================================
// FUNCTION: Create Parent Account (IN student_guardian table ONLY)
// ============================================================================
function createParentAccount($studentId, $firstName, $lastName, $email, $phone, $title = '', $parentFirstName = '', $parentLastName = '')
{
    global $create_by_userid, $create_by_usertype;

    $password = generateSecurePassword(10);
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // If parent names are not provided, use the student's names
    if (empty($parentFirstName)) {
        $parentFirstName = $firstName;
    }
    if (empty($parentLastName)) {
        $parentLastName = $lastName;
    }

    // Check if parent already exists in student_guardian
    $existingGuardian = db_get_val("SELECT id FROM student_guardian WHERE parent_id = ?", [$studentId]);

    if (!$existingGuardian) {
        $randomId = randomFix(15) . '-' . time();
        
        db_insert("student_guardian", [
            'student_id' => 0,
            'parent_id' => $studentId,
            'password' => $hashedPassword,
            'title' => $title,
            'first_name' => $parentFirstName,
            'last_name' => $parentLastName,
            'email' => $email,
            'phone' => $phone,
            'create_by_userid' => $create_by_userid,
            'create_by_usertype' => $create_by_usertype,
            'userid' => $create_by_userid,
            'usertype' => $create_by_usertype,
            'randomid' => $randomId,
            'status' => 1,
            'type' => 1,
            'create_at' => date('Y-m-d H:i:s')
        ]);
    } else {
        // Update existing parent's password if it's plain text
        $existing = db_get_row("SELECT password FROM student_guardian WHERE parent_id = ?", [$studentId]);
        if ($existing && strpos($existing['password'], '$2y$') !== 0) {
            db_update("student_guardian", ['password' => $hashedPassword], "parent_id = ?", [$studentId]);
        }
    }

    return ['username' => $studentId, 'password' => $password];
}


// ============================================================================
// FUNCTION: Resolve the family anchor parent ID for sibling linking
// ============================================================================
function resolveFamilyParentLink($selectedParentRef, $createByUserId)
{
    $selectedParentRef = trim((string)$selectedParentRef);
    if ($selectedParentRef === '') {
        return ['parent_id' => '', 'family_ids' => []];
    }

    $visited = [];
    $currentRef = $selectedParentRef;
    $familyIds = [];

    while ($currentRef !== '' && !in_array($currentRef, $visited, true)) {
        $visited[] = $currentRef;

        $familyStudent = db_get_row(
            "SELECT id, student_id, parent_id FROM manage_student WHERE create_by_userid = ? AND (student_id = ? OR id = ?)",
            [$createByUserId, $currentRef, (int)$currentRef]
        );

        if (!empty($familyStudent)) {
            $studentId = trim((string)($familyStudent['student_id'] ?? ''));
            $studentRowId = (int)($familyStudent['id'] ?? 0);
            $parentId = trim((string)($familyStudent['parent_id'] ?? ''));

            if ($studentId !== '') {
                $familyIds[] = $studentId;
            }
            if ($studentRowId > 0) {
                $familyIds[] = (string)$studentRowId;
            }

            if ($parentId === '' || $parentId === $studentId) {
                return [
                    'parent_id' => $studentId !== '' ? $studentId : $currentRef,
                    'family_ids' => array_values(array_unique($familyIds))
                ];
            }

            $currentRef = $parentId;
            continue;
        }

        break;
    }

    return [
        'parent_id' => $selectedParentRef,
        'family_ids' => array_values(array_unique($familyIds))
    ];
}

// ============================================================================
// FUNCTION: Assign Parent ID from another student (with duplicate check)
// ============================================================================
function assignParentFromStudent($targetStudentId, $sourceStudentId, $createByUserId)
{
    // Check if target already has this parent
    $targetCurrent = db_get_row(
        "SELECT student_id, parent_id FROM manage_student WHERE student_id = ? AND create_by_userid = ?",
        [$targetStudentId, $createByUserId]
    );
    
    // Get the source student's parent_id
    $sourceStudent = db_get_row(
        "SELECT student_id, parent_id FROM manage_student WHERE student_id = ? AND create_by_userid = ?",
        [$sourceStudentId, $createByUserId]
    );

    if (empty($sourceStudent)) {
        return ['success' => false, 'message' => 'Source student not found'];
    }

    $parentIdToAssign = $sourceStudent['parent_id'];
    
    // Check if target already has this parent
    if ($targetCurrent && $targetCurrent['parent_id'] === $parentIdToAssign) {
        return ['success' => false, 'message' => 'This student already has this parent ID assigned.'];
    }

    // If source has no parent_id, we need to create one using source student's info
    if (empty($parentIdToAssign)) {
        // Get source student's details to create parent account
        $sourceFull = db_get_row(
            "SELECT first_name, last_name, email, phone FROM manage_student WHERE student_id = ? AND create_by_userid = ?",
            [$sourceStudentId, $createByUserId]
        );

        if (empty($sourceFull)) {
            return ['success' => false, 'message' => 'Could not get source student details'];
        }

        // Create parent account using source student's ID as parent_id
        $parentIdToAssign = $sourceStudentId;
        $credentials = createParentAccount(
            $parentIdToAssign,
            $sourceFull['first_name'],
            $sourceFull['last_name'],
            $sourceFull['email'] ?? '',
            $sourceFull['phone'] ?? '',
            'Mr.',
            $sourceFull['first_name'],
            $sourceFull['last_name']
        );

        // Update source student with its own parent_id
        db_update(
            "manage_student",
            ['parent_id' => $parentIdToAssign],
            "student_id = ? AND create_by_userid = ?",
            [$sourceStudentId, $createByUserId]
        );
    }

    // Assign the parent_id to the target student
    $updateResult = db_update(
        "manage_student",
        ['parent_id' => $parentIdToAssign],
        "student_id = ? AND create_by_userid = ?",
        [$targetStudentId, $createByUserId]
    );

    if ($updateResult !== false) {
        return [
            'success' => true,
            'message' => 'Parent ID assigned successfully! Parent ID: ' . $parentIdToAssign,
            'parent_id' => $parentIdToAssign
        ];
    } else {
        return ['success' => false, 'message' => 'Failed to update student'];
    }
}

// ============================================================================
// GET EXISTING PARENTS FOR SIBLING DROPDOWN (from student_guardian)
// ============================================================================
$parentGuardians = db_get_rows(
    "SELECT DISTINCT parent_id, first_name, last_name 
     FROM student_guardian 
     WHERE create_by_userid = ? AND parent_id IS NOT NULL AND parent_id != '' AND type = 1
     ORDER BY first_name ASC",
    [$create_by_userid]
);

// Also get parents from manage_student where parent_id exists
$existingParents = db_get_rows(
    "SELECT DISTINCT student_id, first_name, last_name 
     FROM manage_student 
     WHERE create_by_userid = ? AND student_id IS NOT NULL AND student_id != '' AND parent_id IS NOT NULL AND parent_id != ''
     ORDER BY first_name ASC",
    [$create_by_userid]
);

// Merge and deduplicate parent list
$allParentOptions = [];
foreach ($parentGuardians as $p) {
    $allParentOptions[$p['parent_id']] = $p['first_name'] . ' ' . $p['last_name'];
}
foreach ($existingParents as $p) {
    if (!isset($allParentOptions[$p['student_id']])) {
        $allParentOptions[$p['student_id']] = $p['first_name'] . ' ' . $p['last_name'];
    }
}

// ============================================================================
// GET ALL STUDENTS FOR PARENT ASSIGNMENT (with their current parent_id)
// ============================================================================
$allStudentsForParentAssign = db_get_rows(
    "SELECT student_id, first_name, last_name, parent_id 
     FROM manage_student 
     WHERE create_by_userid = ? 
     ORDER BY first_name ASC",
    [$create_by_userid]
);

// ============================================================================
// HANDLE ASSIGN PARENT ACTION
// ============================================================================
if (isset($_POST['assign_parent']) && !empty($randomid)) {
    $sourceStudentId = trim($_POST['source_student_id'] ?? '');
    $targetStudentId = trim($_POST['target_student_id'] ?? '');

    if (empty($sourceStudentId)) {
        $stat['error'] = "Please select a student to copy parent ID from.";
    } elseif ($sourceStudentId === $targetStudentId) {
        $stat['error'] = "Cannot assign parent from the same student.";
    } else {
        $result = assignParentFromStudent($targetStudentId, $sourceStudentId, $create_by_userid);

        if ($result['success']) {
            $_SESSION['success'] = $result['message'];
            // Also ensure the parent exists in student_guardian
            $guardianExists = db_get_val("SELECT id FROM student_guardian WHERE parent_id = ?", [$result['parent_id']]);
            if (!$guardianExists) {
                // Get target student details
                $targetStudent = db_get_row(
                    "SELECT first_name, last_name, email, phone FROM manage_student WHERE student_id = ? AND create_by_userid = ?",
                    [$targetStudentId, $create_by_userid]
                );
                if ($targetStudent) {
                    createParentAccount(
                        $result['parent_id'],
                        $targetStudent['first_name'],
                        $targetStudent['last_name'],
                        $targetStudent['email'] ?? '',
                        $targetStudent['phone'] ?? '',
                        'Mr.',
                        $targetStudent['first_name'],
                        $targetStudent['last_name']
                    );
                }
            }
            redirect($FileName . '?randomid=' . $randomid);
        } else {
            $stat['error'] = $result['message'];
        }
    }
}

// ============================================================================
// ADD STUDENT (WITH SIBLING PAIRING)
// ============================================================================
if (isset($_POST['add_student'])) {
    $firstName = mb_strtoupper(trim($_POST['first_name'] ?? ''), 'UTF-8');
    $lastName = mb_strtoupper(trim($_POST['last_name'] ?? ''), 'UTF-8');
    $gender = $_POST['gender'] ?? '';
    $dateOfBirth = $_POST['date_of_birth'] ?? '';
    $sessionId = $_POST['session'] ?? 0;
    $termId = $_POST['term_id'] ?? 0;
    $classId = $_POST['class'] ?? 0;
    $admissionDate = $_POST['date_of_admission'] ?? date('Y-m-d');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $existingParentId = $_POST['existing_parent_id'] ?? '';
    $parentTitle = $_POST['parent_title'] ?? '';
    $parentFirstName = mb_strtoupper(trim($_POST['parent_first_name'] ?? ''), 'UTF-8');
    $parentLastName = mb_strtoupper(trim($_POST['parent_last_name'] ?? ''), 'UTF-8');

    $errors = [];
    if (empty($firstName)) $errors[] = "First Name is required";
    if (empty($lastName)) $errors[] = "Last Name is required";
    if (empty($gender)) $errors[] = "Gender is required";
    if (empty($sessionId)) $errors[] = "Session is required";
    if (empty($termId)) $errors[] = "Term is required";
    if (empty($classId)) $errors[] = "Class is required";

    if (empty($errors)) {
        $admissionYear = date('Y', strtotime($admissionDate));
        $studentId = generateStudentID($schoolPrefix, $admissionYear);

        // Determine Parent ID
        if (!empty($existingParentId)) {
            // Link to the family anchor (first registered sibling) so all siblings share one parent ID.
            $resolvedFamily = resolveFamilyParentLink($existingParentId, $create_by_userid);
            $parentId = $resolvedFamily['parent_id'] ?: $existingParentId;
            $parentCreated = false;
            $parentCredentials = null;

            if ($parentId !== '') {
                $guardianExists = db_get_val("SELECT id FROM student_guardian WHERE parent_id = ?", [$parentId]);
                if (!$guardianExists) {
                    // Use parent names if provided, otherwise use student names
                    $pFirstName = !empty($parentFirstName) ? $parentFirstName : $firstName;
                    $pLastName = !empty($parentLastName) ? $parentLastName : $lastName;
                    createParentAccount($parentId, $firstName, $lastName, $email, $phone, $parentTitle, $pFirstName, $pLastName);
                }
            }

            if (!empty($resolvedFamily['family_ids'])) {
                $familyIdPlaceholders = implode(',', array_fill(0, count($resolvedFamily['family_ids']), '?'));
                db_update(
                    "manage_student",
                    ['parent_id' => $parentId],
                    "create_by_userid = ? AND (student_id IN ($familyIdPlaceholders) OR parent_id IN ($familyIdPlaceholders) OR id IN ($familyIdPlaceholders))",
                    array_merge([$create_by_userid], $resolvedFamily['family_ids'], $resolvedFamily['family_ids'], $resolvedFamily['family_ids'])
                );
            }
        } else {
            // First child - Create new parent account
            $parentId = $studentId;
            // Use parent names if provided, otherwise use student names
            $pFirstName = !empty($parentFirstName) ? $parentFirstName : $firstName;
            $pLastName = !empty($parentLastName) ? $parentLastName : $lastName;
            $parentCredentials = createParentAccount(
                $parentId,
                $firstName,
                $lastName,
                $email,
                $phone,
                $parentTitle,
                $pFirstName,
                $pLastName
            );
            $parentCreated = true;
        }

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

        $studentData = [
            'userid' => $_SESSION['userid'] ?? 0,
            'usertype' => $_SESSION['usertype'] ?? '',
            'student_id' => $studentId,
            'parent_id' => $parentId,
            'session' => $sessionId,
            'term_id' => $termId,
            'class' => $classId,
            'last_name' => $lastName,
            'first_name' => $firstName,
            'date_of_admission' => $admissionDate,
            'gender' => $gender,
            'date_of_birth' => $dateOfBirth,
            'picture' => $picture,
            'email' => $email,
            'phone' => $phone,
            'create_by_userid' => $create_by_userid,
            'create_by_usertype' => $create_by_usertype,
            'randomid' => $randomId,
        ];

        $insertId = db_insert("manage_student", $studentData);

        if ($insertId) {
            $_SESSION['success'] = "Student added successfully! Student ID: " . $studentId;

            if ($parentCreated && $parentCredentials) {
                $_SESSION['success'] .= " | Parent Login ID: " . $parentCredentials['username'] . " | Password: " . $parentCredentials['password'];
                $_SESSION['temp_password'] = $parentCredentials['password'];
            } elseif (!empty($existingParentId)) {
                $_SESSION['success'] .= " | Linked to existing parent: " . $existingParentId;
            }

            redirect($FileName . '?randomid=' . $randomId);
        } else {
            $stat['error'] = "Failed to add student";
        }
    } else {
        $stat['error'] = implode("<br>", $errors);
    }
}

// ============================================================================
// UPDATE STUDENT (WITH AUTO-CREATE MISSING PARENT)
// ============================================================================
if (isset($_POST['edit_student']) && !empty($randomid)) {
    $firstName = mb_strtoupper(trim($_POST['first_name'] ?? ''), 'UTF-8');
    $lastName = mb_strtoupper(trim($_POST['last_name'] ?? ''), 'UTF-8');
    $gender = $_POST['gender'] ?? '';
    $dateOfBirth = $_POST['date_of_birth'] ?? '';
    $sessionId = $_POST['session'] ?? 0;
    $termId = $_POST['term_id'] ?? 0;
    $classId = $_POST['class'] ?? 0;
    $admissionDate = $_POST['date_of_admission'] ?? '';
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $newParentId = $_POST['existing_parent_id'] ?? '';
    $parentTitle = $_POST['parent_title'] ?? '';
    $parentFirstName = mb_strtoupper(trim($_POST['parent_first_name'] ?? ''), 'UTF-8');
    $parentLastName = mb_strtoupper(trim($_POST['parent_last_name'] ?? ''), 'UTF-8');

    $picture = $_POST['picture_old'] ?? '';
    if (isset($_FILES['picture']['name']) && !empty($_FILES['picture']['name'])) {
        $ext = strtolower(pathinfo($_FILES['picture']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg', 'png', 'jpeg', 'gif', 'webp'])) {
            $picture = md5(time() . uniqid()) . "_" . preg_replace('/[^a-zA-Z0-9._-]/', '_', $_FILES['picture']['name']);
            move_uploaded_file($_FILES['picture']['tmp_name'], "../uploads/" . $picture);
            $oldStudent = db_get_row("SELECT picture FROM manage_student WHERE randomid = ?", [$randomid]);
            if (!empty($oldStudent['picture']) && file_exists("../uploads/" . $oldStudent['picture'])) {
                unlink("../uploads/" . $oldStudent['picture']);
            }
        }
    }

    // If changing parent, resolve the family anchor and propagate it across related siblings.
    $parentCreatedMsg = '';
    if (!empty($newParentId)) {
        $resolvedFamily = resolveFamilyParentLink($newParentId, $create_by_userid);
        $resolvedParentId = $resolvedFamily['parent_id'] ?: $newParentId;

        $parentExists = db_get_val("SELECT id FROM student_guardian WHERE parent_id = ?", [$resolvedParentId]);

        if (!$parentExists) {
            $parentFirstName = !empty($parentFirstName) ? $parentFirstName : $firstName;
            $parentLastName = !empty($parentLastName) ? $parentLastName : $lastName;
            $parentEmail = $email;
            $parentPhone = $phone;

            $parentCredentials = createParentAccount(
                $resolvedParentId, 
                $firstName, 
                $lastName, 
                $parentEmail, 
                $parentPhone, 
                $parentTitle,
                $parentFirstName,
                $parentLastName
            );

            if ($parentCredentials) {
                $parentCreatedMsg = " | NEW parent account created! Login: " . $parentCredentials['username'] . " | Password: " . $parentCredentials['password'];
                $_SESSION['temp_password'] = $parentCredentials['password'];
            } else {
                $parentCreatedMsg = " | Parent account creation failed.";
            }
        } else {
            $parentCreatedMsg = " | Linked to existing parent: " . $resolvedParentId;
        }

        if (!empty($resolvedFamily['family_ids'])) {
            $familyIdPlaceholders = implode(',', array_fill(0, count($resolvedFamily['family_ids']), '?'));
            db_update(
                "manage_student",
                ['parent_id' => $resolvedParentId],
                "create_by_userid = ? AND (student_id IN ($familyIdPlaceholders) OR parent_id IN ($familyIdPlaceholders) OR id IN ($familyIdPlaceholders))",
                array_merge([$create_by_userid], $resolvedFamily['family_ids'], $resolvedFamily['family_ids'], $resolvedFamily['family_ids'])
            );
        }

        $newParentId = $resolvedParentId;
    }

    $updateData = [
        'first_name' => $firstName,
        'last_name' => $lastName,
        'gender' => $gender,
        'date_of_birth' => $dateOfBirth,
        'session' => $sessionId,
        'term_id' => $termId,
        'class' => $classId,
        'date_of_admission' => $admissionDate,
        'picture' => $picture,
        'email' => $email,
        'phone' => $phone,
    ];

    // If parent ID is being changed, update it
    if (!empty($newParentId)) {
        $updateData['parent_id'] = $newParentId;
    }

    db_update("manage_student", $updateData, "randomid = ?", [$randomid]);

    $_SESSION['success'] = "Student updated successfully" . $parentCreatedMsg;

    redirect($FileName . '?randomid=' . $randomid);
}

// ============================================================================
// DELETE STUDENT
// ============================================================================
if (isset($_GET['delete']) && !empty($_GET['delete'])) {
    $student = db_get_row("SELECT * FROM manage_student WHERE randomid = ?", [$_GET['delete']]);
    if ($student) {
        if (!empty($student['picture']) && file_exists("../uploads/" . $student['picture'])) {
            unlink("../uploads/" . $student['picture']);
        }
        db_delete("manage_student", "randomid = ?", [$_GET['delete']]);
        $_SESSION['success'] = "Student deleted successfully";
    }
    redirect($FileName);
}

// ============================================================================
// EXPORT STUDENTS TO CSV
// ============================================================================
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    $export_session = $_GET['session'] ?? '';
    $export_term = $_GET['term_id'] ?? '';

    // Build query to get all students
    $exportSQL = "WHERE create_by_userid = ?";
    $exportParams = [$create_by_userid];
    if (!empty($export_session)) {
        $exportSQL .= " AND session = ?";
        $exportParams[] = $export_session;
    }
    if (!empty($export_term)) {
        $exportSQL .= " AND term_id = ?";
        $exportParams[] = $export_term;
    }

    $exportStudents = db_get_rows("SELECT * FROM manage_student $exportSQL ORDER BY first_name ASC", $exportParams);

    if (!empty($exportStudents)) {
        // CSV Export
        $filename = 'students_' . date('Y-m-d_H-i-s') . '.csv';
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $output = fopen('php://output', 'w');
        // Add BOM for Excel UTF-8
        fputs($output, "\xEF\xBB\xBF");

        // Headers
        fputcsv($output, [
            'Student ID',
            'First Name',
            'Last Name',
            'Email',
            'Phone',
            'Gender',
            'Date of Birth',
            'Date of Admission',
            'Class',
            'Session',
            'Term',
            'Parent ID',
            'Status'
        ]);

        // Data rows
        foreach ($exportStudents as $student) {
            fputcsv($output, [
                $student['student_id'],
                $student['first_name'],
                $student['last_name'],
                $student['email'],
                $student['phone'],
                $student['gender'],
                $student['date_of_birth'],
                $student['date_of_admission'],
                getClassName($student['class']),
                getSessionName($student['session']),
                getTermName($student['term_id']),
                $student['parent_id'],
                'Active'
            ]);
        }
        fclose($output);
        exit;
    } else {
        $_SESSION['error'] = "No students found to export";
        redirect($FileName);
    }
}

// ============================================================================
// GET DATA FOR DISPLAY
// ============================================================================
$editStudent = null;
if (!empty($randomid)) {
    $editStudent = db_get_row("SELECT * FROM manage_student WHERE randomid = ?", [$randomid]);
}

$searchSQL = "WHERE create_by_userid = ?";
$searchParams = [$create_by_userid];
if (!empty($search_session)) {
    $searchSQL .= " AND session = ?";
    $searchParams[] = $search_session;
}
if (!empty($search_term)) {
    $searchSQL .= " AND term_id = ?";
    $searchParams[] = $search_term;
}

$students = db_get_rows("SELECT * FROM manage_student $searchSQL ORDER BY id DESC", $searchParams);
$sessions = db_get_rows("SELECT * FROM school_session WHERE create_by_userid = ? ORDER BY id DESC", [$create_by_userid]);
$terms = db_get_rows("SELECT * FROM school_term WHERE create_by_userid = ? ORDER BY id DESC", [$create_by_userid]);
$classes = db_get_rows("SELECT * FROM school_class WHERE create_by_userid = ? ORDER BY name ASC", [$create_by_userid]);
$tempPassword = $_SESSION['temp_password'] ?? '';
unset($_SESSION['temp_password']);

function getClassName($id)
{
    return db_get_val("SELECT name FROM school_class WHERE id = ?", [$id]) ?: 'N/A';
}
function getSessionName($id)
{
    return db_get_val("SELECT session FROM school_session WHERE id = ?", [$id]) ?: 'N/A';
}
function getTermName($id)
{
    return db_get_val("SELECT term FROM school_term WHERE id = ?", [$id]) ?: 'N/A';
}
?>
<!DOCTYPE html>
<html>

<head>
    <?php include('inc.meta.php'); ?>
    <style>
        * {
            box-sizing: border-box;
        }

        .student-container {
            display: flex;
            gap: 25px;
            padding: 20px;
            min-height: calc(100vh - 120px);
        }

        .student-sidebar {
            width: 32%;
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        .student-main {
            width: 68%;
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
            overflow: hidden;
        }

        .sidebar-header {
            padding: 20px;
            border-bottom: 1px solid #eee;
            background: #f8f9fa;
        }

        .sidebar-search {
            width: 100%;
            padding: 12px 16px;
            border: 1px solid #e0e0e0;
            border-radius: 30px;
            font-size: 14px;
            margin-bottom: 15px;
        }

        .filter-select {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            margin-bottom: 10px;
            font-size: 13px;
        }

        .student-jump-wrap {
            margin-bottom: 12px;
        }

        .student-jump-input,
        .student-jump-select {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            font-size: 13px;
            margin-bottom: 8px;
        }

        .student-jump-input:focus,
        .student-jump-select:focus {
            outline: none;
            border-color: #1B3058;
            box-shadow: 0 0 0 3px rgba(27, 48, 88, 0.1);
        }

        .student-list {
            flex: 1;
            overflow-y: auto;
            padding: 10px;
            max-height: calc(100vh - 280px);
        }

        .student-card {
            display: flex;
            align-items: center;
            padding: 15px;
            margin-bottom: 10px;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.2s;
            background: #fff;
            border: 1px solid #f0f0f0;
        }

        .student-card:hover {
            background: #f8f9ff;
            border-color: #1B3058;
            transform: translateX(3px);
        }

        .student-card.active {
            background: #1B3058;
            border-color: #1B3058;
        }

        .student-card.active .student-name,
        .student-card.active .student-details {
            color: white;
        }

        .student-avatar {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            background: #e8eef5;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 14px;
            font-weight: bold;
            color: #1B3058;
            overflow: hidden;
            flex-shrink: 0;
        }

        .student-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .student-info {
            flex: 1;
        }

        .student-name {
            font-weight: 600;
            font-size: 15px;
            color: #1a2a3a;
            margin-bottom: 4px;
        }

        .student-details {
            font-size: 12px;
            color: #6c757d;
        }

        .student-id {
            font-size: 11px;
            color: #1B3058;
            font-weight: 500;
            margin-top: 3px;
        }

        .main-header {
            padding: 20px 25px;
            border-bottom: 1px solid #eee;
            background: #f8f9fa;
        }

        .main-header h2 {
            margin: 0 0 5px;
            font-size: 22px;
            color: #1B3058;
        }

        .main-content {
            padding: 25px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }

        .form-group {
            margin-bottom: 5px;
        }

        .form-group label {
            display: block;
            font-weight: 500;
            margin-bottom: 6px;
            color: #333;
            font-size: 13px;
        }

        .form-control,
        .form-select {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.2s;
        }

        .form-control:focus,
        .form-select:focus {
            outline: none;
            border-color: #1B3058;
            box-shadow: 0 0 0 3px rgba(27, 48, 88, 0.1);
        }

        .btn {
            padding: 10px 18px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.2s;
        }

        .btn-primary {
            background: #1B3058;
            color: white;
        }

        .btn-primary:hover {
            background: #f21151;
        }

        .btn-success {
            background: #28a745;
            color: white;
        }

        .btn-success:hover {
            background: #1e7e34;
        }

        .btn-danger {
            background: #dc3545;
            color: white;
        }

        .btn-outline {
            background: transparent;
            border: 1px solid #ddd;
        }

        .action-buttons {
            display: flex;
            gap: 12px;
            margin-top: 25px;
            padding-top: 20px;
            border-top: 1px solid #eee;
            flex-wrap: wrap;
        }

        .profile-preview {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
            margin-top: 10px;
            border: 2px solid #1B3058;
        }

        .alert {
            padding: 12px 16px;
            border-radius: 10px;
            margin-bottom: 20px;
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

        .btn-group {
            display: flex;
            gap: 10px;
            margin-bottom: 15px;
            flex-wrap: wrap;
        }

        .btn-group .btn {
            flex: 1;
            min-width: 100px;
        }

        .sibling-box {
            background: #f0f7ff;
            border: 1px solid #c5d5ea;
            border-radius: 12px;
            padding: 15px;
            margin-bottom: 20px;
        }

        .sibling-box label {
            font-weight: 600;
            color: #1B3058;
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }

        .modal-content {
            background: white;
            border-radius: 24px;
            max-width: 450px;
            width: 90%;
            padding: 25px;
            text-align: center;
        }

        .modal-password {
            background: #f0f7ff;
            padding: 20px;
            border-radius: 16px;
            font-family: monospace;
            font-size: 22px;
            font-weight: bold;
            letter-spacing: 2px;
            margin: 20px 0;
        }

        /* Assign Parent Box */
        .assign-parent-box {
            background: #d9ffcd;
            border: 2px solid #07ff7b;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
            position: relative;
        }

        .assign-parent-box .badge-parent {
            position: absolute;
            top: -10px;
            right: 15px;
            background: #07ff3d;
            color: #000;
            padding: 2px 15px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
        }

        .assign-parent-box select {
            margin-top: 8px;
        }

        .current-parent-info {
            background: #e8f4f8;
            padding: 10px 15px;
            border-radius: 8px;
            margin: 10px 0;
            border-left: 4px solid #1B3058;
        }

        .parent-assign-row {
            display: flex;
            gap: 15px;
            align-items: flex-end;
            flex-wrap: wrap;
        }

        .parent-assign-row .form-group {
            flex: 1;
            min-width: 200px;
        }

        .inline-select-search {
            width: 100%;
            padding: 9px 12px;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            font-size: 13px;
            margin: 8px 0;
        }

        .inline-select-search:focus {
            outline: none;
            border-color: #1B3058;
            box-shadow: 0 0 0 3px rgba(27, 48, 88, 0.1);
        }

        @media (max-width: 900px) {
            .student-container {
                flex-direction: column;
            }

            .student-sidebar,
            .student-main {
                width: 100%;
            }

            .form-grid {
                grid-template-columns: 1fr;
            }

            .parent-assign-row {
                flex-direction: column;
            }
        }
    </style>
</head>

<body>
    <div id="wrapper">
        <?php include('inc.header.php'); ?>
        <?php include('inc.sideleft.php'); ?>
        <div class="content-page">
            <div class="content">
                <div class="student-container">

                    <!-- LEFT SIDEBAR -->
                    <div class="student-sidebar">
                        <div class="sidebar-header">
                            <h4 style="margin:0 0 10px">Students</h4>
                            <input type="text" id="searchStudent" class="sidebar-search" placeholder="🔍 Search by name or ID...">
                            <div class="student-jump-wrap">
                                <input type="text" id="studentDropdownSearch" class="student-jump-input" placeholder="Filter dropdown by name or ID...">
                                <select id="studentQuickSelect" class="student-jump-select">
                                    <option value="">Jump to a student...</option>
                                    <?php foreach ($students as $student): ?>
                                        <option value="<?= htmlspecialchars($student['randomid']) ?>" data-student-id="<?= htmlspecialchars($student['student_id']) ?>">
                                            <?= htmlspecialchars($student['first_name'] . ' ' . $student['last_name']) ?> (ID: <?= htmlspecialchars($student['student_id']) ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <form method="GET" action="" style="margin:0">
                                <select name="session" class="filter-select" onchange="this.form.submit()">
                                    <option value="">All Sessions</option>
                                    <?php foreach ($sessions as $s): ?>
                                        <option value="<?= $s['id'] ?>" <?= ($search_session == $s['id']) ? 'selected' : '' ?>><?= htmlspecialchars($s['session']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <select name="term_id" class="filter-select" onchange="this.form.submit()">
                                    <option value="">All Terms</option>
                                    <?php foreach ($terms as $t): ?>
                                        <option value="<?= $t['id'] ?>" <?= ($search_term == $t['id']) ? 'selected' : '' ?>><?= htmlspecialchars($t['term']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <input type="hidden" name="randomid" value="<?= htmlspecialchars($randomid) ?>">
                            </form>
                            <div class="btn-group">
                                <a href="?action=add" class="btn btn-primary" style="flex:1"><i class="fa fa-plus"></i> Add Student</a>
                                <a href="?export=csv&session=<?= urlencode($search_session) ?>&term_id=<?= urlencode($search_term) ?>" class="btn btn-primary" style="background:#28a745" title="Download as CSV"><i class="fa fa-download"></i> CSV</a>
                            </div>
                        </div>
                        <div class="student-list" id="studentList">
                            <?php if (!empty($students)): ?>
                                <?php foreach ($students as $student): ?>
                                    <div class="student-card <?= ($student['randomid'] == $randomid) ? 'active' : '' ?>" data-student-id="<?= $student['randomid'] ?>" onclick="window.location.href='?randomid=<?= $student['randomid'] ?>'">
                                        <div class="student-avatar">
                                            <?php if (!empty($student['picture'])): ?>
                                                <img src="../uploads/<?= htmlspecialchars($student['picture']) ?>">
                                            <?php else: ?>
                                                <i class="fa fa-user" style="font-size:24px"></i>
                                            <?php endif; ?>
                                        </div>
                                        <div class="student-info">
                                            <div class="student-name"><?= htmlspecialchars($student['first_name'] . ' ' . $student['last_name']) ?></div>
                                            <div class="student-details"><?= htmlspecialchars(getClassName($student['class'])) ?> | <?= htmlspecialchars(getTermName($student['term_id'])) ?></div>
                                            <div class="student-id">ID: <?= htmlspecialchars($student['student_id']) ?> | Parent: <?= htmlspecialchars($student['parent_id'] ?? 'None') ?></div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="empty-state" style="text-align: center; padding: 40px; color: #999;">No students found.<br>Click "Add Student" to create one.</div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- RIGHT MAIN PANEL -->
                    <div class="student-main">
                        <?php if ($action == 'add' || empty($randomid)): ?>
                            <!-- ADD STUDENT FORM -->
                            <div class="main-header">
                                <h2><i class="fa fa-user-plus"></i> Add New Student</h2>
                                <p>Fill in the student information below. Parent account will be auto-created or linked.</p>
                            </div>
                            <div class="main-content">
                                <?= showMessage($stat) ?>

                                <!-- Sibling Pairing Section (Add Mode) -->
                                <?php if (!empty($allParentOptions)): ?>
                                    <div class="sibling-box">
                                        <label><i class="fa fa-users"></i> Link to existing parent? (Optional)</label>
                                        <select name="existing_parent_id" id="existing_parent_id" class="form-select" style="margin-top: 8px;">
                                            <option value="">-- Create NEW parent account --</option>
                                            <?php foreach ($allParentOptions as $parentId => $parentName): ?>
                                                <option value="<?= htmlspecialchars($parentId) ?>">
                                                    <?= htmlspecialchars($parentName) ?> (Parent ID: <?= htmlspecialchars($parentId) ?>)
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <small style="display: block; margin-top: 8px;">Select an existing parent to link this student as a sibling. Leave blank to create a new parent account.</small>
                                    </div>
                                <?php endif; ?>

                                <form method="post" enctype="multipart/form-data" id="studentForm">
                                    <div class="form-grid">
                                        <div class="form-group"><label>First Name *</label><input type="text" name="first_name" class="form-control" style="text-transform: uppercase;" oninput="this.value=this.value.toUpperCase()" required></div>
                                        <div class="form-group"><label>Last Name *</label><input type="text" name="last_name" class="form-control" style="text-transform: uppercase;" oninput="this.value=this.value.toUpperCase()" required></div>
                                        <div class="form-group"><label>Gender *</label><select name="gender" class="form-select" required>
                                                <option value="">Select</option>
                                                <option value="Male">Male</option>
                                                <option value="Female">Female</option>
                                            </select></div>
                                        <div class="form-group"><label>Date of Birth</label><input type="date" name="date_of_birth" class="form-control"></div>
                                        <div class="form-group"><label>Session of Admission *</label><select name="session" class="form-select" required><?php foreach ($sessions as $s): ?><option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['session']) ?></option><?php endforeach; ?></select></div>
                                        <div class="form-group"><label>Term *</label><select name="term_id" class="form-select" required><?php foreach ($terms as $t): ?><option value="<?= $t['id'] ?>"><?= htmlspecialchars($t['term']) ?></option><?php endforeach; ?></select></div>
                                        <div class="form-group"><label>Class *</label><select name="class" class="form-select" required><?php foreach ($classes as $c): ?><option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option><?php endforeach; ?></select></div>
                                        <div class="form-group"><label>Date of Admission</label><input type="date" name="date_of_admission" class="form-control" value="<?= date('Y-m-d') ?>"></div>
                                        <div class="form-group"><label>Parent Title</label>
                                            <select name="parent_title" class="form-select">
                                                <option value="">Select Title</option>
                                                <option value="Mr.">Mr.</option>
                                                <option value="Mrs.">Mrs.</option>
                                                <option value="Miss.">Miss.</option>
                                                <option value="Dr.">Dr.</option>
                                                <option value="Prof.">Prof.</option>
                                                <option value="Alh.">Alh.</option>
                                                <option value="Hajia.">Hajia.</option>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label>Parent First Name <small>(leave blank to use student's name)</small></label>
                                            <input type="text" name="parent_first_name" class="form-control" style="text-transform: uppercase;" oninput="this.value=this.value.toUpperCase()" placeholder="Parent's first name">
                                        </div>
                                        <div class="form-group">
                                            <label>Parent Last Name <small>(leave blank to use student's name)</small></label>
                                            <input type="text" name="parent_last_name" class="form-control" style="text-transform: uppercase;" oninput="this.value=this.value.toUpperCase()" placeholder="Parent's last name">
                                        </div>
                                        <div class="form-group"><label>Email (Parent)</label><input type="email" name="email" class="form-control" placeholder="parent@example.com"></div>
                                        <div class="form-group"><label>Phone (Parent)</label><input type="text" name="phone" class="form-control" placeholder="Phone number"></div>
                                        <div class="form-group"><label>Profile Picture</label><input type="file" name="picture" class="form-control" accept="image/*"></div>
                                    </div>
                                    <div class="action-buttons"><button type="submit" name="add_student" class="btn btn-primary"><i class="fa fa-save"></i> Save Student</button><a href="<?= $FileName ?>" class="btn btn-outline">Cancel</a></div>
                                </form>
                            </div>
                        <?php elseif ($editStudent): ?>
                            <!-- EDIT STUDENT FORM (WITH PARENT LINKING) -->
                            <div class="main-header">
                                <h2><i class="fa fa-user"></i> <?= htmlspecialchars($editStudent['first_name'] . ' ' . $editStudent['last_name']) ?></h2>
                                <p>ID: <?= htmlspecialchars($editStudent['student_id']) ?></p>
                            </div>
                            <div class="main-content">
                                <?= showMessage($stat) ?>

                                <!-- ============================================= -->
                                <!-- ASSIGN PARENT FROM EXISTING STUDENT -->
                                <!-- ============================================= -->
                                <div class="assign-parent-box">
                                    <span class="badge-parent">🔗 Assign Parent</span>
                                    <h4 style="margin-top:0;"><i class="fa fa-link"></i> Copy Parent ID from another student</h4>

                                    <div class="current-parent-info">
                                        <strong>Current Parent ID:</strong>
                                        <?php if (!empty($editStudent['parent_id'])): ?>
                                            <span style="color:#1B3058; font-weight:bold;"><?= htmlspecialchars($editStudent['parent_id']) ?></span>
                                            <span style="display:block; font-size:12px; color:#666; margin-top:4px;">
                                                <i class="fa fa-info-circle"></i> This student is already linked to a parent.
                                            </span>
                                        <?php else: ?>
                                            <span style="color:#dc3545; font-weight:bold;">No parent assigned yet</span>
                                        <?php endif; ?>
                                    </div>

                                    <form method="post" onsubmit="return confirm('This will assign the parent ID from the selected student to the current student. Continue?')">
                                        <input type="hidden" name="target_student_id" value="<?= htmlspecialchars($editStudent['student_id']) ?>">
                                        <div class="parent-assign-row">
                                            <div class="form-group" style="flex:2;">
                                                <label style="font-weight:600;">Select student to copy Parent ID from:</label>
                                                <input type="text" id="sourceStudentSearch" class="inline-select-search" placeholder="Type name, student ID, or parent ID to filter...">
                                                <select name="source_student_id" id="source_student_id_select" class="form-select" required>
                                                    <option value="">-- Select a student --</option>
                                                    <?php foreach ($allStudentsForParentAssign as $s): ?>
                                                        <?php if ($s['student_id'] !== $editStudent['student_id']): ?>
                                                            <option value="<?= htmlspecialchars($s['student_id']) ?>">
                                                                <?= htmlspecialchars($s['first_name'] . ' ' . $s['last_name']) ?>
                                                                (ID: <?= htmlspecialchars($s['student_id']) ?>)
                                                                <?php if (!empty($s['parent_id'])): ?>
                                                                    - Parent: <?= htmlspecialchars($s['parent_id']) ?>
                                                                <?php else: ?>
                                                                    - No Parent
                                                                <?php endif; ?>
                                                            </option>
                                                        <?php endif; ?>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="form-group" style="flex:0 0 auto;">
                                                <button type="submit" name="assign_parent" class="btn btn-success" style="white-space:nowrap;">
                                                    <i class="fa fa-copy"></i> Assign Parent
                                                </button>
                                            </div>
                                        </div>
                                        <small style="display:block; margin-top:8px; color:#666;">
                                            <i class="fa fa-lightbulb-o"></i>
                                            Select a student that already has a parent ID. The current student will inherit the same parent ID.
                                            If the selected student has no parent ID, one will be automatically created for them first.
                                        </small>
                                    </form>
                                </div>

                                <!-- Sibling Pairing Section (Edit Mode) -->
                                <?php if (!empty($allParentOptions)): ?>
                                    <div class="sibling-box">
                                        <label><i class="fa fa-users"></i> Change Parent / Link to Sibling (Optional)</label>
                                        <select name="existing_parent_id" id="existing_parent_id" class="form-select" style="margin-top: 8px;">
                                            <option value="">-- Keep current parent --</option>
                                            <?php foreach ($allParentOptions as $parentId => $parentName): ?>
                                                <option value="<?= htmlspecialchars($parentId) ?>" <?= ($editStudent['parent_id'] == $parentId) ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($parentName) ?> (Parent ID: <?= htmlspecialchars($parentId) ?>)
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <small style="display: block; margin-top: 8px;">Select a parent to link this student as a sibling. If the parent doesn't exist, a new parent account will be auto-created.</small>
                                    </div>

                                    <div class="form-group">
                                        <label>Parent Title (for NEW parent only)</label>
                                        <select name="parent_title" class="form-select">
                                            <option value="">Select Title</option>
                                            <option value="Mr.">Mr.</option>
                                            <option value="Mrs.">Mrs.</option>
                                            <option value="Miss.">Miss.</option>
                                            <option value="Dr.">Dr.</option>
                                            <option value="Prof.">Prof.</option>
                                            <option value="Alh.">Alh.</option>
                                            <option value="Hajia.">Hajia.</option>
                                        </select>
                                        <small>Only used if creating a NEW parent account</small>
                                    </div>
                                    <div class="form-group">
                                        <label>Parent First Name (for NEW parent only)</label>
                                        <input type="text" name="parent_first_name" class="form-control" style="text-transform: uppercase;" oninput="this.value=this.value.toUpperCase()" placeholder="Leave blank to use student's name" value="<?= htmlspecialchars($_POST['parent_first_name'] ?? '') ?>">
                                        <small>Only used if creating a NEW parent account</small>
                                    </div>
                                    <div class="form-group">
                                        <label>Parent Last Name (for NEW parent only)</label>
                                        <input type="text" name="parent_last_name" class="form-control" style="text-transform: uppercase;" oninput="this.value=this.value.toUpperCase()" placeholder="Leave blank to use student's name" value="<?= htmlspecialchars($_POST['parent_last_name'] ?? '') ?>">
                                        <small>Only used if creating a NEW parent account</small>
                                    </div>
                                <?php endif; ?>

                                <form method="post" enctype="multipart/form-data">
                                    <div class="form-grid">
                                        <div class="form-group"><label>First Name</label><input type="text" name="first_name" class="form-control" style="text-transform: uppercase;" oninput="this.value=this.value.toUpperCase()" value="<?= htmlspecialchars($editStudent['first_name']) ?>" required></div>
                                        <div class="form-group"><label>Last Name</label><input type="text" name="last_name" class="form-control" style="text-transform: uppercase;" oninput="this.value=this.value.toUpperCase()" value="<?= htmlspecialchars($editStudent['last_name']) ?>" required></div>
                                        <div class="form-group"><label>Gender</label><select name="gender" class="form-select">
                                                <option value="Male" <?= ($editStudent['gender'] == 'Male') ? 'selected' : '' ?>>Male</option>
                                                <option value="Female" <?= ($editStudent['gender'] == 'Female') ? 'selected' : '' ?>>Female</option>
                                            </select></div>
                                        <div class="form-group"><label>Date of Birth</label><input type="date" name="date_of_birth" class="form-control" value="<?= htmlspecialchars($editStudent['date_of_birth']) ?>"></div>
                                        <div class="form-group"><label>Session of Admission</label><select name="session" class="form-select"><?php foreach ($sessions as $s): ?><option value="<?= $s['id'] ?>" <?= ($editStudent['session'] == $s['id']) ? 'selected' : '' ?>><?= htmlspecialchars($s['session']) ?></option><?php endforeach; ?></select></div>
                                        <div class="form-group"><label>Term</label><select name="term_id" class="form-select"><?php foreach ($terms as $t): ?><option value="<?= $t['id'] ?>" <?= ($editStudent['term_id'] == $t['id']) ? 'selected' : '' ?>><?= htmlspecialchars($t['term']) ?></option><?php endforeach; ?></select></div>
                                        <div class="form-group"><label>Class</label><select name="class" class="form-select"><?php foreach ($classes as $c): ?><option value="<?= $c['id'] ?>" <?= ($editStudent['class'] == $c['id']) ? 'selected' : '' ?>><?= htmlspecialchars($c['name']) ?></option><?php endforeach; ?></select></div>
                                        <div class="form-group"><label>Date of Admission</label><input type="date" name="date_of_admission" class="form-control" value="<?= htmlspecialchars($editStudent['date_of_admission']) ?>"></div>
                                        <div class="form-group"><label>Email (Parent)</label><input type="email" name="email" class="form-control" value="<?= htmlspecialchars($editStudent['email']) ?>"></div>
                                        <div class="form-group"><label>Phone (Parent)</label><input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($editStudent['phone']) ?>"></div>
                                        <div class="form-group"><label>Profile Picture</label><input type="file" name="picture" class="form-control" accept="image/*"><input type="hidden" name="picture_old" value="<?= htmlspecialchars($editStudent['picture']) ?>"><?php if ($editStudent['picture']): ?><img src="../uploads/<?= htmlspecialchars($editStudent['picture']) ?>" class="profile-preview"><?php endif; ?></div>
                                    </div>
                                    <div class="action-buttons">
                                        <button type="submit" name="edit_student" class="btn btn-primary"><i class="fa fa-save"></i> Update Student</button>
                                        <a href="<?= $FileName ?>?delete=<?= $editStudent['randomid'] ?>" class="btn btn-danger" onclick="return confirm('Delete this student? All related data will be lost.')"><i class="fa fa-trash"></i> Delete</a>
                                        <a href="<?= $FileName ?>" class="btn btn-outline">Back</a>
                                    </div>
                                </form>
                            </div>
                        <?php else: ?>
                            <div class="empty-state" style="padding:80px; text-align: center;"><i class="fa fa-graduation-cap" style="font-size:64px;color:#ddd;margin-bottom:15px;display:block"></i>
                                <h3>Select a Student</h3>
                                <p>Choose a student from the left sidebar or add a new one.</p><a href="?action=add" class="btn btn-primary"><i class="fa fa-plus"></i> Add New Student</a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php include('inc.footer.php'); ?>
        </div>
    </div>

    <!-- Password Modal -->
    <div id="passwordModal" class="modal">
        <div class="modal-content">
            <h3><i class="fa fa-key"></i> Parent Credentials</h3>
            <div id="modalPasswordDisplay" class="modal-password"></div>
            <p>Please copy this password and share it securely with the parent.</p>
            <button class="btn btn-primary" onclick="copyModalPassword()"><i class="fa fa-copy"></i> Copy Password</button>
            <button class="btn btn-outline" onclick="closeModal()" style="margin-top: 10px;">Close</button>
        </div>
    </div>

    <?php include('inc.js.php'); ?>
    <script>
        const studentQuickSelect = document.getElementById('studentQuickSelect');
        const studentDropdownSearch = document.getElementById('studentDropdownSearch');

        function buildStudentUrl(randomId) {
            const params = new URLSearchParams();
            params.set('randomid', randomId);

            const currentSession = <?= json_encode((string)$search_session) ?>;
            const currentTerm = <?= json_encode((string)$search_term) ?>;

            if (currentSession !== '') params.set('session', currentSession);
            if (currentTerm !== '') params.set('term_id', currentTerm);

            return '?' + params.toString();
        }

        function filterStudentDropdownOptions() {
            if (!studentQuickSelect || !studentDropdownSearch) return;

            const term = studentDropdownSearch.value.toLowerCase().trim();
            Array.from(studentQuickSelect.options).forEach((opt, index) => {
                if (index === 0) {
                    opt.hidden = false;
                    return;
                }

                const searchBlob = (opt.textContent + ' ' + (opt.dataset.studentId || '')).toLowerCase();
                opt.hidden = term !== '' && !searchBlob.includes(term);
            });
        }

        studentDropdownSearch?.addEventListener('input', filterStudentDropdownOptions);

        studentQuickSelect?.addEventListener('change', function() {
            if (!this.value) return;
            window.location.href = buildStudentUrl(this.value);
        });

        const sourceStudentSearch = document.getElementById('sourceStudentSearch');
        const sourceStudentSelect = document.getElementById('source_student_id_select');
        const sourceStudentInitialOptions = sourceStudentSelect
            ? Array.from(sourceStudentSelect.options).slice(1).map(opt => ({
                value: opt.value,
                text: opt.textContent || ''
            }))
            : [];

        function filterSourceStudentOptions() {
            if (!sourceStudentSearch || !sourceStudentSelect) return;

            const term = sourceStudentSearch.value.toLowerCase().trim();

            const matches = term === ''
                ? sourceStudentInitialOptions
                : sourceStudentInitialOptions.filter(item => item.text.toLowerCase().includes(term));

            sourceStudentSelect.innerHTML = '';

            const placeholder = document.createElement('option');
            placeholder.value = '';
            placeholder.textContent = matches.length > 0
                ? '-- Select a student --'
                : '-- No matching student found --';
            sourceStudentSelect.appendChild(placeholder);

            matches.forEach(item => {
                const opt = document.createElement('option');
                opt.value = item.value;
                opt.textContent = item.text;
                sourceStudentSelect.appendChild(opt);
            });

            // Jump to first match automatically so assignment is faster.
            if (matches.length > 0 && term !== '') {
                sourceStudentSelect.selectedIndex = 1;
            }
        }

        sourceStudentSearch?.addEventListener('input', filterSourceStudentOptions);

        document.getElementById('searchStudent')?.addEventListener('keyup', function() {
            let searchTerm = this.value.toLowerCase();
            document.querySelectorAll('.student-card').forEach(card => {
                let text = card.innerText.toLowerCase();
                card.style.display = text.includes(searchTerm) ? 'flex' : 'none';
            });
        });
        document.querySelectorAll('.student-card').forEach(card => {
            if (card.getAttribute('data-student-id') === '<?= $randomid ?>') card.classList.add('active');
        });

        // Password modal
        <?php if (!empty($tempPassword)): ?>
            document.addEventListener('DOMContentLoaded', function() {
                document.getElementById('modalPasswordDisplay').innerHTML = '<?= addslashes($tempPassword) ?>';
                document.getElementById('passwordModal').style.display = 'flex';
            });
        <?php endif; ?>

        function closeModal() {
            document.getElementById('passwordModal').style.display = 'none';
        }

        function copyModalPassword() {
            const password = document.getElementById('modalPasswordDisplay').innerText;
            navigator.clipboard.writeText(password);
            alert('Password copied to clipboard!');
        }
    </script>
</body>

</html>