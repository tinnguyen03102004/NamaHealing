<?php
namespace NamaHealing\Helpers;

class Recaptcha {
    public static function verify(string $token): bool {
        $secret = $_ENV['RECAPTCHA_SECRET'] ?? '';
        if (!$secret || !$token) {
            return false;
        }
        $resp = @file_get_contents('https://www.google.com/recaptcha/api/siteverify?secret='.
            urlencode($secret).'&response='.urlencode($token));
        if (!$resp) return false;
        $data = json_decode($resp, true);
        return !empty($data['success']) && ($data['score'] ?? 0) >= 0.5;
    }
}
