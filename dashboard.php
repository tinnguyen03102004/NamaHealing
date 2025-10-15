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

<style>
  .dashboard-gradient {
    background: radial-gradient(circle at 10% 20%, rgba(213, 243, 235, 0.55), transparent 60%),
                radial-gradient(circle at 90% 10%, rgba(186, 225, 242, 0.45), transparent 55%),
                linear-gradient(135deg, rgba(243, 251, 248, 0.95), rgba(235, 246, 241, 0.9));
  }
  .glass-shell {
    background: linear-gradient(145deg, rgba(255, 255, 255, 0.72), rgba(255, 255, 255, 0.45));
    border: 1px solid rgba(255, 255, 255, 0.55);
    box-shadow: 0 24px 48px rgba(86, 146, 132, 0.22);
    backdrop-filter: blur(22px);
  }
  .glass-card {
    background: linear-gradient(160deg, rgba(255, 255, 255, 0.65), rgba(255, 255, 255, 0.35));
    border: 1px solid rgba(255, 255, 255, 0.5);
    box-shadow: 0 18px 36px rgba(86, 146, 132, 0.18);
    backdrop-filter: blur(18px);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
  }
  .glass-subcard {
    background: linear-gradient(180deg, rgba(255, 255, 255, 0.58), rgba(255, 255, 255, 0.3));
    border: 1px solid rgba(255, 255, 255, 0.48);
    box-shadow: 0 16px 32px rgba(86, 146, 132, 0.16);
    backdrop-filter: blur(16px);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
  }
  .glass-card:hover,
  .glass-subcard:hover {
    transform: translateY(-2px);
    box-shadow: 0 22px 44px rgba(86, 146, 132, 0.26);
  }
  .glass-table {
    background: linear-gradient(180deg, rgba(255, 255, 255, 0.7), rgba(255, 255, 255, 0.35));
    border: 1px solid rgba(255, 255, 255, 0.45);
    box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.35), 0 20px 40px rgba(86, 146, 132, 0.12);
    backdrop-filter: blur(14px);
  }
  .glass-button {
    --glass-button-overlay-top: rgba(255, 255, 255, 0.82);
    --glass-button-overlay-bottom: rgba(255, 255, 255, 0.16);
    --glass-button-overlay-pressed: rgba(255, 255, 255, 0.26);
    --glass-button-border-color: rgba(255, 255, 255, 0.55);
    --glass-button-label-color: rgba(15, 23, 42, 0.94);
    --glass-button-shadow-color: rgba(15, 23, 42, 0.22);
    --glass-button-shadow-hover: rgba(15, 23, 42, 0.28);
    --glass-button-shadow-pressed: rgba(15, 23, 42, 0.18);
    --glass-button-outline-color: rgba(82, 192, 169, 0.6);
    position: relative;
    z-index: 0;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    min-height: max(3.67rem, 58px);
    padding: clamp(0.75rem, 0.7rem + 0.4vw, 1rem) clamp(1.3rem, 1.05rem + 0.9vw, 1.85rem);
    border-radius: 1.333rem;
    border: 1.5px solid var(--glass-button-border-color);
    background: linear-gradient(150deg, var(--glass-button-overlay-top) 0%, var(--glass-button-overlay-bottom) 100%);
    color: var(--glass-button-label-color) !important;
    font-family: "SF Pro Text", "SF Pro Display", -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
    font-weight: 600;
    font-size: clamp(1rem, 0.97rem + 0.15vw, 1.125rem);
    line-height: 1.25;
    letter-spacing: 0.01em;
    text-decoration: none;
    text-shadow: none;
    backdrop-filter: saturate(190%) blur(26px);
    -webkit-backdrop-filter: saturate(190%) blur(26px);
    box-shadow:
      0 18px 30px rgba(148, 187, 169, 0.18),
      0 12px 22px var(--glass-button-shadow-color),
      inset 0 1px 0 rgba(255, 255, 255, 0.55),
      inset 0 -1px 0 rgba(15, 23, 42, 0.08);
    transition:
      transform 0.32s cubic-bezier(0.22, 1, 0.36, 1),
      filter 0.32s cubic-bezier(0.22, 1, 0.36, 1),
      box-shadow 0.32s cubic-bezier(0.22, 1, 0.36, 1),
      background 0.32s ease,
      color 0.32s ease,
      border-color 0.32s ease;
    touch-action: manipulation;
  }
  .glass-button::before,
  .glass-button::after {
    content: "";
    position: absolute;
    inset: 0;
    border-radius: inherit;
    pointer-events: none;
    transition: opacity 0.32s ease, transform 0.32s ease;
  }
  .glass-button::before {
    background:
      radial-gradient(140% 120% at 20% -15%, rgba(255, 255, 255, 0.95) 0%, rgba(255, 255, 255, 0.6) 32%, rgba(255, 255, 255, 0.18) 68%, transparent 100%);
    opacity: 0.78;
    mix-blend-mode: screen;
  }
  .glass-button::after {
    inset: 2px;
    border-radius: calc(1.333rem - 2px);
    background: linear-gradient(180deg, rgba(255, 255, 255, 0.48), rgba(255, 255, 255, 0) 75%);
    box-shadow:
      inset 0 6px 12px rgba(255, 255, 255, 0.38),
      inset 0 -8px 12px rgba(15, 23, 42, 0.16);
    opacity: 0.55;
  }
  .glass-button:hover {
    transform: translateY(-1px) scale(1.01);
    box-shadow:
      0 24px 36px rgba(148, 187, 169, 0.24),
      0 16px 28px var(--glass-button-shadow-hover),
      inset 0 1px 0 rgba(255, 255, 255, 0.55),
      inset 0 -1px 0 rgba(15, 23, 42, 0.08);
  }
  .glass-button:hover::before {
    opacity: 0.92;
    transform: translateY(-1px);
  }
  .glass-button:hover::after {
    opacity: 0.7;
  }
  .glass-button:focus-visible {
    outline: 3px solid var(--glass-button-outline-color);
    outline-offset: 4px;
  }
  .glass-button.is-pressed,
  .glass-button:active {
    transform: translateY(0) scale(0.97);
    filter: brightness(0.96) saturate(1.05);
    background: linear-gradient(160deg, var(--glass-button-overlay-top) 5%, var(--glass-button-overlay-pressed) 100%);
    box-shadow:
      0 12px 20px rgba(148, 187, 169, 0.16),
      0 10px 18px var(--glass-button-shadow-pressed),
      inset 0 2px 6px rgba(15, 23, 42, 0.18);
  }
  .glass-button.is-pressed::before,
  .glass-button:active::before {
    opacity: 0.85;
  }
  .glass-button.is-pressed::after,
  .glass-button:active::after {
    opacity: 0.46;
    transform: translateY(1px);
  }
  .glass-button[aria-disabled="true"],
  .glass-button[disabled],
  .glass-button.disabled {
    cursor: not-allowed;
    filter: saturate(0.8) opacity(0.85);
    --glass-button-overlay-top: rgba(255, 255, 255, 0.45);
    --glass-button-overlay-bottom: rgba(255, 255, 255, 0.12);
    --glass-button-label-color: rgba(17, 24, 32, 0.45);
    box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.28);
  }
  @media (prefers-color-scheme: dark) {
    .glass-button {
      --glass-button-overlay-top: rgba(255, 255, 255, 0.28);
      --glass-button-overlay-bottom: rgba(15, 118, 110, 0.14);
      --glass-button-overlay-pressed: rgba(45, 110, 99, 0.38);
      --glass-button-border-color: rgba(180, 255, 236, 0.32);
      --glass-button-label-color: rgba(236, 253, 245, 0.96);
      --glass-button-shadow-color: rgba(0, 0, 0, 0.55);
      --glass-button-shadow-hover: rgba(0, 0, 0, 0.6);
      --glass-button-shadow-pressed: rgba(0, 0, 0, 0.5);
      --glass-button-outline-color: rgba(94, 234, 212, 0.65);
      box-shadow:
        0 20px 40px rgba(0, 0, 0, 0.48),
        0 12px 28px rgba(0, 0, 0, 0.42),
        inset 0 1px 0 rgba(255, 255, 255, 0.18),
        inset 0 -1px 0 rgba(0, 0, 0, 0.5);
    }
    .glass-button::before {
      background:
        radial-gradient(140% 130% at 20% -20%, rgba(255, 255, 255, 0.75) 0%, rgba(255, 255, 255, 0.4) 34%, rgba(255, 255, 255, 0.12) 72%, transparent 100%);
    }
    .glass-button::after {
      background: linear-gradient(180deg, rgba(255, 255, 255, 0.32), rgba(0, 0, 0, 0.35) 85%);
      box-shadow:
        inset 0 6px 12px rgba(255, 255, 255, 0.2),
        inset 0 -8px 14px rgba(0, 0, 0, 0.55);
      opacity: 0.48;
    }
    .glass-button:hover::after {
      opacity: 0.6;
    }
  }
  @media (prefers-contrast: more) {
    .glass-button {
      --glass-button-overlay-top: rgba(255, 255, 255, 0.95);
      --glass-button-overlay-bottom: rgba(255, 255, 255, 0.32);
      border-width: 2px;
      box-shadow:
        0 26px 36px rgba(15, 23, 42, 0.32),
        0 18px 32px rgba(15, 23, 42, 0.2);
    }
    .glass-button::after {
      opacity: 0.7;
    }
  }
  @media (prefers-contrast: more) and (prefers-color-scheme: dark) {
    .glass-button {
      --glass-button-overlay-top: rgba(255, 255, 255, 0.34);
      --glass-button-overlay-bottom: rgba(26, 95, 91, 0.4);
      box-shadow:
        0 28px 42px rgba(0, 0, 0, 0.65),
        0 18px 32px rgba(0, 0, 0, 0.56);
    }
  }
  @media (prefers-reduced-transparency: reduce) {
    .glass-button {
      backdrop-filter: none;
      -webkit-backdrop-filter: none;
      background: linear-gradient(160deg, rgba(245, 250, 248, 0.96), rgba(209, 229, 222, 0.7));
    }
    .glass-button::before,
    .glass-button::after {
      display: none;
    }
  }
  @media (prefers-reduced-transparency: reduce) and (prefers-color-scheme: dark) {
    .glass-button {
      background: linear-gradient(160deg, rgba(30, 64, 55, 0.95), rgba(15, 52, 45, 0.78));
    }
  }
  @media (prefers-reduced-motion: reduce) {
    .glass-button {
      transition: background 0.25s ease, color 0.25s ease, border-color 0.25s ease;
    }
    .glass-button.is-pressed,
    .glass-button:active {
      transform: none;
      filter: none;
    }
  }
  .glass-emoji-pill {
    background: linear-gradient(135deg, rgba(255, 255, 255, 0.6), rgba(255, 255, 255, 0.25));
    border: 1px solid rgba(255, 255, 255, 0.45);
    box-shadow: 0 12px 24px rgba(255, 200, 134, 0.25);
  }
  .glass-badge {
    background: linear-gradient(135deg, rgba(255, 255, 255, 0.6), rgba(255, 255, 255, 0.25));
    border: 1px solid rgba(255, 255, 255, 0.5);
    color: #896c2f;
  }
  .glass-history-badge {
    background: linear-gradient(135deg, rgba(213, 243, 235, 0.75), rgba(160, 222, 206, 0.55));
    border: 1px solid rgba(255, 255, 255, 0.5);
    color: #145947;
    box-shadow: 0 8px 18px rgba(118, 168, 158, 0.22);
  }
  .glass-table thead {
    background: linear-gradient(180deg, rgba(203, 238, 226, 0.65), rgba(184, 227, 218, 0.55));
    color: #0f4f3e;
  }
  .glass-table tbody tr:hover {
    background: rgba(211, 242, 233, 0.4);
  }
