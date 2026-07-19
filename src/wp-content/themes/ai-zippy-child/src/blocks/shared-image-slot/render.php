<?php
/**
 * Server-side render for the shared decorative image slot.
 *
 * @var array $attributes Block attributes.
 */

defined('ABSPATH') || exit;

$image_url = $attributes['imageUrl'] ?? '';
$alt       = $attributes['alt'] ?? '';

if (!$image_url) {
    return;
}

$wrapper_attributes = get_block_wrapper_attributes([
    'class' => 'achiever-header__decor',
]);
?>
<div <?php echo $wrapper_attributes; ?>>
  <img src="<?php echo esc_url($image_url); ?>" alt="<?php echo esc_attr($alt); ?>" loading="lazy" />
</div>
