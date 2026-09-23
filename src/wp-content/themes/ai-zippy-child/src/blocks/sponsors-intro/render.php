<?php

/**
 * Server-side render — `ai-zippy/sponsors-intro`.
 *
 * LITERAL CLONE of pricemrcopper_final_mockup.html markup L831-841
 * (`<section class="wrap">…`). The section element itself carries `wrap`, so
 * that class travels on the block wrapper (film-intro precedent) and the
 * foundation CSS (`.wrap`) applies unchanged. Every component style this
 * section uses (.eyebrow / .section-title / .section-sub from _base +
 * _components; .grid-3 / .card / .card .icon / .icon.i1-i3 / .btn / .btn-row
 * from _components) already lives in the shared partials —
 * _sponsors-intro.scss ships empty permanently (S2a stub decision).
 *
 * Authoring layer stripped (block-map §6.2 / D5): every in-place editing
 * attribute is removed and the CTA button's SPA navigation hook becomes a
 * real link. Card markup stays on one line per card — the mockup's own
 * single-line shape (about-pillars precedent). The inline
 * `style="margin-top:36px"` on `.btn-row` is kept verbatim. Heading
 * hierarchy h2 → h3 preserved (ui-spec §11).
 *
 * P6-D2: the mockup's scroll-to CTA button ports to a real anchor link
 * (block-map §3 / B15, mockup L840) — `<a class="btn btn-primary"
 * href="…">` resolved through `sponsors_intro_url()` (relative →
 * home_url()); target `/enquiries/`.
 *
 * No image, no listeners in L831-841 → no view.js (block-map §2).
 *
 * @var array    $attributes Block attributes.
 * @var string   $content    Block inner content (unused — `supports.html:false`).
 * @var WP_Block $block      Block instance.
 */

defined('ABSPATH') || exit;

if (!function_exists('sponsors_intro_url')) :
	/**
	 * Turn the CTA's stored target into a real link (P6-D2).
	 *
	 * Root-relative paths ("/enquiries/") are expanded with home_url()
	 * so the link survives a sub-directory install. Absolute URLs pass
	 * through untouched.
	 *
	 * @param string $url Attribute value.
	 * @return string Non-empty URL — never "#".
	 */
	function sponsors_intro_url(string $url): string
	{
		$url = trim($url);

		if ('' === $url) {
			return home_url('/enquiries/');
		}

		return str_starts_with($url, '/') ? home_url($url) : $url;
	}
endif;

$eyebrow = (string) ($attributes['eyebrow'] ?? '');
$title   = (string) ($attributes['title'] ?? '');
$sub     = (string) ($attributes['sub'] ?? '');
$cards   = (array) ($attributes['cards'] ?? []);

$btn_text = (string) ($attributes['btnText'] ?? '');
$btn_url  = sponsors_intro_url((string) ($attributes['btnUrl'] ?? ''));

$wrapper_attributes = get_block_wrapper_attributes(['class' => 'wrap']);
?>
<section <?php echo $wrapper_attributes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- get_block_wrapper_attributes() escapes internally. ?>>
	<span class="eyebrow"><?php echo esc_html($eyebrow); ?></span>
	<h2 class="section-title"><?php echo esc_html($title); ?></h2>
	<p class="section-sub"><?php echo esc_html($sub); ?></p>
	<div class="grid-3">
		<?php foreach ($cards as $card) : ?>
			<div class="card"><div class="icon <?php echo esc_attr((string) ($card['iconVariant'] ?? 'i1')); ?>"><?php echo esc_html((string) ($card['emoji'] ?? '')); ?></div><h3><?php echo esc_html((string) ($card['heading'] ?? '')); ?></h3><p><?php echo esc_html((string) ($card['text'] ?? '')); ?></p></div>
		<?php endforeach; ?>
	</div>
	<div class="btn-row" style="margin-top:36px"><a class="btn btn-primary" href="<?php echo esc_url($btn_url); ?>"><?php echo esc_html($btn_text); ?></a></div>
</section>
