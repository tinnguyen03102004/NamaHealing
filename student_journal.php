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

$stmt = $db->prepare("SELECT meditation_at, created_at, content, teacher_reply, replied_at FROM journals WHERE user_id = ? ORDER BY created_at ASC");
$stmt->execute([$uid]);
$journals = $stmt->fetchAll(PDO::FETCH_ASSOC);

$messages = [];
foreach ($journals as $j) {
    $messages[] = [
        'time' => $j['created_at'],
        'role' => 'student',
        'content' => $j['content'],
        'meditation_at' => $j['meditation_at'],
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

$groupedMessages = [];
foreach ($messages as $message) {
    $timestamp = $message['time'] ? strtotime($message['time']) : null;
    $dateKey = $timestamp ? date('Y-m-d', $timestamp) : uniqid('unknown_', true);
    $displayDate = $timestamp ? date('d/m/Y', $timestamp) : '';
    $message['display_time'] = $timestamp ? date('H:i', $timestamp) : '';
    if (!isset($groupedMessages[$dateKey])) {
        $groupedMessages[$dateKey] = [
            'date' => $displayDate,
            'items' => [],
        ];
    }
    if (!empty($message['meditation_at'])) {
        $meditationTs = strtotime($message['meditation_at']);
        $message['meditation_label'] = $meditationTs
            ? sprintf(__('journal_meditation_day'), date('d/m/Y', $meditationTs))
            : '';
    }
    $groupedMessages[$dateKey]['items'][] = $message;
}

$pageTitle = 'Báo Thiền';
require 'header.php';
?>
<main class="max-w-3xl mx-auto p-4 space-y-6">
  <form method="post" class="bg-white p-4 rounded shadow space-y-4">
    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">
    <div>
      <label class="block mb-1">Ngày thiền</label>
      <input type="date" name="meditation_at" class="w-full border px-3 py-2 rounded" required>
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
  <?php if (!empty($groupedMessages)): ?>
    <section id="journal-history" class="bg-white p-6 rounded-2xl shadow">
      <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-2 mb-4">
        <div>
          <h2 class="text-xl font-semibold text-emerald-800">Lịch sử báo thiền</h2>
          <p class="text-sm text-gray-500">Theo dõi lại những chia sẻ và phản hồi gần đây.</p>
        </div>
      </div>
      <div id="journal-log" class="space-y-6 max-h-[520px] overflow-y-auto pr-1">
        <?php foreach ($groupedMessages as $group): ?>
          <?php
            $dateText = $group['date'];
            $dateObj = DateTime::createFromFormat('d/m/Y', $dateText);
            $day = $dateObj ? $dateObj->format('d') : $dateText;
            $monthYear = $dateObj ? $dateObj->format('m/Y') : '';
          ?>
          <article class="rounded-2xl border border-emerald-50 bg-emerald-50/60 p-5 shadow-sm">
            <header class="mb-4 flex items-center gap-3">
              <div class="flex h-12 w-12 items-center justify-center rounded-full bg-emerald-500 text-white text-lg font-semibold">
                <?= htmlspecialchars($day) ?>
              </div>
              <div class="flex flex-col">
                <span class="text-sm font-semibold text-emerald-700"><?= htmlspecialchars($dateText) ?></span>
                <?php if ($monthYear): ?>
                  <span class="text-xs text-emerald-600/80"><?= htmlspecialchars($monthYear) ?></span>
                <?php endif; ?>
              </div>
            </header>
            <div class="space-y-4">
              <?php foreach ($group['items'] as $entry): ?>
                <?php $isTeacher = ($entry['role'] === 'teacher'); ?>
                <div class="flex <?= $isTeacher ? 'justify-end' : 'justify-start' ?>">
                  <div class="max-w-[75%] rounded-2xl border px-4 py-3 shadow-sm <?= $isTeacher ? 'bg-white border-emerald-200 text-emerald-900' : 'bg-white border-gray-200 text-gray-800' ?>">
                    <div class="mb-2 flex items-center gap-2">
                      <span class="text-xs font-semibold uppercase tracking-wide <?= $isTeacher ? 'text-emerald-600' : 'text-gray-500' ?>">
                        <?= __($isTeacher ? 'journal_teacher_label' : 'journal_student_label') ?>
                      </span>
                      <?php if (!empty($entry['display_time'])): ?>
                        <span class="text-[11px] text-gray-400">
                          <?= htmlspecialchars($entry['display_time']) ?>
                        </span>
                      <?php endif; ?>
                    </div>
                    <?php if (!$isTeacher && !empty($entry['meditation_label'])): ?>
                      <p class="text-xs font-medium text-emerald-600 mb-2">
                        <?= htmlspecialchars($entry['meditation_label']) ?>
                      </p>
                    <?php endif; ?>
                    <div class="text-sm leading-relaxed whitespace-pre-line">
                      <?= nl2br(htmlspecialchars($entry['content'])) ?>
                    </div>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          </article>
        <?php endforeach; ?>
      </div>
    </section>
  <?php else: ?>
    <section class="bg-white p-6 rounded-2xl shadow text-center text-gray-500">
      <h2 class="text-lg font-semibold text-emerald-800 mb-2">Lịch sử báo thiền</h2>
      <p>Chưa có nội dung báo thiền nào. Hãy chia sẻ những trải nghiệm đầu tiên của bạn!</p>
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
