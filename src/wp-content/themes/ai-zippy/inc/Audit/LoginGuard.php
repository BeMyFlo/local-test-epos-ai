<?php

namespace AiZippy\Audit;

use AiZippy\Core\RateLimiter;

defined('ABSPATH') || exit;

/**
 * Soft rate limit for login attempts, backed by the audit log.
 *
 * If 5 or more `login.failed` rows exist for the current IP OR username
 * within the last 15 minutes, every further attempt returns a WP_Error
 * before WordPress checks the password.
 *
 * Why no separate counter table: the audit log is *already* writing this
 * data; the `idx_login_block` covering index makes the COUNT query O(log n)
 * even at millions of rows. One source of truth.
 *
 * Fail-open: if the DB query throws, the guard permits the login attempt.
 * Availability beats over-cautious blocking for a soft rate limit.
 */
class LoginGuard
{
    public const MAX_ATTEMPTS  = 5;
    public const WINDOW_MIN    = 15;

    public static function register(): void
    {
        // Priority 30: after WP's defaults (20), before most security plugins (40+).
        add_filter('authenticate', [self::class, 'check'], 30, 3);
    }

    /**
     * @param \WP_User|\WP_Error|null $user
     * @param string                  $username
     * @param string                  $password
     * @return \WP_User|\WP_Error|null
     */
    public static function check($user, string $username, string $password)
    {
        // Empty submissions: let WP show its native errors (no point spending a query).
        if (empty($username) && empty($password)) {
            return $user;
        }

        // If a previous filter already produced a WP_Error, don't override it.
        // But if it produced a successful WP_User, we still want to gate it
        // (a brute-forcer could land on a real password).

        try {
            global $wpdb;
            $table = AuditInstaller::tableName();
            $ip    = RateLimiter::getClientIp();

            $count = (int) $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT COUNT(*) FROM {$table}
                     WHERE event_type = %s
                       AND created_at > DATE_SUB(NOW(), INTERVAL %d MINUTE)
                       AND (ip = %s OR user_login = %s)",
                    'login.failed',
                    self::WINDOW_MIN,
                    $ip,
                    $username
                )
            );

            if ($count >= self::MAX_ATTEMPTS) {
                return new \WP_Error(
                    'too_many_attempts',
                    sprintf(
                        /* translators: %d: minutes */
                        __('Too many failed login attempts. Please try again in %d minutes.', 'ai-zippy'),
                        self::WINDOW_MIN
                    ),
                    ['status' => 429]
                );
            }
        } catch (\Throwable $e) {
            if (WP_DEBUG) {
                error_log('[AiZippy\\LoginGuard] check error: ' . $e->getMessage());
            }
            // Fail-open: don't block legitimate users when our table is unhealthy.
        }

        return $user;
    }
}
