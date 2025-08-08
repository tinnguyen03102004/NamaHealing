<?php
define('REQUIRE_LOGIN', true);
require 'config.php';
if (($_SESSION['role'] ?? '') !== 'teacher') {
    header('Location: teacher_login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check($_POST['csrf_token'] ?? null);
    $journal_id = (int)($_POST['journal_id'] ?? 0);
    $student_id = (int)($_POST['student_id'] ?? 0);
    $reply = trim($_POST['reply'] ?? '');
    if ($journal_id > 0 && $student_id > 0 && $reply !== '') {
        $stmt = $db->prepare("UPDATE journals SET teacher_reply = ?, reply_at = NOW() WHERE id = ? AND user_id = ?");
        $stmt->execute([$reply, $journal_id, $student_id]);
    }
    header('Location: journal.php?student_id=' . $student_id);
    exit;
}
header('Location: teacher_dashboard.php');
