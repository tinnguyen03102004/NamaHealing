<?php if (!empty($adminTabs)): ?>
<nav class="admin-tabs" data-admin-tabs>
  <div class="admin-tabs__list" role="tablist">
    <?php foreach ($adminTabs as $index => $tab): ?>
      <?php
        $tabId = htmlspecialchars($tab['id'], ENT_QUOTES, 'UTF-8');
        $tabLabel = htmlspecialchars($tab['label'], ENT_QUOTES, 'UTF-8');
        $isActive = $index === 0;
      ?>
      <button
        type="button"
        class="admin-tabs__button<?= $isActive ? ' is-active' : '' ?>"
        data-tab-target="<?= $tabId ?>"
        role="tab"
        aria-selected="<?= $isActive ? 'true' : 'false' ?>"
        aria-controls="tab-panel-<?= $tabId ?>"
        id="tab-button-<?= $tabId ?>"
      >
        <?= $tabLabel ?>
      </button>
    <?php endforeach; ?>
  </div>
</nav>
<?php endif; ?>
