<?php

declare(strict_types=1);

$rootDir = dirname(__DIR__);

if (!file_exists($rootDir . '/.env')) {
    throw new RuntimeException(
        'Missing .env file at ' . $rootDir . '/.env — copy .env.example and fill it in.'
    );
}

$dotenv = Dotenv\Dotenv::createImmutable($rootDir);
$dotenv->load();
$dotenv->required([
    'APP_ENV', 'APP_URL',
    'DB_HOST', 'DB_NAME', 'DB_USER',
]);

$env = static function (string $key, ?string $default = null): ?string {
    $value = $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key);
    if ($value === false || $value === null || $value === '') {
        return $default;
    }
    return (string) $value;
};

return [
    'app' => [
        'env'   => $env('APP_ENV', 'production'),
        'url'   => $env('APP_URL', 'http://localhost'),
        'key'   => $env('APP_KEY', ''),
        'debug' => $env('APP_ENV') === 'local',
    ],
    'db' => [
        'host'    => $env('DB_HOST', '127.0.0.1'),
        'name'    => $env('DB_NAME'),
        'user'    => $env('DB_USER'),
        'pass'    => $env('DB_PASS', ''),
        'charset' => 'utf8mb4',
    ],
    'mail' => [
        'host' => $env('MAIL_HOST'),
        'port' => (int) ($env('MAIL_PORT', '587') ?? 587),
        'user' => $env('MAIL_USER'),
        'pass' => $env('MAIL_PASS'),
        'from' => $env('MAIL_FROM', 'no-reply@makueniyouth.org'),
    ],
    'mpesa' => [
        'env'             => $env('MPESA_ENV', 'sandbox'),
        'consumer_key'    => $env('MPESA_CONSUMER_KEY'),
        'consumer_secret' => $env('MPESA_CONSUMER_SECRET'),
        'shortcode'       => $env('MPESA_SHORTCODE'),
        'passkey'         => $env('MPESA_PASSKEY'),
        'callback_url'    => $env('MPESA_CALLBACK_URL'),
    ],
];
