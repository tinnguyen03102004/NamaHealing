<?php
// views/forgot_password.php
declare(strict_types=1);
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
require_once __DIR__ . '/../helpers/Security.php';

$token   = csrf_peek_token('forgot_password') ?? '';
$message = $message ?? '';
$stage   = $stage   ?? 'request';
$email   = $email   ?? '';
?>
<?php include 'header.php'; ?>
<main class="min-h-[75vh] flex items-center justify-center bg-gradient-to-br from-[#eafcf8] to-[#f8fafb] py-12">
  <div class="w-full max-w-sm rounded-2xl bg-white/90 shadow-lg border border-[#9dcfc3]/40 p-8 backdrop-blur-[2px]">
    <h2 class="text-center text-2xl font-bold mb-6 tracking-wide font-heading">Quên mật khẩu</h2>
    <?php if ($message): ?>
      <div class="bg-blue-50 border border-blue-300 text-blue-700 rounded-md px-3 py-2 text-sm mb-4 text-center">
        <?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?>
      </div>
    <?php endif; ?>

    <?php if ($stage === 'request'): ?>
      <form method="post" class="space-y-4" autocomplete="off">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($token, ENT_QUOTES, 'UTF-8') ?>">
        <input type="hidden" name="action" value="send_code">
        <div>
          <label class="block text-sm font-medium text-[#285F57] mb-1">Email</label>
          <input type="email" name="email" required
            class="w-full px-4 py-2 border-2 border-[#9dcfc3] rounded-lg bg-gray-50 text-[#374151] focus:outline-none focus:border-[#76a89e] focus:bg-white transition">
        </div>
        <button class="w-full mt-2 rounded-lg bg-[#9dcfc3] text-[#285F57] font-semibold py-2 shadow hover:bg-[#76a89e] hover:text-white transition">
          Gửi mã
        </button>
      </form>
    <?php elseif ($stage === 'verify'): ?>
      <form method="post" class="space-y-4" autocomplete="off">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($token, ENT_QUOTES, 'UTF-8') ?>">
        <input type="hidden" name="action" value="reset">
        <input type="hidden" name="email" value="<?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8') ?>">
        <div>
          <label class="block text-sm font-medium text-[#285F57] mb-1">Mã xác thực</label>
          <input type="text" name="code" required
            class="w-full px-4 py-2 border-2 border-[#9dcfc3] rounded-lg bg-gray-50 text-[#374151] focus:outline-none focus:border-[#76a89e] focus:bg-white transition">
        </div>
        <div>
          <label class="block text-sm font-medium text-[#285F57] mb-1">Mật khẩu mới</label>
          <input type="password" name="password" required
            class="w-full px-4 py-2 border-2 border-[#9dcfc3] rounded-lg bg-gray-50 text-[#374151] focus:outline-none focus:border-[#76a89e] focus:bg-white transition">
        </div>
        <div>
          <label class="block text-sm font-medium text-[#285F57] mb-1">Xác nhận mật khẩu</label>
          <input type="password" name="confirm_password" required
            class="w-full px-4 py-2 border-2 border-[#9dcfc3] rounded-lg bg-gray-50 text-[#374151] focus:outline-none focus:border-[#76a89e] focus:bg-white transition">
        </div>
        <button class="w-full mt-2 rounded-lg bg-[#9dcfc3] text-[#285F57] font-semibold py-2 shadow hover:bg-[#76a89e] hover:text-white transition">
          Đổi mật khẩu
        </button>
      </form>
    <?php elseif ($stage === 'success'): ?>
      <div class="text-center text-sm text-[#285F57]">
        Mật khẩu đã được cập nhật.
        <a href="login.php" class="font-semibold hover:underline">Đăng nhập</a>
      </div>
    <?php endif; ?>
  </div>
</main>
<?php include 'footer.php'; ?>
