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
    // Chuyển hướng tự động tới ứng dụng Zoom trên mọi thiết bị
    $app_url = preg_replace('#^https?://#', 'zoommtg://', $url);
    if (stripos($app_url, 'zoommtg://') !== 0 && stripos($app_url, 'zoomus://') !== 0) {
        $app_url = $url;
    }
    $fallback_url = preg_replace('#^zoommtg://#', 'https://', $app_url);
    $fallback_url = preg_replace('#^zoomus://#', 'https://', $fallback_url);
    echo "<!DOCTYPE html><html><head><meta charset='utf-8'><title>Redirecting...</title>";
    echo "<script>window.location.href=" . json_encode($app_url) . ";";
    echo "setTimeout(function(){window.location.href=" . json_encode($fallback_url) . ";},2000);";
    echo "</script></head><body><p>Redirecting to Zoom...</p></body></html>";
    exit;
}

header('Location: welcome.php');
exit;
