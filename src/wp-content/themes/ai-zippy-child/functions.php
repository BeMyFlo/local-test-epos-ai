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
// Decor / mascot positioning helper — generates inline style string
// =============================================================================

/**
 * Build an inline CSS style string for a decorative mascot overlay.
 *
 * @param array  $attributes Block attributes array.
 * @param string $prefix     Attribute prefix, e.g. 'decorLeft', 'decorRight', 'decor', 'mascot'.
 * @param array  $defaults   Default values: zIndex, desktopX, desktopY, desktopSize, mobileX, mobileY, mobileSize.
 * @return string Inline style string ready for use in a style="" attribute.
 */
function ai_zippy_child_decor_style(array $attributes, string $prefix, array $defaults = []): string
{
    $z       = $attributes["{$prefix}ZIndex"]      ?? ($defaults['zIndex']      ?? 5);
    $dx      = $attributes["{$prefix}DesktopX"]    ?? ($defaults['desktopX']    ?? 50);
    $dy      = $attributes["{$prefix}DesktopY"]    ?? ($defaults['desktopY']    ?? 50);
    $ds      = $attributes["{$prefix}DesktopSize"]  ?? ($defaults['desktopSize']  ?? 120);
    $mx      = $attributes["{$prefix}MobileX"]     ?? ($defaults['mobileX']     ?? $dx);
    $my      = $attributes["{$prefix}MobileY"]     ?? ($defaults['mobileY']     ?? $dy);
    $ms      = $attributes["{$prefix}MobileSize"]   ?? ($defaults['mobileSize']   ?? $ds);

    $slug = strtolower(preg_replace('/([a-z])([A-Z])/', '$1-$2', $prefix));

    return implode(' ', [
        "position:absolute;",
        "pointer-events:none;",
        "z-index:{$z};",
        "left:{$dx}%;",
        "top:{$dy}%;",
        "width:{$ds}px;",
        "transform:translate(-50%,-50%);",
        "--{$slug}-desktop-x:{$dx};",
        "--{$slug}-desktop-y:{$dy};",
        "--{$slug}-desktop-size:{$ds};",
        "--{$slug}-mobile-x:{$mx};",
        "--{$slug}-mobile-y:{$my};",
        "--{$slug}-mobile-size:{$ms};",
    ]);
}

// =============================================================================
// Page name renames
// =============================================================================

/**
 * Apply the 2026 page renames to a saved string.
 *
 * The old names ("Regular Art Classes", "Single-Session Art Classes", "Our Camps
 * & Courses") are stored in block attributes and post titles across the site, so
 * they are rewritten at render time rather than edited page by page.
 *
 * Longest patterns run first so "Single-Session Art Classes" is not partly
 * matched by the shorter "Art Classes" rule.
 *
 * @param string $text Text that may contain an old page name.
 * @return string Text with the current page names.
 */
