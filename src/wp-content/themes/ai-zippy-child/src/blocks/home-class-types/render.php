<?php
/**
 * Server-side render for ai-zippy/home-class-types.
 *
 * @var array $attributes Block attributes.
 */

defined('ABSPATH') || exit;

$section_title = $attributes['sectionTitle'] ?? 'EXPLORE OUR CLASSES';
$decor_left_image  = $attributes['decorLeftImage'] ?? '';
$decor_left_alt    = $attributes['decorLeftAlt'] ?? 'Cartoon mascot left decoration';
$decor_right_image = $attributes['decorRightImage'] ?? '';
$decor_right_alt   = $attributes['decorRightAlt'] ?? 'Pencil jar top-right decoration';
$types         = $attributes['types'] ?? [
    ['label' => 'Foundation Art', 'image' => '', 'alt' => 'Foundation Art class photo', 'url' => ''],
    ['label' => 'Art Camp', 'image' => '', 'alt' => 'Art Camp class photo', 'url' => ''],
    ['label' => 'Mixed Media', 'image' => '', 'alt' => 'Mixed Media class photo', 'url' => ''],
    ['label' => 'Watercolour', 'image' => '', 'alt' => 'Watercolour class photo', 'url' => ''],
    ['label' => 'Acrylic Class', 'image' => '', 'alt' => 'Acrylic Class photo', 'url' => ''],
];

$wrapper_attributes = get_block_wrapper_attributes([
    'class' => 'achiever-class-types',
]);
$track_id = wp_unique_id('achiever-class-types-track-');
?>
<section <?php echo $wrapper_attributes; ?>>
  <?php if ($decor_left_image) : ?>
    <div class="achiever-class-types__decor achiever-class-types__decor--left">
      <img src="<?php echo esc_url($decor_left_image); ?>" alt="<?php echo esc_attr($decor_left_alt); ?>" loading="lazy" />
    </div>
  <?php endif; ?>
  <?php if ($decor_right_image) : ?>
    <div class="achiever-class-types__decor achiever-class-types__decor--right">
      <img src="<?php echo esc_url($decor_right_image); ?>" alt="<?php echo esc_attr($decor_right_alt); ?>" loading="lazy" />
    </div>
  <?php endif; ?>
  <h2 class="achiever-class-types__title"><?php echo esc_html($section_title); ?></h2>
  <div class="achiever-scroll-slider achiever-class-types__slider" data-scroll-slider>
    <button type="button" class="achiever-scroll-arrow achiever-scroll-arrow--prev" data-scroll-prev aria-controls="<?php echo esc_attr($track_id); ?>" aria-label="Previous classes">&#8249;</button>
  <div id="<?php echo esc_attr($track_id); ?>" class="achiever-class-types__grid achiever-scroll-track" data-scroll-track tabindex="0">
    <?php foreach ($types as $type) :
        $label    = $type['label'] ?? '';
        $subtitle = $type['subtitle'] ?? '';
        $image    = $type['image'] ?? '';
        $alt      = $type['alt'] ?? $label;
        $url      = $type['url'] ?? '';
        $tag      = $url ? 'a' : 'div';
    ?>
      <<?php echo esc_attr($tag); ?> class="achiever-class-types__card"<?php if ($url) : ?> href="<?php echo esc_url($url); ?>"<?php endif; ?>>
        <?php if ($image) : ?>
          <img src="<?php echo esc_url($image); ?>" alt="<?php echo esc_attr($alt); ?>" loading="lazy" />
        <?php else: ?>
          <div class="achiever-class-types__image-placeholder" style="aspect-ratio: 1 / 1.12; background: #eee;"></div>
        <?php endif; ?>
        <h3><?php echo esc_html($label); ?></h3>
        <?php if ($subtitle) : ?>
          <p><?php echo esc_html($subtitle); ?></p>
        <?php endif; ?>
      </<?php echo esc_attr($tag); ?>>
    <?php endforeach; ?>
  </div>
    <button type="button" class="achiever-scroll-arrow achiever-scroll-arrow--next" data-scroll-next aria-controls="<?php echo esc_attr($track_id); ?>" aria-label="Next classes">&#8250;</button>
  </div>
</section>
