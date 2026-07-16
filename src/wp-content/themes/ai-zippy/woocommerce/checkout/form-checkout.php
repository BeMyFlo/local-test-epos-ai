<?php
/**
 * Checkout Form — AI Zippy Override
 *
 * Card-based layout matching the React checkout design.
 * Numbered sections: 1. Contact & Billing, 2. Order Summary, 3. Payment
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package AiZippy
 * @version 9.4.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

do_action( 'woocommerce_before_checkout_form', $checkout );

if ( ! $checkout->is_registration_enabled() && $checkout->is_registration_required() && ! is_user_logged_in() ) {
	echo esc_html( apply_filters( 'woocommerce_checkout_must_be_logged_in_message', __( 'You must be logged in to checkout.', 'woocommerce' ) ) );
	return;
}
?>

<form name="checkout" method="post" class="checkout woocommerce-checkout az-checkout" action="<?php echo esc_url( wc_get_checkout_url() ); ?>" enctype="multipart/form-data" aria-label="<?php echo esc_attr__( 'Checkout', 'woocommerce' ); ?>">

	<?php if ( $checkout->get_checkout_fields() ) : ?>

		<div class="az-checkout__layout">

			<!-- LEFT COLUMN: Form sections -->
			<div class="az-checkout__form">

				<!-- 1. Contact & Billing -->
				<div class="az-checkout__card">
					<div class="az-checkout__card-header">
						<span class="az-checkout__card-num">1.</span>
						<h3 class="az-checkout__card-title"><?php esc_html_e( 'Contact & Billing', 'ai-zippy' ); ?></h3>
					</div>
					<div class="az-checkout__card-body">
						<?php do_action( 'woocommerce_checkout_before_customer_details' ); ?>
						<?php do_action( 'woocommerce_checkout_billing' ); ?>
						<?php do_action( 'woocommerce_checkout_shipping' ); ?>
						<?php do_action( 'woocommerce_checkout_after_customer_details' ); ?>
					</div>
				</div>

				<!-- 2. Additional Information -->
				<?php if ( apply_filters( 'woocommerce_enable_order_notes_field', 'yes' === get_option( 'woocommerce_enable_order_comments', 'yes' ) ) ) : ?>
				<div class="az-checkout__card">
					<div class="az-checkout__card-header">
						<span class="az-checkout__card-num">2.</span>
						<h3 class="az-checkout__card-title"><?php esc_html_e( 'Additional information', 'ai-zippy' ); ?></h3>
					</div>
					<div class="az-checkout__card-body">
						<?php do_action( 'woocommerce_before_order_notes', $checkout ); ?>
						<?php if ( $checkout->get_checkout_fields( 'order' ) ) : ?>
							<?php foreach ( $checkout->get_checkout_fields( 'order' ) as $key => $field ) : ?>
								<?php woocommerce_form_field( $key, $field, $checkout->get_value( $key ) ); ?>
							<?php endforeach; ?>
						<?php endif; ?>
						<?php do_action( 'woocommerce_after_order_notes', $checkout ); ?>
					</div>
				</div>
				<?php endif; ?>

			</div>

			<!-- RIGHT COLUMN: Order summary -->
			<div class="az-checkout__sidebar">
				<div class="az-checkout__card az-checkout__card--sticky">
					<div class="az-checkout__card-header">
						<h3 class="az-checkout__card-title"><?php esc_html_e( 'Order summary', 'ai-zippy' ); ?></h3>
					</div>
					<div class="az-checkout__card-body">
						<?php do_action( 'woocommerce_checkout_before_order_review_heading' ); ?>
						<div class="az-checkout__items">
							<?php foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) :
								$_product = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
								if ( $_product && $_product->exists() && $cart_item['quantity'] > 0 && apply_filters( 'woocommerce_checkout_cart_item_visible', true, $cart_item, $cart_item_key ) ) :
							?>
							<div class="az-checkout__item" data-cart-key="<?php echo esc_attr( $cart_item_key ); ?>">
								<div class="az-checkout__item-img">
									<?php echo wp_kses_post( $_product->get_image( 'woocommerce_thumbnail' ) ); ?>
								</div>
								<div class="az-checkout__item-detail">
									<div class="az-checkout__item-top">
										<span class="az-checkout__item-name">
											<?php echo wp_kses_post( apply_filters( 'woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key ) ); ?>
										</span>
										<span class="az-checkout__item-total">
											<?php echo wp_kses_post( apply_filters( 'woocommerce_cart_item_subtotal', WC()->cart->get_product_subtotal( $_product, $cart_item['quantity'] ), $cart_item, $cart_item_key ) ); ?>
										</span>
									</div>
									<div class="az-checkout__item-bottom">
										<span class="az-checkout__item-meta"><?php printf( esc_html__( 'Quantity : %s', 'ai-zippy' ), esc_html( $cart_item['quantity'] ) ); ?></span>
										<div class="az-checkout__item-qty">
											<button type="button" class="az-checkout__qty-btn az-checkout__qty-btn--minus" aria-label="<?php esc_attr_e( 'Decrease', 'ai-zippy' ); ?>">−</button>
											<span class="az-checkout__qty-val"><?php echo esc_html( $cart_item['quantity'] ); ?></span>
											<button type="button" class="az-checkout__qty-btn az-checkout__qty-btn--plus" aria-label="<?php esc_attr_e( 'Increase', 'ai-zippy' ); ?>">+</button>
										</div>
									</div>
								</div>
							</div>
							<?php endif; endforeach; ?>
						</div>

						<!-- Coupon -->
						<?php if ( wc_coupons_enabled() ) : ?>
						<div class="az-checkout__coupon">
							<div class="az-checkout__coupon-row">
								<input type="text" name="az_coupon_code" class="az-checkout__coupon-input" placeholder="<?php esc_attr_e( 'Coupon code', 'ai-zippy' ); ?>" id="az-coupon-code" />
								<button type="button" class="az-checkout__coupon-btn" id="az-apply-coupon"><?php esc_html_e( 'Apply', 'ai-zippy' ); ?></button>
							</div>
							<div class="az-checkout__coupon-msg" id="az-coupon-msg"></div>
						</div>
						<?php endif; ?>

						<div class="az-checkout__totals" id="az-checkout-totals">
							<?php \AiZippy\Checkout\CheckoutAssets::renderTotals(); ?>
						</div>

						<!-- Payment & Place Order -->
						<?php do_action( 'woocommerce_checkout_before_order_review' ); ?>
						<div id="order_review" class="woocommerce-checkout-review-order">
							<?php do_action( 'woocommerce_checkout_order_review' ); ?>
						</div>
						<?php do_action( 'woocommerce_checkout_after_order_review' ); ?>
					</div>
				</div>
			</div>

		</div>

	<?php endif; ?>

</form>

<?php do_action( 'woocommerce_after_checkout_form', $checkout ); ?>

<script>
(function() {
	// ---- Coupon: apply + remove (AJAX, no reload) ----
	var btn = document.getElementById('az-apply-coupon');
	var input = document.getElementById('az-coupon-code');
	var msg = document.getElementById('az-coupon-msg');
	var totalsEl = document.getElementById('az-checkout-totals');
	if (!btn || !input) return;

	function setMsg(text, type) {
		msg.textContent = text || '';
		msg.classList.remove('is-success', 'is-error');
		if (text && type) msg.classList.add('is-' + type);
	}

	// Re-render the totals block from the server so the new
	// coupon row + recalculated total appear without a reload.
	function refreshTotals(cb) {
		jQuery.ajax({
			type: 'POST',
			url: '<?php echo esc_js( admin_url( 'admin-ajax.php' ) ); ?>',
			data: { action: 'az_get_checkout_totals' },
			success: function(res) {
				if (res && res.success && res.data && totalsEl) {
					totalsEl.innerHTML = res.data.html;
				}
				// Also nudge WC's order_review (payment + grand total) to refresh
				jQuery(document.body).trigger('update_checkout');
				if (typeof cb === 'function') cb();
			},
			error: function() {
				if (typeof cb === 'function') cb();
			}
		});
	}

	function applyCoupon() {
		var code = input.value.trim();
		if (!code) return;

		setMsg('', null);
		btn.disabled = true;
		btn.textContent = '<?php echo esc_js( __( 'Applying…', 'ai-zippy' ) ); ?>';

		jQuery.ajax({
			type: 'POST',
			url: '<?php echo esc_js( admin_url( 'admin-ajax.php' ) ); ?>',
			data: {
				action: 'woocommerce_apply_coupon',
				security: '<?php echo esc_js( wp_create_nonce( 'apply-coupon' ) ); ?>',
				coupon_code: code,
			},
			success: function(response) {
				var html = String(response || '');
				var temp = document.createElement('div');
				temp.innerHTML = html;

				// WC may render either an ERROR or SUCCESS notice. Look up
				// each kind by its specific class — never by [role="alert"]
				// (success banners use that role too, so it doesn't disambiguate).
				//   classic error:   <ul class="woocommerce-error"><li>…</li></ul>
				//   classic success: <div class="woocommerce-message">…</div>
				//   blocks  error:   <div class="wc-block-components-notice-banner is-error">
				//   blocks  success: <div class="wc-block-components-notice-banner is-success">
				var errorEl   = temp.querySelector('.woocommerce-error, .wc-block-components-notice-banner.is-error');
				var successEl = temp.querySelector('.woocommerce-message, .wc-block-components-notice-banner.is-success');

				// Trust the explicit markers. If neither exists, fall back to
				// "success if non-empty response with no error class" — WC's
				// classic flow sometimes returns just the message text.
				var ok = !errorEl && (successEl || temp.textContent.trim() !== '');

				var noticeEl = errorEl || successEl || temp.querySelector('li, p');
				var text     = noticeEl ? noticeEl.textContent.trim().replace(/\s+/g, ' ') : '';

				if (ok) {
					input.value = '';
					setMsg(text || '<?php echo esc_js( __( 'Coupon applied!', 'ai-zippy' ) ); ?>', 'success');
					refreshTotals();
				} else {
					setMsg(text || '<?php echo esc_js( __( 'Coupon could not be applied.', 'ai-zippy' ) ); ?>', 'error');
				}
			},
			error: function() {
				setMsg('<?php echo esc_js( __( 'Network error. Please try again.', 'ai-zippy' ) ); ?>', 'error');
			},
			complete: function() {
				btn.disabled = false;
				btn.textContent = '<?php echo esc_js( __( 'Apply', 'ai-zippy' ) ); ?>';
			}
		});
	}

	btn.addEventListener('click', applyCoupon);
	input.addEventListener('keydown', function(e) {
		if (e.key === 'Enter') { e.preventDefault(); applyCoupon(); }
	});

	// Intercept the remove-coupon link so it goes through AJAX too
	// (delegated, since the link is re-rendered on each refresh).
	if (totalsEl) {
		totalsEl.addEventListener('click', function(e) {
			var link = e.target.closest('[data-az-remove-coupon]');
			if (!link) return;
			e.preventDefault();
			var code = link.getAttribute('data-az-remove-coupon');
			link.style.opacity = '0.5';

			jQuery.ajax({
				type: 'POST',
				url: '<?php echo esc_js( admin_url( 'admin-ajax.php' ) ); ?>',
				data: {
					action: 'woocommerce_remove_coupon',
					security: '<?php echo esc_js( wp_create_nonce( 'remove-coupon' ) ); ?>',
					coupon: code,
				},
				success: function() {
					setMsg('', null);
					refreshTotals();
				},
				error: function() {
					link.style.opacity = '';
					setMsg('<?php echo esc_js( __( 'Could not remove coupon.', 'ai-zippy' ) ); ?>', 'error');
				}
			});
		});
	}
})();

// ---- Quantity controls ----
(function() {
	var $ = jQuery;
	var updating = false;

	function updateQty(cartKey, newQty) {
		if (updating) return;
		updating = true;

		// Dim the item
		var item = document.querySelector('[data-cart-key="' + cartKey + '"]');
		if (item) item.style.opacity = '0.5';

		$.ajax({
			type: 'POST',
			url: '<?php echo esc_js( admin_url( 'admin-ajax.php' ) ); ?>',
			data: {
				action: 'az_update_checkout_qty',
				cart_key: cartKey,
				quantity: newQty,
				security: '<?php echo esc_js( wp_create_nonce( 'az-checkout-qty' ) ); ?>',
			},
			success: function() {
				// Reload the page to reflect updated cart
				// (WC checkout fragments don't cover our custom sidebar)
				location.reload();
			},
			error: function() {
				if (item) item.style.opacity = '1';
				updating = false;
			}
		});
	}

	document.addEventListener('click', function(e) {
		var item = e.target.closest('.az-checkout__item');
		if (!item) return;
		var key = item.dataset.cartKey;
		if (!key) return;

		var qtyEl = item.querySelector('.az-checkout__qty-val');
		var qty = parseInt(qtyEl.textContent, 10);

		if (e.target.closest('.az-checkout__qty-btn--minus')) {
			// At qty=1, minus removes the item (matches the React checkout behavior).
			// Confirm first so the click can't accidentally drop the line.
			if (qty > 1) {
				updateQty(key, qty - 1);
			} else if (confirm('<?php echo esc_js( __( 'Remove this item from your order?', 'ai-zippy' ) ); ?>')) {
				updateQty(key, 0);
			}
		} else if (e.target.closest('.az-checkout__qty-btn--plus')) {
			updateQty(key, qty + 1);
		} else if (e.target.closest('.az-checkout__qty-remove')) {
			if (confirm('<?php echo esc_js( __( 'Remove this item?', 'ai-zippy' ) ); ?>')) {
				updateQty(key, 0);
			}
		}
	});
})();
</script>
