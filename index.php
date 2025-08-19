<?php
declare(strict_types=1);

session_start();

// Tắt hiển thị lỗi trên production
if (getenv('APP_ENV') === 'production') {
    ini_set('display_errors', '0');
}

// Đường dẫn gốc của ứng dụng
define('BASE_PATH', __DIR__);

// Autoload đơn giản (ưu tiên composer nếu có)
$composer = BASE_PATH . '/vendor/autoload.php';
if (file_exists($composer)) {
    require $composer;
} else {
    spl_autoload_register(function ($class) {
        $prefix = 'NamaHealing\\';
        if (str_starts_with($class, $prefix)) {
            $class = substr($class, strlen($prefix));
        }
        $file = BASE_PATH . '/' . str_replace('\\', '/', $class) . '.php';
        if (file_exists($file)) {
            require $file;
        }
    });
}

require_once BASE_PATH . '/helpers/Response.php';

// Nạp env (nếu có vlucas/phpdotenv)
if (class_exists(\Dotenv\Dotenv::class)) {
    $dotenv = \Dotenv\Dotenv::createImmutable(BASE_PATH);
    $dotenv->safeLoad();
}

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '/';
$uri = rtrim($uri, '/') ?: '/';

use NamaHealing\Controllers\ForgotPasswordController;
use NamaHealing\Helpers\Response;

// Router đơn giản
try {
    switch ($uri) {
        case '/':
            require BASE_PATH . '/home.php';
            break;
        case '/forgot-password':
            (new ForgotPasswordController())->forgotForm();
            break;
        case '/forgot-password/submit':
            (new ForgotPasswordController())->forgotSubmit();
            break;
        case '/reset-password':
            (new ForgotPasswordController())->resetForm();
            break;
        case '/reset-password/submit':
            (new ForgotPasswordController())->resetSubmit();
            break;
        default:
            // Có thể fallback sang file .php cũ nếu tồn tại (giữ tương thích)
            $legacy = BASE_PATH . $uri;
            if (preg_match('#\.php$#', $uri) && file_exists($legacy)) {
                require $legacy;
                break;
            }
            // 404
            Response::view('errors/404', [], 404);
    }
} catch (Throwable $e) {
    error_log('[ERROR] ' . $e->getMessage() . "\n" . $e->getTraceAsString());
    Response::view('errors/500', ['message' => 'Có lỗi hệ thống.'], 500);
}

