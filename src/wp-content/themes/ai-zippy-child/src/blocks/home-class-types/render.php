<?php
/**
 * Server-side render for ai-zippy/home-class-types block.
 *
 * @var array    $attributes Block attributes.
 * @var string   $content    Block content (empty for dynamic blocks).
 * @var WP_Block $block      Block instance.
 */

defined('ABSPATH') || exit;

$section_title = $attributes['sectionTitle'] ?? 'explore our classes';
$types         = $attributes['types'] ?? [];
$decor_image   = $attributes['decorImage'] ?? '';
?>
<div <?php echo get_block_wrapper_attributes(); ?>>
  <section class="achiever-class-types">
    <?php if ($decor_image) : ?>
      <img src="<?php echo esc_url($decor_image); ?>" alt="" class="achiever-class-types__decor" loading="lazy" />
    <?php endif; ?>
    <h2 class="achiever-class-types__title"><?php echo esc_html($section_title); ?></h2>
    <div class="achiever-class-types__grid">
      <?php foreach ($types as $type) :
        $name   = $type['name'] ?? '';
        $format = $type['format'] ?? '';
        $icon   = $type['icon'] ?? '';
      ?>
        <div class="achiever-class-types__card">
          <div class="achiever-class-types__icon-wrapper">
            <?php if ($icon) : ?>
              <img src="<?php echo esc_url($icon); ?>" alt="<?php echo esc_attr($name); ?>" class="achiever-class-types__icon" loading="lazy" />
            <?php endif; ?>
          </div>
          <h3 class="achiever-class-types__name"><?php echo esc_html($name); ?></h3>
          <span class="achiever-class-types__format"><?php echo esc_html($format); ?></span>
        </div>
      <?php endforeach; ?>
    </div>
  </section>
</div>
