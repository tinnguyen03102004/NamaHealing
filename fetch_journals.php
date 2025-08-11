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
    $stmt = $db->prepare('SELECT meditation_at, created_at, content, teacher_reply, replied_at FROM journals WHERE user_id = ?');
    $stmt->execute([$user_id]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $messages = [];
    foreach ($rows as $r) {
        $messages[] = [
            'created_at' => $r['created_at'],
            'role' => 'student',
            'content' => date('d/m/Y', strtotime($r['meditation_at'])) . ': ' . $r['content'],
        ];
        if (!empty($r['teacher_reply'])) {
            $messages[] = [
                'created_at' => $r['replied_at'],
                'role' => 'teacher',
                'content' => $r['teacher_reply'],
            ];
        }
    }
    usort($messages, fn($a, $b) => strtotime($a['created_at']) <=> strtotime($b['created_at']));

    echo json_encode(['messages' => $messages], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    echo json_encode(['error' => 'db_error']);
}
