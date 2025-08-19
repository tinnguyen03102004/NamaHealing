<?php
// Nạp Composer autoloader (nếu có)
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
} else {
    spl_autoload_register(function ($class) {
        $prefix = 'NamaHealing\\';
        if (str_starts_with($class, $prefix)) {
            $class = substr($class, strlen($prefix));
        }
        $path = str_replace('\\', '/', $class);
        $file = __DIR__ . '/' . $path . '.php';
        if (!file_exists($file)) {
            // fall back to lowercase directory names (e.g. controllers/ vs Controllers/)
            $segments = explode('/', $path);
            $fileName = array_pop($segments);
            $segments = array_map('strtolower', $segments);
            $file = __DIR__ . '/' . implode('/', $segments) . '/' . $fileName . '.php';
        }
        if (file_exists($file)) {
            require $file;
        }
    });
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
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Kết nối DB lỗi: " . $e->getMessage());
}

// Khởi động session (nếu chưa có)
if (session_status() === PHP_SESSION_NONE) {
    $domain = $_SERVER['HTTP_HOST'] ?? '';
    if (strpos($domain, ':') !== false) {
        $domain = explode(':', $domain)[0];
    }
    if ($domain && $domain !== 'localhost' && !filter_var($domain, FILTER_VALIDATE_IP)) {
        $domain = '.' . preg_replace('/^www\./', '', $domain);
    } else {
        $domain = '';
    }

    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'domain'   => $domain,
        'httponly' => true,
        'samesite' => 'Lax',
        'secure'   => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
    ]);
    session_start();
}

// CSRF token utilities
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

function csrf_check(?string $token): void {
    if (!isset($_SESSION['csrf_token']) || !is_string($token) || !hash_equals($_SESSION['csrf_token'], $token)) {
        http_response_code(400);
        exit('Invalid CSRF token');
    }
}

// Nạp file đa ngôn ngữ (nếu có)
require_once __DIR__ . '/i18n.php';

// Bắt buộc đăng nhập nếu cấu hình REQUIRE_LOGIN
if (defined('REQUIRE_LOGIN') && !isset($_SESSION['uid'])) {
    header('Location: login.php');
    exit;
}
