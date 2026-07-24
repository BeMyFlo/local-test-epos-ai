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
$decor_left_style  = ai_zippy_child_decor_style($attributes, 'decorLeft', ['zIndex' => 5, 'desktopX' => 8, 'desktopY' => 85, 'desktopSize' => 110, 'mobileX' => 12, 'mobileY' => 85, 'mobileSize' => 75]);
$decor_right_style = ai_zippy_child_decor_style($attributes, 'decorRight', ['zIndex' => 5, 'desktopX' => 92, 'desktopY' => 85, 'desktopSize' => 110, 'mobileX' => 88, 'mobileY' => 85, 'mobileSize' => 75]);
$testimonials = $attributes['testimonials'] ?? [];

if (empty($testimonials)) {
    $testimonials = [
        [
            'name'  => 'Sarah L. (Parent of 6 y/o)',
            'quote' => 'My daughter looks forward to her weekly art class every single week! The instructors are patient, nurturing, and truly inspire creativity.',
        ],
        [
            'name'  => 'David Tan (Parent of 10 y/o)',
            'quote' => 'The holiday camps are fantastic. My son learned so many new techniques in manga drawing and built confidence in his artistic abilities.',
        ],
        [
            'name'  => 'Michelle K. (Birthday Party Host)',
            'quote' => 'We hosted my daughter’s 8th birthday party here. Everything was seamlessly managed, creative, and all the kids had an absolute blast!',
        ],
        [
            'name'  => 'Rachel Ng (Parent of 8 y/o)',
            'quote' => 'The studio environment is so warm and welcoming. My child has developed real artistic confidence and loves showing off her projects.',
        ],
    ];
}

$wrapper_attributes = get_block_wrapper_attributes([
    'class' => 'achiever-testimonials',
]);
?>

<div <?php echo $wrapper_attributes; ?>>
    <?php if ($decor_left_image) : ?>
        <div class="achiever-testimonials__decor achiever-testimonials__decor--left" style="<?php echo esc_attr($decor_left_style); ?>">
            <img src="<?php echo esc_url($decor_left_image); ?>" alt="<?php echo esc_attr($decor_left_alt); ?>" loading="lazy" />
        </div>
    <?php endif; ?>
    <?php if ($decor_right_image) : ?>
        <div class="achiever-testimonials__decor achiever-testimonials__decor--right" style="<?php echo esc_attr($decor_right_style); ?>">
            <img src="<?php echo esc_url($decor_right_image); ?>" alt="<?php echo esc_attr($decor_right_alt); ?>" loading="lazy" />
        </div>
    <?php endif; ?>
    <?php
    $track_id = wp_unique_id('achiever-testimonials-track-');
    ?>
    <div class="achiever-testimonials__container">
        <h2 class="achiever-testimonials__heading"><?php echo esc_html($heading); ?></h2>

        <div class="achiever-scroll-slider" data-scroll-slider>
            <button type="button" class="achiever-scroll-arrow achiever-scroll-arrow--prev" data-scroll-prev aria-controls="<?php echo esc_attr($track_id); ?>" aria-label="Previous testimonials">&#8249;</button>
            <div id="<?php echo esc_attr($track_id); ?>" class="achiever-testimonials__grid achiever-scroll-track" data-scroll-track tabindex="0">
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
            <button type="button" class="achiever-scroll-arrow achiever-scroll-arrow--next" data-scroll-next aria-controls="<?php echo esc_attr($track_id); ?>" aria-label="Next testimonials">&#8250;</button>
        </div>

        <?php if (count($testimonials) > 1) : ?>
            <div class="achiever-testimonials__dots" aria-hidden="true">
                <?php foreach ($testimonials as $index => $_testimonial) : ?>
                    <span class="achiever-testimonials__dot<?php echo 0 === $index ? ' achiever-testimonials__dot--active' : ''; ?>"></span>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
