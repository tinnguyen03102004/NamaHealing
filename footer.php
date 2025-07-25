<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/i18n.php';
$isChatbotPage = basename($_SERVER['PHP_SELF']) === 'chatbot.php';
?>
<footer class="site-footer text-center w-full">
  <?= sprintf(__('footer_message'), date('Y')) ?>
</footer>


<div id="zalo-fab" class="zalo-fab" title="Zalo tư vấn" tabindex="0" aria-label="Zalo tư vấn"></div>
<div id="zalo-branch" class="zalo-branch" tabindex="0">
  <a href="https://zalo.me/0839269501" target="_blank" rel="noopener">
    <img src="logo-zalo.png" alt="Zalo"> Mr. Hữu Tín - 0839 269 501
  </a>
  <a href="https://zalo.me/0989399278" target="_blank" rel="noopener">
    <img src="logo-zalo.png" alt="Zalo"> Ms. Mai Hoàn - 0989 399 278
  </a>
</div>

<style>
  .zalo-fab {
    position: fixed;
    right: 24px;
    bottom: 95px;
    z-index: 9999;
    width: 58px;
    height: 58px;
    border-radius: 50%;
    background: url('logo-zalo.png') center/34px 34px no-repeat;
    box-shadow: 0 2px 8px rgba(0,0,0,.18);
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: box-shadow .2s;
  }
  .zalo-fab:hover { box-shadow: 0 4px 16px rgba(0,0,0,.22); }
  .zalo-branch {
    display: none;
    position: fixed;
    right: 94px;
    bottom: 95px;
    z-index: 10000;
    background: #fff;
    border-radius: 14px;
    box-shadow: 0 2px 12px rgba(0,0,0,.14);
    padding: 12px 20px;
    min-width: 200px;
  }
  .zalo-branch.show { display: block; }
  .zalo-branch a {
    display: flex;
    align-items: center;
    padding: 6px 0;
    font-size: 16px;
    color: #02aaff;
    text-decoration: none;
    border-bottom: 1px solid #e9ecef;
  }
  .zalo-branch a:last-child { border-bottom: none; }
  .zalo-branch img { width: 22px; height: 22px; margin-right: 8px; }
  @media (max-width: 500px) {
    .zalo-branch { min-width: 150px; right: 70px; }
  }
<?php if ($isChatbotPage): ?>
  @media (max-width: 640px) {
    #zalo-fab,
    #zalo-branch { display: none !important; }
  }
<?php endif; ?>
</style>
<script>
function initFooter() {
  const zaloFab = document.getElementById('zalo-fab');
  const zaloBox = document.getElementById('zalo-branch');
  if (!zaloFab || !zaloBox) return;
  zaloFab.addEventListener('click', e => {
    zaloBox.classList.toggle('show');
    e.stopPropagation();
  });
  zaloFab.addEventListener('keydown', e => { if (e.key === 'Enter' || e.key === ' ') zaloFab.click(); });
  zaloBox.addEventListener('click', e => e.stopPropagation());
  document.addEventListener('click', () => zaloBox.classList.remove('show'));
}
document.addEventListener('DOMContentLoaded', initFooter);
</script>
</body>
</html>
