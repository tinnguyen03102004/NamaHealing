<?php
define('REQUIRE_LOGIN', true);
require __DIR__ . '/config.php';

if (!isset($_SESSION['uid']) || ($_SESSION['role'] ?? null) !== 'student') {
    header('Location: login.php');
    exit;
}

$pageTitle = 'Chào mừng';

include 'header.php';
?>
<main class="min-h-[75vh] flex items-center justify-center py-12 px-4">
  <div class="w-full max-w-md bg-white/90 backdrop-blur rounded-2xl shadow-xl shadow-[#76a89e26] p-8 space-y-6">
    <div class="space-y-3 text-center">
      <h1 class="text-2xl font-semibold text-[#285F57]">Chào mừng bạn đến với lớp thiền NamaHealing</h1>
      <p class="text-base text-gray-700 leading-relaxed">
        Vui lòng tiếp tục thanh toán theo hướng dẫn bên dưới để tham gia lớp học.
      </p>
    </div>
    <div class="pt-2 text-center">
      <button
        type="button"
        id="payment-info-trigger"
        class="inline-flex items-center justify-center rounded-xl border border-[#9dcfc3] px-6 py-3 text-sm font-semibold text-[#285F57] transition hover:bg-[#9dcfc3]/10 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#285F57]"
        aria-haspopup="dialog"
        aria-expanded="false"
        aria-controls="payment-info-modal"
      >
        Thông tin thanh toán
      </button>
    </div>
  </div>
</main>
<div
  id="payment-info-modal"
  class="fixed inset-0 z-50 hidden items-center justify-center px-4 py-10 bg-black/60"
  role="dialog"
  aria-modal="true"
  aria-labelledby="payment-info-title"
  aria-hidden="true"
>
  <div class="absolute inset-0" data-modal-close></div>
  <div
    class="relative w-full max-w-2xl sm:max-w-lg bg-white rounded-2xl shadow-2xl overflow-y-auto max-h-[90vh] p-5 sm:p-7"
    data-modal-card
  >
    <button
      type="button"
      class="absolute top-3 right-3 text-gray-400 hover:text-gray-600 focus:outline-none"
      data-modal-close
      aria-label="Đóng"
    >
      <span aria-hidden="true">&times;</span>
    </button>
    <div class="mb-3 px-3 py-3 bg-red-50 border border-red-300 rounded text-red-700 text-base font-semibold text-center leading-snug">
      <span class="uppercase font-bold block">
        <?= __('register_warning_line1') ?>
      </span>
      <span class="block mt-1 text-red-700">
        <?= __('register_warning_line2') ?>
      </span>
    </div>
    <div class="text-lg font-semibold mb-3 text-center" id="payment-info-title"><?= __('register_guide_title') ?></div>
    <ol class="list-decimal pl-5 sm:pl-6 text-base mb-5 space-y-3 text-left">
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
    <div class="mb-4 text-left">
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
</div>
<script>
  document.addEventListener('DOMContentLoaded', () => {
    const trigger = document.getElementById('payment-info-trigger');
    const modal = document.getElementById('payment-info-modal');
    if (!trigger || !modal) {
      return;
    }

    const modalCard = modal.querySelector('[data-modal-card]');
    const closeElements = modal.querySelectorAll('[data-modal-close]');
    const focusableSelectors = 'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])';
    let lastFocusedElement = null;

    const hideModal = () => {
      modal.classList.add('hidden');
      modal.classList.remove('flex');
      modal.setAttribute('aria-hidden', 'true');
      trigger.setAttribute('aria-expanded', 'false');
      if (lastFocusedElement) {
        lastFocusedElement.focus();
      }
      document.removeEventListener('keydown', handleKeydown);
    };

    const handleKeydown = (event) => {
      if (event.key === 'Escape') {
        event.preventDefault();
        hideModal();
      } else if (event.key === 'Tab') {
        const focusable = modalCard ? Array.from(modalCard.querySelectorAll(focusableSelectors)).filter(el => !el.hasAttribute('disabled')) : [];
        if (focusable.length === 0) {
          event.preventDefault();
          return;
        }
        const first = focusable[0];
        const last = focusable[focusable.length - 1];
        if (event.shiftKey) {
          if (document.activeElement === first) {
            event.preventDefault();
            last.focus();
          }
        } else if (document.activeElement === last) {
          event.preventDefault();
          first.focus();
        }
      }
    };

    const showModal = () => {
      lastFocusedElement = document.activeElement instanceof HTMLElement ? document.activeElement : trigger;
      modal.classList.remove('hidden');
      modal.classList.add('flex');
      modal.setAttribute('aria-hidden', 'false');
      trigger.setAttribute('aria-expanded', 'true');
      const firstFocusable = modalCard ? modalCard.querySelector(focusableSelectors) : null;
      if (firstFocusable) {
        firstFocusable.focus();
      }
      document.addEventListener('keydown', handleKeydown);
    };

    trigger.addEventListener('click', (event) => {
      event.preventDefault();
      showModal();
    });

    closeElements.forEach((element) => {
      element.addEventListener('click', (event) => {
        event.preventDefault();
        hideModal();
      });
    });

    modal.addEventListener('click', (event) => {
      if (event.target === modal || event.target.hasAttribute('data-modal-close')) {
        hideModal();
      }
    });
  });
</script>
<?php include 'footer.php'; ?>
