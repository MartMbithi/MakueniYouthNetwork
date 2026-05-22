<?php
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
