<?php

use PHPUnit\Framework\TestCase;

class WelcomeCodeRedemptionTest extends TestCase {
    private ?PDO $db = null;

    protected function setUp(): void {
        try {
            $this->db = new PDO('mysql:host=localhost;dbname=zoom_class;charset=utf8', 'root', '');
            $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (Throwable $e) {
            $this->markTestSkipped('DB not available');
        }

        if ($this->db) {
            $this->db->exec('CREATE TABLE IF NOT EXISTS users (id INT PRIMARY KEY AUTO_INCREMENT, remaining INT NOT NULL DEFAULT 0, is_vip TINYINT(1) NOT NULL DEFAULT 0)');
            $this->db->exec('TRUNCATE TABLE users');
            $stmt = $this->db->prepare('INSERT INTO users(id, remaining) VALUES (1, 0)');
            $stmt->execute();
        }
    }

    public function testRedeemCodeGrantsSessionsAndRedirects(): void {
        if (!$this->db) {
            $this->markTestSkipped('DB not initialised');
        }

        if (session_status() === PHP_SESSION_ACTIVE) {
            session_write_close();
        }

        session_id('welcome-test');
        session_start();
        $_SESSION = [
            'uid' => 1,
            'role' => 'student',
            'csrf_token' => bin2hex(random_bytes(16)),
        ];

        $_POST = [
            'csrf' => $_SESSION['csrf_token'],
            'student_code' => 'VTN2025',
        ];
        $_SERVER['REQUEST_METHOD'] = 'POST';

        if (!defined('WELCOME_TEST_MODE')) {
            define('WELCOME_TEST_MODE', true);
        }

        unset($GLOBALS['redirectUrl']);

        ob_start();
        include __DIR__ . '/../welcome.php';
        ob_end_clean();

        $remain = $this->db->query('SELECT remaining FROM users WHERE id=1')->fetchColumn();
        $this->assertSame('100', (string) $remain);
        $this->assertSame('dashboard.php', $GLOBALS['redirectUrl'] ?? null);

        $_POST = [];
        $_SERVER['REQUEST_METHOD'] = 'GET';
        session_write_close();
        $_SESSION = [];
    }
}
