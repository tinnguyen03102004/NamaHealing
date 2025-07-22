<?php if (session_status() === PHP_SESSION_NONE) session_start(); ?>
<?php require_once __DIR__ . '/i18n.php'; ?>
<footer class="site-footer text-center w-full">
  <?= sprintf(__('footer_message'), date('Y')) ?>
</footer>

<!-- Chatbot FAB đặt ngay trên nút Zalo -->
<div id="chatbot-fab" title="<?= __('chatbot') ?>" tabindex="0" aria-label="<?= __('chatbot') ?>">🤖</div>
<!-- Chat popup -->
<div id="chatbot-popup" aria-hidden="true">
  <div class="chat-header flex items-center justify-between bg-[#9dcfc3] text-[#285F57] px-4 py-2">
    <div class="flex items-center gap-2">
      <img src="logoNama.png" alt="Bot" class="w-8 h-8 rounded-full">
      <span class="font-semibold">Nama Bot</span>
    </div>
    <button id="chatbot-close" class="text-2xl leading-none">&times;</button>
  </div>
  <div id="chatbot-box" class="flex-1 overflow-y-auto p-3 space-y-2 bg-white"></div>
  <div class="chat-footer flex gap-2 p-2 bg-gray-50">
    <input id="chatbot-input" type="text" class="flex-grow border border-gray-300 rounded-full px-3 py-2 focus:outline-none" placeholder="<?= __('chatbot_placeholder') ?>">
    <button id="chatbot-send" class="px-4 py-2 bg-[#9dcfc3] text-[#285F57] rounded-full hover:bg-[#76a89e]">
      <?= __('chatbot_send') ?>
    </button>
  </div>
</div>
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
  #chatbot-fab {
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
  #chatbot-fab:hover {
    box-shadow: 0 4px 16px rgba(0,0,0,.22);
  }
  #zalo-fab {
    position: fixed;
    right: 24px;
    bottom: 95px;
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
    right: 94px;
    bottom: 95px;
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
  #chatbot-popup {
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
  #chatbot-popup.show {
    display: flex;
    opacity: 1;
    transform: translateY(0);
  }
</style>

<script>
  document.addEventListener('DOMContentLoaded', () => {
    const chatFab = document.getElementById('chatbot-fab');
    const popup = document.getElementById('chatbot-popup');
    const closeBtn = document.getElementById('chatbot-close');
    const input = document.getElementById('chatbot-input');
    const sendBtn = document.getElementById('chatbot-send');
    const chatBox = document.getElementById('chatbot-box');

    function appendMessage(role, text) {
      const div = document.createElement('div');
      div.className = role === 'user' ? 'text-right' : 'text-left';
      const span = document.createElement('span');
      span.className = 'inline-block px-3 py-2 rounded-lg ' + (role === 'user' ? 'bg-teal-100 text-gray-700' : 'bg-gray-100 text-gray-700');
      span.textContent = text;
      div.appendChild(span);
      chatBox.appendChild(div);
      chatBox.scrollTop = chatBox.scrollHeight;
    }

    async function sendMessage() {
      const msg = input.value.trim();
      if (!msg) return;
      appendMessage('user', msg);
      input.value = '';
      const res = await fetch('/chatgptapi.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ message: msg })
      });
      if (res.ok) {
        const data = await res.json();
        if (data.reply) appendMessage('assistant', data.reply);
      } else {
        appendMessage('assistant', 'Error');
      }
    }

    if (chatFab) {
      chatFab.onclick = () => {
        popup.classList.toggle('show');
        if (popup.classList.contains('show')) {
          input.focus();
        }
      };
      chatFab.onkeydown = e => { if (e.key === 'Enter' || e.key === ' ') chatFab.click(); };
    }
    closeBtn.onclick = () => popup.classList.remove('show');
    sendBtn.onclick = sendMessage;
    input.addEventListener('keydown', e => { if (e.key === 'Enter') sendMessage(); });
    const fab = document.getElementById('zalo-fab');
    const zaloBox = document.getElementById('zalo-branch');
    fab.onclick = e => { zaloBox.classList.toggle('show'); e.stopPropagation(); };
    zaloBox.onclick = e => e.stopPropagation();
    document.addEventListener('click', () => zaloBox.classList.remove('show'));
    fab.onkeydown = e => { if (e.key === 'Enter' || e.key === ' ') fab.click(); };
  });
</script>
</body>
</html>
