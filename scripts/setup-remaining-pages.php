<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('CLI only.');
}

$apply = in_array('--apply', $argv, true);
$wp_load = '/var/www/html/wp-load.php';

if (!is_file($wp_load)) {
    fwrite(STDERR, "ERROR wp-load.php not found at {$wp_load}.\n");
    exit(1);
}

require_once $wp_load;

/**
 * Recursively check parsed block attributes for an exact string value.
 */
function achiever_attributes_contain(array $attributes, string $expected): bool
{
    foreach ($attributes as $value) {
        if (is_string($value) && $value === $expected) {
            return true;
        }
        if (is_array($value) && achiever_attributes_contain($value, $expected)) {
            return true;
        }
    }
    return false;
}

/**
 * Parse and reserialize trusted block comments using WordPress conventions.
 */
function achiever_canonical_block_content(string $content): string
{
    return serialize_blocks(parse_blocks($content));
}

$manifest_path = __DIR__ . '/remaining-pages-manifest.php';
if (!is_file($manifest_path)) {
    fwrite(STDERR, "ERROR manifest not found at {$manifest_path}.\n");
    exit(1);
}

$pages = require $manifest_path;
$locked_slugs = ['home', 'regular-art-classes', 'foundation-art-course'];
$expected_count = 31;
$expected_populate = 8;
$expected_create = 23;

if (!is_array($pages) || count($pages) !== $expected_count) {
    fwrite(STDERR, 'ERROR manifest must contain exactly ' . $expected_count . " pages.\n");
    exit(2);
}

foreach ($locked_slugs as $locked_slug) {
    if (isset($pages[$locked_slug])) {
        fwrite(STDERR, "ERROR locked slug {$locked_slug} must not exist in manifest.\n");
        exit(2);
    }
}

$canonical_pages = [];
foreach ($pages as $slug => $page) {
    $page['canonical_content'] = achiever_canonical_block_content($page['content']);
    $canonical_pages[$slug] = $page;
}
$pages = $canonical_pages;

$single_session_blocks = parse_blocks($pages['single-session-art-classes']['canonical_content']);
$camps_blocks = parse_blocks($pages['camps-courses']['canonical_content']);
$express_blocks = parse_blocks($pages['express-art-classes']['canonical_content']);
$single_session_ampersand_ok = false;
$camps_ampersand_ok = false;
$express_newline_ok = false;

foreach ($single_session_blocks as $block) {
    $single_session_ampersand_ok = $single_session_ampersand_ok
        || achiever_attributes_contain($block['attrs'] ?? [], 'Ages 4 & up');
}
foreach ($camps_blocks as $block) {
    $camps_ampersand_ok = $camps_ampersand_ok
        || achiever_attributes_contain($block['attrs'] ?? [], 'OUR CAMPS & COURSES');
}
foreach ($express_blocks as $block) {
    $description = $block['attrs']['description'] ?? '';
    $express_newline_ok = $express_newline_ok
        || (is_string($description) && str_contains($description, "\n\nDiscover our Express Art"));
}

if (!$single_session_ampersand_ok || !$camps_ampersand_ok || !$express_newline_ok) {
    fwrite(STDERR, "ERROR block serialization semantic proof failed.\n");
    exit(2);
}

$existing_ids = get_posts([
    'post_type' => 'page',
    'post_status' => 'any',
    'posts_per_page' => -1,
    'fields' => 'ids',
    'orderby' => 'ID',
    'order' => 'ASC',
]);
$nonempty_snapshot = [];

foreach ($existing_ids as $existing_id) {
    $existing_page = get_post((int) $existing_id);
    if (!$existing_page || trim((string) $existing_page->post_content) === '') {
        continue;
    }
    $nonempty_snapshot[(int) $existing_id] = [
        'slug' => (string) $existing_page->post_name,
        'length' => strlen((string) $existing_page->post_content),
        'sha256' => hash('sha256', (string) $existing_page->post_content),
    ];
}

$plan = [];
$counts = ['POPULATE' => 0, 'CREATE' => 0, 'SKIP' => 0, 'DRIFT' => 0];

foreach ($pages as $slug => $page) {
    if (!is_string($slug) || sanitize_title($slug) !== $slug) {
        fwrite(STDERR, "ERROR invalid manifest slug.\n");
        exit(2);
    }
    foreach (['title', 'template', 'content'] as $required_key) {
        if (!isset($page[$required_key]) || !is_string($page[$required_key])) {
            fwrite(STDERR, "ERROR {$slug} is missing {$required_key}.\n");
            exit(2);
        }
    }

    $existing = get_page_by_path($slug, OBJECT, 'page');
    $expected_existing = !empty($page['existing_empty_only']);
    $action = 'DRIFT';
    $reason = '';

    if ($expected_existing) {
        if (!$existing) {
            $reason = 'expected existing empty page is missing';
        } elseif ($existing->post_status === 'trash') {
            $reason = 'expected page is in trash';
        } elseif (trim((string) $existing->post_content) !== '') {
            $reason = 'expected page is no longer empty';
        } else {
            $action = 'POPULATE';
        }
    } elseif ($existing) {
        $reason = 'new target slug already exists';
    } else {
        $action = 'CREATE';
    }

    $counts[$action]++;
    $plan[$slug] = [
        'action' => $action,
        'reason' => $reason,
        'existing_id' => $existing ? (int) $existing->ID : 0,
        'page' => $page,
    ];
}