function ai_zippy_child_rename_page_text(string $text): string
{
    if ($text === '') {
        return $text;
    }

    // Headings separate their two lines with a newline OR a literal <br> tag,
    // depending on when they were saved. SEP matches either and is captured so
    // the original separator is put back, preserving the two-line layout.
    $sep = '(?:\s|<br\s*\/?>)+';

    $patterns = [
        // "Single-Session Classes" is the eyebrow above the "Workshops" heading;
        // renaming it to "Workshops" too would print the same word twice.
        "/\bSingle[\s\x{2010}-\x{2015}-]*Session{$sep}Classes\b/iu"               => 'Drop-in Sessions',
        // Single-session → Workshops (hyphen, en dash or space between words).
        "/\bSingle[\s\x{2010}-\x{2015}-]*Session(?:{$sep}Art)?{$sep}Classes\b/iu" => 'Workshops',
        "/\bSingle[\s\x{2010}-\x{2015}-]*Session({$sep})Offerings\b/iu"           => 'Our${1}Workshops',
        // Our Camps & Courses → Camps & Courses.
        "/\bOur{$sep}Camps(\s*)(?:&amp;|&|and)(\s*)Courses\b/iu"                  => 'Camps${1}&${2}Courses',
        // Regular Art Classes → Regular Classes.
        "/\bRegular({$sep})Art{$sep}Classes\b/iu"                                 => 'Regular${1}Classes',
        "/\bOur{$sep}Regular({$sep})Classes\b/iu"                                 => 'Regular${1}Classes',
        // Old marketing headline on the workshops page.
        "/\bFlexible\s*(?:&amp;|&|and)\s*Fun{$sep}Workshops\b/iu"                 => 'Workshops',
        "/\bExplore{$sep}Our{$sep}Art{$sep}Classes\b/iu"                          => 'Regular Classes',
    ];

    $result = preg_replace(array_keys($patterns), array_values($patterns), $text);
    if ($result === null) {
        return $text;
    }

    // Preserve the original casing style: an all-uppercase source stays uppercase.
    // Entities ("&amp;") and tags ("<br>") are stripped first — both are lowercase
    // by convention and would otherwise mask an all-caps heading.
    $letters = preg_replace('/&[a-z]+;|<[^>]+>/i', '', $text);
    if ($letters !== null && $letters !== '' && $letters === mb_strtoupper($letters, 'UTF-8')) {
        // Upper-case the text but leave HTML entities and tags intact, otherwise
        // "&amp;" becomes "&AMP;" (rendered literally) and "<br>" becomes "<BR>".
        // Each one is swapped for a digit-only placeholder that survives
        // mb_strtoupper() unchanged, then restored afterwards.
        $keep = [];
        $result = preg_replace_callback(
            '/&[a-z]+;|&#\d+;|<[^>]+>/i',
            static function (array $m) use (&$keep): string {
                $keep[] = $m[0];
                return "\0" . (count($keep) - 1) . "\0";
            },
            $result
        ) ?? $result;
        $result = mb_strtoupper($result, 'UTF-8');
        $result = preg_replace_callback(
            '/\0(\d+)\0/',
            static fn(array $m): string => $keep[(int) $m[1]] ?? '',
            $result
        ) ?? $result;
    }

    return $result;
}

/**
 * Rewrite old page names in every theme block's rendered output.
 *
 * Applied centrally rather than per block: the old names live in saved
 * attributes across many blocks, and a global pass means a newly added block
 * cannot silently miss the rename. Only this theme's blocks are touched, and
 * only their visible heading/label elements — never attributes or URLs, so
 * links such as /regular-art-classes/ keep working.
 */
add_filter('render_block', static function (string $content, array $block): string {
    if ($content === '' || strpos($block['blockName'] ?? '', 'ai-zippy/') !== 0) {
        return $content;
    }

    // Rewrite text nodes only — never inside a tag, so href/class/alt stay intact.
    $result = preg_replace_callback(
        '/>([^<]+)</',
        static fn(array $m): string => '>' . ai_zippy_child_rename_page_text($m[1]) . '<',
        $content
    );

    return $result ?? $content;
}, 20, 2);

/**
 * Rewrite old page names in post/page titles (browser tab, headings, menus).
 */
add_filter('the_title', 'ai_zippy_child_rename_page_text', 20, 1);
add_filter('document_title_parts', static function (array $parts): array {
    if (isset($parts['title'])) {
        $parts['title'] = ai_zippy_child_rename_page_text((string) $parts['title']);
    }
    return $parts;
}, 20, 1);

// =============================================================================
// Social links
// =============================================================================

/**
 * The brand's social profiles, in display order.
 *
 * Shared by the footer and the contact page so both stay in sync.
 *
 * @return array<int, array{name:string, url:string}>
 */
function ai_zippy_child_social_links(): array
{
    return [
        ['name' => 'instagram', 'url' => 'https://www.instagram.com/achieversarts'],
        ['name' => 'facebook',  'url' => 'https://www.facebook.com/achieversarts/'],
        ['name' => 'tiktok',    'url' => 'https://www.tiktok.com/@achieversarts'],
        ['name' => 'youtube',   'url' => 'https://www.youtube.com/channel/UCJ6ryG5_ZS6l7PtJj0XhhJg'],
    ];
}

/**
 * Inline SVG icon markup, keyed by icon name.
 *
 * Icons are inlined (rather than an icon font or sprite) so they inherit
 * currentColor and need no extra network request.
 *
 * @param string $name Icon name.
 * @return string SVG markup, or an empty string when the name is unknown.
 */
