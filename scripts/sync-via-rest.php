<?php

declare(strict_types=1);

/**
 * Apply the sitemap page structure to a remote WordPress over the REST API.
 *
 * The companion scripts run beside wp-load.php, which needs shell access. This
 * one needs only an Application Password, so it works against a host that
 * exposes wp-admin but no SSH.
 *
 * Create the password at: wp-admin → Users → Profile → Application Passwords.
 *
 * Usage:
 *   php sync-via-rest.php --site https://host --user NAME --pass "xxxx xxxx ..."
 *   php sync-via-rest.php --site ... --user ... --pass ... --apply
 *
 * Without --apply nothing is written; the planned actions are printed instead.
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('CLI only.');
}

$opts = [
    'site' => '',
    'user' => '',
    'pass' => '',
];
$apply = false;

for ($i = 1; $i < $argc; $i++) {
    $arg = $argv[$i];
    if ($arg === '--apply') {
        $apply = true;
        continue;
    }
    if (str_starts_with($arg, '--') && isset($argv[$i + 1])) {
        $key = substr($arg, 2);
        if (array_key_exists($key, $opts)) {
            $opts[$key] = $argv[++$i];
        }
    }
}

foreach (['site', 'user', 'pass'] as $required) {
    if ($opts[$required] === '') {
        fwrite(STDERR, "ERROR --{$required} is required.\n\n");
        fwrite(STDERR, "Usage: php sync-via-rest.php --site https://host --user NAME --pass 'xxxx xxxx xxxx' [--apply]\n");
        exit(1);
    }
}

$site = rtrim($opts['site'], '/');
$auth = base64_encode($opts['user'] . ':' . $opts['pass']);

/**
 * Perform a REST request, returning [status, decoded body].
 */
function rest(string $method, string $url, string $auth, ?array $body = null): array
{
    $ch = curl_init($url);
    $headers = [
        'Authorization: Basic ' . $auth,
        'Accept: application/json',
    ];

    if ($body !== null) {
        $headers[] = 'Content-Type: application/json';
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }

    curl_setopt_array($ch, [
        CURLOPT_CUSTOMREQUEST => $method,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_TIMEOUT => 60,
        CURLOPT_HTTPHEADER => $headers,
    ]);

    $raw = curl_exec($ch);
    if ($raw === false) {
        fwrite(STDERR, 'ERROR curl: ' . curl_error($ch) . "\n");
        exit(4);
    }
    $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return [$status, json_decode((string) $raw, true)];
}

// ---- Verify credentials before touching anything.
[$status, $me] = rest('GET', $site . '/wp-json/wp/v2/users/me?context=edit', $auth);
if ($status !== 200) {
    fwrite(STDERR, "ERROR authentication failed (HTTP {$status}).\n");
    fwrite(STDERR, "Check the username and Application Password.\n");
    exit(2);
}

echo 'SITE ' . $site . PHP_EOL;
echo 'USER ' . ($me['name'] ?? '?') . ' (' . implode(',', $me['roles'] ?? []) . ')' . PHP_EOL;
echo 'MODE ' . ($apply ? 'APPLY' : 'DRY-RUN') . PHP_EOL . PHP_EOL;

if (!in_array('administrator', $me['roles'] ?? [], true)) {
    fwrite(STDERR, "ERROR the account must be an administrator.\n");
    exit(2);
}

// ---- Build the page set locally, reusing the manifest the other scripts use.
// parse_blocks/serialize_blocks are WordPress functions, so the manifest's
// content strings are used as authored instead.
define('ABSPATH', true);
if (!function_exists('home_url')) {
    function home_url(string $path = ''): string
    {
        global $achiever_site;
        return $achiever_site . $path;
    }
}
$GLOBALS['achiever_site'] = $site;

// Minimal shims for the manifest's final content-injection pass.
if (!function_exists('parse_blocks')) {
    require __DIR__ . '/rest-block-shims.php';
}

$pages = require __DIR__ . '/remaining-pages-manifest.php';

$targets = [
    'regular-art-classes', 'art-workshops', 'camps-courses', 'our-camps', 'short-courses',
    'artventurer', 'canvas-wizard', 'foundation-art-course', 'sketcher-master',
    'little-draws', 'junior-fine-arts', 'drawvinci', 'portfolio-art',
    'acrylic-painting', 'inks-calligraphy', 'clay-artivity', 'crafts-artivity',
    'digital-art', 'dry-medium-sketching', 'fashion-illustration', 'manga-drawing',
    'watercolour', 'express-art-classes',
    'arts-camp', 'crafts-camp', 'holiday-camp', 'artivity-camp',
];

$created = 0;
$updated = 0;
$skipped = 0;

foreach ($targets as $slug) {
    if (!isset($pages[$slug])) {
        printf("%-10s %s\n", 'NO-ENTRY', $slug);
        $skipped++;
        continue;
    }

    $page = $pages[$slug];

    // Look the page up by slug, including non-published states.
    [, $found] = rest('GET', $site . '/wp-json/wp/v2/pages?slug=' . rawurlencode($slug) . '&status=any&context=edit', $auth);
    $existing = is_array($found) && $found ? $found[0] : null;

    $payload = [
        'title' => $page['title'],
        'slug' => $slug,
        'status' => 'publish',
        'content' => $page['content'],
        // The install stores FSE template slugs without the .html suffix.
        'template' => $page['template'],
    ];

    if ($existing) {
        printf("%-10s %-24s id=%d\n", 'UPDATE', $slug, $existing['id']);
        if ($apply) {
            [$st, $res] = rest('POST', $site . '/wp-json/wp/v2/pages/' . $existing['id'], $auth, $payload);
            if ($st >= 400) {
                fwrite(STDERR, "  FAILED {$slug}: HTTP {$st} " . ($res['message'] ?? '') . "\n");
                exit(3);
            }
        }
        $updated++;
        continue;
    }

    printf("%-10s %-24s\n", 'CREATE', $slug);
    if ($apply) {
        [$st, $res] = rest('POST', $site . '/wp-json/wp/v2/pages', $auth, $payload);
        if ($st >= 400) {
            fwrite(STDERR, "  FAILED {$slug}: HTTP {$st} " . ($res['message'] ?? '') . "\n");
            exit(3);
        }
    }
    $created++;
}

printf("\nPLAN create=%d update=%d skipped=%d\n", $created, $updated, $skipped);

if (!$apply) {
    echo "\nDry run only. Re-run with --apply to write these changes.\n";
}
