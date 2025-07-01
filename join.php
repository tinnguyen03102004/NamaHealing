<?php
define('REQUIRE_LOGIN', true);
require 'config.php';
if (!isset($_SESSION['uid']) || $_SESSION['role'] !== 'student') {
    header('Location: login.php'); exit;
}

$uid = $_SESSION['uid'];
$session = ($_GET['s'] ?? 'morning');
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
    <h3 class="mb-3 text-danger">Bạn đã hết buổi học!</h3>
    <div class="mb-3 text-secondary">
      Vui lòng liên hệ admin để được cộng thêm buổi nhé.<br>
    </div>
    <a class="btn btn-success w-100 mb-2" href="<?= $zalo_1 ?>" target="_blank" rel="noopener">
      Đăng ký gia hạn qua Zalo Nguyễn Hữu Tín 0839269501
    </a>
    <a class="btn btn-success w-100 mb-2" href="<?= $zalo_2 ?>" target="_blank" rel="noopener">
      Đăng ký gia hạn qua Zalo Mai Hoàn 0989399278
    </a>
    <a class="btn btn-secondary w-100" href="dashboard.php">Quay lại trang chính</a>
  </div>
</div>
<?php include 'footer.php'; ?>
