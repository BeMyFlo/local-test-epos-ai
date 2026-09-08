<?php

declare(strict_types=1);

/**
 * Sync the pages the client flagged as out of line with the sitemap.
 *
 * setup-remaining-pages.php intentionally refuses to touch pages that already
 * hold content, which is the right default for a first-run seeder. This script
 * covers the follow-up case: a small, explicit set of pages whose structure must
 * be brought back in line with the sitemap even though they are populated.
 *
 * Every overwrite stores the previous content as a WordPress revision first, so
 * an edit can be rolled back from the admin UI.
 *
 * Usage:
 *   php sync-sitemap-pages.php            # dry-run, prints the plan
 *   php sync-sitemap-pages.php --apply    # writes the changes
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('CLI only.');
}

$apply = in_array('--apply', $argv, true);
// Detail pages are normally left alone once populated. --refresh-copy re-applies
// the manifest to them, used when the source copy (the client's doc) changes.
$refresh_copy = in_array('--refresh-copy', $argv, true);
// Locate wp-load.php: an explicit WP_ROOT wins, otherwise walk up from this
// directory (covers a scripts/ folder beside the WordPress root, and the Docker
// layout where the root is /var/www/html).
$wp_load = '';
$candidates = [];

if ($env_root = getenv('WP_ROOT')) {
    $candidates[] = rtrim($env_root, '/') . '/wp-load.php';
}

$dir = __DIR__;
for ($i = 0; $i < 5; $i++) {
    $candidates[] = $dir . '/wp-load.php';
    $candidates[] = $dir . '/src/wp-load.php';
    $parent = dirname($dir);
    if ($parent === $dir) {
        break;
    }
    $dir = $parent;
}

$candidates[] = '/var/www/html/wp-load.php';

foreach ($candidates as $candidate) {
    if (is_file($candidate)) {
        $wp_load = $candidate;
        break;
    }
}

if (!is_file($wp_load)) {
    fwrite(STDERR, "ERROR wp-load.php not found. Set WP_ROOT to the WordPress root.\n");
    exit(1);
}

require_once $wp_load;

$manifest_path = __DIR__ . '/remaining-pages-manifest.php';
if (!is_file($manifest_path)) {
    fwrite(STDERR, "ERROR manifest not found at {$manifest_path}.\n");
    exit(1);
}

// Run as an administrator. Without unfiltered_html, KSES rewrites the "&" inside
// block attribute JSON to "&amp;", which then renders as "Age 3 &amp; up".
$admins = get_users(['role' => 'administrator', 'number' => 1, 'orderby' => 'ID', 'order' => 'ASC']);
if (!$admins) {
    fwrite(STDERR, "ERROR no administrator account found to run as.\n");
    exit(1);
}
wp_set_current_user($admins[0]->ID);

if (!current_user_can('unfiltered_html')) {
    fwrite(STDERR, "ERROR administrator lacks unfiltered_html; block JSON would be mangled.\n");
    exit(1);
}

$pages = require $manifest_path;

/**
 * Compare stored and generated content ignoring differences WordPress itself
 * introduces on save (slashing, and &amp; entity normalisation inside block
 * attribute JSON). Without this every run reports a false UPDATE.
 */
function achiever_content_matches(string $stored, string $generated): bool
{
    // Content is written pre-slashed and stored verbatim, so compare directly;
    // unslashing here would strip real backslashes from JSON escapes like \u0026
    // and report an endless false UPDATE.
    return $stored === $generated;
}

/**
 * Decode HTML entities inside block attribute JSON.
 *
 * The manifest builds attributes with json_encode, so an ampersand arrives as
 * the escape \u0026. Left alone, WordPress stores it as \u0026amp; and the
 * front end renders "Age 3 &amp; up". Decoding before the write keeps a literal
 * "&" in the attribute so esc_html() escapes it exactly once on output.
 */
function achiever_decode_block_entities(string $content): string
{
    return serialize_blocks(achiever_decode_blocks(parse_blocks($content)));
}

/**
 * Decode every string attribute of a block tree.
 */
