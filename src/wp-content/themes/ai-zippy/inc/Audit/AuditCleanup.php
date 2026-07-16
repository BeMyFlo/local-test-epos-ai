<?php

namespace AiZippy\Audit;

defined('ABSPATH') || exit;

/**
 * Daily cleanup of old audit rows.
 *
 * Default retention: 90 days. Configurable via the `ai_zippy_audit_retention_days`
 * option (set from the admin panel).
 *
 * Batched: max 5000 rows deleted per run. If we hit the cap there are likely
 * more old rows to remove, so we re-schedule a single follow-up event 60s later.
 * This keeps each query short and avoids long row-locks on big tables.
 */
class AuditCleanup
{
    public const OPT_RETENTION = 'ai_zippy_audit_retention_days';
    public const DEFAULT_DAYS  = 90;
    public const BATCH_SIZE    = 5000;

    public static function register(): void
    {
        add_action(AuditInstaller::CRON_HOOK, [self::class, 'run']);
    }

    /**
     * Cron entrypoint. Returns the number of rows deleted (also used by the
     * REST "run cleanup now" button).
     */
    public static function run(): int
    {
        global $wpdb;

        $days = (int) get_option(self::OPT_RETENTION, self::DEFAULT_DAYS);
        if ($days < 1) {
            $days = self::DEFAULT_DAYS;
        }

        $table = AuditInstaller::tableName();

        try {
            $deleted = (int) $wpdb->query(
                $wpdb->prepare(
                    "DELETE FROM {$table}
                     WHERE created_at < DATE_SUB(NOW(), INTERVAL %d DAY)
                     LIMIT %d",
                    $days,
                    self::BATCH_SIZE
                )
            );

            // If we hit the batch cap, schedule a follow-up so the next chunk
            // gets cleared without waiting another 24h.
            if ($deleted >= self::BATCH_SIZE) {
                wp_schedule_single_event(time() + 60, AuditInstaller::CRON_HOOK);
            }

            return $deleted;
        } catch (\Throwable $e) {
            if (WP_DEBUG) {
                error_log('[AiZippy\\Audit] cleanup error: ' . $e->getMessage());
            }
            return 0;
        }
    }
}
