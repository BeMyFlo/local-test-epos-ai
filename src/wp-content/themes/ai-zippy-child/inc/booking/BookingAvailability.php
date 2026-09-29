<?php
namespace AiZippyChild;

defined('ABSPATH') || exit;

/**
 * Per-studio booking availability — the single source of truth for each
 * studio's slot list, closed weekdays, blackout dates, capacities and TTLs.
 * Used by the public enquiry form and re-validated server-side at submit.
 *
 * Stored as one option per studio: aa_booking_availability_{studio_slug}.
 * An unknown studio id resolves to empty settings, so a caller can never
 * read or write another studio's configuration.
 */
class BookingAvailability
{
    private const OPT_PREFIX = 'aa_booking_availability_';

    public static function register(): void
    {
        // Seed defaults once per studio so the admin screen and form have
        // data to render.
        add_action('after_setup_theme', static function (): void {
            foreach (BookingStudios::all() as $studio) {
                $name = self::optionName((string) ($studio['slug'] ?? ''));
                if ($name === '' || get_option($name) !== false) {
                    continue;
                }
                add_option($name, self::defaults(), '', false);
            }
        });
    }

    /** Option key for a studio, or '' when the studio id is unknown. */
    public static function optionName(string $studio_id): string
    {
        return BookingStudios::get($studio_id) !== null
            ? self::OPT_PREFIX . $studio_id
            : '';
    }

    public static function defaults(): array
    {
        return [
            // A slot may omit its own capacity — it then inherits
            // default_capacity. An explicit capacity of 0 = unlimited.
            'time_slots' => [
                ['start' => '10:00', 'end' => '12:00', 'label' => ''],
                ['start' => '11:00', 'end' => '13:00', 'label' => ''],
                ['start' => '12:00', 'end' => '14:00', 'label' => ''],
                ['start' => '13:00', 'end' => '15:00', 'label' => ''],
                ['start' => '14:00', 'end' => '16:00', 'label' => ''],
                ['start' => '15:00', 'end' => '17:00', 'label' => ''],
            ],
            'closed_weekdays'  => [0], // 0 = Sunday … 6 = Saturday
            'blackout_dates'   => [],  // ['Y-m-d', …]
            'min_notice_days'  => 1,
            'max_advance_days' => 90,
            'weekday_overrides' => [], // [0..6 => [slot,…], …] recurring per-weekday slots
            'date_overrides'   => [], // ['Y-m-d' => [slot,…], …] per-date slot lists
            'default_capacity' => 4,  // places per slot when the slot omits capacity
            'ttl_new_hours'    => 24, // hours a `new` booking holds its slot
            'ttl_confirmed_minutes' => 60, // minutes a `confirmed` booking holds its slot
            'class_overrides'  => [], // [programme => ['dates' => […], 'weekdays' => […]]]
        ];
    }

    /**
     * Full settings array for a studio, always shape-complete (stored value
     * merged onto defaults). Unknown studio → defaults with no time slots.
     */
    public static function get(string $studio_id): array
    {
        $name = self::optionName($studio_id);
        if ($name === '') {
            $settings = self::defaults();
            $settings['time_slots'] = [];
            return $settings;
        }
        $stored = get_option($name);
        if (!is_array($stored)) {
            return self::defaults();
        }
        return array_merge(self::defaults(), $stored);
    }

    /**
     * Normalised default slot list for rendering: each item has start, end,
     * label. The label is auto-formatted from start/end when left blank.
     */
    public static function getSlots(string $studio_id): array
    {
        return self::prepareSlots(self::normalizeSlots(self::get($studio_id)['time_slots']));
    }

    /**
     * Sanitize a raw slot list (admin input) without auto-formatting labels,
     * so a blank label stays blank in storage and is formatted at read time.
     */
    public static function normalizeSlots($raw): array
    {
        $out = [];
        foreach ((array) $raw as $slot) {
            if (!is_array($slot)) {
                continue;
            }
            $start = self::sanitizeTime($slot['start'] ?? '');
            $end   = self::sanitizeTime($slot['end'] ?? '');
            if ($start === '' || $end === '') {
                continue;
            }
            $entry = [
                'start' => $start,
                'end'   => $end,
                'label' => sanitize_text_field($slot['label'] ?? ''),
            ];
            if (array_key_exists('capacity', $slot)) {
                $entry['capacity'] = max(0, (int) $slot['capacity']);
            }
            $out[] = $entry;
        }
        return $out;
    }

