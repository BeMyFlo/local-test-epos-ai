<?php
namespace AiZippyChild;

defined('ABSPATH') || exit;

/**
 * Bridges a booking enquiry to a WooCommerce order.
 *
 * Model: one shared hidden "Art Booking" product; each order overrides the
 * line-item price with the amount staff quote. GST is added on top by
 * WooCommerce tax rules. Orders are tagged with meta so they can be told apart
 * from regular shop orders.
 */
class BookingOrders
{
    public const META_BOOKING_ID = '_aa_booking_id';
    public const META_STUDIO_ID  = '_aa_studio_id';
    public const META_QUOTE_NOTE = '_aa_quote_note';
    public const PRODUCT_OPTION  = 'aa_booking_product_id';

    public static function register(): void
    {
        add_action('woocommerce_order_status_changed', [self::class, 'syncOrderStatus'], 10, 4);

        // Let customers pay booking orders from the emailed link without the
        // WC "confirm your email" gate. Scoped to booking orders only.
        add_filter('woocommerce_order_email_verification_required', [self::class, 'skipEmailVerification'], 10, 3);

        // Admin order list — "Source" column (HPOS + legacy).
        add_filter('woocommerce_shop_order_list_table_columns', [self::class, 'addOrderColumn']);
        add_action('woocommerce_shop_order_list_table_custom_column', [self::class, 'renderOrderColumn'], 10, 2);
        add_filter('manage_edit-shop_order_columns', [self::class, 'addOrderColumn']);
        add_action('manage_shop_order_posts_custom_column', [self::class, 'renderOrderColumnLegacy'], 10, 2);

        // Order edit screen — booking summary metabox (HPOS + legacy).
        add_action('add_meta_boxes', [self::class, 'addMetabox']);
    }

    /* ---------------------------------------------------------------------
     * Product
     * ------------------------------------------------------------------- */

    public static function getProductId(): int
    {
        $id = (int) get_option(self::PRODUCT_OPTION, 0);
        if ($id && ($p = wc_get_product($id)) && $p->get_status() !== 'trash') {
            return $id;
        }

        $product = new \WC_Product_Simple();
        $product->set_name(__('Art Booking', 'ai-zippy'));
        $product->set_status('private');
        $product->set_catalog_visibility('hidden');
        $product->set_virtual(true);
        $product->set_sold_individually(true);
        $product->set_tax_status('taxable');
        $product->set_price('');
        $product->set_regular_price('');
        $product->update_meta_data('_aa_booking_product', 'yes');
        $id = $product->save();

        update_option(self::PRODUCT_OPTION, $id, false);
        return (int) $id;
    }

    /* ---------------------------------------------------------------------
     * Order creation
     * ------------------------------------------------------------------- */

