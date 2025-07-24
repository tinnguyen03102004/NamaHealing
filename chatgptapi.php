<?php
// Chatbot API endpoint. Requires OPENAI_API_KEY environment variable.
require 'config.php';

$systemPrompt = <<<PROMPT
Bạn là trợ lý lớp thiền NamaHealing dành riêng cho người gặp vấn đề tâm lý như trầm cảm, lo âu, stress, mất ngủ…
Lớp do kiến trúc sư Võ Trọng Nghĩa hướng dẫn và học trực tuyến qua Zoom. Lớp thiền có thu phí và chỉ nhận học viên đang gặp vấn đề tâm lý.
Người bình thường có thể đến chùa hoặc thiền viện để thực hành miễn phí.
Yêu cầu bắt buộc: phải bật camera và mic khi tham gia lớp học để thầy có thể quan sát và hướng dẫn.
Thời gian: sáng 6:00–6:40 (Thứ 2–Chủ nhật) và tối 20:45–21:30 (Thứ 2–Chủ nhật).
Học phí trọn khóa 20 buổi là 8.000.000 VND, hỗ trợ còn 5.000.000 VND cho học viên khó khăn đang sinh sống tại Việt Nam.
Chuyển khoản đăng ký cho Trần Thị Mai Ly (Vietcombank 0371000429939), ghi họ tên và số điện thoại.
Sau khi chuyển, điền form đăng ký và gửi biên lai qua Zalo 0839269501 để được xác nhận tài khoản.
Nếu câu trả lời của bot không đáp ứng được thắc mắc hãy nhắn Zalo 0839269501 để gặp admin.
Hãy trả lời nhẹ nhàng, động viên, không đưa ra lời khuyên y khoa hay pháp lý; nếu người dùng có triệu chứng nặng thì khuyến khích họ gặp bác sĩ chuyên khoa.
PROMPT;

$history = $_SESSION['chat_history'] ?? [];
if (empty($history)) {
    $history = [
        ['role' => 'system', 'content' => $systemPrompt]
    ];
}

$input = json_decode(file_get_contents('php://input'), true);
$message = trim($input['message'] ?? '');

if ($message !== '') {
    $history[] = ['role' => 'user', 'content' => $message];
   $apiKey = !empty($_ENV['OPENAI_API_KEY']) ? $_ENV['OPENAI_API_KEY'] : getenv('OPENAI_API_KEY');

    if (empty($apiKey)) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Missing API key']);
        exit;
    }

    $payload = json_encode([
        'model' => 'ft:gpt-4o-mini-2024-07-18:vtn-architects::BwmlNlD6',
        'messages' => $history
    ]);

    $ch = curl_init('https://api.openai.com/v1/chat/completions');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $apiKey,
        ],
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $payload,
    ]);
    $response = curl_exec($ch);
    if ($response === false) {
        curl_close($ch);
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Request failed']);
        exit;
    }
    curl_close($ch);

    $reply = '';
    if ($response !== false) {
        $json = json_decode($response, true);
        if (isset($json['choices'][0]['message']['content'])) {
            $reply = $json['choices'][0]['message']['content'];
            $history[] = ['role' => 'assistant', 'content' => $reply];
        }
    }

    $_SESSION['chat_history'] = $history;

    header('Content-Type: application/json');
    echo json_encode(['reply' => $reply]);
    exit;
}

http_response_code(400);

