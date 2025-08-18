<?php
// controllers/ForgotPasswordController.php

class ForgotPasswordController
{
    public function handle(): void
    {
        // GET => hiển thị form
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->renderForm();
            return;
        }

        // CSRF
        if (!Security::checkCsrfToken($_POST['csrf_token'] ?? '')) {
            http_response_code(400);
            $this->renderForm('Yêu cầu không hợp lệ (CSRF).');
            return;
        }

        // Honeypot
        if (!empty($_POST['website'] ?? '')) {
            http_response_code(400);
            $this->renderForm('Yêu cầu không hợp lệ.');
            return;
        }

        // reCAPTCHA (nếu bạn dùng)
        $recaptchaToken = trim((string)($_POST['recaptcha_token'] ?? ''));
        if (!Recaptcha::verify($recaptchaToken)) {
            $this->renderForm('Vui lòng xác minh reCAPTCHA.');
            return;
        }

        // Email
        $email = trim((string)($_POST['email'] ?? ''));
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->renderForm('Email không hợp lệ.');
            return;
        }

        // Tìm user
        $db = Database::getConnection();
        $stmt = $db->prepare('SELECT id, email FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$user) {
            $this->renderForm('Email này chưa được đăng ký.');
            return;
        }

        // Rate limit: 3 lần/15 phút cho IP hoặc user
        $ip = $_SERVER['REMOTE_ADDR'] ?? '';
        $stmt = $db->prepare('SELECT COUNT(*) FROM password_resets WHERE (user_id = ? OR ip_address = ?) AND created_at >= (NOW() - INTERVAL 15 MINUTE)');
        $stmt->execute([$user['id'], $ip]);
        $count = (int)$stmt->fetchColumn();
        if ($count >= 3) {
            http_response_code(429);
            $this->renderForm('Quá nhiều yêu cầu. Vui lòng thử lại sau ít phút.');
            return;
        }

        // Xoá token cũ
        $db->prepare('DELETE FROM password_resets WHERE user_id = ?')->execute([$user['id']]);

        // Tạo OTP 6 số + hash
        $otp      = str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $otpHash  = hash('sha256', $otp);
        $expires  = (new DateTime('+15 minutes'))->format('Y-m-d H:i:s');
        $ua       = substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 1000);

        // Lưu DB
        $ins = $db->prepare('INSERT INTO password_resets(user_id, token, expires_at, created_at, ip_address, user_agent) VALUES(?, ?, ?, NOW(), ?, ?)');
        $ins->execute([$user['id'], $otpHash, $expires, $ip, $ua]);

        // Gửi mail
        $subject = 'Mã OTP đặt lại mật khẩu';
        $body = '
            <div style="font-family:Arial,Helvetica,sans-serif">
                <p>Xin chào <b>' . htmlspecialchars($user['email']) . '</b>,</p>
                <p>Mã OTP của bạn là:</p>
                <div style="font-size:26px;font-weight:700;letter-spacing:4px;padding:10px 16px;border:1px dashed #999;display:inline-block">'
                . htmlspecialchars($otp) .
                '</div>
                <p>OTP có hiệu lực trong <b>15 phút</b>. Nếu không phải bạn yêu cầu, bạn có thể bỏ qua email này.</p>
            </div>';

        $sent = Mailer::send($user['email'], $subject, $body);
        if (!$sent) {
            // Gợi ý debug trong log cPanel
            error_log('[ForgotPassword] Mail send failed for ' . $user['email'] . ' from IP ' . $ip);
            $this->renderForm('Không gửi được email OTP. Vui lòng thử lại sau (hoặc liên hệ hỗ trợ).');
            return;
        }

        $this->renderForm('Mã OTP đã được gửi qua email.', true);
    }

    private function renderForm(string $message = '', bool $sent = false): void
    {
        $data = [
            'title'   => 'Quên mật khẩu',
            'message' => $message,
            'sent'    => $sent,
            'csrf'    => Security::csrfToken(),
        ];
        View::render('auth/forgot_password', $data);
    }
}