    /**
     * @return \WC_Order|\WP_Error
     */
    public static function createOrderForBooking(array $booking, float $amount, string $note = '')
    {
        if (!class_exists('WooCommerce')) {
            return new \WP_Error('aa_wc', __('WooCommerce is not available.', 'ai-zippy'));
        }

        if ($amount <= 0) {
            return new \WP_Error('aa_amount', __('Please enter a valid amount greater than zero.', 'ai-zippy'));
        }

        $product = wc_get_product(self::getProductId());
        if (!$product) {
            return new \WP_Error('aa_product', __('Could not create the booking product.', 'ai-zippy'));
        }

        $studio_id   = (string) ($booking['studio_id'] ?? '');
        $studio_name = self::studioName($studio_id);

        try {
            $order = wc_create_order();

            $item_id = $order->add_product($product, 1, [
                'subtotal' => $amount,
                'total'    => $amount,
            ]);

            $item = $item_id ? $order->get_item($item_id) : false;
            if ($item) {
                $label = trim((string) ($booking['programme'] ?? ''));
                $item->set_name($label !== '' ? $label : __('Art Booking', 'ai-zippy'));
                $item->add_meta_data(__('Booking ID', 'ai-zippy'), '#' . (int) $booking['id']);
                if ($studio_name !== '') {
                    $item->add_meta_data(__('Studio', 'ai-zippy'), $studio_name);
                }
                if (!empty($booking['slot_date'])) {
                    $item->add_meta_data(__('Slot Date', 'ai-zippy'), $booking['slot_date']);
                }
                if (!empty($booking['slot_time'])) {
                    $item->add_meta_data(__('Slot Time', 'ai-zippy'), $booking['slot_time']);
                }
                $item->save();
            }

            [$first, $last] = self::splitName((string) ($booking['name'] ?? ''));
            $order->set_billing_first_name($first);
            $order->set_billing_last_name($last);
            $order->set_billing_email((string) ($booking['email'] ?? ''));
            $order->set_billing_phone((string) ($booking['phone'] ?? ''));
            $order->set_billing_country(wc_get_base_location()['country'] ?: 'SG');

            $order->set_created_via('aa_booking');
            $order->update_meta_data(self::META_BOOKING_ID, (int) $booking['id']);
            $order->update_meta_data(self::META_STUDIO_ID, $studio_id);
            if ($note !== '') {
                $order->update_meta_data(self::META_QUOTE_NOTE, $note);
            }

            $order->add_order_note(sprintf(
                /* translators: %d: booking id */
                __('Created from booking enquiry #%d.', 'ai-zippy'),
                $booking['id']
            ));

            $order->calculate_totals(true); // true = recalculate taxes (GST on top)
            $order->update_status('pending', __('Awaiting customer payment.', 'ai-zippy'));
            $order->save();

            return $order;
        } catch (\Throwable $e) {
            return new \WP_Error('aa_order', $e->getMessage());
        }
    }

    private static function splitName(string $full): array
    {
        $full = trim($full);
        if ($full === '') {
            return ['Guest', ''];
        }
        $parts = preg_split('/\s+/', $full, 2);
        return [$parts[0], $parts[1] ?? ''];
    }

    private static function studioName(string $studio_id): string
    {
        if ($studio_id === '') {
            return '';
        }
        $studio = BookingStudios::get($studio_id);
        return (string) ($studio['name'] ?? $studio_id);
    }

    /* ---------------------------------------------------------------------
     * Order -> booking status sync
     * ------------------------------------------------------------------- */

    public static function syncOrderStatus($order_id, $old_status, $new_status, $order): void
    {
        if (!$order instanceof \WC_Order) {
            return;
        }

        $booking_id = (int) $order->get_meta(self::META_BOOKING_ID);
        $studio_id  = (string) $order->get_meta(self::META_STUDIO_ID);
        if (!$booking_id || $studio_id === '') {
            return;
        }

        if (in_array($new_status, ['processing', 'completed'], true)) {
            BookingsDb::updateFields($studio_id, $booking_id, [
                'status'  => 'paid',
                'paid_at' => current_time('mysql'),
            ]);
        } elseif (in_array($new_status, ['cancelled', 'refunded'], true)) {
            BookingsDb::updateFields($studio_id, $booking_id, ['status' => 'confirmed']);
        }
    }

    public static function skipEmailVerification($required, $order, $context)
    {
        if ($order instanceof \WC_Order && $order->get_meta(self::META_BOOKING_ID)) {
            return false;
        }
        return $required;
    }

    /* ---------------------------------------------------------------------
     * Admin order list column
     * ------------------------------------------------------------------- */

    public static function addOrderColumn(array $columns): array
    {
        if (isset($columns['aa_source'])) {
            return $columns;
        }
        $new = [];
        foreach ($columns as $key => $label) {
            $new[$key] = $label;
            if ($key === 'order_status') {
                $new['aa_source'] = __('Source', 'ai-zippy');
            }
        }
        if (!isset($new['aa_source'])) {
            $new['aa_source'] = __('Source', 'ai-zippy');
        }
        return $new;
    }

