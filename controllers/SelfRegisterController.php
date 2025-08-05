<?php
namespace NamaHealing\Controllers;

use PDO;
use NamaHealing\Models\UserModel;
use NamaHealing\Helpers\Recaptcha;
use NamaHealing\Helpers\Mailer;

class SelfRegisterController {
    private PDO $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    public function handle(): void {
        $err = "";
        $done = false;
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_check($_POST['csrf_token'] ?? null);
            $captcha = $_POST['g-recaptcha-response'] ?? ($_POST['recaptcha_token'] ?? '');
            if (!Recaptcha::verify($captcha)) {
                $err = 'reCAPTCHA validation failed';
            } else {
                $name  = trim($_POST['full_name'] ?? '');
                $email = trim($_POST['email'] ?? '');
                $pass  = $_POST['password'] ?? '';
                if (!$name || !$email || !$pass) {
                    $err = 'Vui lòng nhập đầy đủ thông tin';
                } else {
                    $model = new UserModel($this->db);
                    if ($model->findByEmail($email)) {
                        $err = 'Email đã được sử dụng';
                    } else {
                        $verifyToken = bin2hex(random_bytes(16));
                        $model->createStudent($name, $email, $pass, 0, 0, $verifyToken);
                        $link = ($_ENV['APP_URL'] ?? '') . '/verify.php?token=' . urlencode($verifyToken);
                        $body = 'Vui lòng xác thực tài khoản của bạn bằng cách nhấp vào liên kết sau: <a href="' .
                            $link . '">' . $link . '</a>';
                        Mailer::send($email, 'Xác thực tài khoản NamaHealing', $body);
                        $done = true;
                    }
                }
            }
        }
        include __DIR__ . '/../views/self_register.php';
    }
}
