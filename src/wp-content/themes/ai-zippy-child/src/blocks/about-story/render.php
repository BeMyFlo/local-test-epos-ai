<?php

/**
 * Server-side render — `ai-zippy/about-story`.
 *
 * LITERAL CLONE of pricemrcopper_final_mockup.html markup L381-418
 * (`<section class="wrap">…`). The section element itself carries `wrap`, so
 * that class travels on the block wrapper (it is the mockup's semantic
 * container class, not a block-wrapper style) and the foundation CSS
 * (`.wrap`) applies unchanged. CSS lives in
 * src/scss/sections/_about-story.scss (mockup <style> L144-153 + the
 * `.sub-cards` row of the 900px tier, L296).
 *
 * Authoring layer stripped (block-map §6.2 / D5): the mockup's in-place
 * editing attributes and the img's editing-only hooks (authoring class and
 * label) — the img keeps only `src`/`alt` and is styled by the existing
 * `.split img` rule.
 * Inline styles are content and stay verbatim (ui-spec §5):
 * `style="margin-bottom:36px"` on `.split`, `style="max-width:none"` on the
 * second `.story`. Heading hierarchy h2 → h3 → h4 preserved (ui-spec §11).
 *
 * P2-D1: the split's first story block (Strategic Foundation) is editable
 * via the extra `introBlock` attribute — rendered markup is identical to
 * the mockup either way.
 * P2-D2: paragraph / sub-card text renders through the guarded
 * `about_story_inline_html()` kses helper so the mockup's `<i>Mad Vibe
 * City</i>` italics survive editing; headings stay `esc_html()` (no markup
 * in any heading). P2-D4: the 🎵/🎬 emoji live inside the sub-card h4 text,
 * verbatim.
 *
 * No links, no forms, no listeners in L381-418 → no view.js (block-map §2).
 *
 * @var array    $attributes Block attributes.
 * @var string   $content    Block inner content (unused — `supports.html:false`).
 * @var WP_Block $block      Block instance.
 */

defined('ABSPATH') || exit;

if (!function_exists('about_story_inline_html')) :
	/**
	 * Minimal inline allowlist for editable paragraph text (P2-D2).
	 *
	 * The mockup carries real inline markup inside paragraph text (the
	 * `<i>Mad Vibe City</i>` film title); esc_html() would strip it.
	 *
	 * @param string $text Attribute value.
	 * @return string Sanitized HTML with i/em/strong/br preserved.
	 */
	function about_story_inline_html(string $text): string
	{
		return wp_kses($text, [
			'i'      => [],
			'em'     => [],
			'strong' => [],
			'br'     => [],
		]);
	}
endif;

$eyebrow     = (string) ($attributes['eyebrow'] ?? '');
$title       = (string) ($attributes['title'] ?? '');
$intro       = (array) ($attributes['introBlock'] ?? []);
$storyBlocks = (array) ($attributes['storyBlocks'] ?? []);

$img_file = (string) ($attributes['imgFile'] ?? '') ?: 'about-story.jpg';
$img_src  = !empty($attributes['imgUrl']) ? (string) $attributes['imgUrl'] : ai_zippy_child_img($img_file);
$img_alt  = (string) ($attributes['imgAlt'] ?? '');

$wrapper_attributes = get_block_wrapper_attributes(['class' => 'wrap']);
?>
<section <?php echo $wrapper_attributes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- get_block_wrapper_attributes() escapes internally. ?>>
	<span class="eyebrow"><?php echo esc_html($eyebrow); ?></span>
	<h2 class="section-title"><?php echo esc_html($title); ?></h2>
	<div class="split" style="margin-bottom:36px">
		<div class="story">
			<div class="story-block">
				<h3><?php echo esc_html((string) ($intro['heading'] ?? '')); ?></h3>
				<?php foreach ((array) ($intro['paras'] ?? []) as $para) : ?>
					<p><?php echo about_story_inline_html((string) $para); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wp_kses()'d inside. ?></p>
				<?php endforeach; ?>
			</div>
		</div>
		<img src="<?php echo esc_url($img_src); ?>" alt="<?php echo esc_attr($img_alt); ?>">
	</div>
	<div class="story" style="max-width:none">
		<?php foreach ($storyBlocks as $story_block) : ?>
			<div class="story-block">
				<h3><?php echo esc_html((string) ($story_block['heading'] ?? '')); ?></h3>
				<?php foreach ((array) ($story_block['paras'] ?? []) as $para) : ?>
					<p><?php echo about_story_inline_html((string) $para); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wp_kses()'d inside. ?></p>
				<?php endforeach; ?>
				<?php if (!empty($story_block['subCards'])) : ?>
					<div class="sub-cards">
						<?php foreach ((array) $story_block['subCards'] as $sub_card) : ?>
							<div class="sub-card">
								<h4><?php echo esc_html((string) ($sub_card['heading'] ?? '')); ?></h4>
								<p><?php echo about_story_inline_html((string) ($sub_card['text'] ?? '')); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wp_kses()'d inside. ?></p>
							</div>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
			</div>
		<?php endforeach; ?>
	</div>
</section>
