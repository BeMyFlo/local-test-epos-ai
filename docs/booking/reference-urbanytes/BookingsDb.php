<?php
// AI-generated
namespace AiZippyChild;

defined('ABSPATH') || exit;

class BookingsDb
{
    /** Bump when the schema below changes so createTable() re-runs dbDelta. */
    public const DB_VERSION = '4';

    public const STATUSES = ['new', 'contacted', 'confirmed', 'paid', 'cancelled'];

    public static function getTableName(): string
    {
        global $wpdb;
        return $wpdb->prefix . 'ibat_bookings';
    }

    public static function init(): void
    {
        add_action('after_setup_theme', [self::class, 'maybeUpgrade']);
    }

    public static function maybeUpgrade(): void
    {
        if (get_option('ibat_bookings_db_version') === self::DB_VERSION) {
            return;
        }
        self::createTable();
        self::addMissingColumns();
        update_option('ibat_bookings_db_version', self::DB_VERSION);
    }

    /**
     * dbDelta can silently skip ALTERs on existing tables, so add any missing
     * columns explicitly and idempotently.
     */
    public static function addMissingColumns(): void
    {
        global $wpdb;
        $table = self::getTableName();

        $existing = $wpdb->get_col("SHOW COLUMNS FROM $table", 0);
        if (!$existing) {
            return;
        }

        $columns = [
            'quote_amount' => "ADD COLUMN quote_amount DECIMAL(10,2) NULL DEFAULT NULL AFTER status",
            'order_id'     => "ADD COLUMN order_id BIGINT(20) UNSIGNED NULL DEFAULT NULL AFTER quote_amount",
            'confirmed_at' => "ADD COLUMN confirmed_at DATETIME NULL DEFAULT NULL AFTER order_id",
            'paid_at'      => "ADD COLUMN paid_at DATETIME NULL DEFAULT NULL AFTER confirmed_at",
            'coach_preference' => "ADD COLUMN coach_preference VARCHAR(100) NULL DEFAULT '' AFTER preferred_time",
            'notes'          => "ADD COLUMN notes TEXT NULL AFTER details",
        ];

        foreach ($columns as $name => $clause) {
            if (!in_array($name, $existing, true)) {
                $wpdb->query("ALTER TABLE $table $clause");
            }
        }
    }

