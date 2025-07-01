<?php
// logout.php
require 'config.php';      // đảm bảo đã khởi tạo session_start()

// Xoá toàn bộ biến phiên và huỷ session
$_SESSION = [];
session_destroy();

// Tùy chọn: xoá cookie phiên (nếu cần)
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Quay về trang đăng nhập / trang chủ
header('Location: index.php');
exit;
