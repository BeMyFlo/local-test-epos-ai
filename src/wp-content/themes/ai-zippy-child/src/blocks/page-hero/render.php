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
$cta_text = $attributes['ctaText'] ?? '';
$cta_url  = $attributes['ctaUrl'] ?? '#';
$variant  = $attributes['variant'] ?? 'pink';

$wrapper_attributes = get_block_wrapper_attributes([
    'class' => 'achiever-page-hero achiever-page-hero--' . sanitize_html_class($variant),
]);
?>

<section <?php echo $wrapper_attributes; ?>>
    <div class="achiever-page-hero__clouds" aria-hidden="true"></div>
    <div class="achiever-page-hero__rainbow" aria-hidden="true"></div>
    <div class="achiever-page-hero__mascot" aria-hidden="true">♡</div>
    <div class="achiever-page-hero__pencils" aria-hidden="true"></div>

    <div class="achiever-page-hero__inner">
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
