<?php
namespace helpers;

class Response {
    public static function view(string $view, array $data = [], int $status = 200): void {
        http_response_code($status);
        extract($data, EXTR_SKIP);
        $file = dirname(__DIR__) . '/views/' . $view . '.php';
        if (!file_exists($file)) {
            echo "View not found";
            return;
        }
        require $file;
    }

    public static function redirect(string $url): void {
        header('Location: ' . $url);
        exit;
    }

    public static function json(array $payload, int $status = 200): void {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($payload, JSON_UNESCAPED_UNICODE);
        exit;
    }
}
