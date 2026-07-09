<?php
/**
 * School Password Reset Tool — standalone utility, no login required.
 */
require_once('../config.php');

// ============================================================================
// FORM PROCESSING (always before HTML output)
// ============================================================================
$msg     = '';
$msgType = '';

// Single account reset
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reset_password'])) {
    $schoolId = (int) ($_POST['school_id']   ?? 0);
    $newPass  = trim($_POST['new_password']   ?? '');

    if ($schoolId <= 0) {
        $msg = 'Invalid account ID.'; $msgType = 'error';
    } elseif ($newPass === '') {
        $msg = 'Please enter a new password.'; $msgType = 'error';
    } else {
        $hashed = password_hash($newPass, PASSWORD_DEFAULT);
        $ok = db_update('school_register', ['password' => $hashed], 'id = ?', [$schoolId]);
        if ($ok) {
            $acc = db_get_row("SELECT username, name FROM school_register WHERE id = ?", [$schoolId]);
            $msg = 'Password for <strong>'.e($acc['name'] ?? '').'</strong> ('.e($acc['username'] ?? '').') reset to: <code>'.e($newPass).'</code>';
            $msgType = 'success';
        } else {
            $msg = "Database update failed for ID {$schoolId}."; $msgType = 'error';
        }
    }
}

// Bulk reset school-owner accounts
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bulk_reset_all'])) {
    $bulkPass = trim($_POST['bulk_password'] ?? '');
    if ($bulkPass === '') {
        $msg = 'Please enter a bulk password.'; $msgType = 'error';
    } else {
        $hashed = password_hash($bulkPass, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE school_register SET password = ? WHERE usertype IN ('0','admin',0)");
        $stmt->execute([$hashed]);
        $count = $stmt->rowCount();
        $msg = "{$count} school-owner password(s) reset to: <code>".e($bulkPass).'</code>';
        $msgType = 'success';
    }
}

// Load all school_register rows
$accounts = db_get_rows(
    "SELECT id, username, email, name, password, status, usertype, create_by_userid
     FROM school_register
     ORDER BY usertype ASC, id DESC"
);

$filter   = $_GET['filter'] ?? 'all';
$filtered = [];
foreach ($accounts as $a) {
    $ut = (string)($a['usertype'] ?? '');
    if ($filter === 'owners'   && !in_array($ut, ['0','admin'], true)) continue;
    if ($filter === 'teachers' && $ut !== '1')                         continue;
    $filtered[] = $a;
}

