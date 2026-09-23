// B12 — enquiry form (pricemrcopper_final_mockup.html script L1721-1726).
// No-JS default is already correct: `.form-ok{display:none}`
// (_components.scss L164) hides the banner until the handler fires; view.js
// does not run in the editor, so the editor canvas shows the same default.
document.addEventListener('DOMContentLoaded', () => {
	const form = document.getElementById('filmEnquiryForm');
	if (!form) return;
	form.addEventListener('submit', (e) => {
		e.preventDefault();
		const ok = document.getElementById('filmEnquiryOk');
		if (ok) ok.style.display = 'flex';
		form.querySelectorAll('input, textarea, select').forEach((el) => {
			if (el.type !== 'submit') el.value = '';
		});
		setTimeout(() => { if (ok) ok.style.display = 'none'; }, 4000);
	});
});
