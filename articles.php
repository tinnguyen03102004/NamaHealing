<?php
$pageTitle = 'Bài viết';
$metaDescription = 'Tổng hợp các bài viết chia sẻ kiến thức thiền định, yoga và tự chữa lành từ NamaHealing.';

$file = __DIR__ . '/data/articles.json';
$articles = [];
if (file_exists($file)) {
    $json = file_get_contents($file);
    $data = json_decode($json, true);
    if (is_array($data)) {
        $articles = $data;
    }
}
?>
<?php include 'header.php'; ?>
<main class="max-w-3xl mx-auto px-4 py-6">
  <h1 class="text-2xl font-semibold mb-6">Bài viết</h1>
  <?php foreach ($articles as $article): ?>
    <article class="border-b pb-4 mb-4">
      <h3 class="text-xl font-semibold mb-1"><?= htmlspecialchars($article['title'] ?? '') ?></h3>
      <p class="text-sm text-gray-500 mb-2"><?= htmlspecialchars($article['source'] ?? '') ?></p>
      <?php if (!empty($article['link'])): ?>
        <a href="<?= htmlspecialchars($article['link']) ?>" class="text-teal-600 hover:underline" target="_blank" rel="noopener">Đọc tiếp</a>
      <?php else: ?>
        <p><?= htmlspecialchars(mb_strimwidth($article['description'] ?? '', 0, 300, '...')) ?></p>
      <?php endif; ?>
    </article>
  <?php endforeach; ?>
</main>
<?php include 'footer.php'; ?>
