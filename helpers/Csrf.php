<?php
namespace helpers;

class Csrf {
    public static function token(): string {
        if (empty($_SESSION['csrf'])) {
            $_SESSION['csrf'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf'];
    }
    public static function check(?string $token): bool {
        return is_string($token) && hash_equals($_SESSION['csrf'] ?? '', $token);
    }
    public static function input(): string {
        $t = self::token();
        return '<input type="hidden" name="_csrf" value="'.$t.'">';
    }
}
