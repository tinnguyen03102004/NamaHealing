<?php
require 'config.php';
if (!isset($_SESSION['uid']) || $_SESSION['role'] !== 'admin') {
    header('Location: index.php');
    exit;
}

$id = intval($_GET['id'] ?? 0);
if ($id) {
    // 1) Xóa trước tất cả lịch sử sessions của user này
    $db->prepare("DELETE FROM sessions WHERE user_id = ?")->execute([$id]);

    // 2) Sau đó xóa học viên
    $db->prepare("DELETE FROM users WHERE id = ? AND role = 'student'")->execute([$id]);
}

// Quay lại admin
header('Location: admin.php');
exit;
