<?php
// AI-generated
namespace AiZippyChild;

defined('ABSPATH') || exit;

/**
 * Booking availability settings — the single source of truth for the
 * "Preferred Time" slot list, closed weekdays and holiday / blackout dates
 * used by the public enquiry form (events-form block) and validated again
 * server-side in EnquiryApi::handleEnquiry().
 *
 * Stored as one autoloaded option: ibat_booking_availability.
 */
class BookingAvailability
{
    public const OPTION = 'ibat_booking_availability';

    public static function register(): void
    {
        // Seed defaults once so the admin screen and form have data to render.
        add_action('after_setup_theme', static function (): void {
            if (get_option(self::OPTION) === false) {
                add_option(self::OPTION, self::defaults());
            }
        });
    }

    public static function defaults(): array
    {
        return [
            // capacity: 0 = unlimited (no slot check); >0 = max non-cancelled
            // bookings allowed per calendar date for that window.
            'time_slots' => [
                ['start' => '10:00', 'end' => '12:00', 'label' => '', 'capacity' => 0],
                ['start' => '11:00', 'end' => '13:00', 'label' => '', 'capacity' => 0],
                ['start' => '12:00', 'end' => '14:00', 'label' => '', 'capacity' => 0],
                ['start' => '13:00', 'end' => '15:00', 'label' => '', 'capacity' => 0],
                ['start' => '14:00', 'end' => '16:00', 'label' => '', 'capacity' => 0],
                ['start' => '15:00', 'end' => '17:00', 'label' => '', 'capacity' => 0],
            ],
            'closed_weekdays'  => [0], // 0 = Sunday … 6 = Saturday
            'blackout_dates'   => [],  // ['Y-m-d', …]
            'min_notice_days'  => 1,
            'max_advance_days' => 90,
            'weekday_overrides' => [],  // [0..6 => [slot,…], …] recurring per-weekday slots
            'date_overrides'   => [],  // ['Y-m-d' => [slot,…], …] per-date slot lists
        ];
    }

    /**
     * Full settings array, always shape-complete (stored value merged onto defaults).
     */
    public static function get(): array
    {
        $stored = get_option(self::OPTION);
        if (!is_array($stored)) {
            return self::defaults();
        }
        return array_merge(self::defaults(), $stored);
    }

    /**
     * Normalised slot list for rendering: each item has start, end, label.
     * The label is auto-formatted from start/end when the admin left it blank.
     */
    public static function getSlots(): array
    {
        return self::prepareSlots(self::normalizeSlots(self::get()['time_slots']));
    }

