<?php

/**
 * Chunked bulk result PDF export by class.
 *
 * Actions:
 * - start   : create export job
 * - process : generate next chunk and optionally zip outputs
 * - status  : get job status
 */

require_once('../config.php');
require_once('inc.session-create.php');

// ============================================================================
// FIX: USE CORRECT USER IDENTIFICATION (EXACTLY MATCHES dashboard.php)
// ============================================================================
// Use the same method as class_teacher_roll_call_bulk.php
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

header('Content-Type: application/json; charset=utf-8');

$action = $_GET['action'] ?? $_POST['action'] ?? '';
if (!in_array($action, ['start', 'process', 'status'], true)) {
    echo json_encode(['ok' => false, 'message' => 'Invalid action']);
    exit;
}

$schoolId = $create_by_userid;
if ($schoolId <= 0) {
    echo json_encode(['ok' => false, 'message' => 'Invalid school context']);
    exit;
}

$sessionCookieName = session_name();
$sessionCookieId = session_id();

$baseExportDir = rtrim(PATH_UPLOAD, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'bulk_exports' . DIRECTORY_SEPARATOR . $schoolId . DIRECTORY_SEPARATOR;
$baseExportRel = 'uploads/bulk_exports/' . $schoolId . '/';

if (!is_dir($baseExportDir) && !@mkdir($baseExportDir, 0777, true)) {
    echo json_encode(['ok' => false, 'message' => 'Unable to create export directory']);
    exit;
}

cleanupOldJobs($baseExportDir, 72 * 3600);

if ($action === 'start') {
    $sessionId = (int)($_POST['session'] ?? 0);
    $termId = (int)($_POST['term_id'] ?? 0);
    $classId = (int)($_POST['class_id'] ?? 0);
    $assesmentsRaw = trim((string)($_POST['assesments'] ?? ''));
    // Get the school ID from POST if provided, otherwise use the resolved one
    $schoolIdParam = (int)($_POST['create_by_userid'] ?? $schoolId);

    if ($sessionId <= 0 || $termId <= 0 || $classId <= 0 || $assesmentsRaw === '') {
        echo json_encode(['ok' => false, 'message' => 'Missing required filters']);
        exit;
    }

    $assessmentIds = array_values(array_unique(array_filter(array_map('intval', explode('-', $assesmentsRaw)))));
    if (empty($assessmentIds)) {
        echo json_encode(['ok' => false, 'message' => 'No assessments selected']);
        exit;
    }

    $assessmentString = implode('-', $assessmentIds);

    $ownerIds = array_values(array_unique(array_filter([
        (int)$schoolId,
        (int)$schoolIdParam,
        (int)($_SESSION['userid'] ?? 0),
    ], function ($v) {
        return $v > 0;
    })));
    $ownerPrimary = $ownerIds[0] ?? $schoolId;
    $ownerSecondary = $ownerIds[1] ?? $ownerPrimary;

    $students = [];
    $selectionScope = 'class_session_term';

    $scopes = [
        [
            'name' => 'class_session_term',
            'sql' => "SELECT ms.id, ms.student_id, ms.randomid, ms.first_name, ms.last_name
                     FROM manage_student ms
                     INNER JOIN (
                         SELECT student_id, MAX(id) AS latest_id
                         FROM manage_student
                         WHERE create_by_userid = ? AND class = ? AND session = ? AND term_id = ?
                         GROUP BY student_id
                     ) latest ON latest.latest_id = ms.id
                     WHERE ms.create_by_userid = ?
                     ORDER BY ms.first_name ASC, ms.last_name ASC",
            'params' => [$schoolId, $classId, $sessionId, $termId, $schoolId],
        ],
        [
            'name' => 'class_session',
            'sql' => "SELECT ms.id, ms.student_id, ms.randomid, ms.first_name, ms.last_name
                     FROM manage_student ms
                     INNER JOIN (
                         SELECT student_id, MAX(id) AS latest_id
                         FROM manage_student
                         WHERE create_by_userid = ? AND class = ? AND session = ?
                         GROUP BY student_id
                     ) latest ON latest.latest_id = ms.id
                     WHERE ms.create_by_userid = ?
                     ORDER BY ms.first_name ASC, ms.last_name ASC",
            'params' => [$schoolId, $classId, $sessionId, $schoolId],
        ],
        [
            'name' => 'class_only',
            'sql' => "SELECT ms.id, ms.student_id, ms.randomid, ms.first_name, ms.last_name
                     FROM manage_student ms
                     INNER JOIN (
                         SELECT student_id, MAX(id) AS latest_id
                         FROM manage_student
                         WHERE create_by_userid = ? AND class = ?
                         GROUP BY student_id
                     ) latest ON latest.latest_id = ms.id
                     WHERE ms.create_by_userid = ?
                     ORDER BY ms.first_name ASC, ms.last_name ASC",
            'params' => [$schoolId, $classId, $schoolId],
        ],
        [
            'name' => 'class_member_fallback',
            'sql' => "SELECT ms.id, ms.student_id, ms.randomid, ms.first_name, ms.last_name
                     FROM class_member cm
                     INNER JOIN (
                         SELECT student_id, MAX(id) AS latest_id
                         FROM manage_student
                         WHERE create_by_userid = ?
                         GROUP BY student_id
                     ) latest ON (latest.student_id = cm.student_id)
                     INNER JOIN manage_student ms ON ms.id = latest.latest_id
                     WHERE cm.create_by_userid = ?
                     AND cm.class_id = ?
                     ORDER BY ms.first_name ASC, ms.last_name ASC",
            'params' => [$schoolId, $schoolId, $classId],
        ],
        [
            'name' => 'class_member_numeric_fallback',
            'sql' => "SELECT ms.id, ms.student_id, ms.randomid, ms.first_name, ms.last_name
                     FROM class_member cm
                     INNER JOIN manage_student ms ON ms.id = cm.student_id
                     WHERE cm.create_by_userid = ?
                     AND cm.class_id = ?
                     AND ms.create_by_userid = ?
                     GROUP BY ms.student_id
                     ORDER BY ms.first_name ASC, ms.last_name ASC",
            'params' => [$schoolId, $classId, $schoolId],
        ],
        [
            'name' => 'score_table_exact',
            'sql' => "SELECT ms.id, ms.student_id, ms.randomid, ms.first_name, ms.last_name
                     FROM (
                         SELECT DISTINCT student_id
                         FROM input_score_class_teacher
                         WHERE create_by_userid = ? AND class_id = ? AND session_id = ? AND term_id = ?
                     ) sc
                     INNER JOIN manage_student ms ON ms.id = sc.student_id
                     WHERE ms.create_by_userid = ?
                     ORDER BY ms.first_name ASC, ms.last_name ASC",
            'params' => [$schoolId, $classId, $sessionId, $termId, $schoolId],
        ],
        [
            'name' => 'score_table_class_session',
            'sql' => "SELECT ms.id, ms.student_id, ms.randomid, ms.first_name, ms.last_name
                     FROM (
                         SELECT DISTINCT student_id
                         FROM input_score_class_teacher
                         WHERE create_by_userid = ? AND class_id = ? AND session_id = ?
                     ) sc
                     INNER JOIN manage_student ms ON ms.id = sc.student_id
                     WHERE ms.create_by_userid = ?
                     ORDER BY ms.first_name ASC, ms.last_name ASC",
            'params' => [$schoolId, $classId, $sessionId, $schoolId],
        ],
        [
            'name' => 'score_table_class_only',
            'sql' => "SELECT ms.id, ms.student_id, ms.randomid, ms.first_name, ms.last_name
                     FROM (
                         SELECT DISTINCT student_id
                         FROM input_score_class_teacher
                         WHERE create_by_userid = ? AND class_id = ?
                     ) sc
                     INNER JOIN manage_student ms ON ms.id = sc.student_id
                     WHERE ms.create_by_userid = ?
                     ORDER BY ms.first_name ASC, ms.last_name ASC",
            'params' => [$schoolId, $classId, $schoolId],
        ],
        [
            'name' => 'owner_context_class_only',
            'sql' => "SELECT ms.id, ms.student_id, ms.randomid, ms.first_name, ms.last_name
                     FROM (
                         SELECT student_id, MAX(id) AS latest_id
                         FROM manage_student
                         WHERE create_by_userid IN (?, ?) AND class = ?
                         GROUP BY student_id
                     ) latest
                     INNER JOIN manage_student ms ON ms.id = latest.latest_id
                     WHERE ms.create_by_userid IN (?, ?)
                     ORDER BY ms.first_name ASC, ms.last_name ASC",
            'params' => [$ownerPrimary, $ownerSecondary, $classId, $ownerPrimary, $ownerSecondary],
        ],
        [
            'name' => 'owner_context_score_class',
            'sql' => "SELECT ms.id, ms.student_id, ms.randomid, ms.first_name, ms.last_name
                     FROM (
                         SELECT DISTINCT student_id
                         FROM input_score_class_teacher
                         WHERE create_by_userid IN (?, ?) AND class_id = ?
                     ) sc
                     INNER JOIN manage_student ms ON ms.id = sc.student_id
                     WHERE ms.create_by_userid IN (?, ?)
                     ORDER BY ms.first_name ASC, ms.last_name ASC",
            'params' => [$ownerPrimary, $ownerSecondary, $classId, $ownerPrimary, $ownerSecondary],
        ],
    ];

    foreach ($scopes as $scope) {
        $rows = db_get_rows($scope['sql'], $scope['params']);
        if (is_array($rows) && !empty($rows)) {
            $students = $rows;
            $selectionScope = $scope['name'];
            break;
        }
    }

    if (!is_array($students) || empty($students)) {
        echo json_encode([
            'ok' => false,
            'message' => 'No students found for selected class',
            'owner_context' => [$ownerPrimary, $ownerSecondary],
        ]);
        exit;
    }

    $jobId = 'bulk_' . date('Ymd_His') . '_' . bin2hex(random_bytes(3));
    $jobDir = $baseExportDir . $jobId . DIRECTORY_SEPARATOR;
    $pdfDir = $jobDir . 'pdfs' . DIRECTORY_SEPARATOR;

    if (!@mkdir($pdfDir, 0777, true)) {
        echo json_encode(['ok' => false, 'message' => 'Unable to initialize job folder']);
        exit;
    }

    $studentRows = [];
    foreach ($students as $row) {
        $studentRows[] = [
            'id' => (int)($row['id'] ?? 0),
            'student_id' => (string)($row['student_id'] ?? ''),
            'randomid' => (string)($row['randomid'] ?? ''),
            'name' => trim((string)($row['first_name'] ?? '') . ' ' . (string)($row['last_name'] ?? '')),
        ];
    }

    $job = [
        'job_id' => $jobId,
        'status' => 'queued',
        'school_id' => $schoolId,
        'filters' => [
            'session' => $sessionId,
            'term_id' => $termId,
            'class_id' => $classId,
            'assesments' => $assessmentString,
            'selection_scope' => $selectionScope,
        ],
        'created_at' => date('c'),
        'updated_at' => date('c'),
        'total_students' => count($studentRows),
        'processed_students' => 0,
        'failed_students' => 0,
        'students' => $studentRows,
        'pdf_files' => [],
        'errors' => [],
        'zip_file' => '',
        'download_url' => '',
        'message' => 'Job created',
    ];

    if (!saveJob($jobDir . 'job.json', $job)) {
        echo json_encode(['ok' => false, 'message' => 'Unable to save job']);
        exit;
    }

    echo json_encode([
        'ok' => true,
        'job_id' => $jobId,
        'status' => $job['status'],
        'total_students' => $job['total_students'],
        'processed_students' => $job['processed_students'],
        'failed_students' => $job['failed_students'],
        'progress' => 0,
        'selection_scope' => $selectionScope,
        'message' => 'Bulk export started',
    ]);
    exit;
}

$jobId = preg_replace('/[^a-zA-Z0-9_\-]/', '', (string)($_POST['job_id'] ?? $_GET['job_id'] ?? ''));
if ($jobId === '') {
    echo json_encode(['ok' => false, 'message' => 'Missing job id']);
    exit;
}

$jobDir = $baseExportDir . $jobId . DIRECTORY_SEPARATOR;
$jobFile = $jobDir . 'job.json';
$job = loadJob($jobFile);

if (!$job || (int)($job['school_id'] ?? 0) !== $schoolId) {
    echo json_encode(['ok' => false, 'message' => 'Job not found']);
    exit;
}

if ($action === 'status') {
    echo json_encode(statusPayload($job));
    exit;
}

$chunkSize = (int)($_POST['chunk'] ?? 5);
if ($chunkSize < 1) {
    $chunkSize = 1;
}
if ($chunkSize > 20) {
    $chunkSize = 20;
}

if ($chunkSize === 5 && !isset($_POST['chunk'])) {
    $totalStudents = (int)($job['total_students'] ?? 0);
    if ($totalStudents > 40) {
        $chunkSize = 3;
    } elseif ($totalStudents > 25) {
        $chunkSize = 4;
    } elseif ($totalStudents > 15) {
        $chunkSize = 5;
    } else {
        $chunkSize = 8;
    }
}

if (($job['status'] ?? '') === 'completed' || ($job['status'] ?? '') === 'failed') {
    echo json_encode(statusPayload($job));
    exit;
}

$job['status'] = 'processing';
$job['updated_at'] = date('c');

session_write_close();

$total = (int)($job['total_students'] ?? 0);
$processed = (int)($job['processed_students'] ?? 0);
$students = is_array($job['students'] ?? null) ? $job['students'] : [];
$filters = is_array($job['filters'] ?? null) ? $job['filters'] : [];

$end = min($processed + $chunkSize, $total);
for ($i = $processed; $i < $end; $i++) {
    $student = $students[$i] ?? null;
    if (!is_array($student)) {
        $job['failed_students'] = (int)($job['failed_students'] ?? 0) + 1;
        $job['errors'][] = 'Invalid student row at index ' . $i;
        continue;
    }

    $studentId = (string)($student['student_id'] ?? 'student_' . $i);
    $randomid = (string)($student['randomid'] ?? '');

    $query = [
        'randomid' => $randomid,
        'student_id' => $studentId,
        'session' => (int)($filters['session'] ?? 0),
        'term_id' => (int)($filters['term_id'] ?? 0),
        'class_id' => (int)($filters['class_id'] ?? 0),
        'assesments' => (string)($filters['assesments'] ?? ''),
        'create_by_userid' => $schoolId,
        'paper_mode' => 'legacy_auto',
    ];

    $pdfResponse = fetchPdfBinary(SKOOL_URL . 'skool_term_result_pdf.php', $query, $sessionCookieName, $sessionCookieId);

    if (!$pdfResponse['ok']) {
        $job['failed_students'] = (int)($job['failed_students'] ?? 0) + 1;
        $job['errors'][] = $studentId . ': ' . $pdfResponse['message'];
        continue;
    }

    $safeStudentId = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $studentId);
    if ($safeStudentId === '') {
        $safeStudentId = 'student_' . ($i + 1);
    }

    $pdfPath = $jobDir . 'pdfs' . DIRECTORY_SEPARATOR . $safeStudentId . '.pdf';
    $writeOk = @file_put_contents($pdfPath, $pdfResponse['content']);
    if ($writeOk === false) {
        $job['failed_students'] = (int)($job['failed_students'] ?? 0) + 1;
        $job['errors'][] = $studentId . ': Unable to save PDF file';
        continue;
    }

    $job['pdf_files'][] = basename($pdfPath);
}

