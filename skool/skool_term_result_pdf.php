<?php

/**
 * ============================================================================
 * SKOOL TERM RESULT PDF - SINGLE A4 PAGE OPTIMIZED (REDUCED FONTS)
 * ============================================================================
 * FIXED: Fonts reduced by 10% from current sizes, traits section visually distinct
 * Version: 6.3 (Single A4 Page - Reduced Fonts + Distinct Traits)
 * ============================================================================
 */

// ============================================================================
// Dompdf Autoload
// ============================================================================
$dompdfPath = dirname(__DIR__) . '/dompdf_New/autoload.inc.php';

if (!file_exists($dompdfPath)) {
    die("Dompdf not found at: " . $dompdfPath);
}

require_once($dompdfPath);

use Dompdf\Dompdf;
use Dompdf\Options;

$pdfPerfStart = microtime(true);

ob_start();

require_once('../config.php');

$requestedRandomId = trim((string)($_GET['randomid'] ?? ''));
$requestedStudentId = trim((string)($_GET['student_id'] ?? ''));
$requestedSessionId = (int)($_GET['session'] ?? 0);
$requestedTermId = (int)($_GET['term_id'] ?? 0);
$requestedClassId = (int)($_GET['class_id'] ?? 0);

// ============================================================================
// FIX: USE CORRECT USER IDENTIFICATION (EXACTLY MATCHES dashboard.php)
// ============================================================================
$sessionSchoolId = (int)($_SESSION['userid'] ?? 0);

// If create_by_userid is not set in session, try to get it from the user record
if ($sessionSchoolId == 0 && !empty($_SESSION['userid'])) {
    $userData = db_get_row("SELECT create_by_userid FROM users WHERE id = ?", [$_SESSION['userid']]);
    if ($userData && !empty($userData['create_by_userid'])) {
        $sessionSchoolId = (int)$userData['create_by_userid'];
    }
}

// Fallback: if still 0, use the user's own ID
if ($sessionSchoolId == 0) {
    $sessionSchoolId = (int)($_SESSION['userid'] ?? 0);
}

// ============================================================================
// GET STUDENT - USE RANDOMID APPROACH LIKE TERM RESULT
// ============================================================================
$iStudent = [];

// FIRST: Try to get student by randomid
if ($requestedRandomId !== '') {
    $randomParams = [$requestedRandomId];
    $randomQuery = "SELECT * FROM manage_student WHERE randomid = ?";

    if ($sessionSchoolId > 0) {
        $randomQuery .= " AND create_by_userid = ?";
        $randomParams[] = $sessionSchoolId;
    }

    $iStudent = db_get_row($randomQuery . " ORDER BY id DESC LIMIT 1", $randomParams);
}

// SECOND: Try by student_id (fallback)
if (empty($iStudent) && $requestedStudentId !== '') {
    $studentParams = [$requestedStudentId];
    $studentQuery = "SELECT * FROM manage_student WHERE student_id = ?";

    if ($sessionSchoolId > 0) {
        $studentQuery .= " AND create_by_userid = ?";
        $studentParams[] = $sessionSchoolId;
    }

    $iStudent = db_get_row($studentQuery . " ORDER BY id DESC LIMIT 1", $studentParams);
}

// THIRD: Try with session and term filters
if (empty($iStudent) && $requestedStudentId !== '' && $requestedSessionId > 0 && $requestedTermId > 0) {
    $studentQuery = "SELECT * FROM manage_student WHERE student_id = ?";
    $studentParams = [$requestedStudentId];

    if ($sessionSchoolId > 0) {
        $studentQuery .= " AND create_by_userid = ?";
        $studentParams[] = $sessionSchoolId;
    }

    if ($requestedSessionId > 0) {
        $studentQuery .= " AND session = ?";
        $studentParams[] = $requestedSessionId;
    }

    if ($requestedTermId > 0) {
        $studentQuery .= " AND term_id = ?";
        $studentParams[] = $requestedTermId;
    }

    if ($requestedClassId > 0) {
        $studentQuery .= " AND class = ?";
        $studentParams[] = $requestedClassId;
    }

    $iStudent = db_get_row($studentQuery . " ORDER BY id DESC LIMIT 1", $studentParams);
}

// FOURTH: Try with student_id and session only
if (empty($iStudent) && $requestedStudentId !== '' && $requestedSessionId > 0) {
    $studentParams = [$requestedStudentId, $requestedSessionId];
    $studentQuery = "SELECT * FROM manage_student WHERE student_id = ? AND session = ?";

    if ($sessionSchoolId > 0) {
        $studentQuery .= " AND create_by_userid = ?";
        $studentParams[] = $sessionSchoolId;
    }

    $iStudent = db_get_row($studentQuery . " ORDER BY id DESC LIMIT 1", $studentParams);
}

// FIFTH: Try with student_id and term only
if (empty($iStudent) && $requestedStudentId !== '' && $requestedTermId > 0) {
    $studentParams = [$requestedStudentId, $requestedTermId];
    $studentQuery = "SELECT * FROM manage_student WHERE student_id = ? AND term_id = ?";

    if ($sessionSchoolId > 0) {
        $studentQuery .= " AND create_by_userid = ?";
        $studentParams[] = $sessionSchoolId;
    }

    $iStudent = db_get_row($studentQuery . " ORDER BY id DESC LIMIT 1", $studentParams);
}

if (empty($iStudent)) {
    die("Student not found.");
}

// ============================================================================
// GET SCHOOL INFO
// ============================================================================
$SCHOOL_ID = (int)($iStudent['create_by_userid'] ?? 0);

if (empty($SCHOOL_ID)) {
    die("School not found.");
}

$iSchool = db_get_row("SELECT * FROM school_register WHERE id = ?", [$SCHOOL_ID]);

if (empty($iSchool)) {
    die("School not found.");
}

$iState = db_get_row("SELECT * FROM state WHERE id = ?", [$iSchool['state'] ?? 0]);
$statename = is_array($iState) ? ($iState['title'] ?? '') : '';

// ============================================================================
// GET SESSION, TERM, CLASS
// ============================================================================
$sessionId = $requestedSessionId > 0 ? $requestedSessionId : (int)($iStudent['session'] ?? 0);
$termId = $requestedTermId > 0 ? $requestedTermId : (int)($iStudent['term_id'] ?? 0);
$classId = $requestedClassId > 0 ? $requestedClassId : (int)($iStudent['class'] ?? 0);
$assesmentsParam = $_GET['assesments'] ?? '';

$iSession = db_get_row("SELECT * FROM school_session WHERE id = ?", [$sessionId]);
$termRow = db_get_row("SELECT * FROM school_term WHERE id = ?", [$termId]);
$iClass = db_get_row("SELECT * FROM school_class WHERE id = ?", [$classId]);

// ============================================================================
// GET ASSESSMENTS
// ============================================================================
$totalAssesment = !empty($assesmentsParam) ? explode('-', $assesmentsParam) : [];
$totalAssesment = array_values(array_filter(array_map('intval', $totalAssesment), function ($id) {
    return $id > 0;
}));

if (empty($totalAssesment)) {
    $allAssessments = db_get_rows(
        "SELECT id FROM school_assessment WHERE create_by_userid = ? AND (class_id = ? OR class_id IS NULL OR class_id = 0) ORDER BY id ASC",
        [$SCHOOL_ID, $classId]
    );
    if (is_array($allAssessments)) {
        foreach ($allAssessments as $assessRow) {
            $assessmentId = (int)($assessRow['id'] ?? 0);
            if ($assessmentId > 0) {
                $totalAssesment[] = $assessmentId;
            }
        }
    }
}

