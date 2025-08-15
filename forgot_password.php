<?php
require 'config.php';
require 'header.php';
?>
<main class="min-h-[75vh] flex items-center justify-center px-2 py-8">
  <div class="w-full max-w-md bg-white/95 rounded-2xl shadow-2xl shadow-[#76a89e26] px-6 py-8">
    <h2 class="text-center text-2xl font-bold text-mint-text mb-4" style="font-family:'Montserrat',sans-serif;">
      <?= __('forgot_password') ?>
    </h2>
    <p class="text-center text-mint-text mb-6">
      <?= __('forgot_password_instruction') ?>
    </p>
    <div class="text-center">
      <a href="login.php" class="underline text-[#285F57] font-medium">
        <?= __('back') ?>
      </a>
    </div>
  </div>
</main>
<?php include 'footer.php'; ?>
