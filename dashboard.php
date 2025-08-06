<?php
define('REQUIRE_LOGIN', true);
require 'config.php';
if (!isset($_SESSION['uid']) || $_SESSION['role'] !== 'student') {
    header('Location: login.php');
    exit;
}
// Lấy thông tin học viên
$uid = $_SESSION['uid'];
$stmt = $db->prepare("SELECT full_name, remaining FROM users WHERE id=?");
$stmt->execute([$uid]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
$remain = $user['remaining'] ?? 0;
if ($remain <= 0) {
    header('Location: welcome.php');
    exit;
}

// Lấy lịch sử
$stmt = $db->prepare("SELECT session, created_at FROM sessions WHERE user_id=? ORDER BY created_at DESC LIMIT 5");
$stmt->execute([$uid]);
$history = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Thông báo
$db->exec("CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    message TEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
)");
$db->exec("CREATE TABLE IF NOT EXISTS notification_reads (
    notification_id INT NOT NULL,
    user_id INT NOT NULL,
    read_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (notification_id, user_id)
)");

$stmt = $db->query("SELECT id, message, created_at FROM notifications ORDER BY created_at DESC");
$notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $db->prepare("SELECT COUNT(*) FROM notifications n LEFT JOIN notification_reads r ON n.id = r.notification_id AND r.user_id = ? WHERE r.notification_id IS NULL");
$stmt->execute([$uid]);
$unreadCount = (int)$stmt->fetchColumn();

// Kiểm tra giờ hiện tại có nằm trong khung giờ vào lớp (ẩn)
function is_morning_time() {
    $now = date('H:i');
    return ($now >= '05:55' && $now <= '06:40');
}
function is_evening_time() {
    $now = date('H:i');
    return ($now >= '20:40' && $now <= '21:30');
}

// xác định có đang trong giờ học hay không
$allowMorning = is_morning_time();
$allowEvening = is_evening_time();

require 'header.php';
?>

<main class="min-h-[75vh] flex flex-col items-center justify-center px-2 py-8">
  <div class="w-full max-w-xl mx-auto bg-white/95 rounded-2xl shadow-2xl shadow-[#76a89e26] px-6 py-8">
    <h2 class="text-center text-2xl md:text-3xl font-bold text-mint-text mb-2" style="font-family:'Montserrat',sans-serif;">
      <?= sprintf(__('welcome'), htmlspecialchars($user['full_name'])) ?>
    </h2>
    <div class="text-center mb-6 text-lg font-semibold text-green-700 flex flex-col items-center">
      🌿 <span><?= sprintf(__('remaining_sessions'), $remain) ?></span>
    </div>
    <div class="flex flex-col md:flex-row gap-4 mb-6">
        <div class="flex-1 bg-white/90 rounded-xl shadow p-4 flex flex-col items-center border border-mint/30">
          <div class="mb-2 font-semibold text-base text-mint-text"><?= __('morning_class') ?> <span class="text-gray-400 text-sm">06:00-06:40</span></div>
          <form method="post" action="join.php" class="w-full">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">
            <input type="hidden" name="session" value="morning">
            <button type="submit"
               data-allowed="<?= $allowMorning ? '1' : '0' ?>"
               class="w-full rounded-xl bg-gradient-to-tr from-[#b6f0de] to-[#9dcfc3] text-[#285F57] font-bold py-2 text-center mt-3 shadow-lg hover:scale-[1.03] hover:shadow-xl transition focus:ring-2 focus:ring-mint-dark outline-none <?= $allowMorning ? '' : 'opacity-50 cursor-not-allowed' ?>">
               <?= __('join_morning') ?>
            </button>
          </form>
        </div>
        <div class="flex-1 bg-white/90 rounded-xl shadow p-4 flex flex-col items-center border border-mint/30">
          <div class="mb-2 font-semibold text-base text-mint-text"><?= __('evening_class') ?> <span class="text-gray-400 text-sm">20:45-21:30</span></div>
          <form method="post" action="join.php" class="w-full">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">
            <input type="hidden" name="session" value="evening">
            <button type="submit"
               data-allowed="<?= $allowEvening ? '1' : '0' ?>"
               class="w-full rounded-xl bg-gradient-to-tr from-[#b6f0de] to-[#9dcfc3] text-[#285F57] font-bold py-2 text-center mt-3 shadow-lg hover:scale-[1.03] hover:shadow-xl transition focus:ring-2 focus:ring-mint-dark outline-none <?= $allowEvening ? '' : 'opacity-50 cursor-not-allowed' ?>">
               <?= __('join_evening') ?>
            </button>
          </form>
        </div>
    </div>
    <h5 class="text-center text-base font-semibold text-mint-text mt-2 mb-3"><?= __('recent_history') ?></h5>
    <div class="overflow-x-auto">
      <table class="w-full text-sm bg-white border border-gray-100 rounded-lg">
        <thead>
          <tr class="bg-mint/10 text-mint-text font-semibold">
            <th class="py-2 px-3 rounded-tl-lg"><?= __('session') ?></th>
            <th class="py-2 px-3 rounded-tr-lg"><?= __('time') ?></th>
          </tr>
        </thead>
        <tbody>
        <?php if ($history): foreach ($history as $h): ?>
          <tr class="even:bg-gray-50 hover:bg-mint/5 transition">
            <td class="py-2 px-3"><?= $h['session']=='morning'?__('morning'):__('evening') ?></td>
            <td class="py-2 px-3"><?= date('H:i d/m/Y', strtotime($h['created_at'])) ?></td>
          </tr>
        <?php endforeach; else: ?>
          <tr>
            <td colspan="2" class="py-4 text-center text-gray-400"><?= __('no_history') ?></td>
          </tr>
        <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</main>

<script>
document.querySelectorAll('button[data-allowed]').forEach(btn => {
  btn.addEventListener('click', function(e) {
    if (this.dataset.allowed !== '1') {
      e.preventDefault();
      alert(<?= json_encode(__('not_class_time')) ?>);
    }
  });
});
</script>

<?php include 'footer.php'; ?>
