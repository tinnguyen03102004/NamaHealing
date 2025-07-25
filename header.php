<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/i18n.php';
?>
<!DOCTYPE html>
<html lang="<?= $_SESSION['lang'] ?? 'vi' ?>">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= isset($pageTitle) ? htmlspecialchars($pageTitle) : 'NamaHealing' ?></title>
  <?php if (!empty($metaDescription)): ?>
    <meta name="description" content="<?= htmlspecialchars($metaDescription) ?>">
  <?php endif; ?>
  <!-- Tailwind CSS -->
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="style.css">
  <!-- Google Fonts: Cormorant Garamond (logo) + Montserrat (content) -->
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;500;600;700&family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
  <style>
    body {
      font-family: 'Montserrat', Arial, sans-serif !important;
    }
    .logo-text {
      font-family: serif !important;
      font-size: 1.3rem;
      font-weight: 500;
    }
    .font-heading, h1, h2, h3, h4, h5, h6 {
      font-family: 'Montserrat', Arial, sans-serif !important;
      font-weight: 700;
      letter-spacing: .01em;
    }
  </style>
</head>
<body class="bg-[#f9fafb] text-[#374151] pt-24 sm:pt-16">
  <!-- Header bar -->
  <header class="w-full fixed top-0 left-0 z-10 header-bar py-4 sm:h-16 sm:py-0 bg-transparent backdrop-blur-[10px]">
    <div class="max-w-7xl mx-auto px-6 w-full flex flex-col sm:flex-row items-center sm:justify-between h-full">
      <div class="flex items-center justify-between w-full">
        <a href="home.php" class="flex items-center gap-2 text-xl text-[#374151] logo-text">
          <img src="logoNama.png" alt="NamaHealing logo" class="w-8 h-8" />
          <span class="logo-text">NamaHealing</span>
        </a>
        <div class="flex items-center gap-3 sm:gap-6">
          <?php if (!isset($_SESSION['uid'])): ?>
            <a href="login.php" class="px-4 py-2 min-h-[40px] text-base rounded-full border border-[#9dcfc3] hover:bg-[#9dcfc3] hover:text-white transition flex items-center justify-center">
              <?= __('login_button') ?>
            </a>
          <?php else: ?>
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
  
