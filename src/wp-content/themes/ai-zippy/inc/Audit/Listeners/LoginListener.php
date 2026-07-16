<?php

namespace AiZippy\Audit\Listeners;

use AiZippy\Audit\AuditLogger;

defined('ABSPATH') || exit;

/**
 * Audits every login attempt (both success and failure).
 *
 * Failed-login rows are also the data source for `LoginGuard` — a single
 * `idx_login_block` index lookup answers "how many fails by this IP/user
 * in the last 15 minutes".
 */
class LoginListener
{
    public static function register(): void
    {
        add_action('wp_login',         [self::class, 'onSuccess'], 10, 2);
        add_action('wp_login_failed',  [self::class, 'onFailure'], 10, 2);
    }

    public static function onSuccess(string $user_login, \WP_User $user): void
    {
        // wp_get_current_user() may not yet reflect the new login at this hook
        // — pass details directly to logAnonymous which writes user_login + IP.
        AuditLogger::logAnonymous(
            'login.success',
            $user_login,
            'user',
            (int) $user->ID,
            $user->display_name ?: $user_login
        );
    }

    /**
     * @param string         $user_login Attempted login (may not exist).
     * @param \WP_Error|null $error      WP's error object (signature added in WP 5.4).
     */
    public static function onFailure(string $user_login, $error = null): void
    {
        $code = '';
        if ($error instanceof \WP_Error) {
            $code = (string) $error->get_error_code();
        }

        AuditLogger::logAnonymous(
            'login.failed',
            $user_login,
            'user',
            0,
            $user_login,
            $code !== '' ? ['reason' => $code] : []
        );
    }
}
