<?php include 'header.php'; ?>
<main class="min-h-[75vh] flex items-center justify-center py-12">
  <div class="w-full max-w-md bg-white/90 rounded-xl shadow-lg p-8 text-center">
    <?php if ($status === 'success'): ?>
      <h2 class="text-2xl font-bold mb-4 text-green-600">Thanh toán thành công</h2>
      <p class="mb-2">Cảm ơn bạn đã đăng ký. Vui lòng kiểm tra email để nhận hướng dẫn tham gia lớp.</p>
    <?php else: ?>
      <h2 class="text-2xl font-bold mb-4 text-red-600">Thanh toán thất bại</h2>
      <p class="mb-2">Giao dịch không thành công. Vui lòng thử lại hoặc liên hệ hỗ trợ.</p>
    <?php endif; ?>
    <a href="home.php" class="mt-4 inline-block bg-[#9dcfc3] text-[#285F57] px-4 py-2 rounded-lg">Về trang chủ</a>
  </div>
</main>
<?php include 'footer.php'; ?>
