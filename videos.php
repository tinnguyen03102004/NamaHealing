<?php
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
?>
<?php include 'header.php'; ?>
<main class="max-w-7xl mx-auto px-4 py-6">
  <h1 class="text-2xl font-semibold mb-6">Video</h1>
  <?php foreach ($videos as $video): ?>
    <div class="mb-6">
      <?php if (!empty($video['title'])): ?>
        <h3 class="text-lg font-semibold mb-2"><?= htmlspecialchars($video['title']) ?></h3>
      <?php else: ?>
        <h3 class="text-lg font-semibold mb-2">Video không có tiêu đề</h3>
      <?php endif; ?>
      <iframe class="w-full aspect-video rounded-lg" src="https://www.youtube.com/embed/<?= htmlspecialchars($video['youtube_id'] ?? '') ?>" allowfullscreen loading="lazy"></iframe>
    </div>
  <?php endforeach; ?>
</main>
<?php include 'footer.php'; ?>
