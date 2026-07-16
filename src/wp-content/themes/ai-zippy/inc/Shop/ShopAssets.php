<?php

namespace AiZippy\Shop;

defined('ABSPATH') || exit;

/**
 * Enqueue shop filter React app on WooCommerce pages.
 */
class ShopAssets
{
    /**
     * Register hooks.
     */
    public static function register(): void
    {
        add_action('wp_enqueue_scripts', [self::class, 'enqueue']);
    }

    /**
     * Enqueue shop filter assets.
     */
    public static function enqueue(): void
    {
        if (!is_shop() && !is_product_taxonomy()) {
            return;
        }

        \AiZippy\Core\ViteAssets::enqueue(
            'ai-zippy-shop-filter',
            'src/wp-content/themes/ai-zippy/src/js/frontend/shop-filter/index.jsx'
        );

        // On a product category/tag page, pre-seed the filter with the current term.
        if (is_product_taxonomy()) {
            $term = get_queried_object();
            if ($term instanceof \WP_Term) {
                wp_add_inline_script(
                    'ai-zippy-shop-filter',
                    '(function(){' .
                        'var el=document.getElementById("ai-zippy-shop-filter");' .
                        'if(!el)return;' .
                        'var cfg=JSON.parse(el.dataset.config||"{}");' .
                        'cfg.initial_category=' . wp_json_encode($term->slug) . ';' .
                        'el.dataset.config=JSON.stringify(cfg);' .
                    '})();',
                    'before'
                );
            }
        }
    }
}
