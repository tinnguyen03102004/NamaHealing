<?php
require 'config.php';

$token = $_GET['token'] ?? '';
$verified = false;
if ($token) {
    $stmt = $db->prepare('UPDATE users SET verified=1, verify_token=NULL WHERE verify_token=?');
    $stmt->execute([$token]);
    $verified = $stmt->rowCount() > 0;
}

include 'header.php';
?>
<main class="min-h-[75vh] flex items-center justify-center">
  <div class="bg-white/90 rounded-xl shadow-lg p-8 text-center">
    <?php if ($verified): ?>
      <p>Tài khoản của bạn đã được xác thực. <a href="login.php" class="text-blue-600 underline">Đăng nhập</a></p>
    <?php else: ?>
      <p>Token không hợp lệ hoặc đã được sử dụng.</p>
    <?php endif; ?>
  </div>
</main>
<?php include 'footer.php'; ?>
