<?php

/**
 * Server-side render — `ai-zippy/home-who-we-are`.
 *
 * LITERAL CLONE of pricemrcopper_final_mockup.html markup L352-364
 * (`<section class="wrap tight">…`). The section element itself carries
 * `wrap tight`, so those classes travel on the block wrapper and the
 * foundation CSS (`.wrap`, `.wrap.tight`) applies unchanged. Inline styles
 * are content and stay verbatim (ui-spec §5). No SCSS partial of its own —
 * component-driven (eyebrow / section-title / section-sub / grid-3 / card /
 * icon from _components + _base, block-map §2).
 *
 * Authoring layer stripped (block-map §6.2 / D5): the mockup's in-place
 * editing attributes.
 * No behaviors belong to this block → no view.js.
 *
 * @var array    $attributes Block attributes.
 * @var string   $content    Block inner content (unused — `supports.html:false`).
 * @var WP_Block $block      Block instance.
 */

defined('ABSPATH') || exit;

$eyebrow      = (string) ($attributes['eyebrow'] ?? '');
$title        = (string) ($attributes['title'] ?? '');
$para1        = (string) ($attributes['para1'] ?? '');
$para2        = (string) ($attributes['para2'] ?? '');
$para3        = (string) ($attributes['para3'] ?? '');
$pillarsTitle = (string) ($attributes['pillarsTitle'] ?? '');
$cards        = (array) ($attributes['cards'] ?? []);

$wrapper_attributes = get_block_wrapper_attributes(['class' => 'wrap tight']);
?>
<section <?php echo $wrapper_attributes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- get_block_wrapper_attributes() escapes internally. ?>>
	<span class="eyebrow"><?php echo esc_html($eyebrow); ?></span>
	<h2 class="section-title"><?php echo esc_html($title); ?></h2>
	<p class="section-sub" style="margin-bottom:18px"><?php echo esc_html($para1); ?></p>
	<p class="section-sub" style="margin-bottom:18px"><?php echo esc_html($para2); ?></p>
	<p class="section-sub"><?php echo esc_html($para3); ?></p>
	<h3 style="font-size:1.3rem;margin-bottom:22px"><?php echo esc_html($pillarsTitle); ?></h3>
	<div class="grid-3">
		<?php foreach ($cards as $card) : ?>
			<div class="card"><div class="icon <?php echo esc_attr((string) ($card['iconVariant'] ?? 'i1')); ?>"><?php echo esc_html((string) ($card['emoji'] ?? '')); ?></div><h3><?php echo esc_html((string) ($card['heading'] ?? '')); ?></h3><p><?php echo esc_html((string) ($card['text'] ?? '')); ?></p></div>
		<?php endforeach; ?>
	</div>
</section>
