<?php include 'header.php'; ?>
<main class="flex min-h-[70vh] items-center justify-center bg-transparent px-4 py-8">
  <div class="w-full max-w-md bg-white/95 rounded-2xl shadow-2xl shadow-[#76a89e26] px-6 py-8">
    <?php if ($sent): ?>
      <h3 class="text-center text-2xl font-heading mb-4 text-mint-text"><?= __('bill_heading') ?></h3>
      <div class="text-center text-green-700 mb-6"><?= __('bill_success') ?></div>
      <div class="flex justify-center">
        <a href="dashboard.php" class="rounded-lg bg-mint text-mint-text font-semibold px-5 py-2 shadow hover:bg-mint-dark hover:text-white transition"><?= __('back_to_dashboard') ?></a>
      </div>
    <?php else: ?>
      <h3 class="text-center text-2xl font-heading mb-6 text-mint-text"><?= __('bill_heading') ?></h3>
      <form method="post" enctype="multipart/form-data" class="space-y-4">
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">
        <div>
          <label class="block text-sm font-medium text-mint-text mb-1"><?= __('bill_file_label') ?></label>
          <input type="file" name="bill" class="w-full text-sm text-gray-700" accept="image/*,application/pdf">
        </div>
        <div>
          <label class="block text-sm font-medium text-mint-text mb-1"><?= __('bill_note_label') ?></label>
          <textarea name="note" rows="4" class="w-full px-3 py-2 border border-mint rounded-lg bg-gray-50 text-gray-800 focus:outline-none focus:border-mint-dark focus:bg-white transition"></textarea>
        </div>
        <div class="flex justify-between items-center mt-6 gap-2">
          <a class="rounded-lg border border-mint text-mint-text px-4 py-2 text-sm font-medium hover:bg-mint hover:text-white transition text-center" href="dashboard.php"><?= __('back_to_dashboard') ?></a>
          <button class="rounded-lg bg-mint text-mint-text font-semibold px-5 py-2 text-sm shadow hover:bg-mint-dark hover:text-white transition" type="submit"><?= __('bill_submit') ?></button>
        </div>
      </form>
    <?php endif; ?>
  </div>
</main>
<?php include 'footer.php'; ?>

