<?php
namespace NamaHealing\Models;

use PDO;

class UserModel {
    private PDO $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    public function findByEmail(string $email): ?array {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE email = ?');
        $stmt->execute([$email]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function createStudent(string $name, string $email, string $pass, int $remain): void {
        $hash = password_hash($pass, PASSWORD_DEFAULT);
        $stmt = $this->db->prepare("INSERT INTO users (role,full_name,email,password,remaining) VALUES ('student',?,?,?,?)");
        $stmt->execute([$name, $email, $hash, $remain]);
    }
}
