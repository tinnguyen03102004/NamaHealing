<?php
class MetaFetcher {
    public static function fetchMetaFromUrl(string $url): array {
        $context = stream_context_create([
            'http' => [
                'follow_location' => true,
                'timeout' => 5,
                'user_agent' => 'Mozilla/5.0'
            ]
        ]);
        $html = @file_get_contents($url, false, $context);
        if ($html === false) {
            return [];
        }
        libxml_use_internal_errors(true);
        $doc = new DOMDocument();
        $doc->loadHTML($html);
        $xpath = new DOMXPath($doc);
        $meta = function(string $name) use ($xpath): string {
            $nodes = $xpath->query("//meta[@property='$name' or @name='$name']");
            $node = $nodes->item(0);
            return $node instanceof DOMElement ? trim($node->getAttribute('content')) : '';
        };
        $title = $meta('og:title');
        if ($title === '' && $doc->getElementsByTagName('title')->length) {
            $title = trim($doc->getElementsByTagName('title')->item(0)->textContent);
        }
        $description = $meta('og:description');
        if ($description === '') {
            $description = $meta('description');
        }
        $image = '';
        foreach (['og:image:secure_url','og:image:url','og:image','twitter:image','twitter:image:src','image'] as $p) {
            $image = $meta($p);
            if ($image !== '') break;
        }
        if ($image === '') {
            $nodes = $xpath->query("//link[@rel='image_src']");
            if ($nodes->length) {
                $image = trim($nodes->item(0)->getAttribute('href'));
            }
        }
        if ($image === '') {
            $nodes = $xpath->query("//img[@data-src]/@data-src | //img[@src]/@src");
            $node = $nodes->item(0);
            if ($node) {
                $image = trim($node->nodeValue);
            }
        }
        return [
            'title' => $title,
            'description' => $description,
            'image' => $image,
        ];
    }
}

