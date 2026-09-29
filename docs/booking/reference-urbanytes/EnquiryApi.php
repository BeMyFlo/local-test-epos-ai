<?php
// AI-generated
namespace AiZippyChild;

defined('ABSPATH') || exit;

class EnquiryApi
{
    public const NAMESPACE = 'ai-zippy-child/v1';

    public static function register(): void
    {
        add_action('rest_api_init', [self::class, 'registerRoutes']);
    }

    public static function registerRoutes(): void
    {
        // Public form submit
        register_rest_route(self::NAMESPACE, '/enquiry', [
            'methods'             => 'POST',
            'callback'            => [self::class, 'handleEnquiry'],
            'permission_callback' => '__return_true',
        ]);

        // Public: availability config for the enquiry form (slots + closed days).
        register_rest_route(self::NAMESPACE, '/enquiry/availability', [
            'methods'             => 'GET',
            'callback'            => static function (\WP_REST_Request $r) {
                $date = sanitize_text_field((string) $r->get_param('date'));
                return new \WP_REST_Response(BookingAvailability::frontendConfig($date), 200);
            },
            'permission_callback' => '__return_true',
        ]);

        // Admin: read + save availability settings.
        register_rest_route(self::NAMESPACE, '/bookings/availability', [
            [
                'methods'             => 'GET',
                'callback'            => static fn() => new \WP_REST_Response([
                    'success'  => true,
                    'settings' => BookingAvailability::get(),
                ], 200),
                'permission_callback' => [self::class, 'adminPermission'],
            ],
            [
                'methods'             => 'POST',
                'callback'            => [self::class, 'saveAvailability'],
                'permission_callback' => [self::class, 'adminPermission'],
            ],
        ]);

        // Admin REST APIs
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

        // Admin: save the internal (never emailed) note on a booking.
        register_rest_route(self::NAMESPACE, '/bookings/notes', [
            'methods'             => 'POST',
            'callback'            => [self::class, 'saveNotes'],
            'permission_callback' => [self::class, 'adminPermission'],
        ]);

        // Confirm a booking: create the WC order + send the payment-link email.
        register_rest_route(self::NAMESPACE, '/bookings/confirm', [
            'methods'             => 'POST',
            'callback'            => [self::class, 'confirmBooking'],
            'permission_callback' => [self::class, 'adminPermission'],
        ]);

        // Resend the payment-link email for a booking that already has an order.
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
    }

    public static function adminPermission(): bool
    {
        return current_user_can('manage_options');
    }

