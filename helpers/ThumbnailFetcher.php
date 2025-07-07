<?php
use GuzzleHttp\Client;

class ThumbnailFetcher {
    public static function get($url) {
        try {
            $client = new Client(['timeout' => 7]);
            $res = $client->get($url);
            $html = (string)$res->getBody();
            // Find og:image
            if (preg_match('/<meta[^>]+property=[\'\"]og:image[\'\"][^>]+content=[\'\"]([^\'\"]+)[\'\"][^>]*>/i', $html, $m)) {
                return $m[1];
            }
            // Find twitter:image
            if (preg_match('/<meta[^>]+name=[\'\"]twitter:image[\'\"][^>]+content=[\'\"]([^\'\"]+)[\'\"][^>]*>/i', $html, $m)) {
                return $m[1];
            }
            // Fallback: first <img>
            if (preg_match('/<img[^>]+src=[\'\"]([^\'\"]+)[\'\"]/i', $html, $m)) {
                return $m[1];
            }
        } catch (\Exception $e) {
            return null;
        }
        return null;
    }
}
