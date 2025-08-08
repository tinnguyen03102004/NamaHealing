<?php
define('REQUIRE_LOGIN', true);
require 'config.php';
if (!isset($_SESSION['uid']) || $_SESSION['role'] !== 'student') {
    header('Location: login.php');
    exit;
}

$success = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check($_POST['csrf_token'] ?? null);
    $current = $_POST['current_password'] ?? '';
    $new = $_POST['new_password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    $stmt = $db->prepare('SELECT password FROM users WHERE id=?');
    $stmt->execute([$_SESSION['uid']]);
    $hash = $stmt->fetchColumn();

    if (!password_verify($current, $hash)) {
        $error = __('err_current_password');
    } elseif ($new !== $confirm) {
        $error = __('err_password_mismatch');
    } else {
        $newHash = password_hash($new, PASSWORD_DEFAULT);
        $stmt = $db->prepare('UPDATE users SET password=? WHERE id=?');
        $stmt->execute([$newHash, $_SESSION['uid']]);
        $success = true;
    }
}

require 'header.php';
?>
<main class="min-h-[75vh] flex items-center justify-center px-2 py-8">
  <div class="w-full max-w-md bg-white/95 rounded-2xl shadow-2xl shadow-[#76a89e26] px-6 py-8">
    <h2 class="text-center text-2xl font-bold text-mint-text mb-4" style="font-family:'Montserrat',sans-serif;">
      <?= __('change_password') ?>
    </h2>
    <?php if ($success): ?>
      <div class="mb-4 p-3 rounded bg-green-100 text-green-700 text-sm"><?= __('password_changed') ?></div>
    <?php elseif ($error): ?>
      <div class="mb-4 p-3 rounded bg-red-100 text-red-700 text-sm"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <form method="post" class="flex flex-col gap-3">
      <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">
      <label>
        <span class="font-semibold text-mint-text"><?= __('current_password') ?></span>
        <input type="password" name="current_password" class="mt-1 w-full rounded border border-mint px-3 py-2 focus:border-mint-dark focus:ring-mint" required>
      </label>
      <label>
        <span class="font-semibold text-mint-text"><?= __('new_password') ?></span>
        <input type="password" name="new_password" class="mt-1 w-full rounded border border-mint px-3 py-2 focus:border-mint-dark focus:ring-mint" required>
      </label>
      <label>
        <span class="font-semibold text-mint-text"><?= __('confirm_password') ?></span>
        <input type="password" name="confirm_password" class="mt-1 w-full rounded border border-mint px-3 py-2 focus:border-mint-dark focus:ring-mint" required>
      </label>
      <button class="rounded-lg bg-mint text-mint-text font-semibold px-4 py-2 text-sm shadow hover:bg-mint-dark hover:text-white transition"><?= __('change_password') ?></button>
    </form>
  </div>
</main>
<?php include 'footer.php'; ?>
