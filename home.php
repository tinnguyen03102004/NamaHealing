<?php
// home.php – trang chủ NAMA HEALING
require 'config.php';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>NAMA HEALING – Home</title>

  <!-- TailwindCSS -->
  <script src="https://cdn.tailwindcss.com"></script>
  <!-- Google Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;500;600&family=Montserrat:wght@300;400;500&display=swap" rel="stylesheet">

  <style>
    html, body {
      margin: 0;
      padding: 0;
      font-family: 'Montserrat', sans-serif;
      overflow-x: hidden;   /* Ngăn scroll ngang */
      box-sizing: border-box;
    }
    .hero {
      position: relative;
      min-height: 100vh;
      width: 100%;
      background: url('https://images.unsplash.com/photo-1536514498073-50e69d39c6cf?q=80&w=2000&auto=format&fit=crop') center/cover no-repeat;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    .hero::before{
      content:'';
      position:absolute;
      inset:0;
      background:rgba(0,0,0,.22);
      z-index:1;
    }
    .title-wrapper{
      position: relative;
      z-index: 2;
      width: 100%;
      max-width: 800px;
      margin: 0 auto;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
    }
    .subtitle{
      font-family:'Montserrat',sans-serif;
      font-size:1.1rem;
      letter-spacing:.32em;
      margin:0 0 1.1rem 0;
      color:#e0e6df;
      opacity:.83;
      font-weight: 400;
    }
    .site-title{
      font-family:'Cormorant Garamond',serif;
      font-size:4rem;
      letter-spacing:.21em;
      margin:0 0 2.1rem 0;
      color:#fff;
      text-shadow: 0 4px 22px #26505044, 0 1px 0 #fff9;
    }
    .main-nav > ul{
      list-style:none;
      display:flex;
      gap:2.3rem;
      padding:0;margin:0;
      justify-content:center;
    }
    .main-nav a{
      text-decoration:none;
      font-family: 'Montserrat',sans-serif;
      font-weight:400;
      font-size:1.05rem;
      color:#fff;
      opacity:.87;
      padding-bottom:2px;
      border-bottom:2px solid transparent;
      transition:color .23s, border .23s, opacity .22s;
    }
    .main-nav a:hover{
      color:#ffe3c6;
      opacity:1;
      border-bottom:2px solid #ffe3c6;
    }
    @media (max-width:800px){
      .site-title{font-size:2.6rem;}
      .main-nav > ul{gap:1.25rem;}
      .title-wrapper{padding-top:8vh;}
    }
    @media (max-width:550px){
      .site-title{font-size:1.9rem;}
      .subtitle{font-size:0.85rem; letter-spacing:.15em;}
      .title-wrapper{padding-top:14vh;}
      .main-nav > ul{flex-direction:column; align-items:center; gap:1rem;}
      .main-nav a{display:block; padding:0.5rem 0; width:100%; text-align:center;}
    }
    /* Header */
    .header-bar {
      font-family: Arial, Helvetica, sans-serif;
      background: transparent !important;
      backdrop-filter: blur(10px);
      -webkit-backdrop-filter: blur(10px);
      box-shadow: none;
    }
      .logo-text {
        font-family: serif !important;
        font-size: 1.3rem;
        font-weight: 500;
      }
      .cta-button {
        text-align: center;
        margin-bottom: 2.5rem;
      }

  </style>
</head>

<body class="overflow-x-hidden">
  <!-- Thanh header trong suốt hoàn toàn -->
  <header class="w-full fixed top-0 left-0 z-10 header-bar h-16">
    <div class="max-w-7xl mx-auto px-6 w-full flex flex-col sm:flex-row items-center sm:justify-between h-full">
        <div class="flex items-center justify-between w-full">
            <a href="home.php" class="flex items-center gap-2 text-xl text-white font-semibold">
                <img src="logoNama.png" alt="NamaHealing logo" class="w-8 h-8" />
                <span class="logo-text">NamaHealing</span>
            </a>
            <div class="flex items-center gap-3 sm:gap-6">
                <a href="login.php" class="text-base px-4 py-2 min-h-[40px] rounded-full border border-white/40 hover:bg-white hover:text-black transition flex items-center justify-center">
                    <?= __('login_button') ?>
                </a>
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

 
  <!-- ============ HERO ============ -->
  <main class="hero">
    <div class="title-wrapper">
        <p class="subtitle">VO TRONG NGHIA</p>
        <h1 class="site-title font-semibold">NAMA HEALING</h1>
        <div class="cta-button mt-4">
          <a href="ukraine_meditation.php" class="px-4 py-2 rounded-full bg-emerald-600 text-white font-semibold hover:bg-emerald-700"><?= __('ukraine_title') ?></a>
        </div>
        <nav class="main-nav" aria-label="Điều hướng chính">
          <ul>
            <li><a href="#" id="open-class-modal"><?= __('home_nav_class') ?></a></li>
            <li><a href="#" id="open-register-modal"><?= __('home_nav_register') ?></a></li>
            <li><a href="articles.php"><?= __('home_nav_articles') ?></a></li>
            <li><a href="videos.php"><?= __('home_nav_videos') ?></a></li>
            <li><a href="chatbot.php"><?= __("chatbot") ?></a></li>
            <li class="relative group">
              <a href="#" class="cursor-pointer">
                <?= __('home_nav_docs') ?>
              </a>
              <ul class="absolute left-0 top-full mt-1 hidden group-hover:block bg-slate-800 text-white rounded shadow-md whitespace-nowrap z-20">
                <li><a href="prayers.php" class="block px-3 py-1 hover:bg-gray-100"><?= __('home_docs_prayer') ?></a></li>
                <li><a href="chanting.php" class="block px-3 py-1 hover:bg-gray-100"><?= __('home_docs_chant') ?></a></li>
                <li><a href="reference.php" class="block px-3 py-1 hover:bg-gray-100"><?= __('home_docs_reference') ?></a></li>
              </ul>
            </li>
            <li><a href="https://zalo.me/0839269501" target="_blank" rel="noopener"><?= __('home_nav_contact') ?></a></li>
          </ul>
        </nav>
    </div>
  </main>

  <!-- Modal: Giới thiệu Lớp học & Võ Trọng Nghĩa -->
  <div id="class-modal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 hidden">
    <div class="bg-white rounded-2xl w-[96vw] max-w-lg p-4 sm:p-6 relative shadow-2xl overflow-y-auto max-h-[95vh] flex flex-col items-center">
      <button id="close-class-modal" class="absolute top-2 right-2 w-9 h-9 rounded-full bg-gray-100 hover:bg-gray-200 flex items-center justify-center text-xl font-bold">&times;</button>
      <div class="w-full flex items-center justify-center pt-7">
        <img src="VTN.jpg" alt="Võ Trọng Nghĩa" class="rounded-xl shadow-lg object-cover w-full max-w-[340px] h-auto border border-green-100">
      </div>
        <div class="w-full px-4 sm:px-7 py-4 flex flex-col justify-center items-center">
          <h2 class="text-2xl font-semibold text-green-800 mb-2 text-center">NamaHealing</h2>
        <div class="text-base leading-relaxed text-gray-800 space-y-3 text-left sm:text-justify">
          <p><?= __('class_p1') ?></p>
          <p><?= __('class_p2') ?></p>
          <p><?= __('class_p3') ?></p>
          <p class="italic text-green-700 font-medium"><?= __('class_p4') ?></p>
        </div>
        <div class="text-center mt-5">
          <a href="#" id="to-register" class="inline-block px-6 py-2.5 min-h-[40px] rounded-full bg-emerald-600 hover:bg-emerald-700 text-white font-medium transition flex items-center justify-center">
            <?= __('register_now') ?>
          </a>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal: Hướng dẫn đăng ký lớp học -->
  <div id="register-modal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 hidden">
    <div class="bg-white rounded-2xl w-[96vw] max-w-lg p-4 sm:p-6 relative shadow-2xl overflow-y-auto max-h-[95vh] leading-relaxed">
      
      <!-- Cảnh báo nổi bật đầu modal -->
      <div class="mb-3 px-2 py-2 bg-red-50 border border-red-300 rounded text-red-700 text-base font-semibold text-center tracking-wider leading-snug">
        <span class="uppercase font-bold block">
          <?= __('register_warning_line1') ?>
        </span>
        <span class="block mt-1 text-red-700">
          <?= __('register_warning_line2') ?>
        </span>
      </div>
      
      <!-- Nút đóng đặt dưới cảnh báo, không che nội dung -->
      <button id="close-register-modal" class="absolute top-2 right-2 w-10 h-10 rounded-full bg-gray-100 hover:bg-gray-200 flex items-center justify-center text-xl font-bold z-10">&times;</button>
      
      <div class="text-lg font-semibold mb-2 text-center mt-1"><?= __('register_guide_title') ?></div>
      <ol class="list-decimal pl-5 sm:pl-6 text-base mb-4 space-y-3">
        <li>
          <span class="font-medium"><?= __('register_time_title') ?></span>
          <?= __('register_time_desc') ?>
            <ul class="list-disc pl-5 sm:pl-6 space-y-1">
            <li><?= __('register_time_morning') ?></li>
            <li><?= __('register_time_evening') ?></li>
          </ul>
        </li>
        <li>
          <span class="font-medium"><?= __('register_fee_title') ?></span>
          <ul class="list-disc pl-5 sm:pl-6">
            <li><?= __('register_fee_full') ?></li>
            <li><?= __('register_fee_discount') ?></li>
          </ul>
          <?= __('register_bank_title') ?>
            <ul class="list-disc pl-5 sm:pl-6 mt-1 space-y-2">
            <li><?= __('register_bank_holder') ?></li>
            <li><?= __('register_bank_account') ?></li>
            <li><?= __('register_bank_note') ?></li>
          </ul>
          <div class="mt-2 text-red-500 text-[15px] font-medium">
            <?= __('register_fee_note') ?>
          </div>
        </li>
      </ol>
      <div class="mb-3">
        <span class="block font-medium"><?= __('register_steps_title') ?></span>
        <ul class="list-decimal pl-5 sm:pl-6 mt-1 space-y-3">
          <li><?= __('register_step1') ?></li>
          <li><?= __('register_step2') ?></li>
          <li><?= __('register_step3') ?></li>
          <li><?= __('register_step4') ?></li>
        </ul>
      </div>
      <div class="mt-2 text-sm text-gray-500 text-center">
        <?= __('register_support') ?>
      </div>
    </div>
  </div>

  <script>
    // ---- Modal Giới thiệu lớp học ----
    document.getElementById('open-class-modal').onclick = function(e) {
      e.preventDefault();
      document.getElementById('class-modal').classList.remove('hidden');
    };
    document.getElementById('close-class-modal').onclick = function() {
      document.getElementById('class-modal').classList.add('hidden');
    };
    document.getElementById('class-modal').onclick = function(e) {
      if(e.target === this) this.classList.add('hidden');
    };

    // ---- Modal Đăng ký ----
    document.getElementById('open-register-modal').onclick = function(e) {
      e.preventDefault();
      document.getElementById('register-modal').classList.remove('hidden');
    };
    document.getElementById('close-register-modal').onclick = function() {
      document.getElementById('register-modal').classList.add('hidden');
    };
    document.getElementById('register-modal').onclick = function(e) {
      if(e.target === this) this.classList.add('hidden');
    };

    // Chuyển từ modal giới thiệu sang modal đăng ký khi bấm nút "Tôi muốn đăng ký ngay"
    document.getElementById('to-register').onclick = function(e) {
      e.preventDefault();
      document.getElementById('class-modal').classList.add('hidden');
      document.getElementById('register-modal').classList.remove('hidden');
    };
  </script>

  <?php include 'footer.php'; ?>
</body>
</html>