    public static function renderOrderColumn(string $column, $order): void
    {
        if ($column !== 'aa_source') {
            return;
        }
        echo self::sourcePill($order instanceof \WC_Order ? $order : wc_get_order($order));
    }

    public static function renderOrderColumnLegacy(string $column, $post_id): void
    {
        if ($column !== 'aa_source') {
            return;
        }
        echo self::sourcePill(wc_get_order($post_id));
    }

    private static function sourcePill($order): string
    {
        if (!$order instanceof \WC_Order || !$order->get_meta(self::META_BOOKING_ID)) {
            return '<span style="color:#9ca3af;">&mdash;</span>';
        }
        return '<span style="display:inline-block;padding:2px 8px;border-radius:10px;font-size:11px;font-weight:600;background:#FFF0F5;color:#E91E8C;border:1px solid #F9D3E4;">&#127912; Art Booking</span>';
    }

    /* ---------------------------------------------------------------------
     * Order edit metabox
     * ------------------------------------------------------------------- */

    public static function addMetabox(): void
    {
        // Register on both the legacy post screen and the HPOS orders screen.
        foreach (['shop_order', wc_get_page_screen_id('shop-order')] as $screen) {
            if (!$screen) {
                continue;
            }
            add_meta_box(
                'aa_booking_meta',
                __('Art Booking', 'ai-zippy'),
                [self::class, 'renderMetabox'],
                $screen,
                'side',
                'high'
            );
        }
    }

    public static function renderMetabox($post_or_order): void
    {
        $order = $post_or_order instanceof \WC_Order ? $post_or_order : wc_get_order($post_or_order->ID);
        $booking_id = $order ? (int) $order->get_meta(self::META_BOOKING_ID) : 0;

        if (!$booking_id) {
            echo '<p style="color:#6b7280;margin:0;">' . esc_html__('Not a booking order.', 'ai-zippy') . '</p>';
            return;
        }

        $studio_id = (string) $order->get_meta(self::META_STUDIO_ID);
        $booking   = $studio_id !== '' ? BookingsDb::getById($studio_id, $booking_id) : null;
        $admin_url = admin_url('admin.php?page=' . BookingsAdmin::MENU_SLUG . '&studio=' . rawurlencode($studio_id));
        $note      = $order->get_meta(self::META_QUOTE_NOTE);

        echo '<div style="font-size:13px;line-height:1.7;">';
        echo '<p style="margin:0 0 6px;"><strong>' . esc_html__('Booking', 'ai-zippy') . ':</strong> #' . esc_html($booking_id) . '</p>';
        if ($studio_id !== '') {
            echo '<p style="margin:0 0 6px;"><strong>' . esc_html__('Studio', 'ai-zippy') . ':</strong> ' . esc_html(self::studioName($studio_id)) . '</p>';
        }
        if ($booking) {
            echo '<p style="margin:0 0 6px;"><strong>' . esc_html__('Programme', 'ai-zippy') . ':</strong> ' . esc_html(($booking['programme'] ?? '') ?: '—') . '</p>';
            echo '<p style="margin:0 0 6px;"><strong>' . esc_html__('Slot', 'ai-zippy') . ':</strong> ' . esc_html(self::slotLabel($booking)) . '</p>';
        }
        if ($note) {
            echo '<p style="margin:0 0 6px;"><strong>' . esc_html__('Quote note', 'ai-zippy') . ':</strong><br>' . nl2br(esc_html($note)) . '</p>';
        }
        echo '<p style="margin:8px 0 0;"><a href="' . esc_url($admin_url) . '">' . esc_html__('Open Bookings admin', 'ai-zippy') . ' &rarr;</a></p>';
        echo '</div>';
    }

    private static function slotLabel(array $booking): string
    {
        $label = (string) ($booking['slot_date'] ?? '');
        if ($label === '') {
            $label = 'TBD';
        }
        $time = (string) ($booking['slot_time'] ?? '');
        if ($time !== '') {
            $label .= ' @ ' . $time;
        }
        return $label;
    }
}