    public static function handleEnquiry(\WP_REST_Request $request): \WP_REST_Response
    {
        $params = $request->get_json_params();
        if (empty($params)) {
            $params = $request->get_body_params();
        }

        $name           = sanitize_text_field($params['name'] ?? '');
        $company        = sanitize_text_field($params['company'] ?? '');
        $email          = sanitize_email($params['email'] ?? '');
        $phone          = sanitize_text_field($params['phone'] ?? '');
        $event_type     = sanitize_text_field($params['event_type'] ?? '');
        $group_size     = sanitize_text_field($params['group_size'] ?? '');
        $preferred_date = sanitize_text_field($params['preferred_date'] ?? '');
        $preferred_time = sanitize_text_field($params['preferred_time'] ?? '');
        $coach_preference = sanitize_text_field($params['coach_preference'] ?? '');
        $details        = sanitize_textarea_field($params['details'] ?? '');

        // Validation
        if (empty($name)) {
            return new \WP_REST_Response([
                'success' => false,
                'message' => 'Please enter your name.',
            ], 400);
        }

        if (empty($company)) {
            return new \WP_REST_Response([
                'success' => false,
                'message' => 'Please enter your company / organisation.',
            ], 400);
        }

        if (empty($email) || !is_email($email)) {
            return new \WP_REST_Response([
                'success' => false,
                'message' => 'Please enter a valid email address.',
            ], 400);
        }

        if (empty($phone)) {
            return new \WP_REST_Response([
                'success' => false,
                'message' => 'Please enter your phone number.',
            ], 400);
        }

        if (empty($event_type)) {
            return new \WP_REST_Response([
                'success' => false,
                'message' => 'Please select an event type.',
            ], 400);
        }

        if (empty($group_size)) {
            return new \WP_REST_Response([
                'success' => false,
                'message' => 'Please select a group size.',
            ], 400);
        }

        // Preferred date/time (optional) must still match current availability.
        $availability_error = BookingAvailability::validateSubmission($preferred_date, $preferred_time);
        if ($availability_error !== null) {
            return new \WP_REST_Response([
                'success' => false,
                'message' => $availability_error,
            ], 400);
        }

        // Save to Database Table
        $booking_id = BookingsDb::insert([
            'type'           => 'group_enquiry',
            'name'           => $name,
            'company'        => $company,
            'email'          => $email,
            'phone'          => $phone,
            'event_type'     => $event_type,
            'group_size'     => $group_size,
            'preferred_date' => $preferred_date,
            'preferred_time' => $preferred_time,
            'coach_preference' => $coach_preference,
            'details'        => $details,
        ]);

        // Email body
        $to      = ibat_enquiry_to_email();
        $subject = 'New Group Event Enquiry - ' . $name . ($company ? " ($company)" : '');

        $body  = "NEW GROUP EVENT ENQUIRY DETAILS:\n";
        $body .= "=================================\n";
        $body .= "Booking ID: #" . $booking_id . "\n";
        $body .= "Name: $name\n";
        $body .= "Company / Organisation: " . ($company ?: 'N/A') . "\n";
        $body .= "Email: $email\n";
        $body .= "Phone / WhatsApp: $phone\n";
        $body .= "Event Type: $event_type\n";
        $body .= "Group Size: $group_size\n";
        $body .= "Preferred Date: " . ($preferred_date ?: 'Not specified') . "\n";
        $body .= "Preferred Time: " . ($preferred_time ?: 'Not specified') . "\n";
        if ($coach_preference !== '') {
            $body .= "Coaches Preference: " . $coach_preference . "\n";
        }
        $body .= "\n";
        $body .= "ADDITIONAL DETAILS / SPECIAL REQUESTS:\n";
        $body .= ($details ?: 'None') . "\n";
        $body .= "=================================\n";
        $body .= "Submitted via iBAT Website Events Form\n";

        $headers = [
            'Content-Type: text/plain; charset=UTF-8',
            'Reply-To: ' . $email,
        ];

        $sent = wp_mail($to, $subject, $body, $headers);

        // Send confirmation email copy to customer
        if (!empty($email) && is_email($email)) {
            $cust_subject = 'We have received your group enquiry - iBAT';
            $cust_body  = "Hi $name,\n\n";
            $cust_body .= "Thank you for reaching out to iBAT! We have received your group event enquiry and our team will get back to you within 1 business day with a customised quote.\n\n";
            $cust_body .= "YOUR ENQUIRY DETAILS:\n";
            $cust_body .= "------------------------------\n";
            $cust_body .= "Name: $name\n";
            if ($company !== '') {
                $cust_body .= "Company / Organisation: $company\n";
            }
            $cust_body .= "Event Type: $event_type\n";
            $cust_body .= "Group Size: $group_size\n";
            $cust_body .= "Preferred Date: " . ($preferred_date ?: 'N/A') . "\n";
            $cust_body .= "Preferred Time: " . ($preferred_time ?: 'N/A') . "\n";
            if ($coach_preference !== '') {
                $cust_body .= "Coaches Preference: " . $coach_preference . "\n";
            }
            $cust_body .= "\n";
            $cust_body .= "Best regards,\niBAT Team";

            wp_mail($email, $cust_subject, $cust_body, ['Content-Type: text/plain; charset=UTF-8']);
        }

        return new \WP_REST_Response([
            'success'   => true,
            'message'   => 'Thank you! Your group enquiry has been submitted. Our team will get back to you within 1 business day.',
            'bookingId' => $booking_id,
            'mailSent'  => $sent,
        ], 200);
    }

    public static function getBookings(\WP_REST_Request $request): \WP_REST_Response
    {
        $search = sanitize_text_field($request->get_param('search') ?? '');
        $status = sanitize_text_field($request->get_param('status') ?? 'all');
        $type   = sanitize_text_field($request->get_param('type') ?? 'all');

        $items = BookingsDb::getBookings($search, $status, $type);
        foreach ($items as &$item) {
            $item['order'] = !empty($item['order_id'])
                ? self::orderInfo((int) $item['order_id'])
                : null;
        }
        unset($item);

        $stats = BookingsDb::getStats();

        return new \WP_REST_Response([
            'success'  => true,
            'bookings' => $items,
            'stats'    => $stats,
        ], 200);
    }

    /**
     * Compact order summary for the admin table / modal.
     */
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

    public static function confirmBooking(\WP_REST_Request $request): \WP_REST_Response
    {
        $params  = $request->get_json_params() ?: [];
        $id      = (int) ($params['id'] ?? 0);
        $amount  = (float) ($params['amount'] ?? 0);
        $note    = sanitize_textarea_field($params['note'] ?? '');

        $booking = $id ? BookingsDb::getById($id) : null;
        if (!$booking) {
            return new \WP_REST_Response(['success' => false, 'message' => 'Booking not found.'], 404);
        }

        if (!empty($booking['order_id']) && wc_get_order((int) $booking['order_id'])) {
            return new \WP_REST_Response([
                'success' => false,
                'message' => 'This booking already has an order. Use "Resend" instead.',
            ], 409);
        }

        if ($amount <= 0) {
            return new \WP_REST_Response(['success' => false, 'message' => 'Please enter a valid amount.'], 400);
        }

        $order = BookingOrders::createOrderForBooking($booking, $amount, $note);
        if (is_wp_error($order)) {
            return new \WP_REST_Response(['success' => false, 'message' => $order->get_error_message()], 400);
        }

        BookingsDb::updateFields($id, [
            'status'       => 'confirmed',
            'quote_amount' => $amount,
            'order_id'     => $order->get_id(),
            'confirmed_at' => current_time('mysql'),
        ]);

        $mail_sent = BookingMailer::sendPaymentLink($id, $order->get_id());

        return new \WP_REST_Response([
            'success'   => true,
            'mail_sent' => $mail_sent,
            'message'   => 'Order #' . $order->get_order_number() . ($mail_sent
                ? ' created and payment link sent.'
                : ' created, but the email could not be sent — use "Resend".'),
            'order'     => self::orderInfo($order->get_id()),
        ], 200);
    }

