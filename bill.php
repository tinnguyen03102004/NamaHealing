<?php
define('REQUIRE_LOGIN', true);
require 'config.php';

use NamaHealing\Helpers\Mailer;

$uid = $_SESSION['uid'];
$stmt = $db->prepare('SELECT full_name, email FROM users WHERE id=?');
$stmt->execute([$uid]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
$sent = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check($_POST['csrf_token'] ?? null);
    $note = trim($_POST['note'] ?? '');
    $fileInfo = $_FILES['bill'] ?? null;
    $savedName = null;
    if ($fileInfo && $fileInfo['error'] === UPLOAD_ERR_OK) {
        $dir = __DIR__ . '/data/bills';
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        $savedName = time() . '_' . preg_replace('/[^A-Za-z0-9_\.\-]/', '_', $fileInfo['name']);
        move_uploaded_file($fileInfo['tmp_name'], $dir . '/' . $savedName);
    }

    $adminEmail = $_ENV['ADMIN_EMAIL'] ?? 'admin@vtnaa.com';
    $body = '<p>Student: ' . htmlspecialchars($user['full_name']) . ' (' . $user['email'] . ')</p>';
    if ($note) {
        $body .= '<p><strong>Note:</strong><br>' . nl2br(htmlspecialchars($note)) . '</p>';
    }
    if ($savedName) {
        $body .= '<p><strong>File:</strong> ' . htmlspecialchars($savedName) . '</p>';
    }
    Mailer::send($adminEmail, 'Bill request', $body);
    $sent = true;
}

include __DIR__ . '/views/bill.php';