echo 'MODE=' . ($apply ? 'APPLY' : 'DRY-RUN') . PHP_EOL;
echo 'MANIFEST_COUNT=' . count($pages) . PHP_EOL;
foreach ($plan as $slug => $item) {
    echo $item['action'] . ' ' . $slug;
    if ($item['existing_id']) {
        echo ' id=' . $item['existing_id'];
    }
    if ($item['reason']) {
        echo ' reason=' . $item['reason'];
    }
    echo PHP_EOL;
}
echo sprintf(
    "PLAN POPULATE=%d CREATE=%d SKIP=%d DRIFT=%d\n",
    $counts['POPULATE'],
    $counts['CREATE'],
    $counts['SKIP'],
    $counts['DRIFT']
);
echo 'NONEMPTY_SNAPSHOT=' . count($nonempty_snapshot) . PHP_EOL;
echo "PROOF single-session-ampersand=ok camps-courses-ampersand=ok express-newline=ok\n";
foreach ($nonempty_snapshot as $id => $snapshot) {
    echo sprintf(
        "HASH id=%d slug=%s length=%d sha256=%s\n",
        $id,
        $snapshot['slug'],
        $snapshot['length'],
        $snapshot['sha256']
    );
}

if (
    $counts['POPULATE'] !== $expected_populate
    || $counts['CREATE'] !== $expected_create
    || $counts['SKIP'] !== 0
    || $counts['DRIFT'] !== 0
) {
    fwrite(STDERR, "ERROR initial plan must be exactly POPULATE=8 CREATE=23 SKIP=0 DRIFT=0.\n");
    exit(2);
}

if (!$apply) {
    echo "DRY_RUN_OK no database changes made.\n";
    exit(0);
}

global $wpdb;
$wpdb->query('START TRANSACTION');
$created = 0;
$populated = 0;
$post_kses_priority = has_filter('content_save_pre', 'wp_filter_post_kses');
if ($post_kses_priority !== false) {
    remove_filter('content_save_pre', 'wp_filter_post_kses', $post_kses_priority);
}

try {
    foreach ($plan as $slug => $item) {
        $page = $item['page'];
        if ($item['action'] === 'POPULATE') {
            $result = wp_update_post([
                'ID' => $item['existing_id'],
                'post_content' => wp_slash($page['canonical_content']),
            ], true);
            if (is_wp_error($result)) {
                throw new RuntimeException($result->get_error_message());
            }
            $page_id = (int) $result;
            $populated++;
        } else {
            $result = wp_insert_post([
                'post_type' => 'page',
                'post_status' => 'publish',
                'post_title' => $page['title'],
                'post_name' => $slug,
                'post_content' => wp_slash($page['canonical_content']),
            ], true);
            if (is_wp_error($result)) {
                throw new RuntimeException($result->get_error_message());
            }
            $page_id = (int) $result;
            $created++;
        }

        update_post_meta($page_id, '_wp_page_template', $page['template']);
        clean_post_cache($page_id);
    }

    foreach ($nonempty_snapshot as $id => $snapshot) {
        clean_post_cache($id);
        $current = get_post($id);
        $current_content = $current ? (string) $current->post_content : '';
        if (
            !$current
            || strlen($current_content) !== $snapshot['length']
            || hash('sha256', $current_content) !== $snapshot['sha256']
        ) {
            throw new RuntimeException("pre-existing non-empty page changed: id={$id} slug={$snapshot['slug']}");
        }
    }

    foreach ($pages as $slug => $page) {
        $current = get_page_by_path($slug, OBJECT, 'page');
        if (
            !$current
            || (string) $current->post_content !== $page['canonical_content']
            || get_post_meta((int) $current->ID, '_wp_page_template', true) !== $page['template']
        ) {
            throw new RuntimeException("target verification failed: {$slug}");
        }
    }

    if ($created !== $expected_create || $populated !== $expected_populate) {
        throw new RuntimeException('applied action count mismatch');
    }

    $wpdb->query('COMMIT');
    if ($post_kses_priority !== false) {
        add_filter('content_save_pre', 'wp_filter_post_kses', $post_kses_priority);
    }
    echo "APPLY_OK POPULATED={$populated} CREATED={$created} locked hashes unchanged.\n";
} catch (Throwable $error) {
    $wpdb->query('ROLLBACK');
    if ($post_kses_priority !== false) {
        add_filter('content_save_pre', 'wp_filter_post_kses', $post_kses_priority);
    }
    fwrite(STDERR, 'ERROR transaction rolled back: ' . $error->getMessage() . PHP_EOL);
    exit(3);
}
