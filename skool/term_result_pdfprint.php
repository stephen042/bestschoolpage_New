<?php
/**
 * ============================================================================
 * SKOOL TERM RESULT PDF - FIXED (Searches by student_id, not randomid)
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

ob_start();

require_once('../config.php');
require_once('inc.session-create.php');

// ============================================================================
// GET STUDENT DATA - SEARCH BY student_id (NOT randomid)
// ============================================================================
$studentId = $_GET['student_id'] ?? '';
$sessionId = $_GET['session'] ?? 0;
$termId = $_GET['term_id'] ?? 0;
$classId = $_GET['class_id'] ?? 0;
$assesmentsParam = $_GET['assesments'] ?? '';

if (empty($studentId)) {
    die("Student ID is required. Please provide student_id parameter.");
}

// Get student by student_id
$iStudent = db_get_row("SELECT * FROM manage_student WHERE student_id = ?", [$studentId]);

if (empty($iStudent)) {
    die("Student not found with student_id: " . htmlspecialchars($studentId));
}

// Get school ID from student's create_by_userid
$SCHOOL_ID = (int)($iStudent['create_by_userid'] ?? 0);

if (empty($SCHOOL_ID)) {
    die("School not found for this student.");
}

// ============================================================================
// GET SCHOOL INFO
// ============================================================================
$iSchool = db_get_row("SELECT * FROM school_register WHERE id = ?", [$SCHOOL_ID]);

if (empty($iSchool)) {
    die("School not found with ID: " . $SCHOOL_ID);
}

// Get state name
$iState = db_get_row("SELECT * FROM state WHERE id = ?", [$iSchool['state'] ?? 0]);
$statename = $iState['title'] ?? '';

// ============================================================================
// GET SESSION AND CLASS
// ============================================================================
$iSession = db_get_row("SELECT * FROM school_session WHERE id = ?", [$sessionId ?: $iStudent['session']]);
$iClass = db_get_row("SELECT * FROM school_class WHERE id = ?", [$classId ?: $iStudent['class']]);

// ============================================================================
// GET PDF SETTINGS USING SECTION ID
// ============================================================================
$sectionId = $iClass['section_id'] ?? 0;
$pdfSettings = [];

if ($sectionId > 0) {
    $pdfSettings = db_get_row("SELECT * FROM school_pdfsetting WHERE section_id = ? AND create_by_userid = ?", [$sectionId, $SCHOOL_ID]);
}

// If no settings found, use defaults (all ON)
if (empty($pdfSettings)) {
    $pdfSettings = [
        'is_grade' => '1', 'is_class' => '1', 'is_position' => '1', 'is_totalstudent' => '1',
        'is_addmission' => '1', 'is_totalscore' => '1', 'is_session' => '1', 'is_finalaverage' => '1',
        'is_terms' => '1', 'is_highestaverage' => '1', 'is_lowestaverage' => '1', 'is_schoolopen' => '1',
        'is_daypresent' => '1', 'is_dayabsent' => '1', 'is_profilepic' => '1', 'is_affective' => '1',
        'is_phycomotor' => '1', 'is_out' => '1', 'is_highest_avg' => '1', 'is_lowest_avg' => '1',
        'is_class_avg' => '1', 'is_grade_details' => '1', 'is_no_of_subjects' => '1', 'is_pos' => '1',
        'title_1' => 'Class Teacher', 'title_2' => "Class Teacher's Remarks",
        'title_3' => "Principal's Remarks", 'title_4' => 'AFFECTIVE TRAITS', 'title_5' => 'PSYCHOMOTOR'
    ];
}

// Extract settings
$showProfilePic = ($pdfSettings['is_profilepic'] ?? '1') == '1';
$showAdmission = ($pdfSettings['is_addmission'] ?? '1') == '1';
$showClass = ($pdfSettings['is_class'] ?? '1') == '1';
$showSession = ($pdfSettings['is_session'] ?? '1') == '1';
$showTerms = ($pdfSettings['is_terms'] ?? '1') == '1';
$showPosition = ($pdfSettings['is_position'] ?? '1') == '1';
$showTotalScore = ($pdfSettings['is_totalscore'] ?? '1') == '1';
$showFinalAverage = ($pdfSettings['is_finalaverage'] ?? '1') == '1';
$showGrade = ($pdfSettings['is_grade'] ?? '1') == '1';
$showAffective = ($pdfSettings['is_affective'] ?? '1') == '1';
$showPsychomotor = ($pdfSettings['is_phycomotor'] ?? '1') == '1';
$showOutOf = ($pdfSettings['is_out'] ?? '1') == '1';
$showLowestAvg = ($pdfSettings['is_lowest_avg'] ?? '1') == '1';
$showHighestAvg = ($pdfSettings['is_highest_avg'] ?? '1') == '1';
$showClassAvg = ($pdfSettings['is_class_avg'] ?? '1') == '1';
$showGradeDetails = ($pdfSettings['is_grade_details'] ?? '1') == '1';
$showNoOfSubjects = ($pdfSettings['is_no_of_subjects'] ?? '1') == '1';
$showPos = ($pdfSettings['is_pos'] ?? '1') == '1';

// Custom titles
$title1 = $pdfSettings['title_1'] ?? 'Class Teacher';
$title2 = $pdfSettings['title_2'] ?? "Class Teacher's Remarks";
$title3 = $pdfSettings['title_3'] ?? "Principal's Remarks";
$title4 = $pdfSettings['title_4'] ?? 'AFFECTIVE TRAITS';
$title5 = $pdfSettings['title_5'] ?? 'PSYCHOMOTOR';

// ============================================================================
// GET ASSESSMENTS
// ============================================================================
$totalAssesment = !empty($assesmentsParam) ? explode('-', $assesmentsParam) : [];

// ============================================================================
// CALCULATE SCORES
// ============================================================================
$subjectdetail = db_get_rows("SELECT * FROM school_subject WHERE class_id = ? AND create_by_userid = ?", [$iClass['id'], $SCHOOL_ID]);
$iCountSubject = count($subjectdetail);
$iFinalScore = 0;
$subjectScoresData = [];

foreach ($subjectdetail as $Ilist) {
    $subjectTotal = 0;
    $subjectScores = [];
    foreach ($totalAssesment as $Val) {
        $score = floatval(db_get_val("SELECT score FROM input_score_class_teacher WHERE assesment_id = ? AND student_id = ? AND subject_id = ? AND create_by_userid = ?", [$Val, $iStudent['id'], $Ilist['id'], $SCHOOL_ID]) ?: 0);
        $subjectTotal += $score;
        $subjectScores[$Val] = $score;
    }
    $subjectScoresData[] = [
        'subject' => $Ilist['subject'],
        'scores' => $subjectScores,
        'total' => $subjectTotal
    ];
    $iFinalScore += $subjectTotal;
}

$ggrade = $iCountSubject > 0 ? $iFinalScore / $iCountSubject : 0;

// Get grade letter
$gradeLetter = db_get_val("SELECT grade FROM school_grade WHERE maximum_number >= ? AND minimum_number <= ? AND create_by_userid = ?", [$ggrade, $ggrade, $SCHOOL_ID]);
if (empty($gradeLetter)) {
    if ($ggrade >= 70) $gradeLetter = 'A';
    elseif ($ggrade >= 60) $gradeLetter = 'B';
    elseif ($ggrade >= 50) $gradeLetter = 'C';
    elseif ($ggrade >= 45) $gradeLetter = 'D';
    elseif ($ggrade >= 40) $gradeLetter = 'E';
    else $gradeLetter = 'F';
}

// Get position
$allStudentScores = db_get_rows("SELECT student_id, SUM(score) as total FROM input_score_class_teacher WHERE class_id = ? AND session_id = ? AND create_by_userid = ? GROUP BY student_id", [$iClass['id'], $sessionId, $SCHOOL_ID]);
$scores = [];
foreach ($allStudentScores as $s) {
    $scores[$s['student_id']] = $s['total'];
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
?>
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <style>
        * { font-family: DejaVu Sans, sans-serif; }
        body { margin: 0; padding: 20px; }
        .school-name { font-size: 24px; color: #2196F3; font-weight: bold; text-align: center; }
        .school-details { font-size: 12px; text-align: center; }
        .report-title { font-size: 16px; font-weight: bold; text-align: center; margin-top: 15px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        th, td { border: 1px solid #000; padding: 8px; text-align: center; vertical-align: top; }
        th { background-color: #f0f0f0; font-weight: bold; }
        .text-left { text-align: left; }
        .logo-img { max-width: 80px; max-height: 80px; display: block; margin: 0 auto; }
        @media print {
            body { margin: 0; padding: 0; }
        }
    </style>
</head>
<body>

<!-- School Header -->
<div style="text-align: center;">
    <?php if (!empty($iSchool['logo']) && file_exists("../uploads/" . $iSchool['logo'])): ?>
        <img src="../uploads/<?= htmlspecialchars($iSchool['logo']) ?>" class="logo-img">
    <?php endif; ?>
    <div class="school-name"><?= htmlspecialchars($iSchool['name'] ?? 'School Name') ?></div>
    <div class="school-details">MOTO: <?= htmlspecialchars($iSchool['moto'] ?? '') ?></div>
    <div class="school-details"><?= htmlspecialchars($iSchool['location'] ?? '') ?>, <?= htmlspecialchars($statename) ?></div>
    <div class="report-title">REPORT SHEET FOR <?= htmlspecialchars($iSession['session'] ?? '') ?> ACADEMIC SESSION</div>
</div>

<br>

<!-- Student Information -->
<table style="border: none;">
    <tr>
        <td class="text-left" style="border: none; width: 50%;"><strong>NAME:</strong> <?= htmlspecialchars(($iStudent['first_name'] ?? '') . ' ' . ($iStudent['last_name'] ?? '')) ?></td>
        <td class="text-left" style="border: none; width: 50%;">
            <?php if ($showAdmission): ?>
                <strong>Admission No:</strong> <?= htmlspecialchars($iStudent['student_id'] ?? '') ?>
            <?php endif; ?>
        </td>
    </tr>
    <tr>
        <td class="text-left" style="border: none;">
            <?php if ($showClass): ?>
                <strong>Class:</strong> <?= htmlspecialchars($iClass['name'] ?? 'N/A') ?>
            <?php endif; ?>
        </td>
        <td class="text-left" style="border: none;">
            <?php if ($showSession): ?>
                <strong>Session:</strong> <?= htmlspecialchars($iSession['session'] ?? 'N/A') ?>
            <?php endif; ?>
        </td>
    </tr>
    <tr>
        <td class="text-left" style="border: none;">
            <?php if ($showTerms): ?>
                <strong>Term:</strong> <?= htmlspecialchars($iSession['session'] ?? 'N/A') ?>
            <?php endif; ?>
        </td>
        <td class="text-left" style="border: none;">
            <?php if ($showPosition): ?>
                <strong>Position:</strong> <?= $studentPosition ?>
            <?php endif; ?>
        </td>
    </tr>
    <tr>
        <td class="text-left" style="border: none;">
            <?php if ($showTotalScore): ?>
                <strong>Total Score:</strong> <?= $iFinalScore ?>
            <?php endif; ?>
        </td>
        <td class="text-left" style="border: none;">
            <?php if ($showFinalAverage): ?>
                <strong>Average:</strong> <?= round($ggrade, 2) ?>
            <?php endif; ?>
            <?php if ($showGrade): ?>
                <strong> Grade:</strong> <?= $gradeLetter ?>
            <?php endif; ?>
        </td>
    </tr>
</table>

<!-- Subjects Scores Table -->
<table>
    <thead>
        <tr>
            <th>SUBJECT</th>
            <?php foreach ($totalAssesment as $Val): 
                $assName = db_get_val("SELECT assesment FROM school_assessment WHERE id = ?", [$Val]);
            ?>
                <th><?= htmlspecialchars($assName ?: 'N/A') ?></th>
            <?php endforeach; ?>
            <th>TOTAL</th>
            <th>GRD</th>
            <?php if ($showPos): ?><th>POS</th><?php endif; ?>
            <th>COMMENT</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($subjectScoresData as $data): ?>
        <tr>
            <td class="text-left"><?= htmlspecialchars($data['subject']) ?></td>
            <?php foreach ($totalAssesment as $Val): ?>
                <td><?= $data['scores'][$Val] ?? 0 ?></td>
            <?php endforeach; ?>
            <td><strong><?= round($data['total']) ?></strong></td>
            <td><?= $gradeLetter ?></td>
            <?php if ($showPos): ?><td>-</td><?php endif; ?>
            <td>-</td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<!-- Grade Details & Subject Count -->
<?php if ($showGradeDetails || $showNoOfSubjects): ?>
<table style="border: none;">
    <tr>
        <?php if ($showGradeDetails): ?>
        <td style="border: none; text-align: left;">
            <strong>GRADE DETAILS:</strong><br>
            <?php
            $iGradingAll = db_get_rows("SELECT * FROM school_grade WHERE create_by_userid = ? ORDER BY id DESC", [$SCHOOL_ID]);
            $gradeText = [];
            foreach ($iGradingAll as $iList) {
                $gradeText[] = $iList['grade'] . ' = ' . $iList['minimum_number'] . '-' . $iList['maximum_number'];
            }
            echo implode(', ', $gradeText);
            ?>
        </td>
        <?php endif; ?>
        <?php if ($showNoOfSubjects): ?>
        <td style="border: none; text-align: right;">
            <strong>No. of Subjects:</strong> <?= $tSub ?>
        </td>
        <?php endif; ?>
    </tr>
</table>
<?php endif; ?>

<!-- Teacher Remarks -->
<table style="border: none;">
    <?php
    $iFormTeacher = db_get_row("SELECT * FROM class_teacher WHERE school_class = ? AND school_session = ? AND create_by_userid = ?", [$iClass['id'], $sessionId, $SCHOOL_ID]);
    $iTeacher = db_get_row("SELECT * FROM staff_manage WHERE id = ? AND create_by_userid = ?", [$iFormTeacher['staff_id'] ?? 0, $SCHOOL_ID]);
    ?>
    <tr>
        <td style="border: none; width: 150px;"><strong><?= htmlspecialchars($title1) ?>:</strong></td>
        <td style="border: none;"><?= htmlspecialchars(($iTeacher['first_name'] ?? '') . ' ' . ($iTeacher['last_name'] ?? '')) ?></td>
    </tr>
    <tr>
        <td style="border: none;"><strong><?= htmlspecialchars($title2) ?>:</strong></td>
        <td style="border: none;"><?= htmlspecialchars(db_get_val("SELECT comments FROM clas_teacher_make_comment WHERE student_id = ?", [$iStudent['id']]) ?: '') ?></td>
    </tr>
    <?php
    $iPrinciple = db_get_row("SELECT * FROM assign_role WHERE principal = '1' AND create_by_userid = ?", [$SCHOOL_ID]);
    $iStaffPrinciple = db_get_row("SELECT * FROM staff_manage WHERE id = ? AND create_by_userid = ?", [$iPrinciple['staff_id'] ?? 0, $SCHOOL_ID]);
    ?>
    <tr>
        <td style="border: none;"><strong><?= htmlspecialchars($title3) ?>:</strong></td>
        <td style="border: none;"><?= htmlspecialchars(db_get_val("SELECT comments FROM clas_teacher_make_comment WHERE student_id = ? AND userid = ?", [$iStudent['id'], $iPrinciple['staff_id'] ?? 0]) ?: '') ?></td>
    </tr>
</table>

</body>
</html>

<?php
$html = ob_get_clean();

// Generate PDF
$dompdf = new Dompdf();
$options = new Options();
$options->set('isRemoteEnabled', true);
$options->set('isJavascriptEnabled', true);
$dompdf->setOptions($options);
$dompdf->load_html($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$dompdf->stream("Term_Result_" . ($iStudent['first_name'] ?? 'Student') . ".pdf", array("Attachment" => false));
?>