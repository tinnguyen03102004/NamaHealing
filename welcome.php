<?php
define('REQUIRE_LOGIN', true);
require __DIR__ . '/config.php';

if (!isset($_SESSION['uid']) || ($_SESSION['role'] ?? null) !== 'student') {
    header('Location: login.php');
    exit;
}

$pageTitle = 'Chào mừng';

include 'header.php';
?>
<main class="min-h-[75vh] flex items-center justify-center py-12 px-4">
  <div class="w-full max-w-md bg-white/90 backdrop-blur rounded-2xl shadow-xl shadow-[#76a89e26] p-8 space-y-6">
    <div class="space-y-3 text-center">
      <h1 class="text-2xl font-semibold text-[#285F57]">Chào mừng bạn trở lại</h1>
      <p class="text-base text-gray-700 leading-relaxed">
        Chào mừng bạn đến với lớp thiền NamaHealing. Vui lòng tiếp tục thanh toán theo hướng dẫn bên dưới để tham gia lớp học.
      </p>
    </div>
    <div class="pt-2 text-center">
      <a href="payment_info.php" class="inline-flex items-center justify-center rounded-xl border border-[#9dcfc3] px-6 py-3 text-sm font-semibold text-[#285F57] transition hover:bg-[#9dcfc3]/10">
        Thông tin thanh toán
      </a>
    </div>
  </div>
</main>
<?php include 'footer.php'; ?>
