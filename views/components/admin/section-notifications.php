<?php
$sectionKey = htmlspecialchars($tabId ?? 'notifications', ENT_QUOTES, 'UTF-8');
?>
<section class="admin-panel-section" data-tab-content="<?= $sectionKey ?>" id="tab-panel-<?= $sectionKey ?>" role="tabpanel" aria-labelledby="tab-button-<?= $sectionKey ?>">
  <div class="admin-panel-card">
    <h3 class="admin-panel-card__title"><?= __('admin_notifications_section_title') ?></h3>
    <?php if ($notifyDeleted): ?>
      <div class="admin-panel-alert admin-panel-alert--warning"><?= __('notification_deleted') ?></div>
    <?php endif; ?>

    <?php if ($notifySuccess): ?>
      <div class="admin-panel-alert admin-panel-alert--success"><?= __('notification_sent') ?></div>
    <?php endif; ?>

    <form method="post" class="admin-panel-form">
      <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">
      <h3 class="admin-panel-form__headline"><?= __('send_notification') ?></h3>
      <div class="grid gap-3">
        <div class="flex flex-col gap-1">
          <label for="notify_title" class="admin-panel-form__label"><?= __('notification_title_label') ?></label>
          <input id="notify_title" name="notify_title" type="text" class="border border-mint rounded px-3 py-2 focus:border-mint-dark focus:ring-mint" placeholder="<?= __('notification_title_placeholder') ?>">
        </div>
        <div class="grid gap-3 sm:grid-cols-2">
          <div class="flex flex-col gap-1">
            <label for="notify_type" class="admin-panel-form__label"><?= __('notification_type_label') ?></label>
            <select id="notify_type" name="notify_type" class="border border-mint rounded px-3 py-2 focus:border-mint-dark focus:ring-mint">
              <option value="general"><?= __('notification_type_general') ?></option>
              <option value="cancellation"><?= __('notification_type_cancellation') ?></option>
            </select>
          </div>
          <div class="flex flex-col gap-1">
            <label for="notify_scope" class="admin-panel-form__label"><?= __('notification_scope_label') ?></label>
            <select id="notify_scope" name="notify_scope" class="border border-mint rounded px-3 py-2 focus:border-mint-dark focus:ring-mint">
              <option value="both"><?= __('notification_scope_both') ?></option>
              <option value="morning"><?= __('notification_scope_morning') ?></option>
              <option value="evening"><?= __('notification_scope_evening') ?></option>
            </select>
          </div>
        </div>
        <div class="flex flex-col gap-1">
          <label for="notify_expires" class="admin-panel-form__label"><?= __('notification_expires_label') ?></label>
          <input id="notify_expires" type="datetime-local" name="notify_expires" class="border border-mint rounded px-3 py-2 focus:border-mint-dark focus:ring-mint">
        </div>
        <div class="flex flex-col gap-1">
          <label for="notify" class="admin-panel-form__label"><?= __('notification_message_label') ?></label>
          <textarea id="notify" name="notify_message" class="border border-mint rounded px-3 py-2 min-h-[120px] focus:border-mint-dark focus:ring-mint" placeholder="<?= __('notification_placeholder') ?>" required></textarea>
        </div>
      </div>
      <button class="admin-panel-button"><?= __('send_notification') ?></button>
    </form>
  </div>

  <div class="admin-panel-card">
    <h3 class="admin-panel-card__title"><?= __('admin_notifications_list_title') ?></h3>
    <?php if (empty($recentNotifications)): ?>
      <p class="admin-panel-empty"><?= __('notification_none_admin') ?></p>
    <?php else: ?>
      <div class="flex flex-col divide-y divide-gray-100">
        <?php foreach ($recentNotifications as $note): ?>
          <?php
            $typeBadgeClass = $note['type'] === 'cancellation'
              ? 'bg-red-100 text-red-600'
              : 'bg-emerald-100 text-emerald-700';
            $scopeKey = $note['session_scope'] === 'morning'
              ? 'notification_scope_morning'
              : ($note['session_scope'] === 'evening'
                  ? 'notification_scope_evening'
                  : 'notification_scope_both');
            $createdText = sprintf(__('notification_created_at'), date('H:i d/m/Y', strtotime($note['created_at'])));
            $expiresText = $note['expires_at']
              ? sprintf(__('notification_expires_at'), date('H:i d/m/Y', strtotime($note['expires_at'])))
              : __('notification_no_expiry');
          ?>
          <div class="py-3 flex flex-col gap-2 <?= !empty($note['is_expired']) ? 'opacity-70' : '' ?>">
            <div class="flex flex-wrap items-center justify-between gap-2">
              <div class="flex flex-wrap items-center gap-2">
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold <?= $typeBadgeClass ?>">
                  <?= $note['type'] === 'cancellation' ? __('notification_type_cancellation') : __('notification_type_general') ?>
                </span>
                <span class="text-xs text-gray-500"><?= __($scopeKey) ?></span>
                <?php if (!empty($note['is_expired'])): ?>
                  <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] bg-gray-200 text-gray-600 font-medium">
                    <?= __('notification_expired_badge') ?>
                  </span>
                <?php endif; ?>
              </div>
              <div class="text-xs text-gray-400 text-right flex flex-col">
                <span><?= $createdText ?></span>
                <span><?= $expiresText ?></span>
              </div>
            </div>
            <?php if (!empty($note['title'])): ?>
              <div class="text-sm font-semibold text-mint-text"><?= htmlspecialchars($note['title']) ?></div>
            <?php endif; ?>
            <div class="text-sm text-gray-700 whitespace-pre-line"><?= nl2br(htmlspecialchars($note['message'])) ?></div>
            <div class="flex justify-end">
              <form method="post" class="inline">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">
                <button name="delete_notification" value="<?= $note['id'] ?>" class="text-xs text-red-600 hover:text-red-800 font-semibold">
                  <?= __('notification_delete') ?>
                </button>
              </form>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</section>
