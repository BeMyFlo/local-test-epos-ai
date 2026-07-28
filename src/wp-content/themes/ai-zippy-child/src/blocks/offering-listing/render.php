<?php
defined('ABSPATH') || exit;

$heading     = $attributes['heading'] ?? '';
$subheading  = $attributes['subheading'] ?? '';
$layout      = $attributes['layout'] ?? 'grid';
$items       = $attributes['items'] ?? [];
$is_carousel = 'carousel' === $layout;
$instance_id = wp_unique_id('achiever-offerings-');
$heading_id  = $instance_id . '-heading';
$track_id    = $instance_id . '-track';
$background_style = 'position:relative;background-color:' . (sanitize_hex_color($attributes['backgroundColor'] ?? '#ffffff') ?: '#ffffff') . ';';
if (!empty($attributes['backgroundImage'])) {
    $background_style .= 'background-image:url(' . esc_url($attributes['backgroundImage']) . ');background-size:cover;background-position:center;';
}

$decor_left_image  = $attributes['decorLeftImage'] ?? '';
$decor_left_alt    = $attributes['decorLeftAlt'] ?? 'Cartoon mascot left';
$decor_right_image = $attributes['decorRightImage'] ?? '';
$decor_right_alt   = $attributes['decorRightAlt'] ?? 'Cartoon mascot right';

$decor_left_style  = function_exists('ai_zippy_child_decor_style')
    ? ai_zippy_child_decor_style($attributes, 'decorLeft', ['zIndex' => 5, 'desktopX' => 5, 'desktopY' => 10, 'desktopSize' => 140, 'mobileX' => 5, 'mobileY' => 10, 'mobileSize' => 90])
    : '';
$decor_right_style = function_exists('ai_zippy_child_decor_style')
    ? ai_zippy_child_decor_style($attributes, 'decorRight', ['zIndex' => 5, 'desktopX' => 90, 'desktopY' => 85, 'desktopSize' => 160, 'mobileX' => 85, 'mobileY' => 85, 'mobileSize' => 100])
    : '';

$wrapper_attributes = get_block_wrapper_attributes([
    'class' => 'achiever-offering-listing achiever-offering-listing--' . sanitize_html_class($layout),
    'data-offering-listing' => $is_carousel ? 'true' : 'false',
    'style' => $background_style,
]);
?>
<section <?php echo $wrapper_attributes; ?>>
  <?php if ($decor_left_image) : ?>
    <div class="achiever-decor-mascot achiever-decor-mascot--left" style="<?php echo esc_attr($decor_left_style); ?>">
      <img src="<?php echo esc_url($decor_left_image); ?>" alt="<?php echo esc_attr($decor_left_alt); ?>" loading="lazy" />
    </div>
  <?php endif; ?>

  <div class="achiever-offering-listing__inner">
    <?php if ($heading) : ?><h2 id="<?php echo esc_attr($heading_id); ?>" class="achiever-offering-listing__heading"><?php echo esc_html($heading); ?></h2><?php endif; ?>
    <?php if ($subheading) : ?><p class="achiever-offering-listing__subheading"><?php echo esc_html($subheading); ?></p><?php endif; ?>

    <div class="achiever-offering-listing__viewport">
      <?php if ($is_carousel) : ?>
        <button class="achiever-offering-listing__arrow achiever-offering-listing__arrow--prev" type="button" aria-label="Previous offerings" aria-controls="<?php echo esc_attr($track_id); ?>" data-offering-prev>&lsaquo;</button>
      <?php endif; ?>
      <div id="<?php echo esc_attr($track_id); ?>" class="achiever-offering-listing__track" role="region"<?php if ($heading) : ?> aria-labelledby="<?php echo esc_attr($heading_id); ?>"<?php else : ?> aria-label="Offerings"<?php endif; ?> data-offering-track>
        <?php if (!$items && is_admin()) : ?><p class="achiever-offering-listing__editor-empty">Add offerings in the block settings.</p><?php endif; ?>
        <?php foreach ($items as $item) :
          $title       = $item['title'] ?? '';
          $age         = $item['age'] ?? '';
          $description = $item['description'] ?? '';
          $image       = $item['image'] ?? '';
          $alt         = $item['alt'] ?? $title;
          $cta_text    = $item['ctaText'] ?? '';
          $cta_url     = $item['ctaUrl'] ?? '';
          $category    = $item['category'] ?? '';
          $tagline     = $item['tagline'] ?? '';
        ?>
          <article class="achiever-offering-listing__card">
            <div class="achiever-offering-listing__image">
              <?php if ($image) : ?><img src="<?php echo esc_url($image); ?>" alt="<?php echo esc_attr($alt); ?>" loading="lazy" /><?php else : ?><span aria-hidden="true"></span><?php endif; ?>
            </div>
            <div class="achiever-offering-listing__content">
              <?php if ($category) : ?><span class="achiever-offering-listing__category"><?php echo esc_html($category); ?></span><?php endif; ?>
              <?php if ($title) : ?><h3><?php echo esc_html($title); ?></h3><?php endif; ?>
              <?php if ($age) : ?><p class="achiever-offering-listing__age"><?php echo esc_html($age); ?></p><?php endif; ?>
              <?php if ($tagline) : ?><p class="achiever-offering-listing__tagline"><?php echo esc_html($tagline); ?></p><?php endif; ?>
              <?php if ($description) : ?><p><?php echo esc_html($description); ?></p><?php endif; ?>
              <?php if ($cta_text && $cta_url) : ?><a class="achiever-btn" href="<?php echo esc_url($cta_url); ?>"><?php echo esc_html($cta_text); ?></a><?php endif; ?>
            </div>
          </article>
        <?php endforeach; ?>
      </div>
      <?php if ($is_carousel) : ?>
        <button class="achiever-offering-listing__arrow achiever-offering-listing__arrow--next" type="button" aria-label="Next offerings" aria-controls="<?php echo esc_attr($track_id); ?>" data-offering-next>&rsaquo;</button>
      <?php endif; ?>
    </div>
  </div>

  <?php if ($decor_right_image) : ?>
    <div class="achiever-decor-mascot achiever-decor-mascot--right" style="<?php echo esc_attr($decor_right_style); ?>">
      <img src="<?php echo esc_url($decor_right_image); ?>" alt="<?php echo esc_attr($decor_right_alt); ?>" loading="lazy" />
    </div>
  <?php endif; ?>
</section>
