<?php
/**
 * ============================================================================
 * MANAGE SUBJECT TEACHER - TEACHER-FIRST WORKFLOW WITH PAGINATION
 * ============================================================================
 * - Select teacher first, then assign subjects with one click
 * - Search subjects by name + class
 * - Visual indicators (Green = Assigned)
 * - Pagination for large subject lists (20 per page)
 * ============================================================================
 */

include('../config.php');
include('inc.session-create.php');

// ============================================================================
// 1. PAGE CONFIGURATION
// ============================================================================
$PageTitle = "Manage Subject Teacher";
$FileName = 'manage_subject_teacher.php';

$stat = [];
$create_by_userid = $_SESSION['userid'] ?? 0;
$create_by_usertype = $_SESSION['usertype'] ?? '';
$action = $_GET['action'] ?? '';
$selectedTeacherId = $_GET['teacher_id'] ?? 0;
$searchSubject = $_GET['search'] ?? '';
$filterCategory = $_GET['category'] ?? '';
$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($currentPage < 1) $currentPage = 1;

if (!empty($_SESSION['success'])) {
    $stat['success'] = $_SESSION['success'];
    unset($_SESSION['success']);
}

if (!empty($_SESSION['error'])) {
    $stat['error'] = $_SESSION['error'];
    unset($_SESSION['error']);
}

