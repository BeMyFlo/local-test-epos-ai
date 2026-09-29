<?php
namespace AiZippyChild;

defined('ABSPATH') || exit;

/**
 * Public booking REST API under achiever-art/v1.
 * Every route is studio-scoped: no query runs before a valid studio slug is
 * resolved, and the availability payload never contains prices.
 */
class EnquiryApi
{
    public const NAMESPACE = 'achiever-art/v1';

    private const RATE_LIMIT     = 5;
    private const RATE_WINDOW    = HOUR_IN_SECONDS;
    private const RATE_TRANSIENT = 'achiever_enquiry_';

    public static function register(): void
    {
        add_action('rest_api_init', [self::class, 'registerRoutes']);
    }

    public static function registerRoutes(): void
    {
        register_rest_route(self::NAMESPACE, '/booking/enquiry', [
            'methods'             => 'POST',
            'callback'            => [self::class, 'handleEnquiry'],
            'permission_callback' => '__return_true',
        ]);

        register_rest_route(self::NAMESPACE, '/booking/availability', [
            'methods'             => 'GET',
            'callback'            => [self::class, 'handleAvailability'],
            'permission_callback' => '__return_true',
        ]);
    }

    public static function handleEnquiry(\WP_REST_Request $request): \WP_REST_Response
    {
        $params = $request->get_json_params();
        if (empty($params)) {
            $params = $request->get_body_params();
        }

        $name              = sanitize_text_field($params['name'] ?? '');
        $phone             = sanitize_text_field($params['phone'] ?? '');
        $email             = sanitize_email($params['email'] ?? '');
        $children_age      = sanitize_text_field($params['children_age'] ?? '');
        $studio            = sanitize_text_field($params['studio'] ?? '');
        $programme_type    = sanitize_text_field($params['programme_type'] ?? '');
        $preferred_contact = sanitize_text_field($params['preferred_contact'] ?? '');
        $message           = sanitize_textarea_field($params['message'] ?? '');
        $preferred_date    = sanitize_text_field($params['preferred_date'] ?? '');
        $preferred_time    = sanitize_text_field($params['preferred_time'] ?? '');

        // Requireds (aggregate, like the existing form endpoint).
        $errors = [];
        if (empty($name)) {
            $errors[] = 'Name is required.';
        }
        if (empty($phone)) {
            $errors[] = 'Phone number is required.';
        }
        if (empty($email) || !is_email($email)) {
            $errors[] = 'A valid email address is required.';
        }
        if (empty($children_age)) {
            $errors[] = 'Children age is required.';
        }
        if (empty($studio)) {
            $errors[] = 'Please select a studio.';
        }
        if (empty($programme_type)) {
            $errors[] = 'Please select a programme type.';
        }
        if (!empty($errors)) {
            return new \WP_REST_Response([
                'success' => false,
                'message' => implode(' ', $errors),
            ], 400);
        }

        // Resolve studio display name -> slug; reject unknown studios before
        // any query runs.
        $studio_id = BookingStudios::slugForName($studio);
        if ($studio_id === '' || BookingStudios::get($studio_id) === null) {
            return new \WP_REST_Response([
                'success' => false,
                'message' => 'Please select a valid studio.',
            ], 400);
        }

        if (!in_array($programme_type, BookingStudios::programmes(), true)) {
            return new \WP_REST_Response([
                'success' => false,
                'message' => 'Please select a valid programme type.',
            ], 400);
        }

        // Rate limit: max 5 submissions per IP per hour (shared key with the
        // existing /enquiry endpoint).
        $ip            = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $transient_key = self::RATE_TRANSIENT . md5($ip);
        $submissions   = (int) get_transient($transient_key);

        if ($submissions >= self::RATE_LIMIT) {
            return new \WP_REST_Response([
                'success' => false,
                'message' => 'Too many submissions. Please try again later.',
            ], 429);
        }

        set_transient($transient_key, $submissions + 1, self::RATE_WINDOW);

        // Availability re-validation server-side.
        $availability_error = BookingAvailability::validateSubmission($studio_id, $programme_type, $preferred_date, $preferred_time);
        if ($availability_error !== null) {
            return new \WP_REST_Response([
                'success' => false,
                'message' => $availability_error,
            ], 400);
        }

        // Atomic check + insert so the slot is held from the moment of
        // creation (status 'new').
        $capacity   = BookingAvailability::capacityForSlot($studio_id, $preferred_date, $programme_type, $preferred_time);
        $booking_id = BookingsDb::insertWithSlotHold([
            'studio_id'         => $studio_id,
            'name'              => $name,
            'email'             => $email,
            'phone'             => $phone,
            'child_age'         => $children_age,
            'preferred_contact' => $preferred_contact,
            'programme'         => $programme_type,
            'slot_date'         => $preferred_date,
            'slot_time'         => $preferred_time,
            'details'           => $message,
        ], $capacity);

        if ($booking_id === 0) {
            return new \WP_REST_Response([
                'success' => false,
                'message' => 'That time slot is fully booked for the selected date. Please choose another slot.',
            ], 400);
        }

        // Emails.
        $studio_name = (string) (BookingStudios::get($studio_id)['name'] ?? $studio_id);
        $mail_sent   = self::sendStaffNotification($booking_id, $studio_name, $name, $phone, $email, $children_age, $programme_type, $preferred_contact, $preferred_date, $preferred_time, $message);
        self::sendCustomerAcknowledgement($studio_name, $name, $email, $phone, $children_age, $programme_type, $preferred_date, $preferred_time, $message);

        return new \WP_REST_Response([
            'success'   => true,
            'message'   => 'Thank you for your enquiry! We will get back to you soon.',
            'bookingId' => $booking_id,
            'mailSent'  => $mail_sent,
        ], 200);
    }

