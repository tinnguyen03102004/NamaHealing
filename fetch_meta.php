<?php
require 'config.php';

use NamaHealing\Helpers\MetaFetcher;
header('Content-Type: application/json; charset=utf-8');
$url = $_GET['url'] ?? '';
if ($url === '' || !filter_var($url, FILTER_VALIDATE_URL)) {
    echo json_encode(['error' => 'invalid_url']);
    exit;
}
$data = MetaFetcher::fetchMetaFromUrl($url);
echo json_encode($data, JSON_UNESCAPED_UNICODE);

