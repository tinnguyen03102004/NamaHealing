<?php
require 'config.php';
$pageTitle = __('chatbot');
include 'header.php';
?>
<main class="flex flex-col items-center min-h-screen px-4 pt-4 pb-20">
  <div class="w-full max-w-lg flex flex-col flex-1">
    <h1 class="text-2xl font-semibold text-center sticky top-0 bg-white z-20 px-4 py-3 shadow">
      <?= __('chatbot_title') ?>
    </h1>
    <div id="chat-log" class="flex-1 overflow-y-auto space-y-3 pb-4 pt-16">
      <div id="greeting" class="text-center text-gray-500 bg-white border border-gray-200 rounded-lg p-4">
        <?= __('chatbot_greeting') ?>
      </div>
    </div>
  </div>
  <form id="chat-form" class="fixed bottom-0 left-1/2 transform -translate-x-1/2 w-full max-w-lg flex gap-2 px-4 py-3 bg-white border-t border-gray-200 shadow-md z-10" style="padding-bottom: calc(0.75rem + env(safe-area-inset-bottom));">
    <input id="chat-input" type="text" class="flex-grow border border-gray-300 rounded-full px-3 py-2 focus:outline-none" placeholder="<?= __('chatbot_placeholder') ?>" />
    <button type="submit" class="px-4 py-2 bg-[#9dcfc3] text-[#285F57] rounded-full hover:bg-[#76a89e]">
      <?= __('chatbot_send') ?>
    </button>
  </form>
</main>
<style>
  .typing { display:flex; gap:4px; }
  .typing span {
    width:6px; height:6px;
    background:#6b7280;
    border-radius:50%;
    animation: bounce 1s infinite;
  }
  .typing span:nth-child(2) { animation-delay: .2s; }
  .typing span:nth-child(3) { animation-delay: .4s; }
  @keyframes bounce { 0%,80%,100%{transform:translateY(0);} 40%{transform:translateY(-6px);} }
</style>
<script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
  const form = document.getElementById('chat-form');
  const input = document.getElementById('chat-input');
  const log   = document.getElementById('chat-log');
  const greeting = document.getElementById('greeting');

  const renderer = new marked.Renderer();
  renderer.link = (href, title, text) =>
    `<a href="${href}" target="_blank" rel="noopener noreferrer">${text}</a>`;
  marked.setOptions({ breaks: true, mangle: false, headerIds: false, renderer });

  input.addEventListener('keydown', e => {
    if (e.key === 'Enter') {
      e.preventDefault();
      form.requestSubmit();
    }
  });

  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    const text = input.value.trim();
    if (!text) return;
    input.value = '';
    if (greeting) greeting.remove();

    const userWrap = document.createElement('div');
    userWrap.className = 'flex justify-end';
    const userBubble = document.createElement('div');
    userBubble.className = 'bg-teal-600 text-white px-4 py-2 rounded-lg max-w-xs break-words whitespace-pre-wrap';
    userBubble.textContent = text;
    userWrap.appendChild(userBubble);
    log.appendChild(userWrap);
    log.scrollTop = log.scrollHeight;

  const indicator = document.createElement('div');
  indicator.className = 'flex justify-start';
  indicator.innerHTML =
    `<div class="bg-gray-200 px-3 py-2 rounded-lg">
       <div class="typing">
         <span></span><span></span><span></span>
       </div>
     </div>`;
    log.appendChild(indicator);
    log.scrollTop = log.scrollHeight;

    try {
      const res = await fetch('chatgptapi.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ message: text })
      });
      indicator.remove();
      if (res.ok) {
        const data = await res.json();
        const reply = data.reply || data.error || 'Error';
        const botMsg = document.createElement('div');
        botMsg.className = 'bg-gray-50 text-gray-800 px-4 py-2 rounded-md w-full whitespace-pre-wrap';
        botMsg.innerHTML = marked.parse(reply);
        log.appendChild(botMsg);
        log.scrollTop = log.scrollHeight;
      } else {
        const errorWrap = document.createElement('div');
        errorWrap.className = 'flex justify-start';
        errorWrap.innerHTML =
          '<div class="bg-gray-200 text-red-600 px-4 py-2 rounded-lg">Error</div>';
        log.appendChild(errorWrap);
        log.scrollTop = log.scrollHeight;
      }
    } catch (err) {
      indicator.remove();
      const errorWrap = document.createElement('div');
      errorWrap.className = 'flex justify-start';
      errorWrap.innerHTML =
        '<div class="bg-gray-200 text-red-600 px-4 py-2 rounded-lg">Error</div>';
      log.appendChild(errorWrap);
      log.scrollTop = log.scrollHeight;
    }
  });
});
</script>
<?php include 'footer.php'; ?>
