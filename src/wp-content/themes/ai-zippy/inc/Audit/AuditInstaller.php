<?php

namespace AiZippy\Audit;

defined('ABSPATH') || exit;

/**
 * Audit log installer — owns the custom database table and its lifecycle.
 *
 * Table: {prefix}ai_zippy_audit_log
 *
 * Creation triggers:
 *   - after_switch_theme (one-time install)
 *   - init via version-gate (re-runs dbDelta() automatically when DB_VERSION bumps)
 *
 * The version gate keeps a tiny option `ai_zippy_audit_db_version` (autoload yes,
 * but it's a single int — negligible). When the constant moves ahead of the
 * stored value, ensureTable() runs again so schema changes deploy automatically.
 */
class AuditInstaller
{
    public const TABLE       = 'ai_zippy_audit_log';
    public const DB_VERSION  = '1.0.0';
    public const OPT_VERSION = 'ai_zippy_audit_db_version';
    public const CRON_HOOK   = 'ai_zippy_audit_cleanup';

    public static function register(): void
    {
        add_action('after_switch_theme', [self::class, 'ensureTable']);
        add_action('init',                [self::class, 'maybeUpgrade']);
        add_action('switch_theme',        [self::class, 'unscheduleCron']);
    }

    public static function tableName(): string
    {
        global $wpdb;
        return $wpdb->prefix . self::TABLE;
    }

    /**
     * Auto-run dbDelta if our stored version is behind the constant.
     * Cheap: a single get_option() call on every request.
     */
    public static function maybeUpgrade(): void
    {
        if (get_option(self::OPT_VERSION) !== self::DB_VERSION) {
            self::ensureTable();
        }
    }

    /**
     * Create or update the audit log table via dbDelta().
     */
    public static function ensureTable(): void
    {
        global $wpdb;

        $table   = self::tableName();
        $charset = $wpdb->get_charset_collate();

        // dbDelta is picky: two spaces after PRIMARY KEY, lowercase types, no IF NOT EXISTS, etc.
        $sql = "CREATE TABLE {$table} (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            user_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
            user_login VARCHAR(60) NOT NULL DEFAULT '',
            ip VARCHAR(45) NOT NULL DEFAULT '',
            event_type VARCHAR(40) NOT NULL,
            object_type VARCHAR(40) NOT NULL DEFAULT '',
            object_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
            object_label VARCHAR(255) NOT NULL DEFAULT '',
            meta LONGTEXT NULL,
            PRIMARY KEY  (id),
            KEY idx_created_at (created_at),
            KEY idx_event_type (event_type),
            KEY idx_user_id (user_id),
            KEY idx_object (object_type, object_id),
            KEY idx_login_block (event_type, user_login, ip, created_at)
        ) {$charset};";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);

        update_option(self::OPT_VERSION, self::DB_VERSION, true);

        self::scheduleCron();
    }

    /**
     * Idempotent: only schedules if not already scheduled.
     */
    public static function scheduleCron(): void
    {
        if (!wp_next_scheduled(self::CRON_HOOK)) {
            wp_schedule_event(time() + HOUR_IN_SECONDS, 'daily', self::CRON_HOOK);
        }
    }

    /**
     * Run on theme deactivation — keep the cron clean.
     */
    public static function unscheduleCron(): void
    {
        $next = wp_next_scheduled(self::CRON_HOOK);
        if ($next) {
            wp_unschedule_event($next, self::CRON_HOOK);
        }
    }
}
