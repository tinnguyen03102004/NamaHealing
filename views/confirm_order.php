<?php include 'header.php'; ?>
<main class="min-h-[75vh] flex items-center justify-center py-12">
  <div class="w-full max-w-md bg-white/90 rounded-xl shadow-lg p-8">
    <h2 class="text-center text-2xl font-bold mb-6">Xác nhận đơn hàng</h2>
    <div class="space-y-2 mb-4">
      <div><strong>Họ tên:</strong> <?= htmlspecialchars($order['full_name']) ?></div>
      <div><strong>Email:</strong> <?= htmlspecialchars($order['email']) ?></div>
      <div><strong>Số buổi:</strong> <?= (int)$order['sessions'] ?></div>
      <div><strong>Thanh toán:</strong> <?= number_format($order['amount']) ?> VND</div>
    </div>
    <form method="post">
      <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">
      <button name="pay_vnpay" class="w-full bg-[#9dcfc3] text-[#285F57] font-semibold py-2 rounded-lg">
        Thanh toán bằng VNPay QR
      </button>
    </form>
  </div>
</main>
<?php include 'footer.php'; ?>
