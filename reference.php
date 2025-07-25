<?php
require_once __DIR__ . '/i18n.php';
$pageTitle = __('home_docs_reference');
$file = __DIR__ . '/data/docs.json';
$docsData = [];
if (file_exists($file)) {
    $json = file_get_contents($file);
    $docsData = json_decode($json, true);
    if (!is_array($docsData)) $docsData = [];
}
$docs = $docsData['reference'] ?? [];
include 'header.php';
?>
<main class="max-w-3xl mx-auto px-4 py-6">
  <h1 class="text-2xl font-semibold mb-6"><?= __('home_docs_reference') ?></h1>
  <?php if (empty($docs)): ?>
    <p>Chưa có tài liệu.</p>
  <?php else: ?>
    <div class="grid gap-4 sm:grid-cols-2">
      <?php foreach ($docs as $d): ?>
        <a href="<?= htmlspecialchars($d['link']) ?>" target="_blank" class="block bg-white p-4 rounded-lg shadow hover:shadow-md transition">
          <?= htmlspecialchars($d['title']) ?>
        </a>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</main>
<?php include 'footer.php'; ?>
