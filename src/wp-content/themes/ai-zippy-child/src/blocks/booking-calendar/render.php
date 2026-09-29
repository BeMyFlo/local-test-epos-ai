<?php
/**
 * Server-side render for the Booking Calendar block.
 *
 * Studio select first, then programme select; when both are preselected the
 * current month grid is rendered server-side with live remaining places, and a
 * JSON config blob lets view.js navigate months, expand days and submit
 * enquiries against the public booking REST API. Prices are never rendered.
 *
 * @var array $attributes Block attributes.
 */

defined('ABSPATH') || exit;

use AiZippyChild\BookingAvailability;
use AiZippyChild\BookingStudios;
use AiZippyChild\BookingsDb;
use AiZippyChild\EnquiryApi;

$heading           = $attributes['heading'] ?? 'BOOK YOUR CLASS';
$subheading        = $attributes['subheading'] ?? 'Pick a studio and programme to see live availability';
$studio_label      = $attributes['studioLabel'] ?? 'Select Studio';
$programme_label   = $attributes['programmeLabel'] ?? 'Select Programme';
$enroll_text       = $attributes['enrollText'] ?? 'ENROLL NOW';
$full_text         = $attributes['fullText'] ?? 'FULL';
$remaining_text    = $attributes['remainingText'] ?? '%d places left';
$unlimited_text    = $attributes['unlimitedText'] ?? 'Open';
$submit_text       = $attributes['submitText'] ?? 'SEND ENQUIRY';
$success_message   = $attributes['successMessage'] ?? "Thanks! We'll get back to you shortly.";
$contact_options   = $attributes['preferredContactOptions'] ?? ['WhatsApp', 'Email', 'Phone'];
$selected_studio   = (string) ($attributes['selectedStudio'] ?? '');
$selected_programme = (string) ($attributes['selectedProgramme'] ?? '');

$default_labels = [
    'name'             => 'Name',
    'phone'            => 'Phone Number',
    'email'            => 'Email',
    'childAge'         => "Child's Age",
    'preferredContact' => 'Preferred Mode of Contact',
    'message'          => 'Message',
    'date'             => 'Date',
    'time'             => 'Time',
    'selectedSlot'     => 'Your Selection',
];
$labels = array_merge($default_labels, is_array($attributes['labels'] ?? null) ? $attributes['labels'] : []);

$registry_ready = class_exists(BookingStudios::class);
$studios        = $registry_ready ? BookingStudios::all() : [];
$programmes     = $registry_ready ? BookingStudios::programmes() : [];

$studio_entry   = ($registry_ready && $selected_studio !== '') ? BookingStudios::get($selected_studio) : null;
$studio_id      = $studio_entry !== null ? (string) $studio_entry['slug'] : '';
$programme_ok   = in_array($selected_programme, $programmes, true);

// SSR month: same math as BookingAvailability::frontendConfig() — remaining
// places per slot, never a price.
$month_data = null;
if ($studio_entry !== null && $programme_ok && class_exists(BookingAvailability::class) && class_exists(BookingsDb::class)) {
    // On-render expiry: release this studio's just-expired holds before the
    // grid below counts places — between 5-minute cron ticks a lapsed hold
    // must not render as taken.
    BookingsDb::cancelExpired([$studio_id]);

    $settings         = BookingAvailability::get($studio_id);
    $bounds           = BookingAvailability::datePickerBounds($studio_id);
    $default_capacity = max(0, (int) $settings['default_capacity']);
    $ym               = current_time('Y-m');
    $first            = strtotime($ym . '-01');
    $days_in_month    = (int) date('t', $first);

    $days = [];
    for ($d = 1; $d <= $days_in_month; $d++) {
        $ymd  = sprintf('%s-%02d', $ym, $d);
        $open = $ymd >= $bounds['min'] && $ymd <= $bounds['max'] && BookingAvailability::isDateOpen($studio_id, $ymd);

        $slots = [];
        if ($open) {
            $counts = BookingsDb::countsForDate($studio_id, $ymd);
            foreach (BookingAvailability::getSlotsForDate($studio_id, $ymd, $selected_programme) as $slot) {
                $capacity = max(0, array_key_exists('capacity', $slot) ? (int) $slot['capacity'] : $default_capacity);
                $slots[]  = [
                    $slot['label'],
                    $capacity === 0 ? null : max(0, $capacity - ($counts[$slot['label']] ?? 0)),
                ];
            }
        }
        $days[$ymd] = ['open' => $open, 'slots' => $slots];
    }

    $month_data = [
        'ym'             => $ym,
        'bounds'         => $bounds,
        'closedWeekdays' => array_values(array_map('intval', (array) $settings['closed_weekdays'])),
        'blackoutDates'  => array_values((array) $settings['blackout_dates']),
        'days'           => $days,
    ];
}

