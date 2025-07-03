<?php
define('REQUIRE_LOGIN', true);
require 'config.php';
if (!isset($_SESSION['uid']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php'); exit;
}

$uid = intval($_GET['id'] ?? 0);
$stmt = $db->prepare("SELECT full_name, email FROM users WHERE id=? AND role='student'");
$stmt->execute([$uid]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    include 'header.php';
    echo "<div class='max-w-lg mx-auto mt-10 bg-red-50 text-red-600 rounded-lg px-4 py-3 text-center shadow'>
        Không tìm thấy học viên! <a class='underline' href='admin.php'>Quay lại</a>
      </div>";
    include 'footer.php'; exit;
}

// Lấy lịch sử tham gia
$logs = $db->prepare("SELECT session, created_at FROM sessions WHERE user_id=? ORDER BY created_at DESC");
$logs->execute([$uid]);

// Hàm lấy thứ VN
function get_vn_day($datetime) {
    $weekday = ['Chủ nhật','Thứ hai','Thứ ba','Thứ tư','Thứ năm','Thứ sáu','Thứ bảy'];
    $ts = strtotime($datetime);
    return $weekday[date('w', $ts)];
}

include 'header.php';
?>

<main class="min-h-[70vh] py-10 px-4 flex justify-center items-start">
  <div class="w-full max-w-2xl bg-white/95 rounded-2xl shadow-2xl shadow-[#76a89e26] p-6">
    <h3 class="text-center text-2xl font-semibold text-mint-text mb-2">
      Lịch sử tham gia: <?= htmlspecialchars($user['full_name']) ?>
    </h3>
    <p class="text-center text-gray-500 mb-4"><?= htmlspecialchars($user['email']) ?></p>

    <div class="mb-5 text-center">
      <a class="inline-block rounded-lg border border-mint text-mint-text px-4 py-2 min-h-[40px] text-base font-medium hover:bg-mint hover:text-white transition"
         href="admin.php">&larr; Quay lại danh sách</a>
    </div>

    <div class="overflow-x-auto">
      <table class="w-full text-sm bg-white border border-gray-200 rounded-lg overflow-hidden">
        <thead>
          <tr class="bg-mint/10 text-mint-text font-semibold">
            <th class="px-3 py-2 text-left">Buổi</th>
            <th class="px-3 py-2 text-left">Thứ</th>
            <th class="px-3 py-2 text-left">Thời gian</th>
          </tr>
        </thead>
        <tbody>
        <?php if ($logs->rowCount()): foreach ($logs as $log): ?>
          <tr class="border-t even:bg-gray-50 hover:bg-mint/5 transition">
            <td class="px-3 py-2"><?= $log['session']=='morning' ? 'Sáng' : 'Chiều' ?></td>
            <td class="px-3 py-2"><?= get_vn_day($log['created_at']) ?></td>
            <td class="px-3 py-2"><?= date('H:i d/m/Y', strtotime($log['created_at'])) ?></td>
          </tr>
        <?php endforeach; else: ?>
          <tr>
            <td colspan="3" class="text-center py-4 text-gray-400">Chưa có lịch sử.</td>
          </tr>
        <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</main>

<?php include 'footer.php'; ?>
