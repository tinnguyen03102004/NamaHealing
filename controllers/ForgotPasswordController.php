<?php
declare(strict_types=1);

namespace NamaHealing\Controllers;

use PDO;
use NamaHealing\Helpers\Mailer;
use NamaHealing\Helpers\Recaptcha;

require_once __DIR__ . '/../helpers/Security.php';

class ForgotPasswordController
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function handle(): void
    {
        $message = '';
        $sent    = false;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $token = $_POST['csrf_token'] ?? null;
            if (!csrf_verify_and_unset($token, 'forgot_password', 900)) {
                http_response_code(400);
                $message = 'CSRF token không hợp lệ.';
                $this->render($message, $sent);
                return;
            }

            if (!empty($_POST['website'])) {
                $message = 'Yêu cầu không hợp lệ.';
                $this->render($message, $sent);
                return;
            }

            $recaptcha = $_POST['recaptcha_token'] ?? '';
            if (!Recaptcha::verify($recaptcha)) {
                $message = 'Vui lòng xác minh reCAPTCHA.';
                $this->render($message, $sent);
                return;
            }

            $email = trim((string)($_POST['email'] ?? ''));
            if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $message = 'Email không hợp lệ.';
                $this->render($message, $sent);
                return;
            }

            $stmt = $this->db->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$user) {
                $message = 'Email này chưa được đăng ký.';
                $this->render($message, $sent);
                return;
            }

            $window    = date('Y-m-d H:i:s', time() - 900);
            $ip        = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
            $ipStmt    = $this->db->prepare('SELECT COUNT(*) FROM password_resets WHERE ip_address = ? AND created_at >= ?');
            $ipStmt->execute([$ip, $window]);
            $ipCount = (int)$ipStmt->fetchColumn();
            $userStmt = $this->db->prepare('SELECT COUNT(*) FROM password_resets WHERE user_id = ? AND created_at >= ?');
            $userStmt->execute([$user['id'], $window]);
            $userCount = (int)$userStmt->fetchColumn();
            if ($ipCount >= 3 || $userCount >= 3) {
                http_response_code(429);
                $message = 'Quá nhiều yêu cầu, vui lòng thử lại sau.';
                $this->render($message, $sent);
                return;
            }

            $this->db->prepare('DELETE FROM password_resets WHERE user_id = ?')->execute([$user['id']]);
            $code    = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            $hash    = hash('sha256', $code);
            $expires = date('Y-m-d H:i:s', time() + 900);
            $insert  = $this->db->prepare('INSERT INTO password_resets (user_id, token, expires_at, ip_address, user_agent, created_at) VALUES (?, ?, ?, ?, ?, NOW())');
            $insert->execute([$user['id'], $hash, $expires, $ip, $_SERVER['HTTP_USER_AGENT'] ?? '']);

            $body = 'Mã OTP đặt lại mật khẩu của bạn là: <strong>'
                . htmlspecialchars($code, ENT_QUOTES, 'UTF-8') . '</strong>';
            Mailer::send($email, 'Mã OTP đặt lại mật khẩu', $body);
            $sent    = true;
            $message = 'Mã OTP đã được gửi qua email.';
        }

        $this->render($message, $sent);
    }

    private function render(string $message, bool $sent): void
    {
        csrf_generate_token('forgot_password');
        include __DIR__ . '/../views/forgot_password.php';
    }
}
