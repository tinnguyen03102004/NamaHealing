<?php
declare(strict_types=1);

define('REQUIRE_LOGIN', true);
require __DIR__ . '/config.php';

header('Content-Type: application/json; charset=utf-8');

if (($_SESSION['role'] ?? '') !== 'student') {
    http_response_code(403);
    echo json_encode(['error' => 'forbidden']);
    exit;
}

$rawBody = file_get_contents('php://input');
$payload = json_decode($rawBody ?: '[]', true);
if (!is_array($payload)) {
    http_response_code(400);
    echo json_encode(['error' => 'invalid_payload']);
    exit;
}

csrf_check($payload['csrf_token'] ?? null);

$meetingNumber = isset($payload['meetingNumber']) ? preg_replace('/[^0-9]/', '', (string)$payload['meetingNumber']) : '';
if ($meetingNumber === '') {
    http_response_code(400);
    echo json_encode(['error' => 'missing_meeting_number']);
    exit;
}

$role = 0; // 0 = attendee
$sdkKey = trim((string)($_ENV['ZOOM_SDK_CLIENT_ID'] ?? ''));
$sdkSecret = trim((string)($_ENV['ZOOM_SDK_CLIENT_SECRET'] ?? ''));

if ($sdkKey === '' || $sdkSecret === '') {
    http_response_code(503);
    echo json_encode(['error' => 'zoom_sdk_not_configured']);
    exit;
}

$ts = (string)((int)round(microtime(true) * 1000) - 30000);
$msg = $sdkKey . $meetingNumber . $ts . $role;
$hash = hash_hmac('sha256', $msg, $sdkSecret, true);
$hashBase64 = rtrim(strtr(base64_encode($hash), '+/', '-_'), '=');
$signature = rtrim(strtr(base64_encode(implode('.', [$sdkKey, $meetingNumber, $ts, $role, $hashBase64])), '+/', '-_'), '=');

echo json_encode([
    'signature' => $signature,
    'sdkKey' => $sdkKey,
    'ts' => $ts,
]);
