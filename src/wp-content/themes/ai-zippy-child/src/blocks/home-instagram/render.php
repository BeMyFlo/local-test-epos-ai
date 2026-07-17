<?php
/**
 * Server-side render for Home Instagram block.
 *
 * @var array    $attributes Block attributes.
 * @var string   $content    Block inner content.
 * @var WP_Block $block      Block instance.
 */

defined('ABSPATH') || exit;

$heading       = $attributes['heading'] ?? 'fOLLOW US ON INSTAGRAM';
$images        = $attributes['images'] ?? [];
$instagram_url = $attributes['instagramUrl'] ?? 'https://www.instagram.com/achieverart/';

$wrapper_attributes = get_block_wrapper_attributes([
    'class' => 'achiever-instagram',
]);
?>

<div <?php echo $wrapper_attributes; ?>>
    <div class="achiever-instagram__container">
        <h2 class="achiever-instagram__heading"><?php echo esc_html($heading); ?></h2>

        <div class="achiever-instagram__grid">
            <?php foreach ($images as $image) :
                $url = $image['url'] ?? '';
                $alt = $image['alt'] ?? '';
            ?>
                <a href="<?php echo esc_url($instagram_url); ?>" class="achiever-instagram__item" target="_blank" rel="noopener noreferrer">
                    <?php if (!empty($url)) : ?>
                        <img src="<?php echo esc_url($url); ?>" alt="<?php echo esc_attr($alt); ?>" />
                    <?php else : ?>
                        <div class="achiever-instagram__placeholder"></div>
                    <?php endif; ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</div>
