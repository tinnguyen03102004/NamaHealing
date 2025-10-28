<?php
define('REQUIRE_LOGIN', true);
define('CSRF_JSON_RESPONSE', true);
require 'config.php';

if (($_SESSION['role'] ?? '') !== 'teacher') {
    http_response_code(403);
    exit;
}

header('Content-Type: application/json; charset=utf-8');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'invalid_method']);
    exit;
}

csrf_check($_POST['csrf_token'] ?? null);
$user_id = (int)($_POST['user_id'] ?? 0);
$reply = trim($_POST['reply'] ?? '');
if ($user_id <= 0 || $reply === '') {
    echo json_encode(['error' => 'invalid_input']);
    exit;
}

try {
    $db->beginTransaction();
    $stmt = $db->prepare('SELECT id FROM journals WHERE user_id = ? AND teacher_reply IS NULL ORDER BY created_at DESC LIMIT 1');
    $stmt->execute([$user_id]);
    $journal_id = $stmt->fetchColumn();
    if ($journal_id) {
        $up = $db->prepare('UPDATE journals SET teacher_reply = ?, replied_at = NOW(), seen_at = NOW() WHERE id = ?');
        $up->execute([$reply, $journal_id]);
        $seen = $db->prepare('UPDATE journals SET seen_at = NOW() WHERE user_id = ? AND seen_at IS NULL');
        $seen->execute([$user_id]);
        $db->commit();
        echo json_encode(['success' => true]);
    } else {
        $db->rollBack();
        echo json_encode(['error' => 'no_journal']);
    }
} catch (Throwable $e) {
    if ($db->inTransaction()) $db->rollBack();
    echo json_encode(['error' => 'db_error']);
}
