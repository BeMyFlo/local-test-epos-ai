<?php
/**
 * Server-side render for ai-zippy/home-brands block.
 *
 * @var array    $attributes Block attributes.
 * @var string   $content    Block content (empty for dynamic blocks).
 * @var WP_Block $block      Block instance.
 */

defined('ABSPATH') || exit;

$heading     = $attributes['heading'] ?? "Beyond\nThe Canvas";
$description = $attributes['description'] ?? '';
$brands      = $attributes['brands'] ?? [];
$cta_text    = $attributes['ctaText'] ?? 'CLICK TO SEE MORE';
$cta_url     = $attributes['ctaUrl'] ?? '#';
?>
<div <?php echo get_block_wrapper_attributes(); ?>>
  <section class="achiever-brands">
    <div class="achiever-brands__container">
      <div class="achiever-brands__intro">
        <h2 class="achiever-brands__heading"><?php echo nl2br(esc_html($heading)); ?></h2>
        <p class="achiever-brands__description"><?php echo esc_html($description); ?></p>
        <a href="<?php echo esc_url($cta_url); ?>" class="achiever-btn achiever-btn--primary">
          <?php echo esc_html($cta_text); ?>
        </a>
      </div>
      <div class="achiever-brands__grid">
        <?php foreach ($brands as $brand) :
          $b_name = $brand['name'] ?? '';
          $b_icon = $brand['icon'] ?? '';
        ?>
          <div class="achiever-brands__item">
            <?php if ($b_icon) : ?>
              <img src="<?php echo esc_url($b_icon); ?>" alt="<?php echo esc_attr($b_name); ?>" class="achiever-brands__icon" loading="lazy" />
            <?php endif; ?>
            <span class="achiever-brands__name"><?php echo esc_html($b_name); ?></span>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>
</div>
