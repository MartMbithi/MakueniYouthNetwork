<?php

declare(strict_types=1);

/*
 *   Conjured Upon This Day, Fri May 22 2026
 *
 *   M B I T H I — Letterhead omitted from CLI tools so --help stays readable.
 *   Full banner sits on every namespaced source file under app/.
 */

/**
 * One-shot WordPress -> MYN-CMS importer.
 *
 * Pulls posts and pages from the live WP REST API (default:
 * https://makueniyouth.org/wp-json/wp/v2), inserts them into the new schema,
 * downloads each post's featured image via ImageProcessor, and rewrites
 * inline <img src=…/wp-content/uploads/…> URLs to point at /uploads/.
 *
 * IDEMPOTENT: a second run will skip any slug it has already imported.
 *
 * Usage:
 *   php database/import-wordpress.php                 # full import
 *   php database/import-wordpress.php --dry-run       # no DB writes, no downloads
 *   php database/import-wordpress.php --type=posts    # posts only (or pages)
 *   php database/import-wordpress.php --base=https://example.com/wp-json/wp/v2
 *   php database/import-wordpress.php --limit=10      # cap items per type
 */

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "This script must be run from the command line.\n");
    exit(2);
}

$rootDir = dirname(__DIR__);
require $rootDir . '/vendor/autoload.php';

$config = require $rootDir . '/config/config.php';
\App\Core\Database::configure($config['db']);
\App\Services\ImageProcessor::configure($rootDir . '/public/uploads', '/uploads/');

$args = parseArgs($argv);
$dryRun  = (bool) ($args['dry-run'] ?? false);
$baseUrl = rtrim((string) ($args['base'] ?? 'https://makueniyouth.org/wp-json/wp/v2'), '/');
$type    = (string) ($args['type'] ?? 'all');
$limit   = isset($args['limit']) ? (int) $args['limit'] : 0;

writeLine("MYN WordPress import");
writeLine("  base:    {$baseUrl}");
writeLine("  type:    {$type}");
writeLine("  limit:   " . ($limit > 0 ? $limit : 'unlimited'));
writeLine("  dry-run: " . ($dryRun ? 'YES (no writes)' : 'NO'));
writeLine(str_repeat('-', 60));

$tasks = match ($type) {
    'posts' => ['posts'],
    'pages' => ['pages'],
    'all'   => ['posts', 'pages'],
    default => exitWith("Unknown --type=$type"),
};

$total = ['imported' => 0, 'skipped' => 0, 'failed' => 0];

foreach ($tasks as $resource) {
    writeLine("");
    writeLine("== {$resource} ==");
    $r = importResource($resource, $baseUrl, $limit, $dryRun);
    writeLine("  imported: {$r['imported']}   skipped: {$r['skipped']}   failed: {$r['failed']}");
    foreach ($r as $k => $v) {
        if (is_int($v) && isset($total[$k])) {
            $total[$k] += $v;
        }
    }
}

writeLine("");
writeLine(str_repeat('-', 60));
writeLine("DONE  imported={$total['imported']}  skipped={$total['skipped']}  failed={$total['failed']}");
exit(0);

// -----------------------------------------------------------------------

/**
 * @return array{imported:int,skipped:int,failed:int}
 */
function importResource(string $resource, string $baseUrl, int $limit, bool $dryRun): array
{
    $imported = $skipped = $failed = 0;
    $page = 1;
    $perPage = 50;
    $count = 0;

    while (true) {
        if ($limit > 0 && $count >= $limit) {
            break;
        }
        $url = $baseUrl . '/' . $resource . '?per_page=' . $perPage . '&page=' . $page . '&orderby=date&order=asc&_embed=1';
        writeLine("  fetching {$url}");
        try {
            $items = httpJson($url);
        } catch (\Throwable $e) {
            writeLine("    ERROR: " . $e->getMessage());
            break;
        }
        if (!is_array($items) || $items === []) {
            break;
        }
        foreach ($items as $item) {
            if ($limit > 0 && $count >= $limit) {
                break;
            }
            $count++;
            $slug = (string) ($item['slug'] ?? '');
            if ($slug === '') {
                $failed++;
                writeLine("    SKIP (no slug)");
                continue;
            }
            if (slugExists($resource, $slug)) {
                $skipped++;
                writeLine("    SKIP {$slug} (already imported)");
                continue;
            }

            try {
                if ($dryRun) {
                    writeLine("    DRY-RUN would import: {$slug}");
                } else {
                    $id = importOne($resource, $item);
                    writeLine("    OK   {$slug}  -> id={$id}");
                }
                $imported++;
            } catch (\Throwable $e) {
                $failed++;
                writeLine("    FAIL {$slug}: " . $e->getMessage());
            }
        }
        if (count($items) < $perPage) {
            break;
        }
        $page++;
    }

    return ['imported' => $imported, 'skipped' => $skipped, 'failed' => $failed];
}

