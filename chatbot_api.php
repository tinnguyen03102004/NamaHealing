<?php
// Chatbot API endpoint. Requires OPENAI_API_KEY environment variable.
require 'config.php';

$history = $_SESSION['chat_history'] ?? [];

$input = json_decode(file_get_contents('php://input'), true);
$message = trim($input['message'] ?? '');

if ($message !== '') {
    $history[] = ['role' => 'user', 'content' => $message];
    $apiKey = getenv('OPENAI_API_KEY');

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

