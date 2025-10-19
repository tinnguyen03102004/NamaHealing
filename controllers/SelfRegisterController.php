<?php
namespace NamaHealing\Controllers;

use PDO;
use NamaHealing\Models\UserModel;

class SelfRegisterController {
    private PDO $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    public function handle(): void {
        $err = "";
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            \csrf_check($_POST['csrf_token'] ?? null);
            $fullName = trim($_POST['full_name'] ?? '');
            $email    = trim($_POST['email'] ?? '');
            $phone    = trim($_POST['phone'] ?? '');
            $phone    = preg_replace('/\D+/', '', $phone);
            if (!$fullName || !$email || !$phone) {
                $err = \__('err_required_fields');
            } else {
                $model = new UserModel($this->db);
                if ($model->findByIdentifier($phone) || $model->findByIdentifier($email)) {
                    $err = \__('err_email_exists');
                } else {
                    $model->createStudent($fullName, $email, $phone);
                    header('Location: welcome.php');
                    exit;
                }
            }
        }
        include __DIR__ . '/../views/self_register.php';
    }
}
