<?php
declare(strict_types=1);

namespace NamaHealing\Controllers;

use PDO;
use NamaHealing\Helpers\Recaptcha;

require_once __DIR__ . '/../helpers/Security.php';

class ResetPasswordController
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function handle(): void
    {
        $message = '';
        $success = false;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $token = $_POST['csrf_token'] ?? null;
            if (!csrf_verify_and_unset($token, 'reset_password', 900)) {
                http_response_code(400);
                $message = 'CSRF token không hợp lệ.';
                $this->render($message, $success);
                return;
            }

            if (!empty($_POST['website'])) {
                $message = 'Yêu cầu không hợp lệ.';
                $this->render($message, $success);
                return;
            }

            $recaptcha = $_POST['recaptcha_token'] ?? '';
            if (!Recaptcha::verify($recaptcha)) {
                $message = 'Vui lòng xác minh reCAPTCHA.';
                $this->render($message, $success);
                return;
            }

            $email    = trim((string)($_POST['email'] ?? ''));
            $code     = trim((string)($_POST['code'] ?? ''));
            $password = (string)($_POST['password'] ?? '');
            $confirm  = (string)($_POST['confirm_password'] ?? '');

            if ($email === '' || $code === '' || $password === '') {
                $message = 'Vui lòng nhập đầy đủ thông tin.';
                $this->render($message, $success);
                return;
            }

            if ($password !== $confirm) {
                $message = 'Mật khẩu xác nhận không khớp.';
                $this->render($message, $success);
                return;
            }

            $stmt = $this->db->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$user) {
                $message = 'Email không hợp lệ.';
                $this->render($message, $success);
                return;
            }

            $hash = hash('sha256', $code);
            $stmt = $this->db->prepare('SELECT token, expires_at FROM password_resets WHERE user_id = ? ORDER BY id DESC LIMIT 1');
            $stmt->execute([$user['id']]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$row || !hash_equals($row['token'], $hash) || strtotime($row['expires_at']) < time()) {
                $message = 'OTP không hợp lệ hoặc đã hết hạn.';
                $this->render($message, $success);
                return;
            }

            $newHash = password_hash($password, PASSWORD_DEFAULT);
            $this->db->prepare('UPDATE users SET password = ? WHERE id = ?')->execute([$newHash, $user['id']]);
            $this->db->prepare('DELETE FROM password_resets WHERE user_id = ?')->execute([$user['id']]);
            session_regenerate_id(true);
            $success = true;
            $message = 'Mật khẩu đã được cập nhật.';
        }

        $this->render($message, $success);
    }

    private function render(string $message, bool $success): void
    {
        csrf_generate_token('reset_password');
        include __DIR__ . '/../views/reset_password.php';
    }
}
