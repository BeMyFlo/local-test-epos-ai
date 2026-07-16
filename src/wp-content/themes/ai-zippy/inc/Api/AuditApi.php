<?php

namespace AiZippy\Api;

use AiZippy\Audit\AuditCleanup;
use AiZippy\Audit\AuditInstaller;
use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

defined('ABSPATH') || exit;

/**
 * REST endpoints for the audit log admin panel.
 *
 *   GET    /ai-zippy/v1/audit-log              — paginated list + filters
 *   GET    /ai-zippy/v1/audit-log/stats        — counts for dashboard cards
 *   GET    /ai-zippy/v1/audit-log/users        — distinct user_logins for filter dropdown
 *   GET    /ai-zippy/v1/audit-log/settings     — retention days
 *   POST   /ai-zippy/v1/audit-log/settings     — update retention days
 *   POST   /ai-zippy/v1/audit-log/cleanup      — trigger cleanup now
 *   DELETE /ai-zippy/v1/audit-log/{id}         — delete single row (compliance)
 *
 * All endpoints guarded by `manage_options`.
 */
class AuditApi
{
    public const NAMESPACE = 'ai-zippy/v1';

    public static function register(): void
    {
        register_rest_route(self::NAMESPACE, '/audit-log', [
            'methods'             => 'GET',
            'callback'            => [self::class, 'getList'],
            'permission_callback' => [self::class, 'canManage'],
            'args'                => self::listArgs(),
        ]);

        register_rest_route(self::NAMESPACE, '/audit-log/stats', [
            'methods'             => 'GET',
            'callback'            => [self::class, 'getStats'],
            'permission_callback' => [self::class, 'canManage'],
        ]);

        register_rest_route(self::NAMESPACE, '/audit-log/users', [
            'methods'             => 'GET',
            'callback'            => [self::class, 'getUsers'],
            'permission_callback' => [self::class, 'canManage'],
        ]);

        register_rest_route(self::NAMESPACE, '/audit-log/settings', [
            [
                'methods'             => 'GET',
                'callback'            => [self::class, 'getSettings'],
                'permission_callback' => [self::class, 'canManage'],
            ],
            [
                'methods'             => 'POST',
                'callback'            => [self::class, 'saveSettings'],
                'permission_callback' => [self::class, 'canManage'],
            ],
        ]);

        register_rest_route(self::NAMESPACE, '/audit-log/cleanup', [
            'methods'             => 'POST',
            'callback'            => [self::class, 'runCleanup'],
            'permission_callback' => [self::class, 'canManage'],
        ]);

        register_rest_route(self::NAMESPACE, '/audit-log/(?P<id>\d+)', [
            'methods'             => 'DELETE',
            'callback'            => [self::class, 'deleteRow'],
            'permission_callback' => [self::class, 'canManage'],
            'args'                => ['id' => ['type' => 'integer']],
        ]);
    }

    public static function canManage(): bool
    {
        return current_user_can('manage_options');
    }

    private static function listArgs(): array
    {
        return [
            'page'        => ['type' => 'integer', 'default' => 1,  'sanitize_callback' => 'absint'],
            'per_page'    => ['type' => 'integer', 'default' => 25, 'sanitize_callback' => 'absint'],
            'event_type'  => ['type' => 'string',  'default' => '', 'sanitize_callback' => 'sanitize_text_field'],
            'object_type' => ['type' => 'string',  'default' => '', 'sanitize_callback' => 'sanitize_text_field'],
            'user_id'     => ['type' => 'integer', 'default' => 0,  'sanitize_callback' => 'absint'],
            'from'        => ['type' => 'string',  'default' => '', 'sanitize_callback' => 'sanitize_text_field'],
            'to'          => ['type' => 'string',  'default' => '', 'sanitize_callback' => 'sanitize_text_field'],
            'search'      => ['type' => 'string',  'default' => '', 'sanitize_callback' => 'sanitize_text_field'],
        ];
    }

