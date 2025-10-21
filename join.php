<?php
define('REQUIRE_LOGIN', true);
require 'config.php';
require_once __DIR__ . '/helpers/Schema.php';
require_once __DIR__ . '/helpers/Value.php';

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

ensure_users_has_vip($db);
ensure_users_has_first_session_flag($db);
ensure_zoom_links_audience($db);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check($_POST['csrf_token'] ?? null);
    $session = $_POST['session'] ?? 'morning';
} else {
    $session = $_GET['s'] ?? 'morning';
}

$uid = $_SESSION['uid'];
if (!in_array($session, ['morning', 'evening'])) $session = 'morning';

$stmt = $db->prepare('SELECT remaining, is_vip, first_session_completed, COALESCE(full_name, "") AS full_name FROM users WHERE id=?');
$stmt->execute([$uid]);
$userInfo = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
$remain = (int)($userInfo['remaining'] ?? 0);
$isVip = db_bool($userInfo['is_vip'] ?? null);
$firstSessionCompleted = db_bool($userInfo['first_session_completed'] ?? null);

date_default_timezone_set('Asia/Ho_Chi_Minh');

try {
    $attendanceCount = (int)$db->query("SELECT COUNT(*) FROM sessions WHERE user_id=" . (int)$uid)->fetchColumn();
} catch (Throwable $e) {
    $attendanceCount = 0;
}

$isFirstTimer = ($attendanceCount === 0 && !$firstSessionCompleted);
$nowTs = time();
if ($session === 'morning') {
    $blockStart = strtotime('today 05:55');
    $blockEnd = strtotime('today 06:55');
} else {
    $blockStart = strtotime('today 20:40');
    $blockEnd = strtotime('today 21:40');
}

if ($isFirstTimer && $nowTs >= $blockStart && $nowTs <= $blockEnd) {
    $langAttr = htmlspecialchars($_SESSION['lang'] ?? 'vi', ENT_QUOTES, 'UTF-8');
    $title = $session === 'morning' ? __('join_morning') : __('join_evening');
    $safeTitle = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
    $message = __('first_timer_block_window_message');
    $safeMessage = htmlspecialchars($message, ENT_QUOTES, 'UTF-8');
    $buttonLabel = __('back_to_dashboard');
    $safeButtonLabel = htmlspecialchars($buttonLabel, ENT_QUOTES, 'UTF-8');
    echo <<<HTML
<!DOCTYPE html>
<html lang="{$langAttr}">
<head>
{$gtm_head}
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>{$safeTitle}</title>
<script src="https://cdn.tailwindcss.com"></script>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500&display=swap" rel="stylesheet">
<style>body{font-family:'Montserrat',sans-serif;}</style>
</head>
<body class="min-h-screen flex items-center justify-center bg-gradient-to-br from-green-50 to-emerald-100">
{$gtm_body}
<main class="bg-white/90 backdrop-blur rounded-2xl shadow-lg p-6 mx-4 text-center max-w-md">
  <h1 class="text-xl font-semibold text-emerald-700 mb-3">{$safeTitle}</h1>
  <p class="text-base text-gray-700 leading-relaxed mb-5">{$safeMessage}</p>
  <a href="dashboard.php" class="inline-flex items-center justify-center px-5 py-2 rounded-full bg-emerald-600 text-white hover:bg-emerald-700 transition">{$safeButtonLabel}</a>
</main>
</body>
</html>
HTML;
    exit;
}

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

// Kiểm tra số buổi còn lại và trạng thái VIP

if ($remain <= 0) {
    header('Location: welcome.php');
    exit;
}