// ============================================================================
// GET PDF SETTINGS
// ============================================================================
$sectionId = is_array($iClass) ? ($iClass['section_id'] ?? 0) : 0;
$pdfSettings = [];

if ($sectionId > 0) {
    $pdfSettings = db_get_row(
        "SELECT * FROM school_pdfsetting WHERE section_id = ? AND create_by_userid = ? ORDER BY id DESC LIMIT 1",
        [$sectionId, $SCHOOL_ID]
    );

    if (!is_array($pdfSettings) || empty($pdfSettings)) {
        $pdfSettings = db_get_row(
            "SELECT * FROM school_pdfsetting WHERE create_by_userid = ? ORDER BY id DESC LIMIT 1",
            [$SCHOOL_ID]
        );
    }
}

if (!is_array($pdfSettings) || empty($pdfSettings)) {
    $pdfSettings = [
        'is_grade' => '1',
        'is_class' => '1',
        'is_position' => '1',
        'is_totalscore' => '1',
        'is_session' => '1',
        'is_finalaverage' => '1',
        'is_terms' => '1',
        'is_profilepic' => '1',
        'is_affective' => '1',
        'is_phycomotor' => '1',
        'is_grade_details' => '1',
        'is_no_of_subjects' => '1',
        'is_pos' => '1',
        'is_out' => '1',
        'is_lowest_avg' => '1',
        'is_highest_avg' => '1',
        'is_class_avg' => '1',
        'title_1' => 'Class Teacher',
        'title_2' => "Class Teacher's Remarks",
        'title_3' => "Principal's Remarks",
        'title_4' => 'AFFECTIVE TRAITS',
        'title_5' => 'PSYCHOMOTOR'
    ];
}

// Extract settings
$showProfilePic = ($pdfSettings['is_profilepic'] ?? '0') == '1';
$showClass = ($pdfSettings['is_class'] ?? '0') == '1';
$showSession = ($pdfSettings['is_session'] ?? '0') == '1';
$showTerms = ($pdfSettings['is_terms'] ?? '0') == '1';
$showPosition = ($pdfSettings['is_position'] ?? '0') == '1';
$showTotalScore = ($pdfSettings['is_totalscore'] ?? '0') == '1';
$showFinalAverage = ($pdfSettings['is_finalaverage'] ?? '0') == '1';
$showGrade = ($pdfSettings['is_grade'] ?? '0') == '1';
$showPos = ($pdfSettings['is_pos'] ?? '0') == '1';
$showOutOf = ($pdfSettings['is_out'] ?? '0') == '1';
$showLowestAvg = ($pdfSettings['is_lowest_avg'] ?? '0') == '1';
$showHighestAvg = ($pdfSettings['is_highest_avg'] ?? '0') == '1';
$showClassAvg = ($pdfSettings['is_class_avg'] ?? '0') == '1';
$showGradeDetails = ($pdfSettings['is_grade_details'] ?? '0') == '1';
$showNoOfSubjects = ($pdfSettings['is_no_of_subjects'] ?? '0') == '1';
$showAffective = ($pdfSettings['is_affective'] ?? '0') == '1';
$showPsychomotor = ($pdfSettings['is_phycomotor'] ?? '0') == '1';

$title1 = $pdfSettings['title_1'] ?? 'Class Teacher';
$title2 = $pdfSettings['title_2'] ?? "Class Teacher's Remarks";
$title3 = $pdfSettings['title_3'] ?? "Principal's Remarks";
$title4 = $pdfSettings['title_4'] ?? 'AFFECTIVE TRAITS';
$title5 = $pdfSettings['title_5'] ?? 'PSYCHOMOTOR';

// ============================================================================
// CALCULATE ASSESSMENTS MAX TOTAL
// ============================================================================
$assessmentMaxTotal = 0;
$assessmentNameMap = [];
if (!empty($totalAssesment)) {
    $assessmentPlaceholders = implode(',', array_fill(0, count($totalAssesment), '?'));
    $assessmentRows = db_get_rows(
        "SELECT id, assesment, percentage FROM school_assessment WHERE id IN ($assessmentPlaceholders) AND create_by_userid = ?",
        array_merge($totalAssesment, [$SCHOOL_ID])
    );
    if (is_array($assessmentRows)) {
        foreach ($assessmentRows as $assessmentRow) {
            $assessmentId = (int)($assessmentRow['id'] ?? 0);
            if ($assessmentId > 0) {
                $assessmentNameMap[$assessmentId] = (string)($assessmentRow['assesment'] ?? '');
            }
            $maxScore = (float)($assessmentRow['percentage'] ?? 0);
            $assessmentMaxTotal += $maxScore > 0 ? $maxScore : 100;
        }
    }
}

// ============================================================================
// GET SUBJECT DETAILS
// ============================================================================
$subjectdetail = db_get_rows("SELECT * FROM school_subject WHERE class_id = ? AND create_by_userid = ?", [$classId, $SCHOOL_ID]);
if (!is_array($subjectdetail)) $subjectdetail = [];

$iFinalScore = 0;
$subjectScoresData = [];
$subjectStats = [];

$classStudents = db_get_rows(
    "SELECT id FROM manage_student WHERE class = ? AND session = ? AND term_id = ? AND create_by_userid = ?",
    [$classId, $sessionId, $termId, $SCHOOL_ID]
);
if (!is_array($classStudents)) $classStudents = [];

$classStudentIds = [];
foreach ($classStudents as $classStudent) {
    if (is_array($classStudent) && !empty($classStudent['id'])) {
        $classStudentIds[] = (int)$classStudent['id'];
    }
}

$removedStudentMap = [];
$classScoreMap = [];
$selectedScoreMap = [];

if (!empty($subjectdetail) && !empty($classStudentIds)) {
    $subjectIds = [];
    foreach ($subjectdetail as $subjectRow) {
        $subjectId = (int)($subjectRow['id'] ?? 0);
        if ($subjectId > 0) $subjectIds[] = $subjectId;
    }

    if (!empty($subjectIds)) {
        $subjectPlaceholders = implode(',', array_fill(0, count($subjectIds), '?'));

        $removedRows = db_get_rows(
            "SELECT subjectid, studentid FROM student_subject_remove WHERE create_by_userid = ? AND subjectid IN ($subjectPlaceholders)",
            array_merge([$SCHOOL_ID], $subjectIds)
        );
        if (is_array($removedRows)) {
            foreach ($removedRows as $removedRow) {
                $removedSubjectId = (int)($removedRow['subjectid'] ?? 0);
                $removedStudentId = (int)($removedRow['studentid'] ?? 0);
                if ($removedSubjectId > 0 && $removedStudentId > 0) {
                    $removedStudentMap[$removedSubjectId][$removedStudentId] = true;
                }
            }
        }

        $classScoreRows = db_get_rows(
            "SELECT student_id, subject_id, COALESCE(SUM(score), 0) AS total_score
             FROM input_score_class_teacher
             WHERE class_id = ? AND session_id = ? AND term_id = ? AND create_by_userid = ?
             AND subject_id IN ($subjectPlaceholders)
             GROUP BY student_id, subject_id",
            array_merge([$classId, $sessionId, $termId, $SCHOOL_ID], $subjectIds)
        );
        if (is_array($classScoreRows)) {
            foreach ($classScoreRows as $scoreRow) {
                $studentId = (int)($scoreRow['student_id'] ?? 0);
                $subjectId = (int)($scoreRow['subject_id'] ?? 0);
                if ($studentId > 0 && $subjectId > 0) {
                    $classScoreMap[$subjectId][$studentId] = (float)($scoreRow['total_score'] ?? 0);
                }
            }
        }

        if (!empty($totalAssesment)) {
            $assessmentPlaceholders = implode(',', array_fill(0, count($totalAssesment), '?'));
            $selectedScoreRows = db_get_rows(
                "SELECT subject_id, assesment_id, COALESCE(SUM(score), 0) AS total_score
                 FROM input_score_class_teacher
                 WHERE student_id = ? AND class_id = ? AND session_id = ? AND term_id = ? AND create_by_userid = ?
                 AND subject_id IN ($subjectPlaceholders)
                 AND assesment_id IN ($assessmentPlaceholders)
                 GROUP BY subject_id, assesment_id",
                array_merge([$iStudent['id'], $classId, $sessionId, $termId, $SCHOOL_ID], $subjectIds, $totalAssesment)
            );
            if (is_array($selectedScoreRows)) {
                foreach ($selectedScoreRows as $scoreRow) {
                    $subjectId = (int)($scoreRow['subject_id'] ?? 0);
                    $assessmentId = (int)($scoreRow['assesment_id'] ?? 0);
                    if ($subjectId > 0 && $assessmentId > 0) {
                        $selectedScoreMap[$subjectId][$assessmentId] = (float)($scoreRow['total_score'] ?? 0);
                    }
                }
            }
        }
    }
}

