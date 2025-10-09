<?php
define('REQUIRE_LOGIN', true);
require 'config.php';
require_once __DIR__ . '/helpers/Notifications.php';
require_once __DIR__ . '/helpers/Schema.php';
require_once __DIR__ . '/helpers/Value.php';
if (!isset($_SESSION['uid']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php'); exit;
}

$notifyDeleted = false;
$notifySuccess = false;
$zoomUpdated = false;
$vipStatusUpdated = !empty($_SESSION['vip_status_flash'] ?? false);
unset($_SESSION['vip_status_flash']);

notifications_setup($db);
ensure_users_has_vip($db);
ensure_zoom_links_audience($db);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_notification'])) {
    csrf_check($_POST['csrf_token'] ?? null);
    $deleteId = (int)$_POST['delete_notification'];
    if ($deleteId > 0) {
        notifications_delete($db, $deleteId);
        $notifyDeleted = true;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['notify_message'])) {
    csrf_check($_POST['csrf_token'] ?? null);
    $msg = trim($_POST['notify_message']);
    if ($msg !== '') {
        $title = trim($_POST['notify_title'] ?? '');
        $type = $_POST['notify_type'] ?? 'general';
        if (!in_array($type, NOTIFICATION_TYPES, true)) {
            $type = 'general';
        }
        $scope = $_POST['notify_scope'] ?? 'both';
        if (!in_array($scope, NOTIFICATION_SESSION_SCOPES, true)) {
            $scope = 'both';
        }
        $expiresInput = trim($_POST['notify_expires'] ?? '');
        $expiresAt = null;
        if ($expiresInput !== '') {
            $dt = DateTime::createFromFormat('Y-m-d\TH:i', $expiresInput);
            if ($dt instanceof DateTime) {
                $expiresAt = $dt->format('Y-m-d H:i:s');
            }
        }

        notifications_create($db, $msg, [
            'title'         => $title,
            'type'          => $type,
            'session_scope' => $scope,
            'expires_at'    => $expiresAt,
        ]);
        $notifySuccess = true;
    }
}

$recentNotifications = notifications_fetch_recent($db, 25);

// Manage Zoom links
$db->exec("CREATE TABLE IF NOT EXISTS zoom_links (
    session VARCHAR(10) NOT NULL,
    audience VARCHAR(10) NOT NULL DEFAULT 'student',
    url TEXT NOT NULL,
    PRIMARY KEY (session, audience)
)");

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['zoom_links'])) {
    csrf_check($_POST['csrf_token'] ?? null);
    $audiences = ['student', 'vip'];
    $sessions = ['morning', 'evening'];
    foreach ($audiences as $audience) {
        foreach ($sessions as $sess) {
            $field = "zoom_{$audience}_{$sess}";
            $url = trim($_POST[$field] ?? '');
            if ($url === '') {
                continue;
            }
            $stmt = $db->prepare(
                "INSERT INTO zoom_links(session, audience, url) VALUES (?, ?, ?) " .
                "ON DUPLICATE KEY UPDATE url = VALUES(url)"
            );
            $stmt->execute([$sess, $audience, $url]);
        }
    }
    $zoomUpdated = true;
}

$zoomLinks = [
    'student' => ['morning' => '', 'evening' => ''],
    'vip' => ['morning' => '', 'evening' => ''],
];
try {
    $stmt = $db->query("SELECT session, audience, url FROM zoom_links");
    foreach ($stmt as $row) {
        $audience = $row['audience'] ?? 'student';
        if (isset($zoomLinks[$audience][$row['session']])) {
            $zoomLinks[$audience][$row['session']] = $row['url'];
        }
    }
} catch (PDOException $e) {
    $stmt = $db->query("SELECT session, url FROM zoom_links");
    foreach ($stmt as $row) {
        if (isset($zoomLinks['student'][$row['session']])) {
            $zoomLinks['student'][$row['session']] = $row['url'];
        }
    }
}

