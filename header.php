<?php
require_once __DIR__ . '/i18n.php';
?>
<!DOCTYPE html>
<html lang="<?= $_SESSION['lang'] ?? 'vi' ?>">
<head>
  <!-- Google Tag Manager -->
  <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
  new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
  j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
  'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
  })(window,document,'script','dataLayer','GTM-MZ695946');</script>
  <!-- End Google Tag Manager -->
  <?php if (basename($_SERVER['PHP_SELF']) === 'welcome.php'): ?>
  <!-- Google tag (gtag.js) event -->
  <script>
    gtag('event', 'conversion_event_page_view', {
      // <event_parameters>
    });
  </script>
  <?php endif; ?>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= isset($pageTitle) ? htmlspecialchars($pageTitle) : 'NamaHealing' ?></title>
  <?php if (!empty($metaDescription)): ?>
    <meta name="description" content="<?= htmlspecialchars($metaDescription) ?>">
  <?php endif; ?>
  <?php
    $ogTitle = $metaOgTitle ?? $pageTitle ?? '';
    if (!empty($ogTitle)):
  ?>
    <meta property="og:title" content="<?= htmlspecialchars($ogTitle) ?>">
  <?php endif; ?>
  <?php if (!empty($metaDescription)): ?>
    <meta property="og:description" content="<?= htmlspecialchars($metaDescription) ?>">
  <?php endif; ?>
  <?php if (!empty($metaImage)): ?>
    <meta property="og:image" content="<?= htmlspecialchars($metaImage) ?>">
  <?php endif; ?>
  <?php if (!empty($metaUrl)): ?>
    <meta property="og:url" content="<?= htmlspecialchars($metaUrl) ?>">
  <?php endif; ?>
  <?php if (!empty($metaOgType)): ?>
    <meta property="og:type" content="<?= htmlspecialchars($metaOgType) ?>">
  <?php endif; ?>
  <!-- Tailwind CSS -->
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="/style.css?v=<?php echo filemtime(__DIR__.'/style.css'); ?>">
  <!-- Google Fonts: Manrope (primary) + Cormorant Garamond (logo) + Montserrat/Noto Sans (fallbacks) -->
  <link href='https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700&family=Cormorant+Garamond:wght@400;500;600;700&family=Montserrat:wght@400;500;600;700&family=Noto+Sans:wght@400;500;600;700&display=swap' rel='stylesheet'>
  <style>
    body {
      font-family: 'Manrope','Montserrat','Noto Sans',Arial,sans-serif !important;
    }
    .logo-text {
      font-family: serif !important;
      font-size: 1.3rem;
      font-weight: 500;
    }
    .font-heading, h1, h2, h3, h4, h5, h6 {
      font-family: 'Manrope','Montserrat','Noto Sans',Arial,sans-serif !important;
      font-weight: 700;
      letter-spacing: .01em;
    }
  </style>