$total    = count($accounts);
$owners   = count(array_filter($accounts, fn($a) => in_array((string)($a['usertype']??''),['0','admin'],true)));
$teachers = count(array_filter($accounts, fn($a) => (string)($a['usertype']??'')==='1'));
$hashed   = count(array_filter($accounts, fn($a) => strpos((string)$a['password'],'$2y$')===0));
$plain    = $total - $hashed;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>School Password Reset Tool</title>
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Segoe UI',Arial,sans-serif;background:#f4f6f9;padding:24px;color:#333}
.wrap{max-width:1400px;margin:0 auto}
h1{font-size:24px;color:#1B3058;margin-bottom:6px}
p.sub{color:#666;margin-bottom:20px;font-size:14px}
.msg{padding:14px 18px;border-radius:8px;margin-bottom:20px;font-size:14px;line-height:1.6}
.msg.success{background:#d4edda;color:#155724;border-left:4px solid #28a745}
.msg.error{background:#f8d7da;color:#721c24;border-left:4px solid #dc3545}
.bulk{background:#fff3cd;border:1px solid #ffc107;border-radius:10px;padding:16px 20px;margin-bottom:24px}
.bulk h3{color:#856404;margin-bottom:10px;font-size:15px}
.bulk-form{display:flex;align-items:center;gap:10px;flex-wrap:wrap}
.bulk-form input{padding:9px 12px;border:1px solid #ccc;border-radius:6px;font-size:14px;width:220px}
.btn-bulk{background:#dc3545;color:white;border:none;padding:9px 18px;border-radius:6px;cursor:pointer;font-weight:600}
.btn-bulk:hover{background:#c82333}
.tabs{display:flex;gap:8px;margin-bottom:16px}
.tab-link{padding:8px 18px;border-radius:20px;text-decoration:none;font-size:13px;font-weight:600;background:#e0e6f0;color:#333}
.tab-link.active{background:#1B3058;color:#fff}
.tbl-wrap{overflow-x:auto;border-radius:10px;box-shadow:0 2px 10px rgba(0,0,0,.07)}
table{width:100%;border-collapse:collapse;background:white;font-size:13px}
thead th{background:#1B3058;color:white;padding:12px 14px;text-align:left;white-space:nowrap}
tbody td{padding:11px 14px;border-bottom:1px solid #eee;vertical-align:middle}
tbody tr:hover{background:#f9fbff}
.badge{display:inline-block;padding:3px 10px;border-radius:30px;font-size:11px;font-weight:700}
.badge.owner{background:#cce5ff;color:#004085}
.badge.teacher{background:#d4edda;color:#155724}
.badge.other{background:#e2e3e5;color:#383d41}
.badge.active{background:#d4edda;color:#155724}
.badge.inactive{background:#f8d7da;color:#721c24}
.pw-ok{color:#28a745;font-weight:700}
.pw-bad{color:#dc3545;font-weight:700}
.reset-form{display:flex;align-items:center;gap:6px}
.reset-form input[type=text]{padding:7px 10px;border:1px solid #ccc;border-radius:6px;font-size:13px;width:140px}
.btn-reset{background:#28a745;color:white;border:none;padding:7px 14px;border-radius:6px;cursor:pointer;font-weight:600;font-size:13px;white-space:nowrap}
.btn-reset:hover{background:#218838}
.stats{margin-top:20px;background:white;border-radius:10px;padding:16px 20px;display:flex;gap:30px;flex-wrap:wrap;font-size:13px;box-shadow:0 2px 8px rgba(0,0,0,.06)}
.stats span strong{font-size:18px;display:block;color:#1B3058}
.test-login{margin-top:24px;background:#e8f5e9;border-radius:10px;padding:18px 22px}
.test-login h3{margin-bottom:12px;color:#1B3058;font-size:15px}
.test-form{display:flex;gap:10px;flex-wrap:wrap;align-items:center}
.test-form input{padding:9px 12px;border:1px solid #ccc;border-radius:6px;font-size:13px;width:200px}
.btn-test{background:#1B3058;color:white;border:none;padding:9px 20px;border-radius:6px;cursor:pointer;font-weight:600}
.btn-test:hover{background:#f21151}
code{background:#f4f4f4;padding:2px 6px;border-radius:4px;font-size:13px}
</style>
</head>
<body>
<div class="wrap">
  <h1>🏫 School Password Reset Tool</h1>
  <p class="sub">Reset login passwords for any account in <code>school_register</code>. Changes take effect immediately.</p>

  <?php if ($msg !== ''): ?>
    <div class="msg <?= $msgType ?>"><?= $msg ?></div>
  <?php endif; ?>

  <!-- Bulk Reset -->
  <div class="bulk">
    <h3>⚠️ Bulk Reset — School Owner Accounts Only</h3>
    <form method="post" class="bulk-form"
          onsubmit="return confirm('Reset ALL school-owner passwords to this value?');">
      <input type="text" name="bulk_password" placeholder="e.g. password123" value="password123" required>
      <button type="submit" name="bulk_reset_all" class="btn-bulk">Reset ALL Owner Passwords</button>
    </form>
  </div>

  <!-- Filter tabs -->
  <div class="tabs">
    <a href="?filter=all"      class="tab-link <?= $filter==='all'     ?'active':'' ?>">All (<?= $total ?>)</a>
    <a href="?filter=owners"   class="tab-link <?= $filter==='owners'  ?'active':'' ?>">School Owners (<?= $owners ?>)</a>
    <a href="?filter=teachers" class="tab-link <?= $filter==='teachers'?'active':'' ?>">Teachers / Staff (<?= $teachers ?>)</a>
  </div>

  <!-- Table -->
  <div class="tbl-wrap">
    <table>
      <thead>
        <tr>
          <th>ID</th><th>Name / School</th><th>Username</th><th>Email</th>
          <th>Type</th><th>Owner ID</th><th>Password</th><th>Status</th><th>Reset Password</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($filtered)): ?>
          <tr><td colspan="9" style="text-align:center;padding:30px;color:#999">No accounts found.</td></tr>
        <?php endif; ?>
        <?php foreach ($filtered as $a):
            $ut = (string)($a['usertype'] ?? '');
            $badge = in_array($ut,['0','admin'],true)
                ? '<span class="badge owner">Owner</span>'
                : ($ut==='1' ? '<span class="badge teacher">Teacher</span>' : '<span class="badge other">Type '.$ut.'</span>');
            $pwOk  = strpos((string)$a['password'],'$2y$') === 0;
            $pwStr = $pwOk
                ? '<span class="pw-ok">✓ Hashed</span>'
                : '<span class="pw-bad">✗ Plain: <code>'.e($a['password']).'</code></span>';
            $statusBadge = ($a['status']=='1')
                ? '<span class="badge active">Active</span>'
                : '<span class="badge inactive">Inactive</span>';
        ?>
        <tr>
          <td><?= e($a['id']) ?></td>
          <td><strong><?= e($a['name'] ?? '—') ?></strong></td>
          <td><?= e($a['username']) ?></td>
          <td><?= e($a['email']) ?></td>
          <td><?= $badge ?></td>
          <td><?= e($a['create_by_userid'] ?: '—') ?></td>
          <td><?= $pwStr ?></td>
          <td><?= $statusBadge ?></td>
          <td>
            <form method="post" class="reset-form"
                  onsubmit="return confirm('Reset password for <?= e(addslashes($a['name'] ?? $a['username'])) ?>?');">
              <input type="hidden" name="school_id" value="<?= (int)$a['id'] ?>">
              <input type="text" name="new_password" placeholder="New password" required>
              <button type="submit" name="reset_password" class="btn-reset">Reset</button>
            </form>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <!-- Stats -->
  <div class="stats">
    <span><strong><?= $total ?></strong> Total accounts</span>
    <span><strong><?= $owners ?></strong> School owners</span>
    <span><strong><?= $teachers ?></strong> Teachers / Staff</span>
    <span><strong style="color:#28a745"><?= $hashed ?></strong> Hashed ✓</span>
    <span><strong style="color:#dc3545"><?= $plain ?></strong> Plain-text ✗</span>
  </div>

  <!-- Test login -->
  <div class="test-login">
    <h3>🔐 Test Login (opens in new tab)</h3>
    <form method="post" action="../login.php" target="_blank" class="test-form">
      <input type="text"     name="username" placeholder="Username or email" required>
      <input type="text"     name="password" placeholder="Password" required>
      <button type="submit"  name="login" class="btn-test">Test Login</button>
    </form>
  </div>
</div>
</body>
</html>