<?php
header('Content-Type: text/html; charset=utf-8');

$msg = trim($_POST['message'] ?? '');
if ($msg === '') {
    echo '<div class="flex justify-start mb-2"><div class="bg-gray-200 px-4 py-2 rounded-lg">Vui lòng nhập nội dung.</div></div>';
    exit;
}

$reply = 'Cảm ơn bạn đã đặt câu hỏi: ' . htmlspecialchars($msg, ENT_QUOTES, 'UTF-8');

echo '<div class="flex justify-start mb-2"><div class="bg-gray-200 px-4 py-2 rounded-lg max-w-xs break-words">' . $reply . '</div></div>';

