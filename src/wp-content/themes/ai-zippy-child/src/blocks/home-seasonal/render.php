<?php
defined('ABSPATH') || exit;

$section_title = $attributes['sectionTitle'] ?? 'SEASONAL SPECIAL';
$products      = $attributes['products'] ?? [];

if (empty($products)) {
    $products = [
        [
            'name'     => 'Holiday Art Exploration Camp',
            'category' => 'Art Camp',
            'ageTime'  => 'Ages 4 - 8 | 2.5 Hrs x 4 Days',
            'image'    => '/wp-content/uploads/2026/07/LittleDraws-2026.jpg',
            'alt'      => 'Holiday Art Exploration Camp',
            'url'      => '/camps-courses/',
        ],
        [
            'name'     => 'Manga & Comic Illustration Workshop',
            'category' => 'Short Course',
            'ageTime'  => 'Ages 8 - 15 | 2 Hrs x 6 Lessons',
            'image'    => '/wp-content/uploads/2026/07/MangaDrawing2-1.png',
            'alt'      => 'Manga & Comic Illustration Workshop',
            'url'      => '/camps-courses/#short-courses',
        ],
        [
            'name'     => 'Watercolour Landscape & Floral Art',
            'category' => 'Single Session',
            'ageTime'  => 'Ages 7 - 14 | 2 Hrs',
            'image'    => '/wp-content/uploads/2026/07/Watercolour.png',
            'alt'      => 'Watercolour Landscape & Floral Art',
            'url'      => '/single-session-art-classes/',
        ],
        [
            'name'     => 'Sketcher Masterclass & Drawing Fundamentals',
            'category' => 'Special Workshop',
            'ageTime'  => 'Ages 6 - 12 | 2.5 Hrs',
            'image'    => '/wp-content/uploads/2026/07/SketcherMaster-2026.jpg',
            'alt'      => 'Sketcher Masterclass',
            'url'      => '/single-session-art-classes/',
        ],
        [
            'name'     => 'Portfolio Development Intensive',
            'category' => 'Art Camp',
            'ageTime'  => 'Ages 10 - 17 | 3 Hrs x 5 Days',
            'image'    => '/wp-content/uploads/2026/07/PortfolioArt-2027.jpg',
            'alt'      => 'Portfolio Development Intensive',
            'url'      => '/camps-courses/',
        ],
    ];
}
$card_cta_text = $attributes['cardCtaText'] ?? 'BOOK NOW';
$cta_text      = $attributes['ctaText'] ?? 'VIEW MORE WORKSHOP';
$cta_url       = $attributes['ctaUrl'] ?? '#';
$decor_image   = $attributes['decorImage'] ?? '';
$decor_alt     = $attributes['decorAlt'] ?? 'Paint brush decoration';
$decor_left_image  = $attributes['decorLeftImage'] ?? '';
$decor_left_alt    = $attributes['decorLeftAlt'] ?? 'Cartoon mascot left decoration';
$decor_right_image = $attributes['decorRightImage'] ?? '';
$decor_right_alt   = $attributes['decorRightAlt'] ?? 'Cartoon mascot right decoration';
$decor_style       = ai_zippy_child_decor_style($attributes, 'decor', ['zIndex' => 5, 'desktopX' => 12, 'desktopY' => 12, 'desktopSize' => 278, 'mobileX' => 15, 'mobileY' => 10, 'mobileSize' => 150]);
$decor_left_style  = ai_zippy_child_decor_style($attributes, 'decorLeft', ['zIndex' => 5, 'desktopX' => 18, 'desktopY' => 92, 'desktopSize' => 405, 'mobileX' => 20, 'mobileY' => 92, 'mobileSize' => 220]);
$decor_right_style = ai_zippy_child_decor_style($attributes, 'decorRight', ['zIndex' => 5, 'desktopX' => 88, 'desktopY' => 10, 'desktopSize' => 310, 'mobileX' => 85, 'mobileY' => 10, 'mobileSize' => 140]);
$wrapper_attributes = get_block_wrapper_attributes(['class' => 'achiever-seasonal']);
$track_id = wp_unique_id('achiever-seasonal-track-');
?>
<section <?php echo $wrapper_attributes; ?>>
  <?php if ($decor_image) : ?>
    <div class="achiever-seasonal__decor" style="<?php echo esc_attr($decor_style); ?>">
      <img src="<?php echo esc_url($decor_image); ?>" alt="<?php echo esc_attr($decor_alt); ?>" loading="lazy" />
    </div>
  <?php endif; ?>
  <?php if ($decor_left_image) : ?>
    <div class="achiever-seasonal__decor achiever-seasonal__decor--left" style="<?php echo esc_attr($decor_left_style); ?>">
      <img src="<?php echo esc_url($decor_left_image); ?>" alt="<?php echo esc_attr($decor_left_alt); ?>" loading="lazy" />
    </div>
  <?php endif; ?>
  <?php if ($decor_right_image) : ?>
    <div class="achiever-seasonal__decor achiever-seasonal__decor--right" style="<?php echo esc_attr($decor_right_style); ?>">
      <img src="<?php echo esc_url($decor_right_image); ?>" alt="<?php echo esc_attr($decor_right_alt); ?>" loading="lazy" />
    </div>
  <?php else : ?>
    <div class="achiever-seasonal__pencils" aria-hidden="true">
      <span></span>
      <span></span>
      <span></span>
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
          <?php if ($card_cta_text) : ?>
            <a class="achiever-seasonal__card-cta" href="<?php echo esc_url($url ?: '#'); ?>"><?php echo esc_html($card_cta_text); ?></a>
          <?php endif; ?>
        </div>
      </article>
    <?php endforeach; ?>
  </div>
    <button type="button" class="achiever-scroll-arrow achiever-scroll-arrow--next" data-scroll-next aria-controls="<?php echo esc_attr($track_id); ?>" aria-label="Next seasonal workshops">&#8250;</button>
  </div>
  <div class="achiever-scroll-dots" aria-hidden="true"></div>
  <?php if ($cta_text) : ?>
    <a class="achiever-btn achiever-btn--primary achiever-seasonal__cta" href="<?php echo esc_url($cta_url); ?>"><?php echo esc_html($cta_text); ?></a>
  <?php endif; ?>
</section>
