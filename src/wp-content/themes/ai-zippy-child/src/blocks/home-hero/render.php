<?php
/**
 * Server-side render for ai-zippy/home-hero.
 *
 * @var array $attributes Block attributes.
 */

defined('ABSPATH') || exit;

$slides           = $attributes['slides'] ?? [];
$tagline          = $attributes['tagline'] ?? "Let's Artventure";
$tagline_description = $attributes['taglineDescription'] ?? 'Discover our creative journey through art classes, camps, courses and more, designed for artists of all ages.';
$paint_jar_image  = $attributes['paintJarImage'] ?? '';
$paint_jar_alt    = $attributes['paintJarAlt'] ?? 'Paint jar decoration';
$paint_jar_z_index = max(-1, min(50, (int) ($attributes['paintJarZIndex'] ?? 2)));
$paint_jar_desktop_x = max(0, min(100, (int) ($attributes['paintJarDesktopX'] ?? 93)));
$paint_jar_desktop_y = max(-50, min(150, (int) ($attributes['paintJarDesktopY'] ?? 92)));
$paint_jar_desktop_size = max(40, min(500, (int) ($attributes['paintJarDesktopSize'] ?? 90)));
$paint_jar_mobile_x = max(0, min(100, (int) ($attributes['paintJarMobileX'] ?? 88)));
$paint_jar_mobile_y = max(-50, min(150, (int) ($attributes['paintJarMobileY'] ?? 91)));
$paint_jar_mobile_size = max(32, min(500, (int) ($attributes['paintJarMobileSize'] ?? 90)));
$autoplay         = $attributes['autoplay'] ?? false;
$autoplay_speed   = max(2000, min(10000, (int) ($attributes['autoplaySpeed'] ?? 5000)));
$background_image = $attributes['backgroundImage'] ?? '';

if (empty($slides)) {
    $slides = [
      [
        'heading'     => "REGULAR\nART CLASSES",
        'description' => 'Weekly guided lessons that build strong creative foundations through progressive skill development and exploration of different art mediums.',
        'ctaText'     => 'BOOK NOW',
        'ctaUrl'      => '#',
      ],
      [
        'heading' => "ARTY EVENTS\n& PARTIES",
        'description' => 'Birthday parties, group bookings, and corporate events full of creativity, laughter and hands-on art fun.',
        'ctaText' => 'ENQUIRE NOW',
        'ctaUrl' => '#',
      ],
      [
        'heading' => "CAMPS &\nCOURSES",
        'description' => 'Holiday camps and short courses designed to spark imagination and build new skills in a fun, immersive setting.',
        'ctaText' => 'EXPLORE NOW',
        'ctaUrl' => '#',
      ],
    ];
}

$slide_ids = array_map(
    static fn(): string => wp_unique_id('achiever-hero-slide-'),
    $slides
);

$wrapper_attributes = get_block_wrapper_attributes([
    'class' => 'achiever-hero',
]);
$slides_id = wp_unique_id('achiever-hero-slides-');
$bg_style = $background_image ? ' style="background-image: url(' . esc_url($background_image) . ') !important;"' : '';
$paint_jar_style = sprintf(
    '--paint-jar-z-index:%d;--paint-jar-desktop-x:%d;--paint-jar-desktop-y:%d;--paint-jar-desktop-size:%d;--paint-jar-mobile-x:%d;--paint-jar-mobile-y:%d;--paint-jar-mobile-size:%d;',
    $paint_jar_z_index,
    $paint_jar_desktop_x,
    $paint_jar_desktop_y,
    $paint_jar_desktop_size,
    $paint_jar_mobile_x,
    $paint_jar_mobile_y,
    $paint_jar_mobile_size
);
?>
<section <?php echo $wrapper_attributes; ?><?php echo $bg_style; ?>
  data-autoplay="<?php echo $autoplay ? 'true' : 'false'; ?>"
  data-speed="<?php echo esc_attr((string) $autoplay_speed); ?>">
  <div class="achiever-hero__tagline">
    <p class="achiever-hero__tagline-title"><?php echo esc_html($tagline); ?></p>
    <p class="achiever-hero__tagline-copy"><?php echo esc_html($tagline_description); ?></p>
  </div>
  <div class="achiever-hero__content">
    <div class="achiever-hero__paint-jar<?php echo $paint_jar_image ? '' : ' achiever-hero__paint-jar--empty'; ?>" style="<?php echo esc_attr($paint_jar_style); ?>">
      <?php if ($paint_jar_image) : ?><img src="<?php echo esc_url($paint_jar_image); ?>" alt="<?php echo esc_attr($paint_jar_alt); ?>" loading="lazy" /><?php endif; ?>
    </div>
    <div class="achiever-hero__slider">
      <?php if (count($slides) > 1) : ?>
        <button type="button" class="achiever-hero__arrow achiever-hero__arrow--prev" aria-controls="<?php echo esc_attr($slides_id); ?>" aria-label="Previous slide">&#8249;</button>
      <?php endif; ?>

      <div id="<?php echo esc_attr($slides_id); ?>" class="achiever-hero__slides" aria-live="polite">
        <?php foreach ($slides as $index => $slide) :
            $heading     = $slide['heading'] ?? '';
            $description = $slide['description'] ?? '';
            $cta_text    = $slide['ctaText'] ?? '';
            $cta_url     = $slide['ctaUrl'] ?? '#';
            $is_active   = 0 === $index;
        ?>
          <div id="<?php echo esc_attr($slide_ids[$index]); ?>"
            class="achiever-hero__slide<?php echo $is_active ? ' achiever-hero__slide--active' : ''; ?>"
            data-slide="<?php echo esc_attr((string) $index); ?>"
            aria-hidden="<?php echo $is_active ? 'false' : 'true'; ?>">
            <h1 class="achiever-hero__heading"><?php echo nl2br(esc_html($heading)); ?></h1>
            <?php if ($description) : ?>
              <p class="achiever-hero__description"><?php echo esc_html($description); ?></p>
            <?php endif; ?>
            <?php if ($cta_text) : ?>
              <a class="achiever-btn achiever-btn--hero" href="<?php echo esc_url($cta_url); ?>">
                <?php echo esc_html($cta_text); ?>
              </a>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
      </div>

      <?php if (count($slides) > 1) : ?>
        <button type="button" class="achiever-hero__arrow achiever-hero__arrow--next" aria-controls="<?php echo esc_attr($slides_id); ?>" aria-label="Next slide">&#8250;</button>
      <?php endif; ?>
    </div>

    <?php if (count($slides) > 1) : ?>
      <div class="achiever-hero__dots" aria-label="Hero slides">
        <?php foreach ($slides as $index => $_slide) : ?>
          <button type="button"
            class="achiever-hero__dot<?php echo 0 === $index ? ' achiever-hero__dot--active' : ''; ?>"
            data-goto="<?php echo esc_attr((string) $index); ?>"
            aria-controls="<?php echo esc_attr($slide_ids[$index]); ?>"
            aria-current="<?php echo 0 === $index ? 'true' : 'false'; ?>"
            aria-label="Go to slide <?php echo esc_attr((string) ($index + 1)); ?>"></button>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</section>
