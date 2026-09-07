<?php

declare(strict_types=1);

/**
 * Apply the sitemap page structure to a remote WordPress over the REST API.
 *
 * The companion scripts run beside wp-load.php, which needs shell access. This
 * one needs only an Application Password, so it works against a host that
 * exposes wp-admin but no SSH.
 *
 * Authenticates one of two ways:
 *   - an Application Password (wp-admin → Users → Profile), passed to --pass
 *   - a normal wp-admin login, with --login, when the host has not enabled
 *     Application Passwords; this signs in and reuses the session cookie
 *
 * Usage:
 *   php sync-via-rest.php --site https://host --user NAME --pass "xxxx xxxx ..."
 *   php sync-via-rest.php --site https://host --user NAME --pass "secret" --login
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
$use_login = false;

for ($i = 1; $i < $argc; $i++) {
    $arg = $argv[$i];
    if ($arg === '--apply') {
        $apply = true;
        continue;
    }
    if ($arg === '--login') {
        $use_login = true;
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

// Cookie-auth state, populated by achiever_login() when --login is used.
$GLOBALS['achiever_cookie_jar'] = '';
$GLOBALS['achiever_nonce'] = '';

/**
 * Sign in through wp-login.php and capture the session cookie plus the REST
 * nonce, for hosts where Application Passwords are unavailable.
 */
function achiever_login(string $site, string $user, string $pass): void
{
    // The curl CLI is used rather than ext/curl's cookie jar, which cannot
    // always write its file depending on how PHP is sandboxed.
    $jar = tempnam(sys_get_temp_dir(), 'azcookie');
    $GLOBALS['achiever_cookie_jar'] = $jar;

    $post = http_build_query([
        'log' => $user,
        'pwd' => $pass,
        'wp-submit' => 'Log In',
        'redirect_to' => $site . '/wp-admin/',
        'testcookie' => '1',
    ]);

    exec(sprintf(
        'curl -s -o /dev/null --max-time 60 -c %s -b %s -d %s %s',
        escapeshellarg($jar),
        escapeshellarg($jar),
        escapeshellarg($post),
        escapeshellarg($site . '/wp-login.php')
    ));

    $cookies = (string) @file_get_contents($jar);
    if (!str_contains($cookies, 'wordpress_logged_in')) {
        fwrite(STDERR, "ERROR wp-login.php did not return a session cookie.\n");
        fwrite(STDERR, "Check the username and password, and that the host allows logins.\n");
        exit(2);
    }

    // The REST API needs the nonce wp-admin prints for the logged-in user.
    $html = shell_exec(sprintf(
        'curl -s --max-time 60 -b %s %s',
        escapeshellarg($jar),
        escapeshellarg($site . '/wp-admin/')
    )) ?? '';

    if (!preg_match('/"nonce":"([a-f0-9]+)"/', $html, $m)) {
        fwrite(STDERR, "ERROR could not read the REST nonce from wp-admin.\n");
        exit(2);
    }

    $GLOBALS['achiever_nonce'] = $m[1];
}


/**
 * Perform a REST request, returning [status, decoded body].
 */
function rest(string $method, string $url, string $auth, ?array $body = null): array
{
    // Shelled out to curl so the same cookie jar works for both auth modes.
    $cmd = ['curl', '-s', '-w', '\n%{http_code}', '--max-time', '60', '-X', $method];

    if ($GLOBALS['achiever_cookie_jar'] !== '') {
        $cmd[] = '-b';
        $cmd[] = $GLOBALS['achiever_cookie_jar'];
        $cmd[] = '-c';
        $cmd[] = $GLOBALS['achiever_cookie_jar'];
        $cmd[] = '-H';
        $cmd[] = 'X-WP-Nonce: ' . $GLOBALS['achiever_nonce'];
    } else {
        $cmd[] = '-H';
        $cmd[] = 'Authorization: Basic ' . $auth;
    }

    $cmd[] = '-H';
    $cmd[] = 'Accept: application/json';

    if ($body !== null) {
        $cmd[] = '-H';
        $cmd[] = 'Content-Type: application/json';
        $cmd[] = '--data-binary';
        $cmd[] = json_encode($body, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    $cmd[] = $url;

    $shell = implode(' ', array_map('escapeshellarg', $cmd));
    $raw = shell_exec($shell);

    if ($raw === null) {
        fwrite(STDERR, "ERROR curl failed for {$url}\n");
        exit(4);
    }

    // The status code is appended on its own final line by -w.
    $split = strrpos($raw, "\n");
    $status = (int) trim(substr($raw, $split + 1));
    $payload = $split === false ? '' : substr($raw, 0, $split);

    return [$status, json_decode($payload, true)];
}


if ($use_login) {
    achiever_login($site, $opts['user'], $opts['pass']);
}

// ---- Verify credentials before touching anything.
[$status, $me] = rest('GET', $site . '/wp-json/wp/v2/users/me?context=edit', $auth);
if ($status !== 200) {
    fwrite(STDERR, "ERROR authentication failed (HTTP {$status}).\n");
    fwrite(STDERR, $use_login
        ? "Check the username and password.\n"
        : "Check the username and Application Password, or retry with --login.\n");
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
