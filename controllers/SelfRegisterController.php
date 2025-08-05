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
            csrf_check($_POST['csrf_token'] ?? null);
            $name  = trim($_POST['full_name'] ?? '');
            $phone = trim($_POST['phone'] ?? '');
            $pass  = $_POST['password'] ?? '';
            if (!$name || !$phone || !$pass) {
                $err = 'Vui lòng nhập đầy đủ thông tin';
            } else {
                $model = new UserModel($this->db);
                if ($model->findByEmail($phone)) {
                    $err = 'Số điện thoại đã được sử dụng';
                } else {
                    $model->createStudent($name, $phone, $pass);
                    header('Location: welcome.php');
                    exit;
                }
            }
        }
        include __DIR__ . '/../views/self_register.php';
    }
}
