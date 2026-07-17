<?php
/**
 * Server-side render for ai-zippy/home-hero block.
 * Supports multiple slides with auto-rotate.
 *
 * @var array    $attributes Block attributes.
 * @var string   $content    Block content (empty for dynamic blocks).
 * @var WP_Block $block      Block instance.
 */

defined('ABSPATH') || exit;

$tagline          = $attributes['tagline'] ?? "Let's Artventure";
$tagline_subtitle = $attributes['taglineSubtitle'] ?? '';
$slides           = $attributes['slides'] ?? [];
$autoplay         = $attributes['autoplay'] ?? true;
$autoplay_speed   = $attributes['autoplaySpeed'] ?? 5000;
$bg_image         = $attributes['backgroundImage'] ?? '';
$decor_left       = $attributes['decorLeftImage'] ?? '';
$decor_right      = $attributes['decorRightImage'] ?? '';
$decor_bottom     = $attributes['decorBottomImage'] ?? '';

$bg_style = $bg_image ? ' style="background-image: url(' . esc_url($bg_image) . ');"' : '';

// Fallback if no slides defined
if (empty($slides)) {
    $slides = [[
        'heading'     => "REGULAR\nART CLASSES",
        'description' => 'Structured weekly art programmes that build strong creative foundations.',
        'ctaText'     => 'BOOK NOW',
        'ctaUrl'      => '/regular-art-classes/',
    ]];
}
?>
<div <?php echo get_block_wrapper_attributes(); ?>>
  <section class="achiever-hero"<?php echo $bg_style; ?>
    data-autoplay="<?php echo $autoplay ? 'true' : 'false'; ?>"
    data-speed="<?php echo esc_attr($autoplay_speed); ?>">

    <?php if ($decor_left) : ?>
      <img src="<?php echo esc_url($decor_left); ?>" alt="" class="achiever-hero__decor achiever-hero__decor--left" loading="lazy" />
    <?php endif; ?>
    <?php if ($decor_right) : ?>
      <img src="<?php echo esc_url($decor_right); ?>" alt="" class="achiever-hero__decor achiever-hero__decor--right" loading="lazy" />
    <?php endif; ?>

    <div class="achiever-hero__container">
      <span class="achiever-hero__tagline"><?php echo esc_html($tagline); ?></span>
      <p class="achiever-hero__tagline-sub"><?php echo esc_html($tagline_subtitle); ?></p>

      <div class="achiever-hero__slider">
        <button class="achiever-hero__arrow achiever-hero__arrow--prev" aria-label="Previous">&#10094;</button>

        <div class="achiever-hero__slides">
          <?php foreach ($slides as $index => $slide) :
            $heading = $slide['heading'] ?? '';
            $desc    = $slide['description'] ?? '';
            $cta_txt = $slide['ctaText'] ?? '';
            $cta_url = $slide['ctaUrl'] ?? '#';
            $active  = $index === 0 ? ' achiever-hero__slide--active' : '';
          ?>
            <div class="achiever-hero__slide<?php echo $active; ?>" data-slide="<?php echo $index; ?>">
              <h2 class="achiever-hero__heading"><?php echo nl2br(esc_html($heading)); ?></h2>
              <?php if ($desc) : ?>
                <p class="achiever-hero__description"><?php echo esc_html($desc); ?></p>
              <?php endif; ?>
              <?php if ($cta_txt) : ?>
                <a href="<?php echo esc_url($cta_url); ?>" class="achiever-btn achiever-btn--hero">
                  <?php echo esc_html($cta_txt); ?>
                </a>
              <?php endif; ?>
            </div>
          <?php endforeach; ?>
        </div>

        <button class="achiever-hero__arrow achiever-hero__arrow--next" aria-label="Next">&#10095;</button>
      </div>

      <?php if (count($slides) > 1) : ?>
        <div class="achiever-hero__dots">
          <?php foreach ($slides as $index => $slide) : ?>
            <button class="achiever-hero__dot<?php echo $index === 0 ? ' achiever-hero__dot--active' : ''; ?>"
              data-goto="<?php echo $index; ?>" aria-label="Slide <?php echo $index + 1; ?>"></button>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>

    <?php if ($decor_bottom) : ?>
      <img src="<?php echo esc_url($decor_bottom); ?>" alt="" class="achiever-hero__decor achiever-hero__decor--bottom" loading="lazy" />
    <?php endif; ?>
  </section>
</div>
