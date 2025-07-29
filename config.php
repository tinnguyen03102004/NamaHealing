<?php
// Nạp Composer autoloader (nếu có)
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
}

// Nạp biến môi trường từ file .env (nếu có)
if (class_exists(Dotenv\Dotenv::class) && file_exists(__DIR__ . '/.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->safeLoad(); // không lỗi nếu thiếu biến, an toàn hơn -> không ghi đè biến môi trường sẵn có
}

// Lấy thông tin kết nối Database từ biến môi trường (nếu có), nếu không dùng mặc định
$dbHost = $_ENV['DB_HOST'] ?? 'localhost';
$dbName = $_ENV['DB_NAME'] ?? 'zoom_class';
$dbUser = $_ENV['DB_USER'] ?? 'root';
$dbPass = $_ENV['DB_PASS'] ?? '';

try {
    $dsn = "mysql:host={$dbHost};dbname={$dbName};charset=utf8";
    $db = new PDO($dsn, $dbUser, $dbPass);
} catch (PDOException $e) {
    die("Kết nối DB lỗi: " . $e->getMessage());
}

// Khởi động session (nếu chưa có)
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    session_start();
}

// Nạp file đa ngôn ngữ (nếu có)
require_once __DIR__ . '/i18n.php';

// Bắt buộc đăng nhập nếu cấu hình REQUIRE_LOGIN
if (defined('REQUIRE_LOGIN') && !isset($_SESSION['uid'])) {
    header('Location: login.php');
    exit;
}
?>