function ai_zippy_child_icon_svg(string $name): string
{
    $icons = [
        'instagram' => '<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" focusable="false"><path d="M12 2.16c3.2 0 3.58.01 4.85.07 1.17.05 1.8.25 2.23.41.56.22.96.48 1.38.9.42.42.68.82.9 1.38.16.42.36 1.06.41 2.23.06 1.27.07 1.65.07 4.85s-.01 3.58-.07 4.85c-.05 1.17-.25 1.8-.41 2.23-.22.56-.48.96-.9 1.38-.42.42-.82.68-1.38.9-.42.16-1.06.36-2.23.41-1.27.06-1.65.07-4.85.07s-3.58-.01-4.85-.07c-1.17-.05-1.8-.25-2.23-.41-.56-.22-.96-.48-1.38-.9-.42-.42-.68-.82-.9-1.38-.16-.42-.36-1.06-.41-2.23-.06-1.27-.07-1.65-.07-4.85s.01-3.58.07-4.85c.05-1.17.25-1.8.41-2.23.22-.56.48-.96.9-1.38.42-.42.82-.68 1.38-.9.42-.16 1.06-.36 2.23-.41 1.27-.06 1.65-.07 4.85-.07M12 0C8.74 0 8.33.01 7.05.07 5.78.13 4.9.33 4.14.63c-.79.3-1.46.72-2.13 1.38C1.35 2.68.93 3.35.63 4.14.33 4.9.13 5.78.07 7.05.01 8.33 0 8.74 0 12s.01 3.67.07 4.95c.06 1.27.26 2.15.56 2.91.3.79.72 1.46 1.38 2.13.67.66 1.34 1.08 2.13 1.38.76.3 1.64.5 2.91.56C8.33 23.99 8.74 24 12 24s3.67-.01 4.95-.07c1.27-.06 2.15-.26 2.91-.56.79-.3 1.46-.72 2.13-1.38.66-.67 1.08-1.34 1.38-2.13.3-.76.5-1.64.56-2.91.06-1.28.07-1.69.07-4.95s-.01-3.67-.07-4.95c-.06-1.27-.26-2.15-.56-2.91-.3-.79-.72-1.46-1.38-2.13C21.32 1.35 20.65.93 19.86.63 19.1.33 18.22.13 16.95.07 15.67.01 15.26 0 12 0Z"/><path d="M12 5.84a6.16 6.16 0 1 0 0 12.32 6.16 6.16 0 0 0 0-12.32Zm0 10.16a4 4 0 1 1 0-8 4 4 0 0 1 0 8Z"/><circle cx="18.41" cy="5.59" r="1.44"/></svg>',
        'facebook'  => '<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" focusable="false"><path d="M24 12.07C24 5.4 18.63 0 12 0S0 5.4 0 12.07C0 18.1 4.39 23.09 10.13 24v-8.44H7.08v-3.49h3.05V9.41c0-3.02 1.79-4.69 4.53-4.69 1.31 0 2.68.24 2.68.24v2.97h-1.51c-1.49 0-1.96.93-1.96 1.89v2.25h3.33l-.53 3.49h-2.8V24C19.61 23.09 24 18.1 24 12.07Z"/></svg>',
        'tiktok'    => '<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" focusable="false"><path d="M19.59 6.69a4.83 4.83 0 0 1-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 0 1-5.2 1.74 2.89 2.89 0 0 1 2.31-4.64c.3 0 .58.04.86.13V9.4a6.33 6.33 0 0 0-6.34 6.34A6.34 6.34 0 0 0 16.15 20a6.33 6.33 0 0 0 .93-3.29V8.66a8.16 8.16 0 0 0 4.77 1.52V6.73c-.79 0-1.56-.02-2.26-.04Z"/></svg>',
        'youtube'   => '<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" focusable="false"><path d="M23.5 6.19a3.02 3.02 0 0 0-2.12-2.14C19.5 3.55 12 3.55 12 3.55s-7.5 0-9.38.5A3.02 3.02 0 0 0 .5 6.19C0 8.08 0 12 0 12s0 3.92.5 5.81a3.02 3.02 0 0 0 2.12 2.14c1.88.5 9.38.5 9.38.5s7.5 0 9.38-.5a3.02 3.02 0 0 0 2.12-2.14C24 15.92 24 12 24 12s0-3.92-.5-5.81ZM9.55 15.57V8.43L15.82 12l-6.27 3.57Z"/></svg>',
        'whatsapp'  => '<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" focusable="false"><path d="M17.47 14.38c-.3-.15-1.76-.87-2.03-.97-.27-.1-.47-.15-.67.15-.2.3-.77.97-.94 1.17-.17.2-.35.22-.65.07-.3-.15-1.25-.46-2.39-1.47-.88-.79-1.48-1.76-1.65-2.06-.17-.3-.02-.46.13-.61.13-.13.3-.35.45-.52.15-.17.2-.3.3-.5.1-.2.05-.37-.02-.52-.08-.15-.67-1.61-.92-2.21-.24-.58-.49-.5-.67-.51h-.57c-.2 0-.52.07-.79.37-.27.3-1.04 1.02-1.04 2.48s1.06 2.88 1.21 3.08c.15.2 2.1 3.2 5.08 4.49.71.3 1.26.49 1.69.63.71.22 1.36.19 1.87.12.57-.09 1.76-.72 2.01-1.41.25-.7.25-1.29.17-1.41-.07-.13-.27-.2-.57-.35ZM12.05 21.7h-.01a9.6 9.6 0 0 1-4.9-1.34l-.35-.21-3.65.96.97-3.56-.23-.37a9.58 9.58 0 0 1-1.47-5.12c0-5.3 4.32-9.61 9.63-9.61a9.56 9.56 0 0 1 6.8 2.82 9.51 9.51 0 0 1 2.82 6.8c0 5.3-4.32 9.62-9.62 9.62ZM20.5 3.49A11.9 11.9 0 0 0 12.05 0C5.46 0 .1 5.36.1 11.95c0 2.1.55 4.16 1.6 5.98L0 24l6.22-1.63a11.9 11.9 0 0 0 5.82 1.49h.01c6.58 0 11.94-5.36 11.95-11.95a11.87 11.87 0 0 0-3.5-8.42Z"/></svg>',
        'email'     => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="m2.5 6.5 8.4 6.3c.65.5 1.55.5 2.2 0l8.4-6.3"/></svg>',
    ];

    return $icons[$name] ?? '';
}