foreach ($subjectdetail as $subjectRow) {
    if (!is_array($subjectRow) || empty($subjectRow['id'])) continue;

    $subjectId = (int)$subjectRow['id'];
    $removedStudentIds = $removedStudentMap[$subjectId] ?? [];

    $eligibleStudentIds = [];
    foreach ($classStudentIds as $classStudentId) {
        if (!isset($removedStudentIds[$classStudentId])) {
            $eligibleStudentIds[] = $classStudentId;
        }
    }

    $studentTotals = [];
    $classTotalForSubject = 0;
    foreach ($eligibleStudentIds as $eligibleStudentId) {
        $studentSubjectTotal = (float)($classScoreMap[$subjectId][$eligibleStudentId] ?? 0);
        $studentTotals[$eligibleStudentId] = $studentSubjectTotal;
        $classTotalForSubject += $studentSubjectTotal;
    }

    $outOfCount = count($eligibleStudentIds);
    $subjectStats[$subjectId] = [
        'rankings' => buildRankings($studentTotals),
        'out_of' => $outOfCount,
        'low' => $outOfCount > 0 ? min($studentTotals) : 0,
        'high' => $outOfCount > 0 ? max($studentTotals) : 0,
        'class_avg' => $outOfCount > 0 ? ($classTotalForSubject / $outOfCount) : 0,
    ];
}

foreach ($subjectdetail as $Ilist) {
    if (!is_array($Ilist)) continue;
    $subjectId = (int)($Ilist['id'] ?? 0);
    $subjectTotal = 0;
    $subjectScores = [];
    foreach ($totalAssesment as $Val) {
        $assessmentId = (int)$Val;
        $score = (float)($selectedScoreMap[$subjectId][$assessmentId] ?? 0);
        $subjectTotal += $score;
        $subjectScores[$assessmentId] = $score;
    }
    $subjectGradeScore = round($subjectTotal, 2);
    $currentSubjectStats = $subjectStats[$subjectId] ?? ['rankings' => [], 'out_of' => 0, 'low' => 0, 'high' => 0, 'class_avg' => 0];
    $subjectScoresData[] = [
        'subject_id' => $subjectId,
        'subject' => $Ilist['subject'],
        'scores' => $subjectScores,
        'total' => $subjectTotal,
        'grade_score' => $subjectGradeScore,
        'position' => $currentSubjectStats['rankings'][$iStudent['id']]['rank'] ?? 0,
        'out_of' => $currentSubjectStats['out_of'] ?? 0,
        'low' => $currentSubjectStats['low'] ?? 0,
        'high' => $currentSubjectStats['high'] ?? 0,
        'class_avg' => $currentSubjectStats['class_avg'] ?? 0
    ];
    $iFinalScore += $subjectTotal;
}

$iCountSubject = count($subjectdetail);
$ggrade = $iCountSubject > 0 ? $iFinalScore / $iCountSubject : 0;
$overallMaxTotal = $iCountSubject > 0 ? ($iCountSubject * $assessmentMaxTotal) : 0;
$overallGradeScore = round($ggrade, 2);
$overallGradeInfo = resolveGradeScaleInfo($SCHOOL_ID, $overallGradeScore);
$gradeLetter = $overallGradeInfo['grade'];

$classTeacherRow = db_get_row("SELECT staff_id FROM class_teacher WHERE school_class = ? AND school_session = ? AND create_by_userid = ?", [$classId, $sessionId, $SCHOOL_ID]);
$classTeacherInfo = db_get_row("SELECT first_name, last_name FROM staff_manage WHERE id = ? AND create_by_userid = ?", [is_array($classTeacherRow) ? ($classTeacherRow['staff_id'] ?? 0) : 0, $SCHOOL_ID]);
$classTeacherComment = trim((string)(db_get_val("SELECT comments FROM clas_teacher_make_comment WHERE student_id = ?", [$iStudent['id']]) ?: ''));
$principalRoleRow = db_get_row("SELECT staff_id FROM assign_role WHERE principal = '1' AND create_by_userid = ?", [$SCHOOL_ID]);
$principalInfo = db_get_row("SELECT first_name, last_name FROM staff_manage WHERE id = ? AND create_by_userid = ?", [is_array($principalRoleRow) ? ($principalRoleRow['staff_id'] ?? 0) : 0, $SCHOOL_ID]);
$principalRemark = trim((string)(db_get_val("SELECT comments FROM principle_remarks WHERE student_id = ?", [$iStudent['id']]) ?: ''));
$nextTermRow = db_get_row("SELECT nextTerm FROM principal_set_nextTerm WHERE create_by_userid = ? AND session_id = ? AND term_id = ? ORDER BY id DESC", [$SCHOOL_ID, $sessionId, $termId]);
$gradeDetailsRows = db_get_rows("SELECT grade, minimum_number, maximum_number FROM school_grade WHERE create_by_userid = ? ORDER BY id DESC", [$SCHOOL_ID]);

$affectiveRows = [];
$psychomotorRows = [];
$affectiveRatings = [];
$psychomotorRatings = [];

if ($showAffective) {
    $affectiveRows = db_get_rows("SELECT id, trait FROM manage_traits WHERE create_by_userid = ? ORDER BY id ASC", [$SCHOOL_ID]);
    $studentAffectiveRatings = db_get_rows("SELECT traits_id, trait FROM student_traits_class_teacher WHERE session_id = ? AND term_id = ? AND class_id = ? AND student_id = ? AND create_by_userid = ?", [$sessionId, $termId, $classId, $iStudent['id'], $SCHOOL_ID]);
    if (is_array($studentAffectiveRatings)) {
        foreach ($studentAffectiveRatings as $ratingRow) {
            $affectiveRatings[(int)($ratingRow['traits_id'] ?? 0)] = (string)($ratingRow['trait'] ?? '');
        }
    }
}

