<?php
define('REQUIRE_LOGIN', true);
require 'config.php';
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require __DIR__ . '/vendor/autoload.php';
}
use App\Helpers\ThumbnailFetcher;
if (!isset($_SESSION['uid']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

$dataDir = __DIR__ . '/data';
if (!is_dir($dataDir)) {
    mkdir($dataDir, 0777, true);
}
$articlesFile = $dataDir . '/articles.json';
$videosFile = $dataDir . '/videos.json';

if (!file_exists($articlesFile)) file_put_contents($articlesFile, '[]');
if (!file_exists($videosFile)) file_put_contents($videosFile, '[]');

$success = '';
$error = '';

$articles = json_decode(file_get_contents($articlesFile), true);
$videos = json_decode(file_get_contents($videosFile), true);
if (!is_array($articles)) $articles = [];
if (!is_array($videos)) $videos = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'add_article') {
        $title = trim($_POST['title'] ?? '');
        $source = trim($_POST['source'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $link = trim($_POST['link'] ?? '');
        $image = trim($_POST['image'] ?? '');
        if ($image === '' && $link !== '') {
            $fetched = ThumbnailFetcher::get($link);
            if ($fetched) {
                $image = $fetched;
            }
        }
        if ($title === '' || $source === '') {
            $error = 'Thiếu thông tin bắt buộc';
        } else {
            foreach ($articles as $a) {
                if ($a['title'] === $title || (!empty($link) && !empty($a['link']) && $a['link'] === $link)) {
                    $error = 'Trùng tiêu đề hoặc link';
                    break;
                }
            }
            if (!$error) {
                array_unshift($articles, [
                    'title' => $title,
                    'source' => $source,
                    'description' => $description,
                    'link' => $link,
                    'image' => $image
                ]);
                file_put_contents($articlesFile, json_encode($articles, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                $success = 'Đã thêm bài viết';
            }
        }
    } elseif ($action === 'add_video') {
        $title = trim($_POST['video_title'] ?? '');
        $url = trim($_POST['youtube_url'] ?? '');
        preg_match('/(?:v=|\/)([0-9A-Za-z_-]{11})(?:[?&]|$)/', $url, $m);
        $id = $m[1] ?? '';
        if ($title === '' || $id === '') {
            $error = 'Thiếu thông tin video';
        } else {
            foreach ($videos as $v) {
                if ($v['title'] === $title || $v['youtube_id'] === $id) {
                    $error = 'Trùng tiêu đề hoặc ID';
                    break;
                }
            }
            if (!$error) {
                array_unshift($videos, [
                    'title' => $title,
                    'youtube_id' => $id
                ]);
                file_put_contents($videosFile, json_encode($videos, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                $success = 'Đã thêm video';
            }
        }
    } elseif ($action === 'delete_article') {
        $idx = intval($_POST['index'] ?? -1);
        if (isset($articles[$idx])) {
            array_splice($articles, $idx, 1);
            file_put_contents($articlesFile, json_encode($articles, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            $success = 'Đã xóa bài viết';
        }
    } elseif ($action === 'delete_video') {
        $idx = intval($_POST['index'] ?? -1);
        if (isset($videos[$idx])) {
            array_splice($videos, $idx, 1);
            file_put_contents($videosFile, json_encode($videos, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            $success = 'Đã xóa video';
        }
    }
}
?>
<?php include 'header.php'; ?>
<main class="max-w-3xl mx-auto px-4 py-6">
  <h1 class="text-2xl font-semibold mb-6">Admin Panel</h1>
  <a class="inline-block rounded-lg border border-mint text-mint-text px-4 py-2 mb-6 text-sm font-medium hover:bg-mint hover:text-white transition" href="admin.php">&larr; Quay lại quản lý học viên</a>
  <?php if ($success): ?>
    <p class="text-green-600 mb-4"><?= htmlspecialchars($success) ?></p>
  <?php elseif ($error): ?>
    <p class="text-red-600 mb-4"><?= htmlspecialchars($error) ?></p>
  <?php endif; ?>

  <section class="mb-8">
    <h2 class="text-xl font-semibold mb-4">Thêm bài viết</h2>
    <form method="post" class="space-y-4">
      <input type="hidden" name="action" value="add_article">
      <input type="text" name="title" class="w-full border rounded px-3 py-2" placeholder="Tiêu đề" required>
      <input type="text" name="source" class="w-full border rounded px-3 py-2" placeholder="Nguồn" required>
      <textarea name="description" class="w-full border rounded px-3 py-2" placeholder="Mô tả"></textarea>
      <input type="url" name="link" class="w-full border rounded px-3 py-2" placeholder="Link bài viết (tuỳ chọn)">
      <input type="url" name="image" class="w-full border rounded px-3 py-2" placeholder="Ảnh bìa (tuỳ chọn)">
      <button type="submit" class="bg-teal-600 text-white px-4 py-2 rounded">Thêm bài viết</button>
    </form>
  </section>

  <section class="mb-8">
    <h2 class="text-xl font-semibold mb-4">Thêm video</h2>
    <form method="post" class="space-y-4">
      <input type="hidden" name="action" value="add_video">
      <input type="text" name="video_title" class="w-full border rounded px-3 py-2" placeholder="Tiêu đề" required>
      <input type="url" name="youtube_url" class="w-full border rounded px-3 py-2" placeholder="URL YouTube" required>
      <button type="submit" class="bg-teal-600 text-white px-4 py-2 rounded">Thêm video</button>
    </form>
  </section>

  <section class="mb-8">
    <h2 class="text-xl font-semibold mb-2">Danh sách bài viết</h2>
    <?php if (empty($articles)): ?>
      <p class="text-gray-500">Chưa có bài viết nào.</p>
    <?php else: ?>
      <ul class="divide-y">
        <?php foreach ($articles as $i => $a): ?>
          <li class="py-2 flex justify-between items-center">
            <span><?= htmlspecialchars($a['title']) ?></span>
            <form method="post" onsubmit="return confirm('Xóa bài viết này?');">
              <input type="hidden" name="action" value="delete_article">
              <input type="hidden" name="index" value="<?= $i ?>">
              <button class="text-red-600 hover:underline">Xóa</button>
            </form>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>
  </section>

  <section>
    <h2 class="text-xl font-semibold mb-2">Danh sách video</h2>
    <?php if (empty($videos)): ?>
      <p class="text-gray-500">Chưa có video nào.</p>
    <?php else: ?>
      <ul class="divide-y">
        <?php foreach ($videos as $i => $v): ?>
          <li class="py-2 flex justify-between items-center">
            <span><?= htmlspecialchars($v['title']) ?></span>
            <form method="post" onsubmit="return confirm('Xóa video này?');">
              <input type="hidden" name="action" value="delete_video">
              <input type="hidden" name="index" value="<?= $i ?>">
              <button class="text-red-600 hover:underline">Xóa</button>
            </form>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>
  </section>
</main>
<?php include 'footer.php'; ?>
