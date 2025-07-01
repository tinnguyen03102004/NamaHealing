<?php
require 'config.php';
$err = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'] ?? '';
    $pass = $_POST['password'] ?? '';
    $stmt = $db->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $u = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($u && hash_equals($u['password'], hash('sha256', $pass))) {
        $_SESSION['uid'] = $u['id'];
        $_SESSION['role'] = $u['role'];
        header('Location: ' . ($u['role'] === 'admin' ? 'admin.php' : 'dashboard.php'));
        exit;
    }
    $err = "Sai tài khoản hoặc mật khẩu!";
}

include 'header.php';
?>

<main class="min-h-[75vh] flex items-center justify-center bg-gradient-to-br from-[#eafcf8] to-[#f8fafb] py-12">
  <div class="w-full max-w-sm rounded-2xl bg-white/90 shadow-lg border border-[#9dcfc3]/40 p-8 backdrop-blur-[2px]">
    <h2 class="text-center text-2xl font-bold mb-6 tracking-wide font-heading">Đăng nhập lớp học</h2>
    <?php if ($err): ?>
      <div class="bg-red-50 border border-red-300 text-red-700 rounded-md px-3 py-2 text-sm mb-4 text-center"><?= htmlspecialchars($err) ?></div>
    <?php endif; ?>
    <form method="post" autocomplete="off" class="space-y-5">
      <div>
        <label class="block text-sm font-medium text-[#285F57] mb-1">Email / Số điện thoại</label>
        <input name="email" type="text" required autofocus
          class="w-full px-4 py-2 border-2 border-[#9dcfc3] rounded-lg bg-gray-50 text-[#374151] focus:outline-none focus:border-[#76a89e] focus:bg-white transition"
          placeholder="Nhập email hoặc SĐT..." />
      </div>
      <div>
        <label class="block text-sm font-medium text-[#285F57] mb-1">Mật khẩu</label>
        <input name="password" type="password" required
          class="w-full px-4 py-2 border-2 border-[#9dcfc3] rounded-lg bg-gray-50 text-[#374151] focus:outline-none focus:border-[#76a89e] focus:bg-white transition"
          placeholder="Nhập mật khẩu..." />
      </div>
      <button class="w-full mt-2 rounded-lg bg-[#9dcfc3] text-[#285F57] font-semibold py-2 shadow hover:bg-[#76a89e] hover:text-white transition">
        Đăng nhập
      </button>
    </form>
    <div class="mt-5 text-center text-sm text-gray-500">
      Chưa có tài khoản?
      <a href="register.php" class="text-[#285F57] hover:underline font-medium transition">Đăng ký</a>
    </div>
  </div>
</main>

<?php include 'footer.php'; ?>
