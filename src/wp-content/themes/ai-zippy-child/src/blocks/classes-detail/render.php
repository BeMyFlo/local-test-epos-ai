<?php
/**
 * Server-side render for Classes Detail block.
 *
 * @var array $attributes Block attributes.
 */

defined('ABSPATH') || exit;

$sections       = $attributes['sections'] ?? [];
$gallery_title  = $attributes['galleryTitle'] ?? 'YOUR SMILE, OUR PASSION.';
$gallery_images = $attributes['galleryImages'] ?? [];
$gallery_track_id = wp_unique_id('achiever-classes-gallery-track-');

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
      <div class="achiever-classes-detail__inner">
        <div class="achiever-classes-detail__image-slot">
          <?php if ($image) : ?>
            <img src="<?php echo esc_url($image); ?>" alt="<?php echo esc_attr($alt); ?>" loading="lazy" />
          <?php else : ?>
            <svg class="achiever-classes-detail__fallback-art achiever-classes-detail__fallback-art--<?php echo esc_attr((string) (($index % 4) + 1)); ?>" viewBox="0 0 640 360" aria-hidden="true" focusable="false"><path d="M64 278 188 126l91 106 69-76 128 122Z"/><circle cx="444" cy="102" r="46"/><path d="M102 81c68-38 132-43 192-16M83 116c49-32 97-47 143-46"/><path d="m492 244 36-76 36 76-36 36Z"/></svg>
          <?php endif; ?>
        </div>
        <div class="achiever-classes-detail__copy">
          <?php if ($age) : ?><p class="achiever-classes-detail__age"><?php echo esc_html($age); ?></p><?php endif; ?>
          <h2><?php echo esc_html($title); ?></h2>
          <p><?php echo esc_html($description); ?></p>
          <?php if ($cta_text) : ?><a class="achiever-btn" href="<?php echo esc_url($cta_url); ?>"><?php echo esc_html($cta_text); ?></a><?php endif; ?>
        </div>
      </div>
    </section>
  <?php endforeach; ?>

  <section class="achiever-classes-detail__gallery">
    <h2><?php echo esc_html($gallery_title); ?></h2>
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
