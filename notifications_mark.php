<?php
define('REQUIRE_LOGIN', true);
require 'config.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || $_SESSION['role'] !== 'student') {
    http_response_code(400);
    echo json_encode(['status' => 'error']);
    exit;
}

csrf_check($_POST['csrf_token'] ?? null);
$uid = $_SESSION['uid'];

// Ensure tables exist
$db->exec("CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    message TEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
)");
$db->exec("CREATE TABLE IF NOT EXISTS notification_reads (
    notification_id INT NOT NULL,
    user_id INT NOT NULL,
    read_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (notification_id, user_id)
)");

// Fetch unread notifications
$stmt = $db->prepare("SELECT n.id FROM notifications n
    LEFT JOIN notification_reads r ON n.id = r.notification_id AND r.user_id = ?
    WHERE r.notification_id IS NULL");
$stmt->execute([$uid]);
$ids = $stmt->fetchAll(PDO::FETCH_COLUMN);

if ($ids) {
    $insert = $db->prepare("INSERT IGNORE INTO notification_reads (notification_id, user_id) VALUES (?, ?)");
    foreach ($ids as $nid) {
        $insert->execute([$nid, $uid]);
    }
}

echo json_encode(['status' => 'ok']);
