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
        $stage   = 'request';
        $email   = '';

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

            $action = $_POST['action'] ?? '';
            $email  = trim((string)($_POST['email'] ?? ''));

            if ($action === 'send_code') {
                if ($email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $stmt = $this->db->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
                    $stmt->execute([$email]);
                    $user = $stmt->fetch(PDO::FETCH_ASSOC);
                    if ($user) {
                        $this->db->prepare('DELETE FROM password_resets WHERE user_id = ?')->execute([$user['id']]);
                        $code       = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
                        $codeHash   = hash('sha256', $code);
                        $expires_at = date('Y-m-d H:i:s', time() + 600);
                        $ins = $this->db->prepare('INSERT INTO password_resets (user_id, token, expires_at) VALUES (?, ?, ?)');
                        $ins->execute([$user['id'], $codeHash, $expires_at]);
                        $body = 'Mã xác thực của bạn là: <strong>' . htmlspecialchars($code, ENT_QUOTES, 'UTF-8') . '</strong>';
                        Mailer::send($email, 'Mã xác thực đặt lại mật khẩu', $body);
                        $message = 'Mã xác thực đã được gửi.';
                        $stage   = 'verify';
                    } else {
                        $message = 'Email này chưa được đăng ký.';
                        $stage   = 'request';
                    }
                } else {
                    $message = 'Email không hợp lệ.';
                }
            } elseif ($action === 'reset') {
                $code     = trim((string) ($_POST['code'] ?? ''));
                $password = (string) ($_POST['password'] ?? '');
                $confirm  = (string) ($_POST['confirm_password'] ?? '');

                if ($password === '' || $password !== $confirm) {
                    $message = 'Mật khẩu xác nhận không khớp.';
                    $stage   = 'verify';
                } elseif ($email !== '' && $code !== '') {
                    $stmt = $this->db->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
                    $stmt->execute([$email]);
                    $user = $stmt->fetch(PDO::FETCH_ASSOC);
                    if ($user) {
                        $stmt2 = $this->db->prepare('SELECT token, expires_at FROM password_resets WHERE user_id = ?');
                        $stmt2->execute([$user['id']]);
                        $row = $stmt2->fetch(PDO::FETCH_ASSOC);
                        if ($row && hash('sha256', $code) === $row['token'] && strtotime($row['expires_at']) > time()) {
                            $hash = password_hash($password, PASSWORD_DEFAULT);
                            $this->db->prepare('UPDATE users SET password = ? WHERE id = ?')->execute([$hash, $user['id']]);
                            $this->db->prepare('DELETE FROM password_resets WHERE user_id = ?')->execute([$user['id']]);
                            $message = 'Mật khẩu đã được cập nhật.';
                            $stage   = 'success';
                        } else {
                            $message = 'Mã xác thực không hợp lệ hoặc đã hết hạn.';
                            $stage   = 'verify';
                        }
                    } else {
                        $message = 'Mã xác thực không hợp lệ hoặc đã hết hạn.';
                        $stage   = 'verify';
                    }
                } else {
                    $message = 'Vui lòng nhập đầy đủ thông tin.';
                    $stage   = 'verify';
                }
            }

            csrf_generate_token('forgot_password');
            include __DIR__ . '/../views/forgot_password.php';
            return;
        }

        csrf_generate_token('forgot_password');
        include __DIR__ . '/../views/forgot_password.php';
    }
}
