<?php

namespace AiZippy\Checkout;

defined('ABSPATH') || exit;

/**
 * Enqueue checkout assets based on admin template selection.
 *
 * - "react"       → Enqueue Vite-built React checkout app
 * - "woocommerce" → Enqueue WC default checkout styles only
 */
class CheckoutAssets
{
    /**
     * Register hooks.
     */
    public static function register(): void
    {
        add_action('wp_enqueue_scripts', [self::class, 'enqueue']);
        add_action('wp_ajax_az_update_checkout_qty', [self::class, 'ajaxUpdateQty']);
        add_action('wp_ajax_nopriv_az_update_checkout_qty', [self::class, 'ajaxUpdateQty']);
        add_action('wp_ajax_az_get_checkout_totals', [self::class, 'ajaxGetTotals']);
        add_action('wp_ajax_nopriv_az_get_checkout_totals', [self::class, 'ajaxGetTotals']);
    }

    /**
     * AJAX handler: render the cart totals partial. Called after coupon
     * apply/remove so the sidebar updates without a full reload.
     */
    public static function ajaxGetTotals(): void
    {
        if (!function_exists('WC') || !WC()->cart) {
            wp_send_json_error('Cart not available');
        }

        // Make sure totals are fresh (coupons just modified)
        WC()->cart->calculate_totals();

        ob_start();
        self::renderTotals();
        $html = ob_get_clean();

        wp_send_json_success(['html' => $html]);
    }

    /**
     * Render the totals block. Used both server-side from form-checkout.php
     * AND from the ajaxGetTotals() handler so coupon updates produce identical
     * markup to the initial page render.
     */
    public static function renderTotals(): void
    {
        $cart = WC()->cart;
        if (!$cart) {
            return;
        }
        ?>
        <div class="az-checkout__totals-row">
            <span><?php esc_html_e('Subtotal', 'ai-zippy'); ?></span>
            <span><?php wc_cart_totals_subtotal_html(); ?></span>
        </div>

        <?php foreach ($cart->get_coupons() as $code => $coupon) :
            $amount     = $cart->get_coupon_discount_amount($coupon->get_code(), $cart->display_cart_ex_tax);
            $remove_url = wp_nonce_url(
                add_query_arg('remove_coupon', rawurlencode($coupon->get_code()), wc_get_checkout_url()),
                'remove_coupon_' . $coupon->get_code(),
                '_wpnonce'
            );
        ?>
        <div class="az-checkout__totals-row az-checkout__totals-row--discount">
            <span class="az-checkout__coupon-label">
                <span class="az-checkout__coupon-tag">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M20.59 13.41 13.42 20.58a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/>
                        <line x1="7" y1="7" x2="7.01" y2="7"/>
                    </svg>
                    <?php echo esc_html($coupon->get_code()); ?>
                </span>
                <a href="<?php echo esc_url($remove_url); ?>" class="az-checkout__coupon-remove" data-az-remove-coupon="<?php echo esc_attr($coupon->get_code()); ?>" aria-label="<?php echo esc_attr(sprintf(__('Remove coupon %s', 'ai-zippy'), $coupon->get_code())); ?>" title="<?php esc_attr_e('Remove', 'ai-zippy'); ?>">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <line x1="18" y1="6" x2="6" y2="18"/>
                        <line x1="6" y1="6" x2="18" y2="18"/>
                    </svg>
                </a>
            </span>
            <span class="az-checkout__coupon-amount">−<?php echo wc_price($amount); ?></span>
        </div>
        <?php endforeach; ?>

        <?php if ($cart->needs_shipping() && $cart->show_shipping()) : ?>
        <div class="az-checkout__totals-row">
            <span><?php esc_html_e('Shipping', 'ai-zippy'); ?></span>
            <span><?php wc_cart_totals_shipping_html(); ?></span>
        </div>
        <?php endif; ?>

        <?php foreach ($cart->get_fees() as $fee) : ?>
        <div class="az-checkout__totals-row">
            <span><?php echo esc_html($fee->name); ?></span>
            <span><?php wc_cart_totals_fee_html($fee); ?></span>
        </div>
        <?php endforeach; ?>

        <?php if (wc_tax_enabled() && !$cart->display_prices_including_tax()) : ?>
        <div class="az-checkout__totals-row">
            <span><?php esc_html_e('Tax', 'ai-zippy'); ?></span>
            <span><?php wc_cart_totals_taxes_total_html(); ?></span>
        </div>
        <?php endif; ?>

        <div class="az-checkout__totals-row az-checkout__totals-row--total">
            <span><?php esc_html_e('Total', 'ai-zippy'); ?></span>
            <span><?php wc_cart_totals_order_total_html(); ?></span>
        </div>
        <?php
    }

