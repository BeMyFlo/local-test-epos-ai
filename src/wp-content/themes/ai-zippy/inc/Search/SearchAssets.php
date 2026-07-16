<?php

namespace AiZippy\Search;

defined('ABSPATH') || exit;

/**
 * Injects search config (REST URL + nonce) into the page so the
 * search-bar frontend JS can call the API without hardcoding URLs.
 */
class SearchAssets
{
    public static function register(): void
    {
        add_action('wp_enqueue_scripts', [self::class, 'injectConfig'], 20);
    }

    public static function injectConfig(): void
    {
        // Inline script piggybacked on the main theme handle
        // (which is already enqueued by ViteAssets::enqueueTheme)
        wp_add_inline_script(
            'ai-zippy-theme',
            'window.aiZippySearch = ' . wp_json_encode([
                'apiUrl' => esc_url_raw(rest_url('ai-zippy/v1/search')),
                'nonce'  => wp_create_nonce('wp_rest'),
            ]) . ';',
            'before'
        );
    }
}
