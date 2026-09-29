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

        // ---- Admin REST (Bookings admin screen). All routes require
        // manage_options and a valid `studio` parameter; no query runs before
        // the studio guard resolves.
        register_rest_route(self::NAMESPACE, '/bookings', [
            'methods'             => 'GET',
            'callback'            => [self::class, 'getBookings'],
            'permission_callback' => [self::class, 'adminPermission'],
        ]);

        register_rest_route(self::NAMESPACE, '/bookings/status', [
            'methods'             => 'POST',
            'callback'            => [self::class, 'updateStatus'],
            'permission_callback' => [self::class, 'adminPermission'],
        ]);

        register_rest_route(self::NAMESPACE, '/bookings/notes', [
            'methods'             => 'POST',
            'callback'            => [self::class, 'saveNotes'],
            'permission_callback' => [self::class, 'adminPermission'],
        ]);

        register_rest_route(self::NAMESPACE, '/bookings/confirm', [
            'methods'             => 'POST',
            'callback'            => [self::class, 'confirmBooking'],
            'permission_callback' => [self::class, 'adminPermission'],
        ]);

        register_rest_route(self::NAMESPACE, '/bookings/resend', [
            'methods'             => 'POST',
            'callback'            => [self::class, 'resendPaymentLink'],
            'permission_callback' => [self::class, 'adminPermission'],
        ]);

        register_rest_route(self::NAMESPACE, '/bookings/(?P<id>\d+)', [
            'methods'             => 'DELETE',
            'callback'            => [self::class, 'deleteBooking'],
            'permission_callback' => [self::class, 'adminPermission'],
        ]);

        register_rest_route(self::NAMESPACE, '/bookings/availability', [
            [
                'methods'             => 'GET',
                'callback'            => [self::class, 'getAdminAvailability'],
                'permission_callback' => [self::class, 'adminPermission'],
            ],
            [
                'methods'             => 'POST',
                'callback'            => [self::class, 'saveAvailability'],
                'permission_callback' => [self::class, 'adminPermission'],
            ],
        ]);

        register_rest_route(self::NAMESPACE, '/bookings/settings', [
            'methods'             => 'POST',
            'callback'            => [self::class, 'saveSettings'],
            'permission_callback' => [self::class, 'adminPermission'],
        ]);
    }

    public static function adminPermission(): bool
    {
        return current_user_can('manage_options');
    }

    /** Valid studio slug from the request, or '' when missing/unknown. */
    private static function studioParam(\WP_REST_Request $request): string
    {
        $studio = sanitize_text_field((string) $request->get_param('studio'));
        return $studio !== '' && BookingStudios::get($studio) !== null ? $studio : '';
    }

    private static function error(string $message, int $status = 400): \WP_REST_Response
    {
        return new \WP_REST_Response(['success' => false, 'message' => $message], $status);
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
        $mail_sent   = self::sendStaffNotification($studio_id, $booking_id, $studio_name, $name, $phone, $email, $children_age, $programme_type, $preferred_contact, $preferred_date, $preferred_time, $message);
        self::sendCustomerAcknowledgement($studio_name, $name, $email, $phone, $children_age, $programme_type, $preferred_date, $preferred_time, $message);

        return new \WP_REST_Response([
            'success'   => true,
            'message'   => 'Thank you for your enquiry! We will get back to you soon.',
            'bookingId' => $booking_id,
            'mailSent'  => $mail_sent,
        ], 200);
    }

    private static function sendStaffNotification(
        string $studio_id,
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

        // Per-studio notification inbox, falling back to the site admin email.
        $notify = (string) (BookingAvailability::get($studio_id)['notification_email'] ?? '');
        $to     = is_email($notify) ? $notify : get_option('admin_email');

        $sent = wp_mail($to, $subject, $body, $headers);
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

    // ---- Admin handlers -------------------------------------------------

    public static function getBookings(\WP_REST_Request $request): \WP_REST_Response
    {
        $studio_id = self::studioParam($request);
        if ($studio_id === '') {
            return self::error('Unknown studio.');
        }

        $search    = sanitize_text_field((string) ($request->get_param('search') ?? ''));
        $status    = sanitize_text_field((string) ($request->get_param('status') ?? 'all'));
        $programme = sanitize_text_field((string) ($request->get_param('programme') ?? 'all'));

        $items = BookingsDb::getBookings($studio_id, $search, $status, $programme);
        foreach ($items as &$item) {
            $item['order'] = !empty($item['wc_order_id'])
                ? self::orderInfo((int) $item['wc_order_id'])
                : null;
        }
        unset($item);

        return new \WP_REST_Response([
            'success'  => true,
            'bookings' => $items,
            'stats'    => BookingsDb::getStats($studio_id),
        ], 200);
    }

    /** Compact order summary for the admin table / modal. */
    private static function orderInfo(int $order_id): ?array
    {
        if (!function_exists('wc_get_order')) {
            return null;
        }
        $order = wc_get_order($order_id);
        if (!$order) {
            return null;
        }

        return [
            'id'          => $order_id,
            'number'      => $order->get_order_number(),
            'status'      => $order->get_status(),
            'status_name' => wc_get_order_status_name($order->get_status()),
            'total'       => (float) $order->get_total(),
            'total_html'  => html_entity_decode(wp_strip_all_tags(wc_price($order->get_total(), ['currency' => $order->get_currency()])), ENT_QUOTES, 'UTF-8'),
            'currency'    => $order->get_currency(),
            'is_paid'     => $order->is_paid(),
            'pay_url'     => $order->needs_payment() ? $order->get_checkout_payment_url() : '',
            'edit_url'    => $order->get_edit_order_url(),
        ];
    }

    public static function updateStatus(\WP_REST_Request $request): \WP_REST_Response
    {
        $studio_id = self::studioParam($request);
        if ($studio_id === '') {
            return self::error('Unknown studio.');
        }

        $params = $request->get_json_params();
        if (empty($params)) {
            $params = $request->get_body_params();
        }
        $id     = (int) ($params['id'] ?? 0);
        $status = sanitize_text_field((string) ($params['status'] ?? ''));

        $booking = $id ? BookingsDb::getById($studio_id, $id) : null;
        if (!$booking) {
            return self::error('Booking not found.', 404);
        }
        if (!in_array($status, BookingsDb::STATUSES, true)) {
            return self::error('Invalid status.');
        }

        $previous = (string) $booking['status'];
        $fields   = ['status' => $status];
        $now      = current_time('mysql');
        if ($status === 'cancelled' && $previous !== 'cancelled' && empty($booking['cancelled_at'])) {
            $fields['cancelled_at'] = $now;
        }
        if ($status === 'paid' && empty($booking['paid_at'])) {
            $fields['paid_at'] = $now;
        }
        if ($status === 'confirmed' && empty($booking['confirmed_at'])) {
            $fields['confirmed_at'] = $now;
        }

        $ok = BookingsDb::updateStatus($studio_id, $id, $status);
        if (!$ok) {
            return self::error('Could not update the booking status.');
        }
        if (!empty($fields['cancelled_at']) || !empty($fields['paid_at']) || !empty($fields['confirmed_at'])) {
            BookingsDb::updateFields($studio_id, $id, $fields);
        }

        // Let the customer know when staff cancel their booking — only on the
        // transition into cancelled, never on repeats. Slot release is implicit:
        // counts exclude cancelled rows.
        if ($status === 'cancelled' && $previous !== 'cancelled') {
            self::sendCancellationEmail($booking);
        }

        return new \WP_REST_Response(['success' => true], 200);
    }

    /** Save the admin-only internal note (never sent to the customer). */
    public static function saveNotes(\WP_REST_Request $request): \WP_REST_Response
    {
        $studio_id = self::studioParam($request);
        if ($studio_id === '') {
            return self::error('Unknown studio.');
        }

        $params = $request->get_json_params();
        if (empty($params)) {
            $params = $request->get_body_params();
        }
        $id    = (int) ($params['id'] ?? 0);
        $notes = sanitize_textarea_field((string) ($params['notes'] ?? ''));

        if (!$id || !BookingsDb::getById($studio_id, $id)) {
            return self::error('Booking not found.', 404);
        }

        $ok = BookingsDb::updateFields($studio_id, $id, ['notes' => $notes]);

        return new \WP_REST_Response(['success' => $ok, 'notes' => $notes], $ok ? 200 : 400);
    }

    public static function confirmBooking(\WP_REST_Request $request): \WP_REST_Response
    {
        $studio_id = self::studioParam($request);
        if ($studio_id === '') {
            return self::error('Unknown studio.');
        }

        $params = $request->get_json_params();
        if (empty($params)) {
            $params = $request->get_body_params();
        }
        $id     = (int) ($params['id'] ?? 0);
        $amount = (float) ($params['amount'] ?? 0);
        $note   = sanitize_textarea_field((string) ($params['note'] ?? ''));

        $booking = $id ? BookingsDb::getById($studio_id, $id) : null;
        if (!$booking) {
            return self::error('Booking not found.', 404);
        }

        if (!empty($booking['wc_order_id']) && function_exists('wc_get_order') && wc_get_order((int) $booking['wc_order_id'])) {
            return self::error('This booking already has an order. Use "Resend" instead.', 409);
        }

        if ($amount <= 0) {
            return self::error('Please enter a valid amount.');
        }

        // The WooCommerce bridge is only loaded when WooCommerce is active —
        // degrade gracefully on a WC-less install: no fatal, no DB writes, an
        // explicit message for the admin UI.
        if (!class_exists('WooCommerce') || !class_exists(__NAMESPACE__ . '\\BookingOrders') || !class_exists(__NAMESPACE__ . '\\BookingMailer')) {
            return self::error('WooCommerce is not active, so the payment link cannot be created.');
        }

        $order = BookingOrders::createOrderForBooking($booking, $amount, $note);
        if (is_wp_error($order)) {
            return self::error($order->get_error_message());
        }

        BookingsDb::updateFields($studio_id, $id, [
            'status'        => 'confirmed',
            'quoted_amount' => $amount,
            'wc_order_id'   => $order->get_id(),
            'confirmed_at'  => current_time('mysql'),
        ]);

        $mail_sent = BookingMailer::sendPaymentLink($id, (int) $order->get_id());

        return new \WP_REST_Response([
            'success'   => true,
            'mail_sent' => $mail_sent,
            'message'   => 'Order #' . $order->get_order_number() . ($mail_sent
                ? ' created and payment link sent.'
                : ' created, but the email could not be sent — use "Resend".'),
            'order'     => self::orderInfo((int) $order->get_id()),
        ], 200);
    }

    public static function resendPaymentLink(\WP_REST_Request $request): \WP_REST_Response
    {
        $studio_id = self::studioParam($request);
        if ($studio_id === '') {
            return self::error('Unknown studio.');
        }

        $params = $request->get_json_params();
        if (empty($params)) {
            $params = $request->get_body_params();
        }
        $id      = (int) ($params['id'] ?? 0);
        $booking = $id ? BookingsDb::getById($studio_id, $id) : null;

        if (!$booking || empty($booking['wc_order_id'])) {
            return self::error('No order to resend for this booking.');
        }

        if (!function_exists('wc_get_order') || !wc_get_order((int) $booking['wc_order_id'])) {
            return self::error('Linked order no longer exists.', 404);
        }

        if (!class_exists('WooCommerce') || !class_exists(__NAMESPACE__ . '\\BookingOrders') || !class_exists(__NAMESPACE__ . '\\BookingMailer')) {
            return self::error('WooCommerce is not active, so the payment link cannot be resent.');
        }

        $mail_sent = BookingMailer::sendPaymentLink($id, (int) $booking['wc_order_id']);

        return new \WP_REST_Response([
            'success' => true,
            'message' => $mail_sent ? 'Payment link email re-sent.' : 'Could not send the email — check the mail log.',
        ], $mail_sent ? 200 : 500);
    }

    public static function deleteBooking(\WP_REST_Request $request): \WP_REST_Response
    {
        $studio_id = self::studioParam($request);
        if ($studio_id === '') {
            return self::error('Unknown studio.');
        }

        $id = (int) $request->get_param('id');
        if (!$id) {
            return self::error('Invalid ID.');
        }

        $ok = BookingsDb::delete($studio_id, $id);
        return new \WP_REST_Response(['success' => $ok], $ok ? 200 : 400);
    }

    public static function getAdminAvailability(\WP_REST_Request $request): \WP_REST_Response
    {
        $studio_id = self::studioParam($request);
        if ($studio_id === '') {
            return self::error('Unknown studio.');
        }

        return new \WP_REST_Response([
            'success'  => true,
            'settings' => BookingAvailability::get($studio_id),
        ], 200);
    }

    public static function saveAvailability(\WP_REST_Request $request): \WP_REST_Response
    {
        $studio_id = self::studioParam($request);
        if ($studio_id === '') {
            return self::error('Unknown studio.');
        }

        $params   = $request->get_json_params() ?: [];
        $settings = BookingAvailability::save($studio_id, $params);

        return new \WP_REST_Response([
            'success'  => true,
            'message'  => 'Availability settings saved.',
            'settings' => $settings,
        ], 200);
    }

    public static function saveSettings(\WP_REST_Request $request): \WP_REST_Response
    {
        $studio_id = self::studioParam($request);
        if ($studio_id === '') {
            return self::error('Unknown studio.');
        }

        $params   = $request->get_json_params() ?: [];
        $settings = BookingAvailability::saveSettings($studio_id, $params);

        return new \WP_REST_Response([
            'success'  => true,
            'message'  => 'Settings saved.',
            'settings' => $settings,
        ], 200);
    }

    /**
     * Notify the customer that their booking was cancelled from the admin.
     * Lists fixed fields only — internal notes are never emailed.
     */
    private static function sendCancellationEmail(array $booking): void
    {
        if (empty($booking['email']) || !is_email($booking['email'])) {
            return;
        }

        $studio_name = (string) (BookingStudios::get((string) $booking['studio_id'])['name'] ?? $booking['studio_id']);

        $subject = 'Your Achiever Art booking request has been cancelled';

        $body  = 'Hi ' . $booking['name'] . ",\n\n";
        $body .= "Your booking request has been cancelled.\n\n";
        $body .= "BOOKING DETAILS:\n";
        $body .= "------------------------------\n";
        $body .= 'Name: ' . $booking['name'] . "\n";
        $body .= 'Studio: ' . $studio_name . "\n";
        if (!empty($booking['programme'])) {
            $body .= 'Programme: ' . $booking['programme'] . "\n";
        }
        if (!empty($booking['slot_date'])) {
            $body .= 'Preferred Date: ' . $booking['slot_date'] . "\n";
        }
        if (!empty($booking['slot_time'])) {
            $body .= 'Preferred Time: ' . $booking['slot_time'] . "\n";
        }
        $body .= "\nIf you have any questions or would like to rebook, reply to this email or WhatsApp us directly.\n\n";
        $body .= "Best regards,\nAchiever Art Team";

        wp_mail($booking['email'], $subject, $body, ['Content-Type: text/plain; charset=UTF-8']);
    }
}
