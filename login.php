<?php
require 'config.php';

// Nếu đã đăng nhập, chuyển thẳng tới giao diện tương ứng
if (isset($_SESSION['uid'])) {
    if (($_SESSION['role'] ?? '') === 'admin') {
        header('Location: admin.php');
        exit;
    } elseif (($_SESSION['role'] ?? '') === 'teacher') {
        header('Location: teacher_dashboard.php');
        exit;
    } else {
        $stmt = $db->prepare("SELECT remaining FROM users WHERE id=?");
        $stmt->execute([$_SESSION['uid']]);
        $remaining = (int)$stmt->fetchColumn();
        header('Location: ' . ($remaining > 0 ? 'dashboard.php' : 'welcome.php'));
        exit;
    }
}

// Xử lý logic đăng nhập
$err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Kiểm tra token CSRF từ form
    csrf_check($_POST['csrf_token'] ?? null);

    // Lấy và chuẩn hóa định danh (email hoặc số điện thoại)
    $identifier = trim($_POST['identifier'] ?? '');
    if (!str_contains($identifier, '@')) {
        // Nếu không chứa '@' => coi như số ĐT, loại bỏ ký tự không phải số
        $identifier = preg_replace('/\D+/', '', $identifier);
    }
    $password = $_POST['password'] ?? '';

    // Kiểm tra thông tin tài khoản
    $model = new NamaHealing\Models\UserModel($db);
    $user  = $model->findByIdentifier($identifier);
    if ($user && in_array($user['role'], NamaHealing\Models\UserModel::ALLOWED_ROLES, true)) {
        $isStudent = $user['role'] === 'student';
        $validPassword = password_verify($password, $user['password']);

        if (!$isStudent && !$validPassword) {
            $user = null;
        }
    } else {
        $user = null;
    }

    if ($user) {
        // Đăng nhập thành công => tạo session và chuyển hướng
        session_regenerate_id(true);
        $_SESSION['uid']  = $user['id'];
        $_SESSION['role'] = $user['role'];

        remember_issue_token($db, (int)$user['id']);

        // Xác định URL đích sau đăng nhập
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
        $host     = $_SERVER['HTTP_HOST'] ?? '';
        if ($user['role'] === 'admin') {
            $redirectUrl = $protocol . $host . '/admin.php';
        } elseif ($user['role'] === 'teacher') {
            $redirectUrl = $protocol . $host . '/teacher_dashboard.php';
        } else {
            // role student
            $redirectUrl = ($user['remaining'] ?? 0) > 0 
                         ? ($protocol . $host . '/dashboard.php') 
                         : ($protocol . $host . '/welcome.php');
        }
        header("Location: $redirectUrl");
        exit;
    }

    // Nếu tới đây nghĩa là đăng nhập thất bại
    $err = __('login_error');
}

// Hiển thị giao diện (HTML + PHP)
?>
<?php include 'header.php'; ?>
<main class="min-h-[75vh] flex items-center justify-center bg-gradient-to-br from-[#eafcf8] to-[#f8fafb] py-12">
  <div class="w-full max-w-sm rounded-2xl bg-white/90 shadow-lg border border-[#9dcfc3]/40 p-8 backdrop-blur-[2px]">
    <h2 class="text-center text-2xl font-bold mb-6 tracking-wide font-heading">
      <?= __('login_heading') ?>
    </h2>
    <?php if ($err): // Thông báo lỗi đăng nhập ?>
      <div class="bg-red-50 border border-red-300 text-red-700 rounded-md px-3 py-2 text-sm mb-4 text-center">
        <?= htmlspecialchars($err) ?>
      </div>
    <?php endif; ?>
    <form method="post" autocomplete="off" class="space-y-5">
      <!-- Token CSRF ẩn -->
      <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
      <div>
        <label class="block text-sm font-medium text-[#285F57] mb-1">
          <?= __('identifier_label') ?>
        </label>
        <input name="identifier" type="text" required autofocus
          class="w-full px-4 py-2 border-2 border-[#9dcfc3] rounded-lg bg-gray-50 text-[#374151] focus:outline-none focus:border-[#76a89e] focus:bg-white transition"
          placeholder="<?= __('identifier_placeholder') ?>" />
      </div>
      <div>
        <label class="block text-sm font-medium text-[#285F57] mb-1">
          <?= __('password_label') ?>
        </label>
        <input name="password" type="password"
          class="w-full px-4 py-2 border-2 border-[#9dcfc3] rounded-lg bg-gray-50 text-[#374151] focus:outline-none focus:border-[#76a89e] focus:bg-white transition"
          placeholder="<?= __('password_placeholder') ?>" />
        <p class="mt-1 text-xs text-gray-500">
          <?= __('password_hint') ?>
        </p>
      </div>
      <button class="w-full mt-2 rounded-lg bg-[#9dcfc3] text-[#285F57] font-semibold py-2 shadow hover:bg-[#76a89e] hover:text-white transition">
        <?= __('login_button') ?>
      </button>
    </form>
    <div class="mt-5 text-center text-sm text-gray-500">
      <?= __('no_account') ?>
      <a href="register.php" class="text-[#285F57] hover:underline font-medium transition">
        <?= __('register_link') ?>
      </a>
    </div>
  </div>
</main>
<?php include 'footer.php'; ?>
