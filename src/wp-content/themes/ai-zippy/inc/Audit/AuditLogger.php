<?php

namespace AiZippy\Audit;

use AiZippy\Core\RateLimiter;

defined('ABSPATH') || exit;

/**
 * Single write API for the audit log.
 *
 * Fail-safe: never throws, never breaks the originating action. If the table
 * doesn't exist or wpdb errors, the write is silently dropped (with one
 * error_log entry) and the caller continues normally.
 */
class AuditLogger
{
    /**
     * Persist one audit event.
     *
     * @param string $event_type   e.g. 'post.update', 'wc.product.create', 'login.failed'
     * @param string $object_type  e.g. 'post', 'page', 'product', 'product_cat', 'option', 'user'
     * @param int    $object_id
     * @param string $object_label denormalized for fast UI listing (post title, term name, etc.)
     * @param array  $meta         arbitrary JSON-serializable extras (changed_fields, option_name, etc.)
     */
    public static function log(
        string $event_type,
        string $object_type = '',
        int $object_id = 0,
        string $object_label = '',
        array $meta = []
    ): void {
        // Don't audit cron-driven self-traffic (avoids infinite loops if a
        // listener ever fires inside a scheduled task).
        if (defined('DOING_CRON') && DOING_CRON) {
            return;
        }

        try {
            global $wpdb;

            $user      = wp_get_current_user();
            $user_id   = $user && $user->exists() ? (int) $user->ID : 0;
            $user_login = $user && $user->exists() ? (string) $user->user_login : '';

            $row = [
                'created_at'   => current_time('mysql'),
                'user_id'      => $user_id,
                'user_login'   => mb_substr($user_login, 0, 60),
                'ip'           => RateLimiter::getClientIp(),
                'event_type'   => mb_substr($event_type, 0, 40),
                'object_type'  => mb_substr($object_type, 0, 40),
                'object_id'    => $object_id,
                'object_label' => mb_substr(wp_strip_all_tags($object_label), 0, 255),
                'meta'         => empty($meta) ? null : wp_json_encode($meta),
            ];

            $result = $wpdb->insert(
                AuditInstaller::tableName(),
                $row,
                ['%s', '%d', '%s', '%s', '%s', '%s', '%d', '%s', '%s']
            );

            if ($result === false && WP_DEBUG) {
                // One log line, no exception — caller must not be affected.
                error_log('[AiZippy\\Audit] insert failed: ' . $wpdb->last_error);
            }
        } catch (\Throwable $e) {
            if (WP_DEBUG) {
                error_log('[AiZippy\\Audit] write error: ' . $e->getMessage());
            }
        }
    }

    /**
     * Helper for listeners: write a "no current user" event (e.g. failed logins
     * where wp_get_current_user() returns the guest sentinel).
     */
    public static function logAnonymous(
        string $event_type,
        string $user_login_attempt = '',
        string $object_type = '',
        int $object_id = 0,
        string $object_label = '',
        array $meta = []
    ): void {
        if (defined('DOING_CRON') && DOING_CRON) {
            return;
        }

        try {
            global $wpdb;

            $row = [
                'created_at'   => current_time('mysql'),
                'user_id'      => 0,
                'user_login'   => mb_substr($user_login_attempt, 0, 60),
                'ip'           => RateLimiter::getClientIp(),
                'event_type'   => mb_substr($event_type, 0, 40),
                'object_type'  => mb_substr($object_type, 0, 40),
                'object_id'    => $object_id,
                'object_label' => mb_substr(wp_strip_all_tags($object_label), 0, 255),
                'meta'         => empty($meta) ? null : wp_json_encode($meta),
            ];

            $result = $wpdb->insert(
                AuditInstaller::tableName(),
                $row,
                ['%s', '%d', '%s', '%s', '%s', '%s', '%d', '%s', '%s']
            );

            if ($result === false && WP_DEBUG) {
                error_log('[AiZippy\\Audit] anon insert failed: ' . $wpdb->last_error);
            }
        } catch (\Throwable $e) {
            if (WP_DEBUG) {
                error_log('[AiZippy\\Audit] anon write error: ' . $e->getMessage());
            }
        }
    }
}
