<?php
namespace models;

use PDO;

class PasswordResetModel {
    public function __construct(private PDO $pdo) {}

    public function create(int $userId, string $otpHash, string $ip, string $ua, int $ttlMinutes=10): void {
        $stmt = $this->pdo->prepare(
            "INSERT INTO password_resets(user_id, token, expires_at, created_at, ip_address, user_agent)
             VALUES(:uid, :token, (NOW() + INTERVAL :ttl MINUTE), NOW(), :ip, :ua)"
        );
        $stmt->execute([
            ':uid' => $userId,
            ':token' => $otpHash,
            ':ttl' => $ttlMinutes,
            ':ip' => $ip,
            ':ua' => mb_substr($ua, 0, 255),
        ]);
    }

    public function verify(int $userId, string $otpHash): bool {
        $stmt = $this->pdo->prepare(
            "SELECT id FROM password_resets
             WHERE user_id = :uid AND token = :token AND expires_at >= NOW()
             ORDER BY id DESC LIMIT 1"
        );
        $stmt->execute([':uid' => $userId, ':token' => $otpHash]);
        return (bool)$stmt->fetchColumn();
    }

    public function deleteAllForUser(int $userId): void {
        $stmt = $this->pdo->prepare("DELETE FROM password_resets WHERE user_id = :uid");
        $stmt->execute([':uid' => $userId]);
    }
}
