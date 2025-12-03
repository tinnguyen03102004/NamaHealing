<?php
$sectionKey = htmlspecialchars($tabId ?? 'zoom', ENT_QUOTES, 'UTF-8');
$cancelSelectionInput = $cancelSelectionInput ?? [];

$cancelledLookup = [];
foreach ($cancelledSessions as $item) {
    $dateKey = $item['date'] ?? '';
    $sessionKey = $item['session'] ?? '';
    if ($dateKey && in_array($sessionKey, ['morning', 'evening'], true)) {
        $cancelledLookup[$dateKey][$sessionKey] = true;
    }
}

$selectedCancellations = [];
if (!empty($cancelSelectionInput) && is_array($cancelSelectionInput)) {
    foreach ($cancelSelectionInput as $date => $sessions) {
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', (string)$date)) {
            continue;
        }
        $sessions = is_array($sessions) ? $sessions : [$sessions];
        foreach ($sessions as $sess) {
            if (in_array($sess, ['morning', 'evening'], true)) {
                $selectedCancellations[$date][$sess] = true;
            }
        }
    }
}

$calendarMonths = [];
$today = new DateTimeImmutable('today');
for ($offset = 0; $offset < 2; $offset++) {
    $monthStart = $today->modify("first day of +{$offset} month");
    $monthLabel = $monthStart->format('m/Y');
    $gridStart = $monthStart->modify('monday this week');
    $gridEnd = $monthStart->modify('last day of this month')->modify('sunday this week');

    $current = $gridStart;
    $weeks = [];
    while ($current <= $gridEnd) {
        $week = [];
        for ($i = 0; $i < 7; $i++) {
            $dateStr = $current->format('Y-m-d');
            $week[] = [
                'date' => $dateStr,
                'label' => $current->format('d/m'),
                'day' => $current->format('j'),
                'isPast' => $current < $today,
                'isCurrentMonth' => $current->format('n') === $monthStart->format('n'),
                'cancelled' => $cancelledLookup[$dateStr] ?? [],
                'selected' => $selectedCancellations[$dateStr] ?? [],
            ];
            $current = $current->modify('+1 day');
        }
        $weeks[] = $week;
    }

    $calendarMonths[] = [
        'label' => $monthLabel,
        'weeks' => $weeks,
    ];
}
?>
<section class="admin-panel-section" data-tab-content="<?= $sectionKey ?>" id="tab-panel-<?= $sectionKey ?>" role="tabpanel" aria-labelledby="tab-button-<?= $sectionKey ?>">
  <div class="admin-panel-card">
    <h3 class="admin-panel-card__title"><?= __('admin_zoom_section_title') ?></h3>
    <?php if ($zoomUpdated): ?>
      <div class="admin-panel-alert admin-panel-alert--success"><?= __('zoom_links_updated') ?></div>
    <?php endif; ?>

    <form method="post" class="admin-panel-form">
      <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">
      <input type="hidden" name="zoom_links" value="1">
      <div class="grid gap-4">
        <div class="grid gap-2">
          <label class="admin-panel-form__label"><?= __('zoom_links_title') ?></label>
          <div class="flex flex-col gap-2">
            <?php render_zoom_link_field($zoomLinks, 'student', 'morning', 'zoom_morning_label'); ?>
            <?php render_zoom_link_field($zoomLinks, 'student', 'evening', 'zoom_evening_label'); ?>
          </div>
        </div>
        <div class="grid gap-2">
          <label class="admin-panel-form__label"><?= __('zoom_links_vip_title') ?></label>
          <div class="flex flex-col gap-2">
            <?php render_zoom_link_field($zoomLinks, 'vip', 'morning', 'zoom_vip_morning_label'); ?>
            <?php render_zoom_link_field($zoomLinks, 'vip', 'evening', 'zoom_vip_evening_label'); ?>
          </div>
        </div>
      </div>
      <button class="admin-panel-button"><?= __('save_zoom_links') ?></button>
    </form>
  </div>

  <div class="admin-panel-card">
    <h3 class="admin-panel-card__title"><?= __('admin_cancel_section_title') ?></h3>
    <?php if ($cancelMsg): ?>
      <div class="admin-panel-alert admin-panel-alert--success"><?= $cancelMsg ?></div>
    <?php endif; ?>

    <form method="post" class="admin-panel-form">
      <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">
      <input type="hidden" name="cancel_session" value="1">
      <label class="admin-panel-form__label"><?= __('cancel_session_title') ?></label>
      <div class="grid gap-4">
        <p class="text-sm text-gray-600 leading-relaxed"><?= __('cancel_dates_hint') ?></p>
        <div class="flex flex-wrap items-center gap-3 text-xs text-gray-600">
          <span class="inline-flex items-center gap-2"><span class="inline-block w-3 h-3 rounded-full bg-red-200"></span> <?= __('cancel_calendar_marked') ?></span>
          <span class="inline-flex items-center gap-2"><span class="inline-block w-3 h-3 rounded-full bg-mint/50"></span> <?= __('cancel_calendar_selected') ?></span>
          <span class="inline-flex items-center gap-2"><span class="inline-block w-3 h-3 rounded-full bg-gray-200"></span> <?= __('cancel_calendar_past') ?></span>
        </div>
        <div class="grid gap-4 lg:grid-cols-2">
          <?php foreach ($calendarMonths as $month): ?>
            <div class="rounded-xl border border-mint/40 shadow-sm bg-white">
              <div class="px-3 py-2 border-b border-mint/30 text-sm font-semibold text-mint-text flex items-center justify-between">
                <span><?= htmlspecialchars($month['label'], ENT_QUOTES, 'UTF-8') ?></span>
                <span class="text-xs font-normal text-gray-500"><?= __('cancel_calendar_month_hint') ?></span>
              </div>
              <div class="grid grid-cols-7 text-center text-[11px] uppercase tracking-wide text-gray-500 border-b border-mint/30">
                <?php foreach (['Mon','Tue','Wed','Thu','Fri','Sat','Sun'] as $dayLabel): ?>
                  <div class="py-2"><?= htmlspecialchars($dayLabel, ENT_QUOTES, 'UTF-8') ?></div>
                <?php endforeach; ?>
              </div>
              <div class="grid grid-cols-7 gap-2 p-3 text-sm">
                <?php foreach ($month['weeks'] as $week): ?>
                  <?php foreach ($week as $day): ?>
                    <?php
                      $isPast = $day['isPast'];
                      $isMarkedMorning = !empty($day['cancelled']['morning']);
                      $isMarkedEvening = !empty($day['cancelled']['evening']);
                      $isSelectedMorning = !empty($day['selected']['morning']);
                      $isSelectedEvening = !empty($day['selected']['evening']);
                      $cellClasses = [
                        'border', 'border-mint/20', 'rounded-lg', 'min-h-[120px]', 'bg-white', 'flex', 'flex-col', 'gap-1', 'p-2', 'text-left', 'justify-between'
                      ];
                      if (!$day['isCurrentMonth']) {
                          $cellClasses[] = 'opacity-60';
                      }
                      if ($isPast) {
                          $cellClasses[] = 'bg-gray-50';
                      } elseif ($isMarkedMorning || $isMarkedEvening) {
                          $cellClasses[] = 'ring-1';
                          $cellClasses[] = 'ring-red-200';
                          $cellClasses[] = 'bg-red-50';
                      } elseif ($isSelectedMorning || $isSelectedEvening) {
                          $cellClasses[] = 'bg-mint/20';
                      }
                      $cellClassAttr = htmlspecialchars(implode(' ', $cellClasses), ENT_QUOTES, 'UTF-8');
                      $dayLabel = htmlspecialchars($day['label'], ENT_QUOTES, 'UTF-8');
                      $dateValue = htmlspecialchars($day['date'], ENT_QUOTES, 'UTF-8');
                    ?>
                    <div class="<?= $cellClassAttr ?>">
                      <div class="flex items-center justify-between text-xs text-gray-700">
                        <span class="font-semibold text-mint-text"><?= htmlspecialchars($day['day'], ENT_QUOTES, 'UTF-8') ?></span>
                        <span class="text-[11px] text-gray-500"><?= $dayLabel ?></span>
                      </div>
                      <div class="flex flex-col gap-2 mt-1 text-sm">
                        <label class="inline-flex items-center gap-2 text-gray-700">
                          <input type="checkbox"
                                 name="cancel_selection[<?= $dateValue ?>][]"
                                 value="morning"
                                 class="rounded border-gray-300 text-mint focus:ring-mint"
                                 <?php if ($isMarkedMorning || $isSelectedMorning) : ?>checked<?php endif; ?>
                                 <?php if ($isPast) : ?>disabled<?php endif; ?>>
                          <span><?= __('cancel_list_session_label_morning') ?></span>
                        </label>
                        <label class="inline-flex items-center gap-2 text-gray-700">
                          <input type="checkbox"
                                 name="cancel_selection[<?= $dateValue ?>][]"
                                 value="evening"
                                 class="rounded border-gray-300 text-mint focus:ring-mint"
                                 <?php if ($isMarkedEvening || $isSelectedEvening) : ?>checked<?php endif; ?>
                                 <?php if ($isPast) : ?>disabled<?php endif; ?>>
                          <span><?= __('cancel_list_session_label_evening') ?></span>
                        </label>
                      </div>
                      <?php if ($isMarkedMorning || $isMarkedEvening): ?>
                        <div class="text-[11px] text-red-600 mt-1">
                          <?= __('cancel_calendar_existing') ?>
                        </div>
                      <?php endif; ?>
                    </div>
                  <?php endforeach; ?>
                <?php endforeach; ?>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
        <div class="flex flex-col sm:flex-row gap-2 items-center">
          <button name="cancel_action" value="add" class="w-full sm:w-auto rounded-lg bg-mint text-mint-text font-semibold px-4 py-2 text-sm shadow hover:bg-mint-dark hover:text-white transition"><?= __('cancel_add_button') ?></button>
          <button name="cancel_action" value="remove" class="w-full sm:w-auto rounded-lg border border-mint text-mint-text font-medium px-4 py-2 text-sm hover:bg-mint hover:text-white transition"><?= __('cancel_delete_button') ?></button>
        </div>
      </div>
    </form>

    <div class="mt-6">
      <h4 class="text-base font-semibold text-mint-text mb-3"><?= __('cancel_list_title') ?></h4>
      <?php if (!empty($cancelledSessions)): ?>
        <div class="overflow-x-auto">
          <table class="min-w-[360px] w-full border-collapse text-sm text-left text-gray-600">
            <thead class="bg-mint/20 text-mint-text uppercase tracking-wide text-xs">
              <tr>
                <th class="py-2 px-2 sm:px-3 rounded-tl-xl whitespace-nowrap"><?= __('cancel_list_date') ?></th>
                <th class="py-2 px-2 sm:px-3 whitespace-nowrap"><?= __('cancel_list_session') ?></th>
                <th class="py-2 px-2 sm:px-3 rounded-tr-xl text-center whitespace-nowrap"><?= __('cancel_list_actions') ?></th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($cancelledSessions as $item): ?>
                <?php
                  $rawDate = $item['date'] ?? '';
                  $displayDate = $rawDate;
                  try {
                      $dt = new DateTime($rawDate);
                      $displayDate = $dt->format('d/m/Y');
                  } catch (Exception $e) {
                      $displayDate = $rawDate;
                  }
                  $sessionKey = ($item['session'] ?? '') === 'evening' ? 'evening' : 'morning';
                ?>
                <tr class="hover:bg-mint/5 transition">
                  <td class="px-2 sm:px-3 py-2 whitespace-nowrap"><?= htmlspecialchars($displayDate, ENT_QUOTES, 'UTF-8') ?></td>
                  <td class="px-2 sm:px-3 py-2 whitespace-nowrap"><?= __('cancel_list_session_label_' . $sessionKey) ?></td>
                  <td class="px-2 sm:px-3 py-2 text-center">
                    <form method="post" class="inline-flex">
                      <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">
                      <input type="hidden" name="cancel_session" value="1">
                      <input type="hidden" name="cancel_action" value="remove">
                      <input type="hidden" name="cancel_date" value="<?= htmlspecialchars($rawDate, ENT_QUOTES, 'UTF-8') ?>">
                      <input type="hidden" name="cancel_session_type" value="<?= htmlspecialchars($sessionKey, ENT_QUOTES, 'UTF-8') ?>">
                      <button class="rounded bg-red-100 text-red-700 px-3 py-1 text-xs font-semibold shadow hover:bg-red-400 hover:text-white transition" type="submit">
                        <?= __('cancel_list_delete_button') ?>
                      </button>
                    </form>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php else: ?>
        <p class="text-sm text-gray-500"><?= __('cancel_list_empty') ?></p>
      <?php endif; ?>
    </div>
  </div>
</section>
