<?php
/**
 * Server-side render for ai-zippy/home-class-types.
 *
 * @var array $attributes Block attributes.
 */

defined('ABSPATH') || exit;

$section_title = $attributes['sectionTitle'] ?? 'EXPLORE OUR CLASSES';
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
  <h2 class="achiever-class-types__title"><?php echo esc_html($section_title); ?></h2>
  <div class="achiever-scroll-slider achiever-class-types__slider" data-scroll-slider>
    <button type="button" class="achiever-scroll-arrow achiever-scroll-arrow--prev" data-scroll-prev aria-controls="<?php echo esc_attr($track_id); ?>" aria-label="Previous classes">&#8249;</button>
  <div id="<?php echo esc_attr($track_id); ?>" class="achiever-class-types__grid achiever-scroll-track" data-scroll-track tabindex="0">
    <?php foreach ($types as $type) :
        $label = $type['label'] ?? '';
        $image = $type['image'] ?? '';
        $alt   = $type['alt'] ?? $label;
        $url   = $type['url'] ?? '';
        $tag   = $url ? 'a' : 'div';
    ?>
      <<?php echo esc_attr($tag); ?> class="achiever-class-types__card"<?php if ($url) : ?> href="<?php echo esc_url($url); ?>"<?php endif; ?>>
        <span class="achiever-class-types__icon-wrapper<?php echo $image ? '' : ' achiever-class-types__icon-wrapper--placeholder'; ?>"<?php if (!$image) : ?> role="img" aria-label="<?php echo esc_attr($alt); ?>"<?php endif; ?>>
          <?php if ($image) : ?>
            <img class="achiever-class-types__icon" src="<?php echo esc_url($image); ?>" alt="<?php echo esc_attr($alt); ?>" loading="lazy" />
          <?php endif; ?>
        </span>
        <span class="achiever-class-types__name"><?php echo esc_html($label); ?></span>
      </<?php echo esc_attr($tag); ?>>
    <?php endforeach; ?>
  </div>
    <button type="button" class="achiever-scroll-arrow achiever-scroll-arrow--next" data-scroll-next aria-controls="<?php echo esc_attr($track_id); ?>" aria-label="Next classes">&#8250;</button>
  </div>
</section>
