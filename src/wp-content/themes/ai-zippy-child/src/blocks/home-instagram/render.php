<?php
defined('ABSPATH') || exit;
$heading = $attributes['heading'] ?? 'FOLLOW US ON INSTAGRAM';
$decor_left_image  = $attributes['decorLeftImage'] ?? '';
$decor_left_alt    = $attributes['decorLeftAlt'] ?? 'Cartoon mascot left decoration';
$decor_right_image = $attributes['decorRightImage'] ?? '/wp-content/uploads/2026/07/Vibrant-colored-pencils.webp';
$decor_right_alt   = $attributes['decorRightAlt'] ?? 'Pencils top-right decoration';
$decor_left_style  = ai_zippy_child_decor_style($attributes, 'decorLeft', ['zIndex' => 5, 'desktopX' => 8, 'desktopY' => 12, 'desktopSize' => 100, 'mobileX' => 12, 'mobileY' => 12, 'mobileSize' => 75]);
$decor_right_style = ai_zippy_child_decor_style($attributes, 'decorRight', ['zIndex' => 5, 'desktopX' => 88, 'desktopY' => 2, 'desktopSize' => 140, 'mobileX' => 82, 'mobileY' => 2, 'mobileSize' => 90]);
$images = $attributes['images'] ?? [];

if (empty($images)) {
    $images = [
        ['url' => '/wp-content/uploads/2026/07/LittleDraws-2026.jpg', 'alt' => 'Instagram Art 1', 'link' => '#'],
        ['url' => '/wp-content/uploads/2026/07/MangaDrawing2-1.png', 'alt' => 'Instagram Art 2', 'link' => '#'],
        ['url' => '/wp-content/uploads/2026/07/PortfolioArt-2027.jpg', 'alt' => 'Instagram Art 3', 'link' => '#'],
        ['url' => '/wp-content/uploads/2026/07/Regular-Classes_Main-Q.jpg', 'alt' => 'Instagram Art 4', 'link' => '#'],
        ['url' => '/wp-content/uploads/2026/07/Watercolour.png', 'alt' => 'Instagram Art 5', 'link' => '#'],
        ['url' => '/wp-content/uploads/2026/07/SketcherMaster-2026.jpg', 'alt' => 'Instagram Art 6', 'link' => '#'],
    ];
}
$wrapper_attributes = get_block_wrapper_attributes(['class' => 'achiever-instagram']);
$track_id = wp_unique_id('achiever-instagram-track-');
?>
<section <?php echo $wrapper_attributes; ?>>
  <?php if ($decor_left_image) : ?>
    <div class="achiever-instagram__decor achiever-instagram__decor--left" style="<?php echo esc_attr($decor_left_style); ?>">
      <img src="<?php echo esc_url($decor_left_image); ?>" alt="<?php echo esc_attr($decor_left_alt); ?>" loading="lazy" />
    </div>
  <?php endif; ?>
  <?php if ($decor_right_image) : ?>
    <div class="achiever-instagram__decor achiever-instagram__decor--right" style="<?php echo esc_attr($decor_right_style); ?>">
      <img src="<?php echo esc_url($decor_right_image); ?>" alt="<?php echo esc_attr($decor_right_alt); ?>" loading="lazy" />
    </div>
  <?php endif; ?>
  <h2 class="achiever-instagram__heading"><?php echo esc_html($heading); ?></h2>
  <div class="achiever-scroll-slider achiever-instagram__slider" data-scroll-slider>
    <button type="button" class="achiever-scroll-arrow achiever-scroll-arrow--prev achiever-instagram__arrow achiever-instagram__arrow--prev" data-scroll-prev aria-controls="<?php echo esc_attr($track_id); ?>" aria-label="Previous Instagram posts">&#8249;</button>
    <div id="<?php echo esc_attr($track_id); ?>" class="achiever-instagram__grid achiever-scroll-track" data-scroll-track tabindex="0">
      <?php foreach ($images as $image) :
          $url = $image['url'] ?? '';
          $link = $image['link'] ?? '';
      ?>
        <div class="achiever-instagram__item">
          <?php if ($url) : ?>
            <?php if ($link) : ?><a href="<?php echo esc_url($link); ?>"><?php endif; ?>
            <img src="<?php echo esc_url($url); ?>" alt="<?php echo esc_attr($image['alt'] ?? ''); ?>" loading="lazy" />
            <?php if ($link) : ?></a><?php endif; ?>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    </div>
    <button type="button" class="achiever-scroll-arrow achiever-scroll-arrow--next achiever-instagram__arrow achiever-instagram__arrow--next" data-scroll-next aria-controls="<?php echo esc_attr($track_id); ?>" aria-label="Next Instagram posts">&#8250;</button>
  </div>
  <div class="achiever-scroll-dots" aria-hidden="true"></div>
</section>
