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
  </div>
</section>
