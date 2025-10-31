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
            <table class="min-w-[700px] w-full border-collapse text-sm text-left text-gray-600 admin-students-table">
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
                <tr class="hover:bg-mint/5 transition admin-student-row" data-student-row>
                  <td class="px-2 sm:px-3 py-3 align-top admin-student-cell admin-student-cell-id">
                    <span class="admin-cell-label"><?= __('tbl_id') ?></span>
                    <span class="admin-cell-value font-semibold text-mint-text">#<?= $row['id'] ?></span>
                  </td>
                  <td class="px-2 sm:px-3 py-3 align-top admin-student-cell admin-student-cell-name">
                    <span class="admin-cell-label"><?= __('tbl_name') ?></span>
                    <div class="flex flex-col gap-1">
                      <span class="font-medium text-mint-text admin-student-name-text"><?= htmlspecialchars($row['full_name']) ?></span>
                      <span class="text-xs text-gray-500 admin-student-id-badge">ID #<?= $row['id'] ?></span>
                    </div>
                  </td>
                  <td class="px-2 sm:px-3 py-3 align-top admin-student-cell admin-student-cell-email">
                    <span class="admin-cell-label"><?= __('tbl_email') ?></span>
                    <span class="admin-cell-value break-all"><?= htmlspecialchars($row['email']) ?></span>
                  </td>
                  <td class="px-2 sm:px-3 py-3 align-top admin-student-cell admin-student-cell-phone">
                    <span class="admin-cell-label"><?= __('tbl_phone') ?></span>
                    <span class="admin-cell-value break-all"><?= htmlspecialchars($row['phone']) ?></span>
                  </td>
                  <td class="px-2 sm:px-3 py-3 align-top admin-student-cell admin-student-cell-remaining">
                    <span class="admin-cell-label"><?= __('tbl_remaining') ?></span>
                    <span class="admin-cell-value font-semibold <?= $row['remaining'] == 0 ? 'text-red-600' : 'text-mint-text' ?>">
                      <?= $row['remaining'] ?>
                    </span>
                  </td>
                  <?php $firstSessionCompleted = db_bool($row['first_session_completed'] ?? null); ?>
                  <td class="px-2 sm:px-3 py-3 align-top admin-student-cell admin-student-cell-first">
                    <span class="admin-cell-label"><?= __('tbl_first_session') ?></span>
                    <span class="inline-flex items-center gap-1 rounded-full px-3 py-1 text-xs font-semibold <?= $firstSessionCompleted ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-600' ?>">
                      <?= $firstSessionCompleted ? __('first_session_done_badge') : __('first_session_not_done_badge') ?>
                    </span>
                  </td>
                  <?php $isVip = db_bool($row['is_vip'] ?? null); ?>
                  <td class="px-2 sm:px-3 py-3 align-top admin-student-cell admin-student-cell-type">
                    <span class="admin-cell-label"><?= __('tbl_type') ?></span>
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
                  </td>
                  <td class="px-2 sm:px-3 py-3 align-top admin-student-cell admin-student-cell-actions">
                    <span class="admin-cell-label"><?= __('tbl_actions') ?></span>
                    <div class="admin-student-actions" data-admin-actions>
                      <button type="button" class="admin-student-actions__toggle" data-admin-actions-toggle aria-expanded="false">
                        <?= __('admin_actions_button') ?>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                          <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.177l3.71-3.946a.75.75 0 111.08 1.04l-4.243 4.51a.75.75 0 01-1.08 0L5.25 8.27a.75.75 0 01-.02-1.06z" clip-rule="evenodd" />
                        </svg>
                      </button>
                      <div class="admin-student-actions__menu" data-admin-actions-menu hidden>
                        <div class="admin-student-actions__group">
                          <span class="admin-student-actions__group-label"><?= __('admin_action_group_sessions') ?></span>
                          <form method="post" action="add_sessions.php" class="admin-student-actions__form" data-admin-action-form>
                            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">
                            <input type="hidden" name="uid" value="<?= $row['id'] ?>">
                            <label class="admin-student-actions__form-label" for="add-session-input-<?= $row['id'] ?>"><?= __('admin_action_add_sessions_label') ?></label>
                            <div class="admin-student-actions__form-fields">
                              <input id="add-session-input-<?= $row['id'] ?>" type="number" name="add" value="1" class="admin-student-actions__input" />
                              <button type="submit" class="admin-student-actions__button admin-student-actions__button--primary">
                                <?= __('add_sessions') ?>
                              </button>
                            </div>
                            <p class="admin-student-actions__hint"><?= __('admin_action_add_sessions_hint') ?></p>
                            <p class="admin-student-actions__error" data-admin-action-error data-message="<?= __('admin_action_error') ?>" hidden></p>
                          </form>
                        </div>
                        <div class="admin-student-actions__group">
                          <span class="admin-student-actions__group-label"><?= __('admin_action_group_status') ?></span>
                          <form method="post" class="admin-student-actions__form" data-admin-action-form>
                            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">
                            <input type="hidden" name="mark_first_session" value="<?= $row['id'] ?>">
                            <input type="hidden" name="first_session_value" value="<?= $firstSessionCompleted ? 0 : 1 ?>">
                            <button type="submit" class="admin-student-actions__button">
                              <?= $firstSessionCompleted ? __('unmark_first_session_button') : __('mark_first_session_button') ?>
                            </button>
                            <p class="admin-student-actions__error" data-admin-action-error data-message="<?= __('admin_action_error') ?>" hidden></p>
                          </form>
                          <form method="post" class="admin-student-actions__form" data-admin-action-form>
                            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">
                            <input type="hidden" name="toggle_vip" value="<?= $row['id'] ?>">
                            <input type="hidden" name="vip_value" value="<?= $isVip ? 0 : 1 ?>">
                            <button type="submit" class="admin-student-actions__button">
                              <?= $isVip ? __('remove_vip_button') : __('make_vip_button') ?>
                            </button>
                            <p class="admin-student-actions__error" data-admin-action-error data-message="<?= __('admin_action_error') ?>" hidden></p>
                          </form>
                        </div>
                        <div class="admin-student-actions__group">
                          <span class="admin-student-actions__group-label"><?= __('admin_action_group_other') ?></span>
                          <a href="history.php?id=<?= $row['id'] ?>" class="admin-student-actions__link">
                            <?= __('history') ?>
                          </a>
                          <button type="button" class="admin-student-actions__button admin-student-actions__button--danger" data-admin-delete-trigger data-delete-form="delete-form-<?= $row['id'] ?>" data-student-name="<?= htmlspecialchars($row['full_name']) ?>">
                            <?= __('delete') ?>
                          </button>
                        </div>
                      </div>
                      <form method="post" action="delete_user.php" class="admin-student-actions__delete-form" data-admin-delete-form id="delete-form-<?= $row['id'] ?>">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">
                        <input type="hidden" name="id" value="<?= $row['id'] ?>">
                      </form>
                    </div>
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

<div class="admin-modal" id="admin-delete-modal" hidden aria-hidden="true">
  <div class="admin-modal__backdrop" data-admin-modal-dismiss></div>
  <div class="admin-modal__dialog" role="dialog" aria-modal="true" aria-labelledby="admin-delete-modal-title">
    <div class="admin-modal__header">
      <h4 class="admin-modal__title" id="admin-delete-modal-title"><?= __('admin_delete_modal_title') ?></h4>
    </div>
    <div class="admin-modal__body">
      <p class="admin-modal__message"><?= sprintf(__('admin_delete_modal_message'), '<span data-admin-modal-name></span>') ?></p>
      <p class="admin-modal__error" data-admin-modal-error hidden><?= __('admin_delete_modal_error') ?></p>
    </div>
    <div class="admin-modal__footer">
      <button type="button" class="admin-modal__button admin-modal__button--secondary" data-admin-modal-cancel>
        <?= __('admin_delete_modal_cancel') ?>
      </button>
      <button type="button" class="admin-modal__button admin-modal__button--danger" data-admin-modal-confirm>
        <?= __('admin_delete_modal_confirm') ?>
      </button>
    </div>
  </div>
</div>