$day_slots_text = '%d slots';
$no_slots_text  = 'No classes on this date.';

$config = [
    'restUrl'     => esc_url_raw(rest_url(class_exists(EnquiryApi::class) ? EnquiryApi::NAMESPACE : 'achiever-art/v1')),
    'studios'     => array_map(static fn(array $studio): array => [
        'name' => (string) $studio['name'],
        'slug' => (string) $studio['slug'],
    ], $studios),
    'programmes'  => $programmes,
    'labels'      => $labels,
    'i18n'        => [
        'enrollText'     => $enroll_text,
        'fullText'       => $full_text,
        'remainingText'  => $remaining_text,
        'unlimitedText'  => $unlimited_text,
        'daySlotsText'   => $day_slots_text,
        'noSlotsText'    => $no_slots_text,
        'hint'           => $subheading,
    ],
    'successMessage'    => $success_message,
    'selectedStudio'    => $studio_id,
    'selectedProgramme' => $programme_ok ? $selected_programme : '',
];
if ($month_data !== null) {
    $config['month'] = $month_data;
}

$instance_id = wp_unique_id('achiever-booking-calendar-');
$status_id   = $instance_id . '-status';
$month_label = $month_data !== null ? date('F Y', strtotime($month_data['ym'] . '-01')) : '';

