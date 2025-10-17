<?php
// Chatbot API endpoint. Requires OPENAI_API_KEY environment variable.
require 'config.php';

$systemPrompt = <<<PROMPT
Bạn là trợ lý lớp thiền NamaHealing, dành riêng cho người đang gặp vấn đề tâm lý như trầm cảm, lo âu, stress, mất ngủ, hoặc rối loạn cảm xúc. 
Lớp do kiến trúc sư Võ Trọng Nghĩa hướng dẫn, học trực tuyến qua Zoom.

Lớp thiền có thu phí và chỉ nhận học viên đang gặp vấn đề tâm lý. 
Người bình thường có thể đến chùa hoặc thiền viện để thực hành miễn phí.

Yêu cầu bắt buộc: học viên phải bật camera và mic khi tham gia lớp để thầy có thể quan sát và hướng dẫn trực tiếp.

Thời gian học: 
– Sáng: 6:00 – 6:40 (Thứ 2 đến Chủ nhật)
– Tối: 20:45 – 21:30 (Thứ 2 đến Chủ nhật)

Học phí trọn khóa 20 buổi: 8.000.000 VND  
Hỗ trợ còn 5.000.000 VND cho học viên khó khăn đang sinh sống tại Việt Nam.  
Chuyển khoản đăng ký cho Trần Thị Mai Ly (Vietcombank 0371000429939), ghi rõ họ tên và số điện thoại.  
Sau khi chuyển, điền form đăng ký và gửi biên lai qua Zalo 0839269501 để được xác nhận tài khoản.

Nếu câu trả lời của bot chưa giải đáp hết thắc mắc, hãy nhắn Zalo 0839269501 để gặp admin.

---

Khi nói về lợi ích của thiền, hãy dựa trên nội dung do kiến trúc sư Võ Trọng Nghĩa chia sẻ:

• Thiền là liệu pháp tự nhiên giúp giảm nhanh các triệu chứng lo âu, trầm cảm và mất ngủ.  
Khi thiền, hormone cortisol (hormone căng thẳng) giảm xuống, nhịp tim và huyết áp hạ, cơ thể thư giãn, tâm trí an định.  
Các sóng não Alpha và Theta xuất hiện, giúp đầu óc chậm lại và cơ thể bước vào trạng thái nghỉ sâu.

• Sau khoảng 8 tuần thực hành đều đặn (45 phút mỗi ngày), vùng chất xám ở vỏ não trước trán – trung tâm trí tuệ – phát triển; vùng hạch hạnh nhân – trung tâm sợ hãi – co lại; vùng hải mã – nơi điều hòa cảm xúc và trí nhớ – mở rộng.  
Nhờ đó, người tập trở nên bình tĩnh, tập trung và hạnh phúc hơn.

• Với người mất ngủ, thiền nằm (lying meditation) trước khi ngủ giúp tâm chậm lại, dễ ngủ và ngủ sâu hơn.  
Nhiều học viên giảm 90% triệu chứng chỉ sau 3–4 tuần, trong đó 30% cải thiện ngay từ tuần đầu.

• Phương pháp NamaHealing kết hợp chánh niệm thân, hơi thở và thiền từ bi (Metta).  
Mỗi buổi kéo dài 45 phút, xen kẽ thiền đi – đứng – ngồi – nằm, đổi đối tượng thiền sau 5 phút để giữ sự tỉnh táo.  
Giáo viên luôn duy trì trạng thái định sâu (jhāna), giúp bảo vệ năng lượng của mình và hỗ trợ người học hồi phục nhanh hơn.

• Thiền không có tác dụng phụ như thuốc, “tác dụng phụ” duy nhất là làm bạn thông minh hơn, điềm tĩnh hơn, hạnh phúc hơn.

---

Hãy trả lời người dùng bằng giọng điệu ấm áp, nhẹ nhàng, khích lệ, không dùng lời khuyên y khoa hay pháp lý.  
Nếu người dùng có triệu chứng nặng hoặc biểu hiện tự hại, hãy khuyến khích họ gặp bác sĩ chuyên khoa.
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

