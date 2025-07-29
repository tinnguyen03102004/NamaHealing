<?php include 'header.php'; ?>
<main class="min-h-[75vh] flex items-center justify-center bg-gradient-to-br from-[#eafcf8] to-[#f8fafb] py-12">
  <div class="w-full max-w-sm rounded-2xl bg-white/90 shadow-lg border border-[#9dcfc3]/40 p-8 backdrop-blur-[2px]">
    <h2 class="text-center text-2xl font-bold mb-6 tracking-wide font-heading"><?= __('login_heading') ?></h2>
    <?php if ($err): ?>
      <div class="bg-red-50 border border-red-300 text-red-700 rounded-md px-3 py-2 text-sm mb-4 text-center"><?= htmlspecialchars($err) ?></div>
    <?php endif; ?>
    <form method="post" autocomplete="off" class="space-y-5">
      <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">
      <div>
        <label class="block text-sm font-medium text-[#285F57] mb-1"><?= __('email_label') ?></label>
        <input name="email" type="text" required autofocus
          class="w-full px-4 py-2 border-2 border-[#9dcfc3] rounded-lg bg-gray-50 text-[#374151] focus:outline-none focus:border-[#76a89e] focus:bg-white transition"
          placeholder="<?= __('email_placeholder') ?>" />
      </div>
      <div>
        <label class="block text-sm font-medium text-[#285F57] mb-1"><?= __('password_label') ?></label>
        <input name="password" type="password" required
          class="w-full px-4 py-2 border-2 border-[#9dcfc3] rounded-lg bg-gray-50 text-[#374151] focus:outline-none focus:border-[#76a89e] focus:bg-white transition"
          placeholder="<?= __('password_placeholder') ?>" />
      </div>
      <button class="w-full mt-2 rounded-lg bg-[#9dcfc3] text-[#285F57] font-semibold py-2 shadow hover:bg-[#76a89e] hover:text-white transition">
        <?= __('login_button') ?>
      </button>
    </form>
    <div class="mt-5 text-center text-sm text-gray-500">
      <?= __('no_account') ?>
      <a href="register.php" class="text-[#285F57] hover:underline font-medium transition"><?= __('register_link') ?></a>
    </div>
  </div>
</main>
<?php include 'footer.php'; ?>
