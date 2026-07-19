<?php
/**
 * Server-side render for Foundation Course Content block.
 *
 * @var array $attributes Block attributes.
 */

defined('ABSPATH') || exit;

$heading            = $attributes['heading'] ?? 'FOUNDATION ART COURSE';
$tagline            = $attributes['tagline'] ?? "Let's Artventure";
$tagline_description = $attributes['taglineDescription'] ?? 'Discover our creative journey through art classes, camps, courses and more, designed for artists of all ages.';
$description        = $attributes['description'] ?? '';
$service_image      = $attributes['serviceImage'] ?? '';
$service_image_alt  = $attributes['serviceImageAlt'] ?? 'Foundation art class in progress';
$info_box           = $attributes['infoBox'] ?? [];
$terms_title        = $attributes['termsTitle'] ?? 'TERMS';
$terms              = $attributes['terms'] ?? [];
$supplies_title     = $attributes['suppliesTitle'] ?? 'ART SUPPLIES';
$supplies_text      = $attributes['suppliesText'] ?? '';
$cta_text           = $attributes['ctaText'] ?? 'ENQUIRY NOW';
$cta_url            = $attributes['ctaUrl'] ?? '#course-enquiry';
$programme_title    = $attributes['programmeTitle'] ?? 'PROGRAMME CONTENT';
$programme_subtitle = $attributes['programmeSubtitle'] ?? 'Exploration on Different Mediums';
$programme_content  = $attributes['programmeContent'] ?? [];
$lessons_title      = $attributes['lessonsTitle'] ?? 'OUR LESSONS';
$lessons            = $attributes['lessons'] ?? [];
$gallery_title      = $attributes['galleryTitle'] ?? 'YOUR SMILE, OUR PASSION.';
$gallery_images     = $attributes['galleryImages'] ?? [];

$current_slug = get_post_field('post_name', get_queried_object_id());
if ('foundation-art-course' === $current_slug) {
    $info_box['ageRange'] = 'Age 4–6';
}

$current_path = '/' . trim((string) wp_parse_url(get_permalink(), PHP_URL_PATH), '/') . '/';
$other_lessons = array_values(array_filter($lessons, static function ($lesson) use ($current_path) {
    $lesson_path = '/' . trim((string) wp_parse_url($lesson['url'] ?? '', PHP_URL_PATH), '/') . '/';
    return ! empty($lesson['name']) && ! empty($lesson['url']) && $lesson_path !== $current_path;
}));

while (count($gallery_images) < 4) {
    $gallery_images[] = [
        'url' => '',
        'alt' => sprintf('Student artwork photo %d', count($gallery_images) + 1),
    ];
}

$gallery_id = wp_unique_id('achiever-course-gallery-');
$lessons_title_id = wp_unique_id('achiever-course-lessons-');