    /**
     * Sanitize a raw slot list (admin input) without auto-formatting labels, so
     * a blank label stays blank in storage and is formatted at read time.
     */
    public static function normalizeSlots($raw): array
    {
        $out = [];
        foreach ((array) $raw as $slot) {
            $start = self::sanitizeTime($slot['start'] ?? '');
            $end   = self::sanitizeTime($slot['end'] ?? '');
            if ($start === '' || $end === '') {
                continue;
            }
            $out[] = [
                'start'    => $start,
                'end'      => $end,
                'label'    => sanitize_text_field($slot['label'] ?? ''),
                'capacity' => max(0, (int) ($slot['capacity'] ?? 0)),
            ];
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

    /** Per-date slot overrides map: 'Y-m-d' => normalized slot list. */
    public static function getOverrides(): array
    {
        return (array) self::get()['date_overrides'];
    }

    /** Recurring per-weekday slot overrides map: 0..6 => normalized slot list. */
    public static function getWeekdayOverrides(): array
    {
        return (array) self::get()['weekday_overrides'];
    }

    /**
     * Effective slot list for a date, highest precedence first:
     * date-specific override → recurring weekday override → global default.
     */
    public static function getSlotsForDate(string $ymd): array
    {
        if ($ymd !== '') {
            $date_overrides = self::getOverrides();
            if (array_key_exists($ymd, $date_overrides)) {
                return self::prepareSlots((array) $date_overrides[$ymd]);
            }
            $ts = strtotime($ymd);
            if ($ts) {
                $weekday_overrides = self::getWeekdayOverrides();
                $weekday = (int) date('w', $ts);
                if (array_key_exists($weekday, $weekday_overrides)) {
                    return self::prepareSlots((array) $weekday_overrides[$weekday]);
                }
            }
        }
        return self::getSlots();
    }

    /**
     * Slot labels still bookable on a given date, honouring per-slot capacity.
     * Empty / invalid date → capacity is not checked (all labels returned).
     */
    public static function availableLabelsForDate(string $ymd): array
    {
        $slots = self::getSlotsForDate($ymd);
        if ($ymd === '' || !self::isDateOpen($ymd)) {
            // Closed date → no slots; open-but-unknown handled by caller.
            return $ymd === '' ? array_column($slots, 'label') : [];
        }

        $counts = BookingsDb::countsForDate($ymd);
        $out    = [];
        foreach ($slots as $slot) {
            $cap = $slot['capacity'];
            if ($cap === 0 || ($counts[$slot['label']] ?? 0) < $cap) {
                $out[] = $slot['label'];
            }
        }
        return $out;
    }

    /** Just the human labels — these are the <option> values stored in the DB. */
    public static function getSlotLabels(): array
    {
        return array_values(array_map(static fn($s) => $s['label'], self::getSlots()));
    }

    /**
     * Earliest / latest date the customer may pick, as Y-m-d (site timezone).
     */
    public static function datePickerBounds(): array
    {
        $today  = current_time('Y-m-d');
        $notice = max(0, (int) self::get()['min_notice_days']);
        $ahead  = max(1, (int) self::get()['max_advance_days']);

        return [
            'min' => date('Y-m-d', strtotime("$today +$notice days")),
            'max' => date('Y-m-d', strtotime("$today +$ahead days")),
        ];
    }

    /**
     * Config blob injected into the front-end form (window.__ibatAvailability).
     */
    public static function frontendConfig(string $date = ''): array
    {
        $settings = self::get();
        return [
            'slots'          => $date === '' ? self::getSlotLabels() : self::availableLabelsForDate($date),
            'allSlots'       => self::getSlotLabels(),
            'closedWeekdays' => array_values(array_map('intval', $settings['closed_weekdays'])),
            'blackoutDates'  => array_values($settings['blackout_dates']),
            'bounds'         => self::datePickerBounds(),
        ];
    }

    public static function isDateOpen(string $ymd): bool
    {
        $ts = strtotime($ymd);
        if (!$ts || date('Y-m-d', $ts) !== $ymd) {
            return false;
        }
        $settings = self::get();
        if (in_array((int) date('w', $ts), array_map('intval', $settings['closed_weekdays']), true)) {
            return false;
        }
        return !in_array($ymd, $settings['blackout_dates'], true);
    }

    /**
     * Re-validate a submission. Returns an error message, or null when valid.
     * Preferred date/time are both optional on the form, so empty values pass.
     */
    public static function validateSubmission(string $date, string $time): ?string
    {
        $effective_labels = $date !== ''
            ? array_column(self::getSlotsForDate($date), 'label')
            : self::getSlotLabels();
        if ($time !== '' && !in_array($time, $effective_labels, true)) {
            return 'The selected preferred time is no longer available. Please pick another slot.';
        }

        if ($date !== '') {
            $bounds = self::datePickerBounds();
            if ($date < $bounds['min'] || $date > $bounds['max']) {
                return 'Please choose a preferred date within the allowed booking window.';
            }
            if (!self::isDateOpen($date)) {
                return 'We are closed on the selected date. Please choose another day.';
            }
        }

        // Per-slot capacity — only enforceable when both date and time are given.
        if ($date !== '' && $time !== '') {
            foreach (self::getSlotsForDate($date) as $slot) {
                if ($slot['label'] === $time && $slot['capacity'] > 0) {
                    if (BookingsDb::countForSlot($date, $time) >= $slot['capacity']) {
                        return 'That time slot is fully booked for the selected date. Please choose another slot.';
                    }
                    break;
                }
            }
        }

        return null;
    }

    /**
     * Sanitize a raw settings payload (from the admin REST route) and persist it.
     * Returns the stored, shape-complete settings.
     */
    public static function save(array $raw): array
    {
        $slots = self::normalizeSlots($raw['time_slots'] ?? []);

        // Per-date overrides: list of { date, slots } → map keyed by Y-m-d.
        // An empty slot list is meaningful: it closes that specific date.
        $overrides = [];
        foreach ((array) ($raw['date_overrides'] ?? []) as $override) {
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
            $w = (int) ($override['weekday'] ?? -1);
            if ($w < 0 || $w > 6) {
                continue;
            }
            $weekday_overrides[$w] = self::normalizeSlots($override['slots'] ?? []);
        }
        ksort($weekday_overrides);

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
        ];

        update_option(self::OPTION, $settings);
        return $settings;
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
