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
    $times = trim($_POST['times'] ?? '');
    $minutes = trim($_POST['minutes'] ?? '');
    $status = trim($_POST['status'] ?? '');
    if ($meditation_at && $times !== '' && $minutes !== '' && $status !== '') {
        $content = "Học viên hôm nay đã có {$times} thời thiền, mỗi thời {$minutes} phút. Tình trạng tâm lí của học viên: {$status}";
        $stmt = $db->prepare("INSERT INTO journals (user_id, meditation_at, content) VALUES (?, ?, ?)");
        $stmt->execute([$uid, $meditation_at, $content]);
    }
    header('Location: student_journal.php');
    exit;
}

$stmt = $db->prepare("SELECT meditation_at, content, teacher_reply, replied_at FROM journals WHERE user_id = ? ORDER BY meditation_at ASC");
$stmt->execute([$uid]);
$journals = $stmt->fetchAll(PDO::FETCH_ASSOC);

$messages = [];
foreach ($journals as $j) {
    $messages[] = [
        'time' => $j['meditation_at'],
        'role' => 'student',
        'content' => $j['content'],
    ];
    if (!empty($j['teacher_reply'])) {
        $messages[] = [
            'time' => $j['replied_at'],
            'role' => 'teacher',
            'content' => $j['teacher_reply'],
        ];
    }
}
usort($messages, fn($a, $b) => strtotime($a['time']) <=> strtotime($b['time']));

$pageTitle = 'Báo Thiền';
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
      <label class="block mb-1">Số thời thiền</label>
      <input type="number" name="times" class="w-full border px-3 py-2 rounded" required>
    </div>
    <div>
      <label class="block mb-1">Mỗi thời bao nhiêu phút</label>
      <input type="number" name="minutes" class="w-full border px-3 py-2 rounded" required>
    </div>
    <div>
      <label class="block mb-1">Tình trạng hiện nay (đã cải thiện thế nào, còn vấn đề nào ,...)</label>
      <textarea name="status" class="w-full border px-3 py-2 rounded" required></textarea>
    </div>
    <button type="submit" class="bg-[#9dcfc3] text-white px-4 py-2 rounded">Gửi</button>
  </form>
  <?php if ($messages): ?>
    <section id="journal-history" class="bg-white p-4 rounded shadow">
      <h2 class="text-lg font-semibold mb-3">Lịch sử báo thiền</h2>
      <div id="journal-log" class="space-y-2 max-h-96 overflow-y-auto">
        <?php $curDate = ''; foreach ($messages as $m): ?>
          <?php $d = date('d/m/Y', strtotime($m['time'])); ?>
          <?php if ($d !== $curDate): $curDate = $d; ?>
            <div class="text-center text-xs text-gray-500"><span class="px-2 py-1 bg-gray-200 rounded-full"><?= $d ?></span></div>
          <?php endif; ?>
          <?php if ($m['role'] === 'student'): ?>
            <div class="text-left"><div class="inline-block bg-gray-100 p-2 rounded"><?= htmlspecialchars($m['content']) ?></div></div>
          <?php else: ?>
            <div class="text-right"><div class="inline-block bg-green-100 p-2 rounded"><?= htmlspecialchars($m['content']) ?></div></div>
          <?php endif; ?>
        <?php endforeach; ?>
      </div>
    </section>
  <?php endif; ?>
</main>
<script>
document.addEventListener('DOMContentLoaded', () => {
  const log = document.getElementById('journal-log');
  if (!log) return;
  let stick = true;
  const scrollBottom = () => { log.scrollTop = log.scrollHeight; };
  scrollBottom();
  log.addEventListener('scroll', () => {
    stick = log.scrollTop + log.clientHeight >= log.scrollHeight - 10;
  });
  const observer = new MutationObserver(() => { if (stick) scrollBottom(); });
  observer.observe(log, { childList: true });
});
</script>
<?php include 'footer.php'; ?>
