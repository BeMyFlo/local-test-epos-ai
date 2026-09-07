<?php
/**
 * Server-side render for Classes Detail block.
 *
 * @var array $attributes Block attributes.
 */

defined('ABSPATH') || exit;

$sections       = $attributes['sections'] ?? [];
$gallery_title  = $attributes['galleryTitle'] ?? 'YOUR SMILE, OUR PASSION.';
$gallery_mascot = $attributes['galleryMascotImage'] ?? '/wp-content/uploads/2026/07/ChatGPT_Image_Jul_24__2026__11_33_59_AM-removebg-preview.png';
$gallery_mascot_alt = $attributes['galleryMascotAlt'] ?? "Achiever's Arts mascot";
$gallery_mascot_style = function_exists('ai_zippy_child_decor_style')
  ? ai_zippy_child_decor_style($attributes, 'galleryMascot', ['zIndex' => 2, 'desktopX' => 12.5967325881, 'desktopY' => 21.5631443299, 'desktopSize' => 245, 'mobileX' => 19.2, 'mobileY' => 18.6666666667, 'mobileSize' => 132])
  : '';
$gallery_images = $attributes['galleryImages'] ?? [];
$gallery_track_id = wp_unique_id('achiever-classes-gallery-track-');
$decor_defaults = [
  'topPencil' => ['zIndex' => 5, 'desktopX' => 14, 'desktopY' => 0, 'desktopSize' => 140, 'mobileX' => 28, 'mobileY' => 0, 'mobileSize' => 140],
  'leftPencil' => ['zIndex' => 5, 'desktopX' => 3, 'desktopY' => 94, 'desktopSize' => 80, 'mobileX' => 15, 'mobileY' => 94, 'mobileSize' => 80],
  'scissors' => ['zIndex' => 10, 'desktopX' => 50, 'desktopY' => 100, 'desktopSize' => 90, 'mobileX' => 50, 'mobileY' => 100, 'mobileSize' => 90],
  'bottomPencil' => ['zIndex' => 5, 'desktopX' => 95, 'desktopY' => 95, 'desktopSize' => 110, 'mobileX' => 76, 'mobileY' => 94, 'mobileSize' => 110],
];
$decor_style = static function (string $prefix) use ($attributes, $decor_defaults): string {
  return function_exists('ai_zippy_child_decor_style')
    ? ai_zippy_child_decor_style($attributes, $prefix, $decor_defaults[$prefix])
    : '';
};

