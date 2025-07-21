<?php
require 'config.php';
$pageTitle = __('chatbot');
include 'header.php';
?>
<main class="max-w-2xl mx-auto px-4 py-8">
  <h1 class="text-2xl font-semibold mb-4"><?= __('chatbot') ?></h1>
  <div id="chat-box" class="bg-white border border-gray-300 rounded-lg p-4 h-96 overflow-y-auto space-y-2"></div>
  <div class="mt-4 flex">
    <input id="chat-input" type="text" class="flex-grow border border-gray-300 rounded-l-lg p-2 focus:outline-none" placeholder="<?= __('chatbot_placeholder') ?>" />
    <button id="chat-send" class="px-4 py-2 bg-[#9dcfc3] text-[#285F57] rounded-r-lg hover:bg-[#76a89e]">
      <?= __('chatbot_send') ?>
    </button>
  </div>
</main>
<script>
function appendMessage(role, text) {
  const box = document.getElementById('chat-box');
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
  const input = document.getElementById('chat-input');
  const msg = input.value.trim();
  if (!msg) return;
  appendMessage('user', msg);
  input.value = '';
  const res = await fetch('chatbot_api.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ message: msg })
  });
  if (res.ok) {
    const data = await res.json();
    if (data.reply) {
      appendMessage('assistant', data.reply);
    } else if (data.error) {
      appendMessage('assistant', data.error);
    }
  } else {
    appendMessage('assistant', 'Error');
  }
}
document.getElementById('chat-send').addEventListener('click', sendMessage);
document.getElementById('chat-input').addEventListener('keydown', e => {
  if (e.key === 'Enter') sendMessage();
});
</script>
<?php include 'footer.php'; ?>