    /** Auto-format blank labels from start/end at read time. */
    private static function prepareSlots(array $slots): array
    {
        foreach ($slots as &$slot) {
            if (trim((string) $slot['label']) === '') {
                $slot['label'] = self::formatRange($slot['start'], $slot['end']);
            }
        }
        return $slots;
    }

    /** Just the human labels — these are the values stored in aa_bookings.slot_time. */
    public static function getSlotLabels(string $studio_id): array
    {
        return array_values(array_map(static fn($s) => $s['label'], self::getSlots($studio_id)));
    }

    /**
     * Effective slot list for a studio + date + programme, highest precedence
     * first (REQUIREMENTS.md §3): class + specific date → class + weekday →
     * date override → weekday override → studio default time_slots.
     * An override with an empty slot list closes that date/weekday/class.
     */
    public static function getSlotsForDate(string $studio_id, string $ymd, string $programme = ''): array
    {
        $settings = self::get($studio_id);
        if ($ymd === '') {
            return self::getSlots($studio_id);
        }

        $ts      = strtotime($ymd);
        $weekday = $ts ? (int) date('w', $ts) : null;

        if ($programme !== '') {
            $class_overrides = (array) ($settings['class_overrides'] ?? []);
            $class = $class_overrides[$programme] ?? null;
            if (is_array($class)) {
                $dates = (array) ($class['dates'] ?? []);
                if (array_key_exists($ymd, $dates)) {
                    return self::prepareSlots((array) $dates[$ymd]);
                }
                if ($weekday !== null) {
                    $weekdays = (array) ($class['weekdays'] ?? []);
                    if (array_key_exists($weekday, $weekdays)) {
                        return self::prepareSlots((array) $weekdays[$weekday]);
                    }
                }
            }
        }

        $date_overrides = (array) ($settings['date_overrides'] ?? []);
        if (array_key_exists($ymd, $date_overrides)) {
            return self::prepareSlots((array) $date_overrides[$ymd]);
        }

        if ($weekday !== null) {
            $weekday_overrides = (array) ($settings['weekday_overrides'] ?? []);
            if (array_key_exists($weekday, $weekday_overrides)) {
                return self::prepareSlots((array) $weekday_overrides[$weekday]);
            }
        }

        return self::getSlots($studio_id);
    }

    /**
     * Slot labels still bookable on a given date, honouring per-slot capacity.
     * Empty / invalid date → capacity is not checked (all labels returned).
     */
    public static function availableLabelsForDate(string $studio_id, string $ymd, string $programme = ''): array
    {
        $slots = self::getSlotsForDate($studio_id, $ymd, $programme);
        if ($ymd === '' || !self::isDateOpen($studio_id, $ymd)) {
            // Closed date → no slots; open-but-unknown handled by caller.
            return $ymd === '' ? array_column($slots, 'label') : [];
        }

        $default_capacity = max(0, (int) self::get($studio_id)['default_capacity']);
        $counts = BookingsDb::countsForDate($studio_id, $ymd);
        $out    = [];
        foreach ($slots as $slot) {
            $capacity = self::slotCapacity($slot, $default_capacity);
            if ($capacity === 0 || ($counts[$slot['label']] ?? 0) < $capacity) {
                $out[] = $slot['label'];
            }
        }
        return $out;
    }

    /**
     * Earliest / latest date the customer may pick, as Y-m-d (site timezone).
     */
    public static function datePickerBounds(string $studio_id): array
    {
        $settings = self::get($studio_id);
        $today    = current_time('Y-m-d');
        $notice   = max(0, (int) $settings['min_notice_days']);
        $ahead    = max(1, (int) $settings['max_advance_days']);

        return [
            'min' => date('Y-m-d', strtotime("$today +$notice days")),
            'max' => date('Y-m-d', strtotime("$today +$ahead days")),
        ];
    }

    /**
     * Config blob injected into the front-end form. slotAvailability carries
     * per-slot remaining places as [label, remaining] pairs (null = unlimited
     * capacity, 0 = full) for the requested date — never any prices.
     */
    public static function frontendConfig(string $studio_id, string $programme = '', string $date = ''): array
    {
        $settings  = self::get($studio_id);
        $allLabels = self::getSlotLabels($studio_id);

        $availability = [];
        if ($date !== '' && self::isDateOpen($studio_id, $date)) {
            $default_capacity = max(0, (int) $settings['default_capacity']);
            $counts = BookingsDb::countsForDate($studio_id, $date);
            foreach (self::getSlotsForDate($studio_id, $date, $programme) as $slot) {
                $capacity = self::slotCapacity($slot, $default_capacity);
                $availability[] = [
                    $slot['label'],
                    $capacity === 0 ? null : max(0, $capacity - ($counts[$slot['label']] ?? 0)),
                ];
            }
        }

        return [
            'slots'            => $date === '' ? $allLabels : self::availableLabelsForDate($studio_id, $date, $programme),
            'allSlots'         => $allLabels,
            'closedWeekdays'   => array_values(array_map('intval', $settings['closed_weekdays'])),
            'blackoutDates'    => array_values($settings['blackout_dates']),
            'bounds'           => self::datePickerBounds($studio_id),
            'slotAvailability' => $availability,
        ];
    }

