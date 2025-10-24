<?php
require 'config.php';

$pageTitle = 'NamaHealing – Câu chuyện của thầy Võ Trọng Nghĩa';
$metaDescription = 'Câu chuyện chữa lành của thầy Võ Trọng Nghĩa và lớp thiền NamaHealing.';
$metaOgTitle = $pageTitle;
include 'header.php';
?>

<style>
  :root {
    --primary: #0A3325;
    --secondary: #F9D365;
    --light-bg: #FFFDF5;
    --accent: #E9F5F2;
  }
  body {
    font-family: 'Montserrat', sans-serif;
    margin: 0;
    color: #1A1A1A;
    background: var(--light-bg);
    line-height: 1.6;
  }
  .header-bar {
    background: transparent;
    box-shadow: none;
  }
  .story-page {
    background: var(--light-bg);
    color: #1A1A1A;
  }
  .story-page .hero {
    position: relative;
    background: var(--secondary);
    padding: 5rem 1rem 4rem 1rem;
    text-align: center;
    border-bottom: 4px solid var(--primary);
    overflow: hidden;
  }
  .story-page .hero .pattern {
    position: absolute;
    top: -50px;
    left: 50%;
    transform: translateX(-50%);
    width: 100%;
    max-width: 1200px;
    height: 260px;
    opacity: 0.15;
    background-image:
      radial-gradient(circle at 20px 20px, rgba(10,51,37,0.6) 0, rgba(10,51,37,0.6) 6px, transparent 7px),
      radial-gradient(circle at 60px 60px, rgba(10,51,37,0.5) 0, rgba(10,51,37,0.5) 6px, transparent 7px);
    background-size: 120px 120px;
    background-repeat: repeat;
  }
  .story-page .hero h1 {
    font-size: 3rem;
    font-weight: 700;
    color: var(--primary);
    margin-bottom: 1rem;
  }
  .story-page .hero p {
    font-size: 1.25rem;
    max-width: 800px;
    margin: 0 auto;
    color: var(--primary);
  }
  .story-page .section {
    padding: 3rem 1rem;
    border-bottom: 2px solid var(--primary);
  }
  .story-page .section:nth-of-type(even) {
    background: var(--accent);
  }
  .story-page .section h2 {
    font-size: 2rem;
    font-weight: 700;
    margin-bottom: 1.5rem;
    color: var(--primary);
  }
  .story-page .card {
    background: #fff;
    border: 3px solid var(--primary);
    padding: 2rem;
    margin-bottom: 1.5rem;
    box-shadow: 6px 6px 0 rgba(0,0,0,0.15);
  }
  .story-page .card p {
    margin-bottom: 1rem;
  }
  .story-page .timeline {
    display: flex;
    flex-direction: column;
    gap: 2rem;
    margin-top: 2rem;
    list-style: none;
    padding: 0;
  }
  .story-page .timeline-item {
    display: flex;
    align-items: flex-start;
    gap: 1.5rem;
  }
  .story-page .timeline-item span.badge {
    display: inline-block;
    width: 2.2rem;
    height: 2.2rem;
    background: var(--primary);
    color: #fff;
    font-weight: 700;
    text-align: center;
    line-height: 2.2rem;
    border-radius: 50%;
    flex-shrink: 0;
  }
  .story-page .timeline-item .content {
    flex: 1;
  }
  .story-page .info-table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 1rem;
  }
  .story-page .info-table th,
  .story-page .info-table td {
    border: 2px solid var(--primary);
    padding: 0.75rem 1rem;
  }
  .story-page .info-table th {
    background: var(--secondary);
    color: var(--primary);
    text-align: left;
  }
  .story-page .cta {
    text-align: center;
    margin-top: 2rem;
  }
  .story-page .cta a {
    background: var(--primary);
    color: #fff;
    padding: 1rem 2rem;
    border-radius: 40px;
    text-decoration: none;
    font-weight: 600;
    box-shadow: 5px 5px 0 rgba(0,0,0,0.2);
    display: inline-block;
  }
  .story-page .cta a:hover {
    box-shadow: 6px 6px 0 rgba(0,0,0,0.28);
  }
  .story-page .italic-note {
    text-align: center;
    margin-top: 1rem;
    font-style: italic;
    color: var(--primary);
  }
  @media (max-width: 600px) {
    .story-page .hero h1 { font-size: 2rem; }
    .story-page .hero p { font-size: 1rem; }
    .story-page .section h2 { font-size: 1.5rem; }
    .story-page .timeline-item { flex-direction: column; }
    .story-page .timeline-item span.badge { margin-bottom: 0.5rem; }
  }