if ($showPsychomotor) {
    $psychomotorRows = db_get_rows("SELECT id, phycomotor FROM manage_phycomotor WHERE create_by_userid = ? ORDER BY id ASC", [$SCHOOL_ID]);
    $studentPsychomotorRatings = db_get_rows("SELECT pyschmotor_id, pyschmotor FROM student_pyschomotor_class_teacher WHERE session_id = ? AND term_id = ? AND class_id = ? AND student_id = ? AND create_by_userid = ?", [$sessionId, $termId, $classId, $iStudent['id'], $SCHOOL_ID]);
    if (is_array($studentPsychomotorRatings)) {
        foreach ($studentPsychomotorRatings as $ratingRow) {
            $psychomotorRatings[(int)($ratingRow['pyschmotor_id'] ?? 0)] = (string)($ratingRow['pyschmotor'] ?? '');
        }
    }
}

$positionQuery = "SELECT student_id, SUM(score) as total FROM input_score_class_teacher WHERE class_id = ? AND session_id = ? AND term_id = ? AND create_by_userid = ? GROUP BY student_id";
$allStudentScores = db_get_rows($positionQuery, [$classId, $sessionId, $termId, $SCHOOL_ID]);
$scores = [];
if (is_array($allStudentScores)) {
    foreach ($allStudentScores as $s) {
        $scores[$s['student_id']] = $s['total'];
    }
}
arsort($scores);
$position = 1;
$studentPosition = 0;
foreach ($scores as $sid => $total) {
    if ($sid == $iStudent['id']) {
        $studentPosition = $position;
        break;
    }
    $position++;
}

$tSub = count($subjectdetail);

// ============================================================================
// DETERMINE IF WE NEED TO SPLIT THE TABLE
// ============================================================================
$totalRows = count($subjectScoresData);
$totalColumns = 1 + count($totalAssesment) + 2 +
    ($showPos ? 1 : 0) + ($showOutOf ? 1 : 0) +
    ($showLowestAvg ? 1 : 0) + ($showHighestAvg ? 1 : 0) +
    ($showClassAvg ? 1 : 0);

// ============================================================================
// ABSOLUTE FILE PATHS FOR IMAGES
// ============================================================================
$uploadsPathCandidates = [];
$documentRoot = str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT'] ?? '');
if (!empty($documentRoot)) {
    $uploadsPathCandidates[] = rtrim($documentRoot, '/') . '/bestschoolpage/uploads/';
}
$uploadsPathCandidates[] = str_replace('\\', '/', dirname(__DIR__) . '/uploads/');

function toFileUri($path)
{
    $cleanPath = str_replace('\\', '/', $path);
    if (preg_match('/^[A-Za-z]:\//', $cleanPath)) {
        return 'file:///' . $cleanPath;
    }
    return 'file://' . $cleanPath;
}

function getMimeTypeFromPath($path)
{
    $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
    if ($ext === 'png') return 'image/png';
    if ($ext === 'jpg' || $ext === 'jpeg') return 'image/jpeg';
    if ($ext === 'gif') return 'image/gif';
    if ($ext === 'webp') return 'image/webp';
    if ($ext === 'svg') return 'image/svg+xml';
    return 'application/octet-stream';
}

function fileToDataUri($path)
{
    static $dataUriCache = [];
    $cacheKey = str_replace('\\', '/', (string)$path);
    if (array_key_exists($cacheKey, $dataUriCache)) {
        return $dataUriCache[$cacheKey];
    }
    if (!is_file($path) || !is_readable($path)) {
        $dataUriCache[$cacheKey] = '';
        return '';
    }
    $raw = @file_get_contents($path);
    if ($raw === false || $raw === '') {
        $dataUriCache[$cacheKey] = '';
        return '';
    }
    $mime = getMimeTypeFromPath($path);
    $dataUriCache[$cacheKey] = 'data:' . $mime . ';base64,' . base64_encode($raw);
    return $dataUriCache[$cacheKey];
}

function buildRankings($standings)
{
    $rankings = [];
    arsort($standings);
    $rank = 1;
    $tieRank = 0;
    $prevScore = null;
    $count = 0;
    foreach ($standings as $studentId => $score) {
        if ($prevScore === null || (float)$score !== (float)$prevScore) {
            $count = 0;
            $prevScore = $score;
            $rankings[$studentId] = ['score' => $score, 'rank' => $rank];
        } else {
            if ($count++ === 0) {
                $tieRank = $rank - 1;
            }
            $rankings[$studentId] = ['score' => $score, 'rank' => $tieRank];
        }
        $rank++;
    }
    return $rankings;
}

function formatOrdinal($number)
{
    $number = (int)$number;
    if ($number <= 0) return '-';
    $lastTwo = $number % 100;
    if ($lastTwo >= 11 && $lastTwo <= 13) return $number . 'th';
    switch ($number % 10) {
        case 1:
            return $number . 'st';
        case 2:
            return $number . 'nd';
        case 3:
            return $number . 'rd';
        default:
            return $number . 'th';
    }
}

function resolveGradeScaleInfo($schoolId, $score)
{
    $score = round((float)$score, 2);
    static $rangeCacheBySchool = [];
    if (!isset($rangeCacheBySchool[(int)$schoolId])) {
        $rangeCacheBySchool[(int)$schoolId] = db_get_rows(
            "SELECT grade, description, comment, minimum_number, maximum_number
             FROM school_grade
             WHERE create_by_userid = ?
             AND minimum_number <= maximum_number
             ORDER BY minimum_number DESC",
            [$schoolId]
        );
    }
    $ranges = $rangeCacheBySchool[(int)$schoolId];
    if (empty($ranges)) {
        return [
            'grade' => ($score >= 70 ? 'A' : ($score >= 60 ? 'B' : ($score >= 50 ? 'C' : ($score >= 45 ? 'D' : ($score >= 40 ? 'E' : 'F'))))),
            'description' => '',
            'comment' => '',
            'score' => $score,
        ];
    }
    foreach ($ranges as $range) {
        $min = (float)($range['minimum_number'] ?? 0);
        $max = (float)($range['maximum_number'] ?? 100);
        if ($score >= $min && $score <= $max) {
            return [
                'grade'       => trim((string)$range['grade']),
                'description' => trim((string)($range['description'] ?? '')),
                'comment'     => trim((string)($range['comment'] ?? '')),
                'score'       => $score,
            ];
        }
    }
    $fallback = null;
    foreach ($ranges as $range) {
        if (trim((string)($range['grade'] ?? '')) === '') continue;
        $fallback = $range;
        break;
    }
    return [
        'grade' => ($score >= 70 ? 'A' : ($score >= 60 ? 'B' : ($score >= 50 ? 'C' : ($score >= 45 ? 'D' : ($score >= 40 ? 'E' : 'F'))))),
        'description' => trim((string)($fallback['description'] ?? '')),
        'comment' => trim((string)($fallback['comment'] ?? '')),
        'score' => $score,
    ];
}

