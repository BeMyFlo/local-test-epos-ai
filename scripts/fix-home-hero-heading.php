<?php

declare(strict_types=1);

/**
 * Repair home hero slide headings whose line break was flattened on save.
 *
 * The heading is authored as "REGULAR\nART CLASSES". A previous editor save
 * dropped the backslash, leaving the literal "REGULARnART CLASSES" on the page.
 * Because no backslash survives, the renderer cannot detect it — the stored
 * value has to be corrected directly.
 *
 * Usage:
 *   php fix-home-hero-heading.php            # dry-run
 *   php fix-home-hero-heading.php --apply    # writes the fix
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

// Known flattened headings mapped to the intended two-line version.
$repairs = [
    'REGULARnART CLASSES' => "REGULAR\nART CLASSES",
    'ARTY EVENTSn& PARTIES' => "ARTY EVENTS\n& PARTIES",
    'CAMPS &nCOURSES' => "CAMPS &\nCOURSES",
];

$page = get_page_by_path('home', OBJECT, 'page');
if (!$page) {
    $front_id = (int) get_option('page_on_front');
    $page = $front_id ? get_post($front_id) : null;
}

if (!$page) {
    fwrite(STDERR, "ERROR home page not found.\n");
    exit(1);
}

$blocks = parse_blocks($page->post_content);
$changes = [];

$walk = static function (array &$blocks) use (&$walk, $repairs, &$changes): void {
    foreach ($blocks as &$block) {
        if (($block['blockName'] ?? '') === 'ai-zippy/home-hero' && !empty($block['attrs']['slides'])) {
            foreach ($block['attrs']['slides'] as $index => &$slide) {
                $heading = $slide['heading'] ?? '';
                if (isset($repairs[$heading])) {
                    $changes[] = sprintf('slide%d: "%s" -> two lines', $index, $heading);
                    $slide['heading'] = $repairs[$heading];
                }
            }
            unset($slide);
        }

        if (!empty($block['innerBlocks'])) {
            $walk($block['innerBlocks']);
        }
    }
    unset($block);
};

$walk($blocks);

echo 'MODE=' . ($apply ? 'APPLY' : 'DRY-RUN') . PHP_EOL;
echo 'PAGE id=' . $page->ID . ' slug=' . $page->post_name . PHP_EOL;

if (!$changes) {
    echo "No flattened headings found; nothing to do.\n";
    exit(0);
}

foreach ($changes as $change) {
    echo 'FIX ' . $change . PHP_EOL;
}

if (!$apply) {
    echo "\nDry-run only. Re-run with --apply to write the fix.\n";
    exit(0);
}

wp_save_post_revision($page->ID);

$result = wp_update_post([
    'ID' => $page->ID,
    'post_content' => wp_slash(serialize_blocks($blocks)),
], true);

if (is_wp_error($result)) {
    fwrite(STDERR, 'ERROR ' . $result->get_error_message() . "\n");
    exit(3);
}

echo "\nDONE fixed=" . count($changes) . "\n";
