<?php
defined('ABSPATH') || exit;

$heading = $attributes['heading'] ?? "BEYOND\nTHE CANVAS";
// Saved values lost the escape character from "BEYOND\nTHE CANVAS", leaving
// either a literal "\n" or a bare "n" glued between the two words — which
// rendered as "BEYONDnTHE CANVAS". Restore the real line break in both cases.
$heading = str_replace('\n', "\n", $heading);
$heading = preg_replace('/(?<=[A-Z])n(?=[A-Z])/', "\n", $heading);
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
$cta_text = $attributes['ctaText'] ?? 'CLICK TO SEE MORE';
$cta_url = $attributes['ctaUrl'] ?? '#';
$brand_icon_size = (int) ($attributes['brandIconSize'] ?? 90);
$wrapper_attributes = get_block_wrapper_attributes([
    'class' => 'achiever-brands',
    'style' => "--brand-icon-size:{$brand_icon_size};",
]);
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
      <p class="achiever-brands__description"><?php echo nl2br(esc_html($description)); ?></p>
    </div>
    <div class="achiever-brands__showcase">
      <?php
      $first_row_count = max(1, (int) ($attributes['brandsFirstRow'] ?? 3));
      $brand_rows = array_filter([
          array_slice($brands, 0, $first_row_count),
          array_slice($brands, $first_row_count),
      ]);
      ?>
      <div class="achiever-brands__grid">
        <?php foreach ($brand_rows as $row) : ?>
          <div class="achiever-brands__row">
            <?php foreach ($row as $brand) : ?>
              <div class="achiever-brands__item">
                <?php if ($brand['icon'] ?? '') : ?><img class="achiever-brands__icon" src="<?php echo esc_url($brand['icon']); ?>" alt="<?php echo esc_attr($brand['alt'] ?? ($brand['name'] ?? '')); ?>" loading="lazy" /><?php endif; ?>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endforeach; ?>
      </div>
      <?php if ($cta_text) : ?><a class="achiever-btn achiever-btn--primary achiever-brands__cta" href="<?php echo esc_url($cta_url); ?>"><?php echo esc_html($cta_text); ?></a><?php endif; ?>
    </div>
  </div>
</section>
<svg class="achiever-brands__wave" viewBox="0 0 1440 120" preserveAspectRatio="none" aria-hidden="true" focusable="false"><path d="M0,78 C280,22 540,28 800,55 C1080,84 1260,96 1440,82 L1440,120 L0,120 Z" fill="#ffffff" /></svg>
<?php if (!empty($features)) :
    // Default line icons (design order: art, palette, craft tools, idea, easel, faces).
    // Used when a feature has no uploaded icon; cycled by position.
    $feature_default_icons = [
        '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3.5" y="4.5" width="14" height="12" rx="1.5"/><circle cx="13.5" cy="8" r="1.3"/><path d="M4 14l4-4 4 4"/><path d="M20.5 7.5 12 16l-2.2.7.7-2.2 8.5-8.5c.4-.4 1.1-.4 1.5 0s.4 1.1 0 1.5Z"/></svg>',
        '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 3.5a8.5 8.5 0 1 0 0 17c1.2 0 1.8-.9 1.4-1.9-.5-1.2.2-2.4 1.5-2.4h1.9a3.7 3.7 0 0 0 3.7-3.7C20.5 7 16.7 3.5 12 3.5Z"/><circle cx="8" cy="9" r="1"/><circle cx="12" cy="7.5" r="1"/><circle cx="16" cy="9" r="1"/><circle cx="7.5" cy="13" r="1"/></svg>',
        '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="7" cy="7" r="2"/><circle cx="7" cy="15" r="2"/><path d="M8.7 8.3 19 15M8.7 13.7 19 7"/><path d="M4 19.5h16M7 19.5v-1.5M10 19.5v-1.5M13 19.5v-1.5M16 19.5v-1.5"/></svg>',
        '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 6a4.5 4.5 0 0 0-2.5 8.2c.6.4 1 1.1 1 1.8h3c0-.7.4-1.4 1-1.8A4.5 4.5 0 0 0 12 6Z"/><path d="M10.5 18.5h3M11 20.5h2"/><path d="M12 2.5v1.5M5.3 5.3l1 1M2.5 12H4M20 12h1.5M17.7 5.3l-1 1"/></svg>',
        '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="5" y="5" width="14" height="10" rx="1"/><path d="M8 12l3-3 2.5 2.5L16 9"/><path d="M12 3v2M12 15v2M12 17l-4.5 4.5M12 17l4.5 4.5"/></svg>',
        '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="8.5" cy="10" r="4.5"/><circle cx="16.5" cy="14.5" r="4"/><path d="M7 9.5h.01M10 9.5h.01M15.2 14h.01M17.8 14h.01M7 11.5c.5.6 1.2 1 2 .9M15.3 16c.4.5 1 .8 1.7.8"/></svg>',
    ];
?>
  <div class="achiever-brands__features">
    <?php foreach (array_values($features) as $feature_index => $feature) :
        $label = $feature['label'] ?? '';
        $icon  = $feature['icon'] ?? '';
    ?>
      <div class="achiever-brands__feature">
        <span class="achiever-brands__feature-icon">
          <?php if ($icon) : ?>
            <img src="<?php echo esc_url($icon); ?>" alt="" loading="lazy" />
          <?php else : ?>
            <?php echo $feature_default_icons[$feature_index % count($feature_default_icons)]; // phpcs:ignore WordPress.Security.EscapeOutput -- static theme SVG ?>
          <?php endif; ?>
        </span>
        <p class="achiever-brands__feature-label"><?php echo esc_html($label); ?></p>
      </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>
<?php if (!empty(array_filter($gallery_images, static fn($image) => !empty($image['url'])))) : ?>
  <div class="achiever-scroll-slider achiever-brands__gallery-slider" data-scroll-slider data-scroll-marquee>
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
