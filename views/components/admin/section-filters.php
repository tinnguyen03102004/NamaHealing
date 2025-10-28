<?php
$sectionKey = htmlspecialchars($tabId ?? 'filters', ENT_QUOTES, 'UTF-8');
?>
<section class="admin-panel-section" data-tab-content="<?= $sectionKey ?>" id="tab-panel-<?= $sectionKey ?>" role="tabpanel" aria-labelledby="tab-button-<?= $sectionKey ?>">
  <div class="admin-panel-card">
    <h3 class="admin-panel-card__title"><?= __('admin_filters_section_title') ?></h3>
    <form class="admin-panel-form" method="get">
      <div class="flex flex-col sm:flex-row items-center gap-3">
        <input type="text" name="q"
          class="rounded-md border border-mint px-3 py-2 focus:border-mint-dark focus:ring-mint w-full sm:w-52 text-sm"
          placeholder="<?= __('search_placeholder') ?>"
          value="<?= htmlspecialchars($keyword) ?>">
        <select name="status"
          class="rounded-md border border-mint px-3 py-2 focus:border-mint-dark focus:ring-mint text-sm w-full sm:w-36">
          <option value="all"    <?= $status==='all'    ? 'selected' : '' ?>><?= __('filter_all') ?></option>
          <option value="active" <?= $status==='active' ? 'selected' : '' ?>><?= __('filter_active') ?></option>
          <option value="expired"<?= $status==='expired'? 'selected' : '' ?>><?= __('filter_expired') ?></option>
        </select>
        <button class="rounded-lg bg-mint text-mint-text font-semibold px-4 py-2 text-sm shadow hover:bg-mint-dark hover:text-white transition w-full sm:w-auto">
          <?= __('filter_button') ?>
        </button>
      </div>
    </form>
  </div>
</section>
