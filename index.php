<?php
declare(strict_types=1);

session_start();

// Tắt hiển thị lỗi trên production
if (getenv('APP_ENV') === 'production') {
    ini_set('display_errors', '0');
}

define('BASE_PATH', dirname(__DIR__));

// Autoload đơn giản (ưu tiên composer nếu có)
$composer = BASE_PATH . '/vendor/autoload.php';
if (file_exists($composer)) {
    require $composer;
}
spl_autoload_register(function ($class) {
    $paths = [
        BASE_PATH . '/controllers/' . $class . '.php',
        BASE_PATH . '/models/' . $class . '.php',
        BASE_PATH . '/helpers/' . $class . '.php',
    ];
    foreach ($paths as $p) { if (file_exists($p)) { require $p; return; } }
});

// Nạp env (nếu có vlucas/phpdotenv)
if (class_exists(\Dotenv\Dotenv::class)) {
    $dotenv = \Dotenv\Dotenv::createImmutable(BASE_PATH);
    $dotenv->safeLoad();
}

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '/';
$uri = rtrim($uri, '/') ?: '/';

use controllers\ForgotPasswordController;
use helpers\Response;

// Router đơn giản
try {
    switch ($uri) {
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

