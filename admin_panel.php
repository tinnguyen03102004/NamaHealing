<?php
session_start();

define('ADMIN_PASS', getenv('ADMIN_PASS') ?: 'secret');

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

if (!isset($_SESSION['is_admin'])) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $pass = $_POST['password'] ?? '';
        if ($pass === ADMIN_PASS) {
            $_SESSION['is_admin'] = true;
            header('Location: admin_panel.php');
            exit;
        } else {
            $error = 'Sai mật khẩu';
        }
    }
    ?>
    <!DOCTYPE html>
    <html lang="vi">
    <head>
      <meta charset="UTF-8">
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <title>Admin Login</title>
      <script src="https://cdn.tailwindcss.com"></script>
    </head>
    <body class="bg-gray-50 flex items-center justify-center min-h-screen">
      <form method="post" class="bg-white p-6 rounded shadow w-80">
        <h1 class="text-xl font-semibold mb-4">Admin Login</h1>
        <?php if ($error): ?>
          <p class="text-red-600 mb-2"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>
        <input type="password" name="password" class="w-full border rounded px-3 py-2 mb-4" placeholder="Password" required>
        <button type="submit" class="w-full bg-teal-600 text-white py-2 rounded">Login</button>
      </form>
    </body>
    </html>
    <?php
    exit;
}

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
                    'link' => $link
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
    }
}
?>
<?php include 'header.php'; ?>
<main class="max-w-3xl mx-auto px-4 py-6">
  <h1 class="text-2xl font-semibold mb-6">Admin Panel</h1>
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
      <button type="submit" class="bg-teal-600 text-white px-4 py-2 rounded">Thêm bài viết</button>
    </form>
  </section>

  <section>
    <h2 class="text-xl font-semibold mb-4">Thêm video</h2>
    <form method="post" class="space-y-4">
      <input type="hidden" name="action" value="add_video">
      <input type="text" name="video_title" class="w-full border rounded px-3 py-2" placeholder="Tiêu đề" required>
      <input type="url" name="youtube_url" class="w-full border rounded px-3 py-2" placeholder="URL YouTube" required>
      <button type="submit" class="bg-teal-600 text-white px-4 py-2 rounded">Thêm video</button>
    </form>
  </section>
</main>
<?php include 'footer.php'; ?>
