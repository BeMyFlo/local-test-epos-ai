<?php
/**
 * Server-side render for Home Testimonials block.
 *
 * @var array    $attributes Block attributes.
 * @var string   $content    Block inner content.
 * @var WP_Block $block      Block instance.
 */

defined('ABSPATH') || exit;

$heading      = $attributes['heading'] ?? 'WHAT OUR CLIENT LOVE ABOUT US';
$decor_left_image  = $attributes['decorLeftImage'] ?? '';
$decor_left_alt    = $attributes['decorLeftAlt'] ?? 'Cartoon mascot left decoration';
$decor_right_image = $attributes['decorRightImage'] ?? '';
$decor_right_alt   = $attributes['decorRightAlt'] ?? 'Pencil cartoon right decoration';
$testimonials = $attributes['testimonials'] ?? [];

$wrapper_attributes = get_block_wrapper_attributes([
    'class' => 'achiever-testimonials',
]);
?>

<div <?php echo $wrapper_attributes; ?>>
    <?php if ($decor_left_image) : ?>
        <div class="achiever-testimonials__decor achiever-testimonials__decor--left">
            <img src="<?php echo esc_url($decor_left_image); ?>" alt="<?php echo esc_attr($decor_left_alt); ?>" loading="lazy" />
        </div>
    <?php endif; ?>
    <?php if ($decor_right_image) : ?>
        <div class="achiever-testimonials__decor achiever-testimonials__decor--right">
            <img src="<?php echo esc_url($decor_right_image); ?>" alt="<?php echo esc_attr($decor_right_alt); ?>" loading="lazy" />
        </div>
    <?php endif; ?>
    <div class="achiever-testimonials__container">
        <h2 class="achiever-testimonials__heading"><?php echo esc_html($heading); ?></h2>

        <div class="achiever-testimonials__grid">
            <?php foreach ($testimonials as $testimonial) :
                $name   = $testimonial['name'] ?? '';
                $quote  = $testimonial['quote'] ?? '';
            ?>
                <div class="achiever-testimonials__card">
                    <div class="achiever-testimonials__author">
                        <span class="achiever-testimonials__name"><?php echo esc_html($name); ?></span>
                    </div>
                    <blockquote class="achiever-testimonials__quote">
                        <?php echo esc_html($quote); ?>
                    </blockquote>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
