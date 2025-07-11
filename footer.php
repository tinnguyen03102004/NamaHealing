<?php if (session_status() === PHP_SESSION_NONE) session_start(); ?>
<?php require_once __DIR__ . '/i18n.php'; ?>
<footer class="site-footer text-center w-full">
  <?= sprintf(__('footer_message'), date('Y')) ?>
</footer>

<!-- Zalo FAB góc phải dưới -->
<div id="zalo-fab" title="Zalo tư vấn" tabindex="0" aria-label="Zalo tư vấn"></div>

<!-- Popup 2 số Zalo -->
<div id="zalo-branch" tabindex="0">
  <a href="https://zalo.me/0839269501" target="_blank" rel="noopener">
    <img src="logo-zalo.png" alt="Zalo"> Mr. Hữu Tín - 0839 269 501
  </a>
  <a href="https://zalo.me/0989399278" target="_blank" rel="noopener">
    <img src="logo-zalo.png" alt="Zalo"> Ms. Mai Hoàn - 0989 399 278
  </a>
</div>

<!-- CSS Zalo FAB chỉ riêng cho footer -->
<style>
  #zalo-fab {
    position: fixed;
    right: 25px;
    bottom: 25px;
    z-index: 9999;
    width: 58px;
    height: 58px;
    border-radius: 50%;
    background: rgba(2, 171, 255, 0) url('logo-zalo.png') center/34px 34px no-repeat;
    box-shadow: 0 2px 8px rgba(0,0,0,.18);
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: box-shadow .2s;
  }
  #zalo-fab:hover {
    box-shadow: 0 4px 16px rgba(0,0,0,.22);
  }
  #zalo-branch {
    display: none;
    position: fixed;
    right: 95px;
    bottom: 25px;
    z-index: 10000;
    background: #fff;
    border-radius: 14px;
    box-shadow: 0 2px 12px rgba(0,0,0,.14);
    padding: 12px 20px;
    min-width: 200px;
  }
  #zalo-branch.show {
    display: block;
  }
  #zalo-branch a {
    display: flex;
    align-items: center;
    padding: 6px 0;
    font-size: 16px;
    color: #02aaff;
    text-decoration: none;
    border-bottom: 1px solid #e9ecef;
  }
  #zalo-branch a:last-child {
    border-bottom: none;
  }
  #zalo-branch img {
    width: 22px;
    height: 22px;
    margin-right: 8px;
  }
  @media (max-width: 500px) {
    #zalo-branch {
      min-width: 150px;
      right: 70px;
    }
  }
</style>

<script>
  document.addEventListener('DOMContentLoaded', () => {
    const fab = document.getElementById('zalo-fab');
    const box = document.getElementById('zalo-branch');
    fab.onclick = e => { box.classList.toggle('show'); e.stopPropagation(); };
    box.onclick = e => e.stopPropagation();
    document.addEventListener('click', () => box.classList.remove('show'));
    fab.onkeydown = e => { if (e.key === 'Enter' || e.key === ' ') fab.click(); };
  });
</script>
</body>
</html>
