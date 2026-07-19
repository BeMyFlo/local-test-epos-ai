<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('CLI only.');
}

$apply = in_array('--apply', $argv, true);
require_once '/var/www/html/wp-load.php';

$pages = require __DIR__ . '/remaining-pages-manifest.php';
if (!is_array($pages) || count($pages) !== 31) {
    fwrite(STDERR, "ERROR manifest must contain exactly 31 pages.\n");
    exit(2);
}

$targets = [];
foreach ($pages as $slug => $page) {
    $new_blocks = parse_blocks($page['content']);
    $old_blocks = $new_blocks;
    $anchored_forms = 0;

    foreach ($old_blocks as &$block) {
        if (
            ($block['blockName'] ?? '') === 'ai-zippy/course-enquiry-form'
            && ($block['attrs']['anchor'] ?? '') === 'course-enquiry'
        ) {
            unset($block['attrs']['anchor']);
            $anchored_forms++;
        }
    }
    unset($block);

    if ($anchored_forms === 0) {
        continue;
    }
    if ($anchored_forms !== 1) {
        fwrite(STDERR, "ERROR {$slug} must contain exactly one anchored enquiry form.\n");
        exit(2);
    }

    $targets[$slug] = [
        'template' => $page['template'],
        'old_content' => serialize_blocks($old_blocks),
        'new_content' => serialize_blocks($new_blocks),
    ];
}

if (count($targets) !== 21) {
    fwrite(STDERR, 'ERROR anchor sync must target exactly 21 pages, got ' . count($targets) . ".\n");
    exit(2);
}

$all_ids = get_posts([
    'post_type' => 'page',
    'post_status' => 'any',
    'posts_per_page' => -1,
    'fields' => 'ids',
]);
$snapshot = [];
foreach ($all_ids as $id) {
    $page = get_post((int) $id);
    $snapshot[(int) $id] = [
        'slug' => (string) $page->post_name,
        'length' => strlen((string) $page->post_content),
        'sha256' => hash('sha256', (string) $page->post_content),
        'template' => (string) get_post_meta((int) $id, '_wp_page_template', true),
    ];
}

$resolved_targets = [];
foreach ($targets as $slug => $target) {
    $current = get_page_by_path($slug, OBJECT, 'page');
    if (!$current) {
        fwrite(STDERR, "ERROR missing target {$slug}.\n");
        exit(2);
    }
    if ((string) $current->post_content !== $target['old_content']) {
        fwrite(STDERR, "ERROR content drift before anchor sync: {$slug}.\n");
        exit(2);
    }
    if (get_post_meta((int) $current->ID, '_wp_page_template', true) !== $target['template']) {
        fwrite(STDERR, "ERROR template drift before anchor sync: {$slug}.\n");
        exit(2);
    }
    $target['id'] = (int) $current->ID;
    $resolved_targets[$slug] = $target;
}
$targets = $resolved_targets;

echo 'MODE=' . ($apply ? 'APPLY' : 'DRY-RUN') . PHP_EOL;
echo 'ANCHOR_TARGETS=' . count($targets) . PHP_EOL;
foreach ($targets as $slug => $target) {
    echo 'SYNC ' . $slug . ' id=' . $target['id'] . PHP_EOL;
}

foreach ([2, 3, 4, 5, 6] as $protected_id) {
    $item = $snapshot[$protected_id] ?? null;
    if (!$item) {
        fwrite(STDERR, "ERROR protected page missing: {$protected_id}.\n");
        exit(2);
    }
    echo sprintf(
        "HASH id=%d slug=%s length=%d sha256=%s\n",
        $protected_id,
        $item['slug'],
        $item['length'],
        $item['sha256']
    );
}

if (!$apply) {
    echo "DRY_RUN_OK no database changes made.\n";
    exit(0);
}

global $wpdb;
$wpdb->query('START TRANSACTION');
$post_kses_priority = has_filter('content_save_pre', 'wp_filter_post_kses');
if ($post_kses_priority !== false) {
    remove_filter('content_save_pre', 'wp_filter_post_kses', $post_kses_priority);
}

try {
    foreach ($targets as $slug => $target) {
        $result = wp_update_post([
            'ID' => $target['id'],
            'post_content' => wp_slash($target['new_content']),
        ], true);
        if (is_wp_error($result)) {
            throw new RuntimeException($result->get_error_message());
        }
        clean_post_cache($target['id']);
    }

    foreach ($snapshot as $id => $before) {
        if (in_array($id, array_column($targets, 'id'), true)) {
            continue;
        }
        clean_post_cache($id);
        $current = get_post($id);
        $content = $current ? (string) $current->post_content : '';
        if (
            !$current
            || strlen($content) !== $before['length']
            || hash('sha256', $content) !== $before['sha256']
            || get_post_meta($id, '_wp_page_template', true) !== $before['template']
        ) {
            throw new RuntimeException("non-target page changed: id={$id} slug={$before['slug']}");
        }
    }

    foreach ($targets as $slug => $target) {
        clean_post_cache($target['id']);
        $current = get_post($target['id']);
        if (
            !$current
            || (string) $current->post_content !== $target['new_content']
            || get_post_meta($target['id'], '_wp_page_template', true) !== $target['template']
        ) {
            throw new RuntimeException("anchor target verification failed: {$slug}");
        }
    }

    $wpdb->query('COMMIT');
    if ($post_kses_priority !== false) {
        add_filter('content_save_pre', 'wp_filter_post_kses', $post_kses_priority);
    }
    echo 'APPLY_OK SYNCED=' . count($targets) . " non-target hashes unchanged.\n";
} catch (Throwable $error) {
    $wpdb->query('ROLLBACK');
    if ($post_kses_priority !== false) {
        add_filter('content_save_pre', 'wp_filter_post_kses', $post_kses_priority);
    }
    fwrite(STDERR, 'ERROR transaction rolled back: ' . $error->getMessage() . PHP_EOL);
    exit(3);
}
