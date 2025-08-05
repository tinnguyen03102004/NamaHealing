<?php
define('REQUIRE_LOGIN', true);
require 'config.php';
if (!isset($_SESSION['uid']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php'); exit;
}

$notifySuccess = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['notify_message'])) {
    csrf_check($_POST['csrf_token'] ?? null);
    $msg = trim($_POST['notify_message']);
    if ($msg !== '') {
        $db->exec("CREATE TABLE IF NOT EXISTS notifications (
            id INT AUTO_INCREMENT PRIMARY KEY,
            message TEXT NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )");
        $stmt = $db->prepare("INSERT INTO notifications (message) VALUES (?)");
        $stmt->execute([$msg]);
        $notifySuccess = true;
    }
}

require 'header.php';

// --- XỬ LÝ LỌC ---
$keyword = trim($_GET['q'] ?? '');
$status  = $_GET['status'] ?? 'all';

// Xây dựng điều kiện WHERE động
$where = ["role = 'student'"];
$params = [];
if ($keyword !== '') {
    $where[] = "(full_name LIKE ? OR email LIKE ? OR phone LIKE ?)";
    $like = "%{$keyword}%";
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
}
if ($status === 'active') {
    $where[] = "remaining > 0";
} elseif ($status === 'expired') {
    $where[] = "remaining = 0";
}

