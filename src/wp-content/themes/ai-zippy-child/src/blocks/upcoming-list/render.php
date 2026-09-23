<?php

/**
 * Server-side render — `ai-zippy/upcoming-list`.
 *
 * LITERAL CLONE of pricemrcopper_final_mockup.html markup L875-911
 * (`<section class="wrap">…`). The section element itself carries `wrap`, so
 * that class travels on the block wrapper (films-list precedent); event
 * card + ticket-modal styles live in src/scss/sections/_upcoming-list.scss
 * (mockup <style> L236-237 + L242-248 + L249-274 + L277-290 + this block's
 * rows of the 900px tier, L296); `.modal-overlay`/`.modal`/`@keyframes pop`
 * already live in _components.scss.
 *
 * Authoring layer stripped (block-map §6.2 / D5): every in-place editing
 * attribute, the video's authoring class + authoring title attribute, the
 * looping-video overlay hint span, and the "+ Add another event" toolbar
 * button. The CTA `.soft` band at L913-923 is NOT part of
 * this block — it is the shared cta-banner's upcoming instance (S5).
 *
 * P8-D1: the #ticketModal markup (mockup L1021-1065) renders as the last
 * child INSIDE this section — the wrapper is the `<section>` itself, and
 * `.modal-overlay` is display:none + position:fixed, so it has zero layout
 * impact (films-list filmModal precedent).
 * P8-D2: `data-checkout-url=""` stays hardcoded verbatim on every event
 * card — the shipped checkout ends in the order-summary panel + mailto;
 * per-event real checkout URLs are a future content decision (BUILD-STATE
 * open issue; the real-payment branch in view.js handles them already).
 * P8-D3: every text field in L875-911 and L1021-1065 is plain (no inline
 * markup) → all of them render through `esc_html()`; no kses helper and
 * no function_exists guard (events-process precedent).
 * P8-D4: the teaser video keeps `autoplay loop muted playsinline` + the
 * placeholder poster verbatim and no `src` — a looping placeholder until
 * the member replaces it; the poster resolves like every image through
 * the videoPoster{Id,Url,File} triple (films-list pattern).
 * P8-D5: "Tickets"/"per person", the `.buy-ticket` label "Purchase
 * Tickets", and the whole modal chrome (incl. the "Event"/"Date" mirror —
 * the modal is invisible without JS and always overwritten by
 * openTicketModal() — and `#tmEmail href="#"`) stay hardcoded markup: the
 * block-map §2 attribute shape omits them and B7 overwrites `#tmEmail`'s
 * href at checkout, so the link-conversion map (§3) does not apply
 * (films-list "Watch Now" precedent).
 * P8-D6: `id="eventsGrid"` stays verbatim (only the dropped clone
 * machinery referenced it); all modal ids stay verbatim likewise.
 *
 * B5-B9 live in this block's view.js (block-map §2). No-JS default is
 * already correct: `.modal-overlay{display:none}` (_components.scss) and
 * `#tmPanel`/`#tmErr` are hidden by their own CSS. Heading hierarchy
 * h2 → h3 → modal h3/h4/h5 preserved (ui-spec §11).
 *
 * @var array    $attributes Block attributes.
 * @var string   $content    Block inner content (unused — `supports.html:false`).
 * @var WP_Block $block      Block instance.
 */

defined('ABSPATH') || exit;

$eyebrow = (string) ($attributes['eyebrow'] ?? '');
$title   = (string) ($attributes['title'] ?? '');
$sub     = (string) ($attributes['sub'] ?? '');
$events  = (array) ($attributes['events'] ?? []);

