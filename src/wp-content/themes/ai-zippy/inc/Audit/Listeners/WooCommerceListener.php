<?php

namespace AiZippy\Audit\Listeners;

use AiZippy\Audit\AuditLogger;

defined('ABSPATH') || exit;

/**
 * WooCommerce audit hooks:
 *   - Products:   create / update / delete
 *   - Categories: create / update / delete
 *   - Brands:     create / update / delete (only if `product_brand` taxonomy exists)
 *   - Settings:   any `woocommerce_*` option write
 *
 * Field diff for products uses the same `pre_post_update` snapshot pattern as
 * PostListener, but tracks WC-specific meta fields too (price, stock, sku).
 */
class WooCommerceListener
{
    /** @var array<int,array<string,mixed>>  Product field snapshots. */
    private static array $previousProduct = [];

    /**
     * Per-request dedupe set. WooCommerce fires `woocommerce_update_product`
     * multiple times during a single save (data store re-saves, lookup table
     * sync, variation parent touches). We only want one audit row per product
     * per request.
     *
     * Keyed by "{action}:{product_id}", e.g. "update:42".
     *
     * @var array<string,bool>
     */
    private static array $logged = [];

    private const TRACKED_PRODUCT_META = [
        '_price',
        '_regular_price',
        '_sale_price',
        '_stock',
        '_stock_status',
        '_sku',
    ];

    private const TRACKED_PRODUCT_FIELDS = [
        'post_title',
        'post_status',
    ];

    public static function register(): void
    {
        // Products
        add_action('pre_post_update',          [self::class, 'captureProduct'], 10, 1);
        add_action('woocommerce_new_product',  [self::class, 'onProductCreate'], 10, 1);
        add_action('woocommerce_update_product', [self::class, 'onProductUpdate'], 10, 1);
        add_action('woocommerce_delete_product', [self::class, 'onProductDelete'], 10, 1);

        // Categories (built-in `product_cat` taxonomy)
        add_action('created_product_cat', [self::class, 'onCategoryCreate'], 10, 2);
        add_action('edited_product_cat',  [self::class, 'onCategoryUpdate'], 10, 2);
        add_action('delete_product_cat',  [self::class, 'onCategoryDelete'], 10, 4);

        // Brands (custom taxonomy, only if registered by a brand plugin)
        add_action('created_product_brand', [self::class, 'onBrandCreate'], 10, 2);
        add_action('edited_product_brand',  [self::class, 'onBrandUpdate'], 10, 2);
        add_action('delete_product_brand',  [self::class, 'onBrandDelete'], 10, 4);

        // WooCommerce settings — generic update_option hook, filtered for `woocommerce_*`
        add_action('updated_option', [self::class, 'onOptionUpdate'], 10, 3);
    }

    // -------------------------------------------------------------------
    // Products
    // -------------------------------------------------------------------

    public static function captureProduct(int $post_id): void
    {
        try {
            $post = get_post($post_id);
            if (!$post || $post->post_type !== 'product') {
                return;
            }

            $snapshot = [];
            foreach (self::TRACKED_PRODUCT_FIELDS as $f) {
                $snapshot[$f] = (string) $post->$f;
            }
            foreach (self::TRACKED_PRODUCT_META as $m) {
                $snapshot[$m] = (string) get_post_meta($post_id, $m, true);
            }

            self::$previousProduct[$post_id] = $snapshot;
        } catch (\Throwable $e) {
            // Snapshot failure: diff will be empty, but the audit row still writes.
        }
    }

    public static function onProductCreate(int $product_id): void
    {
        $key = "create:{$product_id}";
        if (isset(self::$logged[$key])) {
            return;
        }
        self::$logged[$key] = true;

        $title = wp_specialchars_decode(get_the_title($product_id), ENT_QUOTES);
        AuditLogger::log('wc.product.create', 'product', $product_id, $title);
    }

    public static function onProductUpdate(int $product_id): void
    {
        // Dedupe: WC fires `woocommerce_update_product` multiple times per save.
        // We only want one row per product per request.
        $key = "update:{$product_id}";
        if (isset(self::$logged[$key])) {
            return;
        }
        self::$logged[$key] = true;

        $title = wp_specialchars_decode(get_the_title($product_id), ENT_QUOTES);
        $meta  = [];

        if (isset(self::$previousProduct[$product_id])) {
            $before = self::$previousProduct[$product_id];
            $after  = self::buildSnapshot($product_id);
            $changed = [];
            foreach ($before as $key2 => $val) {
                if ((string) $val !== (string) ($after[$key2] ?? '')) {
                    // Drop the leading underscore for readability ("_price" -> "price")
                    $changed[] = ltrim($key2, '_');
                }
            }
            if (!empty($changed)) {
                $meta['changed_fields'] = $changed;
            }
            unset(self::$previousProduct[$product_id]);
        }

        AuditLogger::log('wc.product.update', 'product', $product_id, $title, $meta);
    }

