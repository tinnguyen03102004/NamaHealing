<?php
$heroImage = 'https://images.unsplash.com/photo-1536514498073-50e69d39c6cf?q=80&w=2000&auto=format&fit=crop';
$currentUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' .
    ($_SERVER['HTTP_HOST'] ?? '') . ($_SERVER['REQUEST_URI'] ?? '');
require 'config.php';
$pageTitle = __('chatbot_meta_title');
$metaOgTitle = __('chatbot_meta_title');
$metaDescription = __('chatbot_meta_description');
$metaImage = $heroImage;
$metaUrl = $currentUrl;
$metaOgType = 'website';
include 'header.php';
?>
<main class="flex flex-col items-center min-h-screen px-4 pt-4 pb-24">
  <div class="w-full max-w-3xl flex flex-col flex-1">
    <h1 class="chatbot-title chatbot-title-wrapper sticky top-20 sm:top-16 bg-transparent z-20 px-4 py-6">
      <span class="chatbot-title-inner">
        <?= __('chatbot_title') ?>
      </span>
    </h1>
    <div id="chat-log" class="flex-1 overflow-y-auto space-y-4 pb-6 pt-20">
      <div id="greeting" class="text-center text-gray-500 bg-white border border-gray-200 rounded-lg p-4">
        <?= __('chatbot_greeting') ?>
      </div>
    </div>
  </div>
  <form id="chat-form" class="fixed bottom-0 left-1/2 transform -translate-x-1/2 w-full max-w-3xl flex gap-2 px-4 py-3 bg-white border-t border-gray-200 shadow-md z-10" style="padding-bottom: calc(0.75rem + env(safe-area-inset-bottom));">
    <input id="chat-input" type="text" class="flex-grow border border-gray-300 rounded-full px-3 py-3 focus:outline-none focus:ring-2 focus:ring-[#9dcfc3]" placeholder="<?= __('chatbot_placeholder') ?>" autocomplete="off" />
    <button type="submit" class="px-4 py-2 bg-[#9dcfc3] text-[#285F57] rounded-full hover:bg-[#76a89e] transition">
      <?= __('chatbot_send') ?>
    </button>
  </form>