/**
 * Render the social icon list.
 *
 * @param string $class_name Root CSS class for the list.
 * @return string List markup, or an empty string when there are no links.
 */
function ai_zippy_child_social_links_html(string $class_name = 'achiever-social'): string
{
    $links = ai_zippy_child_social_links();
    if (empty($links)) {
        return '';
    }

    $items = '';
    foreach ($links as $link) {
        $icon = ai_zippy_child_icon_svg($link['name']);
        if ($icon === '') {
            continue;
        }
        $items .= sprintf(
            '<li class="%1$s__item"><a class="%1$s__link %1$s__link--%2$s" href="%3$s" target="_blank" rel="noopener noreferrer" aria-label="%4$s">%5$s</a></li>',
            esc_attr($class_name),
            esc_attr($link['name']),
            esc_url($link['url']),
            esc_attr(ucfirst($link['name'])),
            $icon // phpcs:ignore WordPress.Security.EscapeOutput -- static theme SVG
        );
    }

    if ($items === '') {
        return '';
    }

    return sprintf('<ul class="%s">%s</ul>', esc_attr($class_name), $items);
}

/**
 * [achiever_social] — renders the social icon list inside static template parts,
 * which cannot call PHP directly.
 */
add_shortcode('achiever_social', static function ($atts): string {
    $atts = shortcode_atts(['class' => 'achiever-social'], $atts, 'achiever_social');
    return ai_zippy_child_social_links_html(sanitize_html_class($atts['class'], 'achiever-social'));
});

/**
 * Fill the footer's social placeholder with the icon list.
 *
 * The footer is a static template part, so it carries an empty
 * `[data-achiever-social]` element that this filter populates. Keeping the
 * markup here means the links and SVGs are defined in exactly one place.
 */