$where_sql = implode(' AND ', $where);
$sql = "SELECT * FROM users WHERE $where_sql ORDER BY id ASC";
$stmt = $db->prepare($sql);
$stmt->execute($params);
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<main class="mx-auto max-w-6xl mt-24 px-2 sm:px-4">
  <!-- Tiêu đề + action -->
  <div class="mb-6 flex flex-col md:flex-row items-start md:items-end justify-between gap-3">
    <div>
      <h2 class="text-2xl font-heading font-semibold text-mint-text mb-1">
        <?= __('admin_title') ?>
      </h2>
      <p class="text-sm text-gray-500"><?= __('admin_subtitle') ?></p>
    </div>
    <div class="flex flex-col sm:flex-row gap-2 mt-3 md:mt-0 w-full sm:w-auto">
      <a class="rounded-lg bg-mint text-mint-text font-semibold px-4 py-2 text-sm shadow hover:bg-mint-dark hover:text-white transition w-full sm:w-auto text-center"
         href="register.php"><?= __('add_student') ?></a>
      <a class="rounded-lg bg-mint text-mint-text font-semibold px-4 py-2 text-sm shadow hover:bg-mint-dark hover:text-white transition w-full sm:w-auto text-center"
         href="admin_panel.php"><?= __('manage_posts') ?></a>
      <a class="rounded-lg border border-mint text-mint-text font-medium px-4 py-2 text-sm hover:bg-mint hover:text-white transition w-full sm:w-auto text-center"
         href="admin.php"><?= __('clear_filter') ?></a>
    </div>
  </div>

  <?php if ($notifySuccess): ?>
    <div class="mb-4 p-3 rounded bg-green-100 text-green-700 text-sm"><?= __('notification_sent') ?></div>
  <?php endif; ?>

  <form method="post" class="mb-6 bg-white/95 rounded-xl shadow px-4 py-3 flex flex-col gap-3">
    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">
    <label for="notify" class="font-semibold text-mint-text"><?= __('send_notification') ?></label>
    <textarea id="notify" name="notify_message" class="border border-mint rounded p-2" placeholder="<?= __('notification_placeholder') ?>" required></textarea>
    <button class="self-start rounded-lg bg-mint text-mint-text font-semibold px-4 py-2 text-sm shadow hover:bg-mint-dark hover:text-white transition"><?= __('send_notification') ?></button>
  </form>

  <!-- FORM LỌC -->
  <form class="mb-5 flex flex-col sm:flex-row items-center gap-3" method="get">
    <input type="text" name="q"
      class="rounded-md border border-mint px-3 py-2 focus:border-mint-dark focus:ring-mint w-full sm:w-52 text-sm"
      placeholder="<?= __('search_placeholder') ?>"
      value="<?= htmlspecialchars($keyword) ?>">
    <select name="status"
      class="rounded-md border border-mint px-3 py-2 focus:border-mint-dark focus:ring-mint text-sm w-full sm:w-36">
      <option value="all"    <?= $status==='all'    ? 'selected' : '' ?>><?= __('filter_all') ?></option>
      <option value="active" <?= $status==='active' ? 'selected' : '' ?>><?= __('filter_active') ?></option>
      <option value="expired"<?= $status==='expired'? 'selected' : '' ?>><?= __('filter_expired') ?></option>
    </select>
    <button class="rounded-lg bg-mint text-mint-text font-semibold px-4 py-2 text-sm shadow hover:bg-mint-dark hover:text-white transition w-full sm:w-auto">
      <?= __('filter_button') ?>
    </button>
  </form>

  <!-- BẢNG DANH SÁCH -->
  <div class="overflow-x-auto rounded-xl shadow-2xl shadow-[#76a89e26] bg-white/95">
    <table class="w-full min-w-[650px] text-sm border-separate border-spacing-y-1">
      <thead>
        <tr class="bg-mint/10 text-mint-text text-base font-semibold">
          <th class="py-2 px-2 sm:px-3 rounded-tl-xl whitespace-nowrap"><?= __('tbl_id') ?></th>
          <th class="py-2 px-2 sm:px-3 whitespace-nowrap"><?= __('tbl_name') ?></th>
          <th class="py-2 px-2 sm:px-3 whitespace-nowrap"><?= __('tbl_email') ?></th>
          <th class="py-2 px-2 sm:px-3 whitespace-nowrap"><?= __('tbl_phone') ?></th>
          <th class="py-2 px-2 sm:px-3 text-center whitespace-nowrap"><?= __('tbl_remaining') ?></th>
          <th class="py-2 px-2 sm:px-3 rounded-tr-xl text-center whitespace-nowrap"><?= __('tbl_actions') ?></th>
        </tr>
      </thead>
      <tbody>
      <?php if (empty($students)): ?>
        <tr>
          <td colspan="6" class="py-5 text-center text-gray-400"><?= __('not_found') ?></td>
        </tr>
      <?php else: foreach ($students as $row): ?>
        <tr class="hover:bg-mint/5 transition">
          <td class="px-2 sm:px-3 py-2"><?= $row['id'] ?></td>
          <td class="px-2 sm:px-3 py-2"><?= htmlspecialchars($row['full_name']) ?></td>
          <td class="px-2 sm:px-3 py-2"><?= htmlspecialchars($row['email']) ?></td>
          <td class="px-2 sm:px-3 py-2"><?= htmlspecialchars($row['phone']) ?></td>
          <td class="px-2 sm:px-3 py-2 text-center font-semibold <?= $row['remaining'] == 0 ? 'text-red-600' : 'text-mint-text' ?>">
            <?= $row['remaining'] ?>
          </td>
          <td class="px-2 sm:px-3 py-2 text-center flex flex-wrap gap-2 justify-center items-center">
            <!-- CỘNG BUỔI -->
            <form method="post" action="add_sessions.php" class="flex gap-1 items-center">
              <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">
              <input type="hidden" name="uid" value="<?= $row['id'] ?>">
              <input type="number" name="add" value="1" min="1"
                class="w-14 rounded border border-mint px-2 py-1 text-sm focus:border-mint-dark focus:ring-mint" />
              <button class="rounded bg-mint/90 text-mint-text px-2 py-1 text-xs font-semibold shadow hover:bg-mint-dark hover:text-white transition" title="<?= __('add_sessions') ?> buổi">
                <?= __('add_sessions') ?>
              </button>
            </form>
            <!-- XÓA -->
            <form method="post" action="delete_user.php" onsubmit="return confirm('<?= __('confirm_delete_student') ?>');" style="display:inline-block">
              <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">
              <input type="hidden" name="id" value="<?= $row['id'] ?>">
              <button class="rounded bg-red-100 text-red-700 px-3 py-1 text-xs font-semibold shadow hover:bg-red-400 hover:text-white transition" title="<?= __('delete') ?> học viên">
                <?= __('delete') ?>
              </button>
            </form>
            <!-- LỊCH SỬ -->
            <a href="history.php?id=<?= $row['id'] ?>"
               class="rounded bg-blue-100 text-blue-700 px-3 py-1 text-xs font-semibold shadow hover:bg-blue-400 hover:text-white transition"
               title="<?= __('history') ?>">
              <?= __('history') ?>
            </a>
          </td>
        </tr>
      <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
</main>

<?php include 'footer.php'; ?>