function getImagePath($filename)
{
    global $uploadsPathCandidates;
    $filename = trim((string)$filename);
    if ($filename === '') return '';
    static $resolvedImageCache = [];
    $filename = rawurldecode($filename);
    if (array_key_exists($filename, $resolvedImageCache)) {
        return $resolvedImageCache[$filename];
    }
    if (strpos($filename, 'data:image/') === 0) {
        $resolvedImageCache[$filename] = $filename;
        return $filename;
    }
    $remoteUrl = '';
    if (preg_match('#^https?://#i', $filename)) {
        $remoteUrl = $filename;
    }
    $normalized = str_replace('\\', '/', $filename);
    $parsedPath = (string)(parse_url($normalized, PHP_URL_PATH) ?? '');
    if ($parsedPath !== '') $normalized = $parsedPath;

    $candidates = [];
    $candidates[] = ltrim($normalized, '/');
    if (stripos($normalized, '/uploads/') !== false) {
        $afterUploads = preg_replace('#^.*?/uploads/#i', '', $normalized);
        if (!empty($afterUploads)) $candidates[] = ltrim($afterUploads, '/');
    }
    $baseName = basename($normalized);
    if ($baseName !== '' && $baseName !== '.' && $baseName !== '..') $candidates[] = $baseName;
    $candidates = array_values(array_unique(array_filter($candidates)));

    $extraBasePaths = [];
    $docRoot = str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT'] ?? '');
    if ($docRoot !== '') {
        $extraBasePaths[] = rtrim($docRoot, '/') . '/uploads/';
        $extraBasePaths[] = rtrim($docRoot, '/') . '/bestschoolpage/uploads/';
    }
    $basePaths = array_merge($uploadsPathCandidates, $extraBasePaths);

    foreach ($candidates as $candidate) {
        foreach ($basePaths as $basePath) {
            $fullPath = rtrim($basePath, '/') . '/' . ltrim($candidate, '/');
            if (is_file($fullPath)) {
                $dataUri = fileToDataUri($fullPath);
                if ($dataUri !== '') {
                    $resolvedImageCache[$filename] = $dataUri;
                    return $dataUri;
                }
                $resolvedImageCache[$filename] = toFileUri($fullPath);
                return $resolvedImageCache[$filename];
            }
        }
    }
    if ($remoteUrl !== '') {
        $resolvedImageCache[$filename] = $remoteUrl;
        return $remoteUrl;
    }
    $resolvedImageCache[$filename] = '';
    return '';
}

// ============================================================================
// SINGLE A4 PAGE - FONTS REDUCED BY 10% FROM CURRENT SIZES
// ============================================================================
$paperSize = 'A4';
$pageMargin = '5.5mm';

// Font sizes reduced by 10% from current values
$baseFont = '9.8pt';        // Was 10.9pt, reduced by 10%
$lineHeight = '1.25';
$headerNameSize = '15.5pt'; // Was 17.25pt, reduced by 10%
$tableFont = '8.1pt';       // Was 8.97pt, reduced by 10%
$tablePadding = '2.1px';    // Was 2.3px, reduced by 10%
$logoMax = '67.3px';        // Was 74.75px, reduced by 10%
$photoSize = '62.1px';      // Was 69px, reduced by 10%
$traitsMarginTop = '4.1px'; // Was 4.6px, reduced by 10%

// Adjust for more subjects
if ($totalRows > 20) {
    $tableFont = '7.25pt';      // Was 8.05pt, reduced by 10%
    $baseFont = '9.3pt';        // Was 10.35pt, reduced by 10%
    $tablePadding = '1.55px';   // Was 1.73px, reduced by 10%
}

if ($totalRows > 30) {
    $tableFont = '7.0pt';       // Was 7.82pt, reduced by 10%
    $baseFont = '8.8pt';        // Was 9.78pt, reduced by 10%
    $tablePadding = '1.25px';   // Was 1.38px, reduced by 10%
    $headerNameSize = '13.5pt'; // Was 14.95pt, reduced by 10%
    $logoMax = '56.9px';        // Was 63.25px, reduced by 10%
    $photoSize = '51.8px';      // Was 57.5px, reduced by 10%
}

// If very few subjects, use larger fonts but still reduced
if ($totalRows <= 8 && $totalColumns <= 10) {
    $tableFont = '9.3pt';       // Was 10.35pt, reduced by 10%
    $baseFont = '10.9pt';       // Was 12.08pt, reduced by 10%
    $pageMargin = '6.8mm';
    $headerNameSize = '17.6pt'; // Was 19.55pt, reduced by 10%
    $logoMax = '77.6px';        // Was 86.25px, reduced by 10%
    $photoSize = '72.5px';      // Was 80.5px, reduced by 10%
}

// Apply the 10% reduction to these as well (they were already increased by 15% in the original)
$logoMax = round((float)$logoMax * 1.0, 2) . 'px';
$photoSize = round((float)$photoSize * 1.0, 2) . 'px';

$principalSignaturePath = '';
$signTermRow = db_get_row("SELECT sign FROM principal_sign_nextTerm WHERE create_by_userid = ? ORDER BY id DESC", [$SCHOOL_ID]);
if (is_array($signTermRow) && !empty($signTermRow['sign'])) {
    $principalSignaturePath = getImagePath($signTermRow['sign']);
}

