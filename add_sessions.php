<?php
require 'config.php';
// Chỉ admin mới được cộng buổi
if (!isset($_SESSION['uid']) || $_SESSION['role'] !== 'admin') {
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check($_POST['csrf_token'] ?? null);
    $uid = intval($_POST['uid'] ?? 0);
    $add = intval($_POST['add'] ?? 0);

    if ($uid > 0 && $add != 0) {
        // Cập nhật số buổi (có thể âm để trừ)
        $stmt = $db->prepare("UPDATE users SET remaining = remaining + ? WHERE id = ?");
        $stmt->execute([$add, $uid]);
    }
}

// Quay lại bảng admin
header('Location: admin.php');
exit;
