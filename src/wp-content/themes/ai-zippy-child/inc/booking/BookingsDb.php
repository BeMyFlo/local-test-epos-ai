<?php
namespace AiZippyChild;

defined('ABSPATH') || exit;

/**
 * Data layer for the {prefix}aa_bookings table. Every method is scoped by
 * studio_id so one studio can never read or mutate another studio's rows.
 */
class BookingsDb
{
    public const TABLE       = 'aa_bookings';
    public const DB_VERSION  = '1';
    public const OPT_VERSION = 'aa_bookings_db_version';

    public const STATUSES = ['new', 'confirmed', 'paid', 'cancelled'];

    public static function register(): void
    {
        add_action('after_switch_theme', [self::class, 'ensureTable']);
        add_action('init',               [self::class, 'maybeUpgrade']);
    }

    public static function getTableName(): string
    {
        global $wpdb;
        return $wpdb->prefix . self::TABLE;
    }

    public static function maybeUpgrade(): void
    {
        if (get_option(self::OPT_VERSION) !== self::DB_VERSION) {
            self::ensureTable();
        }
    }

    public static function ensureTable(): void
    {
        global $wpdb;

        $table   = self::getTableName();
        $charset = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE {$table} (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            studio_id VARCHAR(50) NOT NULL,
            name VARCHAR(255) NOT NULL,
            email VARCHAR(255) NOT NULL,
            phone VARCHAR(100) NOT NULL,
            child_age VARCHAR(50) NULL DEFAULT '',
            preferred_contact VARCHAR(100) NULL DEFAULT '',
            programme VARCHAR(100) NULL DEFAULT '',
            slot_date DATE NULL DEFAULT NULL,
            slot_time VARCHAR(100) NULL DEFAULT '',
            details TEXT NULL,
            notes TEXT NULL,
            status VARCHAR(50) NOT NULL DEFAULT 'new',
            quoted_amount DECIMAL(10,2) NULL DEFAULT NULL,
            wc_order_id BIGINT(20) UNSIGNED NULL DEFAULT NULL,
            confirmed_at DATETIME NULL DEFAULT NULL,
            paid_at DATETIME NULL DEFAULT NULL,
            cancelled_at DATETIME NULL DEFAULT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY studio_status (studio_id, status),
            KEY studio_slot_date (studio_id, slot_date),
            KEY wc_order_id (wc_order_id),
            KEY created_at (created_at)
        ) {$charset};";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);

        update_option(self::OPT_VERSION, self::DB_VERSION, true);
    }

    public static function insert(array $data): int
    {
        global $wpdb;

        $row = self::sanitizeRow($data);

        $inserted = $wpdb->insert(
            self::getTableName(),
            $row,
            ['%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s']
        );

        return $inserted ? (int) $wpdb->insert_id : 0;
    }

    /**
     * Insert a booking while atomically holding its slot: a single guarded
     * INSERT ... SELECT that only lands a row when the live count of
     * non-cancelled bookings for the slot is still under capacity.
     * Returns the new booking id, or 0 when the guard rejected the insert
     * (slot filled concurrently) or on a DB error.
     */
    public static function insertWithSlotHold(array $data, int $capacity): int
    {
        global $wpdb;

        $row = self::sanitizeRow($data);

        // No slot to hold (unlimited capacity, or no date/time given).
        if ($capacity <= 0 || $row['slot_date'] === null || $row['slot_time'] === '') {
            return self::insert($data);
        }

        $table = self::getTableName();
        $sql = "INSERT INTO {$table}
                (studio_id, name, email, phone, child_age, preferred_contact, programme, slot_date, slot_time, details, status, created_at)
                SELECT %s,%s,%s,%s,%s,%s,%s,%s,%s,%s,'new',%s
                FROM (SELECT COUNT(*) AS held FROM {$table}
                      WHERE studio_id=%s AND slot_date=%s AND slot_time=%s AND status <> 'cancelled') AS t
                WHERE t.held < %d";

        $result = $wpdb->query($wpdb->prepare($sql,
            $row['studio_id'], $row['name'], $row['email'], $row['phone'],
            $row['child_age'], $row['preferred_contact'], $row['programme'],
            $row['slot_date'], $row['slot_time'], $row['details'], $row['created_at'],
            $row['studio_id'], $row['slot_date'], $row['slot_time'],
            $capacity
        ));

        if ($result === false) {
            error_log('[Achiever Art] Slot-hold insert failed: ' . $wpdb->last_error);
            return 0;
        }
        return (int) $wpdb->rows_affected === 1 ? (int) $wpdb->insert_id : 0;
    }

