<?php

namespace AiZippy\Audit\Listeners;

use AiZippy\Audit\AuditLogger;

defined('ABSPATH') || exit;

/**
 * Tracks plugin lifecycle events:
 *   - plugin.install   — plugin newly added (uploaded zip / installed from .org)
 *   - plugin.update    — plugin updated to a new version
 *   - plugin.activate  — plugin activated
 *   - plugin.deactivate — plugin deactivated
 *   - plugin.delete    — plugin files removed
 *
 * Object label is the plugin slug or display name; meta carries the version.
 */
class PluginListener
{
    public static function register(): void
    {
        add_action('activated_plugin',          [self::class, 'onActivate'],   10, 2);
        add_action('deactivated_plugin',        [self::class, 'onDeactivate'], 10, 2);
        add_action('upgrader_process_complete', [self::class, 'onUpgrader'],   10, 2);
        add_action('delete_plugin',             [self::class, 'onDelete'],     10, 1);
    }

    public static function onActivate(string $plugin, bool $network_wide = false): void
    {
        [$name, $version] = self::pluginInfo($plugin);

        AuditLogger::log(
            'plugin.activate',
            'plugin',
            0,
            $name,
            [
                'plugin'       => $plugin,
                'version'      => $version,
                'network_wide' => (bool) $network_wide,
            ]
        );
    }

    public static function onDeactivate(string $plugin, bool $network_deactivating = false): void
    {
        [$name, $version] = self::pluginInfo($plugin);

        AuditLogger::log(
            'plugin.deactivate',
            'plugin',
            0,
            $name,
            [
                'plugin'              => $plugin,
                'version'             => $version,
                'network_deactivating' => (bool) $network_deactivating,
            ]
        );
    }

    /**
     * Fires after install OR update via WP_Upgrader. We dispatch by
     * `$options['action']` ('install' or 'update') and `$options['type']`
     * (must be 'plugin').
     *
     * @param \WP_Upgrader $upgrader
     * @param array        $options
     */
    public static function onUpgrader($upgrader, array $options): void
    {
        if (($options['type'] ?? '') !== 'plugin') {
            return;
        }

        $action = $options['action'] ?? '';
        if ($action !== 'install' && $action !== 'update') {
            return;
        }

        // Bulk update: $options['plugins'] is an array of paths.
        // Single install/update: $upgrader->result holds info; for installs
        // we can derive the plugin path from $upgrader->plugin_info().
        $plugins = [];

        if (!empty($options['plugins']) && is_array($options['plugins'])) {
            $plugins = $options['plugins'];
        } else {
            // Single-plugin path
            if (is_object($upgrader) && method_exists($upgrader, 'plugin_info')) {
                $info = $upgrader->plugin_info();
                if (is_string($info) && $info !== '') {
                    $plugins[] = $info;
                }
            }
        }

        $event = $action === 'install' ? 'plugin.install' : 'plugin.update';

        foreach ($plugins as $plugin) {
            [$name, $version] = self::pluginInfo($plugin);

            AuditLogger::log(
                $event,
                'plugin',
                0,
                $name,
                [
                    'plugin'  => $plugin,
                    'version' => $version,
                ]
            );
        }
    }

    public static function onDelete(string $plugin_file): void
    {
        [$name, $version] = self::pluginInfo($plugin_file);

        AuditLogger::log(
            'plugin.delete',
            'plugin',
            0,
            $name ?: $plugin_file,
            [
                'plugin'  => $plugin_file,
                'version' => $version,
            ]
        );
    }

    /**
     * Resolve display name + version from a plugin path. Falls back to the
     * directory slug if header data isn't available (e.g. file already deleted).
     *
     * @return array{0:string,1:string}
     */
    private static function pluginInfo(string $plugin_file): array
    {
        $name    = '';
        $version = '';

        if ($plugin_file !== '') {
            if (!function_exists('get_plugin_data')) {
                require_once ABSPATH . 'wp-admin/includes/plugin.php';
            }
            $abs_path = WP_PLUGIN_DIR . '/' . $plugin_file;
            if (file_exists($abs_path)) {
                $data = @get_plugin_data($abs_path, false, false);
                if (is_array($data)) {
                    $name    = (string) ($data['Name'] ?? '');
                    $version = (string) ($data['Version'] ?? '');
                }
            }

            // Fallback: derive a readable slug from the path
            if ($name === '') {
                $parts = explode('/', $plugin_file);
                $name  = $parts[0] ?? $plugin_file;
            }
        }

        return [$name, $version];
    }
}