if ($shouldCount) {
    try {
        $startedTransaction = false;
        if (!$db->inTransaction()) {
            $db->beginTransaction();
            $startedTransaction = true;
        }

        // Khóa bản ghi học viên để tránh bị trừ buổi nhiều lần khi truy cập đồng thời
        $lockStmt = $db->prepare('SELECT remaining FROM users WHERE id=? FOR UPDATE');
        $lockStmt->execute([$uid]);
        $lockedRemaining = $lockStmt->fetchColumn();

        if ($lockedRemaining === false) {
            if ($startedTransaction && $db->inTransaction()) {
                $db->rollBack();
            }
            throw new RuntimeException('User not found while counting session');
        }

        // Kiểm tra xem đã ghi nhận buổi này trong hôm nay chưa
        $stmt = $db->prepare("SELECT 1 FROM sessions WHERE user_id=? AND session=? AND DATE(created_at)=CURDATE()");
        $stmt->execute([$uid, $session]);
        $alreadyCounted = (bool)$stmt->fetchColumn();

        if (!$alreadyCounted && (int)$lockedRemaining > 0) {
            // Trừ buổi, lưu lịch sử
            $db->prepare('UPDATE users SET remaining=remaining-1, first_session_completed=1 WHERE id=?')->execute([$uid]);
            $db->prepare('INSERT INTO sessions(user_id, session) VALUES (?,?)')->execute([$uid, $session]);
        }

        if ($startedTransaction && $db->inTransaction()) {
            $db->commit();
        }
    } catch (Throwable $e) {
        if ($startedTransaction && $db->inTransaction()) {
            $db->rollBack();
        }
        throw $e;
    }
}

$db->exec("CREATE TABLE IF NOT EXISTS zoom_links (
    session VARCHAR(10) NOT NULL,
    audience VARCHAR(10) NOT NULL DEFAULT 'student',
    url TEXT NOT NULL,
    PRIMARY KEY (session, audience)
)");

$audience = $isVip ? 'vip' : 'student';
$stmt = $db->prepare("SELECT url FROM zoom_links WHERE session=? AND audience=?");
$stmt->execute([$session, $audience]);
$url = $stmt->fetchColumn();
if (!$url && $isVip) {
    $stmt = $db->prepare("SELECT url FROM zoom_links WHERE session=? AND audience='student'");
    $stmt->execute([$session]);
    $url = $stmt->fetchColumn();
}

if (!$url) {
    $lang = $_SESSION['lang'] ?? 'vi';
    $message = __('zoom_link_missing');
    $messageSafe = htmlspecialchars($message, ENT_QUOTES, 'UTF-8');
    $buttonLabel = __('back_to_dashboard');
    $buttonLabelSafe = htmlspecialchars($buttonLabel, ENT_QUOTES, 'UTF-8');
    $langSafe = htmlspecialchars($lang, ENT_QUOTES, 'UTF-8');
    echo <<<HTML
<!DOCTYPE html>
<html lang="{$langSafe}">
<head>
{$gtm_head}
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Zoom</title>
<script src="https://cdn.tailwindcss.com"></script>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500&display=swap" rel="stylesheet">
<style>body{font-family:'Montserrat',sans-serif;}</style>
</head>
<body class="min-h-screen flex items-center justify-center bg-gradient-to-br from-green-50 to-emerald-100">
{$gtm_body}
<div class="bg-white rounded-2xl shadow-lg p-6 mx-4 text-center max-w-md">
  <h1 class="text-xl font-semibold text-emerald-700 mb-3">Zoom</h1>
  <p class="text-base text-gray-700 leading-relaxed">{$messageSafe}</p>
  <a href="dashboard.php" class="mt-5 inline-block px-5 py-2 rounded-full bg-emerald-600 text-white hover:bg-emerald-700 transition">{$buttonLabelSafe}</a>
</div>
</body>
</html>
HTML;
    exit;
}

$ua = strtolower($_SERVER['HTTP_USER_AGENT'] ?? '');
$parsed = parse_url($url);
$queryParams = [];
if (!empty($parsed['query'])) {
    parse_str($parsed['query'], $queryParams);
}
$meetingId = null;
if (isset($parsed['path']) && preg_match('/\/j\/(\d+)/', $parsed['path'], $m)) {
    $meetingId = $m[1];
}
$pwd = $queryParams['pwd'] ?? ($queryParams['passcode'] ?? '');
$appUrl = $url;
if ($meetingId) {
    if (str_contains($ua, 'iphone') || str_contains($ua, 'ipad') || str_contains($ua, 'ipod')) {
        $appUrl = "zoomus://zoom.us/join?confno={$meetingId}" . ($pwd ? "&pwd={$pwd}" : '');
    } elseif (str_contains($ua, 'android')) {
        $appUrl = "zoomus://zoom.us/wc/join/{$meetingId}" . ($pwd ? "?pwd={$pwd}" : '');
    } else {
        $appUrl = "zoommtg://zoom.us/join?confno={$meetingId}" . ($pwd ? "&pwd={$pwd}" : '');
    }
}

