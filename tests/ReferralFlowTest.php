<?php

use NamaHealing\Models\ReferralModel;
use NamaHealing\Models\UserModel;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../models/UserModel.php';
require_once __DIR__ . '/../models/ReferralModel.php';
require_once __DIR__ . '/../helpers/Schema.php';

class ReferralFlowTest extends TestCase
{
    private ?PDO $db = null;

    protected function setUp(): void
    {
        try {
            $this->db = new PDO('mysql:host=localhost;dbname=zoom_class;charset=utf8', 'root', '');
            $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (Throwable $e) {
            $this->markTestSkipped('DB not available');
            return;
        }

        if (!$this->db) {
            $this->markTestSkipped('DB connection not initialized');
            return;
        }

        $this->db->exec('CREATE TABLE IF NOT EXISTS users (
            id INT PRIMARY KEY AUTO_INCREMENT,
            full_name VARCHAR(255) NOT NULL,
            email VARCHAR(255) DEFAULT NULL,
            phone VARCHAR(255) DEFAULT NULL,
            password VARCHAR(255) NOT NULL,
            role VARCHAR(50) NOT NULL DEFAULT "student",
            remaining INT NOT NULL DEFAULT 0
        )');

        $this->db->exec('CREATE TABLE IF NOT EXISTS sessions (
            id INT PRIMARY KEY AUTO_INCREMENT,
            user_id INT NOT NULL,
            session VARCHAR(50) NOT NULL,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        )');

        ensure_referrals_table($this->db);

        $this->db->exec('TRUNCATE TABLE referrals');
        $this->db->exec('TRUNCATE TABLE sessions');
        $this->db->exec('TRUNCATE TABLE users');
    }

    public function testReferralBonusesAwardedOnlyOnce(): void
    {
        if (!$this->db) {
            $this->markTestSkipped('DB connection missing');
        }

        $userModel = new UserModel($this->db);
        $referralModel = new ReferralModel($this->db);

        $referrerId = $userModel->createStudent('Existing Student', 'existing@example.com', '0900000000');
        $this->db->prepare('INSERT INTO sessions (user_id, session, created_at) VALUES (?, "morning", NOW())')
            ->execute([$referrerId]);

        $referredId = $userModel->createStudent('New Student', 'new@example.com', '0999999999');

        $referralModel->createPendingReferral($referrerId, $referredId);

        $this->db->beginTransaction();
        $this->db->prepare('UPDATE users SET remaining = remaining + :add WHERE id = :id')
            ->execute([':add' => 10, ':id' => $referredId]);
        $referralModel->awardBonusForTopUp($userModel, $referredId);
        $this->db->commit();

        $remainingReferred = (int) $this->db->query('SELECT remaining FROM users WHERE id = ' . (int) $referredId)->fetchColumn();
        $remainingReferrer = (int) $this->db->query('SELECT remaining FROM users WHERE id = ' . (int) $referrerId)->fetchColumn();
        $status = $this->db->query('SELECT status FROM referrals WHERE referred_id = ' . (int) $referredId)->fetchColumn();

        $this->assertSame(12, $remainingReferred, 'Referred student should receive 10 + 2 sessions');
        $this->assertSame(5, $remainingReferrer, 'Referrer should receive 5 bonus sessions');
        $this->assertSame('awarded', $status);

        $this->db->beginTransaction();
        $this->db->prepare('UPDATE users SET remaining = remaining + :add WHERE id = :id')
            ->execute([':add' => 4, ':id' => $referredId]);
        $referralModel->awardBonusForTopUp($userModel, $referredId);
        $this->db->commit();

        $remainingReferred = (int) $this->db->query('SELECT remaining FROM users WHERE id = ' . (int) $referredId)->fetchColumn();
        $remainingReferrer = (int) $this->db->query('SELECT remaining FROM users WHERE id = ' . (int) $referrerId)->fetchColumn();

        $this->assertSame(16, $remainingReferred, 'Second top-up should not add extra referral bonuses');
        $this->assertSame(5, $remainingReferrer, 'Referrer should not receive additional bonuses');
    }
}
