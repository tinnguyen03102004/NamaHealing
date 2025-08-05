<?php require 'header.php'; ?>
<main class="flex min-h-[70vh] items-center justify-center bg-transparent">
  <div class="w-full max-w-md bg-white/95 rounded-2xl shadow-2xl shadow-[#76a89e26] px-6 py-8 mx-auto">
    <h3 class="font-heading text-center text-2xl mb-6 text-mint-text"><?= __('register_heading') ?></h3>
    <?php if ($err): ?>
      <div class="bg-red-50 border border-red-300 text-red-700 rounded-md px-3 py-2 text-sm mb-4 text-center">
        <?= htmlspecialchars($err) ?>
      </div>
    <?php endif; ?>
    <form method="post" class="space-y-4">
      <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">
      <div>
        <label class="block text-sm font-medium text-mint-text mb-1"><?= __('name_label') ?></label>
        <input type="text" name="full_name" class="w-full px-3 py-2 border border-mint rounded-lg bg-gray-50 text-gray-800 focus:outline-none focus:border-mint-dark focus:bg-white transition" required>
      </div>
      <div>
        <label class="block text-sm font-medium text-mint-text mb-1"><?= __('email_label') ?></label>
        <input type="text" name="email" class="w-full px-3 py-2 border border-mint rounded-lg bg-gray-50 text-gray-800 focus:outline-none focus:border-mint-dark focus:bg-white transition" required>
      </div>
      <div>
        <label class="block text-sm font-medium text-mint-text mb-1"><?= __('phone_label') ?></label>
        <input type="text" name="phone" class="w-full px-3 py-2 border border-mint rounded-lg bg-gray-50 text-gray-800 focus:outline-none focus:border-mint-dark focus:bg-white transition" required>
      </div>
      <div>
        <label class="block text-sm font-medium text-mint-text mb-1"><?= __('password_label') ?></label>
        <input type="password" name="password" class="w-full px-3 py-2 border border-mint rounded-lg bg-gray-50 text-gray-800 focus:outline-none focus:border-mint-dark focus:bg-white transition" required>
      </div>
      <div class="flex justify-between items-center mt-6 gap-2">
        <a class="rounded-lg border border-mint text-mint-text px-4 py-2 text-sm font-medium hover:bg-mint hover:text-white transition text-center" href="admin.php">
          <?= __('back') ?>
        </a>
        <button class="rounded-lg bg-mint text-mint-text font-semibold px-5 py-2 text-sm shadow hover:bg-mint-dark hover:text-white transition" type="submit">
          <?= __('create_student') ?>
        </button>
      </div>
    </form>
  </div>
</main>
<?php include 'footer.php'; ?>
