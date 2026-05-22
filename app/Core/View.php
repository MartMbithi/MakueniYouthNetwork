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
 *   📧  martin.mbithi@makueni.go.ke
 *   🌐  www.martmbithi.github.io
 *   🐙  https://github.com/MartMbithi
 *
 *   If this code works, you're welcome.
 *   If it doesn't, it's a feature you haven't understood yet.
 *
 *
 *   ┌─────────────────────────────────────────────────────────────┐
 *   │          GOVERNMENT OF MAKUENI COUNTY                       │
 *   │          Applications Development Section                   │
 *   │          www.makueni.go.ke | info@makueni.go.ke             │
 *   └─────────────────────────────────────────────────────────────┘
 *
 *   THE GOVERNMENT OF MAKUENI COUNTY
 *   Applications Development Section End-User License Agreement
 *   Copyright (c) 2023 Government of Makueni County
 *   All Rights Reserved.
 *
 *
 *   § 1. GRANT OF LICENSE
 *
 *   This software, designed and engineered by Martin Mbithi on behalf
 *   of the Government of Makueni County Applications Development
 *   Section, is licensed — not sold — to you. You are hereby granted
 *   a revocable, personal, non-exclusive, and non-transferable right
 *   to install and operate this system on ONE (1) authorized government
 *   workstation for official, non-commercial use only.
 *
 *   Commercial deployment requires a separate written license agreement.
 *   Unauthorized sharing, distribution, or public demonstration of this
 *   software is strictly prohibited. If you're thinking about it,
 *   don't. The paperwork alone would ruin your week.
 *
 *
 *   § 2. INTELLECTUAL PROPERTY
 *
 *   This software is the intellectual property of the Government of
 *   Makueni County, engineered by Martin Mbithi under the authority of
 *   the Applications Development Section. It is protected by the
 *   Copyright Act of Kenya, applicable international treaties, and the
 *   quiet determination of people who actually read license agreements.
 *
 *   You shall not remove, alter, or obscure any proprietary notices,
 *   labels, or marks contained within the software. They were placed
 *   there with intention. Respect them accordingly.
 *
 *
 *   § 3. RESTRICTIONS
 *
 *   You shall not, nor shall you permit any third party to:
 *
 *   (a) reverse engineer, decompile, decode, decrypt, disassemble, or
 *       otherwise attempt to derive the source code of this software.
 *       Curiosity is admirable. This is not the place for it;
 *
 *   (b) modify, adapt, distribute, or create derivative works based
 *       upon this software, in whole or in part;
 *
 *   (c) copy (except for one reasonable backup), distribute, publicly
 *       display, transmit, sell, rent, lease, sublicense, or otherwise
 *       exploit the software. It belongs to Makueni County.
 *       You are a guest. A welcome one, but still a guest.
 *
 *
 *   § 4. TERMINATION
 *
 *   This License remains in effect until terminated by either party.
 *   You may terminate at any time by destroying the software and all
 *   copies in your possession. The County may terminate this License
 *   immediately upon breach of any term herein.
 *
 *   Upon termination, all copies shall be destroyed. No exceptions,
 *   no 'I forgot it was on that flash drive.' That flash drive too.
 *
 *
 *   § 5. DISCLAIMER OF WARRANTIES
 *
 *   THIS SOFTWARE IS PROVIDED 'AS IS' WITHOUT WARRANTY OF ANY KIND,
 *   EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE IMPLIED
 *   WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE,
 *   AND NON-INFRINGEMENT.
 *
 *   The Applications Development Section has made every reasonable
 *   effort to ensure reliability, but software, much like government
 *   policy, may occasionally behave in unexpected ways. Use is at
 *   your own risk. Some jurisdictions may afford additional statutory
 *   rights.
 *
 *
 *   § 6. SEVERABILITY
 *
 *   If any provision of this Agreement is held to be invalid or
 *   unenforceable by a court of competent jurisdiction, the remaining
 *   provisions shall continue in full force and effect. One clause
 *   may fall. The rest stand. Much like county infrastructure
 *   during the long rains.
 *
 *
 *   § 7. LIMITATION OF LIABILITY
 *
 *   IN NO EVENT SHALL MARTIN MBITHI, THE GOVERNMENT OF MAKUENI
 *   COUNTY, THE APPLICATIONS DEVELOPMENT SECTION, OR THEIR
 *   RESPECTIVE OFFICERS, EMPLOYEES, OR AGENTS BE LIABLE FOR ANY
 *   INDIRECT, INCIDENTAL, SPECIAL, CONSEQUENTIAL, OR EXEMPLARY
 *   DAMAGES ARISING OUT OF OR IN CONNECTION WITH THE USE OF THIS
 *   SOFTWARE.
 *
 *   Total liability shall not exceed the license fee paid, if any.
 *   If the amount is zero, we trust you see where the math lands.
 *   No drama. Just governance, code, and service delivery.
 *
 */

