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
$testimonials = $attributes['testimonials'] ?? [];

$wrapper_attributes = get_block_wrapper_attributes([
    'class' => 'achiever-testimonials',
]);
?>

<div <?php echo $wrapper_attributes; ?>>
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
