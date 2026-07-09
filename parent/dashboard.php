<?php

/**
 * Parent Dashboard - COMPLETE WITH LOGOUT, SCHOOL LOGO, SCHOOL NAME
 * - Shows latest results with expandable view
 * - Displays ALL available sessions/terms/classes with results for each child
 * - Beautiful gradient background
 * - Logout button
 * - School logo and name
 * - Change password & edit profile
 * - Mobile responsive with smooth JS interactions
 */

require_once('../config.php');

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$PageTitle = "My Dashboard";
$FileName = 'dashboard.php';

// Redirect if not logged in - using parent_id from session
if (empty($_SESSION['parent_id'])) {
    redirect('index.php');
    exit;
}

$parentId = $_SESSION['parent_id'];
$fullname = $_SESSION['fullname'] ?? 'Parent';
$userid = $_SESSION['userid'] ?? 0;

// ============================================================================
// GET PARENT DETAILS FROM student_guardian
// ============================================================================
$parentDetails = db_get_row("SELECT * FROM student_guardian WHERE id = ?", [(int)$userid]);

if (empty($parentDetails)) {
    $parentDetails = db_get_row(
        "SELECT * FROM student_guardian WHERE type = 1 AND (parent_id = ? OR student_id_str = ?) ORDER BY status DESC, id DESC LIMIT 1",
        [$parentId, $parentId]
    );
}

// If parent not found in student_guardian, try to get from session
if (empty($parentDetails)) {
    $parentDetails = [
        'title' => $_SESSION['title'] ?? '',
        'first_name' => $_SESSION['first_name'] ?? '',
        'last_name' => $_SESSION['last_name'] ?? '',
        'phone' => $_SESSION['phone'] ?? '',
        'email' => $_SESSION['email'] ?? '',
        'parent_id' => $parentId
    ];
}

// ============================================================================
// GET SCHOOL INFO (for logo and name)
// ============================================================================
$schoolOwnerId = (int)($_SESSION['create_by_userid'] ?? 0);
if ($schoolOwnerId <= 0) {
    $schoolOwnerId = (int)($parentDetails['create_by_userid'] ?? 0);
}

if ($schoolOwnerId <= 0) {
    $schoolOwnerId = (int)db_get_val(
        "SELECT create_by_userid FROM manage_student WHERE parent_id = ? ORDER BY id DESC LIMIT 1",
        [$parentId]
    );
}

$school = [];
if ($schoolOwnerId > 0) {
    $school = db_get_row("SELECT * FROM school_register WHERE id = ?", [$schoolOwnerId]) ?: [];
}

$schoolName = trim((string)($school['name'] ?? ''));
if ($schoolName === '') {
    $schoolName = 'Best School Page';
}
$schoolLogo = $school['logo'] ?? '';

// ============================================================================
// GET ALL CHILDREN FOR THIS PARENT FROM manage_student
// ============================================================================
$parentLookupIds = array_values(array_unique(array_filter([
    trim((string)$parentId),
    trim((string)($parentDetails['parent_id'] ?? '')),
    trim((string)($parentDetails['student_id_str'] ?? '')),
])));

$children = [];
if (!empty($parentLookupIds)) {
    $placeholders = implode(',', array_fill(0, count($parentLookupIds), '?'));
    $children = db_get_rows(
        "SELECT ms.*
         FROM (
            SELECT student_id, MAX(id) latest_id
            FROM manage_student
            WHERE parent_id IN ($placeholders)
            GROUP BY student_id
         ) latest
         INNER JOIN manage_student ms
            ON ms.id = latest.latest_id
         ORDER BY ms.first_name ASC",
        $parentLookupIds
    ) ?: [];
}

// ============================================================================
// FUNCTION: Get ALL sessions that have scores for this student
// ============================================================================
function getAvailableSessionsForStudent($studentId)
{
    return db_get_rows(
        "SELECT DISTINCT s.id, s.session 
         FROM school_session s
         INNER JOIN input_score_class_teacher i ON i.session_id = s.id
         WHERE i.student_id = ?
         ORDER BY s.id DESC",
        [$studentId]
    );
}

// ============================================================================
// FUNCTION: Get ALL terms that have scores for this student AND session
// ============================================================================
function getAvailableTermsForStudent($studentId, $sessionId)
{
    return db_get_rows(
        "SELECT DISTINCT t.id, t.term 
         FROM school_term t
         INNER JOIN input_score_class_teacher i ON i.term_id = t.id
         WHERE i.student_id = ? AND i.session_id = ?
         ORDER BY t.id ASC",
        [$studentId, $sessionId]
    );
}

// ============================================================================
// FUNCTION: Get ALL classes that have scores for this student AND session & term
// ============================================================================
function getAvailableClassesForStudent($studentId, $sessionId, $termId)
{
    return db_get_rows(
        "SELECT DISTINCT c.id, c.name 
         FROM school_class c
         INNER JOIN input_score_class_teacher i ON i.class_id = c.id
         WHERE i.student_id = ? AND i.session_id = ? AND i.term_id = ?
         ORDER BY c.name ASC",
        [$studentId, $sessionId, $termId]
    );
}

// ============================================================================
// GET ASSESSMENTS FOR PDF
// ============================================================================
function getAssessmentsForClass($classId)
{
    $assessments = db_get_rows(
        "SELECT id FROM school_assessment WHERE (class_id = ? OR class_id IS NULL OR class_id = 0) ORDER BY id ASC",
        [$classId]
    );
    $ids = [];
    foreach ($assessments as $a) {
        $ids[] = $a['id'];
    }
    return implode('-', $ids);
}

