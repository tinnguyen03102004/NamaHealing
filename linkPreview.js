export async function renderLinkPreview(container, url) {
  if (!url) return;
  container.innerHTML = 'Đang tải preview...';
  container.classList.remove('hidden');
  try {
    const res = await fetch(`fetch_metadata.php?url=${encodeURIComponent(url)}`);
    const data = await res.json();
    if (data.error) throw new Error(data.error);
    container.innerHTML = `
      <a href="${data.url}" target="_blank" rel="noopener" class="block">
        ${data.image ? `<img src="${data.image}" alt="${data.title}" class="w-full h-40 object-cover mb-2">` : ''}
        <p class="font-semibold mb-1">${data.title || ''}</p>
        ${data.description ? `<p class="text-sm mb-1">${data.description}</p>` : ''}
        <span class="text-xs text-gray-500">${data.domain}</span>
      </a>`;
  } catch (e) {
    container.innerHTML = 'Không lấy được thông tin link';
  }
}