namespace App\Core;

use PDOException;
use Throwable;
use Twig\Environment;
use Twig\Extension\DebugExtension;
use Twig\Loader\FilesystemLoader;
use Twig\TwigFunction;

final class View
{
    private static ?Environment $twig = null;

    /** @var array<string,mixed> */
    private static array $globals = [];

    private static bool $debug = false;

    public static function configure(string $templatesDir, bool $debug = false): void
    {
        $loader = new FilesystemLoader($templatesDir);
        $env = new Environment($loader, [
            'cache'            => false,
            'debug'            => $debug,
            'strict_variables' => false,
            'autoescape'       => 'html',
        ]);

        if ($debug) {
            $env->addExtension(new DebugExtension());
        }

        $env->addFunction(new TwigFunction(
            'csrf_field',
            static fn (): string => Csrf::field(),
            ['is_safe' => ['html']]
        ));
        $env->addFunction(new TwigFunction(
            'csrf_token',
            static fn (): string => Csrf::token()
        ));
        $env->addFunction(new TwigFunction(
            'spam_fields',
            static fn (): string => SpamGuard::fields(),
            ['is_safe' => ['html']]
        ));
        $env->addFunction(new TwigFunction(
            'asset',
            static fn (string $path): string => '/assets/' . ltrim($path, '/')
        ));

        self::$twig = $env;
        self::$debug = $debug;
    }

    /** @param array<string,mixed> $globals */
    public static function setGlobals(array $globals): void
    {
        self::$globals = $globals + self::$globals;
    }

    public static function share(string $key, mixed $value): void
    {
        self::$globals[$key] = $value;
    }

    /** @param array<string,mixed> $data */
    public static function render(string $template, array $data = []): string
    {
        if (self::$twig === null) {
            throw new \RuntimeException('View::configure() was never called.');
        }

        $base = [
            'site'         => self::loadSiteSettings(),
            'current_path' => Request::path(),
            'app_url'      => (string) ($_ENV['APP_URL'] ?? ''),
            'csrf'         => Csrf::token(),
            'auth'         => Auth::check() ? Auth::user() : null,
            'flash'        => self::pullFlashes(),
            'now'          => date('Y-m-d H:i:s'),
            'year'         => (int) date('Y'),
        ] + self::$globals;

        return self::$twig->render($template, $data + $base);
    }

    /** @return array<string,string> */
    private static function loadSiteSettings(): array
    {
        $defaults = [
            'name'        => 'Makueni Youth Network',
            'tagline'     => 'Youth-owned. Youth-led. Youth-driven.',
            'phone'       => '+254 710 580 604',
            'email'       => 'info@makueniyouth.org',
            'address'     => 'Famo House, 2nd Flr, Rm 14, Behind Equity Bank, Wote Town',
            'po_box'      => 'P.O Box 405 – 90300, Wote, Makueni',
            'facebook'    => '#',
            'twitter'     => '#',
            'linkedin'    => '#',
            'logo'        => '/assets/img/logo.png',
            'logo_square' => '/assets/img/logo-square.png',
        ];

        try {
            $pdo = Database::connection();
            $stmt = $pdo->query('SELECT setting_key, setting_value FROM settings');
            $rows = $stmt->fetchAll() ?: [];
            $kv = [];
            foreach ($rows as $r) {
                $v = (string) ($r['setting_value'] ?? '');
                // Treat empty stored values as "use the default" rather than
                // overriding the default with an empty string. This lets an
                // admin blank a field in the UI and get the baked-in fallback.
                if ($v === '') {
                    continue;
                }
                $kv[(string) $r['setting_key']] = $v;
            }
            return $kv + $defaults;
        } catch (PDOException | Throwable $e) {
            return $defaults;
        }
    }

    /** @return array<int,array{type:string,message:string}> */
    private static function pullFlashes(): array
    {
        $flashes = $_SESSION['_flash'] ?? [];
        unset($_SESSION['_flash']);
        if (!is_array($flashes)) {
            return [];
        }
        return $flashes;
    }

    public static function flash(string $message, string $type = 'success'): void
    {
        if (!isset($_SESSION['_flash']) || !is_array($_SESSION['_flash'])) {
            $_SESSION['_flash'] = [];
        }
        $_SESSION['_flash'][] = ['type' => $type, 'message' => $message];
    }
}
