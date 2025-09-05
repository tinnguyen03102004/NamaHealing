<?php
use PHPUnit\Framework\TestCase;

class JoinIdempotentTest extends TestCase {
    private ?PDO $db = null;

    protected function setUp(): void {
        try {
            $this->db = new PDO('mysql:host=localhost;dbname=zoom_class;charset=utf8', 'root', '');
            $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (Throwable $e) {
            $this->markTestSkipped('DB not available');
        }
        if ($this->db) {
            $this->db->exec('CREATE TABLE IF NOT EXISTS users (id INT PRIMARY KEY AUTO_INCREMENT, remaining INT NOT NULL)');
            $this->db->exec('CREATE TABLE IF NOT EXISTS sessions (id INT PRIMARY KEY AUTO_INCREMENT, user_id INT, session VARCHAR(10), created_at DATETIME DEFAULT CURRENT_TIMESTAMP)');
            $this->db->exec('TRUNCATE TABLE users');
            $this->db->exec('TRUNCATE TABLE sessions');
            $stmt = $this->db->prepare('INSERT INTO users(id, remaining) VALUES (1, 5)');
            $stmt->execute();
        }
    }

    private function simulateJoin(int $uid, string $session): void {
        $stmt = $this->db->prepare('SELECT 1 FROM sessions WHERE user_id=? AND session=? AND DATE(created_at)=CURDATE()');
        $stmt->execute([$uid, $session]);
        if (!$stmt->fetchColumn()) {
            $this->db->prepare('UPDATE users SET remaining=remaining-1 WHERE id=?')->execute([$uid]);
            $this->db->prepare('INSERT INTO sessions(user_id, session) VALUES (?,?)')->execute([$uid, $session]);
        }
    }

    public function testMultipleJoinsCountOnce() {
        if (!$this->db) {
            $this->markTestSkipped('DB not initialized');
        }
        $this->simulateJoin(1, 'morning');
        $this->simulateJoin(1, 'morning');
        $remain = $this->db->query('SELECT remaining FROM users WHERE id=1')->fetchColumn();
        $this->assertSame('4', (string)$remain);
        $count = $this->db->query("SELECT COUNT(*) FROM sessions WHERE user_id=1 AND session='morning'")->fetchColumn();
        $this->assertSame('1', (string)$count);
    }
}
