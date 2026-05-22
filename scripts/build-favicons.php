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
 * scripts/build-favicons.php
 *
 * Generate the favicon variants the site needs from the master logo.
 *
 * Input :  public/assets/img/logo.png       (the full landscape brand-mark)
 * Output:  public/favicon.png               16x16  (legacy)
 *          public/apple-touch-icon.png     180x180 (iOS)
 *          public/assets/img/logo-square.png  512x512 (Open Graph default)
 *
 * The master is landscape (500x134) — to make a usable square, we crop to
 * a centred square of the SHORT side and re-render onto a cream background
 * so it stays readable on dark UIs (e.g. when a browser composites the
 * favicon onto its dark chrome).
 *
 * Run once after replacing the master logo.
 *   php scripts/build-favicons.php
 */

$root  = dirname(__DIR__);
$src   = $root . '/public/assets/img/logo.png';

if (!is_file($src)) {
    fwrite(STDERR, "Missing source: $src\n");
    exit(2);
}

$source = imagecreatefrompng($src);
if (!$source instanceof GdImage) {
    fwrite(STDERR, "Could not decode $src\n");
    exit(3);
}

imagealphablending($source, true);
imagesavealpha($source, true);

$srcW = imagesx($source);
$srcH = imagesy($source);

/**
 * Render the master onto a square cream canvas with padding.
 */
function makeSquare(GdImage $logo, int $size): GdImage
{
    $logoW = imagesx($logo);
    $logoH = imagesy($logo);

    $canvas = imagecreatetruecolor($size, $size);
    imagealphablending($canvas, true);
    imagesavealpha($canvas, true);

    // Cream background (matches the public-site --cream colour: #f6f1e4)
    $bg = imagecolorallocate($canvas, 0xf6, 0xf1, 0xe4);
    imagefilledrectangle($canvas, 0, 0, $size, $size, $bg);

    // Fit the logo into ~80% of the canvas while preserving aspect ratio.
    $maxW = (int) round($size * 0.86);
    $maxH = (int) round($size * 0.86);
    $ratio = min($maxW / $logoW, $maxH / $logoH);
    $newW  = (int) round($logoW * $ratio);
    $newH  = (int) round($logoH * $ratio);
    $x = (int) round(($size - $newW) / 2);
    $y = (int) round(($size - $newH) / 2);

    imagecopyresampled($canvas, $logo, $x, $y, 0, 0, $newW, $newH, $logoW, $logoH);
    return $canvas;
}

$targets = [
    16  => $root . '/public/favicon.png',
    180 => $root . '/public/apple-touch-icon.png',
    512 => $root . '/public/assets/img/logo-square.png',
];

foreach ($targets as $size => $path) {
    $img = makeSquare($source, $size);
    imagepng($img, $path);
    imagedestroy($img);
    $bytes = filesize($path);
    echo "  wrote {$path}  (${size}x${size}, " . number_format($bytes) . " bytes)\n";
}

imagedestroy($source);
echo "Done.\n";
