<?php
declare(strict_types=1);

require __DIR__ . '/config.php';

use NamaHealing\Controllers\ForgotPasswordController;
use NamaHealing\Controllers\ResetPasswordController;

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?: '/';

switch ($path) {
    case '/forgot-password':
        (new ForgotPasswordController($db))->handle();
        break;
    case '/reset-password':
        (new ResetPasswordController($db))->handle();
        break;
    case '/':
    default:
        header('Location: home.php');
        exit;
}