$wrapper_attributes = get_block_wrapper_attributes(['class' => 'achiever-classes-detail']);
?>
<div <?php echo $wrapper_attributes; ?>>
  <?php foreach ($sections as $index => $section) :
    $title       = $section['title'] ?? '';
    $age         = $section['age'] ?? '';
    $description = $section['description'] ?? '';
    $image       = $section['image'] ?? '';
    $alt         = $section['alt'] ?? $title;
    $cta_text    = $section['ctaText'] ?? '';
    $cta_url     = $section['ctaUrl'] ?? '#';
  ?>
    <section class="achiever-classes-detail__feature achiever-classes-detail__feature--<?php echo esc_attr((string) ($index + 1)); ?>">
      <?php if ($index === 0) : ?>
        <div class="achiever-classes-detail__decor achiever-classes-detail__decor--top-pencil" style="<?php echo esc_attr($decor_style('topPencil')); ?>">
          <?php if (!empty($attributes['topPencilImage'])) : ?><img src="<?php echo esc_url($attributes['topPencilImage']); ?>" alt="<?php echo esc_attr($attributes['topPencilAlt'] ?? ''); ?>" loading="lazy" /><?php else : ?><svg viewBox="0 0 160 30" aria-hidden="true" focusable="false"><path d="M10 20L130 5L150 15L130 25L10 20Z" fill="#7b61ff"/><path d="M10 20L30 18L30 22Z" fill="#ffb800"/></svg><?php endif; ?>
        </div>
        <div class="achiever-classes-detail__decor achiever-classes-detail__decor--left-pencil" style="<?php echo esc_attr($decor_style('leftPencil')); ?>">
          <?php if (!empty($attributes['leftPencilImage'])) : ?><img src="<?php echo esc_url($attributes['leftPencilImage']); ?>" alt="<?php echo esc_attr($attributes['leftPencilAlt'] ?? ''); ?>" loading="lazy" /><?php else : ?><svg viewBox="0 0 80 30" aria-hidden="true" focusable="false"><path d="M0 15L60 5L75 15L60 25L0 15Z" fill="#2ecc71"/><path d="M75 15L85 15L75 20Z" fill="#ffb800"/></svg><?php endif; ?>
        </div>
      <?php endif; ?>

      <div class="achiever-classes-detail__inner">
        <?php if ($index % 2 === 1) : ?>
          <div class="achiever-classes-detail__image-slot">
            <?php if ($image) : ?>
              <img src="<?php echo esc_url($image); ?>" alt="<?php echo esc_attr($alt); ?>" loading="lazy" />
            <?php endif; ?>
          </div>
        <?php endif; ?>

        <div class="achiever-classes-detail__copy">
          <?php if ($age) : ?><p class="achiever-classes-detail__age"><?php echo esc_html($age); ?></p><?php endif; ?>
          <h2><?php echo esc_html($title); ?></h2>
          <p><?php echo nl2br(esc_html(achiever_normalize_newlines($description))); ?></p>
          <?php if ($cta_text) : ?><a class="achiever-btn" href="<?php echo esc_url($cta_url); ?>"><?php echo esc_html($cta_text); ?></a><?php endif; ?>
        </div>

        <?php if ($index % 2 === 0) : ?>
          <div class="achiever-classes-detail__image-slot">
            <?php if ($image) : ?>
              <img src="<?php echo esc_url($image); ?>" alt="<?php echo esc_attr($alt); ?>" loading="lazy" />
            <?php endif; ?>
          </div>
        <?php endif; ?>
      </div>

      <?php if ($index === 0) : ?>
        <div class="achiever-classes-detail__decor achiever-classes-detail__decor--scissors" style="<?php echo esc_attr($decor_style('scissors')); ?>">
          <?php if (!empty($attributes['scissorsImage'])) : ?><img src="<?php echo esc_url($attributes['scissorsImage']); ?>" alt="<?php echo esc_attr($attributes['scissorsAlt'] ?? ''); ?>" loading="lazy" /><?php else : ?><svg viewBox="0 0 100 80" aria-hidden="true" focusable="false"><path d="M20 20C10 20 5 30 15 40L45 45L15 50C5 60 10 70 20 70C30 70 35 55 45 45L75 75L85 65L45 45L85 25L75 15L45 45C35 35 30 20 20 20Z" fill="#ff4d6d"/><circle cx="20" cy="30" r="5" fill="#ffffff"/><circle cx="20" cy="60" r="5" fill="#ffffff"/><polygon points="45,45 95,50 85,75" fill="#fff59d"/></svg><?php endif; ?>
        </div>
      <?php endif; ?>

      <?php if ($index === 1) : ?>
        <div class="achiever-classes-detail__decor achiever-classes-detail__decor--bottom-pencil" style="<?php echo esc_attr($decor_style('bottomPencil')); ?>">
          <?php if (!empty($attributes['bottomPencilImage'])) : ?><img src="<?php echo esc_url($attributes['bottomPencilImage']); ?>" alt="<?php echo esc_attr($attributes['bottomPencilAlt'] ?? ''); ?>" loading="lazy" /><?php else : ?><svg viewBox="0 0 120 30" aria-hidden="true" focusable="false"><path d="M0 15L100 5L115 15L100 25L0 15Z" fill="#9b51e0"/><path d="M115 15L125 15L115 20Z" fill="#ffb800"/></svg><?php endif; ?>
        </div>
      <?php endif; ?>
    </section>
  <?php endforeach; ?>

  <section class="achiever-classes-detail__gallery">
    <div class="achiever-classes-detail__gallery-header">
      <?php if ($gallery_mascot) : ?><img class="achiever-classes-detail__mascot-sticker" src="<?php echo esc_url($gallery_mascot); ?>" alt="<?php echo esc_attr($gallery_mascot_alt); ?>" loading="lazy" style="<?php echo esc_attr($gallery_mascot_style); ?>" /><?php endif; ?>
      <h2><?php echo esc_html($gallery_title); ?></h2>
    </div>
    <div class="achiever-classes-detail__gallery-slider" data-classes-gallery-slider>
      <button class="achiever-classes-detail__gallery-arrow achiever-classes-detail__gallery-arrow--prev" type="button" aria-label="Previous gallery images" aria-controls="<?php echo esc_attr($gallery_track_id); ?>" data-classes-gallery-prev>‹</button>
      <div class="achiever-classes-detail__gallery-grid" id="<?php echo esc_attr($gallery_track_id); ?>" role="list" tabindex="0" aria-label="Student artwork gallery" data-classes-gallery-track>
      <?php foreach ($gallery_images as $index => $image) :
        $url = $image['url'] ?? '';
        $alt = $image['alt'] ?? '';
      ?>
        <div class="achiever-classes-detail__gallery-item" role="listitem">
          <?php if ($url) : ?><img src="<?php echo esc_url($url); ?>" alt="<?php echo esc_attr($alt); ?>" loading="lazy" /><?php else : ?><svg class="achiever-classes-detail__gallery-fallback achiever-classes-detail__gallery-fallback--<?php echo esc_attr((string) (($index % 4) + 1)); ?>" viewBox="0 0 360 260" role="img" aria-label="<?php echo esc_attr($alt); ?>"><path d="M30 209 112 104l60 72 47-53 96 86Z"/><circle cx="270" cy="66" r="31"/><path d="M69 59c42-24 86-28 126-12M55 82c31-21 64-30 95-29"/></svg><?php endif; ?>
        </div>
      <?php endforeach; ?>
      </div>
      <button class="achiever-classes-detail__gallery-arrow achiever-classes-detail__gallery-arrow--next" type="button" aria-label="Next gallery images" aria-controls="<?php echo esc_attr($gallery_track_id); ?>" data-classes-gallery-next>›</button>
    </div>
  </section>
</div>
