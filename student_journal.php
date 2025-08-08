<?php
define('REQUIRE_LOGIN', true);
require 'config.php';
if (($_SESSION['role'] ?? '') !== 'student') {
    header('Location: login.php');
    exit;
}

$uid = $_SESSION['uid'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check($_POST['csrf_token'] ?? null);
    $meditation_at = $_POST['meditation_at'] ?? '';
    $content = trim($_POST['content'] ?? '');
    if ($meditation_at && $content !== '') {
        $stmt = $db->prepare("INSERT INTO journals (user_id, meditation_at, content) VALUES (?, ?, ?)");
        $stmt->execute([$uid, $meditation_at, $content]);
    }
    header('Location: student_journal.php');
    exit;
}

$stmt = $db->prepare("SELECT meditation_at, content, teacher_reply FROM journals WHERE user_id = ? ORDER BY meditation_at DESC");
$stmt->execute([$uid]);
$journals = $stmt->fetchAll(PDO::FETCH_ASSOC);

$pageTitle = 'Nhật ký thiền';
require 'header.php';
?>
<main class="max-w-3xl mx-auto p-4 space-y-6">
  <form method="post" class="bg-white p-4 rounded shadow space-y-4">
    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">
    <div>
      <label class="block mb-1">Thời gian thiền</label>
      <input type="datetime-local" name="meditation_at" class="w-full border px-3 py-2 rounded" required>
    </div>
    <div>
      <label class="block mb-1">Nội dung</label>
      <textarea name="content" class="w-full border px-3 py-2 rounded" required></textarea>
    </div>
    <button type="submit" class="bg-[#9dcfc3] text-white px-4 py-2 rounded">Gửi</button>
  </form>
  <?php if ($journals): ?>
    <section id="journal-history" class="bg-white p-4 rounded shadow">
      <h2 class="text-lg font-semibold mb-3">Lịch sử báo thiền</h2>
      <div class="space-y-4 max-h-96 overflow-y-auto">
        <?php foreach ($journals as $j): ?>
          <div class="border-b pb-4 last:border-b-0 last:pb-0">
            <div class="text-sm text-gray-500 mb-2">
              <?= date('d/m/Y H:i', strtotime($j['meditation_at'])) ?>
            </div>
            <div class="whitespace-pre-line mb-2"><?= htmlspecialchars($j['content']) ?></div>
            <?php if ($j['teacher_reply']): ?>
              <div class="mt-2 p-2 bg-gray-50 border-l-4 border-green-400">
                <strong>Phản hồi:</strong> <?= htmlspecialchars($j['teacher_reply']) ?>
              </div>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
      </div>
    </section>
  <?php endif; ?>
</main>
<?php include 'footer.php'; ?>
