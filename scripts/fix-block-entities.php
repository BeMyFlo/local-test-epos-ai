<?php

declare(strict_types=1);

/**
 * Repair HTML entities that were double-escaped into block attribute text.
 *
 * Block attributes are stored as JSON inside the block delimiter comment, where
 * an ampersand is written as the JSON escape &. When such content is saved
 * through a path that runs it through wp_kses / esc_html first, the "&" becomes
 * "&amp;" *inside the attribute value*, so the stored escape reads &amp;.
 * The renderer then escapes it a second time on output and the visitor sees
 * "Manga u0026amp; Comic Illustration" instead of "Manga & Comic Illustration".
 *
 * This walks every published page's blocks, decodes entities in every string
 * attribute, and writes the corrected content back.
 *
 * Usage:
 *   php fix-block-entities.php            # dry-run
 *   php fix-block-entities.php --apply    # writes the fix
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('CLI only.');
}

$apply = in_array('--apply', $argv, true);

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

$admins = get_users(['role' => 'administrator', 'number' => 1, 'orderby' => 'ID', 'order' => 'ASC']);
if (!$admins) {
    fwrite(STDERR, "ERROR no administrator account found to run as.\n");
    exit(1);
}
wp_set_current_user($admins[0]->ID);

if (!current_user_can('unfiltered_html')) {
    fwrite(STDERR, "ERROR administrator lacks unfiltered_html; the fix would be re-mangled on save.\n");
    exit(1);
}

/**
 * Decode entities until the string is stable, so a value escaped more than once
 * ("&amp;amp;") comes back as a single "&". Capped so a literal "&amp;" that a
 * client genuinely wants on the page cannot spin here.
 */
function achiever_decode_repeatedly(string $value): string
{
    for ($i = 0; $i < 5; $i++) {
        $decoded = html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        if ($decoded === $value) {
            break;
        }
        $value = $decoded;
    }

    // A save that ran the attribute through wp_unslash dropped the backslash of
    // the JSON escape, so "&amp;" survives as the bare text "u0026amp;"
    // and renders literally on the page. Re-resolve any such orphaned escape,
    // then strip the "amp;" tail the entity escaping left behind.
    $value = preg_replace_callback(
        '/u([0-9a-fA-F]{4})/',
        static function (array $matches): string {
            $char = json_decode('"\u' . $matches[1] . '"');
            return is_string($char) ? $char : $matches[0];
        },
        $value
    ) ?? $value;

    return html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

/**
 * Recursively decode every string in a block's attributes.
 */
function achiever_decode_attrs($value, array &$changes, string $path)
{
    if (is_string($value)) {
        $decoded = achiever_decode_repeatedly($value);
        if ($decoded !== $value) {
            $changes[] = $path . ': "' . $value . '" -> "' . $decoded . '"';
        }
        return $decoded;
    }

    if (is_array($value)) {
        foreach ($value as $key => $item) {
            $value[$key] = achiever_decode_attrs($item, $changes, $path . '.' . $key);
        }
    }

    return $value;
}

$pages = get_posts([
    'post_type' => 'page',
    'post_status' => ['publish', 'draft', 'private'],
    'numberposts' => -1,
    'orderby' => 'ID',
    'order' => 'ASC',
]);

echo 'MODE=' . ($apply ? 'APPLY' : 'DRY-RUN') . PHP_EOL;

$fixed_pages = 0;
$fixed_values = 0;

foreach ($pages as $page) {
    $blocks = parse_blocks($page->post_content);
    $changes = [];

    $walk = static function (array $blocks) use (&$walk, &$changes): array {
        foreach ($blocks as $index => $block) {
            if (!empty($block['attrs'])) {
                $blocks[$index]['attrs'] = achiever_decode_attrs(
                    $block['attrs'],
                    $changes,
                    ($block['blockName'] ?? 'block') . '[' . $index . ']'
                );
            }

            if (!empty($block['innerBlocks'])) {
                $blocks[$index]['innerBlocks'] = $walk($block['innerBlocks']);
            }
        }

        return $blocks;
    };

    $blocks = $walk($blocks);

    if (!$changes) {
        continue;
    }

    $fixed_pages++;
    $fixed_values += count($changes);

    echo PHP_EOL . 'PAGE id=' . $page->ID . ' slug=' . $page->post_name . PHP_EOL;
    foreach ($changes as $change) {
        echo '  FIX ' . $change . PHP_EOL;
    }

    if (!$apply) {
        continue;
    }

    wp_save_post_revision($page->ID);

    $result = wp_update_post([
        'ID' => $page->ID,
        'post_content' => wp_slash(serialize_blocks($blocks)),
    ], true);

    if (is_wp_error($result)) {
        fwrite(STDERR, 'ERROR page ' . $page->ID . ': ' . $result->get_error_message() . "\n");
        exit(3);
    }
}

echo PHP_EOL;

if (!$fixed_pages) {
    echo "No double-escaped attributes found; nothing to do.\n";
    exit(0);
}

if (!$apply) {
    echo "Dry-run only. Re-run with --apply to write the fix.\n";
    exit(0);
}

echo 'DONE pages=' . $fixed_pages . ' values=' . $fixed_values . PHP_EOL;
