<?php

/**
 * Server-side render — `ai-zippy/film-tiers`.
 *
 * LITERAL CLONE of pricemrcopper_final_mockup.html markup L452-491
 * (`<section class="soft" id="filmEnquiry">…`). The section element itself
 * carries `soft`, so that class travels on the block wrapper (about-story
 * precedent); tier styles live in src/scss/sections/_film-tiers.scss
 * (mockup <style> L186-193 + the `.tier-grid` row of the 900px tier, L296),
 * the enquiry form chrome (form.enquiry/.field/.form-ok) in
 * _components.scss.
 *
 * Authoring layer stripped (block-map §6.2 / D5): every in-place editing
 * attribute is removed.
 *
 * P3-D2: `id="filmEnquiry"` is the mockup's own anchor (L452) — the
 * film-intro CTA and `/film/#filmEnquiry` deep links target it — so it is
 * hardcoded on the wrapper and `supports.anchor` is false for this block
 * (the anchor UI would promise an id the render never emits).
 * P3-D3: tier text renders through the guarded `film_tiers_inline_html()`
 * kses helper so the mockup's nested
 * `<div><span style="font-size: 0.95rem;">` markup (Tier 1, L463) survives
 * editing — the `<div>`-inside-`<p>` nesting is the mockup's own authoring
 * and is cloned as-is (clone doctrine), not restructured. All other fields
 * stay `esc_html()`.
 * P3-D4: form chrome is static markup — labels, placeholders, the
 * `— Select a budget tier —` empty option, the submit label and the
 * `.form-ok` message (incl. ✅ emoji) are pasted verbatim; only the select's
 * two subject options are editable attributes.
 * P3-D5: option 2's "$400,000" vs the Tier 2 price "$290,000" mismatch is
 * the mockup's own copy (L467 vs L483) — cloned verbatim, recorded as a
 * BUILD-STATE open issue.
 *
 * B12 lives in this block's view.js (block-map §2) — no other listeners in
 * L452-491.
 *
 * @var array    $attributes Block attributes.
 * @var string   $content    Block inner content (unused — `supports.html:false`).
 * @var WP_Block $block      Block instance.
 */

defined('ABSPATH') || exit;

if (!function_exists('film_tiers_inline_html')) :
	/**
	 * Minimal inline allowlist for the editable tier text (P3-D3).
	 *
	 * Tier 1's mockup text carries nested block-ish inline markup
	 * (`<div><span style="font-size: 0.95rem;">…</span></div>`); esc_html()
	 * would strip it, and the plain i/em/b/strong/br set is kept so the same
	 * helper serves any inline accents a member later adds.
	 *
	 * @param string $text Attribute value.
	 * @return string Sanitized HTML with div/span[style]/i/em/b/strong/br preserved.
	 */
	function film_tiers_inline_html(string $text): string
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
$tiers   = (array) ($attributes['tiers'] ?? []);
$note    = (string) ($attributes['note'] ?? '');

$form_heading     = (string) ($attributes['formHeading'] ?? '');
$form_sub         = (string) ($attributes['formSub'] ?? '');
$subject_options  = (array) ($attributes['subjectOptions'] ?? []);

$wrapper_attributes = get_block_wrapper_attributes(['class' => 'soft', 'id' => 'filmEnquiry']);
?>
<section <?php echo $wrapper_attributes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- get_block_wrapper_attributes() escapes internally. ?>>
	<div class="wrap tight">
		<span class="eyebrow"><?php echo esc_html($eyebrow); ?></span>
		<h2 class="section-title"><?php echo esc_html($title); ?></h2>
		<p class="section-sub"><?php echo esc_html($sub); ?></p>

		<div class="tier-grid">
			<?php foreach ($tiers as $tier) : ?>
				<div class="tier">
					<span class="tier-tag"><?php echo esc_html((string) ($tier['tag'] ?? '')); ?></span>
					<div class="tier-price"><?php echo esc_html((string) ($tier['price'] ?? '')); ?></div>
					<span class="tier-runtime"><?php echo esc_html((string) ($tier['runtime'] ?? '')); ?></span>
					<p><?php echo film_tiers_inline_html((string) ($tier['text'] ?? '')); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wp_kses()'d inside. ?></p>
				</div>
			<?php endforeach; ?>
		</div>
		<div class="tier-note">💡 <span><?php echo esc_html($note); ?></span></div>

		<h2 class="section-title"><?php echo esc_html($form_heading); ?></h2>
		<p class="section-sub"><?php echo esc_html($form_sub); ?></p>
		<form class="enquiry" id="filmEnquiryForm">
			<div class="field"><label for="filmName">Your Name</label><input type="text" id="filmName" required="" placeholder="Full name" value=""></div>
			<div class="field"><label for="filmEmail">Email</label><input type="email" id="filmEmail" required="" placeholder="you@company.com" value=""></div>
			<div class="field"><label for="filmSubject">Subject</label>
				<select id="filmSubject" required="">
					<option value="" selected="">— Select a budget tier —</option>
					<?php foreach ($subject_options as $option) : ?>
						<option><?php echo esc_html((string) $option); ?></option>
					<?php endforeach; ?>
				</select>
			</div>
			<div class="field"><label for="filmMsg">Tell Us About Your Film</label><textarea id="filmMsg" rows="5" required="" placeholder="Story, genre, timeline, locations…"></textarea></div>
			<div><button type="submit" class="btn btn-primary">Send Film Enquiry</button></div>
			<div class="form-ok" id="filmEnquiryOk">✅ <span>Thank you! Your film enquiry has been received. We’ll be in touch shortly.</span></div>
		</form>
	</div>
</section>