    /**
     * AJAX handler: update cart item quantity from checkout sidebar.
     */
    public static function ajaxUpdateQty(): void
    {
        check_ajax_referer('az-checkout-qty', 'security');

        $cart_key = sanitize_text_field($_POST['cart_key'] ?? '');
        $quantity = absint($_POST['quantity'] ?? 0);

        if (empty($cart_key)) {
            wp_send_json_error('Invalid cart key');
        }

        if ($quantity === 0) {
            WC()->cart->remove_cart_item($cart_key);
        } else {
            WC()->cart->set_quantity($cart_key, $quantity);
        }

        wp_send_json_success();
    }

    /**
     * Enqueue checkout assets on checkout page only.
     */
    public static function enqueue(): void
    {
        if (!is_checkout() && !is_page('checkout')) {
            return;
        }

        if (CheckoutSettings::isReact()) {
            self::enqueueReactCheckout();
        } else {
            self::enqueueWcCheckout();
        }
    }

    /**
     * Enqueue React checkout app.
     */
    private static function enqueueReactCheckout(): void
    {
        \AiZippy\Core\ViteAssets::enqueue(
            'ai-zippy-checkout',
            'src/wp-content/themes/ai-zippy/src/js/frontend/checkout/index.jsx'
        );

        wp_localize_script('ai-zippy-checkout', 'aiZippyCheckout', [
            'paymentGateways'   => self::getPaymentGateways(),
            'shippingEnabled'   => 'yes' === get_option('woocommerce_calc_shipping', 'yes'),
            'shipToDestination' => get_option('woocommerce_ship_to_destination', 'shipping'),
            'customer'          => self::getCustomerData(),
        ]);
    }

    /**
     * Enqueue WooCommerce default checkout styles.
     */
    private static function enqueueWcCheckout(): void
    {
        \AiZippy\Core\ViteAssets::enqueue(
            'ai-zippy-wc-checkout',
            'src/wp-content/themes/ai-zippy/src/scss/wc-checkout-entry.scss'
        );
    }

    /**
     * Get enabled WooCommerce payment gateways.
     */
    private static function getPaymentGateways(): array
    {
        $gateways = [];

        if (!function_exists('WC') || !WC()->payment_gateways()) {
            return $gateways;
        }

        foreach (WC()->payment_gateways()->get_available_payment_gateways() as $gateway) {
            $gateways[] = [
                'id'          => $gateway->id,
                'title'       => $gateway->get_title(),
                'description' => $gateway->get_description(),
            ];
        }

        return $gateways;
    }

    /**
     * Get logged-in customer data for form pre-fill.
     */
    private static function getCustomerData(): array
    {
        if (!is_user_logged_in() || !function_exists('WC') || !WC()->customer) {
            return [];
        }

        $c = WC()->customer;

        return [
            'firstName' => $c->get_billing_first_name(),
            'lastName'  => $c->get_billing_last_name(),
            'email'     => $c->get_billing_email(),
            'phone'     => $c->get_billing_phone(),
            'billing'   => [
                'address_1' => $c->get_billing_address_1(),
                'address_2' => $c->get_billing_address_2(),
                'city'      => $c->get_billing_city(),
                'state'     => $c->get_billing_state(),
                'postcode'  => $c->get_billing_postcode(),
                'country'   => $c->get_billing_country() ?: 'SG',
                'company'   => $c->get_billing_company(),
            ],
        ];
    }
}
