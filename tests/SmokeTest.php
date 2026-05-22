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

namespace Tests;

use App\Core\Csrf;
use App\Core\Database;
use App\Models\Message;
use App\Models\Page;
use App\Models\Partner;
use App\Models\Post;
use App\Models\Program;
use App\Models\Setting;
use App\Models\Stat;
use App\Services\Paystack;
use PHPUnit\Framework\TestCase;

/**
 * MYN smoke test suite — every public route is reachable, key models
 * return data against the seeded database, CSRF tokens reject stale
 * input, Paystack webhook signature verification stops forgeries.
 *
 * These run against the live dev server (port 8765) booted by
 * `php -S localhost:8765 -t public server.php` — the suite skips
 * over network checks if the server is not up.
 */
final class SmokeTest extends TestCase
{
    private const HOST = 'http://localhost:8765';

    private static function serverUp(): bool
    {
        $ch = curl_init(self::HOST . '/');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_NOBODY         => true,
            CURLOPT_CONNECTTIMEOUT => 1,
            CURLOPT_TIMEOUT        => 2,
        ]);
        @curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        return $status >= 200 && $status < 600;
    }

    private function expectStatus(string $path, int $expected): void
    {
        if (!self::serverUp()) {
            $this->markTestSkipped('dev server not up at ' . self::HOST);
        }
        $ch = curl_init(self::HOST . $path);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_TIMEOUT        => 5,
        ]);
        curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        $this->assertSame($expected, $status, "GET {$path} expected {$expected}, got {$status}");
    }

    // ---------------- public routes ----------------

    public function testHomepageOk(): void                      { $this->expectStatus('/', 200); }
    public function testProgramsListOk(): void                  { $this->expectStatus('/programs', 200); }
    public function testProgramDetailOk(): void                 { $this->expectStatus('/programs/advocacy-civic-engagement', 200); }
    public function testProgramUnknown404(): void               { $this->expectStatus('/programs/no-such-program', 404); }
    public function testImpactListOk(): void                    { $this->expectStatus('/impact', 200); }
    public function testImpactPaginationOk(): void              { $this->expectStatus('/impact?page=1', 200); }
    public function testPostDetailOk(): void                    { $this->expectStatus('/impact/bridging-the-gap-youth-leading-change-in-governance', 200); }
    public function testPostUnknown404(): void                  { $this->expectStatus('/impact/no-such-post', 404); }
    public function testEventsOk(): void                        { $this->expectStatus('/events', 200); }
    public function testEventUnknown404(): void                 { $this->expectStatus('/events/no-such-event', 404); }
    public function testDonateOk(): void                        { $this->expectStatus('/donate', 200); }
    public function testVolunteerOk(): void                     { $this->expectStatus('/volunteer', 200); }
    public function testContactOk(): void                       { $this->expectStatus('/contact', 200); }
    public function testSitemapOk(): void                       { $this->expectStatus('/sitemap.xml', 200); }
    public function testAboutPageOk(): void                     { $this->expectStatus('/about', 200); }
    public function testCatchAllUnknown404(): void              { $this->expectStatus('/this-page-does-not-exist', 404); }

    // ---------------- auth guard ----------------

    public function testUnauthAdminRedirects(): void
    {
        if (!self::serverUp()) {
            $this->markTestSkipped('dev server not up');
        }
        $ch = curl_init(self::HOST . '/admin');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_TIMEOUT        => 5,
        ]);
        curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $loc    = curl_getinfo($ch, CURLINFO_REDIRECT_URL);
        curl_close($ch);
        $this->assertSame(302, $status);
        $this->assertStringContainsString('/admin/login', (string) $loc);
    }

    public function testCsrfRejectsPostWithoutToken(): void
    {
        if (!self::serverUp()) {
            $this->markTestSkipped('dev server not up');
        }
        $ch = curl_init(self::HOST . '/contact');
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => http_build_query(['name' => 'x', 'email' => 'x@x.com', 'message' => 'hello world']),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 5,
        ]);
        curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        $this->assertSame(419, $status);
    }

    // ---------------- contact submission inserts a row ----------------

    public function testContactSubmissionInsertsRow(): void
    {
        if (!self::serverUp()) {
            $this->markTestSkipped('dev server not up');
        }
        $jar = tempnam(sys_get_temp_dir(), 'myn_jar_');

        // Pull the form to get a CSRF token + session cookie
        $ch = curl_init(self::HOST . '/contact');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_COOKIEJAR      => $jar,
            CURLOPT_COOKIEFILE     => $jar,
        ]);
        $html = curl_exec($ch);
        curl_close($ch);
        $this->assertNotFalse($html);
        $this->assertSame(1, preg_match('/name="_csrf" value="([a-f0-9]+)"/', (string) $html, $m));
        $token = $m[1];
        $this->assertNotEmpty($token);

        // Spam guard timestamp + signature (server emits both as hidden inputs).
        $this->assertSame(1, preg_match('/name="_ts" value="(\d+)"/', (string) $html, $tm));
        $this->assertSame(1, preg_match('/name="_ts_sig" value="([a-f0-9]+)"/', (string) $html, $sm));
        $ts  = $tm[1];
        $sig = $sm[1];

        // SpamGuard rejects forms submitted in < 2s; wait it out.
        sleep(3);

        $countBefore = (int) Database::connection()->query('SELECT COUNT(*) FROM messages')->fetchColumn();

        $ch = curl_init(self::HOST . '/contact');
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => http_build_query([
                '_csrf'   => $token,
                '_ts'     => $ts,
                '_ts_sig' => $sig,
                'website' => '',
                'name'    => 'PHPUnit Smoke',
                'email'   => 'phpunit@myn.test',
                'subject' => 'Smoke test',
                'message' => 'This is the M7.4 smoke test contact submission body — at least 10 characters long.',
            ]),
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_COOKIEJAR      => $jar,
            CURLOPT_COOKIEFILE     => $jar,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 5,
        ]);
        curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        @unlink($jar);

        $this->assertSame(302, $status, 'valid CSRF submission should redirect (302)');
        $countAfter = (int) Database::connection()->query('SELECT COUNT(*) FROM messages')->fetchColumn();
        $this->assertSame($countBefore + 1, $countAfter, 'a new messages row should exist');

        Database::connection()->exec("DELETE FROM messages WHERE email='phpunit@myn.test'");
    }

    // ---------------- model smoke ----------------

    public function testModelsReturnSeedData(): void
    {
        $this->assertGreaterThanOrEqual(4, Post::publishedCount());
        $this->assertNotEmpty(Program::tree());
        $this->assertNotEmpty(Setting::all());
        $this->assertCount(4, Stat::all());
        $this->assertCount(6, Partner::all());
        $this->assertNotNull(Page::findBySlug('about'));
        $this->assertNull(Page::findBySlug('definitely-not-a-page'));
    }

    // ---------------- Csrf unit ----------------

    public function testCsrfRoundTrip(): void
    {
        Csrf::rotate();
        $t = Csrf::token();
        $this->assertSame(64, strlen($t));
        $this->assertTrue(Csrf::check($t));
        $this->assertFalse(Csrf::check('bogus'));
        $this->assertFalse(Csrf::check(''));
        $this->assertFalse(Csrf::check(null));
    }

    // ---------------- Paystack webhook signature ----------------

    public function testPaystackWebhookSignature(): void
    {
        Paystack::configure([
            'secret_key' => 'sk_test_unit',
            'public_key' => 'pk_test_unit',
            'currency'   => 'KES',
            'base_url'   => 'https://api.paystack.co',
            'env'        => 'test',
            'callback_url' => null,
        ]);
        $body = '{"event":"charge.success","data":{"reference":"X","amount":1,"currency":"KES","status":"success"}}';
        $good = hash_hmac('sha512', $body, 'sk_test_unit');

        $this->assertTrue(Paystack::verifyWebhookSignature($body, $good));
        $this->assertFalse(Paystack::verifyWebhookSignature($body, 'wrong'));
        $this->assertFalse(Paystack::verifyWebhookSignature($body, null));
        $this->assertFalse(Paystack::verifyWebhookSignature($body . 'x', $good)); // tampered body
    }
}
