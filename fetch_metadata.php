<?php
require_once __DIR__ . '/metadata.php';
header('Content-Type: application/json; charset=utf-8');
$url = $_GET['url'] ?? '';
if (!filter_var($url, FILTER_VALIDATE_URL)) {
    echo json_encode(['error' => 'invalid_url']);
    exit;
}
$meta = get_link_metadata($url);
echo json_encode($meta, JSON_UNESCAPED_UNICODE);
