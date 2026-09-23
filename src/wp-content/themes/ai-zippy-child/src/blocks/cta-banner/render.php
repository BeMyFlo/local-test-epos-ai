<?php

/**
 * Server-side render — `ai-zippy/cta-banner` (shared block).
 *
 * LITERAL CLONE of pricemrcopper_final_mockup.html markup L603-613 — the
 * films-page instance (`<section class="soft"><div class="wrap tight">…`),
 * which is also this block's default. The other two instances (events
 * L862-870 plain variant, upcoming L913-923 soft variant) land with their
 * pages at S5 — same block, different attributes.
 *
 * P4-D9: the variant is a structural boolean, not a class swap —
 * soft: `<section class="soft"><div class="wrap tight">` > card;
 * plain: `<section class="wrap tight" style="padding-top:0">` > card
 * (the events instance's own shape, L862). The card's inline style is
 * cloned verbatim (about-story precedent: inline styles kept).
 * The `<button data-goto="film">` ports to a real `<a class="btn
 * btn-primary">` (block-map §3; never `href="#"`) resolved through
 * `cta_banner_url()`.
 *
 * _cta-banner.scss ships empty permanently (S2a decision) — .card,
 * .wrap/.tight, .soft and .btn are all shared components.
 * No listeners → no view.js (the only behavior is B15, a native link).
 *
 * @var array    $attributes Block attributes.
 * @var string   $content    Block inner content (unused — `supports.html:false`).
 * @var WP_Block $block      Block instance.
 */

defined('ABSPATH') || exit;

if (!function_exists('cta_banner_url')) :
	/**
	 * Turn the stored CTA target into a real link (P3-D1 film_intro_url
	 * precedent).
	 *
	 * Root-relative paths ("/film/") are expanded with home_url() so the
	 * link survives a sub-directory install. Absolute URLs pass through
	 * untouched; an empty value falls back to the site root — never "#".
	 *
	 * @param string $url Attribute value.
	 * @return string Non-empty URL.
	 */
	function cta_banner_url(string $url): string
	{
		$url = trim($url);

		if ('' === $url) {
			return home_url('/');
		}

		return str_starts_with($url, '/') ? home_url($url) : $url;
	}
endif;

$heading  = (string) ($attributes['heading'] ?? '');
$text     = (string) ($attributes['text'] ?? '');
$btn_text = (string) ($attributes['btnLabel'] ?? '');
$btn_url  = cta_banner_url((string) ($attributes['btnUrl'] ?? ''));
$soft     = !empty($attributes['soft']);
?>
<?php if ($soft) : ?>
<section <?php echo get_block_wrapper_attributes(['class' => 'soft']); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- get_block_wrapper_attributes() escapes internally. ?>>
	<div class="wrap tight">
<?php else : ?>
<section <?php echo get_block_wrapper_attributes(['class' => 'wrap tight', 'style' => 'padding-top:0']); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- get_block_wrapper_attributes() escapes internally. ?>>
<?php endif; ?>
	<div class="card" style="display:flex;gap:20px;align-items:center;justify-content:space-between;flex-wrap:wrap">
		<div>
			<h3><?php echo esc_html($heading); ?></h3>
			<p><?php echo esc_html($text); ?></p>
		</div>
		<a class="btn btn-primary" href="<?php echo esc_url($btn_url); ?>"><?php echo esc_html($btn_text); ?></a>
	</div>
<?php if ($soft) : ?>
	</div>
</section>
<?php else : ?>
</section>
<?php endif; ?>
