<?php
defined('ABSPATH') || exit;
$pre_heading = $attributes['preHeading'] ?? "IT'S";
$heading = $attributes['heading'] ?? 'PARTY TIME!';
$subtitle = $attributes['subtitle'] ?? 'BIRTHDAYS · GROUP BOOKINGS · CORPORATE EVENTS · TEAM BONDING';
$decor_left_image = $attributes['decorLeftImage'] ?? '';
$decor_left_alt = $attributes['decorLeftAlt'] ?? 'Party character decoration';
$decor_right_image = $attributes['decorRightImage'] ?? '';
$decor_right_alt = $attributes['decorRightAlt'] ?? 'Cake decoration';
$images = $attributes['images'] ?? [];
$cta_text = $attributes['ctaText'] ?? 'ADVANCED BOOK NOW';
$cta_url = $attributes['ctaUrl'] ?? '#';
$wrapper_attributes = get_block_wrapper_attributes(['class' => 'achiever-party']);
?>
<section <?php echo $wrapper_attributes; ?>>
  <div class="achiever-party__decor achiever-party__decor--left<?php echo $decor_left_image ? '' : ' achiever-party__decor--empty'; ?>"><?php if ($decor_left_image) : ?><img src="<?php echo esc_url($decor_left_image); ?>" alt="<?php echo esc_attr($decor_left_alt); ?>" loading="lazy" /><?php endif; ?></div>
  <div class="achiever-party__decor achiever-party__decor--right<?php echo $decor_right_image ? '' : ' achiever-party__decor--empty'; ?>"><?php if ($decor_right_image) : ?><img src="<?php echo esc_url($decor_right_image); ?>" alt="<?php echo esc_attr($decor_right_alt); ?>" loading="lazy" /><?php endif; ?></div>
  <h2 class="achiever-party__heading"><span class="achiever-party__pre-heading"><?php echo esc_html($pre_heading); ?></span><br><?php echo esc_html($heading); ?></h2>
  <p class="achiever-party__subtitle"><?php echo esc_html($subtitle); ?></p>
  <div class="achiever-party__photos">
    <?php foreach ($images as $image) : ?>
      <div class="achiever-party__photo-slot">
        <?php if ($image['url'] ?? '') : ?><img class="achiever-party__photo" src="<?php echo esc_url($image['url']); ?>" alt="<?php echo esc_attr($image['alt'] ?? ''); ?>" loading="lazy" /><?php else : ?><span aria-hidden="true"></span><?php endif; ?>
      </div>
    <?php endforeach; ?>
  </div>
  <?php if ($cta_text) : ?><a class="achiever-btn achiever-btn--primary" href="<?php echo esc_url($cta_url); ?>"><?php echo esc_html($cta_text); ?></a><?php endif; ?>
</section>
