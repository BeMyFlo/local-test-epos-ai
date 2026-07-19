<?php
defined('ABSPATH') || exit;
$studios = $attributes['studios'] ?? [];
$wrapper_attributes = get_block_wrapper_attributes(['class' => 'achiever-studios-directory']);
?>
<div <?php echo $wrapper_attributes; ?>>
  <?php foreach ($studios as $index => $studio) :
    $phone = $studio['phone'] ?? '';
    $phone_href = preg_replace('/[^0-9+]/', '', $phone);
    $map_url = $studio['mapUrl'] ?? '';
    $map_text = $studio['mapText'] ?? '';
    $gallery = $studio['galleryImages'] ?? [];
  ?>
    <section class="achiever-studios-directory__studio achiever-studios-directory__studio--<?php echo esc_attr((string) ($index + 1)); ?>">
      <div class="achiever-studios-directory__details">
        <h2><?php echo esc_html($studio['name'] ?? ''); ?></h2>
        <?php if (!empty($studio['address'])) : ?><p><?php echo nl2br(esc_html($studio['address'])); ?></p><?php endif; ?>
        <?php if ($phone) : ?><a href="tel:<?php echo esc_attr($phone_href); ?>"><?php echo esc_html($phone); ?></a><?php endif; ?>
        <?php if (!empty($studio['hours'])) : ?><p class="achiever-studios-directory__hours"><?php echo nl2br(esc_html($studio['hours'])); ?></p><?php endif; ?>
      </div>
      <div class="achiever-studios-directory__map">
        <?php if ($map_url && $map_text) : ?><a class="achiever-btn" href="<?php echo esc_url($map_url); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html($map_text); ?></a><?php endif; ?>
      </div>
      <?php if ($gallery) : ?><div class="achiever-studios-directory__gallery"><?php foreach ($gallery as $image) : ?><div><?php if (!empty($image['url'])) : ?><img src="<?php echo esc_url($image['url']); ?>" alt="<?php echo esc_attr($image['alt'] ?? ''); ?>" loading="lazy" /><?php else : ?><span aria-hidden="true"></span><?php endif; ?></div><?php endforeach; ?></div><?php endif; ?>
    </section>
  <?php endforeach; ?>
</div>
