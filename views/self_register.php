<?php include 'header.php'; ?>
<main class="min-h-[75vh] flex items-center justify-center py-12">
  <div class="w-full max-w-md bg-white/90 rounded-xl shadow-lg p-8">
    <h2 class="text-center text-2xl font-bold mb-4">Đăng ký tài khoản</h2>
    <p class="text-center text-gray-600 mb-6">Tham gia thiền cùng thầy Võ Trọng Nghĩa để sớm cải thiện sức khỏe tâm lý.</p>
    <?php if (!empty($err)): ?>
      <div class="text-red-600 text-sm mb-4"><?= htmlspecialchars($err) ?></div>
    <?php endif; ?>
    <form method="post" class="space-y-4">
      <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">
      <div>
        <label class="block mb-1"><?= __('name_label') ?></label>
        <input type="text" name="full_name" required class="w-full px-4 py-2 border rounded-lg" />
      </div>
      <div>
        <label class="block mb-1"><?= __('email_label') ?></label>
        <input type="email" name="email" required class="w-full px-4 py-2 border rounded-lg" />
      </div>
      <div>
        <label class="block mb-1"><?= __('phone_label') ?></label>
        <input type="text" name="phone" required class="w-full px-4 py-2 border rounded-lg" />
      </div>
      <button class="w-full bg-[#9dcfc3] text-[#285F57] font-semibold py-2 rounded-lg">Đăng ký</button>
    </form>
  </div>
</main>
<?php include 'footer.php'; ?>
