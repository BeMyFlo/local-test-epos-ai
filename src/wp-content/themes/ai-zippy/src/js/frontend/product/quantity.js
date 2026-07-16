// =============================================================================
// Quantity stepper — ± buttons next to <input type="number">
// =============================================================================

export function initQuantity() {
	document.querySelectorAll(".zp-qty").forEach((wrap) => {
		const input = wrap.querySelector(".zp-qty__input");
		const minus = wrap.querySelector(".zp-qty__btn--minus");
		const plus  = wrap.querySelector(".zp-qty__btn--plus");
		if (!input || !minus || !plus) return;

		const step = parseFloat(input.step)  || 1;
		const min  = input.hasAttribute("min") ? parseFloat(input.min) : 0;
		const max  = input.hasAttribute("max") ? parseFloat(input.max) : Infinity;

		const clamp = (v) => Math.max(min, Math.min(max, v));
		const get   = () => parseFloat(input.value) || 0;

		minus.addEventListener("click", () => {
			input.value = clamp(get() - step);
			input.dispatchEvent(new Event("change", { bubbles: true }));
		});
		plus.addEventListener("click", () => {
			input.value = clamp(get() + step);
			input.dispatchEvent(new Event("change", { bubbles: true }));
		});
	});
}
