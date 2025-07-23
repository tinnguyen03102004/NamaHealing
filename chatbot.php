<?php
require 'config.php';
$pageTitle = __('chatbot');
include 'header.php';
?>
<main class="max-w-2xl mx-auto px-4 py-8">
  <h1 class="text-2xl font-semibold mb-4"><?= __('chatbot') ?></h1>
  <div id="chat-log" class="bg-white border border-gray-300 rounded-lg p-4 h-96 overflow-y-auto space-y-2"></div>
  <form id="chat-form" class="mt-4 flex">
    <input id="chat-input" type="text" class="flex-grow border border-gray-300 rounded-l-lg p-2 focus:outline-none" placeholder="<?= __('chatbot_placeholder') ?>" />
    <button type="submit" class="px-4 py-2 bg-[#9dcfc3] text-[#285F57] rounded-r-lg hover:bg-[#76a89e]">
      <?= __('chatbot_send') ?>
    </button>
  </form>
</main>
<script>
document.addEventListener('DOMContentLoaded', () => {
  const form = document.getElementById('chat-form');
  const input = document.getElementById('chat-input');
  const log   = document.getElementById('chat-log');

  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    const text = input.value.trim();
    if (!text) return;
    input.value = '';

    const userWrap = document.createElement('div');
    userWrap.className = 'flex justify-end mb-2';
    const userBubble = document.createElement('div');
    userBubble.className = 'bg-blue-500 text-white px-4 py-2 rounded-lg max-w-xs break-words';
    userBubble.textContent = text;
    userWrap.appendChild(userBubble);
    log.appendChild(userWrap);

    const indicator = document.createElement('div');
    indicator.className = 'flex justify-start mb-2';
    indicator.innerHTML =
      `<div class="bg-gray-200 px-3 py-2 rounded-lg flex space-x-1">
         <span class="h-2 w-2 bg-gray-500 rounded-full animate-[wave_1.2s_infinite]"></span>
         <span class="h-2 w-2 bg-gray-500 rounded-full animate-[wave_1.2s_infinite] delay-200"></span>
         <span class="h-2 w-2 bg-gray-500 rounded-full animate-[wave_1.2s_infinite] delay-400"></span>
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
        const botWrap = document.createElement('div');
        botWrap.className = 'flex justify-start mb-2';
        const botBubble = document.createElement('div');
        botBubble.className = 'bg-gray-200 px-4 py-2 rounded-lg max-w-xs break-words';
        botBubble.textContent = reply;
        botWrap.appendChild(botBubble);
        log.appendChild(botWrap);
        log.scrollTop = log.scrollHeight;
      } else {
        const errorWrap = document.createElement('div');
        errorWrap.className = 'flex justify-start mb-2';
        errorWrap.innerHTML =
          '<div class="bg-gray-200 text-red-600 px-4 py-2 rounded-lg">Error</div>';
        log.appendChild(errorWrap);
        log.scrollTop = log.scrollHeight;
      }
    } catch (err) {
      indicator.remove();
      const errorWrap = document.createElement('div');
      errorWrap.className = 'flex justify-start mb-2';
      errorWrap.innerHTML =
        '<div class="bg-gray-200 text-red-600 px-4 py-2 rounded-lg">Error</div>';
      log.appendChild(errorWrap);
      log.scrollTop = log.scrollHeight;
    }
  });
});
</script>
<?php include 'footer.php'; ?>