    private static function sendStaffNotification(
        int $booking_id,
        string $studio_name,
        string $name,
        string $phone,
        string $email,
        string $children_age,
        string $programme,
        string $preferred_contact,
        string $preferred_date,
        string $preferred_time,
        string $message
    ): bool {
        $subject = sprintf('[Achiever\'s Art] New Booking Enquiry from %s', $name);

        $body  = "NEW BOOKING ENQUIRY DETAILS:\n";
        $body .= "=================================\n";
        $body .= "Booking ID: #" . $booking_id . "\n";
        $body .= "Name: $name\n";
        $body .= "Phone: $phone\n";
        $body .= "Email: $email\n";
        $body .= "Children Age: " . ($children_age ?: 'N/A') . "\n";
        $body .= "Studio: $studio_name\n";
        $body .= "Programme Type: $programme\n";
        $body .= "Preferred Contact: " . ($preferred_contact ?: 'N/A') . "\n";
        $body .= "Preferred Date: " . ($preferred_date ?: 'Not specified') . "\n";
        $body .= "Preferred Time: " . ($preferred_time ?: 'Not specified') . "\n";
        $body .= "\n";
        $body .= "MESSAGE / ADDITIONAL DETAILS:\n";
        $body .= ($message ?: 'None') . "\n";
        $body .= "=================================\n";
        $body .= "Submitted via Achiever Art Website Booking Form\n";

        $headers = [
            'Content-Type: text/plain; charset=UTF-8',
            sprintf('Reply-To: %s <%s>', $name, $email),
        ];

        $sent = wp_mail(get_option('admin_email'), $subject, $body, $headers);
        if (!$sent) {
            error_log('[Achiever Art] Failed to send booking enquiry email from: ' . $email);
        }
        return $sent;
    }

    private static function sendCustomerAcknowledgement(
        string $studio_name,
        string $name,
        string $email,
        string $phone,
        string $children_age,
        string $programme,
        string $preferred_date,
        string $preferred_time,
        string $message
    ): void {
        if (empty($email) || !is_email($email)) {
            return;
        }

        $subject = 'We have received your enquiry - Achiever\'s Art';

        $body  = "Hi $name,\n\n";
        $body .= "Thank you for reaching out to Achiever Art! We have received your booking enquiry for $studio_name and our team will get back to you soon.\n\n";
        $body .= "YOUR ENQUIRY DETAILS:\n";
        $body .= "------------------------------\n";
        $body .= "Name: $name\n";
        $body .= "Phone: $phone\n";
        $body .= "Children Age: " . ($children_age ?: 'N/A') . "\n";
        $body .= "Studio: $studio_name\n";
        $body .= "Programme Type: $programme\n";
        $body .= "Preferred Date: " . ($preferred_date ?: 'N/A') . "\n";
        $body .= "Preferred Time: " . ($preferred_time ?: 'N/A') . "\n";
        if ($message !== '') {
            $body .= "\nYour message:\n$message\n";
        }
        $body .= "\n";
        $body .= "Best regards,\nAchiever Art Team";

        wp_mail($email, $subject, $body, ['Content-Type: text/plain; charset=UTF-8']);
    }

    public static function handleAvailability(\WP_REST_Request $request): \WP_REST_Response
    {
        $studio = sanitize_text_field((string) $request->get_param('studio'));
        if ($studio === '') {
            return new \WP_REST_Response([
                'success' => false,
                'message' => 'Please select a studio.',
            ], 400);
        }

        $studio_id = BookingStudios::slugForName($studio);
        if ($studio_id === '' || BookingStudios::get($studio_id) === null) {
            return new \WP_REST_Response([
                'success' => false,
                'message' => 'Unknown studio.',
            ], 400);
        }

        $programme = sanitize_text_field((string) $request->get_param('programme'));

        $date = sanitize_text_field((string) $request->get_param('date'));
        if ($date !== '' && !self::isValidDate($date)) {
            return new \WP_REST_Response([
                'success' => false,
                'message' => 'Invalid date.',
            ], 400);
        }

        $month = sanitize_text_field((string) $request->get_param('month'));
        if ($month !== '' && !preg_match('/^\d{4}-\d{2}$/', $month)) {
            return new \WP_REST_Response([
                'success' => false,
                'message' => 'Invalid month.',
            ], 400);
        }

        // Studio-scoped from here on; payload contains no prices.
        $config = BookingAvailability::frontendConfig($studio_id, $programme, $date);
        $config['month'] = $month;

        return new \WP_REST_Response($config, 200);
    }

    private static function isValidDate(string $value): bool
    {
        $date = \DateTime::createFromFormat('Y-m-d', $value);
        return $date && $date->format('Y-m-d') === $value;
    }
}