</style>

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
    <div class="glass-card relative w-full max-w-lg rounded-2xl p-6">
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
  <div class="glass-shell w-full max-w-xl mx-auto rounded-2xl px-6 py-8">
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
      <article class="glass-card flex flex-col rounded-2xl p-6">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
          <div>
            <h3 class="text-lg font-semibold text-mint-text"><?= __('student_class_section_title') ?></h3>
            <p class="text-sm text-gray-600"><?= __('student_class_section_subtitle') ?></p>
          </div>
        </div>
        <div class="mt-5 grid gap-4 md:grid-cols-2">
          <div class="glass-subcard flex flex-col rounded-xl p-5 transition">
            <div class="flex items-start gap-3">
              <span class="glass-emoji-pill flex h-12 w-12 items-center justify-center rounded-full text-2xl" aria-hidden="true">🌞</span>
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
          <div class="glass-subcard flex flex-col rounded-xl p-5 transition">
            <div class="flex items-start gap-3">
              <span class="glass-emoji-pill flex h-12 w-12 items-center justify-center rounded-full text-2xl" aria-hidden="true">🌙</span>
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
      <article class="glass-card flex h-full flex-col rounded-2xl p-5 transition">
        <div class="flex items-start justify-between gap-3">
          <div class="flex items-start gap-3">
            <span class="glass-emoji-pill flex h-12 w-12 items-center justify-center rounded-full text-2xl" aria-hidden="true">🧘</span>
            <div>
              <h3 class="text-lg font-semibold text-mint-text"><?= __('student_journal_card_title') ?></h3>
              <p class="text-sm text-gray-600"><?= __('student_journal_card_subtitle') ?></p>
            </div>
          </div>
          <span class="glass-badge inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold uppercase tracking-wide">
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
      <article class="glass-card flex h-full flex-col rounded-2xl p-5 transition">
        <div class="flex items-start justify-between gap-3">
          <div class="flex items-start gap-3">
            <span class="glass-emoji-pill flex h-12 w-12 items-center justify-center rounded-full text-2xl" aria-hidden="true">📚</span>
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
        <span class="glass-history-badge inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold uppercase tracking-wide">
          <?= sprintf(__('history_table_total'), $attendanceCount) ?>
        </span>
      </div>
      <?php if (!empty($historySessions)): ?>
        <div class="mt-4 overflow-x-auto">
          <table class="glass-table min-w-full overflow-hidden rounded-2xl text-sm">
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
      if (pressed) {
        button.classList.add('is-pressed');
        if (isInteractive()) {
          vibrate();
        }
        return;
      }
      requestAnimationFrame(function () {
        button.classList.remove('is-pressed');
      });
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
      if (event.key === ' ') {
        event.preventDefault();
        setPressed(true);
      } else if (event.key === 'Enter') {
        setPressed(true);
      }
    });

    button.addEventListener('keyup', function (event) {
      if (!isInteractive()) {
        return;
      }
      if (event.key === ' ') {
        event.preventDefault();
        resetPressed();
        button.click();
      } else {
        resetPressed();
      }
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
