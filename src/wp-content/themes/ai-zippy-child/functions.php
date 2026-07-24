<?php

defined('ABSPATH') || exit;

// =============================================================================
// Vite manifest reader — child's own assets/dist/.vite/manifest.json
// Mirrors the parent's AiZippy\Core\ViteAssets but scoped to this theme.
// =============================================================================

/**
 * Read the child theme's Vite manifest (cached per-request).
 */
function ai_zippy_child_vite_manifest(): array
{
    static $manifest = null;
    if ($manifest !== null) {
        return $manifest;
    }

    $path = get_stylesheet_directory() . '/assets/dist/.vite/manifest.json';
    if (!file_exists($path)) {
        return $manifest = [];
    }

    $decoded = json_decode(file_get_contents($path), true);
    return $manifest = is_array($decoded) ? $decoded : [];
}

/**
 * Enqueue a child-theme Vite asset by its source entry key.
 * Entry keys are the full path from repo root, e.g.:
 *   src/wp-content/themes/ai-zippy-child/src/js/child.js
 */
function ai_zippy_child_enqueue_vite(string $handle, string $entry, array $js_deps = []): void
{
    $manifest = ai_zippy_child_vite_manifest();
    if (empty($manifest[$entry])) {
        return;
    }

    $asset    = $manifest[$entry];
    $dist_uri = get_stylesheet_directory_uri() . '/assets/dist';
    $dist_dir = get_stylesheet_directory() . '/assets/dist';

    // JS entry — skip stub files emitted by Vite when an SCSS entry has no content yet
    if (!empty($asset['file']) && str_ends_with($asset['file'], '.js')) {
        $file_path = $dist_dir . '/' . $asset['file'];
        $is_scss_entry = !empty($asset['src']) && str_ends_with($asset['src'], '.scss');
        $is_tiny_stub  = file_exists($file_path) && filesize($file_path) < 100;

        if (!($is_scss_entry && $is_tiny_stub)) {
            $version = file_exists($file_path) ? filemtime($file_path) : false;
            wp_enqueue_script($handle, $dist_uri . '/' . $asset['file'], $js_deps, $version, true);
        }
    }

    // CSS-only entry (manifest file is .css directly)
    if (!empty($asset['file']) && str_ends_with($asset['file'], '.css') && empty($asset['css'])) {
        $file_path = $dist_dir . '/' . $asset['file'];
        $version   = file_exists($file_path) ? filemtime($file_path) : false;
        // Depend on the parent theme's style so child CSS loads after it.
        wp_enqueue_style($handle, $dist_uri . '/' . $asset['file'], ['ai-zippy-theme-css-0'], $version);
    }

    // CSS bundled with JS entry
    if (!empty($asset['css'])) {
        foreach ($asset['css'] as $i => $css_file) {
            $file_path = $dist_dir . '/' . $css_file;
            $version   = file_exists($file_path) ? filemtime($file_path) : false;
            wp_enqueue_style(
                $handle . '-css-' . $i,
                $dist_uri . '/' . $css_file,
                ['ai-zippy-theme-css-0'],
                $version
            );
        }
    }
}

/**
 * Mark child theme scripts as ES modules (Vite outputs ESM).
 */
add_filter('script_loader_tag', function (string $tag, string $handle): string {
    if (str_starts_with($handle, 'ai-zippy-child')) {
        return str_replace(' src=', ' type="module" src=', $tag);
    }
    return $tag;
}, 10, 2);

/**
 * Enqueue child theme assets (after parent so overrides work).
 */
add_action('wp_enqueue_scripts', function (): void {
    $base = 'src/wp-content/themes/ai-zippy-child/src';

    ai_zippy_child_enqueue_vite('ai-zippy-child',       $base . '/js/child.js');
    ai_zippy_child_enqueue_vite('ai-zippy-child-style', $base . '/scss/style.scss');
}, 20);