</main>
<style>
  .chatbot-title {
    font-family: 'Cormorant Garamond', 'Times New Roman', serif;
    font-size: clamp(1.25rem, 3vw, 1.75rem);
    font-weight: 500;
    letter-spacing: 0.05em;
    color: #285F57;
  }
  .chatbot-title-wrapper {
    display: flex;
    justify-content: center;
    width: 100%;
  }
  .chatbot-title-inner {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0.5rem 1.75rem;
    border-radius: 9999px;
    border: 2px solid #9dcfc3;
    background: linear-gradient(135deg, rgba(157, 207, 195, 0.18), rgba(255, 255, 255, 0.95));
    box-shadow: 0 15px 35px rgba(40, 95, 87, 0.18);
    position: relative;
    overflow: hidden;
    white-space: nowrap;
  }
  .chatbot-title-inner::before {
    content: '';
    position: absolute;
    inset: 2px;
    border-radius: inherit;
    border: 1px solid rgba(255, 255, 255, 0.6);
    opacity: 0.9;
    pointer-events: none;
  }
  .chatbot-title-inner::after {
    content: '';
    position: absolute;
    top: -40%;
    left: -10%;
    width: 80%;
    height: 180%;
    background: radial-gradient(circle at top, rgba(255, 255, 255, 0.5), transparent 70%);
    transform: rotate(12deg);
    pointer-events: none;
  }
  .chat-row {
    width: 100%;
  }
  .message-bubble {
    position: relative;
    transition: box-shadow 0.2s ease, transform 0.2s ease;
  }
  .message-bubble-user {
    background: linear-gradient(135deg, #367a72, #285F57);
  }
  .message-bubble-user:hover {
    box-shadow: 0 12px 24px rgba(40, 95, 87, 0.25);
    transform: translateY(-1px);
  }
  .message-bubble-bot {
    background: rgba(255, 255, 255, 0.9);
    backdrop-filter: blur(4px);
  }
  .message-bubble-bot:hover {
    box-shadow: 0 12px 24px rgba(40, 95, 87, 0.08);
    transform: translateY(-1px);
  }
  .bot-message p {
    margin: 0.25rem 0;
  }
  .bot-message ul,
  .bot-message ol {
    margin: 0.5rem 0;
    padding-left: 1.25rem;
  }
  .bot-message a {
    color: #2563eb;
    text-decoration: underline;
    word-break: break-word;
  }
  .message-copy-btn {
    position: absolute;
    top: 0.5rem;
    right: 0.5rem;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 1.75rem;
    height: 1.75rem;
    border-radius: 9999px;
    background: rgba(255, 255, 255, 0.9);
    color: #285F57;
    border: none;
    cursor: pointer;
    opacity: 0;
    pointer-events: none;
    transition: opacity 0.2s ease, transform 0.2s ease;
  }
  .message-bubble:hover .message-copy-btn,
  .message-bubble:focus-within .message-copy-btn,
  .message-bubble:focus .message-copy-btn {
    opacity: 1;
    pointer-events: auto;
    transform: translateY(0);
  }
  .message-copy-btn svg {
    width: 1rem;
    height: 1rem;
  }
  .message-copy-btn:focus-visible {
    outline: 2px solid #9dcfc3;
    outline-offset: 2px;
  }
  .message-copy-btn.copied {
    background: #9dcfc3;
    color: #285F57;
  }
  @media (pointer: coarse) {
    .message-copy-btn {
      opacity: 1;
      pointer-events: auto;
    }
  }
  .link-preview-card {
    display: flex;
    width: 100%;
    overflow: hidden;
    border-radius: 1rem;
    border: 1px solid rgba(156, 163, 175, 0.3);
    background: rgba(255, 255, 255, 0.85);
    text-decoration: none;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    color: inherit;
  }
  .link-preview-card:hover {
    transform: translateY(-1px);
    box-shadow: 0 12px 24px rgba(40, 95, 87, 0.12);
  }
  .link-preview-thumb {
    width: 96px;
    flex-shrink: 0;
    background-size: cover;
    background-position: center;
  }
  @media (min-width: 640px) {
    .link-preview-thumb {
      width: 128px;
    }
  }
  .link-preview-body {
    padding: 0.9rem 1rem;
    display: flex;
    flex-direction: column;
    gap: 0.35rem;
    min-width: 0;
  }
  .link-preview-title {
    font-weight: 600;
    color: #1f2937;
    line-height: 1.35;
    word-break: break-word;
  }
  .link-preview-description {
    font-size: 0.85rem;
    color: #4b5563;
    line-height: 1.4;
    word-break: break-word;
  }
  .link-preview-meta {
    font-size: 0.75rem;
    color: #6b7280;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 0.5rem;
  }
  .link-preview-cta {
    font-weight: 600;
    color: #285F57;
  }
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
<script src="https://cdn.jsdelivr.net/npm/dompurify@3.0.6/dist/purify.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/emoji-toolkit@6.7.0/lib/js/emoji-toolkit.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
  const form = document.getElementById('chat-form');
  const input = document.getElementById('chat-input');
  const log   = document.getElementById('chat-log');
  const greeting = document.getElementById('greeting');
  const copyLabel = <?= json_encode(__('chatbot_copy')) ?>;
  const copiedLabel = <?= json_encode(__('chatbot_copied')) ?>;
  const previewStrings = {
    loading: <?= json_encode(__('chatbot_preview_loading')) ?>,
    open: <?= json_encode(__('chatbot_preview_open')) ?>,
    unavailable: <?= json_encode(__('chatbot_preview_unavailable')) ?>
  };

  const renderer = new marked.Renderer();
  renderer.link = (href, title, text) =>
    `<a href="${href}" target="_blank" rel="noopener noreferrer">${text}</a>`;
  marked.setOptions({
    gfm: true,
    breaks: true,
    smartLists: true,
    mangle: false,
    headerIds: false,
    renderer
  });

  const emojiToolkit = window.emojiToolkit;
  const convertEmojiShortcodes = (text) => {
    if (!text) return '';
    if (emojiToolkit && typeof emojiToolkit.shortnameToUnicode === 'function') {
      return emojiToolkit.shortnameToUnicode(text);
    }
    return text;
  };

  const domPurifyConfig = (() => {
    if (window.DOMPurify && typeof window.DOMPurify.getDefaultConfig === 'function') {
      const defaultConfig = window.DOMPurify.getDefaultConfig();
      const allowedTags = new Set([...(defaultConfig.ALLOWED_TAGS || []), 'table', 'thead', 'tbody', 'tr', 'th', 'td', 'input']);
      const allowedAttrs = new Set([...(defaultConfig.ALLOWED_ATTR || []), 'align', 'colspan', 'rowspan', 'type', 'checked', 'disabled']);
      return {
        ...defaultConfig,
        ALLOWED_TAGS: Array.from(allowedTags),
        ALLOWED_ATTR: Array.from(allowedAttrs)
      };
    }
    return {
      ALLOWED_TAGS: ['a', 'abbr', 'b', 'blockquote', 'br', 'code', 'dd', 'del', 'div', 'dl', 'dt', 'em', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'hr', 'i', 'img', 'input', 'kbd', 'li', 'mark', 'ol', 'p', 'pre', 's', 'span', 'strong', 'sub', 'sup', 'table', 'tbody', 'td', 'th', 'thead', 'tr', 'u', 'ul'],
      ALLOWED_ATTR: ['href', 'title', 'target', 'rel', 'class', 'id', 'src', 'alt', 'align', 'colspan', 'rowspan', 'type', 'checked', 'disabled']
    };
  })();

  const renderMarkdown = (markdown) => {
    const withEmoji = convertEmojiShortcodes(markdown);
    const html = marked.parse(withEmoji);
    if (window.DOMPurify && typeof window.DOMPurify.sanitize === 'function') {
      return window.DOMPurify.sanitize(html, domPurifyConfig);
    }
    return html;
  };

  const previewCache = new Map();

  const scrollToBottom = (instant = false) => {
    requestAnimationFrame(() => {
      if (instant) {
        log.scrollTop = log.scrollHeight;
      } else {
        log.scrollTo({ top: log.scrollHeight, behavior: 'smooth' });
      }
    });
  };

  const copyToClipboard = async (text) => {
    try {
      if (navigator.clipboard && navigator.clipboard.writeText) {
        await navigator.clipboard.writeText(text);
        return true;
      }
      const textarea = document.createElement('textarea');
      textarea.value = text;
      textarea.setAttribute('readonly', '');
      textarea.style.position = 'absolute';
      textarea.style.left = '-9999px';
      document.body.appendChild(textarea);
      textarea.select();
      const success = document.execCommand('copy');
      document.body.removeChild(textarea);
      return success;
    } catch (error) {
      console.error('Copy failed', error);
      return false;
    }
  };

  const createCopyButton = (getText) => {
    const button = document.createElement('button');
    button.type = 'button';
    button.className = 'message-copy-btn';
    button.setAttribute('aria-label', copyLabel);
    const renderIcon = (type = 'copy') => {
      if (type === 'check') {
        return '<svg viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M16.704 5.29a1 1 0 0 1 .006 1.414l-7.2 7.273a1 1 0 0 1-1.437.015L3.29 9.2a1 1 0 1 1 1.42-1.407l4.064 4.101 6.483-6.553a1 1 0 0 1 1.447-.05"/></svg>';
      }
      return '<svg viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M4 4a2 2 0 0 1 2-2h5.5A2.5 2.5 0 0 1 14 4.5V6h1a2 2 0 0 1 2 2v7a2 2 0 0 1-2 2H9.5A2.5 2.5 0 0 1 7 14.5V13H6a2 2 0 0 1-2-2V4Zm3 9v1.5a.5.5 0 0 0 .5.5H15a1 1 0 0 0 1-1V8a1 1 0 0 0-1-1h-1v4a2 2 0 0 1-2 2H7Zm6-8V4.5a.5.5 0 0 0-.5-.5H6a1 1 0 0 0-1 1v6a1 1 0 0 0 1 1h5.5a.5.5 0 0 0 .5-.5V5Z"/></svg>';
    };
    button.innerHTML = renderIcon();
    let resetTimer;
    button.addEventListener('click', async (event) => {
      event.stopPropagation();
      const text = getText();
      if (!text) return;
      const success = await copyToClipboard(text);
      if (!success) return;
      button.classList.add('copied');
      button.setAttribute('aria-label', copiedLabel);
      button.innerHTML = renderIcon('check');
      clearTimeout(resetTimer);
      resetTimer = setTimeout(() => {
        button.classList.remove('copied');
        button.setAttribute('aria-label', copyLabel);
        button.innerHTML = renderIcon();
      }, 2000);
    });
    return button;
  };

  const createMessageRow = (role, raw) => {
    const row = document.createElement('div');
    row.className = `chat-row flex flex-col gap-2 ${role === 'user' ? 'items-end' : 'items-start'}`;
    const bubble = document.createElement('div');
    if (role === 'user') {
      bubble.className = 'message-bubble message-bubble-user text-white px-4 py-2 rounded-2xl shadow-md max-w-[85%] sm:max-w-[65%] md:max-w-[55%] whitespace-pre-wrap break-words pr-12';
      bubble.textContent = convertEmojiShortcodes(raw);
    } else {
      bubble.className = 'message-bubble message-bubble-bot text-gray-800 px-4 py-3 rounded-2xl shadow-sm max-w-[95%] sm:max-w-[75%] md:max-w-[70%] whitespace-pre-wrap leading-relaxed pr-12 bot-message';
      bubble.innerHTML = renderMarkdown(raw);
    }
    const copyButton = createCopyButton(() => convertEmojiShortcodes(raw));
    bubble.appendChild(copyButton);
    row.appendChild(bubble);
    return { row, bubble };
  };

  const fetchPreview = (url) => {
    if (!previewCache.has(url)) {
      const request = fetch('fetch_meta.php?url=' + encodeURIComponent(url))
        .then((res) => res.ok ? res.json() : {})
        .catch(() => ({}));
      previewCache.set(url, request);
    }
    return previewCache.get(url);
  };

  const attachPreview = (url, row) => {
    const previewCard = document.createElement('a');
    previewCard.className = 'link-preview-card';
    previewCard.href = url;
    previewCard.target = '_blank';
    previewCard.rel = 'noopener noreferrer';

    const body = document.createElement('div');
    body.className = 'link-preview-body';
    const loading = document.createElement('span');
    loading.className = 'text-sm text-gray-500';
    loading.textContent = previewStrings.loading;
    body.appendChild(loading);
    previewCard.appendChild(body);
    row.appendChild(previewCard);

    fetchPreview(url).then((data) => {
      body.innerHTML = '';
      const hostname = (() => {
        try {
          return new URL(url).hostname.replace(/^www\./, '');
        } catch (_) {
          return url;
        }
      })();

      if (!data || (!data.title && !data.description && !data.image)) {
        const fallback = document.createElement('div');
        fallback.className = 'link-preview-description';
        fallback.textContent = previewStrings.unavailable;
        body.appendChild(fallback);
        const meta = document.createElement('div');
        meta.className = 'link-preview-meta';
        const urlSpan = document.createElement('span');
        urlSpan.textContent = hostname;
        meta.appendChild(urlSpan);
        body.appendChild(meta);
        return;
      }

      if (data.image) {
        const thumb = document.createElement('div');
        thumb.className = 'link-preview-thumb';
        try {
          const thumbUrl = String(data.image);
          thumb.style.backgroundImage = `url("${thumbUrl.replace(/"/g, '\"')}")`;
        } catch (error) {
          thumb.style.backgroundImage = 'none';
        }
        previewCard.insertBefore(thumb, body);
      }

      const title = document.createElement('div');
      title.className = 'link-preview-title';
      title.textContent = data.title || hostname;
      body.appendChild(title);

      if (data.description) {
        const description = document.createElement('div');
        description.className = 'link-preview-description';
        description.textContent = data.description;
        body.appendChild(description);
      }

      const meta = document.createElement('div');
      meta.className = 'link-preview-meta';
      const urlSpan = document.createElement('span');
      urlSpan.textContent = hostname;
      meta.appendChild(urlSpan);
      const cta = document.createElement('span');
      cta.className = 'link-preview-cta';
      cta.textContent = previewStrings.open;
      meta.appendChild(cta);
      body.appendChild(meta);
    }).catch(() => {
      body.innerHTML = '';
      const errorText = document.createElement('div');
      errorText.className = 'link-preview-description';
      errorText.textContent = previewStrings.unavailable;
      body.appendChild(errorText);
    }).finally(() => {
      scrollToBottom();
    });
  };

  const enrichLinks = (bubble, row) => {
    const links = Array.from(bubble.querySelectorAll('a[href]'));
    const handled = new Set();
    links.forEach((link) => {
      const url = link.href;
      if (!/^https?:/i.test(url) || handled.has(url)) {
        return;
      }
      handled.add(url);
      attachPreview(url, row);
    });
  };

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

    const { row: userRow } = createMessageRow('user', text);
    log.appendChild(userRow);
    scrollToBottom();

    const indicator = document.createElement('div');
    indicator.className = 'chat-row flex flex-col items-start';
    indicator.innerHTML = `
      <div class="message-bubble message-bubble-bot text-gray-600 bg-gray-100 px-4 py-3 rounded-2xl shadow-sm">
        <div class="typing">
          <span></span><span></span><span></span>
        </div>
      </div>`;
    log.appendChild(indicator);
    scrollToBottom();

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
        const { row: botRow, bubble } = createMessageRow('bot', reply);
        log.appendChild(botRow);
        enrichLinks(bubble, botRow);
        scrollToBottom();
      } else {
        const errorRow = document.createElement('div');
        errorRow.className = 'chat-row flex flex-col items-start';
        errorRow.innerHTML = '<div class="message-bubble message-bubble-bot text-red-600 bg-red-50 px-4 py-2 rounded-2xl border border-red-100">Error</div>';
        log.appendChild(errorRow);
        scrollToBottom();
      }
    } catch (err) {
      indicator.remove();
      const errorRow = document.createElement('div');
      errorRow.className = 'chat-row flex flex-col items-start';
      errorRow.innerHTML = '<div class="message-bubble message-bubble-bot text-red-600 bg-red-50 px-4 py-2 rounded-2xl border border-red-100">Error</div>';
      log.appendChild(errorRow);
      scrollToBottom();
    }
  });

  scrollToBottom(true);
});
</script>
<?php include 'footer.php'; ?>
