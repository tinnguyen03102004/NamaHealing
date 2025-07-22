<?php if (session_status() === PHP_SESSION_NONE) session_start(); ?>
<?php require_once __DIR__ . '/i18n.php'; ?>
<footer class="site-footer text-center w-full">
  <?= sprintf(__('footer_message'), date('Y')) ?>
</footer>

<div id="chatbot-fab" class="chatbot-fab" title="<?= __('chatbot') ?>" tabindex="0" aria-label="<?= __('chatbot') ?>">🤖</div>
<div id="chatbot-popup" class="chatbot-popup" aria-hidden="true">
  <div class="chat-header flex items-center justify-between bg-[#9dcfc3] text-[#285F57] px-4 py-2">
    <div class="flex items-center gap-2">
      <img src="logoNama.png" alt="Bot" class="w-8 h-8 rounded-full">
      <span class="font-semibold">NamaHealing Bot</span>
    </div>
    <button id="chatbot-close" aria-label="close">&times;</button>
  </div>
  <div id="chatbot-box" class="flex-1 overflow-y-auto p-3 space-y-2 bg-white"></div>
  <div class="chat-footer flex gap-2 p-2 bg-gray-50">
    <input id="chatbot-input" type="text" class="flex-grow border border-gray-300 rounded-full px-3 py-2 focus:outline-none" placeholder="<?= __('chatbot_placeholder') ?>">
    <button id="chatbot-send" class="px-4 py-2 bg-[#9dcfc3] text-[#285F57] rounded-full hover:bg-[#76a89e]">
      <?= __('chatbot_send') ?>
    </button>
  </div>
</div>

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
  .chatbot-fab {
    position: fixed;
    right: 24px;
    bottom: 24px;
    z-index: 9999;
    width: 58px;
    height: 58px;
    border-radius: 50%;
    background: #9dcfc3;
    color: #285F57;
    font-size: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 2px 8px rgba(0,0,0,.18);
    cursor: pointer;
    transition: box-shadow .2s;
  }
  .chatbot-fab:hover { box-shadow: 0 4px 16px rgba(0,0,0,.22); }
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
  .chatbot-popup {
    position: fixed;
    right: 24px;
    bottom: 90px;
    z-index: 10000;
    width: calc(100% - 40px);
    max-width: 370px;
    height: 60vh;
    background: #fff;
    border-radius: 18px;
    box-shadow: 0 4px 16px rgba(0,0,0,.2);
    display: none;
    flex-direction: column;
    overflow: hidden;
    opacity: 0;
    transform: translateY(20px);
    transition: transform .3s ease, opacity .3s ease;
  }
  .chatbot-popup.show { display: flex; opacity: 1; transform: translateY(0); }
</style>
<script>
function initChatbot() {
  const $ = id => document.getElementById(id);
  const fab = $('chatbot-fab');
  const popup = $('chatbot-popup');
  const close = $('chatbot-close');
  const input = $('chatbot-input');
  const sendBtn = $('chatbot-send');
  const box = $('chatbot-box');
  const zaloFab = $('zalo-fab');
  const zaloBox = $('zalo-branch');

  function append(role, text) {
    const div = document.createElement('div');
    div.className = role === 'user' ? 'text-right' : 'text-left';
    const span = document.createElement('span');
    span.className = 'inline-block px-3 py-2 rounded-lg ' + (role === 'user' ? 'bg-teal-100 text-gray-700' : 'bg-gray-100 text-gray-700');
    span.textContent = text;
    div.appendChild(span);
    box.appendChild(div);
    box.scrollTop = box.scrollHeight;
  }

  async function sendMessage() {
    const msg = input.value.trim();
    if (!msg) return;
    append('user', msg);
    input.value = '';
    const res = await fetch('chatgptapi.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ message: msg })
    });
    if (res.ok) {
      const data = await res.json();
      if (data.reply) append('assistant', data.reply);
      else if (data.error) append('assistant', data.error);
    } else {
      append('assistant', 'Error');
    }
  }

  fab.addEventListener('click', () => { popup.classList.toggle('show'); if (popup.classList.contains('show')) input.focus(); });
  fab.addEventListener('keydown', e => { if (e.key === 'Enter' || e.key === ' ') fab.click(); });
  close.addEventListener('click', () => popup.classList.remove('show'));
  document.addEventListener('keydown', e => { if (e.key === "Escape") popup.classList.remove("show"); });
  sendBtn.addEventListener('click', sendMessage);
  input.addEventListener('keydown', e => { if (e.key === 'Enter') sendMessage(); });

  zaloFab.addEventListener('click', e => { zaloBox.classList.toggle('show'); e.stopPropagation(); });
  zaloFab.addEventListener('keydown', e => { if (e.key === 'Enter' || e.key === ' ') zaloFab.click(); });
  zaloBox.addEventListener('click', e => e.stopPropagation());
  document.addEventListener('click', () => zaloBox.classList.remove('show'));
}
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', initChatbot);
} else {
  initChatbot();
}
</script>
</body>
</html>
