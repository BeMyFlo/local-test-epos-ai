<?php

namespace AiZippy\Admin;

defined('ABSPATH') || exit;

/**
 * Theme Options Admin Page.
 *
 * Adds a dedicated "Zippy AI" menu to the WordPress dashboard.
 */
class ThemeOptions
{
    public const OPTION_LOADING_PAGE_KEY = 'ai_zippy_loading_page';
    public const OPTION_REVISIONS_KEY    = 'ai_zippy_revisions_limit';
    public const SLUG                    = 'zippy-ai-settings';

    /**
     * Register hooks.
     */
    public static function register(): void
    {
        add_action('admin_menu', [self::class, 'addAdminMenu']);
        add_action('admin_init', [self::class, 'registerSettings']);

        // Limit stored revisions based on the saved option.
        add_filter('wp_revisions_to_keep', [self::class, 'revisionsToKeep'], 10, 2);

        // Frontend hooks
        add_action('wp_body_open', [self::class, 'renderLoadingPage']);
        add_action('wp_head', [self::class, 'renderLoadingStyles']);
    }

    /**
     * Add the dedicated admin menu.
     */
    public static function addAdminMenu(): void
    {
        add_menu_page(
            __('Zippy AI Settings', 'ai-zippy'),
            __('Zippy AI', 'ai-zippy'),
            'manage_options',
            self::SLUG,
            [self::class, 'renderSettingsPage'],
            'dashicons-star-filled',
            60
        );
    }

    /**
     * Register theme settings using the Settings API.
     */
    public static function registerSettings(): void
    {
        register_setting(
            'zippy_ai_settings_group',
            self::OPTION_LOADING_PAGE_KEY,
            [
                'type'              => 'boolean',
                'sanitize_callback' => 'rest_sanitize_boolean',
                'default'           => false,
            ]
        );

        register_setting(
            'zippy_ai_settings_group',
            self::OPTION_REVISIONS_KEY,
            [
                'type'              => 'integer',
                'sanitize_callback' => [self::class, 'sanitizeRevisionsLimit'],
                'default'           => 10,
            ]
        );

        add_settings_section(
            'zippy_ai_general_section',
            __('General Settings', 'ai-zippy'),
            null,
            self::SLUG
        );

        add_settings_section(
            'zippy_ai_revisions_section',
            __('Content Revisions', 'ai-zippy'),
            null,
            self::SLUG
        );

        add_settings_field(
            self::OPTION_LOADING_PAGE_KEY,
            __('Enable Loading Page', 'ai-zippy'),
            [self::class, 'renderToggleField'],
            self::SLUG,
            'zippy_ai_general_section'
        );

        add_settings_field(
            self::OPTION_REVISIONS_KEY,
            __('Revisions per Post', 'ai-zippy'),
            [self::class, 'renderRevisionsField'],
            self::SLUG,
            'zippy_ai_revisions_section'
        );
    }

    /**
     * Render the toggle field.
     */
    public static function renderToggleField(): void
    {
        $value = get_option(self::OPTION_LOADING_PAGE_KEY, false);
        ?>
        <label class="ai-zippy-switch">
            <input type="checkbox" name="<?php echo esc_attr(self::OPTION_LOADING_PAGE_KEY); ?>" value="1" <?php checked(1, $value); ?>>
            <span class="ai-zippy-slider"></span>
        </label>
        <p class="description">
            <?php _e('Show a loading screen with site logo and spinner before content appears.', 'ai-zippy'); ?>
        </p>
        <style>
            .ai-zippy-switch {
                position: relative;
                display: inline-block;
                width: 48px;
                height: 24px;
            }
            .ai-zippy-switch input { 
                opacity: 0;
                width: 0;
                height: 0;
            }
            .ai-zippy-slider {
                position: absolute;
                cursor: pointer;
                top: 0; left: 0; right: 0; bottom: 0;
                background-color: #ccc;
                transition: .4s;
                border-radius: 24px;
            }
            .ai-zippy-slider:before {
                position: absolute;
                content: "";
                height: 18px;
                width: 18px;
                left: 3px;
                bottom: 3px;
                background-color: white;
                transition: .4s;
                border-radius: 50%;
            }
            input:checked + .ai-zippy-slider {
                background-color: #c8a97e;
            }
            input:checked + .ai-zippy-slider:before {
                transform: translateX(24px);
            }
        </style>
        <?php
    }

    /**
     * Sanitize the revisions limit input: -1 (unlimited), 0 (disabled), or 1–100.
     */
    public static function sanitizeRevisionsLimit(mixed $value): int
    {
        $value = (int) $value;

        if ($value < 0) {
            return -1; // unlimited
        }

        return min(max($value, 0), 100);
    }

    /**
     * Filter: how many revisions to keep per post.
     * Applies to pages and products (the post types we enabled revisions on).
     */
    public static function revisionsToKeep(int $num, \WP_Post $post): int
    {
        if (!in_array($post->post_type, ['page', 'product'], true)) {
            return $num;
        }

        $limit = (int) get_option(self::OPTION_REVISIONS_KEY, 10);

        // -1 means keep all; 0 means disabled (but we still return 0 — WP handles it)
        return $limit;
    }

