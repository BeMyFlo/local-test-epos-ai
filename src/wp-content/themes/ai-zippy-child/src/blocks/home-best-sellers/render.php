<?php
defined('ABSPATH') || exit;
$section_title = $attributes['sectionTitle'] ?? 'OUR BEST SELLERS';
$decor_left_image  = $attributes['decorLeftImage'] ?? '';
$decor_left_alt    = $attributes['decorLeftAlt'] ?? 'Cartoon mascot left decoration';
$decor_right_image = $attributes['decorRightImage'] ?? '';
$decor_right_alt   = $attributes['decorRightAlt'] ?? 'Cartoon mascot right decoration';
$decor_left_style  = ai_zippy_child_decor_style($attributes, 'decorLeft', ['zIndex' => 5, 'desktopX' => 8, 'desktopY' => 15, 'desktopSize' => 120, 'mobileX' => 12, 'mobileY' => 15, 'mobileSize' => 75]);
$decor_right_style = ai_zippy_child_decor_style($attributes, 'decorRight', ['zIndex' => 5, 'desktopX' => 92, 'desktopY' => 85, 'desktopSize' => 120, 'mobileX' => 88, 'mobileY' => 85, 'mobileSize' => 75]);
$products = $attributes['products'] ?? [];

if (empty($products)) {
    $products = [
        [
            'name'     => 'Junior Art Explorers Program',
            'category' => 'Regular Classes',
            'ageTime'  => 'Ages 4 - 6 | Weekly 1.5 Hrs',
            'image'    => '/wp-content/uploads/2026/07/Regular-Classes_Main-Q.jpg',
            'alt'      => 'Junior Art Explorers Program',
            'url'      => '/regular-art-classes/',
        ],
        [
            'name'     => 'Creative Canvas & Oil Pastel Studio',
            'category' => 'Regular Classes',
            'ageTime'  => 'Ages 7 - 12 | Weekly 2 Hrs',
            'image'    => '/wp-content/uploads/2026/07/download-1.jpg',
            'alt'      => 'Creative Canvas & Oil Pastel Studio',
            'url'      => '/regular-art-classes/',
        ],
        [
            'name'     => 'Express Art Single Workshop',
            'category' => 'Single Session',
            'ageTime'  => 'All Ages | 1.5 - 2 Hrs',
            'image'    => '/wp-content/uploads/2026/07/ShortCourse_mainQ.jpg',
            'alt'      => 'Express Art Single Workshop',
            'url'      => '/express-art-classes/',
        ],
        [
            'name'     => 'Manga & Digital Character Design',
            'category' => 'Short Course',
            'ageTime'  => 'Ages 9 - 16 | Weekly 2 Hrs',
            'image'    => '/wp-content/uploads/2026/07/MangaDrawing3.png',
            'alt'      => 'Manga & Digital Character Design',
            'url'      => '/camps-courses/#short-courses',
        ],
        [
            'name'     => 'Acrylic & Canvas Painting Mastery',
            'category' => 'Regular Classes',
            'ageTime'  => 'Ages 8 - 14 | Weekly 2 Hrs',
            'image'    => '/wp-content/uploads/2026/07/Aarts_White-Studen-Shirt_Sleeve.jpg',
            'alt'      => 'Acrylic & Canvas Painting Mastery',
            'url'      => '/regular-art-classes/',
        ],
    ];
}
$card_cta_text = $attributes['cardCtaText'] ?? 'BOOK NOW';
$cta_text = $attributes['ctaText'] ?? 'VIEW MORE WORKSHOP';
$cta_url = $attributes['ctaUrl'] ?? '#';
$wrapper_attributes = get_block_wrapper_attributes(['class' => 'achiever-products achiever-products--best-sellers']);
$track_id = wp_unique_id('achiever-best-sellers-track-');
?>
<section <?php echo $wrapper_attributes; ?>>
  <?php if ($decor_left_image) : ?>
    <div class="achiever-products__decor achiever-products__decor--left" style="<?php echo esc_attr($decor_left_style); ?>">
      <img src="<?php echo esc_url($decor_left_image); ?>" alt="<?php echo esc_attr($decor_left_alt); ?>" loading="lazy" />
    </div>
  <?php endif; ?>
  <?php if ($decor_right_image) : ?>
    <div class="achiever-products__decor achiever-products__decor--right" style="<?php echo esc_attr($decor_right_style); ?>">
      <img src="<?php echo esc_url($decor_right_image); ?>" alt="<?php echo esc_attr($decor_right_alt); ?>" loading="lazy" />
    </div>
  <?php endif; ?>
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
          <?php if ($card_cta_text) : ?>
            <a class="achiever-products__card-cta" href="<?php echo esc_url($url ?: '#'); ?>"><?php echo esc_html($card_cta_text); ?></a>
          <?php endif; ?>
        </div>
      </article>
    <?php endforeach; ?>
  </div>
    <button type="button" class="achiever-scroll-arrow achiever-scroll-arrow--next" data-scroll-next aria-controls="<?php echo esc_attr($track_id); ?>" aria-label="Next best sellers">&#8250;</button>
  </div>
  <?php if ($cta_text) : ?><a class="achiever-btn achiever-btn--primary" href="<?php echo esc_url($cta_url); ?>"><?php echo esc_html($cta_text); ?></a><?php endif; ?>
</section>
