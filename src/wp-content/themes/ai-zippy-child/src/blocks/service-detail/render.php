<?php
defined('ABSPATH') || exit;

$service_type     = $attributes['serviceType'] ?? '';
$heading          = $attributes['heading'] ?? '';
$age_range        = $attributes['ageRange'] ?? '';
$description      = $attributes['description'] ?? '';
$main_image       = $attributes['mainImage'] ?? '';
$main_image_alt   = $attributes['mainImageAlt'] ?? $heading;
$info_title       = $attributes['infoTitle'] ?? '';
$info_items       = $attributes['infoItems'] ?? [];
$cta_text         = $attributes['ctaText'] ?? '';
$cta_url          = $attributes['ctaUrl'] ?? '';
$related_title    = $attributes['relatedTitle'] ?? '';
$related_items    = $attributes['relatedItems'] ?? [];
$gallery_title    = $attributes['galleryTitle'] ?? '';
$gallery_images   = $attributes['galleryImages'] ?? [];
$regular_lessons  = [
    '/artventurer/' => 'Age 3 & up',
    '/canvas-wizard/' => 'Age 6 & up',
    '/foundation-art-course/' => 'Age 4–6',
    '/sketcher-master/' => 'Age 7 & up',
    '/little-draws/' => 'Age 5 & up',
    '/junior-fine-arts/' => 'Age 8 & up',
    '/drawvinci/' => 'Age 6 & up',
    '/portfolio-art/' => 'Age 10 & up',
];
$current_path = '/' . trim((string) wp_parse_url(get_permalink(), PHP_URL_PATH), '/') . '/';
$is_regular_detail = isset($regular_lessons[$current_path]);

if ($is_regular_detail) {
    $related_title = 'OUR LESSONS';
    $related_items = array_values(array_filter($related_items, static function ($item) use ($current_path) {
        $item_path = '/' . trim((string) wp_parse_url($item['url'] ?? '', PHP_URL_PATH), '/') . '/';
        return ! empty($item['title']) && ! empty($item['url']) && $item_path !== $current_path;
    }));
    $gallery_title = $gallery_title ?: 'YOUR SMILE, OUR PASSION.';
    while (count($gallery_images) < 4) {
        $gallery_images[] = [
            'url' => '',
            'alt' => sprintf('Student artwork photo %d', count($gallery_images) + 1),
        ];
    }
}

