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

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    echo json_encode(['error' => 'method_not_allowed']);
    exit;
}

$rawBody = file_get_contents('php://input');
$payload = json_decode($rawBody ?: 'null', true);
if (!is_array($payload)) {
    http_response_code(400);
    echo json_encode(['error' => 'invalid_payload']);
    exit;
}

$meetingNumber = isset($payload['meetingNumber']) ? preg_replace('/[^0-9]/', '', (string)$payload['meetingNumber']) : '';
$role = isset($payload['role']) ? (int)$payload['role'] : 0;

if ($meetingNumber === '' || !in_array($role, [0, 1], true)) {
    http_response_code(400);
    echo json_encode(['error' => 'invalid_request']);
    exit;
}

[$sdkKey, $sdkSecret] = (function (): array {
    $key = '';
    $secret = '';

    if (defined('ZOOM_SDK_KEY')) {
        $key = trim((string)ZOOM_SDK_KEY);
    }
    if ($key === '') {
        $key = trim((string)($_ENV['ZOOM_SDK_KEY'] ?? $_ENV['ZOOM_SDK_CLIENT_ID'] ?? ''));
    }

    if (defined('ZOOM_SDK_SECRET')) {
        $secret = trim((string)ZOOM_SDK_SECRET);
    }
    if ($secret === '') {
        $secret = trim((string)($_ENV['ZOOM_SDK_SECRET'] ?? $_ENV['ZOOM_SDK_CLIENT_SECRET'] ?? ''));
    }

    return [$key, $secret];
})();

if ($sdkKey === '' || $sdkSecret === '') {
    http_response_code(503);
    echo json_encode(['error' => 'zoom_sdk_not_configured']);
    exit;
}

$now = time();
$jwtHeader = ['alg' => 'HS256', 'typ' => 'JWT'];
$jwtPayload = [
    'sdkKey'   => $sdkKey,
    'mn'       => $meetingNumber,
    'role'     => $role,
    'iat'      => $now - 30,
    'exp'      => $now + 180,
    'appKey'   => $sdkKey,
    'tokenExp' => $now + 300,
];

$encode = static function ($data): string {
    $json = json_encode($data, JSON_UNESCAPED_SLASHES);
    if ($json === false) {
        throw new RuntimeException('Failed to encode payload');
    }
    return rtrim(strtr(base64_encode($json), '+/', '-_'), '=');
};

try {
    $headerSegment = $encode($jwtHeader);
    $payloadSegment = $encode($jwtPayload);
    $signatureSegment = rtrim(strtr(base64_encode(hash_hmac('sha256', $headerSegment . '.' . $payloadSegment, $sdkSecret, true)), '+/', '-_'), '=');
    $token = $headerSegment . '.' . $payloadSegment . '.' . $signatureSegment;
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => 'signature_failed']);
    exit;
}

echo json_encode([
    'signature' => $token,
    'sdkKey'    => $sdkKey,
]);
