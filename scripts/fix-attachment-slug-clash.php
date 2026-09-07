<?php

declare(strict_types=1);

/**
 * Resolve page slugs that are occupied by media attachments.
 *
 * Four class slugs (canvas-wizard, sketcher-master, drawvinci, watercolour)
 * were held by image attachments, so /canvas-wizard/ redirected to the image
 * file instead of serving the class page. get_page_by_path() still matches an
 * attachment, so the seeding pass wrote page content into those attachments.
 *
 * This script restores each attachment (clears the injected block content and
 * frees the slug by suffixing it with -image), then creates the real page.
 *
 * Usage:
 *   php fix-attachment-slug-clash.php            # dry-run
 *   php fix-attachment-slug-clash.php --apply    # writes the fix
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

$manifest_path = __DIR__ . '/remaining-pages-manifest.php';
$pages = require $manifest_path;

$slugs = ['canvas-wizard', 'sketcher-master', 'drawvinci', 'watercolour'];
$plan = [];

foreach ($slugs as $slug) {
    if (!isset($pages[$slug])) {
        fwrite(STDERR, "ERROR {$slug} missing from manifest.\n");
        exit(2);
    }

    $found = get_posts([
        'name' => $slug,
        'post_type' => 'any',
        'post_status' => 'any',
        'numberposts' => -1,
    ]);

    $attachment = null;
    $page = null;

    foreach ($found as $post) {
        if ($post->post_type === 'attachment') {
            $attachment = $post;
        } elseif ($post->post_type === 'page') {
            $page = $post;
        }
    }

    $plan[$slug] = [
        'attachment' => $attachment,
        'page' => $page,
        'content' => serialize_blocks(parse_blocks($pages[$slug]['content'])),
        'template' => $pages[$slug]['template'],
        'title' => $pages[$slug]['title'],
    ];
}

echo 'MODE=' . ($apply ? 'APPLY' : 'DRY-RUN') . PHP_EOL;

foreach ($plan as $slug => $item) {
    printf(
        "%-18s attachment=%s page=%s\n",
        $slug,
        $item['attachment'] ? 'id=' . $item['attachment']->ID . ' (frees slug, clears injected content)' : 'none',
        $item['page'] ? 'id=' . $item['page']->ID . ' (exists)' : 'will create'
    );
}

if (!$apply) {
    echo "\nDry-run only. Re-run with --apply to write these changes.\n";
    exit(0);
}

$freed = 0;
$created = 0;

foreach ($plan as $slug => $item) {
    if ($item['attachment']) {
        $attachment = $item['attachment'];

        // Only clear content we injected; leave a genuine caption untouched.
        $injected = strpos((string) $attachment->post_content, '<!-- wp:ai-zippy/') !== false;

        // wp_update_post() rejects attachments over the page-template check, so
        // the slug and content are updated directly and the caches flushed.
        global $wpdb;

        $result = $wpdb->update(
            $wpdb->posts,
            [
                'post_name' => $slug . '-image',
                'post_content' => $injected ? '' : $attachment->post_content,
            ],
            ['ID' => $attachment->ID],
            ['%s', '%s'],
            ['%d']
        );

        if ($result === false) {
            fwrite(STDERR, "ERROR freeing {$slug}: database update failed.\n");
            exit(3);
        }

        clean_post_cache($attachment->ID);

        echo "FREED {$slug} attachment id={$attachment->ID} -> {$slug}-image" . ($injected ? ' (content cleared)' : '') . "\n";
        $freed++;
    }

    if ($item['page']) {
        echo "SKIP  {$slug} page already exists id={$item['page']->ID}\n";
        continue;
    }

    $new_id = wp_insert_post([
        'post_type' => 'page',
        'post_status' => 'publish',
        'post_title' => $item['title'],
        'post_name' => $slug,
        'post_content' => wp_slash($item['content']),
    ], true);

    if (is_wp_error($new_id)) {
        fwrite(STDERR, "ERROR creating {$slug}: " . $new_id->get_error_message() . "\n");
        exit(3);
    }

    update_post_meta($new_id, '_wp_page_template', $item['template']);
    echo "CREATED {$slug} page id={$new_id}\n";
    $created++;
}

echo "\nDONE freed={$freed} created={$created}\n";
