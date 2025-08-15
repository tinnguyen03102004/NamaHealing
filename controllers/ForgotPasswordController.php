<?php
namespace NamaHealing\Controllers;

use PDO;
use NamaHealing\Models\UserModel;
use NamaHealing\Helpers\Mailer;

class ForgotPasswordController {
    private PDO $db;

    public function __construct(PDO $db) {
        $this->db = $db;
        // Ensure password_resets table exists
        $this->db->exec("CREATE TABLE IF NOT EXISTS password_resets (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            token VARCHAR(64) NOT NULL,
            expires_at DATETIME NOT NULL,
            INDEX token_idx (token)
        )");
    }

    public function handle(): void {
        $message = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_check($_POST['csrf_token'] ?? null);
            $email = trim($_POST['email'] ?? '');
            if ($email !== '') {
                $model = new UserModel($this->db);
                $user = $model->findByIdentifier($email);
                if ($user) {
                    $token = bin2hex(random_bytes(32));
                    $expiresAt = date('Y-m-d H:i:s', time() + 3600); // 1 hour
                    $model->createResetToken((int)$user['id'], $token, $expiresAt);
                    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
                    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
                    $link = $scheme . $host . '/reset_password.php?token=' . urlencode($token);
                    $body = '<p>Click the link below to reset your password:</p>'
                          . '<p><a href="' . $link . '">' . $link . '</a></p>';
                    Mailer::send($email, 'Password Reset', $body);
                }
            }
            $message = 'If the email exists, a reset link has been sent.';
        }
        include __DIR__ . '/../views/forgot_password.php';
    }
}
