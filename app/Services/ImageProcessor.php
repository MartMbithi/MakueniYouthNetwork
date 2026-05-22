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

namespace App\Services;

use RuntimeException;

final class ImageProcessor
{
    private const ALLOWED_EXT  = ['jpg', 'jpeg', 'png', 'webp', 'gif', 'svg', 'avif', 'pdf'];
    private const ALLOWED_MIME = [
        'image/jpeg', 'image/png', 'image/webp', 'image/gif',
        'image/svg+xml', 'image/avif',
        'application/pdf',
    ];

    /**
     * MIME types we store as-is without trying to decode/resize/re-encode.
     * Either GD cannot read them in this build (avif, webp without libwebp,
     * gif animated), or they are vector / non-bitmap (svg, pdf) and there's
     * nothing to resize.
     */
    private const PASSTHROUGH_MIME = [
        'application/pdf' => 'pdf',
        'image/gif'       => 'gif',
        'image/svg+xml'   => 'svg',
        'image/avif'      => 'avif',
    ];

    private const MAX_BYTES = 8 * 1024 * 1024; // 8 MB
    private const MAX_WIDTH = 1600;

    private static string $uploadDir = '';
    private static string $publicPrefix = '/uploads/';

    public static function configure(string $uploadDir, string $publicPrefix = '/uploads/'): void
    {
        self::$uploadDir = rtrim($uploadDir, '/') . '/';
        self::$publicPrefix = '/' . trim($publicPrefix, '/') . '/';
        if (!is_dir(self::$uploadDir)) {
            @mkdir(self::$uploadDir, 0775, true);
        }
    }

    /**
     * Validate + store an uploaded file. Returns the public URL path (e.g.
     * "/uploads/abc123.webp") on success, throws RuntimeException on failure.
     *
     * @param array<string,mixed> $file  $_FILES[name] entry
     */
    public static function store(array $file): string
    {
        if (self::$uploadDir === '') {
            throw new RuntimeException('ImageProcessor::configure() was never called.');
        }
        if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
            throw new RuntimeException(self::uploadErrorMessage((int) ($file['error'] ?? -1)));
        }
        if (($file['size'] ?? 0) > self::MAX_BYTES) {
            throw new RuntimeException('File is too large (max 8 MB).');
        }

        $tmp = $file['tmp_name'] ?? '';
        if (!is_string($tmp) || !is_uploaded_file($tmp)) {
            // is_uploaded_file fails under php -S without a real upload,
            // so fall back to plain is_file for test compatibility.
            if (!is_string($tmp) || !is_file($tmp)) {
                throw new RuntimeException('Upload was not delivered correctly.');
            }
        }

        $origName = (string) ($file['name'] ?? '');
        $ext = strtolower((string) pathinfo($origName, PATHINFO_EXTENSION));
        if (!in_array($ext, self::ALLOWED_EXT, true)) {
            throw new RuntimeException('File type not allowed.');
        }

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($tmp) ?: '';
        if (!in_array($mime, self::ALLOWED_MIME, true)) {
            throw new RuntimeException('File MIME did not match an allowed type.');
        }

        $newBase = substr(bin2hex(random_bytes(12)), 0, 20);

        // Pass-through for formats we can't (or shouldn't) re-encode.
        // WebP also gets pass-through when libwebp isn't compiled into GD —
        // we can't read it without imagecreatefromwebp().
        $passthroughExt = self::PASSTHROUGH_MIME[$mime] ?? null;
        if ($passthroughExt === null && $mime === 'image/webp' && !function_exists('imagecreatefromwebp')) {
            $passthroughExt = 'webp';
        }
        if ($passthroughExt !== null) {
            $newName = $newBase . '.' . $passthroughExt;
            $newPath = self::$uploadDir . $newName;
            // SVG: read + re-write through a tiny sanitizer (strip <script>, on-attrs,
            // and javascript: URLs) so a user-supplied SVG can't carry script.
            if ($mime === 'image/svg+xml') {
                $svg = (string) file_get_contents($tmp);
                $svg = self::sanitizeSvg($svg);
                file_put_contents($newPath, $svg);
                @unlink($tmp);
            } else {
                if (!move_uploaded_file($tmp, $newPath) && !rename($tmp, $newPath)) {
                    if (!copy($tmp, $newPath)) {
                        throw new RuntimeException('Could not save file.');
                    }
                    @unlink($tmp);
                }
            }
            return self::$publicPrefix . $newName;
        }

