<?php

/**
 * Server-side render — `ai-zippy/about-pillars`.
 *
 * LITERAL CLONE of pricemrcopper_final_mockup.html markup L419-429
 * (`<section class="soft"><div class="wrap tight">…`). The outer section
 * carries `soft`, so that class travels on the block wrapper. CSS lives in
 * src/scss/sections/_about-pillars.scss — none: component-driven (eyebrow /
 * .section-title + .grid-3 + .card / .card .icon from _components + _base,
 * block-map §2); that partial ships empty permanently.
 *
 * Authoring layer stripped (block-map §6.2 / D5): the mockup's in-place
 * editing attributes. Card markup stays on one line per
 * card — the mockup's own single-line shape (P1 `home-who-we-are`
 * precedent). Heading hierarchy h2 → h3 preserved (ui-spec §11).
 *
 * No image, no links, no listeners in L419-429 → no view.js (block-map §2).
 *
 * @var array    $attributes Block attributes.
 * @var string   $content    Block inner content (unused — `supports.html:false`).
 * @var WP_Block $block      Block instance.
 */

defined('ABSPATH') || exit;

$eyebrow = (string) ($attributes['eyebrow'] ?? '');
$title   = (string) ($attributes['title'] ?? '');
$cards   = (array) ($attributes['cards'] ?? []);

$wrapper_attributes = get_block_wrapper_attributes(['class' => 'soft']);
?>
<section <?php echo $wrapper_attributes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- get_block_wrapper_attributes() escapes internally. ?>>
	<div class="wrap tight">
		<span class="eyebrow"><?php echo esc_html($eyebrow); ?></span>
		<h2 class="section-title"><?php echo esc_html($title); ?></h2>
		<div class="grid-3">
			<?php foreach ($cards as $card) : ?>
				<div class="card"><div class="icon <?php echo esc_attr((string) ($card['iconVariant'] ?? 'i1')); ?>"><?php echo esc_html((string) ($card['emoji'] ?? '')); ?></div><h3><?php echo esc_html((string) ($card['heading'] ?? '')); ?></h3><p><?php echo esc_html((string) ($card['text'] ?? '')); ?></p></div>
			<?php endforeach; ?>
		</div>
	</div>
</section>
