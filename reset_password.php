<?php
require 'config.php';

$token = $_GET['token'] ?? '';
$valid = false;
$userId = null;

if ($token) {
    $stmt = $db->prepare('SELECT user_id, expires_at FROM password_resets WHERE token=?');
    $stmt->execute([$token]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row && strtotime($row['expires_at']) > time()) {
        $valid = true;
        $userId = (int)$row['user_id'];
    }
}

$success = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check($_POST['csrf_token'] ?? null);
    $token = $_POST['token'] ?? '';

    $stmt = $db->prepare('SELECT user_id, expires_at FROM password_resets WHERE token=?');
    $stmt->execute([$token]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row || strtotime($row['expires_at']) <= time()) {
        $error = 'Token không hợp lệ hoặc đã hết hạn.';
    } else {
        $password = $_POST['password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';
        if ($password !== $confirm) {
            $error = 'Mật khẩu xác nhận không khớp.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $db->prepare('UPDATE users SET password=? WHERE id=?')->execute([$hash, $row['user_id']]);
            $db->prepare('DELETE FROM password_resets WHERE token=?')->execute([$token]);
            $success = true;
        }
    }
}

include 'header.php';
?>
<main class="min-h-[75vh] flex items-center justify-center px-2 py-8">
  <div class="w-full max-w-md bg-white/95 rounded-2xl shadow-2xl shadow-[#76a89e26] px-6 py-8">
    <h2 class="text-center text-2xl font-bold text-mint-text mb-4" style="font-family:'Montserrat',sans-serif;">Đặt lại mật khẩu</h2>
    <?php if ($success): ?>
      <div class="mb-4 p-3 rounded bg-green-100 text-green-700 text-sm">Mật khẩu đã được cập nhật.</div>
    <?php elseif (!$valid && $_SERVER['REQUEST_METHOD'] !== 'POST'): ?>
      <div class="mb-4 p-3 rounded bg-red-100 text-red-700 text-sm">Token không hợp lệ hoặc đã hết hạn.</div>
    <?php else: ?>
      <?php if ($error): ?>
        <div class="mb-4 p-3 rounded bg-red-100 text-red-700 text-sm"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>
      <?php if ($valid || $_SERVER['REQUEST_METHOD'] === 'POST'): ?>
      <form method="post" class="flex flex-col gap-3">
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">
        <input type="hidden" name="token" value="<?= htmlspecialchars($token); ?>">
        <label>
          <span class="font-semibold text-mint-text">Mật khẩu mới</span>
          <input type="password" name="password" class="mt-1 w-full rounded border border-mint px-3 py-2 focus:border-mint-dark focus:ring-mint" required>
        </label>
        <label>
          <span class="font-semibold text-mint-text">Xác nhận mật khẩu</span>
          <input type="password" name="confirm_password" class="mt-1 w-full rounded border border-mint px-3 py-2 focus:border-mint-dark focus:ring-mint" required>
        </label>
        <button class="rounded-lg bg-mint text-mint-text font-semibold px-4 py-2 text-sm shadow hover:bg-mint-dark hover:text-white transition">Cập nhật mật khẩu</button>
      </form>
      <?php endif; ?>
    <?php endif; ?>
  </div>
</main>
<?php include 'footer.php'; ?>
