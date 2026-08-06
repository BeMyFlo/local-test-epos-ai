<?php
/**
 * Server-side render for Classes Hero block.
 *
 * @var array $attributes Block attributes.
 */

defined('ABSPATH') || exit;

$heading       = $attributes['heading'] ?? "REGULAR\nCLASSES";
$breadcrumb_home_text = $attributes['breadcrumbHomeText'] ?? 'Home';
$breadcrumb_home_url = $attributes['breadcrumbHomeUrl'] ?? '/';
$breadcrumb_current = $attributes['breadcrumbCurrent'] ?? 'Regular Classes';
$section_title = $attributes['sectionTitle'] ?? 'REGULAR CLASSES';

// Saved attributes still carry the old page names — rewrite them on output.
if (function_exists('ai_zippy_child_rename_page_text')) {
    $heading            = ai_zippy_child_rename_page_text($heading);
    $breadcrumb_current = ai_zippy_child_rename_page_text($breadcrumb_current);
    $section_title      = ai_zippy_child_rename_page_text($section_title);
}
$background_image = $attributes['backgroundImage'] ?? '';
$classes       = $attributes['classes'] ?? [];
$track_id      = wp_unique_id('achiever-classes-track-');

// When a custom background image is set in the editor, output it as an inline
// style so it overrides the default image defined in CSS. Empty = keep the CSS default.
$title_section_style = $background_image
    ? sprintf(' style="background-image:url(%s)"', esc_url($background_image))
    : '';

$wrapper_attributes = get_block_wrapper_attributes(['class' => 'achiever-classes-hero']);
?>
<div <?php echo $wrapper_attributes; ?>>
  <section class="achiever-classes-hero__title-section"<?php echo $title_section_style; ?>>
    <div class="achiever-classes-hero__inner">
      <nav class="achiever-classes-hero__breadcrumb" aria-label="Breadcrumb">
        <a href="<?php echo esc_url($breadcrumb_home_url); ?>"><?php echo esc_html($breadcrumb_home_text); ?></a>
        <span aria-hidden="true">›</span>
        <span aria-current="page"><?php echo esc_html($breadcrumb_current); ?></span>
      </nav>
      <h1 class="achiever-classes-hero__heading"><?php echo nl2br(esc_html($heading)); ?></h1>
    </div>
  </section>
  <section class="achiever-classes-hero__carousel-section">
    <h2 class="achiever-classes-hero__section-title"><?php echo esc_html($section_title); ?></h2>
    <div class="achiever-classes-hero__slider" data-classes-slider>
      <button class="achiever-classes-hero__arrow achiever-classes-hero__arrow--prev" type="button" aria-label="Previous classes" aria-controls="<?php echo esc_attr($track_id); ?>" data-classes-prev>‹</button>
      <div class="achiever-classes-hero__carousel" id="<?php echo esc_attr($track_id); ?>" role="list" tabindex="0" aria-label="Regular art classes" data-classes-track>
      <?php foreach ($classes as $index => $class_item) :
        $tag         = $class_item['tag'] ?? '';
        $tag_bg      = $class_item['tagBg'] ?? '#f2708a';
        $age         = $class_item['age'] ?? '';
        $description = $class_item['description'] ?? '';
        $image       = $class_item['image'] ?? '';
        $alt         = $class_item['alt'] ?? ($tag ?: $section_title);
        $cta_url     = $class_item['ctaUrl'] ?? '';
      ?>
        <article class="achiever-classes-hero__card" role="listitem">
          <a class="achiever-classes-hero__card-link" href="<?php echo esc_url($cta_url); ?>" aria-label="Learn more about <?php echo esc_attr($tag); ?>">
          <div class="achiever-classes-hero__image-slot">
            <?php if ($image) : ?>
              <img src="<?php echo esc_url($image); ?>" alt="<?php echo esc_attr($alt); ?>" loading="lazy" />
            <?php else : ?>
              <svg class="achiever-classes-hero__fallback-art achiever-classes-hero__fallback-art--<?php echo esc_attr((string) (($index % 4) + 1)); ?>" viewBox="0 0 120 120" aria-hidden="true" focusable="false"><circle cx="60" cy="60" r="54"/><path d="M27 80 49 52l16 18 11-13 18 23Z"/><circle cx="80" cy="38" r="9"/><path d="M34 31c10-9 22-13 33-12M27 43c7-7 14-11 22-14"/></svg>
            <?php endif; ?>
          </div>
          <?php if ($tag) : ?>
            <span class="achiever-classes-hero__tag" style="--tag-bg:<?php echo esc_attr($tag_bg); ?>"><?php echo esc_html($tag); ?></span>
          <?php endif; ?>
          <?php if ($age) : ?><strong class="achiever-classes-hero__age"><?php echo esc_html($age); ?></strong><?php endif; ?>
          <?php if ($description) : ?><span class="achiever-classes-hero__description"><?php echo esc_html($description); ?></span><?php endif; ?>
          </a>
        </article>
      <?php endforeach; ?>
      </div>
      <button class="achiever-classes-hero__arrow achiever-classes-hero__arrow--next" type="button" aria-label="Next classes" aria-controls="<?php echo esc_attr($track_id); ?>" data-classes-next>›</button>
    </div>
    <svg class="achiever-classes-hero__decor-pencils" viewBox="0 0 220 70" aria-hidden="true" focusable="false">
      <g transform="rotate(-15 40 30)">
        <path d="M0 20 L130 10 L150 20 L130 30 L0 20 Z" fill="#ff6584"/>
        <path d="M150 20 L165 20 L150 25 Z" fill="#ffb800"/>
      </g>
      <g transform="translate(30, 28) rotate(6)">
        <path d="M0 20 L140 10 L160 20 L140 30 L0 20 Z" fill="#7b61ff"/>
        <path d="M160 20 L175 20 L160 25 Z" fill="#ffb800"/>
      </g>
    </svg>
  </section>
</div>