    // ---------------------------------------------------------------
    // GET /audit-log
    // ---------------------------------------------------------------

    public static function getList(WP_REST_Request $request): WP_REST_Response
    {
        global $wpdb;
        $table = AuditInstaller::tableName();

        $page      = max(1, (int) $request->get_param('page'));
        $per_page  = min(100, max(1, (int) $request->get_param('per_page')));
        $offset    = ($page - 1) * $per_page;

        [$where, $params] = self::buildWhere($request);

        $sql_count = "SELECT COUNT(*) FROM {$table} {$where}";
        $sql_list  = "SELECT * FROM {$table} {$where} ORDER BY id DESC LIMIT %d OFFSET %d";

        $total_params = $params;
        $list_params  = array_merge($params, [$per_page, $offset]);

        $total = !empty($total_params)
            ? (int) $wpdb->get_var($wpdb->prepare($sql_count, ...$total_params))
            : (int) $wpdb->get_var($sql_count);

        $rows = !empty($list_params)
            ? $wpdb->get_results($wpdb->prepare($sql_list, ...$list_params), ARRAY_A)
            : $wpdb->get_results($wpdb->prepare($sql_list, $per_page, $offset), ARRAY_A);

        $items = array_map([self::class, 'formatRow'], $rows ?: []);

        return rest_ensure_response([
            'items'    => $items,
            'total'    => $total,
            'pages'    => (int) ceil($total / $per_page),
            'page'     => $page,
            'per_page' => $per_page,
        ]);
    }

    private static function buildWhere(WP_REST_Request $request): array
    {
        $clauses = [];
        $params  = [];

        $event_type = (string) $request->get_param('event_type');
        if ($event_type !== '') {
            $types = array_filter(array_map('trim', explode(',', $event_type)));
            if (!empty($types)) {
                $placeholders = implode(',', array_fill(0, count($types), '%s'));
                $clauses[]   = "event_type IN ({$placeholders})";
                $params      = array_merge($params, $types);
            }
        }

        $object_type = (string) $request->get_param('object_type');
        if ($object_type !== '') {
            $types = array_filter(array_map('trim', explode(',', $object_type)));
            if (!empty($types)) {
                $placeholders = implode(',', array_fill(0, count($types), '%s'));
                $clauses[]   = "object_type IN ({$placeholders})";
                $params      = array_merge($params, $types);
            }
        }

        $user_id = (int) $request->get_param('user_id');
        if ($user_id > 0) {
            $clauses[] = 'user_id = %d';
            $params[]  = $user_id;
        }

        $from = (string) $request->get_param('from');
        if ($from !== '' && strtotime($from) !== false) {
            $clauses[] = 'created_at >= %s';
            $params[]  = gmdate('Y-m-d 00:00:00', strtotime($from));
        }

        $to = (string) $request->get_param('to');
        if ($to !== '' && strtotime($to) !== false) {
            $clauses[] = 'created_at <= %s';
            $params[]  = gmdate('Y-m-d 23:59:59', strtotime($to));
        }

        $search = (string) $request->get_param('search');
        if ($search !== '') {
            global $wpdb;
            $like = '%' . $wpdb->esc_like($search) . '%';
            $clauses[] = '(object_label LIKE %s OR user_login LIKE %s)';
            $params[]  = $like;
            $params[]  = $like;
        }

        $where = empty($clauses) ? '' : 'WHERE ' . implode(' AND ', $clauses);
        return [$where, $params];
    }

    private static function formatRow(array $row): array
    {
        $meta = null;
        if (!empty($row['meta'])) {
            $decoded = json_decode($row['meta'], true);
            $meta    = is_array($decoded) ? $decoded : null;
        }

        return [
            'id'           => (int) $row['id'],
            'created_at'   => $row['created_at'],
            'user_id'      => (int) $row['user_id'],
            'user_login'   => $row['user_login'],
            'ip'           => $row['ip'],
            'event_type'   => $row['event_type'],
            'object_type'  => $row['object_type'],
            'object_id'    => (int) $row['object_id'],
            // Decode any HTML entities saved by older rows (pre-fix). New rows
            // are already plain text, so the decode is a safe no-op for them.
            'object_label' => wp_specialchars_decode((string) $row['object_label'], ENT_QUOTES),
            'meta'         => $meta,
        ];
    }

