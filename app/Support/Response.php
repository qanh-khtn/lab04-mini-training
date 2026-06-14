<?php
declare(strict_types=1);

namespace App\Support;

class Response
{
    public static function view(string $view, array $data = [], int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: text/html; charset=UTF-8');
        extract($data, EXTR_SKIP);

        if (!is_file(\view_path($view))) {
            self::notFound();
            return;
        }

        require \view_path('layout');
        exit;
    }

    public static function json(int $status, array $data): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    public static function redirect(string $url, int $status = 302): void
    {
        \redirect($url, $status);
    }

    public static function notFound(string $message = 'Không tìm thấy trang bạn yêu cầu.'): void
    {
        self::view('errors/404', [
            'title' => 'Không tìm thấy trang',
            'message' => $message,
        ], 404);
    }

    public static function methodNotAllowed(array $allowedMethods = []): void
    {
        if ($allowedMethods !== []) {
            header('Allow: ' . implode(', ', $allowedMethods));
        }

        self::view('errors/405', [
            'title' => 'Phương thức không được hỗ trợ',
            'allowedMethods' => $allowedMethods,
        ], 405);
    }
}
