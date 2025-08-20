<?php
// login_diag.php
require __DIR__.'/config.php';
use NamaHealing\Models\UserModel;

function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

$report = [];
if ($_SERVER['REQUEST_METHOD']==='POST') {
  $report[] = 'POST received';

  // 1) CSRF
  $tokenPost = $_POST['csrf_token'] ?? '';
  $tokenSess = $_SESSION['csrf_token'] ?? null;
  $report[] = 'CSRF in POST: '.($tokenPost ? 'yes' : 'no');
  $report[] = 'CSRF in SESSION: '.($tokenSess ? 'yes' : 'no');
  $report[] = 'CSRF match: '.(($tokenPost && $tokenSess && hash_equals($tokenSess, $tokenPost)) ? 'YES' : 'NO');

  // 2) Identifier chuẩn hoá như app
  $identifier = trim($_POST['identifier'] ?? '');
  if (strpos($identifier,'@')===false) {
    $identifier = preg_replace('/\D+/', '', $identifier);
  }
  $report[] = 'Identifier used: '.h($identifier);

  try {
    // 3) Tìm user
    $um = new UserModel($db);
    $user = $um->findByIdentifier($identifier);
    $report[] = 'User found: '.($user ? 'YES' : 'NO');

    if ($user) {
      $report[] = 'User id: '.$user['id'].' | role: '.$user['role'];
      // 4) Verify bcrypt
      $ok = password_verify($_POST['password'] ?? '', $user['password']);
      $report[] = 'Password verify: '.($ok ? 'TRUE' : 'FALSE');

      // 5) Điều kiện role như app
      $allowed = in_array($user['role'], ['student','teacher','admin'], true);
      $report[] = 'Role allowed: '.($allowed ? 'YES' : 'NO');

      if ($ok && $allowed && $tokenPost && $tokenSess && hash_equals($tokenSess, $tokenPost)) {
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['id'];
        $report[] = '=> LOGIN SUCCESS. Session user_id='. $_SESSION['user_id'];
      } else {
        $report[] = '=> LOGIN FAIL due to '.(
          !$ok ? 'password ' : (
          !$allowed ? 'role ' : (
          !($tokenPost && $tokenSess && hash_equals($tokenSess, $tokenPost)) ? 'CSRF ' : 'unknown'
        )));
      }
    }
  } catch (Throwable $e) {
    $report[] = 'DB/Code error: '.$e->getMessage();
  }
}

// Bảo đảm có CSRF token để render form
if (empty($_SESSION['csrf_token'])) {
  $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>
<!doctype html><meta charset="utf-8">
<style>body{font:14px/1.4 system-ui;margin:24px} input{padding:8px;margin:6px 0;width:360px} .log{background:#111;color:#0f0;padding:12px;white-space:pre-wrap}</style>
<h2>NamaHealing – Login Diagnostic</h2>
<form method="post">
  <label>Email hoặc SĐT</label><br>
  <input name="identifier" placeholder="email@example.com hoặc 09..." required><br>
  <label>Mật khẩu</label><br>
  <input name="password" type="password" required><br>
  <input type="hidden" name="csrf_token" value="<?=h($_SESSION['csrf_token'])?>">
  <button>Test Login</button>
</form>

<?php if($report): ?>
  <h3>Kết quả</h3>
  <div class="log"><?=h(implode("\n",$report))?></div>
<?php endif; ?>