$job['processed_students'] = $end;
$job['updated_at'] = date('c');

if ((int)$job['processed_students'] >= $total) {
    $zipName = 'class_' . ((int)($filters['class_id'] ?? 0)) . '_session_' . ((int)($filters['session'] ?? 0)) . '_term_' . ((int)($filters['term_id'] ?? 0)) . '_' . $jobId . '.zip';
    $zipPath = $jobDir . $zipName;

    $zipOk = buildZipFromPdfFolder($jobDir . 'pdfs' . DIRECTORY_SEPARATOR, $zipPath);
    if ($zipOk) {
        $job['status'] = 'completed';
        $job['zip_file'] = $zipName;
        $job['download_url'] = SITE_URL . $baseExportRel . $jobId . '/' . $zipName;
        $job['message'] = 'Bulk export completed';
    } else {
        $job['status'] = 'failed';
        $job['message'] = 'Unable to create ZIP archive';
        $job['errors'][] = 'ZIP build failed';
    }
} else {
    $job['status'] = 'processing';
    $job['message'] = 'Processing...';
}

saveJob($jobFile, $job);

echo json_encode(statusPayload($job));
exit;

function loadJob($jobFile)
{
    if (!is_file($jobFile)) {
        return null;
    }
    $raw = @file_get_contents($jobFile);
    if ($raw === false || $raw === '') {
        return null;
    }
    $decoded = json_decode($raw, true);
    return is_array($decoded) ? $decoded : null;
}

