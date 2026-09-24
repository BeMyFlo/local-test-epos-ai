<?php

/**
 * S5 — Pages: create/update the 10 pages of docs/build/block-map.md §1.
 *
 * Derived from the zippy-site-pipeline skill template `templates/seed-pages.php`
 * (bootstrap header, idempotent create-or-update, `_wp_page_template` meta,
 * static front page, `/%postname%/` + flush_rules() — all preserved).
 *
 * WHAT IT DOES
 *   1. Creates (or updates by slug — never duplicates) the 10 pages:
 *      home, about, film, films, shop, sponsors, upcoming, events, enquiries,
 *      contact — slugs = mockup page ids verbatim (D2); `/upcoming/` is the nav
 *      "Events" page, `/events/` is "Collaborate For Events".
 *   2. Writes `post_content` as self-closing block comments
 *      (<!-- wp:ai-zippy/{block} /-->) separated by blank lines. Attributes are
 *      emitted ONLY where they differ from the block.json defaults (films
 *      cta-banner = defaults; the events/upcoming cta-banner instances carry
 *      their mockup copy as attributes — the only two non-default placements).
 *   3. Sets _wp_page_template to the matching customTemplates entry in theme.json.
 *   4. show_on_front=page, page_on_front=<home>, /%postname%/ + flush_rules().
 *      No menu objects — the FSE header part hardcodes the nav.
 *
 * Adaptations vs the template:
 *   - Page lookup runs a WP_Query with post_status=any (finds trashed /
 *     auto-draft pages too), so a re-run updates instead of creating {slug}-2.
 *   - post_author => 1 (admin) on insert.
 *   - The seeder only ever touches its own 10 slugs — nothing else in wp-admin
 *     (e.g. the pmc-s4-scratch draft page) is modified or deleted.
 *
 * USAGE (member runbook, S5 Step 3):
 *   docker compose cp scripts/seed-pages.php wordpress:/tmp/seed-pages.php
 *   docker compose exec -T wordpress php /tmp/seed-pages.php
 *
 * IDEMPOTENT: existing slugs are updated, not duplicated.
 * WARNING: re-running overwrites post_content for these 10 slugs — do not
 * re-run after editing pages in wp-admin.
 */

if (!defined('ABSPATH')) {
    $wp_load   = null;
    $candidates = [
        __DIR__ . '/../src/wp-load.php',
        __DIR__ . '/../wp-load.php',
        '/var/www/html/wp-load.php',
    ];
    foreach ($candidates as $path) {
        if (file_exists($path)) {
            $wp_load = $path;
            break;
        }
    }
    if (!$wp_load) {
        die("Cannot find wp-load.php\n");
    }
    define('WP_USE_THEMES', false);
    require_once $wp_load;
}

/**
 * One entry per page in docs/build/block-map.md §1 (titles match theme.json).
 *
 * - 'template' matches a customTemplates entry in the child theme.json.
 * - Block markup is self-closing: <!-- wp:ai-zippy/{block} /--> — save()
 *   returns null, so blocks have no inner content. Defaults from block.json
 *   apply when omitted; attributes only where they differ.
 */