// === Achiever's Art: Block Categories ===
add_filter('block_categories_all', function (array $categories): array {
    $custom = [
        ['slug' => 'achiever-home',    'title' => 'Achiever — Home'],
        ['slug' => 'achiever-classes', 'title' => 'Achiever — Classes'],
        ['slug' => 'achiever-course',  'title' => 'Achiever — Course'],
        ['slug' => 'achiever-events',  'title' => 'Achiever — Events & Parties'],
        ['slug' => 'achiever-contact', 'title' => 'Achiever — Contact'],
        ['slug' => 'achiever-shared',  'title' => 'Achiever — Shared'],
    ];
    return array_merge($custom, $categories);
}, 10, 1);

// =============================================================================
// Auto-register child theme blocks (wp-scripts build output)
// =============================================================================

add_action('init', function (): void {
    $blocks_dir = get_stylesheet_directory() . '/assets/blocks';
    if (!is_dir($blocks_dir)) {
        return;
    }
    foreach (glob($blocks_dir . '/*/block.json') as $block_json) {
        register_block_type(dirname($block_json));
    }
});

// === Achiever's Art: Google Fonts ===
add_action('wp_enqueue_scripts', function (): void {
    wp_enqueue_style(
        'achiever-art-fonts',
        'https://fonts.googleapis.com/css2?family=Baloo+2:wght@500;700;800&family=Caveat:wght@600&family=Poppins:wght@400;500;600;700&display=swap',
        [],
        null
    );
}, 5);

// === Achiever's Art: Editor styles (for block preview) ===
add_action('enqueue_block_editor_assets', function (): void {
    wp_enqueue_style(
        'achiever-art-fonts-editor',
        'https://fonts.googleapis.com/css2?family=Baloo+2:wght@500;700;800&family=Caveat:wght@600&family=Poppins:wght@400;500;600;700&display=swap',
        [],
        null
    );

    $dist_dir = get_stylesheet_directory() . '/assets/dist';
    $dist_uri = get_stylesheet_directory_uri() . '/assets/dist';
    $css_file = $dist_dir . '/css/child-style.css';
    if (file_exists($css_file)) {
        wp_enqueue_style(
            'achiever-art-editor-styles',
            $dist_uri . '/css/child-style.css',
            ['achiever-art-fonts-editor'],
            filemtime($css_file)
        );
    }
}, 10);

