<?php
define('REQUIRE_LOGIN', true);
require 'config.php';
if (($_SESSION['role'] ?? '') !== 'teacher') {
    header('Location: login.php');
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
$stmt = $db->prepare("SELECT id, meditation_at, content, teacher_reply, replied_at FROM journals WHERE user_id = ? ORDER BY meditation_at DESC");
$stmt->execute([$student_id]);
$journals = $stmt->fetchAll(PDO::FETCH_ASSOC);

$pageTitle = 'Báo Thiền: ' . $student['full_name'];
require 'header.php';
?>
<main class="max-w-3xl mx-auto p-4">
  <h2 class="text-2xl font-bold mb-4">Báo Thiền của <?= htmlspecialchars($student['full_name']) ?></h2>
  <div class="space-y-6">
    <?php foreach ($journals as $j): ?>
      <div class="bg-white p-4 rounded shadow space-y-2">
        <div><?= date('d/m/Y', strtotime($j['meditation_at'])) ?>: <?= htmlspecialchars($j['content']) ?></div>
        <?php if ($j['teacher_reply']): ?>
          <div><?= date('d/m/Y', strtotime($j['replied_at'])) ?>: Giáo viên phản hồi: <?= htmlspecialchars($j['teacher_reply']) ?></div>
        <?php else: ?>
          <form method="post" action="reply_journal.php" class="space-y-2">
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
