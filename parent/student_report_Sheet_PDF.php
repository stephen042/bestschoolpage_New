<?php
/**
 * PDF Report Sheet Generator - Rebuilt for PHP 8.x
 * Generates student report sheet using Dompdf
 */

namespace Dompdf;

require_once('dompdf_New/autoload.inc.php');

ob_start();
require_once('../config.php');
require_once('inc.session-create.php');

// ============================================================================
// INITIALIZATION & DATA FETCHING
// ============================================================================

$userId = $_SESSION['userid'] ?? 0;
$randomId = $_GET['randomid'] ?? '';

if (empty($randomId)) {
    die("Student ID is required");
}

// Get user details
$userDetails = db_get_row("SELECT * FROM school_register WHERE id = ?", [$userId]);
if (empty($userDetails)) {
    die("User not found");
}

$createByUserId = $userDetails['create_by_userid'] ?? $userId;

// Get school details
$schoolDetails = db_get_row("SELECT * FROM school_register WHERE id = ?", [$createByUserId]);
if (empty($schoolDetails)) {
    $schoolDetails = [];
}

// Get state name
$state = db_get_row("SELECT title FROM state WHERE id = ?", [$schoolDetails['state'] ?? 0]);
$stateName = $state['title'] ?? '';

// Get current student
$currentStudent = db_get_row("SELECT * FROM manage_student WHERE randomid = ?", [$randomId]);
if (empty($currentStudent)) {
    die("Student not found");
}

// Get assessments
$assesmentAll = db_get_val(
    "SELECT GROUP_CONCAT(assesment_id) FROM score_entry_time_frame 
     WHERE create_by_userid = ? AND session = ? ORDER BY id DESC",
    [$createByUserId, $currentStudent['session'] ?? '']
);

$totalAssesment = !empty($assesmentAll) ? explode(',', $assesmentAll) : [];

// Get subjects
$subjects = db_get_rows(
    "SELECT * FROM school_subject WHERE class_id = ? AND create_by_userid = ?",
    [$currentStudent['class'] ?? 0, $createByUserId]
);

// Get class name
$className = db_get_val("SELECT name FROM school_class WHERE id = ?", [$currentStudent['class'] ?? 0]);

// ============================================================================
// HELPER FUNCTIONS
// ============================================================================
function getAssessmentScore($assessmentId, $studentId, $subjectId, $userId) {
    $score = db_get_row(
        "SELECT score FROM input_score_class_teacher 
         WHERE assesment_id = ? AND student_id = ? AND subject_id = ? AND create_by_userid = ?",
        [$assessmentId, $studentId, $subjectId, $userId]
    );
    return $score['score'] ?? 0;
}

function getGrade($score) {
    $grade = db_get_val(
        "SELECT grade FROM school_grade WHERE maximum_number >= ? ORDER BY maximum_number ASC LIMIT 1",
        [$score]
    );
    return $grade ?? 'N/A';
}

function getAssessmentName($assessmentId) {
    return db_get_val("SELECT assesment FROM school_assessment WHERE id = ?", [$assessmentId]) ?? 'N/A';
}

function getAssessmentPercentage($assessmentId, $userId) {
    return db_get_val(
        "SELECT percentage FROM score_entry_time_frame WHERE assesment_id = ? AND create_by_userid = ?",
        [$assessmentId, $userId]
    ) ?? 0;
}
?>

