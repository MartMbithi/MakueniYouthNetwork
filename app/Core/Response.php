<?php

declare(strict_types=1);

namespace App\Core;

final class Response
{
    public static function html(string $body, int $status = 200): void
    {
        if (!headers_sent()) {
            http_response_code($status);
            header('Content-Type: text/html; charset=utf-8');
        }
        echo $body;
    }

    public static function redirect(string $to, int $status = 302): void
    {
        if (!headers_sent()) {
            http_response_code($status);
            header('Location: ' . $to);
        }
        exit;
    }

    /** @param array<mixed> $data */
    public static function json(array $data, int $status = 200): void
    {
        if (!headers_sent()) {
            http_response_code($status);
            header('Content-Type: application/json; charset=utf-8');
        }
        echo json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    public static function notFound(): void
    {
        if (!headers_sent()) {
            http_response_code(404);
            header('Content-Type: text/html; charset=utf-8');
        }
        try {
            echo View::render('public/404.twig', ['title' => 'Page not found']);
        } catch (\Throwable $e) {
            error_log('[404] template render failed: ' . $e->getMessage());
            echo '<!doctype html><meta charset="utf-8"><title>Not found</title>'
                . '<h1>404 — Page not found</h1>';
        }
    }

    public static function serverError(\Throwable $e, bool $debug = false): void
    {
        if (!headers_sent()) {
            http_response_code(500);
            header('Content-Type: text/html; charset=utf-8');
        }
        error_log('[500] ' . $e::class . ': ' . $e->getMessage()
            . ' in ' . $e->getFile() . ':' . $e->getLine());
        try {
            echo View::render('public/500.twig', [
                'title' => 'Server error',
                'debug' => $debug,
                'error' => $debug ? $e : null,
            ]);
        } catch (\Throwable $renderErr) {
            echo '<!doctype html><meta charset="utf-8"><title>Server error</title>'
                . '<h1>500 — Server error</h1>';
            if ($debug) {
                echo '<pre>' . htmlspecialchars($e::class . ': ' . $e->getMessage()
                    . PHP_EOL . $e->getTraceAsString()) . '</pre>';
            }
        }
    }
}
