<?php
// Kết nối database
try {
    $db = new PDO('mysql:host=localhost;dbname=zoom_class;charset=utf8', 'root', '');
    // Nếu bạn đã đặt mật khẩu cho user root thì thay '' thành 'mật_khẩu_của_bạn'
} catch (PDOException $e) {
    die("Kết nối DB lỗi: " . $e->getMessage());
}

session_start();

// Load Composer autoloader if present
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
}

// Load environment variables from .env if available
if (class_exists(Dotenv\Dotenv::class) && file_exists(__DIR__ . '/.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->safeLoad();
}

require_once __DIR__ . '/i18n.php';

if (defined('REQUIRE_LOGIN') && !isset($_SESSION['uid'])) {
    header('Location: login.php');
    exit;
}
?>