$related_title_id = wp_unique_id('achiever-service-lessons-');
$gallery_track_id = wp_unique_id('achiever-service-gallery-');
$wrapper_attributes = get_block_wrapper_attributes(['class' => 'achiever-service-detail']);
?>
<div <?php echo $wrapper_attributes; ?>>
  <div class="achiever-service-detail__layout">
    <section class="achiever-service-detail__intro">
      <div class="achiever-service-detail__image">
        <?php if ($main_image) : ?>
          <img src="<?php echo esc_url($main_image); ?>" alt="<?php echo esc_attr($main_image_alt); ?>" loading="lazy" />
        <?php else : ?>
          <svg viewBox="0 0 720 440" role="img" aria-label="<?php echo esc_attr($main_image_alt ?: $heading); ?>">
            <rect width="720" height="440" rx="28" fill="#f7dce5" />
            <circle cx="576" cy="110" r="62" fill="#f2b8ca" />
            <path d="M75 350 210 185l112 105 88-78 170 138Z" fill="#fff7f9" />
            <path d="m170 323 70-151 63 151Z" fill="#25344a" opacity=".82" />
            <circle cx="239" cy="150" r="23" fill="#e76891" />
          </svg>
        <?php endif; ?>
      </div>
      <div class="achiever-service-detail__copy" data-floating-protected>
        <?php if ($service_type) : ?><p class="achiever-service-detail__eyebrow"><?php echo esc_html($service_type); ?></p><?php endif; ?>
        <?php if ($heading) : ?><h2><?php echo esc_html($heading); ?></h2><?php endif; ?>
        <?php if ($age_range) : ?><p class="achiever-service-detail__age"><?php echo esc_html($age_range); ?></p><?php endif; ?>
        <?php foreach (preg_split('/\R{2,}/', $description) as $paragraph) : ?>
          <?php if (trim($paragraph)) : ?><p><?php echo esc_html($paragraph); ?></p><?php endif; ?>
        <?php endforeach; ?>
        <?php if ($info_items) : ?>
          <?php if ($info_title) : ?><h3><?php echo esc_html($info_title); ?></h3><?php endif; ?>
          <ul><?php foreach ($info_items as $item) : ?><li><?php echo esc_html($item['text'] ?? ''); ?></li><?php endforeach; ?></ul>
        <?php endif; ?>
        <?php if ($cta_text && $cta_url) : ?><a class="achiever-btn" href="<?php echo esc_url($cta_url); ?>"><?php echo esc_html($cta_text); ?></a><?php endif; ?>
      </div>
    </section>

    <?php if ($related_title || $related_items) : ?>
      <aside class="achiever-service-detail__related" aria-labelledby="<?php echo esc_attr($related_title_id); ?>">
        <?php if ($related_title) : ?><h2 id="<?php echo esc_attr($related_title_id); ?>"><?php echo esc_html($related_title); ?></h2><?php endif; ?>
        <nav class="achiever-service-detail__related-grid" aria-label="<?php echo esc_attr($related_title); ?>">
          <?php foreach ($related_items as $item) :
            $title = $item['title'] ?? '';
            $url = $item['url'] ?? '';
            $item_path = '/' . trim((string) wp_parse_url($url, PHP_URL_PATH), '/') . '/';
          ?>
            <a class="achiever-service-detail__related-card" href="<?php echo esc_url($url); ?>">
              <span><?php echo esc_html($title); ?></span>
              <?php if (isset($regular_lessons[$item_path])) : ?><small><?php echo esc_html($regular_lessons[$item_path]); ?></small><?php endif; ?>
            </a>
          <?php endforeach; ?>
        </nav>
      </aside>
    <?php endif; ?>
  </div>

  <?php if ($gallery_title || $gallery_images) : ?>
    <section class="achiever-service-detail__gallery" data-service-gallery>
      <?php if ($gallery_title) : ?><h2><?php echo esc_html($gallery_title); ?></h2><?php endif; ?>
      <div class="achiever-service-detail__gallery-slider">
        <button type="button" class="achiever-service-detail__gallery-arrow achiever-service-detail__gallery-arrow--prev" aria-label="<?php echo esc_attr__('Previous gallery image', 'ai-zippy'); ?>" aria-controls="<?php echo esc_attr($gallery_track_id); ?>">&#8249;</button>
        <div id="<?php echo esc_attr($gallery_track_id); ?>" class="achiever-service-detail__gallery-grid" tabindex="0">
        <?php foreach ($gallery_images as $image) : $url = $image['url'] ?? ''; $alt = $image['alt'] ?? ''; ?>
          <div><?php if ($url) : ?><img src="<?php echo esc_url($url); ?>" alt="<?php echo esc_attr($alt); ?>" loading="lazy" /><?php else : ?><span class="achiever-service-detail__gallery-fallback" role="img" aria-label="<?php echo esc_attr($alt); ?>"><svg viewBox="0 0 420 320" aria-hidden="true"><rect width="420" height="320" fill="#f8e8ed"/><path d="M35 270 140 125l82 92 55-65 108 118Z" fill="#f1bfd0"/><circle cx="330" cy="78" r="38" fill="#e76891"/></svg></span><?php endif; ?></div>
        <?php endforeach; ?>
        </div>
        <button type="button" class="achiever-service-detail__gallery-arrow achiever-service-detail__gallery-arrow--next" aria-label="<?php echo esc_attr__('Next gallery image', 'ai-zippy'); ?>" aria-controls="<?php echo esc_attr($gallery_track_id); ?>">&#8250;</button>
      </div>
    </section>
  <?php endif; ?>
</div>
