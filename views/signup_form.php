<?php include 'header.php'; ?>
<main class="min-h-[75vh] flex items-center justify-center py-12">
  <div class="w-full max-w-md bg-white/90 rounded-xl shadow-lg p-8">
    <h2 class="text-center text-2xl font-bold mb-6">Đăng ký lớp thiền</h2>
    <form method="post" class="space-y-4" id="signup-form">
      <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">
      <?php if (!empty($error)): ?>
      <div class="text-red-600 text-sm"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>
      <div>
        <label class="block mb-1">Họ tên</label>
        <input type="text" name="full_name" required class="w-full px-4 py-2 border rounded-lg" />
      </div>
      <div>
        <label class="block mb-1">Email</label>
        <input type="email" name="email" required class="w-full px-4 py-2 border rounded-lg" />
      </div>
      <div>
        <label class="block mb-1">Số điện thoại</label>
        <input type="tel" name="phone" required class="w-full px-4 py-2 border rounded-lg" />
      </div>
      <div>
        <label class="block mb-1">Số buổi</label>
        <input type="text" value="20" disabled class="w-full px-4 py-2 border rounded-lg bg-gray-100" />
        <input type="hidden" name="sessions" value="20" />
      </div>
      <div>
        <label class="block mb-1">Học phí</label>
        <input type="text" id="tuition" value="8,000,000 VND" disabled class="w-full px-4 py-2 border rounded-lg bg-gray-100" />
      </div>
      <div class="flex items-center">
        <input type="checkbox" id="financial_aid" name="financial_aid" class="mr-2">
        <label for="financial_aid" class="text-sm">Tôi hiện đang sống ở Việt Nam và gặp khó khăn về vấn đề tài chính</label>
      </div>
      <div class="flex items-start">
        <input type="checkbox" id="mental_health" name="mental_health" class="mr-2 mt-1" required>
        <label for="mental_health" class="text-sm">Tôi hiện đang mắc một trong các triệu chứng tâm lý như trầm cảm, rối loạn lo âu, rối loạn lưỡng cực, mất ngủ, căng thẳng, stress,...</label>
      </div>
      <button class="w-full bg-[#9dcfc3] text-[#285F57] font-semibold py-2 rounded-lg" name="create_order">Đăng ký</button>
    </form>
    <script>
      const financial = document.getElementById('financial_aid');
      const tuition = document.getElementById('tuition');
      financial.addEventListener('change', () => {
        tuition.value = financial.checked ? '5,000,000 VND' : '8,000,000 VND';
      });
    </script>
  </div>
</main>
<?php include 'footer.php'; ?>
