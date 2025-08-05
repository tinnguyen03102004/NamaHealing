<?php
namespace NamaHealing\Controllers;

use PDO;
use NamaHealing\Models\UserModel;

class LoginController {
    private PDO $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    public function handle(): void {
        $err = "";
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_check($_POST['csrf_token'] ?? null);
            $identifier = $_POST['email'] ?? '';
            $pass       = $_POST['password'] ?? '';
            $model      = new UserModel($this->db);
            $user       = $model->findByEmailOrPhone($identifier);
            if ($user && password_verify($pass, $user['password'])) {
                session_regenerate_id(true);
                $_SESSION['uid']  = $user['id'];
                $_SESSION['role'] = $user['role'];
                header('Location: ' . ($user['role'] === 'admin' ? 'admin.php' : 'dashboard.php'));
                exit;
            }
            $err = __('login_error');
        }
        include __DIR__ . '/../views/login.php';
    }
}
