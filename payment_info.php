<?php
require_once __DIR__ . '/i18n.php';
$pageTitle = 'Thông tin thanh toán';
include 'header.php';
?>
<main class="min-h-[75vh] flex items-center justify-center py-12">
  <div class="w-full max-w-2xl bg-white/90 rounded-xl shadow-lg p-8 space-y-6">
    <h2 class="text-2xl font-bold text-center">Nạp buổi học</h2>
    <section class="space-y-2">
      <h3 class="text-lg font-semibold">Quy định tham gia lớp học</h3>
      <p>Học viên cần tuân thủ nội quy lớp và giữ thái độ tôn trọng trong suốt buổi thiền.</p>
    </section>
    <section class="space-y-2">
      <h3 class="text-lg font-semibold">Thông tin chuyển khoản</h3>
      <p>Chủ TK: Trần Thị Mai Ly</p>
      <p>STK: 0371000429939 (Vietcombank - CN Hồ Chí Minh)</p>
    </section>
    <section class="space-y-2">
      <h3 class="text-lg font-semibold">Hướng dẫn</h3>
      <p>Sau khi chuyển khoản, vui lòng gửi ảnh biên lai kèm tên và số điện thoại cho admin qua Zalo hoặc email.</p>
      <p>Sau khi admin xác nhận, hệ thống sẽ cập nhật số buổi học vào tài khoản của bạn.</p>
    </section>
    <p class="text-sm text-gray-500">Hiện chưa hỗ trợ VNPay, sẽ cập nhật sau.</p>
  </div>
</main>
<?php include 'footer.php'; ?>
