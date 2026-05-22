<?php
/**
 * scripts/apply-banner.php
 *
 * Idempotently inject the MBITHI letterhead banner (docs/letterhead-banner.txt)
 * into every PHP source file under app/, routes/, config/, public/, database/,
 * and tests/, after `declare(strict_types=1);` and before the namespace.
 *
 * Usage:
 *   php scripts/apply-banner.php           # scan everything
 *   php scripts/apply-banner.php path/...  # only the given files / globs
 *
 * Files that already contain the banner marker are skipped.
 */

$root   = dirname(__DIR__);
$banner = file_get_contents($root . '/docs/letterhead-banner.txt');
if ($banner === false) {
    fwrite(STDERR, "Missing docs/letterhead-banner.txt\n");
    exit(1);
}
$banner = rtrim($banner, "\n");

$targets = [];
$args = array_slice($argv, 1);

if ($args === []) {
    $dirs = ['app', 'routes', 'config', 'public', 'database', 'tests', 'scripts'];
    foreach ($dirs as $d) {
        $dir = $root . '/' . $d;
        if (!is_dir($dir)) {
            continue;
        }
        $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS));
        foreach ($it as $f) {
            if ($f->isFile() && $f->getExtension() === 'php') {
                $targets[] = $f->getPathname();
            }
        }
    }
} else {
    foreach ($args as $a) {
        if (str_contains($a, '*') || str_contains($a, '?')) {
            foreach (glob($a) ?: [] as $g) {
                if (is_file($g) && str_ends_with($g, '.php')) {
                    $targets[] = realpath($g);
                }
            }
        } elseif (is_file($a) && str_ends_with($a, '.php')) {
            $targets[] = realpath($a);
        }
    }
}

$applied = 0;
$skipped = 0;
$warned  = 0;

foreach (array_unique($targets) as $path) {
    if (str_ends_with($path, '/scripts/apply-banner.php')) {
        // self-skip; this utility script lives outside the policy
        continue;
    }
    $src = file_get_contents($path);
    if ($src === false) {
        fwrite(STDERR, "WARN: unreadable $path\n");
        $warned++;
        continue;
    }
    if (str_contains($src, 'M B I T H I')) {
        $skipped++;
        continue;
    }

    $needle = "declare(strict_types=1);\n";
    $pos = strpos($src, $needle);
    if ($pos === false) {
        fwrite(STDERR, "WARN: no declare(strict_types=1); in $path\n");
        $warned++;
        continue;
    }
    $insertAt = $pos + strlen($needle);

    $new = substr($src, 0, $insertAt) . "\n" . $banner . "\n" . substr($src, $insertAt);
    file_put_contents($path, $new);
    $applied++;
    echo "BANNERED: " . substr($path, strlen($root) + 1) . "\n";
}

echo "---\n";
echo "applied: $applied, skipped (already bannered): $skipped, warnings: $warned\n";
