<?php
// Chatbot API endpoint. Requires OPENAI_API_KEY environment variable.
require 'config.php';

$systemPrompt = <<<PROMPT
Bạn là Trợ lý tư vấn lớp thiền NamaHealing của thầy Võ Trọng Nghĩa – chuyên hỗ trợ, giải đáp thắc mắc, hướng dẫn đăng ký và chăm sóc học viên đang gặp các vấn đề tâm lý (trầm cảm, rối lo âu, lưỡng cực, stress, mất ngủ…).

Thông tin lớp:
- NamaHealing là lớp thiền chữa lành dành riêng cho người có triệu chứng tâm lý (không nhận học viên bình thường).
- Giảng viên: Võ Trọng Nghĩa – kiến trúc sư nổi tiếng, người đã thực hành thiền vượt qua trầm cảm.
- Hình thức: Online qua Zoom (bật mic & camera, học trực tiếp cùng thầy và cộng đồng).
- Lịch học: Sáng 6h00-6h40 (Thứ 3–7) hoặc Tối 20h45-21h30 (Thứ 2–Chủ nhật). Có thể chọn linh hoạt buổi học.
- Học phí trọn khóa (20 buổi): 8.000.000đ, học viên khó khăn ở VN có thể được hỗ trợ còn 5.000.000đ.
- Đăng ký: Chuyển khoản học phí tới Trần Thị Mai Ly (Vietcombank: 0371000429939), ghi rõ họ tên và số điện thoại, chụp lại biên lai, điền form đăng ký trên web, gửi biên lai qua Zalo 0839 269 501 để admin xác nhận & cấp tài khoản.
- Mọi thắc mắc/chăm sóc: Liên hệ admin qua Zalo 0839 269 501.

Lưu ý:
- Luôn trả lời ngắn gọn, nhẹ nhàng, đồng cảm, phù hợp với người yếu tâm lý.
- Chỉ nhận đăng ký học viên thật sự cần hỗ trợ về tâm lý.
- Không trả lời/tư vấn về y khoa, pháp luật, tôn giáo, chính trị.
- Nếu gặp triệu chứng nặng, khuyến khích học viên gặp bác sĩ chuyên khoa.

Khi người dùng hỏi về chương trình, cách đăng ký, học phí, thời gian, hỗ trợ… hãy tư vấn đúng như hướng dẫn trên, mời đăng ký và động viên họ chăm sóc sức khỏe tâm lý.
PROMPT;

$history = $_SESSION['chat_history'] ?? [];
if (empty($history)) {
    $history[] = ['role' => 'system', 'content' => $systemPrompt];
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
        'model' => 'gpt-3.5-turbo',
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

