<?php
defined('ABSPATH') || exit;
$pre_heading = $attributes['preHeading'] ?? "IT'S";
$heading = $attributes['heading'] ?? 'PARTY TIME!';
$subtitle = $attributes['subtitle'] ?? 'BIRTHDAYS · GROUP BOOKINGS · CORPORATE EVENTS · TEAM BONDING';
$decor_left_image = $attributes['decorLeftImage'] ?? '';
$decor_left_alt = $attributes['decorLeftAlt'] ?? 'Party character decoration';
$decor_right_image = $attributes['decorRightImage'] ?? '';
$decor_right_alt = $attributes['decorRightAlt'] ?? 'Cake decoration';
$decor_left_style  = ai_zippy_child_decor_style($attributes, 'decorLeft', ['zIndex' => 5, 'desktopX' => 10, 'desktopY' => 15, 'desktopSize' => 150, 'mobileX' => 12, 'mobileY' => 15, 'mobileSize' => 75]);
$decor_right_style = ai_zippy_child_decor_style($attributes, 'decorRight', ['zIndex' => 5, 'desktopX' => 90, 'desktopY' => 15, 'desktopSize' => 150, 'mobileX' => 88, 'mobileY' => 15, 'mobileSize' => 75]);
$images = $attributes['images'] ?? [];

if (empty($images)) {
    $images = [
        ['url' => '/wp-content/uploads/2026/07/Regular-Classes_Main-Q.jpg', 'alt' => 'Party Fun 1'],
        ['url' => '/wp-content/uploads/2026/07/Aarts_White-Studen-Shirt_Sleeve.jpg', 'alt' => 'Party Fun 2'],
        ['url' => '/wp-content/uploads/2026/07/ShortCourse_mainQ.jpg', 'alt' => 'Party Fun 3'],
        ['url' => '/wp-content/uploads/2026/07/download-1.jpg', 'alt' => 'Party Fun 4'],
    ];
}
$cta_text = $attributes['ctaText'] ?? 'ADVANCED BOOK NOW';
$cta_url = $attributes['ctaUrl'] ?? '#';
$wrapper_attributes = get_block_wrapper_attributes(['class' => 'achiever-party']);
$party_track_id = wp_unique_id('achiever-party-track-');
?>
<section <?php echo $wrapper_attributes; ?>>
  <div class="achiever-party__decor achiever-party__decor--left<?php echo $decor_left_image ? '' : ' achiever-party__decor--empty'; ?>" style="<?php echo esc_attr($decor_left_style); ?>"><?php if ($decor_left_image) : ?><img src="<?php echo esc_url($decor_left_image); ?>" alt="<?php echo esc_attr($decor_left_alt); ?>" loading="lazy" /><?php endif; ?></div>
  <div class="achiever-party__decor achiever-party__decor--right<?php echo $decor_right_image ? '' : ' achiever-party__decor--empty'; ?>" style="<?php echo esc_attr($decor_right_style); ?>"><?php if ($decor_right_image) : ?><img src="<?php echo esc_url($decor_right_image); ?>" alt="<?php echo esc_attr($decor_right_alt); ?>" loading="lazy" /><?php endif; ?></div>
  <h2 class="achiever-party__heading"><span class="achiever-party__pre-heading"><?php echo esc_html($pre_heading); ?></span><br><?php echo esc_html($heading); ?></h2>
  <p class="achiever-party__subtitle"><?php echo esc_html($subtitle); ?></p>
  <div class="achiever-scroll-slider achiever-party__gallery-slider" data-scroll-slider data-scroll-autoplay>
    <button type="button" class="achiever-scroll-arrow achiever-scroll-arrow--prev" data-scroll-prev aria-controls="<?php echo esc_attr($party_track_id); ?>" aria-label="Previous party photos">&#8249;</button>
    <div id="<?php echo esc_attr($party_track_id); ?>" class="achiever-party__photos achiever-scroll-track" data-scroll-track tabindex="0">
      <?php foreach ($images as $image) : ?>
        <div class="achiever-party__photo-slot">
          <?php if ($image['url'] ?? '') : ?><img class="achiever-party__photo" src="<?php echo esc_url($image['url']); ?>" alt="<?php echo esc_attr($image['alt'] ?? ''); ?>" loading="lazy" /><?php else : ?><span aria-hidden="true"></span><?php endif; ?>
        </div>
      <?php endforeach; ?>
    </div>
    <button type="button" class="achiever-scroll-arrow achiever-scroll-arrow--next" data-scroll-next aria-controls="<?php echo esc_attr($party_track_id); ?>" aria-label="Next party photos">&#8250;</button>
  </div>
  <?php if ($cta_text) : ?><a class="achiever-btn achiever-btn--primary" href="<?php echo esc_url($cta_url); ?>"><?php echo esc_html($cta_text); ?></a><?php endif; ?>
</section>
