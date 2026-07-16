<?php

namespace AiZippy\Audit;

use AiZippy\Core\ViteAssets;

defined('ABSPATH') || exit;

/**
 * Enqueues the React audit-log app on its admin page only.
 */
class AuditAssets
{
    public static function register(): void
    {
        add_action('admin_enqueue_scripts', [self::class, 'enqueue']);
    }

    public static function enqueue(string $hook): void
    {
        if (!str_contains($hook, AuditPage::SUBMENU_SLUG)) {
            return;
        }

        ViteAssets::enqueueAdmin(
            'ai-zippy-admin-audit-log',
            'src/wp-content/themes/ai-zippy/src/js/admin/audit-log/index.jsx'
        );
    }
}
