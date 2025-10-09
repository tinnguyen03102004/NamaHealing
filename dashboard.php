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

// Thiết lập dữ liệu lịch tham gia
$today = new DateTimeImmutable('today');
$monthStart = (new DateTimeImmutable('first day of this month'))->setTime(0, 0, 0);
$monthEnd = (new DateTimeImmutable('last day of this month'))->setTime(23, 59, 59);
$gridStart = $monthStart->modify('monday this week')->setTime(0, 0, 0);
$gridEnd = $monthEnd->modify('sunday this week')->setTime(23, 59, 59);

$calendarStmt = $db->prepare("SELECT session, created_at FROM sessions WHERE user_id=? AND created_at BETWEEN ? AND ? ORDER BY created_at ASC");
$calendarStmt->execute([
    $uid,
    $gridStart->format('Y-m-d H:i:s'),
    $gridEnd->format('Y-m-d H:i:s'),
]);
$calendarSessions = $calendarStmt->fetchAll(PDO::FETCH_ASSOC);

$attendanceByDate = [];
$currentMonthKey = $monthStart->format('Y-m');
$hasSessionsInMonth = false;

foreach ($calendarSessions as $entry) {
    $sessionType = $entry['session'];
    if ($sessionType !== 'morning' && $sessionType !== 'evening') {
        continue;
    }

    $sessionDate = new DateTimeImmutable($entry['created_at']);
    $dateKey = $sessionDate->format('Y-m-d');

    if (!isset($attendanceByDate[$dateKey])) {
        $attendanceByDate[$dateKey] = [
            'morning' => false,
            'evening' => false,
            'times' => [
                'morning' => [],
                'evening' => [],
            ],
        ];
    }

    $attendanceByDate[$dateKey][$sessionType] = true;
    $attendanceByDate[$dateKey]['times'][$sessionType][] = $sessionDate->format('H:i');

    if ($sessionDate->format('Y-m') === $currentMonthKey) {
        $hasSessionsInMonth = true;
    }
}

$calendarPeriod = new DatePeriod(
    $gridStart,
    new DateInterval('P1D'),
    $gridEnd->modify('+1 day')
);

$monthNameKey = 'calendar_month_' . $monthStart->format('n');
$monthLabel = sprintf(__('attendance_calendar_month_label'), __($monthNameKey), $monthStart->format('Y'));
$weekdayLabels = [
    ['short' => __('calendar_weekday_mon_short'), 'long' => __('calendar_weekday_mon_long')],
    ['short' => __('calendar_weekday_tue_short'), 'long' => __('calendar_weekday_tue_long')],
    ['short' => __('calendar_weekday_wed_short'), 'long' => __('calendar_weekday_wed_long')],
    ['short' => __('calendar_weekday_thu_short'), 'long' => __('calendar_weekday_thu_long')],
    ['short' => __('calendar_weekday_fri_short'), 'long' => __('calendar_weekday_fri_long')],
    ['short' => __('calendar_weekday_sat_short'), 'long' => __('calendar_weekday_sat_long')],
    ['short' => __('calendar_weekday_sun_short'), 'long' => __('calendar_weekday_sun_long')],
];
$todayKey = $today->format('Y-m-d');

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
    <div class="relative w-full max-w-lg rounded-2xl bg-white p-6 shadow-2xl">
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
        <button type="button" class="rounded-full bg-mint text-mint-text font-semibold px-4 py-2 text-sm shadow hover:bg-mint-dark hover:text-white transition" data-close><?= __('notification_popup_dismiss') ?></button>
      </div>
    </div>
  </div>
<?php endif; ?>

