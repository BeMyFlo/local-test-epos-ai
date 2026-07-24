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
$brands = $attributes['brands'] ?? [];
$gallery_images = $attributes['galleryImages'] ?? [];
$cta_text = $attributes['ctaText'] ?? 'SEE MORE';
$cta_url = $attributes['ctaUrl'] ?? '#';
$wrapper_attributes = get_block_wrapper_attributes(['class' => 'achiever-brands']);
?>
<section <?php echo $wrapper_attributes; ?>>
  <?php if ($decor_image) : ?>
    <div class="achiever-brands__decor">
      <img src="<?php echo esc_url($decor_image); ?>" alt="<?php echo esc_attr($decor_alt); ?>" loading="lazy" />
    </div>
  <?php endif; ?>
  <?php if ($decor_left_image) : ?>
    <div class="achiever-brands__decor achiever-brands__decor--left">
      <img src="<?php echo esc_url($decor_left_image); ?>" alt="<?php echo esc_attr($decor_left_alt); ?>" loading="lazy" />
    </div>
  <?php endif; ?>
  <?php if ($decor_right_image) : ?>
    <div class="achiever-brands__decor achiever-brands__decor--right">
      <img src="<?php echo esc_url($decor_right_image); ?>" alt="<?php echo esc_attr($decor_right_alt); ?>" loading="lazy" />
    </div>
  <?php endif; ?>
  <h2 class="achiever-brands__heading"><?php echo nl2br(esc_html($heading)); ?></h2>
  <p class="achiever-brands__description"><?php echo esc_html($description); ?></p>
  <?php if ($cta_text) : ?><a class="achiever-btn achiever-btn--primary" href="<?php echo esc_url($cta_url); ?>"><?php echo esc_html($cta_text); ?></a><?php endif; ?>
  <div class="achiever-brands__grid">
    <?php foreach ($brands as $brand) : ?>
      <div class="achiever-brands__item">
        <?php if ($brand['icon'] ?? '') : ?><img class="achiever-brands__icon" src="<?php echo esc_url($brand['icon']); ?>" alt="<?php echo esc_attr($brand['alt'] ?? ($brand['name'] ?? '')); ?>" loading="lazy" /><?php endif; ?>
        <span class="achiever-brands__name"><?php echo esc_html($brand['name'] ?? ''); ?></span>
      </div>
    <?php endforeach; ?>
  </div>
  <div class="achiever-brands__gallery">
    <?php foreach ($gallery_images as $image) : ?>
      <div class="achiever-brands__gallery-item">
        <?php if ($image['url'] ?? '') : ?><img class="achiever-brands__gallery-image" src="<?php echo esc_url($image['url']); ?>" alt="<?php echo esc_attr($image['alt'] ?? ''); ?>" loading="lazy" /><?php else : ?><span aria-hidden="true"></span><?php endif; ?>
      </div>
    <?php endforeach; ?>
  </div>
</section>
<svg class="achiever-brands__wave" viewBox="0 0 1300 60" preserveAspectRatio="none" aria-hidden="true" focusable="false"><path d="M0,0 C325,60 975,60 1300,0 L1300,60 L0,60 Z" fill="#ffffff" /></svg>
