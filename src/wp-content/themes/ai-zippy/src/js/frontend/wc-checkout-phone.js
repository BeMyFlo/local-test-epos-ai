// =============================================================================
// Classic WooCommerce checkout — small enhancements
// =============================================================================
// Standalone Vite entry (not part of theme.js) loaded only on the classic
// checkout template (form-checkout.php).
//
// 1. Phone country-code picker: wraps #billing_phone with intl-tel-input so
//    the field gets a flag/dial-code dropdown, defaulting to SG. It's UI
//    only — #billing_phone still submits whatever the visible national-
//    number input holds, unchanged from before this was added. Deliberately
//    not wired up to write the full E.164 number back into that same input:
//    the field's value is intl-tel-input's own display state (a national
//    number), and overwriting it with "+65912345678" fights the library's
//    own re-render, producing a mangled duplicate (e.g. "+65+651231 23").
//    Getting a real E.164 value to the server the right way means a
//    separate hidden input under a different name (intl-tel-input's
//    `hiddenInputs` option) — skipped for now since WooCommerce's
//    required-field check keys off the `billing_phone` POST name, so
//    renaming what #billing_phone submits needs a matching server-side
//    change too.
//
// 2. "Ship to a different address?" toggle: WooCommerce's own checkout.js
//    normally shows/hides .shipping_address on this checkbox, but that
//    script gets dequeued whenever WC_Blocks' Checkout block type renders
//    anywhere on the request (CheckoutShortcode::render() always renders
//    `wp:woocommerce/checkout` first to probe for block output, even when
//    the classic template is selected — see Checkout::render() in
//    woocommerce/src/Blocks/BlockTypes/Checkout.php, which unconditionally
//    dequeues wc-checkout). Handled here instead so the toggle doesn't
//    depend on that script surviving.
// =============================================================================

import intlTelInput from "intl-tel-input";
// The default stylesheet's background-image: url(...) is written relative to
// Vite's output root, not this bundle's own location, and 404s once served
// from the theme. The -no-assets variant skips those url()s; the flag sprite
// path is set below from a fixed theme URL instead (see wcCheckoutPhone in
// CheckoutAssets::enqueueWcCheckout()).
import "intl-tel-input/styles-no-assets";

document.addEventListener("DOMContentLoaded", () => {
	const input = document.getElementById("billing_phone");
	if (!input) return;

	if (window.wcCheckoutPhone?.flagsUrl1x) {
		document.documentElement.style.setProperty(
			"--iti-path-flags-1x",
			`url(${window.wcCheckoutPhone.flagsUrl1x})`,
		);
		document.documentElement.style.setProperty(
			"--iti-path-flags-2x",
			`url(${window.wcCheckoutPhone.flagsUrl2x})`,
		);
	}

	const iti = intlTelInput(input, {
		initialCountry: "sg",
		separateDialCode: true,
	});

	// intlTelInput keeps the input's padding-left in sync with the flag +
	// dial-code button's width via its own ResizeObserver, writing a plain
	// (non-!important) inline style every time it fires — not just once at
	// init. The theme's shared input rule sets padding with !important,
	// which normally beats a plain inline style, so without this the field
	// would always render at that shared padding regardless of what
	// intl-tel-input computes. Re-apply its own value at matching priority
	// on every style-attribute change instead of syncing once, since a plain
	// one-time re-apply would just get overwritten by the next observer tick.
	// Extra breathing room so the digits don't start flush against the dial
	// code button.
	const PADDING_BUFFER_PX = 8;

	const syncPadding = () => {
		const selector = input.closest(".iti")?.querySelector(".iti__selected-country");
		if (!selector) return;
		const width = `${selector.getBoundingClientRect().width + PADDING_BUFFER_PX}px`;
		if (input.style.getPropertyPriority("padding-left") === "important" && input.style.paddingLeft === width) {
			return;
		}
		input.style.setProperty("padding-left", width, "important");
	};
	new MutationObserver(syncPadding).observe(input, { attributes: true, attributeFilter: ["style"] });
	iti.promise.then(syncPadding);
});

document.addEventListener("DOMContentLoaded", () => {
	const checkbox = document.getElementById("ship-to-different-address-checkbox");
	const shippingAddress = document.querySelector(".shipping_address");
	if (!checkbox || !shippingAddress) return;

	// .is-open drives the collapse/expand transition (grid-template-rows,
	// see _wc-checkout.scss); toggling it on `change` instead of a plain
	// style.display swap is what makes the reveal animate.
	shippingAddress.classList.toggle("is-open", checkbox.checked);
	checkbox.addEventListener("change", () => {
		shippingAddress.classList.toggle("is-open", checkbox.checked);
	});
});
