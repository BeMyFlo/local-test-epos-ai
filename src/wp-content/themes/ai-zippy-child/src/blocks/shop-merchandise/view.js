// B1–B4 — shop grid order buttons + custom-order box
// (pricemrcopper_final_mockup.html script L1144-1152, L1219-1249). Ported
// verbatim minus the debug order-placed log line and the `name` variable
// feeding it (P5-D5 — no rendered behavior). No-JS defaults are
// already correct in the markup: #lockMsg visible, #unlockMsg/#customOk
// hidden, .custom-upload locked, #customPreview hidden — view.js does not
// run in the editor, so the canvas shows the same defaults.
document.addEventListener('DOMContentLoaded', () => {
	const grid = document.getElementById('shopGrid');
	if (grid) {
		grid.addEventListener('click', (e) => {
			const btn = e.target.closest('.order-btn');
			if (!btn) return;
			const card = btn.closest('.product');
			const qty = card.querySelector('input').value;
			btn.textContent = `Added ×${qty} ✓`;
			setTimeout(() => { btn.textContent = 'Order'; }, 1800);
		});
	}

	const customQty = document.getElementById('customQty');
	const customUpload = document.getElementById('customUpload');
	const lockMsg = document.getElementById('lockMsg');
	const unlockMsg = document.getElementById('unlockMsg');
	if (customQty && customUpload && lockMsg && unlockMsg) {
		const updateCustomLock = () => {
			const qty = parseInt(customQty.value, 10) || 0;
			const unlocked = qty >= 50;
			customUpload.classList.toggle('locked', !unlocked);
			lockMsg.style.display = unlocked ? 'none' : 'flex';
			unlockMsg.style.display = unlocked ? 'flex' : 'none';
		};
		customQty.addEventListener('input', updateCustomLock);
		updateCustomLock();
	}

	const imgInput = document.getElementById('customImgInput');
	if (imgInput) {
		imgInput.addEventListener('change', function () {
			const file = this.files[0];
			if (!file) return;
			const reader = new FileReader();
			reader.onload = (ev) => {
				const prev = document.getElementById('customPreview');
				prev.src = ev.target.result;
				prev.style.display = 'block';
			};
			reader.readAsDataURL(file);
		});
	}

	const submit = document.getElementById('customSubmit');
	if (submit) {
		submit.addEventListener('click', (e) => {
			e.preventDefault();
			document.getElementById('customOk').style.display = 'flex';
			setTimeout(() => { document.getElementById('customOk').style.display = 'none'; }, 4000);
		});
	}
});