    public static function resendPaymentLink(\WP_REST_Request $request): \WP_REST_Response
    {
        $params  = $request->get_json_params() ?: [];
        $id      = (int) ($params['id'] ?? 0);
        $booking = $id ? BookingsDb::getById($id) : null;

        if (!$booking || empty($booking['order_id'])) {
            return new \WP_REST_Response(['success' => false, 'message' => 'No order to resend for this booking.'], 400);
        }

        if (!wc_get_order((int) $booking['order_id'])) {
            return new \WP_REST_Response(['success' => false, 'message' => 'Linked order no longer exists.'], 404);
        }

        $mail_sent = BookingMailer::sendPaymentLink($id, (int) $booking['order_id']);

        return new \WP_REST_Response([
            'success' => true,
            'message' => $mail_sent ? 'Payment link email re-sent.' : 'Could not send the email — check the mail log.',
        ], $mail_sent ? 200 : 500);
    }

    public static function saveAvailability(\WP_REST_Request $request): \WP_REST_Response
    {
        $params = $request->get_json_params() ?: [];
        $settings = BookingAvailability::save($params);

        return new \WP_REST_Response([
            'success'  => true,
            'message'  => 'Availability settings saved.',
            'settings' => $settings,
        ], 200);
    }

    public static function updateStatus(\WP_REST_Request $request): \WP_REST_Response
    {
        $params = $request->get_json_params();
        $id     = (int) ($params['id'] ?? 0);
        $status = sanitize_text_field($params['status'] ?? '');

        if (!$id || empty($status)) {
            return new \WP_REST_Response(['success' => false, 'message' => 'Invalid parameters'], 400);
        }

        $ok = BookingsDb::updateStatus($id, $status);

        // Let the customer know when staff cancel their booking.
        if ($ok && $status === 'cancelled') {
            self::sendCancellationEmail($id);
        }

        return new \WP_REST_Response(['success' => $ok], $ok ? 200 : 400);
    }

    /**
     * Save the admin-only internal note for a booking (never sent to the customer).
     */
    public static function saveNotes(\WP_REST_Request $request): \WP_REST_Response
    {
        $params = $request->get_json_params();
        $id     = (int) ($params['id'] ?? 0);
        $notes  = sanitize_textarea_field((string) ($params['notes'] ?? ''));

        if (!$id || !BookingsDb::getById($id)) {
            return new \WP_REST_Response(['success' => false, 'message' => 'Booking not found.'], 404);
        }

        $ok = BookingsDb::updateFields($id, ['notes' => $notes]);

        return new \WP_REST_Response(['success' => $ok, 'notes' => $notes], $ok ? 200 : 400);
    }

    /**
     * Notify the customer that their booking was cancelled from the admin.
     */
    private static function sendCancellationEmail(int $id): void
    {
        $booking = BookingsDb::getById($id);
        if (!$booking || empty($booking['email']) || ! is_email($booking['email'])) {
            return;
        }

        $subject = 'Your iBAT booking request has been cancelled';

        $body  = 'Hi ' . $booking['name'] . ",\n\n";
        $body .= "Your booking request has been cancelled.\n\n";
        $body .= "BOOKING DETAILS:\n";
        $body .= "------------------------------\n";
        $body .= 'Name: ' . $booking['name'] . "\n";
        if (!empty($booking['company'])) {
            $body .= 'Company / Organisation: ' . $booking['company'] . "\n";
        }
        if (!empty($booking['preferred_date'])) {
            $body .= 'Preferred Date: ' . $booking['preferred_date'] . "\n";
        }
        if (!empty($booking['preferred_time'])) {
            $body .= 'Preferred Time: ' . $booking['preferred_time'] . "\n";
        }
        $body .= "\nIf you have any questions or would like to rebook, reply to this email or WhatsApp us directly.\n\n";
        $body .= "Best regards,\niBAT Team";

        wp_mail($booking['email'], $subject, $body, ['Content-Type: text/plain; charset=UTF-8']);
    }

    public static function deleteBooking(\WP_REST_Request $request): \WP_REST_Response
    {
        $id = (int) $request->get_param('id');
        if (!$id) {
            return new \WP_REST_Response(['success' => false, 'message' => 'Invalid ID'], 400);
        }

        $ok = BookingsDb::delete($id);
        return new \WP_REST_Response(['success' => $ok], $ok ? 200 : 400);
    }
}