$weekdays = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
$wrapper_attributes = get_block_wrapper_attributes(['class' => 'achiever-booking-calendar']);
?>
<div <?php echo $wrapper_attributes; ?> data-booking-calendar>
  <div class="achiever-booking-calendar__inner">

    <h2 class="achiever-booking-calendar__heading"><?php echo esc_html($heading); ?></h2>
    <p class="achiever-booking-calendar__subheading"><?php echo esc_html($subheading); ?></p>

    <div class="achiever-booking-calendar__selects">
      <label class="achiever-booking-calendar__field">
        <span class="achiever-booking-calendar__label"><?php echo esc_html($studio_label); ?></span>
        <select class="achiever-booking-calendar__select" data-booking-calendar-studio>
          <option value=""><?php echo esc_html($studio_label); ?></option>
          <?php foreach ($studios as $studio) : ?>
            <option value="<?php echo esc_attr($studio['slug']); ?>"<?php selected($studio_id, $studio['slug']); ?>><?php echo esc_html($studio['name']); ?></option>
          <?php endforeach; ?>
        </select>
      </label>
      <label class="achiever-booking-calendar__field">
        <span class="achiever-booking-calendar__label"><?php echo esc_html($programme_label); ?></span>
        <select class="achiever-booking-calendar__select" data-booking-calendar-programme<?php echo $studio_entry !== null ? '' : ' disabled'; ?>>
          <option value=""><?php echo esc_html($programme_label); ?></option>
          <?php foreach ($programmes as $programme) : ?>
            <option value="<?php echo esc_attr($programme); ?>"<?php selected($programme_ok ? $selected_programme : '', $programme); ?>><?php echo esc_html($programme); ?></option>
          <?php endforeach; ?>
        </select>
      </label>
    </div>

    <div class="achiever-booking-calendar__body" data-booking-calendar-body>
      <?php if ($month_data !== null) : ?>
        <div class="achiever-booking-calendar__calendar">
          <div class="achiever-booking-calendar__nav">
            <button type="button" class="achiever-booking-calendar__nav-btn" data-booking-calendar-prev aria-label="Previous month">&lsaquo;</button>
            <span class="achiever-booking-calendar__month-label"><?php echo esc_html($month_label); ?></span>
            <button type="button" class="achiever-booking-calendar__nav-btn" data-booking-calendar-next aria-label="Next month">&rsaquo;</button>
          </div>
          <div class="achiever-booking-calendar__grid">
            <?php foreach ($weekdays as $weekday) : ?>
              <span class="achiever-booking-calendar__weekday"><?php echo esc_html($weekday); ?></span>
            <?php endforeach; ?>
            <?php for ($i = 0, $offset = (int) date('w', strtotime($month_data['ym'] . '-01')); $i < $offset; $i++) : ?>
              <span class="achiever-booking-calendar__day achiever-booking-calendar__day--pad" aria-hidden="true"></span>
            <?php endfor; ?>
            <?php foreach ($month_data['days'] as $ymd => $day) : ?>
              <?php
              $out      = $ymd < $month_data['bounds']['min'] || $ymd > $month_data['bounds']['max'];
              $day_num  = (int) substr($ymd, 8, 2);
              $bookable = 0;
              foreach ($day['slots'] as $slot) {
                  if ($slot[1] === null || $slot[1] > 0) {
                      $bookable++;
                  }
              }
              ?>
              <?php if (!$day['open']) : ?>
                <span class="achiever-booking-calendar__day achiever-booking-calendar__day--<?php echo $out ? 'out' : 'closed'; ?>" aria-disabled="true">
                  <span class="achiever-booking-calendar__day-num"><?php echo esc_html((string) $day_num); ?></span>
                </span>
              <?php elseif (empty($day['slots'])) : ?>
                <button type="button" class="achiever-booking-calendar__day" data-date="<?php echo esc_attr($ymd); ?>" aria-expanded="false">
                  <span class="achiever-booking-calendar__day-num"><?php echo esc_html((string) $day_num); ?></span>
                  <span class="achiever-booking-calendar__day-meta"><?php echo esc_html($no_slots_text); ?></span>
                </button>
              <?php else : ?>
                <button type="button" class="achiever-booking-calendar__day<?php echo $bookable === 0 ? ' achiever-booking-calendar__day--full' : ''; ?>" data-date="<?php echo esc_attr($ymd); ?>" aria-expanded="false">
                  <span class="achiever-booking-calendar__day-num"><?php echo esc_html((string) $day_num); ?></span>
                  <span class="achiever-booking-calendar__day-meta"><?php echo esc_html($bookable === 0 ? $full_text : sprintf($day_slots_text, $bookable)); ?></span>
                </button>
              <?php endif; ?>
            <?php endforeach; ?>
          </div>
          <div class="achiever-booking-calendar__slots" data-booking-calendar-slots hidden></div>
        </div>
      <?php else : ?>
        <p class="achiever-booking-calendar__hint"><?php echo esc_html($subheading); ?></p>
      <?php endif; ?>
    </div>

    <form class="achiever-booking-calendar__panel" data-booking-calendar-panel hidden method="post" aria-describedby="<?php echo esc_attr($status_id); ?>">
      <h3 class="achiever-booking-calendar__panel-title"><?php echo esc_html($labels['selectedSlot']); ?></h3>

      <dl class="achiever-booking-calendar__summary">
        <div class="achiever-booking-calendar__summary-row">
          <dt>Studio</dt>
          <dd data-booking-calendar-summary-studio><?php echo esc_html($studio_entry['name'] ?? ''); ?></dd>
        </div>
        <div class="achiever-booking-calendar__summary-row">
          <dt>Programme</dt>
          <dd data-booking-calendar-summary-programme><?php echo esc_html($programme_ok ? $selected_programme : ''); ?></dd>
        </div>
        <div class="achiever-booking-calendar__summary-row">
          <dt><?php echo esc_html($labels['date']); ?></dt>
          <dd data-booking-calendar-summary-date></dd>
        </div>
        <div class="achiever-booking-calendar__summary-row">
          <dt><?php echo esc_html($labels['time']); ?></dt>
          <dd data-booking-calendar-summary-time></dd>
        </div>
      </dl>

      <input type="hidden" name="studio" value="<?php echo esc_attr($studio_entry['name'] ?? ''); ?>" data-booking-calendar-field-studio />
      <input type="hidden" name="programme_type" value="<?php echo esc_attr($programme_ok ? $selected_programme : ''); ?>" data-booking-calendar-field-programme />
      <input type="hidden" name="preferred_date" value="" data-booking-calendar-field-date />
      <input type="hidden" name="preferred_time" value="" data-booking-calendar-field-time />

      <div class="achiever-booking-calendar__row">
        <input type="text" name="name" placeholder="<?php echo esc_attr($labels['name']); ?>" autocomplete="name" required />
        <input type="tel" name="phone" placeholder="<?php echo esc_attr($labels['phone']); ?>" autocomplete="tel" required />
      </div>
      <div class="achiever-booking-calendar__row">
        <input type="email" name="email" placeholder="<?php echo esc_attr($labels['email']); ?>" autocomplete="email" required />
        <input type="text" name="children_age" placeholder="<?php echo esc_attr($labels['childAge']); ?>" required />
      </div>
      <select name="preferred_contact" data-booking-calendar-contact>
        <option value=""><?php echo esc_html($labels['preferredContact']); ?></option>
        <?php foreach ((array) $contact_options as $option) : ?>
          <option value="<?php echo esc_attr($option); ?>"><?php echo esc_html($option); ?></option>
        <?php endforeach; ?>
      </select>
      <textarea name="message" rows="4" placeholder="<?php echo esc_attr($labels['message']); ?>"></textarea>

      <div class="achiever-booking-calendar__submit-wrap">
        <button type="submit" class="achiever-btn achiever-btn--primary"><?php echo esc_html($submit_text); ?></button>
      </div>

      <p id="<?php echo esc_attr($status_id); ?>" class="achiever-booking-calendar__status" role="status" aria-live="polite"></p>
    </form>

  </div>

  <script type="application/json" class="achiever-booking-calendar__config"><?php echo wp_json_encode($config, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?></script>
</div>
