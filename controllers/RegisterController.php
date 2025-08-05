<?php
namespace NamaHealing\Controllers;

use PDO;
use NamaHealing\Models\UserModel;

class RegisterController {
    private PDO $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    public function handle(): void {
        if (!isset($_SESSION['uid']) || $_SESSION['role'] !== 'admin') {
            header('Location: index.php');
            exit;
        }
        $err = "";
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_check($_POST['csrf_token'] ?? null);
            $name   = trim($_POST['full_name'] ?? '');
            $email  = trim($_POST['email'] ?? '');
            $phone  = trim($_POST['phone'] ?? '');
            $pass   = $_POST['password'] ?? '';
            $remain = intval($_POST['remaining'] ?? 0);
            if (!$name || !$email || !$phone || !$pass) {
                $err = __('err_required_fields');
            } else {
                $model = new UserModel($this->db);
                if ($model->findByEmailOrPhone($email) || $model->findByEmailOrPhone($phone)) {
                    $err = __('err_email_exists');
                } else {
                    $model->createStudent($name, $email, $phone, $pass, $remain);
                    header('Location: admin.php');
                    exit;
                }
            }
        }
        include __DIR__ . '/../views/register.php';
    }
}