function achiever_decode_blocks(array $blocks): array
{
    foreach ($blocks as $index => $block) {
        if (!empty($block['attrs'])) {
            $blocks[$index]['attrs'] = achiever_decode_value($block['attrs']);
        }

        if (!empty($block['innerBlocks'])) {
            $blocks[$index]['innerBlocks'] = achiever_decode_blocks($block['innerBlocks']);
        }
    }

    return $blocks;
}

/**
 * Recover an attribute value that a previous save mangled.
 *
 * Two separate corruptions stack up here:
 *
 *  1. esc_html/KSES turns the "&" of the JSON escape into "&amp;", so the
 *     stored escape reads as the six characters "u0026amp;" once the
 *     backslash is accounted for.
 *  2. wp_unslash on the next save eats the leading backslash, leaving the bare
 *     text "u0026amp;" which renders literally on the card.
 *
 * html_entity_decode alone cannot fix either one, because at that point
 * "u0026amp;" is a JSON escape followed by literal characters rather than an
 * HTML entity. Working on the decoded attribute value instead lets us undo the
 * entity escaping and re-resolve any escape that lost its backslash.
 */
function achiever_decode_value($value)
{
    if (is_array($value)) {
        foreach ($value as $key => $item) {
            $value[$key] = achiever_decode_value($item);
        }

        return $value;
    }

    if (!is_string($value)) {
        return $value;
    }

    // Repeat so a value escaped more than once ("&amp;amp;") collapses to a
    // single "&". Capped so a literal "&amp;" cannot spin here.
    for ($i = 0; $i < 5; $i++) {
        $decoded = html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        if ($decoded === $value) {
            break;
        }
        $value = $decoded;
    }

    // Re-resolve escapes whose leading backslash was stripped on an earlier
    // save, so a stray "u0026" becomes "&" again.
    $value = preg_replace_callback(
        '/u([0-9a-fA-F]{4})/',
        static function (array $matches): string {
            $char = json_decode('"\u' . $matches[1] . '"');
            return is_string($char) ? $char : $matches[0];
        },
        $value
    ) ?? $value;

    // The recovered "&" may itself still carry the "amp;" tail.
    return html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

// Only these slugs may be rewritten over existing content. Everything else is
// left to the normal seeder so this script cannot quietly reshape the site.
$sync_slugs = [
    'regular-art-classes',
    'art-workshops',
    'camps-courses',
    'our-camps',
    'short-courses',
];

// about-us is not rewritten from the manifest: the live page carries bespoke
// copy, a mascot image and extra sections. It only gains the gallery block,
// inserted directly after about-content, and is skipped if already present.
$insert_after = [
    'about-us' => [
        'after' => 'ai-zippy/about-content',
        'block' => '<!-- wp:ai-zippy/course-gallery {"heading":"Your Smile, Our Passion"} /-->',
        'skip_if_contains' => 'ai-zippy/course-gallery',
    ],
];

// Detail pages behind every "See More" link. These are safe to fill because a
// missing or empty page is a dead link either way; a populated one is left alone.
$detail_slugs = [
    // Regular art classes (sitemap slide 20).
    'artventurer', 'canvas-wizard', 'foundation-art-course', 'sketcher-master',
    'little-draws', 'junior-fine-arts', 'drawvinci', 'portfolio-art',
    // Workshops (sitemap slide 30).
    'acrylic-painting', 'inks-calligraphy', 'clay-artivity', 'crafts-artivity',
    'digital-art', 'dry-medium-sketching', 'fashion-illustration',
    'manga-drawing', 'watercolour', 'express-art-classes',
    // Camps (sitemap slide 38).
    'arts-camp', 'crafts-camp', 'holiday-camp', 'artivity-camp',
];

$plan = [];

foreach ($sync_slugs as $slug) {
    if (!isset($pages[$slug])) {
        fwrite(STDERR, "ERROR {$slug} missing from manifest.\n");
        exit(2);
    }

    $page = $pages[$slug];
    $content = achiever_decode_block_entities(serialize_blocks(parse_blocks($page['content'])));
    $existing = get_page_by_path($slug, OBJECT, 'page');

    $plan[$slug] = [
        'page' => $page,
        'content' => $content,
        'existing' => $existing,
        'action' => $existing ? (achiever_content_matches((string) $existing->post_content, $content) ? 'UNCHANGED' : 'UPDATE') : 'CREATE',
    ];
}

$detail_plan = [];

foreach ($detail_slugs as $slug) {
    if (!isset($pages[$slug])) {
        $detail_plan[$slug] = ['action' => 'NO-MANIFEST', 'existing' => null, 'content' => '', 'page' => null];
        continue;
    }

    $page = $pages[$slug];
    $content = achiever_decode_block_entities(serialize_blocks(parse_blocks($page['content'])));
    $existing = get_page_by_path($slug, OBJECT, 'page');

    if (!$existing) {
        $action = 'CREATE';
    } elseif (trim((string) $existing->post_content) === '') {
        $action = 'FILL';
    } elseif ($refresh_copy && !achiever_content_matches((string) $existing->post_content, $content)) {
        $action = 'REFRESH';
    } else {
        $action = 'KEEP';
    }

    $detail_plan[$slug] = [
        'action' => $action,
        'existing' => $existing,
        'content' => $content,
        'page' => $page,
    ];
}

$insert_plan = [];

foreach ($insert_after as $slug => $spec) {
    $existing = get_page_by_path($slug, OBJECT, 'page');

    if (!$existing) {
        $insert_plan[$slug] = ['action' => 'MISSING', 'existing' => null, 'content' => ''];
        continue;
    }

    $current = (string) $existing->post_content;

    if (strpos($current, $spec['skip_if_contains']) !== false) {
        $insert_plan[$slug] = ['action' => 'HAS-BLOCK', 'existing' => $existing, 'content' => $current];
        continue;
    }

    $blocks = parse_blocks($current);
    $rebuilt = [];
    $inserted = false;

    foreach ($blocks as $block) {
        $rebuilt[] = $block;
        if (!$inserted && ($block['blockName'] ?? '') === $spec['after']) {
            foreach (parse_blocks($spec['block']) as $new_block) {
                if ($new_block['blockName']) {
                    $rebuilt[] = $new_block;
                    $inserted = true;
                }
            }
        }
    }

    if (!$inserted) {
        $insert_plan[$slug] = ['action' => 'ANCHOR-MISSING', 'existing' => $existing, 'content' => $current];
        continue;
    }

    $insert_plan[$slug] = [
        'action' => 'INSERT',
        'existing' => $existing,
        'content' => serialize_blocks($rebuilt),
    ];
}

echo 'MODE=' . ($apply ? 'APPLY' : 'DRY-RUN') . PHP_EOL;

foreach ($plan as $slug => $item) {
    $existing = $item['existing'];
    printf(
        "%-10s %-24s %s%s\n",
        $item['action'],
        $slug,
        $existing ? 'id=' . $existing->ID : 'new',
        $existing ? ' old_len=' . strlen((string) $existing->post_content) . ' new_len=' . strlen($item['content']) : ''
    );
}

$detail_summary = ['CREATE' => 0, 'FILL' => 0, 'REFRESH' => 0, 'KEEP' => 0, 'NO-MANIFEST' => 0];
foreach ($detail_plan as $slug => $item) {
    $detail_summary[$item['action']]++;
    if (in_array($item['action'], ['CREATE', 'FILL', 'REFRESH'], true)) {
        printf(
            "%-10s %-24s %s\n",
            $item['action'],
            $slug,
            $item['existing'] ? 'id=' . $item['existing']->ID : 'new'
        );
    }
}
printf(
    "DETAIL     create=%d fill=%d refresh=%d keep=%d no-manifest=%d\n",
    $detail_summary['CREATE'],
    $detail_summary['FILL'],
    $detail_summary['REFRESH'],
    $detail_summary['KEEP'],
    $detail_summary['NO-MANIFEST']
);

foreach ($insert_plan as $slug => $item) {
    $existing = $item['existing'];
    printf(
        "%-10s %-24s %s%s\n",
        $item['action'],
        $slug,
        $existing ? 'id=' . $existing->ID : 'not found',
        $item['action'] === 'INSERT'
            ? ' old_len=' . strlen((string) $existing->post_content) . ' new_len=' . strlen($item['content'])
            : ''
    );
}

if (!$apply) {
    echo "\nDry-run only. Re-run with --apply to write these changes.\n";
    exit(0);
}

$updated = 0;
$created = 0;

foreach ($plan as $slug => $item) {
    if ($item['action'] === 'UNCHANGED') {
        continue;
    }

    $existing = $item['existing'];

    if ($existing) {
        // Snapshot the current content so the change is reversible in wp-admin.
        wp_save_post_revision($existing->ID);

        $result = wp_update_post([
            'ID' => $existing->ID,
            'post_content' => wp_slash($item['content']),
            'post_status' => $existing->post_status === 'trash' ? 'publish' : $existing->post_status,
        ], true);

        if (is_wp_error($result)) {
            fwrite(STDERR, "ERROR updating {$slug}: " . $result->get_error_message() . "\n");
            exit(3);
        }

        update_post_meta($existing->ID, '_wp_page_template', $item['page']['template']);
        echo "UPDATED {$slug} id={$existing->ID}\n";
        $updated++;
        continue;
    }

    $new_id = wp_insert_post([
        'post_type' => 'page',
        'post_status' => 'publish',
        'post_title' => $item['page']['title'],
        'post_name' => $slug,
        'post_content' => wp_slash($item['content']),
    ], true);

    if (is_wp_error($new_id)) {
        fwrite(STDERR, "ERROR creating {$slug}: " . $new_id->get_error_message() . "\n");
        exit(3);
    }

    update_post_meta($new_id, '_wp_page_template', $item['page']['template']);
    echo "CREATED {$slug} id={$new_id}\n";
    $created++;
}

$detail_created = 0;
$detail_filled = 0;

foreach ($detail_plan as $slug => $item) {
    if (!in_array($item['action'], ['CREATE', 'FILL', 'REFRESH'], true)) {
        continue;
    }

    if ($item['action'] === 'FILL' || $item['action'] === 'REFRESH') {
        $existing = $item['existing'];
        wp_save_post_revision($existing->ID);

        $result = wp_update_post([
            'ID' => $existing->ID,
            'post_content' => wp_slash($item['content']),
            'post_status' => $existing->post_status === 'trash' ? 'publish' : $existing->post_status,
        ], true);

        if (is_wp_error($result)) {
            fwrite(STDERR, "ERROR filling {$slug}: " . $result->get_error_message() . "\n");
            exit(3);
        }

        update_post_meta($existing->ID, '_wp_page_template', $item['page']['template']);
        echo ($item['action'] === 'REFRESH' ? 'REFRESHED' : 'FILLED') . " {$slug} id={$existing->ID}\n";
        $detail_filled++;
        continue;
    }

    $new_id = wp_insert_post([
        'post_type' => 'page',
        'post_status' => 'publish',
        'post_title' => $item['page']['title'],
        'post_name' => $slug,
        'post_content' => wp_slash($item['content']),
    ], true);

    if (is_wp_error($new_id)) {
        fwrite(STDERR, "ERROR creating {$slug}: " . $new_id->get_error_message() . "\n");
        exit(3);
    }

    update_post_meta($new_id, '_wp_page_template', $item['page']['template']);
    echo "CREATED {$slug} id={$new_id}\n";
    $detail_created++;
}

$inserted_count = 0;

foreach ($insert_plan as $slug => $item) {
    if ($item['action'] !== 'INSERT') {
        echo "SKIPPED {$slug} ({$item['action']})\n";
        continue;
    }

    $existing = $item['existing'];
    wp_save_post_revision($existing->ID);

    $result = wp_update_post([
        'ID' => $existing->ID,
        'post_content' => wp_slash($item['content']),
    ], true);

    if (is_wp_error($result)) {
        fwrite(STDERR, "ERROR inserting into {$slug}: " . $result->get_error_message() . "\n");
        exit(3);
    }

    echo "INSERTED {$slug} id={$existing->ID}\n";
    $inserted_count++;
}

echo "\nDONE updated={$updated} created={$created} inserted={$inserted_count} detail_created={$detail_created} detail_filled={$detail_filled}\n";