function importOne(string $resource, array $item): int
{
    $slug   = (string) $item['slug'];
    $title  = (string) ($item['title']['rendered']  ?? $slug);
    $body   = (string) ($item['content']['rendered'] ?? '');
    $excerpt = trim(strip_tags((string) ($item['excerpt']['rendered'] ?? '')));
    $excerpt = $excerpt !== '' ? mb_substr($excerpt, 0, 380) : null;
    $date   = (string) ($item['date_gmt'] ?? $item['date'] ?? date('Y-m-d H:i:s'));
    $date   = str_replace('T', ' ', $date);

    $cover = null;
    $featuredId = (int) ($item['featured_media'] ?? 0);
    if ($featuredId > 0) {
        $mediaUrl = extractEmbeddedMediaUrl($item) ?? lookupMediaUrl($featuredId);
        if ($mediaUrl !== null) {
            $cover = downloadToUploads($mediaUrl) ?? $mediaUrl;
        }
    }

    // Rewrite WP media URLs in the body to local /uploads after pulling them.
    $body = rewriteInlineMedia($body);

    if ($resource === 'posts') {
        return \App\Models\Post::create([
            'slug'         => $slug,
            'title'        => htmlEntityDecodeAll($title),
            'excerpt'      => $excerpt !== null ? htmlEntityDecodeAll($excerpt) : null,
            'body'         => $body,
            'cover_image'  => $cover,
            'category_id'  => null,
            'author_id'    => null,
            'status'       => 'published',
            'published_at' => $date,
        ]);
    }
    if ($resource === 'pages') {
        return \App\Models\Page::create([
            'slug'       => $slug,
            'title'      => htmlEntityDecodeAll($title),
            'body'       => $body,
            'meta_desc'  => $excerpt,
            'hero_image' => $cover,
            'status'     => 'published',
        ]);
    }
    throw new RuntimeException("Unknown resource: $resource");
}

function slugExists(string $resource, string $slug): bool
{
    $pdo = \App\Core\Database::connection();
    $table = $resource === 'posts' ? 'posts' : 'pages';
    $stmt = $pdo->prepare("SELECT id FROM {$table} WHERE slug = :s LIMIT 1");
    $stmt->execute([':s' => $slug]);
    return $stmt->fetchColumn() !== false;
}

function extractEmbeddedMediaUrl(array $item): ?string
{
    $media = $item['_embedded']['wp:featuredmedia'][0] ?? null;
    if (!is_array($media)) {
        return null;
    }
    $url = $media['source_url'] ?? null;
    return is_string($url) && $url !== '' ? $url : null;
}

function lookupMediaUrl(int $mediaId): ?string
{
    static $cache = [];
    if (isset($cache[$mediaId])) {
        return $cache[$mediaId];
    }
    try {
        $data = httpJson('https://makueniyouth.org/wp-json/wp/v2/media/' . $mediaId);
    } catch (\Throwable $e) {
        return null;
    }
    $url = $data['source_url'] ?? null;
    return $cache[$mediaId] = (is_string($url) ? $url : null);
}

function downloadToUploads(string $url): ?string
{
    $ext = strtolower((string) pathinfo(parse_url($url, PHP_URL_PATH) ?: '', PATHINFO_EXTENSION));
    if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'pdf'], true)) {
        return null;
    }
    $tmp = tempnam(sys_get_temp_dir(), 'myn_wp_');
    if ($tmp === false) {
        return null;
    }
    $contents = @file_get_contents($url);
    if ($contents === false || $contents === '') {
        @unlink($tmp);
        return null;
    }
    file_put_contents($tmp, $contents);

    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($tmp) ?: '';
    try {
        $path = \App\Services\ImageProcessor::store([
            'name'     => basename(parse_url($url, PHP_URL_PATH) ?: 'asset'),
            'type'     => $mime,
            'tmp_name' => $tmp,
            'error'    => UPLOAD_ERR_OK,
            'size'     => filesize($tmp),
        ]);
        return $path;
    } catch (\Throwable $e) {
        return null;
    } finally {
        @unlink($tmp);
    }
}

function rewriteInlineMedia(string $body): string
{
    return preg_replace_callback(
        '#https?://makueniyouth\.org/wp-content/uploads/[^"\'\s)<>]+#i',
        static function (array $m): string {
            $local = downloadToUploads($m[0]);
            return $local ?? $m[0];
        },
        $body
    ) ?? $body;
}

function httpJson(string $url): mixed
{
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 20,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_HTTPHEADER     => ['Accept: application/json', 'User-Agent: MYN-Importer/1.0'],
    ]);
    $body = curl_exec($ch);
    if ($body === false) {
        $err = curl_error($ch);
        curl_close($ch);
        throw new RuntimeException("network: $err");
    }
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if ($status >= 400) {
        throw new RuntimeException("HTTP {$status} for {$url}");
    }
    $decoded = json_decode((string) $body, true);
    if ($decoded === null && json_last_error() !== JSON_ERROR_NONE) {
        throw new RuntimeException("non-JSON response: " . substr((string) $body, 0, 200));
    }
    return $decoded;
}

function htmlEntityDecodeAll(string $s): string
{
    return html_entity_decode($s, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

/** @return array<string,mixed> */
function parseArgs(array $argv): array
{
    $out = [];
    foreach (array_slice($argv, 1) as $a) {
        if (!str_starts_with($a, '--')) {
            continue;
        }
        $a = substr($a, 2);
        if (str_contains($a, '=')) {
            [$k, $v] = explode('=', $a, 2);
            $out[$k] = $v;
        } else {
            $out[$a] = true;
        }
    }
    return $out;
}

function writeLine(string $line): void
{
    fwrite(STDOUT, $line . "\n");
}

function exitWith(string $msg): never
{
    fwrite(STDERR, "$msg\n");
    exit(2);
}
