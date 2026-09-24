<?php

/**
 * S5 — Media: sideload the 28 design assets into the WordPress Media Library.
 *
 * Derived from the zippy-site-pipeline skill template `templates/upload-images.php`
 * (bootstrap header, requires, idempotency rule, `pfx_media_map` option key,
 * non-zero exit on any failure — all preserved).
 *
 * WHAT IT DOES
 *   1. Sideloads every file from the child theme's build-time assets dir
 *      (inside the container: /var/www/html/wp-content/themes/ai-zippy-child/assets/img
 *      — the repo's src/wp-content/themes/ai-zippy-child/assets/img via the compose
 *      bind mount; only this script itself is `docker compose cp`'d to /tmp).
 *   2. Stores the map  filename => attachment ID  in the option `pfx_media_map`,
 *      keyed by the EXACT basenames that render.php / block.json defaults pass to
 *      ai_zippy_child_img() — the helper does an exact-key lookup.
 *   3. Files upload AS-IS (clone doctrine): no AVIF→WebP conversion, no
 *      about-story.jpg downscale (D9). If the container's image editor lacks AVIF
 *      support, WP skips sub-size generation for those files and keeps the
 *      original — harmless, every block resolves the original attachment URL.
 *
 * SVG (S5-D1): WordPress's default allowed-mime list has no `image/svg+xml`, so
 *   1. a script-process-local `upload_mimes` filter adds it before the loop — it
 *      dies with this process and never weakens the site (no theme change, no
 *      site-wide SVG uploads), and
 *   2. if `media_handle_sideload()` still rejects a `.svg` (libmagic variance
 *      across images), that file falls back to a direct insert:
 *      `copy()` into wp_upload_dir()['path'] + `wp_insert_attachment()` with the
 *      svg mime type. No sub-size metadata is generated for SVGs — not needed
 *      for wp_get_attachment_url() resolution.
 *   Both paths converge on the same map entry and the same printed line; a
 *   failure on either path increments `failed` and exits 1.
 *
 * USAGE (member runbook, S5 Step 2):
 *   docker compose cp scripts/upload-images.php wordpress:/tmp/upload-images.php
 *   docker compose exec -T wordpress php -d memory_limit=1024M /tmp/upload-images.php
 *
 * Preconditions: S0 Step 5 uploads `chown` re-run (S5 Step 1 — root-owned uploads
 * make media_handle_sideload() fail with "Unable to create directory").
 *
 * IDEMPOTENT: a filename whose mapped attachment still exists is skipped.
 */

