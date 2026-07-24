<?php
/**
 * Server-side render for Achiever Page Hero block.
 *
 * @var array $attributes Block attributes.
 */

defined('ABSPATH') || exit;

$eyebrow  = $attributes['eyebrow'] ?? "Achiever's Art";
$heading  = $attributes['heading'] ?? 'Page Title';
$subtitle = $attributes['subtitle'] ?? 'Discover your creative journey through art.';
$breadcrumb_home_text = $attributes['breadcrumbHomeText'] ?? 'Home';
$breadcrumb_home_url  = $attributes['breadcrumbHomeUrl'] ?? '/';
$breadcrumb_current   = $attributes['breadcrumbCurrent'] ?? '';
$background_image     = $attributes['backgroundImage'] ?? '';
$background_alt       = $attributes['backgroundAlt'] ?? '';
$mascot_image          = $attributes['mascotImage'] ?: home_url('/wp-content/uploads/2026/07/Sensory-Playhaus_COLOR.png');
$mascot_alt            = $attributes['mascotAlt'] ?: 'Achiever Art mascot doodle';
$mascot_style          = ai_zippy_child_decor_style($attributes, 'mascot', ['zIndex' => 5, 'desktopX' => 88, 'desktopY' => 68, 'desktopSize' => 220, 'mobileX' => 82, 'mobileY' => 78, 'mobileSize' => 120]);
$cta_text = $attributes['ctaText'] ?? '';
$cta_url  = $attributes['ctaUrl'] ?? '#';
$variant  = $attributes['variant'] ?? 'pink';

$wrapper_attributes = get_block_wrapper_attributes([
    'class' => 'achiever-page-hero achiever-page-hero--' . sanitize_html_class($variant),
]);
?>

<section <?php echo $wrapper_attributes; ?>>
    <?php if ($background_image) : ?>
        <img class="achiever-page-hero__background" src="<?php echo esc_url($background_image); ?>" alt="<?php echo esc_attr($background_alt); ?>" loading="eager" fetchpriority="high" />
    <?php endif; ?>
    <div class="achiever-page-hero__clouds" aria-hidden="true"></div>
    <?php if ($mascot_image) : ?>
        <img class="achiever-page-hero__mascot achiever-page-hero__mascot--image" src="<?php echo esc_url($mascot_image); ?>" alt="<?php echo esc_attr($mascot_alt); ?>" loading="lazy" style="<?php echo esc_attr($mascot_style); ?>" />
    <?php else : ?>
        <div class="achiever-page-hero__mascot" aria-hidden="true">♡</div>
    <?php endif; ?>

    <div class="achiever-page-hero__inner">
        <?php if ($breadcrumb_current) : ?>
            <nav class="achiever-page-hero__breadcrumb" aria-label="Breadcrumb">
                <a href="<?php echo esc_url($breadcrumb_home_url); ?>"><?php echo esc_html($breadcrumb_home_text); ?></a>
                <span aria-hidden="true">&gt;</span>
                <span aria-current="page"><?php echo esc_html($breadcrumb_current); ?></span>
            </nav>
        <?php endif; ?>

        <?php if ($eyebrow) : ?>
            <p class="achiever-page-hero__eyebrow"><?php echo esc_html($eyebrow); ?></p>
        <?php endif; ?>

        <h1 class="achiever-page-hero__heading"><?php echo nl2br(esc_html($heading)); ?></h1>

        <?php if ($subtitle) : ?>
            <p class="achiever-page-hero__subtitle"><?php echo esc_html($subtitle); ?></p>
        <?php endif; ?>

        <?php if ($cta_text) : ?>
            <a class="achiever-btn achiever-btn--hero achiever-page-hero__cta" href="<?php echo esc_url($cta_url); ?>">
                <?php echo esc_html($cta_text); ?>
            </a>
        <?php endif; ?>
    </div>
</section>
