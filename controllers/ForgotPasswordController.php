<?php
// controllers/ForgotPasswordController.php
declare(strict_types=1);

require_once __DIR__ . '/../helpers/Security.php';
// ... require các model/mail cần thiết

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// GET: render form + ROTATE token mới mỗi lần
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // chống cache để tránh form cũ -> token cũ
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Pragma: no-cache');
    $csrf = csrf_generate_token('forgot_password');
    // truyền $csrf sang view (nếu view không tự gọi helper)
    include __DIR__ . '/../views/forgot_password.php';
    exit;
}

// POST: kiểm tra rate‑limit + CSRF
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1) rate‑limit nhẹ, tránh spam
    if (!ratelimit_pass('forgot_password', 5, 300)) {
        http_response_code(429);
        exit('Too many requests. Please try again later.');
    }

    $posted = $_POST['csrf_token'] ?? null;
    if (!csrf_verify_and_unset($posted, 'forgot_password', 900)) { // TTL 15 phút
        http_response_code(400);
        exit('Invalid CSRF token');
    }

    // TODO: validate email server‑side
    $email = trim((string)($_POST['email'] ?? ''));
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(422);
        exit('Email không hợp lệ.');
    }

    // ... phần còn lại: tìm user, xoá token cũ theo email, tạo token mới, gửi mail
    // Gợi ý quan trọng:
    // $pdo->prepare("DELETE FROM password_resets WHERE email = ?")->execute([$email]);
    // $rawToken = bin2hex(random_bytes(32));
    // $tokenHash = hash('sha256', $rawToken);
    // INSERT tokenHash vào DB (cột token đổi thành 64 hex nếu dùng sha256)
    // Gửi email chứa $rawToken trong URL (không bao giờ lưu raw vào DB)
    // Trả về thông báo “Nếu email tồn tại... đã gửi hướng dẫn”.

    exit('Nếu email tồn tại, chúng tôi đã gửi hướng dẫn đặt lại mật khẩu.');
}
