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
    $lang = $_SESSION['lang'] ?? 'vi';
    $title = __('session_cancelled');
    $detail = __('session_cancelled_detail');
    echo <<<HTML
<!DOCTYPE html>
<html lang="{$lang}">
<head>
{$gtm_head}
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>{$title}</title>
<script src="https://cdn.tailwindcss.com"></script>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500&display=swap" rel="stylesheet">
<style>body{font-family:'Montserrat',sans-serif;}</style>
</head>
<body class="min-h-screen flex items-center justify-center bg-gradient-to-br from-green-50 to-emerald-100">
{$gtm_body}
<div class="bg-white rounded-2xl shadow-lg p-6 mx-4 text-center max-w-md">
  <h1 class="text-2xl font-semibold text-red-600 mb-4">{$title}</h1>
  <p class="text-lg text-gray-800 leading-relaxed">{$detail}</p>
  <a href="dashboard.php" class="mt-6 inline-block px-6 py-2 rounded-full bg-emerald-600 text-white hover:bg-emerald-700">Quay lại</a>
</div>
</body>
</html>
HTML;
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
    // Kiểm tra xem đã ghi nhận buổi này trong hôm nay chưa
    $stmt = $db->prepare("SELECT 1 FROM sessions WHERE user_id=? AND session=? AND DATE(created_at)=CURDATE()");
    $stmt->execute([$uid, $session]);
    if (!$stmt->fetchColumn()) {
        // Trừ buổi, lưu lịch sử
        $db->prepare("UPDATE users SET remaining=remaining-1 WHERE id=?")->execute([$uid]);
        $db->prepare("INSERT INTO sessions(user_id, session) VALUES (?,?)")->execute([$uid, $session]);
    }
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

