<?php
require 'config.php';

$pageTitle = 'NamaHealing – Câu chuyện của thầy Võ Trọng Nghĩa';
$metaDescription = 'Câu chuyện chữa lành của thầy Võ Trọng Nghĩa và lớp thiền NamaHealing.';
$metaOgTitle = $pageTitle;
include 'header.php';
?>

<style>
  :root {
    --story-primary: #0a3325;
    --story-secondary: #f9d365;
    --story-light: #fffdf5;
    --story-accent: #e9f5f2;
    --story-ink: #1a1a1a;
  }

  .story-page {
    background: var(--story-light);
    color: var(--story-ink);
    padding-bottom: 4rem;
  }

  .story-page section {
    scroll-margin-top: 120px;
  }

  .story-page .hero {
    position: relative;
    background: linear-gradient(135deg, rgba(249, 211, 101, 0.95) 0%, rgba(255, 244, 210, 0.95) 100%);
    padding: 5.5rem 1.5rem 4.5rem;
    overflow: hidden;
  }

  .story-page .hero .pattern {
    position: absolute;
    inset: -40% -10%;
    background-image:
      radial-gradient(circle at 20px 20px, rgba(10, 51, 37, 0.18) 0, rgba(10, 51, 37, 0.18) 6px, transparent 7px),
      radial-gradient(circle at 60px 60px, rgba(10, 51, 37, 0.12) 0, rgba(10, 51, 37, 0.12) 6px, transparent 7px);
    background-size: 140px 140px;
    opacity: 0.5;
    pointer-events: none;
  }

  .story-page .hero-inner {
    position: relative;
    z-index: 1;
    max-width: 1180px;
    margin: 0 auto;
    display: grid;
    gap: 2.5rem;
    align-items: center;
  }

  .story-page .hero-copy {
    max-width: 640px;
  }

  .story-page .hero-kicker {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.85rem;
    text-transform: uppercase;
    letter-spacing: 0.25em;
    font-weight: 600;
    color: rgba(10, 51, 37, 0.75);
    background: rgba(255, 255, 255, 0.55);
    padding: 0.5rem 1.25rem;
    border-radius: 999px;
    box-shadow: 0 10px 30px rgba(10, 51, 37, 0.15);
    margin-bottom: 1.5rem;
  }

  .story-page .hero h1 {
    font-size: clamp(2.5rem, 5vw, 3.5rem);
    font-weight: 700;
    color: var(--story-primary);
    margin-bottom: 1.25rem;
    line-height: 1.12;
  }

  .story-page .hero .lead {
    font-size: clamp(1.1rem, 2vw, 1.35rem);
    line-height: 1.7;
    color: rgba(10, 51, 37, 0.92);
  }

  .story-page .hero-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 1rem;
    margin-top: 2.25rem;
  }

  .story-page .btn-primary,
  .story-page .btn-secondary {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    padding: 0.9rem 2.2rem;
    border-radius: 999px;
    font-weight: 600;
    text-decoration: none;
    transition: transform 0.2s ease, box-shadow 0.2s ease, background 0.2s ease, color 0.2s ease;
  }

  .story-page .btn-primary {
    background: var(--story-primary);
    color: #fff;
    box-shadow: 0 14px 30px rgba(10, 51, 37, 0.28);
  }

  .story-page .btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 18px 36px rgba(10, 51, 37, 0.3);
  }

  .story-page .btn-secondary {
    background: rgba(255, 255, 255, 0.85);
    color: var(--story-primary);
    border: 1px solid rgba(10, 51, 37, 0.18);
  }

  .story-page .btn-secondary:hover {
    background: rgba(255, 255, 255, 1);
    transform: translateY(-2px);
    box-shadow: 0 12px 24px rgba(10, 51, 37, 0.15);
  }

  .story-page .hero-highlight {
    position: relative;
  }

  .story-page .hero-card {
    background: rgba(255, 255, 255, 0.92);
    border-radius: 28px;
    padding: 2.5rem;
    box-shadow: 0 20px 60px rgba(10, 51, 37, 0.18);
    border: 1px solid rgba(10, 51, 37, 0.12);
    font-size: 1.05rem;
    line-height: 1.7;
    color: rgba(10, 51, 37, 0.9);
  }

  .story-page .hero-card::before {
    content: '“';
    font-size: 4rem;
    line-height: 0.5;
    color: rgba(10, 51, 37, 0.25);
    position: absolute;
    top: -0.6rem;
    left: 1.5rem;
  }

  .story-page .section {
    padding: 4rem 1.5rem;
  }

  .story-page .section-alt {
    background: #ffffff;
  }

  .story-page .section-muted {
    background: var(--story-accent);
  }

  .story-page .section-inner {
    max-width: 1080px;
    margin: 0 auto;
  }

  .story-page .section-header {
    margin-bottom: 2.5rem;
  }

  .story-page .section-eyebrow {
    text-transform: uppercase;
    letter-spacing: 0.2em;
    font-size: 0.82rem;
    font-weight: 600;
    color: rgba(10, 51, 37, 0.6);
    margin-bottom: 0.75rem;
  }

  .story-page .section h2 {
    font-size: clamp(2rem, 3.3vw, 2.6rem);
    margin: 0;
    color: var(--story-primary);
  }

  .story-page .section-lead {
    font-size: 1.1rem;
    line-height: 1.7;
    color: rgba(26, 26, 26, 0.85);
    max-width: 860px;
  }

  .story-page .card-grid {
    display: grid;
    gap: 1.75rem;
  }

  @media (min-width: 900px) {
    .story-page .hero-inner {
      grid-template-columns: minmax(0, 1.1fr) minmax(0, 0.9fr);
    }

    .story-page .card-grid.two-columns {
      grid-template-columns: repeat(2, minmax(0, 1fr));
    }
  }

  .story-page .card {
    background: #ffffff;
    border-radius: 24px;
    padding: 2.25rem;
    border: 1px solid rgba(10, 51, 37, 0.12);
    box-shadow: 0 14px 38px rgba(10, 51, 37, 0.12);
    line-height: 1.75;
    font-size: 1.02rem;
  }

  .story-page .timeline {
    list-style: none;
    margin: 0;
    padding: 0;
    display: grid;
    gap: 2rem;
  }

  @media (min-width: 900px) {
    .story-page .timeline.grid-3 {
      grid-template-columns: repeat(3, minmax(0, 1fr));
    }
  }

  .story-page .timeline-item {
    display: flex;
    gap: 1.25rem;
    align-items: flex-start;
    padding: 1.75rem;
    background: #ffffff;
    border-radius: 20px;
    border: 1px solid rgba(10, 51, 37, 0.1);
    box-shadow: 0 10px 26px rgba(10, 51, 37, 0.08);
  }

  .story-page .badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 2.5rem;
    height: 2.5rem;
    border-radius: 50%;
    background: var(--story-primary);
    color: #fff;
    font-weight: 700;
    font-size: 1.1rem;
    flex-shrink: 0;
  }

  .story-page .timeline-item h3 {
    margin: 0 0 0.6rem;
    font-size: 1.2rem;
    color: var(--story-primary);
  }

  .story-page .timeline-item p {
    margin: 0;
    color: rgba(26, 26, 26, 0.85);
    line-height: 1.7;
  }

  .story-page .table-wrapper {
    margin: 2.5rem 0;
    border-radius: 20px;
    border: 1px solid rgba(10, 51, 37, 0.12);
    overflow: hidden;
    box-shadow: 0 12px 28px rgba(10, 51, 37, 0.08);
    background: #ffffff;
  }

  .story-page .info-table {
    width: 100%;
    border-collapse: collapse;
    min-width: 480px;
  }

  .story-page .info-table th,
  .story-page .info-table td {
    padding: 1rem 1.25rem;
    text-align: left;
  }

  .story-page .info-table thead th {
    background: rgba(9, 51, 37, 0.08);
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    font-size: 0.82rem;
    color: rgba(10, 51, 37, 0.75);
  }

  .story-page .info-table tbody tr:nth-child(even) {
    background: rgba(233, 245, 242, 0.45);
  }

  .story-page .info-table tbody td {
    font-size: 1rem;
    color: rgba(26, 26, 26, 0.85);
  }

  .story-page .cta {
    text-align: center;
    margin-top: 2.5rem;
  }

  .story-page .cta-note {
    text-align: center;
    margin-top: 1.5rem;
    font-style: italic;
    color: rgba(10, 51, 37, 0.7);
  }

  .story-page .steps-grid {
    gap: 1.5rem;
  }

  .story-page .steps-grid .timeline-item {
    flex-direction: column;
    align-items: flex-start;
  }

  .story-page .steps-grid .badge {
    margin-bottom: 0.75rem;
  }

  @media (min-width: 768px) {
    .story-page .steps-grid {
      grid-template-columns: repeat(2, minmax(0, 1fr));
    }
  }

  @media (min-width: 1024px) {
    .story-page .steps-grid {
      grid-template-columns: repeat(4, minmax(0, 1fr));
    }
  }

  @media (max-width: 640px) {
    .story-page .hero {
      padding: 4.5rem 1.25rem 3.5rem;
    }

    .story-page .hero-actions {
      flex-direction: column;
      align-items: stretch;
    }

    .story-page .table-wrapper {
      overflow-x: auto;
      border-radius: 16px;
    }
  }