    // ---------------------------------------------------------------
    // GET /audit-log/stats
    // ---------------------------------------------------------------

    public static function getStats(): WP_REST_Response
    {
        global $wpdb;
        $table = AuditInstaller::tableName();

        $totalsByCategory = $wpdb->get_results(
            "SELECT event_type, COUNT(*) as c
             FROM {$table}
             WHERE created_at > DATE_SUB(NOW(), INTERVAL 7 DAY)
             GROUP BY event_type",
            ARRAY_A
        ) ?: [];

        $byEvent = [];
        $total7d = 0;
        foreach ($totalsByCategory as $r) {
            $byEvent[$r['event_type']] = (int) $r['c'];
            $total7d                  += (int) $r['c'];
        }

        $failed24h = (int) $wpdb->get_var(
            "SELECT COUNT(*) FROM {$table}
             WHERE event_type = 'login.failed'
               AND created_at > DATE_SUB(NOW(), INTERVAL 1 DAY)"
        );

        $totalAll = (int) $wpdb->get_var("SELECT COUNT(*) FROM {$table}");

        return rest_ensure_response([
            'total_all'      => $totalAll,
            'total_7d'       => $total7d,
            'failed_24h'     => $failed24h,
            'by_event_7d'    => $byEvent,
        ]);
    }

    // ---------------------------------------------------------------
    // GET /audit-log/users — distinct actor list for the filter dropdown
    // ---------------------------------------------------------------

    public static function getUsers(): WP_REST_Response
    {
        global $wpdb;
        $table = AuditInstaller::tableName();

        $rows = $wpdb->get_results(
            "SELECT user_id, user_login, MAX(created_at) as last_seen
             FROM {$table}
             WHERE user_id > 0
             GROUP BY user_id, user_login
             ORDER BY last_seen DESC
             LIMIT 100",
            ARRAY_A
        ) ?: [];

        return rest_ensure_response(array_map(fn($r) => [
            'user_id'    => (int) $r['user_id'],
            'user_login' => $r['user_login'],
        ], $rows));
    }

    // ---------------------------------------------------------------
    // Settings (retention days)
    // ---------------------------------------------------------------

    public static function getSettings(): WP_REST_Response
    {
        return rest_ensure_response([
            'retention_days' => (int) get_option(AuditCleanup::OPT_RETENTION, AuditCleanup::DEFAULT_DAYS),
        ]);
    }

    public static function saveSettings(WP_REST_Request $request): WP_REST_Response
    {
        $days = (int) $request->get_param('retention_days');
        if ($days < 7) $days = 7;
        if ($days > 3650) $days = 3650;

        update_option(AuditCleanup::OPT_RETENTION, $days, true);

        return rest_ensure_response([
            'retention_days' => $days,
        ]);
    }

    // ---------------------------------------------------------------
    // Cleanup + delete
    // ---------------------------------------------------------------

    public static function runCleanup(): WP_REST_Response
    {
        $deleted = AuditCleanup::run();
        return rest_ensure_response([
            'deleted' => $deleted,
        ]);
    }

    public static function deleteRow(WP_REST_Request $request)
    {
        global $wpdb;
        $id = (int) $request->get_param('id');
        if ($id < 1) {
            return new WP_Error('bad_id', 'Invalid id', ['status' => 400]);
        }

        $rows = $wpdb->delete(AuditInstaller::tableName(), ['id' => $id], ['%d']);

        return rest_ensure_response([
            'deleted' => (int) $rows,
        ]);
    }
}
