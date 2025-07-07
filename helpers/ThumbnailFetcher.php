<?php

class ThumbnailFetcher {
    public static function get($url) {
        $context = stream_context_create([
            'http' => [
                'timeout' => 7,
                'user_agent' => 'Mozilla/5.0'
            ]
        ]);
        $html = @file_get_contents($url, false, $context);
        if ($html === false) {
            return null;
        }
        if (preg_match('/<meta[^>]+property=[\'\"]og:image[\'\"][^>]+content=[\'\"]([^\'\"]+)[\'\"][^>]*>/i', $html, $m)) {
            return $m[1];
        }
        if (preg_match('/<meta[^>]+name=[\'\"]twitter:image[\'\"][^>]+content=[\'\"]([^\'\"]+)[\'\"][^>]*>/i', $html, $m)) {
            return $m[1];
        }
        if (preg_match('/<img[^>]+src=[\'\"]([^\'\"]+)[\'\"]/i', $html, $m)) {
            return $m[1];
        }
        return null;
    }
}
