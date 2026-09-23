// B5–B9 — ticket purchase modal (pricemrcopper_final_mockup.html script
// L1280-1452). Ported verbatim — constants, helpers and listeners; nothing
// inside that range is authoring machinery. `data-checkout-url` ships empty
// on every card (P8-D2), so the real-payment branch never fires and the
// checkout ends in the order-summary panel + mailto. The `.buy-ticket`
// delegation stays at document level exactly as in the mockup (L1380).
// Hardcoded modal ids assume one upcoming-list per page (films-list
// precedent). No-JS default is already correct:
// `.modal-overlay{display:none}` (_components.scss).
document.addEventListener('DOMContentLoaded', () => {
	const CHECKOUT_URL = '';          // e.g. 'https://buy.stripe.com/xxxxxxxx'
	const BOOKING_FEE  = 0;           // flat fee added to every order, e.g. 1.5
	const CURRENCY     = '$';

	const tm = {
		modal:    document.getElementById('ticketModal'),
		title:    document.getElementById('tmTitle'),
		date:     document.getElementById('tmDate'),
		qty:      document.getElementById('tmQty'),
		minus:    document.getElementById('tmMinus'),
		plus:     document.getElementById('tmPlus'),
		list:     document.getElementById('attendeeList'),
		lineLbl:  document.getElementById('tmLineLabel'),
		subtotal: document.getElementById('tmSubtotal'),
		fee:      document.getElementById('tmFee'),
		total:    document.getElementById('tmTotal'),
		err:      document.getElementById('tmErr'),
		panel:    document.getElementById('tmPanel'),
		orderTxt: document.getElementById('tmOrderText'),
		copyBtn:  document.getElementById('tmCopy'),
		emailBtn: document.getElementById('tmEmail'),
		checkout: document.getElementById('tmCheckout'),
		close:    document.getElementById('tmClose')
	};
	if (!tm.modal) return;
	let tmCtx = { price: 0, name: '', date: '', url: '' };
	const money = n => CURRENCY + n.toFixed(2);

	/* price is read from the editable text on the card, so edits apply live */
	function readPrice(card){
		const el = card.querySelector('.tprice');
		const n = parseFloat((el ? el.textContent : '').replace(/[^0-9.]/g, ''));
		return isFinite(n) && n > 0 ? n : 0;
	}

	function buildAttendeeRows(){
		const want = clampQty(tm.qty.value);
		const rows = tm.list.children;
		while(rows.length > want) tm.list.removeChild(tm.list.lastElementChild);
		for(let i = rows.length; i < want; i++){
			const row = document.createElement('div');
			row.className = 'attendee-row';
			row.innerHTML = '<span>' + (i+1) + '</span>' +
				'<input type="text" placeholder="Attendee ' + (i+1) + ' full name" autocomplete="name">';
			tm.list.appendChild(row);
		}
		[...tm.list.children].forEach((r, i) => {
			r.querySelector('span').textContent = i + 1;
			r.querySelector('input').placeholder = 'Attendee ' + (i+1) + ' full name';
		});
	}

	function clampQty(v){
		let n = parseInt(v, 10);
		if(!isFinite(n)) n = 1;
		return Math.min(20, Math.max(1, n));
	}

	function updateTotals(){
		const q = clampQty(tm.qty.value);
		const sub = q * tmCtx.price;
		const total = sub + BOOKING_FEE;
		tm.lineLbl.textContent = q + ' \u00D7 ticket @ ' + money(tmCtx.price);
		tm.subtotal.textContent = money(sub);
		tm.fee.textContent = money(BOOKING_FEE);
		tm.total.textContent = money(total);
		tm.minus.disabled = q <= 1;
		tm.plus.disabled  = q >= 20;
		return total;
	}

	function refreshModal(){
		tm.qty.value = clampQty(tm.qty.value);
		buildAttendeeRows();
		updateTotals();
		tm.err.style.display = 'none';
		tm.panel.style.display = 'none';
	}

	function openTicketModal(card){
		tmCtx.price = readPrice(card);
		tmCtx.name  = (card.querySelector('.event-info h3') || {}).textContent || 'Event';
		const d = card.querySelector('.event-date .d'), m = card.querySelector('.event-date .m');
		tmCtx.date  = ((d ? d.textContent : '') + ' ' + (m ? m.textContent : '')).trim();
		tmCtx.url   = card.dataset.checkoutUrl || CHECKOUT_URL;
		tm.title.textContent = tmCtx.name.trim();
		tm.date.textContent  = tmCtx.date;
		tm.qty.value = 1;
		tm.list.innerHTML = '';
		refreshModal();
		tm.modal.classList.add('open');
		document.body.style.overflow = 'hidden';
		setTimeout(() => { const f = tm.list.querySelector('input'); if(f) f.focus(); }, 80);
	}
	function closeTicketModal(){
		tm.modal.classList.remove('open');
		document.body.style.overflow = '';
		tm.list.innerHTML = '';          // keeps exported HTML clean
		tm.panel.style.display = 'none';
	}

	document.addEventListener('click', e => {
		const buy = e.target.closest('.buy-ticket');
		if(buy){ e.preventDefault(); openTicketModal(buy.closest('.event-card')); }
	});
	tm.close.addEventListener('click', closeTicketModal);
	tm.modal.addEventListener('click', e => { if(e.target === tm.modal) closeTicketModal(); });
	document.addEventListener('keydown', e => {
		if(e.key === 'Escape' && tm.modal.classList.contains('open')) closeTicketModal();
	});
	tm.minus.addEventListener('click', () => { tm.qty.value = clampQty(tm.qty.value) - 1; refreshModal(); });
	tm.plus .addEventListener('click', () => { tm.qty.value = clampQty(tm.qty.value) + 1; refreshModal(); });
	tm.qty  .addEventListener('input', refreshModal);

	function collectOrder(){
		const names = [...tm.list.querySelectorAll('input')].map(i => i.value.trim());
		const missing = names.some(n => !n);
		[...tm.list.querySelectorAll('input')].forEach(i => i.classList.toggle('err', !i.value.trim()));
		if(missing) return null;
		const q = clampQty(tm.qty.value);
		return {
			event: tmCtx.name.trim(), date: tmCtx.date, qty: q,
			unit: tmCtx.price, fee: BOOKING_FEE, total: q * tmCtx.price + BOOKING_FEE,
			names: names,
			ref: 'PMC-' + Date.now().toString(36).toUpperCase()
		};
	}

	tm.checkout.addEventListener('click', () => {
		const order = collectOrder();
		if(!order){
			tm.err.textContent = 'Please enter a name for every attendee before checking out.';
			tm.err.style.display = 'block';
			return;
		}
		tm.err.style.display = 'none';

		if(tmCtx.url){
			/* Real payment: hand off to the payment provider's hosted checkout. */
			const sep = tmCtx.url.includes('?') ? '&' : '?';
			const url = tmCtx.url + sep + 'quantity=' + order.qty +
				'&client_reference_id=' + encodeURIComponent(order.ref);
			try{ sessionStorage.setItem(order.ref, JSON.stringify(order)); }catch(err){}
			window.location.href = url;
			return;
		}

		/* No provider connected yet — surface the order so it isn't lost. */
		const lines = [
			'ORDER REF: ' + order.ref,
			'Event: ' + order.event,
			'Date: ' + order.date,
			'Tickets: ' + order.qty + ' \u00D7 ' + money(order.unit),
			'Booking fee: ' + money(order.fee),
			'TOTAL: ' + money(order.total),
			'',
			'Attendees:',
			...order.names.map((n, i) => '  ' + (i+1) + '. ' + n)
		].join('\n');
		tm.orderTxt.textContent = lines;
		tm.emailBtn.href = 'mailto:hello@pricemrcopper.com'
			+ '?subject=' + encodeURIComponent('Ticket order ' + order.ref + ' \u2014 ' + order.event)
			+ '&body=' + encodeURIComponent(lines);
		tm.panel.style.display = 'block';
		tm.panel.scrollIntoView({behavior:'smooth', block:'nearest'});
	});

	tm.copyBtn.addEventListener('click', () => {
		const txt = tm.orderTxt.textContent;
		const done = () => { tm.copyBtn.textContent = 'Copied \u2713';
			setTimeout(() => { tm.copyBtn.textContent = 'Copy details'; }, 1800); };
		if(navigator.clipboard) navigator.clipboard.writeText(txt).then(done).catch(done);
		else done();
	});
});
