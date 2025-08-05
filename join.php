<?php
define('REQUIRE_LOGIN', true);
require 'config.php';
if (!isset($_SESSION['uid']) || $_SESSION['role'] !== 'student') {
    header('Location: login.php'); exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: dashboard.php');
    exit;
}

csrf_check($_POST['csrf_token'] ?? null);

$uid = $_SESSION['uid'];
$session = ($_POST['session'] ?? 'morning');
if (!in_array($session, ['morning', 'evening'])) $session = 'morning';

// Kiểm tra số buổi còn lại
$stmt = $db->prepare("SELECT remaining FROM users WHERE id=?");
$stmt->execute([$uid]);
$remain = $stmt->fetchColumn();

if ($remain > 0) {
    // Trừ buổi, lưu lịch sử, redirect sang Zoom
    $db->prepare("UPDATE users SET remaining=remaining-1 WHERE id=?")->execute([$uid]);
    $db->prepare("INSERT INTO sessions(user_id, session) VALUES (?,?)")->execute([$uid, $session]);
    $stmt = $db->prepare("SELECT url FROM zoom_links WHERE session=?");
    $stmt->execute([$session]);
    $url = $stmt->fetchColumn();
    header("Location: $url"); exit;
}

// Định dạng link Zalo chuẩn
$zalo_1 = "https://zalo.me/0839269501";
$zalo_2 = "https://zalo.me/0989399278";

include 'header.php';
?>
<div class="d-flex justify-content-center align-items-center" style="min-height:60vh;">
  <div class="card p-4 text-center" style="max-width:400px;">
    <h3 class="mb-3 text-danger"><?= __('out_of_sessions') ?></h3>
    <div class="mb-3 text-secondary">
      <?= __('contact_admin') ?><br>
    </div>
    <a class="btn btn-success w-100 mb-2" href="<?= $zalo_1 ?>" target="_blank" rel="noopener">
      <?= __('renew_zalo1') ?>
    </a>
    <a class="btn btn-success w-100 mb-2" href="<?= $zalo_2 ?>" target="_blank" rel="noopener">
      <?= __('renew_zalo2') ?>
    </a>
    <a class="btn btn-secondary w-100" href="dashboard.php"><?= __('back_to_dashboard') ?></a>
  </div>
</div>
<?php include 'footer.php'; ?>
