<?php

use PHPUnit\Framework\TestCase;
use NamaHealing\Models\UserModel;

require_once __DIR__ . '/../models/UserModel.php';

class UserModelIdentifierTest extends TestCase
{
    private ?PDO $db = null;

    protected function setUp(): void
    {
        try {
            $this->db = new PDO('mysql:host=localhost;dbname=zoom_class;charset=utf8', 'root', '');
            $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (Throwable $e) {
            $this->markTestSkipped('DB not available');
        }

        if ($this->db) {
            $this->db->exec('CREATE TABLE IF NOT EXISTS users (
                id INT PRIMARY KEY AUTO_INCREMENT,
                full_name VARCHAR(255) NOT NULL,
                email VARCHAR(255) DEFAULT NULL,
                phone VARCHAR(255) DEFAULT NULL,
                password VARCHAR(255) NOT NULL,
                role VARCHAR(50) NOT NULL DEFAULT "student",
                remaining INT NOT NULL DEFAULT 0
            )');
            $this->db->exec('TRUNCATE TABLE users');

            $insert = $this->db->prepare('INSERT INTO users (id, full_name, email, phone, password, role, remaining) VALUES (?, ?, ?, ?, ?, ?, ?)');
            $insert->execute([1, 'Student Legacy', 'legacy@example.com', '0123456789', 'hash', 'student', 0]);
            $insert->execute([2, 'Student Active', 'active@example.com', '0123456789', 'hash', 'student', 5]);
        }
    }

    public function testFindByIdentifierPrefersAccountWithSessions(): void
    {
        if (!$this->db) {
            $this->markTestSkipped('DB not initialized');
        }

        $model = new UserModel($this->db);
        $user = $model->findByIdentifier('0123456789');

        $this->assertNotNull($user);
        $this->assertSame('2', (string)($user['id'] ?? ''));
        $this->assertSame('5', (string)($user['remaining'] ?? ''));
    }
}