if (!defined('ABSPATH')) {
    $wp_load    = null;
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

require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/media.php';
require_once ABSPATH . 'wp-admin/includes/image.php';

// about-story.jpg is 4000×6000 (6.2 MB); GD holds a ~96 MB truecolour bitmap
// while generating sub-sizes. The command line also passes -d memory_limit=1024M.
if ((int) ini_get('memory_limit') < 1024) {
    @ini_set('memory_limit', '1024M');
}

/** Option key holding the map. Keep one key per project. */
$option_key = 'pfx_media_map';

/** Source directory inside the container (child theme build-time assets). */
$source_dir = WP_CONTENT_DIR . '/themes/ai-zippy-child/assets/img';

/**
 * filename => { title, alt }.
 *
 * Keys MUST be the exact basenames that render.php / block.json defaults pass
 * to ai_zippy_child_img() — the helper does exact-key lookup (ui-spec §10 is
 * the single source; titles = alt, except #28 whose mockup source is a
 * <video poster> attribute with no alt at all).
 */
$files = [
    'logo.png'                            => ['title' => 'PricemrCopper logo', 'alt' => 'PricemrCopper logo'],
    'home-hero.avif'                      => ['title' => 'Main banner', 'alt' => 'Main banner'],
    'home-feature-mad-vibe-city.png'      => ['title' => 'Mad Vibe City film', 'alt' => 'Mad Vibe City film'],
    'home-feature-music-beats.avif'       => ['title' => 'Music beats', 'alt' => 'Music beats'],
    'home-feature-global-logistics.avif'  => ['title' => 'Global logistics', 'alt' => 'Global logistics'],
    'about-story.jpg'                     => ['title' => 'Our story', 'alt' => 'Our story'],
    'film-production.avif'                => ['title' => 'Film production', 'alt' => 'Film production'],
    'films-poster-mad-vibe-city.png'      => ['title' => 'Mad Vibe City poster', 'alt' => 'Mad Vibe City poster'],
    'films-poster-film-2.svg'             => ['title' => 'Mad Vibe City - Opening Sequence poster', 'alt' => 'Mad Vibe City - Opening Sequence poster'],
    'films-poster-film-3.svg'             => ['title' => 'Mad Vibe City - Singapore Nights poster', 'alt' => 'Mad Vibe City - Singapore Nights poster'],
    'films-poster-film-4.svg'             => ['title' => 'Behind The Lens poster', 'alt' => 'Behind The Lens poster'],
    'films-poster-film-5.svg'             => ['title' => 'Original Score Sessions poster', 'alt' => 'Original Score Sessions poster'],
    'product-01.jpg'                      => ['title' => 'Black Long Sleeves Hoodie', 'alt' => 'Black Long Sleeves Hoodie'],
    'product-02.jpg'                      => ['title' => 'White Round Neck Print T-Shirt', 'alt' => 'White Round Neck Print T-Shirt'],
    'product-03.jpg'                      => ['title' => 'Mad Vibe City Drink Tumbler Can 650ml', 'alt' => 'Mad Vibe City Drink Tumbler Can 650ml'],
    'product-04.jpg'                      => ['title' => 'Unisex White Polo T-Shirt', 'alt' => 'Unisex White Polo T-Shirt'],
    'product-05.jpg'                      => ['title' => 'Sports Stainless Steel Water Bottle 500ml', 'alt' => 'Sports Stainless Steel Water Bottle 500ml'],
    'product-06.jpg'                      => ['title' => 'Stainless Steel Mug with Handle 12oz', 'alt' => 'Stainless Steel Mug with Handle 12oz'],
    'product-07.jpg'                      => ['title' => 'Drink Glass Mugs with Straw 500ml', 'alt' => 'Drink Glass Mugs with Straw 500ml'],
    'product-08.jpg'                      => ['title' => 'Stainless Steel Tumbler', 'alt' => 'Stainless Steel Tumbler'],
    'product-09.jpg'                      => ['title' => 'Notebook Sets & Calendars', 'alt' => 'Notebook Sets & Calendars'],
    'product-10.jpg'                      => ['title' => 'Cap', 'alt' => 'Cap'],
    'product-11.jpg'                      => ['title' => 'Mug', 'alt' => 'Mug'],
    'product-12.jpg'                      => ['title' => 'Toy Bears', 'alt' => 'Toy Bears'],
    'product-13.jpg'                      => ['title' => 'Notebook Sets (Boxed)', 'alt' => 'Notebook Sets (Boxed)'],
    'product-14.jpg'                      => ['title' => 'Mad Vibe City Tank Tops / T-Shirts', 'alt' => 'Mad Vibe City Tank Tops / T-Shirts'],
    'event-production.avif'               => ['title' => 'Event production', 'alt' => 'Event production'],
    'event-teaser-poster.svg'             => ['title' => 'Event teaser poster', 'alt' => ''],
];

/**
 * Script-process-local only: let the 5 placeholder SVG posters through the
 * sideload mime gate. Dies with this PHP process — never widens the site.
 */
add_filter('upload_mimes', static fn (array $mimes): array => $mimes + ['svg' => 'image/svg+xml']);

$map     = (array) get_option($option_key, []);
$created = 0;
$skipped = 0;
$failed  = 0;

foreach ($files as $filename => $meta) {
    // Already mapped and the attachment still exists -> skip.
    if (!empty($map[$filename]) && get_post((int) $map[$filename])) {
        printf("%-9s %s (ID=%d)\n", 'skip', $filename, (int) $map[$filename]);
        $skipped++;
        continue;
    }

    $source = $source_dir . '/' . $filename;
    if (!file_exists($source)) {
        printf("%-9s %s (missing in %s)\n", 'MISSING', $filename, $source_dir);
        $failed++;
        continue;
    }

    // media_handle_sideload() moves the file, so work on a copy.
    $tmp = wp_tempnam($filename);
    if (!$tmp || !copy($source, $tmp)) {
        printf("%-9s %s (cannot stage temp copy)\n", 'FAILED', $filename);
        $failed++;
        continue;
    }

    $attachment_id = media_handle_sideload(
        [
            'name'     => $filename,
            'tmp_name' => $tmp,
        ],
        0,
        $meta['title']
    );

    // SVG fallback (S5-D1): the mime filter usually suffices, but libmagic
    // variance across images can still reject an svg -> insert it directly.
    if (is_wp_error($attachment_id) && str_ends_with(strtolower($filename), '.svg')) {
        @unlink($tmp);
        $uploads = wp_upload_dir();
        $dest    = $uploads['path'] . '/' . $filename;
        if (!empty($uploads['error']) || !copy($source, $dest)) {
            printf("%-9s %s (cannot copy into %s)\n", 'FAILED', $filename, $uploads['path'] ?? '');
            $failed++;
            continue;
        }
        $attachment_id = wp_insert_attachment(
            [
                'post_mime_type' => 'image/svg+xml',
                'post_title'     => $meta['title'],
                'post_status'    => 'inherit',
            ],
            $dest
        );
        // No sub-size metadata for SVGs — wp_get_attachment_url() does not need it.
    }

    if (is_wp_error($attachment_id) || !$attachment_id) {
        @unlink($tmp);
        printf(
            "%-9s %s (%s)\n",
            'FAILED',
            $filename,
            is_wp_error($attachment_id) ? $attachment_id->get_error_message() : 'insert failed'
        );
        $failed++;
        continue;
    }

    update_post_meta((int) $attachment_id, '_wp_attachment_image_alt', $meta['alt']);
    $map[$filename] = (int) $attachment_id;
    printf("%-9s %s (ID=%d)\n", 'uploaded', $filename, (int) $attachment_id);
    $created++;
}

update_option($option_key, $map);

printf("\nuploaded=%d skipped=%d failed=%d  map entries=%d\n", $created, $skipped, $failed, count($map));

if ($failed > 0) {
    // Non-zero exit so the calling stage does not report a false PASS.
    exit(1);
}
