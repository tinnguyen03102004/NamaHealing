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
$firstSessionUpdated = !empty($_SESSION['first_session_flash'] ?? false);
unset($_SESSION['vip_status_flash']);
unset($_SESSION['first_session_flash']);

notifications_setup($db);
ensure_users_has_vip($db);
ensure_users_has_first_session_flag($db);
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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mark_first_session'])) {
    csrf_check($_POST['csrf_token'] ?? null);
    $studentId = (int)$_POST['mark_first_session'];
    $value = (int)($_POST['first_session_value'] ?? 0);
    if ($studentId > 0) {
        $stmt = $db->prepare('UPDATE users SET first_session_completed = ? WHERE id = ?');
        $stmt->execute([$value ? 1 : 0, $studentId]);
        $_SESSION['first_session_flash'] = true;
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

$cancelledSessions = [];
try {
    $stmt = $db->query("SELECT date, session FROM session_cancellations ORDER BY date DESC, session ASC");
    $cancelledSessions = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $cancelledSessions = [];
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
$adminTabs = [
    ['id' => 'students', 'label' => __('admin_tab_students')],
    ['id' => 'zoom', 'label' => __('admin_tab_zoom')],
    ['id' => 'notifications', 'label' => __('admin_tab_notifications')],
];
?>

<main class="mx-auto max-w-6xl px-2 sm:px-4">
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
    </div>
  </div>

  <?php include __DIR__ . '/views/components/admin/tab-nav.php'; ?>

  <div class="admin-panels">
    <?php $tabId = 'students'; include __DIR__ . '/views/components/admin/section-students.php'; ?>
    <?php $tabId = 'zoom'; include __DIR__ . '/views/components/admin/section-zoom.php'; ?>
    <?php $tabId = 'notifications'; include __DIR__ . '/views/components/admin/section-notifications.php'; ?>
  </div>
</main>

<script>
  document.addEventListener('DOMContentLoaded', function () {
    var tabsContainer = document.querySelector('[data-admin-tabs]');
    if (!tabsContainer) {
      return;
    }

    var tabButtons = Array.from(tabsContainer.querySelectorAll('[data-tab-target]'));
    var tabSections = Array.from(document.querySelectorAll('[data-tab-content]'));

    if (!tabButtons.length || !tabSections.length) {
      return;
    }

    tabsContainer.classList.add('admin-tabs--enhanced');

    tabSections.forEach(function (section, index) {
      var isActive = index === 0;
      section.toggleAttribute('hidden', !isActive);
      section.setAttribute('data-tab-active', isActive ? 'true' : 'false');
    });

    tabButtons.forEach(function (button, index) {
      var isActive = index === 0;
      button.classList.toggle('is-active', isActive);
      button.setAttribute('aria-selected', isActive ? 'true' : 'false');
      if (!isActive) {
        button.setAttribute('tabindex', '-1');
      } else {
        button.removeAttribute('tabindex');
      }

      button.addEventListener('click', function () {
        var targetId = button.getAttribute('data-tab-target');

        tabSections.forEach(function (section) {
          var isMatch = section.getAttribute('data-tab-content') === targetId;
          section.toggleAttribute('hidden', !isMatch);
          section.setAttribute('data-tab-active', isMatch ? 'true' : 'false');
        });

        tabButtons.forEach(function (otherButton) {
          var isCurrent = otherButton === button;
          otherButton.classList.toggle('is-active', isCurrent);
          otherButton.setAttribute('aria-selected', isCurrent ? 'true' : 'false');
          if (!isCurrent) {
            otherButton.setAttribute('tabindex', '-1');
          } else {
            otherButton.removeAttribute('tabindex');
          }
        });
      });
    });
  });
</script>


<?php include 'footer.php'; ?>
