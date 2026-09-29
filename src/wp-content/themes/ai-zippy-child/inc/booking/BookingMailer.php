<?php
namespace AiZippyChild;

defined('ABSPATH') || exit;

/**
 * Registers the Art Booking payment WC_Email and sends it.
 * Delivery goes through the WC mailer, so the site's SMTP plugin applies.
 */
class BookingMailer
{
    private const EMAIL_CLASS = 'WC_Email_AA_Booking_Payment';
    private const EMAIL_ID    = 'aa_booking_payment';

    /** @var \WC_Email|null */
    private static $email = null;

    public static function register(): void
    {
        add_filter('woocommerce_email_classes', [self::class, 'registerEmailClass']);
    }

    /**
     * Filter callback — runs *inside* WC_Emails::init(). Must NOT call
     * WC()->mailer() here (re-entrant). Just build the instance from file.
     */
    public static function registerEmailClass(array $emails): array
    {
        if (!isset($emails[self::EMAIL_CLASS])) {
            $instance = self::buildFromFile();
            if ($instance instanceof \WC_Email) {
                $emails[self::EMAIL_CLASS] = $instance;
                self::$email = $instance;
            }
        }
        return $emails;
    }

    private static function buildFromFile()
    {
        $file = __DIR__ . '/Email/WC_Email_AA_Booking_Payment.php';
        if (!is_readable($file)) {
            return null;
        }
        $instance = require $file;
        return $instance instanceof \WC_Email ? $instance : null;
    }

    /**
     * Get a usable email instance for sending. Safe to call outside the
     * WC_Emails::init() filter (i.e. from REST handlers).
     *
     * @return \WC_Email|null
     */
    private static function getEmail()
    {
        if (self::$email instanceof \WC_Email) {
            return self::$email;
        }

        if (function_exists('WC') && ($mailer = WC()->mailer())) {
            $emails = $mailer->get_emails();
            if (!empty($emails[self::EMAIL_CLASS]) && $emails[self::EMAIL_CLASS] instanceof \WC_Email) {
                return self::$email = $emails[self::EMAIL_CLASS];
            }
            // Some mailer collections are keyed by email id instead.
            if (!empty($emails[self::EMAIL_ID]) && $emails[self::EMAIL_ID] instanceof \WC_Email) {
                return self::$email = $emails[self::EMAIL_ID];
            }
        }

        return self::$email = self::buildFromFile();
    }

    /**
     * Send (or resend) the payment-link email for a booking + its order.
     */
    public static function sendPaymentLink(int $booking_id, int $order_id): bool
    {
        $email = self::getEmail();
        if (!$email) {
            error_log("[Achiever Art] BookingMailer: email class unavailable (booking #{$booking_id})");
            return false;
        }

        $sent = $email->trigger($booking_id, $order_id);

        if (!$sent) {
            error_log("[Achiever Art] BookingMailer: trigger() returned false for booking #{$booking_id}, order #{$order_id}");
        }

        return (bool) $sent;
    }
}