$schoolLogoPath = getImagePath($iSchool['logo'] ?? '');
$studentPhotoPath = ($showProfilePic && !empty($iStudent['picture'])) ? getImagePath($iStudent['picture']) : '';
?>
<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Student Report Sheet</title>
    <style>
        @page {
            size: <?= $paperSize ?> portrait;
            margin: <?= $pageMargin ?>;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 0;
            font-family: 'DejaVu Sans', 'Segoe UI', Arial, sans-serif;
            font-size: <?= $baseFont ?>;
            line-height: <?= $lineHeight ?>;
            color: #222;
            background: white;
        }

        .sheet {
            width: 100%;
            position: relative;
            z-index: 2;
        }

        /* WATERMARK BACKGROUND */
        .watermark {
            position: fixed;
            top: 40%;
            left: 50%;
            transform: translate(-50%, -30%);
            z-index: -1000;
            opacity: 0.1;
            text-align: center;
            width: 100%;
        }

        .watermark img {
            width: 300px;
            height: auto;
        }

        /* HEADER */
        .header {
            margin-bottom: 3px;
            padding-bottom: 2px;
        }

        .header-table {
            width: 100%;
            border-collapse: collapse;
        }

        .header-table td {
            border: none;
            vertical-align: middle;
            padding: 1px 2px;
        }

        .logo-cell {
            width: <?= $logoMax ?>;
            text-align: left;
        }

        .logo-img {
            max-width: <?= $logoMax ?>;
            max-height: <?= $logoMax ?>;
            object-fit: contain;
        }

        .school-info-cell {
            text-align: center;
        }

        .school-name {
            font-size: <?= $headerNameSize ?>;
            font-weight: bold;
            color: #1B3058;
            margin-bottom: 1px;
            letter-spacing: 0.5px;
        }

        .school-moto {
            font-size: 7.7pt;
            color: #555;
            font-style: italic;
        }

        .school-address {
            font-size: 7.0pt;
            color: #444;
        }

        .report-title {
            font-size: 8.8pt;
            font-weight: bold;
            margin-top: 2px;
            color: #1B3058;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .photo-cell {
            width: <?= $photoSize ?>;
            text-align: right;
        }

        .student-photo {
            width: <?= $photoSize ?>;
            height: <?= $photoSize ?>;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #1B3058;
        }

        /* INFO GRID */
        .info-grid {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 2px;
            background: #f8fafc;
            border: 1px solid #dde4ee;
            font-size: 8.1pt;
        }

        .info-grid td {
            border: 1px solid #eef3f8;
            padding: 2px 5px;
            vertical-align: middle;
        }

        .info-label {
            font-weight: bold;
            color: #444;
            width: 24%;
            font-size: 7.7pt;
        }

        .info-value {
            color: #1B3058;
            width: 76%;
            font-size: 8.3pt;
            font-weight: 500;
        }

        .left-column {
            border-right: 1px solid #ddd;
        }

        /* TERM BADGES */
        .term-badges {
            width: 100%;
            border-collapse: collapse;
            margin: 2px 0 2px 0;
        }

        .term-badges td {
            width: 25%;
            border: none;
            padding: 0 2px 0 0;
            vertical-align: top;
        }

        .term-badge {
            border: 1px solid #d8e0ec;
            background: #f4f7fc;
            border-radius: 3px;
            padding: 2px 3px;
            text-align: center;
        }

        .term-badge-label {
            font-size: 6.0pt;
            color: #6d7787;
            text-transform: uppercase;
            margin-bottom: 1px;
            line-height: 1;
            letter-spacing: 0.3px;
        }

        .term-badge-value {
            font-size: 9.3pt;
            font-weight: bold;
            color: #1B3058;
            line-height: 1.2;
        }

        /* SUBJECTS TABLE */
        .subjects-table {
            page-break-inside: avoid;
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 2px;
            font-size: <?= $tableFont ?>;
            table-layout: auto;
        }

        .subjects-table thead th {
            background: #1B3058 !important;
            color: white !important;
            padding: <?= $tablePadding ?>;
            border: 1px solid #2a4780;
            font-weight: bold;
            text-align: center;
            font-size: <?= $tableFont ?>;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        .subjects-table td {
            border: 1px solid #ddd;
            padding: <?= $tablePadding ?>;
            text-align: center;
            overflow: hidden;
            text-overflow: ellipsis;
            font-size: <?= $tableFont ?>;
        }

        .subjects-table .subject-name {
            text-align: left;
            font-weight: 600;
            background: #f5f7fa;
            white-space: normal;
            font-size: <?= $tableFont ?>;
            padding-left: 4px;
        }

        .subjects-table .remark-col {
            text-align: left;
            white-space: normal;
            font-size: <?= $tableFont ?>;
            padding-left: 3px;
        }

        .total-cell {
            font-weight: bold;
            background: #e8f0fe;
            color: #1B3058;
        }

        .grade-cell {
            font-weight: bold;
            background: #e3f2fd;
            color: #0d47a1;
        }

        .position-cell {
            font-weight: bold;
            background: #fff3e0;
            color: #e65100;
        }

        /* GRADE DETAILS */
        .grade-details {
            margin-bottom: 2px;
            padding: 2px 5px;
            background: #f9fafb;
            border: 1px solid #e3e9f2;
            font-size: 6.8pt;
            border-radius: 2px;
        }

        /* REMARKS SECTION */
        .remarks-section {
            margin-top: 2px;
            border-top: 1px solid #ddd;
            padding-top: 2px;
        }

        .remarks-table {
            width: 100%;
            border-collapse: collapse;
        }

        .remarks-table td {
            border: none;
            padding: 1px 3px;
            vertical-align: top;
        }

        .remarks-label {
            font-weight: bold;
            width: 16%;
            color: #444;
            font-size: 7.7pt;
            white-space: nowrap;
        }

        .remarks-value {
            width: 84%;
            font-size: 8.3pt;
            line-height: 1.3;
        }

        /* ============================================================================
         * TRAITS - VISUALLY DISTINCT WITH COLOR AND STYLING
         * ============================================================================ */
        .traits-container {
            margin-top: <?= $traitsMarginTop ?>;
            padding: 4px 8px;
            background: #e8f0fe;
            border: 2px solid #1B3058;
            border-radius: 4px;
            font-size: 7.5pt;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .traits-container .traits-header {
            font-weight: bold;
            color: #1B3058;
            font-size: 7.7pt;
            margin-bottom: 2px;
            border-bottom: 1px solid #1B3058;
            padding-bottom: 2px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .traits-container .traits-row {
            display: inline-block;
            width: 100%;
            margin: 2px 0;
        }

        .traits-container .traits-label {
            font-weight: bold;
            color: #0d47a1;
            display: inline-block;
            min-width: 110px;
            vertical-align: top;
            font-size: 7.8pt;
            background: #dce8f5;
            padding: 0 4px;
            border-radius: 2px;
        }

        .traits-container .traits-items {
            display: inline-block;
            vertical-align: top;
            max-width: 89%;
        }

        .traits-container .trait-item {
            display: inline-block;
            margin: 0 4.1px 1px 0;
            padding: 0 3.2px;
            border-right: 1px solid #b0c4de;
            font-size: 7.0pt;
        }

        .traits-container .trait-item:last-child {
            border-right: none;
        }

        .traits-container .trait-name {
            color: #333;
        }

        .traits-container .trait-rating {
            font-weight: bold;
            color: #1B3058;
            text-transform: capitalize;
            margin-left: 2px;
            padding: 0 3px;
            background: #fff;
            border-radius: 2px;
        }

        .traits-container .trait-rating.excellent {
            color: #1B8A1B;
            background: #e8f5e9;
        }

        .traits-container .trait-rating.good {
            color: #2E7D32;
            background: #e8f5e9;
        }

        .traits-container .trait-rating.fair {
            color: #F9A825;
            background: #fff8e1;
        }

        .traits-container .trait-rating.poor {
            color: #C62828;
            background: #ffebee;
        }

        .traits-container .traits-divider {
            border-top: 2px solid #b0c4de;
            margin: 3px 0;
        }

        /* SIGNATURE SECTION */
        .signature-wrapper {
            text-align: right;
            margin-top: 3px;
            padding-top: 2px;
            border-top: 1px solid #ddd;
        }

        .signature-wrapper .sign-content {
            display: inline-block;
            text-align: center;
            min-width: 150px;
        }

        .signature-wrapper .sign-img {
            max-width: 130px;
            max-height: 38px;
            object-fit: contain;
            display: block;
            margin: 0 auto;
        }

        .signature-wrapper .sign-label {
            font-size: 7.0pt;
            color: #444;
            margin-top: 1px;
            font-weight: 500;
        }

        .signature-wrapper .next-term {
            font-size: 7.0pt;
            margin-top: 2px;
            color: #333;
        }

        @media print {
            body {
                background: white;
            }

            .subjects-table th {
                background: #1B3058 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .traits-container {
                background: #e8f0fe !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
    </style>
</head>

<body>

    <?php if (!empty($schoolLogoPath)): ?>
        <div class="watermark">
            <img src="<?php echo '../uploads/' . (isset($iSchool['logo']) ? $iSchool['logo'] : ''); ?>" style="width: 300px; height: auto;" />
        </div>
    <?php endif; ?>

    <div class="sheet">

        <!-- HEADER -->
        <div class="header">
            <table class="header-table">
                <tr>
                    <td class="logo-cell">
                        <?php if (!empty($schoolLogoPath)): ?>
                            <img class="logo-img" src="<?php echo $schoolLogoPath; ?>" alt="School Logo">
                        <?php endif; ?>
                    </td>
                    <td class="school-info-cell">
                        <div class="school-name"><?php echo htmlspecialchars($iSchool['name'] ?? 'School Name'); ?></div>
                        <div class="school-moto"><?php echo htmlspecialchars($iSchool['moto'] ?? 'Excellence in Education'); ?></div>
                        <div class="school-address"><?php echo htmlspecialchars($iSchool['location'] ?? ''); ?><?php echo !empty($statename) ? ', ' . htmlspecialchars($statename) : ''; ?></div>
                        <div class="report-title">REPORT SHEET FOR <?php echo htmlspecialchars($termRow['term'] ?? 'TERM'); ?> <?php echo htmlspecialchars($iSession['session'] ?? 'SESSION'); ?> ACADEMIC SESSION</div>
                    </td>
                    <td class="photo-cell">
                        <?php if (!empty($studentPhotoPath)): ?>
                            <img class="student-photo" src="<?php echo $studentPhotoPath; ?>" alt="Student Photo">
                        <?php endif; ?>
                    </td>
                </tr>
            </table>
        </div>

        <!-- STUDENT INFO -->
        <table class="info-grid">
            <tr>
                <td class="left-column">
                    <table style="width:100%; border-collapse:collapse;">
                        <tr>
                            <td class="info-label">NAME:</td>
                            <td class="info-value"><?php echo htmlspecialchars(($iStudent['first_name'] ?? '') . ' ' . ($iStudent['last_name'] ?? '')); ?></td>
                        </tr>
                        <tr>
                            <td class="info-label">ADMISSION NO:</td>
                            <td class="info-value"><?php echo htmlspecialchars($iStudent['student_id'] ?? 'N/A'); ?></td>
                        </tr>
                        <?php if ($showClass): ?>
                            <tr>
                                <td class="info-label">CLASS:</td>
                                <td class="info-value"><?php echo htmlspecialchars($iClass['name'] ?? 'N/A'); ?></td>
                            </tr>
                        <?php endif; ?>
                        <?php if ($showTerms): ?>
                            <tr>
                                <td class="info-label">TERM:</td>
                                <td class="info-value"><?php echo htmlspecialchars($termRow['term'] ?? 'N/A'); ?></td>
                            </tr>
                        <?php endif; ?>
                    </table>
                </td>
                <td>
                    <table style="width:100%; border-collapse:collapse;">
                        <?php if ($showSession): ?>
                            <tr>
                                <td class="info-label">SESSION:</td>
                                <td class="info-value"><?php echo htmlspecialchars($iSession['session'] ?? 'N/A'); ?></td>
                            </tr>
                        <?php endif; ?>
                        <?php if ($showTotalScore): ?>
                            <tr>
                                <td class="info-label">TOTAL SCORE:</td>
                                <td class="info-value"><strong><?php echo number_format($iFinalScore, 2); ?></strong></td>
                            </tr>
                        <?php endif; ?>
                        <?php if ($showGrade): ?>
                            <tr>
                                <td class="info-label">GRADE:</td>
                                <td class="info-value"><strong><?php echo htmlspecialchars($gradeLetter); ?></strong></td>
                            </tr>
                        <?php endif; ?>
                        <?php if ($showFinalAverage): ?>
                            <tr>
                                <td class="info-label">AVERAGE:</td>
                                <td class="info-value"><strong><?php echo number_format($ggrade, 2); ?></strong></td>
                            </tr>
                        <?php endif; ?>
                        <?php if ($showPosition): ?>
                            <tr>
                                <td class="info-label">POSITION:</td>
                                <td class="info-value"><strong><?php echo $studentPosition; ?></strong></td>
                            </tr>
                        <?php endif; ?>
                    </table>
                </td>
            </tr>
        </table>

        <!-- TERM BADGES -->
        <table class="term-badges">
            <tr>
                <td>
                    <div class="term-badge">
                        <div class="term-badge-label">Total Score</div>
                        <div class="term-badge-value"><?php echo number_format($iFinalScore, 0); ?></div>
                    </div>
                </td>
                <td>
                    <div class="term-badge">
                        <div class="term-badge-label">Average</div>
                        <div class="term-badge-value"><?php echo number_format($ggrade, 2); ?></div>
                    </div>
                </td>
                <td>
                    <div class="term-badge">
                        <div class="term-badge-label">Grade</div>
                        <div class="term-badge-value"><?php echo htmlspecialchars($gradeLetter); ?></div>
                    </div>
                </td>
                <?php if ($showPosition): ?>
                    <td>
                        <div class="term-badge">
                            <div class="term-badge-label">Position</div>
                            <div class="term-badge-value"><?php echo $studentPosition > 0 ? $studentPosition : '-'; ?></div>
                        </div>
                    </td>
                <?php endif; ?>
            </tr>
        </table>

        <!-- SUBJECTS TABLE -->
        <?php
        $colspan = 1 + count($totalAssesment) + 2;
        if ($showPos) $colspan++;
        if ($showOutOf) $colspan++;
        if ($showLowestAvg) $colspan++;
        if ($showHighestAvg) $colspan++;
        if ($showClassAvg) $colspan++;
        $colspan++;
        ?>

        <table class="subjects-table">
            <colgroup>
                <col style="width:<?php echo $totalColumns > 13 ? '14%' : '17%'; ?>;">
                <?php foreach ($totalAssesment as $Val): ?>
                    <col style="width:<?php echo $totalColumns > 13 ? '3.5%' : '4.5%'; ?>;">
                <?php endforeach; ?>
                <col style="width:<?php echo $totalColumns > 13 ? '4.5%' : '5.5%'; ?>;">
                <col style="width:<?php echo $totalColumns > 13 ? '3.5%' : '4.5%'; ?>;">
                <?php if ($showPos): ?>
                    <col style="width:4%;"><?php endif; ?>
                <?php if ($showOutOf): ?>
                    <col style="width:4%;"><?php endif; ?>
                <?php if ($showLowestAvg): ?>
                    <col style="width:4.5%;"><?php endif; ?>
                <?php if ($showHighestAvg): ?>
                    <col style="width:4.5%;"><?php endif; ?>
                <?php if ($showClassAvg): ?>
                    <col style="width:5.5%;"><?php endif; ?>
                <col style="width:<?php echo $totalColumns > 13 ? '11%' : '13%'; ?>;">
            </colgroup>
            <thead>
                <tr>
                    <th>SUBJECT</th>
                    <?php foreach ($totalAssesment as $Val):
                        $assName = $assessmentNameMap[(int)$Val] ?? '';
                    ?>
                        <th><?php echo htmlspecialchars($assName ?: 'CA'); ?></th>
                    <?php endforeach; ?>
                    <th>TOTAL</th>
                    <th>GRD</th>
                    <?php if ($showPos): ?><th>POS</th><?php endif; ?>
                    <?php if ($showOutOf): ?><th>OUT</th><?php endif; ?>
                    <?php if ($showLowestAvg): ?><th>LOW</th><?php endif; ?>
                    <?php if ($showHighestAvg): ?><th>HIGH</th><?php endif; ?>
                    <?php if ($showClassAvg): ?><th>AVG</th><?php endif; ?>
                    <th>REMARKS</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($subjectScoresData)): ?>
                    <tr>
                        <td colspan="<?= $colspan ?>" style="text-align:center; padding:3px;">No subject score data found for this student.</td>
                    </tr>
                <?php endif; ?>
                <?php foreach ($subjectScoresData as $data):
                    $subjectTotal = $data['total'];
                    $subjectGradeScore = (float)($data['grade_score'] ?? $subjectTotal);
                    $subjectGradeInfo = resolveGradeScaleInfo($SCHOOL_ID, $subjectGradeScore);
                    $subjectGrade = $subjectGradeInfo['grade'];
                    $subjectRemark = $subjectGradeInfo['description'] ?: '-';
                ?>
                    <tr>
                        <td class="subject-name"><?php echo htmlspecialchars($data['subject']); ?></td>
                        <?php foreach ($totalAssesment as $Val): ?>
                            <td><?php echo $data['scores'][$Val] ?? 0; ?></td>
                        <?php endforeach; ?>
                        <td class="total-cell"><?php echo round($subjectTotal); ?></td>
                        <td class="grade-cell"><strong><?php echo $subjectGrade; ?></strong></td>
                        <?php if ($showPos): ?><td class="position-cell"><?php echo htmlspecialchars(formatOrdinal((int)($data['position'] ?? 0))); ?></td><?php endif; ?>
                        <?php if ($showOutOf): ?><td><?php echo (int)($data['out_of'] ?? 0); ?></td><?php endif; ?>
                        <?php if ($showLowestAvg): ?><td><?php echo round((float)($data['low'] ?? 0), 1); ?></td><?php endif; ?>
                        <?php if ($showHighestAvg): ?><td><?php echo round((float)($data['high'] ?? 0), 1); ?></td><?php endif; ?>
                        <?php if ($showClassAvg): ?><td><?php echo round((float)($data['class_avg'] ?? 0), 1); ?></td><?php endif; ?>
                        <td class="remark-col"><?php echo htmlspecialchars($subjectRemark); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- ============================================================================
        TRAITS - VISUALLY DISTINCT WITH COLOR AND STYLING
        ============================================================================ -->
        <?php if ($showAffective || $showPsychomotor): ?>
            <div class="traits-container">
                <div class="traits-header">STUDENT TRAITS & SKILLS ASSESSMENT</div>

                <?php if ($showAffective && !empty($affectiveRows)): ?>
                    <div class="traits-row">
                        <span class="traits-label"><?php echo htmlspecialchars($title4); ?>:</span>
                        <span class="traits-items">
                            <?php
                            $affectiveCount = 0;
                            foreach ($affectiveRows as $traitRow):
                                $traitId = (int)($traitRow['id'] ?? 0);
                                $rating = $affectiveRatings[$traitId] ?? '-';
                                $ratingClass = '';
                                $ratingLower = strtolower($rating);
                                if (in_array($ratingLower, ['excellent', 'outstanding'])) $ratingClass = 'excellent';
                                elseif (in_array($ratingLower, ['good', 'very good', 'great'])) $ratingClass = 'good';
                                elseif (in_array($ratingLower, ['fair', 'average', 'satisfactory'])) $ratingClass = 'fair';
                                elseif (in_array($ratingLower, ['poor', 'needs improvement', 'unsatisfactory'])) $ratingClass = 'poor';
                                $affectiveCount++;
                            ?>
                                <span class="trait-item">
                                    <span class="trait-name"><?php echo htmlspecialchars($traitRow['trait'] ?? ''); ?>:</span>
                                    <span class="trait-rating <?php echo $ratingClass; ?>"><?php echo htmlspecialchars($rating); ?></span>
                                </span>
                            <?php endforeach; ?>
                        </span>
                    </div>
                <?php endif; ?>

                <?php if ($showAffective && !empty($affectiveRows) && $showPsychomotor && !empty($psychomotorRows)): ?>
                    <div class="traits-divider"></div>
                <?php endif; ?>

                <?php if ($showPsychomotor && !empty($psychomotorRows)): ?>
                    <div class="traits-row">
                        <span class="traits-label"><?php echo htmlspecialchars($title5); ?>:</span>
                        <span class="traits-items">
                            <?php
                            foreach ($psychomotorRows as $skillRow):
                                $skillId = (int)($skillRow['id'] ?? 0);
                                $rating = $psychomotorRatings[$skillId] ?? '-';
                                $ratingClass = '';
                                $ratingLower = strtolower($rating);
                                if (in_array($ratingLower, ['excellent', 'outstanding'])) $ratingClass = 'excellent';
                                elseif (in_array($ratingLower, ['good', 'very good', 'great'])) $ratingClass = 'good';
                                elseif (in_array($ratingLower, ['fair', 'average', 'satisfactory'])) $ratingClass = 'fair';
                                elseif (in_array($ratingLower, ['poor', 'needs improvement', 'unsatisfactory'])) $ratingClass = 'poor';
                            ?>
                                <span class="trait-item">
                                    <span class="trait-name"><?php echo htmlspecialchars($skillRow['phycomotor'] ?? ''); ?>:</span>
                                    <span class="trait-rating <?php echo $ratingClass; ?>"><?php echo htmlspecialchars($rating); ?></span>
                                </span>
                            <?php endforeach; ?>
                        </span>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <!-- GRADE DETAILS -->
        <?php if ($showGradeDetails || $showNoOfSubjects): ?>
            <div class="grade-details">
                <?php if ($showGradeDetails): ?>
                    <strong>GRADE DETAILS:</strong>
                    <?php
                    if (is_array($gradeDetailsRows)) {
                        $gradeText = [];
                        foreach ($gradeDetailsRows as $iList) {
                            $gradeText[] = ($iList['grade'] ?? '') . ' = ' . ($iList['minimum_number'] ?? 0) . '-' . ($iList['maximum_number'] ?? 0);
                        }
                        echo implode(', ', $gradeText);
                    }
                    ?>
                <?php endif; ?>
                <?php if ($showNoOfSubjects): ?>
                    &nbsp;|&nbsp; <strong>NO. OF SUBJECTS:</strong> <?php echo $tSub; ?>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <!-- REMARKS SECTION -->
        <div class="remarks-section">
            <table class="remarks-table">
                <tr>
                    <td class="remarks-label"><?php echo htmlspecialchars($title1); ?>:</td>
                    <td class="remarks-value"><?php echo htmlspecialchars((is_array($classTeacherInfo) ? trim(($classTeacherInfo['first_name'] ?? '') . ' ' . ($classTeacherInfo['last_name'] ?? '')) : 'N/A')); ?></td>
                </tr>
                <tr>
                    <td class="remarks-label"><?php echo htmlspecialchars($title2); ?>:</td>
                    <td class="remarks-value"><?php echo htmlspecialchars($classTeacherComment !== '' ? $classTeacherComment : 'No remarks entered.'); ?></td>
                </tr>
                <tr>
                    <td class="remarks-label"><?php echo htmlspecialchars($title3); ?>:</td>
                    <td class="remarks-value">
                        <?php
                        if (!empty($principalRemark)) {
                            echo htmlspecialchars($principalRemark);
                        } else {
                            $defaultComment = $overallGradeInfo['comment'] ?? '';
                            echo htmlspecialchars($defaultComment !== '' ? $defaultComment : 'Keep up the good work!');
                        }
                        ?>
                    </td>
                </tr>
            </table>
        </div>

        <!-- SIGNATURE SECTION -->
        <?php if (!empty($principalSignaturePath) || !empty($nextTermRow)): ?>
            <div class="signature-wrapper">
                <div class="sign-content">
                    <?php if (!empty($principalSignaturePath)): ?>
                        <img class="sign-img" src="<?php echo $principalSignaturePath; ?>" alt="Principal Signature">
                        <div class="sign-label">Principal's Signature</div>
                    <?php endif; ?>
                    <?php if (!empty($nextTermRow)): ?>
                        <div class="next-term">
                            <strong>NEXT TERM BEGINS:</strong><br>
                            <?php echo htmlspecialchars(is_array($nextTermRow) ? ($nextTermRow['nextTerm'] ?? 'To be announced') : 'To be announced'); ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

    </div>

</body>

</html>
<?php
$html = ob_get_clean();

$dompdf = new Dompdf();
$options = new Options();
$options->set('isRemoteEnabled', true);
$options->set('isJavascriptEnabled', true);
$options->set('chroot', $_SERVER['DOCUMENT_ROOT']);
$options->set('isHtml5ParserEnabled', true);
$options->set('defaultFont', 'DejaVu Sans');
$options->set('dpi', 96);
$options->set('enable_css_float', true);
$dompdf->setOptions($options);
$dompdf->load_html($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

$pdfPerfMs = (microtime(true) - $pdfPerfStart) * 1000;
error_log(sprintf(
    '[PDF_PERF] student=%s time_ms=%.2f',
    (string)($iStudent['student_id'] ?? 'unknown'),
    $pdfPerfMs
));

$dompdf->stream("Report_" . ($iStudent['first_name'] ?? 'Student') . ".pdf", array("Attachment" => false));
?>