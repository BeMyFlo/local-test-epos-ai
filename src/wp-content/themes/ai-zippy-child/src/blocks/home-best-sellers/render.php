<?php
defined('ABSPATH') || exit;
$section_title = $attributes['sectionTitle'] ?? 'OUR BEST SELLERS';
$products = $attributes['products'] ?? [];
$cta_text = $attributes['ctaText'] ?? 'VIEW MORE WORKSHOP';
$cta_url = $attributes['ctaUrl'] ?? '#';
$wrapper_attributes = get_block_wrapper_attributes(['class' => 'achiever-products achiever-products--best-sellers']);
$track_id = wp_unique_id('achiever-best-sellers-track-');
?>
<section <?php echo $wrapper_attributes; ?>>
  <h2 class="achiever-products__title"><?php echo esc_html($section_title); ?></h2>
  <div class="achiever-scroll-slider" data-scroll-slider>
    <button type="button" class="achiever-scroll-arrow achiever-scroll-arrow--prev" data-scroll-prev aria-controls="<?php echo esc_attr($track_id); ?>" aria-label="Previous best sellers">&#8249;</button>
  <div id="<?php echo esc_attr($track_id); ?>" class="achiever-products__grid achiever-scroll-track" data-scroll-track tabindex="0">
    <?php foreach ($products as $product) :
        $name = $product['name'] ?? '';
        $url = $product['url'] ?? '';
    ?>
      <article class="achiever-products__card">
        <?php if ($product['image'] ?? '') : ?>
          <?php if ($url) : ?><a href="<?php echo esc_url($url); ?>"><?php endif; ?>
          <img class="achiever-products__card-img" src="<?php echo esc_url($product['image']); ?>" alt="<?php echo esc_attr($product['alt'] ?? $name); ?>" loading="lazy" />
          <?php if ($url) : ?></a><?php endif; ?>
        <?php endif; ?>
        <div class="achiever-products__card-body">
          <h3 class="achiever-products__card-name"><?php echo esc_html($name); ?></h3>
          <p class="achiever-products__card-category"><?php echo esc_html($product['category'] ?? ''); ?></p>
          <p class="achiever-products__card-age-time"><?php echo esc_html($product['ageTime'] ?? ''); ?></p>
        </div>
      </article>
    <?php endforeach; ?>
  </div>
    <button type="button" class="achiever-scroll-arrow achiever-scroll-arrow--next" data-scroll-next aria-controls="<?php echo esc_attr($track_id); ?>" aria-label="Next best sellers">&#8250;</button>
  </div>
  <?php if ($cta_text) : ?><a class="achiever-btn achiever-btn--primary" href="<?php echo esc_url($cta_url); ?>"><?php echo esc_html($cta_text); ?></a><?php endif; ?>
</section>
