<?php

/**
 * Server-side render — `ai-zippy/films-list`.
 *
 * LITERAL CLONE of pricemrcopper_final_mockup.html markup L497-601
 * (`<section class="wrap">…`). The section element itself carries `wrap`, so
 * that class travels on the block wrapper (film-tiers precedent); film card
 * + lightbox styles live in src/scss/sections/_films-list.scss (mockup
 * <style> L196-207 + L212-233 + the `.film-hero` row of the 900px tier,
 * L296); `.modal-overlay`/`@keyframes pop` already live in
 * _components.scss.
 *
 * Authoring layer stripped (block-map §6.2 / D5): the per-card hover tool
 * button groups, the add-another-film toolbar button, every in-place editing
 * attribute, and the §6.3 editing residue — films 4-5 keep only the real `h3`
 * title (the empty heading + inline-styled duplicate at L573/L592 are
 * dropped), films 2-3 meta arrays carry only the real values (the two empty
 * `<br>` spans at L536/L555 drop). Films 2-5 keep their `<p><br></p>`
 * descriptions verbatim → the `desc` default is "<br>".
 *
 * P4-D1: the filmModal markup (mockup L1000-1017) renders as the last child
 * INSIDE this section — the wrapper is the `<section>` itself, and
 * `.modal-overlay` is display:none + position:fixed, so it has zero layout
 * impact (sets the pattern for upcoming-list's #ticketModal).
 * P4-D2: the modal's initial content is the mockup's film-1 mirror (L1012-1014)
 * hardcoded verbatim — it is invisible without JS and always overwritten by
 * openFilmModal() on open.
 * P4-D3: `desc` renders through the guarded `films_list_inline_html()` kses
 * helper (br allowed) so the verbatim `<br>` descriptions survive editing;
 * view.js reads textContent → '' for films 2-5, matching the mockup's modal.
 * P4-D4: the "⚠ No video yet" chip is emitted only when `videoUrl` is empty —
 * the steady state of the mockup's dropped markFilmHasVideo sweep (all five
 * shipped cards carry data-video="").
 * P4-D10: `id="filmGrid"` stays verbatim on the `.film-list` div (only the
 * dropped clone machinery referenced it).
 * P4-D11: `.watch-btn` stays a `<button>` — B10 opens the modal; it is not in
 * the link-conversion map (§3).
 *
 * B10 + B11 live in this block's view.js (block-map §2). No-JS default is
 * already correct: `.modal-overlay{display:none}` (_components.scss).
 *
 * @var array    $attributes Block attributes.
 * @var string   $content    Block inner content (unused — `supports.html:false`).
 * @var WP_Block $block      Block instance.
 */

defined('ABSPATH') || exit;

if (!function_exists('films_list_inline_html')) :
	/**
	 * Minimal inline allowlist for the editable film description (P4-D3).
	 *
	 * Films 2-5 ship the mockup's verbatim "<br>" descriptions and film 1 a
	 * plain paragraph; the i/em/b/strong/br/div/span[style] set keeps all of
	 * them intact across editor round-trips.
	 *
	 * @param string $text Attribute value.
	 * @return string Sanitized HTML with div/span[style]/i/em/b/strong/br preserved.
	 */
	function films_list_inline_html(string $text): string
	{
		return wp_kses($text, [
			'i'      => [],
			'em'     => [],
			'b'      => [],
			'strong' => [],
			'br'     => [],
			'div'    => [],
			'span'   => ['style' => []],
		]);
	}
endif;

$eyebrow = (string) ($attributes['eyebrow'] ?? '');
$title   = (string) ($attributes['title'] ?? '');
$sub     = (string) ($attributes['sub'] ?? '');
$films   = (array) ($attributes['films'] ?? []);

$wrapper_attributes = get_block_wrapper_attributes(['class' => 'wrap']);
?>
<section <?php echo $wrapper_attributes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- get_block_wrapper_attributes() escapes internally. ?>>
	<span class="eyebrow"><?php echo esc_html($eyebrow); ?></span>
	<h2 class="section-title"><?php echo esc_html($title); ?></h2>
	<p class="section-sub"><?php echo esc_html($sub); ?></p>

	<div class="film-list" id="filmGrid">
		<?php foreach ($films as $film) : ?>
			<?php
			$tag        = (string) ($film['tag'] ?? '');
			$runtime    = (string) ($film['runtime'] ?? '');
			$img_file   = (string) ($film['imgFile'] ?? '');
			$img_src    = !empty($film['imgUrl']) ? (string) $film['imgUrl'] : ai_zippy_child_img($img_file);
			$img_alt    = (string) ($film['imgAlt'] ?? '');
			$film_title = (string) ($film['title'] ?? '');
			$meta       = (array) ($film['meta'] ?? []);
			$desc       = (string) ($film['desc'] ?? '');
			$video_url  = (string) ($film['videoUrl'] ?? '');
			?>
			<article class="film-card" data-video="<?php echo esc_attr($video_url); ?>">
				<div class="film-poster">
					<span class="film-tag"><?php echo esc_html($tag); ?></span>
					<span class="film-runtime"><?php echo esc_html($runtime); ?></span>
					<img src="<?php echo esc_url($img_src); ?>" alt="<?php echo esc_attr($img_alt); ?>">
					<span class="play-badge"></span>
				</div>
				<div class="film-body">
					<h3><?php echo esc_html($film_title); ?></h3>
					<div class="film-meta">
						<?php foreach ($meta as $m) : ?><span><?php echo esc_html((string) $m); ?></span><?php endforeach; ?>
						<?php if ('' === $video_url) : ?><span class="no-video-flag">⚠ No video yet</span><?php endif; ?>
					</div>
					<p><?php echo films_list_inline_html($desc); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wp_kses()'d inside. ?></p>
					<button class="btn btn-primary watch-btn">Watch Now</button>
				</div>
			</article>
		<?php endforeach; ?>
	</div>

	<div class="modal-overlay" id="filmModal" role="dialog" aria-modal="true" aria-labelledby="fmTitle">
		<div class="vmodal">
			<div class="vmodal-video">
				<button class="vmodal-close" id="fmClose" aria-label="Close">✕</button>
				<video id="fmVideo" controls="" playsinline="" preload="metadata" style="display: none;"></video>
				<div class="vmodal-empty" id="fmEmpty" style="display: grid;">
					<div>
						<h4>No video added yet</h4>
						<p>Hover this film’s card and use ↑ to upload a video file, or 🔗 to paste a hosted video URL.</p>
					</div>
				</div>
			</div>
			<div class="vmodal-info">
				<h3 id="fmTitle">Mad Vibe City</h3>
				<div class="vmodal-meta" id="fmMeta"><span>2024</span><span>Feature Film</span><span>New York · Singapore</span><span>135 min</span></div>
				<p id="fmDesc">Our debut feature film, produced on location across New York and Singapore. A story of movement, music, and the cities that never quite sleep — and a demonstration of our capacity to execute large-scale, international media projects.</p>
			</div>
		</div>
	</div>
</section>