if (!function_exists('render_zoom_link_field')) {
    function render_zoom_link_field(array $zoomLinks, string $audience, string $session, string $labelKey): void {
        $url = $zoomLinks[$audience][$session] ?? '';
        $inputName = "zoom_{$audience}_{$session}";
        $placeholderText = __($labelKey);
        $placeholderAttr = htmlspecialchars($placeholderText, ENT_QUOTES, 'UTF-8');
        $encodedUrl = htmlspecialchars($url, ENT_QUOTES, 'UTF-8');
        ?>
        <div class="flex flex-col gap-1">
          <div class="flex flex-col sm:flex-row gap-2 items-center">
            <input type="url"
                   name="<?= $inputName ?>"
                   value="<?= $encodedUrl ?>"
                   placeholder="<?= $placeholderAttr ?>"
                   class="flex-1 rounded border border-mint px-2 py-1 focus:border-mint-dark focus:ring-mint">
            <?php if ($url !== ''): ?>
            <a href="<?= $encodedUrl ?>"
               target="_blank"
               rel="noopener noreferrer"
               class="rounded bg-blue-100 text-blue-700 px-3 py-1 text-xs font-semibold shadow hover:bg-blue-400 hover:text-white transition"><?= __('test_link') ?></a>
            <?php endif; ?>
          </div>
          <?php if ($url !== ''): ?>
          <div class="text-xs text-gray-600 break-all">
            <span class="font-medium mr-1"><?= __('current_zoom_link') ?></span>
            <a href="<?= $encodedUrl ?>" target="_blank" rel="noopener noreferrer" class="underline decoration-dotted hover:decoration-solid text-gray-700"><?= $encodedUrl ?></a>
          </div>
          <?php endif; ?>
        </div>
        <?php
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_vip'])) {
    csrf_check($_POST['csrf_token'] ?? null);
    $studentId = (int)$_POST['toggle_vip'];
    $value = (int)($_POST['vip_value'] ?? 0);
    if ($studentId > 0) {
        $stmt = $db->prepare('UPDATE users SET is_vip = ? WHERE id = ?');
        $stmt->execute([$value ? 1 : 0, $studentId]);
        $_SESSION['vip_status_flash'] = true;
        $redirect = 'admin.php';
        if (!empty($_SERVER['QUERY_STRING'])) {
            $redirect .= '?' . $_SERVER['QUERY_STRING'];
        }
        header('Location: ' . $redirect);
        exit;
    }
}

// Manage session cancellations
$db->exec("CREATE TABLE IF NOT EXISTS session_cancellations (
    date DATE NOT NULL,
    session VARCHAR(10) NOT NULL,
    PRIMARY KEY(date, session)
)");
$cancelMsg = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_session'])) {
    csrf_check($_POST['csrf_token'] ?? null);
    $date = $_POST['cancel_date'] ?? '';
    $sess = $_POST['cancel_session_type'] ?? 'morning';
    $action = $_POST['cancel_action'] ?? 'add';
    if ($date) {
        if ($action === 'add') {
            $stmt = $db->prepare("INSERT IGNORE INTO session_cancellations(date, session) VALUES (?, ?)");
            $stmt->execute([$date, $sess]);
            $cancelMsg = __('cancel_added');
        } else {
            $stmt = $db->prepare("DELETE FROM session_cancellations WHERE date=? AND session=?");
            $stmt->execute([$date, $sess]);
            $cancelMsg = __('cancel_removed');
        }
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

  <style>
    .admin-section {
      border: 1px solid rgba(118, 168, 158, 0.35);
      border-radius: 0.75rem;
      background: rgba(255, 255, 255, 0.95);
      box-shadow: 0 20px 45px -30px rgba(32, 69, 61, 0.35);
      margin-bottom: 1.5rem;
      overflow: hidden;
    }
    .admin-section summary {
      cursor: pointer;
      list-style: none;
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 0.75rem;
      padding: 0.9rem 1.1rem;
      background: #edf8f5;
      color: #205246;
      font-weight: 600;
      font-size: 1.05rem;
      border-bottom: 1px solid rgba(118, 168, 158, 0.35);
    }
    .admin-section summary::-webkit-details-marker {
      display: none;
    }
    .admin-section summary::after {
      content: "+";
      font-size: 1.2rem;
      line-height: 1;
      color: inherit;
      transition: transform 0.2s ease;
    }
    .admin-section[open] summary {
      background: #dff3ec;
      color: #174338;
      box-shadow: inset 0 0 0 1px rgba(32, 69, 61, 0.1);
    }
    .admin-section[open] summary::after {
      content: "–";
      transform: rotate(180deg);
    }
    .admin-section__content {
      padding: 1.25rem 1.1rem 1.5rem;
      display: grid;
      gap: 1rem;
    }
    @media (max-width: 640px) {
      .admin-section summary {
        font-size: 1rem;
        padding: 0.8rem 1rem;
      }
      .admin-section__content {
        padding: 1rem;
      }
    }
  </style>

  <details class="admin-section" open>
    <summary>Cập nhật Zoom</summary>
    <div class="admin-section__content">
      <?php if ($zoomUpdated): ?>
        <div class="p-3 rounded bg-green-100 text-green-700 text-sm"><?= __('zoom_links_updated') ?></div>
      <?php endif; ?>

      <form method="post" class="mb-6 bg-white/95 rounded-xl shadow px-4 py-3 flex flex-col gap-3">
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">
        <input type="hidden" name="zoom_links" value="1">
        <div class="grid gap-4">
          <div class="grid gap-2">
            <label class="font-semibold text-mint-text text-sm uppercase tracking-wide"><?= __('zoom_links_title') ?></label>
            <div class="flex flex-col gap-2">
              <?php render_zoom_link_field($zoomLinks, 'student', 'morning', 'zoom_morning_label'); ?>
              <?php render_zoom_link_field($zoomLinks, 'student', 'evening', 'zoom_evening_label'); ?>
            </div>
          </div>
          <div class="grid gap-2">
            <label class="font-semibold text-mint-text text-sm uppercase tracking-wide"><?= __('zoom_links_vip_title') ?></label>
            <div class="flex flex-col gap-2">
              <?php render_zoom_link_field($zoomLinks, 'vip', 'morning', 'zoom_vip_morning_label'); ?>
              <?php render_zoom_link_field($zoomLinks, 'vip', 'evening', 'zoom_vip_evening_label'); ?>
            </div>
          </div>
        </div>
        <button class="self-start rounded-lg bg-mint text-mint-text font-semibold px-4 py-2 text-sm shadow hover:bg-mint-dark hover:text-white transition"><?= __('save_zoom_links') ?></button>
      </form>
    </div>
  </details>

  <details class="admin-section"<?= $cancelMsg ? ' open' : '' ?>>
    <summary>Hủy buổi</summary>
    <div class="admin-section__content">
      <?php if ($cancelMsg): ?>
        <div class="p-3 rounded bg-green-100 text-green-700 text-sm"><?= $cancelMsg ?></div>
      <?php endif; ?>

      <form method="post" class="mb-6 bg-white/95 rounded-xl shadow px-4 py-3 flex flex-col gap-3">
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">
        <input type="hidden" name="cancel_session" value="1">
        <label class="font-semibold text-mint-text"><?= __('cancel_session_title') ?></label>
        <div class="flex flex-col sm:flex-row gap-2 items-center">
          <input type="date" name="cancel_date" class="rounded border border-mint px-2 py-1 focus:border-mint-dark focus:ring-mint" required>
          <select name="cancel_session_type" class="rounded border border-mint px-2 py-1 focus:border-mint-dark focus:ring-mint">
            <option value="morning"><?= __('morning') ?></option>
            <option value="evening"><?= __('evening') ?></option>
          </select>
          <button name="cancel_action" value="add" class="rounded-lg bg-mint text-mint-text font-semibold px-4 py-2 text-sm shadow hover:bg-mint-dark hover:text-white transition"><?= __('cancel_add_button') ?></button>
          <button name="cancel_action" value="remove" class="rounded-lg border border-mint text-mint-text font-medium px-4 py-2 text-sm hover:bg-mint hover:text-white transition"><?= __('cancel_delete_button') ?></button>
        </div>
      </form>
    </div>
  </details>

  <details class="admin-section"<?= ($notifyDeleted || $notifySuccess) ? ' open' : '' ?>>
    <summary>Gửi thông báo</summary>
    <div class="admin-section__content">
      <?php if ($notifyDeleted): ?>
        <div class="p-3 rounded bg-yellow-100 text-yellow-800 text-sm">
          <?= __('notification_deleted') ?>
        </div>
      <?php endif; ?>

      <?php if ($notifySuccess): ?>
        <div class="p-3 rounded bg-green-100 text-green-700 text-sm"><?= __('notification_sent') ?></div>
      <?php endif; ?>

      <form method="post" class="mb-6 bg-white/95 rounded-xl shadow px-4 py-4 flex flex-col gap-4">
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">
        <h3 class="font-semibold text-mint-text text-lg"><?= __('send_notification') ?></h3>
        <div class="grid gap-3">
          <div class="flex flex-col gap-1">
            <label for="notify_title" class="text-sm font-medium text-mint-text"><?= __('notification_title_label') ?></label>
            <input id="notify_title" name="notify_title" type="text" class="border border-mint rounded px-3 py-2 focus:border-mint-dark focus:ring-mint" placeholder="<?= __('notification_title_placeholder') ?>">
          </div>
          <div class="grid gap-3 sm:grid-cols-2">
            <div class="flex flex-col gap-1">
              <label for="notify_type" class="text-sm font-medium text-mint-text"><?= __('notification_type_label') ?></label>
              <select id="notify_type" name="notify_type" class="border border-mint rounded px-3 py-2 focus:border-mint-dark focus:ring-mint">
                <option value="general"><?= __('notification_type_general') ?></option>
                <option value="cancellation"><?= __('notification_type_cancellation') ?></option>
              </select>
            </div>
            <div class="flex flex-col gap-1">
              <label for="notify_scope" class="text-sm font-medium text-mint-text"><?= __('notification_scope_label') ?></label>
              <select id="notify_scope" name="notify_scope" class="border border-mint rounded px-3 py-2 focus:border-mint-dark focus:ring-mint">
                <option value="both"><?= __('notification_scope_both') ?></option>
                <option value="morning"><?= __('notification_scope_morning') ?></option>
                <option value="evening"><?= __('notification_scope_evening') ?></option>
              </select>
            </div>
          </div>
          <div class="flex flex-col gap-1">
            <label for="notify_expires" class="text-sm font-medium text-mint-text"><?= __('notification_expires_label') ?></label>
            <input id="notify_expires" type="datetime-local" name="notify_expires" class="border border-mint rounded px-3 py-2 focus:border-mint-dark focus:ring-mint">
          </div>
          <div class="flex flex-col gap-1">
            <label for="notify" class="text-sm font-medium text-mint-text"><?= __('notification_message_label') ?></label>
            <textarea id="notify" name="notify_message" class="border border-mint rounded px-3 py-2 min-h-[120px] focus:border-mint-dark focus:ring-mint" placeholder="<?= __('notification_placeholder') ?>" required></textarea>
          </div>
        </div>
        <button class="self-start rounded-lg bg-mint text-mint-text font-semibold px-4 py-2 text-sm shadow hover:bg-mint-dark hover:text-white transition"><?= __('send_notification') ?></button>
      </form>
    </div>
  </details>

  <details class="admin-section"<?= empty($recentNotifications) ? '' : ' open' ?>>
    <summary>Danh sách thông báo</summary>
    <div class="admin-section__content">
      <?php if (empty($recentNotifications)): ?>
        <p class="text-sm text-gray-500"><?= __('notification_none_admin') ?></p>
      <?php else: ?>
        <div class="flex flex-col divide-y divide-gray-100">
          <?php foreach ($recentNotifications as $note): ?>
            <?php
              $typeBadgeClass = $note['type'] === 'cancellation'
                ? 'bg-red-100 text-red-600'
                : 'bg-emerald-100 text-emerald-700';
              $scopeKey = $note['session_scope'] === 'morning'
                ? 'notification_scope_morning'
                : ($note['session_scope'] === 'evening'
                    ? 'notification_scope_evening'
                    : 'notification_scope_both');
              $createdText = sprintf(__('notification_created_at'), date('H:i d/m/Y', strtotime($note['created_at'])));
              $expiresText = $note['expires_at']
                ? sprintf(__('notification_expires_at'), date('H:i d/m/Y', strtotime($note['expires_at'])))
                : __('notification_no_expiry');
            ?>
            <div class="py-3 flex flex-col gap-2 <?= !empty($note['is_expired']) ? 'opacity-70' : '' ?>">
              <div class="flex flex-wrap items-center justify-between gap-2">
                <div class="flex flex-wrap items-center gap-2">
                  <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold <?= $typeBadgeClass ?>">
                    <?= $note['type'] === 'cancellation' ? __('notification_type_cancellation') : __('notification_type_general') ?>
                  </span>
                  <span class="text-xs text-gray-500"><?= __($scopeKey) ?></span>
                  <?php if (!empty($note['is_expired'])): ?>
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] bg-gray-200 text-gray-600 font-medium">
                      <?= __('notification_expired_badge') ?>
                    </span>
                  <?php endif; ?>
                </div>
                <div class="text-xs text-gray-400 text-right flex flex-col">
                  <span><?= $createdText ?></span>
                  <span><?= $expiresText ?></span>
                </div>
              </div>
              <?php if (!empty($note['title'])): ?>
                <div class="text-sm font-semibold text-mint-text"><?= htmlspecialchars($note['title']) ?></div>
              <?php endif; ?>
              <div class="text-sm text-gray-700 whitespace-pre-line"><?= nl2br(htmlspecialchars($note['message'])) ?></div>
              <div class="flex justify-end">
                <form method="post" class="inline">
                  <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">
                  <button name="delete_notification" value="<?= $note['id'] ?>" class="text-xs text-red-600 hover:text-red-800 font-semibold">
                    <?= __('notification_delete') ?>
                  </button>
                </form>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </details>

  <details class="admin-section" open>
    <summary>Bộ lọc</summary>
    <div class="admin-section__content">
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
    </div>
  </details>

  <details class="admin-section" open>
    <summary>Bảng học viên</summary>
    <div class="admin-section__content">
      <div class="overflow-x-auto rounded-xl shadow-2xl shadow-[#76a89e26] bg-white/95">
        <?php if ($vipStatusUpdated): ?>
          <div class="mx-4 mt-4 mb-3 rounded-lg bg-emerald-100 text-emerald-700 px-4 py-2 text-sm font-medium">
            <?= __('vip_status_updated') ?>
          </div>
        <?php endif; ?>
        <table class="w-full min-w-[650px] text-sm border-separate border-spacing-y-1">
          <thead>
            <tr class="bg-mint/10 text-mint-text text-base font-semibold">
              <th class="py-2 px-2 sm:px-3 rounded-tl-xl whitespace-nowrap"><?= __('tbl_id') ?></th>
              <th class="py-2 px-2 sm:px-3 whitespace-nowrap"><?= __('tbl_name') ?></th>
              <th class="py-2 px-2 sm:px-3 whitespace-nowrap"><?= __('tbl_email') ?></th>
              <th class="py-2 px-2 sm:px-3 whitespace-nowrap"><?= __('tbl_phone') ?></th>
              <th class="py-2 px-2 sm:px-3 text-center whitespace-nowrap"><?= __('tbl_remaining') ?></th>
              <th class="py-2 px-2 sm:px-3 text-center whitespace-nowrap"><?= __('tbl_type') ?></th>
              <th class="py-2 px-2 sm:px-3 rounded-tr-xl text-center whitespace-nowrap"><?= __('tbl_actions') ?></th>
            </tr>
          </thead>
          <tbody>
          <?php if (empty($students)): ?>
            <tr>
              <td colspan="7" class="py-5 text-center text-gray-400"><?= __('not_found') ?></td>
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
              <td class="px-2 sm:px-3 py-2">
                <?php $isVip = db_bool($row['is_vip'] ?? null); ?>
                <div class="flex flex-col items-center gap-2">
                  <span class="inline-flex items-center gap-1 rounded-full px-3 py-1 text-xs font-semibold <?= $isVip ? 'bg-amber-100 text-amber-700' : 'bg-gray-100 text-gray-600' ?>">
                    <?php if ($isVip): ?>
                      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-4 w-4">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                      </svg>
                      <?= __('vip_badge') ?>
                    <?php else: ?>
                      <?= __('standard_badge') ?>
                    <?php endif; ?>
                  </span>
                  <form method="post" class="flex flex-col items-center gap-1 text-xs">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">
                    <input type="hidden" name="toggle_vip" value="<?= $row['id'] ?>">
                    <input type="hidden" name="vip_value" value="<?= $isVip ? 0 : 1 ?>">
                    <button class="rounded border <?= $isVip ? 'border-gray-300 text-gray-600 hover:bg-gray-100' : 'border-amber-400 text-amber-600 hover:bg-amber-50' ?> px-3 py-1 font-medium transition" type="submit">
                      <?= $isVip ? __('remove_vip_button') : __('make_vip_button') ?>
                    </button>
                  </form>
                </div>
              </td>
              <td class="px-2 sm:px-3 py-2 text-center flex flex-wrap gap-2 justify-center items-center">
                <!-- CỘNG BUỔI -->
                <form method="post" action="add_sessions.php" class="flex gap-1 items-center">
                  <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">
                  <input type="hidden" name="uid" value="<?= $row['id'] ?>">
                  <input type="number" name="add" value="1"
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
    </div>
  </details>
</main>

<script>
  document.addEventListener('DOMContentLoaded', function () {
    const sections = Array.from(document.querySelectorAll('.admin-section'));
    sections.forEach(function (section) {
      section.addEventListener('toggle', function () {
        if (section.open) {
          sections.forEach(function (other) {
            if (other !== section && other.open) {
              other.open = false;
            }
          });
        }
      });
    });
  });
</script>

<?php include 'footer.php'; ?>
