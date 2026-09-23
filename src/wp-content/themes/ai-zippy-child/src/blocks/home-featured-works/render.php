<?php

/**
 * Server-side render — `ai-zippy/home-featured-works`.
 *
 * LITERAL CLONE of pricemrcopper_final_mockup.html markup L366-376
 * (`<section class="soft"><div class="wrap tight">…`). The outer section
 * carries `soft`, so that class travels on the block wrapper. CSS lives in
 * src/scss/sections/_home-featured-works.scss (mockup <style> L110-114).
 *
 * Authoring layer stripped (block-map §6.2 / D5): the mockup's in-place
 * editing class/label hooks and the "Click to upload…" `title` hint — the
 * imgs keep only `src`/`alt` (the mockup's only img class was one of those
 * dropped hooks).
 * The `cap` em-dash heading is verbatim. No behaviors belong to this block
 * → no view.js.
 *
 * @var array    $attributes Block attributes.
 * @var string   $content    Block inner content (unused — `supports.html:false`).
 * @var WP_Block $block      Block instance.
 */

defined('ABSPATH') || exit;

$eyebrow = (string) ($attributes['eyebrow'] ?? '');
$title   = (string) ($attributes['title'] ?? '');
$works   = (array) ($attributes['works'] ?? []);

$wrapper_attributes = get_block_wrapper_attributes(['class' => 'soft']);
?>
<section <?php echo $wrapper_attributes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- get_block_wrapper_attributes() escapes internally. ?>>
	<div class="wrap tight">
		<span class="eyebrow"><?php echo esc_html($eyebrow); ?></span>
		<h2 class="section-title"><?php echo esc_html($title); ?></h2>
		<div class="grid-3">
			<?php foreach ($works as $work) : ?>
				<?php
				$img_file = (string) ($work['imgFile'] ?? '');
				$img_src  = !empty($work['imgUrl']) ? (string) $work['imgUrl'] : ai_zippy_child_img($img_file);
				$img_alt  = (string) ($work['imgAlt'] ?? '');
				?>
				<div class="feature-card">
					<img src="<?php echo esc_url($img_src); ?>" alt="<?php echo esc_attr($img_alt); ?>">
					<div class="cap"><h3><?php echo esc_html((string) ($work['capHeading'] ?? '')); ?></h3><p><?php echo esc_html((string) ($work['capText'] ?? '')); ?></p></div>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>
