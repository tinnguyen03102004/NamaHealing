<?php
require 'config.php';

if (isset($_SESSION['uid']) && ($_SESSION['role'] ?? '') === 'teacher') {
    header('Location: teacher_dashboard.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check($_POST['csrf_token'] ?? null);
    $identifier = trim($_POST['identifier'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $db->prepare("SELECT * FROM users WHERE role='teacher' AND (email = ? OR phone = ?) LIMIT 1");
    $stmt->execute([$identifier, $identifier]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($user && password_verify($password, $user['password'])) {
        session_regenerate_id(true);
        $_SESSION['uid'] = $user['id'];
        $_SESSION['role'] = 'teacher';
        header('Location: teacher_dashboard.php');
        exit;
    } else {
        $error = 'Sai thông tin đăng nhập';
    }
}

$pageTitle = 'Teacher Login';
require 'header.php';
?>
<main class="min-h-[70vh] flex items-center justify-center px-4">
  <form method="post" class="bg-white p-6 rounded shadow w-full max-w-sm space-y-4">
    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">
    <?php if ($error): ?>
      <div class="text-red-500 text-sm"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <div>
      <label class="block mb-1">Email hoặc SĐT</label>
      <input type="text" name="identifier" class="w-full border px-3 py-2 rounded" required>
    </div>
    <div>
      <label class="block mb-1">Mật khẩu</label>
      <input type="password" name="password" class="w-full border px-3 py-2 rounded" required>
    </div>
    <button type="submit" class="w-full bg-[#9dcfc3] text-white py-2 rounded">Đăng nhập</button>
  </form>
</main>
<?php include 'footer.php'; ?>