    /**
     * Render the revisions limit field.
     */
    public static function renderRevisionsField(): void
    {
        $value = (int) get_option(self::OPTION_REVISIONS_KEY, 10);
        ?>
        <div style="display:flex; align-items:center; gap:12px; flex-wrap:wrap;">
            <input
                type="number"
                name="<?php echo esc_attr(self::OPTION_REVISIONS_KEY); ?>"
                value="<?php echo esc_attr($value); ?>"
                min="-1"
                max="100"
                step="1"
                style="width:80px;"
                class="small-text"
            >
            <p class="description" style="margin:0;">
                <?php _e('Maximum revisions to keep per page or product. Use <code>-1</code> to keep all, <code>0</code> to disable revisions.', 'ai-zippy'); ?>
            </p>
        </div>
        <?php
    }

    /**
     * Render the settings page HTML.
     */
    public static function renderSettingsPage(): void
    {
        if (!current_user_can('manage_options')) {
            return;
        }

        ?>
        <div class="wrap" style="max-width: 800px; margin-top: 30px;">
            <h1 style="margin-bottom: 20px; font-weight: 700;">
                <span class="dashicons dashicons-star-filled" style="font-size: 32px; width: 32px; height: 32px; color: #c8a97e;"></span>
                <?php echo esc_html(get_admin_page_title()); ?>
            </h1>

            <div class="card" style="padding: 24px; border-radius: 8px; border: 1px solid #e2e8f0; box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);">
                <form action="options.php" method="post">
                    <?php
                    settings_fields('zippy_ai_settings_group');
                    do_settings_sections(self::SLUG);
                    submit_button(__('Save Settings', 'ai-zippy'), 'primary large');
                    ?>
                </form>
            </div>
        </div>
        <?php
    }

    /**
     * Check if the loading page is enabled.
     */
    public static function isEnabled(): bool
    {
        return (bool) get_option(self::OPTION_LOADING_PAGE_KEY, false);
    }

    /**
     * Render the CSS for the loading page in head.
     */
    public static function renderLoadingStyles(): void
    {
        if (!self::isEnabled() || is_admin()) {
            return;
        }

        ?>
        <style id="ai-zippy-loading-styles">
            #ai-zippy-loader {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: #ffffff;
                z-index: 999999;
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                transition: opacity 0.5s ease, visibility 0.5s ease;
            }

            #ai-zippy-loader.loaded {
                opacity: 0;
                visibility: hidden;
            }

            .ai-zippy-loader-content {
                text-align: center;
            }

            .ai-zippy-loader-logo {
                margin-bottom: 30px;
                max-width: 150px;
                height: auto;
                animation: pulse 2s infinite ease-in-out;
            }

            .ai-zippy-spinner {
                width: 40px;
                height: 40px;
                border: 3px solid rgba(0,0,0,0.1);
                border-top: 3px solid var(--wp--preset--color--accent, #c8a97e);
                border-radius: 50%;
                animation: spin 1s linear infinite;
                margin: 0 auto;
            }

            @keyframes spin {
                0% { transform: rotate(0deg); }
                100% { transform: rotate(360deg); }
            }

            @keyframes pulse {
                0% { transform: scale(0.95); opacity: 0.8; }
                50% { transform: scale(1); opacity: 1; }
                100% { transform: scale(0.95); opacity: 0.8; }
            }
        </style>
        <?php
    }

    /**
     * Render the loading page HTML.
     */
    public static function renderLoadingPage(): void
    {
        if (!self::isEnabled() || is_admin()) {
            return;
        }

        $logo_id = get_theme_mod('custom_logo');
        $logo_url = '';
        
        if ($logo_id) {
            $logo_data = wp_get_attachment_image_src($logo_id, 'full');
            if ($logo_data) {
                $logo_url = $logo_data[0];
            }
        }

        ?>
        <div id="ai-zippy-loader">
            <div class="ai-zippy-loader-content">
                <?php if ($logo_url) : ?>
                    <img src="<?php echo esc_url($logo_url); ?>" alt="<?php echo esc_attr(get_bloginfo('name')); ?>" class="ai-zippy-loader-logo">
                <?php else : ?>
                    <h1 class="ai-zippy-loader-logo" style="font-family: sans-serif; font-weight: 700;"><?php bloginfo('name'); ?></h1>
                <?php endif; ?>
                <div class="ai-zippy-spinner"></div>
            </div>
        </div>
        <script id="ai-zippy-loader-js">
            (function() {
                var loaderHidden = false;
                function hideLoader() {
                    if (loaderHidden) return;
                    var loader = document.getElementById('ai-zippy-loader');
                    if (loader) {
                        loader.classList.add('loaded');
                        setTimeout(function() {
                            loader.remove();
                        }, 500);
                    }
                    loaderHidden = true;
                }

                var fallback = setTimeout(hideLoader, 10000);

                window.addEventListener('load', function() {
                    setTimeout(hideLoader, 500);
                });

                window.hideAiZippyLoader = function() {
                    clearTimeout(fallback);
                    hideLoader();
                };
            })();
        </script>
        <?php
    }
}
