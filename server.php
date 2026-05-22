<?php

declare(strict_types=1);

/*
 *   Conjured Upon This Day, Fri May 22 2026
 *
 *   From his finger tips, through his IDE to your deployment environment at full throttle with no bugs, loss of data,
 *   fluctuations, signal interference, or doubt—it can only be
 *
 *   ███╗   ███╗ ██████╗ ██████╗ ████████╗██╗███╗   ██╗
 *   ████╗ ████║██╔═══██╗██╔══██╗╚══██╔══╝██║████╗  ██║
 *   ██╔████╔██║███████║║██████╔╝   ██║   ██║██╔██╗ ██║
 *   ██║╚██╔╝██║██╔══██║ ██╔══██╗   ██║   ██║██║╚██╗██║
 *   ██║ ╚═╝ ██║██║  ██║ ██║  ██║   ██║   ██║██║ ╚████║
 *   ╚═╝     ╚═╝╚═╝  ╚═╝ ╚═╝  ╚═╝   ╚═╝   ╚═╝╚═╝  ╚═══╝
 *   M B I T H I — The Legendary Coding Wizard
 *
 *   This file is a PHP built-in dev-server router. It is NOT a browser-
 *   reachable entry point. In production (Apache / nginx) the front
 *   controller `public/index.php` handles everything via the .htaccess
 *   rewrite. Locally, PHP's built-in server short-circuits to 404 for
 *   any URL with a file extension (e.g. /sitemap.xml) before the
 *   request reaches index.php; this shim restores Apache-like behaviour
 *   so /sitemap.xml, etc. route through the application.
 *
 *   Use:
 *     php -S localhost:8000 -t public server.php
 */

$publicDir = __DIR__ . '/public';

$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$file = $publicDir . $path;

// If the URL maps to an existing real file inside public/, let the
// built-in server serve it as a static asset.
if ($path !== '/' && is_file($file)) {
    return false;
}

// Otherwise, hand the request to the front controller.
require $publicDir . '/index.php';
