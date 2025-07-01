<?php
require 'config.php';
if (!isset($_SESSION['uid']) || $_SESSION['role'] !== 'admin') {
    header('Location: index.php'); exit;
}

$err = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name  = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $pass  = $_POST['password'] ?? '';
    $remain= intval($_POST['remaining'] ?? 0);

    if (!$name || !$email || !$pass) {
        $err = "Vui lòng nhập đủ thông tin!";
    } else {
        // Kiểm tra trùng email/sdt
        $stmt = $db->prepare("SELECT id FROM users WHERE email=?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $err = "Email hoặc số điện thoại đã tồn tại!";
        } else {
            $hash = hash('sha256', $pass);
            $stmt = $db->prepare("INSERT INTO users (role,full_name,email,password,remaining) VALUES ('student',?,?,?,?)");
            $stmt->execute([$name, $email, $hash, $remain]);
            header('Location: admin.php'); exit;
        }
    }
}

require 'header.php';
?>

<main class="flex min-h-[70vh] items-center justify-center bg-transparent">
  <div class="w-full max-w-md bg-white/95 rounded-2xl shadow-2xl shadow-[#76a89e26] px-6 py-8 mx-auto">
    <h3 class="font-heading text-center text-2xl mb-6 text-mint-text">Thêm học viên mới</h3>
    <?php if ($err): ?>
      <div class="bg-red-50 border border-red-300 text-red-700 rounded-md px-3 py-2 text-sm mb-4 text-center">
        <?= htmlspecialchars($err) ?>
      </div>
    <?php endif; ?>
    <form method="post" class="space-y-4">
      <div>
        <label class="block text-sm font-medium text-mint-text mb-1">Họ và tên</label>
        <input type="text" name="full_name" class="w-full px-3 py-2 border border-mint rounded-lg bg-gray-50 text-gray-800 focus:outline-none focus:border-mint-dark focus:bg-white transition" required>
      </div>
      <div>
        <label class="block text-sm font-medium text-mint-text mb-1">Email hoặc số điện thoại</label>
        <input type="text" name="email" class="w-full px-3 py-2 border border-mint rounded-lg bg-gray-50 text-gray-800 focus:outline-none focus:border-mint-dark focus:bg-white transition" required>
      </div>
      <div>
        <label class="block text-sm font-medium text-mint-text mb-1">Mật khẩu</label>
        <input type="password" name="password" class="w-full px-3 py-2 border border-mint rounded-lg bg-gray-50 text-gray-800 focus:outline-none focus:border-mint-dark focus:bg-white transition" required>
      </div>
      <div>
        <label class="block text-sm font-medium text-mint-text mb-1">Số buổi học ban đầu</label>
        <input type="number" name="remaining" min="0" value="20" class="w-full px-3 py-2 border border-mint rounded-lg bg-gray-50 text-gray-800 focus:outline-none focus:border-mint-dark focus:bg-white transition">
      </div>
      <div class="flex justify-between items-center mt-6 gap-2">
        <a class="rounded-lg border border-mint text-mint-text px-4 py-2 text-sm font-medium hover:bg-mint hover:text-white transition text-center" href="admin.php">
          Quay lại
        </a>
        <button class="rounded-lg bg-mint text-mint-text font-semibold px-5 py-2 text-sm shadow hover:bg-mint-dark hover:text-white transition" type="submit">
          Tạo học viên
        </button>
      </div>
    </form>
  </div>
</main>

<?php include 'footer.php'; ?>