</style>

<div class="story-page">
  <section class="hero">
    <div class="pattern" aria-hidden="true"></div>
    <div class="hero-inner">
      <div class="hero-copy">
        <span class="hero-kicker"><?= __('story_page_kicker') ?></span>
        <h1><?= __('story_page_title') ?></h1>
        <p class="lead"><?= __('class_p3') ?></p>
        <div class="hero-actions">
          <a class="btn-primary" href="https://namahealing.com/register.php"><?= __('register_now') ?></a>
          <a class="btn-secondary" href="#teacher"><?= __('story_secondary_cta') ?></a>
        </div>
      </div>
      <div class="hero-highlight">
        <div class="hero-card">
          <?= __('class_p4') ?>
        </div>
      </div>
    </div>
  </section>

  <section class="section section-alt" id="teacher">
    <div class="section-inner">
      <header class="section-header">
        <p class="section-eyebrow"><?= __('story_teacher_subheading') ?></p>
        <h2><?= __('story_teacher_heading') ?></h2>
      </header>
      <div class="card-grid two-columns">
        <article class="card">
          <?= __('class_p1') ?>
        </article>
        <article class="card">
          <?= __('class_p2') ?>
        </article>
      </div>
    </div>
  </section>

  <section class="section section-muted" id="journey">
    <div class="section-inner">
      <header class="section-header">
        <p class="section-eyebrow">NamaHealing</p>
        <h2><?= __('story_journey_heading') ?></h2>
      </header>
      <ol class="timeline grid-3">
        <li class="timeline-item">
          <span class="badge">1</span>
          <div class="content">
            <h3><?= __('story_timeline_1_title') ?></h3>
            <p><?= __('story_timeline_1_body') ?></p>
          </div>
        </li>
        <li class="timeline-item">
          <span class="badge">2</span>
          <div class="content">
            <h3><?= __('story_timeline_2_title') ?></h3>
            <p><?= __('story_timeline_2_body') ?></p>
          </div>
        </li>
        <li class="timeline-item">
          <span class="badge">3</span>
          <div class="content">
            <h3><?= __('story_timeline_3_title') ?></h3>
            <p><?= __('story_timeline_3_body') ?></p>
          </div>
        </li>
      </ol>
    </div>
  </section>

  <section class="section section-alt" id="class">
    <div class="section-inner">
      <header class="section-header">
        <p class="section-eyebrow">NamaHealing</p>
        <h2><?= __('story_class_heading') ?></h2>
        <p class="section-lead"><?= __('register_time_desc') ?></p>
      </header>
      <div class="table-wrapper">
        <table class="info-table">
          <thead>
            <tr>
              <th><?= __('story_schedule_header_session') ?></th>
              <th><?= __('story_schedule_header_time') ?></th>
              <th><?= __('story_schedule_header_description') ?></th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td><?= __('morning_class') ?></td>
              <td><?= __('register_time_morning') ?></td>
              <td><?= __('morning_class_description') ?></td>
            </tr>
            <tr>
              <td><?= __('evening_class') ?></td>
              <td><?= __('register_time_evening') ?></td>
              <td><?= __('evening_class_description') ?></td>
            </tr>
          </tbody>
        </table>
      </div>
      <p><strong><?= __('register_fee_title') ?></strong> <?= __('register_fee_full') ?> – <?= __('register_fee_discount') ?></p>
      <p><strong><?= __('register_bank_title') ?></strong> <?= __('register_bank_holder') ?>, <?= __('register_bank_account') ?>.</p>
      <p><?= __('register_bank_note') ?></p>
    </div>
  </section>

  <section class="section" id="steps">
    <div class="section-inner">
      <header class="section-header">
        <p class="section-eyebrow">NamaHealing</p>
        <h2><?= __('story_steps_heading') ?></h2>
      </header>
      <ol class="timeline steps-grid">
        <li class="timeline-item">
          <span class="badge">1</span>
          <div class="content">
            <p><?= __('register_step1') ?></p>
          </div>
        </li>
        <li class="timeline-item">
          <span class="badge">2</span>
          <div class="content">
            <p><?= __('register_step2') ?></p>
          </div>
        </li>
        <li class="timeline-item">
          <span class="badge">3</span>
          <div class="content">
            <p><?= __('register_step3') ?></p>
          </div>
        </li>
        <li class="timeline-item">
          <span class="badge">4</span>
          <div class="content">
            <p><?= __('register_step4') ?></p>
          </div>
        </li>
      </ol>
      <div class="cta">
        <a class="btn-primary" href="https://namahealing.com/register.php"><?= __('register_now') ?></a>
      </div>
      <p class="cta-note">
        <?= __('register_support') ?>
      </p>
    </div>
  </section>
</div>

<?php include 'footer.php'; ?>
