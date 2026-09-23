// B10 + B11 — film lightbox (pricemrcopper_final_mockup.html script
// L1499-1568). Ported verbatim minus the dropped authoring machinery
// (initFilmPosters, markFilmHasVideo, the hover-tools guard — block-map
// §4.1/§4.2). The poster/Watch delegation stays at document level exactly
// as in the mockup (L1562). No-JS default is already correct:
// `.modal-overlay{display:none}` (_components.scss) — the modal never shows
// without JS, in the editor and frontend alike.
document.addEventListener('DOMContentLoaded', () => {
	const fm = {
		modal: document.getElementById('filmModal'),
		video: document.getElementById('fmVideo'),
		empty: document.getElementById('fmEmpty'),
		title: document.getElementById('fmTitle'),
		meta:  document.getElementById('fmMeta'),
		desc:  document.getElementById('fmDesc'),
		close: document.getElementById('fmClose')
	};
	if (!fm.modal) return;

	function openFilmModal(card){
		const h3   = card.querySelector('.film-body h3');
		const desc = card.querySelector('.film-body p');
		fm.title.textContent = h3 ? h3.textContent.trim() : 'Film';
		fm.desc.textContent  = desc ? desc.textContent.trim() : '';
		fm.meta.innerHTML = '';
		card.querySelectorAll('.film-meta span').forEach(s => {
			if(s.classList.contains('no-video-flag')) return;    // status chip, not a film detail
			const el = document.createElement('span'); el.textContent = s.textContent.trim(); fm.meta.appendChild(el);
		});
		const rt = card.querySelector('.film-runtime');
		if(rt){ const el = document.createElement('span'); el.textContent = rt.textContent.trim(); fm.meta.appendChild(el); }

		const srcUrl = card.dataset.video || '';
		if(srcUrl){
			fm.video.src = srcUrl;
			fm.video.style.display = 'block';
			fm.empty.style.display = 'none';
			fm.video.load();
			fm.video.play().catch(()=>{});
		} else {
			fm.video.removeAttribute('src');
			fm.video.style.display = 'none';
			fm.empty.style.display = 'grid';
		}
		fm.modal.classList.add('open');
		document.body.style.overflow = 'hidden';
	}
	function closeFilmModal(){
		fm.video.pause();
		fm.video.removeAttribute('src');
		fm.video.load();
		fm.modal.classList.remove('open');
		document.body.style.overflow = '';
	}
	fm.close.addEventListener('click', closeFilmModal);
	fm.modal.addEventListener('click', e => { if(e.target === fm.modal) closeFilmModal(); });
	document.addEventListener('keydown', e => {
		if(e.key === 'Escape' && fm.modal.classList.contains('open')) closeFilmModal();
	});

	/* open on poster click or Watch Now */
	document.addEventListener('click', e => {
		const trigger = e.target.closest('.film-poster, .watch-btn');
		if(!trigger) return;
		e.preventDefault();
		openFilmModal(trigger.closest('.film-card'));
	});
});