<main class="min-h-[75vh] flex flex-col items-center justify-center px-2 py-8">
  <div class="w-full max-w-xl mx-auto bg-white/95 rounded-2xl shadow-2xl shadow-[#76a89e26] px-6 py-8">
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
      <article class="flex flex-col rounded-2xl border border-emerald-100 bg-white/95 p-6 shadow-sm shadow-[#76a89e26]">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
          <div>
            <h3 class="text-lg font-semibold text-mint-text"><?= __('student_class_section_title') ?></h3>
            <p class="text-sm text-gray-600"><?= __('student_class_section_subtitle') ?></p>
          </div>
        </div>
        <div class="mt-5 grid gap-4 md:grid-cols-2">
          <div class="flex flex-col rounded-xl border border-emerald-100 bg-white p-5 shadow-sm shadow-[#76a89e26] transition hover:border-emerald-200 hover:shadow-md">
            <div class="flex items-start gap-3">
              <span class="flex h-12 w-12 items-center justify-center rounded-full bg-emerald-100 text-2xl" aria-hidden="true">🌞</span>
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
                 class="inline-flex w-full items-center justify-center rounded-lg bg-mint text-mint-text px-4 py-2 text-sm font-semibold transition hover:bg-mint-dark hover:text-white focus:outline-none focus:ring-2 focus:ring-mint-dark">
                 <?= __('join_morning') ?>
              </a>
            </div>
          </div>
          <div class="flex flex-col rounded-xl border border-emerald-100 bg-white p-5 shadow-sm shadow-[#76a89e26] transition hover:border-emerald-200 hover:shadow-md">
            <div class="flex items-start gap-3">
              <span class="flex h-12 w-12 items-center justify-center rounded-full bg-emerald-100 text-2xl" aria-hidden="true">🌙</span>
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
                 class="inline-flex w-full items-center justify-center rounded-lg bg-mint text-mint-text px-4 py-2 text-sm font-semibold transition hover:bg-mint-dark hover:text-white focus:outline-none focus:ring-2 focus:ring-mint-dark">
                 <?= __('join_evening') ?>
              </a>
            </div>
          </div>
        </div>
      </article>
    </section>
    <section class="mb-6 space-y-4">
      <article class="flex h-full flex-col rounded-2xl border border-emerald-100 bg-white/95 p-5 shadow-sm shadow-[#76a89e26] transition hover:border-emerald-200 hover:shadow-md">
        <div class="flex items-start justify-between gap-3">
          <div class="flex items-start gap-3">
            <span class="flex h-12 w-12 items-center justify-center rounded-full bg-amber-100 text-2xl" aria-hidden="true">🧘</span>
            <div>
              <h3 class="text-lg font-semibold text-mint-text"><?= __('student_journal_card_title') ?></h3>
              <p class="text-sm text-gray-600"><?= __('student_journal_card_subtitle') ?></p>
            </div>
          </div>
          <span class="inline-flex items-center rounded-full border border-amber-200 bg-amber-50 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-amber-700">
            <?= __('student_journal_card_badge') ?>
          </span>
        </div>
        <p class="mt-4 text-sm leading-relaxed text-gray-600">
          <?= __('student_journal_card_description') ?>
        </p>
        <div class="mt-auto space-y-3 pt-4">
          <a href="student_journal.php"
             class="inline-flex w-full items-center justify-center rounded-lg bg-mint text-mint-text px-4 py-2 text-sm font-semibold transition hover:bg-mint-dark hover:text-white focus:outline-none focus:ring-2 focus:ring-mint-dark">
             <?= __('student_journal_card_button') ?>
          </a>
          <a href="student_journal.php#guide" class="inline-flex items-center justify-center text-sm font-medium text-mint-text hover:text-emerald-700">
            <?= __('student_journal_card_secondary') ?>
          </a>
        </div>
      </article>
      <article class="flex h-full flex-col rounded-2xl border border-emerald-100 bg-white/95 p-5 shadow-sm shadow-[#76a89e26] transition hover:border-emerald-200 hover:shadow-md">
        <div class="flex items-start justify-between gap-3">
          <div class="flex items-start gap-3">
            <span class="flex h-12 w-12 items-center justify-center rounded-full bg-emerald-100 text-2xl" aria-hidden="true">📚</span>
            <div>
              <h3 class="text-lg font-semibold text-mint-text"><?= __('student_materials_card_title') ?></h3>
              <p class="text-sm text-gray-600"><?= __('student_materials_card_subtitle') ?></p>
            </div>
          </div>
          <span class="inline-flex items-center rounded-full border px-3 py-1 text-xs font-semibold uppercase tracking-wide <?= $materialsUnlocked ? 'border-mint/40 bg-mint/10 text-mint-text' : 'border-gray-200 bg-gray-100 text-gray-600' ?>">
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
             class="inline-flex w-full items-center justify-center rounded-lg bg-mint text-mint-text px-4 py-2 text-sm font-semibold transition hover:bg-mint-dark hover:text-white focus:outline-none focus:ring-2 focus:ring-mint-dark <?= $materialsUnlocked ? '' : 'cursor-not-allowed opacity-75' ?>"
             data-materials-link
             data-locked="<?= $materialsUnlocked ? '0' : '1' ?>"
             <?= $materialsUnlocked ? '' : 'aria-disabled="true"' ?>>
            <?= __('student_materials_card_button') ?>
          </a>
        </div>
      </article>
    </section>
    <section class="attendance-calendar mt-8">
      <div class="attendance-calendar__header">
        <div>
          <h5 class="attendance-calendar__title"><?= __('attendance_calendar_title') ?></h5>
          <p class="attendance-calendar__subtitle"><?= __('attendance_calendar_subtitle') ?></p>
        </div>
        <span class="attendance-calendar__badge" aria-live="polite"><?= htmlspecialchars($monthLabel) ?></span>
      </div>
      <div class="attendance-calendar__legend" aria-label="<?= __('attendance_calendar_legend_title') ?>">
        <span class="attendance-calendar__legend-title"><?= __('attendance_calendar_legend_title') ?></span>
        <div class="attendance-calendar__legend-items">
          <div class="attendance-calendar__legend-item">
            <span class="attendance-calendar__legend-dot attendance-calendar__legend-dot--morning">🌞</span>
            <span><?= __('attendance_calendar_legend_morning') ?></span>
          </div>
          <div class="attendance-calendar__legend-item">
            <span class="attendance-calendar__legend-dot attendance-calendar__legend-dot--evening">🌙</span>
            <span><?= __('attendance_calendar_legend_evening') ?></span>
          </div>
          <div class="attendance-calendar__legend-item">
            <span class="attendance-calendar__legend-dot attendance-calendar__legend-dot--both">🌞🌙</span>
            <span><?= __('attendance_calendar_legend_both') ?></span>
          </div>
        </div>
      </div>
      <div class="attendance-calendar__grid">
        <?php foreach ($weekdayLabels as $weekday): ?>
          <div class="attendance-calendar__weekday" aria-label="<?= htmlspecialchars($weekday['long']) ?>">
            <?= htmlspecialchars($weekday['short']) ?>
          </div>
        <?php endforeach; ?>
      </div>
      <div class="attendance-calendar__days">
        <?php foreach ($calendarPeriod as $date): ?>
          <?php
            $dateKey = $date->format('Y-m-d');
            $isCurrentMonth = $date->format('Y-m') === $currentMonthKey;
            $attendance = $attendanceByDate[$dateKey] ?? [
              'morning' => false,
              'evening' => false,
              'times' => [
                'morning' => [],
                'evening' => [],
              ],
            ];

            $dayClasses = ['attendance-calendar__day'];
            if (!$isCurrentMonth) {
              $dayClasses[] = 'attendance-calendar__day--muted';
            }
            if ($attendance['morning'] && $attendance['evening']) {
              $dayClasses[] = 'attendance-calendar__day--has-both';
            } elseif ($attendance['morning']) {
              $dayClasses[] = 'attendance-calendar__day--has-morning';
            } elseif ($attendance['evening']) {
              $dayClasses[] = 'attendance-calendar__day--has-evening';
            }
            if ($dateKey === $todayKey) {
              $dayClasses[] = 'attendance-calendar__day--today';
            }

            $morningStatus = sprintf(
              $attendance['morning'] ? __('attendance_calendar_slot_status_present') : __('attendance_calendar_slot_status_absent'),
              __('morning')
            );
            if ($attendance['morning'] && !empty($attendance['times']['morning'])) {
              $morningStatus .= ' ' . sprintf(__('attendance_calendar_slot_time_suffix'), implode(', ', $attendance['times']['morning']));
            }

            $eveningStatus = sprintf(
              $attendance['evening'] ? __('attendance_calendar_slot_status_present') : __('attendance_calendar_slot_status_absent'),
              __('evening')
            );
            if ($attendance['evening'] && !empty($attendance['times']['evening'])) {
              $eveningStatus .= ' ' . sprintf(__('attendance_calendar_slot_time_suffix'), implode(', ', $attendance['times']['evening']));
            }

            $ariaLabel = sprintf(
              __('attendance_calendar_day_summary'),
              $date->format('d/m/Y'),
              implode('; ', [$morningStatus, $eveningStatus])
            );
          ?>
          <div class="<?= implode(' ', $dayClasses) ?>" aria-label="<?= htmlspecialchars($ariaLabel) ?>" title="<?= htmlspecialchars($ariaLabel) ?>">
            <div class="attendance-calendar__day-header">
              <span class="attendance-calendar__date"><?= htmlspecialchars($date->format('j')) ?></span>
              <?php if ($dateKey === $todayKey): ?>
                <span class="attendance-calendar__today-badge"><?= __('attendance_calendar_today_badge') ?></span>
              <?php endif; ?>
            </div>
            <div class="attendance-calendar__slots">
              <div class="attendance-calendar__slot attendance-calendar__slot--morning <?= $attendance['morning'] ? 'attendance-calendar__slot--checked' : '' ?>" title="<?= htmlspecialchars($morningStatus) ?>">
                <span aria-hidden="true">🌞</span>
              </div>
              <div class="attendance-calendar__slot attendance-calendar__slot--evening <?= $attendance['evening'] ? 'attendance-calendar__slot--checked' : '' ?>" title="<?= htmlspecialchars($eveningStatus) ?>">
                <span aria-hidden="true">🌙</span>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
      <?php if (!$hasSessionsInMonth): ?>
        <p class="attendance-calendar__empty"><?= __('attendance_calendar_empty') ?></p>
      <?php endif; ?>
    </section>
    <div class="text-center mt-4">
      <a href="change_password.php" class="text-sm text-blue-600 underline"><?= __('change_password') ?></a>
    </div>
  </div>
</main>

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
