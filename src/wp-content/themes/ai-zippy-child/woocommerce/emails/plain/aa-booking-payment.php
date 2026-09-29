<?php
/**
 * Art Booking payment-link email — Achiever Art branded (plain text).
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package AiZippyChild
 *
 * @var array    $booking
 * @var WC_Order $order
 * @var string   $email_heading
 * @var string   $additional_content
 * @var string   $studio_name
 * @var WC_Email $email
 */

defined('ABSPATH') || exit;

if (!isset($order) || !($order instanceof WC_Order)) {
    return;
}

$booking = is_array($booking ?? null) ? $booking : [];

$booking_id  = (int) ($booking['id'] ?? 0);
$programme   = (string) ($booking['programme'] ?? '');
$slot_date   = (string) ($booking['slot_date'] ?? '');
$slot_time   = (string) ($booking['slot_time'] ?? '');
$studio_name = (string) ($studio_name ?? '');

$currency = $order->get_currency();
$price    = static function (float $amount) use ($currency): string {
    return html_entity_decode(wp_strip_all_tags(wc_price($amount, ['currency' => $currency])), ENT_QUOTES, 'UTF-8');
};

echo "**" . $email_heading . "**\n\n";
echo sprintf("Hi %s,\n\n", $order->get_billing_first_name() ?: 'there');
echo "Great news - your booking is confirmed. Your place is held and all that is left is the payment below.\n\n";

echo "YOUR BOOKING\n";
echo "------------------------------\n";
echo 'Booking reference: #' . $booking_id . "\n";
if ($studio_name !== '') {
    echo 'Studio: ' . $studio_name . "\n";
}
if ($programme !== '') {
    echo 'Programme: ' . $programme . "\n";
}
if ($slot_date !== '') {
    echo 'Date: ' . $slot_date . "\n";
}
if ($slot_time !== '') {
    echo 'Time slot: ' . $slot_time . "\n";
}
echo "\n";

echo "AMOUNT\n";
echo "------------------------------\n";
echo 'Subtotal: ' . $price((float) $order->get_subtotal()) . "\n";
if ((float) $order->get_total_tax() > 0) {
    echo 'GST: ' . $price((float) $order->get_total_tax()) . "\n";
}
echo 'Total due: ' . $price((float) $order->get_total()) . "\n\n";

echo "Complete your payment here:\n";
echo $order->get_checkout_payment_url() . "\n\n";

if (!empty($additional_content)) {
    echo wp_strip_all_tags($additional_content) . "\n\n";
}

echo "See you in the studio,\n";
echo get_bloginfo('name', 'display') . "\n";