<html>
<head>
    <title>REPORT SHEET</title>
    <style>
        @page { margin: 1.5cm; }
        body { 
            font-family: 'DejaVu Sans', sans-serif; 
            font-size: 12px;
            border: 1px solid #ccc;
            padding: 10px;
        }
        .header-table { width: 100%; margin-bottom: 20px; }
        .school-logo { width: 80%; max-width: 150px; }
        .school-name { 
            font-size: 22px; 
            color: #2196F3; 
            font-weight: bold; 
            text-align: center; 
            margin-bottom: 0;
        }
        .school-address {
            font-size: 12px;
            font-weight: 600;
            text-align: center;
            margin: 5px 0;
        }
        .report-title {
            font-size: 18px;
            text-align: center;
            font-weight: bold;
            margin-top: 30px;
            text-decoration: underline;
        }
        .student-info-table { width: 100%; margin: 15px 0; }
        .student-info-table td { padding: 5px; }
        .subject-table { 
            width: 100%; 
            border-collapse: collapse; 
            margin: 20px 0;
        }
        .subject-table th, .subject-table td {
            border: 1px solid #000;
            padding: 8px;
            text-align: center;
        }
        .subject-table th {
            background-color: #f0f0f0;
            font-weight: bold;
        }
        .subject-table td:first-child {
            text-align: left;
            font-weight: normal;
        }
        .affective-table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
        }
        .affective-table td {
            border: 1px solid #000;
            padding: 6px;
        }
        .affective-table td:first-child {
            width: 70%;
        }
        .summary-table {
            width: 100%;
            margin: 20px 0;
            border-collapse: collapse;
        }
        .summary-table td {
            padding: 8px;
            border: 1px solid #ccc;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .mb-20 { margin-bottom: 20px; }
        .mt-20 { margin-top: 20px; }
        hr { margin: 15px 0; }
    </style>
</head>
<body>

    <!-- Header Section -->
    <table class="header-table">
        <tr>
            <td style="width: 20%;">
                <?php if (!empty($schoolDetails['logo']) && file_exists("../uploads/" . $schoolDetails['logo'])): ?>
                    <img class="school-logo" src="../uploads/<?= htmlspecialchars($schoolDetails['logo']) ?>" alt="Logo">
                <?php endif; ?>
            </td>
            <td style="width: 60%; text-align: center;">
                <div class="school-name"><?= htmlspecialchars($schoolDetails['name'] ?? 'School Name') ?></div>
                <div class="school-address">
                    Website: <?= htmlspecialchars($schoolDetails['website'] ?? 'N/A') ?><br>
                    <?= htmlspecialchars($schoolDetails['location'] ?? '') ?>, <?= htmlspecialchars($stateName) ?>
                </div>
                <div class="report-title">REPORT SHEET</div>
            </td>
            <td style="width: 20%;"></td>
        </tr>
    </table>

    <hr>

    <!-- Student Information -->
    <table class="student-info-table">
        <tr>
            <td style="width: 50%;"><strong>NAME:</strong> <?= htmlspecialchars(($currentStudent['first_name'] ?? '') . ' ' . ($currentStudent['last_name'] ?? '')) ?></td>
            <td style="width: 50%;"><strong>CLASS:</strong> <?= htmlspecialchars($className) ?></td>
        </tr>
        <tr>
            <td><strong>ADMISSION NO:</strong> <?= htmlspecialchars($currentStudent['student_id'] ?? 'N/A') ?></td>
            <td><strong>SESSION:</strong> <?= htmlspecialchars($currentStudent['session'] ?? 'N/A') ?></td>
        </tr>
    </table>

    <!-- Subjects and Scores Table -->
    <?php if (!empty($subjects) && !empty($totalAssesment)): ?>
        <table class="subject-table">
            <thead>
                <tr>
                    <th>SUBJECT</th>
                    <?php foreach ($totalAssesment as $assessmentId): ?>
                        <th>
                            <?= htmlspecialchars(getAssessmentName($assessmentId)) ?>
                            (<?= getAssessmentPercentage($assessmentId, $createByUserId) ?>%)
                        </th>
                    <?php endforeach; ?>
                    <th>TOTAL</th>
                    <th>GRD</th>
                    <th>AVG</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $totalScore = 0;
                $subjectCount = 0;
                $assessmentCount = count($totalAssesment);
                
                foreach ($subjects as $subject): 
                    $subjectCount++;
                    $subjectTotal = 0;
                ?>
                    <tr>
                        <td style="text-align: left;"><?= htmlspecialchars($subject['subject']) ?></td>
                        
                        <?php foreach ($totalAssesment as $assessmentId): 
                            $score = getAssessmentScore($assessmentId, $currentStudent['id'], $subject['id'], $createByUserId);
                            $subjectTotal += $score;
                        ?>
                            <td><?= $score ?></td>
                        <?php endforeach; ?>
                        
                        <td><strong><?= round($subjectTotal) ?></strong></td>
                        <td><?= htmlspecialchars(getGrade($subjectTotal)) ?></td>
                        <td><?= round($subjectTotal / $assessmentCount, 1) ?></td>
                    </tr>
                <?php 
                    $totalScore += $subjectTotal;
                endforeach; 
                ?>
            </tbody>
        </table>

        <!-- Summary Section -->
        <table class="summary-table">
            <tr>
                <td style="width: 33%;"><strong>No. of Subjects:</strong> <?= $subjectCount ?></td>
                <td style="width: 33%;"><strong>Total Score:</strong> <?= round($totalScore) ?></td>
                <td style="width: 33%;"><strong>Final Average:</strong> <?= round($totalScore / $subjectCount, 2) ?></td>
            </tr>
            <tr>
                <td><strong>Final Grade:</strong> <?= htmlspecialchars(getGrade($totalScore / $subjectCount)) ?></td>
                <td colspan="2"></td>
            </tr>
        </table>
    <?php else: ?>
        <div style="text-align: center; padding: 50px; color: red;">
            No subject or assessment data available for this student.
        </div>
    <?php endif; ?>

    <!-- Affective Traits Section -->
    <div style="margin-top: 30px;">
        <strong>AFFECTIVE TRAITS</strong>
        <table class="affective-table">
            <tr>
                <td>PUNCTUALITY</td>
                <td style="width: 20%; text-align: center;">—</td>
            </tr>
            <tr>
                <td>NEATNESS</td>
                <td style="text-align: center;">—</td>
            </tr>
            <tr>
                <td>ATTENTIVENESS</td>
                <td style="text-align: center;">—</td>
            </tr>
            <tr>
                <td>HONESTY</td>
                <td style="text-align: center;">—</td>
            </tr>
            <tr>
                <td>LEADERSHIP</td>
                <td style="text-align: center;">—</td>
            </tr>
        </table>
    </div>

    <!-- Psychomotor Skills Section -->
    <div style="margin-top: 20px;">
        <strong>PSYCHOMOTOR SKILLS</strong>
        <table class="affective-table">
            <tr>
                <td>HANDWRITING</td>
                <td style="width: 20%; text-align: center;">—</td>
            </tr>
            <tr>
                <td>VERBAL FLUENCY</td>
                <td style="text-align: center;">—</td>
            </tr>
            <tr>
                <td>SPORTS</td>
                <td style="text-align: center;">—</td>
            </tr>
            <tr>
                <td>CREATIVITY</td>
                <td style="text-align: center;">—</td>
            </tr>
        </table>
    </div>

    <!-- Teacher/Principal Signature -->
    <div style="margin-top: 40px;">
        <table style="width: 100%;">
            <tr>
                <td style="width: 50%;">
                    <strong>Teacher's Signature:</strong> ___________________
                </td>
                <td style="width: 50%;">
                    <strong>Principal's Signature:</strong> ___________________
                </td>
            </tr>
            <tr>
                <td colspan="2" style="text-align: center; padding-top: 20px;">
                    <strong>Date:</strong> <?= date('d-m-Y') ?>
                </td>
            </tr>
        </table>
    </div>

</body>
</html>

<?php
$html = ob_get_clean();

// Generate PDF
try {
    $dompdf = new Dompdf();
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->loadHtml($html);
    $dompdf->render();
    
    // Stream PDF (view in browser)
    $dompdf->stream("REPORT_SHEET_" . ($currentStudent['student_id'] ?? date('Ymd')), ["Attachment" => false]);
    
} catch (Exception $e) {
    echo "Error generating PDF: " . $e->getMessage();
}
?>