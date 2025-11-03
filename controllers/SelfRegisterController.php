<?php
namespace NamaHealing\Controllers;

use PDO;
use NamaHealing\Models\ReferralModel;
use NamaHealing\Models\UserModel;

require_once __DIR__ . '/../helpers/Schema.php';

class SelfRegisterController {
    private PDO $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    public function handle(): void {
        $err = "";
        $fullName = '';
        $email = '';
        $phoneInput = '';
        $referralInput = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            \csrf_check($_POST['csrf_token'] ?? null);
            $fullName = trim($_POST['full_name'] ?? '');
            $email    = trim($_POST['email'] ?? '');
            $phoneInput    = trim($_POST['phone'] ?? '');
            $referralInput = trim($_POST['referral_phone'] ?? '');

            $phone = preg_replace('/\D+/', '', $phoneInput);
            $referralPhone = preg_replace('/\D+/', '', $referralInput);

            if (!$fullName || !$email || !$phone) {
                $err = \__('err_required_fields');
            } else {
                $model = new UserModel($this->db);
                if ($model->findByIdentifier($phone) || $model->findByIdentifier($email)) {
                    $err = \__('err_email_exists');
                } else {
                    $referrerId = null;
                    if ($referralPhone !== '') {
                        ensure_referrals_table($this->db);
                        $referrer = $model->findByIdentifier($referralPhone);
                        if (!$referrer) {
                            $err = \__('err_invalid_referral');
                        } else {
                            $referrerSessions = $this->db->prepare(
                                'SELECT COUNT(*) FROM sessions WHERE user_id = :uid'
                            );
                            $referrerSessions->execute([':uid' => (int) $referrer['id']]);
                            if ((int) $referrerSessions->fetchColumn() === 0) {
                                $err = \__('err_invalid_referral');
                            } else {
                                $referrerId = (int) $referrer['id'];
                            }
                        }
                    }

                    if ($err === '') {
                        $newStudentId = $model->createStudent($fullName, $email, $phone);
                        if ($referrerId !== null) {
                            $referralModel = new ReferralModel($this->db);
                            $referralModel->createPendingReferral($referrerId, $newStudentId);
                        }
                        header('Location: welcome.php');
                        exit;
                    }
                }
            }
        }
        include __DIR__ . '/../views/self_register.php';
    }
}
