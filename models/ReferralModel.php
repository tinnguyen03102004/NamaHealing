<?php
namespace NamaHealing\Models;

use PDO;

class ReferralModel
{
    public function __construct(private PDO $pdo)
    {
    }

    /**
     * Persist a referral relationship in a pending state.
     */
    public function createPendingReferral(int $referrerId, int $referredId): void
    {
        $sql = <<<'SQL'
INSERT INTO referrals (referrer_id, referred_id, status)
VALUES (:referrer_id, :referred_id, 'pending')
ON DUPLICATE KEY UPDATE
    referrer_id = VALUES(referrer_id),
    status = 'pending',
    awarded_at = NULL
SQL;
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':referrer_id' => $referrerId,
            ':referred_id' => $referredId,
        ]);
    }

    public function findPendingByReferredId(int $referredId): ?array
    {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM referrals WHERE referred_id = :referred_id AND status = 'pending' LIMIT 1"
        );
        $stmt->execute([':referred_id' => $referredId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function markAsAwarded(int $referralId): void
    {
        $stmt = $this->pdo->prepare(
            "UPDATE referrals SET status = 'awarded', awarded_at = NOW() WHERE id = :id"
        );
        $stmt->execute([':id' => $referralId]);
    }

    /**
     * Grant bonus sessions to both students when the referred student tops up for the first time.
     */
    public function awardBonusForTopUp(UserModel $userModel, int $referredId): void
    {
        $referral = $this->findPendingByReferredId($referredId);
        if (!$referral) {
            return;
        }

        $referralId = (int) $referral['id'];
        $referrerId = (int) $referral['referrer_id'];

        if ($userModel->findById($referredId)) {
            $userModel->addSessions($referredId, 2);
        }

        if ($referrerId > 0 && $userModel->findById($referrerId)) {
            $userModel->addSessions($referrerId, 5);
        }

        $this->markAsAwarded($referralId);
    }
}
