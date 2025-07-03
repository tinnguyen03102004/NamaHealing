<?php
if (session_status() === PHP_SESSION_NONE) session_start();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>NamaHealing</title>
  <!-- Tailwind CSS -->
  <script src="https://cdn.tailwindcss.com"></script>
  <!-- Custom style -->
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
<body class="bg-[#f9fafb] text-[#374151]">
  <!-- Header bar -->
  <header class="h-16 w-full fixed top-0 left-0 z-10 flex items-center header-bar backdrop-blur bg-transparent">
    <div class="max-w-7xl mx-auto px-6 w-full flex items-center justify-between">
      <a href="home.php" class="flex items-center gap-2 text-xl text-[#374151] logo-text">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
        </svg>
        <span class="logo-text">NamaHealing</span>
      </a>
      <nav>
        <ul class="flex items-center gap-6 text-base">
          <?php if (!isset($_SESSION['uid'])): ?>
            <li>
              <a href="login.php" class="px-4 py-2 min-h-[40px] text-base rounded-full border border-[#9dcfc3] hover:bg-[#9dcfc3] hover:text-white transition flex items-center justify-center">
                Đăng nhập
              </a>
            </li>
          <?php else: ?>
            <li>
              <a href="logout.php" class="px-4 py-2 min-h-[40px] text-base rounded-full border border-[#9dcfc3] hover:bg-[#9dcfc3] hover:text-white transition flex items-center justify-center">
                Đăng xuất
              </a>
            </li>
          <?php endif; ?>
        </ul>
      </nav>
    </div>
  </header>
  <div style="height: 4rem;"></div> <!-- Đệm để tránh header che nội dung -->
