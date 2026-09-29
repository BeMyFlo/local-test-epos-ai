<?php
/**
 * Art Booking payment-link email — Achiever Art branded (HTML).
 *
 * Self-contained standalone layout (does not use the shared WC email
 * header/footer) so the payment email keeps its own look. The payment button
 * points at the WooCommerce order-pay page, where the zippy-pay gateway
 * renders the PayNow QR — there is no custom payment page.
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

$greeting    = $order->get_billing_first_name();
$pay_url     = $order->get_checkout_payment_url();
$subtotal    = (float) $order->get_subtotal();
$tax_total   = (float) $order->get_total_tax();
$order_total = (float) $order->get_total();
$currency    = $order->get_currency();

// Branding.
$store_name = get_bloginfo('name', 'display');
$logo_url   = '';
if (function_exists('get_custom_logo')) {
    $logo_id  = get_theme_mod('custom_logo');
    $logo_src = $logo_id ? wp_get_attachment_image_src($logo_id, 'full') : false;
    if ($logo_src) {
        $logo_url = $logo_src[0];
    }
}
$home_url   = home_url('/');
$support_to = get_option('woocommerce_email_from_address', get_option('admin_email'));
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=<?php bloginfo('charset'); ?>" />
	<meta content="width=device-width, initial-scale=1.0" name="viewport">
	<title><?php echo esc_html($email_heading); ?></title>
</head>
<body style="margin:0; padding:0; background:#f4f4f7; font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif; color:#1a1a1a;">

<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#f4f4f7; padding:32px 16px;">
	<tr>
		<td align="center">

			<table role="presentation" width="560" cellpadding="0" cellspacing="0" border="0" style="max-width:560px; width:100%;">

				<!-- Brand header -->
				<tr>
					<td style="background:#1A1040; border-radius:16px 16px 0 0; padding:26px 40px; text-align:center;">
						<?php if ($logo_url) : ?>
							<a href="<?php echo esc_url($home_url); ?>" style="text-decoration:none;">
								<img src="<?php echo esc_url($logo_url); ?>" alt="<?php echo esc_attr($store_name); ?>" style="max-height:40px; width:auto; border:0; display:inline-block;" />
							</a>
						<?php else : ?>
							<a href="<?php echo esc_url($home_url); ?>" style="font-size:20px; font-weight:700; color:#ffffff; text-decoration:none; letter-spacing:-0.01em;">
								<?php echo esc_html($store_name); ?>
							</a>
						<?php endif; ?>
					</td>
				</tr>
				<tr>
					<td style="background:#E91E8C; height:5px; line-height:5px; font-size:5px;">&nbsp;</td>
				</tr>

				<!-- Main card -->
				<tr>
					<td style="background:#ffffff; border:1px solid rgba(0,0,0,0.06); border-top:0; border-radius:0 0 16px 16px; padding:36px 40px 32px;">

						<h1 style="margin:0 0 16px; font-size:23px; font-weight:700; color:#1A1040; line-height:1.3; letter-spacing:-0.01em;">
							<?php echo esc_html($email_heading); ?>
						</h1>

						<p style="margin:0 0 12px; font-size:15px; line-height:1.6; color:#1a1a1a;">
							<?php printf(
								/* translators: %s: customer first name */
								esc_html__('Hi %s,', 'ai-zippy'),
								'<strong>' . esc_html($greeting !== '' ? $greeting : __('there', 'ai-zippy')) . '</strong>'
							); ?>
						</p>
						<p style="margin:0 0 20px; font-size:15px; line-height:1.6; color:#555;">
							<?php esc_html_e('Great news — your booking is confirmed. Your place is held and all that is left is the payment below.', 'ai-zippy'); ?>
						</p>

						<!-- Booking summary -->
						<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#FFF0F5; border-radius:12px; margin-bottom:18px;">
							<tr>
								<td style="padding:16px 18px 6px;">
									<span style="font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:0.05em; color:#E91E8C;">
										<?php esc_html_e('Your booking', 'ai-zippy'); ?>
									</span>
								</td>
							</tr>
							<tr>
								<td style="padding:6px 18px 16px;">
									<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="font-size:14px; line-height:1.7; color:#1a1a1a;">
										<tr>
											<td style="padding:2px 0; color:#7a6f86; width:44%;"><?php esc_html_e('Booking reference', 'ai-zippy'); ?></td>
											<td style="padding:2px 0; font-weight:600;">#<?php echo esc_html($booking_id); ?></td>
										</tr>
										<?php if ($studio_name !== '') : ?>
										<tr>
											<td style="padding:2px 0; color:#7a6f86;"><?php esc_html_e('Studio', 'ai-zippy'); ?></td>
											<td style="padding:2px 0; font-weight:600;"><?php echo esc_html($studio_name); ?></td>
										</tr>
										<?php endif; ?>
										<?php if ($programme !== '') : ?>
										<tr>
											<td style="padding:2px 0; color:#7a6f86;"><?php esc_html_e('Programme', 'ai-zippy'); ?></td>
											<td style="padding:2px 0; font-weight:600;"><?php echo esc_html($programme); ?></td>
										</tr>
										<?php endif; ?>
										<?php if ($slot_date !== '') : ?>
										<tr>
											<td style="padding:2px 0; color:#7a6f86;"><?php esc_html_e('Date', 'ai-zippy'); ?></td>
											<td style="padding:2px 0; font-weight:600;"><?php echo esc_html($slot_date); ?></td>
										</tr>
										<?php endif; ?>
										<?php if ($slot_time !== '') : ?>
										<tr>
											<td style="padding:2px 0; color:#7a6f86;"><?php esc_html_e('Time slot', 'ai-zippy'); ?></td>
											<td style="padding:2px 0; font-weight:600;"><?php echo esc_html($slot_time); ?></td>
										</tr>
										<?php endif; ?>
									</table>
								</td>
							</tr>
						</table>

						<!-- Amount -->
						<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#f8f8fa; border-radius:12px; margin-bottom:24px;">
							<tr>
								<td style="padding:16px 18px;">
									<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="font-size:14px; line-height:1.7; color:#1a1a1a;">
										<tr>
											<td style="padding:2px 0; color:#555;"><?php esc_html_e('Subtotal', 'ai-zippy'); ?></td>
											<td align="right" style="padding:2px 0;"><?php echo wp_kses_post(wc_price($subtotal, ['currency' => $currency])); ?></td>
										</tr>
										<?php if ($tax_total > 0) : ?>
										<tr>
											<td style="padding:2px 0; color:#555;"><?php esc_html_e('GST', 'ai-zippy'); ?></td>
											<td align="right" style="padding:2px 0;"><?php echo wp_kses_post(wc_price($tax_total, ['currency' => $currency])); ?></td>
										</tr>
										<?php endif; ?>
										<tr>
											<td style="padding:10px 0 0; border-top:1px solid #e8e8ee; font-weight:700; color:#1A1040; font-size:15px;"><?php esc_html_e('Total due', 'ai-zippy'); ?></td>
											<td align="right" style="padding:10px 0 0; border-top:1px solid #e8e8ee; font-weight:700; color:#1A1040; font-size:15px;"><?php echo wp_kses_post(wc_price($order_total, ['currency' => $currency])); ?></td>
										</tr>
									</table>
								</td>
							</tr>
						</table>

						<!-- CTA -->
						<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
							<tr>
								<td align="center" style="padding:0 0 10px;">
									<table role="presentation" cellpadding="0" cellspacing="0" border="0">
										<tr>
											<td align="center" style="border-radius:12px; background:#E91E8C;">
												<a href="<?php echo esc_url($pay_url); ?>"
													style="display:inline-block; padding:16px 44px; font-size:16px; font-weight:700; color:#ffffff; text-decoration:none; border-radius:12px; letter-spacing:0.01em;">
													<?php esc_html_e('Complete payment', 'ai-zippy'); ?> &rarr;
												</a>
											</td>
										</tr>
									</table>
								</td>
							</tr>
						</table>

						<!-- Fallback link -->
						<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
							<tr>
								<td style="padding:0 0 8px;">
									<p style="margin:0; font-size:12px; line-height:1.6; color:#999999; text-align:center;">
										<?php esc_html_e('Button not working? Copy and paste this link into your browser:', 'ai-zippy'); ?>
									</p>
									<p style="margin:6px 0 0; text-align:center;">
										<a href="<?php echo esc_url($pay_url); ?>" style="font-size:12px; color:#E91E8C; word-break:break-all; text-decoration:underline;">
											<?php echo esc_html($pay_url); ?>
										</a>
									</p>
								</td>
							</tr>
						</table>

						<?php if (!empty($additional_content)) : ?>
						<!-- Admin-configured additional content -->
						<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-top:24px;">
							<tr>
								<td style="padding:16px 18px; background:#f8f8fa; border-radius:10px; font-size:13px; line-height:1.55; color:#555;">
									<?php echo wp_kses_post(wpautop(wptexturize($additional_content))); ?>
								</td>
							</tr>
						</table>
						<?php endif; ?>

						<p style="margin:24px 0 0; font-size:15px; line-height:1.6; color:#1a1a1a;">
							<?php esc_html_e('See you in the studio,', 'ai-zippy'); ?><br>
							<strong><?php echo esc_html($store_name); ?></strong>
						</p>

					</td>
				</tr>

				<!-- Footer -->
				<tr>
					<td style="padding:24px 16px 8px;" align="center">
						<p style="margin:0 0 12px; font-size:12px; color:#999999;">
							<?php printf(
								/* translators: %s: support email address */
								esc_html__('Need help? Contact us at %s', 'ai-zippy'),
								'<a href="mailto:' . esc_attr($support_to) . '" style="color:#E91E8C; text-decoration:none;">' . esc_html($support_to) . '</a>'
							); ?>
						</p>
						<p style="margin:0; font-size:11px; color:#bbbbbb; line-height:1.5;">
							&copy; <?php echo esc_html(date('Y')); ?> <?php echo esc_html($store_name); ?>. <?php esc_html_e('All rights reserved.', 'ai-zippy'); ?><br>
							<?php esc_html_e('You are receiving this email because you made a booking with us.', 'ai-zippy'); ?>
						</p>
					</td>
				</tr>

			</table>

		</td>
	</tr>
</table>

</body>
</html>