$pages = [
    [
        'title'    => 'Home',
        'slug'     => 'home',
        'template' => 'page-home',
        'content'  => '<!-- wp:ai-zippy/home-hero /-->

<!-- wp:ai-zippy/home-who-we-are /-->

<!-- wp:ai-zippy/home-featured-works /-->',
    ],
    [
        'title'    => 'About Us',
        'slug'     => 'about',
        'template' => 'page-about',
        'content'  => '<!-- wp:ai-zippy/about-story /-->

<!-- wp:ai-zippy/about-pillars /-->',
    ],
    [
        'title'    => 'Make A Feature Film?',
        'slug'     => 'film',
        'template' => 'page-film',
        'content'  => '<!-- wp:ai-zippy/film-intro /-->

<!-- wp:ai-zippy/film-tiers /-->',
    ],
    [
        'title'    => 'Our Films',
        'slug'     => 'films',
        'template' => 'page-films',
        'content'  => '<!-- wp:ai-zippy/films-list /-->

<!-- wp:ai-zippy/cta-banner /-->',
    ],
    [
        'title'    => 'Shop',
        'slug'     => 'shop',
        'template' => 'page-shop',
        'content'  => '<!-- wp:ai-zippy/shop-merchandise /-->',
    ],
    [
        'title'    => 'Be Our Sponsors',
        'slug'     => 'sponsors',
        'template' => 'page-sponsors',
        'content'  => '<!-- wp:ai-zippy/sponsors-intro /-->',
    ],
    [
        'title'    => 'Events',
        'slug'     => 'upcoming',
        'template' => 'page-upcoming',
        'content'  => '<!-- wp:ai-zippy/upcoming-list /-->

<!-- wp:ai-zippy/cta-banner {"heading":"Want to run an event with us?","text":"We plan, produce, and promote events end-to-end — concerts, screenings, and creative media experiences.","btnLabel":"Collaborate For Events","btnUrl":"/events/"} /-->',
    ],
    [
        'title'    => 'Collaborate For Events',
        'slug'     => 'events',
        'template' => 'page-events',
        'content'  => '<!-- wp:ai-zippy/events-process /-->

<!-- wp:ai-zippy/cta-banner {"heading":"Looking for what’s coming up?","text":"See our upcoming events, dates, and venues on the Events page.","btnLabel":"View Upcoming Events","btnUrl":"/upcoming/","soft":false} /-->',
    ],
    [
        'title'    => 'Enquiries',
        'slug'     => 'enquiries',
        'template' => 'page-enquiries',
        'content'  => '<!-- wp:ai-zippy/enquiries-form /-->',
    ],
    [
        'title'    => 'Contact Us',
        'slug'     => 'contact',
        'template' => 'page-contact',
        'content'  => '<!-- wp:ai-zippy/contact-details /-->',
    ],
];

/**
 * Find an existing page by slug, any status — a trashed or auto-draft page is
 * found and updated instead of duplicated as {slug}-2 (get_page_by_path()
 * misses those; `any` also ignores attachments and revisions for `name`).
 */
function pmc_page_id_by_slug(string $slug): int
{
    $query = new WP_Query([
        'post_type'      => 'page',
        'name'           => $slug,
        'post_status'    => 'any',
        'posts_per_page' => 1,
        'fields'         => 'ids',
        'no_found_rows'  => true,
    ]);

    return $query->have_posts() ? (int) $query->posts[0] : 0;
}

$page_ids = [];

foreach ($pages as $page) {
    $existing_id = pmc_page_id_by_slug($page['slug']);

    $post_data = [
        'post_title'   => $page['title'],
        'post_name'    => $page['slug'],
        'post_content' => $page['content'],
        'post_status'  => 'publish',
        'post_type'    => 'page',
    ];

    if ($existing_id) {
        $post_data['ID'] = $existing_id;
        $result = wp_update_post($post_data, true);
        $page_id = $existing_id;
        $action  = 'updated';
    } else {
        $post_data['post_author'] = 1; // admin
        $result = wp_insert_post($post_data, true);
        $page_id = $result;
        $action  = 'created';
    }

    if (is_wp_error($result) || !$result || !$page_id) {
        echo "FAILED: {$page['slug']}\n";
        continue;
    }

    update_post_meta((int) $page_id, '_wp_page_template', $page['template']);
    $page_ids[$page['slug']] = (int) $page_id;

    printf("%-9s %-12s ID=%d template=%s\n", $action, $page['slug'], $page_id, $page['template']);
}

// Static front page.
if (!empty($page_ids['home'])) {
    update_option('show_on_front', 'page');
    update_option('page_on_front', $page_ids['home']);
    echo "front page -> {$page_ids['home']}\n";
}

// Pretty permalinks.
global $wp_rewrite;
$wp_rewrite->set_permalink_structure('/%postname%/');
$wp_rewrite->flush_rules();

echo "Pages seeded.\n";
