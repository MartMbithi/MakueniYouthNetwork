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

/**
 * scripts/decommission-live-urls.php
 *
 * Final cut-over step before makueniyouth.org gets shut down. Walks every
 * surviving makueniyouth.org URL across:
 *
 *   - posts.cover_image
 *   - programs.cover_image
 *   - pages.hero_image
 *   - posts.body, pages.body, programs.body (inline <img src=...> and <a href=...>)
 *   - partners.logo  (downloads the 6 partner logos)
 *
 * Each remote URL is fetched through ImageProcessor::store(), which validates
 * MIME, resizes to <=1600 px wide, re-encodes (WebP if libwebp is present,
 * JPEG otherwise), drops it in /uploads under a random hex name, and returns
 * the new local path. The DB row is rewritten to that local path.
 *
 * Idempotent — already-local paths are skipped.
 */

require __DIR__ . '/../vendor/autoload.php';

$config = require __DIR__ . '/../config/config.php';
\App\Core\Database::configure($config['db']);
\App\Services\ImageProcessor::configure(__DIR__ . '/../public/uploads', '/uploads/');

$pdo = \App\Core\Database::connection();

function log_(string $s): void { fwrite(STDOUT, $s . "\n"); }

/**
 * Download a remote URL through ImageProcessor; return the new local path,
 * or null if anything went wrong. Caches by URL so repeated references in
 * the same body share one download.
 */
function fetch_remote(string $url): ?string
{
    static $cache = [];
    if (isset($cache[$url])) {
        return $cache[$url];
    }

    $tmp = tempnam(sys_get_temp_dir(), 'myn_dec_');
    if ($tmp === false) {
        return $cache[$url] = null;
    }

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 30,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_FOLLOWLOCATION => true,
    ]);
    $body = curl_exec($ch);
    $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($body === false || $status >= 400 || $body === '') {
        @unlink($tmp);
        return $cache[$url] = null;
    }
    file_put_contents($tmp, $body);

    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($tmp) ?: '';
    try {
        $local = \App\Services\ImageProcessor::store([
            'name'     => basename(parse_url($url, PHP_URL_PATH) ?: 'asset'),
            'type'     => $mime,
            'tmp_name' => $tmp,
            'error'    => UPLOAD_ERR_OK,
            'size'     => filesize($tmp),
        ]);
        return $cache[$url] = $local;
    } catch (\Throwable $e) {
        log_('   FAIL  ' . $url . ' -> ' . $e->getMessage());
        return $cache[$url] = null;
    } finally {
        @unlink($tmp);
    }
}

// -----------------------------------------------------------------------
// 1. Single-URL columns
// -----------------------------------------------------------------------
$cols = [
    'posts'    => 'cover_image',
    'programs' => 'cover_image',
    'pages'    => 'hero_image',
];

$rewrote = 0;
foreach ($cols as $table => $col) {
    log_("== $table.$col ==");
    $stmt = $pdo->prepare("SELECT id, $col FROM $table WHERE $col LIKE 'http%makueniyouth.org%'");
    $stmt->execute();
    foreach ($stmt->fetchAll() as $row) {
        $url = (string) $row[$col];
        $local = fetch_remote($url);
        if ($local === null) {
            log_('   skip  id=' . $row['id'] . '  (download failed)');
            continue;
        }
        $up = $pdo->prepare("UPDATE $table SET $col = :p WHERE id = :id");
        $up->execute([':p' => $local, ':id' => $row['id']]);
        log_('   OK    id=' . $row['id'] . '  ' . $url . '  ->  ' . $local);
        $rewrote++;
    }
}

// -----------------------------------------------------------------------
// 2. Body HTML — posts.body, programs.body, pages.body
// -----------------------------------------------------------------------
log_('');
log_('== body content (inline <img src> and <a href> on makueniyouth.org) ==');

