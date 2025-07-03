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
    body {
      margin: 0;
      padding: 0;
      font-family: 'Montserrat', sans-serif;
    }
    .hero {
      position: relative;
      min-height: 100vh;
      width: 100vw;
      background: url('https://images.unsplash.com/photo-1536514498073-50e69d39c6cf?q=80&w=2000&auto=format&fit=crop') center/cover no-repeat;
      display: flex;
      align-items: flex-start;
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
      padding-top: 18vh;
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
    .main-nav ul{
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
      .main-nav ul{gap:1.25rem;}
      .title-wrapper{padding-top:6vh;}
    }
    @media (max-width:550px){
      .site-title{font-size:1.9rem;}
      .subtitle{font-size:0.7rem;}
      .title-wrapper{padding-top:10vh;}
      .main-nav ul{flex-direction:column; gap:0.6rem;}
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
  </style>
</head>

<body>
  <!-- Thanh header trong suốt hoàn toàn -->
  <header class="h-16 w-full fixed top-0 left-0 z-10 flex items-center header-bar">
    <div class="max-w-7xl mx-auto px-6 w-full flex items-center justify-between">
        <a href="home.php" class="flex items-center gap-2 text-xl text-white font-semibold">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-green-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
            </svg>
            <span class="logo-text">NamaHealing</span>
        </a>
        <a href="login.php" class="text-sm px-4 py-1.5 rounded-full border border-white/40 hover:bg-white hover:text-black transition">
            Đăng nhập
        </a>
    </div>
  </header>

  <!-- ============ HERO ============ -->
  <main class="hero">
    <div class="title-wrapper">
        <p class="subtitle tracking-[.35em]">VO TRONG NGHIA</p>
        <h1 class="site-title font-semibold">NAMA HEALING</h1>
        <nav class="main-nav" aria-label="Điều hướng chính">
          <ul>
            <li><a href="home.php">Home</a></li>
            <li><a href="#" id="open-class-modal">Lớp học</a></li>
            <li><a href="#" id="open-register-modal">Đăng ký</a></li>
            <li><a href="https://zalo.me/0839269501" target="_blank" rel="noopener">Liên hệ</a></li>
          </ul>
        </nav>
    </div>
  </main>

  <!-- Modal: Giới thiệu Lớp học & Võ Trọng Nghĩa -->
  <div id="class-modal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 hidden">
    <div class="bg-white rounded-2xl max-w-lg w-full p-0 relative shadow-2xl overflow-y-auto max-h-[92vh] flex flex-col items-center">
      <button id="close-class-modal" class="absolute top-3 right-3 w-9 h-9 rounded-full bg-gray-100 hover:bg-gray-200 flex items-center justify-center text-xl font-bold">&times;</button>
      <div class="w-full flex items-center justify-center pt-7">
        <img src="VTN.jpg" alt="Võ Trọng Nghĩa" class="rounded-xl shadow-lg object-cover w-full max-w-[340px] h-auto border border-green-100">
      </div>
      <div class="w-full px-7 py-4 flex flex-col justify-center items-center">
        <h2 class="text-2xl font-semibold text-green-800 mb-2 text-center">NamaHealing</h2>
        <div class="text-base leading-relaxed text-gray-800 space-y-3 text-justify">
          <p>
            <b>Võ Trọng Nghĩa</b> là kiến trúc sư nổi tiếng thế giới về <span class="text-green-700 font-semibold">kiến trúc xanh</span> và <span class="text-yellow-900 font-semibold">kiến trúc tre</span> – các công trình của anh như "Nhà cho cây" (House for Trees), quán cà phê Gió và Nước, trường mẫu giáo Farming Kindergarten,... và nhiều công trình khác đã đạt những giải thưởng lớn, được quốc tế vinh danh. Tuy nhiên, ít ai biết rằng anh từng trải qua giai đoạn trầm cảm ẩn, dẫn đến quyết định tìm đến thiền như một phương pháp chữa lành.
          </p>
          <p>
            Sinh ra tại Lệ Thủy, Quảng Bình — vùng đất từng là tọa độ chiến tranh — tuổi thơ của Võ Trọng Nghĩa gắn liền với nghèo khó và bạo lực học đường. Dù sau này đạt được nhiều thành tựu trong sự nghiệp, anh vẫn phải đối mặt với những tổn thương tâm lý sâu sắc. Việc thực hành thiền đã giúp anh vượt qua khổ đau, tìm lại sự cân bằng và trở thành một "người bình thường" như chính anh chia sẻ.
          </p>
          <p>
            Bên cạnh vai trò kiến trúc sư, Võ Trọng Nghĩa còn từng là giáo sư thỉnh giảng tại Đại học Pennsylvania và Đại học Taipei, giảng dạy về thiền và sức khỏe tâm lý. Anh hiện đang tổ chức các lớp thiền NamaHealing nhằm hỗ trợ những người đang đối mặt với trầm cảm, rối loạn lo âu, mất ngủ và các vấn đề sức khỏe tâm thần khác.
          </p>
          <p class="italic text-green-700 font-medium">
            Hành trình của Võ Trọng Nghĩa là minh chứng cho khả năng chữa lành của thiền định, không chỉ giúp cá nhân anh vượt qua khủng hoảng mà còn lan tỏa năng lượng tích cực đến cộng đồng.
          </p>
        </div>
        <div class="text-center mt-5">
          <a href="#" id="to-register" class="inline-block px-6 py-2.5 rounded-full bg-emerald-600 hover:bg-emerald-700 text-white font-medium transition">
            Tôi muốn đăng ký ngay
          </a>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal: Hướng dẫn đăng ký lớp học -->
  <div id="register-modal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 hidden">
    <div class="bg-white rounded-2xl max-w-lg w-full p-6 relative shadow-2xl">
      <button id="close-register-modal" class="absolute top-3 right-3 w-9 h-9 rounded-full bg-gray-100 hover:bg-gray-200 flex items-center justify-center text-xl font-bold">&times;</button>
      
      <!-- Cảnh báo nổi bật -->
      <div class="mb-3 text-red-600 text-base font-semibold text-center uppercase tracking-wider">
        * Lưu ý: Lớp thiền này CHỈ dành cho những người gặp vấn đề tâm lý (trầm cảm, lo âu, stress, mất ngủ...).<br>
        Người bình thường / không có vấn đề tâm lý không được tham gia.
      </div>
      
      <div class="text-lg font-semibold mb-2 text-center">HƯỚNG DẪN ĐĂNG KÝ LỚP THIỀN ONLINE</div>
      <ol class="list-decimal pl-6 text-base mb-4 space-y-2">
        <li>
          <span class="font-medium">Thời gian & Hình thức:</span>  
          Học qua Zoom (bật mic & camera), linh hoạt:
          <ul class="list-disc pl-6">
            <li>Sáng: <b>6h00-6h40</b> (T3-T7)</li>
            <li>Tối: <b>20h45-21h30</b> (T2-CN)</li>
          </ul>
        </li>
        <li>
          <span class="font-medium">Học phí:</span>
          <ul class="list-disc pl-6">
            <li>Khóa 20 buổi: <span class="text-red-600 font-semibold">8.000.000đ</span></li>
            <li><span class="text-green-600 font-semibold">Ưu đãi chỉ 5.000.000đ</span> cho học viên khó khăn hiện đang sinh sống tại Việt Nam</li>
          </ul>
          Chuyển khoản:
          <ul class="list-disc pl-6 mt-1">
            <li>Chủ TK: <b>Trần Thị Mai Ly</b></li>
            <li>STK: <b>0371000429939</b> (Vietcombank, CN Hồ Chí Minh)</li>
            <li>Nội dung: <i>dong hoc phi thien _ họ tên _ sdt</i></li>
          </ul>
          <div class="mt-2 text-red-500 text-[15px] font-medium">
            * Đây là <b>hộ kinh doanh cá thể (Trần Thị Mai Ly)</b>, nộp thuế 7% doanh thu nên <b>không có hóa đơn</b>.
          </div>
        </li>
      </ol>
      <div class="mb-3">
        <span class="block font-medium">Các bước đăng ký:</span>
        <ul class="list-decimal pl-6 mt-1 space-y-1">
          <li>Chuyển khoản học phí theo thông tin trên.</li>
          <li>Chụp ảnh màn hình biên lai chuyển khoản.</li>
          <li>
            Điền thông tin tại 
            <a href="https://docs.google.com/forms/d/e/1FAIpQLSeLxjPK6Fq95bnVIe17dkadmPDC-1FLGIM2Fku0EMDALyq_4A/viewform?usp=sf_link"
             class="underline text-blue-600 font-medium" target="_blank">Form đăng ký học thiền</a>
          </li>
          <li>Gửi biên lai học phí cho Admin (qua Zalo góc phải) để nhận tài khoản và link Zoom.</li>
        </ul>
      </div>
      <div class="mt-2 text-sm text-gray-500 text-center">
        <b>Hỗ trợ/Zalo:</b> <a href="https://zalo.me/0839269501" target="_blank" class="underline">0839 269 501</a>
      </div>
    </div>
  </div>

  <!-- Modal script -->
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
