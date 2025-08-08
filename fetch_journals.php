<?php
define('REQUIRE_LOGIN', true);
require 'config.php';

if (($_SESSION['role'] ?? '') !== 'teacher') {
    http_response_code(403);
    exit;
}

header('Content-Type: application/json; charset=utf-8');
$student_id = (int)($_GET['student_id'] ?? 0);
if ($student_id <= 0) {
    echo json_encode(['error' => 'invalid_id']);
    exit;
}

try {
    $stmt = $db->prepare('SELECT id, meditation_at, content, teacher_reply, replied_at FROM journals WHERE student_id = ? ORDER BY meditation_at ASC');
    $stmt->execute([$student_id]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['journals' => $rows], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    echo json_encode(['error' => 'db_error']);
}
