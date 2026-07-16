<?php

namespace AiZippy\Audit;

use AiZippy\Admin\ThemeOptions;

defined('ABSPATH') || exit;

/**
 * Registers the "Zippy AI > Audit Log" submenu and renders the React mount point.
 * Mirrors the pattern in `AiZippy\Admin\Typography`.
 */
class AuditPage
{
    public const SUBMENU_SLUG = 'zippy-ai-audit-log';

    public static function register(): void
    {
        add_action('admin_menu', [self::class, 'addSubMenu'], 30);
    }

    public static function addSubMenu(): void
    {
        add_submenu_page(
            ThemeOptions::SLUG,
            __('Audit Log', 'ai-zippy'),
            __('Audit Log', 'ai-zippy'),
            'manage_options',
            self::SUBMENU_SLUG,
            [self::class, 'renderAppMount']
        );
    }

    public static function renderAppMount(): void
    {
        if (!current_user_can('manage_options')) {
            return;
        }
        echo '<div class="wrap" id="ai-zippy-audit-log-app"></div>';
    }
}
