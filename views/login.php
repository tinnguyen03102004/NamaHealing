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
      <a href="#" id="open-register-modal" class="text-[#285F57] hover:underline font-medium transition"><?= __('register_link') ?></a>
    </div>
  </div>
</main>

<!-- Modal: Hướng dẫn đăng ký lớp học (từ home.php) -->
<div id="register-modal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 hidden">
  <div class="bg-white rounded-2xl w-[96vw] max-w-lg p-4 sm:p-6 relative shadow-2xl overflow-y-auto max-h-[95vh] leading-relaxed">
    <div class="mb-3 px-2 py-2 bg-red-50 border border-red-300 rounded text-red-700 text-base font-semibold text-center tracking-wider leading-snug">
      <span class="uppercase font-bold block">
        <?= __('register_warning_line1') ?>
      </span>
      <span class="block mt-1 text-red-700">
        <?= __('register_warning_line2') ?>
      </span>
    </div>
    <button id="close-register-modal" class="absolute top-2 right-2 w-10 h-10 rounded-full bg-gray-100 hover:bg-gray-200 flex items-center justify-center text-xl font-bold z-10">&times;</button>
    <div class="text-lg font-semibold mb-2 text-center mt-1"><?= __('register_guide_title') ?></div>
    <ol class="list-decimal pl-5 sm:pl-6 text-base mb-4 space-y-3">
      <li>
        <span class="font-medium"><?= __('register_time_title') ?></span>
        <?= __('register_time_desc') ?>
        <ul class="list-disc pl-5 sm:pl-6 space-y-1">
          <li><?= __('register_time_morning') ?></li>
          <li><?= __('register_time_evening') ?></li>
        </ul>
      </li>
      <li>
        <span class="font-medium"><?= __('register_fee_title') ?></span>
        <ul class="list-disc pl-5 sm:pl-6">
          <li><?= __('register_fee_full') ?></li>
          <li><?= __('register_fee_discount') ?></li>
        </ul>
        <?= __('register_bank_title') ?>
        <ul class="list-disc pl-5 sm:pl-6 mt-1 space-y-2">
          <li><?= __('register_bank_holder') ?></li>
          <li><?= __('register_bank_account') ?></li>
          <li><?= __('register_bank_note') ?></li>
        </ul>
        <div class="mt-2 text-red-500 text-[15px] font-medium">
          <?= __('register_fee_note') ?>
        </div>
      </li>
    </ol>
    <div class="mb-3">
      <span class="block font-medium"><?= __('register_steps_title') ?></span>
      <ul class="list-decimal pl-5 sm:pl-6 mt-1 space-y-3">
        <li><?= __('register_step1') ?></li>
        <li><?= __('register_step2') ?></li>
        <li><?= __('register_step3') ?></li>
        <li><?= __('register_step4') ?></li>
      </ul>
    </div>
    <div class="mt-2 text-sm text-gray-500 text-center">
      <?= __('register_support') ?>
    </div>
  </div>
</div>

<script>
document.getElementById('open-register-modal').onclick = function(e) {
  e.preventDefault();
  document.getElementById('register-modal').classList.remove('hidden');
};
document.getElementById('close-register-modal').onclick = function() {
  document.getElementById('register-modal').classList.add('hidden');
};
document.getElementById('register-modal').onclick = function(e) {
  if (e.target === this) this.classList.add('hidden');
};
</script>

<?php include 'footer.php'; ?>
