<?php
define('REQUIRE_LOGIN', true);
require __DIR__ . '/config.php';

if (!isset($_SESSION['uid']) || ($_SESSION['role'] ?? null) !== 'student') {
    header('Location: login.php');
    exit;
}

$pageTitle = 'Chào mừng';
$error = '';
$codeValue = '';
$redirectUrl = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check($_POST['csrf'] ?? null);
    $codeValue = trim((string)($_POST['student_code'] ?? ''));

    if ($codeValue === 'VTN2025') {
        $stmt = $db->prepare('UPDATE users SET remaining = remaining + 100 WHERE id = ?');
        $success = $stmt->execute([$_SESSION['uid']]);

        if ($success) {
            if (defined('WELCOME_TEST_MODE') && WELCOME_TEST_MODE === true) {
                $redirectUrl = 'join.php?s=morning';
            } else {
                header('Location: join.php?s=morning');
                exit;
            }
        }

        $error = __('welcome_code_error');
    } else {
        $error = __('welcome_code_error');
    }
}

include 'header.php';
?>
<main class="min-h-[75vh] flex items-center justify-center py-12 px-4">
  <div class="w-full max-w-md bg-white/90 backdrop-blur rounded-2xl shadow-xl shadow-[#76a89e26] p-8 space-y-6">
    <div class="space-y-3 text-center">
      <h1 class="text-2xl font-semibold text-[#285F57]">Chào mừng bạn trở lại</h1>
      <p class="text-base text-gray-700 leading-relaxed">
        Chào mừng bạn đến với lớp thiền NamaHealing. Vui lòng nhập mã học viên để được tặng thêm buổi và vào lớp ngay, hoặc tiếp tục thanh toán theo hướng dẫn bên dưới.
      </p>
    </div>
    <?php if ($error): ?>
      <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700" role="alert">
        <?= htmlspecialchars($error) ?>
      </div>
    <?php endif; ?>
    <form method="post" class="space-y-4">
      <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
      <div class="space-y-2 text-left">
        <label for="student_code" class="block text-sm font-semibold text-[#285F57]"><?= __('welcome_code_label') ?></label>
        <input
          type="text"
          id="student_code"
          name="student_code"
          value="<?= htmlspecialchars($codeValue) ?>"
          placeholder="<?= __('welcome_code_placeholder') ?>"
          class="w-full rounded-xl border border-[#9dcfc3] bg-white/80 px-4 py-3 text-sm text-gray-700 focus:border-[#285F57] focus:ring-2 focus:ring-[#9dcfc3]"
          required
        >
      </div>
      <button
        type="submit"
        class="w-full rounded-xl bg-[#9dcfc3] px-5 py-3 text-sm font-semibold text-[#285F57] transition hover:bg-[#88c4b5] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#285F57]"
      >
        <?= __('welcome_code_submit') ?>
      </button>
    </form>
    <div class="pt-2 text-center">
      <a href="payment_info.php" class="inline-flex items-center justify-center rounded-xl border border-[#9dcfc3] px-6 py-3 text-sm font-semibold text-[#285F57] transition hover:bg-[#9dcfc3]/10">
        Thông tin thanh toán
      </a>
    </div>
  </div>
</main>
<?php include 'footer.php'; ?>