    public static function isDateOpen(string $studio_id, string $ymd): bool
    {
        $ts = strtotime($ymd);
        if (!$ts || date('Y-m-d', $ts) !== $ymd) {
            return false;
        }
        $settings = self::get($studio_id);
        if (in_array((int) date('w', $ts), array_map('intval', (array) $settings['closed_weekdays']), true)) {
            return false;
        }
        return !in_array($ymd, (array) $settings['blackout_dates'], true);
    }

    /**
     * TTL in seconds for a booking status, from the studio's settings —
     * `new` counts from created_at, `confirmed` from confirmed_at. Other
     * statuses have no TTL.
     */
    public static function ttlFor(string $studio_id, string $status): ?int
    {
        $settings = self::get($studio_id);
        if ($status === 'new') {
            return max(1, (int) $settings['ttl_new_hours']) * 3600;
        }
        if ($status === 'confirmed') {
            return max(1, (int) $settings['ttl_confirmed_minutes']) * 60;
        }
        return null;
    }

    /** Effective capacity for the labelled slot on a date (0 = unlimited). */
    public static function capacityForSlot(string $studio_id, string $ymd, string $programme, string $label): int
    {
        $default_capacity = max(0, (int) self::get($studio_id)['default_capacity']);
        foreach (self::getSlotsForDate($studio_id, $ymd, $programme) as $slot) {
            if ($slot['label'] === $label) {
                return self::slotCapacity($slot, $default_capacity);
            }
        }
        return 0;
    }

    /**
     * Re-validate a submission. Returns an error message, or null when valid.
     * Preferred date/time are both optional on the form, so empty values pass.
     */
    public static function validateSubmission(string $studio_id, string $programme, string $date, string $time): ?string
    {
        $effective_labels = $date !== ''
            ? array_column(self::getSlotsForDate($studio_id, $date, $programme), 'label')
            : self::getSlotLabels($studio_id);
        if ($time !== '' && !in_array($time, $effective_labels, true)) {
            return 'The selected preferred time is no longer available. Please pick another slot.';
        }

        if ($date !== '') {
            $bounds = self::datePickerBounds($studio_id);
            if ($date < $bounds['min'] || $date > $bounds['max']) {
                return 'Please choose a preferred date within the allowed booking window.';
            }
            if (!self::isDateOpen($studio_id, $date)) {
                return 'We are closed on the selected date. Please choose another day.';
            }
        }

        // Per-slot capacity — only enforceable when both date and time are given.
        if ($date !== '' && $time !== '') {
            $default_capacity = max(0, (int) self::get($studio_id)['default_capacity']);
            foreach (self::getSlotsForDate($studio_id, $date, $programme) as $slot) {
                if ($slot['label'] !== $time) {
                    continue;
                }
                $capacity = self::slotCapacity($slot, $default_capacity);
                if ($capacity > 0 && BookingsDb::countForSlot($studio_id, $date, $time) >= $capacity) {
                    return 'That time slot is fully booked for the selected date. Please choose another slot.';
                }
                break;
            }
        }

        return null;
    }