$wrapper_attributes = get_block_wrapper_attributes(['class' => 'achiever-course-intro']);
?>
<div <?php echo $wrapper_attributes; ?>>
  <div class="achiever-course-intro__tagline">
    <p class="achiever-course-intro__tagline-title"><?php echo esc_html($tagline); ?></p>
    <p class="achiever-course-intro__tagline-copy"><?php echo esc_html($tagline_description); ?></p>
  </div>
  <div class="achiever-course-intro__detail-layout">
    <section class="achiever-course-intro__overview">
      <div class="achiever-course-intro__service-media">
        <?php if ($service_image) : ?>
          <img src="<?php echo esc_url($service_image); ?>" alt="<?php echo esc_attr($service_image_alt); ?>" loading="lazy" />
        <?php else : ?>
          <svg viewBox="0 0 720 440" role="img" aria-label="<?php echo esc_attr($service_image_alt); ?>">
            <rect width="720" height="440" rx="28" fill="#f7dce5" />
            <circle cx="576" cy="110" r="62" fill="#f2b8ca" />
            <path d="M75 350 210 185l112 105 88-78 170 138Z" fill="#fff7f9" />
            <path d="m170 323 70-151 63 151Z" fill="#25344a" opacity=".82" />
            <circle cx="239" cy="150" r="23" fill="#e76891" />
          </svg>
        <?php endif; ?>
      </div>
      <h1><?php echo esc_html($heading); ?></h1>
      <div class="achiever-course-intro__description" data-floating-protected>
        <?php foreach (preg_split('/\R{2,}/', $description) as $paragraph) : ?>
          <p><?php echo esc_html($paragraph); ?></p>
        <?php endforeach; ?>
      </div>
      <div class="achiever-course-intro__info-box">
        <span><?php echo esc_html($info_box['lessons'] ?? ''); ?></span>
        <span><?php echo esc_html($info_box['duration'] ?? ''); ?></span>
        <span><?php echo esc_html($info_box['ageRange'] ?? ''); ?></span>
      </div>
      <div class="achiever-course-intro__terms">
        <h2><?php echo esc_html($terms_title); ?></h2>
        <?php foreach ($terms as $index => $term) : ?>
          <p<?php echo $index === 3 ? ' class="achiever-course-intro__term-break"' : ''; ?>><?php echo esc_html($term); ?></p>
        <?php endforeach; ?>
      </div>
      <div class="achiever-course-intro__supplies">
        <h2><?php echo esc_html($supplies_title); ?></h2>
        <p><?php echo esc_html($supplies_text); ?></p>
      </div>
      <a class="achiever-btn" href="<?php echo esc_url($cta_url); ?>"><?php echo esc_html($cta_text); ?></a>
    </section>

    <?php if ($other_lessons) : ?>
      <aside class="achiever-course-intro__lessons" aria-labelledby="<?php echo esc_attr($lessons_title_id); ?>">
        <h2 id="<?php echo esc_attr($lessons_title_id); ?>"><?php echo esc_html($lessons_title); ?></h2>
        <nav aria-label="<?php echo esc_attr($lessons_title); ?>">
          <?php foreach ($other_lessons as $lesson) : ?>
            <a href="<?php echo esc_url($lesson['url']); ?>">
              <span><?php echo esc_html($lesson['name']); ?></span>
              <small><?php echo esc_html($lesson['age'] ?? ''); ?></small>
            </a>
          <?php endforeach; ?>
        </nav>
      </aside>
    <?php endif; ?>
  </div>

  <section class="achiever-course-intro__programme">
    <h2><?php echo esc_html($programme_title); ?></h2>
    <p><?php echo esc_html($programme_subtitle); ?></p>
    <div class="achiever-course-intro__programme-grid">
      <?php foreach ($programme_content as $item) : ?><div><?php echo esc_html($item); ?></div><?php endforeach; ?>
    </div>
  </section>

  <section class="achiever-course-intro__gallery" data-course-gallery>
    <h2><?php echo esc_html($gallery_title); ?></h2>
    <div class="achiever-course-intro__gallery-slider">
      <button type="button" class="achiever-course-intro__gallery-arrow achiever-course-intro__gallery-arrow--prev" aria-label="<?php echo esc_attr__('Previous gallery image', 'ai-zippy'); ?>" aria-controls="<?php echo esc_attr($gallery_id); ?>">&#8249;</button>
      <div id="<?php echo esc_attr($gallery_id); ?>" class="achiever-course-intro__gallery-track" tabindex="0">
    <?php foreach ($gallery_images as $image) :
      $url = $image['url'] ?? '';
      $alt = $image['alt'] ?? '';
    ?>
      <div class="achiever-course-intro__gallery-item">
        <?php if ($url) : ?><img src="<?php echo esc_url($url); ?>" alt="<?php echo esc_attr($alt); ?>" loading="lazy" /><?php else : ?><span class="achiever-course-intro__gallery-fallback" role="img" aria-label="<?php echo esc_attr($alt); ?>"><svg viewBox="0 0 420 320" aria-hidden="true"><rect width="420" height="320" fill="#f8e8ed"/><path d="M35 270 140 125l82 92 55-65 108 118Z" fill="#f1bfd0"/><circle cx="330" cy="78" r="38" fill="#e76891"/></svg></span><?php endif; ?>
      </div>
    <?php endforeach; ?>
      </div>
      <button type="button" class="achiever-course-intro__gallery-arrow achiever-course-intro__gallery-arrow--next" aria-label="<?php echo esc_attr__('Next gallery image', 'ai-zippy'); ?>" aria-controls="<?php echo esc_attr($gallery_id); ?>">&#8250;</button>
    </div>
  </section>
</div>