$zoomSdkKey = trim((string)($_ENV['ZOOM_SDK_CLIENT_ID'] ?? ''));
$zoomSdkSecret = trim((string)($_ENV['ZOOM_SDK_CLIENT_SECRET'] ?? ''));
$canEmbedZoom = $meetingId && $zoomSdkKey !== '' && $zoomSdkSecret !== '';

if (!$canEmbedZoom) {
    $fallbackUrl = $url;
    echo "<!DOCTYPE html><html><head>{$gtm_head}<meta charset='utf-8'><title>Redirecting...</title>";
    echo "<script>window.location.href=" . json_encode($appUrl) . ";";
    echo "setTimeout(function(){window.location.href=" . json_encode($fallbackUrl) . ";},2000);";
    echo "</script></head><body>{$gtm_body}<p>Redirecting to Zoom...</p></body></html>";
    exit;
}

$displayName = trim((string)($userInfo['full_name'] ?? ''));
if ($displayName === '') {
    $displayName = 'Nama Healing Student';
}

$languageCode = $_SESSION['lang'] ?? 'vi';
$zoomLanguageMap = [
    'vi' => 'vi-VN',
    'en' => 'en-US',
    'uk' => 'uk-UA',
];
$zoomLanguage = $zoomLanguageMap[$languageCode] ?? 'en-US';
$csrfToken = $_SESSION['csrf_token'] ?? '';
$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$leaveUrl = $scheme . $host . '/dashboard.php';

$langAttr = htmlspecialchars($languageCode, ENT_QUOTES, 'UTF-8');
$title = $session === 'morning' ? __('join_morning') : __('join_evening');
$safeTitle = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
$heading = htmlspecialchars(__('zoom_join_browser_heading'), ENT_QUOTES, 'UTF-8');
$description = htmlspecialchars(__('zoom_join_browser_description'), ENT_QUOTES, 'UTF-8');
$openAppLabel = htmlspecialchars(__('zoom_open_in_app'), ENT_QUOTES, 'UTF-8');
$loadingText = htmlspecialchars(__('zoom_join_loading'), ENT_QUOTES, 'UTF-8');
$errorText = htmlspecialchars(__('zoom_join_error'), ENT_QUOTES, 'UTF-8');
$backLabel = htmlspecialchars(__('back_to_dashboard'), ENT_QUOTES, 'UTF-8');
$safeAppUrl = htmlspecialchars($appUrl, ENT_QUOTES, 'UTF-8');