// ============================================================================
// 2. CSRF PROTECTION
// ============================================================================
if (!function_exists('generateCSRFToken')) {
    function generateCSRFToken() {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
}

if (!function_exists('verifyCSRFToken')) {
    function verifyCSRFToken($token) {
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }
}

$csrfToken = generateCSRFToken();

// ============================================================================
// 3. PAGINATION HELPERS
// ============================================================================
function getPaginatedSubjects($page, $perPage = 20) {
    global $create_by_userid, $searchSubject, $filterCategory;
    
    $offset = ($page - 1) * $perPage;
    
    $searchSQL = "WHERE ss.create_by_userid = ?";
    $params = [$create_by_userid];
    
    if (!empty($searchSubject)) {
        $searchSQL .= " AND (ss.subject LIKE ? OR sc.name LIKE ?)";
        $params[] = "%$searchSubject%";
        $params[] = "%$searchSubject%";
    }
    
    if (!empty($filterCategory)) {
        $searchSQL .= " AND ss.subject LIKE ?";
        $params[] = "%$filterCategory%";
    }
    
    // Get total count
    $countSQL = "SELECT COUNT(*) as total FROM school_subject ss 
                 LEFT JOIN school_class sc ON ss.class_id = sc.id 
                 $searchSQL";
    $totalResult = db_get_row($countSQL, $params);
    $total = $totalResult['total'] ?? 0;
    
    // Get paginated results
    $dataSQL = "SELECT ss.id, ss.subject, ss.class_id, sc.name as class_name 
                FROM school_subject ss
                LEFT JOIN school_class sc ON ss.class_id = sc.id
                $searchSQL
                ORDER BY sc.name ASC, ss.subject ASC
                LIMIT ? OFFSET ?";
    
    $params[] = $perPage;
    $params[] = $offset;
    
    $data = db_get_rows($dataSQL, $params);
    
    return [
        'data' => $data,
        'total' => $total,
        'page' => $page,
        'perPage' => $perPage,
        'totalPages' => ceil($total / $perPage)
    ];
}

function renderPagination($page, $totalPages, $baseUrl) {
    if ($totalPages <= 1) return '';
    
    $html = '<div style="display: flex; gap: 8px; justify-content: center; margin-top: 20px; flex-wrap: wrap;">';
    
    // Previous button
    if ($page > 1) {
        $html .= '<a href="' . $baseUrl . '&page=' . ($page - 1) . '" class="btn btn-outline btn-sm">‹ Prev</a>';
    }
    
    // Page numbers
    $start = max(1, $page - 2);
    $end = min($totalPages, $page + 2);
    
    if ($start > 1) {
        $html .= '<a href="' . $baseUrl . '&page=1" class="btn btn-outline btn-sm">1</a>';
        if ($start > 2) $html .= '<span style="padding: 6px 10px;">...</span>';
    }
    
    for ($i = $start; $i <= $end; $i++) {
        $class = ($i == $page) ? 'btn btn-primary btn-sm' : 'btn btn-outline btn-sm';
        $html .= '<a href="' . $baseUrl . '&page=' . $i . '" class="' . $class . '">' . $i . '</a>';
    }
    
    if ($end < $totalPages) {
        if ($end < $totalPages - 1) $html .= '<span style="padding: 6px 10px;">...</span>';
        $html .= '<a href="' . $baseUrl . '&page=' . $totalPages . '" class="btn btn-outline btn-sm">' . $totalPages . '</a>';
    }
    
    // Next button
    if ($page < $totalPages) {
        $html .= '<a href="' . $baseUrl . '&page=' . ($page + 1) . '" class="btn btn-outline btn-sm">Next ›</a>';
    }
    
    $html .= '</div>';
    $html .= '<div style="text-align: center; font-size: 12px; color: #888; margin-top: 8px;">';
    $html .= 'Showing ' . (($page - 1) * 20 + 1) . ' - ' . min($page * 20, $total) . ' of ' . $total . ' subjects';
    $html .= '</div>';
    
    return $html;
}

// ============================================================================
// 4. BULK ASSIGN SUBJECTS TO TEACHER
// ============================================================================
if (isset($_POST['assign_subjects']) && !empty($selectedTeacherId)) {
    $subjectIds = isset($_POST['subject_ids']) ? explode(',', $_POST['subject_ids']) : [];
    $assignedCount = 0;
    $alreadyAssigned = [];

    if (empty($subjectIds)) {
        $stat['error'] = "Please select at least one subject to assign.";
    } else {
        foreach ($subjectIds as $subjectId) {
            $subjectId = (int)$subjectId;

            $subjectDetails = db_get_row(
                "SELECT ss.id, ss.subject, ss.class_id, sc.name as class_name 
                 FROM school_subject ss
                 LEFT JOIN school_class sc ON ss.class_id = sc.id
                 WHERE ss.id = ? AND ss.create_by_userid = ?",
                [$subjectId, $create_by_userid]
            );

            if (empty($subjectDetails)) {
                continue;
            }

            $existing = db_get_val(
                "SELECT id FROM subject_teacher 
                 WHERE staff_id = ? AND school_subject = ? AND create_by_userid = ?",
                [$selectedTeacherId, $subjectId, $create_by_userid]
            );

            if ($existing) {
                $alreadyAssigned[] = $subjectDetails['subject'] . ' (' . ($subjectDetails['class_name'] ?? 'N/A') . ')';
                continue;
            }

            $insertData = [
                'staff_id' => $selectedTeacherId,
                'school_session' => 0,
                'school_class' => $subjectDetails['class_id'],
                'school_subject' => $subjectId,
                'create_by_userid' => $create_by_userid,
                'create_by_usertype' => $create_by_usertype
            ];

            if (db_insert('subject_teacher', $insertData)) {
                $assignedCount++;
            }
        }

        if ($assignedCount > 0) {
            $stat['success'] = "$assignedCount subject(s) assigned successfully!";
            if (!empty($alreadyAssigned)) {
                $stat['warning'] = "Already assigned: " . implode(", ", $alreadyAssigned);
            }
        } elseif (!empty($alreadyAssigned)) {
            $stat['error'] = "All selected subjects are already assigned to this teacher.";
        } else {
            $stat['error'] = "No subjects were assigned. Please try again.";
        }
    }

    $redirectUrl = $FileName . "?teacher_id=" . $selectedTeacherId . "&search=" . urlencode($searchSubject) . "&category=" . urlencode($filterCategory) . "&page=" . $currentPage;
    redirect($redirectUrl);
    exit;
}

// ============================================================================
// 5. TOGGLE SINGLE SUBJECT ASSIGNMENT
// ============================================================================
if (isset($_GET['toggle_subject']) && !empty($selectedTeacherId) && !empty($_GET['subject_id'])) {
    $subjectId = (int)$_GET['subject_id'];
    $teacherId = (int)$selectedTeacherId;

    $existing = db_get_val(
        "SELECT id FROM subject_teacher 
         WHERE staff_id = ? AND school_subject = ? AND create_by_userid = ?",
        [$teacherId, $subjectId, $create_by_userid]
    );

    if ($existing) {
        db_delete("subject_teacher", "id = ?", [$existing]);
        $_SESSION['success'] = "Subject unassigned successfully.";
    } else {
        $subjectDetails = db_get_row(
            "SELECT ss.id, ss.subject, ss.class_id, sc.name as class_name 
             FROM school_subject ss
             LEFT JOIN school_class sc ON ss.class_id = sc.id
             WHERE ss.id = ? AND ss.create_by_userid = ?",
            [$subjectId, $create_by_userid]
        );

        if ($subjectDetails) {
            $insertData = [
                'staff_id' => $teacherId,
                'school_session' => 0,
                'school_class' => $subjectDetails['class_id'],
                'school_subject' => $subjectId,
                'create_by_userid' => $create_by_userid,
                'create_by_usertype' => $create_by_usertype
            ];

            if (db_insert('subject_teacher', $insertData)) {
                $_SESSION['success'] = "Subject assigned successfully.";
            } else {
                $_SESSION['error'] = "Failed to assign subject.";
            }
        }
    }

    $redirectUrl = $FileName . "?teacher_id=" . $teacherId . "&search=" . urlencode($searchSubject) . "&category=" . urlencode($filterCategory) . "&page=" . $currentPage;
    redirect($redirectUrl);
    exit;
}

// ============================================================================
// 6. BULK REMOVE ASSIGNED SUBJECTS
// ============================================================================
if (isset($_POST['remove_assigned']) && !empty($selectedTeacherId)) {
    $removeIds = isset($_POST['remove_ids']) ? explode(',', $_POST['remove_ids']) : [];
    $removedCount = 0;

    if (empty($removeIds)) {
        $stat['error'] = "Please select at least one subject to remove.";
    } else {
        foreach ($removeIds as $assignmentId) {
            $assignmentId = (int)$assignmentId;
            $deleted = db_delete("subject_teacher", "id = ? AND staff_id = ? AND create_by_userid = ?", [$assignmentId, $selectedTeacherId, $create_by_userid]);
            if ($deleted !== false) {
                $removedCount++;
            }
        }

        if ($removedCount > 0) {
            $_SESSION['success'] = "$removedCount subject(s) removed successfully.";
        } else {
            $_SESSION['error'] = "No subjects were removed.";
        }
    }

    $redirectUrl = $FileName . "?teacher_id=" . $selectedTeacherId . "&search=" . urlencode($searchSubject) . "&category=" . urlencode($filterCategory) . "&page=" . $currentPage;
    redirect($redirectUrl);
    exit;
}

// ============================================================================
// 7. GET DATA FOR DISPLAY
// ============================================================================

// Get all staff members for dropdown
$staffMembers = db_get_rows(
    "SELECT id, staff_id, first_name, last_name 
     FROM staff_manage 
     WHERE create_by_userid = ? 
     ORDER BY first_name ASC",
    [$create_by_userid]
);

// Get selected teacher details
$selectedTeacher = null;
if (!empty($selectedTeacherId)) {
    $selectedTeacher = db_get_row(
        "SELECT * FROM staff_manage WHERE id = ? AND create_by_userid = ?",
        [$selectedTeacherId, $create_by_userid]
    );
}

// Get paginated subjects
$paginatedResult = getPaginatedSubjects($currentPage, 20);
$allSubjects = $paginatedResult['data'];
$totalSubjects = $paginatedResult['total'];
$totalPages = $paginatedResult['totalPages'];

// Build base URL for pagination links
$baseUrl = $FileName . "?teacher_id=" . $selectedTeacherId;
if (!empty($searchSubject)) {
    $baseUrl .= "&search=" . urlencode($searchSubject);
}
if (!empty($filterCategory)) {
    $baseUrl .= "&category=" . urlencode($filterCategory);
}

// Get subjects already assigned to this teacher
$assignedSubjectIds = [];
$assignedAssignments = [];
if (!empty($selectedTeacherId)) {
    $assignedData = db_get_rows(
        "SELECT st.id as assignment_id, st.school_subject as subject_id, ss.subject, sc.name as class_name
         FROM subject_teacher st
         LEFT JOIN school_subject ss ON st.school_subject = ss.id
         LEFT JOIN school_class sc ON ss.class_id = sc.id
         WHERE st.staff_id = ? AND st.create_by_userid = ?
         ORDER BY sc.name ASC, ss.subject ASC",
        [$selectedTeacherId, $create_by_userid]
    );
    $assignedSubjectIds = array_column($assignedData, 'subject_id');
    $assignedAssignments = $assignedData;
}

// Get unique categories for filter
$allCategories = db_get_rows(
    "SELECT DISTINCT subject FROM school_subject WHERE create_by_userid = ? ORDER BY subject ASC",
    [$create_by_userid]
);
$categoryList = [];
foreach ($allCategories as $cat) {
    $categoryList[] = $cat['subject'];
}
$categoryList = array_unique($categoryList);
sort($categoryList);

// Helper function to check if subject is assigned
function isAssigned($subjectId, $assignedSubjectIds) {
    return in_array($subjectId, $assignedSubjectIds);
}

// Helper function to get assignment ID
function getAssignmentId($subjectId, $assignedAssignments) {
    foreach ($assignedAssignments as $assigned) {
        if ($assigned['subject_id'] == $subjectId) {
            return $assigned['assignment_id'];
        }
    }
    return 0;
}
?>
<!DOCTYPE html>
<html>
<head>
    <?php include('inc.meta.php'); ?>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { background: #f5f7fb; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        
        .container-modern { max-width: 1400px; margin: 0 auto; padding: 20px; }
        .page-header { margin-bottom: 30px; }
        .page-header h2 { color: #1B3058; margin: 0; font-size: 24px; }
        .page-header p { color: #666; margin-top: 5px; }
        
        /* Cards */
        .card { background: #fff; border-radius: 20px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); overflow: hidden; margin-bottom: 30px; }
        .card-header { padding: 18px 25px; }
        .card-header h3 { margin: 0; font-size: 18px; font-weight: 600; }
        .card-body { padding: 25px; }
        
        .bg-primary { background: linear-gradient(135deg, #1B3058 0%, #2a4780 100%); color: white; }
        .bg-success { background: linear-gradient(135deg, #28a745 0%, #20c997 100%); color: white; }
        .bg-danger { background: linear-gradient(135deg, #dc3545 0%, #c82333 100%); color: white; }
        .bg-secondary { background: linear-gradient(135deg, #6c757d 0%, #495057 100%); color: white; }
        
        /* Teacher Selector */
        .teacher-selector { display: flex; gap: 20px; align-items: flex-end; flex-wrap: wrap; }
        .teacher-selector .form-group { flex: 1; min-width: 250px; }
        .teacher-selector label { display: block; font-size: 12px; font-weight: 700; color: #666; margin-bottom: 5px; text-transform: uppercase; letter-spacing: 0.5px; }
        .teacher-selector select, .teacher-selector input { 
            width: 100%; padding: 12px 15px; border: 2px solid #e0e0e0; border-radius: 14px; font-size: 14px; background: white; transition: all 0.2s; 
        }
        .teacher-selector select:focus, .teacher-selector input:focus { outline: none; border-color: #1B3058; box-shadow: 0 0 0 3px rgba(27,48,88,0.1); }
        
        .btn { padding: 12px 24px; border: none; border-radius: 12px; cursor: pointer; font-weight: 600; transition: all 0.2s; display: inline-flex; align-items: center; gap: 8px; text-decoration: none; }
        .btn-primary { background: #1B3058; color: white; }
        .btn-primary:hover { background: #f21151; transform: translateY(-2px); }
        .btn-success { background: #28a745; color: white; }
        .btn-success:hover { background: #218838; transform: translateY(-2px); }
        .btn-danger { background: #dc3545; color: white; }
        .btn-danger:hover { background: #c82333; transform: translateY(-2px); }
        .btn-outline { background: transparent; border: 2px solid #ddd; color: #333; }
        .btn-outline:hover { border-color: #1B3058; color: #1B3058; }
        .btn-sm { padding: 6px 14px; font-size: 12px; border-radius: 8px; }
        
        /* Search Bar */
        .search-bar { display: flex; gap: 15px; flex-wrap: wrap; margin-bottom: 20px; }
        .search-bar input { flex: 1; min-width: 200px; padding: 10px 18px; border: 2px solid #e0e0e0; border-radius: 30px; font-size: 14px; }
        .search-bar input:focus { outline: none; border-color: #1B3058; }
        .search-bar select { padding: 10px 18px; border: 2px solid #e0e0e0; border-radius: 30px; font-size: 14px; background: white; }
        
        /* Subject Grid */
        .subject-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 12px; }
        .subject-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 14px 18px;
            border-radius: 14px;
            border: 2px solid #e0e0e0;
            transition: all 0.2s;
            background: #fafafa;
        }
        .subject-item:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,0.08); }
        .subject-item.assigned { border-color: #28a745; background: #e8f5e9; }
        .subject-item .info { display: flex; flex-direction: column; gap: 2px; }
        .subject-item .subject-name { font-weight: 600; font-size: 14px; color: #1B3058; }
        .subject-item .class-name { font-size: 12px; color: #666; }
        .subject-item .badge { font-size: 10px; padding: 2px 8px; border-radius: 20px; font-weight: 600; }
        .badge-assigned { background: #28a745; color: white; }
        .badge-unassigned { background: #e0e0e0; color: #666; }
        .subject-item .actions { display: flex; gap: 8px; align-items: center; }
        
        /* Assigned List */
        .assigned-list { display: flex; flex-direction: column; gap: 8px; }
        .assigned-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px 16px;
            border-radius: 12px;
            background: #f8f9fa;
            border-left: 4px solid #28a745;
        }
        .assigned-item .info { display: flex; flex-direction: column; gap: 2px; }
        .assigned-item .subject-name { font-weight: 600; font-size: 14px; }
        .assigned-item .class-name { font-size: 12px; color: #666; }
        
        .empty-state { text-align: center; padding: 50px; color: #999; }
        .empty-state i { font-size: 48px; color: #ccc; margin-bottom: 15px; display: block; }
        
        .alert { padding: 15px 20px; border-radius: 12px; margin-bottom: 20px; display: flex; align-items: center; gap: 10px; }
        .alert-success { background: #d4edda; color: #155724; border-left: 4px solid #28a745; }
        .alert-danger { background: #f8d7da; color: #721c24; border-left: 4px solid #dc3545; }
        .alert-warning { background: #fff3cd; color: #856404; border-left: 4px solid #ffc107; }
        .alert-info { background: #d1ecf1; color: #0c5460; border-left: 4px solid #17a2b8; }
        
        .checkbox-cell { text-align: center; }
        .checkbox-cell input[type="checkbox"] { width: 18px; height: 18px; cursor: pointer; }
        
        .badge-count { background: rgba(255,255,255,0.2); padding: 2px 10px; border-radius: 20px; font-size: 12px; }
        
        @media (max-width: 768px) {
            .subject-grid { grid-template-columns: 1fr; }
            .teacher-selector { flex-direction: column; }
            .search-bar { flex-direction: column; }
            .search-bar input { width: 100%; }
        }
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
                    <h2><i class="fa fa-chalkboard-teacher"></i> <?= $PageTitle ?></h2>
                    <p>Select a teacher, then search and assign subjects with one click</p>
                </div>

                <?= showMessage($stat) ?>

                <!-- ============================================================ -->
                <!-- STEP 1: SELECT TEACHER -->
                <!-- ============================================================ -->
                <div class="card">
                    <div class="card-header bg-primary">
                        <h3><i class="fa fa-user"></i> Step 1: Select Teacher</h3>
                    </div>
                    <div class="card-body">
                        <form method="GET" action="">
                            <div class="teacher-selector">
                                <div class="form-group">
                                    <label><i class="fa fa-users"></i> Choose Teacher <span style="color:#dc3545">*</span></label>
                                    <select name="teacher_id" class="form-control" onchange="this.form.submit()" required>
                                        <option value="">-- Select Teacher --</option>
                                        <?php foreach ($staffMembers as $staff): ?>
                                            <option value="<?= $staff['id'] ?>" <?= ($selectedTeacherId == $staff['id']) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($staff['first_name'] . ' ' . $staff['last_name']) ?> (<?= htmlspecialchars($staff['staff_id']) ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <?php if (!empty($selectedTeacher)): ?>
                                    <div style="padding: 10px 20px; background: #e8f5e9; border-radius: 14px; min-width: 150px;">
                                        <span style="font-size: 12px; color: #666;">Selected Teacher:</span>
                                        <br><strong style="color: #1B3058;"><?= htmlspecialchars($selectedTeacher['first_name'] . ' ' . $selectedTeacher['last_name']) ?></strong>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <input type="hidden" name="search" value="<?= htmlspecialchars($searchSubject) ?>">
                            <input type="hidden" name="category" value="<?= htmlspecialchars($filterCategory) ?>">
                            <input type="hidden" name="page" value="<?= $currentPage ?>">
                        </form>
                    </div>
                </div>

                <!-- ============================================================ -->
                <!-- STEP 2 & 3: ASSIGN SUBJECTS (Only if teacher selected) -->
                <!-- ============================================================ -->
                <?php if (!empty($selectedTeacher)): ?>

                    <div class="card">
                        <div class="card-header bg-success">
                            <h3>
                                <i class="fa fa-book"></i> Step 2: Assign Subjects 
                                <span class="badge-count"><?= count($assignedSubjectIds) ?> assigned</span>
                            </h3>
                        </div>
                        <div class="card-body">
                            
                            <!-- Search & Filter -->
                            <form method="GET" action="" id="searchForm">
                                <input type="hidden" name="teacher_id" value="<?= $selectedTeacherId ?>">
                                <input type="hidden" name="page" value="<?= $currentPage ?>">
                                <div class="search-bar">
                                    <input type="text" name="search" placeholder="🔍 Search by subject or class..." value="<?= htmlspecialchars($searchSubject) ?>" onchange="this.form.submit()">
                                    <select name="category" onchange="this.form.submit()">
                                        <option value="">All Categories</option>
                                        <?php foreach ($categoryList as $cat): ?>
                                            <option value="<?= htmlspecialchars($cat) ?>" <?= ($filterCategory == $cat) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($cat) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                                    <a href="<?= $FileName ?>?teacher_id=<?= $selectedTeacherId ?>" class="btn btn-outline btn-sm">Reset</a>
                                </div>
                            </form>

                            <!-- Subject Grid -->
                            <?php if (!empty($allSubjects)): ?>
                                <form method="POST" id="assignForm" onsubmit="return prepareAssignSubmit()">
                                    <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                                    <input type="hidden" name="subject_ids" id="subjectIdsHidden" value="">
                                    
                                    <div style="margin-bottom: 15px; display: flex; gap: 10px; flex-wrap: wrap; align-items: center;">
                                        <button type="button" class="btn btn-outline btn-sm" onclick="toggleAllSubjects(true)">☑ Select All</button>
                                        <button type="button" class="btn btn-outline btn-sm" onclick="toggleAllSubjects(false)">☐ Deselect All</button>
                                        <span style="font-size: 12px; color: #888;" id="selectedSubjectCount">0 selected</span>
                                        <button type="submit" name="assign_subjects" class="btn btn-success btn-sm">
                                            <i class="fa fa-check"></i> Assign Selected
                                        </button>
                                    </div>
                                    
                                    <div class="subject-grid" id="subjectGrid">
                                        <?php foreach ($allSubjects as $subject): 
                                            $assigned = isAssigned($subject['id'], $assignedSubjectIds);
                                        ?>
                                            <div class="subject-item <?= $assigned ? 'assigned' : '' ?>">
                                                <div class="info">
                                                    <div class="subject-name"><?= htmlspecialchars($subject['subject']) ?></div>
                                                    <div class="class-name">📚 <?= htmlspecialchars($subject['class_name'] ?? 'N/A') ?></div>
                                                    <span class="badge <?= $assigned ? 'badge-assigned' : 'badge-unassigned' ?>">
                                                        <?= $assigned ? '✅ Assigned' : '⬜ Available' ?>
                                                    </span>
                                                </div>
                                                <div class="actions">
                                                    <?php if ($assigned): ?>
                                                        <span style="font-size: 12px; color: #28a745;">
                                                            <i class="fa fa-check-circle"></i>
                                                        </span>
                                                        <a href="<?= $FileName ?>?teacher_id=<?= $selectedTeacherId ?>&toggle_subject=1&subject_id=<?= $subject['id'] ?>&search=<?= urlencode($searchSubject) ?>&category=<?= urlencode($filterCategory) ?>&page=<?= $currentPage ?>" 
                                                           class="btn btn-danger btn-sm" 
                                                           onclick="return confirm('Remove this subject from this teacher?')">
                                                            <i class="fa fa-times"></i> Unassign
                                                        </a>
                                                    <?php else: ?>
                                                        <input type="checkbox" name="subject_checkbox[]" value="<?= $subject['id'] ?>" class="subject-checkbox" onchange="updateSubjectCount()">
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                    
                                    <?php if ($totalPages > 1): ?>
                                        <?= renderPagination($currentPage, $totalPages, $baseUrl) ?>
                                    <?php endif; ?>
                                    
                                </form>
                            <?php else: ?>
                                <div class="empty-state">
                                    <i class="fa fa-book"></i>
                                    <p>No subjects found. Please add subjects in Configuration first.</p>
                                </div>
                            <?php endif; ?>
                            
                        </div>
                    </div>

                    <!-- ============================================================ -->
                    <!-- STEP 3: ASSIGNED SUBJECTS LIST -->
                    <!-- ============================================================ -->
                    <div class="card">
                        <div class="card-header bg-secondary">
                            <h3>
                                <i class="fa fa-list"></i> Step 3: Assigned Subjects 
                                <span class="badge-count"><?= count($assignedAssignments) ?> assigned</span>
                            </h3>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($assignedAssignments)): ?>
                                <form method="POST" id="removeForm" onsubmit="return prepareRemoveSubmit()">
                                    <input type="hidden" name="remove_ids" id="removeIdsHidden" value="">
                                    <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                                    
                                    <div style="margin-bottom: 15px; display: flex; gap: 10px; flex-wrap: wrap; align-items: center;">
                                        <button type="button" class="btn btn-outline btn-sm" onclick="toggleAllAssigned(true)">☑ Select All</button>
                                        <button type="button" class="btn btn-outline btn-sm" onclick="toggleAllAssigned(false)">☐ Deselect All</button>
                                        <span style="font-size: 12px; color: #888;" id="selectedRemoveCount">0 selected</span>
                                        <button type="submit" name="remove_assigned" class="btn btn-danger btn-sm">
                                            <i class="fa fa-trash"></i> Remove Selected
                                        </button>
                                    </div>
                                    
                                    <div class="assigned-list">
                                        <?php foreach ($assignedAssignments as $assigned): ?>
                                            <div class="assigned-item">
                                                <div class="info">
                                                    <div class="subject-name">📚 <?= htmlspecialchars($assigned['subject'] ?? 'N/A') ?></div>
                                                    <div class="class-name">Class: <?= htmlspecialchars($assigned['class_name'] ?? 'N/A') ?></div>
                                                </div>
                                                <div style="display: flex; gap: 10px; align-items: center;">
                                                    <input type="checkbox" class="assigned-checkbox" value="<?= $assigned['assignment_id'] ?>" onchange="updateRemoveCount()">
                                                    <a href="<?= $FileName ?>?teacher_id=<?= $selectedTeacherId ?>&toggle_subject=1&subject_id=<?= $assigned['subject_id'] ?>&search=<?= urlencode($searchSubject) ?>&category=<?= urlencode($filterCategory) ?>&page=<?= $currentPage ?>" 
                                                       class="btn btn-danger btn-sm" 
                                                       onclick="return confirm('Remove this subject from this teacher?')">
                                                        <i class="fa fa-times"></i> Remove
                                                    </a>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </form>
                            <?php else: ?>
                                <div class="empty-state">
                                    <i class="fa fa-list"></i>
                                    <p>No subjects assigned to this teacher yet.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <script>
                        // Update subject checkbox count
                        function updateSubjectCount() {
                            const checked = document.querySelectorAll('.subject-checkbox:checked');
                            const countSpan = document.getElementById('selectedSubjectCount');
                            if (countSpan) {
                                countSpan.innerHTML = checked.length + ' selected';
                            }
                        }

                        // Toggle all subjects
                        function toggleAllSubjects(select) {
                            const checkboxes = document.querySelectorAll('.subject-checkbox');
                            checkboxes.forEach(cb => cb.checked = select);
                            updateSubjectCount();
                        }

                        // Prepare assign submit - collect all checked subject IDs
                        function prepareAssignSubmit() {
                            const checked = document.querySelectorAll('.subject-checkbox:checked');
                            const ids = [];
                            checked.forEach(cb => ids.push(cb.value));
                            document.getElementById('subjectIdsHidden').value = ids.join(',');
                            if (ids.length === 0) {
                                alert('Please select at least one subject to assign.');
                                return false;
                            }
                            return confirm('Assign ' + ids.length + ' subject(s) to this teacher?');
                        }

                        // Update remove checkbox count
                        function updateRemoveCount() {
                            const checked = document.querySelectorAll('.assigned-checkbox:checked');
                            const countSpan = document.getElementById('selectedRemoveCount');
                            if (countSpan) {
                                countSpan.innerHTML = checked.length + ' selected';
                            }
                        }

                        // Toggle all assigned
                        function toggleAllAssigned(select) {
                            const checkboxes = document.querySelectorAll('.assigned-checkbox');
                            checkboxes.forEach(cb => cb.checked = select);
                            updateRemoveCount();
                        }

                        // Prepare remove submit
                        function prepareRemoveSubmit() {
                            const checked = document.querySelectorAll('.assigned-checkbox:checked');
                            const ids = [];
                            checked.forEach(cb => ids.push(cb.value));
                            document.getElementById('removeIdsHidden').value = ids.join(',');
                            if (ids.length === 0) {
                                alert('Please select at least one subject to remove.');
                                return false;
                            }
                            return confirm('Remove ' + ids.length + ' subject(s) from this teacher?');
                        }

                        // Initialize counts on page load
                        document.addEventListener('DOMContentLoaded', function() {
                            updateSubjectCount();
                            updateRemoveCount();
                        });
                    </script>

                <?php else: ?>
                    <!-- No teacher selected message -->
                    <div class="card">
                        <div class="card-body">
                            <div class="empty-state">
                                <i class="fa fa-user-plus"></i>
                                <p>Please select a teacher from the dropdown above to manage their subject assignments.</p>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

            </div>
        </div>
        <?php include('inc.footer.php'); ?>
    </div>
</div>

<?php include('inc.js.php'); ?>

</body>
</html>