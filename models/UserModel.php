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
     * Passwords are hashed and the account is created with the default role of
     * `student`. The number of remaining sessions can be specified to support
     * automatic session crediting from payment gateways.
     */
    public function createStudent(
        string $fullName,
        string $email,
        string $phone,
        string $password,
        int $remaining = 0
    ): int {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->pdo->prepare(
            "INSERT INTO users (full_name, email, phone, password, role, remaining) " .
            "VALUES (:full_name, :email, :phone, :pass, 'student', :remaining)"
        );
        $stmt->execute([
            ':full_name' => $fullName,
            ':email'     => $email,
            ':phone'     => $phone,
            ':pass'      => $hash,
            ':remaining' => $remaining,
        ]);
        return (int)$this->pdo->lastInsertId();
    }

    /**
     * Increment the remaining sessions for a user.
     */
    public function addSessions(int $userId, int $sessions): void {
        $stmt = $this->pdo->prepare(
            "UPDATE users SET remaining = remaining + :s WHERE id = :id"
        );
        $stmt->execute([':s' => $sessions, ':id' => $userId]);
    }

    public function updatePassword(int $userId, string $password): void {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->pdo->prepare("UPDATE users SET password = :p WHERE id = :id");
        $stmt->execute([':p' => $hash, ':id' => $userId]);
    }
}
