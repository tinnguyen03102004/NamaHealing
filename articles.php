<?php
require_once __DIR__ . '/i18n.php';
$pageTitle = 'Bài Viết';
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
include 'header.php';
?>
<main class="max-w-3xl mx-auto px-4 py-6">
  <h1 class="text-2xl font-semibold mb-6">Bài viết</h1>
  <?php foreach ($articles as $article): ?>
    <article class="border-b pb-4 mb-4">
      <h3 class="text-xl font-semibold mb-1"><?= htmlspecialchars($article['title'] ?? '') ?></h3>
      <?php if (!empty($article['source'])): ?>
        <p class="text-sm text-gray-500 mb-2"><?= htmlspecialchars($article['source']) ?></p>
      <?php endif; ?>
      <?php if (!empty($article['link'])): ?>
        <a href="<?= htmlspecialchars($article['link']) ?>" target="_blank" rel="noopener" class="block rounded-lg overflow-hidden border hover:shadow transition">
            <?php if (!empty($article['image'])): ?>
              <img src="<?= htmlspecialchars($article['image']) ?>" alt="<?= htmlspecialchars($article['title']) ?>" class="w-full aspect-video object-cover">
            <?php endif; ?>
          <div class="p-3">
            <?php if (!empty($article['description'])): ?>
              <p class="text-sm mb-2">
                <?= htmlspecialchars(mb_strimwidth($article['description'], 0, 150, '...')) ?>
              </p>
            <?php endif; ?>
            <span class="text-teal-600 hover:underline">Đọc tiếp</span>
          </div>
        </a>
      <?php else: ?>
        <p><?= htmlspecialchars(mb_strimwidth($article['description'] ?? '', 0, 300, '...')) ?></p>
      <?php endif; ?>
    </article>
  <?php endforeach; ?>
</main>
<?php include 'footer.php'; ?>
