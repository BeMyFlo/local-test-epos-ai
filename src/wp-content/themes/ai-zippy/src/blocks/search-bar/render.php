<?php

/**
 * Search Bar block — server-side render.
 *
 * @var array    $attributes Block attributes
 * @var string   $content    Inner content
 * @var WP_Block $block      Block instance
 */

defined('ABSPATH') || exit;

$display_mode  = $attributes['displayMode']  ?? 'inline';
$search_scope  = $attributes['searchScope']  ?? 'products';
$placeholder   = esc_attr($attributes['placeholder'] ?? __('Search products…', 'ai-zippy'));
$max_results   = (int) ($attributes['maxResults'] ?? 8);
$icon_url      = esc_url($attributes['iconUrl'] ?? '');
$icon_size     = max(12, min(48, (int) ($attributes['iconSize'] ?? 20)));
$uid           = wp_unique_id('zs-');

$wrapper_attrs = get_block_wrapper_attributes([
    'class'            => 'zs__block zs__block--' . esc_attr($display_mode),
    'data-scope'       => esc_attr($search_scope),
    'data-max-results' => $max_results,
    'data-mode'        => esc_attr($display_mode),
    'style'            => '--zs-icon-size:' . $icon_size . 'px',
]);

$svg_search  = '<svg width="' . $icon_size . '" height="' . $icon_size . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>';
$svg_close   = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>';
$icon_markup = $icon_url
    ? '<img src="' . $icon_url . '" alt="" width="' . $icon_size . '" height="' . $icon_size . '" class="zs__custom-icon" aria-hidden="true">'
    : $svg_search;
?>

<div <?php echo $wrapper_attrs; ?>>

	<?php if ($display_mode === 'icon') : ?>

		<!-- Icon-only trigger -->
		<button
			class="zs__icon-trigger"
			aria-label="<?php esc_attr_e('Open search', 'ai-zippy'); ?>"
			aria-expanded="false"
			aria-controls="<?php echo esc_attr($uid); ?>-modal"
			type="button"
		>
			<?php echo $icon_markup; // phpcs:ignore WordPress.Security.EscapeOutput ?>
		</button>

		<!-- Full-page modal (shown on trigger click) -->
		<div class="zs__modal" id="<?php echo esc_attr($uid); ?>-modal" role="dialog" aria-modal="true" aria-label="<?php esc_attr_e('Search', 'ai-zippy'); ?>" hidden>
			<div class="zs__modal-backdrop"></div>
			<div class="zs__modal-inner">
				<div class="zs__input-wrap">
					<?php echo $svg_search; // phpcs:ignore ?>
					<input
						class="zs__input"
						type="search"
						placeholder="<?php echo $placeholder; ?>"
						autocomplete="off"
						spellcheck="false"
						aria-label="<?php esc_attr_e('Search', 'ai-zippy'); ?>"
						aria-autocomplete="list"
						aria-controls="<?php echo esc_attr($uid); ?>-results"
					/>
					<button class="zs__clear" type="button" aria-label="<?php esc_attr_e('Clear search', 'ai-zippy'); ?>" hidden>
						<?php echo $svg_close; // phpcs:ignore ?>
					</button>
				</div>
				<div class="zs__results" id="<?php echo esc_attr($uid); ?>-results" role="listbox" aria-label="<?php esc_attr_e('Search results', 'ai-zippy'); ?>"></div>
				<div class="zs__footer">
					<span class="zs__hint"><kbd>↑↓</kbd> navigate &nbsp; <kbd>Enter</kbd> select &nbsp; <kbd>Esc</kbd> close</span>
				</div>
			</div>
		</div>

	<?php else : ?>

		<!-- Inline search bar -->
		<div class="zs__input-wrap">
			<?php echo $icon_markup; // phpcs:ignore ?>
			<input
				class="zs__input"
				type="search"
				placeholder="<?php echo $placeholder; ?>"
				autocomplete="off"
				spellcheck="false"
				aria-label="<?php esc_attr_e('Search', 'ai-zippy'); ?>"
				aria-autocomplete="list"
				aria-controls="<?php echo esc_attr($uid); ?>-results"
			/>
			<button class="zs__clear" type="button" aria-label="<?php esc_attr_e('Clear search', 'ai-zippy'); ?>" hidden>
				<?php echo $svg_close; // phpcs:ignore ?>
			</button>
		</div>
		<div class="zs__results zs__results--inline" id="<?php echo esc_attr($uid); ?>-results" role="listbox" aria-label="<?php esc_attr_e('Search results', 'ai-zippy'); ?>"></div>

	<?php endif; ?>

</div>
