<?php
$sectionKey = htmlspecialchars($tabId ?? 'students', ENT_QUOTES, 'UTF-8');
$studentCount = count($students);
$countLabelKey = $studentCount === 1 ? 'admin_students_result_count_single' : 'admin_students_result_count';
$countLabel = sprintf(__($countLabelKey), $studentCount);

$filterChips = [];
if ($keyword !== '') {
    $filterChips[] = sprintf(__('admin_filters_keyword_badge'), htmlspecialchars($keyword, ENT_QUOTES, 'UTF-8'));
}
if ($status === 'active' || $status === 'expired') {
    $statusLabels = [
        'active'  => __('filter_active'),
        'expired' => __('filter_expired'),
    ];
    $statusLabel = $statusLabels[$status] ?? '';
    if ($statusLabel !== '') {
        $filterChips[] = sprintf(__('admin_filters_status_badge'), htmlspecialchars($statusLabel, ENT_QUOTES, 'UTF-8'));
    }
}
?>
<section class="admin-panel-section" data-tab-content="<?= $sectionKey ?>" id="tab-panel-<?= $sectionKey ?>" role="tabpanel" aria-labelledby="tab-button-<?= $sectionKey ?>">
  <div class="admin-panel-card">
    <div class="admin-panel-card__header">
      <h3 class="admin-panel-card__title"><?= __('admin_students_section_title') ?></h3>
      <span class="admin-result-chip" aria-live="polite"><?= $countLabel ?></span>
    </div>
    <div class="admin-students-table-shell">
      <div class="admin-students-table-scroll">
        <div class="admin-students-toolbar">
          <form class="admin-students-toolbar__form" method="get">
            <label class="sr-only" for="admin-filter-keyword"><?= __('search_placeholder') ?></label>
            <input id="admin-filter-keyword" type="text" name="q"
              class="rounded-md border border-mint px-3 py-2 focus:border-mint-dark focus:ring-mint w-full sm:w-52 text-sm"
              placeholder="<?= __('search_placeholder') ?>"
              value="<?= htmlspecialchars($keyword) ?>">
            <label class="sr-only" for="admin-filter-status"><?= __('admin_filters_status_label') ?></label>
            <select id="admin-filter-status" name="status"
              class="rounded-md border border-mint px-3 py-2 focus:border-mint-dark focus:ring-mint text-sm w-full sm:w-36">
              <option value="all"    <?= $status==='all'    ? 'selected' : '' ?>><?= __('filter_all') ?></option>
              <option value="active" <?= $status==='active' ? 'selected' : '' ?>><?= __('filter_active') ?></option>
              <option value="expired"<?= $status==='expired'? 'selected' : '' ?>><?= __('filter_expired') ?></option>
            </select>
            <button class="rounded-lg bg-mint text-mint-text font-semibold px-4 py-2 text-sm shadow hover:bg-mint-dark hover:text-white transition w-full sm:w-auto">
              <?= __('filter_button') ?>
            </button>
          </form>
          <div class="admin-students-toolbar__filters">
            <?php if (!empty($filterChips)): ?>
              <span class="admin-students-toolbar__filters-label"><?= __('admin_filters_applied_label') ?>:</span>
              <?php foreach ($filterChips as $chip): ?>
                <span class="admin-filter-chip"><?= $chip ?></span>
              <?php endforeach; ?>
            <?php else: ?>
              <span class="admin-students-toolbar__filters-label"><?= __('admin_filters_no_active') ?></span>
            <?php endif; ?>
            <a class="admin-students-toolbar__reset" href="admin.php"><?= __('clear_filter') ?></a>
          </div>
        </div>
        <div class="admin-students-table-wrapper">
          <?php if ($firstSessionUpdated): ?>
            <div class="admin-students-message bg-emerald-100 text-emerald-700">
              <?= __('first_session_status_updated') ?>
            </div>
          <?php endif; ?>
          <?php if ($vipStatusUpdated): ?>
            <div class="admin-students-message bg-emerald-100 text-emerald-700">
              <?= __('vip_status_updated') ?>
            </div>
          <?php endif; ?>
          <div class="admin-students-table-frame">
            <table class="min-w-[700px] w-full border-collapse text-sm text-left text-gray-600">
              <thead class="bg-mint/20 text-mint-text uppercase tracking-wide text-xs">
                <tr>
                  <th class="py-2 px-2 sm:px-3 rounded-tl-xl whitespace-nowrap"><?= __('tbl_id') ?></th>
                  <th class="py-2 px-2 sm:px-3 whitespace-nowrap"><?= __('tbl_name') ?></th>
                  <th class="py-2 px-2 sm:px-3 whitespace-nowrap"><?= __('tbl_email') ?></th>
                  <th class="py-2 px-2 sm:px-3 whitespace-nowrap"><?= __('tbl_phone') ?></th>
                  <th class="py-2 px-2 sm:px-3 text-center whitespace-nowrap"><?= __('tbl_remaining') ?></th>
                  <th class="py-2 px-2 sm:px-3 text-center whitespace-nowrap"><?= __('tbl_first_session') ?></th>
                  <th class="py-2 px-2 sm:px-3 text-center whitespace-nowrap"><?= __('tbl_type') ?></th>
                  <th class="py-2 px-2 sm:px-3 rounded-tr-xl text-center whitespace-nowrap"><?= __('tbl_actions') ?></th>
                </tr>
              </thead>
              <tbody>
              <?php if (empty($students)): ?>
                <tr>
                  <td colspan="8" class="py-5 text-center text-gray-400"><?= __('not_found') ?></td>
                </tr>
              <?php else: foreach ($students as $row): ?>
                <tr class="hover:bg-mint/5 transition">
                  <td class="px-2 sm:px-3 py-2"><?= $row['id'] ?></td>
                  <td class="px-2 sm:px-3 py-2"><?= htmlspecialchars($row['full_name']) ?></td>
                  <td class="px-2 sm:px-3 py-2"><?= htmlspecialchars($row['email']) ?></td>
                  <td class="px-2 sm:px-3 py-2"><?= htmlspecialchars($row['phone']) ?></td>
                  <td class="px-2 sm:px-3 py-2 text-center font-semibold <?= $row['remaining'] == 0 ? 'text-red-600' : 'text-mint-text' ?>">
                    <?= $row['remaining'] ?>
                  </td>
                  <?php $firstSessionCompleted = db_bool($row['first_session_completed'] ?? null); ?>
                  <td class="px-2 sm:px-3 py-2 text-center">
                    <div class="flex flex-col items-center gap-2">
                      <span class="inline-flex items-center gap-1 rounded-full px-3 py-1 text-xs font-semibold <?= $firstSessionCompleted ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-600' ?>">
                        <?= $firstSessionCompleted ? __('first_session_done_badge') : __('first_session_not_done_badge') ?>
                      </span>
                      <form method="post" class="flex flex-col items-center gap-1 text-xs">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">
                        <input type="hidden" name="mark_first_session" value="<?= $row['id'] ?>">
                        <input type="hidden" name="first_session_value" value="<?= $firstSessionCompleted ? 0 : 1 ?>">
                        <button class="rounded border <?= $firstSessionCompleted ? 'border-gray-300 text-gray-600 hover:bg-gray-100' : 'border-emerald-400 text-emerald-600 hover:bg-emerald-50' ?> px-3 py-1 font-medium transition" type="submit">
                          <?= $firstSessionCompleted ? __('unmark_first_session_button') : __('mark_first_session_button') ?>
                        </button>
                      </form>
                    </div>
                  </td>
                  <td class="px-2 sm:px-3 py-2">
                    <?php $isVip = db_bool($row['is_vip'] ?? null); ?>
                    <div class="flex flex-col items-center gap-2">
                      <span class="inline-flex items-center gap-1 rounded-full px-3 py-1 text-xs font-semibold <?= $isVip ? 'bg-amber-100 text-amber-700' : 'bg-gray-100 text-gray-600' ?>">
                        <?php if ($isVip): ?>
                          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-4 w-4">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                          </svg>
                          <?= __('vip_badge') ?>
                        <?php else: ?>
                          <?= __('standard_badge') ?>
                        <?php endif; ?>
                      </span>
                      <form method="post" class="flex flex-col items-center gap-1 text-xs">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">
                        <input type="hidden" name="toggle_vip" value="<?= $row['id'] ?>">
                        <input type="hidden" name="vip_value" value="<?= $isVip ? 0 : 1 ?>">
                        <button class="rounded border <?= $isVip ? 'border-gray-300 text-gray-600 hover:bg-gray-100' : 'border-amber-400 text-amber-600 hover:bg-amber-50' ?> px-3 py-1 font-medium transition" type="submit">
                          <?= $isVip ? __('remove_vip_button') : __('make_vip_button') ?>
                        </button>
                      </form>
                    </div>
                  </td>
                  <td class="px-2 sm:px-3 py-2 text-center flex flex-wrap gap-2 justify-center items-center">
                    <form method="post" action="add_sessions.php" class="flex gap-1 items-center">
                      <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">
                      <input type="hidden" name="uid" value="<?= $row['id'] ?>">
                      <input type="number" name="add" value="1"
                        class="w-14 rounded border border-mint px-2 py-1 text-sm focus:border-mint-dark focus:ring-mint" />
                      <button class="rounded bg-mint/90 text-mint-text px-2 py-1 text-xs font-semibold shadow hover:bg-mint-dark hover:text-white transition" title="<?= __('add_sessions') ?> buổi">
                        <?= __('add_sessions') ?>
                      </button>
                    </form>
                    <form method="post" action="delete_user.php" onsubmit="return confirm('<?= __('confirm_delete_student') ?>');" style="display:inline-block">
                      <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">
                      <input type="hidden" name="id" value="<?= $row['id'] ?>">
                      <button class="rounded bg-red-100 text-red-700 px-3 py-1 text-xs font-semibold shadow hover:bg-red-400 hover:text-white transition" title="<?= __('delete') ?> học viên">
                        <?= __('delete') ?>
                      </button>
                    </form>
                    <a href="history.php?id=<?= $row['id'] ?>"
                       class="rounded bg-blue-100 text-blue-700 px-3 py-1 text-xs font-semibold shadow hover:bg-blue-400 hover:text-white transition"
                       title="<?= __('history') ?>">
                      <?= __('history') ?>
                    </a>
                  </td>
                </tr>
              <?php endforeach; endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
