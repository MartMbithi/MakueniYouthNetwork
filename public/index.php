<?php

declare(strict_types=1);

use App\Core\Router;

$rootDir = dirname(__DIR__);

require $rootDir . '/vendor/autoload.php';

/** @var array $config */
$config = require $rootDir . '/config/config.php';

$isLocal = ($config['app']['env'] ?? 'production') === 'local';

error_reporting(E_ALL);
ini_set('display_errors', $isLocal ? '1' : '0');
ini_set('log_errors', '1');
ini_set('error_log', $rootDir . '/storage/logs/app.log');

set_exception_handler(static function (\Throwable $e) use ($isLocal, $rootDir): void {
    error_log('[' . date('c') . '] ' . $e::class . ': ' . $e->getMessage()
        . ' in ' . $e->getFile() . ':' . $e->getLine() . PHP_EOL . $e->getTraceAsString());
    http_response_code(500);
    header('Content-Type: text/plain; charset=utf-8');
    if ($isLocal) {
        echo $e::class . ': ' . $e->getMessage() . PHP_EOL
            . $e->getFile() . ':' . $e->getLine() . PHP_EOL
            . $e->getTraceAsString();
    } else {
        echo 'Server error';
    }
});

if (session_status() !== PHP_SESSION_ACTIVE) {
    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');

    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'domain'   => '',
        'secure'   => $isHttps,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_name('myn_session');
    session_start();
}

$router = new Router();

require $rootDir . '/routes/web.php';
require $rootDir . '/routes/admin.php';

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$uri    = $_SERVER['REQUEST_URI'] ?? '/';
$path   = parse_url($uri, PHP_URL_PATH) ?: '/';

$router->dispatch($method, $path);
