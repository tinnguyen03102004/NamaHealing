<?php /** @var string $csrf */ /** @var string $recaptcha_site_key */ ?>
<!doctype html>
<html lang="vi">
<head>
  <meta charset="utf-8">
  <title><?= htmlspecialchars($title ?? 'Đặt lại mật khẩu - NamaHealing') ?></title>
  <meta name="description" content="<?= htmlspecialchars($description ?? '') ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta property="og:title" content="Đặt lại mật khẩu - NamaHealing">
  <meta property="og:description" content="Nhập OTP đã gửi qua email để đặt lại mật khẩu">
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
    <h1>Đặt lại mật khẩu</h1>
    <form id="resetForm" method="post" action="/reset-password/submit" novalidate>
      <?= \NamaHealing\Helpers\Csrf::input() ?>
      <input type="text" name="website" style="display:none">
      <label>Email</label><input type="email" name="email" required>
      <label>OTP (6 số)</label><input type="text" name="otp" inputmode="numeric" pattern="\d{6}" required>
      <label>Mật khẩu mới</label><input type="password" name="password" minlength="8" required>
      <label>Nhập lại mật khẩu mới</label><input type="password" name="password_confirm" minlength="8" required>
      <input type="hidden" name="g-recaptcha-response" id="g-recaptcha-response">
      <button type="submit">Đặt lại mật khẩu</button>
    </form>
  </div>
  <script>
    const siteKey = "<?= htmlspecialchars($recaptcha_site_key) ?>";
    function execRecaptcha(action) {
      if (!siteKey) return Promise.resolve('');
      return grecaptcha.execute(siteKey, {action}).then(token => token);
    }
    document.getElementById('resetForm').addEventListener('submit', async (e) => {
      e.preventDefault();
      const token = await execRecaptcha('reset_password');
      document.getElementById('g-recaptcha-response').value = token || '';
      const form = e.target;
      const resp = await fetch(form.action, {method:'POST', body:new FormData(form)});
      const data = await resp.json();
      alert(data.message || 'Đã xử lý');
      if (resp.ok) window.location.href = '/login.php';
    });
  </script>
</body>
</html>
