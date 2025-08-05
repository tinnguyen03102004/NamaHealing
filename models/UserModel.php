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

    public function createStudent(
        string $name,
        string $email,
        string $pass
    ): void {
        $hash = password_hash($pass, PASSWORD_DEFAULT);
        $sql = "INSERT INTO users (role, full_name, email, password) "
             . "VALUES ('student', ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$name, $email, $hash]);
    }
}

