<?php
defined('ABSPATH') || exit;

$heading = $attributes['heading'] ?? "BEYOND\nTHE CANVAS";
$description = $attributes['description'] ?? 'Beyond The Canvas by The Artivity Collective brings together a unique family of creative brands and experiences designed to inspire self-expression, hands-on learning, and artistic exploration for all ages.';
$decor_image = $attributes['decorImage'] ?? '';
$decor_alt = $attributes['decorAlt'] ?? 'Cat decoration';
$decor_left_image  = $attributes['decorLeftImage'] ?? '';
$decor_left_alt    = $attributes['decorLeftAlt'] ?? 'Cartoon mascot left decoration';
$decor_right_image = $attributes['decorRightImage'] ?? '';
$decor_right_alt   = $attributes['decorRightAlt'] ?? 'Cartoon mascot right decoration';
$decor_style       = ai_zippy_child_decor_style($attributes, 'decor', ['zIndex' => 5, 'desktopX' => 8, 'desktopY' => 15, 'desktopSize' => 60, 'mobileX' => 12, 'mobileY' => 12, 'mobileSize' => 60]);
$decor_left_style  = ai_zippy_child_decor_style($attributes, 'decorLeft', ['zIndex' => 5, 'desktopX' => 8, 'desktopY' => 85, 'desktopSize' => 120, 'mobileX' => 12, 'mobileY' => 85, 'mobileSize' => 75]);
$decor_right_style = ai_zippy_child_decor_style($attributes, 'decorRight', ['zIndex' => 5, 'desktopX' => 92, 'desktopY' => 15, 'desktopSize' => 120, 'mobileX' => 88, 'mobileY' => 15, 'mobileSize' => 75]);
$brands = $attributes['brands'] ?? [];
$features = $attributes['features'] ?? [];
$gallery_images = $attributes['galleryImages'] ?? [];

if (empty($brands)) {
    $brands = [
        ['name' => 'Artivity Studio', 'icon' => '/wp-content/uploads/2026/07/YanailsAtelier-Logo_sparkle.png', 'alt' => 'Artivity Studio'],
        ['name' => 'Sensory Playhaus', 'icon' => '/wp-content/uploads/2026/07/Sensory-Playhaus_COLOR-scaled.png', 'alt' => 'Sensory Playhaus'],
        ['name' => 'Press-on Nails', 'icon' => '/wp-content/uploads/2026/07/YanailsAtelier-Logo_sparkle.png', 'alt' => 'Press-on Nails'],
        ['name' => 'Young Entrepreneur', 'icon' => '/wp-content/uploads/2026/07/Sensory-Playhaus_STICKER-scaled.png', 'alt' => 'Young Entrepreneur'],
    ];
}

