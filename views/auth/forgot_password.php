<?php /** @var string $csrf */ /** @var string $recaptcha_site_key */ ?>
<!doctype html>
<html lang="vi">
<head>
  <meta charset="utf-8">
  <title><?= htmlspecialchars($title ?? 'Quên mật khẩu - NamaHealing') ?></title>
  <meta name="description" content="<?= htmlspecialchars($description ?? '') ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta property="og:title" content="Quên mật khẩu - NamaHealing">
  <meta property="og:description" content="Nhập email để nhận mã OTP đặt lại mật khẩu">
  <link rel="preconnect" href="https://www.google.com">
  <script src="https://www.google.com/recaptcha/api.js?render=<?= htmlspecialchars($recaptcha_site_key) ?>"></script>
  <style>
    body{font-family:system-ui,Arial;margin:0;padding:24px;background:#f6f7f9}
    .card{max-width:480px;margin:24px auto;background:#fff;padding:24px;border-radius:16px;box-shadow:0 10px 20px rgba(0,0,0,.06)}
    label{display:block;margin:12px 0 6px}
    input{width:100%;padding:12px;border:1px solid #ddd;border-radius:10px}
    button{margin-top:16px;padding:12px 16px;border:0;background:#111;color:#fff;border-radius:12px;cursor:pointer}
    .muted{color:#666;font-size:14px}
  </style>
</head>
<body>
  <div class="card">
    <h1>Quên mật khẩu</h1>
    <form id="forgotForm" method="post" action="/forgot-password/submit" novalidate>
      <?= \NamaHealing\Helpers\Csrf::input() ?>
      <input type="text" name="website" style="display:none">
      <label>Email</label>
      <input type="email" name="email" required placeholder="you@example.com">
      <input type="hidden" name="g-recaptcha-response" id="g-recaptcha-response">
      <button type="submit">Gửi OTP</button>
      <p class="muted">Chúng tôi sẽ gửi OTP 6 chữ số nếu email hợp lệ.</p>
    </form>
    <p class="muted">Đã có OTP? <a href="/reset-password">Đặt lại mật khẩu</a></p>
  </div>
  <script>
    const siteKey = "<?= htmlspecialchars($recaptcha_site_key) ?>";
    function execRecaptcha(action) {
      if (!siteKey) return Promise.resolve('');
      return grecaptcha.execute(siteKey, {action}).then(token => token);
    }
    document.getElementById('forgotForm').addEventListener('submit', async (e) => {
      e.preventDefault();
      const token = await execRecaptcha('forgot_password');
      document.getElementById('g-recaptcha-response').value = token || '';
      const form = e.target;
      const resp = await fetch(form.action, {method:'POST', body:new FormData(form)});
      const data = await resp.json();
      alert(data.message || 'Đã gửi yêu cầu');
      if (resp.ok) window.location.href = '/reset-password';
    });
  </script>
</body>
</html>
