<?php
namespace NamaHealing\Models;

use PDO;

class UserModel {
    public function __construct(private PDO $pdo) {}

    public function findByEmail(string $email): ?array {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE email = :email LIMIT 1");
        $stmt->execute([':email' => $email]);
        $u = $stmt->fetch(PDO::FETCH_ASSOC);
        return $u ?: null;
    }

    /**
     * Find a user by either email or phone number.
     *
     * The application often accepts a single "identifier" field where the
     * user may enter an email address or a phone number. The original
     * implementation only provided a `findByEmail` method which caused runtime
     * errors when other parts of the code attempted to look up users by phone
     * number. This helper consolidates the logic in one place and returns the
     * first matching record.
     */
    public function findByIdentifier(string $identifier): ?array {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM users WHERE email = :id OR phone = :id LIMIT 1"
        );
        $stmt->execute([':id' => $identifier]);
        $u = $stmt->fetch(PDO::FETCH_ASSOC);
        return $u ?: null;
    }

    /**
     * Create a new student account.
     *
     * A minimal set of fields is inserted to match the usage throughout the
     * application. Passwords are hashed and the account is created with the
     * default role of `student` and no remaining sessions.
     */
    public function createStudent(string $name, string $email, string $phone, string $password): int {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->pdo->prepare(
            "INSERT INTO users (full_name, email, phone, password, role, remaining, created_at) " .
            "VALUES (:name, :email, :phone, :pass, 'student', 0, NOW())"
        );
        $stmt->execute([
            ':name'  => $name,
            ':email' => $email,
            ':phone' => $phone,
            ':pass'  => $hash,
        ]);
        return (int)$this->pdo->lastInsertId();
    }

    public function updatePassword(int $userId, string $password): void {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->pdo->prepare("UPDATE users SET password = :p WHERE id = :id");
        $stmt->execute([':p' => $hash, ':id' => $userId]);
    }
}
