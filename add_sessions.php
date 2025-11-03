<?php
use NamaHealing\Models\ReferralModel;
use NamaHealing\Models\UserModel;

require 'config.php';
require_once __DIR__ . '/helpers/Schema.php';
ensure_referrals_table($db);
// Chỉ admin mới được cộng buổi
if (!isset($_SESSION['uid']) || $_SESSION['role'] !== 'admin') {
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check($_POST['csrf_token'] ?? null);
    $uid = intval($_POST['uid'] ?? 0);
    $add = intval($_POST['add'] ?? 0);

    if ($uid > 0 && $add != 0) {
        $userModel = new UserModel($db);
        $referralModel = new ReferralModel($db);

        try {
            $db->beginTransaction();
            // Cập nhật số buổi (có thể âm để trừ)
            $stmt = $db->prepare("UPDATE users SET remaining = remaining + ? WHERE id = ?");
            $stmt->execute([$add, $uid]);

            if ($add > 0) {
                $referralModel->awardBonusForTopUp($userModel, $uid);
            }

            $db->commit();
        } catch (Throwable $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            throw $e;
        }
    }
}

// Quay lại bảng admin
header('Location: admin.php');
exit;
