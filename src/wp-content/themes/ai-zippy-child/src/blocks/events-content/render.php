<?php
defined('ABSPATH') || exit;
$offerings = $attributes['offerings'] ?? [];
$wrapper_attributes = get_block_wrapper_attributes(['class' => 'achiever-events-content']);
?>
<div <?php echo $wrapper_attributes; ?>>
  <?php foreach ($offerings as $index => $offering) :
    $image = $offering['image'] ?? '';
    $cta_text = $offering['ctaText'] ?? '';
    $cta_url = $offering['ctaUrl'] ?? '';
  ?>
    <section class="achiever-events-content__offering achiever-events-content__offering--<?php echo esc_attr((string) ($index + 1)); ?>">
      <div class="achiever-events-content__copy">
        <?php if (!empty($offering['sectionLabel'])) : ?><p class="achiever-events-content__eyebrow"><?php echo esc_html($offering['sectionLabel']); ?></p><?php endif; ?>
        <?php if (!empty($offering['title'])) : ?><h2><?php echo esc_html($offering['title']); ?><?php if (!empty($offering['age'])) : ?> <span>(<?php echo esc_html($offering['age']); ?>)</span><?php endif; ?></h2><?php endif; ?>
        <?php if (!empty($offering['tagline'])) : ?><h3><?php echo esc_html($offering['tagline']); ?></h3><?php endif; ?>
        <?php if (!empty($offering['description'])) : ?><p><?php echo esc_html($offering['description']); ?></p><?php endif; ?>
        <?php if (!empty($offering['features'])) : ?><ul><?php foreach ($offering['features'] as $feature) : ?><li><?php echo esc_html($feature); ?></li><?php endforeach; ?></ul><?php endif; ?>
        <?php if ($cta_text && $cta_url) : ?><a class="achiever-btn" href="<?php echo esc_url($cta_url); ?>"><?php echo esc_html($cta_text); ?></a><?php endif; ?>
      </div>
      <div class="achiever-events-content__image"><?php if ($image) : ?><img src="<?php echo esc_url($image); ?>" alt="<?php echo esc_attr($offering['alt'] ?? ''); ?>" loading="lazy" /><?php else : ?><span aria-hidden="true"></span><?php endif; ?></div>
    </section>
  <?php endforeach; ?>
</div>