function saveJob($jobFile, $data)
{
    $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    if ($json === false) {
        return false;
    }
    return @file_put_contents($jobFile, $json, LOCK_EX) !== false;
}

function statusPayload($job)
{
    $total = (int)($job['total_students'] ?? 0);
    $processed = (int)($job['processed_students'] ?? 0);
    $progress = $total > 0 ? round(($processed / $total) * 100, 2) : 0;

    return [
        'ok' => true,
        'job_id' => (string)($job['job_id'] ?? ''),
        'status' => (string)($job['status'] ?? 'queued'),
        'total_students' => $total,
        'processed_students' => $processed,
        'failed_students' => (int)($job['failed_students'] ?? 0),
        'progress' => $progress,
        'download_url' => (string)($job['download_url'] ?? ''),
        'message' => (string)($job['message'] ?? ''),
    ];
}

function fetchPdfBinary($endpoint, $query, $sessionCookieName, $sessionCookieId)
{
    $url = $endpoint . '?' . http_build_query($query);

    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT => 120,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_HTTPHEADER => [
                'Cookie: ' . $sessionCookieName . '=' . $sessionCookieId,
            ],
        ]);

        $body = curl_exec($ch);
        $error = curl_error($ch);
        $code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($body === false || $error !== '') {
            return ['ok' => false, 'message' => 'Curl error: ' . $error, 'content' => ''];
        }
        if ($code >= 400) {
            return ['ok' => false, 'message' => 'HTTP ' . $code, 'content' => ''];
        }

        if (strpos((string)$body, '%PDF') !== 0) {
            return ['ok' => false, 'message' => 'Invalid PDF response', 'content' => ''];
        }

        return ['ok' => true, 'message' => 'ok', 'content' => $body];
    }

    $context = stream_context_create([
        'http' => [
            'method' => 'GET',
            'timeout' => 120,
            'header' => "Cookie: {$sessionCookieName}={$sessionCookieId}\r\n",
        ],
    ]);

    $body = @file_get_contents($url, false, $context);
    if ($body === false || strpos((string)$body, '%PDF') !== 0) {
        return ['ok' => false, 'message' => 'Unable to fetch PDF', 'content' => ''];
    }

    return ['ok' => true, 'message' => 'ok', 'content' => $body];
}