    public static function onProductDelete(int $product_id): void
    {
        $key = "delete:{$product_id}";
        if (isset(self::$logged[$key])) {
            return;
        }
        self::$logged[$key] = true;

        // get_the_title() may already return empty if the post is gone; capture early.
        $raw   = get_the_title($product_id);
        $title = $raw ? wp_specialchars_decode($raw, ENT_QUOTES) : '(deleted product)';
        AuditLogger::log('wc.product.delete', 'product', $product_id, $title);
    }

    private static function buildSnapshot(int $post_id): array
    {
        $post = get_post($post_id);
        if (!$post) {
            return [];
        }
        $snap = [];
        foreach (self::TRACKED_PRODUCT_FIELDS as $f) {
            $snap[$f] = (string) $post->$f;
        }
        foreach (self::TRACKED_PRODUCT_META as $m) {
            $snap[$m] = (string) get_post_meta($post_id, $m, true);
        }
        return $snap;
    }

    // -------------------------------------------------------------------
    // Categories
    // -------------------------------------------------------------------

    public static function onCategoryCreate(int $term_id, int $tt_id): void
    {
        self::logTerm('wc.category.create', 'product_cat', $term_id);
    }

    public static function onCategoryUpdate(int $term_id, int $tt_id): void
    {
        self::logTerm('wc.category.update', 'product_cat', $term_id);
    }

    public static function onCategoryDelete(int $term_id, int $tt_id, $deleted_term, $object_ids): void
    {
        $name = is_object($deleted_term) ? (string) ($deleted_term->name ?? '') : '';
        $name = $name !== '' ? wp_specialchars_decode($name, ENT_QUOTES) : '';
        AuditLogger::log('wc.category.delete', 'product_cat', $term_id, $name);
    }

    // -------------------------------------------------------------------
    // Brands (only if taxonomy exists)
    // -------------------------------------------------------------------

    public static function onBrandCreate(int $term_id, int $tt_id): void
    {
        self::logTerm('wc.brand.create', 'product_brand', $term_id);
    }

    public static function onBrandUpdate(int $term_id, int $tt_id): void
    {
        self::logTerm('wc.brand.update', 'product_brand', $term_id);
    }

    public static function onBrandDelete(int $term_id, int $tt_id, $deleted_term, $object_ids): void
    {
        $name = is_object($deleted_term) ? (string) ($deleted_term->name ?? '') : '';
        $name = $name !== '' ? wp_specialchars_decode($name, ENT_QUOTES) : '';
        AuditLogger::log('wc.brand.delete', 'product_brand', $term_id, $name);
    }

    private static function logTerm(string $event, string $taxonomy, int $term_id): void
    {
        $term = get_term($term_id, $taxonomy);
        $name = (is_object($term) && !is_wp_error($term)) ? (string) $term->name : '';
        $name = $name !== '' ? wp_specialchars_decode($name, ENT_QUOTES) : '';
        AuditLogger::log($event, $taxonomy, $term_id, $name);
    }

    // -------------------------------------------------------------------
    // Settings — any woocommerce_* option write
    // -------------------------------------------------------------------

    /**
     * Hooked on `updated_option`. Fires for every option write site-wide,
     * so we filter to `woocommerce_*` keys only. Skips known noisy options.
     */
    public static function onOptionUpdate(string $option, $old_value, $new_value): void
    {
        if (!str_starts_with($option, 'woocommerce_')) {
            return;
        }

        // Skip transient-like options that change constantly with no admin action.
        static $skip = [
            'woocommerce_db_version',
            'woocommerce_version',
            'woocommerce_admin_notices',
            'woocommerce_meta_box_errors',
            'woocommerce_queue_flush_rewrite_rules',
            'woocommerce_maybe_regenerate_images_hash',
        ];

        if (in_array($option, $skip, true)) {
            return;
        }

        // Don't audit during cron / cli installs (would generate hundreds of rows).
        if ((defined('WP_CLI') && WP_CLI) || (defined('DOING_CRON') && DOING_CRON)) {
            return;
        }

        AuditLogger::log(
            'wc.config.update',
            'option',
            0,
            $option,
            ['option_name' => $option]
        );
    }
}
