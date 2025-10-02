<?php
define('REQUIRE_LOGIN', true);
require 'config.php';
require_once __DIR__ . '/helpers/Notifications.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || $_SESSION['role'] !== 'student') {
    http_response_code(400);
    echo json_encode(['status' => 'error']);
    exit;
}

csrf_check($_POST['csrf_token'] ?? null);
$uid = $_SESSION['uid'];

notifications_setup($db);
$notificationId = isset($_POST['notification_id']) ? (int)$_POST['notification_id'] : 0;

if ($notificationId > 0) {
    notifications_mark($db, $uid, [$notificationId]);
} else {
    notifications_mark_all($db, $uid);
}

echo json_encode(['status' => 'ok']);