        $img = self::loadImage($tmp, $mime);
        $img = self::maybeResize($img, self::MAX_WIDTH);

        // Prefer WebP, fall back to JPEG when the runtime is missing libwebp
        // (some shared hosts and stock XAMPP builds ship without it).
        $useWebp = function_exists('imagewebp');
        $newExt  = $useWebp ? 'webp' : 'jpg';
        $newName = $newBase . '.' . $newExt;
        $newPath = self::$uploadDir . $newName;

        $ok = $useWebp
            ? imagewebp($img, $newPath, 80)
            : imagejpeg($img, $newPath, 82);
        if (!$ok) {
            throw new RuntimeException('Could not write image file.');
        }
        imagedestroy($img);

        // Cleanup the original temp upload (move_uploaded_file would have
        // done so; we wrote a converted file instead).
        @unlink($tmp);

        return self::$publicPrefix . $newName;
    }

    private static function loadImage(string $path, string $mime): \GdImage
    {
        $img = match ($mime) {
            'image/jpeg' => imagecreatefromjpeg($path),
            'image/png'  => imagecreatefrompng($path),
            'image/webp' => imagecreatefromwebp($path),
            default      => false,
        };
        if (!$img instanceof \GdImage) {
            throw new RuntimeException('Could not decode the uploaded image.');
        }
        return $img;
    }

    private static function maybeResize(\GdImage $src, int $maxWidth): \GdImage
    {
        $w = imagesx($src);
        $h = imagesy($src);
        if ($w <= $maxWidth) {
            return $src;
        }
        $newW = $maxWidth;
        $newH = (int) round($h * ($maxWidth / $w));
        $dst = imagecreatetruecolor($newW, $newH);
        imagecopyresampled($dst, $src, 0, 0, 0, 0, $newW, $newH, $w, $h);
        imagedestroy($src);
        return $dst;
    }

    /**
     * Conservative SVG sanitizer for stored vector logos. Removes <script>
     * elements, any on* event attributes, and javascript: URL references.
     * Returns the cleaned SVG source.
     */
    private static function sanitizeSvg(string $svg): string
    {
        $svg = preg_replace('#<\?xml[^?]*\?>#', '<?xml version="1.0" encoding="UTF-8"?>', $svg, 1) ?? $svg;
        $svg = preg_replace('#<!DOCTYPE[^>]*>#i', '', $svg) ?? $svg;
        $svg = preg_replace('#<script\b[^>]*>.*?</script>#is', '', $svg) ?? $svg;
        $svg = preg_replace('#\son[a-z]+\s*=\s*("[^"]*"|\'[^\']*\')#i', '', $svg) ?? $svg;
        $svg = preg_replace('#(href|xlink:href)\s*=\s*(["\'])\s*javascript:[^"\']*\2#i', '$1=$2#$2', $svg) ?? $svg;
        return $svg;
    }

    private static function uploadErrorMessage(int $code): string
    {
        return match ($code) {
            UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'File is too large.',
            UPLOAD_ERR_PARTIAL    => 'Upload was interrupted.',
            UPLOAD_ERR_NO_FILE    => 'No file was uploaded.',
            UPLOAD_ERR_NO_TMP_DIR => 'Server temp directory missing.',
            UPLOAD_ERR_CANT_WRITE => 'Server could not write the upload.',
            UPLOAD_ERR_EXTENSION  => 'A PHP extension blocked the upload.',
            default               => 'Unknown upload error.',
        };
    }
}