add_filter('render_block', static function (string $content): string {
    if (strpos($content, 'data-achiever-social') === false) {
        return $content;
    }
    return str_replace(
        '<div class="achiever-footer__social-col" data-achiever-social></div>',
        '<div class="achiever-footer__social-col">' . ai_zippy_child_social_links_html('achiever-social') . '</div>',
        $content
    );
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

// === Achiever's Art: Load frontend CSS in the block editor iframe (WP 6.3+) ===
// enqueue_block_editor_assets only targets the OUTER wp-admin page, not the iframed
// canvas — so blocks render unstyled inside the editor. add_editor_style() would
// reach the iframe but WP prefixes every selector with `.editor-styles-wrapper`,
// which breaks our `html body …` / absolute selectors. enqueue_block_assets fires
// in BOTH the frontend AND the editor iframe and enqueues the stylesheet verbatim
// (no prefixing), so the editor matches the frontend 1:1.
add_action('enqueue_block_assets', function (): void {
    // Frontend already enqueues child-style via Vite (ai-zippy-child-style);
    // only add it inside the editor iframe to avoid a duplicate on the frontend.
    if (!is_admin()) {
        return;
    }

    // 1. Parent theme frontend CSS (same file the frontend loads via theme.js bundle)
    $parent_css_deps = [];
    $parent_css_file = get_template_directory() . '/assets/dist/css/style.css';
    if (file_exists($parent_css_file)) {
        wp_enqueue_style(
            'achiever-art-editor-parent-styles',
            get_template_directory_uri() . '/assets/dist/css/style.css',
            [],
            filemtime($parent_css_file)
        );
        $parent_css_deps[] = 'achiever-art-editor-parent-styles';
    }

    // 2. Child theme frontend CSS — after parent, mirroring the frontend load order
    $dist_dir = get_stylesheet_directory() . '/assets/dist';
    $dist_uri = get_stylesheet_directory_uri() . '/assets/dist';
    $css_file = $dist_dir . '/css/child-style.css';
    $child_css_deps = $parent_css_deps;

    if (file_exists($css_file)) {
        wp_enqueue_style(
            'achiever-art-editor-styles',
            $dist_uri . '/css/child-style.css',
            $parent_css_deps,
            filemtime($css_file)
        );
        $child_css_deps = ['achiever-art-editor-styles'];
    }

    wp_enqueue_style(
        'achiever-art-fonts-editor',
        'https://fonts.googleapis.com/css2?family=Baloo+2:wght@500;700;800&family=Caveat:wght@600&family=Poppins:wght@400;500;600;700&display=swap',
        [],
        null
    );

    // 3. High-priority overrides — the exact same CSS the frontend injects via
    // wp_add_inline_style('wp-block-library', ...), loaded last so it wins here too.
    wp_register_style('achiever-art-editor-overrides', false, $child_css_deps, null);
    wp_enqueue_style('achiever-art-editor-overrides');
    wp_add_inline_style('achiever-art-editor-overrides', ai_zippy_child_override_css());
});

// === High priority CSS overrides for Section Titles, Logos & Gallery Slider ===
// Shared source for BOTH the frontend and the editor iframe — keep them 1:1.
function ai_zippy_child_override_css(): string
{
    return '
        .wp-block-woocommerce-mini-cart,
        .wp-block-woocommerce-customer-account,
        .wc-block-mini-cart,
        .wc-block-customer-account-link,
        .wc-block-mini-cart__button {
            display: none !important;
        }

        /* ─── HEADER LOGO (larger than the footer logo) ─────────────── */
        html body .achiever-header__logo {
            max-height: 110px !important;
        }

        html body .achiever-header__logo .wp-block-site-logo,
        html body .achiever-header__logo .custom-logo-link {
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            height: 110px !important;
            max-height: 110px !important;
            width: auto !important;
        }

        html body .achiever-header__logo img,
        html body .achiever-header__logo svg {
            height: 110px !important;
            max-height: 110px !important;
            width: auto !important;
            max-width: 340px !important;
            object-fit: contain !important;
            display: block !important;
        }

        /* ─── FOOTER LOGO ───────────────────────────────────────────── */
        html body .achiever-footer__logo {
            max-height: 80px !important;
        }

        html body .achiever-footer__logo .wp-block-site-logo,
        html body .achiever-footer__logo .custom-logo-link {
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            height: 80px !important;
            max-height: 80px !important;
            width: auto !important;
        }

        html body .achiever-footer__logo img,
        html body .achiever-footer__logo svg {
            height: 80px !important;
            max-height: 80px !important;
            width: auto !important;
            max-width: 260px !important;
            object-fit: contain !important;
            display: block !important;
        }

        /* ─── SECTION TITLES (52px) ────────────────────────────────── */
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
            font-size: 52px !important;
            line-height: 1.05 !important;
        }

        @media (max-width: 767px) {
            html body h1.achiever-classes-hero__heading,
            html body h2.achiever-classes-hero__section-title,
            html body .achiever-classes-detail__copy h2,
            html body .achiever-classes-detail__gallery h2 {
                font-size: 34px !important;
            }
        }

        /* ─── CLASSES HERO SLIDER ──────────────────────────────────── */
        html body .achiever-classes-hero__title-section {
            background-color: #ffffff !important;
            background-image: url("/wp-content/uploads/2026/07/hero-bg-mascots.png") !important;
            background-size: cover !important;
            background-position: center top !important;
            background-repeat: no-repeat !important;
            min-height: 340px !important;
            display: flex !important;
            flex-direction: column !important;
            justify-content: center !important;
            align-items: center !important;
            padding-top: 32px !important;
            padding-bottom: 24px !important;
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
            font-size: 52px !important;
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
            background-color: #fffec9 !important;
            padding-top: 38px !important;
            padding-bottom: 40px !important;
            text-align: center !important;
        }

        html body .achiever-course-intro__programme .achiever-course-intro__section-title {
            color: #5b7dba !important;
            font-size: 52px !important;
            font-weight: 800 !important;
            letter-spacing: 0.12em !important;
            line-height: 1.1 !important;
            margin-bottom: 2px !important;
        }

        html body .achiever-course-intro__programme-subtitle {
            color: #5b7dba !important;
            font-size: 21px !important;
            font-weight: 400 !important;
            margin-top: 0 !important;
            margin-bottom: 24px !important;
        }

        html body .achiever-course-intro__programme-grid {
            display: grid !important;
            grid-template-columns: repeat(2, 1fr) !important;
            gap: 22px !important;
            max-width: 1015px !important;
            margin: 0 auto !important;
        }

        html body .achiever-course-intro__programme-card {
            background-color: #d9f3ff !important;
            color: #1a1040 !important;
            min-height: 60px !important;
            padding: 4px 24px !important;
            border-radius: 12px !important;
            font-size: 22px !important;
            font-weight: 400 !important;
            line-height: 1.2 !important;
            text-align: center !important;
        }

        /* ─── COURSE INTRO GALLERY SLIDER FIX ──────────────────────── */
        html body .achiever-course-intro__gallery {
            background-color: transparent !important;
            padding: 0 !important;
            width: 100% !important;
            box-sizing: border-box !important;
            display: block !important;
        }

        html body .achiever-course-intro__gallery-slider {
            position: relative !important;
            max-width: none !important;
            margin: 0 auto !important;
            padding-inline: 0 !important;
            box-sizing: border-box !important;
        }

        html body .achiever-course-intro__gallery-track {
            display: flex !important;
            flex-direction: row !important;
            flex-wrap: nowrap !important;
            gap: 0 !important;
            overflow-x: auto !important;
            scroll-snap-type: x mandatory !important;
            scrollbar-width: none !important;
            padding: 0 !important;
            width: 100% !important;
            box-sizing: border-box !important;
        }

        html body .achiever-course-intro__gallery-track::-webkit-scrollbar {
            display: none !important;
        }

        html body .achiever-course-intro__gallery-item {
            flex: 0 0 calc(100% / 3) !important;
            width: auto !important;
            min-width: 0 !important;
            height: 365px !important;
            border-radius: 0 !important;
            overflow: hidden !important;
            scroll-snap-align: start !important;
            box-shadow: none !important;
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
            width: 58px !important;
            height: 88px !important;
            border-radius: 0 !important;
            background-color: transparent !important;
            color: #ffffff !important;
            border: none !important;
            box-shadow: none !important;
            text-shadow: 0 2px 10px rgba(0,0,0,0.28) !important;
            cursor: pointer !important;
            font-size: 82px !important;
            font-weight: 300 !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
        }

        html body .achiever-course-intro__gallery-arrow--prev {
            left: 14px !important;
        }

        html body .achiever-course-intro__gallery-arrow--next {
            right: 14px !important;
        }

        @media (max-width: 767px) {
            html body .achiever-course-intro__programme .achiever-course-intro__section-title {
                font-size: 36px !important;
                letter-spacing: 0.08em !important;
            }
            html body .achiever-course-intro__programme-subtitle {
                font-size: 17px !important;
            }
            html body .achiever-course-intro__programme-grid {
                grid-template-columns: 1fr !important;
                gap: 14px !important;
            }
            html body .achiever-course-intro__programme-card {
                min-height: 56px !important;
                padding: 12px 16px !important;
                font-size: 17px !important;
            }
            html body .achiever-course-intro__gallery-item {
                flex-basis: 86vw !important;
                height: 330px !important;
            }
            html body .achiever-course-intro__gallery-arrow {
                width: 44px !important;
                height: 68px !important;
                font-size: 62px !important;
            }
            html body .achiever-course-intro__gallery-arrow--prev {
                left: 4px !important;
            }
            html body .achiever-course-intro__gallery-arrow--next {
                right: 4px !important;
            }
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

        /* Regular Art Classes gallery — screenshot-faithful full-width strip.
           overflow-y stays visible so the mascot sticker can sit above the
           section edge (negative Vertical position) without being clipped. */
        html body .achiever-classes-detail__gallery {
            width: 100% !important;
            margin: 0 !important;
            padding: 0 !important;
            overflow-x: clip !important;
            overflow-y: visible !important;
            background: #ffffff !important;
        }

        html body .achiever-classes-detail__gallery-header {
            position: relative !important;
            display: flex !important;
            width: 100% !important;
            max-width: none !important;
            height: 388px !important;
            margin: 0 !important;
            padding: 58px 70px 0 !important;
            align-items: center !important;
            justify-content: center !important;
            overflow: visible !important;
            background: linear-gradient(to bottom, #ffe4ee 0, #ffe4ee 58px, #ffffff 58px, #ffffff 100%) !important;
            box-sizing: border-box !important;
        }

        html body .achiever-classes-detail__mascot-sticker {
            position: absolute !important;
            top: calc(var(--gallery-mascot-desktop-y, 21.5631443299) * 1%) !important;
            left: calc(var(--gallery-mascot-desktop-x, 12.5967325881) * 1%) !important;
            width: calc(var(--gallery-mascot-desktop-size, 245) * 1px) !important;
            height: auto !important;
            max-width: none !important;
            margin: 0 !important;
            right: auto !important;
            bottom: auto !important;
            transform: translate(-50%, -50%) !important;
            object-fit: contain !important;
        }

        html body .achiever-classes-detail__gallery .achiever-classes-detail__gallery-header h2 {
            width: min(100%, 780px) !important;
            margin: 0 !important;
            color: #ff6584 !important;
            font-family: "Poppins", sans-serif !important;
            font-size: clamp(34px, 4.6vw, 52px) !important;
            font-weight: 800 !important;
            line-height: 0.94 !important;
            letter-spacing: 0.1em !important;
            text-align: center !important;
            text-transform: uppercase !important;
        }

        html body .achiever-classes-detail__gallery-slider {
            position: relative !important;
            width: 100% !important;
            max-width: none !important;
            height: 365px !important;
            margin: 0 !important;
            padding: 0 !important;
        }

        html body .achiever-classes-detail__gallery .achiever-classes-detail__gallery-grid {
            display: flex !important;
            flex-direction: row !important;
            flex-wrap: nowrap !important;
            width: 100% !important;
            height: 365px !important;
            gap: 0 !important;
            margin: 0 !important;
            padding: 0 !important;
            overflow-x: auto !important;
            overflow-y: hidden !important;
            scroll-snap-type: x mandatory !important;
            scrollbar-width: none !important;
        }

        html body .achiever-classes-detail__gallery .achiever-classes-detail__gallery-item {
            flex: 0 0 31vw !important;
            width: 31vw !important;
            min-width: 31vw !important;
            height: 365px !important;
            margin: 0 !important;
            border-radius: 0 !important;
            box-shadow: none !important;
            scroll-snap-align: start !important;
        }

        html body .achiever-classes-detail__gallery .achiever-classes-detail__gallery-item img,
        html body .achiever-classes-detail__gallery .achiever-classes-detail__gallery-item svg {
            display: block !important;
            width: 100% !important;
            height: 100% !important;
            object-fit: cover !important;
        }

        html body .achiever-classes-detail__gallery-arrow {
            display: grid !important;
            position: absolute !important;
            top: 50% !important;
            z-index: 3 !important;
            width: 82px !important;
            height: 100px !important;
            padding: 0 !important;
            transform: translateY(-50%) !important;
            border: 0 !important;
            border-radius: 0 !important;
            background: transparent !important;
            color: #ffffff !important;
            box-shadow: none !important;
            font-size: 82px !important;
            font-weight: 300 !important;
            line-height: 1 !important;
            opacity: 1 !important;
        }

        html body .achiever-classes-detail__gallery-arrow--prev {
            left: 4px !important;
        }

        html body .achiever-classes-detail__gallery-arrow--next {
            right: 4px !important;
        }

        @media (max-width: 767px) {
            html body .achiever-classes-detail__gallery-header {
                height: 300px !important;
                padding: 58px 20px 0 !important;
            }
            html body .achiever-classes-detail__mascot-sticker {
                top: calc(var(--gallery-mascot-mobile-y, 18.6666666667) * 1%) !important;
                left: calc(var(--gallery-mascot-mobile-x, 19.2) * 1%) !important;
                width: calc(var(--gallery-mascot-mobile-size, 132) * 1px) !important;
            }
            html body .achiever-classes-detail__gallery .achiever-classes-detail__gallery-header h2 {
                width: min(100%, 350px) !important;
                font-size: clamp(34px, 4.6vw, 52px) !important;
                line-height: 0.98 !important;
                letter-spacing: 0.015em !important;
            }
            html body .achiever-classes-detail__gallery-slider,
            html body .achiever-classes-detail__gallery .achiever-classes-detail__gallery-grid,
            html body .achiever-classes-detail__gallery .achiever-classes-detail__gallery-item {
                height: 330px !important;
            }
            html body .achiever-classes-detail__gallery .achiever-classes-detail__gallery-grid {
                flex-direction: row !important;
                flex-wrap: nowrap !important;
            }
            html body .achiever-classes-detail__gallery .achiever-classes-detail__gallery-item {
                flex-basis: 86vw !important;
                width: 86vw !important;
                min-width: 86vw !important;
            }
            html body .achiever-classes-detail__gallery-arrow {
                width: 62px !important;
                height: 82px !important;
                font-size: 62px !important;
            }
        }
    ';
}

add_action('wp_enqueue_scripts', function (): void {
    // Own dedicated handle — attaching to 'wp-block-library' silently drops the
    // CSS on installs that load core block styles separately (handle never
    // enqueued), which is exactly what happened on staging.
    wp_register_style('achiever-art-overrides', false, [], null);
    wp_enqueue_style('achiever-art-overrides');
    wp_add_inline_style('achiever-art-overrides', ai_zippy_child_override_css());
}, 9999);

// === Hide the default WooCommerce shop archive title (page-hero banner already shows it) ===
add_filter('woocommerce_show_page_title', '__return_false');

$form_handler = get_stylesheet_directory() . '/inc/form-handler.php';
if (file_exists($form_handler)) {
    require_once $form_handler;
}

/**
 * Normalise literal "\n" escape sequences back into real newlines.
 *
 * Block attributes saved through the editor can persist a newline as the two
 * characters backslash + n. Rendering that through nl2br() leaves the escape
 * visible (e.g. "REGULAR\nART CLASSES" reads as "REGULARNART CLASSES"), so
 * every block heading that supports line breaks runs through this first.
 */
if (!function_exists('achiever_normalize_newlines')) :
function achiever_normalize_newlines($text): string
{
    if (!is_string($text) || $text === '') {
        return '';
    }

    return str_replace(['\r\n', '\n', '\r'], "\n", $text);
}
endif;
