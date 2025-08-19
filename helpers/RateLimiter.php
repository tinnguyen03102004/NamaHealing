<?php
namespace NamaHealing\Helpers;

use PDO;

class RateLimiter {
    // Giới hạn: tối đa 5 yêu cầu OTP / 30 phút / IP hoặc / email
    public static function allow(PDO $pdo, string $email, string $ip): bool {
        $sql = "SELECT COUNT(*) FROM password_resets
                WHERE created_at >= (NOW() - INTERVAL 30 MINUTE)
                AND (ip_address = :ip OR user_id IN (SELECT id FROM users WHERE email = :email))";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':ip' => $ip, ':email' => $email]);
        return ((int)$stmt->fetchColumn()) < 5;
    }
}
