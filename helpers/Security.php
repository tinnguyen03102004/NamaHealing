<?php
// helpers/Security.php
declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) {
    // Bắt đầu session thật sớm trong bootstrap của bạn; phòng hờ ở đây:
    session_start();
}

/** Sinh CSRF token (rotate mỗi lần render form) */
function csrf_generate_token(string $form = 'default'): string {
    $token = bin2hex(random_bytes(32));
    $_SESSION['csrf'][$form] = [
        'value' => $token,
        'ts'    => time(),
    ];
    return $token;
}

/** Lấy CSRF token hiện có (không sinh mới) – phòng trường hợp cần đọc lại */
function csrf_peek_token(string $form = 'default'): ?string {
    return $_SESSION['csrf'][$form]['value'] ?? null;
}

/** Kiểm tra và xoá token sau khi dùng (one‑time) */
function csrf_verify_and_unset(?string $token, string $form = 'default', int $ttl = 600): bool {
    if (!is_string($token) || $token === '') return false;
    $entry = $_SESSION['csrf'][$form] ?? null;
    if (!$entry) return false;
    $valid = hash_equals($entry['value'], $token) && (time() - ($entry['ts'] ?? 0) <= $ttl);
    // one‑time token: xoá ngay sau khi kiểm tra (dù pass hay fail để tránh replay)
    unset($_SESSION['csrf'][$form]);
    return $valid;
}

/** Rate limit cực nhẹ theo IP + route (vd: 5 req/5 phút) */
function ratelimit_pass(string $key, int $max = 5, int $window = 300): bool {
    $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    $k  = "rl:{$key}:{$ip}";
    $now = time();
    $_SESSION[$k] = array_filter((array)($_SESSION[$k] ?? []), fn($t) => ($now - $t) <= $window);
    if (count($_SESSION[$k]) >= $max) return false;
    $_SESSION[$k][] = $now;
    return true;
}
