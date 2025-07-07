<?php
function get_link_metadata(string $url): array {
    if (!filter_var($url, FILTER_VALIDATE_URL)) {
        return [];
    }
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_USERAGENT => 'Mozilla/5.0 (compatible)'
    ]);
    $html = curl_exec($ch);
    curl_close($ch);
    if ($html === false) {
        return [];
    }
    libxml_use_internal_errors(true);
    $doc = new DOMDocument();
    $doc->loadHTML($html);
    libxml_clear_errors();
    $xpath = new DOMXPath($doc);
    $q = fn($query) => ($n = $xpath->query($query)->item(0)) ? trim($n->nodeValue) : '';

    $title = $q("//meta[@property='og:title']/@content");
    $description = $q("//meta[@property='og:description']/@content");
    $image = $q("//meta[@property='og:image']/@content");

    if ($title === '') $title = $q('//title');
    if ($description === '') $description = $q("//meta[@name='description']/@content");
    if ($image === '') {
        $image = $q('//img/@src');
        if ($image === '') {
            $image = $q("//link[contains(@rel,'icon')]/@href");
        }
    }
    if ($image && parse_url($image, PHP_URL_SCHEME) === null) {
        $parts = parse_url($url);
        if (strpos($image, '/') === 0) {
            $image = $parts['scheme'] . '://' . $parts['host'] . $image;
        } else {
            $base = rtrim($url, '/');
            $image = $base . '/' . ltrim($image, '/');
        }
    }
    return [
        'title' => $title,
        'description' => $description,
        'image' => $image,
        'domain' => parse_url($url, PHP_URL_HOST),
        'url' => $url
    ];
}
