<?php
declare(strict_types=1);

const REMEMBER_COOKIE_NAME = 'nama_remember';
const REMEMBER_COOKIE_LIFETIME = 60 * 60 * 24 * 30; // 30 days

function remember_table(PDO $db): void
{
    $db->exec("CREATE TABLE IF NOT EXISTS user_remember_tokens (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        selector CHAR(32) NOT NULL,
        token_hash CHAR(64) NOT NULL,
        expires_at DATETIME NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY selector (selector),
        INDEX user_idx (user_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
}

function remember_cookie_domain(): string
{
    $domain = $_SERVER['HTTP_HOST'] ?? '';
    if ($domain === '' || $domain === 'localhost' || filter_var($domain, FILTER_VALIDATE_IP)) {
        return '';
    }
    if (str_contains($domain, ':')) {
        $domain = explode(':', $domain)[0];
    }
    return '.' . ltrim(preg_replace('/^www\./', '', $domain), '.');
}

function remember_set_cookie(string $value, int $lifetime = REMEMBER_COOKIE_LIFETIME): void
{
    setcookie(REMEMBER_COOKIE_NAME, $value, [
        'expires'  => time() + $lifetime,
        'path'     => '/',
        'domain'   => remember_cookie_domain(),
        'secure'   => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    $_COOKIE[REMEMBER_COOKIE_NAME] = $value;
}

function remember_clear_cookie(): void
{
    setcookie(REMEMBER_COOKIE_NAME, '', [
        'expires'  => time() - 3600,
        'path'     => '/',
        'domain'   => remember_cookie_domain(),
        'secure'   => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    unset($_COOKIE[REMEMBER_COOKIE_NAME]);
}

function remember_issue_token(PDO $db, int $userId): void
{
    remember_table($db);
    $selector = bin2hex(random_bytes(16));
    $validator = bin2hex(random_bytes(32));
    $hash = hash('sha256', $validator);
    $expires = (new DateTimeImmutable('+' . REMEMBER_COOKIE_LIFETIME . ' seconds'))
        ->setTimezone(new DateTimeZone(date_default_timezone_get()))
        ->format('Y-m-d H:i:s');

    $db->prepare('DELETE FROM user_remember_tokens WHERE user_id = ? OR expires_at < NOW()')->execute([$userId]);

    $stmt = $db->prepare('INSERT INTO user_remember_tokens (user_id, selector, token_hash, expires_at) VALUES (?, ?, ?, ?)');
    $stmt->execute([$userId, $selector, $hash, $expires]);

    remember_set_cookie($selector . ':' . $validator);
}

function remember_consume(PDO $db, string $cookieValue): ?int
{
    remember_table($db);
    if (!str_contains($cookieValue, ':')) {
        return null;
    }
    [$selector, $validator] = explode(':', $cookieValue, 2);
    if ($selector === '' || $validator === '') {
        return null;
    }
    $stmt = $db->prepare('SELECT id, user_id, token_hash, expires_at FROM user_remember_tokens WHERE selector = ? LIMIT 1');
    $stmt->execute([$selector]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$row) {
        return null;
    }
    if (strtotime($row['expires_at']) < time()) {
        $db->prepare('DELETE FROM user_remember_tokens WHERE id = ?')->execute([$row['id']]);
        return null;
    }
    $expected = $row['token_hash'];
    if (!hash_equals($expected, hash('sha256', $validator))) {
        $db->prepare('DELETE FROM user_remember_tokens WHERE id = ?')->execute([$row['id']]);
        return null;
    }
    $db->prepare('DELETE FROM user_remember_tokens WHERE id = ?')->execute([$row['id']]);
    return (int)$row['user_id'];
}

function remember_forget(PDO $db, ?string $cookieValue = null, ?int $userId = null): void
{
    remember_table($db);
    if ($cookieValue && str_contains($cookieValue, ':')) {
        [$selector] = explode(':', $cookieValue, 2);
        $stmt = $db->prepare('DELETE FROM user_remember_tokens WHERE selector = ?');
        $stmt->execute([$selector]);
    }
    if ($userId) {
        $stmt = $db->prepare('DELETE FROM user_remember_tokens WHERE user_id = ?');
        $stmt->execute([$userId]);
    }
    remember_clear_cookie();
}

function remember_bootstrap(PDO $db): void
{
    if (isset($_SESSION['uid'])) {
        return;
    }
    $cookie = $_COOKIE[REMEMBER_COOKIE_NAME] ?? null;
    if (!$cookie) {
        return;
    }
    $userId = remember_consume($db, $cookie);
    if (!$userId) {
        remember_clear_cookie();
        return;
    }
    $stmt = $db->prepare('SELECT id, role FROM users WHERE id = ? LIMIT 1');
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$user) {
        remember_clear_cookie();
        return;
    }
    session_regenerate_id(true);
    $_SESSION['uid'] = (int)$user['id'];
    $_SESSION['role'] = $user['role'];
    remember_issue_token($db, (int)$user['id']);
}
