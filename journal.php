<?php
require 'config.php';
if (($_SESSION['role'] ?? '') !== 'teacher') {
    header('Location: teacher_login.php');
    exit;
}

$student_id = (int)($_GET['student_id'] ?? 0);
if ($student_id <= 0) {
    header('Location: teacher_dashboard.php');
    exit;
}

// Mark unseen journals as seen
$upd = $db->prepare("UPDATE journals SET seen_at = NOW() WHERE user_id = ? AND seen_at IS NULL");
$upd->execute([$student_id]);

// Fetch student name
$stmt = $db->prepare("SELECT full_name FROM users WHERE id = ? AND role='student'");
$stmt->execute([$student_id]);
$student = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$student) {
    header('Location: teacher_dashboard.php');
    exit;
}

// Fetch journals
$stmt = $db->prepare("SELECT id, meditation_at, content, teacher_reply FROM journals WHERE user_id = ? ORDER BY meditation_at DESC");
$stmt->execute([$student_id]);
$journals = $stmt->fetchAll(PDO::FETCH_ASSOC);

$pageTitle = 'Nhật ký: ' . $student['full_name'];
require 'header.php';
?>
<main class="max-w-3xl mx-auto p-4">
  <h2 class="text-2xl font-bold mb-4">Nhật ký của <?= htmlspecialchars($student['full_name']) ?></h2>
  <div class="space-y-6">
    <?php foreach ($journals as $j): ?>
      <div class="bg-white p-4 rounded shadow">
        <div class="text-sm text-gray-500 mb-2">
          <?= date('d/m/Y H:i', strtotime($j['meditation_at'])) ?>
        </div>
        <div class="whitespace-pre-line mb-2"><?= htmlspecialchars($j['content']) ?></div>
        <?php if ($j['teacher_reply']): ?>
          <div class="mt-2 p-2 bg-gray-50 border-l-4 border-green-400">
            <strong>Phản hồi:</strong> <?= htmlspecialchars($j['teacher_reply']) ?>
          </div>
        <?php else: ?>
          <form method="post" action="reply_journal.php" class="mt-2 space-y-2">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">
            <input type="hidden" name="journal_id" value="<?= $j['id'] ?>">
            <input type="hidden" name="student_id" value="<?= $student_id ?>">
            <textarea name="reply" class="w-full border px-3 py-2 rounded" required></textarea>
            <button type="submit" class="bg-[#9dcfc3] text-white px-4 py-1 rounded">Gửi phản hồi</button>
          </form>
        <?php endif; ?>
      </div>
    <?php endforeach; ?>
  </div>
</main>
<?php include 'footer.php'; ?>