    private static function sanitizeRow(array $data): array
    {
        return [
            'studio_id'         => sanitize_text_field($data['studio_id'] ?? ''),
            'name'              => sanitize_text_field($data['name'] ?? ''),
            'email'             => sanitize_email($data['email'] ?? ''),
            'phone'             => sanitize_text_field($data['phone'] ?? ''),
            'child_age'         => sanitize_text_field($data['child_age'] ?? ''),
            'preferred_contact' => sanitize_text_field($data['preferred_contact'] ?? ''),
            'programme'         => sanitize_text_field($data['programme'] ?? ''),
            'slot_date'         => self::normalizeSlotDate($data['slot_date'] ?? ''),
            'slot_time'         => sanitize_text_field($data['slot_time'] ?? ''),
            'details'           => sanitize_textarea_field($data['details'] ?? ''),
            'status'            => 'new',
            'created_at'        => current_time('mysql'),
        ];
    }

    private static function normalizeSlotDate($value): ?string
    {
        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }
        $date = \DateTime::createFromFormat('Y-m-d', $value);
        if (!$date || $date->format('Y-m-d') !== $value) {
            return null;
        }
        return $value;
    }

    public static function getBookings(string $studio_id, string $search = '', string $status = '', string $programme = '', int $limit = 100, int $offset = 0): array
    {
        global $wpdb;
        $table = self::getTableName();

        $where  = ['studio_id = %s'];
        $params = [$studio_id];

        if ($search !== '') {
            $like = '%' . $wpdb->esc_like($search) . '%';
            $where[] = '(name LIKE %s OR email LIKE %s OR phone LIKE %s)';
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
        }

        if ($status !== '' && $status !== 'all') {
            $where[] = 'status = %s';
            $params[] = sanitize_text_field($status);
        }

        if ($programme !== '' && $programme !== 'all') {
            $where[] = 'programme = %s';
            $params[] = sanitize_text_field($programme);
        }

        $where_sql = implode(' AND ', $where);
        $query     = "SELECT * FROM $table WHERE $where_sql ORDER BY created_at DESC, id DESC LIMIT %d OFFSET %d";
        $params[]  = $limit;
        $params[]  = $offset;

        $prepared = $wpdb->prepare($query, $params);
        return $wpdb->get_results($prepared, ARRAY_A) ?: [];
    }

    public static function getById(string $studio_id, int $id): ?array
    {
        global $wpdb;
        $table = self::getTableName();
        $row = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM $table WHERE studio_id = %s AND id = %d", $studio_id, $id),
            ARRAY_A
        );
        return $row ?: null;
    }

    public static function getStats(string $studio_id): array
    {
        global $wpdb;
        $table = self::getTableName();

        $counts = [];
        foreach (['total' => '1=1', 'new' => "status = 'new'", 'confirmed' => "status = 'confirmed'", 'paid' => "status = 'paid'", 'cancelled' => "status = 'cancelled'"] as $key => $extra) {
            $counts[$key] = (int) $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM $table WHERE studio_id = %s AND $extra",
                $studio_id
            ));
        }
        return $counts;
    }

    public static function countForSlot(string $studio_id, string $date, string $time): int
    {
        global $wpdb;
        if ($studio_id === '' || $date === '' || $time === '') {
            return 0;
        }
        $table = self::getTableName();
        return (int) $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table WHERE studio_id = %s AND slot_date = %s AND slot_time = %s AND status != 'cancelled'",
            $studio_id,
            $date,
            $time
        ));
    }

    public static function countsForDate(string $studio_id, string $date): array
    {
        global $wpdb;
        if ($studio_id === '' || $date === '') {
            return [];
        }
        $table = self::getTableName();
        $rows  = $wpdb->get_results($wpdb->prepare(
            "SELECT slot_time AS t, COUNT(*) AS c FROM $table
             WHERE studio_id = %s AND slot_date = %s AND status != 'cancelled' AND slot_time != ''
             GROUP BY slot_time",
            $studio_id,
            $date
        ), ARRAY_A) ?: [];

        $out = [];
        foreach ($rows as $row) {
            $out[$row['t']] = (int) $row['c'];
        }
        return $out;
    }

    public static function updateStatus(string $studio_id, int $id, string $status): bool
    {
        if (!in_array($status, self::STATUSES, true)) {
            return false;
        }
        return self::updateFields($studio_id, $id, ['status' => $status]);
    }

    public static function updateFields(string $studio_id, int $id, array $fields): bool
    {
        global $wpdb;

        $formats = [
            'status'       => '%s',
            'quoted_amount' => '%f',
            'wc_order_id'  => '%d',
            'confirmed_at' => '%s',
            'paid_at'      => '%s',
            'cancelled_at' => '%s',
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

        $updated = $wpdb->update(
            self::getTableName(),
            $data,
            ['id' => $id, 'studio_id' => $studio_id],
            $data_format,
            ['%d', '%s']
        );
        return $updated !== false;
    }

    public static function delete(string $studio_id, int $id): bool
    {
        global $wpdb;
        $deleted = $wpdb->delete(
            self::getTableName(),
            ['id' => $id, 'studio_id' => $studio_id],
            ['%d', '%s']
        );
        return (bool) $deleted;
    }
}
