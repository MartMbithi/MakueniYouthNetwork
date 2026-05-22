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
 * scripts/post-import-cleanup.php
 *
 * Runs once, immediately after database/import-wordpress.php. Three jobs:
 *
 *   1. MIGRATE the body of each imported WP "program" page (slug-matched)
 *      into the programs table, so the rich WP copy is what the public
 *      /programs/* routes render — not the placeholders from seed.sql.
 *
 *   2. MERGE the WP about-us page body into our intentional /about page
 *      (slug "about"). Source is preferred when it has more content.
 *
 *   3. DELETE WP junk pages we never want public: WP placeholders
 *      (sample-page, sample-page-2, home-laundry, blog), plugin pages
 *      (donor-dashboard, donation-failed, donation-confirmation), and the
 *      WP-version pages that duplicate our own routes (programs,
 *      donation, volunteer-with-us, fundraising-campaign).
 */

require __DIR__ . '/../vendor/autoload.php';

$config = require __DIR__ . '/../config/config.php';
\App\Core\Database::configure($config['db']);
$pdo = \App\Core\Database::connection();

function log_(string $msg): void { fwrite(STDOUT, $msg . "\n"); }

// -----------------------------------------------------------------------
// 1. WP program pages -> programs table
// -----------------------------------------------------------------------
$map = [
    'advocacy-civic-education'                       => 'advocacy-civic-engagement',
    'leadership-talent-development'                  => 'leadership-talent-development',
    'education-capacity-enhancement'                 => 'education-capacity-enhancement',
    'youth-mentorship-program'                       => 'youth-mentorship',
    'foundational-literacy-and-numeracy-assessment'  => 'foundational-literacy-numeracy-assessment',
];

log_('== migrating WP program pages into the programs table ==');
foreach ($map as $wpSlug => $programSlug) {
    $stmt = $pdo->prepare('SELECT body, hero_image FROM pages WHERE slug = :s LIMIT 1');
    $stmt->execute([':s' => $wpSlug]);
    $page = $stmt->fetch();
    if (!$page) {
        log_('  - SKIP ' . $wpSlug . ' (no WP page imported)');
        continue;
    }
    $up = $pdo->prepare(
        'UPDATE programs SET body = :body,
                              cover_image = COALESCE(NULLIF(cover_image, ""), :hero),
                              status = "published"
         WHERE slug = :slug'
    );
    $up->execute([
        ':body' => $page['body'],
        ':hero' => $page['hero_image'],
        ':slug' => $programSlug,
    ]);
    if ($up->rowCount() > 0) {
        log_('  OK   ' . $wpSlug . ' -> programs.' . $programSlug . ' (body=' . strlen($page['body']) . ' chars)');
    } else {
        log_('  WARN no programs row found for slug=' . $programSlug);
    }
    // drop the page now that its content moved
    $pdo->prepare('DELETE FROM pages WHERE slug = :s')->execute([':s' => $wpSlug]);
}

// -----------------------------------------------------------------------
// 2. Merge WP about-us into seeded about
// -----------------------------------------------------------------------
log_('');
log_('== merging WP about-us into /about ==');
$stmt = $pdo->prepare('SELECT body, hero_image FROM pages WHERE slug = "about-us" LIMIT 1');
$stmt->execute();
$wpAbout = $stmt->fetch();
if ($wpAbout && strlen((string) $wpAbout['body']) > 1000) {
    $pdo->prepare(
        'UPDATE pages SET body = :body,
                          hero_image = COALESCE(NULLIF(hero_image, ""), :hero)
         WHERE slug = "about"'
    )->execute([
        ':body' => $wpAbout['body'],
        ':hero' => $wpAbout['hero_image'],
    ]);
    $pdo->exec('DELETE FROM pages WHERE slug = "about-us"');
    log_('  OK merged about-us body into /about (' . strlen($wpAbout['body']) . ' chars)');
} else {
    log_('  SKIP about-us not present or too thin to use');
}

// -----------------------------------------------------------------------
// 3. Delete WP junk pages
// -----------------------------------------------------------------------
log_('');
log_('== deleting WP junk pages ==');
$junk = [
    'sample-page', 'sample-page-2', 'home-laundry', 'blog',
    'donor-dashboard', 'donation-failed', 'donation-confirmation',
    'programs',           // would shadow /programs route
    'donation',           // we have /donate
    'volunteer-with-us',  // we have /volunteer
    'fundraising-campaign',
];
$in = implode(',', array_fill(0, count($junk), '?'));
$del = $pdo->prepare("DELETE FROM pages WHERE slug IN ($in)");
$del->execute($junk);
log_('  deleted ' . $del->rowCount() . ' junk pages');

// -----------------------------------------------------------------------
// Summary
// -----------------------------------------------------------------------
log_('');
log_('== final page list ==');
$rows = $pdo->query('SELECT slug, CHAR_LENGTH(body) AS chars FROM pages ORDER BY slug')->fetchAll();
foreach ($rows as $r) {
    log_('  /' . $r['slug'] . '  (' . $r['chars'] . ' chars)');
}
log_('');
log_('== final programs (with bodies) ==');
$rows = $pdo->query('SELECT slug, CHAR_LENGTH(body) AS chars FROM programs ORDER BY sort_order')->fetchAll();
foreach ($rows as $r) {
    log_('  /programs/' . $r['slug'] . '  (' . $r['chars'] . ' chars)');
}
log_('');
log_('Done.');