</head>
<body class="bg-[#f9fafb] text-[#374151] pt-20 sm:pt-16">
  <!-- Google Tag Manager (noscript) -->
    <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-MZ695946"
  height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
  <!-- End Google Tag Manager (noscript) -->
  <!-- Header bar -->
  <header class="w-full fixed top-0 left-0 z-10 header-bar py-2 sm:h-16 sm:py-0 bg-transparent backdrop-blur-[10px]">
    <div class="max-w-7xl mx-auto px-6 w-full flex flex-col sm:flex-row items-center sm:justify-between h-full">
      <div class="flex items-center justify-between w-full">
        <a href="home.php" class="flex items-center gap-2 text-xl text-[#374151] logo-text">
          <img src="LogoNama.svg" alt="NamaHealing logo" class="w-8 h-8" />
          <span class="logo-text">NamaHealing</span>
        </a>
                      <div class="flex items-center gap-3 sm:gap-6">
              <?php if (!isset($_SESSION['uid'])): ?>
                <a href="login.php" class="px-4 py-2 min-h-[40px] text-base rounded-full border border-[#9dcfc3] hover:bg-[#9dcfc3] hover:text-white transition flex items-center justify-center">
                  <?= __('login_button') ?>
                </a>
              <?php else: ?>
                <?php if (($_SESSION['role'] ?? '') === 'teacher'): ?>
                  <a href="teacher_dashboard.php" class="px-4 py-2 min-h-[40px] text-base rounded-full border border-[#9dcfc3] hover:bg-[#9dcfc3] hover:text-white transition flex items-center justify-center">
                    Teacher
                  </a>
                <?php endif; ?>
                <a href="logout.php" class="px-4 py-2 min-h-[40px] text-base rounded-full border border-[#9dcfc3] hover:bg-[#9dcfc3] hover:text-white transition flex items-center justify-center">
                  <?= __('logout_button') ?>
                </a>
              <?php endif; ?>
              <span class="hidden sm:block">
              <a href="?lang=vi" class="text-sm country-code <?= ($_SESSION['lang'] ?? 'vi') === 'vi' ? 'font-bold' : '' ?>" aria-label="<?= __('language_vi') ?>">vn</a>
              |
              <a href="?lang=en" class="text-sm country-code <?= ($_SESSION['lang'] ?? 'vi') === 'en' ? 'font-bold' : '' ?>" aria-label="<?= __('language_en') ?>">en</a>
              |
              <a href="?lang=uk" class="text-sm country-code <?= ($_SESSION['lang'] ?? 'vi') === 'uk' ? 'font-bold' : '' ?>" aria-label="<?= __('language_uk') ?>">uk</a>
            </span>
          </div>
      </div>
      <div class="sm:hidden w-full text-center mt-1">
        <a href="?lang=vi" class="text-sm country-code <?= ($_SESSION['lang'] ?? 'vi') === 'vi' ? 'font-bold' : '' ?>" aria-label="<?= __('language_vi') ?>">vn</a>
        |
        <a href="?lang=en" class="text-sm country-code <?= ($_SESSION['lang'] ?? 'vi') === 'en' ? 'font-bold' : '' ?>" aria-label="<?= __('language_en') ?>">en</a>
        |
        <a href="?lang=uk" class="text-sm country-code <?= ($_SESSION['lang'] ?? 'vi') === 'uk' ? 'font-bold' : '' ?>" aria-label="<?= __('language_uk') ?>">uk</a>
      </div>
    </div>
  </header>
  <?php if (isset($_SESSION['uid']) && $_SESSION['role'] === 'student' && isset($notifications)): ?>
    <div class="fixed top-20 right-4 z-50">
      <div class="relative">
        <button id="notif-btn" data-csrf="<?= $_SESSION['csrf_token']; ?>" class="relative p-2 bg-white rounded-full shadow">
          <span class="text-2xl">🔔</span>
          <?php if ($unreadCount > 0): ?>
            <span id="notif-count" class="absolute top-0 right-0 translate-x-1/2 -translate-y-1/2 bg-red-500 text-white text-xs rounded-full px-1"><?= $unreadCount ?></span>
          <?php endif; ?>
        </button>
        <div id="notif-list" class="hidden absolute right-0 mt-2 w-72 max-w-[90vw] bg-white border border-gray-200 rounded shadow-lg max-h-[80vh] overflow-y-auto text-sm">
          <div class="px-4 py-2 font-semibold border-b"><?= __('notifications') ?></div>
          <?php if (!empty($notifications)): foreach ($notifications as $n): ?>
            <?php
              $title = trim($n['title'] ?? '');
              $typeBadgeClass = ($n['type'] ?? 'general') === 'cancellation'
                ? 'bg-red-100 text-red-600'
                : 'bg-emerald-100 text-emerald-700';
              $scope = $n['session_scope'] ?? 'both';
              $scopeKey = $scope === 'morning'
                ? 'notification_scope_morning'
                : ($scope === 'evening' ? 'notification_scope_evening' : 'notification_scope_both');
            ?>
            <div class="px-4 py-3 border-b last:border-0">
              <div class="flex flex-wrap items-center justify-between gap-2">
                <div class="flex flex-wrap items-center gap-2">
                  <?php if ($title !== ''): ?>
                    <span class="text-sm font-semibold text-mint-text"><?= htmlspecialchars($title) ?></span>
                  <?php endif; ?>
                  <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold <?= $typeBadgeClass ?>">
                    <?= ($n['type'] ?? 'general') === 'cancellation' ? __('notification_type_cancellation') : __('notification_type_general') ?>
                  </span>
                  <span class="text-[11px] text-gray-500"><?= __($scopeKey) ?></span>
                </div>
                <span class="text-xs text-gray-400"><?= date('H:i d/m/Y', strtotime($n['created_at'])) ?></span>
              </div>
              <div class="mt-2 text-sm text-gray-700 whitespace-pre-line"><?= nl2br(htmlspecialchars($n['message'])) ?></div>
              <?php if (!empty($n['expires_at'])): ?>
                <div class="mt-2 text-[11px] text-gray-400"><?= sprintf(__('notification_expires_at'), date('H:i d/m/Y', strtotime($n['expires_at']))) ?></div>
              <?php endif; ?>
            </div>
          <?php endforeach; else: ?>
            <div class="px-4 py-2 text-gray-500"><?= __('no_notifications') ?></div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  <?php endif; ?>