function getClassName($id)
{
    return db_get_val("SELECT name FROM school_class WHERE id = ?", [$id]) ?: 'N/A';
}

function getDashboardGrade($averageScore)
{
    $score = (float)$averageScore;
    if ($score >= 75) return 'A';
    if ($score >= 65) return 'B';
    if ($score >= 55) return 'C';
    if ($score >= 45) return 'D';
    if ($score >= 40) return 'E';
    return 'F';
}

// ============================================================================
// GET LATEST SCORES SUMMARY FOR DISPLAY
// ============================================================================
function getLatestResultSummary($studentId)
{
    // Get latest session with scores
    $latestSession = db_get_row(
        "SELECT DISTINCT s.id, s.session 
         FROM school_session s
         INNER JOIN input_score_class_teacher i ON i.session_id = s.id
         WHERE i.student_id = ?
         ORDER BY s.id DESC
         LIMIT 1",
        [$studentId]
    );

    if (empty($latestSession)) {
        return null;
    }

    // Get latest term for this session
    $latestTerm = db_get_row(
        "SELECT DISTINCT t.id, t.term 
         FROM school_term t
         INNER JOIN input_score_class_teacher i ON i.term_id = t.id
         WHERE i.student_id = ? AND i.session_id = ?
         ORDER BY t.id DESC
         LIMIT 1",
        [$studentId, $latestSession['id']]
    );

    if (empty($latestTerm)) {
        return null;
    }

    // Get class for this session and term
    $latestClass = db_get_row(
        "SELECT DISTINCT c.id, c.name 
         FROM school_class c
         INNER JOIN input_score_class_teacher i ON i.class_id = c.id
         WHERE i.student_id = ? AND i.session_id = ? AND i.term_id = ?
         LIMIT 1",
        [$studentId, $latestSession['id'], $latestTerm['id']]
    );

    // Get total score
    $totalScore = db_get_val(
        "SELECT SUM(score) 
         FROM input_score_class_teacher 
         WHERE student_id = ? AND session_id = ? AND term_id = ?",
        [$studentId, $latestSession['id'], $latestTerm['id']]
    );

    // Get number of subjects
    $subjectCount = db_get_val(
        "SELECT COUNT(DISTINCT subject_id) 
         FROM input_score_class_teacher 
         WHERE student_id = ? AND session_id = ? AND term_id = ?",
        [$studentId, $latestSession['id'], $latestTerm['id']]
    );

    // Get position
    $positionQuery = "SELECT student_id, SUM(score) as total 
                      FROM input_score_class_teacher 
                      WHERE class_id = ? AND session_id = ? AND term_id = ? 
                      GROUP BY student_id";
    $allScores = db_get_rows($positionQuery, [
        $latestClass['id'] ?? 0,
        $latestSession['id'],
        $latestTerm['id']
    ]);

    $position = 1;
    $scores = [];
    if (is_array($allScores)) {
        foreach ($allScores as $s) {
            $scores[$s['student_id']] = (float)$s['total'];
        }
        arsort($scores);
        $pos = 1;
        foreach ($scores as $sid => $total) {
            if ($sid == $studentId) {
                $position = $pos;
                break;
            }
            $pos++;
        }
    }

    $averageScore = ((int)$subjectCount > 0) ? ((float)$totalScore / (int)$subjectCount) : 0;
    $grade = ((int)$subjectCount > 0) ? getDashboardGrade($averageScore) : '-';

    return [
        'session_id' => $latestSession['id'],
        'session_name' => $latestSession['session'],
        'term_id' => $latestTerm['id'],
        'term_name' => $latestTerm['term'],
        'class_id' => $latestClass['id'] ?? 0,
        'class_name' => $latestClass['name'] ?? 'N/A',
        'total_score' => (float)($totalScore ?? 0),
        'subject_count' => (int)($subjectCount ?? 0),
        'position' => $position,
        'average_score' => round($averageScore, 2),
        'grade' => $grade
    ];
}

// ============================================================================
// GET ALL AVAILABLE DATA FOR A STUDENT (pre-loaded for instant filtering)
// ============================================================================
function getAllAvailableDataForStudent($studentId)
{
    $data = [
        'sessions' => [],
        'terms' => [],
        'classes' => []
    ];

    // Get all sessions with scores
    $sessions = getAvailableSessionsForStudent($studentId);
    $data['sessions'] = $sessions;

    // For each session, get terms
    foreach ($sessions as $session) {
        $terms = getAvailableTermsForStudent($studentId, $session['id']);
        $data['terms'][$session['id']] = $terms;

        // For each term, get classes
        foreach ($terms as $term) {
            $classes = getAvailableClassesForStudent($studentId, $session['id'], $term['id']);
            $data['classes'][$session['id']][$term['id']] = $classes;
        }
    }

    return $data;
}

