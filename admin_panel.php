<?php
define('REQUIRE_LOGIN', true);
require 'config.php';
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require __DIR__ . '/vendor/autoload.php';
}
require_once __DIR__ . '/helpers/ThumbnailFetcher.php';
require_once __DIR__ . '/helpers/MetaFetcher.php';
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
        $link = trim($_POST['link'] ?? '');
        $image = trim($_POST['image'] ?? '');
        $descInput = trim($_POST['description'] ?? '');

        $meta = [];
        if ($link !== '') {
            $meta = MetaFetcher::fetchMetaFromUrl($link);
        }

        if ($title === '' && isset($meta['title'])) {
            $title = $meta['title'];
        }
        $description = $descInput !== '' ? $descInput : ($meta['description'] ?? '');

        if ($image === '' && !empty($meta['image'])) {
            $image = $meta['image'];
        }
        if ($image === '' && !empty($_FILES['thumbnail']['tmp_name'])) {
            $uploadDir = __DIR__ . '/uploads';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            $ext = pathinfo($_FILES['thumbnail']['name'], PATHINFO_EXTENSION);
            $fname = uniqid('thumb_') . '.' . strtolower($ext);
            if (move_uploaded_file($_FILES['thumbnail']['tmp_name'], "$uploadDir/$fname")) {
                $image = 'uploads/' . $fname;
            }
        }
        if ($image === '' && $link !== '') {
            $image = ThumbnailFetcher::get($link);
        }
        if ($title === '' || $link === '') {
            $error = 'Thiếu tiêu đề hoặc link';
        } else {
            foreach ($articles as $a) {
                if ($a['title'] === $title || (!empty($link) && !empty($a['link']) && $a['link'] === $link)) {
                    $error = 'Trùng tiêu đề hoặc link';
                    break;
                }
            }
            if (!$error) {
                $new = [
                    'title' => $title,
                    'link' => $link,
                ];
                if ($description !== '') $new['description'] = $description;
                if ($image !== '') $new['image'] = $image;
                array_unshift($articles, $new);
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
    } elseif ($action === 'move_article') {
        $idx = intval($_POST['index'] ?? -1);
        $dir = $_POST['dir'] ?? '';
        if ($dir === 'up' && $idx > 0 && isset($articles[$idx])) {
            [$articles[$idx - 1], $articles[$idx]] = [$articles[$idx], $articles[$idx - 1]];
            file_put_contents($articlesFile, json_encode($articles, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            $success = 'Đã cập nhật thứ tự';
        } elseif ($dir === 'down' && $idx >= 0 && $idx < count($articles) - 1 && isset($articles[$idx])) {
            [$articles[$idx], $articles[$idx + 1]] = [$articles[$idx + 1], $articles[$idx]];
            file_put_contents($articlesFile, json_encode($articles, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            $success = 'Đã cập nhật thứ tự';
        }
    } elseif ($action === 'move_video') {
        $idx = intval($_POST['index'] ?? -1);
        $dir = $_POST['dir'] ?? '';
        if ($dir === 'up' && $idx > 0 && isset($videos[$idx])) {
            [$videos[$idx - 1], $videos[$idx]] = [$videos[$idx], $videos[$idx - 1]];
            file_put_contents($videosFile, json_encode($videos, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            $success = 'Đã cập nhật thứ tự';
        } elseif ($dir === 'down' && $idx >= 0 && $idx < count($videos) - 1 && isset($videos[$idx])) {
            [$videos[$idx], $videos[$idx + 1]] = [$videos[$idx + 1], $videos[$idx]];
            file_put_contents($videosFile, json_encode($videos, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            $success = 'Đã cập nhật thứ tự';
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
    <form method="post" enctype="multipart/form-data" id="article-form" class="space-y-4">
      <input type="hidden" name="action" value="add_article">
      <input type="text" id="article-title" name="title" class="w-full border rounded px-3 py-2" placeholder="Tiêu đề" required>
      <input type="url" id="article-link" name="link" class="w-full border rounded px-3 py-2" placeholder="Link bài viết" required>
      <textarea id="article-desc" name="description" class="w-full border rounded px-3 py-2" placeholder="Mô tả"></textarea>
      <input type="url" id="article-image" name="image" class="w-full border rounded px-3 py-2" placeholder="Ảnh bìa (tuỳ chọn)">
      <input type="file" id="thumbnail-upload" name="thumbnail" class="hidden">
      <p id="thumb-msg" class="text-red-600 text-sm hidden"><?= __('thumb_not_found') ?></p>
      <div class="flex gap-2">
        <button type="button" id="fetch-meta" class="border px-4 py-2 rounded">Lấy dữ liệu</button>
        <button type="submit" class="bg-teal-600 text-white px-4 py-2 rounded">Thêm bài viết</button>
      </div>
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
            <div class="flex items-center gap-2">
              <?php if ($i > 0): ?>
                <form method="post">
                  <input type="hidden" name="action" value="move_article">
                  <input type="hidden" name="dir" value="up">
                  <input type="hidden" name="index" value="<?= $i ?>">
                  <button class="px-2">&#8593;</button>
                </form>
              <?php endif; ?>
              <?php if ($i < count($articles) - 1): ?>
                <form method="post">
                  <input type="hidden" name="action" value="move_article">
                  <input type="hidden" name="dir" value="down">
                  <input type="hidden" name="index" value="<?= $i ?>">
                  <button class="px-2">&#8595;</button>
                </form>
              <?php endif; ?>
              <form method="post" onsubmit="return confirm('<?= __('confirm_delete_article') ?>');">
                <input type="hidden" name="action" value="delete_article">
                <input type="hidden" name="index" value="<?= $i ?>">
                <button class="text-red-600 hover:underline"><?= __('delete') ?></button>
              </form>
            </div>
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
            <div class="flex items-center gap-2">
              <?php if ($i > 0): ?>
                <form method="post">
                  <input type="hidden" name="action" value="move_video">
                  <input type="hidden" name="dir" value="up">
                  <input type="hidden" name="index" value="<?= $i ?>">
                  <button class="px-2">&#8593;</button>
                </form>
              <?php endif; ?>
              <?php if ($i < count($videos) - 1): ?>
                <form method="post">
                  <input type="hidden" name="action" value="move_video">
                  <input type="hidden" name="dir" value="down">
                  <input type="hidden" name="index" value="<?= $i ?>">
                  <button class="px-2">&#8595;</button>
                </form>
              <?php endif; ?>
              <form method="post" onsubmit="return confirm('<?= __('confirm_delete_video') ?>');">
                <input type="hidden" name="action" value="delete_video">
                <input type="hidden" name="index" value="<?= $i ?>">
                <button class="text-red-600 hover:underline"><?= __('delete') ?></button>
              </form>
            </div>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>
  </section>
  <script>
    const linkInput = document.getElementById('article-link');
    const titleInput = document.getElementById('article-title');
    const descInput = document.getElementById('article-desc');
    const imgInput = document.getElementById('article-image');
    const fileInput = document.getElementById('thumbnail-upload');
    const msg = document.getElementById('thumb-msg');
    document.getElementById('fetch-meta').onclick = async () => {
      const url = linkInput.value.trim();
      if (!url) return;
      const resp = await fetch('fetch_meta.php?url=' + encodeURIComponent(url));
      if (!resp.ok) return;
      const data = await resp.json();
      if (!titleInput.value) titleInput.value = data.title || '';
      if (!descInput.value) descInput.value = data.description || '';
      if (data.image) {
        imgInput.value = data.image;
        fileInput.classList.add('hidden');
        msg.classList.add('hidden');
      } else {
        fileInput.classList.remove('hidden');
        msg.classList.remove('hidden');
      }
    };
    linkInput.addEventListener('change', () => document.getElementById('fetch-meta').click());
  </script>
</main>
<?php include 'footer.php'; ?>
