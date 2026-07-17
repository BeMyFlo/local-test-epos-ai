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
        'https://fonts.googleapis.com/css2?family=Dancing+Script:wght@400;500;600;700&family=Playfair+Display:ital,wght@0,400;0,500;0,600;0,700;0,800;0,900;1,400;1,700&family=Poppins:wght@300;400;500;600;700;800;900&display=swap',
        [],
        null
    );
}, 5);

// === Achiever's Art: Editor styles (for block preview) ===
add_action('enqueue_block_editor_assets', function (): void {
    wp_enqueue_style(
        'achiever-art-fonts-editor',
        'https://fonts.googleapis.com/css2?family=Dancing+Script:wght@400;500;600;700&family=Playfair+Display:ital,wght@0,400;0,500;0,600;0,700;0,800;0,900;1,400;1,700&family=Poppins:wght@300;400;500;600;700;800;900&display=swap',
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

// === Hide WooCommerce account icon & mini-cart from header ===
add_action('wp_enqueue_scripts', function (): void {
    $css = '
        .wp-block-woocommerce-mini-cart,
        .wp-block-woocommerce-customer-account,
        .wc-block-mini-cart,
        .wc-block-customer-account-link,
        .wc-block-mini-cart__button {
            display: none !important;
        }
        .achiever-party {
            position: relative;
            padding: 60px 20px 50px;
            background: #fef9e0 !important;
            overflow: hidden;
            text-align: center;
        }
        .achiever-party__decor {
            position: absolute;
            z-index: 2;
            pointer-events: none;
        }
        .achiever-party__decor--left {
            top: 0; left: 0; width: 180px;
        }
        .achiever-party__decor--right {
            top: 0; right: 0; width: 200px;
        }
        .achiever-party__header {
            position: relative; z-index: 3; margin-bottom: 30px;
        }
        .achiever-party__pre-heading {
            display: block;
            color: #e8627c;
            font-size: 1.75rem;
            font-weight: 800;
            text-transform: uppercase;
            font-style: italic;
        }
        .achiever-party__heading {
            color: #e8627c;
            font-size: 3rem;
            font-weight: 800;
            text-transform: uppercase;
            margin: 0;
            line-height: 1.1;
        }
        .achiever-party__slider {
            position: relative;
            margin: 0 auto 30px;
            max-width: 1100px;
        }
        .achiever-party__track {
            display: flex;
            gap: 12px;
            overflow-x: auto;
            scroll-snap-type: x mandatory;
            scrollbar-width: none;
            padding: 10px 0;
        }
        .achiever-party__track::-webkit-scrollbar { display: none; }
        .achiever-party__slide {
            flex: 0 0 calc(33.333% - 8px);
            scroll-snap-align: start;
            border-radius: 12px;
            overflow: hidden;
            aspect-ratio: 4/3;
        }
        .achiever-party__slide img {
            width: 100%; height: 100%; object-fit: cover;
        }
        .achiever-party__slide-placeholder {
            width: 100%; height: 100%;
            background: linear-gradient(135deg, #f8d7da 0%, #fef3cd 100%);
        }
        .achiever-party__arrow {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            z-index: 5;
            width: 36px; height: 36px;
            border: none;
            background: rgba(255,255,255,0.85);
            color: #1a2a4a;
            font-size: 1.5rem;
            border-radius: 50%;
            cursor: pointer;
            display: flex; align-items: center; justify-content: center;
        }
        .achiever-party__arrow--prev { left: 8px; }
        .achiever-party__arrow--next { right: 8px; }
        .achiever-party__subtitle {
            color: #1a2a4a;
            font-size: 0.85rem;
            font-weight: 700;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            margin-bottom: 24px;
        }
        .achiever-party__heading,
        .achiever-party__pre-heading {
            color: #e8627c !important;
        }
    ';
    wp_add_inline_style('wp-block-library', $css);
}, 99);

$form_handler = get_stylesheet_directory() . '/inc/form-handler.php';
if (file_exists($form_handler)) {
    require_once $form_handler;
}

// === Party slider arrows JS ===
add_action('wp_footer', function (): void {
    ?>
    <script>
    (function(){
        const slider = document.querySelector('[data-party-slider]');
        if (!slider) return;
        const track = slider.querySelector('.achiever-party__track');
        const prev = slider.querySelector('.achiever-party__arrow--prev');
        const next = slider.querySelector('.achiever-party__arrow--next');
        if (!track || !prev || !next) return;
        const getScrollAmount = () => {
            const slide = track.querySelector('.achiever-party__slide');
            return slide ? slide.offsetWidth + 12 : 300;
        };
        prev.addEventListener('click', () => {
            track.scrollBy({ left: -getScrollAmount(), behavior: 'smooth' });
        });
        next.addEventListener('click', () => {
            track.scrollBy({ left: getScrollAmount(), behavior: 'smooth' });
        });
    })();
    </script>
    <?php
}, 99);
