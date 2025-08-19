<?php
namespace controllers;

use helpers\Csrf;
use helpers\Mailer;
use helpers\RateLimiter;
use helpers\Response;
use models\UserModel;
use models\PasswordResetModel;
use PDO;

class ForgotPasswordController {
    private PDO $pdo;
    private UserModel $users;
    private PasswordResetModel $resets;

    public function __construct() {
        // Kết nối PDO từ env
        $dsn = sprintf('mysql:host=%s;dbname=%s;charset=utf8mb4',
            getenv('DB_HOST'), getenv('DB_NAME'));
        $this->pdo = new PDO($dsn, getenv('DB_USER'), getenv('DB_PASS'), [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ]);
        $this->users = new UserModel($this->pdo);
        $this->resets = new PasswordResetModel($this->pdo);
    }

    public function forgotForm(): void {
        Response::view('auth/forgot_password', [
            'csrf' => \helpers\Csrf::token(),
            'recaptcha_site_key' => getenv('RECAPTCHA_SITE_KEY'),
            'title' => 'Quên mật khẩu - NamaHealing',
            'description' => 'Nhập email để nhận mã OTP đặt lại mật khẩu',
        ]);
    }

    public function forgotSubmit(): void {
        // CSRF
        if (!Csrf::check($_POST['_csrf'] ?? null)) {
            Response::json(['message' => 'CSRF token không hợp lệ'], 400);
        }
        // Honeypot
        if (!empty($_POST['website'])) {
            Response::json(['message' => 'Spam detected'], 400);
        }
        // reCAPTCHA v3
        $this->assertRecaptcha($_POST['g-recaptcha-response'] ?? '');

        $email = trim((string)($_POST['email'] ?? ''));
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Response::json(['message' => 'Email không hợp lệ'], 422);
        }

        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        if (!RateLimiter::allow($this->pdo, $email, $ip)) {
            Response::json(['message' => 'Bạn thao tác quá nhanh, thử lại sau ít phút.'], 429);
        }

        $user = $this->users->findByEmail($email);
        // Không tiết lộ user tồn tại hay không
        if ($user) {
            $otp = random_int(100000, 999999);
            $otpHash = hash('sha256', $otp . ($this->pepper()));

            $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
            $this->resets->create((int)$user['id'], $otpHash, $ip, $ua);

            $appUrl = rtrim(getenv('APP_URL') ?: '', '/');
            $html = $this->otpEmailTemplate($otp, $appUrl);
            Mailer::queue($this->pdo, $email, 'Mã OTP đặt lại mật khẩu', $html);
        }

        Response::json(['message' => 'Nếu email hợp lệ, OTP sẽ được gửi trong giây lát.']);
    }

    public function resetForm(): void {
        Response::view('auth/reset_password', [
            'csrf' => \helpers\Csrf::token(),
            'recaptcha_site_key' => getenv('RECAPTCHA_SITE_KEY'),
            'title' => 'Đặt lại mật khẩu - NamaHealing',
            'description' => 'Nhập OTP đã gửi qua email để đặt lại mật khẩu',
        ]);
    }

    public function resetSubmit(): void {
        if (!Csrf::check($_POST['_csrf'] ?? null)) {
            Response::json(['message' => 'CSRF token không hợp lệ'], 400);
        }
        if (!empty($_POST['website'])) {
            Response::json(['message' => 'Spam detected'], 400);
        }
        $this->assertRecaptcha($_POST['g-recaptcha-response'] ?? '');

        $email = trim((string)($_POST['email'] ?? ''));
        $otp   = trim((string)($_POST['otp'] ?? ''));
        $pass1 = (string)($_POST['password'] ?? '');
        $pass2 = (string)($_POST['password_confirm'] ?? '');

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Response::json(['message' => 'Email không hợp lệ'], 422);
        }
        if (!preg_match('/^\d{6}$/', $otp)) {
            Response::json(['message' => 'OTP không hợp lệ'], 422);
        }
        if ($pass1 !== $pass2 || strlen($pass1) < 8) {
            Response::json(['message' => 'Mật khẩu không hợp lệ (>= 8 ký tự) hoặc không khớp'], 422);
        }

        $user = $this->users->findByEmail($email);
        if (!$user) {
            // Không tiết lộ
            Response::json(['message' => 'OTP không đúng hoặc đã hết hạn'], 400);
        }

        $otpHash = hash('sha256', (int)$otp . ($this->pepper()));
        if (!$this->resets->verify((int)$user['id'], $otpHash)) {
            Response::json(['message' => 'OTP không đúng hoặc đã hết hạn'], 400);
        }

        $this->users->updatePassword((int)$user['id'], $pass1);
        $this->resets->deleteAllForUser((int)$user['id']);

        // Audit log đơn giản
        error_log(sprintf('[AUDIT] password_reset user_id=%d ip=%s', (int)$user['id'], $_SERVER['REMOTE_ADDR'] ?? ''));

        Response::json(['message' => 'Đặt lại mật khẩu thành công. Bạn có thể đăng nhập ngay.']);
    }

    private function assertRecaptcha(string $token): void {
        $secret = getenv('RECAPTCHA_SECRET_KEY');
        if (!$secret) return; // cho phép dev nếu chưa cấu hình
        $resp = $this->post('https://www.google.com/recaptcha/api/siteverify', [
            'secret' => $secret,
            'response' => $token,
            'remoteip' => $_SERVER['REMOTE_ADDR'] ?? null,
        ]);
        $ok = $resp['success'] ?? false;
        $score = (float)($resp['score'] ?? 0);
        if (!$ok || $score < 0.5) {
            Response::json(['message' => 'Xác minh reCAPTCHA thất bại'], 400);
        }
    }

    private function post(string $url, array $fields): array {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query($fields),
            CURLOPT_TIMEOUT => 10,
        ]);
        $out = curl_exec($ch);
        if ($out === false) return [];
        return json_decode($out, true) ?: [];
    }

    private function pepper(): string {
        return getenv('APP_PEPPER') ?: 'change-this-pepper';
    }

    private function otpEmailTemplate(int $otp, string $appUrl): string {
        $minutes = 10;
        return <<<HTML
        <div style="font-family: Arial, sans-serif; max-width:600px; margin:auto">
          <h2>NamaHealing - Mã OTP đặt lại mật khẩu</h2>
          <p>Mã OTP của bạn là:</p>
          <div style="font-size:28px;font-weight:bold;letter-spacing:4px">$otp</div>
          <p>OTP có hiệu lực trong $minutes phút.</p>
          <p>Vui lòng mở trang <a href="$appUrl/reset-password">$appUrl/reset-password</a> và nhập OTP để đặt mật khẩu mới.</p>
          <p>Nếu không phải bạn yêu cầu, vui lòng bỏ qua email này.</p>
        </div>
        HTML;
    }
}
