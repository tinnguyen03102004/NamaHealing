<?php
// views/forgot_password.php
declare(strict_types=1);
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
require_once __DIR__ . '/../helpers/Security.php';

// Lấy token đã sinh trong controller (nếu bạn thích để view tự sinh, có thể dùng csrf_generate_token('forgot_password'))
$token = csrf_peek_token('forgot_password') ?? csrf_generate_token('forgot_password');
$message = $message ?? '';
?>
<!doctype html>
<html lang="vi">
<head>
  <meta charset="utf-8">
  <title>Quên mật khẩu</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- chặn cache trên trình duyệt -->
  <meta http-equiv="Cache-Control" content="no-store, no-cache, must-revalidate, max-age=0">
  <meta http-equiv="Pragma" content="no-cache">
</head>
<body>
  <?php if ($message): ?>
    <p><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></p>
  <?php endif; ?>
  <form method="post" action="forgot_password.php">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($token, ENT_QUOTES, 'UTF-8') ?>">
    <label>Email</label>
    <input type="email" name="email" required>
    <button type="submit">Gửi liên kết đặt lại</button>
  </form>
</body>
</html>