function buildZipFromPdfFolder($pdfDir, $zipPath)
{
    if (!class_exists('ZipArchive')) {
        return false;
    }

    $files = glob($pdfDir . '*.pdf');
    if (!is_array($files) || empty($files)) {
        return false;
    }

    $zip = new ZipArchive();
    if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
        return false;
    }

    foreach ($files as $file) {
        if (is_file($file)) {
            $zip->addFile($file, basename($file));
        }
    }

    $zip->close();
    return is_file($zipPath);
}

function cleanupOldJobs($baseExportDir, $maxAgeSeconds)
{
    $entries = @scandir($baseExportDir);
    if (!is_array($entries)) {
        return;
    }

    $now = time();
    foreach ($entries as $entry) {
        if ($entry === '.' || $entry === '..') {
            continue;
        }
        $full = $baseExportDir . $entry;
        if (!is_dir($full)) {
            continue;
        }
        $mtime = @filemtime($full);
        if ($mtime === false) {
            continue;
        }
        if (($now - $mtime) > $maxAgeSeconds) {
            deleteDirectory($full);
        }
    }
}

function deleteDirectory($dir)
{
    if (!is_dir($dir)) {
        return;
    }

    $items = scandir($dir);
    if (!is_array($items)) {
        return;
    }

    foreach ($items as $item) {
        if ($item === '.' || $item === '..') {
            continue;
        }
        $path = $dir . DIRECTORY_SEPARATOR . $item;
        if (is_dir($path)) {
            deleteDirectory($path);
        } else {
            @unlink($path);
        }
    }

    @rmdir($dir);
}