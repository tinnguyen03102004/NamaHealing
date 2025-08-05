<?php
require_once __DIR__ . '/i18n.php';
$pageTitle = 'Thông tin thanh toán';
include 'header.php';
?>
<main class="min-h-[75vh] flex items-center justify-center py-12 bg-black/40">
  <div class="bg-white rounded-2xl w-[96vw] max-w-lg p-4 sm:p-6 relative shadow-2xl overflow-y-auto max-h-[95vh] leading-relaxed">

    <!-- Cảnh báo nổi bật đầu trang -->
    <div class="mb-3 px-2 py-2 bg-red-50 border border-red-300 rounded text-red-700 text-base font-semibold text-center tracking-wider leading-snug">
      <span class="uppercase font-bold block">
        <?= __('register_warning_line1') ?>
      </span>
      <span class="block mt-1 text-red-700">
        <?= __('register_warning_line2') ?>
      </span>
    </div>

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
        <li>Gửi biên lai học phí cho admin (qua Zalo góc phải) để cập nhật số buổi.</li>
      </ul>
    </div>
    <div class="mt-2 text-sm text-gray-500 text-center">
      <?= __('register_support') ?>
    </div>
  </div>
</main>
<?php include 'footer.php'; ?>
