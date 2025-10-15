<?php
define('REQUIRE_LOGIN', true);
require 'config.php';
require_once __DIR__ . '/helpers/Notifications.php';
if (!isset($_SESSION['uid']) || $_SESSION['role'] !== 'student') {
    header('Location: login.php');
    exit;
}
// Lấy thông tin học viên
$uid = $_SESSION['uid'];
$stmt = $db->prepare("SELECT full_name, remaining FROM users WHERE id=?");
$stmt->execute([$uid]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
$remain = $user['remaining'] ?? 0;
if ($remain <= 0) {
    header('Location: welcome.php');
    exit;
}

$historyLimit = 20;
$historyStmt = $db->prepare("SELECT session, created_at FROM sessions WHERE user_id=? ORDER BY created_at DESC LIMIT $historyLimit");
$historyStmt->execute([$uid]);
$historySessions = $historyStmt->fetchAll(PDO::FETCH_ASSOC);

$attendanceStmt = $db->prepare("SELECT COUNT(*) FROM sessions WHERE user_id=?");
$attendanceStmt->execute([$uid]);
$attendanceCount = (int) $attendanceStmt->fetchColumn();
$materialsUnlocked = $attendanceCount > 0;
$materialsFlash = $_SESSION['materials_error'] ?? '';
unset($_SESSION['materials_error']);

// Thông báo
notifications_setup($db);
$notifications = notifications_fetch_active($db);
$unreadCount = notifications_unread_count($db, $uid);
$popupNotification = notifications_unread_cancellation($db, $uid);

require 'header.php';
?>

<?php if (!empty($popupNotification)): ?>
  <?php
    $scopeKey = $popupNotification['session_scope'] === 'morning'
      ? 'notification_scope_morning'
      : ($popupNotification['session_scope'] === 'evening'
          ? 'notification_scope_evening'
          : 'notification_scope_both');
    $modalTitle = $popupNotification['title'] !== null && $popupNotification['title'] !== ''
      ? htmlspecialchars($popupNotification['title'])
      : __('notification_popup_intro');
    $modalCreated = sprintf(__('notification_created_at'), date('H:i d/m/Y', strtotime($popupNotification['created_at'])));
    $modalScope = sprintf(__('notification_popup_scope'), __($scopeKey));
  ?>
  <div id="notification-modal" role="dialog" aria-modal="true" class="fixed inset-0 z-40 flex items-center justify-center bg-black/40 px-4" data-notification-id="<?= $popupNotification['id'] ?>" data-csrf="<?= $_SESSION['csrf_token']; ?>">
    <div class="glass-surface glass-card relative w-full max-w-lg rounded-2xl p-6">
      <button type="button" class="absolute top-3 right-3 text-gray-400 hover:text-gray-600 focus:outline-none" data-close aria-label="<?= __('notification_popup_close_label') ?>">
        &times;
      </button>
      <h3 class="text-xl font-semibold text-mint-text mb-2"><?= $modalTitle ?></h3>
      <div class="text-sm text-gray-700 whitespace-pre-line leading-relaxed"><?= nl2br(htmlspecialchars($popupNotification['message'])) ?></div>
      <div class="mt-3 text-xs text-gray-500 space-y-1">
        <div><?= $modalScope ?></div>
        <div><?= $modalCreated ?></div>
      </div>
      <div class="mt-5 flex justify-end">
        <button type="button" class="glass-button" data-close><?= __('notification_popup_dismiss') ?></button>
      </div>
    </div>
  </div>
<?php endif; ?>

<main class="dashboard-gradient min-h-[75vh] flex flex-col items-center justify-center px-2 py-8">
  <div class="glass-surface glass-shell w-full max-w-xl mx-auto rounded-2xl px-6 py-8">
    <h2 class="text-center text-2xl md:text-3xl font-bold text-mint-text mb-2" style="font-family:'Montserrat',sans-serif;">
      <?= sprintf(__('welcome'), htmlspecialchars($user['full_name'])) ?>
    </h2>
    <div class="text-center mb-6 text-lg font-semibold text-green-700 flex flex-col items-center">
      🌿 <span><?= sprintf(__('remaining_sessions'), $remain) ?></span>
    </div>
    <?php if ($materialsFlash): ?>
      <div class="mb-4 rounded-xl border border-yellow-200 bg-yellow-50 px-4 py-3 text-sm text-yellow-800">
        <?= htmlspecialchars($materialsFlash) ?>
      </div>
    <?php endif; ?>
    <section class="mb-6">
      <article class="glass-surface glass-card flex flex-col rounded-2xl p-6">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
          <div>
            <h3 class="text-lg font-semibold text-mint-text"><?= __('student_class_section_title') ?></h3>
            <p class="text-sm text-gray-600"><?= __('student_class_section_subtitle') ?></p>
          </div>
        </div>
        <div class="mt-5 grid gap-4 md:grid-cols-2">
          <div class="glass-surface glass-card glass-card--sub flex flex-col rounded-xl p-5">
            <div class="flex items-start gap-3">
              <span class="glass-pill glass-emoji-pill flex h-12 w-12 items-center justify-center rounded-full text-2xl" aria-hidden="true">🌞</span>
              <div>
                <h4 class="text-lg font-semibold text-mint-text"><?= __('morning_class') ?></h4>
                <p class="text-sm font-medium text-emerald-700"><?= __('morning_class_time') ?></p>
              </div>
            </div>
            <p class="mt-4 text-sm leading-relaxed text-gray-600">
              <?= __('morning_class_description') ?>
            </p>
            <div class="mt-auto pt-4">
              <a href="join.php?s=morning"
                 class="glass-button w-full">
                 <?= __('join_morning') ?>
              </a>
            </div>
          </div>
          <div class="glass-surface glass-card glass-card--sub flex flex-col rounded-xl p-5">
            <div class="flex items-start gap-3">
              <span class="glass-pill glass-emoji-pill flex h-12 w-12 items-center justify-center rounded-full text-2xl" aria-hidden="true">🌙</span>
              <div>
                <h4 class="text-lg font-semibold text-mint-text"><?= __('evening_class') ?></h4>
                <p class="text-sm font-medium text-emerald-700"><?= __('evening_class_time') ?></p>
              </div>
            </div>
            <p class="mt-4 text-sm leading-relaxed text-gray-600">
              <?= __('evening_class_description') ?>
            </p>
            <div class="mt-auto pt-4">
              <a href="join.php?s=evening"
                 class="glass-button w-full">
                 <?= __('join_evening') ?>
              </a>
            </div>
          </div>
        </div>
      </article>
    </section>
    <section class="mb-6 space-y-4">
      <article class="glass-surface glass-card flex h-full flex-col rounded-2xl p-5">
        <div class="flex items-start justify-between gap-3">
            <div class="flex items-start gap-3">
              <span class="glass-pill glass-emoji-pill flex h-12 w-12 items-center justify-center rounded-full text-2xl" aria-hidden="true">🧘</span>
            <div>
              <h3 class="text-lg font-semibold text-mint-text"><?= __('student_journal_card_title') ?></h3>
              <p class="text-sm text-gray-600"><?= __('student_journal_card_subtitle') ?></p>
            </div>
          </div>
          <span class="glass-pill glass-badge inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold uppercase tracking-wide">
            <?= __('student_journal_card_badge') ?>
          </span>
        </div>
        <p class="mt-4 text-sm leading-relaxed text-gray-600">
          <?= __('student_journal_card_description') ?>
        </p>
        <div class="mt-auto space-y-3 pt-4">
          <a href="student_journal.php"
             class="glass-button w-full">
            <?= __('student_journal_card_button') ?>
          </a>
          <a href="student_journal.php#guide" class="inline-flex items-center justify-center text-sm font-medium text-mint-text hover:text-emerald-700">
            <?= __('student_journal_card_secondary') ?>
          </a>
        </div>
      </article>
      <article class="glass-surface glass-card flex h-full flex-col rounded-2xl p-5">
        <div class="flex items-start justify-between gap-3">
            <div class="flex items-start gap-3">
              <span class="glass-pill glass-emoji-pill flex h-12 w-12 items-center justify-center rounded-full text-2xl" aria-hidden="true">📚</span>
            <div>
              <h3 class="text-lg font-semibold text-mint-text"><?= __('student_materials_card_title') ?></h3>
              <p class="text-sm text-gray-600"><?= __('student_materials_card_subtitle') ?></p>
            </div>
          </div>
          <span class="inline-flex items-center rounded-full border px-3 py-1 text-xs font-semibold uppercase tracking-wide <?= $materialsUnlocked ? 'border-white/50 bg-white/30 text-mint-text' : 'border-white/40 bg-white/20 text-gray-600' ?>">
            <?= $materialsUnlocked ? __('student_materials_card_open_badge') : __('student_materials_card_locked_badge') ?>
          </span>
        </div>
        <p class="mt-4 text-sm leading-relaxed text-gray-600">
          <?= __('student_materials_card_description') ?>
        </p>
        <p class="mt-3 text-sm font-medium <?= $materialsUnlocked ? 'text-mint-text' : 'text-gray-500' ?>">
          <?= $materialsUnlocked ? __('student_materials_card_open_hint') : __('student_materials_card_locked_hint') ?>
        </p>
        <div class="mt-auto pt-4">
          <a href="student_materials.php"
             class="glass-button w-full <?= $materialsUnlocked ? '' : 'cursor-not-allowed opacity-75' ?>"
             data-materials-link
             data-locked="<?= $materialsUnlocked ? '0' : '1' ?>"
             <?= $materialsUnlocked ? '' : 'aria-disabled="true"' ?>>
            <?= __('student_materials_card_button') ?>
          </a>
        </div>
      </article>
    </section>
    <section class="mt-8">
      <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h5 class="text-lg font-semibold text-mint-text"><?= __('recent_history') ?></h5>
          <p class="text-sm text-gray-500"><?= sprintf(__('history_table_description'), $historyLimit) ?></p>
        </div>
        <span class="glass-pill glass-badge glass-history-badge inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold uppercase tracking-wide">
          <?= sprintf(__('history_table_total'), $attendanceCount) ?>
        </span>
      </div>
      <?php if (!empty($historySessions)): ?>
        <div class="mt-4 overflow-x-auto">
          <table class="glass-surface glass-table min-w-full overflow-hidden rounded-2xl text-sm">
            <thead class="text-left">
              <tr>
                <th scope="col" class="px-4 py-3 font-semibold uppercase tracking-wide"><?= __('session') ?></th>
                <th scope="col" class="px-4 py-3 font-semibold uppercase tracking-wide"><?= __('history_table_joined_at') ?></th>
              </tr>
            </thead>
            <tbody class="divide-y divide-white/30">
              <?php foreach ($historySessions as $entry): ?>
                <tr class="transition">
                  <td class="px-4 py-3 font-medium text-gray-700">
                    <?php if ($entry['session'] === 'morning'): ?>
                      <span class="mr-1" aria-hidden="true">🌞</span><?= __('morning') ?>
                    <?php elseif ($entry['session'] === 'evening'): ?>
                      <span class="mr-1" aria-hidden="true">🌙</span><?= __('evening') ?>
                    <?php else: ?>
                      <?= htmlspecialchars($entry['session']) ?>
                    <?php endif; ?>
                  </td>
                  <td class="px-4 py-3 text-gray-600">
                    <?= htmlspecialchars(date('H:i d/m/Y', strtotime($entry['created_at']))) ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php else: ?>
        <div class="mt-4 rounded-2xl border border-dashed border-emerald-200 bg-emerald-50/60 px-5 py-6 text-center text-sm text-emerald-700">
          <p class="font-semibold"><?= __('no_history') ?></p>
          <p class="mt-1 text-emerald-600"><?= __('no_history_hint') ?></p>
        </div>
      <?php endif; ?>
    </section>
    <div class="text-center mt-4">
      <a href="change_password.php" class="text-sm text-blue-600 underline"><?= __('change_password') ?></a>
    </div>
  </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', function () {
  const canVibrate = typeof navigator !== 'undefined' && typeof navigator.vibrate === 'function';
  const vibrate = function () {
    if (!canVibrate) {
      return;
    }
    try {
      navigator.vibrate(10);
    } catch (error) {
      // Ignore vibration errors silently to avoid interrupting the interaction.
    }
  };

  document.querySelectorAll('.glass-button').forEach(function (button) {
    const isInteractive = function () {
      return button.getAttribute('aria-disabled') !== 'true' && !button.hasAttribute('disabled') && !button.classList.contains('disabled');
    };

    const setPressed = function (pressed) {
      button.classList.toggle('is-pressed', pressed);
      if (pressed && isInteractive()) {
        vibrate();
      }
    };

    button.addEventListener('pointerdown', function (event) {
      if (event.button !== 0 && event.pointerType === 'mouse') {
        return;
      }
      if (!isInteractive()) {
        return;
      }
      setPressed(true);
    });

    const resetPressed = function () {
      setPressed(false);
    };

    ['pointerup', 'pointercancel', 'pointerleave', 'blur'].forEach(function (evt) {
      button.addEventListener(evt, resetPressed);
    });

    button.addEventListener('keydown', function (event) {
      if (!isInteractive()) {
        return;
      }
      if (event.key === 'Enter' || event.key === ' ') {
        setPressed(true);
      }
    });

    button.addEventListener('keyup', function () {
      resetPressed();
    });
  });
});
</script>

<?php if (!$materialsUnlocked): ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
  var materialsLink = document.querySelector('[data-materials-link][data-locked="1"]');
  if (materialsLink) {
    const message = <?= json_encode(__('student_materials_locked_message'), JSON_UNESCAPED_UNICODE); ?>;
    materialsLink.addEventListener('click', function (event) {
      event.preventDefault();
      alert(message);
    });
  }
});
</script>
<?php endif; ?>

<?php include 'footer.php'; ?>