foreach (['posts', 'programs', 'pages'] as $table) {
    $stmt = $pdo->query("SELECT id, body FROM $table WHERE body LIKE '%makueniyouth.org%'");
    foreach ($stmt->fetchAll() as $row) {
        $orig = (string) $row['body'];

        // First pass: <img src="...makueniyouth.org/wp-content/uploads/...">
        // — download via ImageProcessor, rewrite the src to the local path.
        // If the source 404s (broken link on the dying site), DROP the entire
        // <img> tag rather than leaving a known-bad URL in the body.
        $new = preg_replace_callback(
            '#<img\b[^>]*\bsrc=(["\'])(https?://(?:www\.)?makueniyouth\.org/wp-content/uploads/[^"\']+)\1[^>]*/?>#i',
            static function (array $m): string {
                $local = fetch_remote($m[2]);
                if ($local === null) {
                    log_('   drop  broken <img src=' . $m[2] . '>');
                    return '';
                }
                // Reconstruct a clean <img> tag pointing at the local path.
                $tag = $m[0];
                return preg_replace('#\bsrc=(["\'])[^"\']+\1#', 'src=$1' . $local . '$1', $tag, 1) ?? $tag;
            },
            $orig
        ) ?? $orig;

        // Second pass: anything else that escaped (raw URLs, srcset, etc.)
        // Try to download or, if download fails, leave the URL alone (it'll be
        // caught by the final audit).
        $new = preg_replace_callback(
            '#https?://(?:www\.)?makueniyouth\.org/wp-content/uploads/[^"\'\s)<>]+#i',
            static function (array $m): string {
                $local = fetch_remote($m[0]);
                return $local ?? $m[0];
            },
            $new
        ) ?? $new;

        // Strip any remaining hyperlinks to the live host — leave the text content
        $new = preg_replace_callback(
            '#<a[^>]+href=["\']https?://(?:www\.)?makueniyouth\.org[^"\']*["\'][^>]*>(.*?)</a>#is',
            static fn (array $m): string => $m[1],
            $new
        ) ?? $new;

        if ($new !== $orig) {
            $up = $pdo->prepare("UPDATE $table SET body = :b WHERE id = :id");
            $up->execute([':b' => $new, ':id' => $row['id']]);
            log_('   OK    ' . $table . '.id=' . $row['id'] . '  (rewrote body)');
            $rewrote++;
        }
    }
}

// -----------------------------------------------------------------------
// 3. Partner logos — explicit, slug-matched
// -----------------------------------------------------------------------
log_('');
log_('== partner logos (from live site) ==');

$partnerLogos = [
    'KCDF'                        => 'https://makueniyouth.org/wp-content/uploads/2025/06/KCDF.jpeg',
    'Usawa Agenda'                => 'https://makueniyouth.org/wp-content/uploads/2025/06/Usawa-Agenda.png',
    'Zizi Afrique'                => 'https://makueniyouth.org/wp-content/uploads/2025/06/Zizi.png',
    'Africa Voices'               => 'https://makueniyouth.org/wp-content/uploads/2025/06/Africa-voice-slogo.svg',
    'Poverty Eradication Network' => 'https://makueniyouth.org/wp-content/uploads/2025/06/Poverty-Eradication-Network.png',
    'EYC'                         => 'https://makueniyouth.org/wp-content/uploads/2025/06/eyc.jpg',
];

foreach ($partnerLogos as $name => $url) {
    $local = fetch_remote($url);
    if ($local === null) {
        log_('   FAIL  ' . $name . '  ' . $url);
        continue;
    }
    $up = $pdo->prepare('UPDATE partners SET logo = :logo WHERE name = :name');
    $up->execute([':logo' => $local, ':name' => $name]);
    if ($up->rowCount() === 0) {
        log_('   WARN  no partner row for "' . $name . '"');
    } else {
        log_('   OK    ' . $name . '  ->  ' . $local);
        $rewrote++;
    }
}

// -----------------------------------------------------------------------
// 4. Final audit
// -----------------------------------------------------------------------
log_('');
log_('== final audit: anything still pointing at makueniyouth.org? ==');
// Audit only flags body URLs that are website links — bare `info@makueniyouth.org`
// in a mailto: is the org's own email and a false positive. We check for the
// presence of "//makueniyouth.org" (the URL form) instead of the loose match.
$leftover = $pdo->query("
    SELECT 'posts.cover_image' AS w, id FROM posts WHERE cover_image LIKE '%makueniyouth.org%'
    UNION ALL
    SELECT 'programs.cover_image', id FROM programs WHERE cover_image LIKE '%makueniyouth.org%'
    UNION ALL
    SELECT 'pages.hero_image',    id FROM pages    WHERE hero_image  LIKE '%makueniyouth.org%'
    UNION ALL
    SELECT 'posts.body',          id FROM posts    WHERE body LIKE '%//makueniyouth.org%'
    UNION ALL
    SELECT 'programs.body',       id FROM programs WHERE body LIKE '%//makueniyouth.org%'
    UNION ALL
    SELECT 'pages.body',          id FROM pages    WHERE body LIKE '%//makueniyouth.org%'
    UNION ALL
    SELECT 'partners.logo',       id FROM partners WHERE logo LIKE '%makueniyouth.org%'
")->fetchAll();

if ($leftover === []) {
    log_('   ✓ clean — no live URLs left in any tracked column');
} else {
    foreach ($leftover as $r) {
        log_('   LEFT  ' . $r['w'] . '  id=' . $r['id']);
    }
}

log_('');
log_('Done. Rewrites applied: ' . $rewrote);
