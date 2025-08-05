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
            $identifier = $_POST['identifier'] ?? '';
            // Identifier can be email or phone
            $pass  = $_POST['password'] ?? '';
            $model = new UserModel($this->db);
            $user  = $model->findByIdentifier($identifier);
            if ($user && password_verify($pass, $user['password'])) {
                session_regenerate_id(true);
                $_SESSION['uid']  = $user['id'];
                $_SESSION['role'] = $user['role'];
                if ($user['role'] === 'admin') {
                    header('Location: admin.php');
                } else {
                    if (($user['remaining'] ?? 0) > 0) {
                        header('Location: dashboard.php');
                    } else {
                        header('Location: welcome.php');
                    }
                }
                exit;
            }
            $err = __('login_error');
        }
        include __DIR__ . '/../views/login.php';
    }
}
