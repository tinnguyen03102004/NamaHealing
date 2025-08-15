<?php include 'header.php'; ?>
<main class="min-h-[75vh] flex items-center justify-center bg-gradient-to-br from-[#eafcf8] to-[#f8fafb] py-12">
  <div class="w-full max-w-sm rounded-2xl bg-white/90 shadow-lg border border-[#9dcfc3]/40 p-8 backdrop-blur-[2px]">
    <h2 class="text-center text-2xl font-bold mb-6 tracking-wide font-heading">Forgot Password</h2>
    <?php if (!empty($message)): ?>
      <div class="bg-green-50 border border-green-300 text-green-700 rounded-md px-3 py-2 text-sm mb-4 text-center">
        <?= htmlspecialchars($message) ?>
      </div>
    <?php endif; ?>
    <form method="post" class="space-y-5">
      <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">
      <div>
        <label class="block text-sm font-medium text-[#285F57] mb-1">Email</label>
        <input name="email" type="email" required autofocus
          class="w-full px-4 py-2 border-2 border-[#9dcfc3] rounded-lg bg-gray-50 text-[#374151] focus:outline-none focus:border-[#76a89e] focus:bg-white transition" />
      </div>
      <button class="w-full mt-2 rounded-lg bg-[#9dcfc3] text-[#285F57] font-semibold py-2 shadow hover:bg-[#76a89e] hover:text-white transition">Send Reset Link</button>
    </form>
  </div>
</main>
<?php include 'footer.php'; ?>