$jsConfig = json_encode([
    'meetingNumber' => (string)$meetingId,
    'passcode' => (string)$pwd,
    'userName' => $displayName,
    'sdkKey' => $zoomSdkKey,
    'language' => $zoomLanguage,
    'csrfToken' => $csrfToken,
    'leaveUrl' => $leaveUrl,
    'appUrl' => $appUrl,
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
$loadingTextJson = json_encode(__('zoom_join_loading'));
$errorTextJson = json_encode(__('zoom_join_error'));
$openAppLabelJson = json_encode(__('zoom_open_in_app'));

echo <<<HTML
<!DOCTYPE html>
<html lang="{$langAttr}">
<head>
{$gtm_head}
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>{$safeTitle}</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500&display=swap" rel="stylesheet">
<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://source.zoom.us/2.18.5/css/zoom-meeting-embedded.min.css">
<style>
  body { font-family: 'Montserrat', sans-serif; background: linear-gradient(135deg, #f0fdf4, #dcfce7); }
  #meetingSDKElement { width: 100%; height: 100%; }
  .zoom-wrapper { min-height: 100vh; display: flex; flex-direction: column; }
  .zoom-container { flex: 1 1 auto; min-height: 60vh; }
</style>
</head>
<body class="zoom-wrapper">
{$gtm_body}
<header class="py-4 text-center">
  <h1 class="text-2xl font-semibold text-emerald-700">{$heading}</h1>
  <p class="mt-2 text-gray-700">{$description}</p>
</header>
<main class="flex-1">
  <div class="mx-auto max-w-6xl w-full px-4 pb-6">
    <div id="meetingSDKElement" class="zoom-container rounded-xl overflow-hidden shadow-lg bg-white"></div>
    <div class="mt-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
      <span id="join-status" class="text-sm text-gray-600">{$loadingText}</span>
      <div class="flex flex-wrap gap-3">
        <a id="open-app-button" href="{$safeAppUrl}" class="inline-flex items-center justify-center px-4 py-2 rounded-full bg-emerald-600 text-white hover:bg-emerald-700 transition" target="_blank" rel="noopener">{$openAppLabel}</a>
        <a href="dashboard.php" class="inline-flex items-center justify-center px-4 py-2 rounded-full border border-emerald-500 text-emerald-700 hover:bg-emerald-50 transition">{$backLabel}</a>
      </div>
    </div>
  </div>
</main>
<script type="application/json" id="zoom-config">{$jsConfig}</script>
<script src="https://source.zoom.us/2.18.5/lib/vendor/react.min.js"></script>
<script src="https://source.zoom.us/2.18.5/lib/vendor/react-dom.min.js"></script>
<script src="https://source.zoom.us/2.18.5/lib/vendor/redux.min.js"></script>
<script src="https://source.zoom.us/2.18.5/lib/vendor/redux-thunk.min.js"></script>
<script src="https://source.zoom.us/2.18.5/lib/vendor/lodash.min.js"></script>
<script src="https://source.zoom.us/2.18.5/zoom-meeting-embedded-2.18.5.min.js"></script>
<script>
  const zoomConfig = JSON.parse(document.getElementById('zoom-config').textContent);
  const messages = {
    loading: {$loadingTextJson},
    error: {$errorTextJson},
    openApp: {$openAppLabelJson}
  };
  const statusEl = document.getElementById('join-status');
  statusEl.textContent = messages.loading;

  async function joinMeeting() {
    if (!window.ZoomMtgEmbedded) {
      statusEl.textContent = messages.error + ' (SDK not loaded)';
      return;
    }
    const client = window.ZoomMtgEmbedded.createClient();
    const meetingSDKElement = document.getElementById('meetingSDKElement');
    try {
      await client.init({
        zoomAppRoot: meetingSDKElement,
        language: zoomConfig.language,
        customize: {
          meetingInfo: ['topic', 'host', 'mn', 'password'],
          toolbar: {
            buttons: [
              {
                text: messages.openApp,
                className: 'zoom-app-link',
                onClick: () => window.open(zoomConfig.appUrl, '_blank', 'noopener')
              }
            ]
          }
        }
      });

      const response = await fetch('zoom_signature.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        credentials: 'same-origin',
        body: JSON.stringify({
          meetingNumber: zoomConfig.meetingNumber,
          role: 0,
          csrf_token: zoomConfig.csrfToken
        })
      });

      if (!response.ok) {
        throw new Error('Signature request failed');
      }

      const data = await response.json();
      if (!data.signature) {
        throw new Error(data.error || 'Missing signature');
      }

      await client.join({
        signature: data.signature,
        sdkKey: data.sdkKey,
        meetingNumber: zoomConfig.meetingNumber,
        password: zoomConfig.passcode,
        userName: zoomConfig.userName,
        leaveUrl: zoomConfig.leaveUrl
      });
      statusEl.textContent = '';
    } catch (error) {
      console.error(error);
      statusEl.textContent = messages.error + ' ' + (error && error.message ? error.message : '');
    }
  }

  joinMeeting();
</script>
</body>
</html>
HTML;
exit;

