<?php

/**
 * Server-side render — `ai-zippy/home-hero`.
 *
 * LITERAL CLONE of pricemrcopper_final_mockup.html markup L337-350
 * (`<section class="hero"><div class="wrap hero-grid">…`). CSS lives in
 * src/scss/sections/_home-hero.scss (mockup <style> L79-88 + the `.hero-grid`
 * row of the 900px tier, L296) — never in this block's style.scss.
 *
 * Authoring layer stripped (block-map §6.2 / D5): the mockup's in-place
 * editing attributes/labels and the "Click to upload…" `title` hint.
 * Behavior map B15: the two SPA router buttons become real links —
 * "Explore Our Shop" → /shop/, "Make A Film With Us" → /film/. No placeholder
 * hrefs, no SPA routing attributes, no click handlers, no view.js.
 *
 * The h1 is the site's only h1 (ui-spec §11) and keeps the two `.grad-text`
 * prism accents as real HTML spans, space-joined exactly like mockup L341.
 *
 * @var array    $attributes Block attributes.
 * @var string   $content    Block inner content (unused — `supports.html:false`).
 * @var WP_Block $block      Block instance.
 */

defined('ABSPATH') || exit;

if (!function_exists('pmc_home_hero_url')) :
	/**
	 * Turn a stored CTA target into a real link.
	 *
	 * Root-relative slugs ("/shop/") are expanded with home_url() so the links
	 * survive a sub-directory install. Absolute URLs pass through untouched.
	 *
	 * @param string $url Attribute value.
	 * @return string Non-empty URL — never "#".
	 */
	function pmc_home_hero_url(string $url): string
	{
		$url = trim($url);

		if ('' === $url) {
			return home_url('/');
		}

		return str_starts_with($url, '/') ? home_url($url) : $url;
	}
endif;

$eyebrow = (string) ($attributes['eyebrow'] ?? '');
$lead    = (string) ($attributes['lead'] ?? '');

/* Four editable title pieces so the two `.grad-text` accents stay real spans. */
$title_part_1  = (string) ($attributes['titlePart1'] ?? '');
$title_accent1 = '<span class="grad-text">' . esc_html((string) ($attributes['titleAccent1'] ?? '')) . '</span>';
$title_part_2  = (string) ($attributes['titlePart2'] ?? '');
$title_accent2 = '<span class="grad-text">' . esc_html((string) ($attributes['titleAccent2'] ?? '')) . '</span>';

$btn_primary_text = (string) ($attributes['primaryBtnText'] ?? '');
$btn_primary_url  = pmc_home_hero_url((string) ($attributes['primaryBtnUrl'] ?? ''));
$btn_ghost_text   = (string) ($attributes['ghostBtnText'] ?? '');
$btn_ghost_url    = pmc_home_hero_url((string) ($attributes['ghostBtnUrl'] ?? ''));

/* `.hero` is the mockup's own class and carries the section background, so it
   goes on the block wrapper (style the semantic class, never the block class).
   The wrapper adds no vertical margin — the rhythm comes from `.wrap`. */
$hero_file = (string) ($attributes['imgFile'] ?? '') ?: 'home-hero.avif';
$hero_src  = !empty($attributes['imgUrl']) ? (string) $attributes['imgUrl'] : ai_zippy_child_img($hero_file);
$hero_alt  = (string) ($attributes['imgAlt'] ?? '');

$wrapper_attributes = get_block_wrapper_attributes(['class' => 'hero']);
?>
<section <?php echo $wrapper_attributes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- get_block_wrapper_attributes() escapes internally. ?>>
	<div class="wrap hero-grid">
		<div>
			<span class="eyebrow"><?php echo esc_html($eyebrow); ?></span>
			<h1><?php echo esc_html($title_part_1); ?> <?php echo $title_accent1; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- esc_html()'d above. ?> <?php echo esc_html($title_part_2); ?> <?php echo $title_accent2; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- esc_html()'d above. ?></h1>
			<p class="lead"><?php echo esc_html($lead); ?></p>
			<div class="btn-row">
				<a class="btn btn-primary" href="<?php echo esc_url($btn_primary_url); ?>"><?php echo esc_html($btn_primary_text); ?></a>
				<a class="btn btn-ghost" href="<?php echo esc_url($btn_ghost_url); ?>"><?php echo esc_html($btn_ghost_text); ?></a>
			</div>
		</div>
		<img class="hero-img" src="<?php echo esc_url($hero_src); ?>" alt="<?php echo esc_attr($hero_alt); ?>">
	</div>
</section>