    /**
     * Sanitize a raw settings payload (from the admin REST route) and persist
     * it. Returns the stored, shape-complete settings.
     */
    public static function save(string $studio_id, array $raw): array
    {
        $slots = self::normalizeSlots($raw['time_slots'] ?? []);

        // Per-date overrides: list of { date, slots } → map keyed by Y-m-d.
        // An empty slot list is meaningful: it closes that specific date.
        $overrides = [];
        foreach ((array) ($raw['date_overrides'] ?? []) as $override) {
            if (!is_array($override)) {
                continue;
            }
            $d  = sanitize_text_field($override['date'] ?? '');
            $ts = strtotime($d);
            if (!$ts || date('Y-m-d', $ts) !== $d) {
                continue;
            }
            $overrides[$d] = self::normalizeSlots($override['slots'] ?? []);
        }
        ksort($overrides);

        // Recurring per-weekday overrides: list of { weekday, slots } → map 0..6.
        $weekday_overrides = [];
        foreach ((array) ($raw['weekday_overrides'] ?? []) as $override) {
            if (!is_array($override)) {
                continue;
            }
            $w = (int) ($override['weekday'] ?? -1);
            if ($w < 0 || $w > 6) {
                continue;
            }
            $weekday_overrides[$w] = self::normalizeSlots($override['slots'] ?? []);
        }
        ksort($weekday_overrides);

        // Class-binding overrides: list of { programme, date?|weekday?, slots }
        // → [programme => ['dates' => […], 'weekdays' => […]]]. An empty slot
        // list closes that programme on that date/weekday.
        $class_overrides = [];
        foreach ((array) ($raw['class_overrides'] ?? []) as $override) {
            if (!is_array($override)) {
                continue;
            }
            $programme = sanitize_text_field($override['programme'] ?? '');
            if ($programme === '') {
                continue;
            }
            $entry = self::normalizeSlots($override['slots'] ?? []);
            $d = sanitize_text_field($override['date'] ?? '');
            if ($d !== '') {
                $ts = strtotime($d);
                if ($ts && date('Y-m-d', $ts) === $d) {
                    $class_overrides[$programme]['dates'][$d] = $entry;
                }
                continue;
            }
            $w = (int) ($override['weekday'] ?? -1);
            if ($w >= 0 && $w <= 6) {
                $class_overrides[$programme]['weekdays'][$w] = $entry;
            }
        }
        foreach ($class_overrides as $programme => $maps) {
            $class_overrides[$programme] = [
                'dates'    => (array) ($maps['dates'] ?? []),
                'weekdays' => (array) ($maps['weekdays'] ?? []),
            ];
            ksort($class_overrides[$programme]['dates']);
            ksort($class_overrides[$programme]['weekdays']);
        }
        ksort($class_overrides);

        $weekdays = array_values(array_unique(array_filter(
            array_map('intval', (array) ($raw['closed_weekdays'] ?? [])),
            static fn($d) => $d >= 0 && $d <= 6
        )));
        sort($weekdays);

        $blackout = [];
        foreach ((array) ($raw['blackout_dates'] ?? []) as $d) {
            $d  = sanitize_text_field($d);
            $ts = strtotime($d);
            if ($ts && date('Y-m-d', $ts) === $d) {
                $blackout[$d] = true;
            }
        }
        $blackout = array_keys($blackout);
        sort($blackout);

        $settings = [
            'time_slots'       => $slots ?: self::defaults()['time_slots'],
            'closed_weekdays'  => $weekdays,
            'blackout_dates'   => $blackout,
            'min_notice_days'  => max(0, (int) ($raw['min_notice_days'] ?? 0)),
            'max_advance_days' => max(1, (int) ($raw['max_advance_days'] ?? 90)),
            'weekday_overrides' => $weekday_overrides,
            'date_overrides'   => $overrides,
            'default_capacity' => max(0, (int) ($raw['default_capacity'] ?? 4)),
            'ttl_new_hours'    => max(1, (int) ($raw['ttl_new_hours'] ?? 24)),
            'ttl_confirmed_minutes' => max(1, (int) ($raw['ttl_confirmed_minutes'] ?? 60)),
            'class_overrides'  => $class_overrides,
        ];

        update_option(self::optionName($studio_id), $settings, false);
        return $settings;
    }

    /** Effective capacity for a slot: explicit value, or the studio default. */
    private static function slotCapacity(array $slot, int $default_capacity): int
    {
        $capacity = array_key_exists('capacity', $slot)
            ? (int) $slot['capacity']
            : $default_capacity;
        return max(0, $capacity);
    }

    private static function sanitizeTime(string $value): string
    {
        $value = trim($value);
        return preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', $value) ? $value : '';
    }

    /** "10:00" + "12:00" → "10am – 12pm";  "13:30" → "1:30pm". */
    private static function formatRange(string $start, string $end): string
    {
        return self::formatTime($start) . ' – ' . self::formatTime($end);
    }

    private static function formatTime(string $hm): string
    {
        [$h, $m] = array_map('intval', explode(':', $hm));
        $suffix  = $h >= 12 ? 'pm' : 'am';
        $hour12  = $h % 12;
        if ($hour12 === 0) {
            $hour12 = 12;
        }
        return $m === 0 ? "{$hour12}{$suffix}" : sprintf('%d:%02d%s', $hour12, $m, $suffix);
    }
}