if (empty($gallery_images)) {
    $gallery_images = [
        ['url' => '/wp-content/uploads/2026/07/Regular-Classes_Main-Q.jpg', 'alt' => 'Studio Gallery 1'],
        ['url' => '/wp-content/uploads/2026/07/ShortCourse_mainQ.jpg', 'alt' => 'Studio Gallery 2'],
        ['url' => '/wp-content/uploads/2026/07/Aarts_White-Studen-Shirt_Sleeve.jpg', 'alt' => 'Studio Gallery 3'],
        ['url' => '/wp-content/uploads/2026/07/LittleDraws-2026.jpg', 'alt' => 'Studio Gallery 4'],
        ['url' => '/wp-content/uploads/2026/07/MangaDrawing2-1.png', 'alt' => 'Studio Gallery 5'],
    ];
}
$cta_text = $attributes['ctaText'] ?? 'SEE MORE';
$cta_url = $attributes['ctaUrl'] ?? '#';
$wrapper_attributes = get_block_wrapper_attributes(['class' => 'achiever-brands']);
$gallery_track_id = wp_unique_id('achiever-brands-gallery-track-');
?>
<section <?php echo $wrapper_attributes; ?>>
  <?php if ($decor_image) : ?>
    <div class="achiever-brands__decor" style="<?php echo esc_attr($decor_style); ?>">
      <img src="<?php echo esc_url($decor_image); ?>" alt="<?php echo esc_attr($decor_alt); ?>" loading="lazy" />
    </div>
  <?php endif; ?>
  <?php if ($decor_left_image) : ?>
    <div class="achiever-brands__decor achiever-brands__decor--left" style="<?php echo esc_attr($decor_left_style); ?>">
      <img src="<?php echo esc_url($decor_left_image); ?>" alt="<?php echo esc_attr($decor_left_alt); ?>" loading="lazy" />
    </div>
  <?php endif; ?>
  <?php if ($decor_right_image) : ?>
    <div class="achiever-brands__decor achiever-brands__decor--right" style="<?php echo esc_attr($decor_right_style); ?>">
      <img src="<?php echo esc_url($decor_right_image); ?>" alt="<?php echo esc_attr($decor_right_alt); ?>" loading="lazy" />
    </div>
  <?php endif; ?>
  <div class="achiever-brands__container">
    <div class="achiever-brands__intro">
      <h2 class="achiever-brands__heading"><?php echo nl2br(esc_html($heading)); ?></h2>
      <p class="achiever-brands__description"><?php echo esc_html($description); ?></p>
    </div>
    <div class="achiever-brands__showcase">
      <div class="achiever-brands__grid">
        <?php foreach ($brands as $brand) : ?>
          <div class="achiever-brands__item">
            <?php if ($brand['icon'] ?? '') : ?><img class="achiever-brands__icon" src="<?php echo esc_url($brand['icon']); ?>" alt="<?php echo esc_attr($brand['alt'] ?? ($brand['name'] ?? '')); ?>" loading="lazy" /><?php endif; ?>
            <span class="achiever-brands__name"><?php echo esc_html($brand['name'] ?? ''); ?></span>
          </div>
        <?php endforeach; ?>
      </div>
      <?php if ($cta_text) : ?><a class="achiever-btn achiever-btn--primary achiever-brands__cta" href="<?php echo esc_url($cta_url); ?>"><?php echo esc_html($cta_text); ?></a><?php endif; ?>
    </div>
  </div>
</section>
<svg class="achiever-brands__wave" viewBox="0 0 1440 120" preserveAspectRatio="none" aria-hidden="true" focusable="false"><path d="M0,78 C280,22 540,28 800,55 C1080,84 1260,96 1440,82 L1440,120 L0,120 Z" fill="#ffffff" /></svg>
<?php if (!empty($features)) : ?>
  <div class="achiever-brands__features">
    <?php foreach ($features as $feature) :
        $label = $feature['label'] ?? '';
        $icon  = $feature['icon'] ?? '';
    ?>
      <div class="achiever-brands__feature">
        <span class="achiever-brands__feature-icon">
          <?php if ($icon) : ?><img src="<?php echo esc_url($icon); ?>" alt="" loading="lazy" /><?php endif; ?>
        </span>
        <p class="achiever-brands__feature-label"><?php echo esc_html($label); ?></p>
      </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>
<?php if (!empty(array_filter($gallery_images, static fn($image) => !empty($image['url'])))) : ?>
  <div class="achiever-scroll-slider achiever-brands__gallery-slider" data-scroll-slider>
    <button type="button" class="achiever-scroll-arrow achiever-scroll-arrow--prev" data-scroll-prev aria-controls="<?php echo esc_attr($gallery_track_id); ?>" aria-label="Previous studio photos">&#8249;</button>
    <div id="<?php echo esc_attr($gallery_track_id); ?>" class="achiever-brands__gallery achiever-scroll-track" data-scroll-track tabindex="0">
      <?php foreach ($gallery_images as $image) : ?>
        <?php if ($image['url'] ?? '') : ?>
          <div class="achiever-brands__gallery-item">
            <img class="achiever-brands__gallery-image" src="<?php echo esc_url($image['url']); ?>" alt="<?php echo esc_attr($image['alt'] ?? ''); ?>" loading="lazy" />
          </div>
        <?php endif; ?>
      <?php endforeach; ?>
    </div>
    <button type="button" class="achiever-scroll-arrow achiever-scroll-arrow--next" data-scroll-next aria-controls="<?php echo esc_attr($gallery_track_id); ?>" aria-label="Next studio photos">&#8250;</button>
  </div>
<?php endif; ?>
