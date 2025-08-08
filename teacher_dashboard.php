<?php
define('REQUIRE_LOGIN', true);
require 'config.php';

ini_set('display_errors', 1); error_reporting(E_ALL); // tắt khi chạy thật

$pdo = $pdo ?? ($db ?? null);
if (!$pdo) { die('Không có PDO connection ($pdo/$db)'); }

if (($_SESSION['role'] ?? '') !== 'teacher') {
    header('Location: login.php'); exit;
}

try {
    $sql = "
        SELECT 
            u.id, u.full_name, u.email,
            (SELECT COUNT(*) FROM sessions s WHERE s.user_id = u.id) AS session_count,
            (SELECT MAX(j.meditation_at) FROM journals j WHERE j.student_id = u.id) AS latest_journal,
            EXISTS(
              SELECT 1 FROM journals j 
              WHERE j.student_id = u.id AND j.seen_at IS NULL
            ) AS has_unseen
        FROM users u
        WHERE u.role = 'student'
        ORDER BY u.full_name
    ";
    $students = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
    die('Lỗi truy vấn: ' . htmlspecialchars($e->getMessage()));
}

$pageTitle = 'Teacher Dashboard';
require 'header.php';
?>
<main class="p-4 max-w-5xl mx-auto">
  <h2 class="text-2xl font-bold mb-4">Danh sách học viên</h2>
  <div class="overflow-x-auto">
    <table class="w-full table-auto bg-white shadow rounded">
      <thead class="bg-gray-100">
        <tr>
          <th class="p-2 text-left">Tên</th>
          <th class="p-2 text-left">Email</th>
          <th class="p-2 text-left">Số buổi đã tham gia</th>
          <th class="p-2 text-left">Nhật ký gần nhất</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($students as $s): ?>
        <tr class="border-t">
          <td class="p-2">
            <a href="journal.php?student_id=<?= (int)$s['id'] ?>" class="text-blue-600 underline">
              <?= htmlspecialchars($s['full_name'] ?? '') ?>
            </a>
            <?php if (!empty($s['has_unseen'])): ?>
              <span class="ml-2 bg-red-500 text-white text-xs px-2 py-1 rounded">Chưa xem</span>
            <?php endif; ?>
          </td>
          <td class="p-2"><?= htmlspecialchars($s['email'] ?? '') ?></td>
          <td class="p-2 text-center"><?= (int)($s['session_count'] ?? 0) ?></td>
          <td class="p-2">
            <?= !empty($s['latest_journal']) ? date('d/m/Y H:i', strtotime($s['latest_journal'])) : '-' ?>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</main>
<?php include 'footer.php'; ?>
