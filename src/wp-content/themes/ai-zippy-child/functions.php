<?php

/**
 * AI Zippy Child Theme Functions
 *
 * Project-specific customizations live here.
 * The parent theme (ai-zippy) handles core assets, REST APIs, cart/checkout, etc.
 * This file adds the child's own Vite-built assets and auto-registers any
 * client-specific Gutenberg blocks from assets/blocks/.
 */

defined('ABSPATH') || exit;

/** Webfonts used by the design (mockup <head> L8, verbatim): Sora 500-800 + Inter 400-700. */
define('AI_ZIPPY_CHILD_FONT_URL', 'https://fonts.googleapis.com/css2?family=Sora:wght@500;600;700;800&family=Inter:wght@400;500;600;700&display=swap');

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
    $deps     = ai_zippy_child_style_deps();

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
        wp_enqueue_style($handle, $dist_uri . '/' . $asset['file'], $deps, $version);
    }

    // CSS bundled with JS entry
    if (!empty($asset['css'])) {
        foreach ($asset['css'] as $i => $css_file) {
            $file_path = $dist_dir . '/' . $css_file;
            $version   = file_exists($file_path) ? filemtime($file_path) : false;
            wp_enqueue_style(
                $handle . '-css-' . $i,
                $dist_uri . '/' . $css_file,
                $deps,
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

// =============================================================================
// 2. Frontend webfonts + preconnect hints (mockup <head> L6-8, verbatim)
// =============================================================================

add_action('wp_enqueue_scripts', function (): void {
    wp_enqueue_style('ai-zippy-child-fonts', AI_ZIPPY_CHILD_FONT_URL, [], null);
}, 5);

/** Preconnect hints, cloned from the mockup <head> L6-7. */
add_action('wp_head', function (): void {
    echo '<link rel="preconnect" href="https://fonts.googleapis.com">' . "\n";
    echo '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>' . "\n";
}, 1);

// =============================================================================
// 3. Editor parity — the built stylesheet must reach the Gutenberg canvas
//    (functions-editor-parity template; S4 precondition)
// =============================================================================

add_action('after_setup_theme', function (): void {
    // Required for add_editor_style() to reach the block editor canvas.
    add_theme_support('editor-styles');

    // Prefer the manifest (survives a config change), fall back to the stable
    // Vite output name defined in vite.config.child.js.
    $manifest = ai_zippy_child_vite_manifest();
    $entry    = 'src/wp-content/themes/ai-zippy-child/src/scss/style.scss';
    $relative = [];

    if (!empty($manifest[$entry]['css'])) {
        foreach ($manifest[$entry]['css'] as $css_file) {
            $relative[] = 'assets/dist/' . $css_file;
        }
    } elseif (!empty($manifest[$entry]['file']) && str_ends_with($manifest[$entry]['file'], '.css')) {
        $relative[] = 'assets/dist/' . $manifest[$entry]['file'];
    } else {
        $relative[] = 'assets/dist/css/child-style.css';
    }

    foreach ($relative as $path) {
        // get_stylesheet_directory() is always the CHILD, so this stays child
        // CSS even though the parent may have a file at the same relative path.
        if (file_exists(get_stylesheet_directory() . '/' . $path)) {
            add_editor_style($path);
        }
    }
}, 20);

/**
 * Webfonts inside the editor canvas. A remote URL passed to add_editor_style()
 * triggers a blocking wp_remote_head() on every editor load, so enqueue it
 * instead; fonts leaking into the rest of wp-admin is harmless — project CSS
 * is not, which is why only the font stylesheet uses this hook.
 */
add_action('enqueue_block_assets', function (): void {
    if (!is_admin()) {
        return; // the frontend already gets fonts from wp_enqueue_scripts
    }

    wp_enqueue_style('ai-zippy-child-editor-fonts', AI_ZIPPY_CHILD_FONT_URL, [], null);
});

// =============================================================================
// 4. Template parts — area tag, active nav link, logo URL shortcode
// =============================================================================

/**
 * Core wraps a header-area part in <header> and a footer-area part in <footer>.
 * parts/header.html and parts/footer.html already contain the real
 * <header class="pmc-header"> / <footer class="pmc-footer"> from the mockup,
 * so the wrapper would produce <header><header> — invalid HTML and a
 * duplicated banner/contentinfo landmark. The child templates set
 * tagName:"div" explicitly; this filter applies the same tag to the PARENT
 * theme's templates (index, archive, search, 404, single), which this stage
 * must not edit.
 *
 * `.wp-site-blocks > .wp-block-template-part { display:contents }` (in
 * _base.scss) then removes the wrapper box, which is what lets the sticky
 * prism bar + header travel the page.
 */
add_filter('default_wp_template_part_areas', function (array $areas): array {
    foreach ($areas as $i => $area) {
        if (isset($area['area']) && in_array($area['area'], ['header', 'footer'], true)) {
            $areas[$i]['area_tag'] = 'div';
        }
    }
    return $areas;
});

/**
 * The mockup's SPA router stamped `.active` on the current page's nav link in
 * JS; B15 replaced the router with real permalinks, so the same signal is
 * restored server-side: while the header part renders, stamp `active` on the
 * nav anchor whose href matches the current request path. The styling lives on
 * `.pmc-nav a.active` in _header.scss (shipped at S2a). External and anchor
 * links never match; exact (untrailing-slashed) path match only.
 */
add_filter('render_block', function (string $html, array $block): string {
    if ('core/html' !== ($block['blockName'] ?? '') || !str_contains($html, 'id="mainNav"')) {
        return $html;
    }

    $current = wp_parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
    $current = untrailingslashit($current);
    $current = ('' === $current) ? '/' : $current;

    $home_host = wp_parse_url(home_url(), PHP_URL_HOST);
    $processor = new WP_HTML_Tag_Processor($html);

    while ($processor->next_tag('a')) {
        $href = $processor->get_attribute('href');
        if (!is_string($href) || '' === $href || str_starts_with($href, '#')) {
            continue;
        }

        $parts = wp_parse_url($href);
        if (!empty($parts['host']) && $parts['host'] !== $home_host) {
            continue; // external link — never "active"
        }

        $path = untrailingslashit($parts['path'] ?? '/');
        $path = ('' === $path) ? '/' : $path;
        if ($path !== $current) {
            continue;
        }

        $processor->add_class('active');
    }

    return $processor->get_updated_html();
}, 10, 2);

/**
 * Logo URL for parts/header.html — resolves through the Media Library map
 * (S5 uploads logo.png and stores the attachment ID), falling back to the
 * theme's build-time asset until then. Shortcodes resolve inside template
 * parts before do_blocks(), so the token inside src="" is replaced in place.
 */
add_shortcode('pmc_logo_url', function (): string {
    return esc_url(ai_zippy_child_img('logo.png'));
});

// =============================================================================
// 5. Media Library image resolver (S5 stores filename => attachment ID in the
//    `pfx_media_map` option; the theme-asset path is the fallback only)
// =============================================================================

function ai_zippy_child_img(string $filename): string
{
    static $map = null;
    $map ??= (array) get_option('pfx_media_map', []);

    if (!empty($map[$filename])) {
        $url = wp_get_attachment_url((int) $map[$filename]);
        if ($url) {
            return $url;
        }
    }

    return get_stylesheet_directory_uri() . '/assets/img/' . $filename; // fallback only
}

// =============================================================================
// 6. Style dependencies guard
//    wp_enqueue_style() with an unregistered dependency prints NOTHING, so the
//    hardcoded parent handle must only be used when the parent bundle exists.
// =============================================================================

function ai_zippy_child_style_deps(): array
{
    return wp_style_is('ai-zippy-theme-css-0', 'registered') ? ['ai-zippy-theme-css-0'] : [];
}

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
