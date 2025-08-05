<?php
namespace NamaHealing\Models;

use PDO;

class UserModel {
    private PDO $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    public function findByEmailOrPhone(string $identifier): ?array {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE email = ? OR phone = ?');
        $stmt->execute([$identifier, $identifier]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function createStudent(
        string $name,
        string $email,
        string $phone,
        string $pass,
        int $remain = 0
    ): void {
        $hash = password_hash($pass, PASSWORD_DEFAULT);
        $sql = "INSERT INTO users (role,full_name,email,phone,password,remaining,verified,verify_token) "
             . "VALUES ('student', ?, ?, ?, ?, ?, 1, NULL)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$name, $email, $phone, $hash, $remain]);
    }
}

