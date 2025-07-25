<?php
$pageTitle = __('home_docs_prayer');
$file = __DIR__ . '/../data/docs.json';
$docsData = [];
if (file_exists($file)) {
    $json = file_get_contents($file);
    $docsData = json_decode($json, true);
    if (!is_array($docsData)) $docsData = [];
}
$docs = $docsData['prayers'] ?? [];
include '../header.php';
?>
<main class="max-w-3xl mx-auto px-4 py-6">
  <h1 class="text-2xl font-semibold mb-4"><?= __('home_docs_prayer') ?></h1>
  <?php if (empty($docs)): ?>
    <p>Chưa có tài liệu.</p>
  <?php else: ?>
    <ul class="space-y-2">
      <?php foreach ($docs as $d): ?>
        <li>
          <a href="<?= htmlspecialchars($d['link']) ?>" target="_blank" class="text-teal-600 hover:underline">
            <?= htmlspecialchars($d['title']) ?>
          </a>
        </li>
      <?php endforeach; ?>
    </ul>
  <?php endif; ?>
</main>
<?php include '../footer.php'; ?>
