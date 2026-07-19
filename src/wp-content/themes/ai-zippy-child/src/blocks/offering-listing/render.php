<?php
defined('ABSPATH') || exit;

$heading    = $attributes['heading'] ?? '';
$subheading = $attributes['subheading'] ?? '';
$layout     = $attributes['layout'] ?? 'grid';
$items      = $attributes['items'] ?? [];
$is_carousel = 'carousel' === $layout;
$instance_id = wp_unique_id('achiever-offerings-');
$heading_id = $instance_id . '-heading';
$track_id = $instance_id . '-track';

$wrapper_attributes = get_block_wrapper_attributes([
    'class' => 'achiever-offering-listing achiever-offering-listing--' . sanitize_html_class($layout),
    'data-offering-listing' => $is_carousel ? 'true' : 'false',
]);
?>
<section <?php echo $wrapper_attributes; ?>>
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
</section>
