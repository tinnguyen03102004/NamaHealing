<?php
define('REQUIRE_LOGIN', true);
require 'config.php';

$gtm_head = <<<'HTML'
<!-- Google Tag Manager -->
<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src='https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);})(window,document,'script','dataLayer','GTM-PNFMJD34');</script>
<!-- End Google Tag Manager -->
HTML;

$gtm_body = <<<'HTML'
<!-- Google Tag Manager (noscript) -->
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-PNFMJD34" height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
<!-- End Google Tag Manager (noscript) -->
HTML;

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

// Kiểm tra khung giờ cho phép vào lớp
$now = date('H:i');
$allowed = false;
if ($session === 'morning') {
    $allowed = ($now >= '05:55' && $now <= '06:40');
} else {
    $allowed = ($now >= '20:40' && $now <= '21:30');
}

if (!$allowed) {
    $title = $session === 'morning' ? __('join_morning') : __('join_evening');
    echo "<!DOCTYPE html><html><head>{$gtm_head}<meta charset='utf-8'><title>{$title}</title></head><body>{$gtm_body}<p>" . __('not_class_time') . "</p></body></html>";
    exit;
}

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
    // Chuyển hướng tự động tới ứng dụng Zoom phù hợp với từng thiết bị
    $ua = strtolower($_SERVER['HTTP_USER_AGENT'] ?? '');
    $parsed = parse_url($url);
    $queryParams = [];
    if (!empty($parsed['query'])) parse_str($parsed['query'], $queryParams);
    $meetingId = null;
    if (isset($parsed['path']) && preg_match('/\/j\/(\d+)/', $parsed['path'], $m)) {
        $meetingId = $m[1];
    }
    $pwd = $queryParams['pwd'] ?? '';
    if (strpos($ua, 'iphone') !== false || strpos($ua, 'ipad') !== false || strpos($ua, 'ipod') !== false) {
        $app_url = "zoomus://zoom.us/join?confno={$meetingId}" . ($pwd ? "&pwd={$pwd}" : '');
    } elseif (strpos($ua, 'android') !== false) {
        $app_url = "zoomus://zoom.us/wc/join/{$meetingId}" . ($pwd ? "?pwd={$pwd}" : '');
    } else {
        $app_url = "zoommtg://zoom.us/join?confno={$meetingId}" . ($pwd ? "&pwd={$pwd}" : '');
    }
    if (!$meetingId) {
        $app_url = $url;
    }
    $fallback_url = $url;
    echo "<!DOCTYPE html><html><head>{$gtm_head}<meta charset='utf-8'><title>Redirecting...</title>";
    echo "<script>window.location.href=" . json_encode($app_url) . ";";
    echo "setTimeout(function(){window.location.href=" . json_encode($fallback_url) . ";},2000);";
    echo "</script></head><body>{$gtm_body}<p>Redirecting to Zoom...</p></body></html>";
    exit;
}

header('Location: welcome.php');
exit;
