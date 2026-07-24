<?php
defined('ABSPATH') || exit;

$section_title = $attributes['sectionTitle'] ?? 'SEASONAL SPECIAL';
$products      = $attributes['products'] ?? [];
$cta_text      = $attributes['ctaText'] ?? 'VIEW MORE WORKSHOP';
$cta_url       = $attributes['ctaUrl'] ?? '#';
$decor_image   = $attributes['decorImage'] ?? '';
$decor_alt     = $attributes['decorAlt'] ?? 'Paint brush decoration';
$decor_left_image  = $attributes['decorLeftImage'] ?? '';
$decor_left_alt    = $attributes['decorLeftAlt'] ?? 'Cartoon mascot left decoration';
$decor_right_image = $attributes['decorRightImage'] ?? '';
$decor_right_alt   = $attributes['decorRightAlt'] ?? 'Cartoon mascot right decoration';
$wrapper_attributes = get_block_wrapper_attributes(['class' => 'achiever-seasonal']);
$track_id = wp_unique_id('achiever-seasonal-track-');
?>
<section <?php echo $wrapper_attributes; ?>>
  <?php if ($decor_image) : ?>
    <div class="achiever-seasonal__decor">
      <img src="<?php echo esc_url($decor_image); ?>" alt="<?php echo esc_attr($decor_alt); ?>" loading="lazy" />
    </div>
  <?php endif; ?>
  <?php if ($decor_left_image) : ?>
    <div class="achiever-seasonal__decor achiever-seasonal__decor--left">
      <img src="<?php echo esc_url($decor_left_image); ?>" alt="<?php echo esc_attr($decor_left_alt); ?>" loading="lazy" />
    </div>
  <?php endif; ?>
  <?php if ($decor_right_image) : ?>
    <div class="achiever-seasonal__decor achiever-seasonal__decor--right">
      <img src="<?php echo esc_url($decor_right_image); ?>" alt="<?php echo esc_attr($decor_right_alt); ?>" loading="lazy" />
    </div>
  <?php endif; ?>
  <h2 class="achiever-seasonal__title"><?php echo esc_html($section_title); ?></h2>
  <div class="achiever-scroll-slider" data-scroll-slider>
    <button type="button" class="achiever-scroll-arrow achiever-scroll-arrow--prev" data-scroll-prev aria-controls="<?php echo esc_attr($track_id); ?>" aria-label="Previous seasonal workshops">&#8249;</button>
  <div id="<?php echo esc_attr($track_id); ?>" class="achiever-seasonal__grid achiever-scroll-track" data-scroll-track tabindex="0">
    <?php foreach ($products as $product) :
        $name = $product['name'] ?? '';
        $category = $product['category'] ?? '';
        $age_time = $product['ageTime'] ?? '';
        $image = $product['image'] ?? '';
        $alt = $product['alt'] ?? $name;
        $url = $product['url'] ?? '';
    ?>
      <article class="achiever-seasonal__card">
        <?php if ($image) : ?>
          <?php if ($url) : ?><a href="<?php echo esc_url($url); ?>"><?php endif; ?>
          <img class="achiever-seasonal__card-image" src="<?php echo esc_url($image); ?>" alt="<?php echo esc_attr($alt); ?>" loading="lazy" />
          <?php if ($url) : ?></a><?php endif; ?>
        <?php endif; ?>
        <div class="achiever-seasonal__card-content">
          <h3 class="achiever-seasonal__card-name"><?php echo esc_html($name); ?></h3>
          <p class="achiever-seasonal__card-category"><?php echo esc_html($category); ?></p>
          <p class="achiever-seasonal__card-age-time"><?php echo esc_html($age_time); ?></p>
        </div>
      </article>
    <?php endforeach; ?>
  </div>
    <button type="button" class="achiever-scroll-arrow achiever-scroll-arrow--next" data-scroll-next aria-controls="<?php echo esc_attr($track_id); ?>" aria-label="Next seasonal workshops">&#8250;</button>
  </div>
  <?php if ($cta_text) : ?><a class="achiever-btn achiever-btn--primary" href="<?php echo esc_url($cta_url); ?>"><?php echo esc_html($cta_text); ?></a><?php endif; ?>
</section>
