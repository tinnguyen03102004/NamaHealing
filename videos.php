<?php
require_once __DIR__ . '/i18n.php';
$pageTitle = 'Video';
$metaDescription = 'Video hướng dẫn thiền, yoga và chữa lành từ NamaHealing giúp bạn thư giãn, cân bằng cuộc sống.';

$file = __DIR__ . '/data/videos.json';
$videos = [];
if (file_exists($file)) {
    $json = file_get_contents($file);
    $data = json_decode($json, true);
    if (is_array($data)) {
        $videos = $data;
    }
}
include 'header.php';
?>
<main class="max-w-6xl mx-auto px-4 py-8">
  <h1 class="text-2xl font-semibold mb-8">Video</h1>
  <div class="grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
    <?php foreach ($videos as $video): ?>
      <div class="bg-white rounded-xl shadow overflow-hidden flex flex-col">
        <?php if (!empty($video['title'])): ?>
          <h3 class="text-base font-semibold p-4 pb-2"><?= htmlspecialchars($video['title']) ?></h3>
        <?php else: ?>
          <h3 class="text-base font-semibold p-4 pb-2">Video không có tiêu đề</h3>
        <?php endif; ?>
        <iframe class="w-full aspect-video" src="https://www.youtube.com/embed/<?= htmlspecialchars($video['youtube_id'] ?? '') ?>" allowfullscreen loading="lazy"></iframe>
      </div>
    <?php endforeach; ?>
  </div>
</main>
<?php include 'footer.php'; ?>