// === High priority CSS overrides for Section Titles, Logos & Gallery Slider ===
add_action('wp_enqueue_scripts', function (): void {
    $css = '
        .wp-block-woocommerce-mini-cart,
        .wp-block-woocommerce-customer-account,
        .wc-block-mini-cart,
        .wc-block-customer-account-link,
        .wc-block-mini-cart__button {
            display: none !important;
        }

        /* ─── HEADER & FOOTER LOGO OVERRIDES ────────────────────────── */
        html body .achiever-header__logo,
        html body .achiever-footer__logo {
            max-height: 80px !important;
        }

        html body .achiever-header__logo .wp-block-site-logo,
        html body .achiever-header__logo .custom-logo-link,
        html body .achiever-footer__logo .wp-block-site-logo,
        html body .achiever-footer__logo .custom-logo-link {
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            height: 80px !important;
            max-height: 80px !important;
            width: auto !important;
        }

        html body .achiever-header__logo img,
        html body .achiever-header__logo svg,
        html body .achiever-footer__logo img,
        html body .achiever-footer__logo svg {
            height: 80px !important;
            max-height: 80px !important;
            width: auto !important;
            max-width: 260px !important;
            object-fit: contain !important;
            display: block !important;
        }

        /* ─── SECTION TITLES (70px) ────────────────────────────────── */
        html body h1.achiever-classes-hero__heading,
        html body h2.achiever-classes-hero__section-title,
        html body .achiever-classes-detail__copy h2,
        html body .achiever-classes-detail__gallery h2,
        html body .achiever-classes-hero__heading,
        html body .achiever-classes-hero__section-title,
        html body .achiever-class-types__title,
        html body .achiever-seasonal__title,
        html body .achiever-beyond__title,
        html body .achiever-best-sellers__title,
        html body .achiever-products__title,
        html body .achiever-party__title,
        html body .achiever-instagram__title,
        html body .achiever-testimonials__title {
            font-size: 70px !important;
            line-height: 1.1 !important;
        }

        @media (max-width: 767px) {
            html body h1.achiever-classes-hero__heading,
            html body h2.achiever-classes-hero__section-title,
            html body .achiever-classes-detail__copy h2,
            html body .achiever-classes-detail__gallery h2 {
                font-size: 42px !important;
            }
        }

        /* ─── CLASSES HERO SLIDER ──────────────────────────────────── */
        html body .achiever-classes-hero__title-section {
            background-color: #ffffff !important;
            background-image: url("/wp-content/uploads/2026/07/hero-bg-mascots.png") !important;
            background-size: cover !important;
            background-position: center top !important;
            background-repeat: no-repeat !important;
            min-height: 450px !important;
            display: flex !important;
            flex-direction: column !important;
            justify-content: center !important;
            align-items: center !important;
            padding-top: 40px !important;
            padding-bottom: 40px !important;
            position: relative !important;
            box-sizing: border-box !important;
        }

        html body .achiever-classes-hero__slider {
            position: relative !important;
            width: 100% !important;
            max-width: 1280px !important;
            margin: 0 auto !important;
            padding-inline: 60px !important;
            box-sizing: border-box !important;
        }

        html body .achiever-classes-hero__carousel {
            display: flex !important;
            flex-direction: row !important;
            flex-wrap: nowrap !important;
            align-items: flex-start !important;
            justify-content: center !important;
            gap: 28px !important;
            overflow-x: auto !important;
            scroll-snap-type: x mandatory !important;
            scrollbar-width: none !important;
            padding-block: 12px !important;
            padding-inline: 0 !important;
            margin: 0 auto !important;
            width: 100% !important;
        }

        html body .achiever-classes-hero__carousel::-webkit-scrollbar {
            display: none !important;
        }

        html body .achiever-classes-hero__card {
            flex: 0 0 240px !important;
            width: 240px !important;
            min-width: 240px !important;
            max-width: 240px !important;
            flex-shrink: 0 !important;
            scroll-snap-align: center !important;
            text-align: center !important;
        }

        html body .achiever-classes-hero__image-slot {
            width: 220px !important;
            height: 220px !important;
            border-radius: 50% !important;
            overflow: hidden !important;
            margin: 0 auto 16px !important;
            border: 5px solid #ffffff !important;
            box-shadow: 0 12px 30px rgba(82, 96, 139, 0.15) !important;
        }

        html body .achiever-classes-hero__image-slot img {
            width: 100% !important;
            height: 100% !important;
            object-fit: cover !important;
        }

        html body .achiever-classes-hero__arrow {
            position: absolute !important;
            top: 38% !important;
            transform: translateY(-50%) !important;
            z-index: 20 !important;
            width: 44px !important;
            height: 44px !important;
            border-radius: 50% !important;
            background: #ffffff !important;
            color: #1a1040 !important;
            border: none !important;
            box-shadow: 0 4px 14px rgba(0,0,0,0.15) !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            cursor: pointer !important;
            font-size: 24px !important;
            line-height: 1 !important;
        }

        html body .achiever-classes-hero__arrow--prev {
            left: 10px !important;
        }

        html body .achiever-classes-hero__arrow--next {
            right: 10px !important;
        }

        /* ─── COURSE INTRO / CAMPS & COURSES 4-SECTIONS ────────────── */
        html body .achiever-course-intro {
            margin: 0 !important;
            padding: 0 !important;
            width: 100% !important;
            box-sizing: border-box !important;
        }

        html body .achiever-course-intro__hero {
            background-color: #ffffff !important;
            background-image: url("/wp-content/uploads/2026/07/hero-bg-mascots.png") !important;
            background-size: cover !important;
            background-position: center top !important;
            background-repeat: no-repeat !important;
            min-height: 400px !important;
            display: flex !important;
            flex-direction: column !important;
            justify-content: center !important;
            align-items: center !important;
            padding: 40px 20px !important;
            text-align: center !important;
        }

        html body .achiever-course-intro__tagline {
            background: transparent !important;
            background-color: transparent !important;
        }

        html body .achiever-course-intro__tagline-title {
            font-family: "Caveat", "Baloo 2", cursive, sans-serif !important;
            font-size: 54px !important;
            font-weight: 700 !important;
            color: #ff6584 !important;
            margin: 0 0 6px !important;
            line-height: 1.1 !important;
        }

        html body .achiever-course-intro__tagline-copy {
            font-size: 15px !important;
            color: #5b7dba !important;
            max-width: 580px !important;
            margin: 0 auto 16px !important;
            line-height: 1.5 !important;
        }

        html body .achiever-course-intro__heading {
            font-family: "Baloo 2", sans-serif !important;
            font-size: 70px !important;
            font-weight: 900 !important;
            color: #ff6584 !important;
            text-transform: uppercase !important;
            line-height: 1.1 !important;
            margin: 10px 0 0 !important;
        }

        html body .achiever-course-intro__pink-section {
            background-color: #ffe4ee !important;
            padding-top: 60px !important;
            padding-bottom: 80px !important;
            position: relative !important;
            text-align: center !important;
            overflow: hidden !important;
        }

        html body .achiever-course-intro__pink-section .achiever-course-intro__container {
            max-width: 800px !important;
            margin: 0 auto !important;
            padding-inline: 20px !important;
            position: relative !important;
            z-index: 2 !important;
        }

        html body .achiever-course-intro__description p {
            color: #5b7dba !important;
            font-size: 16px !important;
            font-weight: 600 !important;
            line-height: 1.6 !important;
            margin-bottom: 16px !important;
            text-align: center !important;
        }

        html body .achiever-course-intro__info-wrapper {
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            gap: 24px !important;
            margin-top: 36px !important;
            position: relative !important;
            max-width: 900px !important;
            margin-inline: auto !important;
        }

        html body .achiever-course-intro__info-pill {
            background: #ff6584 !important;
            color: #ffffff !important;
            border-radius: 24px !important;
            padding: 20px 48px !important;
            display: flex !important;
            flex-direction: column !important;
            align-items: center !important;
            gap: 4px !important;
            box-shadow: 0 10px 24px rgba(255, 101, 132, 0.25) !important;
            font-size: 16px !important;
            font-weight: 800 !important;
            line-height: 1.4 !important;
            z-index: 2 !important;
        }

        html body .achiever-course-intro__decor-scissors {
            width: 70px !important;
            height: auto !important;
            position: absolute !important;
            left: 40px !important;
            top: 50% !important;
            transform: translateY(-50%) !important;
            z-index: 1 !important;
        }

        html body .achiever-course-intro__decor-scissors svg {
            width: 70px !important;
            height: auto !important;
            display: block !important;
        }

        html body .achiever-course-intro__decor-rocket {
            width: 140px !important;
            height: auto !important;
            position: absolute !important;
            right: 20px !important;
            top: 50% !important;
            transform: translateY(-50%) !important;
            z-index: 1 !important;
        }

        html body .achiever-course-intro__decor-rocket img {
            width: 100% !important;
            max-width: 140px !important;
            height: auto !important;
            object-fit: contain !important;
            display: block !important;
        }

        html body .achiever-course-intro__white-section {
            background-color: #ffffff !important;
            padding-top: 60px !important;
            padding-bottom: 90px !important;
            position: relative !important;
            text-align: center !important;
            overflow: hidden !important;
        }

        html body .achiever-course-intro__white-section .achiever-course-intro__container {
            max-width: 800px !important;
            margin: 0 auto !important;
            padding-inline: 20px !important;
            position: relative !important;
            z-index: 2 !important;
        }

        html body .achiever-course-intro__decor-pencil-left {
            width: 70px !important;
            height: auto !important;
            position: absolute !important;
            left: 60px !important;
            top: 160px !important;
            pointer-events: none !important;
            z-index: 1 !important;
        }

        html body .achiever-course-intro__decor-pencil-left svg {
            width: 70px !important;
            height: auto !important;
            display: block !important;
        }

        html body .achiever-course-intro__decor-pencils {
            width: 110px !important;
            height: auto !important;
            position: absolute !important;
            right: 60px !important;
            top: 120px !important;
            pointer-events: none !important;
            z-index: 1 !important;
        }

        html body .achiever-course-intro__decor-pencils svg {
            width: 110px !important;
            height: auto !important;
            display: block !important;
        }

        html body .achiever-course-intro__bear-mascot {
            width: 150px !important;
            height: auto !important;
            position: absolute !important;
            left: 30px !important;
            bottom: 0 !important;
            pointer-events: none !important;
            z-index: 1 !important;
        }

        html body .achiever-course-intro__bear-mascot img {
            width: 100% !important;
            max-width: 150px !important;
            height: auto !important;
            object-fit: contain !important;
            display: block !important;
        }

        html body .achiever-course-intro__section-title {
            font-family: "Baloo 2", sans-serif !important;
            font-size: 48px !important;
            font-weight: 900 !important;
            color: #ff6584 !important;
            text-transform: uppercase !important;
            letter-spacing: 0.08em !important;
            line-height: 1.1 !important;
            margin-bottom: 16px !important;
            text-align: center !important;
        }

        html body .achiever-course-intro__terms p,
        html body .achiever-course-intro__supplies p {
            color: #5b7dba !important;
            font-size: 15px !important;
            font-weight: 600 !important;
            line-height: 1.6 !important;
            margin-bottom: 6px !important;
            text-align: center !important;
        }

        html body .achiever-course-intro__cta {
            margin-top: 36px !important;
        }

        html body .achiever-course-intro__cta .achiever-btn {
            background-color: #ff6584 !important;
            color: #ffffff !important;
            border-radius: 999px !important;
            padding: 14px 44px !important;
            font-size: 15px !important;
            font-weight: 800 !important;
            letter-spacing: 0.05em !important;
            display: inline-block !important;
            box-shadow: 0 8px 20px rgba(255, 101, 132, 0.3) !important;
            border: none !important;
            text-decoration: none !important;
            text-transform: uppercase !important;
        }

        html body .achiever-course-intro__programme {
            background-color: #fdfec7 !important;
            padding-top: 70px !important;
            padding-bottom: 90px !important;
            text-align: center !important;
        }

        html body .achiever-course-intro__programme .achiever-course-intro__section-title {
            color: #1a1040 !important;
        }

        html body .achiever-course-intro__programme-subtitle {
            color: #5b7dba !important;
            font-size: 16px !important;
            font-weight: 700 !important;
            margin-top: -10px !important;
            margin-bottom: 36px !important;
        }

        html body .achiever-course-intro__programme-grid {
            display: grid !important;
            grid-template-columns: repeat(2, 1fr) !important;
            gap: 20px !important;
            max-width: 720px !important;
            margin: 0 auto !important;
        }

        html body .achiever-course-intro__programme-card {
            background-color: #d5f1ff !important;
            color: #1a1040 !important;
            padding: 20px 24px !important;
            border-radius: 16px !important;
            font-size: 15px !important;
            font-weight: 800 !important;
            line-height: 1.4 !important;
            text-align: center !important;
        }

        /* ─── COURSE INTRO GALLERY SLIDER FIX ──────────────────────── */
        html body .achiever-course-intro__gallery {
            background-color: #ffffff !important;
            padding: 60px 20px !important;
            width: 100% !important;
            box-sizing: border-box !important;
            display: block !important;
        }

        html body .achiever-course-intro__gallery-slider {
            position: relative !important;
            max-width: 1140px !important;
            margin: 0 auto !important;
            padding-inline: 50px !important;
            box-sizing: border-box !important;
        }

        html body .achiever-course-intro__gallery-track {
            display: flex !important;
            flex-direction: row !important;
            flex-wrap: nowrap !important;
            gap: 20px !important;
            overflow-x: auto !important;
            scroll-snap-type: x mandatory !important;
            scrollbar-width: none !important;
            padding-block: 10px !important;
            width: 100% !important;
            box-sizing: border-box !important;
        }

        html body .achiever-course-intro__gallery-track::-webkit-scrollbar {
            display: none !important;
        }

        html body .achiever-course-intro__gallery-item {
            flex: 0 0 340px !important;
            width: 340px !important;
            min-width: 340px !important;
            height: 240px !important;
            border-radius: 16px !important;
            overflow: hidden !important;
            scroll-snap-align: center !important;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08) !important;
        }

        html body .achiever-course-intro__gallery-item img {
            width: 100% !important;
            height: 100% !important;
            object-fit: cover !important;
            display: block !important;
        }

        html body .achiever-course-intro__gallery-arrow {
            position: absolute !important;
            top: 50% !important;
            transform: translateY(-50%) !important;
            z-index: 10 !important;
            width: 44px !important;
            height: 44px !important;
            border-radius: 50% !important;
            background-color: #ffffff !important;
            color: #1a1040 !important;
            border: none !important;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15) !important;
            cursor: pointer !important;
            font-size: 24px !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
        }

        html body .achiever-course-intro__gallery-arrow--prev {
            left: 0 !important;
        }

        html body .achiever-course-intro__gallery-arrow--next {
            right: 0 !important;
        }

        /* ─── OFFERING LISTING ─────────────────────────────────────── */
        html body .achiever-offering-listing {
            padding: 60px 20px !important;
            position: relative !important;
            width: 100% !important;
            box-sizing: border-box !important;
            background-color: #ffffff !important;
        }

        html body .achiever-offering-listing__inner {
            max-width: 1200px !important;
            margin: 0 auto !important;
            position: relative !important;
            z-index: 2 !important;
        }

        html body .achiever-offering-listing__track {
            display: grid !important;
            grid-template-columns: repeat(3, 1fr) !important;
            gap: 28px !important;
            width: 100% !important;
        }

        @media (max-width: 991px) {
            html body .achiever-offering-listing__track {
                grid-template-columns: repeat(2, 1fr) !important;
            }
        }

        @media (max-width: 600px) {
            html body .achiever-offering-listing__track {
                grid-template-columns: 1fr !important;
            }
        }

        /* DECORATION MASCOT OVERLAYS */
        html body .achiever-decor-mascot {
            position: absolute !important;
            pointer-events: none !important;
            transform: translate(-50%, -50%) !important;
        }

        html body .achiever-decor-mascot--left {
            top: calc(var(--decor-left-desktop-y, 10) * 1%) !important;
            left: calc(var(--decor-left-desktop-x, 5) * 1%) !important;
            width: calc(var(--decor-left-desktop-size, 140) * 1px) !important;
            z-index: var(--decor-left-z-index, 5) !important;
        }

        html body .achiever-decor-mascot--right {
            top: calc(var(--decor-right-desktop-y, 85) * 1%) !important;
            left: calc(var(--decor-right-desktop-x, 90) * 1%) !important;
            width: calc(var(--decor-right-desktop-size, 160) * 1px) !important;
            z-index: var(--decor-right-z-index, 5) !important;
        }

        html body .achiever-decor-mascot img {
            display: block !important;
            width: 100% !important;
            height: auto !important;
            object-fit: contain !important;
        }
    ';
    wp_add_inline_style('wp-block-library', $css);
}, 9999);

// === Hide the default WooCommerce shop archive title (page-hero banner already shows it) ===
add_filter('woocommerce_show_page_title', '__return_false');

$form_handler = get_stylesheet_directory() . '/inc/form-handler.php';
if (file_exists($form_handler)) {
    require_once $form_handler;
}
