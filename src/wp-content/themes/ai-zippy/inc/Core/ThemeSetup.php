<?php

namespace AiZippy\Core;

defined('ABSPATH') || exit;

/**
 * Theme setup: supports, blocks, block categories.
 */
class ThemeSetup
{
    /**
     * Register hooks.
     */
    public static function register(): void
    {
        add_action('after_setup_theme', [self::class, 'setup']);
        add_action('init', [self::class, 'registerBlocks']);
        add_filter('block_categories_all', [self::class, 'blockCategories']);
    }

    /**
     * Theme supports.
     */
    public static function setup(): void
    {
        add_theme_support('wp-block-styles');
        add_theme_support('editor-styles');
        add_theme_support('woocommerce');
        add_theme_support('responsive-embeds');

        // Single product gallery: slider only — main image with thumbnail
        // strip below. We skip zoom and WC's photoswipe lightbox; our custom
        // lightbox at /js/frontend/product/lightbox.js handles full-screen view.
        add_theme_support('wc-product-gallery-slider');

        // Enable revisions for pages and products.
        add_post_type_support('page', 'revisions');
        add_post_type_support('product', 'revisions');
        add_action('wp_enqueue_scripts', 'wp_enqueue_global_styles', 1);
    }

    /**
     * Register custom blocks from assets/blocks (wp-scripts build output).
     *
     * After registration, re-stamp all block style handles with the content-hash
     * version from index.asset.php so CSS cache-busts automatically on every
     * build — no need to bump block.json version manually.
     */
    public static function registerBlocks(): void
    {
        $blocks_dir = AI_ZIPPY_THEME_DIR . '/assets/blocks';

        if (!is_dir($blocks_dir)) {
            return;
        }

        foreach (glob($blocks_dir . '/*/block.json') as $block_json) {
            $block_dir  = dirname($block_json);
            $asset_file = $block_dir . '/index.asset.php';

            register_block_type($block_dir);

            // Re-stamp style handles with the content-hash version so the
            // browser cache busts on every build without touching block.json.
            if (!file_exists($asset_file)) {
                continue;
            }

            $asset   = require $asset_file;
            $version = $asset['version'] ?? null;

            if (!$version) {
                continue;
            }

            // Derive the block name slug from the directory (e.g. "search-bar")
            // and match the handles WordPress auto-registers: "ai-zippy-{slug}-style"
            // and "ai-zippy-{slug}-editor".
            $slug    = basename($block_dir);
            $handles = [
                "ai-zippy-{$slug}-style",
                "ai-zippy-{$slug}-editor",
            ];

            foreach ($handles as $handle) {
                if (wp_style_is($handle, 'registered')) {
                    wp_styles()->registered[$handle]->ver = $version;
                }
            }
        }
    }

    /**
     * Register custom block category.
     */
    public static function blockCategories(array $categories): array
    {
        array_unshift($categories, [
            'slug'  => 'ai-zippy',
            'title' => 'AI Zippy',
            'icon'  => 'star-filled',
        ]);

        return $categories;
    }
}
