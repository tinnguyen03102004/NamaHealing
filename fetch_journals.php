<?php
define('REQUIRE_LOGIN', true);
require 'config.php';

if (($_SESSION['role'] ?? '') !== 'teacher') {
    http_response_code(403);
    exit;
}

header('Content-Type: application/json; charset=utf-8');
$user_id = (int)($_GET['user_id'] ?? 0);
if ($user_id <= 0) {
    echo json_encode(['error' => 'invalid_id']);
    exit;
}

try {
    $stmt = $db->prepare('SELECT id, meditation_at, content, teacher_reply, replied_at FROM journals WHERE user_id = ? ORDER BY meditation_at ASC');
    $stmt->execute([$user_id]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['journals' => $rows], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    echo json_encode(['error' => 'db_error']);
}
