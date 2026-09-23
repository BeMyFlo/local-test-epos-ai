<?php

/**
 * Server-side render — `ai-zippy/events-process`.
 *
 * LITERAL CLONE of pricemrcopper_final_mockup.html markup L846-860
 * (`<section class="wrap">…`). The section element itself carries `wrap`, so
 * that class travels on the block wrapper (film-intro precedent) and the
 * foundation CSS (`.wrap`) applies unchanged. Every component style this
 * section uses (.split/.split img, .step-list/.step/.step .n, plus the 900px
 * `.split` row) already lives in _components.scss — _events-process.scss
 * ships empty permanently (P7-D7).
 *
 * Authoring layer stripped (block-map §6.2 / D5): the img drops its
 * editing-only hooks (authoring class, label, and the authoring title
 * attribute) and keeps only `src`/`alt`; every in-place editing attribute is
 * removed.
 *
 * P7-D4: DOM order preserved verbatim — the text column (section-sub +
 * step-list) comes FIRST and the img SECOND inside `.split`, the mirror of
 * film-intro; `.split` is a two-equal-column grid, so source order defines
 * the sides (image on the right).
 * P7-D3: every text field in L846-860 is plain (no inline markup) → all of
 * them render through `esc_html()`; no kses helper, and with no helper
 * functions at all there is no `function_exists` guard block.
 * Step markup stays on one line per step — the mockup's own single-line
 * shape (film-intro precedent). Heading hierarchy h2 → h3 preserved
 * (ui-spec §11).
 *
 * No listeners in L846-860 → no view.js (P7-D1).
 *
 * @var array    $attributes Block attributes.
 * @var string   $content    Block inner content (unused — `supports.html:false`).
 * @var WP_Block $block      Block instance.
 */

defined('ABSPATH') || exit;

$eyebrow = (string) ($attributes['eyebrow'] ?? '');
$title   = (string) ($attributes['title'] ?? '');
$intro   = (string) ($attributes['intro'] ?? '');
$steps   = (array) ($attributes['steps'] ?? []);

$img_file = (string) ($attributes['imgFile'] ?? '') ?: 'event-production.avif';
$img_src  = !empty($attributes['imgUrl']) ? (string) $attributes['imgUrl'] : ai_zippy_child_img($img_file);
$img_alt  = (string) ($attributes['imgAlt'] ?? '');

$wrapper_attributes = get_block_wrapper_attributes(['class' => 'wrap']);
?>
<section <?php echo $wrapper_attributes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- get_block_wrapper_attributes() escapes internally. ?>>
	<span class="eyebrow"><?php echo esc_html($eyebrow); ?></span>
	<h2 class="section-title"><?php echo esc_html($title); ?></h2>
	<div class="split">
		<div>
			<p class="section-sub"><?php echo esc_html($intro); ?></p>
			<div class="step-list">
				<?php foreach ($steps as $step) : ?>
					<div class="step"><span class="n"><?php echo esc_html((string) ($step['n'] ?? '')); ?></span><div><h3><?php echo esc_html((string) ($step['heading'] ?? '')); ?></h3><p><?php echo esc_html((string) ($step['text'] ?? '')); ?></p></div></div>
				<?php endforeach; ?>
			</div>
		</div>
		<img src="<?php echo esc_url($img_src); ?>" alt="<?php echo esc_attr($img_alt); ?>">
	</div>
</section>
