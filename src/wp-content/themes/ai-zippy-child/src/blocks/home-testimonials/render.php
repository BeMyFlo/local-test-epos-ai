<?php
/**
 * Server-side render for Home Testimonials block.
 *
 * @var array    $attributes Block attributes.
 * @var string   $content    Block inner content.
 * @var WP_Block $block      Block instance.
 */

defined('ABSPATH') || exit;

$heading      = $attributes['heading'] ?? "WHAT OUR CLIENT\nLOVE ABOUT US";
$testimonials = $attributes['testimonials'] ?? [];

$wrapper_attributes = get_block_wrapper_attributes([
    'class' => 'achiever-testimonials',
]);
?>

<div <?php echo $wrapper_attributes; ?>>
    <div class="achiever-testimonials__container">
        <h2 class="achiever-testimonials__heading"><?php echo nl2br(esc_html($heading)); ?></h2>

        <div class="achiever-testimonials__grid">
            <?php foreach ($testimonials as $testimonial) :
                $name   = $testimonial['name'] ?? '';
                $quote  = $testimonial['quote'] ?? '';
                $avatar = $testimonial['avatar'] ?? '';
            ?>
                <div class="achiever-testimonials__card">
                    <blockquote class="achiever-testimonials__quote">
                        <?php echo wp_kses_post($quote); ?>
                    </blockquote>
                    <div class="achiever-testimonials__author">
                        <?php if (!empty($avatar)) : ?>
                            <img class="achiever-testimonials__avatar" src="<?php echo esc_url($avatar); ?>" alt="<?php echo esc_attr($name); ?>" />
                        <?php else : ?>
                            <div class="achiever-testimonials__avatar-placeholder"></div>
                        <?php endif; ?>
                        <span class="achiever-testimonials__name"><?php echo esc_html($name); ?></span>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
