<?php
$sectionKey = htmlspecialchars($tabId ?? 'zoom', ENT_QUOTES, 'UTF-8');
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
      <div class="flex flex-col sm:flex-row gap-2 items-center">
        <input type="date" name="cancel_date" class="rounded border border-mint px-2 py-1 focus:border-mint-dark focus:ring-mint" required>
        <select name="cancel_session_type" class="rounded border border-mint px-2 py-1 focus:border-mint-dark focus:ring-mint">
          <option value="morning"><?= __('morning') ?></option>
          <option value="evening"><?= __('evening') ?></option>
        </select>
        <button name="cancel_action" value="add" class="rounded-lg bg-mint text-mint-text font-semibold px-4 py-2 text-sm shadow hover:bg-mint-dark hover:text-white transition"><?= __('cancel_add_button') ?></button>
        <button name="cancel_action" value="remove" class="rounded-lg border border-mint text-mint-text font-medium px-4 py-2 text-sm hover:bg-mint hover:text-white transition"><?= __('cancel_delete_button') ?></button>
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
