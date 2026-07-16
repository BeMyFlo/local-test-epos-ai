<?php

/**
 * Theme Autoloader & Bootstrap
 *
 * - PSR-4 autoloader for AiZippy\ namespace
 * - Registers all modules from inc/ subdirectories
 * - Procedural files in setup/ are loaded directly
 *
 * Namespace mapping:
 *   AiZippy\Core\*     → inc/Core/*.php      (framework: ViteAssets, ThemeSetup, Cache, RateLimiter)
 *   AiZippy\Admin\*    → inc/Admin/*.php     (admin UI: Customizer, ThemeOptions, Typography)
 *   AiZippy\Api\*      → inc/Api/*.php       (REST endpoints)
 *   AiZippy\Hooks\*    → inc/Hooks/*.php     (action/filter glue)
 *   AiZippy\Account\*  → inc/Account/*.php   (My Account feature)
 *   AiZippy\Shop\*     → inc/Shop/*.php      (shop page)
 *   AiZippy\Cart\*     → inc/Cart/*.php      (cart page)
 *   AiZippy\Checkout\* → inc/Checkout/*.php  (checkout + order confirmation)
 *   AiZippy\Audit\*    → inc/Audit/*.php     (admin audit log + login rate limit)
 *   AiZippy\Search\*   → inc/Search/*.php    (search bar block assets)
 *
 * Bootstrap order matters:
 *   1. ViteAssets first — everything else depends on it for asset enqueuing.
 *   2. ThemeSetup second — registers post types, block categories, theme supports.
 *   3. Admin + feature modules — register hooks; order within a group is flexible.
 *   4. REST routes last — hooked onto rest_api_init so actual registration is deferred.
 */

defined('ABSPATH') || exit;

// PSR-4 Autoloader: AiZippy\ → inc/
spl_autoload_register(function (string $class): void {
    $prefix = 'AiZippy\\';

    if (!str_starts_with($class, $prefix)) {
        return;
    }

    $relative = substr($class, strlen($prefix));
    $file = AI_ZIPPY_THEME_DIR . '/inc/' . str_replace('\\', '/', $relative) . '.php';

    if (file_exists($file)) {
        require_once $file;
    }
});

// Load procedural setup files (early boot)
foreach (glob(AI_ZIPPY_THEME_DIR . '/inc/setup/*.php') as $file) {
    require_once $file;
}

// ---- Bootstrap: register all modules ----

// Core
AiZippy\Core\ViteAssets::register();
AiZippy\Core\ThemeSetup::register();

// Admin
AiZippy\Admin\Customizer::register();
AiZippy\Admin\ThemeOptions::register();
AiZippy\Admin\Typography::register();

// Hooks
AiZippy\Hooks\CacheInvalidation::register();

// API
add_action('rest_api_init', [AiZippy\Api\ProductFilterApi::class, 'register']);
add_action('rest_api_init', [AiZippy\Api\TypographyApi::class, 'register']);
add_action('rest_api_init', [AiZippy\Api\SearchApi::class, 'register']);
add_action('rest_api_init', [AiZippy\Api\AuditApi::class, 'register']);

// Search
AiZippy\Search\SearchAssets::register();

// Audit (admin action tracking + login rate limit)
AiZippy\Audit\AuditInstaller::register();
AiZippy\Audit\AuditCleanup::register();
AiZippy\Audit\AuditPage::register();
AiZippy\Audit\AuditAssets::register();
AiZippy\Audit\Listeners\PostListener::register();
AiZippy\Audit\Listeners\WooCommerceListener::register();
AiZippy\Audit\Listeners\LoginListener::register();
AiZippy\Audit\Listeners\PluginListener::register();
AiZippy\Audit\LoginGuard::register();

// Account
AiZippy\Account\AccountAssets::register();

// Product (single product page)
AiZippy\Product\ProductShortcode::register();
AiZippy\Product\ProductAssets::register();
AiZippy\Product\RelatedProductsShortcode::register();

// Shop
AiZippy\Shop\ShopAssets::register();

// Cart
AiZippy\Cart\CartAssets::register();

// Checkout
AiZippy\Checkout\CheckoutSettings::register();
AiZippy\Checkout\CheckoutShortcode::register();
AiZippy\Checkout\OrderConfirmationShortcode::register();
AiZippy\Checkout\CheckoutValidation::register();
AiZippy\Checkout\CheckoutAssets::register();
