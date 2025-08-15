<?php
namespace NamaHealing\Models;

use PDO;

class UserModel {
    private PDO $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    public function findByIdentifier(string $identifier): ?array {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE email = ? OR phone = ? LIMIT 1');
        $stmt->execute([$identifier, $identifier]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function createStudent(
        string $name,
        string $email,
        string $phone,
        string $pass
    ): void {
        $hash = password_hash($pass, PASSWORD_DEFAULT);
        $sql = "INSERT INTO users (role, full_name, email, phone, password) "
             . "VALUES ('student', ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$name, $email, $phone, $hash]);
    }

    /**
     * Store a password reset token for a user.
     */
    public function createResetToken(int $userId, string $token, string $expiresAt): void {
        $sql = "INSERT INTO password_resets (user_id, token, expires_at) VALUES (?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId, $token, $expiresAt]);
    }

    /**
     * Find a reset token record if it hasn't expired.
     */
    public function findResetToken(string $token): ?array {
        $sql = "SELECT * FROM password_resets WHERE token = ? AND expires_at > NOW() LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$token]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }
}

