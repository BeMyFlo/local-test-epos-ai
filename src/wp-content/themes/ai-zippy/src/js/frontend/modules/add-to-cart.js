/**
 * AJAX Add to Cart — intercepts add-to-cart clicks across the site.
 *
 * Match policy:
 *   - Buttons/links with class `.az-add-to-cart` (preferred — explicit opt-in)
 *   - Anchors with `?add-to-cart=ID` in the URL (legacy WC fallback)
 *
 * Supports:
 *   - Shop filter cards + Product Showcase block (.sf__card-btn.az-add-to-cart)
 *   - Single product page summary buttons (.zp-add-btn.az-add-to-cart)
 *     Reads quantity from the form's quantity input + variation_id from
 *     a sibling `.variation_id` hidden input on variable products.
 */

import { addToCart } from "./cart-api.js";

export function initAddToCart() {
	document.addEventListener("click", handleClick);
}

async function handleClick(e) {
	const btn = e.target.closest(
		'.az-add-to-cart, a[href*="add-to-cart="]',
	);
	if (!btn) return;

	// Skip the "Buy Now" button — it intentionally redirects to /checkout
	// via WC's server-side ?add-to-cart= handler. Don't intercept it.
	if (btn.matches("[data-buy-now]") || btn.closest("[data-buy-now]")) return;

	e.preventDefault();

	// Prevent click when busy or WC has marked the variation form as needing selection.
	// WC manages the .disabled class (not the disabled attribute) on variation buttons.
	if (
		btn.classList.contains("is-loading") ||
		btn.classList.contains("disabled") ||
		btn.disabled
	) return;

	// Determine product ID + quantity + variation
	const { productId, quantity, variation, error } = resolveCartPayload(btn);

	if (error) {
		showToast(error, "error");
		return;
	}
	if (!productId) return;

	const originalText = btn.innerHTML;
	btn.classList.add("is-loading");
	btn.innerHTML = spinnerSvg() + " Adding...";

	try {
		const cart = await addToCart(Number(productId), quantity, variation);

		// Success state
		btn.classList.remove("is-loading");
		btn.classList.add("is-added");
		btn.innerHTML = checkSvg() + " Added!";

		// Update mini cart
		updateMiniCart(cart);

		// Show toast
		showToast("Product added to cart", "success");

		// Reset button after 2s
		setTimeout(() => {
			btn.classList.remove("is-added");
			btn.innerHTML = originalText;
		}, 2000);
	} catch (err) {
		btn.classList.remove("is-loading");
		btn.innerHTML = originalText;
		showToast(err.message || "Failed to add to cart", "error");
	}
}

/**
 * Resolve the cart payload from a clicked element. Looks at:
 *   - data-product-id on the button itself
 *   - ?add-to-cart= URL param (anchor links)
 *   - Surrounding .variations_form for variation_id (variable products)
 *   - Surrounding form for an input[name="quantity"] (single product page)
 */
function resolveCartPayload(btn) {
	let productId = btn.dataset.productId;
	let variation = [];

	// Anchor with ?add-to-cart=ID
	if (!productId && btn.tagName === "A" && btn.href) {
		try {
			const url = new URL(btn.href, window.location.origin);
			productId = url.searchParams.get("add-to-cart");
		} catch {
			// invalid URL, fall through
		}
	}

	// Variation forms — Store API expects the PARENT product id plus an
	// `attributes` array. Sending the variation_id alone causes:
	//   "Missing attributes for variable product."
	const variationsForm = btn.closest(".variations_form");
	if (variationsForm) {
		const vidInput = variationsForm.querySelector("input.variation_id");
		const vid = vidInput ? Number(vidInput.value) : 0;
		if (!vid || vid === 0) {
			return { error: "Please select all options before adding to cart." };
		}

		// WC tags the form with the parent product id.
		const parentId = Number(variationsForm.dataset.product_id || variationsForm.getAttribute("data-product_id"));
		if (parentId > 0) {
			productId = parentId;
		}

		// Collect attribute pairs from `[name^="attribute_"]` form fields.
		// Our swatch UI mirrors values into hidden <select> elements (not
		// hidden <input>) so WC's variation script can listen on them. We also
		// include any plain <select> rows (size, etc.) and any hidden inputs
		// some WC variations themes still use.
		variationsForm.querySelectorAll('[name^="attribute_"]').forEach((field) => {
			if (!field.name || !field.value) return;
			variation.push({
				attribute: field.name.replace(/^attribute_/, ""),
				value: field.value,
			});
		});
	}

	// Quantity from a nearby input[name="quantity"]
	let quantity = 1;
	const wrap = btn.closest("form, .zp-summary");
	if (wrap) {
		const qtyInput = wrap.querySelector('input[name="quantity"]');
		if (qtyInput) {
			const parsed = parseInt(qtyInput.value, 10);
			if (Number.isFinite(parsed) && parsed > 0) {
				quantity = parsed;
			}
		}
	}

	return { productId, quantity, variation };
}

/**
 * Update the WooCommerce mini cart after adding an item.
 *
 * The WC Blocks mini cart subscribes to wp.data "wc/store/cart".
 * We push the new cart data into that store so the badge/amount re-render,
 * then fire the WC blocks event so the mini cart drawer logic runs too.
 */
function updateMiniCart(cart) {
	// 1. Push cart response into the WC blocks cart store.
	//    The mini cart subscribes to getCartData() and re-renders on change.
	try {
		if (typeof wp !== "undefined" && wp.data) {
			const store = wp.data.dispatch("wc/store/cart");
			if (store?.receiveCart) {
				store.receiveCart(cart);
			}
		}
	} catch { /* wp.data not ready */ }

	// 2. Fire the WC blocks event with the same shape WC uses internally.
	//    preserveCartData:true tells the mini cart not to re-fetch (we just set it).
	document.body.dispatchEvent(
		new CustomEvent("wc-blocks_added_to_cart", {
			bubbles: true,
			cancelable: true,
			detail: { preserveCartData: true },
		})
	);

	// 3. jQuery fallback for classic WC themes/plugins
	if (typeof jQuery !== "undefined") {
		jQuery(document.body).trigger("added_to_cart");
	}
}

/**
 * Show a toast notification.
 */
function showToast(message, type = "success") {
	// Remove existing toasts
	document
		.querySelectorAll(".az-toast")
		.forEach((t) => t.remove());

	const toast = document.createElement("div");
	toast.className = `az-toast az-toast--${type}`;
	toast.innerHTML = `
		<span>${message}</span>
		<button class="az-toast__close" aria-label="Close">&times;</button>
	`;

	document.body.appendChild(toast);

	// Close on click
	toast.querySelector(".az-toast__close").addEventListener("click", () => {
		toast.classList.add("is-closing");
		setTimeout(() => toast.remove(), 300);
	});

	// Auto-dismiss
	setTimeout(() => {
		if (toast.parentNode) {
			toast.classList.add("is-closing");
			setTimeout(() => toast.remove(), 300);
		}
	}, 4000);
}

function spinnerSvg() {
	return '<svg class="az-spinner" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10" opacity="0.25"/><path d="M12 2a10 10 0 0 1 10 10" stroke-linecap="round"/></svg>';
}

function checkSvg() {
	return '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"><polyline points="20 6 9 17 4 12"/></svg>';
}