    public static function createTable(): void
    {
        global $wpdb;
        $table_name      = self::getTableName();
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            type VARCHAR(50) NOT NULL DEFAULT 'group_enquiry',
            name VARCHAR(255) NOT NULL,
            company VARCHAR(255) NULL DEFAULT '',
            email VARCHAR(255) NOT NULL,
            phone VARCHAR(100) NOT NULL,
            event_type VARCHAR(100) NULL DEFAULT '',
            group_size VARCHAR(100) NULL DEFAULT '',
            preferred_date VARCHAR(100) NULL DEFAULT '',
            preferred_time VARCHAR(100) NULL DEFAULT '',
            coach_preference VARCHAR(100) NULL DEFAULT '',
            details TEXT NULL,
            notes TEXT NULL,
            status VARCHAR(50) NOT NULL DEFAULT 'new',
            quote_amount DECIMAL(10,2) NULL DEFAULT NULL,
            order_id BIGINT(20) UNSIGNED NULL DEFAULT NULL,
            confirmed_at DATETIME NULL DEFAULT NULL,
            paid_at DATETIME NULL DEFAULT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY status (status),
            KEY type (type),
            KEY order_id (order_id),
            KEY created_at (created_at)
        ) $charset_collate;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);
    }

    public static function insert(array $data): int
    {
        global $wpdb;
        self::createTable();

        $inserted = $wpdb->insert(
            self::getTableName(),
            [
                'type'           => sanitize_text_field($data['type'] ?? 'group_enquiry'),
                'name'           => sanitize_text_field($data['name'] ?? ''),
                'company'        => sanitize_text_field($data['company'] ?? ''),
                'email'          => sanitize_email($data['email'] ?? ''),
                'phone'          => sanitize_text_field($data['phone'] ?? ''),
                'event_type'     => sanitize_text_field($data['event_type'] ?? ''),
                'group_size'     => sanitize_text_field($data['group_size'] ?? ''),
                'preferred_date' => sanitize_text_field($data['preferred_date'] ?? ''),
                'preferred_time' => sanitize_text_field($data['preferred_time'] ?? ''),
                'coach_preference' => sanitize_text_field($data['coach_preference'] ?? ''),
                'details'        => sanitize_textarea_field($data['details'] ?? ''),
                'status'         => 'new',
                'created_at'     => current_time('mysql'),
            ],
            ['%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s']
        );

        return $inserted ? (int) $wpdb->insert_id : 0;
    }

    public static function getBookings(string $search = '', string $status = '', string $type = '', int $limit = 100, int $offset = 0): array
    {
        global $wpdb;
        self::createTable();
        $table_name = self::getTableName();

        $where = ['1=1'];
        $params = [];

        if (!empty($search)) {
            $like = '%' . $wpdb->esc_like($search) . '%';
            $where[] = '(name LIKE %s OR email LIKE %s OR phone LIKE %s OR company LIKE %s OR event_type LIKE %s)';
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
        }

        if (!empty($status) && $status !== 'all') {
            $where[] = 'status = %s';
            $params[] = sanitize_text_field($status);
        }

        if (!empty($type) && $type !== 'all') {
            $where[] = 'type = %s';
            $params[] = sanitize_text_field($type);
        }

        $where_sql = implode(' AND ', $where);
        $query = "SELECT * FROM $table_name WHERE $where_sql ORDER BY created_at DESC LIMIT %d OFFSET %d";
        $params[] = $limit;
        $params[] = $offset;

        $prepared = $wpdb->prepare($query, $params);
        return $wpdb->get_results($prepared, ARRAY_A) ?: [];
    }

    /**
     * How many non-cancelled bookings already hold a given date + time-slot label.
     */
    public static function countForSlot(string $date, string $time): int
    {
        global $wpdb;
        if ($date === '' || $time === '') {
            return 0;
        }
        $table = self::getTableName();
        return (int) $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table WHERE preferred_date = %s AND preferred_time = %s AND status != 'cancelled'",
            $date,
            $time
        ));
    }

    /**
     * Booked counts for every time-slot label on a date: ['10am – 12pm' => 3, …].
     */
    public static function countsForDate(string $date): array
    {
        global $wpdb;
        if ($date === '') {
            return [];
        }
        $table = self::getTableName();
        $rows  = $wpdb->get_results($wpdb->prepare(
            "SELECT preferred_time AS t, COUNT(*) AS c FROM $table
             WHERE preferred_date = %s AND status != 'cancelled' AND preferred_time != ''
             GROUP BY preferred_time",
            $date
        ), ARRAY_A) ?: [];

        $out = [];
        foreach ($rows as $row) {
            $out[$row['t']] = (int) $row['c'];
        }
        return $out;
    }

    public static function getById(int $id): ?array
    {
        global $wpdb;
        $table_name = self::getTableName();
        $row = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $id),
            ARRAY_A
        );
        return $row ?: null;
    }

    public static function getStats(): array
    {
        global $wpdb;
        self::createTable();
        $table_name = self::getTableName();

        $total     = (int) $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
        $new       = (int) $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE status = 'new'");
        $confirmed = (int) $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE status = 'confirmed'");
        $paid      = (int) $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE status = 'paid'");
        $group     = (int) $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE type = 'group_enquiry'");

        return [
            'total'     => $total,
            'new'       => $new,
            'confirmed' => $confirmed,
            'paid'      => $paid,
            'group'     => $group,
        ];
    }

    public static function updateStatus(int $id, string $status): bool
    {
        if (!in_array($status, self::STATUSES, true)) {
            return false;
        }
        return self::updateFields($id, ['status' => $status]);
    }

    /**
     * Update an arbitrary subset of columns. Only whitelisted keys are written.
     */
    public static function updateFields(int $id, array $fields): bool
    {
        global $wpdb;

        $formats = [
            'status'       => '%s',
            'quote_amount' => '%f',
            'order_id'     => '%d',
            'confirmed_at' => '%s',
            'paid_at'      => '%s',
            'notes'        => '%s',
        ];

        $data = [];
        $data_format = [];
        foreach ($fields as $key => $value) {
            if (!isset($formats[$key])) {
                continue;
            }
            $data[$key] = $value;
            $data_format[] = $formats[$key];
        }

        if (empty($data)) {
            return false;
        }

        $updated = $wpdb->update(self::getTableName(), $data, ['id' => $id], $data_format, ['%d']);
        return $updated !== false;
    }

    public static function delete(int $id): bool
    {
        global $wpdb;
        $deleted = $wpdb->delete(
            self::getTableName(),
            ['id' => $id],
            ['%d']
        );
        return (bool) $deleted;
    }
}