$wrapper_attributes = get_block_wrapper_attributes(['class' => 'wrap']);
?>
<section <?php echo $wrapper_attributes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- get_block_wrapper_attributes() escapes internally. ?>>
	<span class="eyebrow"><?php echo esc_html($eyebrow); ?></span>
	<h2 class="section-title"><?php echo esc_html($title); ?></h2>
	<p class="section-sub"><?php echo esc_html($sub); ?></p>

	<div class="events-grid" id="eventsGrid">
		<?php foreach ($events as $event) : ?>
			<?php
			$badge         = (string) ($event['badge'] ?? '');
			$day           = (string) ($event['day'] ?? '');
			$month         = (string) ($event['month'] ?? '');
			$event_title   = (string) ($event['title'] ?? '');
			$desc          = (string) ($event['desc'] ?? '');
			$meta          = (array) ($event['meta'] ?? []);
			$price         = (string) ($event['price'] ?? '');
			$poster_url    = (string) ($event['videoPosterUrl'] ?? '');
			$poster_file   = (string) ($event['videoPosterFile'] ?? '');
			$poster_src    = !empty($poster_url) ? $poster_url : ai_zippy_child_img($poster_file);
			?>
			<article class="event-card" data-checkout-url="">
				<div class="event-media">
					<video autoplay="" loop="" muted="" playsinline="" poster="<?php echo esc_url($poster_src); ?>"></video>
					<span class="event-badge"><?php echo esc_html($badge); ?></span>
				</div>
				<div class="event-body">
					<div class="event-date">
						<span class="d"><?php echo esc_html($day); ?></span>
						<span class="m"><?php echo esc_html($month); ?></span>
					</div>
					<div class="event-info">
						<h3><?php echo esc_html($event_title); ?></h3>
						<p><?php echo esc_html($desc); ?></p>
						<div class="event-meta">
							<?php foreach ($meta as $m) : ?>
							<span><?php echo esc_html((string) $m); ?></span>
							<?php endforeach; ?>
						</div>
						<div class="ticket-line">
							<span>Tickets</span>
							<span class="tprice"><?php echo esc_html($price); ?></span>
							<span>per person</span>
						</div>
						<button class="btn btn-primary buy-ticket">Purchase Tickets</button>
					</div>
				</div>
			</article>
		<?php endforeach; ?>
	</div>

	<div class="modal-overlay" id="ticketModal" role="dialog" aria-modal="true" aria-labelledby="tmTitle">
		<div class="modal">
			<div class="modal-head">
				<h3 id="tmTitle">Event</h3>
				<p id="tmDate">Date</p>
				<button class="modal-close" id="tmClose" aria-label="Close">✕</button>
			</div>
			<div class="modal-body">
				<h4>How many tickets?</h4>
				<div class="qty-stepper">
					<button type="button" id="tmMinus" aria-label="Fewer tickets">−</button>
					<input type="number" id="tmQty" value="1" min="1" max="20" aria-label="Number of tickets">
					<button type="button" id="tmPlus" aria-label="More tickets">+</button>
				</div>

				<div class="modal-sep"></div>

				<h4>Attendee names</h4>
				<div id="attendeeList"></div>

				<div class="modal-sep"></div>

				<h4>Order summary</h4>
				<div class="summary-row"><span id="tmLineLabel">1 × ticket</span><span id="tmSubtotal">$25.00</span></div>
				<div class="summary-row"><span>Booking fee</span><span id="tmFee">$0.00</span></div>
				<div class="summary-total"><span class="lbl">Total</span><span class="amt" id="tmTotal">$25.00</span></div>

				<div class="err-msg" id="tmErr"></div>

				<div class="checkout-panel" id="tmPanel">
					<h5>Order summary ready</h5>
					<p style="font-size:.85rem;color:var(--ink-soft)">No payment provider is connected yet, so here are the order details. Send them to us and we’ll follow up with a payment link.</p>
					<pre id="tmOrderText"></pre>
					<div class="row">
						<button class="btn btn-primary" id="tmCopy" type="button">Copy details</button>
						<a class="btn btn-ghost" id="tmEmail" href="#">Email this order</a>
					</div>
				</div>
			</div>
			<div class="modal-foot">
				<button class="btn btn-primary" id="tmCheckout" type="button">Proceed To Checkout</button>
				<p class="modal-msg">Secure payment is handled by our payment provider.</p>
			</div>
		</div>
	</div>
</section>
