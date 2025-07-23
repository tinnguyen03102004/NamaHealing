<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function load_lang(): void {
    $lang = $_SESSION['lang'] ?? 'vi';
    if (isset($_GET['lang'])) {
        $chosen = $_GET['lang'];
        if (in_array($chosen, ['vi', 'en', 'uk'], true)) {
            $lang = $chosen;
        } else {
            $lang = 'vi';
        }
        $_SESSION['lang'] = $lang;
    }
    $file = __DIR__ . "/lang/$lang.php";
    $GLOBALS['__lang'] = file_exists($file) ? include $file : [];
}

function __($key): string {
    return $GLOBALS['__lang'][$key] ?? $key;
}

load_lang();

