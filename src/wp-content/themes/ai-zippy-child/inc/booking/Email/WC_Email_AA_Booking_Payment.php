<?php
/**
 * Customer email: Art Booking confirmed — pay now.
 *
 * Fired by do_action('aa_booking_payment_link', $booking_id, $order_id) from
 * the Bookings admin ("Confirm & Send" / "Resend"). Routes through the WC
 * mailer, so the site's SMTP plugin handles delivery automatically.
 */

defined('ABSPATH') || exit;

if (!class_exists('WC_Email')) {
    return;
}

if (!class_exists('WC_Email_AA_Booking_Payment')) :

class WC_Email_AA_Booking_Payment extends WC_Email
{
    /** @var array|null */
    public $booking = null;

    public function __construct()
    {
        $this->id             = 'aa_booking_payment';
        $this->customer_email = true;
        $this->title          = __('Art Booking — payment link', 'ai-zippy');
        $this->description    = __('Sent to the customer when staff confirm a booking and issue a quote. Contains the booking summary and a secure WooCommerce payment link.', 'ai-zippy');

        $this->template_base  = get_stylesheet_directory() . '/woocommerce/';
        $this->template_html  = 'emails/aa-booking-payment.php';
        $this->template_plain = 'emails/plain/aa-booking-payment.php';

        $this->placeholders = [
            '{site_title}'   => $this->get_blogname(),
            '{booking_id}'   => '',
            '{order_number}' => '',
        ];

        // Fired manually from the admin.
        add_action('aa_booking_payment_link', [$this, 'trigger'], 10, 2);

        parent::__construct();

        $this->recipient = '';
    }

    public function get_default_subject(): string
    {
        return __('Your Achiever Art booking is confirmed — complete your payment', 'ai-zippy');
    }

    public function get_default_heading(): string
    {
        return __('Booking confirmed — one step left', 'ai-zippy');
    }

    public function get_default_additional_content(): string
    {
        return __('Questions? Reply to this email or message us on WhatsApp and our team will help you out.', 'ai-zippy');
    }

    /**
     * @param int $booking_id
     * @param int $order_id
     */
    public function trigger($booking_id, $order_id): bool
    {
        $this->setup_locale();

        $booking_id = (int) $booking_id;
        $order_id   = (int) $order_id;

        $this->object = $order_id && function_exists('wc_get_order') ? wc_get_order($order_id) : false;
        $studio_id    = $this->object instanceof \WC_Order ? (string) $this->object->get_meta('_aa_studio_id') : '';

        $this->booking = ($studio_id !== '' && class_exists('\AiZippyChild\BookingsDb'))
            ? \AiZippyChild\BookingsDb::getById($studio_id, $booking_id)
            : null;

        if (!$this->booking || !($this->object instanceof \WC_Order)) {
            $this->restore_locale();
            return false;
        }

        $this->recipient                      = $this->object->get_billing_email();
        $this->placeholders['{booking_id}']   = '#' . $booking_id;
        $this->placeholders['{order_number}'] = $this->object->get_order_number();

        $sent = false;
        if ($this->is_enabled() && $this->get_recipient()) {
            $sent = $this->send(
                $this->get_recipient(),
                $this->get_subject(),
                $this->get_content(),
                $this->get_headers(),
                $this->get_attachments()
            );
        }

        $this->restore_locale();
        return (bool) $sent;
    }

    public function get_content_html(): string
    {
        return wc_get_template_html($this->template_html, $this->templateArgs(false), '', $this->template_base);
    }

    public function get_content_plain(): string
    {
        return wc_get_template_html($this->template_plain, $this->templateArgs(true), '', $this->template_base);
    }

    private function templateArgs(bool $plain): array
    {
        return [
            'booking'            => $this->booking,
            'order'              => $this->object,
            'email_heading'      => $this->get_heading(),
            'additional_content' => $this->get_additional_content(),
            'sent_to_admin'      => false,
            'plain_text'         => $plain,
            'email'              => $this,
            'studio_name'        => $this->studioName(),
        ];
    }

    private function studioName(): string
    {
        $studio_id = is_array($this->booking) ? (string) ($this->booking['studio_id'] ?? '') : '';
        if ($studio_id === '' || !class_exists('\AiZippyChild\BookingStudios')) {
            return $studio_id;
        }
        $studio = \AiZippyChild\BookingStudios::get($studio_id);
        return (string) ($studio['name'] ?? $studio_id);
    }
}

endif;

return new WC_Email_AA_Booking_Payment();
