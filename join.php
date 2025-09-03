<?php
define('REQUIRE_LOGIN', true);
require 'config.php';

$gtm_head = <<<'HTML'
<!-- Google Tag Manager -->
<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src='https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);})(window,document,'script','dataLayer','GTM-MZ695946');</script>
<!-- End Google Tag Manager -->
HTML;

$gtm_body = <<<'HTML'
<!-- Google Tag Manager (noscript) -->
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-MZ695946" height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
<!-- End Google Tag Manager (noscript) -->
HTML;

if (!isset($_SESSION['uid']) || $_SESSION['role'] !== 'student') {
    header('Location: login.php'); exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check($_POST['csrf_token'] ?? null);
    $session = $_POST['session'] ?? 'morning';
} else {
    $session = $_GET['s'] ?? 'morning';
}

$uid = $_SESSION['uid'];
if (!in_array($session, ['morning', 'evening'])) $session = 'morning';

// Check for canceled session
$db->exec("CREATE TABLE IF NOT EXISTS session_cancellations (
    date DATE NOT NULL,
    session VARCHAR(10) NOT NULL,
    PRIMARY KEY(date, session)
)");
$today = date('Y-m-d');
$stmt = $db->prepare("SELECT 1 FROM session_cancellations WHERE date=? AND session=?");
$stmt->execute([$today, $session]);
if ($stmt->fetchColumn()) {
    echo "<!DOCTYPE html><html><head><meta charset='utf-8'><title>" . __('session_cancelled') . "</title></head><body><p style='text-align:center;margin-top:20px;font-weight:bold;color:red;'>" . __('session_cancelled') . "</p></body></html>";
    exit;
}

function should_count_session(string $session): bool {
    $now = time();
    if ($session === 'morning') {
        $start = strtotime('06:00');
    } else {
        $start = strtotime('20:45');
    }
    return ($now >= $start - 10 * 60) && ($now <= $start + 45 * 60);
}
$shouldCount = should_count_session($session);

// Kiểm tra số buổi còn lại
$stmt = $db->prepare("SELECT remaining FROM users WHERE id=?");
$stmt->execute([$uid]);
$remain = (int)$stmt->fetchColumn();

if ($remain <= 0) {
    header('Location: welcome.php');
    exit;
}

if ($shouldCount) {
    // Trừ buổi, lưu lịch sử
    $db->prepare("UPDATE users SET remaining=remaining-1 WHERE id=?")->execute([$uid]);
    $db->prepare("INSERT INTO sessions(user_id, session) VALUES (?,?)")->execute([$uid, $session]);
}

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

