<?php
/**
 * Server-side render for Home Party block.
 *
 * @var array    $attributes Block attributes.
 * @var string   $content    Block inner content.
 * @var WP_Block $block      Block instance.
 */

defined('ABSPATH') || exit;

$pre_heading     = $attributes['preHeading'] ?? "IT'S";
$heading         = $attributes['heading'] ?? 'PARTY TIME!';
$subtitle        = $attributes['subtitle'] ?? 'BIRTHDAYS · GROUP BOOKINGS · CORPORATE EVENTS · TEAM BONDING';
$images          = $attributes['images'] ?? [];
$cta_text        = $attributes['ctaText'] ?? 'VIEW MORE WORKSHOP';
$cta_url         = $attributes['ctaUrl'] ?? '/arty-events-parties/';
$decor_left      = $attributes['decorLeftImage'] ?? '';
$decor_right     = $attributes['decorRightImage'] ?? '';

$wrapper_attributes = get_block_wrapper_attributes([
    'class' => 'achiever-party',
]);
?>

<div <?php echo $wrapper_attributes; ?>>
    <?php if ($decor_left) : ?>
        <img src="<?php echo esc_url($decor_left); ?>" alt="" class="achiever-party__decor achiever-party__decor--left" loading="lazy" />
    <?php endif; ?>
    <?php if ($decor_right) : ?>
        <img src="<?php echo esc_url($decor_right); ?>" alt="" class="achiever-party__decor achiever-party__decor--right" loading="lazy" />
    <?php endif; ?>

    <div class="achiever-party__header">
        <span class="achiever-party__pre-heading"><?php echo esc_html($pre_heading); ?></span>
        <h2 class="achiever-party__heading"><?php echo esc_html($heading); ?></h2>
    </div>

    <div class="achiever-party__slider" data-party-slider>
        <button class="achiever-party__arrow achiever-party__arrow--prev" aria-label="Previous">&lsaquo;</button>
        <div class="achiever-party__track">
            <?php foreach ($images as $image) :
                $url = $image['url'] ?? '';
                $alt = $image['alt'] ?? '';
            ?>
                <div class="achiever-party__slide">
                    <?php if (!empty($url)) : ?>
                        <img src="<?php echo esc_url($url); ?>" alt="<?php echo esc_attr($alt); ?>" loading="lazy" />
                    <?php else : ?>
                        <div class="achiever-party__slide-placeholder"></div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
        <button class="achiever-party__arrow achiever-party__arrow--next" aria-label="Next">&rsaquo;</button>
    </div>

    <p class="achiever-party__subtitle"><?php echo esc_html($subtitle); ?></p>
    <a href="<?php echo esc_url($cta_url); ?>" class="achiever-btn achiever-btn--primary"><?php echo esc_html($cta_text); ?></a>
</div>