</style>

<div class="story-page">
  <section class="hero">
    <div class="pattern" aria-hidden="true"></div>
    <h1><?= __('class_p4') ?></h1>
    <p><?= __('class_p3') ?></p>
  </section>

  <section class="section" id="teacher">
    <h2>Câu chuyện của Thầy Võ Trọng Nghĩa</h2>
    <div class="card">
      <?= __('class_p1') ?>
    </div>
    <div class="card">
      <?= __('class_p2') ?>
    </div>
    <p><em><?= __('class_p4') ?></em></p>
  </section>

  <section class="section" id="journey">
    <h2>Hành trình đi tìm sự bình an</h2>
    <div class="timeline">
      <div class="timeline-item">
        <span class="badge">1</span>
        <div class="content">
          <strong>Tuổi thơ và sự nghiệp kiến trúc</strong>
          <p>Sinh ra ở Lệ Thủy, Quảng Bình, thầy trải qua tuổi thơ nghèo khó và bạo lực học đường. Sau đó, ông trở thành kiến trúc sư nổi tiếng với những công trình xanh như “House for Trees”, cà phê Gió và Nước, trường mẫu giáo Farming Kindergarten.</p>
        </div>
      </div>
      <div class="timeline-item">
        <span class="badge">2</span>
        <div class="content">
          <strong>Khủng hoảng và tìm kiếm</strong>
          <p>Áp lực từ công việc và quá khứ dẫn đến trầm cảm ẩn. Từ đó, thầy bắt đầu thực hành thiền định như phương pháp tự chữa lành và khám phá bản ngã.</p>
        </div>
      </div>
      <div class="timeline-item">
        <span class="badge">3</span>
        <div class="content">
          <strong>Giảng dạy và lan tỏa thiền</strong>
          <p>Không chỉ là kiến trúc sư, thầy còn giảng dạy tại Đại học Pennsylvania, Taipei và mở các lớp NamaHealing giúp những người gặp vấn đề tâm lý vượt qua trầm cảm, lo âu và mất ngủ.</p>
        </div>
      </div>
    </div>
  </section>

  <section class="section" id="class">
    <h2>Lớp Thiền NamaHealing</h2>
    <p><?= __('register_time_desc') ?></p>
    <table class="info-table">
      <tr><th>Buổi</th><th>Thời gian</th><th>Mô tả</th></tr>
      <tr><td><?= __('morning_class') ?></td><td><?= __('register_time_morning') ?></td><td><?= __('morning_class_description') ?></td></tr>
      <tr><td><?= __('evening_class') ?></td><td><?= __('register_time_evening') ?></td><td><?= __('evening_class_description') ?></td></tr>
    </table>
    <p><strong><?= __('register_fee_title') ?></strong> <?= __('register_fee_full') ?> – <?= __('register_fee_discount') ?></p>
    <p><strong><?= __('register_bank_title') ?></strong> <?= __('register_bank_holder') ?>, <?= __('register_bank_account') ?>.</p>
    <p><?= __('register_bank_note') ?></p>
  </section>

  <section class="section" id="steps">
    <h2>Các bước đăng ký</h2>
    <ol class="timeline">
      <li class="timeline-item"><span class="badge">1</span><div class="content"><p><?= __('register_step1') ?></p></div></li>
      <li class="timeline-item"><span class="badge">2</span><div class="content"><p><?= __('register_step2') ?></p></div></li>
      <li class="timeline-item"><span class="badge">3</span><div class="content"><p><?= __('register_step3') ?></p></div></li>
      <li class="timeline-item"><span class="badge">4</span><div class="content"><p><?= __('register_step4') ?></p></div></li>
    </ol>
    <div class="cta">
      <a href="home.php#register-modal"><?= __('register_now') ?></a>
    </div>
    <p class="italic-note">
      <?= __('register_support') ?>
    </p>
  </section>
</div>

<?php include 'footer.php'; ?>
