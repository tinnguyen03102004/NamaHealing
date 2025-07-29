<?php
namespace NamaHealing\Helpers;

class ThumbnailFetcher {
    public static function get($url) {
        $context = stream_context_create([
            'http' => [
                'timeout' => 7,
                'follow_location' => true,
                'user_agent'  => 'Mozilla/5.0'
            ]
        ]);

        $html = @file_get_contents($url, false, $context);
        if ($html !== false) {
            $patterns = [
                // prefer secure og:image if available
                '/<meta[^>]+property=[\'\"]og:image:secure_url[\'\"][^>]+content=[\'\"]([^\'\"]+)[\'\"][^>]*>/i',
                '/<meta[^>]+property=[\'\"]og:image[\'\"][^>]+content=[\'\"]([^\'\"]+)[\'\"][^>]*>/i',
                '/<meta[^>]+name=[\'\"]twitter:image[\'\"][^>]+content=[\'\"]([^\'\"]+)[\'\"][^>]*>/i',
                '/<link[^>]+rel=[\'\"]image_src[\'\"][^>]+href=[\'\"]([^\'\"]+)[\'\"][^>]*>/i',
                '/<img[^>]+data-src=[\'\"]([^\'\"]+)[\'\"]/i',
                '/<img[^>]+src=[\'\"]([^\'\"]+)[\'\"]/i'
            ];

            foreach ($patterns as $pattern) {
                if (preg_match($pattern, $html, $m)) {
                    return $m[1];
                }
            }
        }

        // Fallback: use screenshot service if no image was found or page fetch failed
        return 'https://image.thum.io/get/cover/' . urlencode($url);
    }
}

