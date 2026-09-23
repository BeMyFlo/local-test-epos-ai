<?php

/**
 * Server-side render — `ai-zippy/film-intro`.
 *
 * LITERAL CLONE of pricemrcopper_final_mockup.html markup L434-449
 * (`<section class="wrap">…`). The section element itself carries `wrap`, so
 * that class travels on the block wrapper (about-story precedent) and the
 * foundation CSS (`.wrap`) applies unchanged. Every component style this
 * section uses (.split/.split img, .step-list/.step/.step .n, .btn-row/.btn)
 * already lives in _components.scss — _film-intro.scss ships empty
 * permanently (S2a stub decision).
 *
 * Authoring layer stripped (block-map §6.2 / D5): the img drops its
 * editing-only hooks (authoring class and label) and keeps only
 * `src`/`alt`; every in-place editing attribute is removed.
 *
 * P3-D1: the mockup's scroll-to CTA button ports to a real anchor link
 * (block-map §3 / B16) — `<a class="btn btn-primary" href="…">`
 * resolved through `film_intro_url()` (relative → home_url()); smooth scroll
 * is native (`html{scroll-behavior:smooth}`, _base.scss L23).
 * P3-D3: the intro paragraph renders through the guarded
 * `film_intro_inline_html()` kses helper so the mockup's `<b>`/`<i>` inline
 * markup survives editing; eyebrow/title/step fields stay `esc_html()` (no
 * markup anywhere else in L434-449).
 *
 * No listeners in L434-449 → no view.js (block-map §2).
 *
 * @var array    $attributes Block attributes.
 * @var string   $content    Block inner content (unused — `supports.html:false`).
 * @var WP_Block $block      Block instance.
 */

defined('ABSPATH') || exit;

if (!function_exists('film_intro_url')) :
	/**
	 * Turn the CTA's stored target into a real link (P3-D1).
	 *
	 * Root-relative paths ("/film/#filmEnquiry") are expanded with home_url()
	 * so the link survives a sub-directory install. Absolute URLs pass
	 * through untouched.
	 *
	 * @param string $url Attribute value.
	 * @return string Non-empty URL — never "#".
	 */
	function film_intro_url(string $url): string
	{
		$url = trim($url);

		if ('' === $url) {
			return home_url('/film/#filmEnquiry');
		}

		return str_starts_with($url, '/') ? home_url($url) : $url;
	}
endif;

if (!function_exists('film_intro_inline_html')) :
	/**
	 * Minimal inline allowlist for the editable intro paragraph (P3-D3).
	 *
	 * The mockup carries real inline markup inside the intro text (the
	 * `<b>New York City</b>` / `<b>Singapore</b>` and `<i>Mad Vibe City</i>`
	 * accents); esc_html() would strip it.
	 *
	 * @param string $text Attribute value.
	 * @return string Sanitized HTML with i/em/b/strong/br preserved.
	 */
	function film_intro_inline_html(string $text): string
	{
		return wp_kses($text, [
			'i'      => [],
			'em'     => [],
			'b'      => [],
			'strong' => [],
			'br'     => [],
		]);
	}
endif;

$eyebrow = (string) ($attributes['eyebrow'] ?? '');
$title   = (string) ($attributes['title'] ?? '');
$intro   = (string) ($attributes['intro'] ?? '');
$steps   = (array) ($attributes['steps'] ?? []);

$img_file = (string) ($attributes['imgFile'] ?? '') ?: 'film-production.avif';
$img_src  = !empty($attributes['imgUrl']) ? (string) $attributes['imgUrl'] : ai_zippy_child_img($img_file);
$img_alt  = (string) ($attributes['imgAlt'] ?? '');

$btn_text = (string) ($attributes['btnText'] ?? '');
$btn_url  = film_intro_url((string) ($attributes['btnUrl'] ?? ''));

$wrapper_attributes = get_block_wrapper_attributes(['class' => 'wrap']);
?>
<section <?php echo $wrapper_attributes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- get_block_wrapper_attributes() escapes internally. ?>>
	<span class="eyebrow"><?php echo esc_html($eyebrow); ?></span>
	<h2 class="section-title"><?php echo esc_html($title); ?></h2>
	<div class="split">
		<img src="<?php echo esc_url($img_src); ?>" alt="<?php echo esc_attr($img_alt); ?>">
		<div>
			<p class="section-sub"><?php echo film_intro_inline_html($intro); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wp_kses()'d inside. ?></p>
			<div class="step-list">
				<?php foreach ($steps as $step) : ?>
					<div class="step"><span class="n"><?php echo esc_html((string) ($step['n'] ?? '')); ?></span><div><h3><?php echo esc_html((string) ($step['heading'] ?? '')); ?></h3><p><?php echo esc_html((string) ($step['text'] ?? '')); ?></p></div></div>
				<?php endforeach; ?>
			</div>
			<div class="btn-row"><a class="btn btn-primary" href="<?php echo esc_url($btn_url); ?>"><?php echo esc_html($btn_text); ?></a></div>
		</div>
	</div>
</section>
