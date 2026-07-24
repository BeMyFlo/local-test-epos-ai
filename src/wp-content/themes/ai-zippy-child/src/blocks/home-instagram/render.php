<?php
defined('ABSPATH') || exit;
$heading = $attributes['heading'] ?? 'FOLLOW US ON INSTAGRAM';
$decor_left_image  = $attributes['decorLeftImage'] ?? '';
$decor_left_alt    = $attributes['decorLeftAlt'] ?? 'Cartoon mascot left decoration';
$decor_right_image = $attributes['decorRightImage'] ?? '';
$decor_right_alt   = $attributes['decorRightAlt'] ?? 'Cartoon mascot right decoration';
$images = $attributes['images'] ?? [];
$wrapper_attributes = get_block_wrapper_attributes(['class' => 'achiever-instagram']);
$track_id = wp_unique_id('achiever-instagram-track-');
?>
<section <?php echo $wrapper_attributes; ?>>
  <?php if ($decor_left_image) : ?>
    <div class="achiever-instagram__decor achiever-instagram__decor--left">
      <img src="<?php echo esc_url($decor_left_image); ?>" alt="<?php echo esc_attr($decor_left_alt); ?>" loading="lazy" />
    </div>
  <?php endif; ?>
  <?php if ($decor_right_image) : ?>
    <div class="achiever-instagram__decor achiever-instagram__decor--right">
      <img src="<?php echo esc_url($decor_right_image); ?>" alt="<?php echo esc_attr($decor_right_alt); ?>" loading="lazy" />
    </div>
  <?php endif; ?>
  <h2 class="achiever-instagram__heading"><?php echo esc_html($heading); ?></h2>
  <div class="achiever-scroll-slider" data-scroll-slider>
    <button type="button" class="achiever-scroll-arrow achiever-scroll-arrow--prev" data-scroll-prev aria-controls="<?php echo esc_attr($track_id); ?>" aria-label="Previous Instagram posts">&#8249;</button>
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
    <button type="button" class="achiever-scroll-arrow achiever-scroll-arrow--next" data-scroll-next aria-controls="<?php echo esc_attr($track_id); ?>" aria-label="Next Instagram posts">&#8250;</button>
  </div>
</section>
