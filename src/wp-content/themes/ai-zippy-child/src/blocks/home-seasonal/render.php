<?php
/**
 * Server-side render for ai-zippy/home-seasonal block.
 *
 * @var array    $attributes Block attributes.
 * @var string   $content    Block content (empty for dynamic blocks).
 * @var WP_Block $block      Block instance.
 */

defined('ABSPATH') || exit;

$section_title = $attributes['sectionTitle'] ?? 'seasonal special';
$products      = $attributes['products'] ?? [];
$cta_text      = $attributes['ctaText'] ?? 'VIEW MORE WORKSHOP';
$cta_url       = $attributes['ctaUrl'] ?? '/shop/';
$badge_image   = $attributes['badgeImage'] ?? '';
$decor_right   = $attributes['decorRightImage'] ?? '';
$decor_bottom  = $attributes['decorBottomImage'] ?? '';
?>
<div <?php echo get_block_wrapper_attributes(); ?>>
  <section class="achiever-seasonal">
    <?php if ($decor_right) : ?>
      <img src="<?php echo esc_url($decor_right); ?>" alt="" class="achiever-seasonal__decor achiever-seasonal__decor--right" loading="lazy" />
    <?php endif; ?>

    <div class="achiever-seasonal__header">
      <?php if ($badge_image) : ?>
        <img src="<?php echo esc_url($badge_image); ?>" alt="" class="achiever-seasonal__badge" loading="lazy" />
      <?php endif; ?>
      <h2 class="achiever-seasonal__title"><?php echo esc_html($section_title); ?></h2>
    </div>
    <div class="achiever-seasonal__grid">
      <?php foreach ($products as $product) :
        $p_name     = $product['name'] ?? '';
        $p_category = $product['category'] ?? '';
        $p_age_time = $product['ageTime'] ?? '';
        $p_image    = $product['image'] ?? '';
        $p_cta_url  = $product['ctaUrl'] ?? '#';
      ?>
        <div class="achiever-seasonal__card">
          <div class="achiever-seasonal__card-image">
            <?php if ($p_image) : ?>
              <img src="<?php echo esc_url($p_image); ?>" alt="<?php echo esc_attr($p_name); ?>" loading="lazy" />
            <?php else : ?>
              <div class="achiever-seasonal__card-placeholder"></div>
            <?php endif; ?>
          </div>
          <div class="achiever-seasonal__card-content">
            <h3 class="achiever-seasonal__card-name"><?php echo esc_html($p_name); ?></h3>
            <span class="achiever-seasonal__card-category"><?php echo esc_html($p_category); ?></span>
            <span class="achiever-seasonal__card-age-time"><?php echo esc_html($p_age_time); ?></span>
            <a href="<?php echo esc_url($p_cta_url); ?>" class="achiever-btn achiever-btn--seasonal">BOOK NOW</a>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
    <div class="achiever-seasonal__footer">
      <?php if ($decor_bottom) : ?>
        <img src="<?php echo esc_url($decor_bottom); ?>" alt="" class="achiever-seasonal__decor achiever-seasonal__decor--bottom" loading="lazy" />
      <?php endif; ?>
      <a href="<?php echo esc_url($cta_url); ?>" class="achiever-btn achiever-btn--primary">
        <?php echo esc_html($cta_text); ?>
      </a>
    </div>
  </section>
</div>