// ============================================================================
// CHANGE PASSWORD
// ============================================================================
$passwordMessage = '';
if (isset($_POST['change_password'])) {
    $oldPassword = $_POST['old_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    $errors = [];

    $user = db_get_row("SELECT * FROM student_guardian WHERE parent_id = ? AND type = 1", [$parentId]);

    if ($user) {
        $storedPassword = $user['password'] ?? '';
        if (strpos($storedPassword, '$2y$') === 0) {
            $passwordValid = password_verify($oldPassword, $storedPassword);
        } else {
            $passwordValid = ($storedPassword === $oldPassword);
        }

        if (!$passwordValid) {
            $errors[] = "Current password is incorrect";
        }
    } else {
        $errors[] = "User not found";
    }

    if (strlen($newPassword) < 8) $errors[] = "New password must be at least 8 characters";
    if (!preg_match('/[A-Z]/', $newPassword)) $errors[] = "New password must contain at least one uppercase letter";
    if (!preg_match('/[0-9]/', $newPassword)) $errors[] = "New password must contain at least one number";
    if (!preg_match('/[!@#$%&*?]/', $newPassword)) $errors[] = "New password must contain at least one symbol (!@#$%&*?)";
    if ($newPassword !== $confirmPassword) $errors[] = "New password and confirm password do not match";

    if (empty($errors)) {
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        db_update("student_guardian", ['password' => $hashedPassword], "parent_id = ?", [$parentId]);
        $passwordMessage = '<div class="alert alert-success">✅ Password changed successfully!</div>';
    } else {
        $passwordMessage = '<div class="alert alert-danger">' . implode('<br>', $errors) . '</div>';
    }
}

// ============================================================================
// UPDATE PROFILE
// ============================================================================
$profileMessage = '';
if (isset($_POST['update_profile'])) {
    $title = $_POST['title'] ?? '';
    $firstName = trim($_POST['first_name'] ?? '');
    $lastName = trim($_POST['last_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');

    $errors = [];
    if (empty($firstName)) $errors[] = "First name is required";
    if (empty($lastName)) $errors[] = "Last name is required";

    if (empty($errors)) {
        db_update("student_guardian", [
            'title' => $title,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'phone' => $phone,
            'email' => $email
        ], "parent_id = ?", [$parentId]);

        $_SESSION['fullname'] = $firstName . ' ' . $lastName;
        $_SESSION['title'] = $title;
        $_SESSION['first_name'] = $firstName;
        $_SESSION['last_name'] = $lastName;
        $_SESSION['phone'] = $phone;
        $_SESSION['email'] = $email;

        $profileMessage = '<div class="alert alert-success">✅ Profile updated successfully!</div>';
        $parentDetails = db_get_row("SELECT * FROM student_guardian WHERE parent_id = ? AND type = 1", [$parentId]);
    } else {
        $profileMessage = '<div class="alert alert-danger">' . implode('<br>', $errors) . '</div>';
    }
}

// ============================================================================
// LOGOUT
// ============================================================================
if (isset($_GET['logout'])) {
    session_destroy();
    redirect('index.php');
    exit;
}
?>
<!DOCTYPE html>
<html>

<head>
    <?php include('../skool/inc.meta.php'); ?>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
            padding: 0;
            margin: 0;
        }

        .dashboard-container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 20px 15px;
        }

        .school-header {
            text-align: center;
            margin-bottom: 20px;
        }

        .school-logo {
            max-width: 70px;
            max-height: 70px;
            margin-bottom: 8px;
            border-radius: 50%;
            background: white;
            padding: 5px;
        }

        .school-name {
            font-size: 22px;
            font-weight: bold;
            color: white;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.2);
        }

        .welcome-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 18px 20px;
            margin-bottom: 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 12px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .welcome-text h2 {
            font-size: 20px;
            margin-bottom: 4px;
            color: #1B3058;
        }

        .welcome-text p {
            color: #666;
            font-size: 13px;
            margin: 0;
        }

        .btn-group {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .settings-btn,
        .logout-btn {
            border: none;
            padding: 8px 18px;
            border-radius: 40px;
            cursor: pointer;
            font-size: 13px;
            font-weight: 600;
            transition: all 0.2s;
            white-space: nowrap;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .settings-btn {
            background: linear-gradient(135deg, #1B3058, #2a4780);
            color: white;
        }

        .logout-btn {
            background: linear-gradient(135deg, #dc3545, #c82333);
            color: white;
        }

        .settings-btn:hover,
        .logout-btn:hover {
            transform: scale(1.02);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
            color: white;
        }

        /* Child Cards */
        .child-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border-radius: 20px;
            margin-bottom: 18px;
            overflow: hidden;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
            transition: all 0.3s;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .child-card:hover {
            transform: translateY(-3px);
        }

        .child-header {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 16px 20px;
            background: rgba(248, 249, 250, 0.8);
            cursor: pointer;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        }

        .child-avatar {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: linear-gradient(135deg, #1B3058, #2a4780);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            font-weight: bold;
            color: white;
            overflow: hidden;
            flex-shrink: 0;
        }

        .child-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .child-info {
            flex: 1;
            min-width: 0;
        }

        .child-name {
            font-size: 18px;
            font-weight: 700;
            color: #1B3058;
            margin-bottom: 2px;
            word-break: break-word;
        }

        .child-details {
            font-size: 12px;
            color: #666;
            margin: 0;
        }

        .child-id {
            font-size: 11px;
            color: #999;
            margin-top: 2px;
        }

        .collapse-icon {
            font-size: 18px;
            color: #999;
            transition: transform 0.3s;
            background: rgba(0, 0, 0, 0.05);
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            cursor: pointer;
        }

        .collapse-icon.open {
            transform: rotate(180deg);
            background: #1B3058;
            color: white;
        }

        .child-body {
            padding: 18px 20px;
            display: none;
            background: white;
        }

        .child-body.show {
            display: block;
        }

        /* Latest Result Summary */
        .latest-result {
            background: #f8f9ff;
            border-radius: 16px;
            padding: 15px 18px;
            margin-bottom: 18px;
            border-left: 4px solid #1B3058;
        }

        .latest-result-title {
            font-size: 13px;
            font-weight: 700;
            color: #1B3058;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .latest-result-title .badge {
            background: #28a745;
            color: white;
            font-size: 10px;
            padding: 2px 10px;
            border-radius: 20px;
        }

        .result-stats {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }

        .result-stat {
            flex: 1;
            min-width: 80px;
            text-align: center;
            background: white;
            padding: 8px 12px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }

        .result-stat .label {
            font-size: 10px;
            color: #888;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .result-stat .value {
            font-size: 18px;
            font-weight: 700;
            color: #1B3058;
            margin-top: 2px;
        }

        .result-stat .value .small {
            font-size: 12px;
            color: #666;
        }

        /* Historical Search */
        .historical-search {
            background: #f5f7fb;
            border-radius: 16px;
            padding: 16px 18px;
            margin-top: 5px;
        }

        .historical-title {
            font-size: 13px;
            font-weight: 700;
            color: #666;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .historical-title i {
            color: #1B3058;
        }

        .filter-row {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            align-items: flex-end;
        }

        .filter-group {
            flex: 1;
            min-width: 120px;
        }

        .filter-group label {
            display: block;
            font-size: 10px;
            font-weight: 700;
            color: #888;
            margin-bottom: 3px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .filter-select {
            width: 100%;
            padding: 9px 12px;
            border: 2px solid #e0e0e0;
            border-radius: 12px;
            font-size: 13px;
            background: white;
            cursor: pointer;
            transition: all 0.2s;
            -webkit-appearance: none;
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='8' viewBox='0 0 12 8'%3E%3Cpath d='M1 1l5 5 5-5' stroke='%23999' stroke-width='1.5' fill='none' stroke-linecap='round'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 10px center;
            padding-right: 32px;
        }

        .filter-select:focus {
            outline: none;
            border-color: #1B3058;
        }

        .filter-select:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .view-result-btn {
            background: linear-gradient(135deg, #1B3058, #2a4780);
            color: white;
            border: none;
            padding: 9px 20px;
            border-radius: 12px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            min-width: 120px;
            justify-content: center;
            text-decoration: none;
        }

        .view-result-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(27, 48, 88, 0.3);
            color: white;
        }

        .view-result-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        .view-result-btn.btn-green {
            background: linear-gradient(135deg, #28a745, #20c997);
        }

        .view-result-btn.btn-green:hover {
            box-shadow: 0 5px 15px rgba(40, 167, 69, 0.3);
            color: white;
        }

        .view-result-btn.btn-sm {
            padding: 6px 14px;
            font-size: 11px;
            min-width: auto;
        }

        .no-results {
            text-align: center;
            padding: 15px;
            color: #999;
            font-size: 13px;
        }

        .no-results i {
            font-size: 24px;
            display: block;
            margin-bottom: 5px;
            color: #ddd;
        }

        /* Quick link buttons */
        .quick-links {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 10px;
        }

        .quick-link {
            background: white;
            border: 1px solid #e0e0e0;
            border-radius: 20px;
            padding: 4px 12px;
            font-size: 11px;
            color: #555;
            text-decoration: none;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        .quick-link:hover {
            background: #1B3058;
            color: white;
            border-color: #1B3058;
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(5px);
            -webkit-backdrop-filter: blur(5px);
            z-index: 1000;
            justify-content: center;
            align-items: center;
            padding: 15px;
        }

        .modal-content {
            background: white;
            border-radius: 24px;
            max-width: 500px;
            width: 100%;
            max-height: 90vh;
            overflow-y: auto;
            padding: 25px;
            position: relative;
            animation: modalSlideIn 0.3s ease;
        }

        @keyframes modalSlideIn {
            from {
                transform: translateY(-30px);
                opacity: 0;
            }

            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 18px;
            padding-bottom: 12px;
            border-bottom: 2px solid #eee;
        }

        .modal-header h3 {
            color: #1B3058;
            font-size: 20px;
            margin: 0;
        }

        .modal-close {
            font-size: 28px;
            cursor: pointer;
            color: #999;
            transition: all 0.2s;
            line-height: 1;
            padding: 0 5px;
        }

        .modal-close:hover {
            color: #dc3545;
        }

        .form-group {
            margin-bottom: 18px;
        }

        .form-group label {
            display: block;
            font-weight: 600;
            margin-bottom: 6px;
            color: #333;
            font-size: 13px;
        }

        .form-control {
            width: 100%;
            padding: 11px 14px;
            border: 2px solid #e0e0e0;
            border-radius: 12px;
            font-size: 14px;
            transition: all 0.2s;
            -webkit-appearance: none;
            appearance: none;
        }

        .form-control:focus {
            outline: none;
            border-color: #1B3058;
            box-shadow: 0 0 0 3px rgba(27, 48, 88, 0.1);
        }

        .btn-primary {
            background: #1B3058;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 12px;
            font-weight: 600;
            cursor: pointer;
            width: 100%;
            font-size: 15px;
            transition: all 0.2s;
        }

        .btn-primary:hover {
            background: #f21151;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(242, 17, 81, 0.3);
        }

        .btn-primary:active {
            transform: scale(0.98);
        }

        .alert {
            padding: 11px 15px;
            border-radius: 12px;
            margin-bottom: 18px;
            font-size: 13px;
            word-break: break-word;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border-left: 4px solid #28a745;
        }

        .alert-danger {
            background: #f8d7da;
            color: #721c24;
            border-left: 4px solid #dc3545;
        }

        .alert-info {
            background: #d1ecf1;
            color: #0c5460;
            border-left: 4px solid #17a2b8;
        }

        .empty-state {
            text-align: center;
            padding: 40px 20px;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            color: #999;
        }

        .empty-state i {
            font-size: 48px;
            color: #ddd;
            margin-bottom: 12px;
            display: block;
        }

        .empty-state h3 {
            color: #666;
            margin-bottom: 5px;
        }

        .empty-state p {
            margin: 0;
        }

        hr {
            margin: 18px 0;
            border: none;
            border-top: 1px solid #eee;
        }

        .badge-count {
            background: #1B3058;
            color: white;
            font-size: 10px;
            padding: 1px 8px;
            border-radius: 20px;
            margin-left: 5px;
        }

        /* ============================================ */
        /* MOBILE RESPONSIVE */
        /* ============================================ */

        @media (max-width: 480px) {
            .dashboard-container {
                padding: 12px 10px;
            }

            .school-name {
                font-size: 18px;
            }

            .school-logo {
                max-width: 55px;
                max-height: 55px;
            }

            .welcome-card {
                padding: 15px;
                flex-direction: column;
                text-align: center;
                gap: 10px;
            }

            .welcome-text h2 {
                font-size: 18px;
            }

            .welcome-text p {
                font-size: 12px;
            }

            .btn-group {
                justify-content: center;
                width: 100%;
            }

            .settings-btn,
            .logout-btn {
                font-size: 12px;
                padding: 8px 16px;
                flex: 1;
                text-align: center;
                justify-content: center;
            }

            .child-header {
                padding: 12px 14px;
                gap: 10px;
            }

            .child-avatar {
                width: 48px;
                height: 48px;
                font-size: 20px;
            }

            .child-name {
                font-size: 15px;
            }

            .child-details {
                font-size: 11px;
            }

            .child-id {
                font-size: 10px;
            }

            .collapse-icon {
                width: 26px;
                height: 26px;
                font-size: 14px;
            }

            .child-body {
                padding: 14px;
            }

            .latest-result {
                padding: 12px 14px;
            }

            .result-stats {
                gap: 8px;
            }

            .result-stat {
                min-width: 100px;
                padding: 6px 10px;
            }

            .result-stat .value {
                font-size: 10px;
            }

            .result-stat .label {
                font-size: 10px;
            }

            .historical-search {
                padding: 12px 14px;
            }

            .filter-group {
                min-width: 100%;
                flex: 0 0 100%;
            }

            .filter-select {
                padding: 9px 12px;
                font-size: 13px;
                padding-right: 32px;
                background-position: right 10px center;
            }

            .view-result-btn {
                width: 100%;
                justify-content: center;
                padding: 11px 20px;
                font-size: 14px;
            }

            .modal-content {
                padding: 20px;
                border-radius: 20px;
            }

            .modal-header h3 {
                font-size: 18px;
            }

            .modal-close {
                font-size: 24px;
            }

            .form-control {
                padding: 10px 12px;
                font-size: 13px;
            }

            .btn-primary {
                padding: 11px 20px;
                font-size: 14px;
            }

            .empty-state {
                padding: 30px 15px;
            }

            .empty-state i {
                font-size: 40px;
            }

            .empty-state h3 {
                font-size: 16px;
            }

            .quick-links {
                justify-content: center;
            }

            .quick-link {
                font-size: 10px;
                padding: 3px 10px;
            }
        }

        @media (min-width: 481px) and (max-width: 768px) {
            .dashboard-container {
                padding: 18px 15px;
            }

            .welcome-card {
                padding: 16px 18px;
                flex-direction: column;
                text-align: center;
            }

            .btn-group {
                justify-content: center;
            }

            .child-header {
                padding: 14px 16px;
                gap: 12px;
            }

            .child-avatar {
                width: 52px;
                height: 52px;
                font-size: 22px;
            }

            .child-name {
                font-size: 16px;
            }

            .filter-group {
                min-width: 130px;
            }

            .view-result-btn {
                width: 100%;
                justify-content: center;
            }
        }

        @media (min-width: 769px) and (max-width: 1024px) {
            .dashboard-container {
                padding: 25px 20px;
            }

            .filter-group {
                min-width: 150px;
            }
        }

        @media (hover: none) {
            .child-card:hover {
                transform: none;
            }

            .settings-btn:hover,
            .logout-btn:hover {
                transform: none;
                box-shadow: none;
            }

            .view-result-btn:hover {
                transform: none;
                box-shadow: none;
            }

            .btn-primary:hover {
                transform: none;
                box-shadow: none;
            }
        }

        @media (max-height: 600px) and (orientation: landscape) {
            .dashboard-container {
                padding: 10px 15px;
            }

            .school-header {
                margin-bottom: 20px;
            }

            .school-logo {
                max-width: 60px;
                max-height: 60px;
            }

            .school-name {
                font-size: 16px;
            }

            .welcome-card {
                padding: 12px 15px;
                margin-bottom: 15px;
            }

            .welcome-text h2 {
                font-size: 16px;
            }

            .child-header {
                padding: 10px 14px;
            }

            .child-avatar {
                width: 40px;
                height: 40px;
                font-size: 18px;
            }

            .child-name {
                font-size: 14px;
            }

            .child-body {
                padding: 12px 14px;
            }

            .modal-content {
                padding: 18px;
                max-height: 85vh;
            }
        }

        @media (-webkit-min-device-pixel-ratio: 2),
        (min-resolution: 192dpi) {

            .child-card,
            .welcome-card,
            .modal-content {
                border: 0.5px solid rgba(255, 255, 255, 0.2);
            }
        }
    </style>
</head>

<body>
    <div class="dashboard-container">

        <!-- School Header -->
        <div class="school-header">
            <?php if (!empty($schoolLogo) && file_exists("../uploads/" . $schoolLogo)): ?>
                <img src="../uploads/<?= htmlspecialchars($schoolLogo) ?>" class="school-logo" alt="School Logo">
            <?php endif; ?>
            <div class="school-name"><?= htmlspecialchars($schoolName) ?></div>
        </div>

        <!-- Welcome Card -->
        <div class="welcome-card">
            <div class="welcome-text">
                <h2>👋 Hello, <?= htmlspecialchars(($parentDetails['title'] ?? '') . ' ' . ($parentDetails['first_name'] ?? '')) ?>!</h2>
                <p>View your children's results. Latest results shown below.</p>
            </div>
            <div class="btn-group">
                <button class="settings-btn" onclick="openSettings()">
                    <i class="fa fa-cog"></i> Settings
                </button>
                <a href="?logout=1" class="logout-btn" onclick="return confirm('Are you sure you want to logout?')">
                    <i class="fa fa-sign-out"></i> Logout
                </a>
            </div>
        </div>

        <!-- Children List -->
        <?php if (!empty($children)): ?>
            <?php foreach ($children as $index => $child):
                $childClass = getClassName($child['class']);
                $assessmentsParam = getAssessmentsForClass($child['class']);

                // Get ALL available data for this student
                $availableData = getAllAvailableDataForStudent($child['id']);
                $allSessions = $availableData['sessions'];
                $allTerms = $availableData['terms'];
                $allClasses = $availableData['classes'];

                $latestResult = getLatestResultSummary($child['id']);

                // Count total available results
                $totalResults = 0;
                foreach ($allSessions as $session) {
                    $terms = $allTerms[$session['id']] ?? [];
                    foreach ($terms as $term) {
                        $classes = $allClasses[$session['id']][$term['id']] ?? [];
                        $totalResults += count($classes);
                    }
                }

                // Get first session for initial dropdown
                $firstSessionId = !empty($allSessions) ? $allSessions[0]['id'] : 0;
                $initialTerms = $allTerms[$firstSessionId] ?? [];
            ?>
                <div class="child-card">
                    <div class="child-header" onclick="toggleChild(<?= $index ?>)">
                        <div class="child-avatar">
                            <?php if (!empty($child['picture']) && file_exists("../uploads/" . $child['picture'])): ?>
                                <img src="../uploads/<?= htmlspecialchars($child['picture']) ?>" alt="Photo">
                            <?php else: ?>
                                <?= strtoupper(substr($child['first_name'] ?? '?', 0, 1)) ?>
                            <?php endif; ?>
                        </div>
                        <div class="child-info">
                            <div class="child-name"><?= htmlspecialchars($child['first_name'] . ' ' . $child['last_name']) ?></div>
                            <div class="child-details">ID: <?= htmlspecialchars($child['student_id']) ?> | Class: <?= htmlspecialchars($childClass) ?></div>
                        </div>
                        <div class="collapse-icon" id="icon_<?= $index ?>">▼</div>
                    </div>
                    <div class="child-body" id="body_<?= $index ?>">

                        <?php if ($latestResult): ?>
                            <!-- Latest Result Summary -->
                            <div class="latest-result">
                                <div class="latest-result-title">
                                    <i class="fa fa-star" style="color: #f21151;"></i>
                                    Latest Result
                                    <span class="badge">Latest</span>
                                </div>
                                <div class="result-stats">
                                    <div class="result-stat">
                                        <div class="label">Session</div>
                                        <div class="value" style="font-size:13px;"><?= htmlspecialchars($latestResult['session_name']) ?></div>
                                    </div>
                                    <div class="result-stat">
                                        <div class="label">Term</div>
                                        <div class="value" style="font-size:13px;"><?= htmlspecialchars($latestResult['term_name']) ?></div>
                                    </div>
                                    <div class="result-stat">
                                        <div class="label">Total Score</div>
                                        <div class="value"><?= number_format($latestResult['total_score'], 0) ?></div>
                                    </div>
                                    <div class="result-stat">
                                        <div class="label">Subjects</div>
                                        <div class="value"><?= $latestResult['subject_count'] ?></div>
                                    </div>
                                    <div class="result-stat">
                                        <div class="label">Grade</div>
                                        <div class="value"><?= htmlspecialchars($latestResult['grade'] ?? '-') ?></div>
                                    </div>
                                </div>
                                <!-- Quick View Latest Result Button -->
                                <div style="margin-top:12px; text-align:right;">
                                    <a href="parent_term_result_pdf.php?randomid=<?= urlencode($child['randomid']) ?>&student_id=<?= urlencode($child['student_id']) ?>&session=<?= $latestResult['session_id'] ?>&term_id=<?= $latestResult['term_id'] ?>&class_id=<?= $latestResult['class_id'] ?>&assesments=<?= urlencode($assessmentsParam) ?>"
                                        target="_blank"
                                        class="view-result-btn btn-green"
                                        style="padding: 8px 16px; font-size:12px; min-width:auto;">
                                        <i class="fa fa-file-pdf-o"></i> View This Term's Result
                                    </a>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-info" style="text-align:center;">
                                <i class="fa fa-info-circle"></i> No results available yet.
                            </div>
                        <?php endif; ?>

                        <!-- Historical Search -->
                        <div class="historical-search">
                            <div class="historical-title">
                                <i class="fa fa-history"></i> View Historical Results
                                <?php if ($totalResults > 0): ?>
                                    <span class="badge-count"><?= $totalResults ?> result(s) available</span>
                                <?php endif; ?>
                            </div>

                            <?php if (!empty($allSessions)): ?>
                                <form method="GET" action="parent_term_result_pdf.php" target="_blank" id="historyForm_<?= $child['id'] ?>">
                                    <!-- Hidden fields -->
                                    <input type="hidden" name="randomid" value="<?= htmlspecialchars($child['randomid']) ?>">
                                    <input type="hidden" name="student_id" value="<?= htmlspecialchars($child['student_id']) ?>">
                                    <input type="hidden" name="assesments" value="<?= $assessmentsParam ?>">

                                    <div class="filter-row">
                                        <div class="filter-group">
                                            <label><i class="fa fa-calendar"></i> Session</label>
                                            <select name="session" class="filter-select" onchange="loadTerms(<?= $child['id'] ?>, this.value)">
                                                <option value="">-- Select --</option>
                                                <?php foreach ($allSessions as $s): ?>
                                                    <option value="<?= $s['id'] ?>">
                                                        <?= htmlspecialchars($s['session']) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="filter-group">
                                            <label><i class="fa fa-tag"></i> Term</label>
                                            <select name="term_id" class="filter-select" id="termSelect_<?= $child['id'] ?>" onchange="loadClasses(<?= $child['id'] ?>, this.value)">
                                                <option value="">Select session first</option>
                                            </select>
                                        </div>
                                        <div class="filter-group">
                                            <label><i class="fa fa-building"></i> Class</label>
                                            <select name="class_id" class="filter-select" id="classSelect_<?= $child['id'] ?>">
                                                <option value="">Select term first</option>
                                            </select>
                                        </div>
                                        <div class="filter-group" style="min-width:100px; flex:0.5;">
                                            <button type="submit" class="view-result-btn" id="historyBtn_<?= $child['id'] ?>" disabled>
                                                <i class="fa fa-file-pdf-o"></i> View
                                            </button>
                                        </div>
                                    </div>
                                </form>

                                <!-- Quick Links for all available results -->
                                <?php if ($totalResults > 0 && $totalResults <= 10): ?>
                                    <div class="quick-links">
                                        <span style="font-size:11px; color:#888; margin-right:5px;">Quick access:</span>
                                        <?php
                                        $count = 0;
                                        foreach ($allSessions as $session):
                                            $terms = $allTerms[$session['id']] ?? [];
                                            foreach ($terms as $term):
                                                $classes = $allClasses[$session['id']][$term['id']] ?? [];
                                                foreach ($classes as $class):
                                                    if ($count >= 10) break 3;
                                                    $count++;
                                        ?>
                                                    <a href="parent_term_result_pdf.php?randomid=<?= urlencode($child['randomid']) ?>&student_id=<?= urlencode($child['student_id']) ?>&session=<?= $session['id'] ?>&term_id=<?= $term['id'] ?>&class_id=<?= $class['id'] ?>&assesments=<?= urlencode($assessmentsParam) ?>"
                                                        target="_blank"
                                                        class="quick-link">
                                                        <i class="fa fa-file-pdf-o"></i>
                                                        <?= htmlspecialchars($session['session']) ?> - <?= htmlspecialchars($term['term']) ?>
                                                    </a>
                                            <?php
                                                endforeach;
                                            endforeach;
                                        endforeach;
                                        if ($totalResults > 10): ?>
                                            <span style="font-size:11px; color:#999;">+ <?= $totalResults - 10 ?> more</span>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>

                            <?php else: ?>
                                <div class="no-results">
                                    <i class="fa fa-folder-open-o"></i>
                                    No historical results found
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Hidden data containers for AJAX -->
                        <div id="termsData_<?= $child['id'] ?>" style="display:none;">
                            <?php echo json_encode($allTerms); ?>
                        </div>
                        <div id="classesData_<?= $child['id'] ?>" style="display:none;">
                            <?php echo json_encode($allClasses); ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="empty-state">
                <i class="fa fa-users-slash"></i>
                <h3>No Children Found</h3>
                <p>Please contact the school administrator to link your children to this account.</p>
            </div>
        <?php endif; ?>

    </div>

    <!-- Settings Modal -->
    <div id="settingsModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fa fa-cog"></i> Settings</h3>
                <span class="modal-close" onclick="closeSettings()">&times;</span>
            </div>

            <!-- Change Password -->
            <div style="margin-bottom: 30px;">
                <h4 style="color: #1B3058; margin-bottom: 15px;"><i class="fa fa-key"></i> Change Password</h4>
                <?= $passwordMessage ?>
                <form method="post">
                    <div class="form-group">
                        <label>Current Password</label>
                        <input type="password" name="old_password" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>New Password</label>
                        <input type="password" name="new_password" class="form-control" required>
                        <small style="font-size: 11px; color: #888;">Must contain: Uppercase, Number, Symbol (!@#$%&*?)</small>
                    </div>
                    <div class="form-group">
                        <label>Confirm New Password</label>
                        <input type="password" name="confirm_password" class="form-control" required>
                    </div>
                    <button type="submit" name="change_password" class="btn-primary">Update Password</button>
                </form>
            </div>

            <hr>

            <!-- Edit Profile -->
            <div>
                <h4 style="color: #1B3058; margin-bottom: 15px;"><i class="fa fa-user"></i> Edit Profile</h4>
                <?= $profileMessage ?>
                <form method="post">
                    <div class="form-group">
                        <label>Title</label>
                        <select name="title" class="form-control">
                            <option value="">Select Title</option>
                            <option value="Mr." <?= ($parentDetails['title'] ?? '') == 'Mr.' ? 'selected' : '' ?>>Mr.</option>
                            <option value="Mrs." <?= ($parentDetails['title'] ?? '') == 'Mrs.' ? 'selected' : '' ?>>Mrs.</option>
                            <option value="Miss." <?= ($parentDetails['title'] ?? '') == 'Miss.' ? 'selected' : '' ?>>Miss.</option>
                            <option value="Dr." <?= ($parentDetails['title'] ?? '') == 'Dr.' ? 'selected' : '' ?>>Dr.</option>
                            <option value="Prof." <?= ($parentDetails['title'] ?? '') == 'Prof.' ? 'selected' : '' ?>>Prof.</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>First Name *</label>
                        <input type="text" name="first_name" class="form-control" value="<?= htmlspecialchars($parentDetails['first_name'] ?? '') ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Last Name *</label>
                        <input type="text" name="last_name" class="form-control" value="<?= htmlspecialchars($parentDetails['last_name'] ?? '') ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Phone</label>
                        <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($parentDetails['phone'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($parentDetails['email'] ?? '') ?>">
                    </div>
                    <button type="submit" name="update_profile" class="btn-primary">Update Profile</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        // ================================================================
        // TOGGLE CHILD PANEL
        // ================================================================
        function toggleChild(index) {
            var body = document.getElementById('body_' + index);
            var icon = document.getElementById('icon_' + index);
            body.classList.toggle('show');
            icon.classList.toggle('open');
        }

        // Expand first child by default
        document.addEventListener('DOMContentLoaded', function() {
            if (document.getElementById('body_0')) {
                document.getElementById('body_0').classList.add('show');
                document.getElementById('icon_0').classList.add('open');
            }

            // Initialize all child forms
            document.querySelectorAll('.child-body').forEach(function(body) {
                var sessionSelect = body.querySelector('select[name="session"]');
                if (sessionSelect && sessionSelect.options.length > 1) {
                    var form = body.querySelector('form');
                    if (form) {
                        var studentId = form.id.replace('historyForm_', '');
                        if (studentId) {
                            // Auto-select first session
                            var firstSession = sessionSelect.options[1].value;
                            if (firstSession) {
                                sessionSelect.value = firstSession;
                                loadTerms(parseInt(studentId), firstSession);
                            }
                        }
                    }
                }
            });
        });

        // ================================================================
        // LOAD TERMS DYNAMICALLY
        // ================================================================
        function loadTerms(studentId, sessionId) {
            var termSelect = document.getElementById('termSelect_' + studentId);
            var classSelect = document.getElementById('classSelect_' + studentId);
            var historyBtn = document.getElementById('historyBtn_' + studentId);

            // Reset dependent dropdowns
            classSelect.innerHTML = '<option value="">Select term first</option>';
            classSelect.disabled = true;
            historyBtn.disabled = true;

            if (!sessionId) {
                termSelect.innerHTML = '<option value="">Select session first</option>';
                termSelect.disabled = true;
                return;
            }

            // Get terms data
            var termsDataDiv = document.getElementById('termsData_' + studentId);
            if (!termsDataDiv) return;

            try {
                var termsData = JSON.parse(termsDataDiv.innerHTML);
                var terms = termsData[sessionId] || [];

                termSelect.innerHTML = '';
                if (terms.length > 0) {
                    termSelect.innerHTML = '<option value="">-- Select Term --</option>';
                    terms.forEach(function(term) {
                        var option = document.createElement('option');
                        option.value = term.id;
                        option.text = term.term;
                        termSelect.appendChild(option);
                    });
                    termSelect.disabled = false;
                    // Auto-select first term and load classes
                    if (terms.length > 0) {
                        termSelect.value = terms[0].id;
                        loadClasses(studentId, terms[0].id);
                    }
                } else {
                    termSelect.innerHTML = '<option value="">No terms available</option>';
                    termSelect.disabled = true;
                }
            } catch (e) {
                console.error('Error parsing terms data:', e);
                termSelect.innerHTML = '<option value="">Error loading terms</option>';
                termSelect.disabled = true;
            }
        }

        // ================================================================
        // LOAD CLASSES DYNAMICALLY
        // ================================================================
        function loadClasses(studentId, termId) {
            var sessionSelect = document.querySelector('#historyForm_' + studentId + ' select[name="session"]');
            var classSelect = document.getElementById('classSelect_' + studentId);
            var historyBtn = document.getElementById('historyBtn_' + studentId);

            var sessionId = sessionSelect ? sessionSelect.value : '';

            if (!sessionId || !termId) {
                classSelect.innerHTML = '<option value="">Select term first</option>';
                classSelect.disabled = true;
                historyBtn.disabled = true;
                return;
            }

            var classesDataDiv = document.getElementById('classesData_' + studentId);
            if (!classesDataDiv) return;

            try {
                var classesData = JSON.parse(classesDataDiv.innerHTML);
                var classes = (classesData[sessionId] && classesData[sessionId][termId]) || [];

                classSelect.innerHTML = '';
                if (classes.length > 0) {
                    classSelect.innerHTML = '<option value="">-- Select Class --</option>';
                    classes.forEach(function(cls) {
                        var option = document.createElement('option');
                        option.value = cls.id;
                        option.text = cls.name;
                        classSelect.appendChild(option);
                    });
                    classSelect.disabled = false;

                    // Auto-select first class and enable button
                    if (classes.length > 0) {
                        classSelect.value = classes[0].id;
                        historyBtn.disabled = false;
                    }
                } else {
                    classSelect.innerHTML = '<option value="">No classes available</option>';
                    classSelect.disabled = true;
                    historyBtn.disabled = true;
                }
            } catch (e) {
                console.error('Error parsing classes data:', e);
                classSelect.innerHTML = '<option value="">Error loading classes</option>';
                classSelect.disabled = true;
                historyBtn.disabled = true;
            }
        }

        // ================================================================
        // MODAL FUNCTIONS
        // ================================================================
        function openSettings() {
            document.getElementById('settingsModal').style.display = 'flex';
        }

        function closeSettings() {
            document.getElementById('settingsModal').style.display = 'none';
        }

        window.onclick = function(event) {
            var modal = document.getElementById('settingsModal');
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        }
    </script>
</body>

</html>