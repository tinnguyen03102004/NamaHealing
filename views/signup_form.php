<?php include 'header.php'; ?>
<main class="min-h-[75vh] flex items-center justify-center py-12">
  <div class="w-full max-w-md bg-white/90 rounded-xl shadow-lg p-8">
    <h2 class="text-center text-2xl font-bold mb-6">Đăng ký lớp thiền</h2>
    <form method="post" class="space-y-4">
      <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">
      <div>
        <label class="block mb-1">Họ tên</label>
        <input type="text" name="full_name" required class="w-full px-4 py-2 border rounded-lg" />
      </div>
      <div>
        <label class="block mb-1">Email</label>
        <input type="email" name="email" required class="w-full px-4 py-2 border rounded-lg" />
      </div>
      <div>
        <label class="block mb-1">Số buổi</label>
        <input type="number" name="sessions" value="1" min="1" class="w-full px-4 py-2 border rounded-lg" />
      </div>
      <button class="w-full bg-[#9dcfc3] text-[#285F57] font-semibold py-2 rounded-lg" name="create_order">Tiếp tục</button>
    </form>
  </div>
</main>
<?php include 'footer.php'; ?>
