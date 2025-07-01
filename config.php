<?php
// Kết nối database
try {
    $db = new PDO('mysql:host=localhost;dbname=zoom_class;charset=utf8', 'root', '');
    // Nếu bạn đã đặt mật khẩu cho user root thì thay '' thành 'mật_khẩu_của_bạn'
} catch (PDOException $e) {
    die("Kết nối DB lỗi: " . $e->getMessage());
}

session_start();

if (defined('REQUIRE_LOGIN') && !isset($_SESSION['uid'])) {
    header('Location: login.php');
    exit;
}

function normalize($str) {
    $str = strtolower(trim($str));
    return preg_replace('/\s+/', ' ', $str);
}
?>
