<?php
header('Content-Type: application/json');
$url = $_GET['url'] ?? '';
if (!$url) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing url']);
    exit;
}
if (!filter_var($url, FILTER_VALIDATE_URL)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid url']);
    exit;
}
$context = stream_context_create([
    'http' => [
        'follow_location' => true,
        'timeout' => 5,
        'user_agent' => 'Mozilla/5.0'
    ]
]);
$html = @file_get_contents($url, false, $context);
if ($html === false) {
    http_response_code(500);
    echo json_encode(['error' => 'Fetch failed']);
    exit;
}
libxml_use_internal_errors(true);
$doc = new DOMDocument();
$doc->loadHTML($html);
$xpath = new DOMXPath($doc);
$meta = function(string $name) use ($xpath): string {
    $nodes = $xpath->query("//meta[@property='$name' or @name='$name']");
    return $nodes->length ? trim($nodes->item(0)->getAttribute('content')) : '';
};
$title = $meta('og:title');
if (!$title && $doc->getElementsByTagName('title')->length) {
    $title = trim($doc->getElementsByTagName('title')->item(0)->textContent);
}
$description = $meta('og:description');
if (!$description) {
    $description = $meta('description');
}
$image = $meta('og:image');
$host = parse_url($url, PHP_URL_HOST);
if (!$image) {
    $scheme = parse_url($url, PHP_URL_SCHEME);
    $image = $scheme . '://' . $host . '/favicon.ico';
}
$data = [
    'url' => $url,
    'title' => $title,
    'description' => $description,
    'image' => $image,
    'domain' => $host
];

echo json_encode($data);

