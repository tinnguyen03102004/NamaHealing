function fetchLinkPreview(url) {
  return fetch('fetch_metadata.php?url=' + encodeURIComponent(url))
    .then(res => {
      if (!res.ok) throw new Error('Failed');
      return res.json();
    });
}

function createLinkCard(data) {
  const a = document.createElement('a');
  a.href = data.url;
  a.target = '_blank';
  a.rel = 'noopener';
  a.className = 'link-card';

  const img = document.createElement('img');
  img.src = data.image;
  a.appendChild(img);

  const body = document.createElement('div');
  body.className = 'body';

  const title = document.createElement('div');
  title.className = 'title';
  title.textContent = data.title || data.domain;
  body.appendChild(title);

  if (data.description) {
    const desc = document.createElement('div');
    desc.className = 'desc';
    desc.textContent = data.description;
    body.appendChild(desc);
  }

  const domain = document.createElement('span');
  domain.className = 'domain';
  domain.textContent = data.domain;
  body.appendChild(domain);

  a.appendChild(body);
  return a;
}

function initLinkPreviews() {
  document.querySelectorAll('[data-link-preview]').forEach(el => {
    const url = el.dataset.linkPreview;
    if (!url) return;
    el.innerHTML = '<div class="skeleton"></div>';
    fetchLinkPreview(url)
      .then(data => {
        el.innerHTML = '';
        el.appendChild(createLinkCard(data));
      })
      .catch(() => {
        el.textContent = url;
      });
  });
}

document.addEventListener('DOMContentLoaded', initLinkPreviews);

