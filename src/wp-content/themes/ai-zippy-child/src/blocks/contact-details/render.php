<?php

/**
 * Server-side render — `ai-zippy/contact-details`.
 *
 * LITERAL CLONE of pricemrcopper_final_mockup.html markup L956-968
 * (`<section class="wrap">…`). The section element itself carries `wrap`, so
 * that class travels on the block wrapper (events-process precedent). The
 * section's styles live in src/scss/sections/_contact-details.scss (mockup
 * <style> L166-175 + the `.contact-grid` row of the 900px tier, L296).
 *
 * Authoring layer stripped (block-map §6.2 / D5): the 5 in-place editing
 * attributes on L957-964 are removed.
 *
 * P9-D5: items render in the mockup's single-line card shape
 * (about-pillars/film-intro precedent) — the `<b>` heading renders only
 * when the item carries a non-empty `heading` (item 1 in the mockup);
 * email/phone/hours stay plain text, never linkified (the mockup renders
 * them as text — the `.ct a` rules in the partial are the mockup's own
 * dead CSS, ported verbatim per P9-D9).
 * P9-D3: every field is plain text → `esc_html()` only; the map URL goes
 * through `esc_url()`; no kses helper, and with no helper function there
 * is no `function_exists` guard (P7-D3 precedent).
 * P9-D8: `mapUrl` falls back to the mockup URL via `?:` so the iframe
 * never renders an empty src (img-file pattern); the map is an iframe, not
 * a media asset (ui-spec §10) — no Media Library wiring.
 *
 * No listeners in L956-968 and block-map §2 assigns none → no view.js
 * (P7-D1 precedent).
 *
 * @var array    $attributes Block attributes.
 * @var string   $content    Block inner content (unused — `supports.html:false`).
 * @var WP_Block $block      Block instance.
 */

defined('ABSPATH') || exit;

$eyebrow = (string) ($attributes['eyebrow'] ?? '');
$title   = (string) ($attributes['title'] ?? '');
$items   = (array) ($attributes['items'] ?? []);

$map_url = (string) ($attributes['mapUrl'] ?? '') ?: 'https://www.google.com/maps?q=60+Paya+Lebar+Road+Paya+Lebar+Square+Singapore+409051&output=embed';

$wrapper_attributes = get_block_wrapper_attributes(['class' => 'wrap']);
?>
<section <?php echo $wrapper_attributes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- get_block_wrapper_attributes() escapes internally. ?>>
	<span class="eyebrow"><?php echo esc_html($eyebrow); ?></span>
	<h2 class="section-title"><?php echo esc_html($title); ?></h2>
	<div class="contact-grid">
		<div>
			<?php foreach ($items as $item) : ?>
				<div class="contact-item"><span class="ci"><?php echo esc_html((string) ($item['icon'] ?? '')); ?></span><div class="ct"><?php if (!empty($item['heading'])) : ?><b><?php echo esc_html((string) $item['heading']); ?></b><?php endif; ?><?php foreach ((array) ($item['lines'] ?? []) as $line) : ?><div><?php echo esc_html((string) $line); ?></div><?php endforeach; ?></div></div>
			<?php endforeach; ?>
		</div>
		<iframe class="map-frame" src="<?php echo esc_url($map_url); ?>" loading="lazy" title="Map"></iframe>
	</div>
</section>
