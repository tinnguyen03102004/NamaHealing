<?php
declare(strict_types=1);

namespace NamaHealing\Controllers;

use PDO;
use NamaHealing\Helpers\Mailer;

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

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!ratelimit_pass('forgot_password', 5, 300)) {
                http_response_code(429);
                $message = 'Quá nhiều yêu cầu, vui lòng thử lại sau.';
                include __DIR__ . '/../views/forgot_password.php';
                return;
            }

            $posted = $_POST['csrf_token'] ?? null;
            if (!csrf_verify_and_unset($posted, 'forgot_password', 900)) {
                http_response_code(400);
                $message = 'CSRF token không hợp lệ.';
                include __DIR__ . '/../views/forgot_password.php';
                return;
            }

            $email = trim((string)($_POST['email'] ?? ''));
            if ($email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $stmt = $this->db->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
                $stmt->execute([$email]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($user) {
                    $this->db->prepare('DELETE FROM password_resets WHERE user_id = ?')->execute([$user['id']]);
                    $rawToken   = bin2hex(random_bytes(32));
                    $tokenHash  = hash('sha256', $rawToken);
                    $expires_at = date('Y-m-d H:i:s', time() + 3600);
                    $ins = $this->db->prepare('INSERT INTO password_resets (user_id, token, expires_at) VALUES (?, ?, ?)');
                    $ins->execute([$user['id'], $tokenHash, $expires_at]);

                    $base = $_ENV['APP_URL'] ?? (($_SERVER['HTTPS'] ?? 'off') === 'on' ? 'https://' : 'http://') . ($_SERVER['HTTP_HOST'] ?? '') . dirname($_SERVER['PHP_SELF']);
                    $link = rtrim($base, '/\\') . '/reset_password.php?token=' . $rawToken;
                    $body = 'Nhấn vào liên kết sau để đặt lại mật khẩu: <a href="' . htmlspecialchars($link, ENT_QUOTES, 'UTF-8') . '">' . htmlspecialchars($link, ENT_QUOTES, 'UTF-8') . '</a>';
                    Mailer::send($email, 'Đặt lại mật khẩu', $body);
                }
            }
            $message = 'Nếu email tồn tại, chúng tôi đã gửi hướng dẫn đặt lại mật khẩu.';
            include __DIR__ . '/../views/forgot_password.php';
            return;
        }

        // GET
        csrf_generate_token('forgot_password');
        include __DIR__ . '/../views/forgot_password.php';
    }
}
